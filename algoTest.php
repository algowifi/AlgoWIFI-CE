<!DOCTYPE html>
<html lang="en">
<head>
    <title>TEST ALGORAND FUNCTIONS</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- ALGO SDK -->
    <script src="https://cdn.jsdelivr.net/npm/algosdk@1.13.0/dist/browser/algosdk.min.js" integrity="sha384-3IaxTgktbWGiqPkr5oZQMM4H99ziYzoEUb6sznk03coGR2Cdf1r0I1GWPUL37iu8" crossorigin="anonymous"></script>
    <script src="js/algoTest.js"></script>
</head>

<body>

    <?php
        include('sdk/algorand.php');
        include('./scriptsPHP/algoConfig.php');
        $return=$algorand->get("v2","status");        
        print_r($return);
        $return_array=json_decode($return['response']);
        $lastRound=$return_array->{'last-round'} ;
        print_r($return);


        $mainAccountAddress = "HX2NYIIWEYBTSKE2EMAKIRZAQPRRL2JJOLPBCX33V2TYWGIBR626JHM6RA";

        echo "<h4>Main Account balance</h4>";
        $return=$algorand->get("v1","account",$mainAccountAddress);
        print_r($return);
       
        echo "<h4>Main Account info</h4>";
        $return=$algorand->get("v2","accounts",$mainAccountAddress,"?format=json"); //?format=json or msgpack (opcional, default json)
        print_r($return);




        echo "<h4>kmd version</h4>";
        $return=$algorand_kmd->get("versions");
        print_r($return);

    

        //echo "<h4>Kmd Swagger</h4>";
        //$return=$algorand_kmd->get("swagger.json");
        //print_r($return);


        //wallet list
        $return=$algorand_kmd->get("v1","wallets");
        echo("<h4>wallet list</h4>");
        print_r($return);


        //wallet init
        $params['params']=array(
            "wallet_id" => "27008546fcceca5252bf3938f21eff5e",
            "wallet_password" => "shype2022",
        );
        $return=$algorand_kmd->post("v1","wallet","init",$params);
        
        $return_array=json_decode($return['response']);
        
        echo("<h4>wallet init</h4>");
        print_r($return);

        $wallet_handle_token=$return_array->wallet_handle_token;

        echo ("handleToken ".print_r($wallet_handle_token));



        //Generate a key
        $params['params']=array(
            "display_mnemonic" => false,
            "wallet_handle_token" => $wallet_handle_token
        );
        $return=$algorand_kmd->post("v1","key",$params);
        echo "<h4>generate new key</h4>";
        print_r($return);

        $return_array=json_decode($return['response']);
        $newGeneratedAddress=$return_array->{'address'} ;


        // //import key in wallet - useless cause the key is already in the wallet
        // $params['params']=array(
        //     "private_key" => "",
        //     "wallet_handle_token" => $wallet_handle_token
        // );
        // $return=$algorand_kmd->post("v1","key","import",$params);
        // echo "<h4>import new key in wallet</h4>";
        // print_r($return);

        //make a transaction of 0,3 algos from main to new 
        $transaction=array(
            "txn" => array(
                    "fee" => 1000, //Fee
                    "fv" => $lastRound, //First Valid
                    "gen" => "testnet-v1.0", // GenesisID
                    "gh" => "SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=", //Genesis Hash
                    "lv" => ($lastRound+200), //Last Valid
                    "note" => "", //Your note
                    "snd" => $mainAccountAddress, //Sender
                    "type" => "pay", //Tx Type
                    "rcv" => $newGeneratedAddress, //Receiver
                    "amt" => 300000, //Amount
                ),
    );
    
    $params['params']=array(
       "transaction" => $algorand_kmd->txn_encode($transaction),
       "wallet_handle_token" => $wallet_handle_token,
       "wallet_password" => "shype2022",
    );
    
    //sign transaction
    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn=base64_decode($r->signed_transaction);
    echo "<h4>transazione di 0,2 algos</h4>";
    print_r($return);
    
    //broadcast transaction
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);
    echo "<h4>broadcast transaction</h4>";
    print_r($return);


    //opt-in to an asset
    $transaction=array(
        "txn" => array(
                "type" => "axfer", //Tx Type
                "arcv" => $newGeneratedAddress, //AssetReceiver
                "snd" => $newGeneratedAddress, //Sender
                "fee" => 1000, //Fee
                "fv" => $lastRound+1, //First Valid
                "lv" => $lastRound+300, //Last Valid
                "gh" => "SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=", //Genesis Hash
                "xaid" => 67967557, //XferAsset ID
            ),
    );

    //sign transaction
    $params['params']=array(
        "transaction" => $algorand_kmd->txn_encode($transaction),
        "wallet_handle_token" => $wallet_handle_token,
        "wallet_password" => "shype2022",
     );
    $return=$algorand_kmd->post("v1","transaction","sign",$params);
    $r=json_decode($return['response']);
    $txn=base64_decode($r->signed_transaction);
    echo "<h4>transazione di opt-in</h4>";
    print_r($return);
    
    //broadcast transaction
    $params['transaction']=$txn;
    $return=$algorand->post("v2","transactions",$params);
    echo "<h4>broadcast opt-in</h4>";
    print_r($return);


    ?>
</body>

</html>
