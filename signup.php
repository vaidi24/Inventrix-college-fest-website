<?php
session_start();
include "db.php"; // <-- your DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $userid   = trim($_POST["userid"]);
    $mobile   = trim($_POST["mobile"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];
 



    // 1. Check if passwords match
    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        // 2. Check if userid already exists
        $stmt = $conn->prepare("SELECT userid FROM users WHERE userid = ?");
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "College ID already registered!";
        } else {
            // 3. Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, userid, mobile, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullname, $userid, $mobile, $email, $hash);
            
            if ($stmt->execute()) {
    header("Location: login.php?signup=success");
    exit();
} else {
    $error = "Error during signup. Try again.";
}

        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Inventrix</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <h2>Sign Up</h2>
    <?php 
      if (!empty($error)) echo "<p style='color:red;'>$error</p>"; 
      if (!empty($success)) echo "<p style='color:green;'>$success</p>"; 
    ?>
    <form method="POST">
      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" name="fullname" required>

      <label for="userid">College ID (Unique)</label>
      <input type="text" id="userid" name="userid" required pattern="[A-Z0-9]{7}">

      <label for="mobile">Mobile Number</label>
      <input type="tel" id="mobile" name="mobile" required pattern="[0-9]{10}">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="6">

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required>

      <button type="submit">Sign Up</button>
    </form>
    <p class="message">Already have an account? <a href="login.php">Log In</a></p>
  </div>
</body>
</html>
