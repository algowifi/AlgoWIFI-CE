<?php
include 'check.php';
include('sdk/algorand.php');
include('./scriptsPHP/algoConfig.php');

adminCheck();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Kmd for administrators</title>
    <link rel="icon" href="./img/favicon_algowifi.png" type="image/x-icon" />
    <meta charset='utf-8'>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap & jQuery-->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!--Menu-->
    <link href='https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
    <script type="text/javascript" src="./js/menu.js"></script>
    <link rel="stylesheet" href="./css/menu.css">
    <!-- Own -->
    <script type="text/javascript" src="./js/kmd.js"></script>

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
    <div class="bg-light">

        <input type="text" hidden id="awifiAssetId" value="<?php echo $algowifiAssetId; ?>">
        <h2>Transactions tool</h2>
        <form id="transactionForm">
            <div class="container">
                <div class="row">
                    <div class="col">
                        From
                    </div>

                    <div class="col">
                        To
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <input id="from" placeholder="Address" type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input id="to" placeholder="Address" type="text" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Algo</label>
                            <input id="fromAlgoField" type="text" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Algo</label>
                            <input id="toAlgoField" type="text" class="form-control" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>AWIFI</label>
                            <input id="fromAwifiField" type="text" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>AWIFI</label>
                            <input id="toAwifiField" type="text" class="form-control" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Assets (id : amount)</label>
                            <select id="fromAssetsField" class="form-select" size="10" disabled>

                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Assets (id : amount)</label>
                            <select id="toAssetsField" class="form-select" size="10" disabled>

                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Transfer</label>
                            <select id="transferType" class="form-select"></select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Asset ID to transfer</label>
                            <input id="transferAssetId" type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Amount to transfer (micro)</label>
                            <input id="transferAmount" type="number" class="form-control" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="modal-footer">
                            <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
                                <span class="sr-only"></span>
                            </div>
                            <button type="reset" id="btnReset" class="btn btn-secondary" tabindex="2">Reset</button>
                            <button type="submit" id="btnSave" value="btnSave" class="btn btn-primary" translate="1">Perform transaction</button>
                        </div>
                    </div>

                </div>














            </div>
        </form>


        <div id="liveAlertPlaceholder"></div>


        <div class="container">
            <div class="row">
                <div class="col">
                    <form>
                        <div class="form-group">
                            <label for="logTxtArea">Log</label>
                            <textarea class="form-control" id="logTxtArea" rows="3"></textarea>
                        </div>
                    </form>
                </div>
            </div>
            <br>
            <p><button type="button" id="btnNew" class="btn btn-primary"><i class="bx bx-plus"></i> Generate new Account</button></p>
        </div>



        <!-- <div class="container">
            <h2>NFT tool</h2>
            <form id="nftForm">
                <fieldset class="border p-2">

                    <legend class="w-auto">Asset details</legend>
                    <div class="row">
                        <div class="col">
                            NFT standard
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standardRadios" id="standardRadios1" value="arc69" checked>
                                    <label class="form-check-label" for="standardRadios1">
                                        ARC69
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standardRadios" id="standardRadios2" value="arc3">
                                    <label class="form-check-label" for="standardRadios2">
                                        ARC3
                                    </label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Choose File</label>
                                    <input id="newFileUrl" type="file" accept="*" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newName">
                                        Name
                                    </label>
                                    <input id="newName" placeholder="NFT_Name#33" type="text" class="form-control" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newUnitName">
                                        Unit Name
                                    </label>
                                    <input id="newUnitName" placeholder="NFT_Name" type="text" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newTotSupply">
                                        Total supply
                                    </label>
                                    <input id="newTotSupply" type="number" value="1" class="form-control" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newDecimals">
                                        Decimals
                                    </label>
                                    <input id="newDecimals" type="number" value="0" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newUrl">
                                        Url
                                    </label>
                                    <input id="newUrl" placeholder="url" type="url" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newMetaHash">
                                        Metadata hash
                                    </label>
                                    <input id="newMetaHash" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="newDescription">Description</label>
                                    <textarea class="form-control" placeholder="Describe your nft" id="newDescription" rows="3"></textarea>
                                </div>
                            </div>
                        </div>


                        <div id="propertiesDiv">
                            <label>Properties</label>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <input name="newKey0" id="newKey0" placeholder="key" type="text" class="form-control newKey">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <input name="newValue0" id="newValue0" placeholder="value" type="text" class="form-control newValue">
                                    </div>
                                </div>
                                <div class="col">
                                    <button class="btn btn-info" type="button" id="addPropertyBtn">+</button>
                                    <button class="btn btn-danger" type="button" id="cancFirstPropertyBtn"><i class='bx bxs-trash'></i></button>
                                </div>

                            </div>
                        </div>

                </fieldset>
                <br>
                <fieldset class="border p-2">

                    <legend class="w-auto">Asset management</legend>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label>Manager</label>
                                <input id="newManager" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label>Reserve</label>
                                <input id="newReserve" type="text" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label>Freeze</label>
                                <input id="newFreeze" type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label>Clawback</label>
                                <input id="newClawback" type="text" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="newNote">Note</label>
                                <textarea class="form-control" id="newNote" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                </fieldset>


                <div class="row">
                    <div class="col">
                        <div class="modal-footer">
                            <div id="spinner2" class="spinner-border text-primary" role="status" style="display: none;">
                                <span class="sr-only"></span>
                            </div>
                            <button type="reset" id="btnReset2" class="btn btn-secondary">Reset</button>
                            <button type="submit" id="btnSave2" value="btnSave" class="btn btn-primary" translate="1">CREATE</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div id="liveAlertPlaceholder2"></div> -->















    </div>
    <!--Container Main end-->

    <hr>


    <div class="bg-light">
    <div class="container">
            <h2>NFT tool</h2>
            <form id="nftForm">
                <input type="hidden" id="cbAddress" value="<?php echo $centralBankAddress;?>">
                <fieldset class="border p-2">

                    <legend class="w-auto">Asset details</legend>
                    <div class="row">
                        <div class="col">
                            NFT standard
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standardRadios" id="standardRadios1" value="arc69" checked>
                                    <label class="form-check-label" for="standardRadios1">
                                        ARC69
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standardRadios" id="standardRadios2" value="arc3">
                                    <label class="form-check-label" for="standardRadios2">
                                        ARC3
                                    </label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Choose File</label>
                                    <input id="newFileUrl" type="file" accept="*" class="form-control" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newName">
                                        Name
                                    </label>
                                    <input id="newName" placeholder="NFT_Name#33" type="text" class="form-control" >
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newUnitName">
                                        Unit Name
                                    </label>
                                    <input id="newUnitName" placeholder="NFT_Name" type="text" class="form-control" >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newTotSupply">
                                        Total supply
                                    </label>
                                    <input id="newTotSupply" type="number" value="1" class="form-control" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newDecimals">
                                        Decimals
                                    </label>
                                    <input id="newDecimals" type="number" value="0" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newUrl">
                                        Url
                                    </label>
                                    <input id="newUrl" placeholder="https://yourUrl.com" type="url" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label class="form-group-label" for="newMetaHash">
                                        Metadata hash
                                    </label>
                                    <input id="newMetaHash" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="newDescription">Description</label>
                                    <textarea class="form-control" placeholder="Describe your nft" id="newDescription" rows="3" disabled></textarea>
                                </div>
                            </div>
                        </div>


                        <div id="propertiesDiv">
                            <label>Properties</label>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <input name="newKey0" id="newKey0" placeholder="key" onchange="updatePropertiesAsJsonInNote();" type="text" class="form-control newKey">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <input name="newValue0" id="newValue0" placeholder="value" onchange="updatePropertiesAsJsonInNote();" type="text" class="form-control newValue">
                                    </div>
                                </div>
                                <div class="col">
                                    <button class="btn btn-info" type="button" id="addPropertyBtn">+</button>
                                    <button class="btn btn-danger" type="button" id="cancFirstPropertyBtn"><i class='bx bxs-trash'></i></button>
                                </div>

                            </div>
                        </div>

                </fieldset>
                <br>
                <fieldset class="border p-2">

                    <legend class="w-auto">Asset management</legend>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label>Manager</label>
                                <input id="newManager" type="text" class="form-control" value="<?php echo $centralBankAddress;?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label>Reserve</label>
                                <input id="newReserve" type="text" class="form-control" value="<?php echo $centralBankAddress;?>" >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label>Freeze</label>
                                <input id="newFreeze" type="text" class="form-control" value="<?php echo $centralBankAddress;?>" >
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label>Clawback</label>
                                <input id="newClawback" type="text" class="form-control" value="<?php echo $centralBankAddress;?>" >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="newNote">Note</label>
                                <textarea class="form-control" id="newNote" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                </fieldset>


                <div class="row">
                    <div class="col">
                        <div class="modal-footer">
                            <div id="spinner2" class="spinner-border text-primary" role="status" style="display: none;">
                                <span class="sr-only"></span>
                            </div>
                            <button type="reset" id="btnReset2" class="btn btn-secondary">Reset</button>
                            <button type="submit" id="btnSave2" value="btnSave" class="btn btn-primary" translate="1">CREATE</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div id="liveAlertPlaceholder2"></div>

    </div>





</body>

</html>