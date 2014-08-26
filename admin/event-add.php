<?php
	$title = 'Add Event';

	if(is_post()){
		$p = FormProcessor::oxyClean($_POST);
		
		$query = //sql query
		"INSERT INTO [event] (
			departmentId, themeId, eventName, eventPoints, eventDescription,
			eventDispute, contactName, contactPhone, contactEmail
		) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?); SELECT SCOPE_IDENTITY();";
		
		//parameters for query
		$params = [
			$p['department_id'],
			$p['theme_id'],
			$p['name'],
			$p['points'],
			$p['description'],
			$p['dispute'],
			$p['contact_name'],
			$p['contact_phone'],
			$p['contact_email']
		];
		
		//boosh
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		$event = getLastId($result);
		header("Location: ?id=session-add&event={$event}");
	}
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<legend>Event Information</legend>
		<div class="shift-right">
			<div class="global-col2-1">
				<label>Event Name</label> 
				<input type="text" name="name" />
			</div>
			<div class="global-col2-2">
				<label>Event LINK Points Worth</label> 
				<input type="text" name="points" />
			</div>
			<div class="hr-clear"></div>
			
			<div class="global-col2-1">
				<label for="dept">Sponsor Department</label>
				<select name="department_id" id="dept">
					<?= dropDownChanger('department', 'departmentId', 'departmentName'); ?>
				</select>
			</div>
			<div class="global-col2-2">
				<label for="theme">LINK Theme</label>
				<select name="theme_id" id="theme">
					<?= dropDownChanger('theme', 'themeId', 'themeName'); ?>
				</select>
			</div>
			<div class="hr-clear"></div>
			
			<label>Description</label> 
			<textarea name="description" rows="6" cols="60"></textarea>
			
			<label>Dispute Details</label> 
			<textarea name="dispute" rows="6" cols="60"></textarea>
			
			<div class="global-col3-1">
				<label>Contact Name</label> 
				<input type="text" name="contact_name" />
			</div>
			<div class="global-col3-2">
				<label>Contact Phone</label> 
				<input type="text" name="contact_phone" />
			</div>
			<div class="global-col3-3">
				<label>Contact E-mail</label> 
				<input type="text" name="contact_email" />
			</div>
			<div class="hr-clear"></div>
		</div>
	</fieldset>

	<div class="submitbox">
		<input type="submit" value="<?= $title; ?>" name="form_submit" />
	</div>
</form>