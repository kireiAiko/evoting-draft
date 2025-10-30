<?php
session_start();

// Folder where scanned ballots are stored
$ballotsFolder = "C:/xampp/htdocs/voting-practice/ballots";
$errors = [];
$step   = 'scan';
$invalid_message = '';

/* Database credentials */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'votesystem');

/* Connect to DB */
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    $errors[] = "Database connection failed: " . $mysqli->connect_error;
}

/* Ensure student_id is set in session */
$student_id = $_SESSION['student_id'] ?? '';
if (!$student_id) {
    header("Location: start-voting.php");
    exit;
}

/* Check if student already voted */
$stmt = $mysqli->prepare("SELECT vote_status FROM studlog WHERE studentID = ? LIMIT 1");
$stmt->bind_param('s', $student_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if ($row['vote_status'] === 'voted') {
        $errors[] = "This student has already voted.";
        $step = 'invalid';
        $invalid_message = "Student already voted";
    }
} else {
    $errors[] = "Student is not currently enrolled.";
    $step = 'invalid';
    $invalid_message = "Invalid student";
}
$stmt->close();

/* Handle POST actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ballot']) && empty($errors)) {

    $files = glob($ballotsFolder . "/*.*");

    if (!$files) {
        $errors[] = "⚠️ No ballot found in the ballots folder. Please scan first.";
    } else {
        // Get the latest scanned file
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        $latestFile = $files[0];

        // Call Flask scanner service
        $url = "http://127.0.0.1:5000/scan";
        $payload = json_encode([
            'image_name' => basename($latestFile),
            'student_id' => $student_id
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === FALSE || $httpcode != 200) {
            $errors[] = "❌ Scanner service not reachable or returned error ($httpcode). " .
                        ($curl_error ? "Curl Error: $curl_error" : "Please check if Flask is running.");
        } else {
            $data = json_decode($response, true);

            if (!is_array($data)) {
                $errors[] = "⚠️ Invalid JSON response from scanner service.";
            } elseif (!empty($data['status']) && $data['status'] === 'OK') {
                $_SESSION['vote_summary'] = $data['summary'] ?? [];
                
                // ---- UPDATE student vote_status ----
                $update = $mysqli->prepare("UPDATE studlog SET vote_status='voted' WHERE studentID=?");
                $update->bind_param('s', $student_id);
                $update->execute();
                $update->close();

                $step = 'saved';
            } elseif (!empty($data['status']) && $data['status'] === 'INVALID') {
                $invalid_message = $data['error'] ?? 'Invalid ballot';
                $step = 'invalid';
            } else {
                $errors[] = "⚠️ " . htmlspecialchars($data['error'] ?? 'Ballot scanning failed.');
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_summary'])) {
    header("Location: vote_summary.php");
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_start'])) {
    session_unset();
    header("Location: start-voting.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scan Ballot</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family: 'Montserrat', Arial, Helvetica, sans-serif; background: linear-gradient(135deg, #2c2c6e, #00bcd4); display:flex; justify-content:center; align-items:center; height:100vh; margin:0; position:relative; }
.panel { background:#fff; padding:40px 50px; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,.08); text-align:center; width:460px; }
button { padding:14px 28px; font-size:16px; border:0; border-radius:8px; cursor:pointer; background: #00bcd4; color:#fff; margin-top:25px; transition: all 0.3s ease; }
button:hover { background: #0097a7; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.errors { background:#fbe4e6; color:#c62828; padding:10px 15px; border-radius:6px; margin-top:10px; text-align:left; }

/* Scanner Animation */
.scanner-container { position: relative; width: 160px; height: 180px; margin: 30px auto; background: #0f0f0fff; border-radius: 10px; box-shadow: inset 0 0 15px rgba(0,0,0,0.3); overflow: hidden; }
.scanner-glass { position: absolute; inset: 10px; background: #a19f9fff; border-radius: 6px; }
.ballot-paper { position: absolute; top: 20px; left: 20px; width: 120px; height: 135px; background: #fff; border: 2px solid #ddd; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.ballot-marks { display: flex; flex-direction: column; gap: 5px; left: 5px; }
.circle { width: 8px; height: 8px; border-radius: 50%; animation: pulse 1s infinite; margin-left: 15px; margin-top: 10px; border: 1px solid #333; }
.filled { background: #333; }
.scan-line { position: absolute; top: 10px; left: 10px; width: calc(100% - 20px); height: 4px; background: linear-gradient(to bottom, rgba(91, 244, 255, 1), rgba(172, 241, 250, 0.8)); border-radius: 2px; animation: scanMove 2s linear infinite; }
@keyframes scanMove { 0% { top: 10px; } 100% { top: calc(100% - 14px); } }

/* Back Button */
.back-btn {
  position: absolute;
  top: 20px;
  left: 20px;
  color: white;
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  transition: color 0.3s ease;
}
.back-btn:hover { color: #e0f7fa; }
</style>
</head>
<body>

<!-- Back Button -->
<a href="start-voting.php" class="back-btn" title="Back">
  <i class="fa-solid fa-arrow-left"></i>
</a>

<div class="panel">
  <h2>Ballot Scanning</h2>

  <div class="scanner-container">
    <div class="scanner-glass"></div>
    <div class="ballot-paper">
      <div class="ballot-marks">
        <div class="circle filled"></div>
        <div class="circle"></div>
        <div class="circle filled"></div>
        <div class="circle"></div>
        <div class="circle filled"></div>
      </div>
    </div>
    <div class="scan-line"></div>
  </div>

  <?php if ($errors): ?>
    <div class="errors">
      <?php foreach ($errors as $e) echo htmlspecialchars($e).'<br>'; ?>
    </div>
  <?php endif; ?>

  <?php if ($step === 'scan'): ?>
    <p>Place your ballot on the scanner.<br><br>
       Then click below to save and analyze your votes automatically.</p>
    <form method="post">
        <button type="submit" name="save_ballot" value="1">
          <i class="fa-solid fa-save"></i>&nbsp;Save & Scan Ballot
        </button>
    </form>

  <?php elseif ($step === 'saved'): ?>
    <p>✅ Ballot has been scanned successfully!</p>
    <?php if (!empty($_SESSION['student_id'])): ?>
      <p><strong>Student ID:</strong> <?=htmlspecialchars($_SESSION['student_id'])?></p>
    <?php endif; ?>
    <form method="post">
        <button type="submit" name="view_summary" value="1">
          <i class="fa-solid fa-eye"></i>&nbsp;View Vote Summary
        </button>
    </form>

  <?php elseif ($step === 'invalid'): ?>
    <p style="color:red; font-weight:bold;">⚠️ <?=$invalid_message?></p>
    <form method="post">
        <button type="submit" name="return_start" value="1">
          <i class="fa-solid fa-rotate-left"></i>&nbsp;Return to Start
        </button>
    </form>
  <?php endif; ?>

  <script>
document.addEventListener("DOMContentLoaded", () => {
  const scanLine = document.querySelector(".scan-line");
  const hasError = document.querySelector(".errors") || 
                   document.querySelector("p[style*='color:red']");

  if (hasError && scanLine) {
    // Stop the blue scan line animation
    scanLine.style.animationPlayState = "paused";
    scanLine.style.background = "rgba(100, 100, 100, 0.3)";
  }
});
</script>

</div>
</body>
</html>
