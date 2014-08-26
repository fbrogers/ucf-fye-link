<?php if(!isset($data) or !($data instanceof TemplateData)) die('Data not passed.');

	$data->site_template('ucf_admin');
	$data->site_title('Administration Console');
	$data->site_subtitle('Learning and Interacting with New Knights');
	$data->site_subtitle_href('../');
	$data->site_navigation([
		'Pending' => '?id=approve',
		'Events' => '?id=event',
		'Sessions' => '?id=session',
		'Students' => '?id=students',
		'Disputes' => '?id=dispute',
		'Departments' => '?id=departments',
		'Reports' => '?id=reports'
	]);
?>