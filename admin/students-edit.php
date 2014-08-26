<?php
	if(!isset($_REQUEST['student']) or !is_numeric($_REQUEST['student'])){
		die('Student is required.');
	}

	$title = 'Edit Student';
	$student = $_REQUEST['student'];

	if(is_post()){
		//sanitize and assignment
		$post = FormProcessor::oxyClean($_POST);
		$first_name = $post['first_name'];
		$last_name = $post['last_name'];
		$email = $post['email'];
		$card_number = $post['card_number'];
		
		//mezmerise
		$query = "UPDATE [student] SET studentFirst = ?, studentLast = ?, studentEmail = ?,
		studentCard = ?, studentPid = ? WHERE [studentId] = ?";

		//parameters
		$params = [
			$post['first_name'],
			$post['last_name'],
			$post['email'],
			$post['card_number'],
			$post['pid'],
			$student
		];

		//run query
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

		//redirect if complete
		exit(header("Location: ?id=student-detail&student={$student}"));
	}	
	
	$query = "SELECT * FROM [student] WHERE [studentId] = ?";
	$result = sqlsrv_query($conn, $query, [$student]) or die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($result);
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<legend>Contact Information</legend>
		<div class="shift-right">
			<div class="global-col2-1">
				<label for="first_name">First Name</label> 
				<input type="text" name="first_name" id="first_name" maxlength="100" value="<?= $row['studentFirst'] ?>" />
			</div>
			<div class="global-col2-2">
				<label for="last_name">Last Name</label> 
				<input type="text" name="last_name" id="last_name" maxlength="100" value="<?= $row['studentLast'] ?>" />
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label for="email">Email Address</label> 
				<input type="text" name="email" id="email" maxlength="255" value="<?= $row['studentEmail'] ?>" />
			</div>
			<div class="global-col2-2">
				<label for="card_number">Card Number</label>
				<input type="card_number" name="card_number" id="card_number" maxlength="16" value="<?= $row['studentCard'] ?>" />
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label for="pid">PID (Numbers Only)</label> 
				<input type="text" name="pid" id="pid" maxlength="7" value="<?= $row['studentPid'] ?>" />
			</div>
			<div class="hr-clear"></div>
		</div>
	</fieldset>
	
	<div class="submitbox">
		<input type="hidden" value="<?= $row['studentId'] ?>" name="student" />
		<input type="submit" value="<?= $title ?>" name="form_submit" />
	</div>
</form>