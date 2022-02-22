<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to publishers only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot add campaigns!", 0);
        printOutput(0, 'user not authenticated');
        $conn->close();
        die();
       
    } 
    else if (!$_SESSION['user']['isPublisher']) 
    {
        error_log("user is not a publisher, cannot add campaigns!", 0);
        printOutput(0, 'user not a publisher');
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

    // Upload Dir
     $dir = '../uploadZ';
    

    //Validate Image File
    if ( 0 < $_FILES['file']['error'] ) {
        error_log('Error: ' . $_FILES['file']['error'], 0);
        printOutput(0, 'Error: ' . $_FILES['file']['error']);
        $conn->close();
        die();
    }

    //check filesize 
    if ($_FILES['file']['size'] > 1000000) {
        error_log("Exceeded filesize limit.", 0);
        printOutput(0, 'Exceeded filesize limit.');
        $conn->close();
        die();
    }

    //Validate Fields
     if (!isset($_POST['newName']) || !isset($_POST['newDescription']) 
     || !isset($_POST['newLandingUrl']) || !isset($_POST['newHotspots']) )
     {
         error_log("missing params, cannot add campaign!", 0);
         printOutput(0, 'missing params');
         $conn->close();
         die();
     }

    $userId = $_SESSION['user']['id'];
    $newName = validate($_POST['newName']);
    $newDescription = validate($_POST['newDescription']);
    $newLandingUrl = validate($_POST['newLandingUrl']);
    $newHotspots = validate($_POST['newHotspots']);


    if (empty($newName) || empty($newDescription) || empty($newLandingUrl) || empty($newHotspots) ) 
    {
        error_log("missing params, cannot add campaign!", 0);
        printOutput(0,'missing params');
        $conn->close();
        die();
    }

    $newHotspots = explode(",", $newHotspots);

    // create new directory with 744 permissions if it does not exist yet
    if ( !file_exists($dir) ) 
    {
        mkdir ($dir, 0744);
        // if (mkdir ($dir, 0744))
        //     echo 'dir created ';
        // else 
        //     echo 'error creating dir ';
    }
    
        
        //insert in database
        $sql = "INSERT INTO Campaign (userId, name, description, imageUrl, landingUrl) VALUES ($userId, '".$newName."', '".$newDescription."', '".substr($newImageUrl, 1)."', '".$newLandingUrl."')";
        
        error_log("performing query ".$sql, 0);
    
        if ($conn->query($sql) === TRUE) 
        {
            $newCampaignId = $conn->insert_id;
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $newFileName = $newCampaignId.".".$ext;
            $newImageUrl = $dir."/".$newFileName;

            //try to move file
            $fileOK = move_uploaded_file($_FILES['file']['tmp_name'], $newImageUrl);
            if (!$fileOK)
            {
                //remove last inserted record
                $sql = "DELETE FROM Campaign WHERE id=LAST_INSERT_ID()";
                error_log("performing query ".$sql, 0);
            
                if ($conn->query($sql) === TRUE) 
                {
                    error_log("campaign without file upload removed: " . $sql);
                    
                } else 
                {
                    error_log("Error: " . $sql . " " . $conn->error);
                }
                error_log("File not moved! ".$_FILES['file']['name'], 0);
                printOutput(0, "File upload error! ");
                $conn->close();
                die();
            }
            else 
            {
                error_log("file Moved successfully ".$_FILES['file']['name']." in ".$newImageUrl,0);

                //update image url
                $sql2 = 'UPDATE Campaign set imageUrl="'.substr($newImageUrl, 1).'" WHERE id = LAST_INSERT_ID()';
                if ($conn->query($sql2) === TRUE) 
                {
                    error_log("ImageUrl setted for campaign!");
                } else 
                {
                    error_log("Error: ".$sql2 ." ". $conn->error);
                }

                //Insert relations with hotspots
                foreach ($newHotspots as $hotId)
                {
                    $q = "INSERT INTO Hotspot_Campaign (campaignId, hotspotId) VALUES ($newCampaignId, ".$hotId.")";
                    if ($conn->query($q) === TRUE) 
                    {
                        error_log($q." performed");
                    }
                    else 
                    {
                        error_log($q." Failed");
                    }
                }

                printOutput(1,'Campaign saved successfully!');
            }        
    
        } 
        else 
        {
            printOutput(0, "Error: " . $sql . " " . $conn->error);
            error_log("Error: " . $sql . " " . $conn->error);
        }
        
        $conn->close();
        


        
    


?>