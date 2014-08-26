<?php 
	$title = 'Events'; 
	$links = ['Add Event' => '?id=event-add', 'Wipe Event Data' => '?id=event-wipe'];

	//create a sql statement filter
	if(isset($_GET['dept']) && is_numeric($_GET['dept']) && $_GET['dept']){ 
		$limit = "WHERE (E.departmentId = '".$_GET['dept']."') ";
	} 

	//main sql query
	$query = "SELECT
		E.eventId,
		E.eventName,
		COALESCE(D.departmentAcronym, D.departmentName) AS dept,
		T.themeName,
		E.eventPoints,
		E.contactName,
		E.contactEmail,
		ISNULL(J.total,0) AS total,
		ISNULL(K.total,0) AS approved
	FROM [event] E
	INNER JOIN [department] D ON E.departmentId = D.departmentId
	INNER JOIN [theme] T ON E.themeId = T.themeId
	INNER JOIN (
		SELECT event.eventId, COUNT(session.sessionId) AS total 
		FROM [event]
		LEFT JOIN [session] ON session.eventId = event.eventId
		GROUP BY event.eventId
	) J ON J.eventId = E.eventId
	LEFT JOIN (
		SELECT event.eventId, COUNT(session.sessionId) AS total 
		FROM [event]
		LEFT JOIN [session] ON session.eventId = event.eventId
		WHERE session.isApproved = 1
		GROUP BY event.eventId
	) K ON K.eventId = E.eventId ";
				
	//include filter if set
	if(isset($limit)){
		$query .= $limit;
	}
	
	//finish query
	$query .= "ORDER BY E.eventName";
	$result = sqlsrv_query($conn, $query);
	$events = [];
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$events[] = $row;
	}
?>

<script type="text/javascript">
	$(function(){
		$('.del').click(function(){
			return confirm("This will permanently delete this row. Are you sure?");
		});
	});
</script>

<table class="grid smaller">
	<tr>
		<th scope="col">Name</th>
		<th scope="col">
			<form action="" method="get" name="limit_dept">
				<input type="hidden" value="event" name="id" />
				<select name="dept" onchange="limit_dept.submit(); return false;" style="width: 100px;">
					<option></option>
					<option value="0">All</option>
					<?= get_partners($conn); ?>
				</select>
			</form>		
		</th>
		<th scope="col">Theme</th>
		<th scope="col">Points</th>
		<th scope="col">Contact</th>
		<th scope="col">Sessions</th>
		<th scope="col" style="width: 100px;">Actions</th>
	</tr>
	<?php foreach($events as $row): ?>
	<tr>
		<th scope="row"><?= $row['eventName'] ?></th>
		<td><?= $row['dept'] ?></td>
		<td><?= $row['themeName'] ?></td>
		<td><?= $row['eventPoints'] ?></td>
		<td><a href="mailto:<?= $row['contactEmail'] ?>"><?= $row['contactName'] ?></a></td>
		<td><?= $row['total'] ?> (<?= $row['approved'] ?>)</td>
		<td>
			<a href="?id=session-add&amp;event=<?= $row['eventId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/page_white_add.png" alt="icon" title="Add Session" class="icon" />
			</a>
			<a href="?id=session&amp;event=<?= $row['eventId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/page_white_magnify.png" alt="icon" title="View Sessions" class="icon" />
			</a>
			<a href="?id=event-edit&amp;event=<?= $row['eventId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/database_edit.png" alt="icon" title="Edit Event" class="icon" />
			</a>
			<form action="?id=event-del" method="post">
				<input type="hidden" name="event" value="<?= $row['eventId'] ?>" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/icons/fff_delete.png" alt="icon" title="Delete Event" class="icon del" />
			</form>
		</td>
	</tr>
	<?php endforeach; ?>
</table>