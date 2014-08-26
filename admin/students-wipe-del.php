<?php
	session_start();
	require_once 'C:\WebDFS\Websites\_phplib\sdestemplate\template_functions_generic.php';
	require_once 'includes/connection.inc.php';
	require_once 'includes/functions.inc.php';

	//check to make sure this is over AJAX
	if(is_ajax()){

		//if the XSRF token is the same
		if($_POST['token'] == $_SESSION['token']){

			//if the SDES logins match
			if(isset($_POST['insurance']) && $_POST['authname'] == "SDES\\".$_POST['insurance']){
				
				//ATTEMPT TO WIPE ALL STUDENT DATA.
				$query = "DELETE FROM [student]";
				$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(),true));
				echo 'Students wiped sucessfully.';

			} else {
				echo 'Incorrect username.';	
			} 
		} else {
			echo 'Please use the appropriate page to submit this request.';	
		} 
	} else {
		echo 'This page must be accessed with an AJAX object. Please use the appropriate page to submit this request.';
	}
?>