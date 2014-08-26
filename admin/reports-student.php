<?php
	if(is_post()){	
		//determine the semester variable
		$for = $_POST['term'] != NULL ? term_convert($_POST['term']) : 'Full Term';

		//start CSV file output
		$csv = "Last Name,First Name,PID,Email,Points,Events Attended\n";

		//sql query
		$query = 
		"SELECT 
			studentPid,
			studentFirst,
			studentLast,
			studentEmail,
			ISNULL(J.sessionCount,0) AS sessionCount,
			ISNULL(J.pointSum,0) AS pointSum
		FROM [student] S 
		LEFT JOIN (
			SELECT
				studentSession.studentId,
				COUNT(studentSession.sessionId) AS sessionCount,
				SUM(event.eventPoints) AS pointSum
			FROM studentSession
			INNER JOIN [session] ON session.sessionId = studentSession.sessionId
			INNER JOIN [event] ON event.eventId = session.eventId ";

			if(isset($_POST['term'])){
				$query .= "WHERE session.sessionSemester = '".$_POST['term']."' ";
			}

		$query .= 
			"GROUP BY studentId
		) J ON S.studentId = J.studentId 
		ORDER BY studentLast";
		
		$result = sqlsrv_query($conn, $query);
		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$csv .=  $row['studentLast']	.","
					.$row['studentFirst']	.","
					.$row['studentPid']		.","
					.$row['studentEmail']	.","
					.$row['pointSum']		.","
					.$row['sessionCount']	."\n";
		}
		
		//start headers for file output
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"LINK Student Report - ".$for.".csv\"");
		exit($csv);
	}
?>