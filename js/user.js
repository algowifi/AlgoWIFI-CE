
$.fn.dataTable.ext.order['dom-checkmark'] = function  ( settings, col )
{
    return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
        return $('i', td).hasClass('bxs-check-circle') ? '1' : '0';
    } );
}

$(document).ready(function () {

var ownerAddress = $('#algorandAddressField').val();
var ownerId = $('#userIdField').val();

//Datatables
var hotspotsTable = $('#hotspotsTable').DataTable({
    "ajax": "./scriptsPHP/hotspotterHotspots.php?a="+ownerAddress,
     "columns": [
        { "data": "nft", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
            $(nTd).html(`<a target='_blank' data-toggle='tooltip' data-placement='top' title='View on algoExplorer' href='https://testnet.algoexplorer.io/asset/${oData.nft}'>${oData.nft}</a>`);
        }
        },
        { "data": "location" , "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
            $(nTd).html(`<a target='_blank' data-toggle='tooltip' data-placement='top' title='Open hotspot page' href='./hotspot.php?hotid=${oData.id}'>${oData.location}</a>`);
            } },
        { "data": "totViews" },
        { "data": "assetAmount" },
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
        }
      ],
      "initComplete": function(settings, json) {  
        
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

var simple_checkmark = function (data, type, full, meta){
    var isOn = data == true ? "<i class='bx bxs-check-circle bx-lg' style='color:#16d422' checked ></i>" : "<i class='bx bxs-x-circle bx-lg' style='color:#e4300d'  ></i>";
    return isOn;
}

var campaignsTable = $('#campaignsTable').DataTable({
    "ajax": "./scriptsPHP/campaignList.php?uid="+ownerId,
     "columns": [
        { "data": "name" , "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
            $(nTd).html(`<a target='_blank' data-toggle='tooltip' data-placement='top' title='Open campaign page' href='./campaign.php?cid=${oData.id}'>${oData.name}</a>`);
            }},
         { "data": "imageUrl", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
            $(nTd).html(`<img src='${oData.imageUrl}' width='80' height='80' />`);
            } },
         { "data": "isActive" , "render": simple_checkmark, "orderDataType": "dom-checkmark"},
         { "data": "creation" },
     ],
     "order": [[ 2, 'desc' ], [ 3, 'asc' ]],
     "columnDefs": [
        {
          targets: 1,
          className: 'dt-center'
        },
        {
            targets: 2,
            className: 'dt-center'
        }
      ],
      "initComplete": function(settings, json) {  
        
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
        
      },
      "oLanguage": {
        "sInfo": "Showing _START_ to _END_ of _TOTAL_ campaigns",
        "sInfoEmpty": "No campaigns to show",
        "sEmptyTable": "No campaigns available",
        // "sZeroRecords": "No campaigns to display",
        "sLengthMenu": "Display _MENU_ campaigns",
        "sInfoFiltered": " - filtering from _MAX_ campaigns"
        }
});



    //BUTTONS
    $('#btnCancel').click(function () {
        history.back(1);
    });

    $('#btnRemove').click(function () 
    {
        var userID = $('#userIdField').val();
        var name = $('#nameField').val();

        if (confirm("Are you sure to delete user "+name+"?") == true) 
        {
            $.post("./scriptsPHP/removeUser.php", { userID: userID}).done(function (data) {
                if (data['success'] == 1) 
                {
                    //redirect to users.php with message
                    window.location.href = './users.php?message='+data['message'];
                }
                else                    
                {
                    alert(data['message']);
                }
            });
        } 
        
    });

     //Buttons
     $('#btnNew').click(function() 
     {
        $("#btnNew").hide();
        $('#addAwifiForm').show();
        $('#userForm').hide();
        $('#containerTitle').html("Transfer AWIFI to this user");
    });      

    function showUserForm()
    {
        $("#btnNew").show();
        $('#addAwifiForm').hide();
        $('#addAwifiForm').trigger("reset");
        $('#userForm').show();
        $('#containerTitle').html("");
    }

    $('#btnCancelTransfer').click(function() 
    {
        showUserForm();
    });

    //form submit
    $("#addAwifiForm").submit(function (event) 
    {
        event.preventDefault();
        //disable button
        $('#btnSaveTransfer').prop("disabled",true);
        //start spinner
        $('#spinner2').show();

        //get form fields
        var userID = $('#algorandAddressField').val();
        var amount = $('#amount').val();

        //send fields to php
        $.post("./scriptsPHP/transferAwifi.php", { userID: userID, amount:amount }).done(function (data) {
            if (data['success'] == 1) 
            {
                showUserForm();
                alertBox(data['message'], 'success');

            }
            else 
            {
                alertBox(data['message'], 'danger');
            }
            //re enable button 
            $('#btnSaveTransfer').prop("disabled",false);
            //stop spinner
            $('#spinner2').hide();

        });

    });

    $("#userForm").submit(function (event) 
    {
        event.preventDefault();
        
        //disable button
        $('#btnSave').prop("disabled",true);
        //start spinner
        $('#spinner').show();

        //get form fields
        var userID = $('#userIdField').val();
        var name = $('#nameField').val();
        var email = $('#emailField').val();
        var note = $('#noteField').val();
        var address = $('#addressField').val();
        var algorandAddress = $('#algorandAddressField').val();
        var nft = $('#nftField').val();
        var password = $('#passwordField').val();
        var isEnabled = $('#isEnabledField').is(":checked");
        var isAdmin = $('#isAdminField').is(":checked");

        //send fields to php
        $.post("./scriptsPHP/editUser.php", { userID: userID, name: name, email: email, note: note,
        address: address, algorandAddress: algorandAddress, nft: nft, password:password, isEnabled:isEnabled, isAdmin:isAdmin }).done(function (data) {
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

});

function alertBox(message, type) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
  }