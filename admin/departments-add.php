<?php
	$title = 'Add Department';

	if(is_post()){
		$p = FormProcessor::oxyClean($_POST);
		
		//insert query
		$query = "INSERT INTO [department] (
			departmentName, 
			departmentAcronym,
			departmentPhone,
			departmentEmail,
			departmentUrl,
			departmentLocation,
			departmentMapId,
			isDisplayed
		) VALUES (?,?,?,?,?,?,?,?)";
		
		//parameters for query
		$params = array(
			$p['departmentName'],
			$p['departmentAcronym'],
			$p['departmentPhone'],
			$p['departmentEmail'],
			$p['departmentUrl'],
			$p['departmentLocation'],
			$p['departmentMapId'],
			$p['isDisplayed']
		);

		$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
		exit(header("Location: ?id=departments"));
	}
?>

<form action="" method="post" class="fieldset">
	<fieldset>
		<div class="shift-right">
			<label for="departmentName">Name</label>
			<input type="text" name="departmentName" id="departmentName" />
			
			<label for="departmentAcronym">Acronym</label>
			<input type="text" name="departmentAcronym" id="departmentAcronym" />
			
			<label for="departmentPhone">Phone</label>
			<input type="text" name="departmentPhone" id="departmentPhone" />
			
			<label for="departmentEmail">Email</label>
			<input type="text" name="departmentEmail" id="departmentEmail" />
			
			<label for="departmentUrl">Website URI</label>
			<input type="text" name="departmentUrl" id="departmentUrl" />
			
			<label for="departmentLocation">Location</label>
			<input type="text" name="departmentLocation" id="departmentLocation" />
			
			<label for="departmentMapId">UCF Map ID</label>
			<input type="text" name="departmentMapId" id="departmentMapId" />
			
			<label>Is Displayed on the Partners Page?</label>
			<input type="radio" name="isDisplayed" id="isDisplayed1" value="1" />
			<label for="isDisplayed1">Yes</label>

			<input type="radio" name="isDisplayed" id="isDisplayed2" value="0" />
			<label for="isDisplayed2">No</label>
		</div>
	</fieldset>
	<div class="submitbox">
		<input type="submit" value="<?= $title ?>" />
	</div>
</form>