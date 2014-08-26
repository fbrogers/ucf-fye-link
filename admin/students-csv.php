<?php $title = 'Students Upload'; ?>

<form enctype="multipart/form-data" action="?id=students-ftic" method="post" class="fieldset">
	<fieldset>
		<legend>Card Reader Data</legend>
		<input type="file" name="csv" />
	</fieldset>
	<div class="submitbox">
		<input type="submit" value="Upload" />
	</div>
</form>