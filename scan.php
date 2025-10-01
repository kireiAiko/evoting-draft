<?php
session_start();
$studentID = $_SESSION['student_id'] ?? null;
if (!$studentID) { header('Location: start-voting.php'); exit; }

$ballotsFolder = "C:/xampp/htdocs/electronic_voting/ballots";
$errors = [];
$step   = 'scan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_ballot'])) {
        // ✅ Find the newest file inside ballots folder
        $files = glob($ballotsFolder . "/*.*");
        if (!$files) {
            $errors[] = "No ballot found in ballots folder. Please scan first.";
        } else {
            // Sort files by modified time (latest first)
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $latestFile = $files[0];
            $ext = pathinfo($latestFile, PATHINFO_EXTENSION);
            $newName = $ballotsFolder . "/" . $studentID . "." . $ext;

            if (@rename($latestFile, $newName)) {
                $_SESSION['saved_file'] = $newName;

                // ✅ Call scanner_service.py (Flask) to detect votes via cURL
                $url = "http://127.0.0.1:5000/scan?sid=" . urlencode($studentID);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
if ($response === FALSE) {
    $errors[] = "❌ Scanner service not reachable. Please check if it's running.";
} else {
    $data = json_decode($response, true);
    if ($data && $data['status'] === 'OK') {
        $_SESSION['vote_summary'] = $data['summary'];
        $step = 'saved';
    } else {
        // ✅ Handle overvote explicitly
        if (!empty($data['error']) && stripos($data['error'], 'overvote') !== false) {
            $errors[] = "⚠️ Overvote detected. Please rescan your ballot.";
        }
        // ✅ Handle blank/unreadable ballot
        elseif (!empty($data['error']) && stripos($data['error'], 'blank') !== false) {
            $errors[] = "⚠️ Blank or unreadable ballot. Please rescan.";
        }
        // ✅ Generic fallback
        else {
            $errors[] = "⚠️ Ballot saved but scanning failed: " .
                        htmlspecialchars($data['error'] ?? 'Unknown error');
        }
    }
}

            } else {
                $errors[] = "Failed to rename ballot file.";
            }
        }
    }
    elseif (isset($_POST['view_summary'])) {
        header("Location: vote_summary.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scan Ballot</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
  font-family: 'Montserrat', Arial, Helvetica, sans-serif;
  background: linear-gradient(to bottom, #e0f7fa, #00bcd4);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}
.panel{background:#fff;padding:40px 50px;border-radius:12px;
       box-shadow:0 4px 16px rgba(0,0,0,.08);text-align:center;width:460px;}
.icon{font-size:64px;color: #1FC0DA; margin-bottom:20px;}
button{padding:14px 28px;font-size:16px;border:0;border-radius:8px;cursor:pointer;
       background: #00bcd4;color:#fff;margin-top:25px;transition: all 0.3s ease;}
button:hover{background: #0097a7; transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);}
.errors{background:#fbe4e6;color:#c62828;padding:10px 15px;border-radius:6px;margin-top:10px;}
.warnings{background:#fff8e1;color:#f57c00;padding:10px 15px;border-radius:6px;margin-top:10px;}
</style>
</head>
<body>
<div class="panel">
  <div class="icon"><i class="fa-solid fa-print"></i></div>
  <h2>Ballot Scanning for <?=htmlspecialchars($studentID)?></h2>

  <?php if ($errors): ?>
    <div class="errors"><?php foreach ($errors as $e) echo htmlspecialchars($e).'<br>'; ?></div>
  <?php endif; ?>

  <?php if ($step === 'scan'): ?>
    <p>Place your ballot on the scanner.<br><br>
       Then click below to save it as <strong><?=$studentID?>.jpg</strong> and analyze votes.</p>
    <form method="post">
        <input type="hidden" name="save_ballot" value="1">
        <button type="submit"><i class="fa-solid fa-save"></i>&nbsp;Save & Scan Ballot</button>
    </form>

  <?php elseif ($step === 'saved'): ?>
    <p>✅ Ballot has been saved and votes scanned!</p>
    <form method="post">
        <input type="hidden" name="view_summary" value="1">
        <button type="submit"><i class="fa-solid fa-eye"></i>&nbsp;View Vote Summary</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>