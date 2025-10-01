<?php
include 'includes/session.php';
include 'includes/conn.php'; // if not included in session.php

if (isset($_POST['election_title']) && isset($_POST['saved_at'])) {
    $election_title = $_POST['election_title'];
    $saved_at = $_POST['saved_at'];

    // Prepare the DELETE statement
    $sql = "DELETE FROM saved_votes WHERE election_title = ? AND saved_at = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $election_title, $saved_at);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Saved vote batch for <strong>" . htmlspecialchars($election_title) . "</strong> (saved at " . htmlspecialchars($saved_at) . ") deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete records: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error: Failed to prepare delete statement.";
    }
} else {
    $_SESSION['error'] = "Invalid request. Missing election title or timestamp.";
}

header('Location: saved_votes.php');
exit();
