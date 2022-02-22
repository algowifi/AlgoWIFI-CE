<!DOCTYPE html>
<?php 
    include 'check.php'; 
    include 'scriptsPHP/dbConn.php'; 
    include('sdk/algorand.php');
    include('scriptsPHP/algoConfig.php');
    adminCheck();
    if (!isset($_GET['userid']))
    {
        //show current user profile
    }
    else 
    {
        //load user data from db
        $sql = "SELECT * FROM User WHERE id=".$_GET['userid'];
        $result = $conn->query($sql);

        if ($result->num_rows == 1) 
        {
            // output data of each row
            $user = $result->fetch_assoc();
        } 
        else 
        {
            echo "0 results";
        }
        $conn->close();
    }
    
    //get algorand asset balance
    $return=$algorand->get("v1","account",$user['algorandAddress']);
    $return_array=json_decode($return['response']);
    $algoWifiAmount=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000;
    $algoAmount=$return_array->{'amount'} / 1000000;

     //Get type of user
     $userType = "";
     if ($user['isAdmin'])
     {
         $userType = "Admin ";
     }
     if ($user['isLocation'])
     {
         $userType .= "Location";
     }
     else if ($user['isPublisher'])
     {
         $userType .= "Publisher";
     }
     else if ($user['isHotspotter'])
     {
         $userType .= "Hotspotter";
     }
     else if (!$user['isAdmin'])
     {
        $userType = 'User';
     }

?>
<html lang="en">
<head>
    <title>User Page</title>
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
    <link rel="stylesheet" href="./css/menu.css">

     <!-- datatables -->
     <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css"></link>

    <script type="text/javascript" src="./js/user.js"></script>
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

        <h4><span id="userTypeField"><?php echo $userType; ?></span> <span id="userNameField"><?php echo ($user['name']); ?></span></h4>
        <h5><span id="userBalanceField"><?php echo (number_format($algoWifiAmount, 4, '.', ',')); ?></span> AWIFI, <span id="userAlgoBalanceField"><?php echo(number_format($algoAmount, 3, '.', ','));?></span> Algo</h5>
        <p>
            <button type="button" id="btnNew" class="btn btn-primary"><i class="bx bx-money-withdraw"></i></button>
            <div id="containerTitle"></div>
        </p>
        
        <!-- user form -->
        <form id="userForm">
            <form>
                <div class="modal-body">
                    <input id="userIdField" type="hidden" value="<?php echo $user['id']; ?>">
                    <p>Scan to follow this account on Algorand mobile App</p>
                    <img src=<?php echo "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".$user['algorandAddress']; ?> title="Algorand Address QR-Code" />
                    <?php 
                    echo "<p><a target='_blank' href='".$algoExplorerUrlPrefix.$user['algorandAddress']."'>".$user['algorandAddress']."</a></p>"; 
                    ?>

                    <br>
                    <div class="form-group">
                        <label>Name</label>
                        <input id="nameField" type="text" class="form-control" value="<?php echo $user['name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input id="emailField" type="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input id="noteField" type="text" class="form-control" value="<?php echo $user['note']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input id="addressField" type="text" class="form-control" value="<?php echo $user['address']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Algorand Address: <a target='_blank' href='<?php echo $algoExplorerUrlPrefix.$user['algorandAddress']; ?>'>View in algoexplorer</a></label>
                        <input id="algorandAddressField" type="text" class="form-control" value="<?php echo $user['algorandAddress']; ?>" required>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>NFT</label>
                        <input id="nftField" type="text" class="form-control" value="<?php echo $user['nft']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input id="passwordField" type="text" class="form-control" value="<?php echo $user['password']; ?>" required>
                    </div>
                    <div class="form-check form-switch">
                        <label class="form-check-label" for="isEnabledField">isEnabled</label>
                        <input id="isEnabledField" type="checkbox" class="form-check-input" <?php if ($user['isEnabled']) echo 'checked' ?> >
                    </div>
                    <div class="form-check form-switch">
                        <label class="form-check-label" for="isAdminField">isAdmin</label>
                        <input id="isAdminField" type="checkbox" class="form-check-input" <?php if ($user['isAdmin']) echo 'checked' ?> >
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
                        <span class="sr-only"></span>
                    </div>
                    <?php
                    if ($_SESSION['user']['id'] != $_GET['userid'])
                    {
                        //echo '<button type="button" id="btnRemove" class="btn btn-danger">Remove user</button>';
                    }
                    ?>
                <button type="button" id="btnCancel" class="btn btn-secondary" tabindex="2">Cancel</button>
                    <button type="submit" id="btnSave" value="btnSave" class="btn btn-primary" translate="1">Save</button>
                </div>
            </form>
        </form>
        <!-- user form end -->

        <!-- Add awifi form -->
        <form id="addAwifiForm" style="display:none;">
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount</label>
                        <input id="amount" type="number" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="spinner2" class="spinner-border text-primary" role="status" style="display: none;">
                        <span class="sr-only"></span>
                    </div>
                    <button type="button" id="btnCancelTransfer" class="btn btn-secondary" tabindex="2">Cancel</button>
                    <button type="submit" id="btnSaveTransfer" class="btn btn-primary" translate="1">Save</button>
                </div>
            </form>
        </form>
        <!-- Add awifi form end -->


        <?php
            if ($user['isHotspotter'])
            {
                echo "<h4>Owned Hotspots</h4>";
                echo '<table id="hotspotsTable" class="display" style="width:100%">
                <thead><tr><th>NFT</th><th>Location</th><th>Views</th><th>AWIFI</th></tr></thead>
                <tbody></tbody>
                <tfoot><tr><th>NFT</th><th>Location</th><th>Views</th><th>AWIFI</th></tr></tfoot></table><hr>';
            }
            else if ($user['isPublisher'])
            {
                echo "<h4>Campaigns</h4>";
                echo '<table id="campaignsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Active</th>
                    <th>Creation date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Active</th>
                    <th>Creation date</th>
                </tr>
            </tfoot>
        </table> ';

            }
            
        ?>

        

    </div>
    <!--Container Main end-->
</body>
</html>