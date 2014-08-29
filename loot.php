<?php $title = 'My LINK Loot'; 

if(is_post() && isset($_POST['pid']) && is_numeric($_POST['pid'])){
	//includes
	require_once 'includes/connection.inc.php'; 
	require_once 'includes/functions.inc.php';

	//get semester
	$term = get_trm(date('D, d M Y H:i:s'));
	
	//save out studentPid
	$pid = $_POST['pid'];
	
	//first sql query: get student points
	$query = "SELECT TOP 1 ISNULL(A.points, 0) AS points, studentId
	FROM [student] S
	LEFT JOIN (
		SELECT student.studentPid, SUM(event.eventPoints) AS points
		FROM [student]
		INNER JOIN [studentSession] ON studentSession.studentId = student.studentId
		INNER JOIN [session] ON session.sessionId = studentSession.sessionId
		INNER JOIN [event] ON event.eventId = session.eventId
		WHERE session.sessionSemester = ?
		GROUP BY student.studentPid
	) A ON S.studentPid = A.studentPid
	WHERE (S.studentPid = ?)";

	//commit query
	$result = sqlsrv_query($conn, $query, [$term, $pid]) or die(print_r(sqlsrv_errors(), true));
	if(!sqlsrv_has_rows($result)){
		header("Location: invalid");
		exit();
	}
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
	
	//assign variables
	$points = $row['points'];
	$student = $row['studentId'];
	
	//second sql query: get student events for the current semester
	$query = "SELECT eventName, eventPoints, eventDescription,
	sessionStart, sessionEnd, sessionLocation 
	FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [studentSession] SS ON SS.sessionId = S.sessionId
	WHERE (SS.studentId = ?) ORDER BY sessionStart";

	//sql query
	$result = sqlsrv_query($conn, $query, [$student]) or die(print_r(sqlsrv_errors(), true));
	$sessions = [];
	while($row = sqlsrv_fetch_array($result)){
		$sessions[] = $row;
	} ?>

	<div class="left">
		<p>
			Welcome to your LINK Loot page! You currently have 
			<strong class="larger"><?= $points; ?></strong> LINK points this semester.
			Listed below are all of the events we have on record for you. If you have attended
			an event more than five business days ago and it is not listed below, you can file a 
			<a href="dispute/<?= $pid ?>">LINK Dispute by clicking here</a> or clicking on the
			"Dispute" button to the side.
		</p>
	</div>
	<div class="sidebar-right">
		<a href="dispute/<?= $pid ?>">
			<img src="images/dispute.png" class="round" alt="dispute button" title="Dispute" />
		</a>
	</div>

	<div class="hr-blank"></div>
	
	<table class="grid smaller">
		<tr>
			<th scope="col">Program</th>
			<th scope="col" style="width: 300px;">Description</th>
			<th scope="col">Points</th>
			<th scope="col">Time</th>
			<th scope="col">Location</th>
		</tr>
		<?php foreach($sessions as $row): ?>
		<tr>
			<th scope="row"><?= $row['eventName'] ?></th>
			<td><?= $row['eventDescription'] ?></td>
			<td><?= $row['eventPoints'] ?></td>
			<td><?= smalldate($row['sessionStart'], $row['sessionEnd']) ?></td>
			<td><?= $row['sessionLocation'] ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

<?php } else { ?>

	<p class="center">Enter your UCFID below to check your LINK Loot points and events.</p>

	<div class="center">
		<form action="" method="post" id="form" class="fieldset">
			<input type="text" name="pid" maxlength="7" />
			<input type="submit" name="submit" value="Submit" />
		</form>
	</div>

<?php } ?>