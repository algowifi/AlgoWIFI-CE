<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');

header('Content-Type: application/json; charset=utf-8');

// allow execution to admin only
if (!isset($_SESSION['user'])) {
    error_log("user is not authenticated, cannot create nft!", 0);
    printOutput(0, 'user not authenticated');
    $conn->close();
    die();
} else if (!$_SESSION['user']['isAdmin']) {
    error_log("user is not an admin, cannot create nft!", 0);
    printOutput(0, 'user not allowed');
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

function printOutput($success, $msg)
{
    if ($success) {
        $output['success'] = 1;
        $output['message'] = $msg;
        echo json_encode($output);
    } else {
        $output['success'] = 0;
        $output['message'] = $msg;
        echo json_encode($output);
    }
}

// Upload Dir
$dir = '../nftAttachments';


//Validate File
if (0 < $_FILES['file']['error']) {
    error_log('Error: ' . $_FILES['file']['error'], 0);
    printOutput(0, 'Error: ' . $_FILES['file']['error']);
    $conn->close();
    die();
}

// //check filesize TODO: set limit!
// if ($_FILES['file']['size'] > 1000000) {
//     error_log("Exceeded filesize limit.", 0);
//     printOutput(0, 'Exceeded filesize limit.');
//     $conn->close();
//     die();
// }

//Validate Fields
if (
    !isset($_POST['newStandard']) || !isset($_POST['newTotSupply']) || !isset($_POST['newDecimals'])) 
{
    error_log("missing params, cannot create nft!");
    printOutput(0, 'missing params');
    $conn->close();
    die();
}

$userId = $_SESSION['user']['id'];
$newStandard = validate($_POST['newStandard']);
$newName = validate($_POST['newName']);
$newUnitName = validate($_POST['newUnitName']);
$newTotSupply = $_POST['newTotSupply'];
$newDecimals = $_POST['newDecimals'];
$newManager = validate($_POST['newManager']);
$newReserve = validate($_POST['newReserve']);
$newFreeze = validate($_POST['newFreeze']);
$newClawback = validate($_POST['newClawback']);


if (empty($newStandard)) 
{
    error_log("missing params, cannot create nft!");
    printOutput(0, 'missing params');
    $conn->close();
    die();
}

//get optional params
$newUrl = validate($_POST['newUrl']);
$newMetaHash = validate($_POST['newMetaHash']);
$newDescription = validate($_POST['newDescription']);
$newNote = validate($_POST['newNote']);

$newMetaHash = validate($_POST['newMetaHash']);

$newProperties = json_decode($_POST['newProperties']);

$msg = "creazione nft: {standard: ".$newStandard." - name: ".$newName." - unitName: ".$newUnitName." - Manager: ".$newManager." - Reserve: ".$newReserve." - Freeze: ".$newFreeze." - Claw: ".$newClawback." - Properties: [";

foreach($newProperties as $k => $v)
{
    $msg .= "(".$k." : ".$v."),";
}
$msg .= "] - totalSupply: ".$newTotSupply." - decimals: ".$newDecimals." - url: ".$newUrl." - metadataHash: ".$newMetaHash." - description: ".$newDescription." - note: ".$newNote." }";

error_log($msg);

// // create new directory with 744 permissions if it does not exist yet
// if (!file_exists($dir)) {
//     mkdir($dir, 0744);
//     // if (mkdir ($dir, 0744))
//     //     echo 'dir created ';
//     // else 
//     //     echo 'error creating dir ';
// }

// //try to move file
// $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
// $newFileName = $userId."-".$newName.".".$ext;
// $newFileUrl = $dir."/".$newFileName;

// $fileOK = move_uploaded_file($_FILES['file']['tmp_name'], $newFileUrl);
// if (!$fileOK) {
//     error_log("File not moved! " . $_FILES['file']['name'], 0);
//     printOutput(0, "File upload error! ");
//     $conn->close();
//     die();
// }

//TODO: handle standard => note, 
// standard, alla fine dell url bisogna aggiungere #arc3


//TODO: handle file

//il file deve essere salvato in ipfs, prendiamo il link che ne deriva e a questo link si aggiunge #arc3

//arc3 prevede il salvataggio in ipfs

//arc69 non di default






//********** START ALGORAND PROCEDURE **********
//1) get lastRound from algod
$return=$algorand->get("v2","status");        
$return_array=json_decode($return['response']);
$lastRound=$return_array->{'last-round'} ;


$transaction=array(
    "txn" => array(
            "fee" => 1000, //Fee
            "fv" => $lastRound, //First Valid
            "gh" => $genesis, //Genesis Hash
            "lv" => ($lastRound+200), //Last Valid
            "snd" => $centralBankAddress, //Sender
            "type" => "acfg", //Tx Type
            "note" => $newNote,
            "apar" => array(
                "an" => $newName,
                "au" => $newUrl,
                "c" => $newClawback,
                "dc" => intval($newDecimals),
                "f" => $newFreeze,
                "m" => $newManager,
                "r" => $newReserve,
                "t" => intval($newTotSupply),
                "un" => $newUnitName,
                "am" => $newMetaHash,
                "df" => false
            ),
        ),
);

$params['params']=array(
    "transaction" => $algorand_kmd->txn_encode($transaction),
    "wallet_handle_token" => $wallet_handle_token,
    "wallet_password" => $mainWalletPw,
);

$failString = "";

//3.1) sign transaction
$return=$algorand_kmd->post("v1","transaction","sign",$params);
$r=json_decode($return['response']);
$txn=base64_decode($r->signed_transaction);

$s = print_r($return, true);
$failString .= '<br> sign transaction: '.$s;

//3.2) broadcast transaction
$params['transaction']=$txn;
$return=$algorand->post("v2","transactions",$params);

$s = print_r($return, true);
$failString .= '<br> broadcast transaction: '.$s;


//check if transaction ok
if ($return['code'] != 200) //nft not created successfully
{
    // $s = print_r($return, true);
    // $failString .= 'Transaction Failed! '.$s;

    printOutput(0,$failString);
    $conn->close();
    die();
}

$s = print_r($return, true);
$okString = 'Transaction Succeded! '.$s;


sleep(15);

//4) GET NFT IDENTIFIER
//4.1) get assets
$return=$algorand->get("v1","assets");
$return_array=json_decode($return['response']);
$assetsArray=$return_array->{'assets'};

//4.2) Find an asset id by unique name
$foundID = -1;
foreach ($assetsArray as $anAsset) 
{
    if ($anAsset->{'AssetParams'}->{'assetname'} == $newName)
    {
        $foundID = $anAsset->{'AssetIndex'};
        break;
    } 
}

//********** END ALGORAND PROCEDURE **********


printOutput(1,$okString." - nftid: ".$foundID);



$conn->close();



?>