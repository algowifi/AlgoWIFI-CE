<?php
    session_start();
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user is not an admin, cannot transfer awifi!", 0);
        printOutput(0, 'not admin');
        die();
       
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin transferring awifi");
    } else {
        error_log("user is not an admin, cannot transfer awifi!", 0);
        printOutput(0, 'not admin2');
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


    //1 get params 
    if (!isset($_POST['userID']) || !isset($_POST['amount']))
    {
        error_log("missing params, cannot perform transaction!", 0);
        printOutput(0, 'missing params');
        die();
    }
    $receiverID = $_POST['userID'];
    $amount = validate($_POST['amount']);

    error_log("receiverID: ".$receiverID." - amount: ".$amount,0);
    
    //perform transaction

    //0) Declare constants
    $senderID = $mainAccountAddress;

    //1) get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;

     //Transaction 1
     $transactions=array();
     $transactions[]=array(
        "txn" => array(
                "type" => "axfer", //Tx Type
                "arcv" => $receiverID, //AssetReceiver
                "snd" => $senderID, //Sender
                "fee" => 1000, //Fee
                "fv" => $lastRound, //First Valid
                "lv" => $lastRound+300, //Last Valid
                "gh" => $genesis, //Genesis Hash
                "xaid" => $algowifiAssetId, //XferAsset ID
                "aamt" => ($amount * 10000),
            ),
    );
     //Transaction 2
     $transactions[]=array(
        "txn" => array(
                "fee" => 1000, //Fee
                "fv" => $lastRound, //First Valid
                "gen" => $genesisID, // GenesisID
                "gh" => $genesis, //Genesis Hash
                "lv" => ($lastRound+200), //Last Valid
                "note" => "", //Your note
                "snd" => $senderID, //Sender
                "type" => "pay", //Tx Type
                "rcv" => $receiverID, //Receiver
                "amt" => ($amount * 100 * 1000), //Amount 1algo = 1000000 microAlgos
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
 
    
 
 
    //  echo "broadcast txts\n";
    //  print_r($return);
 
     //End Test Transactions Group
 










    // //asset transfer 
    // $transaction=array(
    //     "txn" => array(
    //             "type" => "axfer", //Tx Type
    //             "arcv" => $receiverID, //AssetReceiver
    //             "snd" => $senderID, //Sender
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound, //First Valid
    //             "lv" => $lastRound+300, //Last Valid
    //             "gh" => $genesis, //Genesis Hash
    //             "xaid" => $algowifiAssetId, //XferAsset ID
    //             "aamt" => ($amount * 10000),
    //         ),
    // );

    // //sign transaction
    // $params['params']=array(
    //     "transaction" => $algorand_kmd->txn_encode($transaction),
    //     "wallet_handle_token" => $wallet_handle_token,
    //     "wallet_password" => $mainWalletPw,
    //  );
    // $return=$algorand_kmd->post("v1","transaction","sign",$params);
    // $r=json_decode($return['response']);
    // $txn=base64_decode($r->signed_transaction);
    // //print_r($return);

    // //broadcast transaction
    // $params['transaction']=$txn;
    // $return=$algorand->post("v2","transactions",$params);

    // //3) make a transaction of 10% algos from main to new address
    // $transaction=array(
    //     "txn" => array(
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound+1, //First Valid
    //             "gen" => $genesisID, // GenesisID
    //             "gh" => $genesis, //Genesis Hash
    //             "lv" => ($lastRound+200), //Last Valid
    //             "note" => "", //Your note
    //             "snd" => $senderID, //Sender
    //             "type" => "pay", //Tx Type
    //             "rcv" => $receiverID, //Receiver
    //             "amt" => ($amount * 100 * 1000), //Amount 1algo = 1000000 microAlgos
    //         ),
    // );

    // $params['params']=array(
    //     "transaction" => $algorand_kmd->txn_encode($transaction),
    //     "wallet_handle_token" => $wallet_handle_token,
    //     "wallet_password" => $mainWalletPw,
    //  );
     
    //  //3.1) sign transaction
    //  $return=$algorand_kmd->post("v1","transaction","sign",$params);
    //  $r=json_decode($return['response']);
    //  $txn=base64_decode($r->signed_transaction);
     
    //  //3.2) broadcast transaction
    //  $params['transaction']=$txn;
    //  $return=$algorand->post("v2","transactions",$params);



    
    //check transaction status
    $return_array=json_decode($return['response']);
    $transactionID=$return_array->{'txId'} ;
    if ($return['code'] == 200)
    {
        $successString = 'Transaction performed! sender:'.$senderID.' - reciver:'.$receiverID.' - txId: '.$transactionID.' amount: '.$amount;
        printOutput(1, $successString);
        error_log($successString);
    }
    else
    {
        $s = print_r($return, true);
        $failString = 'Transaction Failed! '.$s;
        printOutput(0, $failString);
        error_log($failString);
    }


?>