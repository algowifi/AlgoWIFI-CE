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

    //Transaction 1 //opt-in to asset
    $transactions=array();
    $transactions[]=array(
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
    //Transaction 2 //asset transfer
    $transactions[]=array(
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


    //2) Group TRansactions
    $groupid=$algorand_kmd->groupid($transactions);
    #Assigns Group ID
    $transactions[0]['txn']['grp']=$groupid;
    $transactions[1]['txn']['grp']=$groupid;

    //3) Sign Transactions
    #Sign Transaction 1
    $txn="";
    $clearTxn="";
    $params['params']=array(
    //"public_key" => $algorand_kmd->pk_encode($mainAccountAddress),
    "transaction" => $algorand_kmd->txn_encode($transactions[0]),
    "wallet_handle_token" => $wallet_handle_token,
    "wallet_password" => $mainWalletPw,
    );


    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn.=base64_decode($r->signed_transaction);
    $clearTxn.=$r->signed_transaction;

    #Sign Transaction 2
    $params['params']=array(
    //"public_key" => $algorand_kmd->pk_encode($mainAccountAddress),
    "transaction" => $algorand_kmd->txn_encode($transactions[1]),
    "wallet_handle_token" => $wallet_handle_token,
    "wallet_password" => $mainWalletPw,
    );

    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn.=base64_decode($r->signed_transaction);
    $clearTxn.=$r->signed_transaction;

    //4) Send Transaction Group
    #Broadcasts a raw atomic transaction to the network.
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);
    $txId=$return['response']->txId;

    //5) check transaction group status
    $return_array=json_decode($return['response']);
    $transactionID=$return_array->{'txId'} ;
    if ($return['code'] == 200)
    {
        $successString = 'Asset transfer succeded! txId: '.$transactionID;
        printOutput(1, $successString);
        error_log($successString);

        //Try opt-out after atomic transfer
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

    }
    else
    {
        $s = print_r($return, true);
        $failString = 'Transaction Failed! '.$s;
        printOutput(0, $failString);
        error_log($failString);
    }

















    

    //***** END ALGORAND PROCEDURES *****
    
    

?>