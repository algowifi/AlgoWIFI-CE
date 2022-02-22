<!DOCTYPE html>
<html lang="en">
<?php 
session_start(); 
if (isset($_SESSION['user']))
{
    header("Location: home.php");
    die();
}
?>
<head>
    <title>Autenticazione</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/login.css">
     <!--Bootstrap & jQuery-->
     <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script type="text/javascript" src="./js/login.js"></script>

</head>
<body>
    
  <div class="wrapper <?php if (!isset($_GET['message']) && !isset($_GET['error'])) echo "fadeInDown";?> ">
    <div id="formContent">  
    

      <!-- Icon -->
      <div class="fadeIn first">
        <img src="https://algowifi.com/images/algowifiLogo_PNG.png" id="icon" alt="AlgoWiFi" />
      </div>
  
      <!-- Login Form -->
      <form id="loginForm" method="post" action="./scriptsPHP/auth.php">
        <input type="email" id="email" class="fadeIn second" name="email" placeholder="Email" required>
        <input type="password" id="pass" class="fadeIn third" name="pass" placeholder="Password" required>
        <input type="submit" id="submit" class="fadeIn fourth" value="Login">
        <div id="liveAlertPlaceholder"></div>
      </form>
  
      <!-- Footer -->
      <div id="formFooter">
        <a class="underlineHover" href="#" onclick="forgotPw();">Forgot password?</a>
        
      </div>
    </div>
  </div>
    
   

</body>
</html>