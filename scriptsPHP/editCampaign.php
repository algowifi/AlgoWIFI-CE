<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    //allow execution to admin only 
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not authenticated, cannot edit campaign!", 0);
            printOutput(0);
            $conn->close();
            die();
    } 
    else if ($_SESSION['user']['isAdmin']) 
    {
        error_log("admin editing campaign");
    } 
    else 
    {
        error_log("user is not an admin, cannot edit campaign!", 0);
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
            $output['message'] = "Campaign edited successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error editing campaign";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['cid']) || !isset($_POST['newName']) 
    || !isset($_POST['newDesc']) || !isset($_POST['newImg']) 
    || !isset($_POST['newLanding']) || !isset($_POST['newIsActive']))
    {
        error_log("missing params, cannot edit campaign!", 0);
        printOutput(0);
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
        printOutput(0);
        $conn->close();
        exit();
    }
    


    //perform query
    $sql = "UPDATE Campaign SET name='".$newName."', description='".$newDesc."', imageUrl='".$newImg."', landingUrl='".$newLanding."', isActive=".$newIsActive." WHERE id=".$campaignId;
    
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