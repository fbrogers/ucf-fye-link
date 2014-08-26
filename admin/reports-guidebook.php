<?php
	//start CSV file output
	$csv = null.",Session Title,Date,Time Start,Time End,Room/Location,Schedule Track (Optional),Description (Optional)\n";

	//main sql query
	$query = "SELECT 
		E.eventName,
		S.sessionStart,
		S.sessionEnd,
		S.sessionLocation,
		S.sessionRoom,
		T.themeName,
		E.eventDescription	
	FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [theme] T ON E.themeId = T.themeId
	ORDER BY S.sessionStart, S.sessionEnd, E.eventName
	";

	$result = sqlsrv_query($conn, $query);
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){

		$csv .=  null.","
			."\"".str_replace('"', '""', $row['eventName'])."\","
			.date('n/j/y', strtotime($row['sessionStart'])).","
			.date('g:i A', strtotime($row['sessionStart'])).","
			.date('g:i A', strtotime($row['sessionEnd'])).","
			."\"".str_replace('"', '""', $row['sessionLocation'])." ".str_replace('"', '""', $row['sessionRoom'])."\","
			."\"".str_replace('"', '""', $row['themeName'])."\","
			."\"".str_replace('"', '""', $row['eventDescription'])."\"\n";
	}

	//start headers for file output
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"LINK Guidebook Event Export.csv\"");
	exit($csv);
?>