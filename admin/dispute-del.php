<?php
	if(isset($_POST['dispute']) && is_numeric($_POST['dispute'])){
		$query = "DELETE FROM [dispute] WHERE disputeId = ?";
		$result = sqlsrv_query($conn, $query, [$_POST['dispute']]) or die(print_r(sqlsrv_errors(), true));
		header("Location: ?id=dispute");
	}
?>