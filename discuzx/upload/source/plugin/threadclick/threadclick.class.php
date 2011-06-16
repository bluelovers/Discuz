<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_threadclick {

}

class plugin_threadclick_forum extends plugin_threadclick {

	function viewthread_postbottom_output() {
		global $_G;

		$return = '';
		if($_G['tid'] && $_G['page'] == 1) {
			$return = '<style type="text/css">
	.atd { margin: 15px auto; }
		.atd img { margin-bottom: 10px; }
		.atd a { display: block; }
			.atd a:hover { text-decoration: none; }
		.atd td { padding: 10px; text-align: center; vertical-align: bottom; }
			.atd .atdc { position: relative; margin: 0 auto 10px; width: 20px; height: 50px; }
				.atdc div { position: absolute; left: 0; bottom: 0; width: 20px; text-align: left; }
				.atd .ac1 { background: #C30; }
				.atd .ac2 { background: #0C0; }
				.atd .ac3 { background: #F90; }
				.atd .ac4 { background: #06F; }
				.atdc em { position: absolute; margin: -25px 0 0 -5px; width: 30px; font-size: 11px; text-align: center; color: {LIGHTTEXT}; }
</style>
<div id=\'click_div\'></div>
<script type=\'text/javascript\'>
	ajaxget(\'plugin.php?id=threadclick&op=show&tid='.$_G['tid'].'\', \'click_div\');
</script>';
		}

		return (array)$return;
	}
}
?>