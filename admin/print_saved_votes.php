<?php
require_once('tcpdf/tcpdf.php'); // Ensure TCPDF is installed
include 'includes/session.php';

if (!isset($_POST['election_title']) || !isset($_POST['saved_at'])) {
    die("Invalid request.");
}

$election_title = $_POST['election_title'];
$saved_at       = $_POST['saved_at'];

// ── Fetch data ─────────────────────────────────────────────
$inner_sql = "SELECT
                positions.description      AS position_name,
                positions.priority         AS pos_priority,
                candidates.lastname        AS lastname,
                candidates.firstname       AS firstname,
                COALESCE(sv.total_votes,0) AS total_votes
              FROM candidates
              JOIN positions               ON positions.id = candidates.position_id
              LEFT JOIN saved_votes sv     ON sv.candidate_id = candidates.id
                                           AND sv.saved_at     = ?
                                           AND sv.election_title = ?
              ORDER BY pos_priority ASC, total_votes DESC";
$stmt = $conn->prepare($inner_sql);
$stmt->bind_param("ss", $saved_at, $election_title);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("No vote data found for this batch.");
}

// ── Initialize PDF ────────────────────────────────────────
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('OCC Voting System');
$pdf->SetTitle('Election Results - '.$election_title);
$pdf->SetHeaderData('', 0, 'Election Results', $election_title . ' (Saved: '.date('F j, Y H:i:s', strtotime($saved_at)).')');
$pdf->setPrintFooter(false);

$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// ── Build content ────────────────────────────────────────
$html = "<h2 style='text-align:center;'>Election Results</h2>";
$html .= "<h4 style='text-align:center;'>$election_title</h4>";
$html .= "<p><strong>Saved At:</strong> ".date('F j, Y H:i:s', strtotime($saved_at))."</p>";

$current_pos = '';
while ($row = $res->fetch_assoc()) {
    if ($current_pos != $row['position_name']) {
        if ($current_pos != '') {
            $html .= "</table><br>";
        }
        $current_pos = $row['position_name'];
        $html .= "<h3>".$current_pos."</h3>";
        $html .= "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
        $html .= "<tr style='background-color:#f2f2f2;'>
                    <th width='65%'>Candidate</th>
                    <th width='35%'>Total Votes</th>
                  </tr>";
    }

    $html .= "<tr>
                <td>".htmlspecialchars($row['lastname'].', '.$row['firstname'])."</td>
                <td align='center'>".intval($row['total_votes'])."</td>
              </tr>";
}

$html .= "</table>";

// ── Output PDF ───────────────────────────────────────────
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Election_Results_'.$election_title.'.pdf', 'D'); // 'D' = force download
exit;
?>
