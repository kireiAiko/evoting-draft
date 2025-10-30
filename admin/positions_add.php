<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    $description = $_POST['description'];
    $max_elected = $_POST['max_elected'];

    $sql = "SELECT * FROM positions ORDER BY priority DESC LIMIT 1";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    $priority = $row ? $row['priority'] + 1 : 1;

    $sql = "INSERT INTO positions (description, priority, max_elected) VALUES ('$description', '$priority', '$max_elected')";
    if($conn->query($sql)){
        $_SESSION['success'] = 'Position added successfully';
    } else {
        $_SESSION['error'] = $conn->error;
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: positions.php');
?>
