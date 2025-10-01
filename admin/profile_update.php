<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $_SESSION['error'] = 'Username is required';
        header('location: index.php');
        exit();
    }

    try {
        if (!empty($password)) {
            // Update both username + password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE admin SET username = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $hashed_password, $user['id']);
        } else {
            // Update only username
            $sql = "UPDATE admin SET username = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $username, $user['id']);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Profile updated successfully';
            // Refresh session username
            $_SESSION['admin_username'] = $username;
        } else {
            $_SESSION['error'] = 'Something went wrong while updating';
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('location: index.php');
exit();
?>
