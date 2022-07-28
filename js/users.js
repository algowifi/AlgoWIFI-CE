$(document).ready( function () 
{
    //Datatables
    var table = $('#example').DataTable({
        "ajax": "./scriptsPHP/usersList.php",
         "columns": [
            { "data": "id" },
            { "data": "type"},
            { "data": "name" },
             { "data": "email" },
             { "data": "assetAmount" },
             { "data": "algoAmount" },
             { "data": "algorandAddress", /*"fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(`<a target='_blank' href='https://testnet.algoexplorer.io/address/${oData.algorandAddress}'>${oData.algorandAddress}</a>`);
                }*/ 
             }
         ],
         "pageLength": 25,
         "columnDefs": [
            {
              targets: 4,
              render: $.fn.dataTable.render.number(',', '.', 4, ''),
              className: 'dt-right'
            },
            {
                targets: 5,
                render: $.fn.dataTable.render.number(',', '.', 3, ''),
                className: 'dt-right'
            }
          ],
          "oLanguage": {
            "sInfo": "Showing _START_ to _END_ of _TOTAL_ users",
            "sInfoEmpty": "No users to show",
            "sEmptyTable": "No users available",
            // "sZeroRecords": "No users to display",
            "sLengthMenu": "Display _MENU_ users",
            "sInfoFiltered": " - filtering from _MAX_ users"
            }
    });

    

    $('#example tbody').on( 'click', 'tr', function () {
        var userID = $(this).children().first().html();
        window.location.href = "user.php?userid="+userID;

    } );

    

     //Buttons
     $('#btnNew').click(function() 
     {
        $("#btnNew").hide();
        $('#insertionForm').show();
        $('#example').parents('div.dataTables_wrapper').first().hide();
        $('#containerTitle').html("Add a new user");
    });      

    function showTableHideForm()
    {
        $("#btnNew").show();
        $('#insertionForm').hide();
        $('#insertionForm').trigger("reset");
        $('#example').parents('div.dataTables_wrapper').first().show();
        $('#containerTitle').html("Users List");

    }

    $('#btnCancel').click(function() 
    {
        showTableHideForm();
        alertBox('Insert Cancelled', 'warning')
    });

    //form submit
    $( "#insertionForm" ).submit(function( event ) {
        event.preventDefault();
         //disable button
         $('#btnSave').prop("disabled",true);
          //start spinner
          $('#spinner').show();

        //get form fields
        var newName = $('#newName').val();
        var newEmail = $('#newEmail').val();
        var newNote = $('#newNote').val();
        var newAddress = $('#newAddress').val();
        
        //get radios
        var isAdmin = $('#newIsAdmin').is(':checked');
        var isLoc = $('#newIsLocation').is(':checked');
        var isPub = $('#newIsPublisher').is(':checked');
        var isHot = $('#newIsHotspotter').is(':checked');


        //send fields to php
        $.post( "./scriptsPHP/addNewUser.php", { newName: newName, newEmail: newEmail, newNote: newNote, newAddress: newAddress, isAdmin : isAdmin, isLocation : isLoc, isPublisher : isPub, isHotspotter : isHot } ).done(function( data ) {
            if (data['success']==1)
            {
                alertBox(data['message'],"success");
                //update datatables
                table.ajax.reload();
                showTableHideForm();
            }
            else 
            {
                alert(data['message']);
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