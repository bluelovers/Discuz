<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: list_photo.func.php 4476 2010-09-15 04:51:33Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

function showlistrowalbum($value) {
	global $_G, $BASESCRIPT;

	$mlist = '';
	$value['url'] = ($_GET['optpass'] == 1 || $_GET['updatepass'] == 1 )?$BASESCRIPT.'?action=edit&m=album&itemid='.$value['itemid'].'&op=adminview&'.($_GET['updatepass'] == 1?'updatepass':'optpass').'=1':$BASESCRIPT.'?action=list&m=photo&shopid='.$value['shopid'].'&albumid='.$value['itemid'].'&filtersubmit=GO';
	$albumcats = getmodelcategory('album');
	$imgurl = getattachurl($value['subjectimage'], 1);
	
	$value['subject'] = cutstr($value['subject'], 15);

	// 上傳的鏈接
	if($value['itemid']>0) {
		if($value['frombbs']) {
			if(pkperm('isadmin')) {
				$addlink = '<a style="color:#900;" href="admin.php?action=import&fromalbum=1&albumid='.$value['itemid'].'">'.lang('import_albumchoose').'</a>';
			}
		} else {
			$addlink = '<a style="color:#900;" href="'.$BASESCRIPT.'?action=add&m=photo&albumid='.$value['itemid'].'">'.lang('album_addphoto').'</a>';
		}
	} else {
		$addlink = '';
	}

	$mlist = "
	<li>		   
		<div class=\"b\">
					<p><a href=\"{$value['url']}\" rel=\"internal\" title=\"{$value['subject']}\">
			<img class=\"fit129\" src=\"{$imgurl}\" rel=\"\" height=\"128\" width=\"128\" /></a>
			</p>
		</div>
	
		<div class=\"w\" style=\"margin-top:5px; \">
			<div id=\"flExpand\" style=\"height:75; line-height:20px;\">
				<div id=\"div_subject_{$value['itemid']}\" style=\"position:absolute; display:none;\">
					<input id=\"input_subject_{$value['itemid']}\" style=\"height:20px; border:#ccc 1px solid; padding:1px;\" name=\"subject['{$value['itemid']}']\" onblur=\"edit_album_subject({$value['itemid']}, this.value);\" value=\"{$value['subject']}\" />
				</div>
				<a id=\"label_subject_{$value['itemid']}\" href=\"javascript:;\" onclick=\"start_edit_album_subject('{$value['itemid']}');\" style=\"display:block; height:30px; line-height:30px; text-decoration:none;\" rel=\"internal\" title=\"{$value['subject']}\">{$value['subject']} &nbsp;<img src=\"static/image/ico_edit.png\" /></a>
				".$addlink."<br />
				".(IN_ADMIN === true ? (($value['title']===NULL?lang('album_default'):$value['title'])."<br/>") : "") . "
				".lang('album_catid').': '.$albumcats[$value['catid']]['name']."<br />
				".lang('display_order').":&nbsp;<input class=\"txt\" style=\"heigth:30px; width:30px; border:#ccc 1px solid; font-size:9px;\" type=\"text\" name=\"display[{$value['itemid']}]\" value=\"" 
				. (IN_ADMIN === true ? $value['displayorder'] : $value['displayorder_s']) . "\" checked />
				<input class=\"checkbox\" style=\"\" type=\"checkbox\" name=\"item[]\" value=\"".$value['itemid']."\" checked />
				<span></span>
			</div>
		</div>
	</li>

	";

	return $mlist;
}

function showlistrowphoto($value) {
	global $_G, $BASESCRIPT;

	$mlist = '';
	$value['url'] = 'store.php?id='.$value['shopid'].'&action=album&xid='.$value['albumid'].'" target="_blank"';
	$imgurl = getattachurl($value['subjectimage'], 1);
	$value['subject'] = cutstr($value['subject'], 15);

	$mlist = "
	<li>		   
		<div class=\"b\">
					<p><a href=\"{$value['url']}\" rel=\"internal\" title=\"{$value['subject']}\">
			<img class=\"fit129\" src=\"{$imgurl}\" rel=\"\" height=\"128\" width=\"128\" /></a>
			</p>
		</div>
	
		<div class=\"w\" style=\"margin-top:5px; \">
			<div id=\"flExpand\" style=\"height:75; line-height:20px;\">
				<div id=\"div_subject_{$value['itemid']}\" style=\"position:absolute; display:none;\">
					<input id=\"input_subject_{$value['itemid']}\" style=\"height:20px; border:#ccc 1px solid; padding:1px;\" name=\"subject['{$value['itemid']}']\" onblur=\"edit_photo_subject({$value['itemid']}, this.value);\" value=\"{$value['subject']}\" />
				</div>
				<a id=\"label_subject_{$value['itemid']}\" href=\"javascript:;\" onclick=\"start_edit_photo_subject('{$value['itemid']}');\" style=\"display:block; height:30px; line-height:30px; text-decoration:none;\" rel=\"internal\" title=\"{$value['subject']}\">{$value['subject']} &nbsp;<img src=\"static/image/ico_edit.png\" /></a>
				".lang('photo_albumid').': '.$value['title']."<br />
				"."<div><a href=\"".$BASESCRIPT.'?action=batchmod&operation=setalbumimg&albumid='.$value['albumid'].'&photoid='.$value['itemid']."\">".lang('setalbumimg')."</a></div>"."
				<input class=\"checkbox\" style=\"\" type=\"checkbox\" name=\"item[]\" value=\"".$value['itemid']."\" checked />".lang('select')."
				<span></span>
			</div>
		</div>
	</li>
	";

	return $mlist;
}

function showlistphoto($mname, $mlist, $multipage) {
	//數據列表顯示
	showformheader('batchmod&m='.$mname);
	showtableheader($mname.'_listresult', 'notop');
	showsubtitle(array());
	showtablefooter();
	echo '<div id="photo_list">';
	echo $mlist;
	echo '<div style="clear:both;"><table><tr><td style="width:30px;vertical-align:middle;height:25px;"><input style="float:left;" type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')" checked/></td><td style="width:30px;vertical-align:middle;height:30px;">'.lang('selectall').'</td><td id="pagetd" style="width:500px;vertical-align:middle;">'.$multipage.'</td></tr></table></div>';
	echo '<style>
				#pagetd .pages{margin-top:10px;}
			</style>
		</div>';

}

?>