<?php
	require_once 'C:\WebDFS\Websites\_phplib\sdestemplate\template_functions_generic.php';
	require_once 'connection.inc.php';
	require_once 'functions.inc.php';

	//check to make sure this is over AJAX
	if(is_ajax()){
		$query = "SELECT * FROM [session] WHERE eventId = ? AND sessionStart < GETDATE() AND DATEDIFF(dd, sessionStart, GETDATE()) < 45";
		$result = sqlsrv_query($conn, $query, [$_POST['event']]) or die(print_r(sqlsrv_errors(),true));
		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			echo '<option value="'.$row['sessionId'].'">'.date('l, F jS, Y @ g:ia', strtotime($row['sessionStart'])).'</option>';
		}
	}
?>