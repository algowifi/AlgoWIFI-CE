<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    //allow execution to admin only 
    // and also tp publishers but only if campaign is not active
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot edit campaign!", 0);
            printOutput(0, 'Campaign not saved! User not allowed');
            $conn->close();
            die();
    } 
    else if ($_SESSION['user']['isAdmin']) 
    {
        error_log("admin editing campaign");
    } 
    else if ($_SESSION['user']['isPublisher'] && isset($_POST['newIsActive']) && $_POST['newIsActive'])
    {
        //allow also publishers but only if campaign is not active
        error_log("publisher editing campaign ".$_POST['cid']);
    }
    else 
    {
        error_log("user is not allowed to edit campaign!");
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

    function printOutput($success, $message)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = $message;
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = $message;
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['cid']) || !isset($_POST['newName']) 
    || !isset($_POST['newDesc']) || !isset($_POST['newLanding']) 
    || !isset($_POST['newIsActive']) || !isset($_POST['newImg']) )
    {
        error_log("missing params, cannot edit campaign!", 0);
        printOutput(0, 'Campaign not saved! Missing params');
        $conn->close();
        die();
    }

    $campaignId = $_POST['cid'];
    $newName = validate($_POST['newName']);
    $newDesc = validate($_POST['newDesc']);
    $newImg = validate($_POST['newImg']);
    $newLanding = validate($_POST['newLanding']);
    $newIsActive = $_POST['newIsActive'];

    if (empty($newName) || empty($newDesc) || empty($newImg) || empty($newLanding)) 
    {
        error_log("missing params, cannot edit campaign!", 0);
        printOutput(0, 'Campaign not saved! Missing params');
        $conn->close();
        exit();
    }

    //file upload
    if (isset($_FILES['newFile']))
    {
         // Upload Dir
        $dir = '../uploadZ';
    
        //Validate Image File
        if ( 0 < $_FILES['newFile']['error'] ) {
            error_log('Error: ' . $_FILES['newFile']['error'], 0);
            printOutput(0, 'Error: ' . $_FILES['newFile']['error']);
            $conn->close();
            die();
        }
    
        //check filesize 
        if ($_FILES['newFile']['size'] > 1000000) {
            error_log("Exceeded filesize limit.", 0);
            printOutput(0, 'Campaign not saved! Exceeded filesize limit.');
            $conn->close();
            die();
        }

        // create new directory with 744 permissions if it does not exist yet
        if ( !file_exists($dir) ) 
        {
            mkdir ($dir, 0744);
        }

        //create new file name
        $ext = pathinfo($_FILES['newFile']['name'], PATHINFO_EXTENSION);
        $newFileName = $campaignId.".".$ext;
        $newImageUrl = $dir."/".$newFileName;
        
        //try to move file
        $fileOK = move_uploaded_file($_FILES['newFile']['tmp_name'], $newImageUrl);
        if (!$fileOK)
        {
            error_log("Fail to move new image for campaign!");
            printOutput(0, 'Campaign not saved! File upload Failed!');
            $conn->close();
            die();
        }
        else 
        {
            $newImg = substr($newImageUrl, 1);
            error_log("file Moved successfully ".$_FILES['newFile']['name']." in ".$newImageUrl);
        }

    }
    //end file upload

   
    


    //perform query
    $sql = "UPDATE Campaign SET name='".$newName."', description='".$newDesc."', imageUrl='".$newImg."', landingUrl='".$newLanding."', isActive=".$newIsActive." WHERE id=".$campaignId;
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        printOutput(1,"Campaign saved successfully!");
    } else 
    {
        printOutput(0,'Error saving campaign!');
        error_log("Error: " . $sql . " " . $conn->error);
    }
    
    $conn->close();
    

?>