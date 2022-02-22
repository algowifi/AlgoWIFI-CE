<!DOCTYPE html>
<?php include 'check.php';
include './sdk/algorand.php';
include './scriptsPHP/algoConfig.php';
?>

<html lang="en">
<head>
    <title>Home</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <!--Bootstrap & jQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!--Menu-->
    <link href='https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
    <script type="text/javascript" src="./js/menu.js"></script>
    <script type="text/javascript" src="./js/home.js"></script>
    <link rel="stylesheet" href="./css/menu.css">
</head>
<body id="body-pd" class="body-pd">
    
    <header class="header body-pd" id="header">
        <div class="header_toggle"> <i class='bx bx-menu bx-x' id="header-toggle"></i> </div>
        <div class="header_img"> <img src="./img/Alogo.png" alt=""> </div>
    </header>

    <?php
         printMenu();
    ?>
    <!--Container Main start-->
    <div class="container height-100 bg-light">
        <h4>Welcome <?php
        echo((($_SESSION['user']['isAdmin'] == true) ? "Admin " : "User ").$_SESSION['user']['name']);?></h4>
        <?php
            if ($_SESSION['user']['isAdmin'])
            {
                //print platform Data
                echo "<hr>";
                echo "<h5>Platform Data</h5>";
                echo "<p>Scan to follow the platform account on Algorand mobile App</p><img src=";
                echo "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".$mainAccountAddress;
                echo ' title="Algorand Address QR-Code" /><br>';

                $return=$algorand->get("v1","account",$mainAccountAddress);
                $return_array=json_decode($return['response']);
                $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000;
                $algoAmount=$return_array->{'amount'} / 1000000;
            
                //print platform account address
                echo "<p><a target='_blank' href='".$algoExplorerUrlPrefix.$mainAccountAddress."'>".$mainAccountAddress."</a></p>";
                echo "<p>Algo balance: ".number_format($algoAmount, 3, '.', ',')."</p>";
                echo "<p>AWIFI balance: ".number_format($algoWifiAmount, 4, '.', ',')."</p>";
            }
            else 
            {
                $return=$algorand->get("v1","account",$_SESSION['user']['algorandAddress']);
                $return_array=json_decode($return['response']);
                $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000;
                $algoAmount=$return_array->{'amount'} / 1000000;
                echo "<h5>Balance</h5>";

                echo "<p>AWIFI: ".number_format($algoWifiAmount, 4, '.', ',')."</p>";
                if ($_SESSION['user']['isLocation'])
                {
                    echo "<p>Algo: ".number_format($algoAmount, 3, '.', ',')."</p>";
                }
            }
        ?>

        <hr>

        <?php 
        if (!$_SESSION['user']['isLocation']) 
        {
            echo '<h5 id="metricsTitle">Loading metrics...</h5>
            <div id="spinner" class="spinner-border text-primary" role="status">
            <span class="sr-only"></span>
            </div>';
        }
         ?>
        

        <div class="row gap-3" <?php if ($_SESSION['user']['isLocation']) echo 'style="display:none;"'; ?>>
           
            <div class="col card" <?php if (!$_SESSION['user']['isAdmin']) echo 'style="display:none;"'; ?>>
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <ul class="card-text">
                        <li>Admin <span id="numAdmin"></span></li>
                        <li>Publishers <span id="numPublisher"></span></li>
                        <li>Hotspotters <span id="numHotspotter"></span></li>
                        <li>Locations <span id="numLocation"></span></li>
                    </ul>
                    <a href="./users.php" class="btn btn-primary">Show <span id="numTotUsers"></span> users</a>
                </div>
            </div>
            
            <div class="col card" <?php if (!($_SESSION['user']['isAdmin'] || $_SESSION['user']['isHotspotter'])) echo 'style="display:none;"'; ?>>
                <div class="card-body">
                    <h5 class="card-title">Hotspots</h5>
                    <ul class="card-text">
                        <li>Hotspots <span id="numHotspots"></span></li>
                    </ul>
                    <a href="./hotspots.php" class="btn btn-primary">Show <span id="numHotspots2"></span> hotspots</a>                </div>
            </div>
            
            <div class="col card" <?php if (!($_SESSION['user']['isAdmin'] || $_SESSION['user']['isPublisher'])) echo 'style="display:none;"'; ?>>
                <div class="card-body">
                    <h5 class="card-title">Campaigns</h5>
                    <ul class="card-text">
                        <li>On Campaigns <span id="numEnabledCampaigns"></span></li>
                        <li>Off Campaigns <span id="numDisabledCampaigns"></span></li>
                    </ul>
                    <a href="./campaigns.php" class="btn btn-primary">Show <span id="numTotCampaigns"></span> campaigns</a>                </div>
            </div>
            
            <div class="col card">
                <div class="card-body">
                    <h5 class="card-title">Views and Earnings</h5>
                    <ul class="card-text">
                    <li>Tot views: <span id="totViews"></span></li>
                    <li <?php if (!($_SESSION['user']['isAdmin'] || $_SESSION['user']['isPublisher'])) echo 'style="display:none;"'; ?>>AWIFI sent: <span id="spentAWIFI"></span></li>
                    <li <?php if (!($_SESSION['user']['isAdmin'] || $_SESSION['user']['isHotspotter'])) echo 'style="display:none;"'; ?>>AWIFI received: <span id="receivedAWIFI"></span></li>
                    </ul>
                </div>
            </div>

        </div> 

    </div>
    <!--Container Main end-->
</body>
</html>