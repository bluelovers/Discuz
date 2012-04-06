<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: google.php 22319 2011-04-29 09:40:43Z monkey $
 */

@define('IN_API', true);
@define('CURSCRIPT', 'api');

require_once('../../source/class/class_core.php');
require_once('../../source/function/function_home.php');

$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;

$discuz->init();

$google = new GoogleAPI($discuz);
$google->run();

class GoogleAPI
{
	var $core;
	var $version = '1.0.0';
	function GoogleAPI(&$core) {
		$this->core = &$core;
	}

	function run() {
		$this->authcheck();
		$method = 'on_'.getgpc('a');
		if(method_exists($this, $method)) {
			$this->xmlheader();
			$this->$method();
			$this->xmlfooter();
		} else {
			$this->error('Unknow command');
		}
	}

	function authcheck() {
		$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
		$auth = md5($siteuniqueid.'DISCUZ*COMSENZ*GOOGLE*API'.substr(time(), 0, 6));
		if($auth != getgpc('s') && ip2long($_SERVER['REMOTE_ADDR']) != 2096036344 && ip2long($_SERVER['REMOTE_ADDR']) != 2096036256) {
			$this->error('Access error');
		}
	}

	function error($message) {
		$this->xmlheader();
		echo "<error>".$message."</error>";
		$this->xmlfooter();
	}

	function result($message = 'success') {
		$this->xmlheader();
		echo "<result>".$message."</result>";
		$this->xmlfooter();
	}

	function xmlheader() {
		static $isshowed;
		if(!$isshowed) {
			@header("Content-type: application/xml");
			echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<document>\n";
			echo "<description>Discuz! API For Google Function</description>\n";
			echo "<version>{$this->version}</version>\n";
			$isshowed = true;
		}
		return true;
	}

	function xmlfooter($halt = true) {
		echo "\n</document>\n";
		$halt && exit();
	}

	function on_on() {
		DB::insert('common_setting', array('skey' => 'google', 'svalue' => 1), false, true);
		$this->result();
	}

	function on_off() {
		DB::insert('common_setting', array('skey' => 'google', 'svalue' => 0), false, true);
		$this->result();
	}

	function on_gtt() {
		$tids = explode(',', getgpc('t'));
		$msg = getgpc('msg') ? true : false;
		$xmlcontent .= "<threadsdata>\n";
		if(is_array($tids) && !empty($tids)) {
			$ftid = $mark = '';
			foreach ($tids as $tid) {
				if(is_numeric($tid)) {
					$ftid .= $mark."'$tid'";
					$mark = ',';
				}
			}
			if($ftid) {
				$query = DB::query("SELECT tid, fid, posttableid, dateline, special, authorid, subject, views, replies, lastpost FROM ".DB::table('forum_thread')." WHERE tid IN($ftid)");
				while($thread = DB::fetch($query)) {
					$thread['message'] = '';
					if($msg) {
						if($thread['posttableid']) {
							$tablenamelist['forum_post_'.intval($thread['posttableid'])][] = $thread['tid'];
						} else {
							$tablenamelist['forum_post'][] = $thread['tid'];
						}
					}
					$threadlist[$thread['tid']] = $thread;
				}
				if($msg) {
					foreach($tablenamelist AS $tablename => $tids) {
						$pquery = DB::query("SELECT tid, message FROM ".DB::table($tablename)." WHERE tid IN (".dimplode($tids).") AND first=1", 'SILENT');
						while($pquery && $post = DB::fetch($pquery)) {
							$threadlist[$post['tid']]['message'] = htmlspecialchars($post['message']);
						}
					}
					unset($tablenamelist);
				}

				foreach($threadlist AS $tid => $thread) {
					$xmlcontent .=
					"	<thread>\n".
					"		<tid>$thread[tid]</tid>\n".
					"		<fid>$thread[fid]</fid>\n".
					"		<authorid>$thread[authorid]</authorid>\n".
					"		<subject>$thread[subject]</subject>\n".
					"		<views>$thread[views]</views>\n".
					"		<replies>$thread[replies]</replies>\n".
					"		<special>$thread[replies]</special>\n".
					"		<posttableid>$thread[posttableid]</posttableid>\n".
					"		<dateline>$thread[dateline]</dateline>\n".
					"		<lastpost>$thread[lastpost]</lastpost>\n".
					($msg ? "		<message>$thread[message]</message>\n" : '').
					"	</thread>\n";
				}
			}

		}
		$xmlcontent .= "</threadsdata>";
		echo $xmlcontent;
	}

	function on_gts() {
		$xmlcontent = '';
		$threads = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread'));

		$posts = 0;
		loadcache('posttableids');
		if($_G['cache']['posttableids']) {
			foreach($_G['cache']['posttableids'] AS $tableid) {
				$posts += DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($tableid))." LIMIT 1");
			}
		}
		$members = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
		$bbname = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='bbname'");
		$yesterdayposts = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='historyposts'");
		if(!empty($yesterdayposts)) {
			$yesterdayposts = explode("\t", $yesterdayposts);
			$yestoday = intval($yesterdayposts[0]);
			$mostpost = intval($yesterdayposts[1]);
		} else {
			$yestoday = $mostpost = 0;
		}

		$xmlcontent .= "<sitedata>\n".
		"	<bbname>".htmlspecialchars($bbname)."</bbname>\n".
		"	<threads>$threads</threads>\n".
		"	<posts>$posts</posts>\n".
		"	<members>$members</members>\n".
		"	<yesterdayposts>$yestoday</yesterdayposts>\n".
		"	<mostposts>$mostpost</mostposts>\n".
		"</sitedata>\n";
		echo $xmlcontent;

		echo "<forumdata>\n";
		$query = DB::query("SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, ff.description
					FROM ".DB::table('forum_forum')." f
					LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
					WHERE status <3
					ORDER BY fid");
		while($forum = DB::fetch($query)) {
			echo "	<$forum[type]>\n".
			"		<fid>$forum[fid]</fid>\n".
			"		<fup>$forum[fup]</fup>\n".
			"		<name>".htmlspecialchars($forum['name'])."</name>\n".
			"		<description>".htmlspecialchars($forum['description'])."</description>\n".
			"		<threads>$forum[threads]</threads>\n".
			"		<posts>$forum[posts]</posts>\n".
			"		<todayposts>$forum[todayposts]</todayposts>\n".
			"	</$forum[type]>\n";
		}

		echo "</forumdata>";

	}

}

?>