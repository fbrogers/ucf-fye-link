<?php
	if(isset($_POST['dept']) && is_numeric($_POST['dept'])){
		$query = "DELETE FROM [department] WHERE (departmentId = ?)";
		$result = sqlsrv_query($conn, $query, [$_POST['dept']]) or die(print_r(sqlsrv_errors(), true));
		header("Location: ?id=departments");
	}
?>