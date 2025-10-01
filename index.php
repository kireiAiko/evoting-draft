<?php
  // Load election title from config.ini inside admin folder
  $configPath = '../admin/config.ini';
  $parse = file_exists($configPath) ? parse_ini_file($configPath, false, INI_SCANNER_RAW) : [];

  $default_title = 'Electronic Voting System of One Cainta College';

  // Use config title if available and trimmed
  $title = isset($parse['election_title']) && strlen(trim($parse['election_title'])) > 0
    ? trim($parse['election_title'], "\"'")
    : $default_title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="static/home.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Yatra+One&display=swap" rel="stylesheet">
</head>
<body>
  <div class="logowbg">
    <div class="logofo">
      <img src="static/occ_logo.png" class="logo" />
    </div>
  </div>

  <header class="index-home">
    <div class="bg-head">
      <img class="bgimage" src="static/occ.png" />
      <div class="home-content">
        <h1 class="election-title"><?php echo htmlspecialchars($title); ?></h1>
        <div class="buto">
          <a href="start-voting.php">
            <button>Start Voting</button>
          </a>
          <a href="admin/index.php">
            <button style="background-color: #303841;">Admin Login</button>
          </a>
        </div>
      </div>
    </div>
  </header>
</body>
</html>
