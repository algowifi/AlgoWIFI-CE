

$(document).ready(function () {

    var awifiAssetId = $('#awifiAssetId').val();

    getAccounts();

    //Buttons
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


    $("form").on("reset", function () {
        $('#fromAssetsField').empty();
        $('#toAssetsField').empty();
        $('#transferType').empty();
    });

    $("form").submit(function (event) {
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

function alertBox(message, type) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
}