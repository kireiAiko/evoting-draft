<!-- Admin Profile Modal -->
<div class="modal fade" id="profile">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><b>Admin Profile</b></h4>
      </div>

      <div class="modal-body">
        <!-- Profile Icon + Username -->
        <div class="text-center" style="margin-bottom: 20px;">
          <i class="fa fa-user-circle-o" style="font-size:60px; color:#3c8dbc;"></i>
          <h4 style="margin-top:10px;"><?php echo $user['username']; ?></h4>
        </div>

        <form class="form-horizontal" method="POST" 
              action="profile_update.php?return=<?php echo basename($_SERVER['PHP_SELF']); ?>" 
              enctype="multipart/form-data">

          <div class="form-group">
            <label for="username" class="col-sm-3 control-label">Username</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="username" name="username" 
                     value="<?php echo $user['username']; ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="col-sm-3 control-label">Password</label>
            <div class="col-sm-9">
              <input type="password" class="form-control" id="password" name="password" 
                     placeholder="Enter new password (optional)">
            </div>
          </div>

          <hr>

          <div class="form-group">
            <label for="curr_password" class="col-sm-3 control-label">Current Password:</label>
            <div class="col-sm-9">
              <input type="password" class="form-control" id="curr_password" name="curr_password" 
                     placeholder="Input current password to save changes" required>
            </div>
          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-success btn-flat" name="save">
          <i class="fa fa-check-square-o"></i> Save
        </button>
        </form>
      </div>
    </div>
  </div>
</div>
