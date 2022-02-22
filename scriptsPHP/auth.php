<?php     //Script invoked from a login form.

    session_start();
    include('dbConn.php');
    

    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }


    //1 get params 
    if (!isset($_POST['email']) || !isset($_POST['pass']))
    {
        //return error message
        header("Location: ../index.php?error=Missing params");
        die();
    }



    //2 validate params
    $email = validate($_POST['email']);
    $pass = validate($_POST['pass']);
    if (empty($email) || empty($pass)) 
    {
        header("Location: ../index.php?error=Missing fields");
        exit();
    }

    $pass = md5($pass);

    $sql = "SELECT * FROM User WHERE email='$email' AND password='$pass'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) 
    {
        $row = mysqli_fetch_assoc($result);

        if ($row['email'] === $email && $row['password'] === $pass) 
        {

            $_SESSION['user'] = $row;

            header("Location: ../home.php");

        }
        else
        {
            header("Location: ../index.php?error=Incorect User name or password");
            
        }

    }
    else 
    {
        header("Location: ../index.php?error=Incorect User name or password");
        error_log("err query: ".$sql);

    }

    exit();

?>