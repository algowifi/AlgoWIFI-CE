<?php
session_start();
include('dbConn.php');
include('../sdk/algorand.php');
include('./algoConfig.php');
header('Content-Type: application/json; charset=utf-8');

function printOutput($success, $params)
    {
        if ($success)
        {
            $output['success'] = 1;
            $output['metrics'] = $params;
            echo json_encode($output);
        }
        else
        {
            $output['success'] = 0;
            $output['metrics'] = null;
            echo json_encode($output);
        }
    }

if (!isset($_SESSION['user']))
{
    error_log("user is not authenticated, cannot get metrics!", 0);
    printOutput(0, 'user is not authenticated, cannot get metrics!');
    $conn->close();
    die();
}
else //user authenticated
{
    $campaignId = isset($_POST['cid']) ? $_POST['cid'] : -1;

    if ($_SESSION['user']['isAdmin'])
    {
        //1) get tot num of users for every kind
        $numAdmin = 0;
        $numPublisher = 0;
        $numHotspotter = 0;
        $numLocation = 0;
        $numTotUsers = 0;
        //A) get admins count
        $sql = "SELECT COUNT(isAdmin) as adminCount FROM User WHERE isAdmin = true";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numAdmin = $row['adminCount']; 
        //B) get publishers count
        $sql = "SELECT COUNT(isPublisher) as publisherCount FROM User WHERE isPublisher = true";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numPublisher = $row['publisherCount'];
        //C) get hotspotters count
        $sql = "SELECT COUNT(isHotspotter) as hotspotterCount FROM User WHERE isHotspotter = true";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numHotspotter = $row['hotspotterCount'];
        //D) get locations count
        $sql = "SELECT COUNT(isLocation) as locationCount FROM User WHERE isLocation = true";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numLocation = $row['locationCount'];
        $numTotUsers = $numAdmin + $numPublisher + $numHotspotter + $numLocation;
    
        //2) get number of enabled and disabled campaigns
        $numEnabledCampaigns = 0;
        $numDisabledCampaigns = 0;
        $numTotCampaigns = 0;
        //A) get enabled campaigns count
        $sql = "SELECT COUNT(isActive) as activeCount FROM Campaign WHERE isActive = true";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numEnabledCampaigns = $row['activeCount']; 
        //B) get disabled campaigns count
        $sql = "SELECT COUNT(isActive) as disabledCount FROM Campaign WHERE isActive = false";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numDisabledCampaigns = $row['disabledCount']; 
        $numTotCampaigns = $numEnabledCampaigns + $numDisabledCampaigns;


        //3) get num of hotspots
        $numHotspots = 0;
        $sql = "SELECT COUNT(*) AS numHotspots FROM Hotspot;";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numHotspots = $row['numHotspots']; 
       
        //4) get tot views
        $totViews = 0;
        $sql = "SELECT SUM(views) as totViews FROM Hotspot_Campaign";
        if ($campaignId != -1)
         {
             $sql .= " WHERE campaignId = ".$campaignId;
         }
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $totViews = $row['totViews']; 

        //5) calculate received and sended AWIFI
        //calculated values are in microAWIFI 
        //1 AWIFI = 10000 microAWIFI
        $receivedAWIFI = $totViews * $platformFee;  
        $spentAWIFI = $totViews * $hotspotFee;
        if ($campaignId != -1)
        {
            $spentAWIFI = $totViews * $platformFee;
        }


        //prepare output 
        if ($campaignId != -1)
        {
            $output['campaignId'] = $campaignId;
        }
        $output['numAdmin'] = $numAdmin;
        $output['numPublisher'] = $numPublisher;
        $output['numHotspotter'] = $numHotspotter;
        $output['numLocation'] = $numLocation;
        $output['numTotUsers'] = $numTotUsers;
        $output['numEnabledCampaigns'] = $numEnabledCampaigns;
        $output['numDisabledCampaigns'] = $numDisabledCampaigns;
        $output['numTotCampaigns'] = $numTotCampaigns;
        $output['numHotspots'] = $numHotspots;
        $output['totViews'] = $totViews;
        $output['receivedMicroAWIFI'] = $receivedAWIFI;
        $output['spentMicroAWIFI'] = $spentAWIFI;
        //print output
        printOutput(1,$output);

        
    }
    else if ($_SESSION['user']['isPublisher'])
    {
        $publisherId = $_SESSION['user']['id'];

        //1) get number of enabled and disabled campaigns
        $numEnabledCampaigns = 0;
        $numDisabledCampaigns = 0;
        $numTotCampaigns = 0;
        //A) get enabled campaigns count
        $sql = "SELECT COUNT(*) as activeCount FROM Campaign WHERE isActive = true AND userId = ".$publisherId;
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numEnabledCampaigns = $row['activeCount']; 
        //B) get disabled campaigns count
        $sql = "SELECT COUNT(*) as disabledCount FROM Campaign WHERE isActive = false AND userId = ".$publisherId;
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $numDisabledCampaigns = $row['disabledCount']; 
        $numTotCampaigns = $numEnabledCampaigns + $numDisabledCampaigns;

         //2) get tot views
         $totViews = 0;
         $sql = "SELECT SUM(HC.views) as totViews FROM Hotspot_Campaign as HC, Campaign as C, User as U
         WHERE HC.campaignId = C.id AND C.userId = U.id AND U.id = ".$publisherId;
         if ($campaignId != -1)
         {
             $sql .= " AND C.id = ".$campaignId;
         }
         $result = $conn->query($sql);
         $row = $result->fetch_assoc();
         $totViews = $row['totViews']; 
 
         //3) calculate spent AWIFI
         $spentAWIFI = $totViews * $platformFee;

        //prepare output 
        if ($campaignId != -1)
        {
            $output['campaignId'] = $campaignId;
        }
        $output['numEnabledCampaigns'] = $numEnabledCampaigns;
        $output['numDisabledCampaigns'] = $numDisabledCampaigns;
        $output['numTotCampaigns'] = $numTotCampaigns;
        $output['totViews'] = $totViews;
        $output['spentMicroAWIFI'] = $spentAWIFI;

        //print output
        printOutput(1,$output);


    }
    else if ($_SESSION['user']['isHotspotter'])
    {
        //Numero di hotspots, numero totale di visualizzazioni
        $hotspotterId = $_SESSION['user']['id'];
        $hotspotterAlgorandAddress = $_SESSION['user']['algorandAddress'];
 

        //1) get num of hotspots
        $numHotspots = 0;
        $return=$algorand->get("v1","account",$hotspotterAlgorandAddress);
        $return_array=json_decode($return['response']);
        $assets=$return_array->{'assets'};
        foreach ($assets as $k => $v)
        {
            if ($k != $algowifiAssetId && $v->{'amount'} == 1)
            {
                $numHotspots++;
            }   
        }

        //2 get num of views
        $microAWIFIAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'};
        $totViews = $microAWIFIAmount / $hotspotFee;


        //prepare output
        $output['numHotspots'] = $numHotspots;
        $output['totViews'] = $totViews;
        $output['receivedMicroAWIFI'] = $microAWIFIAmount;

        //print output
        printOutput(1,$output);



    }
    else 
    {
        $output['islocation'] = true;
        printOutput(1,$output);
    }
}//end else (user authenticated)


?>