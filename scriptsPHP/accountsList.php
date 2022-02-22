<?php
session_start();
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

//allow execution to admin only
if (!isset($_SESSION['user']))
{
    error_log('user not authenticated. cannot print accounts list');
    $output['success'] = 0;
    $output['error'] = 'user not authenticated';
    echo json_encode($output);
}
else if ($_SESSION['user']['isAdmin'])
{
    //ok
}
else 
{
    error_log('user is not admin. cannot print accounts list');
    $output['success'] = 0;
    $output['error'] = 'not allowed';
    echo json_encode($output);

}

//Get every key who opted-in to AWIFI
$apiUrl = 'https://algoindexer.testnet.algoexplorerapi.io/v2/accounts?asset-id='.$algowifiAssetId;
$response = file_get_contents($apiUrl);
$response = json_decode($response);
$output['success'] = 1;
$output['response'] = $response;
echo json_encode($output, JSON_PRETTY_PRINT);


?>