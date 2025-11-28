<?php
session_start();
include "db.php"; // <-- make sure you already created db.php with DB connection

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = trim($_POST["userid"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT password FROM users WHERE userid = ?");
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION["userid"] = $userid;
             if ($userid === "ADMIN12") {
                header("Location: admin.php");
                exit();
            } else {
                header("Location: index.php"); // default page
                exit();
            }

        }
         
        else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Inventrix</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
      <label for="userid">College ID</label>
      <input type="text" id="userid" name="userid" required pattern="[A-Z0-9]{7}">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Log In</button>
    </form>
    <p class="message">Not registered yet? <a href="signup.php">Sign up</a></p>
  </div>
</body>
</html>
