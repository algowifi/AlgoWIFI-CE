var propertiesIndex = 1;

function removeRow(btn) {
    btn.parentElement.parentElement.remove();
    updatePropertiesAsJsonInNote();
}

function updatePropertiesAsJsonInNote()
{
    var newProperties = {};
    $('#propertiesDiv .row').each(function () {
        let key = $(this).find('.col .form-group input.newKey').val();
        let val = $(this).find('.col .form-group input.newValue').val();
        if (key !== "" && val !== "")
            newProperties[key] = val;
    });
    let jsonString = JSON.stringify(newProperties, null, 2);
    $('#newNote').val(jsonString);
}

$(document).ready(function () {

    var awifiAssetId = $('#awifiAssetId').val();

    getAccounts();

    //Buttons

    $('#addPropertyBtn').click(function () {
        $('#propertiesDiv').append('<div class="row" style="margin-top:5px;"><div class="col"><div class="form-group"><input name="newKey' + propertiesIndex + '" placeholder="key" onchange="updatePropertiesAsJsonInNote();" type="text" class="form-control newKey" required></div></div><div class="col"><div class="form-group"><input name="newValue' + propertiesIndex + '"placeholder="value" onchange="updatePropertiesAsJsonInNote();" type="text" class="form-control newValue" required></div></div><div class="col"><button class="btn btn-danger" onclick="removeRow(this);" type="button"><i class="bx bxs-trash"></i></button></div></div>');
        propertiesIndex++;
    });

    $('#cancFirstPropertyBtn').click(function () {
        $('#newKey0').val('');
        $('#newValue0').val('');
        updatePropertiesAsJsonInNote();

    });


    $('#btnNew').click(function () {
        $('#btnNew').prop("disabled", true);
        //call api
        $.post("./scriptsPHP/generateKey.php", {}).done(function (data) {
            if (data['success'] == 1) {
                alertBox(data['message'], "success");
            }
            else {
                alertBox(data['message'], 'danger');
            }
            kmdLog(data['message']);
            $('#btnNew').prop("disabled", false);

        });
    });



    //Forms

    $("#transactionForm").on("reset", function () {
        $('#fromAssetsField').empty();
        $('#toAssetsField').empty();
        $('#transferType').empty();
    });

    $("#nftForm").on("reset", function () {
        let cbAdd = $('#cbAddress').val();
        $('#newManager').val(cbAdd);
        $('#newReserve').val(cbAdd);
        $('#newFreeze').val(cbAdd);
        $('#newClawback').val(cbAdd);
    });
    

    $("#transactionForm").submit(function (event) {
        event.preventDefault();
        //disable button
        $('#btnSave').prop("disabled", true);
        //start spinner
        $('#spinner').show();

        //get form fields
        var from = $('#from').val();
        var to = $('#to').val();
        var assetId = $('#transferAssetId').val();
        var amount = $('#transferAmount').val();

        //send fields to php
        $.post("./scriptsPHP/transaction.php", { from: from, to: to, assetId: assetId, amount: amount }).done(function (data) {
            if (data['success'] == 1) {
                alertBox(data['message'], "success");
            }
            else {
                alertBox(data['message'], 'danger');
            }

            kmdLog(data['message']);
            //re enable button 
            $('#btnSave').prop("disabled", false);
            //stop spinner
            $('#spinner').hide();

        });

    });

    $("#nftForm").submit(function (event) {
        event.preventDefault();



        //get form fields
        var newStandard = $('input[name=standardRadios]:checked', '#nftForm').val()
        var file_data = $('#newFileUrl').prop('files')[0];
        var newName = $('#newName').val();
        var newUnitName = $('#newUnitName').val();
        var newTotSupply = $('#newTotSupply').val();
        var newDecimals = $('#newDecimals').val();
        var newUrl = $('#newUrl').val();
        var newMetaHash = $('#newMetaHash').val();
        var newDescription = $('#newDescription').val();

        var newProperties = {};
        $('#propertiesDiv .row').each(function () {
            let key = $(this).find('.col .form-group input.newKey').val();
            let val = $(this).find('.col .form-group input.newValue').val();
            if (key !== "" && val !== "")
                newProperties[key] = val;
        });

        var newManager = $('#newManager').val();
        var newReserve = $('#newReserve').val();
        var newFreeze = $('#newFreeze').val();
        var newClawback = $('#newClawback').val();
        var newNote = $('#newNote').val();


        var form_data = new FormData();
        form_data.append('newStandard', newStandard);
        form_data.append('file', file_data);
        form_data.append('newName', newName);
        form_data.append('newUnitName', newUnitName);
        form_data.append('newTotSupply', newTotSupply);
        form_data.append('newDecimals', newDecimals);
        form_data.append('newUrl', newUrl);
        form_data.append('newMetaHash', newMetaHash);
        form_data.append('newDescription', newDescription);
        form_data.append('newProperties', JSON.stringify(newProperties)); //newProperties);
        form_data.append('newManager', newManager);
        form_data.append('newReserve', newReserve);
        form_data.append('newFreeze', newFreeze);
        form_data.append('newClawback', newClawback);
        form_data.append('newNote', newNote);


        if (confirm('Confermi la creazione del nuovo NFT ' + newName + '?')) {

            //disable button
            $('#btnSave2').prop("disabled", true);
            //start spinner
            $('#spinner2').show();

            $.ajax({
                url: './scriptsPHP/createNFT.php',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (response) {
                    if (response.success == 1) {
                        alertBox(response.message, "success", "liveAlertPlaceholder2");
                    }
                    else {
                        alertBox(response.message, "danger", "liveAlertPlaceholder2");
                    }
                    //enable button
                    $('#btnSave2').prop("disabled", false);
                    //stop spinner
                    $('#spinner2').hide();

                },
                error: function (jqXHR, textstatus, errorThrown) {
                    alert("errore ajax " + textstatus + " " + errorThrown);
                    //enable button
                    $('#btnSave2').prop("disabled", false);
                    //stop spinner
                    $('#spinner2').hide();
                }
            });
        }

    });

    //on change of transfer type
    $("#transferType").change(function () {
        $('#transferAssetId').val($(this).val());
    });


    //on change of addresses from and to
    $("#to").change(function () {
        var add = $(this).val();

        function cleanFields() {
            $('#toAlgoField').val("");
            $('#toAwifiField').val("");
            $('#toAssetsField').empty();
        }

        if (add === "") {
            cleanFields();
            return;
        }

        $.ajax({
            url: "https://algoindexer.testnet.algoexplorerapi.io/v2/accounts/" + add,
            success: function (response) {
                $('#toAssetsField').empty();
                $('#toAlgoField').val(response.account.amount)

                response.account.assets.forEach(asset => {
                    if (asset['asset-id'] == awifiAssetId) {
                        $('#toAwifiField').val(asset.amount)
                    }
                    var o = new Option(asset['asset-id'] + " : " + asset.amount, asset['asset-id']);
                    $(o).html(asset['asset-id'] + " : " + asset.amount);
                    $("#toAssetsField").append(o);
                });

            },
            error: function (jqXHR, textstatus, errorThrown) {
                cleanFields();
                kmdLog("Error getting " + add + " Info!");
            }
        });
    });


    $("#from").change(function () {
        var add = $(this).val();

        function cleanFields() {
            $('#fromAlgoField').val("");
            $('#fromAwifiField').val("");
            $('#fromAssetsField').empty();
            $('#transferType').empty();
            $('#transferAssetId').val("");
        }

        if (add === "") {
            cleanFields();
            return;
        }

        $.ajax({
            url: "https://algoindexer.testnet.algoexplorerapi.io/v2/accounts/" + add,
            success: function (response) {
                $('#fromAssetsField').empty();
                $('#transferType').empty();

                $('#fromAlgoField').val(response.account.amount)

                if (response.account.amount > 0) {
                    var o2 = new Option('Algo', 'Algo');
                    $(o2).html('Algo');
                    $("#transferType").append(o2);
                }
                $('#transferAssetId').val("Algo");


                response.account.assets.forEach(asset => {
                    if (asset['asset-id'] == awifiAssetId) {
                        $('#fromAwifiField').val(asset.amount)
                    }
                    var o = new Option(asset['asset-id'] + " : " + asset.amount, asset['asset-id']);
                    $(o).html(asset['asset-id'] + " : " + asset.amount);
                    $("#fromAssetsField").append(o);

                    if (asset.amount > 0) {
                        var o2 = new Option(asset['asset-id'], asset['asset-id']);
                        $(o2).html(asset['asset-id']);
                        $("#transferType").append(o2);
                    }


                });

            },
            error: function (jqXHR, textstatus, errorThrown) {
                cleanFields();
                kmdLog("Error getting " + add + " Info!");
            }
        });
    });

});

function kmdLog(msg) {
    var log = $('#logTxtArea');
    log.val(log.val() + '\n' + msg);
    log.scrollTop(log[0].scrollHeight);
}

function getAccounts() {
    kmdLog('Getting Account List');
    $.ajax({
        url: "scriptsPHP/accountsList.php",
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success == 1) {
                response.response.accounts.forEach(element => {
                    kmdLog(element.address);
                });
                //var str = JSON.stringify(response.response, null, 2)
                //kmdLog(str);
            }
            else {
                kmdLog("ERRORE ");
            }
            kmdLog('End Account List');
        },
        error: function (jqXHR, textstatus, errorThrown) {
            kmdLog("errore ajax " + textstatus + " " + errorThrown);
        }
    });
}

function alertBox(message, type, placeholderID = "liveAlertPlaceholder") {
    var alertPlaceholder = document.getElementById(placeholderID);
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
}