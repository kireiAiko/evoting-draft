<?php
  include 'includes/session.php';

  // Start transaction
  $conn->begin_transaction();

  try {
    // Step 1: Delete all votes
    $sql = "DELETE FROM votes";
    $conn->query($sql);

    // Step 2: Reset all students' vote_status to 'not voted'
    $reset_status_sql = "UPDATE studlog SET vote_status = 'not voted'";
    $conn->query($reset_status_sql);

    // Commit the transaction
    $conn->commit();

    $_SESSION['success'] = "Votes reset successfully and student voting status restored to 'not voted'";
  } catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    $_SESSION['error'] = "Something went wrong while resetting: " . $e->getMessage();
  }

  header('location: votes.php');
?>
