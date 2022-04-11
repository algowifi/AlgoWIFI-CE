<?php

include './scriptsPHP/dbConn.php';
include('sdk/algorand.php');
include('./scriptsPHP/algoConfig.php');

header('Content-Type: application/json; charset=utf-8');

$performTransactions = true;
$missingNFT = false;
$defaultImage = "./img/default.jpeg";
$defaultLandingPage = "'https://algowifi.com'";

// $mac = $_POST['mac'];
// $ip = $_POST['ip'];
// $username = $_POST['username'];
// $linklogin = $_POST['link-login'];
// $linkorig = $_POST['link-orig'];
// $error = $_POST['error'];
// $chapid = $_POST['chap-id'];
// $chapchallenge = $_POST['chap-challenge'];
// $linkloginonly = $_POST['link-login-only'];
// $linkorigesc = $_POST['link-orig-esc']; // pagina di landing dopo accesso. Possibilita' di variarla in base alla campagna.
// $macesc = $_POST['mac-esc'];
// $server = $_POST['server-name'];
$NFT_wifi = $_POST['identity']; 
$referAdd = $_SERVER['HTTP_REFERER'];
$remoteAdd = $_SERVER['REMOTE_ADDR'];

$remoteAdd = str_replace("https://", "", $remoteAdd);
$remoteAdd = str_replace("http://", "", $remoteAdd);
$remoteAdd = str_replace("www.", "", $remoteAdd);


//se l'nft non è impostato, mostra campagna di default
if (!isset($NFT_wifi)) {
    //show default campaign
    $image = $defaultImage;
    $linkorigesc = $defaultLandingPage;

    $missingNFT = true;
    $performTransactions = false;
}



if (!$missingNFT) {
    //call algoexplorer api to get hotspot owner address from nft
    $response = file_get_contents($algoExplorerAssetsApiPrefix . $NFT_wifi . '/balances?currency-greater-than=0');
    $response = json_decode($response);
    $nftOwnerAddress = $response->{'balances'}[0]->{'address'};

//get hotspot mysql id
$sql1 = "SELECT id FROM Hotspot WHERE nft = " . $NFT_wifi . " AND validator LIKE '%" . $referAdd . "%'";
$result = $conn->query($sql1);
$hotspotMysqlId = -1;
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $hotspotMysqlId = $row['id'];
}
//check if hotspot id is valid!!
if ($hotspotMysqlId == -1) {
    //set default campaign
    $image = $defaultImage;
    $linkorigesc = $defaultLandingPage;
    $performTransactions = false;
    $missingNFT = true;
    error_log("not found any hotspot for nft: ".$NFT_wifi." && validator: ".$referAdd);
} 
else 
{
     //Get active Campaign
     $sql = "SELECT C.*, HC.id as relation_id, HC.hotspotId, HC.campaignId, U.algorandAddress as publisher_address, U.id as publisher_id FROM User as U, Campaign as C, Hotspot_Campaign as HC WHERE C.isActive = 1 AND U.id = C.userId AND HC.hotspotId = " . $hotspotMysqlId . " AND C.id = HC.campaignId ORDER BY C.creation ASC LIMIT 1"; // carica la campagna attiva LiFo
     error_log("performing query: ".$sql);

     $result = $conn->query($sql);
 
     //se non esiste una campagna attiva su questo hotspot, mostra default
     if ($result->num_rows != 1) {
         $image = $defaultImage;
         $linkorigesc = $defaultLandingPage;
         $performTransactions = false;
     }
     else 
     {
         $row = $result->fetch_assoc();
         $image = $row['imageUrl'];
         $message = $row['name'];
     $validity = $row['isActive'];
     $algoaddressADV = $row['publisher_address'];
     $publisherId = $row['publisher_id'];
     $relationId = $row['relation_id'];
     $linkorigesc = $row['landingUrl'];
     $id_campaign = $row['id'];
     //$combined = "Mac : $mac - Location  : $server - $message"; // Note to blockchain
 
     //build a json note
     //$output['Mac'] = $mac;
     //$output['Location'] = $server. " - ".$message;
     $output['CampaignName'] = $message;
     $output['CampaignId'] = $row['id'];
     $output['referer'] = $referAdd;
     $output['remote'] = $remoteAdd;
     $combined = json_encode($output);
 
     //get Publisher AWIFI balance
     $return = $algorand->get("v1", "account", $algoaddressADV);
     $return_array = json_decode($return['response']);
     $AWIFI = $return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000;
 
     //if balance == 0 show default campaign and disable every campaign of this publisher 
     if ($AWIFI == 0) 
     {
         $image = $defaultImage;
         $linkorigesc = $defaultLandingPage;
         $performTransactions = false;
         
         // Disable every campaign of this publisher
         $sql2 = "UPDATE Campaign SET isActive=0 WHERE userId=".$publisherId;
         error_log("performing query ".$sql2, 0);
         if ($conn->query($sql2) === TRUE) 
         {
             error_log("Publisher ".$algoaddressADV." has balance 0! Every campaign has been disabled!");
         } else 
         {
             error_log("Error: " . $sql2 . " " . $conn->error);
         }
     }
     }
 
     
}
   
} //end !missingNFT


if ($performTransactions)
 {

    #Prepare transaction
    // get lastRound from algod
    $return = $algorand->get("v2", "status");
    $return_array = json_decode($return['response']);
    $lastround = $return_array->{'last-round'};

    //3) make a transaction Group
    //Transaction 1 : payment to platform
    $transactions=array();
    $transactions[]=array(
        "txn" => array(
            "aamt" => $platformFee,
            "type" => "axfer", //Tx Type
            "fee" => 1000, //Fee
            "fv" => $lastround, //Take the last round
            "gen" => $genesisID, // GenesisID
            "gh" => $genesis, //Genesis Hash
            "lv" => $lastround + 200, //Add 200 round 
            "note" => $combined, //Your note
            "snd" => $algoaddressADV, //Sender publisher
            "arcv" => $mainAccountAddress, //Platform address
            "xaid" => $algowifiAssetId, // ID Asa
        ),
    );
    //Transaction 2 : payment to hotspot owner
    $transactions[]=array(
        "txn" => array(
            "aamt" => $hotspotFee,
            "type" => "axfer", //Tx Type
            "fee" => 1000, //Fee
            "fv" => $lastround, //Take the last round
            "gen" => $genesisID, // GenesisID
            "gh" => $genesis, //Genesis Hash
            "lv" => $lastround + 200, //Add 200 round 
            "note" => $combined, //Your note
            "snd" => $mainAccountAddress, //Sender platoform
            "arcv" => $nftOwnerAddress, //Receiver nft owner
            "xaid" => $algowifiAssetId, // ID Asa
        ),
    );

    //Transaction 3 : payment to reserve address
    $transactions[] = array(
        "txn" => array(
            "aamt" => $reserveFee,
            "type" => "axfer", //Tx Type
            "fee" => 1000, //Fee
            "fv" => $lastround, //Take the last round
            "gen" => $genesisID, // GenesisID
            "gh" => $genesis, //Genesis Hash
            "lv" => $lastround + 200, //Add 200 round 
            "note" => $combined, //Your note
            "snd" => $mainAccountAddress, //Sender platform
            "arcv" => $mainReserveAddress, //Receiver reserve address
            "xaid" => $algowifiAssetId, // ID Asa
        ),
    );
    
    //2) Group TRansactions
    $groupid=$algorand_kmd->groupid($transactions);
    #Assigns Group ID
    $transactions[0]['txn']['grp']=$groupid;
    $transactions[1]['txn']['grp']=$groupid;
    $transactions[2]['txn']['grp']=$groupid;
 
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

    #Sign Transaction 3
    $params['params'] = array(
        //"public_key" => $algorand_kmd->pk_encode($mainAccountAddress),
        "transaction" => $algorand_kmd->txn_encode($transactions[2]),
        "wallet_handle_token" => $wallet_handle_token,
        "wallet_password" => $mainWalletPw,
    );

    $return = $algorand_kmd->post("v1", "transaction", "sign", $params);
    $r = json_decode($return['response']);
    $txn .= base64_decode($r->signed_transaction);
    $clearTxn .= $r->signed_transaction;

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
        //Increment views for relation hotspot_campaign
        $sql3 = "UPDATE Hotspot_Campaign SET views = views + 1 WHERE id = ".$relationId;
        error_log("performing query ".$sql3, 0);
        if ($conn->query($sql3) === TRUE) 
        {
            error_log("Certified view for campaign ".$id_campaign);
        } else 
        {
            error_log("Error: " . $sql3 . " " . $conn->error);
        }
   }
   else
   {
       $s = print_r($return, true);
       $failString = 'Transaction Failed! '.$s;
       error_log($failString);
   }
    
} //end performTransactions
else if ($image == $defaultImage && !$missingNFT) {

    //build a json note
    //$output['Mac'] = $mac;
    //$output['Location'] = $server . " - " . "defaultCampaign";
    $output['CampaignName'] = "defaultCampaign";
    $output['CampaignId'] = "defaultCampaign";
    $output['referer'] = $referAdd;
    $output['remote'] = $remoteAdd;
    $combined = json_encode($output);
    
    // get lastRound from algod
    $return = $algorand->get("v2", "status");
    $return_array = json_decode($return['response']);
    $lastround = $return_array->{'last-round'};

    //perform reserve account to hotspotter transaction
    $transaction = array(
        "txn" => array(
            "aamt" => $reserveFee,
            "type" => "axfer", //Tx Type
            "fee" => 1000, //Fee
            "fv" => $lastround, //Take the last round
            "gen" => $genesisID, // GenesisID
            "gh" => $genesis, //Genesis Hash
            "lv" => $lastround + 200, //Add 200 round 
            "note" => $combined, //Your note
            "snd" => $mainReserveAddress, //Sender platform
            "arcv" => $nftOwnerAddress, //Receiver reserve address
            "xaid" => $algowifiAssetId, // ID Asa
        ),
    );
    //sign transaction
    $params['params'] = array(
        "transaction" => $algorand_kmd->txn_encode($transaction),
        "wallet_handle_token" => $wallet_handle_token,
        "wallet_password" => $mainWalletPw,
    );
    $return = $algorand_kmd->post("v1", "transaction", "sign", $params);
    $r = json_decode($return['response']);
    $txn = base64_decode($r->signed_transaction);

    //broadcast transaction
    $params['transaction'] = $txn;
    $return = $algorand->post("v2", "transactions", $params);
}

$conn->close();

if ($image == $defaultImage)
{
    $fullImageUrl = str_replace("./img/", "https://algowifi.com/algowifi/img/", $image);
}
else
{
    $fullImageUrl = str_replace("./", "https://algowifi.com/algowifi/", $image);
}

$output['image'] = $fullImageUrl;
echo json_encode($output);
