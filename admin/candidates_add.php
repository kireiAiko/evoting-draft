<?php
include 'includes/session.php';

if (!isset($_POST['add'])) {
    $_SESSION['error'] = 'Fill up add form first';
    header('location: candidates.php');
    exit();
}

// --- Inputs ---
$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$position  = $_POST['position'] ?? '';
$program   = trim($_POST['program'] ?? '');

// --- Allowed programs ---
$validPrograms = ['BSIS', 'BTVTED', 'BSE']; // replace with your actual programs

// --- Helpers ---
function norm_spaces($s) {
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function isValidName($name, $maxWords = 4, $minWords = 1) {
    $name = norm_spaces($name);
    if ($name === '') return false;
    $words = explode(' ', $name);
    $count = count($words);
    if ($count < $minWords || $count > $maxWords) return false;

    foreach ($words as $w) {
        if (!preg_match('/^[A-Za-z]{2,}$/', $w)) return false;
        if (!preg_match('/[AEIOUaeiou]/', $w)) return false;
    }
    return true;
}

// --- Normalize inputs ---
$firstname = norm_spaces($firstname);
$lastname  = norm_spaces($lastname);
$program   = norm_spaces($program);

// --- Validations ---
if (!isValidName($firstname, 4, 1)) {
    $_SESSION['error'] = 'Invalid First Name.';
    header('location: candidates.php'); exit();
}

if (!isValidName($lastname, 2, 1)) {
    $_SESSION['error'] = 'Invalid Last Name.';
    header('location: candidates.php'); exit();
}

if (empty($position)) {
    $_SESSION['error'] = 'Please select a position.';
    header('location: candidates.php'); exit();
}

if (!in_array($program, $validPrograms)) {
    $_SESSION['error'] = 'Invalid program.';
    header('location: candidates.php'); exit();
}

// --- Escape inputs ---
$firstnameEsc = $conn->real_escape_string($firstname);
$lastnameEsc  = $conn->real_escape_string($lastname);
$positionEsc  = $conn->real_escape_string($position);
$programEsc   = $conn->real_escape_string($program);

// --- Check duplicate ---
$dup_sql = "SELECT id FROM candidates WHERE firstname='$firstnameEsc' AND lastname='$lastnameEsc'";
$dup_q   = $conn->query($dup_sql);

if ($dup_q->num_rows > 0) {
    $_SESSION['error'] = 'Duplicate entry: candidate already exists.';
} else {
    $sql = "INSERT INTO candidates (position_id, firstname, lastname, program) 
            VALUES ('$positionEsc', '$firstnameEsc', '$lastnameEsc', '$programEsc')";
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Candidate added successfully';
    } else {
        $_SESSION['error'] = $conn->error;
    }
}

header('location: candidates.php');
exit();
?>
