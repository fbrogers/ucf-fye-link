<?php
	$title = 'RDS Data, Step 2';
	session_start();
	if(isset($_SESSION['transaction']) && !empty($_SESSION['transaction'])){
		/*-----------------------------------------------
		.ATTEMPT AN INSERT OF THE SESSION QUERY.
		-------------------------------------------------*/
		$queries = $_SESSION['transaction'];
		
		//start transaction
		sqlsrv_begin_transaction($conn) or die(print_r(sqlsrv_errors(), true));
		
		//loop through queries
		foreach($queries as $query){
			$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));
		}
		
		//commit the transaction
		sqlsrv_commit($conn);

		//pull back last insert
		$semester = term_convert(get_trm()).' '.date('Y');
		
		$query = "SELECT ROW_NUMBER() OVER(ORDER BY [studentPid]) AS iteration, 
		studentPid, studentLast, studentFirst, studentEmail, studentCard 
		FROM [student] WHERE [studentSemester] = ?";
		$result = sqlsrv_query($conn, $query, [$semester]) or die(print_r(sqlsrv_errors(), true));

		//print out the imported student data ?>
		
		<h3>Import Successful!</h3>

		<p>Listed below are the students for the current semester. If the students are not listed, please
		email SDES IT at <a href="mailto:sdestech@ucf.edu">sdestech@ucf.edu</a>.</p>

		<table class="grid smaller">
			<tr>
				<th scope="col">Count</th>
				<th scope="col">PID</th>
				<th scope="col">Last Name</th>
				<th scope="col">First Name</th>
				<th scope="col">Email</th>
				<th scope="col">Card Number</th>
			</tr>
			<?php while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
				echo '<tr>';
				foreach($row as $x){
					echo '<td>'.$x.'</td>';
				}
				echo '</tr>';
			} ?>
		</table>

		<?php
			session_destroy();
	}
	else{
		echo '<h3>No query in session.</h3>';
	}
?>