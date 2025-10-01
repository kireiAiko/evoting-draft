<!-- Save Confirmation Modal -->
<?php
  $parse = parse_ini_file('config.ini', false, INI_SCANNER_RAW);
  $election_title = isset($parse['election_title']) ? $parse['election_title'] : 'Election Title';
?>
<div class="modal fade" id="saveconfirm">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Confirm Save</b></h4>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <p>SAVE CURRENT VOTES</p>
          <h4>
            Are you sure you want to save the current vote results?<br>
            These will be stored under the title:<br><br>
            <strong><?php echo htmlspecialchars($election_title); ?></strong>
          </h4>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <a href="votes_save.php" class="btn btn-success btn-flat">
          <i class="fa fa-save"></i> Confirm Save
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Reset Modal -->
<div class="modal fade" id="reset">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Resetting...</b></h4>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <p>RESET VOTES</p>
          <h4>
            This will delete only the current votes and reset the counts to 0.<br>
            <strong>Saved votes will NOT be affected.</strong>
          </h4>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
          <i class="fa fa-close"></i> Close
        </button>
        <a href="votes_reset.php" class="btn btn-danger btn-flat">
          <i class="fa fa-refresh"></i> Reset
        </a>
      </div>
    </div>
  </div>
</div>
