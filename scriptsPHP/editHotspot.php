<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not an admin, cannot edit hotspots!", 0);
        printOutput(0);
        $conn->close();
        die();
       
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin editing hotspot");
    } else {
        error_log("user is not an admin, cannot edit hotspots!", 0);
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


    //1 get params 
    if (!isset($_POST['hotspotID']) || !isset($_POST['location']) 
    || !isset($_POST['network']) || !isset($_POST['note']) || !isset($_POST['nft']) )
    {
        error_log("missing params, cannot edit hotspot!", 0);
        printOutput(0, 'missing params1 ');
        $conn->close();
        die();
    }
    $hsID = $_POST['hotspotID'];
    $newLocation = validate($_POST['location']);
    $newNetwork = validate($_POST['network']);
    $newNote = validate($_POST['note']);
    $newValidator = validate($_POST['validator']);
    $newNft = validate($_POST['nft']);

    if (empty($newLocation) || empty($newNetwork) 
    || empty($newNote) || empty($newNft)) 
    {
        error_log("missing params, cannot edit hotspot!", 0);
        printOutput(0, 'missing params');
        $conn->close();
        exit();
    }
    



    //perform query
    $sql = "UPDATE Hotspot SET location='".$newLocation."', networkName='".$newNetwork."', note='".$newNote."', validator='".$newValidator."', nft='".$newNft."' WHERE id=".$hsID;
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        printOutput(1, 'Hotspot saved!');
    } else 
    {
        printOutput(0, 'Error saving hotspot: '.$sql . " " . $conn->error);
        error_log("Error: " . $sql . " " . $conn->error);
    }
    
    $conn->close();
    

?>