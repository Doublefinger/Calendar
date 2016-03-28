<?php
session_start();
//if user has already signed in, redirect to the file page
if (isset($_SESSION['user_name'])) {
    header("Location: ../index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>

    <!--Bootstrap&&jQuery-->
    <link rel="stylesheet" href="../bootstrap-3.3.6-dist/css/bootstrap.min.css">
    <script src="../jquery/jquery-1.12.0.min.js"></script>
    <script src="../bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div>
    <!--Inspired by https://getbootstrap.com/examples/signin/-->
    <div class="container">
        <form class="form-signin" action="../php/validate.php" method="POST">
            <h2 class="form-signin-heading">Module 5</h2>
            <input type="text" name="register" value="0" hidden="hidden">
            <label for="username" class="sr-only">username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="username" required
                   autofocus>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password"
                   required>
            <br>
            <?php
            include 'util.php';
            $fail = GET('fail');
            if ($fail == 1) {
                //null username
                echo '<div class="alert alert-danger" role="alert">Please enter your username.</div>';
            } else if ($fail == 3) {
                //Invalid password
                echo '<div class="alert alert-danger" role="alert">Invalid password, the minimum length is 6.</div>';
            } else if ($fail == 4) {
                //Wrong password or invalid username
                echo '<div class="alert alert-danger" role="alert">Username and password does not match.</div>';
            }
            ?>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
            <button class="btn btn-lg btn-success btn-block" type="button"
                    onclick="self.location='../php/register.php'">Go To Register
            </button>
        </form>
    </div>
</div>
</body>
</html>