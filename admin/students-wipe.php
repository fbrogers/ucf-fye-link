<?php
	session_start();
	$title = 'Confirmation';
	$authname = $_SERVER['AUTH_USER'];
	$token = md5(mt_rand());
	$_SESSION['token'] = $token;
?>

<script type="text/javascript">
	$(document).ready(function(){
		$('.delete').click(function(){
			var row = $(this);
			if(confirm('Are you sure you wish to continue?\nONE DOES NOT SIMPLY WALK INTO MORDOR.\nTHERE IS NO GOING BACK.')){
				var insurance = $('#insurance').val();
				var token = $('#token').val();
				var authname = $('#authname').val();
				
				$.post("students-wipe-del.php",
				{ insurance:insurance, token:token, authname:authname },
				function(data){
					$('.message').remove();
					$('<div class="message">'+data+'</div>').insertAfter('div.top:first');
				});
			}
			return false;
		});
	});
</script>

<h3>You are about to erase ALL student data, including associations from students to sessions, attendance data, etc.
If you are sure you want to proceed, type in your current SDES username in the box below and click Submit.</h3>

<form class="fieldset" action="" method="post">
	<fieldset>
		<legend>Insurance</legend>
		<div class="shift-right">
			<label for="insurance">SDES Username</label>
			<input type="text" name="insurance" id="insurance" maxlength="15" />
			<input type="hidden" name="token" id="token" value="<?= $token ?>" />
			<input type="hidden" name="authname" id="authname" value="<?= $authname ?>" />
		</div>
	</fieldset>
	<div class="submitbox">
		<input type="submit" class="delete" value="<?= $title; ?>" />
	</div>
</form>