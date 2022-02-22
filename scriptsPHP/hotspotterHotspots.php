<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution only to admin and hotspotters
if (!isset($_SESSION['user'])) 
{
    error_log("user is not authenticated, cannot print a hotspotter hotspots list!", 0);
    echo("user is not authenticated, cannot print a hotspotter hotspots list!");
    $conn->close();
    die();
} 
else if ($_SESSION['user']['isAdmin']) 
{
    error_log("admin printing hotspotter hotspots list");
}
else if ($_SESSION['user']['isHotspotter']) 
{
    error_log("hotspotter printing own hotspots list");
} 
else 
{
    error_log("user is not allowed to print hotspotter hotspot list!");
    echo("user is not allowed to print hotspotter hotspot list!");
    $conn->close();
    die();
}

//validate params 
if (!isset($_GET['a'])) 
{
    error_log("missing params. cannot print hotspotter hotspot list!", 0);
    $conn->close();
    die();
}
$hotspotterAlgorandAddress = $_GET['a'];

//get every hotspot's nft of this user from algorand
$hotspots = [];
$i = 0;
$return=$algorand->get("v1","account",$hotspotterAlgorandAddress);
$return_array=json_decode($return['response']);
$assets=$return_array->{'assets'};
foreach ($assets as $nft => $v)
{
    if ($nft != $algowifiAssetId && $v->{'amount'} == 1)
    {
        //get hotspot data from database
        $sql = "SELECT H.*, SUM(HC.views) as totViews FROM Hotspot as H, Hotspot_Campaign as HC WHERE H.id = HC.hotspotId AND H.nft = ".$nft." GROUP BY H.id;";
        $result = $conn->query($sql);
        if ($result->num_rows == 1)
        {
            $row = $result->fetch_assoc();
            $row['assetAmount'] = ($row['totViews'] * $hotspotFee)/10000 ;
            $hotspots[$i++] = $row;
        }
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
    error_log("hotspotter hotspots list printed", 0);
}

$conn->close();


?>