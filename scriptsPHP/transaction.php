<?php
    session_start();
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');


     // allow execution to admin only
     if (!isset($_SESSION['user'])) 
     {
        error_log("user not authenticated, cannot perform transaction!");
        printOutput(0, 'user not authenticated, cannot perform transaction');
        $conn->close();
        die();
    } else if ($_SESSION['user']['isAdmin']) 
    {
        error_log("admin performing transaction");
    } else {
        error_log("user is not an admin, cannot perform transaction!");
        printOutput(0,"user is not an admin, cannot perform transaction!");
        $conn->close();
        die();
    }

    //1 get params 
    if (!isset($_POST['from']) || !isset($_POST['to']) 
    || !isset($_POST['assetId']) || !isset($_POST['amount']) )
    {
        error_log("missing params, cannot perform transaction!");
        printOutput(0,"missing params, cannot perform transaction!");
        $conn->close();
        die();
    }
    
    $fromAddress = validate($_POST['from']);
    $toAddress = validate($_POST['to']);
    $assetId = $_POST['assetId'];
    $amount = $_POST['amount'];
    $isAssetTransfer = ($assetId != 'Algo');

    error_log('Attempt to transfer '.$amount.' of '.$assetId.' from '.$fromAddress.' to '.$toAddress);

    // get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;

    //build transaction
    $transaction=array(
        "txn" => array(
                "fee" => 1000, //Fee
                "fv" => $lastRound, //First Valid
                "gen" => $genesisID, // GenesisID
                "gh" => $genesis, //Genesis Hash
                "lv" => ($lastRound+200), //Last Valid
                "note" => "", //Your note
                "snd" => $fromAddress, //Sender
                "type" => "pay", //Tx Type
                "rcv" => $toAddress, //Receiver
                "amt" => $amount, //Amount 1algo = 1000000 microAlgos
            ),
    );

    if ($isAssetTransfer)
    {
        $transaction=array(
            "txn" => array(
                    "type" => "axfer", //Tx Type
                    "arcv" => $toAddress, //AssetReceiver
                    "snd" => $fromAddress, //Sender
                    "fee" => 1000, //Fee
                    "fv" => $lastRound, //First Valid
                    "lv" => $lastRound+300, //Last Valid
                    "gh" => $genesis, //Genesis Hash
                    "xaid" => $assetId, //XferAsset ID
                    //"aamt" => $amount,
                ),
        );

        if ($amount > 0)
        {
            $transaction['txn']['aamt']=$amount;
        }
    }

    //sign transaction
    $params['params']=array(
        "transaction" => $algorand_kmd->txn_encode($transaction),
        "wallet_handle_token" => $wallet_handle_token,
        "wallet_password" => $mainWalletPw,
     );
    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn=base64_decode($r->signed_transaction);

    //broadcast transaction
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);

    //check transaction status
    $return_array=json_decode($return['response']);
    $transactionID=$return_array->{'txId'} ;
    if ($return['code'] == 200)
    {
        $successString = 'Transaction performed! sender:'.$fromAddress.' - reciver:'.$toAddress.' - txId: '.$transactionID.' amount: '.$amount;
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




    
    //functions
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


?>