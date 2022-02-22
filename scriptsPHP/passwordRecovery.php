<?php
	include('dbConn.php');
	header('Content-Type: text/html; charset=utf-8');




	if (!isset($_GET['email']))
	{
			echo '{"success":0, "message":"Missing Email address."}';
	}
	else
	{
		$email = $_GET['email'];
		$sql = "SELECT * FROM User WHERE email='".$email."';";
		$result = $conn->query($sql);

		if ($result->num_rows == 1) 
		{
			$user = $result->fetch_assoc();

			//crea nuova password, crea hash e imposta
			$newPassword= generatePassword();
			$newPasswordHash= md5($newPassword);
			$id = $user["id"];
			$sql = "UPDATE User SET password='".$newPasswordHash."' WHERE id='".$id."';";
			if ($conn->query($sql) === TRUE) 
			{
				$name = $user["name"];
		    	$to = $user["email"];
				$subject = 'Password recovery - AlgoWiFi';
				$message = '<center><img src=""  style="width:220px; height:138px;"></img></center><p>Hello '.ucwords($name).', we received a password recovery request for your AlgoWifi account!</p> 
					<p></p>
					<p>Email: '.$email.'</p>
					<p>Password: '.$newPassword.'</p>
					<p>You can change the password anytime after a login <a href="#">here</a> <p>';
				$headers = 'MIME-Version: 1.0' . "\r\n".
					'Content-type: text/html; charset=UTF-8'."\r\n" .
					'From: AlgoWiFi <help@algowifi.com>' . "\r\n" .
					'Reply-To:  AlgoWiFi <help@algowifi.com>' . "\r\n" .
		    		'X-Mailer: PHP/' . phpversion();
				mail($to, $subject, $message, $headers);


		    	echo '{"success":1, "message":"New password sent to '.$email.'"}';
			}	


	    	


		} 
		else 
		{
			echo '{"success":0, "message":"Invalid Account! E-mail '.$email.' unknown"}';
		}
	}
	
	$conn->close();

?>