<?php
include 'includes/session.php';

if (!isset($_POST['add'])) {
    $_SESSION['error'] = 'Fill up add form first';
    header('location: voters.php');
    exit();
}

$studentID  = trim($_POST['studentID'] ?? '');
$firstName  = trim($_POST['firstName'] ?? '');
$lastName   = trim($_POST['lastName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$program    = trim($_POST['program'] ?? '');

// Allowed programs (case-insensitive check)
$validPrograms = [
    'Bachelor of Science In Information Systems',
    'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP',
    'BACHELOR IN TECHNICAL VOCATIONAL TEACHER EDUCATION',
    'BSIS','BSE','BTVTED'
];

// helper: normalize multiple spaces to single space and trim
function norm_spaces($s) {
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

// improved real-name heuristic
function isValidName($name, $maxWords = 4, $minWords = 1) {
    $name = norm_spaces($name);
    if ($name === '') return false;
    // split into words
    $words = explode(' ', $name);
    $count = count($words);
    if ($count < $minWords || $count > $maxWords) return false;

    foreach ($words as $w) {
        // only letters (A-Z case-insensitive) — change to allow apostrophe/hyphen if desired
        if (!preg_match('/^[A-Za-z]{2,}$/', $w)) {
            return false; // must be at least 2 letters and letters only
        }
        // must contain at least one vowel (reduce likelihood of gibberish)
        if (!preg_match('/[AEIOUaeiou]/', $w)) {
            return false;
        }
    }
    return true;
}

// normalize inputs
$studentID  = norm_spaces($studentID);
$firstName  = norm_spaces($firstName);
$lastName   = norm_spaces($lastName);
$middleName = norm_spaces($middleName);
$program    = norm_spaces($program);

// Validations
if (!preg_match('/^[0-9]{4}-[0-9]{5}$/', $studentID)) {
    $_SESSION['error'] = 'Invalid Student ID format. ';
    header('location: voters.php'); exit();
}

// firstName: allow 1-4 words
if (!isValidName($firstName, 4, 1)) {
    $_SESSION['error'] = 'Invalid First Name.';
    header('location: voters.php'); exit();
}

// lastName: allow 1-2 words
if (!isValidName($lastName, 2, 1)) {
    $_SESSION['error'] = 'Invalid Last Name.';
    header('location: voters.php'); exit();
}

// middleName: optional but if present allow 1-2 words
if ($middleName !== '' && !isValidName($middleName, 2, 1)) {
    $_SESSION['error'] = 'Invalid Middle Name.';
    header('location: voters.php'); exit();
}

// program check (case-insensitive)
$validUpper = array_map('strtoupper', $validPrograms);
if (!in_array(strtoupper($program), $validUpper)) {
    $_SESSION['error'] = 'Invalid program. ';
    header('location: voters.php'); exit();
}

// Escape for SQL
$studentIDEsc  = $conn->real_escape_string($studentID);
$firstNameEsc  = $conn->real_escape_string($firstName);
$lastNameEsc   = $conn->real_escape_string($lastName);
$middleNameEsc = $conn->real_escape_string($middleName);
$programEsc    = $conn->real_escape_string($program);

// Insert into both tables
$sql1 = "INSERT INTO student (studentID, firstName, lastName, middleName, program)
         VALUES ('$studentIDEsc', '$firstNameEsc', '$lastNameEsc', '$middleNameEsc', '$programEsc')";

$sql2 = "INSERT INTO studlog (studentID, firstName, lastName, middleName, program, vote_status)
         VALUES ('$studentIDEsc', '$firstNameEsc', '$lastNameEsc', '$middleNameEsc', '$programEsc', 'Not Voted')";

if ($conn->query($sql1) && $conn->query($sql2)) {
    $_SESSION['success'] = 'Voter added successfully';
} else {
    // If duplicate studentID or DB error, show useful message
    $_SESSION['error'] = $conn->error;
}

header('location: voters.php');
exit();
?>
