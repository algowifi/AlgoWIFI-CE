<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

  //allow execution to admin only 
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot enable|disable campaign!", 0);
            printOutput(0, 'Campaign not saved! User not allowed');
            $conn->close();
            die();
    } 
    else if ($_SESSION['user']['isAdmin']) 
    {
        error_log("admin is enabling|disabling campaign");
    } 
    else 
    {
        error_log("user is not allowed to enable|disable campaign!");
        printOutput(0, 'Campaign not saved! User not allowed');
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
        global $newValue;

        if ($success)
        {
            $status = ($newValue === "true") ? "enabled" : "disabled";
            $output['success'] = 1;
            $output['message'] = "Campaign ".$status." successfully";
            echo json_encode($output);
        }
        else
        {
            $status = ($newValue === "true") ? "enabling" : "disabling";
            $output['success'] = 0;
            $output['message'] = "Error ".$status." campaign ";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['cid']) || !isset($_POST['newValue']))
    {
        error_log("missing params, cannot enable|disable campaign!");
        printOutput(0);
        $conn->close();
        die();
    }
    
    $campaignId = $_POST['cid'];
    $newValue = $_POST['newValue'];


    //perform query
    $sql = "UPDATE Campaign SET isActive=".$newValue." WHERE id=".$campaignId;
    
    error_log("performing query ".$sql);

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