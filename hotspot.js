$(document).ready(function () {

    //BUTTONS
    $('#btnCancel').click(function () {
        history.back(1);
    });

    //Forms
    $("#hotspotForm").submit(function (event) 
    {
        event.preventDefault();
        
        //disable button
        $('#btnSave').prop("disabled",true);
        //start spinner
        $('#spinner').show();

        //get form fields
        var hsID = $('#hsIdField').val();
        var locationValue = $('#locationField').val();
        var network = $('#networkField').val();
        var note = $('#noteField').val();
        var validator = $('#validatorField').val();
        var nft = $('#nftField').val();

        //send fields to php
        $.post("./scriptsPHP/editHotspot.php", { hotspotID: hsID, location: locationValue, network: network, note: note, validator: validator, nft : nft }).done(function (data) {
            if (data['success'] == 1) 
            {
                alertBox(data['message'], 'success');
            }
            else 
            {
                alertBox(data['message'], 'danger');
            }
            
            //re enable button 
            $('#btnSave').prop("disabled",false);
            //stop spinner
            $('#spinner').hide();


        });

    });

    $('#changeOwnerForm').submit(function(event){
        event.preventDefault();
        //disable button
        $('#btnSave2').prop("disabled",true);
        //start spinner
        $('#spinner3').show();

        //get form fields
        var senderAddress = $('#ownerAddress').val();
        var receiverAddress = $('#newOwner').val();

        var nftId = $('#nftIdField').val();

        //send fields to php
        $.post("./scriptsPHP/changeNftOwner.php", { from: senderAddress, to: receiverAddress, nftId: nftId}).done(function (data) {
            if (data['success'] == 1) 
            {
                alertBox(data['message'], 'success', 1);
                refreshNftInfo();
            }
            else 
            {
                alertBox(data['message'], 'danger', 1);
            }
            
            //re enable button 
            $('#btnSave2').prop("disabled",false);
            //stop spinner
            $('#spinner3').hide();


        });


    });

    //if there is a message | error as get param, show it.
    var parameterList = new URLSearchParams(window.location.search)
    var receivedMsg = parameterList.get('message');
    var receivedErr = parameterList.get('error');
    //var receivedUserID = parameterList.get('userid');
    if (receivedMsg != null)
    {
        alertBox(receivedMsg,"success");
    }
    if (receivedErr != null)
    {
        alertBox(receivedErr,"danger");
    }

    // //Get nft info
    // var nft = $('#nftField').val();
    // $.ajax({
    //     url: "./scriptsPHP/hotspotInfo.php?nftId="+nft
    // }).then(function(data) 
    // {
    //     $('#nftInfoTextarea').val(JSON.stringify(data, null, 2));
    //     $('#ownerAddress').val(data['owner']);
    //     $('#ownerName').val(data['ownerName']);
    //     $('#spinner2').hide();
    // });
    refreshNftInfo();

});

function refreshNftInfo()
{
    //Get nft info
    var nft = $('#nftField').val();
    $.ajax({
        url: "./scriptsPHP/hotspotInfo.php?nftId="+nft
    }).then(function(data) 
    {
        $('#nftInfoTextarea').val(JSON.stringify(data, null, 2));
        $('#ownerAddress').val(data['owner']);
        $('#ownerName').val(data['ownerName']);
        $('#spinner2').hide();
        //find owner option in select and remove
        $('#'+data['owner']).remove();

    });
}

function alertBox(message, type, alertPlace = 0) 
{
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    if (alertPlace > 0)
        alertPlaceholder = document.getElementById('liveAlertPlaceholder2');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
  }