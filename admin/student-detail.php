<?php
	$title = 'Student Details';

	if(isset($_GET['student']) && is_numeric($_GET['student'])){
		$student = $_GET['student'];
		
		//student data
		$query = 
		"SELECT TOP 1
			S.studentId,
			S.studentFirst,
			S.studentLast,
			S.studentPid,
			S.studentEmail,
			ISNULL(C.sumPoints, 0) AS sumPoints,
			ISNULL(B.sprPoints, 0) AS sprPoints,
			(ISNULL(B.sprPoints, 0) + ISNULL(C.sumPoints, 0)) AS total
		FROM 
			[student] S
		LEFT JOIN (
			SELECT student.studentId, SUM(event.eventPoints) AS sprPoints
			FROM [student]
			INNER JOIN [studentSession] ON studentSession.studentId = student.studentId
			INNER JOIN [session] ON session.sessionId = studentSession.sessionId
			INNER JOIN [event] ON event.eventId = session.eventId
			WHERE session.sessionSemester = 'spr'
			GROUP BY student.studentId
		) B ON S.studentId = B.studentId
		LEFT JOIN (
			SELECT student.studentId, SUM(event.eventPoints) AS sumPoints
			FROM [student]
			INNER JOIN [studentSession] ON studentSession.studentId = student.studentId
			INNER JOIN [session] ON session.sessionId = studentSession.sessionId
			INNER JOIN [event] ON event.eventId = session.eventId
			WHERE session.sessionSemester = 'sum'
			GROUP BY student.studentId
		) C ON S.studentId = C.studentId
		WHERE (S.studentId = ?)";
		
		$params = [$_GET['student']];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r( sqlsrv_errors(), true));
		$data = sqlsrv_fetch_array($result);
		
		//events associated with the student
		$query = "SELECT S.sessionId, eventName, eventPoints, sessionStart, sessionEnd, sessionLocation, departmentName
		FROM [event] E
		INNER JOIN [department] D ON E.departmentId = D.departmentId
		INNER JOIN [session] S ON E.eventId = S.eventId
		INNER JOIN [studentSession] SS ON S.sessionId = SS.sessionId
		WHERE (SS.studentId = ?)
		ORDER BY sessionStart DESC";
		
		$result = sqlsrv_query($conn, $query, [$student]);
		$events = [];
		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$events[] = $row;
		}
	} else {
		header("Location: ?id=students");	
	}
?>

<table class="grid">
	<tr>
		<th scope="col">Name</th>
		<th scope="col">Email</th>
		<th scope="col">PID</th>
		<th scope="col">Fall &amp; Spring</th>
		<th scope="col">Summer</th>
		<th scope="col">Total</th>
	</tr>
	<tr>
		<th scope="row"><?= $data['studentLast'] ?>, <?= $data['studentFirst'] ?></th>
		<td><a href="mailto:<?= $data['studentEmail'] ?>"><?= $data['studentEmail'] ?></a></td>
		<td><?= $data['studentPid'] ?></td>
		<td><?= $data['sprPoints'] ?></td>
		<td><?= $data['sumPoints'] ?></td>
		<td><?= $data['total'] ?></td>
	</tr>
</table>

<h2 class="header">Event Attendance</h2>
<table class="grid smaller">
	<tr>
		<th scope="col">Name</th>
		<th scope="col">Dept</th>
		<th scope="col">Points</th>
		<th scope="col">Date &amp; Time</th>
		<th scope="col">Location</th>
		<th scope="col"></th>
	</tr>
	<?php foreach($events as $row): ?>
	<tr>
		<th scope="row"><?= $row['eventName'] ?></th>
		<td><?= $row['departmentName'] ?></td>
		<td><?= $row['eventPoints'] ?></td>
		<td><?= smalldate($row['sessionStart'], $row['sessionEnd']) ?></td>
		<td><?= $row['sessionLocation'] ?></td>
		<td>
			<form action="?id=session-attend" method="post">
				<input type="image" src="https://assets.sdes.ucf.edu/images/user_delete.png" title="Delete Attendance Record" class="icon" />
				<input type="hidden" value="<?= $row['sessionId'] ?>" name="session" />
				<input type="hidden" value="<?= $student ?>" name="student" />
				<input type="hidden" name="action" value="student-delete" />
			</form>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

