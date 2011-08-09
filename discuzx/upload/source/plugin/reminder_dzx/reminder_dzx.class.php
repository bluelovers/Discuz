<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_reminder_dzx {
	var $reminder = array();
	var $charset = array();

	function plugin_reminder_dzx() {
		global $_G;

		$this->reminder = $_G['cache']['plugin']['reminder_dzx'];
		$this->reminder['group'] = (array)unserialize($this->reminder['group']);
		$this->reminder['uids'] = explode(',', $this->reminder['uids']);
		$this->reminder['perm'] = in_array('', $this->reminder['group']) ? TRUE : (in_array($_G['groupid'], $this->reminder['group']) ? TRUE : (in_array($_G['uid'], $this->reminder['uids']) ? TRUE : FALSE));
		$this->charset = strtolower($_G['config']['output']['charset']);
		if($this->charset == 'utf-8') {
			$this->charset = '_utf8';
		} elseif($this->charset == 'big5') {
			$this->charset = '_big5';
		} else {
			$this->charset = '';
		}
	}

	function global_footer() {
		global $_G;

		$output = '';
		if($this->reminder['perm']) {
			$r_cookie = explode('D', $_G['cookie']['reminder']);
			if(empty($_G['cookie']['reminder']) || $r_cookie['0'] != $_G['uid']) {
				$setting = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin_reminder')." WHERE uid='{$_G['uid']}' LIMIT 1");
				if(empty($setting)) {
					$this->reminder['ispop'] = intval($this->reminder['ispop']);
					$setting = array(
						'uid' => $_G['uid'],
						'remind' => $this->reminder['ispop'] ? '1' : 0,
						'readtype' => $this->reminder['ispop'].'_'.$this->reminder['ispop'].'_'.($this->reminder['ispop'] ? 1 : 0),
					);
					DB::insert('common_plugin_reminder', $setting);
				}
				dsetcookie('reminder', ($_G['cookie']['reminder'] = implode('D', $setting)), 31536000);
			}

			$setting = explode('D', $_G['cookie']['reminder']);
			$setting['2'] = stripslashes($setting['2']);

			if($setting['1']) {
				$type = '&type='.$setting['2'];
				if($_G['uid']) {
					$query_string = $type.'&time='.TIMESTAMP;
					if(in_array(CURMODULE, array('forumdisplay', 'viewthread')) && $_G['fid']) {
						$query_string .= '&fid='.$_G['fid'];
					}

					if($_G['member']['newprompt'] || $_G['member']['newpm']) {
						$timeout = '100';
					} else {
						$first = strpos($query_string, '&fid=') ? 'first = 0;fid='.$_G['fid'] : '';
						$timeout = 'getnewtimeout';
					}

					$output = '
<style type="text/css">
.focus {text-align: left; position: fixed;right: 10px;bottom: 10px;z-index: 300;overflow: hidden;width: 270px;background: white;}
.rda {border-bottom: 1px dashed #CDCDCD;_height: auto;height: 80px;min-height: 80px;overflow: hidden;}
.hm {text-align: center;}
</style>
<div class="focus" id="remindtip" style="display:none;">
	<div class="bm" id="rtip">
		<div id="addclose"></div>
		<div class="bm_h cl" id="rr_close"><a href="javascript:;" id="r_close" class="y" title="'.lang('plugin/reminder_dzx', 'close').'">'.lang('plugin/reminder_dzx', 'close').'</a><h2>'.lang('plugin/reminder_dzx', 'reminder_title').'</h2></div>

		<div class="bm_c">
			<ul class="xld cl rda">
			<span id="contents">'.lang('plugin/reminder_dzx', 'reminder_title').'</span>
			</ul>
			<p class="ptn hm"><a href="javascript:;" id="remindset" onclick="showWindow(\'remindset\', \'home.php?mod=spacecp&ac=plugin&id=reminder_dzx:reminder_dzx\');return false;" class="xi2" target="_blank">'.lang('plugin/reminder_dzx', 'pop_setting').'</a></p>
		</div>
	</div>
</div>
<script type="text/javascript" src="source/plugin/reminder_dzx/template/extra'.$this->charset.'.js"></script>';
					$output .= '<script type="text/javascript">getnew_handle = setTimeout(function () {getnew(\''.$query_string.'\''.($this->reminder['active'] ? '' : ', 86400000').');}, '.$timeout.');'.$first.'</script>';
				}
			}
		}

		return $output;
	}
}
?>



