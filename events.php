<?php
// events.php
include "db.php";

function h($v){ return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8'); }

function renderDay($conn, $dayLabel){
  $stmt = $conn->prepare("SELECT * FROM events WHERE day = ? ORDER BY event_date, event_time, id");
  $stmt->bind_param("s", $dayLabel);
  $stmt->execute();
  $res = $stmt->get_result();

  echo '<div class="day">';
  echo '<h2>'.strtoupper($dayLabel).' Events</h2>';

  if ($res->num_rows === 0) {
    echo '<p>No events yet.</p>';
  } else {
    while($row = $res->fetch_assoc()){
      echo '<div class="card">
              <h3>'.h($row['event_name']).'</h3>
              <p><b>Date:</b> '.h($row['event_date']).' &nbsp;</p>
              <p> <b>Time:</b> '.h($row['event_time']).'</p>
              <p><b>Venue:</b> '.h($row['venue']).' &nbsp;</p>
             <p>  <b>Price:</b> ₹'.h($row['price']).' &nbsp;</p>
             <p>   <b>Capacity:</b> '.h($row['capacity']).'</p>
              <p><b>Rules:</b>'.nl2br(h($row['rules'])).'</p>
            </div>';
    }
  }
  echo '</div>';

  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
 <meta name="viewport" content="width=380, initial-scale=1.0">
<title>Events</title>
<style>
  /* body{font-family:Arial, sans-serif; background:#0b1020; color:#fff; margin:0; padding:30px;}
  h1{margin-top:0;} */
  .grid{display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;}
  .day h2{margin:10px 0;}
  .card{background: linear-gradient(to bottom right, #001234a2, #1377db69); border:1px solid #4da6ff; border-radius:14px; padding:16px; margin: 10px;}
  .card h3{margin:0 0 8px;}
</style>
<link href="index.css" rel="stylesheet">
</head>

<body>
     <header class="topbar">
    <div id="logo">
      <img src="logo.png" alt="logo">
      <div id="afterlogo"><h1>Inventrix</h1></div>
    </div>
     <nav id="navbar">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li><a href="registration.php">Registration</a></li>
        <li><a href="gallery.html">Gallery</a></li>
      </ul>
    </nav>
    <!-- Right controls wrapper -->
    <div class="right-controls">
      <button id="login"><a href="login.php">Log in</a></button>
      <button class="hamburger" id="hamburger">&#9776;</button>
    </div>
  </header>

  <h1 style="text-align:center;">Our Events</h1>
  <div class="grid">
    <?php renderDay($conn, 'day1'); ?>
    <?php renderDay($conn, 'day2'); ?>
  </div>
 <script src="index.js"></script>
</body>
</html>
