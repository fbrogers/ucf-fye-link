<?php 
	$title = 'Edit Department';

	if(is_post()){
		//insert query
		$query = "UPDATE [department] SET 
			departmentName = ?, 
			departmentAcronym = ?,
			departmentPhone = ?,
			departmentEmail = ?,
			departmentUrl = ?,
			departmentLocation = ?,
			departmentMapId = ?,
			isDisplayed = ?
		WHERE (departmentId = ?)";
		
		//sanitize
		$p = FormProcessor::oxyClean($_POST);
		
		//parameters for query
		$params = [
			$p['departmentName'],
			$p['departmentAcronym'],
			$p['departmentPhone'],
			$p['departmentEmail'],
			$p['departmentUrl'],
			$p['departmentLocation'],
			$p['departmentMapId'],
			$p['isDisplayed'],
			$p['dept']
		];
		
		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		exit(header("Location: ?id=departments"));
	}

	$query = "SELECT * FROM [department] WHERE departmentId = ?";
	$result = sqlsrv_query($conn, $query, [$_GET['dept']]);
	$row = sqlsrv_fetch_array($result);	
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<div class="shift-right">
			<label for="departmentName">Name</label>
			<input type="text" name="departmentName" id="departmentName" value="<?= $row['departmentName'] ?>" />
			
			<label for="departmentAcronym">Acronym</label>
			<input type="text" name="departmentAcronym" id="departmentAcronym" value="<?= $row['departmentAcronym'] ?>" />
			
			<label for="departmentPhone">Phone</label>
			<input type="text" name="departmentPhone" id="departmentPhone" value="<?= $row['departmentPhone'] ?>" />
			
			<label for="departmentEmail">Email</label>
			<input type="text" name="departmentEmail" id="departmentEmail" value="<?= $row['departmentEmail'] ?>" />
			
			<label for="departmentUrl">Website URI</label>
			<input type="text" name="departmentUrl" id="departmentUrl" value="<?= $row['departmentUrl'] ?>" />
			
			<label for="departmentLocation">Location</label>
			<input type="text" name="departmentLocation" id="departmentLocation" value="<?= $row['departmentLocation'] ?>" />
			
			<label for="departmentMapId">UCF Map ID</label>
			<input type="text" name="departmentMapId" id="departmentMapId" value="<?= $row['departmentMapId'] ?>" />
			
			<label>Is Displayed on the Partners Page?</label>
			<input type="radio" name="isDisplayed" id="isDisplayed1" value="1" <?= $row['isDisplayed'] === 1 ? 'checked="checked"' : null;?> />
			<label for="isDisplayed1">Yes</label>
			<input type="radio" name="isDisplayed" id="isDisplayed2" value="0" <?= $row['isDisplayed'] === 0 ? 'checked="checked"' : null;?> />
			<label for="isDisplayed2">No</label>
		</div>
	</fieldset>
	<div class="submitbox">
		<input type="hidden" name="dept" value="<?= $row['departmentId'] ?>" />
		<input type="submit" value="<?= $title ?>" name="form_submit" />
	</div>
</form>