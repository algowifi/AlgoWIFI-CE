<?php
    session_start();
    include('dbConn.php');
    include('../sdk/algorand.php');
    include('./algoConfig.php');
    header('Content-Type: application/json; charset=utf-8');

    // allow execution to admin only
    if (!isset($_SESSION['user'])) {
        error_log("user is not an admin, cannot add new hotspot!", 0);
        printOutput(0, 'user is not an admin, cannot add new hotspot!');
        $conn->close();
        die();
    } else if ($_SESSION['user']['isAdmin']) {
        error_log("admin adding new hotspot");
    } else {
        error_log("user is not an admin, cannot add new hotspot!", 0);
        printOutput(0, 'user is not an admin, cannot add new hotspot!');
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
        if ($success)
        {
            $output['success'] = 1;
            $output['message'] = $msg;//"New Hotspot added successfully!";
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = $msg;//"Error adding new Hotspot!";
            echo json_encode($output);
        }
    }


    //1 get params 
    if (!isset($_POST['newLocation']) || !isset($_POST['newNetwork']) 
    || !isset($_POST['newNote']) || !isset($_POST['newOwner']))
    {
        error_log("missing params, cannot add new hotspot!", 0);
        printOutput(0, "Error adding new hotspot: missing params.");
        $conn->close();
        die();
    }
    $newLocation = validate($_POST['newLocation']);
    $newNetwork = validate($_POST['newNetwork']);
    $newNote = validate($_POST['newNote']);
    $newOwner = validate($_POST['newOwner']);
    $newValidator = validate($_POST['newValidator']);

    if (empty($newLocation) || empty($newNetwork) || empty($newNote) || empty($newOwner)) 
    {
        error_log("missing params, cannot add new hotspot!", 0);
        printOutput(0, "Error adding new hotspot: missing params.");
        exit();
    }

    

    //perform query
    $sql = "INSERT INTO Hotspot (location, networkName, note, validator, nft)
    VALUES ('".$newLocation."', '".$newNetwork."', '".$newNote."','".$newValidator."', 0)";
    
    error_log("performing query ".$sql, 0);

    if ($conn->query($sql) === TRUE) 
    {
        
        //Create nft
        
        $hotspotID = $conn->insert_id;
        $nftName = "AWIFISPOT_TEST_".$hotspotID;

            
        //***** START ALGORAND PROCEDURES *****

        //1) get lastRound from algod
        $return=$algorand->get("v2","status");        
        $return_array=json_decode($return['response']);
        $lastRound=$return_array->{'last-round'} ;

        //3) create nft for the new hotspot
        $transaction=array(
            "txn" => array(
                    "fee" => 1000, //Fee
                    "fv" => $lastRound, //First Valid
                    "gh" => $genesis, //Genesis Hash
                    "lv" => ($lastRound+200), //Last Valid
                    "snd" => $newOwner, //Sender
                    "type" => "acfg", //Tx Type
                    "apar" => array(
                        "an" => $nftName,
                        "au" => "algowifi.com",
                        "c" => $newOwner,
                        "dc" => 0,
                        "f" => $newOwner,
                        "m" => $newOwner,
                        "r" => $newOwner,
                        "t" => 1,
                        "un" => "AWIFISPT"
                    ),
                ),
        );

        $params['params']=array(
            "transaction" => $algorand_kmd->txn_encode($transaction),
            "wallet_handle_token" => $wallet_handle_token,
            "wallet_password" => $mainWalletPw,
        );
        
        //3.1) sign transaction
        $return=$algorand_kmd->post("v1","transaction","sign",$params);
        $r=json_decode($return['response']);
        $txn=base64_decode($r->signed_transaction);
        
        //3.2) broadcast transaction
        $params['transaction']=$txn;
        $return=$algorand->post("v2","transactions",$params);

        //check if transaction ok
        //$return_array=json_decode($return['response']);
        //$lastRound=$return_array->{'last-round'} ;
        if ($return['code'] != 200) //nft not created successfully
        {
            printOutput(0,$return['message']);

            //remove inserted hotspot
            $sql = "DELETE FROM Hotspot WHERE id=LAST_INSERT_ID()";
    
            error_log("performing query ".$sql, 0);
        
            if ($conn->query($sql) === TRUE) 
            {
                error_log("hotspot without nft removed: " . $sql);
                
            } else 
            {
                error_log("Error: " . $sql . " " . $conn->error);
            }
        

            $conn->close();
            die();
        }
    

        sleep(10);

        //4) GET NFT IDENTIFIER
        //4.1) get assets
        $return=$algorand->get("v1","assets");
        $return_array=json_decode($return['response']);
        $assetsArray=$return_array->{'assets'};

        //4.2) Find an asset id by unique name
        $foundID = -1;
        foreach ($assetsArray as $anAsset) 
        {
            if ($anAsset->{'AssetParams'}->{'assetname'} == $nftName)
            {
                $foundID = $anAsset->{'AssetIndex'};
                break;
            } 
        }





        //***** END ALGORAND PROCEDURES *****

        //update Field nft
        $sql2 = 'UPDATE Hotspot set nft='.$foundID.' WHERE id = LAST_INSERT_ID()';
        if ($conn->query($sql2) === TRUE) 
        {
            printOutput(1, "New hotspot added successfully!");
        } else 
        {
            printOutput(0, "Error: ".$sql2." ".$conn->error );
            error_log("Error: ".$sql2 ." ". $conn->error);
        }

    } else 
    {
        printOutput(0 , "Error: ". $sql2 . " " . $conn->error);
        error_log("Error: ". $sql ." ". $conn->error);
    }
    
    $conn->close();
    

?>