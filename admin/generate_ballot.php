<?php
require_once('tcpdf/tcpdf.php');
include 'includes/conn.php';

// Read election title
$parse = parse_ini_file('config.ini', false, INI_SCANNER_RAW);
$electionTitle = isset($parse['election_title']) ? strtoupper(trim($parse['election_title'])) : "STUDENT COUNCIL VOTATION";

// Fetch positions
$positionsQuery = $conn->query("SELECT id, description FROM positions ORDER BY priority ASC");
$positions = [];
while ($row = $positionsQuery->fetch_assoc()) {
    $positions[] = $row;
}

// Fetch candidates
$candidatesQuery = $conn->query("SELECT * FROM candidates ORDER BY lastname ASC, firstname ASC");
$candidates = [];
while ($row = $candidatesQuery->fetch_assoc()) {
    $candidates[$row['position_id']][] = $row;
}

// Create PDF (Letter size)
$pdf = new TCPDF('L', 'mm', [279.4, 215.9], true, 'UTF-8', false); // Landscape Letter
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();

// Three columns (ballots)
$outerMargin = 10;
$innerMargin = 6;
$colWidth = ($pageWidth - ($outerMargin * 2) - ($innerMargin * 2)) / 3;
$ballotHeight = $pageHeight - 20; // for top/bottom space

// Draw single ballot function
function drawBallot($pdf, $x, $y, $width, $height, $title, $positions, $candidates) {
    $currentY = $y + 8;

    // Border
    $pdf->Rect($x, $y, $width, $height);

    // Header: School name
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY($x, $currentY);
    $pdf->MultiCell($width, 6, "ONE CAINTA COLLEGE", 0, 'C', 0);
    $currentY += 6;

    // Subheader: Dynamic election title
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY($x, $currentY);
    $pdf->MultiCell($width, 5, strtoupper($title), 0, 'C', 0);
    $currentY += 6;

    // S.Y.
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetXY($x, $currentY);
    $pdf->MultiCell($width, 5, "S.Y. 2025 - 2026", 0, 'C', 0);
    $currentY += 8;

    // Instruction
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY($x, $currentY);
    $pdf->MultiCell($width, 5, "Shade the circle beside the name of your chosen candidate.", 0, 'C', 0);
    $currentY += 8;

    // Candidates per position
    foreach ($positions as $pos) {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($x + 5, $currentY);
        $pdf->Cell($width - 10, 5, strtoupper($pos['description']) . " (1 vote)", 0, 1, 'L');
        $currentY += 5;

        if (!empty($candidates[$pos['id']])) {
            $pdf->SetFont('helvetica', '', 9);
            foreach ($candidates[$pos['id']] as $cand) {
                $circleX = $x + 8;
                $circleY = $currentY + 2;
                $pdf->Circle($circleX, $circleY, 1.7);
                $pdf->SetXY($circleX + 5, $currentY);
                $pdf->Cell($width - 15, 5, strtoupper($cand['lastname'] . ', ' . $cand['firstname']), 0, 1, 'L');
                $currentY += 5;
            }
        }
        $currentY += 3;
    }


}

// Draw 3 ballots across (left, center, right)
for ($i = 0; $i < 3; $i++) {
    $xPos = $outerMargin + ($i * ($colWidth + $innerMargin));
    drawBallot($pdf, $xPos, 10, $colWidth, $ballotHeight, $electionTitle, $positions, $candidates);
}

// Output
$pdf->Output('ballot_3perpage.pdf', 'I');
?>
