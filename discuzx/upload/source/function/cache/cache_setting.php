<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_setting.php 17250 2010-09-28 01:07:42Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_setting() {
	global $_G;

	$table = 'common_setting';
	$skipkeys = array('posttableids', 'siteuniqueid', 'mastermobile', 'bbrules', 'bbrulestxt', 'closedreason',
		'creditsnotify', 'backupdir', 'custombackup', 'jswizard', 'maxonlines', 'modreasons', 'newsletter',
		'welcomemsg', 'welcomemsgtxt', 'postno', 'postnocustom', 'customauthorinfo', 'domainwhitelist', 'ipregctrl',
		'ipverifywhite', 'fastsmiley'
		);
	$serialized = array('memory', 'search', 'creditspolicy', 'ftp', 'secqaa', 'ec_credit', 'qihoo', 'spacedata',
		'infosidestatus', 'uc', 'indexhot', 'relatedtag', 'sitemessage', 'uchome', 'heatthread', 'recommendthread',
		'disallowfloat', 'allowviewuserthread', 'advtype', 'click', 'rewritestatus', 'rewriterule', 'privacy', 'focus',
		'forumkeys', 'article_tags', 'verify', 'seotitle', 'seodescription', 'seokeywords', 'domain', 'ranklist',
		'seccodedata', 'inviteconfig'
		);

	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table($table)." WHERE skey NOT IN(".dimplode($skipkeys).')');

	while($setting = DB::fetch($query)) {
		if($setting['skey'] == 'extcredits') {
			if(is_array($setting['svalue'] = unserialize($setting['svalue']))) {
				foreach($setting['svalue'] as $key => $value) {
					if($value['available']) {
						unset($setting['svalue'][$key]['available']);
					} else {
						unset($setting['svalue'][$key]);
					}
				}
			}
		} elseif($setting['skey'] == 'creditsformula') {
			if(!checkformulacredits($setting['svalue'])) {
				$setting['svalue'] = '$member[\'extcredits1\']';
			} else {
				$setting['svalue'] = preg_replace("/(friends|doings|blogs|albums|polls|sharings|digestposts|posts|threads|oltime|extcredits[1-8])/", "\$member['\\1']", $setting['svalue']);
			}
		} elseif($setting['skey'] == 'maxsmilies') {
			$setting['svalue'] = $setting['svalue'] <= 0 ? -1 : $setting['svalue'];
		} elseif($setting['skey'] == 'threadsticky') {
			$setting['svalue'] = explode(',', $setting['svalue']);
		} elseif($setting['skey'] == 'attachdir') {
			$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
			$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DISCUZ_ROOT.$setting['svalue'] : $setting['svalue']);
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'attachurl') {
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'onlinehold') {
			$setting['svalue'] = $setting['svalue'] * 60;
		} elseif(in_array($setting['skey'], $serialized)) {
			$setting['svalue'] = @unserialize($setting['svalue']);
			if($setting['skey'] == 'search') {
				foreach($setting['svalue'] as $key => $val) {
					foreach($val as $k => $v) {
						$setting['svalue'][$key][$k] = max(0, intval($v));
					}
				}
			}
			if($setting['skey'] == 'ftp') {
				$setting['svalue']['attachurl'] .= substr($setting['svalue']['attachurl'], -1, 1) != '/' ? '/' : '';
			}
		}
		$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
	}
	DB::free_result($query);

	$data['newusergroupid'] = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=".intval($data['initcredits'])." AND ".intval($data['initcredits'])."<creditslower LIMIT 1");

	if($data['srchhotkeywords']) {
		$data['srchhotkeywords'] = explode("\n", $data['srchhotkeywords']);
	}

	if($data['search']) {
		$searchstatus = 0;
		foreach($data['search'] as $item) {
			if($item['status']) {
				$searchstatus = 1;
				break;
			}
		}
		if(!$searchstatus) {
			$data['search'] = array();
		}
	}

	$data['creditspolicy'] = array_merge($data['creditspolicy'], get_cachedata_setting_creditspolicy());

	if($data['heatthread']['iconlevels']) {
		$data['heatthread']['iconlevels'] = explode(',', $data['heatthread']['iconlevels']);
		arsort($data['heatthread']['iconlevels']);
	} else {
		$data['heatthread']['iconlevels'] = array();
	}
	if($data['verify']) {
		foreach($data['verify'] as $key => $value) {
			if($value['available']) {
				$icourl = parse_url($value['icon']);
				if(!$icourl['host']) {
					$data['verify'][$key]['icon'] = $data['attachurl'].'common/'.$value['icon'];
				}
			}
		}
	}

	if($data['recommendthread']['status']) {
		if($data['recommendthread']['iconlevels']) {
			$data['recommendthread']['iconlevels'] = explode(',', $data['recommendthread']['iconlevels']);
			arsort($data['recommendthread']['iconlevels']);
		} else {
			$data['recommendthread']['iconlevels'] = array();
		}
	} else {
		$data['recommendthread'] = array('allow' => 0);
	}

	if(!empty($data['ftp'])) {
		if(!empty($data['ftp']['allowedexts'])) {
			$data['ftp']['allowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['allowedexts']);
			$data['ftp']['allowedexts'] = explode("\n", strtolower($data['ftp']['allowedexts']));
			array_walk($data['ftp']['allowedexts'], 'trim');
		}
		if(!empty($data['ftp']['disallowedexts'])) {
			$data['ftp']['disallowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['disallowedexts']);
			$data['ftp']['disallowedexts'] = explode("\n", strtolower($data['ftp']['disallowedexts']));
			array_walk($data['ftp']['disallowedexts'], 'trim');
		}
		$data['ftp']['connid'] = 0;
	}

	if(!empty($data['forumkeys'])) {
		$data['forumfids'] = array_flip($data['forumkeys']);
	} else {
		$data['forumfids'] = array();
	}

	$data['commentitem'] = explode("\t", $data['commentitem']);
	$commentitem = array();
	foreach($data['commentitem'] as $k => $v) {
		$tmp = explode(chr(0).chr(0).chr(0), $v);
		if(count($tmp) > 1) {
			$commentitem[$tmp[0]] = $tmp[1];
		} else {
			$commentitem[$k] = $v;
		}
	}
	$data['commentitem'] = $commentitem;

	if($data['allowviewuserthread']['allow']) {
		$data['allowviewuserthread'] = is_array($data['allowviewuserthread']['fids']) && $data['allowviewuserthread']['fids'] ? dimplode($data['allowviewuserthread']['fids']) : '';
	} else {
		$data['allowviewuserthread'] = false;
	}

	include_once DISCUZ_ROOT.'./source/discuz_version.php';
	$_G['setting']['version'] = $data['version'] = DISCUZ_VERSION;

	$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
	foreach (array('register', 'login', 'newthread', 'reply') as $type) {
		$data['sitemessage'][$type] = !empty($data['sitemessage'][$type]) ? explode("\n", $data['sitemessage'][$type]) : array();
	}

	$data['cachethreadon'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE status='1' AND threadcaches>0") ? 1 : 0;
	$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

	if(!$data['imagelib']) unset($data['imageimpath']);

	if(is_array($data['relatedtag']['order'])) {
		asort($data['relatedtag']['order']);
		$relatedtag = array();
		foreach($data['relatedtag']['order'] AS $k => $v) {
			$relatedtag['status'][$k] = $data['relatedtag']['status'][$k];
			$relatedtag['name'][$k] = $data['relatedtag']['name'][$k];
			$relatedtag['limit'][$k] = $data['relatedtag']['limit'][$k];
			$relatedtag['template'][$k] = $data['relatedtag']['template'][$k];
		}
		$data['relatedtag'] = $relatedtag;

		foreach((array)$data['relatedtag']['status'] AS $appid => $status) {
			if(!$status) {
				unset($data['relatedtag']['limit'][$appid]);
			}
		}
		unset($data['relatedtag']['status'], $data['relatedtag']['order'], $relatedtag);
	}

	$data['domain']['defaultindex'] = isset($data['defaultindex']) ? $data['defaultindex'] : '';
	$data['domain']['holddomain'] = isset($data['holddomain']) ? $data['holddomain'] : '';
	$data['domain']['list'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_domain')." WHERE idtype IN('subarea', 'forum', 'topic', 'channel')");
	while($value = DB::fetch($query)) {
		$data['domain']['list'][$value['domain'].'.'.$value['domainroot']] = array('id' => $value['id'], 'idtype' => $value['idtype']);
	}
	writetocache('domain', getcachevars(array('domain' => $data['domain'])));

	$data['seccodedata'] = is_array($data['seccodedata']) ? $data['seccodedata'] : array();
	if($data['seccodedata']['type'] == 2) {
		if(extension_loaded('ming')) {
			unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
			$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
			$data['seccodedata']['color'], $data['seccodedata']['size'],
			$data['seccodedata']['animator']);
		} else {
			$data['seccodedata']['animator'] = 0;
		}
	} elseif($data['seccodedata']['type'] == 99) {
		$data['seccodedata']['width'] = 32;
		$data['seccodedata']['height'] = 24;
	}

	$data['watermarktype'] = !empty($data['watermarktype']) ? unserialize($data['watermarktype']) : array();
	$data['watermarktext'] = !empty($data['watermarktext']) ? unserialize($data['watermarktext']) : array();
	foreach($data['watermarktype'] as $k => $v) {
		if($data['watermarktype'][$k] == 'text' && $data['watermarktext']['text'][$k]) {
			if($data['watermarktext']['text'][$k] && strtoupper(CHARSET) != 'UTF-8') {
				$data['watermarktext']['text'][$k] = diconv($data['watermarktext']['text'][$k], CHARSET, 'UTF-8', true);
			}
			$data['watermarktext']['text'][$k] = bin2hex($data['watermarktext']['text'][$k]);
			if(file_exists('static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k];
			} elseif(file_exists('static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k];
			} else {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'][$k];
			}
			$data['watermarktext']['color'][$k] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['color'][$k]);
			$data['watermarktext']['shadowcolor'][$k] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['shadowcolor'][$k]);
		} else {
			$data['watermarktext']['text'][$k] = '';
			$data['watermarktext']['fontpath'][$k] = '';
			$data['watermarktext']['color'][$k] = '';
			$data['watermarktext']['shadowcolor'][$k] = '';
		}
	}

	$data['styles'] = array();
	$query = DB::query("SELECT s.styleid, s.name, s.extstyle, t.directory FROM ".DB::table('common_style')." s
				LEFT JOIN ".DB::table('common_template')." t ON s.templateid=t.templateid
				WHERE s.available='1'");
	while($style = DB::fetch($query)) {
		$data['styles'][$style['styleid']] = dhtmlspecialchars($style['name']);
	}

	$exchcredits = array();
	$allowexchangein = $allowexchangeout = FALSE;
	foreach((array)$data['extcredits'] as $id => $credit) {
		$data['extcredits'][$id]['img'] = $credit['img'] ? '<img style="vertical-align:middle" src="'.$credit['img'].'" />' : '';
		if(!empty($credit['ratio'])) {
			$exchcredits[$id] = $credit;
			$credit['allowexchangein'] && $allowexchangein = TRUE;
			$credit['allowexchangeout'] && $allowexchangeout = TRUE;
		}
		$data['creditnotice'] && $data['creditnames'][] = str_replace("'", "\'", htmlspecialchars($id.'|'.$credit['title'].'|'.$credit['unit']));
	}
	$data['creditnames'] = $data['creditnotice'] ? @implode(',', $data['creditnames']) : '';

	$creditstranssi = explode(',', $data['creditstrans']);
	$data['creditstrans'] = $creditstranssi[0];
	unset($creditstranssi[0]);
	$data['creditstransextra'] = $creditstranssi;
	for($i = 1;$i < 10;$i++) {
		$data['creditstransextra'][$i] = $data['creditstrans'] ? (!$data['creditstransextra'][$i] ? $data['creditstrans'] : $data['creditstransextra'][$i]) : 0;
	}
	$data['exchangestatus'] = $allowexchangein && $allowexchangeout;
	$data['transferstatus'] = isset($data['extcredits'][$data['creditstrans']]);

	list($data['zoomstatus'], $data['imagemaxwidth']) = explode("\t", $data['zoomstatus']);
	$data['imagemaxwidth'] = substr(trim($data['imagemaxwidth']), -1, 1) != '%' && $data['imagemaxwidth'] <= 1920 ? $data['imagemaxwidth'] : '';

	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	$data['ucenterurl'] = UC_API;

	$query = DB::query("SELECT identifier, name FROM ".DB::table('common_magic')." WHERE available='1'");
	while($magic = DB::fetch($query)) {
		$data['magics'][$magic['identifier']] = $magic['name'];
	}

	$data['tradeopen'] = DB::result_first("SELECT count(*) FROM ".DB::table('common_usergroup_field')." WHERE allowposttrade='1'") ? 1 : 0;
	$data['medalstatus'] = intval(DB::result_first("SELECT count(*) FROM ".DB::table('forum_medal')." WHERE available='1'"));

	$focus = array();
	if($data['focus']['data']) {
		foreach($data['focus']['data'] as $k => $v) {
			if($v['position']) {
				foreach($v['position'] as $position) {
					$focus[$position][$k] = $k;
				}
			}
		}
	}
	$data['focus'] = $focus;

	list($data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['threadplugins'], $data['specialicon']) = get_cachedata_setting_plugin();

	if(empty($data['defaultindex'])) $data['defaultindex'] = array();
	list($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms']) = get_cachedata_mainnav();

	$data['footernavs'] = get_cachedata_footernav();
	$data['spacenavs'] = get_cachedata_spacenavs();
	$data['mynavs'] = get_cachedata_mynavs();

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$ucapparray = uc_app_ls();
	$data['allowsynlogin'] = isset($ucapparray[UC_APPID]['synlogin']) ? $ucapparray[UC_APPID]['synlogin'] : 1;
	$appnamearray = array('UCHOME','XSPACE','DISCUZ','SUPESITE','SUPEV','ECSHOP','ECMALL');
	$data['ucapp'] = $data['ucappopen'] = array();
	$data['uchomeurl'] = '';
	$data['discuzurl'] = $_G['siteurl'];
	$appsynlogins = 0;
	foreach($ucapparray as $apparray) {
		if($apparray['appid'] != UC_APPID) {
			if(!empty($apparray['synlogin'])) {
				$appsynlogins = 1;
			}
			if($data['uc']['navlist'][$apparray['appid']] && $data['uc']['navopen']) {
				$data['ucapp'][$apparray['appid']]['name'] = $apparray['name'];
				$data['ucapp'][$apparray['appid']]['url'] = $apparray['url'];
			}
		} else {
			$data['discuzurl'] = $apparray['url'];
		}
		if(!empty($apparray['viewprourl'])) {
			$data['ucapp'][$apparray['appid']]['viewprourl'] = $apparray['url'].$apparray['viewprourl'];
		}
		foreach($appnamearray as $name) {
			if($apparray['type'] == $name && $apparray['appid'] != UC_APPID) {
				$data['ucappopen'][$name] = 1;
				if($name == 'UCHOME') {
					$data['uchomeurl'] = $apparray['url'];
				} elseif($name == 'XSPACE') {
					$data['xspaceurl'] = $apparray['url'];
				}
			}
		}
	}
	$data['allowsynlogin'] = $data['allowsynlogin'] && $appsynlogins ? 1 : 0;
	$data['homeshow'] = $data['uchomeurl'] && $data['uchome']['homeshow'] ? $data['uchome']['homeshow'] : '0';

	unset($data['allowthreadplugin']);
	if($data['jspath'] == 'data/cache/') {
		writetojscache();
	} elseif(!$data['jspath']) {
		$data['jspath'] = 'static/js/';
	}

	if($data['cacheindexlife']) {
		$cachedir = DISCUZ_ROOT.'./'.$data['cachethreaddir'];
		$tidmd5 = substr(md5(0), 3);
		@unlink($cachedir.'/'.$tidmd5[0].'/'.$tidmd5[1].'/'.$tidmd5[2].'/0.htm');
	}

	save_syscache('setting', $data);
	$_G['setting'] = $data;
}

function get_cachedata_setting_creditspolicy() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE action IN ('promotion_visit', 'promotion_register')");
	while($creditrule = DB::fetch($query)) {
		$ruleexist = false;
		for($i = 1; $i <= 8; $i++) {
			if($creditrule['extcredits'.$i]) {
				$ruleexist = true;
			}
		}
		$data[$creditrule['action']] = $ruleexist;
	}
	return $data;
}

function get_cachedata_setting_plugin() {
	global $_G;
	$data  = array();

	$data['plugins'] = $data['pluginlinks'] = $data['hookscript'] = $data['threadplugins'] = $data['specialicon'] = $adminmenu = $scriptlang = array();
	$query = DB::query("SELECT pluginid, available, name, identifier, directory, datatables, modules, version FROM ".DB::table('common_plugin')."");
	$data['plugins']['available'] = array();
	while($plugin = DB::fetch($query)) {
		$addadminmenu = $plugin['available'] && DB::result_first("SELECT count(*) FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]'") ? TRUE : FALSE;
		$plugin['modules'] = unserialize($plugin['modules']);
		if($plugin['available']) {
			$data['plugins']['available'][] = $plugin['identifier'];
			$data['plugins']['version'][$plugin['identifier']] = $plugin['version'];
			if(!empty($plugin['modules']['extra']['langexists'])) {
				@include DISCUZ_ROOT.'./data/plugindata/'.$plugin['identifier'].'.lang.php';
			}
		}
		$plugin['directory'] = $plugin['directory'].((!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '');
		if(is_array($plugin['modules'])) {
			unset($plugin['modules']['extra']);
			foreach($plugin['modules'] as $k => $module) {
				if($plugin['available'] && isset($module['name'])) {
					$k = '';
					switch($module['type']) {
						case 1:
							$navtype = 0;
						case 23:
							if($module['type'] == 23) $navtype = 1;
						case 24:
							if($module['type'] == 24) $navtype = 2;
						case 25:
							if($module['type'] == 25) $navtype = 3;
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							if(!DB::result_first("SELECT count(*) FROM ".DB::table('common_nav')." WHERE navtype='$navtype' AND type='3' AND identifier='$plugin[identifier]'")) {
								DB::insert('common_nav', array(
								'name' => $module['menu'],
								'title' => $module['navtitle'],
								'url' => $module['url'],
								'type' => 3,
								'identifier' => $plugin['identifier'],
								'navtype' => $navtype,
								'available' => 1,
								'icon' => $module['navicon'],
								'subname' => $module['navsubname'],
								'suburl' => $module['navsuburl'],
								));
							}
							break;
						case 5:
							$k = 'jsmenu';
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							list($module['menu'], $module['title']) = explode('/', $module['menu']);
							$module['menu'] = $module['type'] == 1 ? ($module['menu'].($module['title'] ? '<span>'.$module['title'].'</span>' : '')) : $module['menu'];
							$data['plugins'][$k][] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'url' => "<a id=\"mn_plink_$module[name]\" href=\"$module[url]\">$module[menu]</a>");
							break;
						case 14:
							$k = 'faq';
						case 15:
							$k = !$k ? 'modcp_base' : $k;
						case 16:
							$k = !$k ? 'modcp_tools' : $k;
						case 7:
							$k = !$k ? 'spacecp' : $k;
						case 17:
							$k = !$k ? 'spacecp_profile' : $k;
						case 19:
							$k = !$k ? 'spacecp_credit' : $k;
							$data['plugins'][$k][$plugin['identifier'].':'.$module['name']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory']);
							break;
						case 21:
							$k = !$k ? 'portalcp' : $k;
							$data['plugins'][$k][$plugin['identifier'].':'.$module['name']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory']);
							break;
						case 3:
							$addadminmenu = TRUE;
							break;
						case 4:
							$data['plugins']['include'][$plugin['identifier']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'script' => $plugin['directory'].$module['name']);
							break;
						case 11:
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
							$classes = get_declared_classes();
							$classnames = array();
							$cnlen = strlen('plugin_'.$plugin['identifier']);
							foreach($classes as $classname) {
								if(substr($classname, 0, $cnlen) == 'plugin_'.$plugin['identifier']) {
									$hscript = substr($classname, $cnlen + 1);
									$classnames[$hscript ? $hscript : 'global'] = $classname;
								}
							}
							foreach($classnames as $hscript => $classname) {
								$hookmethods = get_class_methods($classname);
								foreach($hookmethods as $funcname) {
									$v = explode('_', $funcname);
									$curscript = $v[0];
									if(!$curscript || $classname == $funcname) {
										continue;
									}
									if(!@in_array($script, $data['hookscript'][$hscript][$curscript]['module'])) {
										$data['hookscript'][$hscript][$curscript]['module'][$plugin['identifier']] = $script;
										$data['hookscript'][$hscript][$curscript]['adminid'][$plugin['identifier']] = $module['adminid'];
									}
									if(preg_match('/\_output$/', $funcname)) {
										$varname = preg_replace('/\_output$/', '', $funcname);
										$data['hookscript'][$hscript][$curscript]['outputfuncs'][$varname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
									} else {
										$data['hookscript'][$hscript][$curscript]['funcs'][$funcname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
									}
								}
							}
							break;
						case 12:
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
							if(class_exists('threadplugin_'.$plugin['identifier'])) {
								$classname = 'threadplugin_'.$plugin['identifier'];
								$hookclass = new $classname;
								if($hookclass->name) {
									$data['threadplugins'][$plugin['identifier']]['name'] = $hookclass->name;
									$data['threadplugins'][$plugin['identifier']]['icon'] = $hookclass->iconfile;
									$data['threadplugins'][$plugin['identifier']]['module'] = $script;
								}
							}
							break;
					}
				}
			}
		}
		if($addadminmenu) {
			$adminmenu[] = array('url' => "plugins&operation=config&do=$plugin[pluginid]", 'action' => 'plugins_config_'.$plugin['pluginid'], 'name' => $plugin['name']);
		}
	}
	$_G['setting']['plugins']['available'] = $data['plugins']['available'];
	$file = DISCUZ_ROOT.'./data/plugindata/lang_plugin.php';
	if($fp = @fopen($file, 'wb')) {
		fwrite($fp, "<?php\n".getcachevars(array('lang' => $scriptlang)).'?>');
		fclose($fp);
	}

	writetocache('adminmenu', getcachevars(array('adminmenu' => $adminmenu)));

	$data['pluginhooks'] = array();
	foreach($data['hookscript'] as $hscript => $hookscript) {
		foreach($hookscript as $curscript => $scriptdata) {
			if(is_array($scriptdata['funcs'])) {
				foreach($scriptdata['funcs'] as $funcname => $funcs) {
					usort($funcs, 'pluginmodulecmp');
					$tmp = array();
					foreach($funcs as $k => $v) {
						$tmp[$k] = $v['func'];
					}
					$data['hookscript'][$hscript][$curscript]['funcs'][$funcname] = $tmp;
				}
			}
			if(is_array($scriptdata['outputfuncs'])) {
				foreach($scriptdata['outputfuncs'] as $funcname => $funcs) {
					usort($funcs, 'pluginmodulecmp');
					$tmp = array();
					foreach($funcs as $k => $v) {
						$tmp[$k] = $v['func'];
					}
					$data['hookscript'][$hscript][$curscript]['outputfuncs'][$funcname] = $tmp;
				}
			}
		}
	}

	foreach(array('links', 'spacecp', 'include', 'jsmenu', 'space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_member', 'modcp_forum') as $pluginkey) {
		if(is_array($data['plugins'][$pluginkey])) {
			if(in_array($pluginkey, array('space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_tools'))) {
				uasort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			} else {
				usort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			}
			foreach($data['plugins'][$pluginkey] as $key => $module) {
				unset($data['plugins'][$pluginkey][$key]['displayorder']);
			}
		}
	}

	return 	array($data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['threadplugins'], $data['specialicon']);

}

function get_cachedata_mainnav() {
	global $_G;

	$data['navs'] = $data['subnavs'] = $data['menunavs'] = $data['navmns'] = $data['navmn'] = $data['navdms'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='0' AND (available='1' OR type='0') AND parentid='0' ORDER BY displayorder");
	while($nav = DB::fetch($query)) {
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		if($nav['identifier'] == 3 && $nav['type'] == 0 && !$_G['setting']['groupstatus']) {
			$nav['available'] = 0;
		}
		if($nav['type'] == 3) {
			if(!in_array($nav['identifier'], $_G['setting']['plugins']['available'])) {
				continue;
			}
		}
		if($nav['identifier'] == 5 && $nav['type'] == 0 && !$_G['setting']['my_app_status']) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 8 && $nav['type'] == 0 && !$_G['setting']['ranklist']['status']) {
			$nav['available'] = 0;
		}
		$nav['style'] = parsehighlight($nav['highlight']);
		$nav['url'] = $_G['config']['app']['domain'][$nav['identifier']] ? 'http://'.$_G['config']['app']['domain'][$nav['identifier']] : $nav['url'];
		$data['navs'][$id]['navname'] = $nav['name'];
		$data['navs'][$id]['filename'] = $nav['url'];
		$data['navs'][$id]['available'] = $nav['available'];
		$nav['name'] = $nav['name'].($nav['title'] ? '<span>'.$nav['title'].'</span>' : '');
		$subquery = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='0' AND parentid='$nav[id]' AND available='1' ORDER BY displayorder");
		$subnavs = '';
		while($subnav = DB::fetch($subquery)) {
			$item = "<a href=\"$subnav[url]\" hidefocus=\"true\" ".($subnav['title'] ? "title=\"$subnav[title]\" " : '').($subnav['target'] == 1 ? "target=\"_blank\" " : '').parsehighlight($subnav['highlight']).">$subnav[name]</a>";
			$liparam = !$nav['subtype'] || !$nav['subcols'] ? '' : ' style="width:'.sprintf('%1.1f', (1 / $nav['subcols']) * 100).'%"';
			$subnavs .= '<li'.$liparam.'>'.$item.'</li>';
		}
		list($navid) = explode('.', basename($nav['url']));
		if($nav['type'] || $navid == 'misc' || $nav['identifier'] == 6) {
			if($nav['type'] == 4) {
				$navid = 'P'.$nav['identifier'];
			} else {
				$navid = 'N'.substr(md5(($nav['url'] != '#' ? $nav['url'] : $nav['name'])), 0, 4);
			}
		}
		$navid = 'mn_'.$navid;
		$onmouseover = '';
		if($subnavs) {
			if($nav['subtype']) {
				$onmouseover = 'navShow(\''.substr($navid, 3).'\')';
				$data['subnavs'][$navid] = $subnavs;
			} else {
				$onmouseover = 'showMenu({\'ctrlid\':this.id})';
				$data['menunavs'][] = '<ul class="p_pop h_pop" id="'.$navid.'_menu" style="display: none">'.$subnavs.'</ul>';
			}
		}
		if($nav['identifier'] == 6 && $nav['type'] == 0) {
			if(!empty($_G['setting']['plugins']['jsmenu'])) {
				$onmouseover .= "showMenu({'ctrlid':this.id,'menuid':'plugin_menu'})";
			} else {
				$data['navs'][$id]['available'] = 0;
				continue;
			}
		}
		$data['navs'][$id]['nav'] = "id=\"$navid\" ".($onmouseover ? 'onmouseover="'.$onmouseover.'"' : '')."><a href=\"$nav[url]\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')." $nav[style]>$nav[name]</a";
		$data['navs'][$id]['navid'] = $navid;
		$data['navs'][$id]['level'] = $nav['level'];

		$purl = parse_url($nav['url']);
		$getvars = array();
		if($purl['query']) {
			parse_str($purl['query'], $getvars);
			$data['navmns'][$purl['path']][] = array($getvars, $navid);
		} elseif($purl['host']) {
			$data['navdms'][strtolower($purl['host'].$purl['path'])] = $navid;
		} elseif($purl['path']) {
			$data['navmn'][$purl['path']] = $navid;
		}
	}
	$data['menunavs'] = implode('', $data['menunavs']);

	return array($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms']);

}

function get_cachedata_footernav() {
	global $_G;

	$data['footernavs'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='1' ORDER BY displayorder");
	while($nav = DB::fetch($query)) {
		$nav['extra'] = '';
		if(!$nav['type']) {
			if($nav['identifier'] == 'report') {
				$nav['url'] = 'javascript:;';
				$nav['extra'] = ' onclick="showWindow(\'miscreport\', \'misc.php?mod=report&url=\'+REPORTURL);return false;"';
			} elseif($nav['identifier'] == 'archiver' && !$_G['setting']['archiver']) {
				continue;
			}
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').' '.parsehighlight($nav['highlight']).$nav['extra'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['footernavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']);
	}
	return $data['footernavs'];
}

function get_cachedata_spacenavs() {
	global $_G;
	$data['spacenavs'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='2' ORDER BY displayorder");
	while($nav = DB::fetch($query)) {
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
			}
			$nav['icon'] = '<img src="'.$navicon.'" width="16" height="16" />';
		}
		$nav['allowsubnew'] = 1;
		if(!$nav['subname'] || !$nav['suburl'] || substr($nav['subname'], 0, 1) == "\t") {
			$nav['allowsubnew'] = 0;
			$nav['subname'] = substr($nav['subname'], 1);
		}
		$nav['extra'] = '';
		if(!$nav['type'] && ($nav['identifier'] == 'magic' && !$_G['setting']['magicstatus'] || $nav['identifier'] == 'medal' && !$_G['setting']['medalstatus'])) {
			continue;
		}
		if(!$nav['type'] && $nav['allowsubnew']) {
			if($nav['identifier'] == 'share') {
				$nav['extra'] = ' onclick="showWindow(\'share\', this.href, \'get\', 0);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'activity') {
				if($_G['setting']['activityforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['activityforumid'].'&special=4';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'poll') {
				if($_G['setting']['pollforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['pollforumid'].'&special=1';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'reward') {
				if($_G['setting']['rewardforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['rewardforumid'].'&special=3';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'debate') {
				if($_G['setting']['debateforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['debateforumid'].'&special=5';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'trade') {
				if($_G['setting']['tradeforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['tradeforumid'].'&special=2';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'credit') {
				$nav['allowsubnew'] = $_G['setting']['ec_ratio'] && ($_G['setting']['ec_account'] || $_G['setting']['ec_tenpay_opentrans_chnid'] || $_G['setting']['ec_tenpay_bargainor']);
			}
		}
		$nav['subcode'] = $nav['allowsubnew'] ? '<span><a href="'.$nav['suburl'].'"'.($nav['target'] == 1 ? ' target="_blank"' : '').$nav['extra'].'>'.$nav['subname'].'</a></span>' : '';
		if($nav['name'] != '{hr}') {
			if(in_array($nav['name'], array('{userpanelarea1}', '{userpanelarea2}'))) {
				$nav['code'] = str_replace(array('{', '}'), '', $nav['name']);
			} else {
				$nav['code'] = '<li>'.$nav['subcode'].'<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').'>'.$nav['icon'].$nav['name'].'</a></li>';
			}
		} else {
			$nav['code'] = '</ul><hr class="da" /><ul>';
		}
		$id = $nav['type'] == 0 && !in_array($nav['name'], array('{userpanelarea1}', '{userpanelarea2}')) ? $nav['identifier'] : 100 + $nav['id'];
		$data['spacenavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level']);
	}
	return $data['spacenavs'];
}

function get_cachedata_mynavs() {
	global $_G;

	$data['mynavs'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='3' ORDER BY displayorder");
	while($nav = DB::fetch($query)) {
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
			}
			$navicon = preg_match('/^http:\/\//i', $navicon) ? $navicon : $_G['siteurl'].$navicon;
			$nav['icon'] = ' style="background-image:url('.$navicon.') !important"';
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').$nav['icon'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['mynavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level']);
	}
	return $data['mynavs'];
}

function writetojscache() {
	$dir = DISCUZ_ROOT.'static/js/';
	$dh = opendir($dir);
	$remove = array(
		'/(^|\r|\n)\/\*.+?\*\/(\r|\n)/is',
		'/\/\/note.+?(\r|\n)/i',
		'/\/\/debug.+?(\r|\n)/i',
		'/(^|\r|\n)(\s|\t)+/',
		'/(\r|\n)/',
	);
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'js') {
			$jsfile = $dir.$entry;
			$fp = fopen($jsfile, 'r');
			$jsdata = @fread($fp, filesize($jsfile));
			fclose($fp);
			$jsdata = preg_replace($remove, '', $jsdata);
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/'.$entry, 'w')) {
				fwrite($fp, $jsdata);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
		}
	}
}

function pluginmodulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function parsehighlight($highlight) {
	if($highlight) {
		$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

?>