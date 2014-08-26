<?php
	$title = 'Reports';
	$links = ['Guidebook Event Export' => '?id=reports-guidebook'];
	$cmonth = date('m');
	$cterm = get_trm();
?>

<div class="fieldset half">	
	<form action="?id=reports-student" method="post">
		<input type="submit" name="action" value="Student Report"> for 
		<select name="term">
			<option></option>
			<option value="spr">Fall &amp; Spring</option>
			<option value="sum">Summer</option>
		</select>
	</form>

	<div class="hr-blank"></div>

	<form action="?id=reports-month" method="post">
		<input type="submit" name="action" value="Monthly Report"> for 
		<select name="month">
			<option></option>
			<option value="01">January</option>
			<option value="02">February</option>
			<option value="03">March</option>
			<option value="04">April</option>
			<option value="05">May</option>
			<option value="06">June</option>
			<option value="07">July</option>
			<option value="08">August</option>
			<option value="09">September</option>
			<option value="10">October</option>
			<option value="11">November</option>
			<option value="12">December</option>
		</select>
	</form>
</div>