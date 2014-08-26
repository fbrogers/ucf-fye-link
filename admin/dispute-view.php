<?php 
if(isset($_GET['dispute']) && is_numeric($_GET['dispute'])){
	$title = 'View Loot Dispute'; 
	$dispute = $_GET['dispute'];

	$query = "SELECT TOP 1
		created,
		studentFirst,
		studentLast,
		studentEmail,
		eventName,
		contactName,
		contactEmail,
		eventDispute,
		sessionLocation,
		sessionStart,
		sessionEnd,
		disputeDetails,
		disputeAttendance,
		disputeAttachmentExt
	FROM [dispute] D 
	INNER JOIN [student] ST ON D.studentId = ST.studentId
	INNER JOIN [session] SE ON D.sessionId = SE.sessionId
	INNER JOIN [event] E ON SE.eventId = E.eventId
	WHERE disputeId = ?";
	$result = sqlsrv_query($conn, $query, [$dispute]);
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC); ?>

	<table class="grid">
		<tr>
			<th scope="col">Field</th>
			<th scope="col">Value</th>
		</tr>
		<?php foreach($row as $index => $value):?>
		<tr>
			<th scope="row"><?= $index ?></th>
			<td><?= $value ?></td>
		</tr>
		<?php endforeach ?>
		<?php if(isset($row['disputeAttachmentExt']) && $row['disputeAttachmentExt'] != NULL):?>
		<tr>
			<th scope="row">Attachment</th>
			<td><a href="?id=dispute-attachment&amp;xid=<?= $dispute ?>">[Download]</a></td>
		</tr>
		<?php endif ?>
	</table>
<?php } ?>