<?php
include 'includes/session.php';

$parse = parse_ini_file('config.ini', false, INI_SCANNER_RAW);
$election_title = isset($parse['election_title']) ? $parse['election_title'] : 'Election Title';

// Step 1: Check if there are votes
$sql = "SELECT COUNT(*) AS total FROM votes";
$query = $conn->query($sql);
$row = $query->fetch_assoc();

if ($row['total'] == 0) {
    $_SESSION['error'] = "No vote records to save.";
    header('location: votes.php');
    exit();
}

// Step 2: Save votes per candidate and position
$sql = "SELECT position_id, candidate_id, COUNT(*) AS total_votes 
        FROM votes 
        GROUP BY position_id, candidate_id";
$query = $conn->query($sql);

while ($row = $query->fetch_assoc()) {
    $pos_id = $row['position_id'];
    $cand_id = $row['candidate_id'];
    $total = $row['total_votes'];

    $stmt = $conn->prepare("INSERT INTO saved_votes (election_title, position_id, candidate_id, total_votes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $election_title, $pos_id, $cand_id, $total);
    $stmt->execute();
}

// Step 3: Optional – Clear current votes after saving (if desired)
// $conn->query("DELETE FROM votes");
// $conn->query("UPDATE studlog SET vote_status = 'not voted'");

$_SESSION['success'] = "Votes saved successfully under '$election_title'";
header('location: votes.php');
?>
