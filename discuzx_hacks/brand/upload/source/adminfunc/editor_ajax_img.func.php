<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: editor_ajax_img.func.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

function geteditcont($cont='www', $string=0) {
	global $_G, $_SGLOBAL, $wheresql, $lang, $_SC;
	if(pkperm('isadmin')) {
		$shopid = intval($_G['cookie']['shopid']);
		if($shopid<1) { exit('<div>cookie error</div>');}
		$wheresql = " WHERE shopid='$shopid'";
	} elseif($_G['myshopstatus'] == 'verified') {
		$wheresql = " WHERE shopid='$_G[myshopid]'";
	}
	switch($cont) {
		case 'www':
			$str =
<<<EOF
			<div id="e_www" unselectable="on" class="p_opt popupfix" style="">
				<table width="100%" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<th width="74%">$lang[editor_imgsrc]<span class="xi1" id="e_image_status"></span></th>
							<th width="13%">$lang[editor_imgwidth]</th>
							<th width="13%">$lang[editor_imgheight]</th>
						</tr>
						<tr>
							<td><input type="text" autocomplete="off" class="px" value="" style="width: 95%;" id="e_image_param_1"></td>
							<td><input autocomplete="off" class="px p_fre" value="" size="1" id="e_image_param_2"></td>
							<td><input autocomplete="off" class="px p_fre" value="" size="1" id="e_image_param_3"></td>
						</tr>
						<tr>
							<td align="center" class="pns" colspan="3">
								<button id="e_image_submit" class="pn pnc" type="button"><span>$lang[settingsubmit]</span></button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
			$("#e_image_submit").click(
				function() {
					var msgeditor;
					msgeditor=$("#message")[0].editor;
					var edtimgextra = '';
					if($("#e_image_param_2").val()>0) {
						edtimgextra += ' width="'+$("#e_image_param_2").val()+'"';
					}
					if($("#e_image_param_3").val()>0) {
						edtimgextra += ' height="'+$("#e_image_param_3").val()+'"';
					}

					msgeditor.pasteHTML('<img src="'+$("#e_image_param_1").val()+'" '+edtimgextra+' />');
					msgeditor.hidePanel();
					return false;
				}
			);
			</script>
EOF;
		break;
		case 'albumlist':
			$str = '
			<div style="" id="e_albumlist" unselectable="on" class="p_opt">
				<div class="upfilelist">
					'.lang('editor_albumchoose').'
					<select id="choosealbum">
						<option value="0">'.lang('all').'</option>
						<option value="-1">'.lang('album_default').'</option>';
			$query = DB::query('SELECT itemid, subject FROM '.tname('albumitems').$wheresql.' ORDER BY itemid DESC');
			while($albumarr = DB::fetch($query)) {
						$str .= "<option value=\"$albumarr[itemid]\">$albumarr[subject]</option>";
			}
			$str .= '
					</select>
					<p id="albumphoto"></p>
				</div>
			</div>
			<script type="text/javascript">
				$("#choosealbum").change(function() { $("#albumphoto").load(\''.$BASESCRIPT.'?action=ajax_editor&cont=imgattachlist&albumid=\'+$("#choosealbum").val());});
			</script>
';
		break;
		case 'imgattachlist':
			$str = '
			<div id="e_imgattachlist" unselectable="on" class="p_opt">
				<div class="upfilelist">
					<div id="imgattachlist" style="">
					'.showattachshtml().'
					</div>
					<div id="unusedimgattachlist"></div>
				</div>
				<p style="" id="imgattach_notice" class="notice">'.lang('editor_clickphoto').'</p>
			</div>';
		break;
		case 'multi':
			$configxml = rawurlencode('misc.php?ac=swfupload&op=config&ineditor=1');
			$str =
<<<EOF
			<div id="e_multi" class="swfup" style="float:left;">
						<div id="swfup">
							<h1>Alternative content</h1>
							<p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
						</div>
			</div>
			<script charset="utf-8" type="text/javascript" src="static/image/admin/swfobject.js"></script>
			<script type="text/javascript">
				swfobject.embedSWF("static/image/admin/upload.swf?config=$configxml", "swfup", "100%", "400", "9.0.0", "static/image/admin/expressInstall.swf");

				function swfHandler(albumid, albumurl) {
					$("#e_image_ctrl a").removeClass('current');
					$("#e_btn_imgattachlist").addClass('current');
					$("#e_cont").load(albumurl + albumid);
				}
			</script>
EOF;
		break;
	}

	if($string) {
		return $str;
	} else {
		echo $str;
	}
}

function showattachrow($value) {
	global $_G, $_SGLOBAL;
	$title = $value['subject'];
	$url = getattachurl($value['subjectimage'], 1);
	$thumb = getattachurl($value['subjectimage']);
	$mlist = '
		<div id="photodiv_'.$value['itemid'].'" class="photo_div" style="height:100px;">
			<table style="clear:both;"><tr>
				<td style="border:none;">
					<div class="photoimg_desc">
						<a name="'.$value['itemid'].'" href="'.$url.'" title="'.$title.'"><img id="photoimg_'.$value['itemid'].'" class="photoimg" src="'.$thumb.'" alt="'.$title.'" /></a>
					</div>
				</td>
			</tr></table>
		</div>';

	return $mlist;
}

function showattachshtml() {
	global $_G, $_SGLOBAL, $perpage, $wheresql, $BASESCRIPT;
	$perpage = 12;
	$page = intval($_GET['page']);
	$page = $page>0?$page:1;
	$limitsql = ' ORDER BY itemid DESC LIMIT '.(($page-1)*$perpage).', '.$perpage;
	$_GET['albumid'] = intval($_GET['albumid']);
	$attachhtmllist = '';
	$selectsql = 'SELECT * FROM '.tname('photoitems');
	$countsql = 'SELECT COUNT(itemid) FROM '.tname('photoitems');
	if($_GET['albumid']>0) {
		$wheresql .= " AND albumid='$_GET[albumid]'";
	} elseif($_GET['albumid']==-1) {
		//albumid -1 為默認相冊，0 為全部圖片
		$wheresql .= " AND albumid='0'";
	}
	$query = DB::query($selectsql.$wheresql.$limitsql);
	while($pic = DB::fetch($query)) {
		$attachhtmllist .= showattachrow($pic);
	}
	$count = DB::result_first($countsql.$wheresql);
	$multiurl = "$BASESCRIPT?action=ajax_editor&cont=imgattachlist&albumid={$_GET[albumid]}";
	$multipage = multi($count, $perpage, $page, $multiurl, 1);

	$attachliststr =
<<<EOF
		<div id="import_div">
			$attachhtmllist
		</div>
		<div style="clear:both;">$multipage</div>
		<style>
			#pagetd .pages{margin-top:10px;}
		</style>
		<script type="text/javascript">
			$("#imgattachlist .pages a").click(
				function() {
					$("#e_cont").load(this.href);
					return false;
				}
			);
			$("#import_div .photoimg_desc a").click(
				function() {
					var msgeditor;
					msgeditor=$("#message")[0].editor;
					msgeditor.pasteHTML('<img aid="'+this.name+'" src="'+this.href+'" alt="'+this.title+'" />');
					return false;
				}
			);
		</script>
EOF;
	return $attachliststr;

}

?>