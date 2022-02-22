<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution only to admin and hotspotter

// if (!isset($_SESSION['user'])) 
// {
    // error_log("user is not an admin, cannot print hotspots list!", 0);
//     $conn->close();
//     die();
// } 
// else if ($_SESSION['user']['isAdmin']) 
// {
//     error_log("admin printing users list");
// } 
// else 
// {
//     error_log("user is not an admin, cannot print hotspots list!", 0);
//     $conn->close();
//     die();
// }
//allow execution to authenticated users only, specifically only to admin and hotspotters
//if isAdmin, load all hotspots
//else if isHotspotter, load only own hotspots
//else die();
if (!isset($_SESSION['user'])) 
{
    error_log("user is not an admin, cannot print hotspots list!", 0);
    $conn->close();
    die();
} 
else if ($_SESSION['user']['isAdmin']) 
{
    error_log("admin printing hotspots list");
}
else if ($_SESSION['user']['isHotspotter']) 
{
    error_log("publisher printing own hotspots list");
} 
else 
{
    error_log("user is not allowed to print campaign list!", 0);
    $conn->close();
    die();
}

$hotspots = [];
//perform query 
$result = $conn->query("SELECT * FROM Hotspot");
if ($result->num_rows > 0) 
{
    $i = 0;
    while ($row = $result->fetch_assoc()) 
    {
         //Get owner name from nft:
         $response = file_get_contents($algoExplorerAssetsApiPrefix.$row['nft'].'/balances?currency-greater-than=0');

         

        $response = json_decode($response);
        $nftOwnerAddress = $response->{'balances'}[0]->{'address'};
        //get owner name from mysql
        $query2 = "SELECT name FROM User WHERE algorandAddress = '".$nftOwnerAddress."'";
        $nameResult = $conn->query($query2);
        if ($nameResult->num_rows == 1) 
		{
			$row['ownerName'] = $nameResult->fetch_assoc()['name'];
        }
        else 
        {
            $row['ownerName'] = 'unknown';
        }
        if ($_SESSION['user']['isHotspotter']) 
        {
            if ($_SESSION['user']['algorandAddress'] == $nftOwnerAddress)
                $hotspots[$i++] = $row;
        }
        else
        {
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
    error_log("hotspots list printed", 0);
}

$conn->close();


