<?php
  include 'includes/session.php';

  if(isset($_POST['edit'])){
    $id        = $_POST['id'];
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $position  = $_POST['position'];
    $program   = $_POST['program'];

    // Check for duplicate on OTHER records
    $dup_sql = "SELECT id FROM candidates 
                WHERE firstname = '$firstname' AND lastname = '$lastname' AND id <> '$id'";
    $dup_q   = $conn->query($dup_sql);

    if($dup_q->num_rows > 0){
      $_SESSION['error'] = 'Duplicate entry: candidate already on the list.';
    }
    else{
      $sql = "UPDATE candidates SET firstname = '$firstname', lastname = '$lastname', position_id = '$position', program = '$program' WHERE id = '$id'";
      if($conn->query($sql)){
        $_SESSION['success'] = 'Candidate updated successfully';
      }
      else{
        $_SESSION['error'] = $conn->error;
      }
    }
  }
  else{
    $_SESSION['error'] = 'Select item to edit first';
  }

  header('location: candidates.php');
?>