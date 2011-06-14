<?php
//error_reporting(E_ALL);
/*
[qqcat_picexif] (C) QQCAT 2009-2010
$File: hooks.class.php, v1.0.0
$url: http://www.0718i.cn
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_qqcat_picexif {
  function plugin_qqcat_picexif() {
    global $_G,$lang;
    $this->identifier = 'qqcat_picexif';
    $this->mode = $_G['cache']['plugin'][$this->identifier]['mode'];
    $this->contentid = $_G['cache']['plugin'][$this->identifier]['contentid'];
    $this->content = $_G['cache']['plugin'][$this->identifier]['content'];
    $this->forums = @unserialize($_G['cache']['plugin'][$this->identifier]['forums']);
    $this->isset = 1;
    $this->plugdir = $discuz_root.'source/plugin/'.$this->identifier;
        
		//if(!isset($lang[temp])) {
			require_once $this->plugdir.'/lang.inc.php';
			$this->lang = $lang;
		//}

		$this->contentid = max(1,intval($this->contentid));
		if ($this->contentid==1){
		}else{
			$this->content = $this->lang[temp][$this->contentid];
		}

		$this->forums = is_array($this->forums) && $this->forums ? $this->forums : array();
		
  }
    	
	function global_footer(){
		return @$this->show_exifinfo();
	}
	
	private function show_exifinfo(){
		global $postlist, $_G, $post;
//print_r($post);

		$postlist = is_array($postlist) ? $postlist : array();
		//print_r($postlist);

		if(!$this->isset) {
			return '';
		}

		if(!in_array($_G[fid], $this->forums)) {
			return '';
		}

//		if(!is_array($thread) || empty($thread['attachment'])) {
//			return '';
//		}

//print_r($postlist);
		foreach($postlist as $pid => $post) {
			$newmsg = $postlist[$pid]['message'];
			$newlist = $postlist[$pid]['attachlist'];
			$newimglst = $postlist[$pid]['imagelist'];
			//print_r($postlist[$pid]);
			foreach($post['attachments'] as $a_key => $a_value) {
				$attachfile = $discuz_root.$a_value['url'].$a_value['attachment'];
				if ($a_value['isimage']!=0 && file_exists($attachfile)) {
					$exif = @$this->getExif($a_value['ext'],$attachfile,$a_key,$this->content);
					//echo $exif;
					if ($exif){
						$postlist[$pid]['exif'] = $exif;
						$exiftxt = $exif;
						$search = "/(<.*img.*id=\"aimg_".$a_key."\".*\/>)/i";
						$replace = "\\1".$exiftxt;
						if ($newmsg) $newmsg = preg_replace($search, $replace, $newmsg);
						$newlist = preg_replace($search, $replace, $newlist);
						$newimglst = preg_replace($search, $replace, $newimglst);
					}
				}
			}
			$postlist[$pid]['message'] = $newmsg;
			$postlist[$pid]['attachlist'] = $newlist;
			$postlist[$pid]['imagelist'] = $newimglst;
		}
//print_r($postlist[$pid]['message']);
		return '';
	}

	function getExif($extension,$attach,$aid,$content){
		$searchs = array(
		'{aid}','{相机型号}','{曝光时间}','{光圈}',
		'{曝光补偿}','{曝光模式}','{白平衡}','{ISO}',
		'{焦距}','{拍摄时间}','{分辨率}','{闪光灯}',
		'{相机厂商}'
		);
		if ($this->mode == '1'){
			/*EXIF读取phpexif.php*/
			require_once($this->plugdir.'/phpexif.php');
			if (($extension == 'jpg' || $extension == 'jpeg')){
				$er=new EXIF();
				$er->get_exif($attach);
				$exif = $er->TAG->get_all_tag();
				//print_r($exif);
				if($exif['DateTimeOriginal']!='未记录' && $exif['DateTimeOriginal']!=''){
					$imginfo=getimagesize($attach);
					$_x=$imginfo[0];
					$_y=$imginfo[1];

					$replaces = array(
					$aid,$exif[Model],$exif[ExposureTime],$exif[FNumber],
					$exif[ExposureBiasValue],$exif[ExposureProgram],$exif[WhiteBalance],$exif[ISOSpeedRatings],
					$exif[FocalLength],$exif[DateTimeOriginal],$_x.'*'.$_y,$exif[FlashMode],
					$exif[Maker]);
					$writedata = str_replace($searchs, $replaces, $content);
//print_r($writedata);
					return $writedata;
				}

			}
			}elseif ($this->mode == '2'){
			/*EXIF读取MODEL*/
			if (($extension == 'jpg' || $extension == 'jpeg') && function_exists ('read_exif_data')){
				$exif = @read_exif_data($attach,0,true);
				if ($exif['EXIF']['ExifVersion']){
					//print_r($exif);
					$FLASH_MODE_cname = array(
					0x00 => "关闭",
					0x01 => "开启",
					0x05 => "打开(不探测返回光线)",
					0x07 => "打开(探测返回光线)",
					0x09 => "打开(强制)",
					0x0D => "打开(强制/不探测返回光线)",
					0x0F => "打开(强制/探测返回光线)",
					0x10 => "关闭(强制)",
					0x18 => "关闭(自动)",
					0x19 => "打开(自动)",
					0x1D => "打开(自动/不探测返回光线)",
					0x1F => "打开(自动/探测返回光线)",
					0x20 => "没有闪光功能",
					0x41 => "打开(防红眼)",
					0x45 => "打开(防红眼/不探测返回光线)",
					0x47 => "打开(防红眼/探测返回光线)",
					0x49 => "打开(强制/防红眼)",
					0x4D => "打开(强制/防红眼/不探测返回光线)",
					0x4F => "打开(强制/防红眼/探测返回光线)",
					0x59 => "打开(自动/防红眼)",
					0x5D => "打开(自动/防红眼/不探测返回光线)",
					0x5F => "打开(自动/防红眼/探测返回光线)"
					);
					$FlashMode = $FLASH_MODE_cname[$exif[EXIF][Flash]];
					$replaces = array(
					$aid,$exif['IFD0']['Model'],$exif['EXIF']['ExposureTime'],$exif['COMPUTED']['ApertureFNumber'],
					$exif['EXIF']['ExposureBiasValue'],$exif['EXIF']['ExposureMode']==1?"手动":"自动",$exif[EXIF][WhiteBalance]==1?"手动":"自动",$exif['EXIF'][ISOSpeedRatings],
					$exif[EXIF][FocalLength]*1,$exif[EXIF][DateTimeOriginal],$exif[COMPUTED][Width].'*'.$exif[COMPUTED][Height],$FlashMode,
					$exif[IFD0][Make]);
					$writedata = str_replace($searchs, $replaces, $content);
					//$attach_exif = "<font color=red> 相机型号：</font>".$exif['IFD0']['Model']."<br>"."<font color=red> 曝光时间：</font>".$exif['EXIF']['ExposureTime']."<font color=red> 光 圈：</font>".$exif['COMPUTED']['ApertureFNumber']."<font color=red> 曝光补偿：</font>".$exif['EXIF']['ExposureBiasValue']."EV"."<font color=red> 曝光模式：</font>".$exif=($exif['EXIF']['ExposureMode']==1?"手动":"自动")."<br>"."<font color=red> 白 平 衡：</font>".$exif=($exif[EXIF][WhiteBalance]==1?"手动":"自动")."<font color=red> ISO感光度：</font>".$exif['EXIF'][ISOSpeedRatings]."<font color=red> 焦距：</font>".$exif[EXIF][FocalLength]."mm"."<br>"."<font color=red> 拍摄时间：</font>".$exif[EXIF][DateTimeOriginal]."<font color=red> 分 辨 率：</font>".$exif[COMPUTED][Width]."*".$exif[COMPUTED][Height];
					return $writedata;
				}
			}
		}
		elseif ($this->mode == '3'){
			/*EXIF读取iExif.inc.php*/
			require_once($this->plugdir.'/iExif.lang.php');
			require_once($this->plugdir.'/iExif.inc.php');
			if (($extension == 'jpg' || $extension == 'jpeg')){
				$er=new YzuoCom_ExifInfo($attach,$lang);
				$er->API_ProcessFile();
				$exif = $er->API_ShowExifInfo();

				if($exif['tag_datetime']!=''){
					$exif['tag_exposure_program'] = $exif['tag_exposure_program']==1?"手动":"自动";
					$exif['tag_whitebalance'] = $exif['tag_whitebalance']==1?"手动":"自动";

					$replaces = array(
					$aid,$exif[tag_model],$exif[tag_exposure_time],$exif[tag_Fnumber],
					$exif[tag_exposure_bias],$exif[tag_exposure_program],$exif[tag_whitebalance],$exif[tag_iso_exuivalent],
					$exif[tag_focal_length],$exif[tag_datetime],$exif[tag_width].'*'.$exif[tag_height],$exif[tag_flash],
					$exif[tag_make]);
					$writedata = str_replace($searchs, $replaces, $content);

					return $writedata;
				}
			}
		}
		return false;
	}
	
}

//取得EXIF信息
function get_image_exif1($extension,$attach,$aid){
	$exif = new plugin_qqcat_picexif;
	$result = $exif->getExif($extension,$attach,$aid,$exif->content);
	return $result;
}

function get_image_exif($attach){
	global $_G;
	$exif = new plugin_qqcat_picexif;
		if(!$exif->isset) {
			return '';
		}

		if(!in_array($_G[fid], $exif->forums)) {
			return '';
		}
	$result = $exif->getExif($attach['ext'],$discuz_root.$attach['url'].$attach['attachment'],$attach['aid'],$exif->content);
	//echo $result;
	return $result;
}
?>