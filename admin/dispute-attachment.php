<?php 
	if(isset($_GET['xid']) && is_numeric($_GET['xid'])){
		$dispute = $_GET['xid'];

		//get back the first picture
		$query = "SELECT TOP 1 disputeAttachmentExt, disputeAttachment FROM [dispute] WHERE [disputeId] = ?";
		$result = sqlsrv_query($conn, $query, [$dispute]) or die(print_r( sqlsrv_errors(), true));
		sqlsrv_fetch($result) or die(print_r( sqlsrv_errors(), true));
		$ext = sqlsrv_get_field($result, 0);
		$image = sqlsrv_get_field($result, 1, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY));

		//return file data
		if($ext == 'pdf'){
			header("Content-Disposition: inline; filename=dispute-attachment-".$dispute.'.'.$ext);
			header("Content-Type: application/pdf");
		} else {
			header("Content-Disposition: attachment; filename=dispute-attachment-".$dispute.'.'.$ext);
			header("Content-Type: application/octet-stream");
		}

		//dump file data to client
		fpassthru($image);
		die();
	}
?>