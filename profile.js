$(document).ready( function () 
{

    //Buttons    
   $('#btnCancel').click(function() 
   {
        $('#changePwForm').trigger("reset");
   });


   //passwords validation
   function validatePasswords()
   {
        var pw1 = $('#newPw').val();
        var pw2 = $('#newPw2').val();
        var confirm_password = document.getElementById("newPw2");
        if (pw1 != pw2)
        {
            $('#newPw2').get(0).setCustomValidity("Passwords Don't Match");
        } 
        else 
        {
            $('#newPw2').get(0).setCustomValidity('');
        }
   }
   $('#newPw').change(validatePasswords);
   $('#newPw2').keyup(validatePasswords);

    //form submit
    $( "#changePwForm" ).submit(function( event ) {
        event.preventDefault();
         //disable button
         $('#btnSave').prop("disabled",true);
       

        //get form fields
        var pw1 = $('#newPw').val();    

        //start spinner
        $('#spinner').show();
        
        //send fields to php
        $.post( "./scriptsPHP/changeOwnPw.php", { p: pw1 } ).done(function( data ) {
            if (data['success']==1)
            {
                alertBox(data['message'],"success");
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

});

function alertBox(message, type) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
  }