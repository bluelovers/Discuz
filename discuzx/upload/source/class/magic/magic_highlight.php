<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_highlight.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_highlight {

	var $version = '1.0';
	var $name = 'highlight_name';
	var $description = 'highlight_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'expiration' => array(
				'title' => 'highlight_expiration',
				'type' => 'text',
				'value' => '',
				'default' => 24,
			),
			'fids' => array(
				'title' => 'highlight_forum',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		$magic['fids'] = explode("\t", $magic['forum']);

		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		global $_G;
		$magicnew['forum'] = is_array($parameters['fids']) && !empty($parameters['fids']) ? implode("\t",$parameters['fids']) : '';
		$magicnew['expiration'] = intval($parameters['expiration']);
	}

	function usesubmit() {
		global $_G;
		if(empty($_G['gp_tid'])) {
			showmessage(lang('magic/highlight', 'highlight_info_nonexistence'));
		}

		$thread = getpostinfo($_G['gp_tid'], 'tid', array('fid', 'authorid', 'subject'));
		$this->_check($thread['fid']);
		magicthreadmod($_G['gp_tid']);

		DB::query("UPDATE ".DB::table('forum_thread')." SET highlight='$_G[gp_highlight_color]', moderated='1' WHERE tid='$_G[gp_tid]'");
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		$expiration = TIMESTAMP + $this->parameters['expiration'] * 3600;

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['gp_tid']);
		updatemagicthreadlog($_G['gp_tid'], $this->magic['magicid'], $expiration > 0 ? 'EHL' : 'HLT', $expiration);

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/stick', 'highlight_notification'), array('tid' => $_G['gp_tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/highlight', 'highlight_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		if($tid) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		magicshowtype('top');
		$lang = lang('magic/highlight');
		magicshowsetting(lang('magic/highlight', 'highlight_info', array('expiration' => $this->parameters['expiration'])), 'tid', $tid, 'text');
echo <<<EOF
	<p class="mtm mbn">$lang[highlight_color]</p>
	<div class="hasd mbm cl">
		<input type="hidden" id="highlight_color" name="highlight_color" />
		<input type="text" readonly="readonly" class="crl" id="highlight_color_show" />
		<a href="javascript:;" id="highlight_color_ctrl" class="dpbtn" onclick="showHighLightColor('highlight_color')">^</a>
	</div>
	<script type="text/javascript" reload="1">
		function showHighLightColor(hlid) {
			var showid = hlid + '_show';
			if(!$(showid + '_menu')) {
				var str = '';
				var coloroptions = {'0' : '#000', '1' : '#EE1B2E', '2' : '#EE5023', '3' : '#996600', '4' : '#3C9D40', '5' : '#2897C5', '6' : '#2B65B7', '7' : '#8F2A90', '8' : '#EC1282'};
				var menu = document.createElement('div');
				menu.id = showid + '_menu';
				menu.className = 'cmen';
				menu.style.display = 'none';
				for(var i in coloroptions) {
					str += '<a href="javascript:;" onclick="$(\'' + hlid + '\').value=' + i + ';$(\'' + showid + '\').style.backgroundColor=\'' + coloroptions[i] + '\';hideMenu(\'' + menu.id + '\')" style="background:' + coloroptions[i] + ';color:' + coloroptions[i] + ';">' + coloroptions[i] + '</a>';
				}
				menu.innerHTML = str;
				$('append_parent').appendChild(menu);
			}
			showMenu({'ctrlid':hlid + '_ctrl','evt':'click','showid':showid});
		}
	</script>
EOF;
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
	}

	function _check($fid) {
		if(!checkmagicperm($this->parameters['forum'], $fid)) {
			showmessage(lang('magic/highlight', 'highlight_info_noperm'));
		}
	}

}

?>