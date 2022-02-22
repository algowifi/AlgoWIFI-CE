<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    //allow execution authenticate users only
    if (!isset($_SESSION['user'])) 
    {
        error_log("Error: auth required", 0);
        printOutput(0, 'Error: auth required');
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
    if (!isset($_GET['nftId']))
    {
        error_log("missing params", 0);
        printOutput(0, "Error getting hotspot Info: missing params.");
        $conn->close();
        die();
    }
    $nftId = validate($_GET['nftId']);

    if (empty($nftId)) 
    {
        error_log("missing params!", 0);
        printOutput(0, "Error getting hotspot Info: missing params.");
        $conn->close();
        exit();
    }

    $response = file_get_contents($algoExplorerAssetsApiPrefix.$nftId.'/balances?currency-greater-than=0');
    $response = json_decode($response);
    $nftOwnerAddress = $response->{'balances'}[0]->{'address'};

    $response = file_get_contents($algoExplorerAssetsApiPrefix.$nftId);
    $response = json_decode($response);
    $nftParams = $response->{'asset'}->{'params'};
    
    $nftParams->{'owner'} = $nftOwnerAddress;

    //get owner name from mysql
    $query2 = "SELECT name FROM User WHERE algorandAddress = '".$nftOwnerAddress."'";
    $nameResult = $conn->query($query2);
    if ($nameResult->num_rows == 1) 
    {
        $nftParams->{'ownerName'} = $nameResult->fetch_assoc()['name'];
    }
    else 
    {
        $nftParams->{'ownerName'} = 'unknown';
    }

    echo json_encode($nftParams);



 
    
    $conn->close();
    

?>