<?php
	$servername = "localhost";
	$username = "algowifi";//"algoDBUser";
	$password = "Your_pass_db"; // pass for db


	// Create connection
	$conn = new mysqli($servername, $username, $password);

	mysqli_set_charset($conn, "utf8");

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 
	//echo "Connected successfully";
	
	mysqli_select_db($conn, "algowifi"); //algowifi
	
	function apoEsc ($string)
	{
		return str_replace("'","''",$string);
	}

	function generatePassword($length = 8) 
	{
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	    $count = mb_strlen($chars);

	    for ($i = 0, $result = ''; $i < $length; $i++) 
	    {
	        $index = rand(0, $count - 1);
	        $result .= mb_substr($chars, $index, 1);
	    }

	    return $result;
	}
?>
