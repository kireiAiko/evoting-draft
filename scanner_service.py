from flask import Flask, request, jsonify
import os, shutil, datetime, traceback
import scanner_ocr_detect
import mysql.connector
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

BALLOT_DIR = "C:/xampp/htdocs/voting-practice/ballots"
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'votesystem'
}

# --------------------- DATABASE OPERATIONS ---------------------
def insert_vote(student_id, position_name, candidate_name):
    conn = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # Split candidate name into first and last name
        parts = candidate_name.strip().rsplit(' ', 1)
        if len(parts) < 2:
            print(f"[WARN] Candidate name not in 'First Last' format: {candidate_name}")
            return False
        first, last = parts[0], parts[1]

        # Find candidate
        cursor.execute("SELECT id FROM candidates WHERE UPPER(firstname)=%s AND UPPER(lastname)=%s",
                       (first.upper(), last.upper()))
        res = cursor.fetchone()
        if not res:
            print(f"[ERROR] Candidate not found: {candidate_name}")
            return False
        candidate_id = res[0]

        # Find position
        cursor.execute("SELECT id FROM positions WHERE UPPER(description)=%s", (position_name.upper(),))
        res = cursor.fetchone()
        if not res:
            print(f"[ERROR] Position not found: {position_name}")
            return False
        position_id = res[0]

        # Prevent duplicate votes for same position
        cursor.execute("SELECT id FROM votes WHERE student_id=%s AND position_id=%s", (student_id, position_id))
        if cursor.fetchone():
            print(f"[INFO] Duplicate vote ignored for student {student_id}, position {position_name}")
            return False

        voted_at = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cursor.execute("""
            INSERT INTO votes (student_id, candidate_id, position_id, voted_at)
            VALUES (%s, %s, %s, %s)
        """, (student_id, candidate_id, position_id, voted_at))
        conn.commit()
        return True

    except Exception as e:
        print(f"[ERROR] insert_vote failed: {e}")
        traceback.print_exc()
        return False

    finally:
        if conn:
            conn.close()

# --------------------- BALLOT VALIDATION ---------------------
def validate_ballot(votes):
    """
    votes = {position: [candidates]}
    Returns: (valid: bool, reason: str)
    """
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT description, max_elected FROM positions")
        positions_db = cursor.fetchall()

        total_votes = sum(len(v) if isinstance(v, list) else 1 for v in votes.values())
        if total_votes == 0:
            return False, "Invalid ballot: No votes detected. Please try again."

        for pos_name, max_elected in positions_db:
            voted_candidates = votes.get(pos_name, [])
            if not isinstance(voted_candidates, list):
                voted_candidates = [voted_candidates]

            if not voted_candidates:
                continue  # allow missing sections

            if len(voted_candidates) > max_elected:
                return False, f"Invalid ballot: Overvote detected for {pos_name}. Please review marks."

        return True, ""

    except Exception as e:
        print(f"[ERROR] validate_ballot failed: {e}")
        traceback.print_exc()
        return False, f"Validation error: {e}"

    finally:
        if conn:
            conn.close()


# --------------------- MAIN ENDPOINT ---------------------
@app.route('/scan', methods=['POST'])
def scan_ballot():
    data = request.get_json(force=True)
    image_name = data.get("image_name")
    student_id = data.get("student_id")

    if not image_name or not student_id:
        return jsonify({"error": "Missing image_name or student_id"}), 400

    image_path = os.path.join(BALLOT_DIR, image_name)
    if not os.path.exists(image_path):
        return jsonify({"error": f"Image not found: {image_name}"}), 404

    new_name = f"{student_id}.jpg"
    new_path = os.path.join(BALLOT_DIR, new_name)
    if image_name != new_name:
        shutil.move(image_path, new_path)
        image_path = new_path

    try:
        try:
            votes = scanner_ocr_detect.analyze_ballot(image_path)
            print(f"[INFO] OCR Result: {votes}")
        except ValueError as e:
            # Pass through detailed OCR errors
            err_msg = str(e)
            print(f"[❌ INVALID BALLOT] {err_msg}")
            return jsonify({
                "status": "INVALID",
                "student_id": student_id,
                "error": err_msg,
                "votes_detected": 0,
                "votes_inserted": 0,
                "summary": []
            }), 200

        # Normalize votes
        for k, v in list(votes.items()):
            if not isinstance(v, list):
                votes[k] = [v]

        valid, reason = validate_ballot(votes)
        if not valid:
            print(f"[INVALID] {reason}")
            return jsonify({
                "status": "INVALID",
                "student_id": student_id,
                "error": reason,
                "votes_detected": sum(len(v) for v in votes.values()),
                "votes_inserted": 0,
                "summary": []
            }), 200

        # Insert votes
        inserted = 0
        for position, candidates in votes.items():
            for c in candidates:
                if insert_vote(student_id, position, c):
                    inserted += 1

        print(f"[✅ OK] Inserted {inserted} votes for student {student_id}")

        return jsonify({
            "status": "OK",
            "student_id": student_id,
            "votes_detected": sum(len(v) for v in votes.values()),
            "votes_inserted": inserted,
            "summary": [
                {"position": p, "candidate": c} for p, candidates in votes.items() for c in candidates
            ]
        }), 200

    except Exception as e:
        print(f"[ERROR] Ballot processing failed: {e}")
        traceback.print_exc()
        return jsonify({
            "status": "ERROR",
            "student_id": student_id,
            "details": str(e)
        }), 500

if __name__ == "__main__":
    app.run(debug=True)
