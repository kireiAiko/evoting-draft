<?php
include 'includes/session.php';
require 'vendor/autoload.php'; // For PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            // Load Excel file
            $spreadsheet = IOFactory::load($fileTmpPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header (first row)
            $isHeader = true;
            $countInserted = 0;

            foreach ($rows as $row) {
                if ($isHeader) { $isHeader = false; continue; }

                $studentID = $row[0] ?? '';
                $lastName = $row[1] ?? '';
                $firstName = $row[2] ?? '';
                $middleName = $row[3] ?? '';
                $program = $row[4] ?? '';
                $vote_status = $row[5] ?? 'Not Voted';

                if (!empty($studentID)) {
                    $stmt = $conn->prepare("INSERT INTO studlog (studentID, lastName, firstName, middleName, program, vote_status)
                                            VALUES (?, ?, ?, ?, ?, ?)
                                            ON DUPLICATE KEY UPDATE 
                                            lastName=VALUES(lastName),
                                            firstName=VALUES(firstName),
                                            middleName=VALUES(middleName),
                                            program=VALUES(program)");
                    $stmt->bind_param("ssssss", $studentID, $lastName, $firstName, $middleName, $program, $vote_status);
                    $stmt->execute();
                    $countInserted++;
                }
            }

            $_SESSION['success'] = "Successfully imported $countInserted records from Excel.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error reading Excel file: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please upload a valid Excel file.";
    }
} else {
    $_SESSION['error'] = "Invalid access.";
}

header("Location: voters.php");
exit();
?>
