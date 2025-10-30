<!-- Add Modal -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="positions_add.php">
        <div class="modal-header">
          <h4 class="modal-title">Add Position</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Position</label>
            <input type="text" class="form-control" name="description" required>
          </div>
          <div class="form-group">
            <label>Max Elected</label>
            <input type="number" class="form-control" name="max_elected" value="1" min="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="positions_edit.php">
        <div class="modal-header">
          <h4 class="modal-title">Edit Position</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" class="id" name="id">
          <div class="form-group">
            <label>Position</label>
            <input type="text" class="form-control" id="edit_description" name="description" required>
          </div>
          <div class="form-group">
            <label>Max Elected</label>
            <input type="number" class="form-control" id="edit_max_elected" name="max_elected" value="1" min="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="positions_delete.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE POSITION</p>
                    <h2 class="bold description"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
              </form>
            </div>
        </div>
    </div>
</div>



     