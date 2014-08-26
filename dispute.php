<?php $title = 'Submit a Loot Dispute'; 
require_once 'includes/connection.inc.php';

if(!isset($_GET['xid']) or !is_numeric($_GET['xid']) or strlen($_GET['xid']) != 7){
	header("Location: ../loot");
}

$pid = $_GET['xid'];

if(is_post()){
	//get the student information based on the sutdentPid
	$query = "SELECT * FROM [student] WHERE studentPid = ?";
	$result = sqlsrv_query($conn, $query, [$pid]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	//file upload and smash
	if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] != UPLOAD_ERR_NO_FILE){
		$attachment = filePack('attachment');
		
		//insert into database
		$query = "INSERT INTO [dispute] (
			studentId, 
			sessionId, 
			disputeAttendance, 
			disputeDetails, 
			disputeAttachment, 
			disputeAttachmentExt
		) VALUES (?, ?, ?, ?, ?, ?)";
		
		//parameters
		$params = [
			$row['studentId'],
			$_POST['sessionId'],
			$_POST['sign_in'],
			$_POST['details'],
			[
				$attachment['blob'],
				SQLSRV_PARAM_IN,
				SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
				SQLSRV_SQLTYPE_VARBINARY('max')
			],
			$attachment['ext']
		];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

	} else {
		//insert into database
		$query = "INSERT INTO [dispute] (studentId, sessionId, disputeAttendance, disputeDetails) VALUES (?, ?, ?, ?)";
		$params = [$row['studentId'],$_POST['sessionId'],$_POST['sign_in'],$_POST['details']];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
	}

	//new FP object
	try{
		$f = new FormProcessor;
		$f->from($row['studentEmail']);
		$f->to(['link@ucf.edu', $row['studentEmail']]);
		$f->subject('Link Dispute | '.$row['studentFirst'].' '.$row['studentLast']);
		$f->attach('attachment', false);
		$f->send('../dispute-thanks');
		exit();
	} catch(Exception $e){
		die('Error: '.$e->getMessage());
	}
}

//get the student information based on the sutdentPid
$query = "SELECT eventName, E.eventId FROM [event] E
INNER JOIN [session] S ON E.eventId = S.eventId 
WHERE sessionStart < GETDATE() 
AND DATEDIFF(dd, sessionStart, GETDATE()) < 45 
AND DATEDIFF(dd, sessionStart, GETDATE()) > 5
ORDER BY eventName ASC";
$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));
$events = [];
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
	$events[] = $row;
}
?>

<script type="text/javascript">
	$(document).ready(function(){
		$('#event').change(function(){
			$('#sessionId').html('');
			var event = $(this).val();
				
			$.post("includes/ajax-dispute.php",
			{ event:event },
			function(data){
				$('#sessionId').html(data);
			});
			return false;
		});
	
		$("#form").validate({ rules: {
			event: "required",
			sessionId: "required",
			sign_in: "required",
			details: "required",
			agree: "required"
		} });
	});
</script>

<p>Enter the dispute information below. Please be aware that events that have taken place in the past 
five (5) days will <strong>not</strong> appear in the list.</p>
	
<form action="" method="post" class="fieldset" id="form" enctype="multipart/form-data">
	<input type="hidden" name="pid" value="<?= $pid; ?>" />

	<fieldset>
		<legend>Dispute Information</legend>
		<div class="space-left mzero">
			<p><strong>Dispute Policies</strong></p>
			<ol class="dispute_policies">
				<li>
					<p>In order to receive LINK Loot, I must sign in, or have my ID card
					swiped at each event. I understand this is my responsibility.</p>
				</li>
				<li>
					<p>I understand sign-in sheets may not be disputed. However, since technology
					sometimes falters, if an event used a card reader, 
					I may dispute no more than 5 LINK programs per month.</p>
					
					<p><em>There should never be a technical problem 5 times in one month. If
					your LINK Loot is not being credited, you should visit Card Services to
					make sure your ID card does not need replaced or is invalid.</em></p>
				</li>
				<li>
					<p>I will check my LINK Loot often and follow dispute deadlines. I will
					remember that Loot Dispute deadlines are due no later than
					<strong>the 15th of each month for the previous month</strong>. I will not
					submit disputes after the deadline.</p>
				</li>
				<li>
					<p>I will use and enjoy the LINK program respectively by following the
					terms I have been given.</p>
				</li>
			</ol>
		</div>
		<div class="shift-right">
			<label for="event">Disputed Program</label>
			<select name="event" id="event" class="event">
				<option></option>
				<?php foreach($events as $row):?>
				<option value="<?= $row['eventId'] ?>"><?= $row['eventName'] ?></option>
				<?php endforeach ?>
			</select>

			<label for="sessionId">Disputed Session of Event Above</label>
			<select name="sessionId" id="sessionId">
				<option></option>
			</select>

			<label class="sentence">How did you sign into the event?</label>
			<input type="radio" name="sign_in" id="signin1" value="Card Swipe" /><label for="signin1">I swiped my UCF ID</label><br />
			<input type="radio" name="sign_in" id="signin2" value="Check-in Roster" /><label for="signin2">I signed a check-in roster</label><br />
			<input type="radio" name="sign_in" id="signin3" value="None" /><label for="signin3">I signed in through some other means</label>
			<label for="sign_in" class="error" style="display: none;">You must select an option.</label>
			
			<label for="details" class="sentence">Please provide details that would help to prove your attendance:</label>
			<textarea name="details" id="details" rows="6" cols="61"></textarea>
			
			<label for="attachment" class="sentence">Please attach any documents or files supporting your claim(s):</label>
			<input type="file" name="attachment" id="attachment" />

			<p>Please read through the policies located to the left and verify that you have
			been informed and understand the <em>Loot Dispute</em> policies before filing a dispute.</p>
			
			<label>Agreement</label>
			<input type="checkbox" name="agree" id="agree" value="Yes" /><label for="agree">I agree to the terms and conditions listed to the left.</label>
			<label for="agree" class="error" style="display: none;">You must agree to continue.</label>
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" value="<?= $title; ?>" />
	</div>
</form>