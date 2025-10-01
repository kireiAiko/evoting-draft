<?php
include 'includes/session.php';

$return = isset($_GET['return']) ? $_GET['return'] : 'home.php';

if (isset($_POST['save'])) {
    $title = trim($_POST['title']);

    // Escape double quotes and wrap in quotes for INI format
    $safe_title = str_replace('"', '', $title);
    $content = 'election_title = "' . $safe_title . '"';

    // Save to config.ini
    if (file_put_contents('config.ini', $content) !== false) {
        $_SESSION['success'] = 'Election title updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update config.ini';
    }
} else {
    $_SESSION['error'] = "Fill up config form first";
}

header('location: ' . $return);
?>
