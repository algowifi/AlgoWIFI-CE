
$.fn.dataTable.ext.order['dom-checkmark'] = function  ( settings, col )
{
    return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
        return $('i', td).hasClass('bxs-check-circle') ? '1' : '0';
    } );
}

$(document).ready( function () 
{

    var simple_checkmark = function (data, type, full, meta){
        var isOn = data == true ? "<i class='bx bxs-check-circle bx-lg' style='color:#16d422' checked ></i>" : "<i class='bx bxs-x-circle bx-lg' style='color:#e4300d'  ></i>";
        return isOn;
    }

     //Datatables
     var table = $('#example').DataTable({
        "ajax": "./scriptsPHP/campaignList.php",
         "columns": [
            { "data": "id" },
            { "data": "name" },
             { "data": "imageUrl", "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(`<img src='${oData.imageUrl}' width='80' height='80' />`);
                } },
             { "data": "isActive" , "render": simple_checkmark, "orderDataType": "dom-checkmark"},
             { "data": "creation" },
         ],
         "order": [[ 3, 'desc' ], [ 4, 'asc' ]],
         "columnDefs": [
            {
              targets: 2,
              className: 'dt-center'
            },
            {
                targets: 3,
                className: 'dt-center'
            }
          ],
         "oLanguage": {
            "sInfo": "Showing _START_ to _END_ of _TOTAL_ campaigns",
            "sInfoEmpty": "No campaigns to show",
            "sEmptyTable": "No campaigns available",
            // "sZeroRecords": "No campaigns to display",
            "sLengthMenu": "Display _MENU_ campaigns",
            "sInfoFiltered": " - filtering from _MAX_ campaigns"
            }
    });

    $('#example tbody').on( 'click', 'tr', function () {
        var cid = $(this).children().first().html();
        window.location.href = "campaign.php?cid="+cid;

    } );

    //Buttons
    $('#btnNew').click(function() 
    {
       $("#btnNew").hide();
       $('#insertionForm').show();
       $('#example').parents('div.dataTables_wrapper').first().hide();
       $('#containerTitle').html("Add new campaign");
   });      

   function showTableHideForm()
   {
       $("#btnNew").show();
       $('#insertionForm').hide();
       $('#insertionForm').trigger("reset");
       $('#example').parents('div.dataTables_wrapper').first().show();
       $('#containerTitle').html("Campaigns List");
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
        var newDescription = $('#newDescription').val();
        var newLandingUrl = $('#newLandingUrl').val();
        var newHotspots = $('#newHotspots').val();
        var file_data = $('#newImageUrl').prop('files')[0];   
        var form_data = new FormData();                  
        form_data.append('newName', newName);
        form_data.append('newDescription', newDescription);
        form_data.append('newLandingUrl', newLandingUrl);
        form_data.append('newHotspots', newHotspots);
        form_data.append('file', file_data);
        //alert(" hotspots: " + newHotspots);                             


        $.ajax({
            url: './scriptsPHP/addNewCampaign.php',
            dataType: 'json', 
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(response){
				if (response.success == 1)
                {
                    alertBox('Campaign Saved!', 'success');
                    table.ajax.reload();
                    showTableHideForm();
                }
                else 
                {
                    alertBox('Error saving Campaign! ', 'danger');
                }
                //enable button
                $('#btnSave').prop("disabled",false);
                //stop spinner
                $('#spinner').hide();

            },
            error: function(jqXHR, textstatus, errorThrown)
		    {
				alert("errore ajax "+textstatus + " "+errorThrown);
                //enable button
                $('#btnSave').prop("disabled",false);
                //stop spinner
                $('#spinner').hide();
			}
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