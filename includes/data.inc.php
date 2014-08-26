<?php if(!isset($data) or !($data instanceof TemplateData)) die('Data not passed.');

define('EMAIL', 'link@ucf.edu');
define('TWITTER', 'linkloot');

$data->site_template('ucf');
$data->site_base('http://'.$_SERVER['SERVER_NAME'].'/');
$data->site_title('LINK Program');
$data->site_subtitle('First Year Experience');
$data->site_subtitle_href('http://fye.sdes.ucf.edu/');
$data->site_css('https://assets.sdes.ucf.edu/plugins/jqueryui/css/ui-lightness/jqueryui.css', 'screen');
$data->site_css('css/style.css','screen');
$data->site_js('https://assets.sdes.ucf.edu/scripts/jquery.validate.js');
$data->site_js('https://assets.sdes.ucf.edu/plugins/jqueryui/js/jqueryui.js');
$data->site_gaid('UA-6602068-3');
$data->site_navigation([
	'Home' => './',
	'About' => 'about',
	'Link Loot' => 'linkloot',
	'Programs' => 'programs',
	'Partners' => 'partners',
	'Submit a Program' => 'submit',
	'Contact' => 'contact'
]);
$data->site_billboard(true);
$data->site_billboard_allowed_pages(['dispute-thanks']);
$data->site_footer_ucf_icon('admin/');
$dir = get_directory_info('fye');
$data->site_directory_basics([
	'phone' => $dir['phone'],
	'fax' => $dir['fax'],
	'email' => EMAIL,
	'location' => $dir['location']['building'].' '.$dir['location']['roomNumber'],
	'mapId' => $dir['location']['buildingNumber']
]);
$data->site_hours(load_hours_from_directory($dir['hours']));
$data->site_social([
	'facebook' => 'https://www.facebook.com/ucflink',
	'twitter' => 'https://twitter.com/'.TWITTER
]);
$data->site_directory_helper('fye-link');

?>