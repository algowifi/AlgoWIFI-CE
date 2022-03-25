$(document).ready(function () {

    var cID = $('#campaignIdField').val();
    var isAdmin = $('#isAdm').val() == 1;

    //Datatables
    var table = $('#example').DataTable({
        "ajax": "./scriptsPHP/campaignHotspotsList.php?cid=" + cID,
        "columns": [
            {
                "data": "nft", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(`<a target='_blank' data-toggle='tooltip' data-placement='top' title='View on algoExplorer' href='https://testnet.algoexplorer.io/asset/${oData.nft}'>${oData.nft}</a>`);
                }
            },
            {
                "data": "location", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (!isAdmin)
                        $(nTd).html(oData.location);
                    else
                        $(nTd).html(`<a target='_blank' data-toggle='tooltip' data-placement='top' title='Open hotspot page' href='./hotspot.php?hotid=${oData.id}'>${oData.location}</a>`);
                }
            },
            { "data": "totViews" },
            { "data": "assetAmount" },
            {
                "data": "id", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    var removeBtnId = 'removeHotspot' + oData.id;
                    if (isAdmin)
                        $(nTd).html('<i id="' + removeBtnId + '" class="bx bx-x-circle removeHotspotButton" style="color:#F90707" data-toggle="tooltip" data-placement="top" title="Remove this campaign from this Hotspot"></i>');
                    else
                        $(nTd).html('<i class="bx bx-x-circle removeHotspotButton" style="color:#F90707" ></i>');
                }
            }
        ],
        "columnDefs": [
            {
                targets: 2,
                "className": 'dt-center'
            },
            {
                targets: 3,
                render: $.fn.dataTable.render.number(',', '.', 4, ''),
                "className": 'dt-right'
            },
            {
                "targets": [4],
                "visible": isAdmin,
                "className": 'dt-center'
            }
        ],
        "initComplete": function (settings, json) {
            if (isAdmin)
                addRemoveListener();

            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })

        },
        "oLanguage": {
            "sInfo": "Showing _START_ to _END_ of _TOTAL_ hotspots",
            "sInfoEmpty": "No hotspots to show",
            "sEmptyTable": "No hotspots available",
            // "sZeroRecords": "No hotspots to display",
            "sLengthMenu": "Display _MENU_ hotspots",
            "sInfoFiltered": " - filtering from _MAX_ hotspots"
        }
    });

    //BUTTONS
    $('#btnDisable').click(function () {
        if (confirm("Are you sure to disable this Campaign? It can be enabled again only by administrators!") == true) {
            var cID = $('#campaignIdField').val();
            var uID = $('#campaignOwnerIdField').val();
            $.post("./scriptsPHP/disableCampaign.php", { cid: cID, uid: uID }).done(function (data) {
                if (data['success'] == 1) {
                    alertBox('Campaign disabled!', 'success');
                    $('#isActiveField').prop("checked", false);
                    $('#btnDisable').remove();
                }
                else {
                    alertBox(data['message'], 'danger');
                }
            });
        }
    });

    $('#btnRemove').click(function () {
        if (confirm("Are you sure to completly delete this Campaign?") == true) {
            var cID = $('#campaignIdField').val();
            var uID = $('#campaignOwnerIdField').val();
            $.post("./scriptsPHP/removeCampaign.php", { cid: cID, uid: uID }).done(function (data) {
                if (data['success'] == 1) {
                    //go back with message campaign removed
                    window.location.href = "campaigns.php?message=Campaign removed!";
                }
                else {
                    alertBox(data['message'], 'danger');
                }
            });
        }
    });

    $('#btnCancel').click(function () {
        history.back(1);
    });

    $('#btnCancel2').click(function () {
        $('#insertionForm').hide();
        $('#insertionForm').trigger('reset');

    });

    $('#btnNew').click(function () {
        $('#insertionForm').show();

    });

    addRemoveListener();

    //Forms
    $("#campaignForm").submit(function (event) {
        event.preventDefault();

        //disable button
        $('#btnSave').prop("disabled", true);
        //start spinner
        $('#spinner').show();

        //get form fields
        var cID = $('#campaignIdField').val();
        var newName = $('#nameField').val();
        var newDesc = $('#descriptionField').val();
        var newImg = $('#imageUrlField').val();
        var newLanding = $('#landingUrlField').val();
        var newIsActive = $('#isActiveField').is(":checked");

        var file_data = $('#newImageUrl').prop('files')[0];

        var form_data = new FormData();
        form_data.append('cid', cID);
        form_data.append('newName', newName);
        form_data.append('newDesc', newDesc);
        form_data.append('newLanding', newLanding);
        form_data.append('newIsActive', newIsActive);
        form_data.append('newImg', newImg);

        if (file_data != undefined) {
            //change the image, perform new file update
            form_data.append('newFile', file_data);
        }

        //send fields to php
        //v.2 
        $.ajax({
            url: './scriptsPHP/editCampaign.php',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                if (response.success == 1) {
                    alertBox(response.message, 'success');
                }
                else {
                    alertBox(response.message, 'danger');
                }
                //enable button
                $('#btnSave').prop("disabled", false);
                //stop spinner
                $('#spinner').hide();

            },
            error: function (jqXHR, textstatus, errorThrown) {
                alert("errore ajax " + textstatus + " " + errorThrown);
                //enable button
                $('#btnSave').prop("disabled", false);
                //stop spinner
                $('#spinner').hide();
            }
        });





        // //send fields to php 
        // //v.1 working but without file upload
        // $.post("./scriptsPHP/editCampaign.php", { cid: cID, newName: newName, newDesc: newDesc, newImg: newImg, newLanding: newLanding, newIsActive : newIsActive }).done(function (data) {
        //     if (data['success'] == 1) 
        //     {
        //         alertBox(data['message'], 'success');
        //     }
        //     else 
        //     {
        //         alertBox(data['message'], 'danger');
        //     }
        //     //re enable button 
        //     $('#btnSave').prop("disabled",false);
        //     //stop spinner
        //     $('#spinner').hide();

        // });

    });

    $("#insertionForm").submit(function (event) {
        event.preventDefault();

        //disable button
        $('#btnSave2').prop("disabled", true);
        //start spinner
        $('#spinner2').show();

        //get form fields
        var cID = $('#campaignIdField').val();
        var newHotspots = $('#newHotspots').val();

        var form_data = new FormData();
        form_data.append('cid', cID);
        form_data.append('newHotspots', newHotspots);

        //send fields to php
        //v.2 
        $.ajax({
            url: './scriptsPHP/addHotspotsToCampaign.php',
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                if (response.success == 1) {
                    alertBox(response.message, 'success');
                    //reset and hide form
                    $('#insertionForm').hide();
                    $('#insertionForm').trigger('reset');
                }
                else {
                    alertBox(response.message, 'danger');
                }
                //enable button
                $('#btnSave2').prop("disabled", false);
                //stop spinner
                $('#spinner2').hide();
                //reload datatable
                table.ajax.reload();

            },
            error: function (jqXHR, textstatus, errorThrown) {
                alert("errore ajax " + textstatus + " " + errorThrown);
                //enable button
                $('#btnSave2').prop("disabled", false);
                //stop spinner
                $('#spinner2').hide();
            }
        });

    });



    //if there is a message | error as get param, show it.
    var parameterList = new URLSearchParams(window.location.search)
    var receivedMsg = parameterList.get('message');
    var receivedErr = parameterList.get('error');
    //var receivedUserID = parameterList.get('userid');
    if (receivedMsg != null) {
        alertBox(receivedMsg, "success");
    }
    if (receivedErr != null) {
        alertBox(receivedErr, "danger");
    }

    refreshMetrics();

    function addRemoveListener() {
        $('.removeHotspotButton').click(function () {

            var hsId = $(this).attr('id').replace('removeHotspot', '');
            var cID = $('#campaignIdField').val();
            var hsNft = $(this).parent().parent().children(":first").children(":first").html();

            if (confirm("Do you want to remove this campaign from the hotspot " + hsNft + "?") == true) {
                //send request to php
                $.post("./scriptsPHP/removeHotspot_Campaign.php", { hid: hsId, cid: cID }).done(function (data) {
                    if (data['success'] == 1) {
                        alertBox('Hotspot removed!', 'success');
                        table.ajax.reload();
                    }
                    else {
                        alertBox(data['message'], 'danger');
                    }
                });
            }
        });
    }

});



function alertBox(message, type, alertPlace = 0) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    if (alertPlace > 0)
        alertPlaceholder = document.getElementById('liveAlertPlaceholder2');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
}

function refreshMetrics() {
    var cID = $('#campaignIdField').val();

    $.post("./scriptsPHP/metrics.php", { cid: cID }).done(function (data) {
        if (data['success'] == 1) {
            if (data['metrics']['totViews'] != undefined) {
                $('#totViews').html(data['metrics']['totViews']);
            }
            if (data['metrics']['spentMicroAWIFI'] != undefined) {
                $('#spentAWIFI').html((data['metrics']['spentMicroAWIFI'] / 10000).toFixed(4));
            }
        }
        else {
            // alertBox(data['message'], 'danger');
        }

    });

}


