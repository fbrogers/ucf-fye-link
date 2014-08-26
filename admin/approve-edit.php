<?php 
	$title = 'Approve Session';
	
	//save out sessionId
	if(isset($_REQUEST['session']) && is_numeric($_REQUEST['session'])){
		$session = $_REQUEST['session'];
	}

	//save out eventId
	if(isset($_REQUEST['event']) && is_numeric($_REQUEST['event'])){
		$event = $_REQUEST['event'];
	}

	if(is_post() && isset($_POST['form_submit'])){

		//if approved
		if($_POST['form_submit'] == 'Approve'){

			//assign points to the event listing
			if(isset($_POST['points']) && is_numeric($_POST['points']) && $_POST['points'] > 0){
				$query = "UPDATE [event] SET eventPoints = ? WHERE (eventId = ?)";
				$params = [$_POST['points'], $event];
				$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
			}
		
			//update the session listing for any edits
			$query = "UPDATE [session] SET [isApproved] = 1 WHERE [sessionId] = ?";
			$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));
		
			//select the event listing just updated
			$query = "SELECT eventName, themeName, eventPoints, eventDescription,
			eventDispute, sessionStart, sessionEnd, sessionLocation, contactEmail
			FROM [session] S
			INNER JOIN [event] E ON S.eventId = E.eventId
			INNER JOIN [theme] T ON E.themeId = T.themeId
			WHERE S.sessionId = ?";

			//run query
			$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));

			//check for a single returned row
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) or die('No rows returned.');
			
			//start email
			$body = '<p>This automated e-mail is to inform you that your session request for
			<strong>'.$row['eventName'].'</strong> has been approved for the LINK program.
			Your event details are below:</p>
			<p>
				Event Name: '.$row['eventName'].'<br />
				Event Theme: '.$row['themeName'].'<br />
				Event Points: '.$row['eventPoints'].'<br />
				Event Description: '.$row['eventDescription'].'<br />
				Dispute Details: '.$row['eventDispute'].'<br />
				Session Date/Time: '.smalldate($row['sessionStart'], $row['sessionEnd']).'<br />
				Location: '.$row['sessionLocation'].'<br />
				Notes from LINK Admin: '.$_POST['notes'].'
			</p>
			<p>Thank you for submitting this session. If there are any changes or corrections,
			please reply to this email.</p>';
			
			//policies
			$body .= '<h2>LINK Program Policies</h2>
			<ul>
				<li>
					<p>Please note that ALL programs must be submitted at least TWO WEEKS
					prior to the event date.</p>
				</li>
				<li>
					<p>This form must be complete in order to be approved for the LINK Program.
					Any incomplete forms will be held until all program information
					is provided.</p>
				</li>
				<li>
					<p>All sign-in sheets must be returned to LINK within two business
					days of the event.</p>
				</li>
				<li>
					<p>LINK card scanners can be checked out in 218 Howard Phillips Hall
					one day in advance, and must be returned the following business day.
					You may check out 1 scanner for every 100 students you anticipate 
					attending.</p>
				</li>
				<li>
					<p>LINK does not attend events to sign students in or to swipe
					students\' IDs. Please plan to have your staff or volunteers available
					to help students check-in for LINK Loot.</p>
				</li>
				<li>
					<p>If LINK consistently receives late attendance information from your
					office, you will be restricted from offering LINK Loot for the
					following semesters.</p>
				</li>
			</ul>';

			//send the email
			try{
				$f = new FormProcessor();
				$f->to($row['contactEmail']);
				$f->from('link@ucf.edu');
				$f->subject('LINK | Session Approved');
				$f->body($body, false);
				$f->send('?id=approve');
				exit();
			} catch(Exception $e){
				die('Error: '.$e->getMessage());
			}

		} elseif($_POST['form_submit'] == 'Deny'){
			//select the event listing just updated
			$query = "SELECT eventName, themeName, eventPoints, eventDescription,
			eventDispute, sessionStart, sessionEnd, sessionLocation, contactEmail
			FROM [session] S 
			INNER JOIN [event] E ON S.eventId = E.eventId 
			INNER JOIN [theme] T ON E.themeId = T.themeId 
			WHERE S.sessionId = ?";

			//run query
			$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));

			//check for a single returned row
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) or die('No rows returned.');
			
			//start email
			$body = '<p>This automated e-mail is to inform you that your session request for
			<strong>'.$row['eventName'].'</strong> has been 
			denied for the LINK program. Your event details are below:</p>
			<p>
				Event Name: '.$row['eventName'].'<br />
				Event Theme: '.$row['themeName'].'<br />
				Event Points: '.$row['eventPoints'].'<br />
				Event Description: '.$row['eventDescription'].'<br />
				Dispute Details: '.$row['eventDispute'].'<br />
				Session Date/Time: '.smalldate($row['sessionStart'], $row['sessionEnd']).'<br />
				Location: '.$row['sessionLocation'].'<br />
				Notes from LINK Admin: '.$_POST['notes'].'
			</p>
			<p>If there are any changes or corrections, please reply to this email.</p>';

			//send the email
			try{
				$f = new FormProcessor();
				$f->to($row['contactEmail']);
				$f->from('link@ucf.edu');
				$f->subject('LINK | Session Denied');
				$f->body($body, false);
				$f->send('?id=approve');

				//delete session via function
				deleteSession($session);

				exit();
			} catch(Exception $e){
				die('Error: '.$e->getMessage());
			}
		}
	}

	$query = "SELECT eventName, eventPoints, eventDescription, eventDispute, contactName, contactPhone, 
	contactEmail, S.eventId, S.sessionId, sessionStart, sessionEnd, sessionLocation, sessionRoom
	FROM [session] S INNER JOIN [event] E ON S.eventId = E.eventId
	WHERE S.sessionId = ?";
	$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	$links = [
		'Edit Session' => '?id=session&session='.$session,
		'Edit Event' => '?id=event-edit&event='.$event
	];
?>

<div class="global-col2-1">
	<h2>Event Details</h2>
	<table class="grid">
		<tr>
			<th scope="col">Field</th>
			<th scope="col">Data</th>
		</tr>
		<?php if(is_array($row)){
			foreach($row as $index => $x){
				echo '<tr><th scope="row">'.$index.'</th><td>'.$x.'</td></tr>';
			}
			if($row['eventPoints']){
				$points = false;
			}
		} ?>
	</table>
</div>

<div class="global-col2-2">
	<h2>Session Details</h2>
	<form name="approve" method="post" action="" class="fieldset half">
		<fieldset>
			<input type="hidden" name="eid" value="<?= $row['eventId']; ?>" />
			<input type="hidden" name="sid" value="<?= $row['sessionId']; ?>" />

			<label>Points</label>
			<input type="text" name="points" value="0" <?php if(isset($points)) echo 'disabled="disabled"';?> />

			<label>Date/Time</label>
			<input type="text" value="<?= smalldate($row['sessionStart'], $row['sessionEnd']);?>" disabled="disabled" />

			<label>Location</label>
			<input type="text" value="<?= $row['sessionLocation'] ?>" disabled="disabled" />

			<label>Room / Other Details</label>
			<input type="text" value="<?= $row['sessionRoom'] ?>" disabled="disabled" />

			<label>Notes for Creator</label>
			<textarea name="notes" rows="6" cols="47"></textarea>
		</fieldset>
		<div class="submitbox">
			<input type="submit" value="Approve" name="form_submit" />
			<input type="submit" value="Deny" name="form_submit" />
		</div>
	</form>
</div>
