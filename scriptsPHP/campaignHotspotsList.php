<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution only to admin and publishers
if (!isset($_SESSION['user'])) 
{
    error_log("user is not authenticated, cannot print a campaign hotspots list!", 0);
    $conn->close();
    die();
} 
else if ($_SESSION['user']['isAdmin']) 
{
    error_log("admin printing campaign hotspots list");
}
else if ($_SESSION['user']['isPublisher']) 
{
    error_log("publisher printing own campaign hotspots list");
} 
else 
{
    error_log("user is not allowed to print campaign hotspot list!", 0);
    $conn->close();
    die();
}
//validate params 
if (!isset($_GET['cid'])) 
{
    error_log("missing params. cannot print campaign hotspot list!", 0);
    $conn->close();
    die();
}
$cid = $_GET['cid'];


$sql = 'SELECT H.id, H.nft as nft, H.location as location, HC.views as totViews FROM Hotspot as H, Hotspot_Campaign as HC WHERE HC.campaignId = '.$cid.' AND H.id = HC.hotspotId ORDER BY H.location;';
$result = $conn->query($sql);
$hotspots = [];
 if ($result->num_rows > 0) 
 {
     $i = 0;
     while ($row = $result->fetch_assoc()) 
     {
         $row['assetAmount'] = ($row['totViews'] * $platformFee)/10000 ;
         $hotspots[$i++] = $row;
     }
 } 


$jsonData['data'] = $hotspots;      
$json = json_encode($jsonData);
if ($json == false)
{
    echo("err json");
    error_log("err json", 0);
}
else 
{
    echo ($json);
    error_log("hotspots list printed", 0);
}

$conn->close();


