<header class="main-header">
  <!-- Logo -->
  <a href="#" class="logo">
    <span class="logo-mini">
      <img src="images/occ_logo.png" alt="Logo Mini" style="height: 50px;">
    </span>
    <span class="logo-lg"><b>Admin</b>Panel</span>
  </a>

  <!-- Header Navbar -->
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>

    <!-- Title -->
    <span class="voting-system-text" 
          style="display: inline-block; margin-left: 10px; vertical-align: middle; line-height: 50px; color: #fff; font-family:Arial, Helvetica, sans-serif;">
      <b>CENTRALIZED ELECTRONIC VOTING SYSTEM</b>
    </span>

    <!-- User Menu -->
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
          <!-- Profile Icon -->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Profile">
            <i class="fa fa-user-circle-o" style="font-size:22px;"></i>
          </a>
          <ul class="dropdown-menu">
            <!-- User Header -->
            <li class="user-header">     
              <p>
                <?php echo $user['username']; ?>
                <small>Created on <?php echo date('M. Y', strtotime($user['created_on'])); ?></small>
              </p>
            </li>
            <!-- Footer Buttons -->
            <li class="user-footer">
              <div class="pull-left">
                <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" id="admin_profile">Update</a>
              </div>
              <div class="pull-right">
                <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>

<?php include 'includes/profile_modal.php'; ?>
