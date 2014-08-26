<?php
if(is_post()){
	//for filename conversion
	$months = [
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December'
	];

	//more month variables
	if(isset($_POST['month'])){
		$month = $_POST['month'];
		$for = " - ".$months[$month];
	}
	
	//start output data
	$output = "Name,Programs\n";
	
	//sql query
	$query = 
	"SELECT
		D.departmentName,
		COUNT(S.sessionId) AS total
	FROM [department] D
	INNER JOIN [event] E ON E.departmentId = D.departmentId
	INNER JOIN [session] S ON S.eventId = E.eventId
	WHERE MONTH(S.sessionStart) = ? 
	GROUP BY D.departmentName 
	ORDER BY D.departmentName ASC";
	
	//result of sql query
	$result = sqlsrv_query($conn, $query, [$month]);
	
	//dump each row to the output data
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$output .= $row['departmentName'].",".$row['total']."\n";
	}
	
	//break a couple lines for the second set of data
	$output .= "\n\nEvent,Date,Department,Theme,Attendance\n";
	
	//sql query
	$query = 
	"SELECT
		E.eventName,
		S.sessionStart,
		D.departmentName,
		T.themeName,
		ISNULL(A.attendance,0) AS attendance
	FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [department] D ON E.departmentId = D.departmentId 
	INNER JOIN [theme] T ON T.themeId = E.themeId
	LEFT JOIN (
		SELECT SS.sessionId AS id, COUNT(SS.studentId) AS attendance
		FROM [studentSession] SS
		GROUP BY SS.sessionId
	) A ON S.sessionId = A.id
	WHERE MONTH(S.sessionStart) = ?
	ORDER BY S.sessionStart";
	
	//result of sql query
	$result = sqlsrv_query($conn, $query, [$month]);
	
	//dump each row to the output data
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$output .= "\"".$row['eventName']."\",".$row['sessionStart'].",".$row['departmentName'].",\"".$row['themeName']."\",".$row['attendance']."\n";
	}
	
	//output to user
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"LINK Monthly Report - ".$for.".csv\"");
	exit($output);
}
?>