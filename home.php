<div class="sidebar-right">
	<div class="event-title">Check Your Loot!</div>
	<div class="menu">
		<form class="loot-widget" id="login" method="post" action="loot">
			<label for="pid">UCFID</label>
			<input type="text" name="pid" id="pid" maxlength="7" class="small" />
			<input type="submit" name="submit" value="Loot!" />
		</form>
	</div>	
	<div class="hr-clear"></div>
		
	<?= $data->html_social_button('facebook'); ?>
	<?= $data->html_social_button('twitter'); ?><br/>
	<a href="https://ucf.collegiatelink.net/organization/link"><img src="images/knightconnect.gif" alt="button" class="clean" /></a>&nbsp;
	<a href="https://itunes.apple.com/us/app/knightguide-ucf/id862828216?mt=8"><img src="images/knightguide-ios.gif" alt="button" class="clean" /></a><a href="https://play.google.com/store/apps/details?id=com.guidebook.apps.KnightGuide.android"><img src="images/knightguide-android.gif" alt="button" class="clean" /></a>
</div>
<div class="left">
	<p>
		LINK (Learning and Interacting with New Knights) is an education and involvement-based program to help
		first-time-in-college students get involved on campus. LINK is designed to get students
		out of their rooms and into the UCF community. With events from academic and learning
		programs to interaction and community-building events, there is something to help
		every student feel right at home.
	</p>

	<h2 class="header">Upcoming Programs</h2>
	<?php $sessions = getSessions(); ?>
	<?php printSessions($sessions); ?>
</div>
<div class="sidebar-right nomobile">
	<?= $data->html_twitter_feed('linkloot'); ?>

	<div class="hr-blank"></div>

	<img src="images/logo.jpg" alt="" class="floatright" />
</div>
<div class="hr-clear"></div>