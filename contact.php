<?php $title = 'Contact'; ?>

<div class="sidebar-right">
	<div class="menu">
		<?= $data->html_block_contact(); ?>
	</div>

	<div class="hr-clear"></div>

	<img src="images/logo-white.jpg" alt="" class="floatright" />
</div>
<div class="left">
	<p>For LINK questions or inquiries, the best and most efficient way to contact us is via email.</p>

	<div class="hr"></div>

	<?= $data->get_directory_helper()->PrintStaff(); ?>
</div>
<div class="hr-clear"></div>