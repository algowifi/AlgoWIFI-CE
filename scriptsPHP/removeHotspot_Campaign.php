<?php
    session_start();
    include('dbConn.php');

    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user not authenticated, cannot remove a campaign_hotspot relation!", 0);
        printOutput(0);
        $conn->close();
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin removing campaign_hotspot relation");
    } else {
        error_log("user is not an admin, cannot remove a campaign_hotspot relation!", 0);
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
            $output['message'] = "hotspot relation removed successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error removing hotspot relation";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['hid']) || !isset($_POST['cid']))
    {
        error_log("missing params, cannot remove hotspot relation!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    
    $hid = $_POST['hid'];    
    $cid = $_POST['cid'];    


    


    //perform query
    $sql = "DELETE FROM Hotspot_Campaign WHERE hotspotId=".$hid." AND campaignId=".$cid;
    
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