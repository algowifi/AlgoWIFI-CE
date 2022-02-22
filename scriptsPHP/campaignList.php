<?php
session_start();
include('dbConn.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution to authenticated users only, specifically only to admin and publishers
//if isAdmin, load all campaigns | load a user campaigns 
//else if isPublisher, load only own campaigns
//else die();
if (!isset($_SESSION['user'])) 
{
    error_log("user is not authenticated, cannot print campaign list!", 0);
    $conn->close();
    die();
} 
else if ($_SESSION['user']['isAdmin']) 
{
    error_log("admin printing campaign list");
    $sql = 'SELECT * FROM Campaign';
    if (isset($_GET['uid']))
    {
        //a publisher who ask for the campaign list for a certain user
        $sql = 'SELECT * FROM Campaign WHERE userId = '.$_GET['uid'];

    }
}
else if ($_SESSION['user']['isPublisher']) 
{
    error_log("publisher printing own campaign list");
    $sql = 'SELECT * FROM Campaign WHERE userId = '.$_SESSION['user']['id'];
} 
else 
{
    error_log("user is not allowed to print campaign list!", 0);
    $conn->close();
    die();
}


//perform query 
$result = $conn->query($sql);
if ($result->num_rows > 0) 
{
    $i = 0;
    while ($row = $result->fetch_assoc()) 
    {
        $campaigns[$i++] = $row;
        //echo('ok'.$row['name'].' ');
    }
} 


$jsonData['data'] = $campaigns;      
$json = json_encode($jsonData);
if ($json == false)
{
    echo("err json");
    error_log("err json", 0);
}
else 
{
    echo ($json);
    error_log("campaign list printed", 0);
}

$conn->close();


