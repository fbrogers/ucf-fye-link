<?php
	require_once 'C:\WebDFS\Websites\_phplib\sdestemplate\template_functions_generic.php';
	require_once 'connection.inc.php';
	require_once 'functions.inc.php';

	//check to make sure this is over AJAX
	if(!is_ajax()){
		die('Must be submitted over AJAX');
	}

	$query = "SELECT eventId FROM [event] WHERE eventName = ?";
	$params = [$_POST['event_name']];
	$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(),true));
	if(sqlsrv_has_rows($result)){
		die('This event name is already taken. Please choose another.');
	}
?>