<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution only to admin

if (!isset($_SESSION['user'])) 
{
    error_log("user is not an admin, cannot print user list!", 0);
    $conn->close();
    die();
} 
else if ($_SESSION['user']['isAdmin']) 
{
    error_log("admin printing users list");
} 
else 
{
    error_log("user is not an admin, cannot print user list!", 0);
    $conn->close();
    die();
}

//perform query 
$result = $conn->query("SELECT * FROM User");
if ($result->num_rows > 0) 
{
    $i = 0;
    while ($row = $result->fetch_assoc()) 
    {

        //start algorand routine to add algowifi balance to every user
        //get user algorand address
        $userAlgorandAddress = $row['algorandAddress'];
        //Get asset amount
        $return=$algorand->get("v1","account",$userAlgorandAddress);
        $return_array=json_decode($return['response']);
        $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'};
        $algoAmount = $return_array->{'amount'};
        //add asset amount to user
        $row['assetAmount'] = $algoWifiAmount/10000;
        $row['algoAmount'] = $algoAmount/1000000;
        //end algorand routine to add algowifi balance to every user


        //add user type (admin, location, publisher, hotspotter)
        $status = "";
        if ($row['isAdmin'])
        {
            $status = 'Admin ';
        }
        if ($row['isLocation'])
        {
            $status .= 'Location';
        }
        else if ($row['isPublisher'])
        {
            $status .= 'Publisher';
        }
        else if ($row['isHotspotter'])
        {
            $status .= 'Hotspotter';
        }
        $row['type'] = $status;

        //if ($row["isAdmin"] == 0) {
            $users[$i++] = $row;
        //}
        //echo('ok'.$row['name'].' ');
    }
} 


$jsonData['data'] = $users;      
$json = json_encode($jsonData);
if ($json == false)
{
    echo("err json");
    error_log("err json", 0);
}
else 
{
    echo ($json);
    error_log("users list printed", 0);
}

$conn->close();


