<?php
	if(!isset($_REQUEST['session']) or !is_numeric($_REQUEST['session'])){
		die('Session is required.');
	}

	//save to a local variable
	$session = $_REQUEST['session'];

	//post state
	if(is_post() && isset($_POST['action'])){

		switch($_POST['action']){
			case 'csv-card':
			
				//check for file upload errors
				$_FILES['foo']['error'] == UPLOAD_ERR_OK or die('File Upload Error: '.$_FILES['foo']['error']);
				$file = file_get_contents($_FILES['foo']['tmp_name']) or die("File Read Error");
				$lines = explode("\n", $file);
				
				//get all studentIds and studentCards
				$query = "SELECT studentId, studentCard FROM [student]";
				$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));

				//loop through returned rows
				while($row = sqlsrv_fetch_array($result)){
					$cards[$row['studentCard']] = $row['studentId'];
				}
				
				//initialize the studentId array
				$students = [];
				
				//loop over each line of the uploaded file
				foreach($lines as $x){

					//look for required UCF card string
					if(substr($x,0,2) == "%B" && is_numeric(substr($x,2,16))){

						//get the UCF card number
						$card = substr($x,2,16);

						//add to array and check for dupes
						if(isset($cards[$card]) && !in_array($cards[$card], $students)){

							//implicit typecast (haha, php typecast)
							$students[] = (string)$cards[$card];
						}
					}
				}

				//sort the array for no reason, really
				asort($students);
				
				//loop over culled studentIds
				foreach($students as $student){
					$query = "INSERT INTO [studentSession] (sessionId, studentId) VALUES (?, ?)";
					$params = [$session, $student];
					$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
				}
				
				//redirect to clear POST in history
				exit(header("Location: ?id=session-attend&session={$session}"));
				break;

			case 'csv-pid':
				//check for file upload errors
				$_FILES['foo']['error'] == UPLOAD_ERR_OK or die('File Upload Error: '.$_FILES['foo']['error']);
				$file = file_get_contents($_FILES['foo']['tmp_name']) or die("File Read Error");
				$lines = explode("\n", $file);
				
				//get all studentIds and studentCards
				$query = "SELECT studentId, studentPid FROM [student]";
				$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));

				//loop through returned rows
				while($row = sqlsrv_fetch_array($result)){
					$pids[$row['studentPid']] = $row['studentId'];
				}
				
				//initialize the studentId array
				$students = [];
				
				//loop over each line of the uploaded file
				foreach($lines as $x){

					//look for required pid num string
					if(is_numeric(substr(trim($x),0,7))){

						//get the pid string
						$pid = substr(trim($x),0,7);

						//add to array and check for dupes
						if(isset($pids[$pid]) && !in_array($pids[$pid], $students)){
							$students[] = (string)$pids[$pid];
						}
					}
				}
				
				//loop over culled studentIds
				foreach($students as $student){

					//insert the student into the linking table
					$query = "INSERT INTO [studentSession] (sessionId, studentId) VALUES (?, ?)";
					$params = [$session, $student];
					$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
				}
				break;

			case 'single-pid':
				//save the PID to a local variable
				$pid = $_POST['pid'];
				
				//get all studentIds and studentCards
				$query = "SELECT studentId FROM [student] WHERE studentPid = ?";
				$result = sqlsrv_query($conn, $query, [$pid]) or die(print_r(sqlsrv_errors(), true));
				$row = sqlsrv_fetch_array($result) or die(print_r(sqlsrv_errors(), true));
				$student = $row['studentId'];
				
				//insert garnered PID
				$query = "INSERT INTO [studentSession] (sessionId, studentId) VALUES (?, ?)";
				$result = sqlsrv_query($conn, $query, [$session, $student]) or die(print_r(sqlsrv_errors(), true));
				break;

			case 'student-delete':
				$query = "DELETE FROM [studentSession] WHERE (studentSessionId = ?)";
				$result = sqlsrv_query($conn, $query, [$_POST['student']]) or die(print_r(sqlsrv_errors(), true));
				exit(header('Location: ?id=session-attend&session='.$_GET['session']));
		}
	}

	//get event information
	$query = "SELECT TOP 1 E.eventName, S.sessionStart, S.sessionEnd, S.sessionLocation FROM [session] S 
	INNER JOIN [event] E ON S.eventId = E.eventId WHERE (S.sessionId = ?)";
	$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));

	//get single row return
	$page = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	//set page title
	$title = $page['eventName'];

	$query = //get associated student information
	"SELECT ROW_NUMBER() OVER(ORDER BY S.studentLast) AS count, 
		SS.studentSessionId, SS.studentId, S.studentPid, S.studentFirst, S.studentLast 
	FROM [studentSession] SS 
	INNER JOIN [student] S ON S.studentId = SS.studentId 
	WHERE (SS.sessionId = ?)";

	//commit query or die
	$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));

	//dump returned rows
	$students = [];
	while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$students[] = $row;
	}
?>

<script type="text/javascript">
	$(document).ready(function(){
		$('#focusfield').focus();
	});
</script>

<div class="global-col2-1 mzero">
	<h4>Date: <?= smalldate($page['sessionStart'], $page['sessionEnd']) ?></h4>
	<h4>Location: <?= $page['sessionLocation'] ?></h4>
	
	<div class="fieldset">
		<fieldset>
			<form enctype="multipart/form-data" action="" method="post">
				<label>Card Reader Data</label>
				<input type="hidden" name="session" value="<?= $session; ?>" />
				<input type="hidden" name="action" value="csv-card" />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
				<input type="file" name="foo" class="medium" />
				<input type="submit" value="Upload" name="form_submit" />
			</form>
			
			<div class="hr-blank"></div>
			
			<form enctype="multipart/form-data" action="" method="post">
				<label>CSV of PIDs</label>
				<input type="hidden" name="session" value="<?= $session; ?>" />
				<input type="hidden" name="action" value="csv-pid" />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
				<input type="file" name="foo" class="medium" />
				<input type="submit" value="Upload" name="form_submit" />
			</form>
			
			<div class="hr-blank"></div>
			
			<form action="" method="post">
				<label>Add by PID</label>
				<input type="hidden" name="session" value="<?= $session; ?>" />
				<input type="hidden" name="action" value="single-pid" />
				<input type="text" name="pid" class="medium" id="focusfield" maxlength="7" />
				<input type="submit" value="Add" name="form_submit" />
			</form>
		</fieldset>
	</div>
</div>

<div class="global-col2-2">		
	<table class="grid">
		<tr>
			<th scope="col"></th>
			<th scope="col">Name</th>
			<th scope="col">PID</th>
			<th scope="col"></th>
		</tr>
		<?php foreach($students as $row): ?>
		<tr>
			<td><?= $row['count'] ?></td>
			<td>
				<a href="?id=student-detail&amp;student=<?= $row['studentId']; ?>">
					<?= $row['studentLast'] ?>, <?= $row['studentFirst'] ?>
				</a>
			</td>
			<td><?= $row['studentPid'] ?></td>
			<td>
				<form action="" method="post">
					<input type="image" src="https://assets.sdes.ucf.edu/images/user_delete.png" />
					<input type="hidden" value="<?= $session ?>" name="session" />
					<input type="hidden" value="<?= $row['studentSessionId'] ?>" name="student" />
					<input type="hidden" name="action" value="student-delete" />
				</form>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>