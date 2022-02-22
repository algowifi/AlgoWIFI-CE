<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');
    include('./algoConfig.php');

    header('Content-Type: application/json; charset=utf-8');

        //***** START ALGORAND PROCEDURES *****

    //0) Declare constants
    $testAccountAddress = "W3RRHEFH6EDYSRR3CWFMRFMMILRAAXUU424F7AWRFHANNHSZV335D4PH34";


    //1) get lastRound from algod
    $return=$algorand->get("v2","status");        
    $return_array=json_decode($return['response']);
    $lastRound=$return_array->{'last-round'} ;


    // // test stampa assets
    // $return=$algorand->get("v1","account",$testAccountAddress);
    // $return_array=json_decode($return['response']);
    // $assets=$return_array->{'assets'};
    // //echo ("balance ".$balance."\n");
    // print_r($return);
    // foreach ($assets as $k => $v)
    // {
    //     if ($k != $algowifiAssetId)
    //         echo $k.": ".$v->{'amount'}."\n";
    // }
    // die();
    


    //Test Transactions Group

    //1) Create transactions
    $receiver1 = "AI4PG7BZ5457R6KRKZ3VOIRSEWXYPWWAA72H3R2TGD7TGULJBTOHI5VCL4";
    $receiver2 = "BJRRCLYDHMHHN5JOCKCRIU4II7IBAVHMZEXBICHNHT2U5GEDKIDCLWQYQ4";
    //$testo = "messaggio testo"; 
    //Transaction 1
    $transactions=array();
    $transactions[]=array(
            "txn" => array(
                    "type" => "pay", //Tx Type
                    "fee" => 1000, //Fee
                    "fv" => $lastRound, //First Valid
                    "gen" => $genesisID, // GenesisID
                    "gh" => $genesis, //Genesis Hash
		    "lv" => $lastRound+300, //Last Valid
		    "note" => "",
                    "snd" => $mainAccountAddress, //Sender
                    "rcv" => $receiver1, //Receiver
                    "amt" => 1000, //Amount
                ),
    );
    //Transaction 2
    $transactions[]=array(
        "txn" => array(
            "type" => "pay", //Tx Type
            "fee" => 1000, //Fee
            "fv" => $lastRound, //First Valid
            "gen" => $genesisID, // GenesisID
            "gh" => $genesis, //Genesis Hash
	    "lv" => ($lastRound+300), //Last Valid
	    "note"=> "",
            "snd" => $mainAccountAddress, //Sender
            "rcv" => $receiver2, //Receiver
            "amt" => 1000, //Amount
        ),
    );
    
    


    //2) Group TRansactions
    $groupid=$algorand_kmd->groupid($transactions);
    #Assigns Group ID
    $transactions[0]['txn']['grp']=$groupid;
    $transactions[1]['txn']['grp']=$groupid;

     echo "Transactions group\n";
     print_r($transactions);

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

    echo "sign t1\n";
    print_r($return);

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

    echo "sign t2\n";
    print_r($return);
  
    //echo "txn:\n";
    //echo $txn;
    echo"\n";

    echo "base64_encode(\$txn): ".base64_encode($txn);
    echo"\n";
    echo"\nCleartxt:\n";
    echo $clearTxn;
    echo"\n";
   
    
    //4) Send Transaction Group
    #Broadcasts a raw atomic transaction to the network.
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);
    $txId=$return['response']->txId;

   


    echo "broadcast txts\n";
    print_r($return);

    //End Test Transactions Group

















    // //3) create nft for the new hotspot
    //  $nftID = "AWIFIHOTSPOT_TEST_1000";
    // $newOwner = "AI4PG7BZ5457R6KRKZ3VOIRSEWXYPWWAA72H3R2TGD7TGULJBTOHI5VCL4";
    // $transaction=array(
    //     "txn" => array(
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound, //First Valid
    //             "gh" => $genesis, //Genesis Hash
    //             "lv" => ($lastRound+200), //Last Valid
    //             "snd" => $newOwner, //Sender
    //             "type" => "acfg", //Tx Type
    //             "apar" => array(
    //                 "an" => $nftID,
    //                 "au" => "algowifi.com",
    //                 "c" => $newOwner,
    //                 "dc" => 0,
    //                 "f" => $newOwner,
    //                 "m" => $newOwner,
    //                 "r" => $newOwner,
    //                 "t" => 1,
    //                 "un" => "AWIFISPT"
    //             ),
    //         ),
    // );

    // $params['params']=array(
    //     "transaction" => $algorand_kmd->txn_encode($transaction),
    //     "wallet_handle_token" => $wallet_handle_token,
    //     "wallet_password" => $mainWalletPw,
    // );
    
    // //3.1) sign transaction
    // $return=$algorand_kmd->post("v1","transaction","sign",$params);
    // $r=json_decode($return['response']);
    // $txn=base64_decode($r->signed_transaction);
    // print_r($return);
    
    // //3.2) broadcast transaction
    // $params['transaction']=$txn;
    // $return=$algorand->post("v2","transactions",$params);
    // print_r($return);












    //get info about a transaction
    //$txid = "CYLUEWRTS64GR7X6EAZWND7LKHFSVMEDI4QAXI53M5AUSKMSYTEA";
    //$return=$algorand->get("v1","transaction",$txid); //start the algorand-indexer to run
    //$return=$algorand->get("v1","account",$mainAccountAddress,"transaction",$txid);

    // //get assets
    // $return=$algorand->get("v1","assets");
    // $return_array=json_decode($return['response']);
    // $assetsArray=$return_array->{'assets'};

    // //Find an asset id by unique name
    // $nftName = "AWIFISPOT_TEST_6";
    // $foundID = "0";
    // foreach ($assetsArray as $anAsset) 
    // {
    //     if ($anAsset->{'AssetParams'}->{'assetname'} == $nftName)
    //     {
    //         $foundID = $anAsset->{'AssetIndex'};
    //         break;
    //     } 
    // }
    // echo "Found id ".$foundID;

    // //Get Asset Informations
    // $return=$algorand->get("v2","assets",$foundID);
    // print_r($return);
    // $return_array=json_decode($return['response']);
    // $algoWifiCreator=$return_array->{'params'}->{'creator'} ;
    // $algoWifiManager=$return_array->{'params'}->{'manager'} ;
    // echo "<p> Asset creator: ".$algoWifiCreator. "</p>";
    // echo "<p> Asset manager: ".$algoWifiManager. "</p>";






    // echo("\nWallet handle token: ".$wallet_handle_token." .\n");


    // //get balance
    // $return=$algorand->get("v1","account",$testAccountAddress);
    // $return_array=json_decode($return['response']);
    // $balance=$return_array->{'amount'};
    // echo ("balance ".$balance."\n");
    // print_r($return);

    // //4) make a transaction to close the address
    // $transaction=array(
    //     "txn" => array(
    //             "type" => "pay", //Tx Type
    //             "close" => $mainAccountAddress,
    //             "fee" => 1000, //Fee
    //             "fv" => $lastRound, //First Valid
    //             "lv" => ($lastRound+200), //Last Valid
    //             "gen" => "testnet-v1.0", // GenesisID
    //             "gh" => $genesis, //Genesis Hash
    //             "note" => "Account closed", //You note
    //             "snd" => $testAccountAddress, //Sender
    //             "rcv" => $mainAccountAddress, //Receiver
    //             "amt" => ($balance - 1000), //balance - fee
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

    //  print_r($return);

















    // //Get asset amount and asset creator
    // $return=$algorand->get("v1","account",$testAccountAddress);
    // print_r($return);
    // $return_array=json_decode($return['response']);
    // $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} ;
    // $algoWifiCreator=$return_array->{'assets'}->{$algowifiAssetId}->{'creator'} ;
    // echo "<p> Asset creator: ".$algoWifiCreator. "</p>";
    
    // //asset revoke test
    //  //move back algowifi balance to main Account
    //  $transaction=array(
    //     "txn" => array(
    //             "type" => "axfer", //Tx Type
    //             "arcv" => $algoWifiCreator, //AssetReceiver
    //             "asnd" => $testAccountAddress, //AssetSender
    //             "snd" => $algoWifiCreator, //Sender
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
    // print_r($return);



    
?>
