
$(document).ready( function () 
{
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

        function forgotPw()
		{

			if (document.getElementById("email").value.length == 0)
			{
				alert("Insert your email and push Forgot password again, we'll send you new credentials!");
				$("#email").focus();
			}
			else
			{
				var emailA = $("#email").val();
        $.ajax({
					url:"scriptsPHP/passwordRecovery.php",
					data: {email:emailA},
					success: function(data)
					{
						var response = JSON.parse(data);
						if (response.success == 1)
						{
							alert(response.message);
							$("#email").val('');

						}else
							alert("ERRORE "+ response.message);
					},
					error: function(jqXHR, textstatus, errorThrown)
					{
						alert("errore ajax "+textstatus + " "+errorThrown);
					}
				});
			}
		}


function alertBox(message, type) {
    var alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
  }