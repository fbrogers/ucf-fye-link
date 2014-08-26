<?php 
	if(!isset($_REQUEST['session']) or !is_numeric($_REQUEST['session'])){
		die('Session is necessary.');
	}
	
	$title = 'Edit Session'; 
	$session = $_REQUEST['session'];
		
	if(is_post()){
		$term = get_trm($_POST['start_time']);
		$event = $_POST['eid'];

		$query = 
		"UPDATE [session]
		SET
			sessionStart = ?,
			sessionEnd = ?,
			sessionLocation = ?,
			sessionMapId = ?,
			sessionSemester = ?,
			eventId = ?
		WHERE sessionId = ?";
		
		$params = [$_POST['start_time'], $_POST['end_time'], $_POST['location'], $_POST['mapid'], $term, $event, $session];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		exit(header("Location: ?id=session"));
	}
	
	//inital session table query
	$query = 
	"SELECT
		eventId,
		sessionStart,
		sessionEnd,
		sessionLocation,
		sessionRoom,
		sessionMapId
	FROM [session] S
	WHERE sessionId = ?";

	$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result);
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<div class="shift-right">
			<label for="eid">Associated Event</label>
			<select name="eid" id="eid">
				<?= dropDownChanger('event', 'eventId', 'eventName', $row['eventId']); ?>
			</select>
		
			<label for="st">Start Date/Time</label>
			<input type="text" value="<?= $row['sessionStart'] ?>" name="start_time" id="st" />
			
			<label for="et">End Date/Time</label>
			<input type="text" value="<?= $row['sessionEnd'] ?>" name="end_time" id="et" />
			
			<label for="location">Location</label>
			<input type="text" value="<?= $row['sessionLocation'] ?>" name="location" id="location" />
			
			<label for="room">Room / Other Details</label>
			<input type="text" value="<?= $row['sessionRoom'] ?>" name="room" id="room" />
			
			<label for="mapid">Map ID</label>
			<input type="text" value="<?= $row['sessionMapId'] ?>" name="mapid" id="mapid" />
		</div>
	</fieldset>	
	<div class="submitbox">
		<input type="submit" value="<?= $title; ?>" />
		<input type="hidden" name="id" value="<?= $session; ?>" />
	</div>
</form>