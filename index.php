<?php
session_start();
include "db.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=380, initial-scale=1.0">
  <title>Inventrix</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="index.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
        <li><a href="registration.php">registration</a></li>
        <li><a href="gallery.html">Gallery</a></li>
      </ul>
    </nav>
    <!-- Right controls wrapper -->
    <div class="right-controls">
      <button id="login"><a href="login.php">Log in</a></button>
      <button class="hamburger" id="hamburger">&#9776;</button>
    </div>
  </header>
  <main>
    <div class="cyber-banner">
  <!-- Left features -->
  <div class="features-col">
    <div class="feature">
      <i class="fas fa-lightbulb"></i>
      <div class="feature-title">Innovative</div>
      <div class="feature-subtitle">Fresh ideas, Smart tech, Limitless creativity</div>
    </div>
    <div class="feature">
      <i class="fas fa-music"></i>
      <div class="feature-title">Entertainment</div>
      <div class="feature-subtitle">From stage to screen — never a dull moment</div>
    </div>
  </div>

  <!-- Center text -->
  <div class="center-content">
    <div class="badge">IT Department of The KET's V G Vaze College</div>
    <h1>Inventrix<br>Life with Technology</h1>
    <p>Get ready to experience innovation, creativity, and fun like never before! Inventrix isn't just a fest - it's a celebration of talent, technology, and teamwork.</p>
  </div>

  <!-- Right features -->
  <div class="features-col">
    <div class="feature">
      <i class="fas fa-fire"></i>
      <div class="feature-title">High Spirit</div>
      <div class="feature-subtitle">Energy that never fades</div>
    </div>
    <div class="feature">
      <i class="fas fa-laugh-beam"></i>
      <div class="feature-title">Fun</div>
      <div class="feature-subtitle">Laughter, games, and unforgettable memories</div>
    </div>
  </div>
</div>
<!-- scrolling left notice -->
<div class="scrollednotice-wrapper">
  <div class="scrollednotice-text">
   
    🚀 IT Department Presents Inventrix 2025 – Register Now! 
  </div>
</div></div>

<!-- boxwes -->
<div class="update-container">
  <div class="update-card">
    <h3>Coming Soon</h3>
    <p>Date: 01/12/2025</p>
  </div>
  <div class="update-card">
    <h3>IT Dept Presents</h3>
    <p>Annual Event</p>
    <h4>Inventrix</h4>
  </div>
  <div class="update-card">
    <h3>Registrations</h3>
    <p>Starts From...</p>
  </div>
</div>

  </main>
<footer class="site-footer">
    <div class="footer-content">
      <h1>Inventrix</h1>
      <div class="footer-section address">
      <h3>Contact Us</h3>
      <p>V. G. Vaze College, Mithagar Rd,<br> Mulund East, Mumbai, Maharashtra 400081 <br> maininventrix@gmail.com</p>
      <p>Contact Number: +91 9876543210</p>
      </div>
    </div>

      <div>
        <h3>Follow Us</h3>
        <a href="https://www.instagram.com/inventrix.makeithappen?igsh=MWJveW04bjJ1cWNxMA==" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="mailto:maininventrix@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>

<script>
  $(window).on("scroll", function () {
    $(".update-card").each(function () {
      const cardTop = $(this).offset().top;
      const scrollBottom = $(window).scrollTop() + $(window).height();

      if (scrollBottom > cardTop + 50) {
        $(this).addClass("visible");
      }
    });
  });
</script>
<script src="index.js"></script>
</body>
</html> 