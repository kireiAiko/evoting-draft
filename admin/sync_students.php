<?php
// Start session and include DB connection
session_start();
include 'includes/conn.php';

try {
    // STEP 1: Delete students from studlog that no longer exist in student table
    $delete_sql = "DELETE FROM studlog WHERE studentID NOT IN (SELECT studentID FROM student)";
    $conn->query($delete_sql);

    // STEP 2: Insert new students into studlog
    $insert_sql = "INSERT INTO studlog (studentID, lastName, firstName, middleName, program, vote_status)
                   SELECT s.studentID, s.lastName, s.firstName, s.middleName, s.program, 'not voted'
                   FROM student s
                   LEFT JOIN studlog sl ON s.studentID = sl.studentID
                   WHERE sl.studentID IS NULL";
    $conn->query($insert_sql);

    // STEP 3 (Optional): Update existing student info in studlog (only if changed)
    $update_sql = "UPDATE studlog sl
                   JOIN student s ON sl.studentID = s.studentID
                   SET sl.lastName = s.lastName,
                       sl.firstName = s.firstName,
                       sl.middleName = s.middleName,
                       sl.program = s.program";
    $conn->query($update_sql);

    $_SESSION['success'] = 'Student records successfully synced with studlog!';
} catch (Exception $e) {
    $_SESSION['error'] = 'Sync failed: ' . $e->getMessage();
}

header('location: voters.php');
exit();
