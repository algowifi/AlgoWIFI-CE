<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
include('./apiConfig.php');

header('Content-Type: application/json; charset=utf-8');

// allow execution to admin only
if (!isset($_SESSION['user'])) {
    error_log("user not authenticated, cannot remove other users!", 0);
    printOutput(0);
    $conn->close();
    die();
} else if ($_SESSION['user']['isAdmin']) {
    error_log("admin removing user");
} else {
    error_log("user is not an admin, cannot remove other users!", 0);
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
    if ($success) {
        $output['success'] = 1;
        $output['message'] = "User removed successfully";
        echo json_encode($output);
    } else {
        $output['success'] = 0;
        $output['message'] = "Error removing user";
        echo json_encode($output);
    }
}

function getNFTOwnership($nftId, $from)
{
    global $mainAccountAddress;
    global $changeNftOwnerApi;

    // set post fields
    $post = [
        'from' => $from,
        'to' => $mainAccountAddress,
        'nftId'   => $nftId,
    ];

    $ch = curl_init($changeNftOwnerApi);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch,CURLOPT_POST, 1);           

    $response = curl_exec($ch);

    curl_close($ch);
    
    error_log("Response from changeNftOwner api: ".$response);
    return $response;
}

function getBalanceBack($from, $assetID, $amount)
{
    global $mainAccountAddress;
    global $transactionApi;

    // set post fields
    $post = [
        'from' => $from,
        'to' => $mainAccountAddress,
        'assetId'   => $assetID,
        'amount'   => $amount,
    ];

    $ch = curl_init($transactionApi);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch,CURLOPT_POST, 1);           

    $response = curl_exec($ch);

    curl_close($ch);

    error_log("Response from transaction api: ".$response);
    return $response;
}


//1 get params 
if (!isset($_POST['userID'])) {
    error_log("missing params, cannot remove user!", 0);
    printOutput(0);
    $conn->close();
    die();
}

$userID = $_POST['userID'];

//2 get type from user to remove
$sql = "SELECT isHotspotter, isPublisher, algorandAddress FROM `User` where id = " . $userID;
error_log("performing query " . $sql);
$result = $conn->query($sql);

if ($result->num_rows != 1) 
{
    error_log("user not found, cannot remove user!");
    printOutput(0);
    $conn->close();
    die();
}

$row = $result->fetch_assoc();
$isHotspotter = $row['isHotspotter'];
$isPublisher = $row['isPublisher'];
$userToRemoveAlgorandAddress = $row['algorandAddress'];


$return = $algorand->get("v1", "account", $userToRemoveAlgorandAddress);
$return_array = json_decode($return['response']);


//3 if isHotspotter change ownership of every hotspot to platform
if ($isHotspotter == 1) 
{
    //3.1 get every hotspot's nft of this user from algorand
    $hotspots = [];
    $i = 0;
    $assets = $return_array->{'assets'};
    foreach ($assets as $nft => $v) {
        if ($nft != $algowifiAssetId && $v->{'amount'} == 1) 
        {
            //3.2 get ownership of nft
            getNFTOwnership($nft,$userToRemoveAlgorandAddress);
        }
    }
}

//4 empty algo and algoWifi balances
$algoWifiAmount = $return_array->{'assets'}->{$algowifiAssetId}->{'amount'};
getBalanceBack($userToRemoveAlgorandAddress,$algowifiAssetId, $algoWifiAmount);

$return = $algorand->get("v1", "account", $userToRemoveAlgorandAddress);
$return_array = json_decode($return['response']);
$algoAmount = $return_array->{'amount'};
getBalanceBack($userToRemoveAlgorandAddress,"Algo", $algoAmount-1000 - 200000); //- 1000 microalgos for the transaction fee, -200000 is minimal account threshold with one asset


//5 finally remove user from db

//perform query
$sql = "DELETE FROM User WHERE id=" . $userID;

error_log("performing query " . $sql, 0);

if ($conn->query($sql) === TRUE) 
{
    printOutput(1);
} else {
    printOutput(0);
    error_log("Error: " . $sql . " " . $conn->error);
}

$conn->close();
