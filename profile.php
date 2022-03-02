<!DOCTYPE html>
<?php
include 'check.php';
include './sdk/algorand.php';
include './scriptsPHP/algoConfig.php';

?>
<html lang="en">

<head>
    <title>User Profile</title>
    <link rel="icon" href="./img/favicon_algowifi.png" type="image/x-icon" />
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
    <script type="text/javascript" src="./js/profile.js"></script>
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
    <div class="height-100 bg-light" style="overflow:scroll;">
        <div id="liveAlertPlaceholder"></div>
        <h4><?php if ($_SESSION['user']['isAdmin']) echo "Admin";
            else echo "User"; ?> Profile</h4>
        <div id="userInfoContainer">
            <!-- <p>Scan to follow this account on Algorand mobile App</p>
            <img src=<?php echo "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $_SESSION['user']['algorandAddress']; ?> title="Algorand Address QR-Code" />
        <br> -->

            <div class="container">
                <div class="row">
                    <div class="col">
                        <h5>Personal Data</h5>
                        <?php
                        echo "<p>" . $_SESSION['user']['name'] . "</p>";
                        echo "<p>" . $_SESSION['user']['email'] . "</p>";
                        echo "<p>" . $_SESSION['user']['address'] . "</p>";
                        echo "<p>" . $_SESSION['user']['note'] . "</p>";
                        // if ($_SESSION['user']['isAdmin']) 
                        // {
                        //     //get algo balance and algowifi balance
                        //     $return=$algorand->get("v1","account",$mainAccountAddress);
                        //     $return_array=json_decode($return['response']);
                        //     $algoPlatformBalance=$return_array->{'amount'} / 1000000;
                        //     $algoWifiPlatformBalance=$return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000 ;
                        //     //print platform Data
                        //     echo "<hr>";
                        //     echo "<h5>Platform Data</h5>";
                        //     echo "<p>Scan to follow the platform account on Algorand mobile App</p><img src=";
                        //     echo "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".$mainAccountAddress;
                        //     echo ' title="Algorand Address QR-Code" /><br>';

                        //     //print platform account address
                        //     echo "<p><a target='_blank' href='".$algoExplorerUrlPrefix.$mainAccountAddress."'>".$mainAccountAddress."</a></p>";
                        //     //print algo and algowifi balance
                        //     echo "<p>Algo balance: ".number_format($algoPlatformBalance, 3, '.', ',')."</p>";
                        //     echo "<p>AWIFI balance: ".number_format($algoWifiPlatformBalance, 4, '.', ',')."</p>";
                        // } 
                        // else 
                        {
                            echo "<p>Scan to follow your account on Algorand mobile App</p><img src=";
                            echo "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $_SESSION['user']['algorandAddress'];
                            echo ' title="Algorand Address QR-Code" /><br>';

                            echo "<p><a target='_blank' href='" . $algoExplorerUrlPrefix . $_SESSION['user']['algorandAddress'] . "'>" . $_SESSION['user']['algorandAddress'] . "</a></p>";

                            //get algo balance and algowifi balance
                            $return = $algorand->get("v1", "account", $_SESSION['user']['algorandAddress']);
                            $return_array = json_decode($return['response']);
                            $algoBalance = $return_array->{'amount'} / 1000000;
                            $algoWifiBalance = $return_array->{'assets'}->{$algowifiAssetId}->{'amount'} / 10000;
                            //print algo and algowifi balance
                            //echo "<p>Algo balance: ".number_format($algoBalance, 3, '.', ',')."</p>";
                            echo "<p>AWIFI balance: " . number_format($algoWifiBalance, 4, '.', ',') . "</p>";
                        }
                        ?>
                    </div>
                    <div class="col">
                        <!-- Change Pw form -->
                        <form id="changePwForm">
                            <div class="modal-body">
                                <h5>Change Password</h5>
                                <div class="form-group">
                                    <label>New password</label>
                                    <input id="newPw" type="password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Repeat password</label>
                                    <input id="newPw2" type="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
                                    <span class="sr-only"></span>
                                </div>
                                <button type="button" id="btnCancel" class="btn btn-secondary" tabindex="2">Cancel</button>
                                <button type="submit" id="btnSave" value="btnSave" class="btn btn-primary" translate="1">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end userinfocontainer -->

        <hr>


    </div>
    <!--Container Main end-->
</body>

</html>