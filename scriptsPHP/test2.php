<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');
    include('./algoConfig.php');

    header('Content-Type: application/json; charset=utf-8');


    //1) get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;


    //Test algo explorer indexer api 

    //test get owner from asset
    $response = file_get_contents('https://algoindexer.testnet.algoexplorerapi.io/v2/assets?asset-id=69442814');
    $response = json_decode($response);
    print_r($response->{'assets'}[0]->{'params'}->{'creator'});

        

    

    
?>