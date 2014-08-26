<?php 
	$title = 'Departments'; 
	$links = ['Add a Department' => '?id=departments-add'];
	$depts = [];
	
	$query = "SELECT 
		D.departmentId,
		departmentName,
		departmentAcronym,
		departmentPhone,
		departmentEmail,
		departmentUrl,		
		departmentLocation,
		departmentMapId,
		ISNULL(total,0) AS total FROM [department] D
	LEFT JOIN (
		SELECT COUNT(session.sessionId) AS total, department.departmentId FROM [session] 
		INNER JOIN [event] ON session.eventId = event.eventId
		INNER JOIN [department] ON event.departmentId = department.departmentId
		GROUP BY department.departmentId
	) S ON D.departmentId = S.departmentId
	ORDER BY departmentName ASC";
	
	$result = sqlsrv_query($conn, $query);
	while($row = sqlsrv_fetch_array($result)){
		$depts[] = $row;
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
		<th scope="col">Department</th>
		<th scope="col">Acronym</th>
		<th scope="col">Phone</th>
		<th scope="col">Email</th>
		<th scope="col">Website</th>
		<th scope="col">Location</th>
		<th scope="col">Sessions</th>
		<th scope="col" style="width: 80px;">Actions</th>
	</tr>
	<?php foreach($depts as $row): ?>
	<tr>
		<th scope="row"><?= $row['departmentName'] ?></th>
		<td><?= $row['departmentAcronym'] ?></td>
		<td><?= $row['departmentPhone'] ?></td>
		<td><?= $row['departmentEmail'] ?></td>
		<td><a href="<?= $row['departmentUrl'] ?>">[Link]</a></td>
		<td><a href="http://map.ucf.edu/?show=<?= $row['departmentMapId'] ?>"><?= $row['departmentLocation'] ?></a></td>
		<td><?= $row['total'] ?></td>
		<td>
			<a href="?id=session&amp;dept=<?= $row['departmentId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/page_white_magnify.png" alt="icon" title="View Sessions" class="icon" />
			</a>
			<a href="?id=departments-edit&amp;dept=<?= $row['departmentId'] ?>">
				<img src="http://assets.sdes.ucf.edu/images/building_edit.png" alt="icon" title="Edit Department" class="icon" />
			</a>
			<form action="?id=departments-del" method="post">
				<input type="hidden" name="dept" value="<?= $row['departmentId'] ?>" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/building_delete.png" alt="icon" title="Delete Department" class="icon del" />
			</form>
		</td>
	</tr>
	<?php endforeach ?>
</table>