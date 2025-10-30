import cv2
import pytesseract
import numpy as np
import mysql.connector
import os
from pytesseract import Output
from fuzzywuzzy import fuzz

# ---------- CONFIG -------------------------------------------------
DB = dict(host='127.0.0.1', user='root', password='', database='votesystem')
pytesseract.pytesseract.tesseract_cmd = r"C:\\Program Files\\Tesseract-OCR\\tesseract.exe"
# -------------------------------------------------------------------

OCR_CORRECTIONS = {
    "RUSSET": "RUSSEL",
    "HATSAY": "HALBAY",
    "PACLO": "PAOLO",
    "ETAIZA": "ELAIZA",
    "CATAMONRA": "CATAMORA",
    "VY": "LY",
    "VERO": "DUERO",
    "NICOLE": "BRISEIDA"
}

POSITIONS = [
    "President",
    "Vice President",
    "Secretary",
    "Treasurer",
    "PIO",
    "Auditor"
]

# === Load candidates ===
def load_candidates():
    try:
        conn = mysql.connector.connect(**DB)
        cursor = conn.cursor()
        cursor.execute("SELECT CONCAT(firstname, ' ', lastname) FROM candidates")
        candidates = [c[0].upper() for c in cursor.fetchall()]
        cursor.close()
        conn.close()
        print(f"🟢 Loaded {len(candidates)} candidates.")
        return candidates
    except mysql.connector.Error as e:
        print(f"❌ DB connection failed: {e}")
        return []

CANDIDATES = load_candidates()


# === Analyze the entire ballot ===
def analyze_ballot(image_path):
    if not os.path.exists(image_path):
        raise FileNotFoundError(f"Ballot not found: {image_path}")

    print(f"\n🗳️ Analyzing ballot: {os.path.basename(image_path)}")
    img = cv2.imread(image_path)
    if img is None:
        raise ValueError("Failed to load image.")

    h, w, _ = img.shape
    crop = img[int(h * 0.15):h, 0:w]  # crop voting area
    gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    _, thresh = cv2.threshold(blur, 120, 255, cv2.THRESH_BINARY_INV)
    kernel = np.ones((3, 3), np.uint8)
    thresh = cv2.morphologyEx(thresh, cv2.MORPH_OPEN, kernel, iterations=1)

    # --- Detect stains or damaged paper ---
    damage_score = detect_damage(gray)
    if damage_score > 0.35:
        raise ValueError("Invalid ballot: Damaged or dirty paper detected. Please use a clean ballot and try again.")

    # --- Detect filled marks ---
    contours, _ = cv2.findContours(thresh, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
    filled_marks = []
    half_shaded_count = 0   # 🩵 FIX: use counters instead of booleans
    over_shaded_count = 0

    for c in contours:
        x, y, w_box, h_box = cv2.boundingRect(c)
        area = cv2.contourArea(c)
        filled_ratio = cv2.countNonZero(thresh[y:y + h_box, x:x + w_box]) / (w_box * h_box)

        if not (15 < w_box < 80 and 15 < h_box < 80 and 400 < area < 5000):
            continue

        # Detect shading quality
        if 0.40 <= filled_ratio <= 0.85:
            filled_marks.append((x + w_box // 2, y + h_box // 2))
        elif 0.20 <= filled_ratio < 0.40:
            half_shaded_count += 1
        elif filled_ratio > 0.9:
            over_shaded_count += 1

    # 🩵 FIX: Prioritize clear error categories
    if len(filled_marks) == 0:
        if over_shaded_count > 0:
            raise ValueError("Invalid ballot: Over-shaded mark detected. Please shade normally and avoid heavy marks.")
        elif half_shaded_count > 0:
            raise ValueError("Invalid ballot: Incomplete shading detected. Please fill the circle completely and try again.")
        else:
            raise ValueError("Invalid ballot: No valid marks detected. Please try again.")

    filled_marks.sort(key=lambda p: p[1])
    print(f"🟢 Found {len(filled_marks)} filled marks")

    # Save debug images
    cv2.imwrite("debug_thresh.jpg", thresh)
    debug_img = crop.copy()
    cv2.drawContours(debug_img, contours, -1, (0, 255, 0), 2)
    for (x, y) in filled_marks:
        cv2.circle(debug_img, (x, y), 10, (0, 0, 255), 2)
    cv2.imwrite("debug_contours.jpg", debug_img)

    # --- OCR detection ---
    ocr_data = pytesseract.image_to_data(gray, output_type=Output.DICT)
    text_positions = [
        {'name': word.strip().upper(), 'x': ocr_data['left'][i], 'y': ocr_data['top'][i]}
        for i, word in enumerate(ocr_data['text'])
        if len(word.strip()) > 1
    ]

    def get_nearby_text(cx, cy, names, y_range=40, x_filter=None):
        nearby = []
        for n in names:
            if abs(n['y'] - cy) < y_range:
                if x_filter == 'left' and n['x'] < cx:
                    nearby.append(n['name'])
                elif x_filter == 'right' and n['x'] > cx:
                    nearby.append(n['name'])
                elif x_filter is None:
                    nearby.append(n['name'])
        return ' '.join(nearby[:3])

    def fuzzy_match(text, candidates):
        best_score = 0
        best_match = None
        for cand in candidates:
            score = fuzz.token_set_ratio(text, cand)
            if score > best_score:
                best_score = score
                best_match = cand
        return best_match, best_score

    detected_positions = []
    for t in text_positions:
        match, score = fuzzy_match(t['name'], POSITIONS)
        if match and score > 70:
            detected_positions.append({'position': match, 'y': t['y']})
    detected_positions.sort(key=lambda p: p['y'])

    votes = {pos: [] for pos in POSITIONS}
    for x, y in filled_marks:
        candidate_text = get_nearby_text(x, y, text_positions, y_range=40, x_filter='right')
        corrected_text = OCR_CORRECTIONS.get(candidate_text, candidate_text)
        best_match, best_score = fuzzy_match(corrected_text, CANDIDATES)

        assigned_position = None
        min_dist = float('inf')
        for pos in detected_positions:
            if pos['y'] < y:
                dist = y - pos['y']
                if dist < min_dist:
                    min_dist = dist
                    assigned_position = pos['position']

        if best_match and best_score > 70 and assigned_position:
            votes[assigned_position].append(best_match)
            print(f"🟩 {assigned_position}: {best_match} (score={best_score})")
        else:
            print(f"🟥 No good match for mark at ({x},{y}) (best score={best_score})")

    votes = {k: v for k, v in votes.items() if v}
    print(f"✅ Detected votes per position: {{ {', '.join(f'{k}:{len(v)}' for k,v in votes.items())} }}")

    # --- Final validations ---
    for pos, candidates in votes.items():
       if len(candidates) > 1:
        raise ValueError("Invalid ballot: Overvote detected. Please try again.")


    return votes


# === Detect ballot paper damage (stains, tears, crumples) ===
def detect_damage(gray_img):
    blur = cv2.GaussianBlur(gray_img, (9, 9), 0)
    _, binary = cv2.threshold(blur, 180, 255, cv2.THRESH_BINARY_INV)
    contours, _ = cv2.findContours(binary, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    total_area = gray_img.shape[0] * gray_img.shape[1]
    dark_area = sum(cv2.contourArea(c) for c in contours if cv2.contourArea(c) > 3000)
    ratio = dark_area / total_area

    if ratio > 0.35:
        print(f"⚠️ Detected possible damaged/stained paper (dark area ratio={ratio:.2f})")
    return ratio
