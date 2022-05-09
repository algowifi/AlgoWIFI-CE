<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

/**
 * Create data to plot for publishers
 */

function printOutput($success, $params)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['metrics'] = $params;
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['metrics'] = null;
            echo json_encode($output);
        }
    }

if (!isset($_SESSION['user']))
{
    error_log("user is not authenticated, cannot get publisher graph data!", 0);
    printOutput(0, 'user is not authenticated, cannot get publisher graph data!');
    $conn->close();
    die();
}
else //user authenticated
{
    $userId = $_SESSION['user']['id'];//isset($_GET['uid']) ? $_GET['uid'] : -1;

    $sql = 'SELECT C.id, C.name FROM Campaign as C WHERE C.userId = '.$userId;

    //perform query 
    $campaignNames = [];
    $campaignViews = [];
    
    $result = $conn->query($sql);
    if ($result->num_rows > 0) 
    {
        $i = 0;
        while ($row = $result->fetch_assoc()) 
        {
            $sql2 = 'SELECT SUM(views) AS totViews FROM Hotspot_Campaign WHERE campaignId = '.$row['id'];
            $result2 = $conn->query($sql2);
            $row2 = $result2->fetch_assoc();
            array_push($campaignNames,$row['name']);
            $views = ($row2['totViews'] == null ? 0 : intval($row2['totViews']));
            array_push($campaignViews,$views);
        }
    } 
    $output['names'] = $campaignNames;
    $output['values'] = $campaignViews;
    printOutput(1,$output);

}//end else (user authenticated)


?>