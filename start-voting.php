<?php
session_start();

/* Database credentials */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'votesystem');

$errors      = [];
$studentID   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = trim($_POST['studentID'] ?? '');
    if (!preg_match('/^\d{4}-?\d{5}$/', $studentID)) {
        $errors[] = 'Student ID must be in the form YYYY-##### (e.g. 2022-02811).';
    }

    if (!$errors) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_errno) {
            $errors[] = 'Database connection failed: ' . $mysqli->connect_error;
        } else {
            $stmt = $mysqli->prepare("SELECT vote_status FROM studlog WHERE studentID = ? LIMIT 1");
            $stmt->bind_param('s', $studentID);
            $stmt->execute();
            $res  = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if ($row['vote_status'] === 'voted') {
                    $errors[] = 'This student has already voted.';
                }
            } else {
                $errors[] = 'Student is not currently enrolled.';
            }
            $stmt->close();
        }
    }

    if (!$errors) {
        $_SESSION['student_id'] = $studentID;
        header('Location: scan.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Start Voting</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Montserrat', Arial, Helvetica, sans-serif;
  background: linear-gradient(135deg, #2c2c6e, #00bcd4);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  position: relative;
}
.card {
  background: #ffffff;
  padding: 50px 60px;
  border-radius: 20px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
  width: 450px;
  text-align: center;
}
.card h2 {
  font-size: 1.8rem;
  font-weight: 800;
  color: #2c2c6e;
  margin-bottom: 5px;
}
.card p {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 25px;
  text-transform: uppercase;
  letter-spacing: 1px;
}
form {
  display: flex;
  flex-direction: column;
  align-items: center;
}
input[type="text"] {
  padding: 15px 18px;
  width: 100%;
  font-size: 16px;
  background: #f1f1f1;
  border: none;
  border-radius: 12px;
  margin-bottom: 25px;
  outline: none;
  transition: all 0.3s ease;
}
input[type="text"]:focus {
  background: #fff;
  box-shadow: 0 0 6px rgba(0, 188, 212, 0.4);
}
button {
  width: 100%;
  padding: 15px;
  font-size: 16px;
  font-weight: 700;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  background: #00bcd4;
  color: #fff;
  text-transform: uppercase;
  transition: all 0.3s ease;
}
button:hover {
  background: #0097a7;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.errors {
  background: #fdecea;
  color: #b71c1c;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 14px;
  text-align: left;
}
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
<a href="index.php" class="back-btn" title="Back">
  <i class="fa-solid fa-arrow-left"></i>
</a>

<div class="card">
    <h2>Enter Your Student ID</h2>
    <p>For Verification</p>
    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e) echo htmlspecialchars($e).'<br>'; ?>
      </div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="studentID" placeholder="e.g. 2022-02811" value="<?= htmlspecialchars($studentID) ?>" required>
        <button type="submit">Proceed</button>
    </form>
</div>
</body>
</html>
