<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Centralized Electronic Voting System</title>
  <link rel="stylesheet" href="dist/css/adminhome.css">
  <!-- Chart.js v4 -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Dashboard</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>".$_SESSION['error']."
            </div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
            </div>";
          unset($_SESSION['success']);
        }
      ?>

      <!-- Summary Boxes -->
      <div class="row">
        <?php
          $pos_cnt = $conn->query("SELECT * FROM positions")->num_rows;
          $cand_cnt = $conn->query("SELECT * FROM candidates")->num_rows;
          $total_voters = $conn->query("SELECT COUNT(*) AS total FROM studlog")->fetch_assoc()['total'];
          $voted_cnt = $conn->query("SELECT COUNT(*) AS total FROM studlog WHERE vote_status='voted'")->fetch_assoc()['total'];
        ?>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner"><h3 style="color: white;"><?= $pos_cnt ?></h3><p>No. of Positions</p></div>
            <div class="icon"><i class="fa fa-tasks"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner"><h3 style="color: white;"><?= $cand_cnt ?></h3><p>No. of Candidates</p></div>
            <div class="icon"><i class="fa fa-black-tie"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner"><h3 style="color: white;"><?= $total_voters ?></h3><p>Total Voters</p></div>
            <div class="icon"><i class="fa fa-users"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner"><h3 style="color: white;"><?= $voted_cnt ?></h3><p>Voters Voted</p></div>
            <div class="icon"><i class="fa fa-check"></i></div>
          </div>
        </div>
      </div>

      <!-- Vote Tally Section -->
      <div class="row">
        <div class="col-xs-12">
          <h3 class="page-header">Votes Tally</h3>
        </div>
      </div>

      <?php
      $pos_q = $conn->query("SELECT * FROM positions ORDER BY priority ASC");
      $openRow = false;
      $colIndex = 0;

      while ($pos = $pos_q->fetch_assoc()):
          $slug = slugify($pos['description']);
          $labels = [];
          $votes = [];

          $cand_q = $conn->query("SELECT * FROM candidates WHERE position_id = '{$pos['id']}'");
          while ($cand = $cand_q->fetch_assoc()) {
              $labels[] = $cand['lastname'] . ', ' . $cand['firstname'];
              $vcount = $conn->query("SELECT COUNT(*) AS total FROM votes WHERE candidate_id='{$cand['id']}'")->fetch_assoc()['total'];
              $votes[] = (int)$vcount;
          }

          if (!$openRow) {
              echo "<div class='row'>";
              $openRow = true;
          }
      ?>
        <div class="col-sm-6">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h4 class="box-title"><b><?= htmlspecialchars($pos['description']) ?></b></h4>
            </div>
            <div class="box-body" style="height: 300px; display: flex; align-items: center; justify-content: center;">
              <?php if (empty($labels)): ?>
                <p class="text-muted">No candidates for this position.</p>
              <?php else: ?>
                <canvas id="<?= $slug ?>" style="height: 250px; width: 100%; max-height: 100%;"></canvas>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php
          $colIndex++;
          if ($colIndex % 2 == 0) {
              echo "</div>";
              $openRow = false;
          }
        ?>

        <?php if (!empty($labels)): ?>
        <script>
        (function(){
          // labels and votes are encoded on the server and available here
          const labels = <?= json_encode($labels) ?>;
          const votes = <?= json_encode($votes) ?>.map(Number);

          // get the canvas element for this chart
          const canvas = document.getElementById('<?= $slug ?>');
          if (!canvas) return;
          const ctx = canvas.getContext('2d');

          // compute sensible bounds for the numeric axis
          let maxVote = votes.length ? Math.max(...votes) : 1;
          if (!isFinite(maxVote) || maxVote < 1) maxVote = 1;
          const xMax = maxVote + 1; // add headroom so small bars (1) are visible

          // destroy previous Chart instance if it exists (prevents duplicate behavior)
          if (canvas.chartInstance) {
            try { canvas.chartInstance.destroy(); } catch(e) { /* ignore */ }
            canvas.chartInstance = null;
          }

          const config = {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Votes',
                data: votes,
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                borderWidth: 1
              }]
            },
            options: {
              indexAxis: 'y', // horizontal bars: numeric axis is x
              responsive: true,
              maintainAspectRatio: false,
              animation: false, // speed up rendering & make deterministic
              scales: {
                x: {
                  type: 'linear',      // force numeric linear scale for votes
                  position: 'bottom',
                  beginAtZero: true,
                  min: 0,              // hard force baseline at 0
                  max: xMax,           // hard force an upper bound (prevents auto-zoom to 1..2)
                  ticks: {
                    stepSize: 1,       // whole-number ticks
                    callback: function(value) {
                      // show only integers on the axis
                      return Number.isInteger(value) ? value : '';
                    }
                  },
                  grid: {
                    drawBorder: true,
                    drawOnChartArea: true
                  }
                },
                y: {
                  type: 'category',
                  ticks: {
                    autoSkip: false,
                    maxRotation: 0,
                    minRotation: 0
                  },
                  grid: {
                    drawBorder: false,
                    drawOnChartArea: false
                  }
                }
              },
              plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
              }
            }
          };

          // create and save instance on the canvas element
          canvas.chartInstance = new Chart(ctx, config);

          // Debug logs (store/remove in production)
          try {
            const scale = canvas.chartInstance.scales.x;
            console.log('Chart <?= $slug ?> votes:', votes, 'scale.min=', scale.min, 'scale.max=', scale.max);
          } catch (e) {
            // ignore debug error
          }
        })();
        </script>
        <?php endif; ?>

      <?php endwhile;
      if ($openRow) echo "</div>"; ?>
      <!-- /Vote Tally Section -->

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
