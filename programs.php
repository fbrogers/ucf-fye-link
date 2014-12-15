<?php
$title = 'Programs';
require_once 'includes/connection.inc.php';

if(isset($_GET['xid'])){	
	if(!is_numeric($_GET['xid'])){
		header("Location: ../programs");
	}

	//get sessions
	$query = "SELECT * FROM [session] S
	INNER JOIN [event] E ON S.eventId = E.eventId
	INNER JOIN [department] D ON E.departmentId = D.departmentId
	INNER JOIN [theme] T ON E.themeId = T.themeId
	WHERE S.isApproved = '1' AND E.eventId = ? ORDER BY S.sessionStart";
	$params = array($_GET['xid']);
	$result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
	
	//check for existing rows
	sqlsrv_has_rows($result) or die(header("Location:../programs"));
	
	//dump data to sessions array
	$sessions = [];
	while($row = sqlsrv_fetch_array($result)){
		$sessions[] = $row;
	}

	//quick access
	$event = $sessions[0];
	
	//set title
	$title = $event['eventName'];
	
	//start table of sessions
	echo '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
	echo @'<div class="left">';
	
	//event details
	echo '<p class="larger">'.nl2br($event['eventDescription']).'</p>';
	echo '<div class="hr-clear"></div>';
	
	//social media integration
	$links = 
	'<div class="content-main-links">
		<!-- TWITTER SHARE-->
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="I am going to -'.htmlentities($title).'-!" data-via="ucflink" data-related="ucflink" data-count="none" data-hashtags="ucf">Tweet This Program!</a>
		<!-- /TWITTER SHARE -->
		<!-- FACEBOOK SHARE -->
		<a name="fb_share" onclick="window.open(this.href); return false" href="https://www.facebook.com/sharer.php?u='.urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']).'&amp;t='.urlencode($title).'">
			<img src="images/facebook-share.png" alt="facebook share" class="facebook clean" />
		</a> 
		<!-- /FACEBOOK SHARE -->
	</div>';
	
	//start table of sessions
	echo @'<h3>Sessions of this Event</h3>
	<table class="grid smaller mobile">
		<tr>
			<th scope="col">Date</th>
			<th scope="col">Start</th>
			<th scope="col">End</th>
			<th scope="col">Location</th>
		</tr>';
	
	//table for sessions
	foreach($sessions as $session){
	
		//highlight passed session
		echo isset($_GET['yid']) && $_GET['yid'] == $session['sessionId']
			? '<tr class="selected">'
			: '<tr>';
			
		//finish out table
		echo '<td>'.date('l, F jS', strtotime($session['sessionStart'])).'</td>';
		echo '<td>'.date('g:ia', strtotime($session['sessionStart'])).'</td>';
		echo '<td>'.date('g:ia', strtotime($session['sessionEnd'])).'</td>';
		echo '<td><a href="http://map.ucf.edu/?show='.$session['sessionMapId'].'">'.$session['sessionLocation'].'</a> '.$session['sessionRoom'].'</td>';
		echo '</tr>';
	}

	//end table of sessions
	echo @'</table>';
	echo '</div>';
	
	//sidebar
	echo '<div class="sidebar-right">
		<div class="event-title">Partner Information</div>
		<div class="menu">
			<table class="grid smaller">
				<tbody>
					<tr>
						<th scope="row">Contact</th>
						<td><a href="mailto:'.$event['contactEmail'].'">'.$event['contactName'].'</a></td>
					</tr>
					<tr>
						<th scope="row">Partner</th>
						<td>'.$event['departmentName'].'</td>
					</tr>
					<tr>
						<th scope="row">Phone</th>
						<td>'.$event['departmentPhone'].'</td>
					</tr>
					<tr>
						<th scope="row">E-mail</th>
						<td><a href="mailto:'.$event['departmentEmail'].'">'.$event['departmentEmail'].'</a></td>
					</tr>
					<tr>
						<th scope="row">Location</th>
						<td><a href="http://map.ucf.edu/?show='.$event['departmentMapId'].'">'.$event['departmentLocation'].'</a></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>';

	//end
	echo '<div class="hr-clear"></div>';
	
} else {
	
	$links = @'<div class="content-main-links">
	<form action="programs" method="get" name="select_month">
		Select a Month: <select name="month" onchange="document.select_month.submit();return false;">
			<option></option>
			<optgroup label="Summer">
				<option value="05">May</option>
				<option value="06">June</option>
				<option value="07">July</option>
			</optgroup>
			<optgroup label="Fall">
				<option value="08">August</option>
				<option value="09">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option>
			</optgroup>
			<optgroup label="Spring">
				<option value="01">January</option>
				<option value="02">February</option>
				<option value="03">March</option>
				<option value="04">April</option>
			</optgroup>
		</select>
	</form></div>';
	
	//initialize variable
	$limit = NULL;

	//set sql query filter
	if(isset($_GET['month'])){
		$limit .= "AND MONTH(S.sessionStart) = '".$_GET['month']."'"; 
		$month = $_GET['month'];
	} else {
		$limit .= "AND MONTH(S.sessionStart) = '".date("m")."'"; 
		$month = date("m");
	}
	
	$title .= ' for '.date('F', mktime(0, 0, 0, $month, 1, 1));

	$query = "SELECT S.sessionId, S.eventId, D.departmentName, themeName, E.themeId, 
			eventName, eventPoints, eventDescription, contactName, contactEmail, 
			sessionStart, sessionEnd, sessionLocation, sessionRoom, sessionMapId
			FROM [session] S
			INNER JOIN [event] E ON S.eventId = E.eventId
			INNER JOIN [department] D ON E.departmentId = D.departmentId
			INNER JOIN [theme] T ON E.themeId = T.themeId
			WHERE (S.isApproved = '1') ".$limit." 
			ORDER BY S.sessionStart";
			
	$result = sqlsrv_query($conn, $query);
	$sessions = [];
	while($row = sqlsrv_fetch_array($result)){
		$sessions[] = $row;
	}?>

	<div class="left">
		<?php printSessions($sessions); ?>
	</div>

	<div class="sidebar-right">
		<img src="images/logo-white.jpg" alt="" class="floatright" />
	</div>

	<div class="hr"></div>

	<p><em>
		Students cannot earn multiple points for attending multiple sessions of the
		same program. All points earned during Orientation will count toward your Fall LINK Loot.
	</em></p>
<?php } ?>