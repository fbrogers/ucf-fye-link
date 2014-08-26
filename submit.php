<?php
require_once 'includes/connection.inc.php';
$title = 'Program Submission';
$links = ['Program Policies' => 'policies'];
$rand = mt_rand(0,1000000);

if(is_post()){	
	//sanitize
	FormProcessor::oxyClean($_POST);

	//captcha check
	if(!isset($_POST['their']) || !isset($_POST['captcha']) || $_POST['their'] != $_POST['captcha']){
		die("CAPTCHA not completed correctly. Please press back and try again.");
	}

	if(!isset($_POST['prevEvent']) || empty($_POST['prevEvent'])){

		//insert values into the event table
		$query = "INSERT INTO [event] 
		(departmentId, themeId, eventName, eventDescription, eventDispute, contactName, contactPhone, contactEmail) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?); SELECT SCOPE_IDENTITY();";

		//query parameters
		$params = [
		$_POST['departmentId'],
		$_POST['themeId'],
		$_POST['event_name'],
		$_POST['description'],
		$_POST['dispute'],
		$_POST['contact_name'],
		$_POST['contact_phone'],
		$_POST['contact_email']
		];

		//throw an error if result failed
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

		//get the primary key of the insert
		$event = getLastId($result);

	} else {
		$event = $_POST['prevEvent'];
	}

	for($s = 0; $s < count($_POST['session_start_date']); $s++){
		//concatenate date and time values
		$start_time =   $_POST['session_start_date'][$s].' '.$_POST['session_start_time_hour'][$s].':'.$_POST['session_start_time_minutes'][$s].':00.0000000';
		$end_time =     $_POST['session_end_date'][$s].' '.$_POST['session_end_time_hour'][$s].':'.$_POST['session_end_time_minutes'][$s].':00.0000000';

		//current term validation
		$term = get_trm($start_time);

		//map name and map ID
		$map_pieces = explode('|', $_POST['location'][$s]);

		//insert current session
		$query = "INSERT INTO [session] (eventId, sessionStart, sessionEnd, sessionLocation, sessionMapId, sessionRoom, sessionSemester) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$params = [$event, $start_time, $end_time, $map_pieces[0], $map_pieces[1], $_POST['room'][$s], $term];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
	}
	exit(header("Location: thanks"));
}

//get all current events
$query = "SELECT eventName, E.eventId, departmentName FROM [event] E
INNER JOIN [session] S ON S.eventId = E.eventId
INNER JOIN [department] D ON D.departmentId = E.departmentId
WHERE S.isApproved = '1' GROUP BY E.eventId, E.eventName, D.departmentName
ORDER BY departmentName, eventName";
$result = sqlsrv_query($conn, $query) or die(print_r( sqlsrv_errors(), true));
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
	$events[$row['departmentName']][] = $row;
}

//get all current themes
$query = "SELECT * FROM [theme] ORDER BY themeName";
$result = sqlsrv_query($conn, $query) or die(print_r( sqlsrv_errors(), true));
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
	$themes[] = $row;
}

//get all map locations
$ch = curl_init("http://map.ucf.edu/locations/.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
$json = curl_exec($ch);
curl_close($ch);

//grab only objects that are type building
$json = json_decode($json, true);
foreach($json as $x){
	if(isset($x['object_type']) && $x['object_type'] == 'Building'){
		$final[$x['id']] = htmlentities($x['title']);
	}
}
?>

<script type="text/javascript">
$(function(){
		//accessibility hide
		$('#prev_container, #new_container').hide();

		//for adding and removing sessions
		var session = $('#scontainer').html();
		throw_candy();
		$('#add').click(function(e){
			$('#scontainer').append(session);
			throw_candy();
			e.preventDefault();
		});
		$('#remove').click(function(e){
			if($('#scontainer > .session').size() > 1){
				$('#scontainer > .session:last').remove();
			}
			e.preventDefault();
		});

		//for switching between new event or prev event
		$("#event_previous").click(function(){
			$('#prev_container').show();
			$('#new_container').hide();
		});
		$("#event_new").click(function(){
			$("#new_container").show();
			$('#prev_container').hide();
		});

		//jquery validate
		$("#form").validate({
			rules: {
				type: "required",
				their: "required",
				agree: "required",
				prevEvent: { required: '#event_previous:checked' },
				event_name: { required: '#event_new:checked' },
				departmentId: { required: "#event_new:checked" },
				themeId: { required: "#event_new:checked" },
				description: { required: "#event_new:checked" },
				dispute: { required: "#event_new:checked" },
				contact_name: { required: "#event_new:checked" },
				contact_phone: { required: "#event_new:checked" },
				contact_email: { required: "#event_new:checked" }
			}
		});
		
		//ajax for event name
		$('#event_name').focusout(function(){
			$('label.ajax').remove();
			var event_name = $(this).val();
			$.post("includes/ajax-eventname.php",
				{ event_name:event_name },
				function(data){
					if(data){
						$('#event_name').after('<label class="ajax error">'+data+'</label>');
					} 
				});
			return false;
		});

		//datepicker assignment
		function throw_candy(){
			$("input.candy").not('.hasDatePicker').datepicker({
				dateFormat: "yy-mm-dd",
				minDate: +14,
				showOn: 'both',
				buttonImage: 'https://assets.sdes.ucf.edu/images/calendar.gif',
				onSelect: function(dateStr){
					$("#form").validate().element(this);
				}
			});
		}
	});
</script>

<form action="" method="post" class="fieldset" id="form">
	<fieldset>
		<legend>Event Type</legend>
		<div class="shift-right">
			<label>Session Type</label>
			<input type="radio" name="type" value="newEvent" id="event_previous" />
			<label for="event_previous">Adding new sessions to an existing event</label><br />

			<input type="radio" name="type" value="prevEvent" id="event_new" />
			<label for="event_new">Adding a new event</label><br />
			
			<label class="error" style="display:none;" for="type">You must select an event type.</label>
		</div>
	</fieldset>
	
	<fieldset id="prev_container">
		<legend>Existing Event</legend>
		<div class="shift-right">
			<label for="prevEvent">Select the Existing Event</label>
			<select name="prevEvent" id="prevEvent" size="15">
				<?php foreach($events as $name => $dept): ?>
				<optgroup label="<?= $name ?>">
					<?php foreach($dept as $row): ?>
					<option value="<?= $row['eventId'] ?>"><?= $row['eventName'] ?></option>
					<?php endforeach; ?>
				</optgroup>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<fieldset id="new_container">
		<legend>New Event</legend>
		<div class="space-left">
			<p>
				<strong>Theme</strong><br />
				If you need clarification on the descriptions of each theme, 
				<a href="about#theme" onclick="window.open(this.href); return false;">visit the about page</a>.
			</p>

			<p>
				<strong>Dispute Details</strong><br />
				Students often dispute their attendance at programs and it is helpful for us to know specific
				information that ONLY students in attendance at the event will know. Please submit three
				characteristics or details that students at your program should know (ex. food served,
				specific information presented, offices in attendance, or anything that is NOT in your
				program description).
			</p>

			<p>
				<strong>Contact Information</strong><br />
				The Contact Name and E-mail will be provided to students.
			</p>
		</div>
		<div class="shift-right">
			<label for="event_name">Event Name</label>
			<input type="text" name="event_name" id="event_name" maxlength="100" />

			<div class="global-col2-1">
				<label for="departmentId">UCF Sponsoring Entity</label>
				<select name="departmentId" id="departmentId">
					<option></option>
					<?= echoDept($conn); ?>
				</select>
			</div>
			<div class="global-col2-2">
				<label for="themeId">Theme</label>
				<select name="themeId" id="themeId">
					<option></option>
					<?php foreach($themes as $row): ?>
					<option value="<?= $row['themeId'] ?>"><?= $row['themeName'] ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="hr-clear"></div>

		<label for="description">Event Description</label>
		<textarea name="description" id="description" rows="6" cols="61"></textarea>

		<label for="dispute">Dispute Details</label>
		<textarea name="dispute" id="dispute" rows="4" cols="61"></textarea>

		<label for="contact_name">Contact Name</label>
		<input type="text" name="contact_name" id="contact_name" maxlength="150" />

		<label for="contact_phone">Contact Phone</label>
		<input type="text" name="contact_phone" id="contact_phone" maxlength="15" />

		<label for="contact_email">Contact Email</label>
		<input type="text" name="contact_email" id="contact_email" maxlength="255" />
	</div>
	</fieldset>

	<fieldset>
		<legend>Session Information</legend>
		<div class="space-left">
			<p>
				<strong>Session Dates</strong><br />
				Please note that ALL programs must be submitted at least TWO WEEKS prior to the event date.
			</p>
			<p>

				<button id="add" style="padding: 5px 10px;">Add Another Session</button>
				<button id="remove" style="padding: 5px 10px;">Remove Last Session</button>

			</p>
		</div>
		<div class="shift-right">
			<div id="scontainer">
				<div class="session">
					<div class="global-col2-1">
						<label>Session Start Date</label>
						<input type="text" name="session_start_date[]" class="candy required" style="width: 65%;" />
					</div>
					<div class="global-col2-2">
						<label>Session Start Time</label> 
						<select name="session_start_time_hour[]" style="width: auto;" class="required">
							<option></option>
							<option value="00">12 AM</option>
							<option value="01">01 AM</option>
							<option value="02">02 AM</option>
							<option value="03">03 AM</option>
							<option value="04">04 AM</option>
							<option value="05">05 AM</option>
							<option value="06">06 AM</option>
							<option value="07">07 AM</option>
							<option value="08">08 AM</option>
							<option value="09">09 AM</option>
							<option value="10">10 AM</option>
							<option value="11">11 AM</option>
							<option value="12">12 PM</option>
							<option value="13">01 PM</option>
							<option value="14">02 PM</option>
							<option value="15">03 PM</option>
							<option value="16">04 PM</option>
							<option value="17">05 PM</option>
							<option value="18">06 PM</option>
							<option value="19">07 PM</option>
							<option value="20">08 PM</option>
							<option value="21">09 PM</option>
							<option value="22">10 PM</option>
							<option value="23">11 PM</option>
						</select> :
						<select name="session_start_time_minutes[]" style="width: auto;" class="required">
							<option value="00">00</option>
							<option value="05">05</option>
							<option value="10">10</option>
							<option value="15">15</option>
							<option value="20">20</option>
							<option value="25">25</option>
							<option value="30">30</option>
							<option value="35">35</option>
							<option value="40">40</option>
							<option value="45">45</option>
							<option value="50">50</option>
							<option value="55">55</option>
						</select>
						<label class="error" style="display:none;" for="session_start_time_hour[]">You must select an hour.</label>
					</div>
					<div class="hr-clear"></div>

					<div class="global-col2-1">
						<label>Session End Date</label>
						<input type="text" name="session_end_date[]" class="candy required" style="width: 65%;" />
					</div>
					<div class="global-col2-2">
						<label>Session End Time</label> 
						<select name="session_end_time_hour[]" style="width: auto;" class="required">
							<option></option>
							<option value="00">12 AM</option>
							<option value="01">01 AM</option>
							<option value="02">02 AM</option>
							<option value="03">03 AM</option>
							<option value="04">04 AM</option>
							<option value="05">05 AM</option>
							<option value="06">06 AM</option>
							<option value="07">07 AM</option>
							<option value="08">08 AM</option>
							<option value="09">09 AM</option>
							<option value="10">10 AM</option>
							<option value="11">11 AM</option>
							<option value="12">12 PM</option>
							<option value="13">01 PM</option>
							<option value="14">02 PM</option>
							<option value="15">03 PM</option>
							<option value="16">04 PM</option>
							<option value="17">05 PM</option>
							<option value="18">06 PM</option>
							<option value="19">07 PM</option>
							<option value="20">08 PM</option>
							<option value="21">09 PM</option>
							<option value="22">10 PM</option>
							<option value="23">11 PM</option>
						</select> :
						<select name="session_end_time_minutes[]" style="width: auto;" class="required">
							<option value="00">00</option>
							<option value="05">05</option>
							<option value="10">10</option>
							<option value="15">15</option>
							<option value="20">20</option>
							<option value="25">25</option>
							<option value="30">30</option>
							<option value="35">35</option>
							<option value="40">40</option>
							<option value="45">45</option>
							<option value="50">50</option>
							<option value="55">55</option>
						</select>
						<label class="error" style="display:none;" for="session_end_time_hour[]">You must select an hour.</label>
					</div>
					<div class="hr-clear"></div>

					<div class="global-col2-1">	
						<label for="location">Location</label>
						<select name="location[]" id="location" class="required">
							<option></option>
							<?php foreach($final as $i => $x): ?>
							<option value="<?= $x ?>|<?= $i ?>"><?= $x ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="global-col2-2">	
					<label for="room">Room / Other Details</label>
					<input type="text" name="room[]" id="room" maxlength="100" class="required" />
				</div>
				<div class="hr-clear"></div>
				<div class="hr-blank"></div>
			</div>
		</div>
	</div>
	</fieldset>

	<fieldset>
		<legend>Agreement Section</legend>
		<div class="space-left">
			<p>
				<strong>Policies</strong>: Please read all of our policies thoroughly before
				submitting your program information.
			</p>
			<ul>
				<li><a href="policies" onclick="window.open(this.href); return false;">LINK Program Policies</a></li>
			</ul>
		</div>
		<div class="shift-right">
			<label>Agreement</label>
			<input type="checkbox" name="agree" id="agree" value="true" />
			<label for="agree" class="sentence">I have read and agree to the policies linked on the left.</label>
			<label class="error" style="display:none;" for="agree">You must agree to the terms.</label>
		</div>
	</fieldset>

	<fieldset>
		<legend>CAPTCHA</legend>
		<div class="space-left">
			<p>
				To stop spam submissions, please type the number displayed into the text box.
			</p>
			<p>
				<strong><?= $rand ?></strong>
			</p>
		</div>
		<div class="shift-right">
			<label>Enter the Code Here:</label>
			<input type="text" name="their" id="their" />
			<input type="hidden" name="captcha" id="captcha" value="<?= $rand ?>" />
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" name="submit" value="Add Event" />
	</div>
</form>