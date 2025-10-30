<?php
include 'includes/session.php';

if (isset($_POST['delete'])) {
    $studentID = $_POST['studentID'];

    // Start transaction for safer operation
    $conn->begin_transaction();

    try {
        // Delete from student table
        $sql1 = "DELETE FROM student WHERE studentID = '$studentID'";
        $conn->query($sql1);

        // Delete from studlog table
        $sql2 = "DELETE FROM studlog WHERE studentID = '$studentID'";
        $conn->query($sql2);

        // Commit both deletions
        $conn->commit();
        $_SESSION['success'] = 'Voter deleted successfully.';
    } 
    catch (Exception $e) {
        // Rollback if something went wrong
        $conn->rollback();
        $_SESSION['error'] = 'Error deleting voter: ' . $e->getMessage();
    }
} 
else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: voters.php');
?>
