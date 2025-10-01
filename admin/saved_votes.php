<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
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
    <div style="margin: 15px 0;">
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
  echo "<div class='table-responsive'><table class='table table-bordered'>";
  while ($row = $res->fetch_assoc()) {
    if ($current_pos != $row['position_name']) {
      if ($current_pos != '') echo "</tbody>";
      $current_pos = $row['position_name'];
      echo "<thead class='bg-info'>";
      echo "<tr><th colspan='2'>".htmlspecialchars($current_pos)."</th></tr>";
      echo "<tr><th style='width:65%'>Candidate</th><th style='width:35%'>Total Votes</th></tr>";
      echo "</thead><tbody>";
    }
    echo "<tr>
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
