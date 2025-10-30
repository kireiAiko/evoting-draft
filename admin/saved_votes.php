<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<style>
/* ==== Saved Votes Redesign (Clean, Flat, Professional) ==== */
.content-header h1 {
  font-weight: 700;
  color: #1e2d3b;
}

.box.box-primary {
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  margin-bottom: 25px;
}

.box-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  padding: 18px 22px;
  border-bottom: 1px solid #d8e0e8;
  border-radius: 10px 10px 0 0;
}

.box-header h3 {
  font-size: 18px;
  color: #1c2733;
  margin: 0;
  font-weight: 600;
}

.box-header small {
  color: #6b7a89;
  font-size: 13px;
}

.box-tools form {
  display: inline-block;
}

.box-body {
  padding: 20px;
  background: #fff;
  border-radius: 0 0 10px 10px;
}

/* === Table Styling === */
.table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table thead.bg-info th {
  background: #e9f2fb !important;
  color: #1a2a3a;
  font-weight: 600;
  font-size: 15px;
  text-transform: uppercase;
  border-top: 2px solid #3b82f6;
}

.table th, .table td {
  padding: 10px 14px;
  border: 1px solid #e1e6eb;
}

.table tbody tr:nth-child(even) {
  background: #f9fbfd;
}

.table tbody tr:hover {
  background: #f1f6fa;
}

.table th:first-child, .table td:first-child {
  border-left: none;
}

.table th:last-child, .table td:last-child {
  border-right: none;
}

.winner-row {
  background-color: #d1e7dd !important; 
  font-weight: 600;
}

.winner-row td {
  border-top: 2px solid #198754 !important; 
  border-bottom: 2px solid #198754 !important;
  color: #0f5132;
}

/* === Buttons === */
.btn {
  border-radius: 6px;
  font-weight: 600;
}

.btn-success {
  background: #2ea44f;
  border: none;
}

.btn-success:hover {
  background: #278a42;
}

.btn-danger {
  background: #d73a49;
  border: none;
}

.btn-danger:hover {
  background: #b92c3a;
}

.btn-default {
  background: #e9ecef;
  border: none;
  color: #333;
}

.btn-default:hover {
  background: #d6d9dc;
}
</style>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Saved Votes</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Saved Votes</li>
      </ol>
    </section>

    <!-- Back Button -->
    <div style="margin: 15px 0; padding: 10px 15px;">
      <a href="votes.php" class="btn btn-default btn-flat">
        <i class="fa fa-arrow-left"></i> Back to Votes
      </a>
    </div>

    <section class="content">
<?php
// ── Flash messages ────────────────────────────────────────────────
if (isset($_SESSION['error'])) {
  echo "<div class='alert alert-danger alert-dismissible'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <h4><i class='icon fa fa-warning'></i> Error!</h4>{$_SESSION['error']}
        </div>";
  unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
  echo "<div class='alert alert-success alert-dismissible'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <h4><i class='icon fa fa-check'></i> Success!</h4>{$_SESSION['success']}
        </div>";
  unset($_SESSION['success']);
}

// ── Load distinct batches (title + saved_at) ──────────────────────
$batch_sql = "SELECT DISTINCT election_title, saved_at
              FROM saved_votes
              ORDER BY saved_at DESC";
$batch_q = $conn->query($batch_sql);

if ($batch_q->num_rows == 0) {
  echo "<h4 class='text-center'>No saved vote records found.</h4>";
}

while ($batch = $batch_q->fetch_assoc()):
  $election_title = $batch['election_title'];
  $saved_at       = $batch['saved_at']; // raw timestamp
  $saved_label    = date('F j, Y H:i:s', strtotime($saved_at)); // pretty
?>
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <i class="fa fa-archive"></i>
            <?= htmlspecialchars($election_title) ?> &nbsp;
            <small class="text-muted">(saved <?= $saved_label ?>)</small>
          </h3>
          <div class="box-tools" style="display:flex; gap:5px;">
            
            <!-- PRINT BUTTON -->
            <form method="POST" action="print_saved_votes.php" target="_blank">
              <input type="hidden" name="election_title" value="<?= htmlspecialchars($election_title) ?>">
              <input type="hidden" name="saved_at" value="<?= htmlspecialchars($saved_at) ?>">
              <button type="submit" class="btn btn-success btn-sm btn-flat" title="Print PDF">
                <i class="fa fa-print"></i> Print
              </button>
            </form>

            <!-- DELETE BUTTON -->
            <form method="POST" action="delete_saved_votes.php"
                  onsubmit="return confirm('Delete results saved <?= $saved_label ?>?');">
              <input type="hidden" name="election_title" value="<?= htmlspecialchars($election_title) ?>">
              <input type="hidden" name="saved_at" value="<?= htmlspecialchars($saved_at) ?>">
              <button type="submit" class="btn btn-danger btn-sm btn-flat" title="Delete this batch">
                <i class="fa fa-trash"></i> Delete
              </button>
            </form>
          </div>
        </div>

        <div class="box-body">
<?php
  // fetch every candidate for this batch (even 0 votes)
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

  $current_pos = '';
$first_in_pos = true; // track top candidate per position
echo "<div class='table-responsive'><table class='table table-bordered'>";

while ($row = $res->fetch_assoc()) {
  // New position group?
  if ($current_pos != $row['position_name']) {
    if ($current_pos != '') echo "</tbody>"; // close previous section
    $current_pos = $row['position_name'];
    echo "<thead class='bg-info'>";
    echo "<tr><th colspan='2' style='font-size: 20px; font-weight: 800;'>".htmlspecialchars($current_pos)."</th></tr>";
    echo "<tr><th style='width:65%'>Candidate</th><th style='width:35%'>Total Votes</th></tr>";
    echo "</thead><tbody>";
    $first_in_pos = true; // reset for new position
  }

  // highlight top candidate (first row in each position)
  $row_class = $first_in_pos ? "winner-row" : "";
  $first_in_pos = false; // next rows won’t be top

  echo "<tr class='{$row_class}'>
          <td>".htmlspecialchars($row['lastname'].', '.$row['firstname'])."</td>
          <td>".intval($row['total_votes'])."</td>
        </tr>";
}

if ($current_pos != '') echo "</tbody>";
echo "</table></div>";

?>
        </div>
      </div>
<?php endwhile; ?>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
