<?php
	function term_convert($term){
		$term_conv = [
			"spr" => "Fall &amp; Spring",
			"sum" => "Summer",
			"all" => "All"
		];

		return $term_conv[$term];
	}

	function hasCredit($student, $session){
		//open sql connection
		$instance = db::instance();
		$conn = $instance::connect();

		//get session's event ID
		$query = "SELECT TOP 1 studentSessionId FROM [studentSession] WHERE studentId = ? AND sessionId = ?";
		$result = sqlsrv_query($conn, $query, [$student, $session]) or die(print_r(sqlsrv_errors(), true));
		return sqlsrv_has_rows($result);
	}
	
	function deleteSession($session){
		//open sql connection
		$instance = db::instance();
		$conn = $instance::connect();

		//get session's event ID
		$query = "SELECT eventId FROM [session] WHERE sessionId = ?";
		$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));
		$row = sqlsrv_fetch_array($result);
		$event = $row['eventId'];

		//delete session
		$query = "DELETE FROM [session] WHERE (sessionId = ?)";
		$result = sqlsrv_query($conn, $query, [$session]) or die(print_r(sqlsrv_errors(), true));

		//check to see if event has any sessions
		$query = "SELECT E.eventId FROM [event] E INNER JOIN [session] S ON E.eventId = S.eventId WHERE E.eventId = ?";
		$result = sqlsrv_query($conn, $query, [$event]) or die(print_r(sqlsrv_errors(), true));

		if(!sqlsrv_has_rows($result)){
			//delete event from database if no sessions left
			$query = "DELETE FROM [event] WHERE [eventId] = ?";
			$beleted = sqlsrv_query($conn, $query, [$event]) or die(print_r(sqlsrv_errors(), true));
		}
	}

	function get_partners(){
		//open sql connection
		$instance = db::instance();
		$conn = $instance::connect();

		//init output
		$output = NULL;

		//select all departments
		$query = "SELECT departmentId, COALESCE(departmentAcronym, departmentName) AS dept FROM department ORDER BY dept";
		$result = sqlsrv_query($conn, $query);
		while($row = sqlsrv_fetch_array($result)){
			$output .= '<option value="'.$row['departmentId'].'">'.$row['dept'].'</option>'."\n";
		}

		//return html
		return $output;
	}

	function reduce($string, $slice){
		if(isset($string[$slice])){
			return substr_replace($string, '&hellip;', $slice);
		} else {
			return $string;	
		}
	}
		
	function display_date($start, $end){
		if(date('d M Y', strtotime($start)) == date('d M Y', strtotime($end))){
			return date('D, d M Y\<\b\r\ \/\>g:ia', strtotime($start))." to ".date('g:ia', strtotime($end));
		} else {
			return date('D, d M Y g:ia', strtotime($start))." to<br />".date('D, d M Y g:ia', strtotime($end));
		}
	}
	
	function smalldate($start, $end){
		if(date('d M Y', strtotime($start)) == date('d M Y', strtotime($end))){
			return date('Y-m-d g:ia', strtotime($start))." / ".date('g:ia', strtotime($end));
		} else {
			return date('Y-m-d g:ia', strtotime($start)). " / ".date('Y-m-d g:ia', strtotime($end));
		}
	}
	
	function get_trm($date = "now"){
		$now = strtotime($date);
		if($now > strtotime('08 May') && $now < strtotime('07 August')){
			return 'sum';
		}			
		return 'spr';
	}	
?>