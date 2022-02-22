$(document).ready( function () 
{
    //Datatables
    var table = $('#example').DataTable({
        "ajax": "./scriptsPHP/hotspotsList.php",
        "responsive": true,
         "columns": [
            { "data": "id" },
            { "data": "nft" },
            { "data": "networkName" },
            { "data": "location" },
            { "data": "ownerName" },
             { "data": "note" },
         ],
         "oLanguage": {
            "sInfo": "Showing _START_ to _END_ of _TOTAL_ hotspots",
            "sInfoEmpty": "No hotspots to show",
            "sEmptyTable": "No hotspots available",
            // "sZeroRecords": "No hotspots to display",
            "sLengthMenu": "Display _MENU_ hotspots",
            "sInfoFiltered": " - filtering from _MAX_ hotspots"
            }
    });



    if (true)//($('#isAdm').val()==1)
    {
        $('#example tbody').on( 'click', 'tr', function () {
            var hsid = $(this).children().first().html();
            window.location.href = "hotspot.php?hotid="+hsid;
    
        } );
    }

    

    //Buttons
    $('#btnNew').click(function() 
    {
       $("#btnNew").hide();
       $('#insertionForm').show();
       $('#example').parents('div.dataTables_wrapper').first().hide();
       $('#containerTitle').html("Add new hotspot");
   });      

   function showTableHideForm()
   {
       $("#btnNew").show();
       $('#insertionForm').hide();
       $('#insertionForm').trigger("reset");
       $('#example').parents('div.dataTables_wrapper').first().show();
       $('#containerTitle').html("Hotspots List");
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
        var newLocation = $('#newLocation').val();
        var newNetwork = $('#newNetwork').val();
        var newNote = $('#newNote').val();
        var newValidator = $('#newValidator').val();
        var newOwner = $('#newOwner').val();
        
        //send fields to php
        $.post( "./scriptsPHP/addNewHotspot.php", { newLocation: newLocation, newNetwork: newNetwork, newNote: newNote, newValidator : newValidator, newOwner: newOwner } ).done(function( data ) {
            if (data['success']==1)
            {
                alertBox(data['message'],"success");
                //update datatables
                table.ajax.reload();
                showTableHideForm();
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



    //test algoexplorer api
    // $.ajax({
    //     url: "https://algoindexer.testnet.algoexplorerapi.io/v2/assets?asset-id=67967557"
    // }).then(function(data) {
    //    alert("ok "+data);
    // });

});

function alertBox(message, type) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
  }