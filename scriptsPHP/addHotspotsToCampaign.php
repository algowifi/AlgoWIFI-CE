<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot add hotspots to campaigns!", 0);
        printOutput(0, 'user not authenticated');
        $conn->close();
        die();
    } 
    else if (!$_SESSION['user']['isAdmin']) 
    {
        error_log("user is not an admin, cannot add hotspots to campaigns!", 0);
        printOutput(0, 'not allowed');
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

    function printOutput($success, $msg)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = $msg;
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = $msg;
            echo json_encode($output);
        }
    }

    

    //Validate Fields
     if ( !isset($_POST['newHotspots']) || !isset($_POST['cid']))
     {
         error_log("missing params, cannot add hotspots to campaign!", 0);
         printOutput(0, 'missing params');
         $conn->close();
         die();
     }

     $newHotspots = validate($_POST['newHotspots']);
     $newCampaignId = validate($_POST['cid']);


    if ( empty($newHotspots) ) 
    {
        error_log("missing params, cannot add hotspots to campaign!", 0);
        printOutput(0,'missing params');
        $conn->close();
        die();
    }

    $newHotspots = explode(",", $newHotspots);
    
    $outputString = "";
    //Insert relations with hotspots
    foreach ($newHotspots as $hotId)
    {
        $q = "SELECT * FROM Hotspot_Campaign WHERE campaignId = ".$newCampaignId." AND hotspotId = ".$hotId;
        $result = $conn->query($q);
        if ($result->num_rows == 1)
        {
            error_log("Campaign ".$newCampaignId." is already published on hotspot ". $hotId);
            $outputString .= "This campaign is already published on hotspot ". $hotId."<br>";
        }
        else 
        {
            $q = "INSERT INTO Hotspot_Campaign (campaignId, hotspotId) VALUES ($newCampaignId, ".$hotId.")";
            if ($conn->query($q) === TRUE) 
            {
                error_log($q." performed");
                $outputString .= "Hotspot ".$hotId. " added!<br>";
            }
            else 
            {
                error_log($q." Failed");
                $outputString .= "FAIL to add Hotspot ".$hotId. "<br>";
            }
        } 
    }
        
    printOutput(1, $outputString);
    
    $conn->close();
