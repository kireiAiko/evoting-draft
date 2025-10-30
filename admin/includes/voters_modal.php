<!-- Add New Voter Modal -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Add New Voter</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="voters_add.php">
          <div class="form-group">
            <label for="studentID" class="col-sm-3 control-label">Student ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="studentID" name="studentID" required>
            </div>
          </div>
          <div class="form-group">
            <label for="lastname" class="col-sm-3 control-label">Last Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="lastname" name="lastName" required>
            </div>
          </div>
          <div class="form-group">
            <label for="firstname" class="col-sm-3 control-label">First Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="firstname" name="firstName" required>
            </div>
          </div>
          <div class="form-group">
            <label for="middlename" class="col-sm-3 control-label">Middle Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="middlename" name="middleName">
            </div>
          </div>
          <div class="form-group">
            <label for="program" class="col-sm-3 control-label">Program</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="program" name="program" required>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-primary btn-flat" name="add">
          <i class="fa fa-save"></i> Save
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Voter Modal -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Edit Voter</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="voters_edit.php">
          <div class="form-group">
            <label for="edit_studentID" class="col-sm-3 control-label">Student ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_studentID" name="studentID" readonly>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_lastname" class="col-sm-3 control-label">Last Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_lastname" name="lastName" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_firstname" class="col-sm-3 control-label">First Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_firstname" name="firstName" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_middlename" class="col-sm-3 control-label">Middle Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_middlename" name="middleName">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_program" class="col-sm-3 control-label">Program</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_program" name="program" required>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-success btn-flat" name="edit">
          <i class="fa fa-check-square-o"></i> Update
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Voter Modal -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Deleting...</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="voters_delete.php">
          <input type="hidden" id="del_studentID" name="studentID">
          <div class="text-center">
            <p>DELETE VOTER</p>
            <h2 class="bold fullname"></h2>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-danger btn-flat" name="delete">
          <i class="fa fa-trash"></i> Delete
        </button>
        </form>
      </div>
    </div>
  </div>
</div>
