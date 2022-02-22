<!DOCTYPE html>
<?php 
    include 'check.php'; 
    include 'scriptsPHP/dbConn.php'; 
    include('sdk/algorand.php');
    include('scriptsPHP/algoConfig.php');
    //adminCheck();
    if (!isset($_GET['hotid']))
    {
        //show current user profile
    }
    else 
    {
        //load hotspot data from db
        $sql = "SELECT * FROM Hotspot WHERE id=".$_GET['hotid'];
        $result = $conn->query($sql);

        if ($result->num_rows == 1) 
        {
            // output data of each row
            $thisHotspot = $result->fetch_assoc();
        } 
        else 
        {
            echo "0 results";
        }

        //load this hotspot totViews
        $sql = "SELECT SUM(views) as totViews FROM Hotspot_Campaign WHERE hotspotId = ".$_GET['hotid'];
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $totViews = $row['totViews']; 
        
    }
    
    

?>
<html lang="en">
<head>
    <title>Hotspot Page</title>
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
    <script type="text/javascript" src="./js/hotspot.js"></script>
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
    <div class="height-100 bg-light">
        <div id="liveAlertPlaceholder"></div>

        <h4>Hotspot <?php echo $thisHotspot['id']; ?></h4>
        <p>Tot views: <?php echo $totViews; ?></p>


        <?php
        //check if there is an active campaign on this hotspot
        //Get active Campaign
        $sql = "SELECT C.*, HC.id as relation_id, HC.hotspotId, HC.campaignId, HC.views, U.algorandAddress as publisher_address, U.id as publisher_id 
        FROM User as U, Campaign as C, Hotspot_Campaign as HC 
        WHERE C.isActive = 1 AND U.id = C.userId AND HC.hotspotId = " . $thisHotspot['id'] . " AND C.id = HC.campaignId
        ORDER BY C.creation ASC LIMIT 1"; // carica la campagna attiva LiFo
    
        $result = $conn->query($sql);
        if ($result->num_rows == 1) 
        {
            $row = $result->fetch_assoc();
            $cId = $row['id'];  
            $cName = $row['name'];  
            $cImage = $row['imageUrl'];
            $cViews = $row['views'];
            $publisherAddress = $row['publisher_address'];

            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">Current active campaign</h5>';
            echo '<p class="card-text">';
            echo $cName.' - Views: '.$cViews;
            echo ' </p>';
            echo '<p class="card-text">Publisher: '.$publisherAddress.'</p>';

            if ($_SESSION['user']['isAdmin'])
                echo '<a target="_blank" href="./campaign.php?cid='.$cId.'" class="btn btn-primary">Open campaign</a>  ';              
            echo '</div>';
            echo '</div>';

      
        }



        $inputDisabled = ($_SESSION['user']['isAdmin']) ? '' : 'disabled' ;

        ?>


        <!-- hotspot form -->
        <form id="hotspotForm">
            <form>
                <div class="modal-body">
                <input id="hsIdField" type="hidden" value="<?php echo $thisHotspot['id']; ?>">
                <input id="nftIdField" type="hidden" value="<?php echo $thisHotspot['nft']; ?>">
                    <div class="form-group">
                        <label>Location</label>
                        <input id="locationField" type="text" class="form-control" value="<?php echo $thisHotspot['location']; ?>" required <?php echo $inputDisabled;?>>
                    </div>
                    <div class="form-group">
                        <label>Network Name</label>
                        <input id="networkField" type="text" class="form-control" value="<?php echo $thisHotspot['networkName']; ?>" required <?php echo $inputDisabled;?>>
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input id="noteField" type="text" class="form-control" value="<?php echo $thisHotspot['note']; ?>" required <?php echo $inputDisabled;?>>
                    </div>
                    <div class="form-group">
                        <label>Validator</label>
                        <input id="validatorField" type="text" class="form-control" value="<?php echo $thisHotspot['validator']; ?>" <?php echo $inputDisabled;?>>
                    </div>
                    <div class="form-group">
                        <label>NFT</label>
                        <input id="nftField" type="text" class="form-control" value="<?php echo $thisHotspot['nft']; ?>" required <?php echo $inputDisabled;?>>
                    </div>
                </div>

                <?php
                    if ($_SESSION['user']['isAdmin'])
                    {
                        echo '<div class="modal-footer">
                            <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
                                <span class="sr-only"></span>
                            </div>
                            <button type="button" id="btnCancel" class="btn btn-secondary" tabindex="2">Cancel</button>
                            <button type="submit" id="btnSave" value="btnSave" class="btn btn-primary" translate="1">Save</button>
                        </div>';
                    }
                ?>
                
            </form>
        </form>
        <!-- hotspot form end -->
        
        <!-- <form>
            <h4>Nft Data</h4>
            <?php
                //$l = $algoExplorerAssetUrlPrefix.$thisHotspot['nft'];
                //echo  "<p><a target='_blank' href='".$l."'>View on AlgoExplorer</a></p>";
            ?>

            <div id="spinner2" class="spinner-border text-primary" role="status">
                <span class="sr-only"></span>
            </div>
            <div class="form-group">
                <label for="nftInfoTextarea">Nft Info:</label>
                <textarea class="form-control" id="nftInfoTextarea" rows="3" disabled></textarea>
            </div>
        </form> -->

        <hr>

        <form id="changeOwnerForm" <?php 
        if (!$_SESSION['user']['isAdmin'])
        {
            echo ' style="display:none;"';
        }
        ?>>
            <div class="modal-body">
                <div id="liveAlertPlaceholder2"></div>
                <h4>Change Owner</h4>
                <div class="form-group">
                    <label>Current Owner Address</label>
                    <input id="ownerAddress" type="text" class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label>Current Owner Name</label>
                    <input id="ownerName" type="text" class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label>New Owner</label>
                    <select  id="newOwner" class="form-select" aria-label="Select new owner" required>
                        <option value="" selected>Choose a user</option>
                             <?php
                                 //perform query 
                                $result = $conn->query("SELECT name, algorandAddress FROM User WHERE isEnabled = true AND isHotspotter = true ORDER BY name");
                                if ($result->num_rows > 0) 
                                {
                                    while ($row = $result->fetch_assoc()) 
                                    {
                                        echo '<option id="'.$row['algorandAddress'].'" value="'.$row['algorandAddress'].'">'.$row['name'].'</option>';
                                    }
                                } 
                            ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <div id="spinner3" class="spinner-border text-primary" role="status" style="display: none;">
                    <span class="sr-only"></span>
                </div>
                <button type="submit" id="btnSave2" value="btnSave2" class="btn btn-primary" translate="1">Change</button>
            </div>
        </form>


       <?php $conn->close(); ?>

    </div>
    <!--Container Main end-->
</body>
</html>