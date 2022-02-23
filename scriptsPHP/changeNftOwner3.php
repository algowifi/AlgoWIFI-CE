<?php
    session_start();
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) {
        error_log("user is not an admin, cannot add change hotspot ownership!", 0);
        printOutput(0, 'user is not an admin, cannot add change hotspot ownership!');
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin adding new hotspot");
    } else {
        error_log("user is not an admin, cannot add change hotspot ownership!", 0);
        printOutput(0, 'user is not an admin, cannot add change hotspot ownership!');
        die();
    }
    
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function printOutput($success, $msg)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = $msg;
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = $msg;
            echo json_encode($output);
        }
    }


    //1) get params 
    if (!isset($_POST['from']) || !isset($_POST['to']) 
    || !isset($_POST['nftId']) )
    {
        error_log("missing params, cannot change hotspot ownership!", 0);
        printOutput(0, "missing params, cannot change hotspot ownership!");
        die();
    }
    $from = validate($_POST['from']);
    $to = validate($_POST['to']);
    $nft = validate($_POST['nftId']);

    if (empty($from) || empty($to) || empty($nft)) 
    {
        error_log("missing params, cannot change hotspot ownership!", 0);
        printOutput(0, "missing params, cannot change hotspot ownership!");
        exit();
    }

    //***** START ALGORAND PROCEDURES *****

    //1) get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;

     //2) opt-in to an asset
     $transaction=array(
        "txn" => array(
                "type" => "axfer", //Tx Type
                "arcv" => $to, //AssetReceiver
                "snd" => $to, //Sender
                "fee" => 1000, //Fee
                "fv" => $lastRound, //First Valid
                "lv" => $lastRound+300, //Last Valid
                "gh" => $genesis, //Genesis Hash
                "xaid" => $nft, //XferAsset ID
            ),
    );

    //2.1) sign transaction
    $params['params']=array(
        "transaction" => $algorand_kmd->txn_encode($transaction),
        "wallet_handle_token" => $wallet_handle_token,
        "wallet_password" => $mainWalletPw,
     );
    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn=base64_decode($r->signed_transaction);
    
    //2.2) broadcast transaction
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);


    //2.3 check if first transaction succeded   
    $return_array=json_decode($return['response']);
    $transactionID=$return_array->{'txId'} ;
    if ($return['code'] == 200)
    {
        //3) asset transfer 
        $transaction=array(
            "txn" => array(
                    "type" => "axfer", //Tx Type
                    "arcv" => $to, //AssetReceiver
                    "snd" => $from, //Sender
                    "fee" => 1000, //Fee
                    "fv" => $lastRound, //First Valid
                    "lv" => $lastRound+300, //Last Valid
                    "gh" => $genesis, //Genesis Hash
                    "xaid" => $nft, //XferAsset ID
                    "aamt" => 1,
                ),
        );

        //3.1) sign transaction
        $params['params']=array(
            "transaction" => $algorand_kmd->txn_encode($transaction),
            "wallet_handle_token" => $wallet_handle_token,
            "wallet_password" => $mainWalletPw,
        );
        $return=$algorand_kmd->post("v1","transaction","sign",$params);
        $r=json_decode($return['response']);
        $txn=base64_decode($r->signed_transaction);
        //print_r($return);

        //3.2) broadcast transaction
        $params['transaction']=$txn;
        $return=$algorand->post("v2","transactions",$params);

        //3.3) check if second transaction succeded
        if ($return['code'] == 200)
        {

            //4. Opt-out
            $transaction=array(
                "txn" => array(
                        "type" => "axfer", //Tx Type
                        "arcv" => $to, //AssetReceiver
                        "snd" => $from, //Sender
                        "fee" => 1000, //Fee
                        "fv" => $lastRound, //First Valid
                        "lv" => $lastRound+300, //Last Valid
                        "gh" => $genesis, //Genesis Hash
                        "xaid" => $nft, //XferAsset ID
                        "aclose" => $from,
                    ),
            );

            //4.1) sign transaction
            $params['params']=array(
                "transaction" => $algorand_kmd->txn_encode($transaction),
                "wallet_handle_token" => $wallet_handle_token,
                "wallet_password" => $mainWalletPw,
            );
            $return=$algorand_kmd->post("v1","transaction","sign",$params);
            $r=json_decode($return['response']);
            $txn=base64_decode($r->signed_transaction);
            
            //4.2) broadcast transaction
            $params['transaction']=$txn;
            $return=$algorand->post("v2","transactions",$params);

            //4.4 check if opt-out transaction succeded   
            if ($return['code'] == 200)
            {
                printOutput(1, 'Asset transfer succeded!');
            }
            else 
            {
                printOutput(1, 'Asset transfer succeded! But Opt-out failed!');
            }

        }
        else 
        {
            printOutput(0, 'Error Transferring asset: '.$return['message']);
        }
    }
    else 
    {
        printOutput(0, 'Error Opt-In: '.$return['message']);
    }


    

    //***** END ALGORAND PROCEDURES *****
    
    

?>