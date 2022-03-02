<?php
    session_start();
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) {
        error_log("user is not an admin, cannot generate new key!");
        printOutput(0);
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin generating new key");
    } else {
        error_log("user is not an admin, cannot generate new keys!", 0);
        printOutput(0);
        die();
    }
    
    function printOutput($success, $newKey = "")
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = "New key ".$newKey." created!";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error generating new key";
            echo json_encode($output);
        }
    }

    //1) get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;

    //2) Generate a key and get new address
    $params['params']=array(
        "display_mnemonic" => false,
        "wallet_handle_token" => $wallet_handle_token
    );
    $return=$algorand_kmd->post("v1","key",$params);
    $return_array=json_decode($return['response']);
    $newGeneratedAddress=$return_array->{'address'};
    
    if ($newGeneratedAddress != "")
        printOutput(1,$newGeneratedAddress);
    else 
        printOutput(0);

?>