<?php
if(isset($_POST['session']) && is_numeric($_POST['session'])){
	deleteSession($_POST['session']);
	header("Location: ?id=session");
}
?>