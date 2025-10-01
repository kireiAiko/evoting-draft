"""
scanner_service.py – Test mode (improved)
• Expects image {studentID}.jpg/.png in /ballots
• Detects marks, detects overvotes / blank ballots and returns JSON.
Run: python scanner_service.py
"""
import os
from flask import Flask, request, jsonify
import cv2
import mysql.connector
from datetime import datetime

# ---------- CONFIG -------------------------------------------------
DB = dict(host='127.0.0.1', user='root', password='', database='votesystem')
BALLOT_DIR = 'ballots'
IMG_EXTS   = ('.jpg', '.jpeg', '.png', '.tif')
# tweak this if your scanner is noisy (0.10 -> 0.15 etc)
MIN_DARK_FOR_MARK = 0.12
# -------------------------------------------------------------------

app = Flask(__name__)

# ---------- BBOXES (keep as you have) ------------------------------
BBOXES = {
    8: {   # PRESIDENT
        23: (74, 294, 35, 35),
        24: (74, 331, 35, 35),
        25: (76, 356, 35, 35),
    },
    9: {   # VICE PRESIDENT
        26: (71, 423, 35, 35),
        27: (71, 456, 35, 35),
    },
    11: {  # SECRETARY
        28: (76, 520, 35, 35),
        29: (74, 556, 35, 35),
        30: (71, 587, 35, 35),
        31: (74, 620, 35, 35),
    },
    12: {  # TREASURER
        32: (74, 689, 35, 35),
        33: (74, 720, 35, 35),
        34: (74, 758, 35, 35),
    },
    13: {  # PIO
        37: (76, 820, 35, 35),
        38: (76, 854, 35, 35),
        39: (76, 887, 35, 35),
    },
    14: {  # AUDITOR
        40: (78, 951, 35, 35),
        41: (74, 985, 35, 35),
        42: (74, 1018, 35, 35),
    }
}
# ------------------------------------------------------------------

def find_image(student_id):
    for ext in IMG_EXTS:
        path = os.path.join(BALLOT_DIR, f'{student_id}{ext}')
        if os.path.isfile(path):
            return path
    return None

def detect_marks(img, min_dark=MIN_DARK_FOR_MARK):
    """
    Returns (votes, overvotes, details)
      - votes: list of dicts {'position_id', 'candidate_id'}
      - overvotes: list of dicts {'position_id', 'candidates': [cand_ids], 'ratios': [...]}
      - details: per-position per-candidate dark ratios (for debugging)
    """
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.GaussianBlur(gray, (3,3), 0)
    _, thresh = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

    # remove small speckles
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (3,3))
    thresh = cv2.morphologyEx(thresh, cv2.MORPH_OPEN, kernel)

    votes = []
    overvotes = []
    details = {}

    h_img, w_img = thresh.shape

    for pos_id, candidates in BBOXES.items():
        marked = []
        details[pos_id] = {}
        for cand_id, (x, y, w, h) in candidates.items():
            # crop ROI safely
            x1 = max(0, x)
            y1 = max(0, y)
            x2 = min(w_img, x + w)
            y2 = min(h_img, y + h)
            roi = thresh[y1:y2, x1:x2]

            if roi.size == 0:
                dark_ratio = 0.0
            else:
                dark_ratio = cv2.countNonZero(roi) / float(w * h)

            details[pos_id][cand_id] = round(dark_ratio, 3)
            if dark_ratio >= min_dark:
                marked.append((cand_id, dark_ratio))

        if len(marked) == 1:
            votes.append({'position_id': pos_id, 'candidate_id': marked[0][0]})
        elif len(marked) > 1:
            # overvote: list candidate ids and their ratios (for debugging)
            marked_sorted = sorted(marked, key=lambda x: x[1], reverse=True)
            overvotes.append({
                'position_id': pos_id,
                'candidates': [m[0] for m in marked_sorted],
                'ratios': [round(m[1],3) for m in marked_sorted]
            })
        # else: no mark for this position

    # print diagnostics to terminal to help debug
    print("\n--- detect_marks results ---")
    for pid, d in details.items():
        print(f"Position {pid}: ", d)
    print("votes:", votes)
    print("overvotes:", overvotes)
    print("---------------------------\n")

    return votes, overvotes, details

@app.route('/scan')
def scan_route():
    sid = request.args.get('sid', '').strip()
    if not sid:
        return jsonify(status='ERROR', error='sid missing'), 400

    img_path = find_image(sid)
    if not img_path:
        return jsonify(status='ERROR', error='Image not found in ballots folder'), 404

    try:
        img = cv2.imread(img_path)
        if img is None:
            return jsonify(status='ERROR', error='Image load failed'), 400

        votes, overvotes, details = detect_marks(img)

        if overvotes:
            return jsonify(
                status='ERROR',
                error='Overvote detected',
                overvotes=overvotes,
                details=details
            ), 400

        if not votes:
            return jsonify(status='ERROR', error='Blank ballot detected', details=details), 400

    except Exception as e:
        return jsonify(status='ERROR', error=str(e)), 500

    # ---- insert votes and return summary
    try:
        conn = mysql.connector.connect(**DB)
        cur  = conn.cursor()

        for v in votes:
            cur.execute("""INSERT INTO votes
                           (student_id, position_id, candidate_id, voted_at)
                           VALUES (%s,%s,%s,%s)""",
                        (sid, v['position_id'], v['candidate_id'],
                         datetime.now().strftime('%Y-%m-%d %H:%M:%S')))
        cur.execute("UPDATE studlog SET vote_status='voted' WHERE studentID=%s", (sid,))

        cur.execute("""SELECT p.description,
                              CONCAT(c.lastname, ', ', c.firstname)
                       FROM votes v
                       JOIN positions  p ON p.id = v.position_id
                       JOIN candidates c ON c.id = v.candidate_id
                       WHERE v.student_id=%s""", (sid,))
        summary = [{'position': r[0], 'candidate': r[1]} for r in cur.fetchall()]

        conn.commit()

    except Exception as e:
        return jsonify(status='ERROR', error='DB error: ' + str(e)), 500
    finally:
        try:
            cur.close()
            conn.close()
        except:
            pass

    return jsonify(status='OK', summary=summary, image=os.path.basename(img_path))
# ------------------------------------------------------------------
if __name__ == '__main__':
    os.makedirs(BALLOT_DIR, exist_ok=True)
    app.run(host='0.0.0.0', port=5000, debug=False)
