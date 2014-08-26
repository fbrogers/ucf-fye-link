<?php
	$title = 'Add New Student'; 

	if(is_post()){
		//sanitize
		$x = FormProcessor::oxyClean($_POST);
		$starting_semester = $x['term'].' '.$x['year'];
		
		//insert query from the form post
		$query = "INSERT INTO [student] (studentPid, studentCard, studentFirst,
		studentLast, studentEmail, studentSemester) VALUES (?, ?, ?, ?, ?, ?); 
		SELECT SCOPE_IDENTITY();";

		//parameters
		$params = [$x['pid'], $x['card_number'], $x['first_name'], $x['last_name'], $x['email'], $starting_semester];

		//run query
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

		//get inserted primary key
		$student = getLastId($result);

		//redirect
		header("Location: ?id=student-detail&student=".$student);
	}
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<legend>Contact Information</legend>
		<div class="shift-right">
			<div class="global-col2-1">
				<label for="first_name">First Name</label> 
				<input type="text" name="first_name" maxlength="100" />
			</div>
			<div class="global-col2-2">
				<label for="last_name">Last Name</label> 
				<input type="text" name="last_name" maxlength="100" />
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label for="email">E-mail Address</label> 
				<input type="text" name="email" id="email" maxlength="255" />
			</div>
			<div class="global-col2-2">
				<label for="pid">PID (Numbers Only)</label>
				<input type="pid" name="pid" id="pid" maxlength="7" />
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label for="card_number">Card Number</label>
				<input type="card_number" name="card_number" id="card_number" maxlength="16" />
			</div>
			<div class="global-col2-2">
				<label for="term">Starting Semester</label>
				<select id="term" name="term" style="width: auto;">
					<option></option>
					<option>Fall</option>
					<option>Spring</option>
					<option>Summer</option>
				</select>
				<select id="year" name="year" style="width: auto;">
					<option></option>
					<option><?= date('Y')-1 ?></option>
					<option><?= date('Y') ?></option>
					<option><?= date('Y')+1 ?></option>
				</select>
			</div>
			<div class="hr-clear"></div>
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" value="<?= $title ?>" />
	</div>
</form>