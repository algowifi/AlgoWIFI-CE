<?php
    
    //1) Constants
    $mainAccountAddress = "HX2NYIIWEYBTSKE2EMAKIRZAQPRRL2JJOLPBCX33V2TYWGIBR626JHM6RA";
    $mainWalletPw = 'shype2022';
    $mainWalletId = '27008546fcceca5252bf3938f21eff5e';
    $algodToken = '49f27db9b910c6e3548de27e73af38697394906ff2c2f0aeace2fe2b15589bce';
    $kmdToken = 'b1bdecba8a5374ea4e4b853df49f9d58c1877ee5edcbd2fba63653228dc35d74';
    $genesis = 'SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=';
    $algowifiAssetId = 67967557;
    $algodPort = 53898;
    $kmdPort = 7833;
    $server = "localhost";
    $genesisID = "testnet-v1.0";
    $algoExplorerUrlPrefix = "https://testnet.algoexplorer.io/address/";
    $algoExplorerAssetsApiPrefix = "https://algoindexer.testnet.algoexplorerapi.io/v2/assets/";
    $algoExplorerAssetUrlPrefix = 'https://testnet.algoexplorer.io/asset/';
    
    $platformFee = 100;
    $hotspotFee = 60;

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