<?php
	if(!isset($_REQUEST['event']) or !is_numeric($_REQUEST['event'])){
		die('Event is necessary.');
	}

	$title = 'Add Session'; 
	$event = $_REQUEST['event'];
	
	if(is_post()){
		$start_time = $_POST['syear'].'-'.$_POST['smonth'].'-'.$_POST['sday'].' '.$_POST['stime1'].':'.$_POST['stime2'].':00.0000000';
		$end_time = $_POST['eyear'].'-'.$_POST['emonth'].'-'.$_POST['eday'].' '.$_POST['etime1'].':'.$_POST['etime2'].':00.0000000';
		$p = FormProcessor::oxyClean($_POST);
		$term = get_trm($start_time);
		
		//map name and map ID
		$map_pieces = explode('|', $p['location']);
					
		//insert query
		$query = 
		"INSERT INTO [session]
		(
			eventId,
			sessionStart,
			sessionEnd,
			sessionLocation,
			sessionRoom,
			sessionMapId,
			sessionSemester,
			isApproved
		) 
		VALUES (?, ?, ?, ?, ?, ?, ?, '1')";

		//run query
		$params = [$event, $start_time, $end_time, $map_pieces[0], $p['room'], $map_pieces[1], $term];
		$result = sqlsrv_query($conn, $query, $params) or die(print_r( sqlsrv_errors(), true));
		header("Location: ?id=session");
	}
	
	//inital session table query
	$query = "SELECT * FROM [event] E WHERE eventId = ?";
	$result = sqlsrv_query($conn, $query, [$event]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result);
	
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
			$final[$x['id']] = $x['title'];
		}
	}
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<legend>Event Information</legend>
		<div class="space-left">
			<p><strong>Name</strong>:<br /><?= $row['eventName']; ?></p>
			<p><strong>Points</strong>:<br /><?= $row['eventPoints']; ?></p>
			<p><strong>Description</strong>:<br /><?= $row['eventDescription']; ?></p>
			<p><strong>Contact</strong>:<br /><?= $row['contactName']; ?></p>
		</div>
		<div class="shift-right">
			<input type="hidden" name="event" value="<?= $event; ?>" />
		
			<div class="global-col2-1">
				<label>Start Date</label>
				<select name="smonth" style="width: auto;">
					<option value="01">Jan</option>
					<option value="02">Feb</option>
					<option value="03">Mar</option>
					<option value="04">Apr</option>
					<option value="05">May</option>
					<option value="06">Jun</option>
					<option value="07">Jul</option>
					<option value="08">Aug</option>
					<option value="09">Sep</option>
					<option value="10">Oct</option>
					<option value="11">Nov</option>
					<option value="12">Dec</option>
				</select>
				<select name="sday" style="width: auto;">
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
					<option value="31">31</option>
				</select>
				<select name="syear" style="width: auto;">
					<option><?= date('Y'); ?></option>
					<option><?= date('Y')+1; ?></option>
				</select>
			</div>
			<div class="global-col2-2">
				<label>Start Time</label>
				<select name="stime1" style="width: auto;">
					<option value="00">00</option>
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12" selected="selected">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
				</select> :
				<select name="stime2" style="width: auto;">
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
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label>End Date</label>
				<select name="emonth" style="width: auto;">
					<option value="01">Jan</option>
					<option value="02">Feb</option>
					<option value="03">Mar</option>
					<option value="04">Apr</option>
					<option value="05">May</option>
					<option value="06">Jun</option>
					<option value="07">Jul</option>
					<option value="08">Aug</option>
					<option value="09">Sep</option>
					<option value="10">Oct</option>
					<option value="11">Nov</option>
					<option value="12">Dec</option>
				</select>
				
				<select name="eday" style="width: auto;">
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
					<option value="31">31</option>
				</select>
				
				<select name="eyear" style="width: auto;">
					<option><?= date('Y'); ?></option>
					<option><?= date('Y')+1; ?></option>
				</select>
			</div>
			<div class="global-col2-2">
				<label>End Time</label>
				<select name="etime1" style="width: auto;">
					<option value="00">00</option>
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12" selected="selected">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
				</select> :
				<select name="etime2" style="width: auto;">
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
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">	
				<label for="location">Location</label>
				<select name="location" id="location" class="required">
					<option></option>
					<?php foreach($final as $i => $x): ?>
					<option value="<?= $x ?>|<?= $i ?>"><?= $x ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="global-col2-2">	
				<label for="room">Room / Other Details</label>
				<input type="text" name="room" id="room" maxlength="100" class="required" />
			</div>
			<div class="hr-clear"></div>
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" value="<?= $title; ?>" />
	</div>
</form>