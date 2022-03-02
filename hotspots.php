<!DOCTYPE html>
<?php
include 'check.php';
include './scriptsPHP/dbConn.php';
?>
<html lang="en">

<head>
    <title>Hotspots List</title>
    <link rel="icon" href="./img/favicon_algowifi.png" type="image/x-icon" />
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!--Bootstrap & jQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!--Menu-->
    <script type="text/javascript" src="./js/menu.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="./css/menu.css">

    <!-- datatables -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    </link>
    <script src="js/hotspots.js"></script>
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
        <h4 id="containerTitle">Hotspots List</h4>
        <?php
        if ($_SESSION['user']['isAdmin']) {
            echo '<input id="isAdm" value="1" type="hidden">';
            echo '<p><button type="button" id="btnNew" class="btn btn-primary"><i class="bx bxs-plus-square"></i></button></p>';
        } else {
            echo '<input id="isAdm" value="0" type="hidden">';
        }
        ?>
        <table id="example" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>NFT</th>
                    <th>Network</th>
                    <th>Location</th>
                    <th>Owner</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th>Id</th>
                    <th>NFT</th>
                    <th>Network</th>
                    <th>Location</th>
                    <th>Owner</th>
                    <th>Note</th>
                </tr>
            </tfoot>
        </table>


        <!-- Insertion form -->
        <form id="insertionForm" style="display:none;">
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Location</label>
                        <input id="newLocation" type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Network</label>
                        <input id="newNetwork" type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input id="newNote" type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Validator</label>
                        <input id="newValidator" type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Owner</label>
                        <select id="newOwner" class="form-select" aria-label="Select Owner" required>
                            <option value="" selected>Choose a user</option>
                            <?php
                            //perform query 
                            $result = $conn->query("SELECT name, algorandAddress FROM User WHERE isEnabled = true AND isHotspotter = true ORDER BY name");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['algorandAddress'] . '">' . $row['name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
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
        </form>
        <!-- Insertion form end -->


    </div>
    <!--Container Main end-->



</body>

</html>