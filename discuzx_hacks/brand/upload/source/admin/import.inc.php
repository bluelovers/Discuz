<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: import.inc.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

foreach(array('tid', 'page', 'maxpage', 'count', 'firstonly', 'step', 'albumid', 'minsize', 'authorid') as $value) {
	$$value = intval(!empty($_POST[$value])?$_POST[$value]:(!empty($_GET[$value])?$_GET[$value]:(!empty($_G['cookie']['i_'.$value])?$_G['cookie']['i_'.$value]:0)));
}

$_GET['tid'] = intval($_GET['tid']);
$norepeat = intval($_G['cookie']['i_norepeat']);
$authorid = intval($_G['cookie']['i_t_authorid']);
$author = $_G['cookie']['i_t_author'];

$shopid = intval($_G['cookie']['shopid']);
getpanelinfo($_G['cookie']['shopid']);
$mycats = mymodelcategory('album');

$page = $page>0?$page:1;
$step = $step>0?$step:1;
$perpage = 24;

$sqlaids = '';
$mname='album';

if(empty($maxpage) && $step>2) {
	cpmsg('import_cookie_error');
}

shownav('infomanage', 'photo_import');
showsubmenu('menu_album', array(
	array('menu_album_add', 'add&m=album', '0'),
	array('menu_photo_import', 'import&m=album', '1')
));
showtips('photo_import_tips');

if($step == 1) {
	//默认填写基本信息页面
	clearimportcookie();

	//论坛来的导入相册
	if(!empty($_GET['frombbs']) && $_GET['tid']>0) {
		ssetcookie('i_referer', 'admin.php?action=import&m=album&tid='.$_GET['tid'], 900);
		ssetcookie('shopid', '', -86400 * 365);
		header('Location:admin.php?action=list');
		exit();
	}

	//相册管理来的继续导入相册
	if(!empty($_GET['fromalbum']) && $_GET['albumid']>0) {
		ssetcookie('i_albumid', $albumid, 900);
		$shopid = DB::result_first('SELECT shopid FROM '.tname('albumitems')." WHERE itemid='$albumid'");
		ssetcookie('shopid', $shopid, 900);
		if($_GET['refresh']!='no') {
			header('Location: admin.php?action=import&fromalbum=1&refresh=no&albumid='.$albumid);
			exit();
		}
	}

	showformheader('import');
	ssetcookie('i_referer', '', -86400 * 365);
	if(empty($_SGLOBAL['panelinfo']['subject'])) {
		showtableheader();
	} else {
		showtableheader(lang('import_toshop').$_SGLOBAL['panelinfo']['subject'], 'notop');
	}
	showhiddenfields(array('step' => '2'));
	showsetting('import_tid', 'tid', $_GET['tid']>0?$_GET['tid']:'', 'number', 0, 0, '<span id="import_thread_subject_comment"></span>');
	showsetting('import_thread_subject', 't_subject', '', 'text', 0, 1);
	showsetting('import_thread_message', 't_message', '', 'textarea', 0, 1);
	showsetting('import_thread_authorid', 't_authorid', '', 'text', 0, 1);
	showsetting('import_thread_author', 't_author', '', 'text', 0, 1);
	showsetting('import_minsize', 'minsize', '20', 'number');
	showsetting('import_firstonly', array('firstonly', array(
					array(true, lang('yes')),
					array(false, lang('no'))
					), true), false, 'mradio');
	showsubmit('settingsubmit', 'submit', '');
	showtablefooter();
	showformfooter();
	showimportthreadjs();
} elseif($step==2) {
	//设置cookie
	require_once(B_ROOT.'./api/bbs_pic.php');
	$count = list_thread_pic($tid, $_POST['firstonly'], $page-1, 1, 20, 1); //查询一次获得总数
	setimportcookie();
	ssetcookie('i_count', $count, 900);
	ssetcookie('i_maxpage', ceil($count/$perpage), 900);
	ssetcookie('i_norepeat', '1', 900);
	header('Location:admin.php?action=import&m=album&step=3');

} elseif($step==3) {
	//图片列表显示页面
	require_once(B_ROOT.'./api/bbs_pic.php');
	$list_pic = list_thread_pic($tid, $firstonly, $page-1, $perpage, $minsize);
	showformheader('import');
	showtableheader('import_listresult', 'notop');
	showtablefooter();
	showhiddenfields(array('step' => '4'));
	showhiddenfields(array('page' => $page));

	showattachshtml(); //图片列表

	showalbummod();
	showformfooter();//批量操作的form结束
} elseif($step==4) {
	//数据提交处理页面
	$attachs = getpostattach();
	$updatesql = array();
	if($_POST['albumop'] == 'new') {
		require_once(B_ROOT.'./source/adminfunc/album.func.php');
		$albumid = createalbum($shopid, $_POST['catid'], $authorid, $author, $_POST['albumname'], $_POST['albumdesc']);
		//更新相册记录为从论坛导入的相册
		$imgurl = $attachs[0]['url'];
		if(strpos($imgurl, 'http://')===0) {
			$remoteattach = loadClass('attach')->saveremotefile($imgurl, array(320, 240));
			$imgurl = $remoteattach['file'];
		}
		$updatesql[] = " `frombbs`='1', `tid`='tid', `subjectimage`='".$imgurl."' ";
		ssetcookie('i_albumid', $albumid, 900);
	} elseif(empty($albumid)) {
		cpmsg('import_albumid_error', '', '', '', true, true);
	}

	$query = DB::query('SELECT itemid, shopid, subject FROM '.tname('albumitems')." WHERE itemid='$albumid'");
	list($albumid, $shopid, $albumsubject) = DB::fetch_row($query);
	if(empty($albumid) || empty($shopid)) {
		cpmsg('import_albumid_error', '', '', '', true, true);
	}

	$insertattach = array();
	foreach($attachs as $v) {
		$insertattach[] = inserttable('photoitems', array('shopid'=>$shopid, 'albumid'=>$albumid, 'bbsaid'=>$v['aid'], 'uid'=>$authorid, 'username'=>$author, 'subject'=>$v['desc'], 'subjectimage'=>$v['url'], 'dateline'=>$_G['timestamp'], 'lastpost'=>$_G['timestamp'], 'allowreply'=>1, 'grade'=>3), 1);
	}
	$jumpurl = 'admin.php?action=import&step=3&jump=1&page='.$page;
	$pics = count($insertattach);
	$updatesql[] = " `picnum`=`picnum`+$pics ";
	DB::query('UPDATE '.tname('albumitems').' SET '.implode(', ', $updatesql)." WHERE itemid='$albumid'");
	$importmsg = '<a href="store.php?id='.$shopid.'&action=album&xid='.$albumid.'" target="_blank">'.$albumsubject.'</a>';
	//删除列表缓存
	$_BCACHE->deltype('sitelist', 'album');
	$_BCACHE->deltype('storelist', 'album', $shopid);
	$_BCACHE->deltype('storelist', 'photo', $shopid, $albumid);
	cpmsg(lang('import_success1').$importmsg.lang('import_success2').$pics.lang('import_success3'), $jumpurl, '', '', true, false, array(), 5000);
}

function showattachrow($value) {
	global $_G, $_SGLOBAL;
	$title = !empty($value['description'])?$value['description']:$value['filename'];
	$url = !empty($value['thumb'])?$value['url'].'.thumb.jpg':$value['url'];
	$mlist = '
		<div id="photodiv_'.$value['aid'].'" class="photo_div" style="height:100px;">
			<table style="clear:both;"><tr>
				<td style="border:none;">
					<div class="photoimg_desc">
						<a href="#" title="'.$title.'" onclick="zoom(this, $(\'#photoimg_'.$value['aid'].'\').attr(\'src\'), $(\'#photoimg_'.$value['aid'].'\').attr(\'alt\'))"><img id="photoimg_'.$value['aid'].'" class="photoimg" src="'.$url.'" alt="'.$title.'" /></a>
					</div>
				</td>
				<td style="border:none;">
					<div class="photo_desc">
						<div style="width: 130px;">'.lang('select').': <input id="check_'.$value['aid'].'" class="checkbox" type="checkbox" name="item[]" value="'.$value['aid'].'" checked /></div>
						<div style="overflow: hidden; height:20px; line-height:20px; width:110px;">'.lang('import_aid').': '.$value['aid'].'</div>
						<div style="overflow: hidden; width:130px;">'.lang('import_desc').': </div>
						<input id="desc_'.$value['aid'].'" class="txt" style="width:110px;" type="number" name="import_desc['.$value['aid'].']" value="'.$title.'" />
					</div>
				</td>
			</tr></table>
		</div>
		<!--hidden for aid.'.$value['aid'].'-->';
	foreach($value as $n=>$v) {
		if(in_array($n, array('thumb', 'url'))) {
			$mlist .= "<input type=\"hidden\" name=\"import_{$n}[{$value[aid]}]\" value=\"$v\" />";
		}
	}
	$mlist .= '		<!--hidden for aid.'.$value['aid'].' END-->';
	return $mlist;
}

function showattachshtml() {
	global $_G, $_SGLOBAL, $_SC, $list_pic, $count, $maxpage, $perpage, $page, $tid, $firstonly, $sqlaids, $norepeat, $albumid, $minsize;
	$step = 0;
	$attachhtmllist = $sqlaids = $comma = '';
	foreach($list_pic['attachments'] as $pic) {
		$step++;
		$sqlaids .= "{$comma}'{$pic[aid]}'";
		$comma = ', ';
		$attachhtmllist .= showattachrow($pic);
	}
	$multiurl = "admin.php?action=import&step=3";
	$multipage = multi($count, $perpage, $page, $multiurl, 1);
	echo '
		<script charset="utf-8" src="static/js/viewgoodspic.js" type="text/javascript"></script>
		<div id="import_div">';
	echo $attachhtmllist;
	echo '
		</div>
		<div style="clear:both;"><table><tr><td style="width:30px;vertical-align:middle;height:25px;"><input style="float:left;" type="checkbox" checked="" name="chkall" onclick="checkall(this.form, \'item\')" /></td><td style="width:30px;vertical-align:middle;height:30px;">'.lang('selectall').'</td><td id="pagetd" style="width:500px;vertical-align:middle;">'.$multipage.'</td></tr></table></div>
		<style>
			#pagetd .pages{margin-top:10px;}
		</style>
	';
	if($sqlaids) {
		//去重检查的sql
		$repeats = array();
		$query = DB::query('SELECT bbsaid FROM '.tname('photoitems')." WHERE bbsaid IN ($sqlaids) GROUP BY bbsaid");
		while($temprow = DB::fetch($query)) {
			$repeats[] = '"'.$temprow['bbsaid'].'"';
		}
		$repeatjsarr = implode(', ', $repeats);
		echo '
		<script type="text/javascript">
			var repeataids = new Array('.$repeatjsarr.');
			'.(!empty($_GET['jump'])?'
			//控制跳转下一页的js
			var jumpurl = "admin.php?action=import&step=3&page=";
			if(repeataids.length == $(".photo_div").length) {
				if('.$page.' == '.$maxpage.') {
					jumpurl = "admin.php?action=import";
				} else {
					jumpurl += '.($page+1).';
				}
				window.location.href = jumpurl;
			}':'').'
			//去重检查的js
			function norepeatcheck() {
				var norepeatst = $("#norepeat")[0].checked;
				if(norepeatst){
					setnorepeat();
				}else{
					unsetnorepeat();
				}
			}

			function setnorepeat() {
				for(i=0; i<repeataids.length; i++) {
					$("#check_"+repeataids[i]).attr({"disabled":"disable"});
					$("#desc_"+repeataids[i]).attr({"disabled":"disable"});
					$("#photodiv_"+repeataids[i]).fadeOut("1000");
				}
				jSetCookie("'.$_SC['cookiepre'].'i_norepeat", 1, 900);
				norepeatst = true;
			}

			function unsetnorepeat() {
				for(i=0; i<repeataids.length; i++) {
					$("#photodiv_"+repeataids[i]).fadeIn("600");
					$("#check_"+repeataids[i]).attr({"disabled":""});
					$("#desc_"+repeataids[i]).attr({"disabled":""});
				}
				jSetCookie("'.$_SC['cookiepre'].'i_norepeat", 0, 900);
				norepeatst = false;
			}

			//js设置cookie的方法
			function jSetCookie(key, value, lifetime) {
				var exp  = new Date();
				exp.setTime(exp.getTime() + lifetime*1000);
				document.cookie = key + "="+ escape (value) + ";expires=" + exp.toGMTString();
			}
			//js取出cookie
			function jGetCookie(key) {
				var ckarr = document.cookie.match(new RegExp("(^| )"+key+"=([^;]*)(;|$)"));
				if(ckarr != null) return unescape(ckarr[2]); return null;
			}

			//页面初始化时执行去重检查
			$(function(){ norepeatcheck();});
		</script>
		';
	}
}

function showalbummod() {
	global $_G, $_SGLOBAL, $_SC, $norepeat, $albumid, $mycats;
	//批量操作方法
	showtableheader(lang('operation_form'), 'nobottom');
	showsubtitle(array('', 'operation', 'option', 'description'));
	showtablerow('', array('class="td25"', 'class="td24"', 'class="td24" style="width:360px;"', 'class="rowform" style="width:auto;"'), array(
			'',
			lang('import_norepeat'),
			'<input class="checkbox" type="checkbox" name="namenorepeat" id="norepeat" value="1" onclick="norepeatcheck();" '.($norepeat?' checked="checked" ':'').'/>',
			lang('import_norepeat_comment')
	));
	if($albumid>0) {
		showtablerow('', array('class="td25"', 'class="td24"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="albumop" value="choose" />',
				lang('import_albumchoose'),
				$albumid,
				lang('import_albumchoose_comment')
		));
	}
	showtablerow('', array('class="td25"', 'class="td24"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
			'<input class="radio" type="radio" name="albumop" value="new" checked="checked" />',
			lang('import_albumnew'),
			'<input class="txt" type="text" name="albumname" value="'.$_G['cookie']['i_t_subject'].'" style="width:350px;" /><br />
			<textarea name="albumdesc" style="width:350px; height:80px;">'.$_G['cookie']['i_t_message'].'</textarea>',
			lang('import_albumnew_comment')
	));
	$catstr = '';
	foreach($mycats as $value) {
		$catstr .= '<option value="'.$value['catid'].'" >'.$value['name'].'</option>';
	}
	showtablerow('', array('class="td25"', 'class="td24"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
			'',
			lang('album_catid'),
			'<select name="catid" id="album_catid" style="width:140px;"><option value="0" selected="selected">'.lang('please_select').'</option>'.$catstr.'</select>',
			lang('album_catid_comment')
	));

	showtablerow('', array('colspan="4"'), array(
			'<div id="album_attr"></div>'
	));
	echo '
	<script type="text/javascript" charset="'.$_G['charset'].'">
		//分类变更时读取属性筛选器
		$("#album_catid").change(function() { getAlbumAttrList();});
		function getAlbumAttrList() {
			jSetCookie("'.$_SC['cookiepre'].'imp_catid", $("#album_catid").val(), 900);
			$.get(
				"batch.attribute.php",
				{ ajax:"1", type:"album", typeid:$("#album_catid").val()},
				function(data) {
					$("#album_attr").html(data);
					getAlbumAttrCookie();
					$("#album_attr :input").change(function() { setAlbumAttrCookie();});
				});
		}
		//分类改变时读取相册属性cookie
		function getAlbumAttrCookie() {
			var attrlength = $("#album_attr :input[name^=\'attr_ids\']").length;
			var attrkey = "";
			var attrvalue ="";
			for(var attri=0; attri<attrlength; attri++) {
				attrkey = $("#album_attr :input[name^=\'attr_ids\']")[attri].name.substring(9);
				attrkey = attrkey.replace("]", "");
				attrvalue = jGetCookie("'.$_SC['cookiepre'].'imp_attr_"+attrkey);
				if(attrvalue!=null) {
					$("#album_attr :input[name=\'attr_ids["+attrkey+"]\']").val(attrvalue);
				}
			}
		}
		//属性改变时设置属性cookie
		function setAlbumAttrCookie() {
			var attrlength = $("#album_attr :input[name^=\'attr_ids\']").length;
			var attrname = "";
			var attrvalue ="";
			for(var attri=0; attri<attrlength; attri++) {
				attrkey = $("#album_attr :input[name^=\'attr_ids\']")[attri].name.substring(9);
				attrkey = attrkey.replace("]", "");
				attrvalue = $("#album_attr :input[name^=\'attr_ids\']")[attri].value;
				jSetCookie("'.$_SC['cookiepre'].'imp_attr_"+attrkey, attrvalue, 900);
			}
		}
		//页面初始化时选择分类
		$(function(){ readAlbumCatid();});
		function readAlbumCatid() {
			var albumcatid = jGetCookie("'.$_SC['cookiepre'].'imp_catid");
			if(albumcatid==null) { albumcatid=0;}
			$("#album_catid").val(albumcatid);
			getAlbumAttrList();
		}
	</script>'; //属性联动
	showsubmit('listsubmit', 'submit', '');
	showtablefooter();
}

function getpostattach() {
	//从POST数据得到附件数组
	$retattach = array();
	foreach($_POST['item'] as $aid) {
		$attach = array();
		$attach['aid'] = intval($aid);
		$attach['desc'] = $_POST['import_desc'][$aid];
		$attach['url'] = $_POST['import_url'][$aid];
		$retattach[] = $attach;
	}
	return $retattach;
}

function showimportthreadjs() {
	global $_G, $_SC;
	$tiderrortips = lang('import_tid_error');
	echo
<<<EOF
	<script type="text/javascript" charset="$_G[charset]">
		var t_pid = 0;
		function tidblurdo() {
			var tid=$("#tid").val();
			if(tid>0) {
				$.get("admin.php", {action:"ajax", opt:"getThread", tid:$("#tid").val()}, function(data) {
					t_pid = $(data).find("threadinfo pid").text();
					if(t_pid>0) {
						$("#import_thread_subject_comment").html("");
						$("#hidden_import_thread_subject").fadeIn("fast");
						$("#t_subject").val($(data).find("threadinfo subject").text());
						$("#hidden_import_thread_message").fadeIn("fast");
						$("#t_message").val($(data).find("threadinfo message").text());
						$("#t_authorid").val($(data).find("threadinfo authorid").text());
						$("#t_author").val($(data).find("threadinfo author").text());
					}
				}, 'xml' );
			}
			if(t_pid>0) {
				return true;
			} else {
				$("#import_thread_subject_comment").html("$tiderrortips");
				return false;
			}
		}
		$("#tid").blur(function() {tidblurdo();});
		$("#cpform").submit(function() {
			//form表单提交 当没有t_pid的时候才尝试重新获取t_pid
			if(t_pid<=0) { return tidblurdo();}
		});
EOF;
	echo ($_GET['tid']>0?'$(function() {tidblurdo();});':'').'</script>';
}

function clearimportcookie() {
	foreach(array('tid', 'maxpage', 'firstonly', 'albumid', 'minsize') as $value) {
		ssetcookie('i_'.$value, '', -86400 * 365);
	}
}

function setimportcookie() {
	foreach(array('tid', 't_subject', 't_message', 't_authorid', 't_author', 'firstonly', 'minsize') as $value) {
		ssetcookie('i_'.$value, $_POST[$value], 900);
	}
}

?>