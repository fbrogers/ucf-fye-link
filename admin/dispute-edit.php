<?php 
	if(isset($_POST['dispute']) && is_numeric($_POST['dispute']) && isset($_POST['action'])){
		//quick assignment to save keystrokes
		$dispute = $_POST['dispute'];
		
		//get student and session/event info
		$query = "SELECT TOP 1
			D.studentId,
			D.sessionId,
			created,
			studentEmail,
			eventName
		FROM [dispute] D 
		INNER JOIN [student] ST ON D.studentId = ST.studentId
		INNER JOIN [session] SE ON D.sessionId = SE.sessionId
		INNER JOIN [event] E ON SE.eventId = E.eventId
		WHERE disputeId = ?";
		
		$result = sqlsrv_query($conn, $query, [$dispute]);
		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
		
		if(hasCredit($row['studentId'], $row['sessionId'])){	

			//delete dispute outright
			$query = "DELETE FROM [dispute] WHERE disputeId = ?";
			$result = sqlsrv_query($conn, $query, [$dispute]) or die(print_r(sqlsrv_errors(), true));
			
		}elseif($_POST['action'] == 'Approve'){
			
			//give the student credit for the session/event
			$query = "INSERT INTO [studentSession] (studentId, sessionId) VALUES (?, ?)";
			$params = [$row['studentId'], $row['sessionId']];
			$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

			//remove the dispute from pending status
			$query = "UPDATE [dispute] SET isApproved = '1' WHERE disputeId = ?";
			$result = sqlsrv_query($conn, $query, [$dispute]) or die(print_r(sqlsrv_errors(), true));

			$body = 
			"<p>
				This automated email is to inform you that your dispute request, submitted
				on ".date('l, F jS g:ia', strtotime($row['created']))." for <em>".$row['eventName']."</em> has been
				approved and you have been given credit for the event. If there are any other concerns, 
				please reply to this email.
			</p>";

			try{
				$f = new FormProcessor();
				$f->from('link@ucf.edu');
				$f->to($row['studentEmail']);
				$f->subject('LINK | Dispute Approved');
				$f->body($body, false);
				$f->send();

			}catch(Exception $e){
				die('Error: '.$e->getMessage());
			}

		} elseif ($_POST['action'] == 'Deny'){

			$body = 
			"<p>
				This automated email is to inform you that your dispute request, submitted
				on ".date('l, F jS g:ia', strtotime($row['created']))." for <em>".$row['eventName']."</em> has
				been denied. If there are any other concerns, please reply to this email.
			</p>";	

			//start email
			try{
				$f = new FormProcessor();
				$f->from('link@ucf.edu');
				$f->to($row['studentEmail']);
				$f->subject('LINK | Dispute Approved');
				$f->body($body, false);
				$f->send();

				//remove the dispute from pending status
				$query = "UPDATE [dispute] SET isApproved = '0' WHERE disputeId = ?";
				$params = array($dispute);
				$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));

			}catch(Exception $e){
				die('Error: '.$e->getMessage());
			}
		}
	}
	
	header("Location: ?id=dispute");
?>