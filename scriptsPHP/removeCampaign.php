<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    //allow execution to publishers and admin only
    if (!isset($_SESSION['user'])) 
    {
            error_log("user is not authenticated, cannot remove campaign!", 0);
            printOutput(0);
            $conn->close();
            die();
    } 
    else if ($_SESSION['user']['isPublisher']) 
    {
        error_log("publisher removing campaign");
    } 
    else if ($_SESSION['user']['isAdmin']) 
    {
        error_log("admin removing campaign");
    }  
    else 
    {
        error_log("user not allowed to remove campaign!", 0);
        printOutput(0);
        $conn->close();
        die();
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
            $output['message'] = "Campaign removed successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error removing campaign ";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['cid']) || !isset($_POST['uid']))
    {
        error_log("missing params, cannot remove campaign!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    
    $campaignId = $_POST['cid'];
    $ownerId = $_POST['uid'];

    //check user is the campaign owner
    if ((!$_SESSION['user']['isAdmin']) && $_SESSION['user']['id'] != $ownerId)
    {
        error_log("publisher not allowed to remove other publisher's campaign!", 0);
        printOutput(0);
        $conn->close();
        exit();
    }

    //perform query
    $sql = "DELETE FROM Campaign WHERE id=".$campaignId;
    
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