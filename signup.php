<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statements to prevent SQL injection
        $stmt = $db->prepare("INSERT INTO `signup` (`username`, `email`, `password`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect to mainPage.php after successful registration
            header("Location: index.php");
            exit();
        } else {
            echo "<script type='text/javascript'> alert('Error registering user');</script>";
        }
        $stmt->close();
    } else {
        echo "<script type='text/javascript'> alert('Please Enter Valid Information');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up Form</title>
  <link rel="stylesheet" type="text/css" href="css/signup.css">
</head>
<body>
  <div class="wrapper">
    <div class="form-wrapper signUp">
      <form action="signup.php" method="POST">
        <h2>Sign Up</h2>
        <div class="input-group">
          <input type="text" name="username" required>
          <label>Username</label>
        </div>
        <div class="input-group">
          <input type="email" name="email" required>
          <label>Email</label>
        </div>
        <div class="input-group">
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <div class="remember">
          <label><input type="checkbox" required> I agree to the terms & conditions</label>
        </div>
        <button type="submit">Sign Up</button>
        <div class="signUp-link">
          <p>Already have an account? <a href="login.php" class="signInBtn-link">Sign In</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
