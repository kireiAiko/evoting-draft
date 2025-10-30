<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Voters List</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Voters</li>
      </ol>
    </section>

    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                <i class="fa fa-plus"></i> New
              </a>
              <a href="sync_student.php" class="btn btn-info btn-sm btn-flat">
                <i class="fa fa-refresh"></i> Sync Students
              </a>
              <a href="#uploadExcel" data-toggle="modal" class="btn btn-success btn-sm btn-flat">
              <i class="fa fa-upload"></i> Upload Excel
              </a>
            </div> 
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <th>Student ID</th>
                  <th>Last Name</th>
                  <th>First Name</th>
                  <th>Middle Name</th>
                  <th>Program</th>
                  <th>Vote Status</th>
                  <th>Tools</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM studlog ORDER BY studentID ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr>
                          <td>".$row['studentID']."</td>
                          <td>".$row['lastName']."</td>
                          <td>".$row['firstName']."</td>
                          <td>".$row['middleName']."</td>
                          <td>".$row['program']."</td>
                          <td>".$row['vote_status']."</td>
                          <td>
                            <button class='btn btn-success btn-sm edit btn-flat' 
                              data-studentid='".$row['studentID']."' 
                              data-lastname='".$row['lastName']."' 
                              data-firstname='".$row['firstName']."' 
                              data-middlename='".$row['middleName']."' 
                              data-program='".$row['program']."'>
                              <i class='fa fa-edit'></i> Edit
                            </button>
                            <button class='btn btn-danger btn-sm delete btn-flat' 
                              data-studentid='".$row['studentID']."'>
                              <i class='fa fa-trash'></i> Delete
                            </button>
                          </td>
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
  <?php include 'includes/voters_modal.php'; ?>

  <!-- Upload Excel Modal -->
<div class="modal fade" id="uploadExcel" tabindex="-1" role="dialog" aria-labelledby="uploadExcelLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="upload_voters_excel.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h4 class="modal-title"><b>Upload Excel File</b></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <p>Select an Excel file (.xlsx or .xls) containing voter/student data:</p>
          <input type="file" name="excel_file" accept=".xlsx,.xls" class="form-control" required>
          <small class="text-muted">Expected columns: studentID, lastName, firstName, middleName, program, vote_status</small>
        </div>
        <div class="modal-footer">
          <button type="submit" name="import" class="btn btn-primary btn-flat">
            <i class="fa fa-check"></i> Import
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  // Add new voter
  $('#addNewBtn').click(function(e){
    e.preventDefault();
    $('#addnew').modal('show');
  });

  // Edit voter
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#edit').modal('show');
    $('#edit_studentID').val($(this).data('studentid'));
    $('#edit_firstname').val($(this).data('firstname'));
    $('#edit_lastname').val($(this).data('lastname'));
    $('#edit_middlename').val($(this).data('middlename'));
    $('#edit_program').val($(this).data('program'));
  });

  // Delete voter
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var studentID = $(this).data('studentid');
    $('#del_studentID').val(studentID);
    $('.fullname').text(studentID);
  });
});
</script>

</body>
</html>
 