<?php 
	$title = 'Sessions Pending Approval'; 
	
	$query = "SELECT eventName, departmentName, themeName, contactName, 
	contactEmail, S.sessionId, E.eventId, S.sessionStart, S.sessionEnd
	FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [department] D ON E.departmentId = D.departmentId
	INNER JOIN [theme] T ON E.themeId = T.themeId
	WHERE (S.isApproved IS NULL)
	ORDER BY S.sessionStart ASC";

	$result = sqlsrv_query($conn, $query);
	$pending = [];
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$pending[] = $row;
	}
?>

<table class="grid smaller">
	<tr>
		<th scope="col">Name</th>
		<th scope="col">Dept</th>
		<th scope="col">Theme</th>
		<th scope="col">Dates</th>
		<th scope="col" style="width: 100px;">Contact</th>
		<th scope="col" style="width: 80px;">Action</th>
	</tr>
	<?php foreach($pending as $row): ?>
	<tr>
		<th scope="row"><?= $row['eventName'] ?></th>
		<td><?= $row['departmentName'] ?></td>
		<td><?= $row['themeName'] ?></td>
		<td><?= smalldate($row['sessionStart'], $row['sessionEnd']) ?></td>
		<td><a href="mailto:<?= $row['contactEmail'] ?>"><?= $row['contactName'] ?></a></td>
		<td>
			<a href="?id=approve-edit&amp;event=<?= $row['eventId'] ?>&amp;session=<?= $row['sessionId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/database_go.png" alt="icon" title="Approve Session" class="icon" />
			</a>
			<a href="?id=session-edit&amp;session=<?= $row['sessionId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_page_white_edit.png" alt="icon" title="Edit Session" class="icon" />
			</a>
			<a href="?id=event-edit&amp;event=<?= $row['eventId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_database_edit.png" alt="icon" title="Edit Event" class="icon" />
			</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>