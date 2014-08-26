<?php 
	$title = 'Approved Sessions'; 
	
	$links = 
	'<div class="content-main-links">
		<form action="" method="get" name="limit_dept">
			<input type="hidden" value="session" name="id" />
			Term: <select name="term" onchange="this.form.submit();">
				<option></option>
				<option value="spr">Fall and Spring</option>
				<option value="sum">Summer</option>
			</select>
			Department: <select name="dept" onchange="this.form.submit();">
				<option></option>
				'.get_partners().'
			</select>
		</form>
	</div>';

	if(isset($_GET['term']) && $_GET['term'] == null){
		unset($_GET['term']);
	}
	if(isset($_GET['dept']) && $_GET['dept'] == null){
		unset($_GET['dept']);
	}

	if(isset($_GET['event'])){
		$limit = "AND (E.eventId = '".$_GET['event']."') ";
	} else { 
		//limit by department
		if(isset($_GET['dept']) && !is_null($_GET['dept'])){
			$limit = "AND (E.departmentId = '".$_GET['dept']."') ";
		} elseif(isset($_GET['term']) && !is_null($_GET['term'])){
			$limit = "AND (S.sessionSemester = '".$_GET['term']."') "; 
		} else {
			$term = get_trm(date('D, d M Y H:i:s'));
			$limit = "AND (S.sessionSemester = '{$term}') "; 
			$msg = term_convert($term);
		}
	}

	//sql query
	$query = 
	"SELECT
		S.sessionId,
		E.eventId,
		departmentName,
		eventName,
		eventPoints,
		S.sessionStart,
		S.sessionEnd,
		S.sessionLocation,
		ISNULL(C.total,0) AS total
	FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [department] D ON E.departmentId = D.departmentId 
	LEFT JOIN (
		SELECT sessionId, COUNT(studentId) AS total 
		FROM studentSession GROUP BY sessionid
	) C ON C.sessionId = S.sessionId
	WHERE (S.isApproved = '1') {$limit}
	ORDER BY sessionStart ASC";
	$result = sqlsrv_query($conn, $query) or die(print_r( sqlsrv_errors(), true));
	
	$sessions = [];
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$sessions[] = $row;
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
		<th scope="col">Dept</th>
		<th scope="col">Dates</th>
		<th scope="col">Location</th>
		<th scope="col">Points</th>
		<th scope="col">#</th>
		<th scope="col" style="width: 100px;">Actions</th>
	</tr>	
	<?php foreach($sessions as $row): ?>
	<tr>
		<th scope="row"><?= $row['eventName'] ?></th>
		<td><?= reduce($row['departmentName'], 25) ?></td>
		<td><?= smalldate($row['sessionStart'], $row['sessionEnd']) ?></td>
		<td><?= reduce($row['sessionLocation'], 25) ?></td>
		<td><?= $row['eventPoints'] ?></td>
		<td><?= $row['total'] ?></td>
		<td>
			<a href="?id=session-attend&amp;session=<?= $row['sessionId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_user_add.png" alt="icon" title="Attendance" class="icon" />
			</a>
			<a href="?id=session-edit&amp;session=<?= $row['sessionId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_page_white_edit.png" alt="icon" title="Edit Session" class="icon" />
			</a>
			<a href="?id=event-edit&amp;event=<?= $row['eventId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/icons/fff_database_edit.png" alt="icon" title="Edit Event" class="icon" />
			</a>
			<form action="?id=session-del" method="post">
				<input type="hidden" name="session" value="<?= $row['sessionId'] ?>" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/icons/fff_delete.png" alt="icon" title="Delete Session" class="icon del" />
			</form>
		</td>
	</tr>
	<?php endforeach; ?>
</table>