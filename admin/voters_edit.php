<?php
include 'includes/session.php';

if (!isset($_POST['edit'])) {
    $_SESSION['error'] = 'Please select a voter to edit first';
    header('location: voters.php');
    exit();
}

$studentID  = trim($_POST['studentID'] ?? '');
$firstName  = trim($_POST['firstName'] ?? '');
$lastName   = trim($_POST['lastName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$program    = trim($_POST['program'] ?? '');

// Allowed programs (case-insensitive)
$validPrograms = [
    'Bachelor of Science In Information Systems',
    'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP',
    'BACHELOR IN TECHNICAL VOCATIONAL TEACHER EDUCATION',
    'BSIS','BSE','BTVTED'
];

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
        // must be letters only (at least 2 letters)
        if (!preg_match('/^[A-Za-z]{2,}$/', $w)) {
            return false;
        }
        // must have at least one vowel to avoid gibberish
        if (!preg_match('/[AEIOUaeiou]/', $w)) {
            return false;
        }
    }
    return true;
}

// --- Normalize Inputs ---
$studentID  = norm_spaces($studentID);
$firstName  = norm_spaces($firstName);
$lastName   = norm_spaces($lastName);
$middleName = norm_spaces($middleName);
$program    = norm_spaces($program);

// --- Validations ---
if (!preg_match('/^[0-9]{4}-[0-9]{5}$/', $studentID)) {
    $_SESSION['error'] = 'Invalid Student ID format. Must be like 2022-03228.';
    header('location: voters.php'); exit();
}

if (!isValidName($firstName, 4, 1)) {
    $_SESSION['error'] = 'Invalid First Name.';
    header('location: voters.php'); exit();
}

if (!isValidName($lastName, 2, 1)) {
    $_SESSION['error'] = 'Invalid Last Name.';
    header('location: voters.php'); exit();
}

if ($middleName !== '' && !isValidName($middleName, 2, 1)) {
    $_SESSION['error'] = 'Invalid Middle Name.';
    header('location: voters.php'); exit();
}

$validUpper = array_map('strtoupper', $validPrograms);
if (!in_array(strtoupper($program), $validUpper)) {
    $_SESSION['error'] = 'Invalid program. ';
    header('location: voters.php'); exit();
}

// --- Escape for SQL ---
$studentIDEsc  = $conn->real_escape_string($studentID);
$firstNameEsc  = $conn->real_escape_string($firstName);
$lastNameEsc   = $conn->real_escape_string($lastName);
$middleNameEsc = $conn->real_escape_string($middleName);
$programEsc    = $conn->real_escape_string($program);

// --- Update both tables ---
$sql1 = "UPDATE student 
         SET firstName='$firstNameEsc', lastName='$lastNameEsc', middleName='$middleNameEsc', program='$programEsc'
         WHERE studentID='$studentIDEsc'";

$sql2 = "UPDATE studlog 
         SET firstName='$firstNameEsc', lastName='$lastNameEsc', middleName='$middleNameEsc', program='$programEsc'
         WHERE studentID='$studentIDEsc'";

if ($conn->query($sql1) && $conn->query($sql2)) {
    $_SESSION['success'] = 'Voter information updated successfully';
} else {
    $_SESSION['error'] = $conn->error;
}

header('location: voters.php');
exit();
?>
