<?php
	$title = 'Students';
	$links = [
		'Add Student' => '?id=students-add',
		'Upload FTICs' => '?id=students-csv',
		'Wipe Student Data' => '?id=students-wipe'
	];

	//set basic variables
	$sortable = get_trm(date('D, d M Y H:i:s'));
	$name = term_convert($sortable);
	$filter = isset($_GET['filter']) ? $_GET['filter'] : NULL;

	//filter out non alphanumerics
	if(!preg_match("/^[a-zA-Z0-9_]*$/", $filter)){
		die('Filter contains non-valid characters.');
	}
	
	//get all students and all of their points
	$query = 
	"SELECT TOP 500
		S.studentId,
		S.studentFirst,
		S.studentLast,
		S.studentPid, 
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
	WHERE (S.[studentFirst] LIKE '%{$filter}%') OR (S.[studentLast] LIKE '%{$filter}%') 
	OR (S.[studentCard] LIKE '%{$filter}%')
	OR (S.[studentPid] LIKE '%{$filter}%')
	OR (S.studentEmail LIKE '%{$filter}%')
	ORDER BY [{$sortable}Points] DESC, S.studentLast ASC";
	
	//run query
	$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));
	
	//dump results
	$students = [];
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$students[] = $row;
	}
?>

<div class="floatright">
	<form name="students" method="get" action="">
		<input type="hidden" name="id" id="filter" value="students" />
		<input type="text" name="filter" id="filter" />
		<input type="submit" value="Filter" />
	</form>
</div>

<table class="grid">
	<caption>Top 500 - Sorted by <?= $name ?></caption>
	<tr>
		<th scope="col"></th>
		<th scope="col">Name</th>
		<th scope="col">PID</th>
		<th scope="col">Fall &amp; Spring Points</th>
		<th scope="col">Summer Points</th>
		<th scope="col">Total Points</th>
		<th scope="col"></th>
	</tr>
	<?php foreach($students as $i => $row): ?>
	<tr>
		<th scope="row"><?= ++$i ?></th>
		<td>
			<a href="?id=student-detail&amp;student=<?= $row['studentId'] ?>">
				<?= $row['studentLast'] ?>, <?= $row['studentFirst'] ?>
			</a>
		</td>
		<td><?= $row['studentPid'] ?></td>
		<td><?= $row['sprPoints'] ?></td>
		<td><?= $row['sumPoints'] ?></td>
		<td><?= $row['total'] ?></td>
		<td>
			<a href="?id=students-edit&amp;student=<?= $row['studentId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_page_white_edit.png" alt="icon" class="icon" />
			</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>