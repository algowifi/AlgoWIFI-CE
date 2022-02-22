<?php
    session_start();
    include('dbConn.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        //allow execution to owner users 
        if ($_SESSION['user']['id'] == $_POST['userID'])
        {
            error_log("user is editing his own profile!", 0);
        }
        else 
        {
            error_log("user is not an admin, cannot edit other profiles!", 0);
            printOutput(0);
            $conn->close();
            die();
        }
       
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin editing user");
    } else {
        error_log("user is not an admin, cannot edit other profiles!", 0);
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
            $output['message'] = "User edited successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error editing user";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['userID']) || !isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['note']) || !isset($_POST['address']) || !isset($_POST['algorandAddress']) || !isset($_POST['nft']) || !isset($_POST['password']) || !isset($_POST['isEnabled']) || !isset($_POST['isAdmin']))
    {
        error_log("missing params, cannot edit user!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    $userID = $_POST['userID'];
    $newName = validate($_POST['name']);
    $newEmail = validate($_POST['email']);
    $newNote = validate($_POST['note']);
    $newAddress = validate($_POST['address']);
    $newAlgorandAddress = validate($_POST['algorandAddress']);
    $newNft = validate($_POST['nft']);
    $newPassword = validate($_POST['password']);
    $newIsEnabled = $_POST['isEnabled'];
    $newIsAdmin = $_POST['isAdmin'];

    error_log("".$newIsEnabled." ".$newIsAdmin,0);

    if (empty($newName) || empty($newEmail) || empty($newNote) || empty($newAddress) || empty($newAlgorandAddress) || empty($newNft) || empty($newPassword)) 
    {
        error_log("missing params, cannot edit user!", 0);
        printOutput(0);
        $conn->close();
        exit();
    }
    



    //perform query
    $sql = "UPDATE User SET name='".$newName."', email='".$newEmail."', note='".$newNote."', address='".$newAddress."', algorandAddress='".$newAlgorandAddress."', nft='".$newNft."', password='".md5($newPassword)."', isEnabled=".$newIsEnabled.", isAdmin=".$newIsAdmin." WHERE id=".$userID;
    
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