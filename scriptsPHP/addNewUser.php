<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) {
        error_log("user is not an admin, cannot add new user!", 0);
        printOutput(0);
        $conn->close();
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin adding new user");
    } else {
        error_log("user is not an admin, cannot add new user!", 0);
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
            $output['message'] = "New User added successfully";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = "Error adding new User";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['newName']) || !isset($_POST['newEmail']) 
    || !isset($_POST['newNote']) || !isset($_POST['newAddress']) 
    || !isset($_POST['isAdmin']) || !isset($_POST['isLocation']) 
    || !isset($_POST['isPublisher']) || !isset($_POST['isHotspotter']))
    {
        error_log("missing params, cannot add new user!", 0);
        printOutput(0);
        $conn->close();
        die();
    }
    $newName = validate($_POST['newName']);
    $newEmail = validate($_POST['newEmail']);
    $newNote = validate($_POST['newNote']);
    $newAddress = validate($_POST['newAddress']);
    $newisAdmin = $_POST['isAdmin'];
    $newisLoc = $_POST['isLocation'];
    $newisPub = $_POST['isPublisher'];
    $newisHot = $_POST['isHotspotter'];

    if (empty($newName) || empty($newEmail) || empty($newNote) || empty($newAddress)) 
    {
        error_log("missing params, cannot add new user!", 0);
        printOutput(0);
        $conn->close();
        exit();
    }

    //***** START ALGORAND PROCEDURES *****

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
    $newGeneratedAddress=$return_array->{'address'} ;


    //3) make a transaction Group
    //Transaction 1 : 0,39 algos from main to new address
    $transactions=array();
    $transactions[]=array(
        "txn" => array(
                "fee" => 1000, //Fee
                "fv" => $lastRound, //First Valid
                "gen" => $genesisID, // GenesisID
                "gh" => $genesis, //Genesis Hash
                "lv" => ($lastRound+200), //Last Valid
                "note" => "", //Your note
                "snd" => $centralBankAddress, //Sender
                "type" => "pay", //Tx Type
                "rcv" => $newGeneratedAddress, //Receiver
                "amt" => 390000, //Amount
            ),
    );
    //Transaction 2 : asset opt-in
    $transactions[]=array(
        "txn" => array(
                "type" => "axfer", //Tx Type
                "arcv" => $newGeneratedAddress, //AssetReceiver
                "snd" => $newGeneratedAddress, //Sender
                "fee" => 1000, //Fee
                "fv" => $lastRound+1, //First Valid
                "lv" => $lastRound+300, //Last Valid
                "gh" => $genesis, //Genesis Hash
                "xaid" => $algowifiAssetId, //XferAsset ID
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

   //5) check transaction status
   $return_array=json_decode($return['response']);
   $transactionID=$return_array->{'txId'} ;
   if ($return['code'] == 200)
   {
       $successString = 'Transaction performed! sender:'.$senderID.' - reciver:'.$receiverID.' - txId: '.$transactionID.' amount: '.$amount;
       error_log($successString);
   }
   else
   {
       $s = print_r($return, true);
       $failString = 'Transaction Failed! '.$s;
       error_log($failString);
   }












    // //3) make a transaction of 0,3 algos from main to new address
    // $transaction=array(
    //     "txn" => array(
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound, //First Valid
    //             "gen" => $genesisID, // GenesisID
    //             "gh" => $genesis, //Genesis Hash
    //             "lv" => ($lastRound+200), //Last Valid
    //             "note" => "", //Your note
    //             "snd" => $mainAccountAddress, //Sender
    //             "type" => "pay", //Tx Type
    //             "rcv" => $newGeneratedAddress, //Receiver
    //             "amt" => 390000, //Amount
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

    //  //4) opt-in to an asset
    // $transaction=array(
    //     "txn" => array(
    //             "type" => "axfer", //Tx Type
    //             "arcv" => $newGeneratedAddress, //AssetReceiver
    //             "snd" => $newGeneratedAddress, //Sender
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound+1, //First Valid
    //             "lv" => $lastRound+300, //Last Valid
    //             "gh" => $genesis, //Genesis Hash
    //             "xaid" => $algowifiAssetId, //XferAsset ID
    //         ),
    // );

    // //4.1) sign transaction
    // $params['params']=array(
    //     "transaction" => $algorand_kmd->txn_encode($transaction),
    //     "wallet_handle_token" => $wallet_handle_token,
    //     "wallet_password" => $mainWalletPw,
    //  );
    // $return=$algorand_kmd->post("v1","transaction","sign",$params);
    // $r=json_decode($return['response']);
    // $txn=base64_decode($r->signed_transaction);
    
    // //4.2) broadcast transaction
    // $params['transaction']=$txn;
    // $return=$algorand->post("v2","transactions",$params);

    //***** END ALGORAND PROCEDURES *****

    
    //create new password
	$newPassword= generatePassword();
	$newPasswordHash= md5($newPassword);


    //perform query

    $sql = "INSERT INTO User (name, email, note, address, password, algorandAddress, nft, isAdmin, isLocation, isPublisher, isHotspotter)
    VALUES ('".$newName."', '".$newEmail."', '".$newNote."', '".$newAddress."', '".$newPasswordHash."', '".$newGeneratedAddress."', '".$newGeneratedAddress."', ".$newisAdmin.", ".$newisLoc.", ".$newisPub.", ".$newisHot.")";
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        printOutput(1);
        //send email with credentials to user
		$to = $newEmail;
		$subject = 'New Account created - AlgoWiFi';
		$message = '<center><img src=""  style="width:220px; height:138px;"></img></center><p>Hello '.ucwords($newName).', your AlgoWifi account has been created!</p> 
					<p>You can now log in with the following credentials:</p>
					<p>Email: '.$newEmail.'</p>
					<p>Password: '.$newPassword.'</p>
					<p>You can change the password anytime after a login <a href="#">here</a> <p>';
		$headers = 'MIME-Version: 1.0' . "\r\n".
					'Content-type: text/html; charset=UTF-8'."\r\n" .
					'From: AlgoWiFi <help@algowifi.com>' . "\r\n" .
					'Reply-To:  AlgoWiFi <help@algowifi.com>' . "\r\n" .
		    		'X-Mailer: PHP/' . phpversion();
		mail($to, $subject, $message, $headers);

    } else 
    {
        printOutput(0);
        error_log("Error: " . $sql . " " . $conn->error);
    }
    
    $conn->close();
    

?>