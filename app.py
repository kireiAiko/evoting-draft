from flask import Flask, render_template, request, redirect, flash, url_for
import mysql.connector
import os
import json
from scanning.scanner import scan_ballot

app = Flask(__name__)
app.secret_key = 'your_secret_key'

# MySQL connection config
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'votesystem'
}

@app.route('/start-voting', methods=['GET', 'POST'])
def start_voting():
    if request.method == 'POST':
        student_id = request.form['student_id']
        student_id = student_id.strip()

        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        # Check if student exists
        cursor.execute("SELECT * FROM studlog WHERE studentID = %s", (student_id,))
        student = cursor.fetchone()

        if not student:
            flash("Student ID not found or not enrolled.", "error")
            return redirect(url_for('start_voting'))

        if student['vote_status'] == 'voted':
            flash("You have already voted.", "error")
            return redirect(url_for('start_voting'))

        # Check if ballot exists
        ballot_path = f"ballots/{student_id}.jpg"
        if not os.path.exists(ballot_path):
            flash("Ballot not found for this student ID.", "error")
            return redirect(url_for('start_voting'))

        # Scan the ballot
        results = scan_ballot(ballot_path)

        # Update vote status to "voted"
        cursor.execute("UPDATE studlog SET vote_status = 'voted' WHERE studentID = %s", (student_id,))
        conn.commit()

        cursor.close()
        conn.close()

        # Save results to summary_data.json (optional for watcher)
        with open('templates/summary_data.json', 'w') as f:
            json.dump({'student_id': student_id, 'results': results}, f)

        return render_template('vote_summary.html', student_id=student_id, results=results)

    return render_template('start-voting.html')
