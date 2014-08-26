<?php
	$title = 'Loot Disputes'; 

	$query = "SELECT
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
		D.disputeId,
		disputeDetails,
		disputeAttendance,
		disputeAttachmentExt,
		C.total
	FROM [dispute] D 
	INNER JOIN [student] ON D.studentId = student.studentId
	INNER JOIN [session] ON D.sessionId = session.sessionId
	INNER JOIN [event] E ON session.eventId = E.eventId
	INNER JOIN (
		SELECT COUNT(dispute.disputeId) AS total, student.studentId FROM [dispute] 
		INNER JOIN [student] ON dispute.studentId = student.studentId
		GROUP BY student.studentId
	) C ON D.studentId = C.studentId
	WHERE D.isApproved IS NULL
	ORDER BY session.sessionStart ASC";
	
	$result = sqlsrv_query($conn, $query);
	$disputes = [];
	while($row = sqlsrv_fetch_array($result)){
		$disputes[] = $row;
	}
?>

<script type="text/javascript">
	$(function(){
		$('.approve').click(function(){
			return confirm("This change will take immediate effect and archive the dispute. Are you sure?");
		});

		$('.del').click(function(){
			return confirm("This will permanently delete this row. Are you sure?");
		});
	});
</script>

<table class="grid smaller">
	<tr>
		<th scope="col">Disputed Event</th>
		<th scope="col">Disputed Session</th>
		<th scope="col">Student</th>
		<th scope="col">Attendance Claim</th>
		<th scope="col">Upload</th>
		<th scope="col">Submitted</th>
		<th scope="col" style="width: 80px;">Actions</th>
	</tr>
	<?php foreach($disputes as $row): ?>
	<tr>
		<th scope="row"><a href="?id=dispute-view&amp;dispute=<?= $row['disputeId'] ?>"><?= $row['eventName'] ?></a></th>
		<td><?= date('l, F jS g:ia', strtotime($row['sessionStart'])) ?></td>
		<td><?= $row['studentFirst'].' '.$row['studentLast'] ?> (<?= $row['total'] ?>)</td>
		<td><?= $row['disputeAttendance'] ?></td>
		<td>
			<?php if(!is_null($row['disputeAttachmentExt'])): ?>
			<a href="?id=dispute-attachment&amp;xid=<?= $row['disputeId'] ?>">[Link]</a>
			<?php endif ?>
		</td>
		<td><?= date('F jS', strtotime($row['created'])) ?></td>
		<td>
			<form action="?id=dispute-edit" method="post">
				<input type="hidden" name="dispute" value="<?= $row['disputeId'] ?>" />
				<input type="hidden" name="action" value="Approve" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/icons/fff_accept.png" alt="icon" title="Approve Dispute" class="icon approve" />
			</form>
			<form action="?id=dispute-edit" method="post">
				<input type="hidden" name="dispute" value="<?= $row['disputeId'] ?>" />
				<input type="hidden" name="action" value="Deny" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/icons/fff_stop.png" alt="icon" title="Deny Dispute" class="icon approve" />
			</form>
			<form action="?id=dispute-del" method="post">
				<input type="hidden" name="dispute" value="<?= $row['disputeId'] ?>" />
				<input type="image" src="http://assets.sdes.ucf.edu/images/icons/fff_delete.png" alt="icon" title="Delete Dispute" class="icon del" />
			</form>
		</td>
	</tr>
	<?php endforeach ?>
</table>