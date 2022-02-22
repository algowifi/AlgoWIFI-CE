<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');

    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) 
    {
        error_log("user not authenticated, cannot remove other users!", 0);
        printOutput(0);
        $conn->close();
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin removing user");
    } else {
        error_log("user is not an admin, cannot remove other users!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function printOutput($success)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = "User removed successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error removing user";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['userID']))
    {
        error_log("missing params, cannot remove user!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    
    $userID = $_POST['userID'];    


    //  //***** START ALGORAND PROCEDURES *****

    // //0) Declare constants
    // $mainAccountAddress = "HX2NYIIWEYBTSKE2EMAKIRZAQPRRL2JJOLPBCX33V2TYWGIBR626JHM6RA";
    // $mainWalletPw = 'shype2022';
    // $algodToken = '49f27db9b910c6e3548de27e73af38697394906ff2c2f0aeace2fe2b15589bce';
    // $genesis = 'SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=';
    // $algowifiAssetId = 67967557;

    // //1) get lastRound from algod
    // $algorand = new Algorand_algod($algodToken,"localhost",53898); //get the token key in data/algod.admin.token
    // $return=$algorand->get("v2","status");        
    // $return_array=json_decode($return['response']);
    // $lastRound=$return_array->{'last-round'} ;

    // $algorand_kmd = new Algorand_kmd('b1bdecba8a5374ea4e4b853df49f9d58c1877ee5edcbd2fba63653228dc35d74',"localhost",7833); 

    // //2) get handle token from wallet
    // $params['params']=array(
    //     "wallet_id" => "27008546fcceca5252bf3938f21eff5e",
    //     "wallet_password" => $mainWalletPw,
    // );
    // $return=$algorand_kmd->post("v1","wallet","init",$params);
    // $return_array=json_decode($return['response']);
    // $wallet_handle_token=$return_array->wallet_handle_token;

    // //3) Get the address to remove
    // $sql = "SELECT algorandAddress FROM User WHERE id=".$userID;
    // $addressToClose = "";
    // $result = $conn->query($sql);
    // $row = $result->fetch_assoc();
    // $addressToClose = $row['algorandAddress'];

    // //3.1 Get the balance
    // $return=$algorand->get("v1","account",$addressToClose);
    // $return_array=json_decode($return['response']);
    // $balance=$return_array->{'amount'};

    // //3.2) Get asset amount
    // $return=$algorand->get("v1","account",$addressToClose);
    // $return_array=json_decode($return['response']);
    // $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} ;

    // //asset transfer 
    // //move back algowifi balance to main Account
    // $transaction=array(
    //     "txn" => array(
    //             "type" => "axfer", //Tx Type
    //             "arcv" => $mainAccountAddress, //AssetReceiver
    //             "snd" => $addressToClose, //Sender
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound, //First Valid
    //             "lv" => $lastRound+300, //Last Valid
    //             "gh" => $genesis, //Genesis Hash
    //             "xaid" => $algowifiAssetId, //XferAsset ID
    //             "aamt" => $algoWifiAmount,
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
    // print_r($return);

    // //broadcast transaction
    // $params['transaction']=$txn;
    // $return=$algorand->post("v2","transactions",$params);


    // //4) make a transaction to close the address
    // $transaction=array(
    //     "txn" => array(
    //             "type" => "pay", //Tx Type
    //             "close" => $mainAccountAddress,
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound+1, //First Valid
    //             "lv" => ($lastRound+200), //Last Valid
    //             "gen" => "testnet-v1.0", // GenesisID
    //             "gh" => $genesis, //Genesis Hash
    //             "note" => "Account closed", //You note
    //             "snd" => $addressToClose, //Sender
    //             "rcv" => $mainAccountAddress, //Receiver
    //             "amt" => $balance,
    //         ),
    // );

    // $params['params']=array(
    //     "transaction" => $algorand_kmd->txn_encode($transaction),
    //     "wallet_handle_token" => $wallet_handle_token,
    //     "wallet_password" => $mainWalletPw,
    //  );
     
    //  //4.1) sign transaction
    //  $return=$algorand_kmd->post("v1","transaction","sign",$params);
    //  $r=json_decode($return['response']);
    //  $txn=base64_decode($r->signed_transaction);
     
    //  //4.2) broadcast transaction
    //  $params['transaction']=$txn;
    //  $return=$algorand->post("v2","transactions",$params);

    //  //5) remove address?



    // //***** END ALGORAND PROCEDURES *****


    //perform query
    $sql = "DELETE FROM User WHERE id=".$userID;
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        printOutput(1);
    } else 
    {
        printOutput(0);
        error_log("Error: " . $sql . " " . $conn->error);
    }
    
    $conn->close();
    

?>