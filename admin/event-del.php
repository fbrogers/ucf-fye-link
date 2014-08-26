<?php
	if(isset($_POST['event']) && is_numeric($_POST['event'])){
		$query = 'DELETE FROM [event] WHERE (eventId = ?)';
		$params = array($_POST['event']);
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		header("Location: ?id=event");
	}
?>