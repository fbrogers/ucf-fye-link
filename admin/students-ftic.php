<?php
	/*---------------------------------------------------------------------
	Purpose:
	The LINK Program needs new FTIC (First Time In College) data from the 
	University-wide PeopleSoft database called "RDS". The query was
	originally written long before my time and probably could be
	optimized further.

	Data is pulled from RDS, stored, and checked against the current LINK
	user table. Duplicates are shown. If the user prompts to continue,
	the data will be imported into the LINK database.
	---------------------------------------------------------------------*/
	session_start();
	$title = 'FTIC Data, Step 1';
	$links = ['Translode FTICs to LINK Database' => '?id=students-ftic-insert'];

	/*-----------------------------------------------
	.SEMESTER DEFINITION.

	Defines the semester for use in the RDS query.
	Not sanitized because the admin. console is using
	Integrated/Windows Authentication.
	-------------------------------------------------*/
	define('SEMESTER', term_convert(get_trm()).' '.date('Y'));

	/*-----------------------------------------------
	.CURRENT LINK USER TABLE DUMP.
	-------------------------------------------------*/
	$pid_collection = [];
	$sdes_query = "SELECT [studentPid] FROM [student]";
	$sdes_result = sqlsrv_query($conn, $sdes_query);
	while($sdes_row = sqlsrv_fetch_array($sdes_result, SQLSRV_FETCH_ASSOC)){
		$pid_collection[] = $sdes_row['studentPid'];
	}

	/*-----------------------------------------------
	.OPEN FILE.
	-------------------------------------------------*/

	if($_FILES['csv']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['csv']['tmp_name'])){
		$csv = file_get_contents($_FILES['csv']['tmp_name']); 
		$csv = explode("\n", $csv);

		/*-----------------------------------------------
		.INSERT TRANSACTION QUERY BUILDER.

		As the returned data is printed into a viewable form,
		the query for inserting the data into the database is
		constructed and stored in the session. Duplicate
		rows are detected and ignored.
		-------------------------------------------------*/
		$db_query = [];
		$rds_data = @'<div style="width: 100%; height: 500px; overflow: auto;">
			<table class="grid smaller">
			<tr>
				<th scope="col">Count</th>
				<th scope="col">PID</th>
				<th scope="col">Last Name</th>
				<th scope="col">First Name</th>
				<th scope="col">Email</th>
				<th scope="col">UCF Card #</th>
			</tr>';

		foreach($csv as $iterator => $row){
			$pieces = explode(',',$row);
			if(count($pieces) == 5){

				//pre-structure each piece of information
				$pid = substr($pieces[0], 0, 7);
				$lname = str_replace("'", "''", $pieces[1]);
				$fname = str_replace("'", "''", $pieces[2]);
				$email = $pieces[3];
				$card = substr($pieces[4], 0, 16);

				if(isset($pid, $fname, $lname, $email, $card) && trim($email) != NULL && $email != 'NULL' && trim($card) != NULL){
					$rds_data .= in_array($pid, $pid_collection) ? '<tr style="background: #f99">' : '<tr>';
					$rds_data .= '<th scope="row">'.$iterator.'</th>';
					foreach($pieces as $x) $rds_data .= '<td>'.$x.'</td>';
					$rds_data .= '</tr>';

					if(!in_array($pid,$pid_collection)){
						$db_query[] = sprintf("INSERT INTO [student] (studentPid, studentCard, studentFirst, studentLast, studentEmail, studentSemester) 
						VALUES ('%s','%s','%s','%s','%s','%s'); ", $pid, $card, $fname, $lname, $email, SEMESTER);
					}
				}
			}
		}

		$rds_data .= '</table>';
		$rds_data .= '</div>';
		$_SESSION['transaction'] = $db_query;

		/*-----------------------------------------------
		.HTML BLOCK AND ECHO.
		-------------------------------------------------*/	?>
		<script type="text/javascript">
			$(document).ready(function(){
				$('a#action').click(function(){
					if(confirm('Are you sure?')){
						return true;
					}
					return false;
				});
			});
		</script>

		<?= $rds_data; 
	}
?>