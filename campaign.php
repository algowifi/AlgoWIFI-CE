<!DOCTYPE html>
<?php
include 'check.php';
include 'scriptsPHP/dbConn.php';
include('sdk/algorand.php');
include('scriptsPHP/algoConfig.php');

//load campaign data from db
$sql = "SELECT C.*, U.algorandAddress, U.name as user_name FROM Campaign as C, User as U WHERE C.id=" . $_GET['cid'] . ' AND U.id = C.userId';
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    // output data of each row
    $thisCapaign = $result->fetch_assoc();
} else {
    echo "0 results " . $sql;
}

//load hotspots
$sql = 'SELECT H.id, H.nft, H.location, HC.views FROM Hotspot as H, Hotspot_Campaign as HC WHERE HC.campaignId = ' . $thisCapaign['id'] . ' AND H.id = HC.hotspotId ORDER BY H.location;';
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $i = 0;
    while ($row = $result->fetch_assoc()) {
        $hotspots[$i++] = $row;
    }
}

$allowEditing = (($_SESSION['user']['isAdmin']) || ($_SESSION['user']['isPublisher'] && !$thisCapaign['isActive']));
$inputDisabled = $allowEditing ? '' : 'disabled';
$isActiveDisabled = ($_SESSION['user']['isAdmin']) ? '' : 'disabled';

?>
<html lang="en">

<head>
    <title>Campaign Page</title>
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

    <!-- datatables -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    </link>

    <script type="text/javascript" src="./js/campaign.js"></script>

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

        <h4>Views & earnings</h4>
        <p>Tot views: <span id="totViews"></span></p>
        <p>Tot AWIFI spent: <span id="spentAWIFI"></span></p>
        <hr>

        <h4>Hotspots</h4>
        <?php
        if ($_SESSION['user']['isAdmin']) {
            echo '<input id="isAdm" value="1" type="hidden">';
            echo '<p><button type="button" id="btnNew" class="btn btn-primary"><i class="bx bxs-plus-square"></i></button></p>';

            echo '<form id="insertionForm" style="display:none;">';
            echo '<div class="form-group">';
            echo '<label>Select hotspots to add:</label>';
            echo '<select id="newHotspots" class="form-select" multiple size="10" aria-label="Choose your favorite hotspots" required>';
            $result = $conn->query("SELECT location, id FROM Hotspot ORDER BY location");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . $row['location'] . '</option>';
                }
            }
            echo '</select>';
            echo '</div>';
            echo '<div class="modal-footer">
                    <div id="spinner2" class="spinner-border text-primary" role="status" style="display: none;">
                        <span class="sr-only"></span>
                    </div>
                    <button type="button" id="btnCancel2" class="btn btn-secondary" tabindex="2">Cancel</button>
                    <button type="submit" value="btnSave2" id="btnSave" class="btn btn-primary" translate="1">Add</button>
                </div>';
            echo ' </form><hr>';
        } else {
            echo '<input id="isAdm" value="0" type="hidden">';
        }
        ?>
        <table id="example" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>NFT</th>
                    <th>Location</th>
                    <th>Views</th>
                    <th>AWIFI</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th>NFT</th>
                    <th>Location</th>
                    <th>Views</th>
                    <th>AWIFI</th>
                    <th>Remove</th>
                </tr>
            </tfoot>
        </table>
        <hr>

        <h4>Campaign <span id="campaignNameField"><?php echo ($thisCapaign['name']); ?></span></h4>

        <!-- Campaign form -->
        <form id="campaignForm">
            <form>
                <div class="modal-body">
                    <input id="campaignIdField" type="hidden" value="<?php echo $thisCapaign['id']; ?>" <?php echo $inputDisabled; ?>>
                    <input id="campaignOwnerIdField" type="hidden" value="<?php echo $thisCapaign['userId']; ?>" <?php echo $inputDisabled; ?>>
                    <div class="form-check form-switch">
                        <label class="form-check-label" for="isActiveField">isActive</label>
                        <input id="isActiveField" type="checkbox" class="form-check-input" <?php echo $isActiveDisabled;
                                                                                            if ($thisCapaign['isActive'])
                                                                                                echo ' checked';
                                                                                            ?>>
                    </div>
                    <div class="form-group">
                        <label>Owner Name</label>
                        <input id="ownerNameField" type="text" class="form-control" value="<?php echo $thisCapaign['user_name']; ?>" required disabled>
                    </div>
                    <div class="form-group">
                        <label>Owner Address</label>
                        <input id="ownerAddressField" type="text" class="form-control" value="<?php echo $thisCapaign['algorandAddress']; ?>" required disabled>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input id="nameField" type="text" class="form-control" value="<?php echo $thisCapaign['name']; ?>" required <?php echo $inputDisabled; ?>>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input id="descriptionField" type="text" class="form-control" value="<?php echo $thisCapaign['description']; ?>" required <?php echo $inputDisabled; ?>>
                    </div>
                    <div class="form-group">
                        <label>Current image</label>
                        <input id="imageUrlField" type="text" class="form-control" value="<?php echo $thisCapaign['imageUrl']; ?>" required <?php echo $isActiveDisabled; ?>>
                        <img src='<?php echo $thisCapaign['imageUrl']; ?>' />
                    </div>
                    <?php
                    if ($allowEditing) {
                        echo '<div class="form-group">
                            <label>Change image</label>
                            <input id="newImageUrl" type="file" accept="image/jpeg,image/png" class="form-control" >
                            </div>';
                    }
                    ?>
                    <div class="form-group">
                        <label>Landing url</label>
                        <input id="landingUrlField" type="text" class="form-control" value="<?php echo $thisCapaign['landingUrl']; ?>" required <?php echo $inputDisabled; ?>>
                    </div>
                    <div class="form-group">
                        <label>Creation date</label>
                        <input id="creationDateField" type="text" class="form-control" value="<?php echo $thisCapaign['creation']; ?>" disabled>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
                        <span class="sr-only"></span>
                    </div>
                    <?php
                    if ($allowEditing) {
                        echo '<button type="button" id="btnCancel" class="btn btn-secondary" tabindex="2">Cancel</button>';
                        echo '<button type="submit" id="btnSave" value="btnSave" class="btn btn-primary" translate="1">Save</button>';
                    } else if ($_SESSION['user']['isPublisher'] && $thisCapaign['isActive']) {
                        echo '<button type="button" id="btnDisable" class="btn btn-danger" tabindex="2">Disable this campaign</button>';
                    }
                    echo '<button type="button" id="btnRemove" class="btn btn-danger">Delete this campaign</button>';

                    ?>
                </div>
            </form>
        </form>
        <!-- campaign form end -->






    </div>
    <!--Container Main end-->
</body>
<?php
$conn->close();
?>

</html>