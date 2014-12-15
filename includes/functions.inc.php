<?php
	//definitions and converters
	define('SPR_END_DATE', '05-08');
	define('SUM_END_DATE', '08-07');
	
	$term_conv = ["spr" => "Spring", "sum" => "Summer", "fal" => "Fall", "all" => "All"];
	
	function getSessions(){
		require_once 'includes/connection.inc.php';
		$query = 
		"SELECT TOP 6 
			S.sessionId, S.eventId, D.departmentName, themeName, E.themeId, 
			eventName, eventPoints, eventDescription, contactName, contactEmail, 
			sessionStart, sessionEnd, sessionLocation, sessionRoom, sessionMapId
		FROM [session] S
		INNER JOIN [event] E ON S.eventId = E.eventId
		INNER JOIN [department] D ON E.departmentId = D.departmentId
		INNER JOIN [theme] T ON E.themeId = T.themeId
		WHERE (S.isApproved = '1') AND sessionStart > GETDATE() 
		ORDER BY S.sessionStart";
		$result = sqlsrv_query($conn, $query);
		$sessions = [];
		while($row = sqlsrv_fetch_array($result)){
			$sessions[] = $row;
		}

		return $sessions;
	}

	function printSessions($sessions){
		if(!empty($sessions)){
			echo '<script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
			foreach($sessions as $row){ ?>
		<div class="news">
			<div class="news-theme">
				<img src="images/theme/<?= $row['themeId'] ?>.png" alt="theme" /><br />
				<?= $row['themeName'] ?><br />
				<?= $row['eventPoints'] ?> Points
				<br />
				<!-- TWITTER SHARE-->
				<a href="https://twitter.com/share" 
					class="twitter-share-button" 
					data-url="<?= htmlentities('http://link.sdes.ucf.edu/programs/'.$row['eventId'].'/'.$row['sessionId']);?>" 
					data-text="I am going to <?= htmlentities($row['eventName']); ?> on <?= htmlentities(date('l, M jS', strtotime($row['sessionStart']))); ?>!" 
					data-via="<?= TWITTER ?>" data-related="<?= TWITTER ?>" data-count="none" data-hashtags="ucf">Tweet!</a>
				<!-- /TWITTER SHARE -->
				<br />
				<!-- FACEBOOK SHARE -->
				<a name="fb_share" onclick="window.open(this.href); return false" href="https://www.facebook.com/sharer.php?u=<?= urlencode("http://link.sdes.ucf.edu/programs/".$row['eventId']."/".$row['sessionId']); ?>&amp;t=<?= urlencode($row['eventName']) ?>">
					<img src="images/facebook-share.png" alt="facebook share" class="facebook clean" />
				</a> 
				<!-- /FACEBOOK SHARE -->
			</div>
			<div class="news-content">
				<div class="news-title bullets">
					<a href="programs/<?= $row['eventId'] ?>/<?= $row['sessionId'] ?>">
						<?= $row['eventName'] ?>
					</a>
				</div>

				<div class="news-strapline">
					<?= display_date($row['sessionStart'], $row['sessionEnd']) ?>
				</div>

				<div class="news-strapline">
					<a href="http://map.ucf.edu/?show=<?= $row['sessionMapId'] ?>"><?= $row['sessionLocation'] ?></a>
					<?= $row['sessionRoom'] ?>
				</div>

				<?php if($row['contactName'] != NULL): ?>
					<div class="datestamp">
						Contact:
						<a href="mailto:<?= $row['contactEmail']; ?>"><?= $row['contactName'] ?></a>
					</div>
				<?php endif; ?>

				<p>
					<?= nl2br($row['eventDescription']) ?>
				</p>
			</div>
		</div>
		<div class="hr-blank"></div>
		<?php }
		} else {
			echo '<p class="larger">No sessions for the current month.</p>';
		}
	}
	
	function getEvents($conn){
		//get the next 6 events
		$query = "SELECT TOP 6 E.eventId, sessionId, eventName, eventDescription, eventPoints, sessionStart, sessionLocation, sessionMapId
		FROM [session] S
		INNER JOIN [event] E ON S.eventId = E.eventId
		WHERE S.isApproved = 1 AND S.sessionStart > GETDATE()
		ORDER BY S.sessionStart ASC";
		
		$result = sqlsrv_query($conn, $query);
		$sessions = [];
		while($row = sqlsrv_fetch_array($result)){
			$sessions[] = $row;
		}

		//loop through results			
		foreach($sessions as $count => $row){
			echo 
			@'<li class="event">
				<div class="date">
					<span class="month">'.date('M', strtotime($row['sessionStart'])).'</span>
					<span class="day">'.date('d', strtotime($row['sessionStart'])).'</span>
				</div>
				<a class="title" href="programs/'.$row['eventId'].'/'.$row['sessionId'].'">'.$row['eventName'].'</a>
				<a href="http://map.ucf.edu/?show='.$row['sessionMapId'].'">'.$row['sessionLocation'].'</a>
				<a href="#">'.$row['eventPoints'].' Points</a>
				<div class="end"></div>
			</li>';
		}
	}

	function echoDept($conn){
		$output = null;
		$query = "SELECT departmentId, departmentName FROM department ORDER BY departmentName";
		$result = sqlsrv_query($conn, $query);
		while($row = sqlsrv_fetch_array($result)){
			echo '<option value="'.$row['departmentId'].'">'.$row['departmentName'].'</option>'."\n";
		}
		return $output;
	}

	function getPartners(){
		require_once 'includes/connection.inc.php';
		$partners = [];
		$query = "SELECT * FROM [department] WHERE isDisplayed = '1' ORDER BY [departmentName] ASC";
		$result = sqlsrv_query($conn, $query) or die(print_r(sqlsrv_errors(), true));
		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$partners[] = $row;
		}
		return $partners;
	}
	
	function reduce($string, $slice){
		if(isset($string[$slice])) return substr_replace($string, '...', $slice);
		else return $string;
	}
		
	function display_date($start, $end) {
		//if dates are identical, combine day print
		if(date('d M Y', strtotime($start)) == date('d M Y', strtotime($end))) {
			return date('l, M jS, g:ia', strtotime($start))." &ndash; ".date('g:ia', strtotime($end));
			
		//else, echo both days and times
		} else {
			return date('l, M jS, g:ia', strtotime($start))." &ndash; ".date('l, M jS, g:ia', strtotime($end));
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
	
	function filePack($file){
		$blacklist = array(".php", ".phtml", ".php3", ".php4", ".exe");  
		foreach ($blacklist as $item){
			if(preg_match("/$item$/i", $_FILES[$file]['name']))	die("We do not allow the uploading of one of the file(s) you attempted to upload.");
		}
		$extension = explode('.', $_FILES[$file]['name']);
		$filetype = end($extension);
		$tempname = $_FILES[$file]['tmp_name'];
		$datablob = file_get_contents($tempname);
		return array('blob' => $datablob, 'ext' => $filetype);
	}
?>