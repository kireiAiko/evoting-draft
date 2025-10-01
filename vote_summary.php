<?php
session_start();

// Redirect if no vote summary is stored
if (!isset($_SESSION['vote_summary']) || !isset($_SESSION['student_id'])) {
    header('Location: start_voting.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$summary = $_SESSION['vote_summary'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting Summary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
       body {
    background: linear-gradient(135deg, #2c2c6e, #00bcd4);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Montserrat', sans-serif;
    margin: 0;
}

.summary-box {
    background: #fff;
    padding: 40px 50px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 700px;
    animation: fadeIn 0.6s ease-in-out;
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    color: #2c2c6e;
    letter-spacing: 1px;
}

p {
    font-size: 15px;
    margin-bottom: 20px;
    text-align: center;
}

table {
    border-radius: 12px;
    overflow: hidden;
}

table th {
    background: #2c2c6e;
    color: #fff;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
}

table td {
    background: #f9fafc;
    font-size: 15px;
    padding: 12px 15px;
}

.alert {
    border-radius: 12px;
}

.btn-primary {
    background: #2c2c6e;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #3d3d91;
    transform: scale(1.05);
}

/* smooth entrance animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

    </style>
</head>
<body>
    <div class="summary-box">
        <h2>Vote Summary</h2>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($student_id) ?></p>

        <?php if (count($summary) > 0): ?>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Position</th>
                        <th>Selected Candidate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['position']) ?></td>
                            <td><?= htmlspecialchars($item['candidate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                No votes recorded. Please check your ballot or try again.
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="start-voting.php" class="btn btn-primary">Return to Start</a>
        </div>
    </div>
</body>
</html>
