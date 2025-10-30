<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Votes</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Votes</li>
      </ol>
    </section>

    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <div class="row">
                <div class="col-md-6">
                  <button type="button" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#saveconfirm">
                    <i class="fa fa-save"></i> Save
                  </button>

                  <a href="../admin/saved_votes.php" class="btn btn-primary btn-sm btn-flat">
                    <i class="fa fa-folder-open"></i> View Saved Votes
                  </a>
                </div>
                <div class="col-md-6 text-right">
                  <a href="#reset" data-toggle="modal" class="btn btn-danger btn-sm btn-flat">
                    <i class="fa fa-refresh"></i> Reset
                  </a>
                </div>
              </div>
            </div>

            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Position</th>
                  <th>Candidate</th>
                  <th>Voter (Student ID)</th>
                  <th>Vote Time</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT 
                              votes.*, 
                              positions.description AS position_name, 
                              candidates.firstname AS can_first, 
                              candidates.lastname AS can_last, 
                              studlog.studentID AS voter_id
                            FROM votes 
                            LEFT JOIN positions ON positions.id = votes.position_id 
                            LEFT JOIN candidates ON candidates.id = votes.candidate_id 
                            LEFT JOIN studlog ON studlog.studentID = votes.student_id
                            ORDER BY positions.priority ASC, votes.voted_at DESC";
                    
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr>
                          <td class='hidden'></td>
                          <td>".$row['position_name']."</td>
                          <td>".$row['can_last'].", ".$row['can_first']."</td>
                          <td>".$row['voter_id']."</td>
                          <td>".$row['voted_at']."</td>
                        </tr>
                      ";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>   
  </div>

  <?php include 'includes/footer.php'; ?>

  <!-- Reset Votes Modal -->
  <div class="modal fade" id="reset">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="votes_reset.php">
          <div class="modal-header">
            <h4 class="modal-title">Reset Votes</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to reset all votes?</p>
            <p>This will:</p>
            <ul>
              <li>Delete all votes</li>
              <li>Reset all students’ vote status to “not voted”</li>
              <li>Clear all ballot images in the ballots folder</li>
            </ul>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Yes, Reset</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'includes/votes_modal.php'; ?>
  <?php include 'includes/config_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
