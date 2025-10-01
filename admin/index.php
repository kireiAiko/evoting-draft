<?php
    session_start();
    if(isset($_SESSION['admin'])){
        header('location:home.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OCC Centralized Online Voting System</title>
    <link rel="stylesheet" href="dist/css/adminlog.css?v=1.1">
</head>
<body>

<div class="login-container">
    <!-- Left Side (Logo + Branding) -->
    <div class="login-left">
        <img src="images/occ_logo.png" alt="OCC Logo">
        <h1>OCC Centralized Online Voting System</h1>
    </div>

    <!-- Right Side (Login Form) -->
    <div class="login-right">
        <h2>Admin Login</h2>
        <form action="login.php" method="POST">
            <div class="form">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login">
                <i class="fa fa-sign-in"></i> Login
            </button>
        </form>

        <?php
            if(isset($_SESSION['error'])){
                echo "
                    <div class='callout callout-danger'>
                        <p>".$_SESSION['error']."</p> 
                    </div>
                ";
                unset($_SESSION['error']);
            }
        ?>
    </div>
</div>

</body>
</html>
