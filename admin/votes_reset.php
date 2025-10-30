<?php
include 'includes/session.php';

// Path to ballots folder
$ballots_folder = "C:/xampp/htdocs/voting-practice/ballots";

// Start transaction
$conn->begin_transaction();

try {
    // Step 1: Delete all votes
    $sql = "DELETE FROM votes";
    $conn->query($sql);

    // Step 2: Reset all students' vote_status to 'not voted'
    $reset_status_sql = "UPDATE studlog SET vote_status = 'not voted'";
    $conn->query($reset_status_sql);

    // Step 3: Clear all files in ballots folder
    $files = glob($ballots_folder . "/*"); // get all file names
    foreach($files as $file){
        if(is_file($file)){
            unlink($file); // delete file
        }
    }

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Votes reset successfully, student voting status restored, and ballots folder cleared.";
} catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    $_SESSION['error'] = "Something went wrong while resetting: " . $e->getMessage();
}

header('location: votes.php');
exit();
?>
