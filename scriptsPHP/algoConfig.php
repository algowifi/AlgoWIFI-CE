<?php

    // Marco Caldarazzo @ Algowifi
    // thanks to Algorand Foundation
    // Italy - 22 September 2022
    // info@airlan.it -  for contact
    // thanks to  https://developer.algorand.org/u/felipe.vieira/ for PHP SDK
    // thanks to Sergio Caserta - Algorand Champion
 
    //1) Constants
    $mainAccountAddress = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; // main account
    $mainReserveAddress = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; // reserve address 
    $centralBankAddress = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; // deposit account 
    $mainWalletPw = ''; //password wallet
    $mainWalletId = 'XXXXXXXXXXXXXXXXXXXXXXXXXXX'; // ID wallet
    $algodToken = '49f27db9b910c6e3548de27e73af38697394906ff2c2f0aeace2fe2b15589bce'; // algod token
    $kmdToken = 'b1bdecba8a5374ea4e4b853df49f9d58c1877ee5edcbd2fba63653228dc35d74'; // Kmd token
    $genesis = 'SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI='; // Genesis hash
    $algowifiAssetId = 67967557; // id AWIFI  token
    $algodPort = 53898;
    $kmdPort = 7833;
    $server = "localhost";
    $genesisID = "testnet-v1.0";
    $algoExplorerUrlPrefix = "https://testnet.algoexplorer.io/address/";
    $algoExplorerAssetsApiPrefix = "https://algoindexer.testnet.algoexplorerapi.io/v2/assets/";
    $algoExplorerAssetUrlPrefix = 'https://testnet.algoexplorer.io/asset/';
    
    $platformFee = 100;
    $hotspotFee = 60;
    $reserveFee = 10;

    //2) Instances to algod and Kmd
    $algorand = new Algorand_algod($algodToken,$server,$algodPort); 
    $algorand_kmd = new Algorand_kmd($kmdToken,$server,$kmdPort); 

    //3) Init Wallet and get handle token
    $params['params']=array(
        "wallet_id" => $mainWalletId,
        "wallet_password" => $mainWalletPw,
    );
    $return=$algorand_kmd->post("v1","wallet","init",$params);
    $return_array=json_decode($return['response']);
    $wallet_handle_token=$return_array->wallet_handle_token;


?>
