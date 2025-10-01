<?php
  include 'includes/session.php';

  if(isset($_POST['add'])){
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $position  = $_POST['position'];
    $program   = $_POST['program'];

    // Check for duplicate (same first AND last name)
    $dup_sql   = "SELECT id FROM candidates WHERE firstname = '$firstname' AND lastname = '$lastname'";
    $dup_q     = $conn->query($dup_sql);

    if($dup_q->num_rows > 0){
      $_SESSION['error'] = 'Duplicate entry: candidate already on the list.';
    }
    else{
      $sql = "INSERT INTO candidates (position_id, firstname, lastname, program) VALUES ('$position', '$firstname', '$lastname', '$program')";
      if($conn->query($sql)){
        $_SESSION['success'] = 'Candidate added successfully';
      }
      else{
        $_SESSION['error'] = $conn->error;
      }
    }
  }
  else{
    $_SESSION['error'] = 'Fill up add form first';
  }

  header('location: candidates.php');
?>
