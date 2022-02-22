<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to owners only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot change own password!", 0);
        printOutput(0);
        $conn->close();
        die();  
    }
    else 
    {
        error_log("user changing own password");
    }
    
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function printOutput($success)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = "Password modified successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error changing password";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_SESSION['user']['id']) || !isset($_POST['p']))
    {
        error_log("missing params, cannot change password!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    $userID = $_SESSION['user']['id'];
    $newPassword = validate($_POST['p']);


    if (empty($newPassword)) 
    {
        error_log("Error empty password!", 0);
        printOutput(0);
        $conn->close();
        exit();
    }


    //perform query
    $sql = "UPDATE User SET password='".md5($newPassword)."' WHERE id=".$userID;
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        printOutput(1);
    } else 
    {
        printOutput(0);
        error_log("Error: " . $sql . " " . $conn->error);
    }
    
    $conn->close();
    

?>