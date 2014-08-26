<?php
	$title = 'Edit Event'; 

	if(!isset($_REQUEST['event']) or !is_numeric($_REQUEST['event'])){
		die('Event must be passed to this page.');
	}

	$event = $_REQUEST['event'];
	
	if(is_post()){
		//sanitize post data
		$p = FormProcessor::oxyClean($_POST);
		
		//sql update query
		$query = "UPDATE [event] SET
			departmentId = ?,
			themeId = ?,
			eventName = ?,
			eventPoints = ?,
			eventDescription = ?,
			eventDispute = ?, 
			contactName = ?,
			contactPhone = ?,
			contactEmail = ?
		WHERE eventId = ?";
		
		//parameters for the query
		$params = [
			$p['department_id'],
			$p['theme_id'],
			$p['name'],
			$p['points'],
			$p['description'],
			$p['dispute'],
			$p['contact_name'],
			$p['contact_phone'],
			$p['contact_email'],
			$event
		];
			
		//boosh
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		header("Location: ?id=event");
	}
	
	$query = "SELECT * FROM [event] WHERE [eventId] = ?";
	$result = sqlsrv_query($conn, $query, [$event]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
?>
	
<script type="text/javascript">
	$(document).ready(function(){
		$("#form").validate({
			rules: {
				department_id: "required",
				theme_id: "required",
				name: "required",
				points: "required",
				description: "required",
				dispute: "required",
				contact_name: "required",
				contact_email: {required: false, email: true}
			}
		});
	});
</script>

<form action="" id="form" method="post" class="fieldset">
	<input type="hidden" name="event" value="<?= $event; ?>" />
	<fieldset>
		<legend>Event Details</legend>
		<div class="shift-right">
			<label for="department_id">Department</label>
			<select name="department_id" id="department_id">
				<?= dropDownChanger('department', 'departmentId', 'departmentName', $row['departmentId']) ?>
			</select>
			
			<label for="theme_id">Theme</label>
			<select name="theme_id" id="theme_id">
				<?= dropDownChanger('theme', 'themeId', 'themeName', $row['themeId']) ?>
			</select>
			
			<label for="name">Name</label>
			<input type="text" name="name" id="name" value="<?= $row['eventName']; ?>" />

			<label for="points">Points</label>
			<input type="text" name="points" id="points" value="<?= $row['eventPoints']; ?>" />

			<label for="description">Description</label>
			<textarea name="description" id="description" rows="4" cols="61"><?= $row['eventDescription']; ?></textarea>

			<label for="dispute">Dispute Details</label>
			<textarea name="dispute" id="dispute" rows="4" cols="61"><?= $row['eventDispute']; ?></textarea>
		</div>
	</fieldset>

	<fieldset>
		<legend>Contact Details</legend>
		<div class="shift-right">
			<label for="contact_name">Contact Name</label>
			<input type="text" name="contact_name" id="contact_name" value="<?= $row['contactName']; ?>" />

			<label for="contact_phone">Contact Phone</label>
			<input type="text" name="contact_phone" id="contact_phone" value="<?= $row['contactPhone']; ?>" />

			<label for="contact-email">Contact E-mail</label>
			<input type="text" name="contact_email" id="contact_email" value="<?= $row['contactEmail']; ?>" />
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" value="Update Event" />
	</div>
</form>