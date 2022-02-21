<?php

// $image = "img/natale.jpeg" ; // variabile che contiene immagine campagna
// $algoaddressADV = "TA2NXZAW4XSIQIWH7NRT3DJDKZATRMRGQIPQDAL3XVJKX6VJGUFUYYCAPQ" ; address del publisher

   $mac=$_POST['mac'];
   $ip=$_POST['ip'];
   $username=$_POST['username'];
   $linklogin=$_POST['link-login'];
   $linkorig=$_POST['link-orig'];
   $error=$_POST['error'];
   $chapid=$_POST['chap-id'];
   $chapchallenge=$_POST['chap-challenge'];
   $linkloginonly=$_POST['link-login-only'];
   $linkorigesc=$_POST['link-orig-esc']; // pagina di landing dopo accesso. Possibilita' di variarla in base alla campagna.
   $macesc=$_POST['mac-esc'];
   $server=$_POST['server-name'];
   $NFT_wifi= $_POST['identity'];


# Plug  POST data to blockchain ver. 1.0 
# 7/05/2021 copyright Beaconet
# Marco Caldarazzo
########################################## Inizio routine di check ##########################################################################





####################################################
# routine ricerca campagna attiva 
####################################################

include('sdk/algorand.php');
include 'Database.php';

$conn = OpenCon();

$sql = "SELECT * FROM campagne WHERE validity ='yes' ORDER BY id DESC LIMIT 1"; // carica la campagna attiva LiFo

$result = mysqli_query($conn, $sql);


$row = mysqli_fetch_array($result);
		  $message=$row['message'];
		  $validity=$row['validity'];
		  $image=$row['image'];
                  $algoaddressADV=$row['algoaddressADV'];
                  $linkorigesc=$row['landing'];
                  $id_campaign=$row['id_campaign'];
                  $combined = "Mac : $mac - Location  : $server - $message"; // Note to blockchain

// if validity = no , attiva campagna di default

#####################################################
# routine verifica appartenenza hot-spot a campagna
#####################################################


//$sql = " SELECT * FROM Net_ADV WHERE id_campaign = '$id_campaign' AND NFT_WIFI = '$NFT_wifi' "; // check if hot-spot is in campaign
//$check = mysqli_query($conn,$sql);
//if (mysqli_num_rows($check)==0) {

//set default campaign;
 
//}



##########################################################
# routine di check account - 338444324 ID di AWIFI
##########################################################

##### sostituire con chiamata API ad algoexplorer.


# $algorand = new Algorand_algod('828a44e54abd44d2f6eb90ad83b340fc48f3def58bfe180893fdfa5e98c15d79','localhost',53898);
# $return_balance_ass=$algorand->get("v2","accounts", $algoaddressADV,"?format=json"); // check AWIFI balance
# $AWIFI= array_search(338444324, $return_balance); 

// if $AWIFI == 0 then $image = "img/default.jpg" & $algoaddressADV = "default" ; // attiva campagna di default 

##################################################### fine routine di check #####################################################################




##################################################### Start transaction routine ##################################################################


$algorand_kmd = new Algorand_kmd('8a985caa351c8cd850b3ad7d72e88ccd0afc00925a34ae9b73d54087a68c4685',"localhost",7833); 

#Wallet Init to get the handle token
$params['params']=array(
    "wallet_id" => "c8598a9069dee374ef5fec8ef93f5d7f",
    "wallet_password" => "password",
);


$return=$algorand_kmd->post("v1","wallet","init",$params);
$return_array=json_decode($return['response']);
$wallet_handle_token=$return_array->wallet_handle_token;

#Prepare transaction
$algorand = new Algorand_algod('828a44e54abd44d2f6eb90ad83b340fc48f3def58bfe180893fdfa5e98c15d79','localhost',53898);

$return=$algorand->get("v2","transactions","params");
# print_r($return);
$return_array=json_decode($return['response']);
$lastround=$return_array->{'last-round'} ;


$transaction=array(
        "txn" => array(
		"aamt" =>"10000",
		"type" => "axfer", //Tx Type
                "fee" => 1000, //Fee
                "fv" => $lastround, //Take the last round
                "gen" => "mainnet-v1.0", // GenesisID
                "gh" => "YBQ4JWH4DW655UWXMBF6IVUOH5WQIGMHVQ333ZFWEC22WOJERLPQ=", //Genesis Hash
                "lv" => $lastround+200, //Add 200 round 
                "note" => $combined, //Your note
                "snd" => $algoaddressADV, //Sender publisher
                "arcv" => $NFT_wifi, //Receiver hot-spot
                "xaid" => "338444324", // ID Asa
            ),
);


#Sign Transaction
$params['params']=array(
   "transaction" => $algorand_kmd->txn_encode($transaction),
   "wallet_handle_token" => $wallet_handle_token,
   "wallet_password" => "password",
);

$return=$algorand_kmd->post("v1","transaction","sign",$params);
$r=json_decode($return['response']);
$txn=base64_decode($r->signed_transaction);

#Broadcasts a raw transaction to the network.
$algorand = new Algorand_algod('828a44e54abd44d2f6eb90ad83b340fc48f3def58bfe180893fdfa5e98c15d79','localhost',53898);

$params['transaction']=$txn;
$return=$algorand->post("v2","transactions",$params);
if(!empty($return['response']->txId)){
    $txId=$return['response']->txId;
    echo "txId: $txId";
}

############################## End blockchain routine ##############################################################
?>






<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>AlgoWiFi hotspot > login</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="-1" />
<style type="text/css">
body {color: #737373; font-size: 10px; font-family: verdana;}

textarea,input,select {
background-color: #FDFBFB;
border: 1px solid #BBBBBB;
padding: 2px;
margin: 1px;
font-size: 14px;
color: #808080;
}

a, a:link, a:visited, a:active { color: #AAAAAA; text-decoration: none; font-size: 10px; }
a:hover { border-bottom: 1px dotted #c1c1c1; color: #AAAAAA; }
img {border: none;}
td { font-size: 14px; color: #7A7A7A; }
</style>

</head>

<body>




<table width="100%" style="margin-top: 10%;">
	<tr>
	<td align="center" valign="middle">
	<div class="notice" style="color: #c1c1c1; font-size: 30px">Questa connessione e' offerta da:<p>
	<div class="notice" style="color: #c1c1c1; font-size: 30px"><img src="<?= $image; ?>"> <p>

<!-- $(if trial == 'yes') -->
Per collegarti, <a style="color: #FF8080 ;font-size: 32px"href="<?php echo $linkloginonly; ?>?dst=<?php echo $linkorigesc; ?>&username=T-<?php echo $macesc; ?>">click here</a>.
<!-- $(endif) -->

</div><br />
	
	
<!-- $(if error) -->
<br /><div style="color: #FF8080; font-size: 9px"><?php echo $error; ?></div>
<!-- $(endif) -->

	</td>
	</tr>
</table>

</body>
</html>
