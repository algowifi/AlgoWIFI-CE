<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    //allow execution to publishers only 
    if (!isset($_SESSION['user'])) 
    {
            error_log("user is not authenticated, cannot disable campaign!", 0);
            printOutput(0);
            $conn->close();
            die();
    } 
    else if ($_SESSION['user']['isPublisher']) 
    {
        error_log("publisher disabling campaign");
    } 
    else 
    {
        error_log("user is not an admin, cannot disable campaign!", 0);
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
            $output['message'] = "Campaign disabled successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error disabling campaign ";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['cid']) || !isset($_POST['uid']))
    {
        error_log("missing params, cannot disable campaign!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    
    $campaignId = $_POST['cid'];
    $ownerId = $_POST['uid'];

    //check user is the campaign owner
    if ($_SESSION['user']['id'] != $ownerId)
    {
        error_log("publisher not allowed to disable other publisher's campaign!", 0);
        printOutput(0);
        $conn->close();
        exit();
    }

    //perform query
    $sql = "UPDATE Campaign SET isActive=false WHERE id=".$campaignId;
    
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