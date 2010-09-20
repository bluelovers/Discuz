<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: main.inc.php 4379 2010-09-09 03:00:50Z fanshengshuai $
 */

if(!defined('IN_BRAND') && !defined('IN_ADMIN')) {
	exit('Access Denied');
}


$title = '';
if(defined('IN_STORE')) {
	$title = lang('panel_title');
} elseif(defined('IN_ADMIN')) {
	$title = lang('admin_title');
}

//<!DOCTYPE之前不能有空格
echo <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>$title</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta content="Comsenz Inc." name="Copyright" />
		<link rel="stylesheet" href="static/image/admin/admincp.css" type="text/css" media="all" />
		<script language="javascript" type="text/javascript" src="static/js/jquery.js" charset="utf-8"></script>
	</head>
	<body style="margin: 0px" scroll="no">
		<div id="append_parent"></div>
		<table cellpadding="0" cellspacing="0" width="100%" height="100%">
			<tr>
				<td colspan="2" height="90">
					<div class="mainhd">
					<div class="logo">BRAND Administrator's Control Panel</div>
					<div class="uinfo">
						<p>$lang[header_welcome], <em>$_G[username]</em> [ <a href="batch.login.php?action=logout&sid=$sid" target="_top">$lang[header_logout]</a> ]</p>
						<p class="btnlink"><a href="index.php" target="_blank">$lang[header_pk]</a></p>
					</div>
					<div class="navbg"></div>
					<div class="nav">
					<ul id="topmenu">

EOT;
if(pkperm('isadmin')) {
	require_once(B_ROOT.'./source/admininc/perm.inc.php');
	foreach($topmenu as $k => $v) {
		if($v === '') {
			$v = @array_keys($menu[$k]);
			$v = $menu[$k][$v[0]][1];
		}
		showheader($k, $v);
	}
	if($isfounder)
		echo '<li><em><a id="header_uc" hidefocus="true" href="'.UC_API.'/admin.php?m=frame&a=main&iframe=1" onclick="uc_login=1;toggleMenu(\'uc\', \'\');" target="main">'.$lang['header_uc'].'</a></em></li>';
} elseif($_G['myshopstatus'] == 'verified' || $_G['myshopstatus'] == 'unverified') {

	showheader('index', 'index');

	if($shop->status == 'new') {
		showheader('shop', 'edit&m=shop');
	}elseif($shop->status == 'normal') {
		showheader('shop', 'edit&m=shop');
		showheader('infomanage', 'report');
	}

} else {
	cpmsg('無權限', 'index.php');
}

echo <<<EOT

					</ul>
					<div class="currentloca">
EOT;

if (IN_ADMIN === true) {
	echo "<p id=\"admincpnav\"></p>";
} else {
	echo "<p>".lang('you_cur_shop_is')." <font style=\"color:#6666ff; font-weight:bold;\">".(!empty($_SGLOBAL['panelinfo']['subject']) ? $_SGLOBAL['panelinfo']['subject'] : $lang['none'])."</font>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<select id=\"myshoplist\"><option value=\"0\">{$lang['change_your_cur_shop']}</option>";
	foreach ($_G['myshopsarr'] as $myshop) {
		echo "<option value=\"{$myshop['itemid']}\">{$myshop['subject']}</option>";	
	}
	echo "</select>";
	echo "
	<script>
	$(\"#myshoplist\").bind('change', function() {
		location = \"panel.php?shopid=\" + this.value;
	});
	</script>
	";
	echo "</p>";
}
			
echo <<<EOT
					</div>
					<div class="navbd"></div>
					<div class="sitemapbtn"></div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" width="160" class="menutd">
			<div id="leftmenu" class="menu">

EOT;

require_once(B_ROOT.'./source/admininc/menu.inc.php');

$uc_api_url = UC_API;
$ucadd = pkperm('isadmin') ? ", 'uc'" : '';
$release = B_RELEASE;
$bver = B_VER;

echo <<<EOT

			</div>
		</td>
		<td valign="top" width="100%" class="mask" id="mainframes">
EOT;
if (IN_STORE === true) {
	echo "<div style=\"border-bottom:1px solid #B5CFD9; padding:0 0 5px 10px;\"><p id=\"admincpnav\"></p></div>";
}
echo <<<EOT
			<iframe src="$BASESCRIPT?$extra" id="main" name="main" onload="mainFrame(0)" width="100%" height="100%" frameborder="0" scrolling="yes" style="overflow: visible;display:"></iframe>
		</td>
	</tr>
</table>

<div id="scrolllink" style="display: none">
	<span onclick="menuScroll(1)"><img src="static/image/admin/scrollu.gif" /></span>
	<span onclick="menuScroll(2)"><img src="static/image/admin/scrolld.gif" /></span>
</div>
<div class="copyright">
	<p>$lang[brand] @ $release</p>
</div>

<script type="text/JavaScript" charset="$_G[charset]">
	var headers = new Array('index', 'global', 'catmanage', 'shop', 'infomanage', 'admintools'$ucadd);
	var admincpfilename = '$BASESCRIPT';
	var menukey = '', custombarcurrent = 0;
	function toggleMenu(key, url) {
		if(key == 'index' && url == 'home') {
			if(BROWSER.ie) {
				doane(event);
			}
			parent.location.href = admincpfilename + '?frames=yes';
			return false;
		}
		menukey = key;
		for(var k in headers) {
			if($('#menu_' + headers[k])[0]) {
				$('#menu_' + headers[k])[0].style.display = headers[k] == key ? '' : 'none';
			}
		}
		$('#menu_paneladd').css("display", "none");
		var lis = $('#topmenu LI');
		for(var i = 0; i < lis.length; i++) {
			if(lis[i].className == 'navon') lis[i].className = '';
		}
		$('#header_' + key)[0].parentNode.parentNode.className = 'navon';
		if(url) {
			parent.mainFrame(0);
			parent.main.location = admincpfilename + '?action=' + url;
			var hrefs = $('#menu_' + key + ' A');
			for(var j = 0; j < hrefs.length; j++) {
				hrefs[j].className = hrefs[j].href.substr(hrefs[j].href.indexOf(admincpfilename + '?action=') + 19) == url ? 'tabon' : (hrefs[j].className == 'tabon' ? '' : hrefs[j].className);
			}
		}
		setMenuScroll();
		return false;
	}
	function setMenuScroll() {
		var obj = $('#menu_' + menukey)[0];
		var scrollh = document.body.offsetHeight - 160;
		obj.style.overflow = 'visible';
		obj.style.height = '';
		$('#scrolllink')[0].style.display = 'none';
		if(obj.offsetHeight + 150 > document.body.offsetHeight && scrollh > 0) {
			obj.style.overflow = 'hidden';
			obj.style.height = scrollh + 'px';
			$('#scrolllink')[0].style.display = '';
		}
	}
	function menuScroll(op, e) {
		var obj = $('#menu_' + menukey)[0];
		var scrollh = document.body.offsetHeight - 160;
		if(op == 1) {
			obj.scrollTop = obj.scrollTop - scrollh;
		} else if(op == 2) {
			obj.scrollTop = obj.scrollTop + scrollh;
		} else if(op == 3) {
			if(!e) e = window.event;
			if(e.wheelDelta <= 0 || e.detail > 0) {
				obj.scrollTop = obj.scrollTop + 20;
			} else {
				obj.scrollTop = obj.scrollTop - 20;
			}
		}
	}
	function initCpMenus(menuContainerid) {
		var key = '';
		var hrefs = $("#"+menuContainerid+ " A");
		for(var i = 0; i < hrefs.length; i++) {
			if(menuContainerid == 'leftmenu' && !key && '$extra'.indexOf(hrefs[i].href.substr(hrefs[i].href.indexOf(admincpfilename + '?action=') + 12)) != -1) {
				key = hrefs[i].parentNode.parentNode.id.substr(5);
				hrefs[i].className = 'tabon';
			}
			if(!hrefs[i].getAttribute('ajaxtarget')) hrefs[i].onclick = function() {
				if(menuContainerid != 'custommenu') {
					var lis = $("#"+menuContainerid + ' LI');
					for(var k = 0; k < lis.length; k++) {
						if(lis[k].firstChild.className != 'menulink') lis[k].firstChild.className = '';
					}
					if(this.className == '') this.className = menuContainerid == 'leftmenu' ? 'tabon' : 'bold';
				}
				if(menuContainerid != 'leftmenu') {
					var hk, currentkey;
					var leftmenus = $('#leftmenu A');
					for(var j = 0; j < leftmenus.length; j++) {
						hk = leftmenus[j].parentNode.parentNode.id.substr(5);
						if(this.href.indexOf(leftmenus[j].href) != -1) {
							leftmenus[j].className = 'tabon';
							if(hk != 'index') currentkey = hk;
						} else {
							leftmenus[j].className = '';
						}
					}
					if(currentkey) toggleMenu(currentkey);
					hideMenu();
				}
			}
		}
		return key;
	}
	var header_key = initCpMenus('leftmenu');
	toggleMenu(header_key ? header_key : 'index');
	function initCpMap() {
		var ul, hrefs, s;
		s = '<ul class="cnote"><li><img src="static/image/admin/tn_map.gif" /></li><li> $lang[custommenu_tips]</li></ul><table class="cmlist" id="mapmenu"><tr>';

		for(var k in headers) {
			if(headers[k] != 'index' && headers[k] != 'uc') {
				s += '<td valign="top"><ul class="cmblock"><li><h4>' + $('#header_' + headers[k]).html() + '</h4></li>';
				ul = $('#menu_' + headers[k])[0];
				hrefs = ul.getElementsByTagName('a');
				for(var i = 0; i < hrefs.length; i++) {
					s += '<li><a href="' + hrefs[i].href + '" target="' + hrefs[i].target + '" k="' + headers[k] + '">' + hrefs[i].innerHTML + '</a></li>';
				}
				s += '</ul></td>';
			}
		}
		s += '</tr></table>';
		return s;
	}
	var cmcache = false;
	function showMap() {
		showMenu({'ctrlid':'cpmap','evt':'click', 'duration':3, 'pos':'00'});
		if(!cmcache) ajaxget(admincpfilename + '?action=misc&operation=custommenu&' + Math.random(), 'custommenu', '');
	}
	function resetEscAndF5(e) {
		e = e ? e : window.event;
		actualCode = e.keyCode ? e.keyCode : e.charCode;
		if(actualCode == 27) {
			if($('#cpmap_menu')[0].style.display == 'none') {
				showMap();
			} else {
				hideMenu();
			}
		}
		if(actualCode == 116 && parent.main) {
			if(custombarcurrent) {
				parent.$('#main_' + custombarcurrent)[0].contentWindow.location.reload();
			} else {
				parent.main.location.reload();
			}
			if(document.all) {
				e.keyCode = 0;
				e.returnValue = false;
			} else {
				e.cancelBubble = true;
				e.preventDefault();
			}
		}
	}
	function uc_left_menu(uc_menu_data) {
		var leftmenu = $('#menu_uc')[0];
		leftmenu.innerHTML = '';
		var html_str = '';
		for(var i=0;i<uc_menu_data.length;i+=2) {
			html_str += '<li><a href="'+uc_menu_data[(i+1)]+'" hidefocus="true" onclick="uc_left_switch(this)" target="main">'+uc_menu_data[i]+'</a></li>';
		}
		leftmenu.innerHTML = html_str;
		toggleMenu('uc', '');
		$('#admincpnav')[0].innerHTML = 'UCenter';
	}
	var uc_left_last = null;
	function uc_left_switch(obj) {
		if(uc_left_last) {
			uc_left_last.className = '';
		}
		obj.className = 'tabon';
		uc_left_last = obj;
	}
	function uc_modify_sid(sid) {
		$('#header_uc')[0].href = '$uc_api_url/admin.php?m=frame&a=main&iframe=1&sid=' + sid;
	}

	function mainFrame(id, src) {
		var setFrame = !id ? 'main' : 'main_' + id, exists = 0, src = !src ? '' : src;
		var obj = $('#mainframes')[0].getElementsByTagName('IFRAME');
		for(i = 0;i < obj.length;i++) {
			if(obj[i].name == setFrame) {
				exists = 1;
			}
			obj[i].style.display = 'none';
		}
		if(!exists) {
			if(BROWSER.ie) {
				frame = document.createElement('<iframe name="' + setFrame + '" id="' + setFrame + '"></iframe>');
			} else {
				frame = document.createElement('iframe');
				frame.name = setFrame;
				frame.id = setFrame;
			}
			frame.width = '100%';
			frame.height = '100%';
			frame.frameBorder = 0;
			frame.scrolling = 'yes';
			frame.style.overflow = 'visible';
			frame.style.display = 'none';
			if(src) {
				frame.src = src;
			}
			$('#mainframes')[0].appendChild(frame);
		}
		if(id) {
			custombar_set(id);
		}
		$("#"+setFrame)[0].style.display = '';
		if(!src && custombarcurrent) {
			$('#custombar_' + custombarcurrent)[0].className = '';
			custombarcurrent = 0;
		}
	}


</script>
</body>
</html>

EOT;

?>