<?php

/**
 *      本程序由靖飒開發
 *      若要二次開發或用于商業用途的，需要經過靖飒同意。
 *
 *      2011-08-16
 *
 *		願我的妻子兒女、家人、朋友身體安康，天天快樂！
 */


/*   SQL 升級代碼

ALTER TABLE `hsk_vgallerys` ADD `tid` MEDIUMINT( 15 ) NOT NULL DEFAULT '0' AFTER `sid` ;
ALTER TABLE `hsk_vgallerys` ADD `pid` INT( 18 ) NOT NULL DEFAULT '0' AFTER `tid` ;
ALTER TABLE `hsk_vgallerys` ADD `vprice` MEDIUMINT( 12 ) NOT NULL DEFAULT '0' AFTER `pid` ;
*/




//得到動作參數
$discuz_dotion = 219;
//print_R($_G);
//------------獲取網頁傳遞的參數-------------------------------------------//

$tion				= dhtmlspecialchars($_G['gp_tion']);
$fid				= dhtmlspecialchars($_G['gp_fid']);
$ids				= dhtmlspecialchars($_G['gp_ids']);
$vid				= dhtmlspecialchars($_G['gp_vid']);
$page				= dhtmlspecialchars($_G['gp_page']);
$types				= dhtmlspecialchars($_G['gp_types']);

//------------獲取系統全局變量-------------------------------------------//

$discuz_uid			= $_G['uid'];									//用戶ID
$discuz_user		= $_G['username'];								//用戶ID
$adminid			= $_G['adminid'];								//系統管理員
$timestamp			= TIMESTAMP;									//時間
$timeoffset			= $_G['member']['timeoffset'];					//時間差
$grouptitle			= $_G['group']['grouptitle'];
$credits			= $_G['member']['credits'];
$attachdir			= $_G['setting']['attachdir'];
$attachurl			= $_G['setting']['attachurl'];
$clientip			= $_G['clientip'];
$creditstrans		= $_G['setting']['creditstrans'];				//購買主題用的積分
$maxincperthread	= $_G['setting']['maxincperthread'];			//最高獲得的利益
$maxprice			= $_G['group']['maxprice'];						//最多能賣多少錢
$mygroupid			= $_G['member']['groupid'];						//自已的組ID
$creditstax			= $_G['setting']['creditstax'];					//這個是收取的利率

if(substr($attachurl, 0, 7)=="http://"){
	$att_str = $_G['setting']['discuzurl']."/";
	$attachurl = str_replace($att_str, '', $attachurl);
}



//------------獲取插件的後台參數---------------------------------------------//

$getvar				= $_G['cache']['plugin']['vgallery'];

$isopens			= $getvar['isopens'];						//視頻展區功能開關
$isguest			= $getvar['isguest'];						//視頻展區是否允許遊客訪問
$isevaluate			= $getvar['isevaluate'];					//是否允許對視頻進行評價
$israte				= $getvar['israte'];						//是否允許對視頻進行打分
$isaudit			= $getvar['isaudit'];						//不是管理員發布的視頻進否需要審核後才能顯示
$isauditv			= $getvar['isauditv'];						//不是管理員發布的評論進否需要審核後才能顯示
$isgetimg			= $getvar['isgetimg'];						//是否本地化網絡圖片
$isusercredits		= $getvar['isusercredits'];					//用戶發布視頻使用的自定義積分字段是
$topcheck			= $getvar['topcheck'];						//側欄的熱播數據多久更新一次【小時】
$thefid				= $getvar['thefid'];						//關聯的論壇版塊的FID值。
$isuploadimg		= $getvar['isuploadimg'];					//是否允許會員上傳視頻截圖。
$autholdpush		= $getvar['autholdpush'];					//是否允許自動生成論壇有沒有的視頻。
$theusergp_1		= $getvar['theusergp_1'];					//這個是使用權限用戶級
$theusergp_2		= $getvar['theusergp_2'];					//這個是發布視頻權限用戶級
$theusergp_3		= $getvar['theusergp_3'];					//這個是審核權限用戶級  // 本組免費收看收費的視頻
$ispostmoney		= $getvar['ispostmoney'];					//能否出售視頻
$thecooksave		= $getvar['thecooksave'];					//視頻保存個數


//把權限提出來		1=使用權，2=發布權，3=審核權
$theusergp_1 = (array)unserialize($theusergp_1);
$groupon_1 = in_array('', $theusergp_1) ? TRUE : (in_array($mygroupid, $theusergp_1) ? TRUE : FALSE);

$theusergp_2 = (array)unserialize($theusergp_2);
$groupon_2 = in_array('', $theusergp_2) ? TRUE : (in_array($mygroupid, $theusergp_2) ? TRUE : FALSE);

$theusergp_3 = (array)unserialize($theusergp_3);
$groupon_3 = in_array('', $theusergp_3) ? TRUE : (in_array($mygroupid, $theusergp_3) ? TRUE : FALSE);

//------------參數、變量等獲取完成---------------------------------------------//
//print_r($_G);			//打印參數備用功能

//------------獲取跟鈔票有關的東東---------------------------------------------//

$vcuser = 'extcredits'.$isusercredits;
$vcname = $_G['setting']['extcredits'][$isusercredits]['title'];
$vcunit = $_G['setting']['extcredits'][$isusercredits]['unit'];


//----------------視頻出售方面的東西-------------------------------------------//
$money_id = 'extcredits'.$creditstrans;
$money_name = $_G['setting']['extcredits'][$creditstrans]['title'];
$money_unit = $_G['setting']['extcredits'][$creditstrans]['unit'];


define('PDIR', 'plugin.php?id=vgallery:vgallery');
define('MDIR', 'source/plugin/vgallery/images');
define('PTEM', './source/plugin/vgallery/template');
define('PINC', './source/plugin/vgallery/include');
define('PNAME', '視頻展廳');

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


//echo "您最多能使用 $maxprice $money_unit $money_name, 最高收入是 $maxincperthread ";


//--- X2 要多一條查詢啊。。。暈死了

if($discuz_uid){
	$query = DB::query("SELECT $vcuser, $money_id FROM ".DB::table('common_member_count')." WHERE uid='$discuz_uid'");
	$userinfo = DB::fetch($query);
	$vcsize = $userinfo[$vcuser];
	$mymoney = $userinfo[$money_id];
}


//------------判斷權限---------------------------------------------//

if($adminid!=1 && !$groupon_1){
	showmessage("對不起, 目前本功能不對您所在的組開放使用權.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
}

if(!$isopens && $adminid!=1){
	showmessage("視頻展廳功能暫時關閉中，請稍候訪問。感謝您的關注！", dreferer());
}

//------------獲取頁面右側的顯示數據---------------------------------------------//

$polls_top = $polls_new = array();
if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_toper.hsk'))		@require DISCUZ_ROOT.'./data/cache/vgallery_toper.hsk';
if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_style.hsk')){
	@require DISCUZ_ROOT.'./data/cache/vgallery_style.hsk';
}else{
	mystylewrite();
	showmessage("首次運行本系統，已經成功創建了類別信息，現在准備進入主頁...", PDIR);
}


//------------這個版本不想用infloat了，並不是很實用，見證平凡吧---------------------------------------------//

//------------最近真的是太忙了，沒有太多時間寫---------------------------------------------//


//------------熱播緩存數據寫入，每隔規定時間刷新一次----------------------------------------------------------------------------//
$topertime = intval($topertime);
if(($timestamp-$topertime) > $topcheck){
	//已經過了更新時間了，進行更新
	$toplist = array();
	$i=0;
	$query = DB::query("SELECT v.id, v.vsubject, v.uid, m.username FROM hsk_vgallerys v LEFT JOIN ".DB::table('common_member')." m USING(uid) where v.album=0 and v.audit=1 ORDER BY v.views desc, v.id desc limit 14");
	while($topdata = DB::fetch($query)){
		$topdata['vsubject'] = cutstr(addslashes($topdata['vsubject']), 23, '...');
		$cache_str .= "\t".$i."\t=>\t array('id'=>".$topdata['id'].",\t'vsubject'=>'".$topdata['vsubject']."',\t'uid'=>'".$topdata['uid']."',\t'username'=>'".$topdata['username']."'),\n";
		$toplist[] = $topdata;
		$i++;
	}

	//yg最新評論的視頻
	$toppolls = array();
	$i=0;
	$query = DB::query("SELECT id, vsubject, polls FROM hsk_vgallerys where album=0 and audit=1 ORDER BY updateline desc, id desc limit 14");
	while($topdata = DB::fetch($query)){
		$topdata['vsubject'] = cutstr(addslashes($topdata['vsubject']), 22, '...');
		$cache_str2 .= "\t".$i."\t=>\t array('id'=>".$topdata['id'].",\t'vsubject'=>'".$topdata['vsubject']."',\t'polls'=>'".$topdata['polls']."'),\n";
		$toppolls[] = $topdata;
		$i++;
	}
	$cache_str = "\$topertime = $timestamp;\n\$toplist = array(\n".$cache_str.");\n\$toppolls = array(\n".$cache_str2.");";
	writetocache("vgallery_toper", $cache_str);

}
//------------熱播緩存數據寫入，每隔規定時間刷新一次----------------------------------------------------------------------------//


//------------插件代碼段開始了，先是發布新視頻---------------------------------------------//

if($tion == 'release'){	//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■發布視頻段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	$navname = $navtitle ="發布新視頻...";
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能發布視頻.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
	//是否可以給普通會員發布
	if(!$groupon_2 && $adminid!=1)
		showmessage("對不起, 目前系統不允許您所在的組發布視頻！<br><br>--- [<a href='member.php?mod=logging&action=logout'>換個身份登錄</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");


	//如果是管理員，則不需要審核了！
	$oraudit = $adminid==1 || $groupon_3 || !$isaudit ? 1 : 0;
	$orclosed = $adminid==1 || $groupon_3 || !$isaudit ? 0 : 1;
	//顯示發布界面-------------------------快先做這個模板吧。。。
	if(!submitcheck('reportsubmit')) {
		//SQL取得類別
		if($types != 2 && $types != 3)
			$types = 1;

		$onestyle = null;
		foreach($styleloop as $datarow){
			if(!$onestyle && !$datarow['sup']){
				$onestyle = $datarow['sid'];
			}
		}
		//print_r($dataloop);

		if($types == 3){
			$query = DB::query("SELECT vsubject, id FROM hsk_vgallerys WHERE album=1 and uid='$discuz_uid' ORDER BY id");
			while($datarow = DB::fetch($query)){
				$dataloop2[] = $datarow;
				$inalbum = 1;
			}
			if(!$inalbum)//沒有專輯
			showmessage("您還沒有專輯， 請先建立一個專輯再來發布專輯視頻吧！", 'plugin.php?id=vgallery:vgallery&tion=release&types=2');
		}

		include template("gallery_release", 'Kannol', PTEM);
	}else{

		//提交後檢查
		$vurl		= trim(dhtmlspecialchars($_G['gp_vurl']));
		$vname		= trim(dhtmlspecialchars($_G['gp_vname']));
		$vstyle		= dhtmlspecialchars($_G['gp_sort_id']);
		$vmessage	= substr(dhtmlspecialchars($_G['gp_vmessage']),0,1000);
		$timeone	= intval($_G['gp_timeone']);
		$timetwo	= intval($_G['gp_timetwo']);
		$timelong	= $timeone*60+$timetwo;
		$vsup		= intval($_G['gp_ab1']);
		$picstyle	= intval($_G['gp_picstyle']);
		$vprice		= intval($_G['gp_vprice']);
		$picstyle	= $picstyle==2 ? 2 : 1;
		$file2		= dhtmlspecialchars($_G['gp_file2']);

		if($vprice && $ispostmoney){
			if($vprice > $maxprice)
				$vprice = $maxprice;
		}else{
			$vprice = 0;
		}

		if($types == 1 || $types == 3){
			if(strlen($vurl)<10 || strlen($vurl)>250){
				showmessage("請檢查視頻地址，它可能不正確！請返回修改。");
			}
		}

		if(strlen($vname)<6 || strlen($vname)>80){
			showmessage("視頻或專輯名稱應該控制在3-35個漢字或6-80個字符內！請返回修改。");
		}

		if(!$vstyle && $types!=3){
			showmessage("您沒有選擇視頻或專輯的所屬的分類，請選擇後繼續！請返回修改。");
		}

		//檢查是否有相同的視頻了！

		if($types == 1 || $types == 3){
			$query = DB::query("SELECT vurl FROM hsk_vgallerys WHERE vurl='$vurl' and uid='$discuz_uid'");
			if($pidate = DB::fetch($query)){
				showmessage("對不起，已經由你自己發布過相同的視頻了，請勿重複發布！請返回。");
			}
		}else{
			$query = DB::query("SELECT vsubject FROM hsk_vgallerys WHERE vsubject='$vname' and uid='$discuz_uid'");
			if($pidate = DB::fetch($query)){
				showmessage("對不起，您要創建的專輯與原有的重複，不能再次創建了，請返回。");
			}
		}
		//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=------文件上傳
		if($_FILES['file1']['size'] && $picstyle == 2){
			require_once PINC.'/update_class.func.php';
			$fileType=array('jpg', 'jpg', 'gif', 'png');//允許上傳的文件類型
			$upfileDir = 'vgallery/';

			$makdir1 = $attachdir."/".$upfileDir;
			$makdirs = $makdir1."small_pic/";

			$sqldirname = "./".$attachurl.$upfileDir."small_pic/";

			if(!is_dir($makdir1)) {
				@mkdir($makdir1, 0777);
			}

			$maxSize=1500; //單位：KB
			$ftype = strtolower(substr($_FILES['file1']['name'],-3,3));
			if(!in_array($ftype, $fileType)){
				$msg = '不允許上傳該類型的文件！';
			}
			if($_FILES['file1']['size']> $maxSize*1024){
				$msg = '上傳的文件超過規定的大小！';
			}
			if($_FILES['file1']['error'] !=0){
				$msg = '未知錯誤，文件上傳失敗！';
			}

			if($msg){
				showmessage("視頻截圖上傳失敗：".$msg.", 請返回換另一張圖片再試。");
			}

			$targetDir=$makdir1;
			$targetFile=date('Ymd').time()."~".rand(1111,9999).'.'.$ftype;

			$realFile=$targetDir.$targetFile;
			$smallFile = $makdirs.$targetFile;
			$sqldirname = $sqldirname.$targetFile;
			if(!is_dir($makdirs)) {
				@mkdir($makdirs, 0777);
			}

			//showmessage($attachdir);


			if(@copy($_FILES['file1']['tmp_name'], $realFile) || (function_exists('move_uploaded_file') && @move_uploaded_file($_FILES['file1']['tmp_name'], $realFile))) {
				@unlink($_FILES['file1']['tmp_name']);
			}

			$fsize = $_FILES['file1']['size'];
			$class = new resizeimage($realFile, 240, 180, 1);
			@unlink($realFile);
		}elseif($picstyle == 1 && $file2){

			if($isgetimg){
				$upfileDir = 'vgallery/';

				$makdir1 = $attachdir."/".$upfileDir;
				$makdirs = $makdir1."small_pic/";

				$sqldirname = "./".$attachurl.$upfileDir."small_pic/";

				$fileType=array('jpg', 'jpg', 'gif', 'png');//允許上傳的文件類型
				$ftype = strtolower(substr($file2,-3,3));
				if(!in_array($ftype, $fileType)){
					$ftype = "jpg";
				}
				
				if(!is_dir($makdir1)) {
					@mkdir($makdir1, 0777);
				}

				$targetDir=$makdir1;
				$targetFile=date('Ymd').time()."~".rand(1111,9999).'.'.$ftype;

				$realFile=$targetDir.$targetFile;
				$smallFile = $makdirs.$targetFile;
				$sqldirname = $sqldirname.$targetFile;
				if(!is_dir($makdirs)) {
					@mkdir($makdirs, 0777);
				}
				
				
				$img = grabimage($file2, $sqldirname);

				if(!$img)//圖片上傳成功
					$sqldirname = $file2;


			}else{
				$sqldirname = $file2;
			}

		}
	//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--------文件上傳
		
		//開始上傳視頻信息
		if($types == 1 || $types == 3){
			if($types == 1){
				$vsup = 0;
				$theurlpage = PDIR."&tion=mylist&types=2'>我的視頻</a>";
			}else{
				$vidpage = "&vid=".$vsup;
				//專輯內含的視頻數加1
				DB::query("UPDATE hsk_vgallerys SET vsum=vsum+1 WHERE id='$vsup'");
				$theurlpage = PDIR."&tion=myablist&vid=".$vsup."'>管理專輯內的視頻</a>";

				$query = DB::query("SELECT sid, purl FROM hsk_vgallerys WHERE id='$vsup'");
				$indexdata = DB::fetch($query);
				$vstyle = $indexdata['sid'];
				$ourpurl = $indexdata['purl'];
				$sqldirname = $sqldirname ? $sqldirname : $ourpurl;
			}

			//如果類別是首頁顯示的，就對它進行寫緩存；
			index_topstyle_4($vstyle);

			//$msg, $title, $user, $vid, $url, dateline, $vodstyle

			//發布到指定的論壇中
			$strarr = array('fid'=>"$thefid", 'posttableid'=>'0', 'readperm'=>'0', 'price'=>$vprice, 'typeid'=>0, 'sortid'=>0, 'author'=>"$discuz_user", 'authorid'=>"$discuz_uid", 'subject'=>"$vname", 'dateline'=>$timestamp, 'lastpost'=>$timestamp, 'lastposter'=>"$discuz_user", 'displayorder'=>'0', 'digest'=>'0', 'special'=>'0', 'attachment'=>'0', 'moderated'=>'0', 'highlight'=>'0', 'closed'=>'	$orclosed', 'status'=>'0', 'isgroup'=>'0');
			$tid = DB::insert('forum_thread', $strarr, 1);

			DB::query("INSERT INTO hsk_vgallerys (sid, tid, pid, vprice, album, sup, vsubject, vurl, purl, uid, dateline, timelong, views, polls, valuate, audit, vinfo) 
						VALUES ('$vstyle', '$tid', '0', '$vprice', 0, $vsup, '$vname', '$vurl', '$sqldirname', '$discuz_uid', '$timestamp', '$timelong', '0', '0', '0', '$oraudit', '$vmessage')");
			$newid = DB::insert_id();
			$postdateline = gmdate("Y-m-d", $timestamp + 3600 * $timeoffset);
			$postmessage = loadmsg($vmessage, $vname, $discuz_user, $newid, $vurl, $postdateline, $vprice, $vsup);

			
			require_once libfile('function/post');
			require_once libfile('function/forum');
			
			$pid = insertpost(array('fid' => $thefid,'tid' => $tid,'first' => '1','author' => "$discuz_user",'authorid' => $discuz_uid,'subject'=>"$vname", 'dateline' => $timestamp,'message' => "$postmessage",'useip' => $clientip,'invisible' => '0','anonymous' => '0','usesig' => '0','htmlon' => '0','bbcodeoff' => '0','smileyoff' => '0','parseurloff' => '0','attachment' => '0',));
			$expiration = $timestamp + 86400;
			DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$discuz_uid', '$discuz_user', '$timestamp', 'EHL', '$expiration', '1')");
			DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$discuz_uid', '$discuz_user', '$timestamp', 'CLS', '0', '1')");

			updatepostcredits('+', $discuz_uid, 'post', $thefid);
			$lastpost = "$tid\t".addslashes($vsubject)."\t$timestamp\t$discuz_user";
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$thefid'", 'UNBUFFERED');
			DB::query("UPDATE hsk_vgallerys SET pid='$pid' WHERE id='$newid'");

			updateforumcount($thefid);

			showmessage("恭喜您，視頻已經發布成功！請您選擇以下操作：...<br><br>--- [<a href='".PDIR."&tion=release&types=".$types.$vidpage."'>繼續發布</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".$theurlpage."] ---", dreferer());

		}else{
			DB::query("INSERT INTO hsk_vgallerys (sid, album, sup, vsubject, vurl, purl, uid, dateline, timelong, views, polls, valuate, audit	, vinfo) 
						VALUES ('$vstyle', 1, 0, '$vname', '', '$sqldirname', '$discuz_uid', '$timestamp', '0', '0', '0', '0', '1', '$vmessage')");
			$vid = DB::insert_id();

			showmessage("恭喜您，專輯已經創建成功了，您可以爲專輯添加視頻了！請您選擇以下操作：...<br><br>--- [<a href='".PDIR."&tion=release&types=3&vid=".$vid."'>爲專輯添加視頻</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=mylist'>我的視頻</a>] ---", dreferer());
		}		
	}
}elseif($tion == "albumnewpost"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■多集連續發布模式■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="發布新視頻...";
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能發布視頻.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
	//是否可以給普通會員發布
	if(!$groupon_2 && $adminid!=1)
		showmessage("對不起, 目前系統不允許您所在的組發布視頻！<br><br>--- [<a href='member.php?mod=logging&action=logout'>換個身份登錄</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");


	//如果是管理員，則不需要審核了！
	$oraudit = $adminid==1 || $groupon_3 || !$isaudit ? 1 : 0;
	$orclosed = $adminid==1 || $groupon_3 || !$isaudit ? 0 : 1;

	if(!submitcheck('reportsubmit')) {
		//SQL取得類別
		$onestyle = null;

		$types = 3;
		$query = DB::query("SELECT vsubject, id FROM hsk_vgallerys WHERE album=1 and uid='$discuz_uid' ORDER BY id");
		while($datarow = DB::fetch($query)){
			$dataloop2[] = $datarow;
			$inalbum = 1;
		}
		if(!$inalbum)//沒有專輯
			showmessage("您還沒有專輯， 請先建立一個專輯再來發布專輯視頻吧！", 'plugin.php?id=vgallery:vgallery&tion=release&types=2');

		include template("gallery_albumnewpost", 'Kannol', PTEM);
	}else{
		$vnames = $_G['gp_vname'];
		$vurls = $_G['gp_vurl'];
		$timeones = $_G['gp_timeone'];
		$timetwos = $_G['gp_timetwo'];
		$vprices = $_G['gp_vprices'];
		$vid = intval($_G['gp_s1']);


		for($i=0; $i<=9; $i++){
			//把數組組織好
			$vurl		= trim(dhtmlspecialchars($vurls[$i]));
			$vname		= trim(dhtmlspecialchars($vnames[$i]));
			$timeone	= intval($timeones[$i]);
			$timetwo	= intval($timetwos[$i]);
			$vprice		= intval($vprices[$i]);

			if($vprice && $ispostmoney){
				if($vprice > $maxprice)
					$vprice = $maxprice;
			}else{
				$vprice = 0;
			}

			if($vurl && $vname){

				if(strlen($vurl)<6 || strlen($vurl)>250){
					$datarow[$i]['errmsg'] = '視頻地址不符合要求！';
				}

				if(strlen($vname)<6 || strlen($vname)>80){
					$datarow[$i]['errmsg'] = '視頻名稱不符合要求（規定長度爲6-80個字符）';
				}

				$datarow[$i]['vname'] = $vname;
				$datarow[$i]['vurl'] = $vurl;
				$datarow[$i]['timeone'] = $timeone;
				$datarow[$i]['timetwo'] = $timetwo;
				$datarow[$i]['vprice'] = $vprice;
			}
		}		
		//print_r($datarow);exit();
		
		if(!$datarow)		showmessage("對不起，您沒有輸入任何有效的視頻信息！請返回再試。");

		$query = DB::query("SELECT sid, vsubject, purl, vinfo FROM hsk_vgallerys WHERE id='$vid'");
		$indexdata = DB::fetch($query);
		$vstyle = $indexdata['sid'];
		$thesubject = $indexdata['vsubject'];
		$thenewpurl = $indexdata['purl'];
		$thenewinfo = $indexdata['vinfo'];

		$i=0;
		foreach($datarow as $key=>$value){

			if(!$value['errmsg']){
				//查詢是否有重複視頻
				$vurl = $value['vurl'];
				$query = DB::query("SELECT id FROM hsk_vgallerys WHERE vurl='$vurl' and uid='$discuz_uid'");
				if($pidate = DB::fetch($query)){
					$datarow[$key]['errmsg'] = '此視頻曾由您自己發布過，不能重複發布！';
				}else{
					//添加視頻
					$timelong	= $value['timeone']*60+$value['timetwo'];
					$vname = $value['vname'];
					$i++;
					$datarow[$key]['errmsg'] = '0';
					$vprice = $value['vprice'];


					//發布到指定的論壇中
					$strarr = array('fid'=>"$thefid", 'posttableid'=>'0', 'readperm'=>'0', 'price'=>$vprice, 'typeid'=>0, 'sortid'=>0, 'author'=>"$discuz_user", 'authorid'=>"$discuz_uid", 'subject'=>"$vname", 'dateline'=>$timestamp, 'lastpost'=>$timestamp, 'lastposter'=>"$discuz_user", 'displayorder'=>'0', 'digest'=>'0', 'special'=>'0', 'attachment'=>'0', 'moderated'=>'0', 'highlight'=>'0', 'closed'=>'	$orclosed', 'status'=>'0', 'isgroup'=>'0');
					$tid = DB::insert('forum_thread', $strarr, 1);

					DB::query("INSERT INTO hsk_vgallerys (sid, album, tid, sup, vprice, vsubject, vurl, purl, uid, dateline, timelong, views, polls, valuate, audit, vinfo) 
								VALUES ('$vstyle', 0, $tid, $vid, $vprice, '$vname', '$vurl', '$thenewpurl', '$discuz_uid', '$timestamp', '$timelong', '0', '0', '0', '$oraudit', '$thenewinfo')");
					
					$newid = DB::insert_id();
					$postdateline = gmdate("Y-m-d", $timestamp + 3600 * $timeoffset);
					$postmessage = loadmsg($thenewinfo, $vname, $discuz_user, $newid, $vurl, $postdateline, $vprice, $vid);

					
					require_once libfile('function/post');
					require_once libfile('function/forum');
					
					$pid = insertpost(array('fid' => $thefid,'tid' => $tid,'first' => '1','author' => "$discuz_user",'authorid' => $discuz_uid,'subject'=>"$vname", 'dateline' => $timestamp,'message' => "$postmessage",'useip' => $clientip,'invisible' => '0','anonymous' => '0','usesig' => '0','htmlon' => '0','bbcodeoff' => '0','smileyoff' => '0','parseurloff' => '0','attachment' => '0',));
					$expiration = $timestamp + 86400;
					DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$discuz_uid', '$discuz_user', '$timestamp', 'EHL', '$expiration', '1')");
					DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$discuz_uid', '$discuz_user', '$timestamp', 'CLS', '0', '1')");

					updatepostcredits('+', $discuz_uid, 'post', $thefid);
					$lastpost = "$tid\t".addslashes($vsubject)."\t$timestamp\t$discuz_user";
					DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$thefid'", 'UNBUFFERED');
					DB::query("UPDATE hsk_vgallerys SET pid='$pid' WHERE id='$newid'");				
				}
			}
		}

		//如果類別是首頁顯示的，就對它進行寫緩存；

		DB::query("UPDATE hsk_vgallerys SET vsum=vsum+$i WHERE id='$vid'");
		index_topstyle_4($vstyle);
		//print_r($datarow);

		include template("gallery_albumnewpost_return", 'Kannol', PTEM);
	}




}elseif($tion == "mylist"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■我的視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	//首先是我的視頻列表	
	//SQL取得類別
	$navname = $navtitle ="我的視頻和專輯...";
	$types = $types != 1 ? 2 : 1;
	if($types == 1){

		//獲取頁數信息
		$page = max(1, intval($page));
		$ppp = 15;

		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where uid='$discuz_uid' and album=1");
		$maxpage = DB::result($query, 0);
		$countmax = $maxpage;

		$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=mylist&types='.$types);

		$query = DB::query("SELECT id, sid, vsubject, purl, vprice, dateline, vinfo, vsum FROM hsk_vgallerys where uid='$discuz_uid' and album=1 ORDER BY id limit ".(($page-1)*$ppp).", $ppp");
		while($datarow = DB::fetch($query)){
			$datarow['vinfo'] = cutstr($datarow['vinfo'], 50, "..");
			$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
			if(!substr($datarow['purl'],0,7) == 'http://'){
				$thepicurl = DISCUZ_ROOT.$datarow['purl'];
				if(!file_exists("$thepicurl") || !$datarow['purl']){
					$datarow['purl'] = "./".MDIR."/noimages.gif";
				}
			}
			$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
			$datalist[] = $datarow;
		}
	}else{
		//獲取頁數信息
		$page = max(1, intval($page));
		$ppp = 15;

		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where uid='$discuz_uid' and album=0 and sup=0");
		$maxpage = DB::result($query, 0);
		$countmax = $maxpage;

		$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=mylist&types='.$types);
		$query = DB::query("SELECT m.id, m.sid, m.vsubject, m.purl, m.dateline, m.vprice, m.timelong, m.views, m.polls, m.valuate, m.updateline, m.audit, t.tid, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid where m.uid='$discuz_uid' and m.album=0 and m.sup=0 ORDER BY m.id desc limit ".(($page-1)*$ppp).", $ppp");
		while($datarow = DB::fetch($query)){
			$datarow['vinfo'] = cutstr($datarow['vinfo'], 50, "..");
			$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
			$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
			$datarow['valuate'] = sprintf("%01.2f", $datarow['valuate']/100);
			$datarow['timelong'] = checkthetime($datarow['timelong']);
			$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
			$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
			if(!substr($datarow['purl'],0,7) == 'http://'){
				$thepicurl = DISCUZ_ROOT.$datarow['purl'];
				if(!file_exists("$thepicurl") || !$datarow['purl']){
					$datarow['purl'] = "./".MDIR."/noimages.gif";
				}
			}
			$datarow['audits'] = $datarow['audit'] ? null : "<font color=red>未審核 - </font>";
			$datalist[] = $datarow;
		}
	}

	include template("gallery_mylist", 'Kannol', PTEM);

}elseif($tion == "myablist"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■我的專輯視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="我的專輯視頻管理...";
	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

	//檢查是否有修改權限，權限1、管理員，權限2、自己
	$adminsql = $adminid == 1 ? null : "and uid='".$discuz_uid."'";
	$query = DB::query("SELECT id, vsubject, dateline, sup, purl, sid, vsum FROM hsk_vgallerys WHERE album=1 and id='$vid' $adminsql");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要修改的有效信息，可能是權限不足或無信息，請返回檢查。");
	$datarow['dateline'] = gmdate("Y-m-d, H:i", $datarow['dateline'] + 3600 * $timeoffset);
	if(!substr($datarow['purl'],0,7) == 'http://'){
		$thepicurl = DISCUZ_ROOT.$datarow['purl'];
		if(!file_exists("$thepicurl") || !$datarow['purl']){
			$datarow['purl'] = "./".MDIR."/noimages.gif";
		}
	}

	foreach($styleloop as $datarow2){
		if($datarow2['sid'] == $datarow['sid']){
			$sort_1_sid = $datarow2['sup'];
			$sort_2_name = $datarow2['sort'];
		}
	}

	foreach($styleloop as $value){
		if($value['sid'] == $sort_1_sid)
			$sort_1_name = $value['sort'];
	}



	//獲取頁數信息
	$page = max(1, intval($page));
	$ppp = 15;

	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sup='$vid'");
	$maxpage = DB::result($query, 0);
	$countmax = $maxpage;

	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=myablist&vid='.$vid);

	$query = DB::query("SELECT m.id, m.sid, m.vsubject, m.purl, m.vprice, m.tid, m.dateline, m.vinfo, m.views, m.polls, t.tid, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid where m.sup='$vid' ORDER BY m.id limit ".(($page-1)*$ppp).", $ppp");
	$novod = 1;
	while($datarow1 = DB::fetch($query)){
		$datarow1['vinfo'] = cutstr($datarow1['vinfo'], 50, "..");

		if(!substr($datarow1['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow1['purl'];
			if(!file_exists("$thepicurl") || !$datarow1['purl']){
				$datarow1['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		
		$datarow1['dateline1'] = gmdate("Y-m-d", $datarow1['dateline'] + 3600 * $timeoffset);
		$datarow1['dateline2'] = gmdate("H:i:s", $datarow1['dateline'] + 3600 * $timeoffset);
		$datarow1['views'] = $datarow1['views2'] ? $datarow1['views2'] : $datarow1['views'];
		$datarow1['polls'] = $datarow1['views2'] ? $datarow1['replies'] : $datarow1['polls'];
		$datalist[] = $datarow1;
		$novod = 0;
	}


	include template("gallery_myablist", 'Kannol', PTEM);


}elseif($tion == "addfavorites"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■加入我的收藏 段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能執行此操作.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。", 'javascript:close();', array(), array('closetime' => false, 'showdialog' => 1));

	//檢查是否已經收藏過
	$query = DB::query("SELECT vid FROM hsk_vgallery_favorites WHERE uid='$discuz_uid' and vid='$vid'");
	if($datarow = DB::fetch($query))	showmessage("您好，此視頻您已經收藏過了！", 'javascript:close();', array(), array('closetime' => true, 'showdialog' => 1));

	//執行收藏操作
	DB::query("INSERT INTO hsk_vgallery_favorites (vid, uid, dateline) VALUES ('$vid', '$discuz_uid', '$timestamp')");
	showmessage("恭喜，您已經成功收藏此視頻！", 'javascript:close();', array(), array('closetime' => true, 'showdialog' => 1));

}elseif($tion == "delefavorites"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■移除我的收藏 段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能執行此操作.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

	//執行收藏操作
	DB::query("DELETE from hsk_vgallery_favorites where vid='$vid' and uid='$discuz_uid'");
	showmessage("已經成功將此視頻移除您的收藏夾外！", '', array(), array('closetime' => true, 'showdialog' => 1));

}elseif($tion == "report"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■舉報視頻 段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能執行此操作.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

	if(!submitcheck('reportsubmit')) {
		$navname = $navtitle ="舉報視頻...";

		$query = DB::query("SELECT m.*, n.id as abid, n.vsubject as abname, n.sid as supsid FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.id='$vid'");
		if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要舉報的有效信息，請返回檢查。");
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}

		include template("gallery_report", "Kannol", PTEM);

	}else{
		$message = dhtmlspecialchars($_G['gp_message']);
		if(!$message)	showmessage("對不起，您沒有填寫舉報理由，因此無法完成舉報，請返回重試！");
		//執行操作

		$query = DB::query("SELECT vid FROM hsk_vgallery_report WHERE vid='$vid' and uid='$discuz_uid' and message='$message'");
		if($datarow = DB::fetch($query))	showmessage("對不起，相同的舉報已經存在，您不需要再次提交！請返回");

		DB::query("INSERT INTO hsk_vgallery_report (vid, uid, dateline, message) VALUES ('$vid', '$discuz_uid', '$timestamp', '$message')");
		showmessage("感謝您，您已經舉報成功，等待管理員處理！", PDIR."&tion=view&vid=".$vid);
	}

}elseif($tion == "favorites"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■我的收藏 段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■


	$navname = $navtitle ="我收藏的視頻...";
	$types = $types != 1 ? 2 : 1;

	//獲取頁數信息
	$page = max(1, intval($page));
	$ppp = 10;

	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallery_favorites where uid='$discuz_uid'");
	$maxpage = DB::result($query, 0);
	$countmax = $maxpage;

	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion='.$tion);
	$query = DB::query("SELECT f.id, f.vid, m.vsubject, m.purl, m.dateline, m.timelong, m.views, m.valuate, m.vprice, m.polls, m.valuate, p.username FROM hsk_vgallery_favorites f LEFT JOIN hsk_vgallerys m ON m.id=f.vid LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid where f.uid='$discuz_uid' ORDER BY m.id limit ".(($page-1)*$ppp).", $ppp");
	while($datarow = DB::fetch($query)){
		$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
		$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
		$datarow['valuate'] = sprintf("%01.2f", $datarow['valuate']/100);
		$datarow['timelong'] = checkthetime($datarow['timelong']);

		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datalist[] = $datarow;
	}


	include template("gallery_favorites", "Kannol", PTEM);


}elseif($tion == "edit"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■編輯視頻和專輯段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	//編輯
	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");
	$navname = $navtitle ="編輯視頻或專輯...";
	$oraudit = $adminid==1 || $groupon_3 || !$isaudit ? 1 : 0;

	//檢查是否有修改權限，權限1、管理員，權限2、自己
	$adminsql = $adminid == 1 ? null : "and m.uid='".$discuz_uid."'";
	$query = DB::query("SELECT m.*, n.vsubject as abname, p.username FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid WHERE m.id='$vid' $adminsql");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要修改的有效信息，可能是權限不足或無信息，請返回檢查。");
	$types = $datarow['album'] == 1 ? 1 : 2;
	$thesup = $datarow['sup'];
	$oldsid = $datarow['sid'];
	
	//提交前的編輯內容頁面
	if(!submitcheck('reportsubmit')) {

		//列出類別
		foreach($styleloop as $datarow1){
			if($datarow1['sid'] == $datarow['sid'])	$sort_1_sid = $datarow1['sup'];
		}
		//print_r($dataloop);

		$time1 = floor($datarow['timelong']/60);
		$time2 = $datarow['timelong']%60;
		if(substr($datarow['purl'],0,7) != 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}

		include template("gallery_edit", "Kannol", PTEM);

	}else{

		//提交後檢查
		$vurl		= dhtmlspecialchars($_G['gp_vurl']);
		$vname		= dhtmlspecialchars($_G['gp_vname']);
		$vstyle		= dhtmlspecialchars($_G['gp_sort_id']);
		$delepurl	= intval($_G['gp_delepurl']);
		$vmessage	= substr(dhtmlspecialchars($_G['gp_vmessage']),0,250);
		$timeone	= intval($_G['gp_timeone']);
		$timetwo	= intval($_G['gp_timetwo']);
		$timelong	= $timeone*60+$timetwo;
		$vsup		= intval($_G['gp_s1']);
		$picstyle	= intval($_G['gp_picstyle']);
		$vprice		= intval($_G['gp_vprice']);
		$picstyle	= $picstyle==2 ? 2 : 1;
		$file2		= dhtmlspecialchars($_G['gp_file2']);

		if($vprice && $ispostmoney){
			if($vprice > $maxprice)
				$vprice = $maxprice;
		}else{
			$vprice = 0;
		}

		if($types == 2){
			if(strlen($vurl)<10 || strlen($vurl)>250){
				showmessage("請檢查視頻地址，它可能不正確！請返回修改。");
			}
		}

		if(strlen($vname)<6 || strlen($vname)>80){
			showmessage("視頻或專輯名稱應該控制在3-35個漢字或6-80個字符內！請返回修改。");
		}

		if(!$vstyle && !$thesup){
			showmessage("您沒有選擇視頻或專輯的所屬的分類，請選擇後繼續！請返回修改。");
		}

		//檢查是否有相同的視頻了！

		if($types == 2){
			$query = DB::query("SELECT vurl FROM hsk_vgallerys WHERE vurl='$vurl' and id<>'$vid'");
			if($pidate = DB::fetch($query)){
				showmessage("對不起，相同的視頻已經存您的視頻列表，請勿重複發布！請返回。");
			}
			if(!$thesup)		$thesup_sql = ", sid='".$vstyle."'";
		}else{
			$query = DB::query("SELECT vsubject FROM hsk_vgallerys WHERE vsubject='$vname' and id<>'$vid'");
			if($pidate = DB::fetch($query)){
				showmessage("對不起，您要編輯的專輯與現有的重複，必須更改別的名稱，請返回。");
			}
		}
		//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=------文件上傳
		if($_FILES['file1']['size'] && !$delepurl && $picstyle == 2){
			require_once PINC.'/update_class.func.php';
			$fileType=array('jpg', 'jpg', 'gif', 'png');//允許上傳的文件類型
			$upfileDir = 'vgallery/';

			$makdir1 = $attachdir."/".$upfileDir;
			$makdirs = $makdir1."small_pic/";

			$sqldirname = "./".$attachurl.$upfileDir."small_pic/";

			if(!is_dir($makdir1)) {
				@mkdir($makdir1, 0777);
			}

			$maxSize=1500; //單位：KB
			$ftype = strtolower(substr($_FILES['file1']['name'],-3,3));
			if(!in_array($ftype, $fileType)){
				$msg = '不允許上傳該類型的文件！';
			}
			if($_FILES['file1']['size']> $maxSize*1024){
				$msg = '上傳的文件超過規定的大小！';
			}
			if($_FILES['file1']['error'] !=0){
				$msg = '未知錯誤，文件上傳失敗！';
			}

			if($msg){
				showmessage("視頻截圖上傳失敗：".$msg.", 請返回換另一張圖片再試。");
			}

			$targetDir=$makdir1;
			$targetFile=date('Ymd').time()."~".rand(1111,9999).'.'.$ftype;

			$realFile=$targetDir.$targetFile;
			$smallFile = $makdirs.$targetFile;
			$sqldirname = $sqldirname.$targetFile;
			if(!is_dir($makdirs)) {
				@mkdir($makdirs, 0777);
			}

			//showmessage($attachdir);


			if(@copy($_FILES['file1']['tmp_name'], $realFile) || (function_exists('move_uploaded_file') && @move_uploaded_file($_FILES['file1']['tmp_name'], $realFile))) {
				@unlink($_FILES['file1']['tmp_name']);
			}

			$fsize = $_FILES['file1']['size'];
			$class = new resizeimage($realFile, 240, 180, 1);
			@unlink($realFile);
			$edit_url = $datarow['purl'] ? DISCUZ_ROOT.$datarow['purl'] : NULL;
			@unlink($edit_url);
			$purlsql = ", purl='$sqldirname'";

		}elseif($picstyle == 1 && $file2 && !$delepurl){

			if($isgetimg){
				$upfileDir = 'vgallery/';

				$makdir1 = $attachdir."/".$upfileDir;
				$makdirs = $makdir1."small_pic/";

				$sqldirname = "./".$attachurl.$upfileDir."small_pic/";

				$fileType=array('jpg', 'jpg', 'gif', 'png');//允許上傳的文件類型
				$ftype = strtolower(substr($file2,-3,3));
				if(!in_array($ftype, $fileType)){
					$ftype = "jpg";
				}
				
				if(!is_dir($makdir1)) {
					@mkdir($makdir1, 0777);
				}

				$targetDir=$makdir1;
				$targetFile=date('Ymd').time()."~".rand(1111,9999).'.'.$ftype;

				$realFile=$targetDir.$targetFile;
				$smallFile = $makdirs.$targetFile;
				$sqldirname = $sqldirname.$targetFile;
				if(!is_dir($makdirs)) {
					@mkdir($makdirs, 0777);
				}
				
				
				$img = grabimage($file2, $sqldirname);

				if(!$img)//圖片上傳成功
					$sqldirname = $file2;

				$edit_url = $datarow['purl'] ? DISCUZ_ROOT.$datarow['purl'] : NULL;
				@unlink($edit_url);
				$purlsql = ", purl='$sqldirname'";


			}else{
				$edit_url = $datarow['purl'] ? DISCUZ_ROOT.$datarow['purl'] : NULL;
				@unlink($edit_url);
				$sqldirname = $file2;
				$purlsql = ", purl='$sqldirname'";
			}
		}
		if($delepurl){
			//刪除截圖信息
			$edit_url = $datarow['purl'] ? DISCUZ_ROOT.$datarow['purl'] : NULL;
			@unlink($edit_url);
			$purlsql = ", purl=''";
		}
	//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--------文件上傳
		
		//開始編輯視頻信息
		if($types == 2){
			//exit("$vprice");
			$oraudit = $adminid==1 || $groupon_3 || !$isaudit ? ", audit='1'" : ", audit='0'";
			$orclosed = $adminid==1 || $groupon_3 || !$isaudit ? ", closed='0'" : ", closed='1'";

			DB::query("UPDATE hsk_vgallerys SET vsubject='$vname', vprice='$vprice' $purlsql $thesup_sql $oraudit, timelong='$timelong', vinfo='$vmessage', vurl='$vurl' where id='$vid'");
			if($oldsid != $vstyle){
				//如果類別是首頁顯示的，就對它進行寫緩存；
				index_topstyle_4($vstyle);
				index_topstyle_4($oldsid);
			}
			//關聯主題操作
			$tid = $datarow['tid'];
			$pid = $datarow['pid'];

			$query = DB::query("SELECT tid from ".DB::table("forum_thread")." WHERE tid='$tid'");
			if(!$datasearch = DB::fetch($query)){
				$tid = 0;
			}

			require_once libfile('function/post');
			require_once libfile('function/forum');
			if($tid){
				//有論壇主題的 直接編輯
				$postdateline = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
				$postmessage = loadmsg($vmessage, $vname, $datarow['username'], $vid, $vurl, $postdateline, $vprice, $thesup);
				DB::query("UPDATE ".DB::table('forum_thread')." SET subject='$vname', price='$vprice' $orclosed where tid='$tid'");
				DB::query("UPDATE ".DB::table('forum_post')." SET subject='$vname', message='$postmessage' where pid='$pid'");
			}elseif($autholdpush){
				$push_user		= $datarow['username'];
				$push_uid		= $datarow['uid'];
				$push_time		= $datarow['dateline'];

				//無論壇主題的，自動生成
				$strarr = array('fid'=>"$thefid", 'posttableid'=>'0', 'readperm'=>'0', 'price'=>"$vprice", 'typeid'=>'0', 'sortid'=>'0', 'author'=>"$push_user", 'authorid'=>"$push_uid", 'subject'=>"$vname", 'dateline'=>"$timestamp", 'lastpost'=>"$timestamp", 'lastposter'=>"$push_user", 'displayorder'=>'0', 'digest'=>'0', 'special'=>'0', 'attachment'=>'0', 'moderated'=>'0', 'highlight'=>'0', 'closed'=>'	$orclosed', 'status'=>'0', 'isgroup'=>'0');
				$tid = DB::insert('forum_thread', $strarr, 1);

				$postdateline = gmdate("Y-m-d", $push_time + 3600 * $timeoffset);
				$postmessage = loadmsg($vmessage, $vname, $push_user, $vid, $vurl, $postdateline, $vprice, $thesup);

				$pid = insertpost(array('fid' => $thefid,'tid' => $tid,'first' => '1','author' => "$push_user",'authorid' => $push_uid,'subject' => "$vsubject",'dateline' => $timestamp,'message' => "$postmessage",'useip' => $clientip,'invisible' => '0','anonymous' => '0','usesig' => '0','htmlon' => '0','bbcodeoff' => '0','smileyoff' => '0','parseurloff' => '0','attachment' => '0',));
				$expiration = $timestamp + 86400;

				DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$push_uid', '$push_user', '$timestamp', 'EHL', '$expiration', '1')");
				DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$push_uid', '$push_user', '$timestamp', 'CLS', '0', '1')");

				updatepostcredits('+', $push_uid, 'post', $thefid);
				$lastpost = "$tid\t".addslashes($vsubject)."\t$timestamp\t$push_user";
				DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$thefid'", 'UNBUFFERED');
				DB::query("UPDATE hsk_vgallerys SET pid='$pid', tid='$tid' WHERE id='$vid'");
			}

			updateforumcount($thefid);
			showmessage("恭喜您，視頻已經編輯成功！准備爲您轉入到視頻列表頁面。", $thesup ? PDIR."&tion=myablist&vid=".$thesup : PDIR."&tion=mylist&types=2");

		}else{

			DB::query("UPDATE hsk_vgallerys SET sid='$vstyle', vsubject='$vname' $purlsql, vinfo='$vmessage' where id='$vid'");
			DB::query("UPDATE hsk_vgallerys SET sid='$vstyle' where sup='$vid'");
			if($oldsid != $vstyle){
				//如果類別是首頁顯示的，就對它進行寫緩存；
				index_topstyle_4($oldsid);
				index_topstyle_4($vstyle);
			}

			showmessage("恭喜您，專輯已經編輯成功了！請您選擇以下操作：...<br><br>--- [<a href='".PDIR."&tion=mylist&types=1'>轉到專輯列表</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=myablist&vid=".$vid."'>管理專輯內的視頻</a>] ---");
		}		

	}

}elseif($tion == "delete"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■刪除視頻和專輯段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
	//編輯
	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");
	$navname = $navtitle ="刪除視頻或專輯...";

	//檢查是否有刪除權限，權限1、管理員，權限2、自己
	$adminsql = $adminid == 1 ? null : "and m.uid='".$discuz_uid."'";
	$query = DB::query("SELECT m.*, n.id as abid, n.vsubject as abname, n.sid as supsid FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.id='$vid' $adminsql");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要刪除的有效信息，可能是權限不足或無信息，請返回檢查。");
	$datarow['purl1'] = $datarow['purl'];
	if(!substr($datarow['purl'],0,7) == 'http://'){
		$thepicurl = DISCUZ_ROOT.$datarow['purl'];
		if(!file_exists("$thepicurl") || !$datarow['purl']){
			$datarow['purl1'] = "./".MDIR."/noimages.gif";
		}
	}
	$types = $datarow['album'] == 1 ? 1 : 2;
	$thesup = $datarow['sup'];
	$thesupsid = $datarow['supsid'];
	$datarow['sid'] = $thesupsid ? $thesupsid : $datarow['sid'];
	$deletid = $datarow['tid'];
	//提交前的內容頁面
	if(!submitcheck('reportsubmit')) {

		foreach($styleloop as $datarow2){
			if($datarow2['sid'] == $datarow['sid']){
				$sort_1_sid = $datarow2['sup'];
				$sort_2_name = $datarow2['sort'];
			}
		}

		foreach($styleloop as $value){
			if($value['sid'] == $sort_1_sid)
				$sort_1_name = $value['sort'];
		}

		$datarow['dateline'] = gmdate("Y-m-d, H:i", $datarow['dateline'] + 3600 * $timeoffset);
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl1'] = "./".MDIR."/noimages.gif";
			}
		}
		$timetime = checkthetime($datarow['timelong']);

		include template("gallery_delete", "Kannol", PTEM);

	}else{

		//提交後檢查       如果刪除的是專輯
		if($types==1){
			//先查詢專輯內有無視頻，如果有，是多少個，要計算減少多少積分
			$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sup='$vid'");
			$maxpage = DB::result($query, 0);
			if($maxpage){
				//先把專輯內的視頻移出來
				DB::query("UPDATE hsk_vgallerys SET sid='$datarow[sid]', sup='0' where sup='$vid'");
			}
			//再刪除專輯名稱
			DB::query("DELETE FROM hsk_vgallerys where id='$vid'");
			showmessage("提示：已經成功刪除您選擇的專輯，准備轉入到專輯列表頁面。", PDIR."&tion=mylist&types=1");
		}else{		//刪除的是視頻
			
			//刪除截圖信息
			$edit_url = $datarow['purl'] ? DISCUZ_ROOT.$datarow['purl'] : NULL;
			if($edit_url)	@unlink($edit_url);
			//刪除視頻
			DB::query("DELETE FROM hsk_vgallerys where id='$vid'");

			if($deletid){
				require_once libfile('function/delete');
				$_G['forum']['fid'] = $thefid;
				deletethread($deletid, true, true);
				updateforumcount($thefid);
			}
			
			if($thesup){
				//刪除的是專輯內的視頻
				DB::query("UPDATE hsk_vgallerys SET vsum=vsum-1 where id='$thesup'");		//專輯內的視頻數-1
				showmessage("提示：已經成功刪除您選擇的視頻，准備轉入到視頻所在專輯的管理頁面。", PDIR."&tion=myablist&vid=".$thesup);
			}else{
				showmessage("提示：已經成功刪除您選擇的視頻，准備轉入到視頻列表頁面。", PDIR."&tion=mylist&types=2");
			}			
		}
	}
}elseif($tion == "appvod"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■將視頻加入到專輯列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");
	$navname = $navtitle ="將視頻加入到專輯...";

	//檢查是否有權限，權限1、管理員，權限2、自己
	$adminsql = $adminid == 1 ? null : "and m.uid='".$discuz_uid."'";
	$query = DB::query("SELECT m.sup, m.vsubject, m.id, n.id as abid, n.vsubject as abname, n.sid as supsid FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.id='$vid' and m.album=0 $adminsql");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要管理的有效信息，可能是權限不足或無信息，請返回檢查。");
	$thesup = $datarow['sup'];
	if($thesup)		showmessage("您要加入專輯的視頻已經在某個專輯內了，請先移出後再進行加入！<br><br>--- [<a href='".PDIR."&tion=myablist&vid=".$thesup."'>轉到所在專輯</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=mylist&types=2'>我的視頻列表</a>] ---");

	if(!submitcheck('reportsubmit')) {

		//列出我的專輯
		$query = DB::query("SELECT id, vsubject, vsum FROM hsk_vgallerys where uid='$discuz_uid' and album=1 ORDER BY id");
		while($albumrow = DB::fetch($query)){
			$datalist[] = $albumrow;
		}
		include template("gallery_appvod", "Kannol", PTEM);
	}else{
		//提交審核
		$appabid	= intval($_G['gp_appabid']);
		if(!$appabid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

		//檢查是否確有此專輯
		$query = DB::query("SELECT id, sid FROM hsk_vgallerys WHERE id='$appabid' and uid='$discuz_uid'");
		if(!$ablister = DB::fetch($query))	showmessage("對不起，未找到您要加入的專輯，可能是權限不足或無此專輯，請返回檢查。");

		$thenewsid = $ablister['sid'];

		//如果有了就加進專輯裏
		DB::query("UPDATE hsk_vgallerys SET sup='$appabid', sid='$thenewsid'  where id='$vid'");
		DB::query("UPDATE hsk_vgallerys SET vsum=vsum+1 where id='$appabid'");
		showmessage("已經成功的將視頻加入到指定的專輯內了，請繼續選擇以下操作：<br><br>--- [<a href='".PDIR."&tion=myablist&vid=".$appabid."'>轉到所在專輯</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=mylist&types=2'>我的視頻列表</a>] ---");
	}

}elseif($tion == "pushvod"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■將視頻移出專輯列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");
	$navname = $navtitle ="將視頻加移出專輯...";

	//檢查是否有權限，權限1、管理員，權限2、自己
	$adminsql = $adminid == 1 ? null : "and m.uid='".$discuz_uid."'";
	$query = DB::query("SELECT m.sup, m.vsubject, m.id, n.id as abid, n.vsubject as abname, n.sid as supsid, n.vsum as absum FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.id='$vid' and m.album=0 $adminsql");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要管理的有效信息，可能是權限不足或無信息，請返回檢查。");
	$thesup = $datarow['sup'];
	$thesupsid = $datarow['supsid'];

	if(!$thesup)		showmessage("您要移出的視頻目前並沒有在某個專輯內，無法進行此操作！<br><br>--- [<a href='".PDIR."&tion=mylist&types=1'>我的專輯列表</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=mylist&types=2'>我的視頻列表</a>] ---");

	if(!submitcheck('reportsubmit')) {
		include template("gallery_appvod", "Kannol", PTEM);
	}else{
		//提交審核

		DB::query("UPDATE hsk_vgallerys SET sup='0', sid='$thesupsid'  where id='$vid'");
		DB::query("UPDATE hsk_vgallerys SET vsum=vsum-1 where id='$thesup'");
		showmessage("已經成功的將視頻從指定的專輯內移出了，請繼續選擇以下操作：<br><br>--- [<a href='".PDIR."&tion=myablist&vid=".$thesup."'>回到專輯管理</a>]&nbsp;&nbsp;&nbsp; --- &nbsp;&nbsp;&nbsp;[<a href='".PDIR."&tion=mylist&types=2'>我的視頻列表</a>] ---");
	}


//---------------以上是”我的...“的功能，代碼段已經結束，以下是界面部分-----------------------------

}elseif($tion == "list"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="視頻列表...";

	if($types == 2){
		//按播放次數排序
		$types_sql = 't.views';
	}elseif($types == 3){
		//按評論數排序
		$types_sql = 'm.valuate';
	}elseif($types == 4){
		//按得分數排序
		$types_sql = 't.replies';
	}else{
		//按發布時間排序
		$types == 1;
		$types_sql = 'm.id';
	}

	$gourl = $_G['gp_gourl'];
	if($gourl == 1){
		$thegopage = '&gourl=1';
		$thegosql1 = "and album=0";
		$thegosql2 = "and m.album=0";
		$thegosql3 = "and v.album=0";
		$thegosele_1 = "selected";
	}elseif($gourl == 2){
		$thegopage = '&gourl=2';
		$thegosql1 = "and album=1";
		$thegosql2 = "and m.album=1";
		$thegosql3 = "and v.album=1";
		$thegosele_2 = "selected";
	}else{
		$thegosql1 = "and sup=0";
		$thegosql2 = "and m.sup=0";
		$thegosql3 = "and v.sup=0";
	}


	//獲取頁數信息

	$absum = 0;
	
	$page = max(1, intval($page));
	$ppp = 16;

	$sid = intval($_G['gp_sid']);
	$fid = intval($_G['gp_fid']);
	if($sid){//如果查看的是二級分目錄
		foreach($styleloop as $datarow){
			if($datarow['sid'] == $sid){
				$fid = $datarow['sup'];		//得到一級目錄的ID
				$sidsname = $datarow['sort'];
			}
		}

		//專輯個數
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sid='$sid' and album=1");
		$absum = DB::result($query, 0);

		//先取得頁面
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sid='$sid' and audit=1 $thegosql1");
		$maxpage = DB::result($query, 0);
		$countmax = $maxpage;
		$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=list&types='.$types."&sid=".$sid.$thegopage);

		//寫出SQL語句，等會要用到
		$query = DB::query("SELECT m.id, m.album, m.vsubject, m.vprice, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.sid='$sid' and m.audit=1 $thegosql1 ORDER BY $types_sql DESC limit ".(($page-1)*$ppp).", $ppp");
		$fidsidpage = "&sid=".$sid;
	}elseif($fid){

		//專輯個數
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys v LEFT JOIN hsk_vgallery_sort s USING(sid) where s.sup='$fid' and v.album=1");
		$absum = DB::result($query, 0);

		//先取得頁面
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys v LEFT JOIN hsk_vgallery_sort s USING(sid) where s.sup='$fid' and v.audit=1 $thegosql3");
		$maxpage = DB::result($query, 0);
		$countmax = $maxpage;
		$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=list&types='.$types."&fid=".$fid.$thegopage);

		//如果只選擇一級目錄，那麽SQL語句又不一樣
		$query = DB::query("SELECT m.id, m.album, m.vsubject, m.vprice, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN hsk_vgallery_sort s ON s.sid=m.sid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE s.sup='$fid' and m.audit=1 $thegosql2 ORDER BY $types_sql DESC limit ".(($page-1)*$ppp).", $ppp");
		$fidsidpage = "&fid=".$fid;
	}else{
		//專輯個數
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where album=1");
		$absum = DB::result($query, 0);

		//先取得頁面
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where audit=1 $thegosql1");
		$maxpage = DB::result($query, 0);
		$countmax = $maxpage;
		$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=list&types='.$types.$thegopage);
		//全部類別的SQL
		$query = DB::query("SELECT m.id, m.album, m.vsubject, m.vprice, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.audit=1 $thegosql2 ORDER BY $types_sql DESC limit ".(($page-1)*$ppp).", $ppp");
		$fidsidpage = null;
	}

	foreach($styleloop as $datarow){
		if($datarow['sid'] == $fid){
			$fidsname = $datarow['sort'];
		}
	}
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datarow['timelong'] = checkthetime($datarow['timelong']);
		$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
		$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
		$datarow['isalbum'] = $datarow['album'] ? 'ablist' : 'view';
		$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
		$datalist[] = $datarow;
	}
	
	include template("gallery_list", "Kannol", PTEM);

}elseif($tion == "author"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="會員發布列表...";
	$uid = intval($_G['gp_uid']);
	if(!$uid)		showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

	if($types == 2){
		//按播放次數排序
		$types_sql = 't.views';
	}elseif($types == 3){
		//按評論數排序
		$types_sql = 'm.valuate';
	}elseif($types == 4){
		//按得分數排序
		$types_sql = 't.replies';
	}else{
		//按發布時間排序
		$types == 1;
		$types_sql = 'm.id';
	}
	$fidsidpage = "&uid=".$uid;
	//獲取頁數信息

	$gourl = $_G['gp_gourl'];
	if($gourl == 1){
		$thegopage = '&gourl=1';
		$thegosql1 = "and album=0";
		$thegosql2 = "and m.album=0";
		$thegosql3 = "and v.album=0";
		$thegosele_1 = "selected";
	}elseif($gourl == 2){
		$thegopage = '&gourl=2';
		$thegosql1 = "and album=1";
		$thegosql2 = "and m.album=1";
		$thegosql3 = "and v.album=1";
		$thegosele_2 = "selected";
	}

	$absum = 0;
	
	$page = max(1, intval($page));
	$ppp = 16;

	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where uid='$uid' and audit=1 $thegosql1");
	$maxpage = DB::result($query, 0);
	$countmax = $maxpage;
	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=author&uid='.$uid.$thegopage);

	//寫出SQL語句，等會要用到
	$query = DB::query("SELECT m.id, m.album, m.vsubject, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.uid='$uid' and m.audit=1 $thegosql2 ORDER BY $types_sql DESC limit ".(($page-1)*$ppp).", $ppp");

	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datarow['timelong'] = checkthetime($datarow['timelong']);
		$datarow['isalbum'] = $datarow['album'] ? 'ablist' : 'view';
		$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
		$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
		$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
		$datalist[] = $datarow;
		$theusername = $datarow['username'];
	}
	
	include template("gallery_list", "Kannol", PTEM);



}elseif($tion == "search"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="視頻搜索結果...";

	if($types == 2){
		//按播放次數排序
		$types_sql = 't.views';
	}elseif($types == 3){
		//按評論數排序
		$types_sql = 'm.valuate';
	}elseif($types == 4){
		//按得分數排序
		$types_sql = 't.replies';
	}else{
		//按發布時間排序
		$types == 1;
		$types_sql = 'm.id';
	}
	$srchtxter = trim(dhtmlspecialchars($_G['gp_srchtxt']));
	$srchtxt = $srchtxter == '請輸入搜索內容' ? null : $srchtxter;
	$fidsidpage = $srchtxt ? "&srchtxt=".$srchtxt : null;
	//獲取頁數信息

	$page = max(1, intval($page));
	$ppp = 16;

	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where vsubject LIKE '%$srchtxt%' and audit=1 and album=0");
	$maxpage = DB::result($query, 0);
	$countmax = $maxpage;
	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=search'.$fidsidpage);

	//寫出SQL語句，等會要用到
	$query = DB::query("SELECT m.id, m.album, m.vsubject, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.vsubject LIKE '%$srchtxt%' and m.album=0 and m.audit=1 ORDER BY $types_sql DESC limit ".(($page-1)*$ppp).", $ppp");

	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
		$datarow['vsubjectc'] = str_replace($srchtxt, '<font color="red">'.$srchtxt.'</font>', $datarow['vsubjectc']);
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datarow['timelong'] = checkthetime($datarow['timelong']);
		$datarow['isalbum'] = $datarow['album'] ? 'ablist' : 'view';
		$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
		$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
		$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
		$datalist[] = $datarow;
	}
	
	include template("gallery_search", "Kannol", PTEM);



}elseif($tion == "ablist"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■視頻列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle ="專輯視頻列表...";

	if($types == 2){
		//按播放次數排序
		$types_sql = 't.views DESC';
	}elseif($types == 3){
		//按評論數排序
		$types_sql = 'm.valuate DESC';
	}elseif($types == 4){
		//按得分數排序
		$types_sql = 't.replies DESC';
	}else{
		//按發布時間排序
		$types == 1;
		$types_sql = 'm.id';
	}

	//專輯信息獲取
	$query = DB::query("SELECT m.id, m.uid, m.vsubject, m.sid, m.dateline, m.purl, m.vsum, m.views, m.vinfo, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p USING(uid) WHERE m.id='$vid'");
	if(!$abvalue = DB::fetch($query))	showmessage("對不起，未找到您要查看的有效信息，可能是權限參數不完成或非法來路，請返回檢查。");
	$abvalue['dateline'] = gmdate("Y-m-d, H:i", $abvalue['dateline'] + 3600 * $timeoffset);

	foreach($styleloop as $datarow2){
		if($datarow2['sid'] == $abvalue['sid']){
			$sort_1_sid = $fid = $datarow2['sup'];
			$sort_2_name = $datarow2['sort'];
		}
	}

	foreach($styleloop as $value){
		if($value['sid'] == $sort_1_sid)
			$sort_1_name = $value['sort'];
	}
	$sid = $abvalue['sid'];

	//獲取頁數信息
	$page = max(1, intval($page));
	$ppp = 20;

	//先取得頁面
	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sup='$vid' and audit=1");
	$maxpage = DB::result($query, 0);
	$countmax = $maxpage;
	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=ablist&vid='.$vid.'&types='.$types);

	$query = DB::query("SELECT m.id, m.album, m.vsubject, m.vprice, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.sup='$vid' and m.audit=1 ORDER BY $types_sql limit ".(($page-1)*$ppp).", $ppp");
	$fidsidpage = null;
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = preg_replace("/(.*?)第(.*?)集/", "<center>第 \\2 集</center>", $datarow['vsubject']);
		$datarow['vsubjectc'] = cutstr($datarow['vsubjectc'], 16, '..');
		$datarow['timelong'] = checkthetime($datarow['timelong']);
		$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
		$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
		$datarow['purl'] = $datarow['purl'] ? $datarow['purl'] : $abvalue['purl'];
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datalist[] = $datarow;
	}

	include template("gallery_ablist", "Kannol", PTEM);

}elseif($tion == "view"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■播放視頻段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");
	$navname="觀看視頻...";

	//檢查是否有權限，權限1、管理員，權限2、自己
	$query = DB::query("SELECT m.*, p.username, c.$vcuser, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('common_member_count')." c ON c.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.id='$vid'");
	if(!$viewdata = DB::fetch($query))	showmessage("對不起，未找到您要觀賞的視頻，可能是權限不足或無信息，請返回檢查。");


	$viewdata['timelong'] = checkthetime($viewdata['timelong']);
	$viewdata['dateline'] = gmdate("Y年m月d日, H:i", $viewdata['dateline'] + $timeoffset * 3600);
	$viewdata['vinfo'] = nl2br($viewdata['vinfo']);
	$viewdata['views'] = $viewdata['views2'] ? $viewdata['views2'] : $viewdata['views'];
	$viewdata['polls'] = $viewdata['views2'] ? $viewdata['replies'] : $viewdata['polls'];
	$thesup = $viewdata['sup'];
	$thesupsid = $viewdata['supsid'];
	$bzuid = $viewdata['uid'];
	$sid = $viewdata['sid'];
	$tid = $viewdata['tid'];
	//print_r($viewdata);exit();

	if($viewdata['album'])
		showmessage("請稍候，正在轉入專輯列表...", PDIR."&tion=ablist&vid=".$vid);

	if($tid){
		DB::query("UPDATE ".DB::table('forum_thread')." SET views=views+1 where tid='$tid'");
	}else{
		DB::query("UPDATE hsk_vgallerys SET views=views+1 where id='$vid'");
	}

	if($thesup)
		DB::query("UPDATE hsk_vgallerys SET views=views+1 where id='$thesup'");


	foreach($styleloop as $sidrow){
		if($sidrow['sid'] == $sid){
			$fid = $sidrow['sup'];		//得到一級目錄的ID
			$sidsname = $sidrow['sort'];
		}
	}
	//視頻類型

	$thestyle = strtolower(substr(strrchr($viewdata['vurl'],"."),1));
	if('flv' == $thestyle){
		$theplayer_style = 'flv';
		$flv_title = substr($_G['setting']['discuzurl'], 7);
	}elseif('qvod://' == strtolower(substr($viewdata['vurl'], 0, 7))){
		$theplayer_style = 'qvod';
	}elseif('gvod://' == strtolower(substr($viewdata['vurl'], 0, 7))){
		$theplayer_style = 'gvod';
	}elseif('mms://' == strtolower(substr($viewdata['vurl'], 0, 6))){
		$theplayer_style = 'mms';
	}elseif('rtsp://' == strtolower(substr($viewdata['vurl'], 0, 7))){
		$theplayer_style = 'rtsp';
	}elseif(in_array($thestyle, array('wmv','avi','wma','mp4','mp3'))){
		$theplayer_style = 'mms';
	}elseif(in_array($thestyle, array('rm','rmvb','ram','ra'))){
		$theplayer_style = 'rtsp';
	}elseif($thestyle == 'mov'){
		$theplayer_style = 'mov';
	}else{
		$theplayer_style = 'swf';
	}

	if($thecooksave){

		//最新的5個視頻$(array)unserialize($);

		$dvlistn[0]['id'] = $viewdata['id'];
		$dvlistn[0]['subject'] = cutstr($viewdata['vsubject'], 36, '..');

		$dvlist = (array)unserialize(stripslashes($_G['cookie']['vgallery_list']));

		$dvlx = count($dvlist);

		if($dvlx){
			//有數據，分別往後推
			for($i=0; $i<$dvlx && $i<$thecooksave-1; $i++){
				if($vid == $dvlist[$i]['id'])
					$now= 1;
				$dvlistn[$i+1]['id'] = $dvlist[$i]['id'];
				$dvlistn[$i+1]['subject'] = $dvlist[$i]['subject'];
			}
		}
		$dvlistw = serialize($dvlistn);
		if($now){}else{
			dsetcookie('vgallery_list', $dvlistw, 31536000);
		}
	}

	//付費視頻：
	if($viewdata['vprice'] && $adminid!=1 && !$groupon_3 && $discuz_uid != $viewdata['uid']){
		$tid = $viewdata['tid'];
		//付費的視頻，檢查是否購買過
		$query = DB::query("SELECT dateline FROM ".DB::table('common_credit_log')." WHERE uid='$discuz_uid' and operation='BTC' and relatedid='$tid'");
		if(!$inbuy = DB::fetch($query)){
			$buynow = 1;
			$viewdata['purl'] = null;
		}
	}

	if($types == "fullscreen"){
		if(!$buynow){
			//大屏播放
			include template("gallery_view_fullscreen", "Kannol", PTEM);
			exit();
		}else{
			//要付款
			showmessage("付費視頻您必須先購買才可以觀看，請不要動歪腦筋了。", 'javascript:close();', array(), array('closetime' => false, 'showdialog' => 1));
		}
	}

	$query = DB::query("SELECT id, sid, vsubject, purl FROM hsk_vgallerys WHERE uid='$bzuid' and audit=1 and album=0 and id<>$vid ORDER BY id desc limit 0, 9");
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 10, '..');
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datalist[] = $datarow;
	}

	$smile = null;
	for($i=0; $i<10; $i++){
		$smile .= ' <a style="cursor:pointer" onclick="check_smile('.$i.')"><img src="'.MDIR.'/img'.$i.'.gif" border="0"></a>';
	}
	$auditviews = !$isauditv || $adminid==1 ? null : '<font color=red>您發布的評論需經<b>審核</b>才能顯示！</font>';
	$navtitle = $viewdata['vsubject'];
	include template("gallery_view", "Kannol", PTEM);


}elseif($tion == 'buynow'){			//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■購買視頻■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能購買視頻，請先關閉此提示並登錄。");
	if(!$vid)	showmessage("參數傳遞時出現錯誤，未能找到有效的參數值，請返回檢查連接是否正常。");

	//不需要購買的用戶組
	if($groupon || $adminid == 1){
		showmessage('您所在的用戶級不需要購買視頻，請關閉當著提示並刷新本頁面。');
	}
	
	$query = DB::query("SELECT m.id, m.tid, m.uid, m.vsubject, m.vprice, p.username, c.$money_id FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('common_member_count')." c ON c.uid=m.uid WHERE m.id='$vid'");
	if(!$datarow = DB::fetch($query))	showmessage("對不起，未找到您要購買的視頻，可能是未審核或已經刪除的。");
	if($datarow['uid'] == $discuz_uid){
		showmessage('自己發布的視頻，是不需要購買的，請關閉當著提示並刷新本頁面。');
	}

	$tid = $datarow['tid'];
	$datarow['netprice'] = floor($datarow['vprice'] * (1 - $creditstax));
	if(!submitcheck('reportsubmit')){
		//顯示購買前的信息
		$mymoney_lave = $mymoney - $datarow['vprice'];

		include template("gallery_buynow", "Kannol", PTEM);

	}else{
		//購買，並轉入
		//付費的視頻，檢查是否購買過
		$query = DB::query("SELECT dateline FROM ".DB::table('common_credit_log')." WHERE uid='$discuz_uid' and operation='BTC' and relatedid='$tid'");
		if(!$inbuy = DB::fetch($query)){
			//如果還沒購買過，那麽就購買

			if($mymoney - $datarow['vprice'] < 0){//沒有錢買
				showmessage('目前您的'.$money_name.'不夠，無法購買此視頻，請先多賺點'.$money_name.'再來買吧...', PDIR.'&tion=view&vid='.$vid);
			}

			//進入寫數據
			$updateauthor = true;
			if($maxincperthread > 0) {
				if((DB::result_first("SELECT SUM($money_id) FROM ".DB::table('common_credit_log')." WHERE uid='$datarow[uid]' AND operation='STC' AND relatedid='$tid'")) > $maxincperthread) {
					$updateauthor = false;
				}
			}
			if($updateauthor) {
				updatemembercount($datarow['uid'], array($money_id => $datarow['netprice']), 1, 'STC', $tid);
			}
			updatemembercount($discuz_uid, array($money_id => -$datarow['vprice']), 1, 'BTC', $tid);

			//檢查是否已經在自己的收藏夾中
			$query = DB::query("SELECT id FROM hsk_vgallery_favorites WHERE uid='$discuz_uid' and vid='$vid'");
			if(!$inthef = DB::fetch($query)){
				DB::query("INSERT INTO hsk_vgallery_favorites (vid, uid, dateline) VALUES ('$vid', '$discuz_uid', '$timestamp')");
			}

			showmessage('已經成功的購買了此視頻，並已經存入您的收藏夾，爲您轉入視頻觀看頁面...', PDIR.'&tion=view&vid='.$vid);

		}else{
			showmessage('您已經購買過此視頻了，不用再次購買，現在爲您轉入觀看頁面...', PDIR.'&tion=view&vid='.$vid);
		}

	}

}elseif($tion == 'loadpolls'){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■獲取評論列表段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■


	//獲取頁數信息
	$page = max(1, intval($page));
	$ppp = 10;
	//cache_smilies();

	if(!$vid)
		showmessage('對不起，參數傳遞不正常，無法繼續，請返回重試！');

	$query = DB::query("SELECT v.id, v.tid, v.pid, t.views, t.replies, v.uid, v.dateline, v.vsubject, v.vinfo, v.vurl, v.vprice, v.sup FROM hsk_vgallerys v LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=v.tid WHERE v.audit='1' and v.id='$vid'");
	if(!$datarow = DB::fetch($query)){
		showmessage("未找到您要查看的評論內容，可能是未被審核或已被刪除！");
	}

	//先取得頁面
	$tid = $datarow['tid'];
	$pid = $datarow['pid'];
	$countmax = $maxpage = $datarow['replies'];
	$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=loadpolls&vid='.$vid, 0, 8, 0, 'poplepolls');

	$query = DB::query("SELECT p.*, m.username
							FROM ".DB::table('forum_post'). " p
							LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid
							WHERE p.invisible='0' and p.tid='$tid' and p.pid<>'$pid' ORDER BY p.dateline DESC LIMIT ".(($page-1)*$ppp).", $ppp");

	// libfile('function/discuzcode');
	while($datarow = DB::fetch($query)){
		$datarow['dbdateline'] = gmdate("Y-m-d H:i", $datarow['dateline'] + $timeoffset * 3600);
		$datarow['dateline'] = dgmdate($datarow['dateline'], 'u');
		$datarow['post_quote'] = cutstr(preg_replace('/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is', '', $datarow['message']), 80, '...');
		$maxsiliescode = $_G['setting']['maxsmilies'];
		$datarow['post'] = discuzcode($datarow['message'], $maxsiliescode);
		$pollvs[] = $datarow;
	}

	include template("gallery_polls", 'Kannol', PTEM);


}elseif($tion == "reply"){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■影視評論代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	if(!$isevaluate && $adminid != 1)
		showmessage("目前管理員關閉了視頻評論功能，給您帶來不便請體諒！");
	$revid = intval($_G['gp_revid']);
	if(!$revid)
		showmessage('對不起，參數傳遞不正常，無法繼續，請返回重試！');

	$message = $_G['gp_message'];

	if(submitcheck('replysubmit')) {
		if(!$discuz_uid)
			showmessage("請先登錄或注冊，再進行評論！");
		$message_box = strlen(preg_replace('/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is', '', $message));
		if($message == '' || ($message && $message_box == 0)) {
			showmessage('請您填寫有效的評論內容後再提交！');
		}
		if(strlen($message) > 500 || $message_box < 6)	showmessage("評論的字數應該保持在6-500個字符（3-250個漢字）！");;

		require_once libfile('function/post');
		if(checkflood()) {
			showmessage('post_flood_ctrl');
		}
		//
	}else{
		//showmessage("非法提交評論！");
	}

	$query = DB::query("SELECT v.id, v.tid, m.username, v.uid, v.dateline, v.vsubject, v.vinfo, v.vurl, v.vprice, v.sup FROM hsk_vgallerys v LEFT JOIN ".DB::table('common_member')." m ON m.uid=v.uid WHERE v.audit='1' and v.id='$revid'");
	if(!$datarow = DB::fetch($query)){
		showmessage("未找到您要評論的視頻，可能是未被審核或已被刪除！");
	}
	$tid			= $datarow['tid'];

	$query = DB::query("SELECT tid from ".DB::table("forum_thread")." WHERE tid='$tid'");
	if(!$datasearch = DB::fetch($query)){
		$tid = 0;
	}


	$push_user		= $datarow['username'];
	$push_uid		= $datarow['uid'];
	$vname			= $datarow['vsubject'];
	require_once libfile('function/post');
	require_once libfile('function/forum');
	if(!$tid){
		//自動生成貼子並回複
		$push_time		= $datarow['dateline'];
		$vmessage		= $datarow['vinfo'];
		$vprice			= $datarow['vprice'];
		$thesup			= $datarow['sup'];
		$vurl			= $datarow['vurl'];

		//無論壇主題的，自動生成
		$strarr = array('fid'=>"$thefid", 'posttableid'=>'0', 'readperm'=>'0', 'price'=>"$vprice", 'typeid'=>'0', 'sortid'=>'0', 'author'=>"$push_user", 'authorid'=>"$push_uid", 'subject'=>"$vname", 'dateline'=>"$timestamp", 'lastpost'=>"$timestamp", 'lastposter'=>"$push_user", 'displayorder'=>'0', 'digest'=>'0', 'special'=>'0', 'attachment'=>'0', 'moderated'=>'0', 'highlight'=>'0', 'closed'=>'	$orclosed', 'status'=>'0', 'isgroup'=>'0');
		$tid = DB::insert('forum_thread', $strarr, 1);

		$postdateline = gmdate("Y-m-d", $push_time + 3600 * $timeoffset);
		$postmessage = loadmsg($vmessage, $vname, $push_user, $revid, $vurl, $postdateline, $vprice, $thesup);
		$pid = insertpost(array('fid' => $thefid,'tid' => $tid,'first' => '1','author' => "$push_user",'authorid' => $push_uid,'subject' => "$vsubject",'dateline' => $timestamp,'message' => "$postmessage",'useip' => $clientip,'invisible' => '0','anonymous' => '0','usesig' => '0','htmlon' => '0','bbcodeoff' => '0','smileyoff' => '0','parseurloff' => '0','attachment' => '0',));
		$expiration = $timestamp + 86400;

		DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$push_uid', '$push_user', '$timestamp', 'EHL', '$expiration', '1')");
		DB::query("INSERT INTO ".DB::table('forum_thread')."mod (tid, uid, username, dateline, action, expiration, status) VALUES ('$tid', '$push_uid', '$push_user', '$timestamp', 'CLS', '0', '1')");

		updatepostcredits('+', $push_uid, 'post', $thefid);
		$lastpost = "$tid\t".addslashes($vsubject)."\t$timestamp\t$push_user";
		DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$thefid'", 'UNBUFFERED');
		DB::query("UPDATE hsk_vgallerys SET pid='$pid', tid='$tid' WHERE id='$revid'");
	}

	
	//直接寫入到POST表裏
	require_once libfile('function/post');
	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
	$usesig = !empty($_G['gp_usesig']) ? 1 : ($_G['uid'] && $_G['group']['maxsigsize'] ? 1 : 0);

	$pinvisible = !$isauditv || $adminid==1 || $groupon_3 ? "0" : "-2";
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);

	$pid = insertpost(array(
		'fid' => $thefid,
		'tid' => $tid,
		'first' => '0',
		'author' => $discuz_user,
		'authorid' => $discuz_uid,
		'subject' => '',
		'dateline' => $timestamp,
		'message' => $message,
		'useip' => $clientip,
		'invisible' => $pinvisible,
		'anonymous' => 0,
		'usesig' => $usesig,
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'status' => (defined('IN_MOBILE') ? 8 : 0),
	));

	$lastpostsql = "lastpost='$timestamp',";
	DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$discuz_user', $lastpostsql replies=replies+1 WHERE tid='$tid'", 'UNBUFFERED');
	updatepostcredits('+', $discuz_uid, 'reply', $thefid);

	$lastpost = "$tid\t".addslashes($vsubject)."\t$timestamp\t$discuz_user";
	DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$thefid'", 'UNBUFFERED');

	//通知提醒。

	if($push_uid != $discuz_uid){
		$message = '<a href="home.php?mod=space&uid='.$discuz_uid.'" target="_blank">'.$discuz_user.'</a> 評論了您的視頻 <a href="plugin.php?id=vgallery&tion=view&vid='.$revid.'" target="_blank">'.$vname.'</a> &nbsp; 您也可通過貼子形式 <a href="forum.php?mod=redirect&goto=findpost&pid='.$pid.'&ptid='.$tid.'" target="_blank" class="lit">查看</a>';
		notification_add($push_uid, 'post', $message, array(
			'from_id' => $tid,
			'from_idtype' => 'post',
		));
	}

	showmessage('恭喜您，已經成功發表評論！', '', array(), array('showmsg' => 0, 'locationtime' => true));

}elseif($tion == 'loadsup'){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■觀看視頻時，如果是專輯，就顯示專輯中的其它視頻連接■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$rid = intval($_G['gp_rid']);
	$oid = intval($_G['gp_oid']);
	$oid = $oid ? $oid : $rid;
	$seleid = $oid ? $oid : $rid;
	//先取得總數
	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where audit=1 and sup='$vid' and album=0 and id<$rid");
	$thesumx = DB::result($query, 0);

	if($thesumx >= 4){
		if($thesumx>4){
			//說明有多于4個那麽就可以點向前按扭
			$link_p = '<a href="'.PDIR.'&tion=loadsup&oid='.$oid.'&vid='.$vid.'&rid=%thepurl%" onclick="ajaxget(this.href, \'load_thealbum\');doane(event);"><img border="0" src="'.MDIR.'/p1.gif" width="13" height="65" onmouseover="this.src=\''.MDIR.'/p2.gif\'"; onmouseout="this.src=\''.MDIR.'/p1.gif\';"></a>';
		}else{//如果等于4，說明正好顯示完，那麽就不可以點擊了
			$link_p = '<img border="0" src="'.MDIR.'/p0.gif" width="13" height="65">';
		}
		$thesum = 4;
		$theout = 5;
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where audit=1 and sup='$vid' and album=0 and id>=$rid");
		$theout1 = DB::result($query, 0);
		if($theout1 <= 4){
			$link_n = '<img border="0" src="'.MDIR.'/ne0.gif" width="13" height="65">';
			if($theout+$thesumx<=9)
				$link_p = '<img border="0" src="'.MDIR.'/p0.gif" width="13" height="65">';
			$thesum = 9-$theout1;
			$theout = $theout1;
		}else{
			if($theout1==5){
				$link_n = '<img border="0" src="'.MDIR.'/ne0.gif" width="13" height="65">';
			}else{
				$link_n = '<a href="'.PDIR.'&tion=loadsup&oid='.$oid.'&vid='.$vid.'&rid=%thenurl%" onclick="ajaxget(this.href, \'load_thealbum\');doane(event);"><img border="0" src="'.MDIR.'/ne1.gif" width="13" height="65" onmouseover="this.src=\''.MDIR.'/ne2.gif\'"; onmouseout="this.src=\''.MDIR.'/ne1.gif\';"></a>';
			}
		}
	}else{
		$thesum = $thesumx;
		$theout = 9-$thesum;
		$link_p = '<img border="0" src="'.MDIR.'/p0.gif" width="13" height="65">';
		$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where audit=1 and sup='$vid' and album=0 and id>=$rid");
		$theout1 = DB::result($query, 0);
		if($theout1 <= 4){
			$link_n = '<img border="0" src="'.MDIR.'/ne0.gif" width="13" height="65">';
			$thesum = 9-$theout1;
			$theout = $theout1;
		}else{
			if($theout1==5 || $theout1+$thesum<=9){
				$link_n = '<img border="0" src="'.MDIR.'/ne0.gif" width="13" height="65">';
			}else{
				$link_n = '<a href="'.PDIR.'&tion=loadsup&oid='.$oid.'&vid='.$vid.'&rid=%thenurl%" onclick="ajaxget(this.href, \'load_thealbum\');doane(event);"><img border="0" src="'.MDIR.'/ne1.gif" width="13" height="65" onmouseover="this.src=\''.MDIR.'/ne2.gif\'"; onmouseout="this.src=\''.MDIR.'/ne1.gif\';"></a>';
			}
		}
	}
	
	$query = DB::query("SELECT m.id, m.vsubject, m.purl, n.vsubject as abname, n.vsum, n.purl as abpurl FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.sup='$vid' and m.audit=1 and m.album=0 and m.id<$rid ORDER BY m.id desc limit 0, $thesum");

	$thesupname = null;
	$thesupvsum = 0;
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = preg_replace("/(.*?)第(.*?)集/", "<center>第 \\2 集</center>", $datarow['vsubject']);
		$datarow['vsubjectc'] = $datarow['vsubjectc'] == $datarow['vsubject'] ? cutstr($datarow['vsubjectc'], 12, '..') : $datarow['vsubjectc'];
		$datarow['purl'] = $datarow['purl'] ? $datarow['purl'] : $datarow['abpurl'];
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$thesupname = $thesupname ? $thesupname : $datarow['abname'];
		$thesupvsum = $thesupvsum ? $thesupvsum : $datarow['vsum'];
		$datalist[] = $datarow;
	}

	$query = DB::query("SELECT m.id, m.vsubject, m.purl, n.vsubject as abname, n.vsum, n.purl as abpurl FROM hsk_vgallerys m LEFT JOIN hsk_vgallerys n ON n.id=m.sup WHERE m.sup='$vid' and m.audit=1 and m.album=0 and m.id>=$rid ORDER BY m.id limit 0, $theout");

	$thesupname = null;
	$thesupvsum = 0;
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = preg_replace("/(.*?)第(.*?)集/", "<center>第 \\2 集</center>", $datarow['vsubject']);
		$datarow['vsubjectc'] = $datarow['vsubjectc'] == $datarow['vsubject'] ? cutstr($datarow['vsubjectc'], 12, '..') : $datarow['vsubjectc'];
		$datarow['purl'] = $datarow['purl'] ? $datarow['purl'] : $datarow['abpurl'];
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$thesupname = $thesupname ? $thesupname : $datarow['abname'];
		$thesupvsum = $thesupvsum ? $thesupvsum : $datarow['vsum'];
		$datalist1[] = $datarow;
	}

	$thestr_p = null;
	for($i=$thesum-1; $i>=0; $i--){
		if($datalist[$i])
			$datalist2[] = $datalist[$i];
		$thestr_p = $thestr_p ? $thestr_p : $datalist[$i]['id'];
	}

	$thestr_n = null;
	for($i=0; $i<$theout; $i++){
		if($datalist1[$i])
			$datalist2[] = $datalist1[$i];
		$thestr_n = $datalist1[$i]['id'];

	}

	//替換連接點的ID值	
	$link_p = str_replace("%thepurl%", $thestr_p, $link_p);	$link_n = str_replace("%thenurl%", $thestr_n, $link_n);

	include template("gallery_loadsup", 'Kannol', PTEM);

}elseif($tion == 'pollstar'){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■影視打分段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$mystar = intval($_G['gp_mystar']);
	$polls = intval($_G['gp_polls']);
	$dele = intval($_G['gp_dele']);


	//查自己是否有評分
	$query = DB::query("SELECT COUNT(*) FROM hsk_vgallery_evaluate where vid='$vid' and mypolls>0");
	$maxpage = DB::result($query, 0);

	if($discuz_uid && $dele==1 && $israte){
		//刪除評分
		DB::query("DELETE FROM hsk_vgallery_evaluate WHERE vid='$vid' and uid='$discuz_uid' and mypolls>0");
	}

	$arrmypolls = $polldisabled = $allpolls = 0;
	$query = DB::query("SELECT mypolls, uid, vid FROM hsk_vgallery_evaluate where vid='$vid' and mypolls>0");
	while($datarow = DB::fetch($query)){
		$arrmypolls++;
		$allpolls += $datarow['mypolls'];
		if($datarow['uid'] == $discuz_uid){
			$polldisabled = 1;
			$themypolls = $datarow['mypolls'];
		}
	}

	if($polldisabled==0 && $polls>0 && $polls<=10 && $israte && $discuz_uid){
		//評分
		DB::query("INSERT INTO hsk_vgallery_evaluate (vid, uid, dateline, audit, post, mypolls)
					VALUES ('$vid', '$discuz_uid', '$timestamp', '1', '', '$polls')");
		$arrmypolls += 1;
		$polldisabled = 1;
		$themypolls = $polls;
		$thepolls = sprintf("%01.2f", ($allpolls+$polls)/($maxpage+1));
		$maxpage += 1;
	}else{
		$thepolls = $maxpage ? sprintf("%01.2f", $allpolls/$maxpage) : 0 ;
	}

	$query = DB::query("SELECT m.sid, m.valuate, m.dateline, m.views, m.polls, m.valuate, t.views as views2, t.replies FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid where m.id='$vid'");
	if($datarow = DB::fetch($query)){
		$datarow['dateline'] = gmdate("Y-m-d H:i", $datarow['dateline'] + $timeoffset * 3600);
		$datarow['views'] = $datarow['views2'] ? $datarow['views2'] : $datarow['views'];
		$datarow['polls'] = $datarow['views2'] ? $datarow['replies'] : $datarow['polls'];
		$mystar = sprintf("%01.1f", $datarow['valuate']/100);
	}

	if($mystar != $thepolls){
		$che_polls = $thepolls * 100;
		DB::query("UPDATE hsk_vgallerys SET valuate='$che_polls' WHERE id='$vid'");
		$mystar = 0;
	}

	//print_r($mypolls);
	//exit($mystar);

	include template("gallery_star", 'Kannol', PTEM);

}elseif($tion == 'loadsid'){//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■觀看頁面加載本類最新和最熱門視頻的代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$sid = intval($_G['gp_sid']);
	$typesql = $types==2 ? 'id' : 'views';

	$query = DB::query("SELECT purl, id, vsubject FROM hsk_vgallerys WHERE audit=1 and sid='$sid' and album=0 ORDER BY $typesql DESC LIMIT 0, 6");

	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 10, '..');
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$divloop[] = $datarow;
	}


	include template("gallery_loadsid", 'Kannol', PTEM);



/*


■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■



*/

}elseif($tion == 'manage'){

	if($adminid == 1 || $groupon_3){
	}else{
		showmessage("非法提交！");
	}

	if($types == "a"){
		//審核視頻

		if(!$groupon_3)
			showmessage("越權操作！");


		if(!submitcheck('reportsubmit')){
			//管理視頻
			$navname="審核新視頻...";
			//獲取頁數信息

			$page = max(1, intval($page));
			$ppp = 20;

			//先取得頁面
			$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where audit=0");
			$maxpage = DB::result($query, 0);
			$countmax = $maxpage;
			$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types);
			//全部類別的SQL
			$query = DB::query("SELECT m.id, m.vsubject, m.dateline, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p USING(uid) WHERE m.audit=0 ORDER BY m.id DESC limit ".(($page-1)*$ppp).", $ppp");
			$fidsidpage = null;

			while($datarow = DB::fetch($query)){
				$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
				if(!substr($datarow['purl'],0,7) == 'http://'){
					$thepicurl = DISCUZ_ROOT.$datarow['purl'];
					if(!file_exists("$thepicurl") || !$datarow['purl']){
						$datarow['purl'] = "./".MDIR."/noimages.gif";
					}
				}
				$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['timelong'] = checkthetime($datarow['timelong']);
				$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
				$datalist[] = $datarow;
			}
			include template("manage_audit", 'Kannol', PTEM);

		}else{
			//提交後
			$ptdata = $_G['gp_ptdata'];

			$auditarr = $deletes = "0";
			if(is_array($ptdata)){
				foreach($ptdata as $keys=>$value){
					if($value == 1){
						//審核通過;
						$auditarr .= ",".$keys;
					}else if($value == 2){
						//刪除
						$deletes .= ",".$keys;
					}
				}
			}

			//先刪除

			$mycon=0;
			require_once libfile('function/delete');
			require_once libfile('function/post');
			if($deletes){
				
				$mycon=1;
				$query = DB::query("SELECT uid, purl,tid FROM hsk_vgallerys WHERE id IN ($deletes)");
				$delepic = 0;
				$_G['forum']['fid'] = $thefid;
				while($datadele = DB::fetch($query)){

					if(!substr($datadele['purl'],0,7) == 'http://'){
						$thepicurl = DISCUZ_ROOT.$datadele['purl'];
						if(file_exists("$thepicurl") && $datadele['purl']){
							$datadele['purl'] = $thepicurl;
							$pslist[] = $datadele;
							$delepic = 1;
						}
					}
					$delearray[$datadele['tid']] = '1';
				}
				DB::query("DELETE FROM hsk_vgallerys WHERE id IN ($deletesql)");
				$tids = array_keys($delearray);
				deletethread($tids, true, true);
				updateforumcount($thefid);

				//刪除文件....
				if($delepic){
					foreach($pslist as $id) {
						@unlink($id['purl']);
					}
				}
			}

			if($auditarr){
				$mycon=1;
				//審核
				$query = DB::query("Update hsk_vgallerys set audit='1' WHERE id IN ($auditarr)");
				$query = DB::query("Update ".DB::table('forum_thread')." set closed='0' WHERE tid IN ($auditarr)");

				index_topstyle_all();
			}

			if($mycon==0){
				showmessage('提示：本次並沒有選擇任何操作，請確認！', dreferer());
			}else{
				updateforumcount($thefid);
				showmessage('提示：已經成功執行了您對選中的視頻的相關操作！', dreferer());
			}
		}

	}elseif($types == "b"){
		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");

		if(!submitcheck('reportsubmit')){
			//管理視頻
			$navname="管理專輯內的視頻...";
			//獲取頁數信息

			$page = max(1, intval($page));
			$ppp = 20;

			//先取得頁面
			$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sup=$vid");
			$maxpage = DB::result($query, 0);
			$countmax = $maxpage;
			$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types."&vid=".$vid);
			//全部類別的SQL
			$query = DB::query("SELECT m.id, m.tid, t.views, t.replies, m.vsubject, m.dateline, m.purl, m.uid, m.views, m.polls, m.valuate, m.timelong, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid WHERE m.sup=$vid ORDER BY m.id DESC limit ".(($page-1)*$ppp).", $ppp");
			$fidsidpage = null;

			while($datarow = DB::fetch($query)){
				$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
				if(!substr($datarow['purl'],0,7) == 'http://'){
					$thepicurl = DISCUZ_ROOT.$datarow['purl'];
					if(!file_exists("$thepicurl") || !$datarow['purl']){
						$datarow['purl'] = "./".MDIR."/noimages.gif";
					}
				}
				$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['timelong'] = checkthetime($datarow['timelong']);
				$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
				$datalist[] = $datarow;
			}
			include template("manage_list", 'Kannol', PTEM);

		}else{
			//提交後
			$deletes = $_G['gp_deletes'];
			$deletesql = implode(",", $deletes);
			if(empty($deletes)) showmessage("對不起，您沒有選中任何一個視頻，所以不能執行操作。", dreferer());

			$query = DB::query("SELECT purl FROM hsk_vgallerys WHERE id='$vid'");
			if(!$abdatarow = DB::fetch($query)){
				showmessage("專輯不存在，可能已經被刪除！", dreferer());
			}



			$query = DB::query("SELECT uid, purl, tid FROM hsk_vgallerys WHERE id IN ($deletesql)");
			$delepic = $i = 0;
			while($datadele = DB::fetch($query)){
				if(!substr($datadele['purl'],0,7) == 'http://' && $datadele['purl'] != $abdatarow['purl']){
					$thepicurl = DISCUZ_ROOT.$datadele['purl'];
					if(file_exists("$thepicurl") && $datadele['purl']){
						$datadele['purl'] = $thepicurl;
						$pslist[] = $datadele;
						$delepic = 1;
					}
				}
				$delearray[$datadele['tid']] = '1';
				$i++;
			}
			require_once libfile('function/delete');
			require_once libfile('function/post');
			$_G['forum']['fid'] = $thefid;
			DB::query("DELETE FROM hsk_vgallerys WHERE id IN ($deletesql)");
			DB::query("UPDATE hsk_vgallerys SET vsum=vsum-$i WHERE id='$vid'");

			$tids = array_keys($delearray);
			updateforumcount($thefid);
			deletethread($tids, true, true);


			//刪除文件....
			if($delepic){
				foreach($pslist as $id) {
					@unlink($id['purl']);
				}
			}
			index_topstyle_all();
			showmessage('提示：已經成功的刪除了您選擇的視頻！', dreferer());
		}

	}elseif($types == "p"){
		if(!$groupon_3)
			showmessage("越權操作！");

		if(!submitcheck('reportsubmit')){
			//管理視頻
			$navname="管理評論內容...";
			//獲取頁數信息

			$poststitle = "所有視頻的評論...";
			if($vid){
				$vidpage = "&vid=".$vid;
				$query = DB::query("SELECT vsubject, id, tid from hsk_vgallerys where id='$vid'");
				if($datarow1 = DB::fetch($query)){
					$poststitle = "<a href='".PDIR."&tion=view&vid=".$datarow1['id']."' class='xi1' target='_blank'>".$datarow1['vsubject']."</a> 的評論...";
				}else{
					showmessage("沒有找到您要查詢的資源，可能已經被刪除了！（請返回）");
				}
				$vidsql1 = "and tid='$datarow1[tid]'";
				$vidsql2 = "and p.tid='$datarow1[tid]'";
			}

			$page = max(1, intval($page));
			$ppp = 30;

			//先取得頁面
			$query = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_post')." where fid='$thefid' and first<>1 $vidsql1");
			$maxpage = DB::result($query, 0);
			$countmax = $maxpage;
			$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types.$vidpage);
			//全部類別的SQL
			$query = DB::query("SELECT p.subject, p.pid, p.author, p.authorid, v.tid, v.id as vid, p.message, p.dateline, p.invisible
									FROM ".DB::table('forum_post')." p
									LEFT JOIN hsk_vgallerys v ON v.tid=p.tid
									WHERE p.fid='$thefid' and p.first<>1 $vidsql2 ORDER BY p.dateline DESC LIMIT ".(($page-1)*$ppp).", $ppp");

			while($datarow = DB::fetch($query)){
				$datarow['postc'] = cutstr($datarow['message'], 50, '..');
				$datarow['dateline1'] = gmdate("m-d H:i", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['audits'] = $datarow['invisible']==0 ? null : "<font color=red>未審核-</font>";
				$datalist[] = $datarow;
			}
			include template("manage_polls", 'Kannol', PTEM);

		}else{
			//提交後
			$ptdata = $_G['gp_ptdata'];

			$auditarr = $deletes = "0";
			if(is_array($ptdata)){
				foreach($ptdata as $keys=>$value){
					if($value == 1){
						//審核通過;
						$auditarr .= ",".$keys;
					}else if($value == 2){
						//刪除
						$deletes .= ",".$keys;
					}
				}
			}

			//先刪除

			$mycon=0;
			if($auditarr){
				$mycon=1;
				//審核
				$query = DB::query("Update ".DB::table('forum_post')." set invisible='0' WHERE pid IN ($auditarr)");
			}

			if($deletes){
				$mycon=1;
				//刪除審核
				$query = DB::query("Update ".DB::table('forum_post')." set invisible='-2' WHERE pid IN ($deletes)");
			}

			if($mycon==0){
				showmessage('提示：本次並沒有選擇任何操作，請確認！', dreferer());
			}else{
				showmessage('提示：已經成功執行了您對選中的評論的相關操作！', dreferer());
			}
		}

	}elseif($types == "s"){
		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");

		$navname="管理類別...";
		$sid = intval($_G['gp_sid']);

		if(submitcheck('reportsubmit')){


			//刪除
			$deletes = $_G['gp_deletes'];
			$deletesql = implode(",", $deletes);

			if($deletes)
				DB::query("DELETE FROM hsk_vgallery_sort WHERE sid IN ($deletesql)");

			
			//重新排序
			$newdps = ($_G['gp_newdps']);
			if(is_array($newdps)){
				foreach($newdps as $keys=>$value){
					DB::query("UPDATE hsk_vgallery_sort SET dps='$value' where sid='$keys'");
				}
			}


			$new_sort_dps = intval($_G['gp_new_sort_dps']);
			$new_sort = trim($_G['gp_new_sort']);
			$newsup = intval($_G['gp_newsup']);

			if(!$new_sort){
				//沒有名稱
				mystylewrite();
			}else{
				//看看有沒有重複的
				$query = DB::query("SELECT sid FROM hsk_vgallery_sort where sup=$newsup and sort='$new_sort'");
				if($datarow = DB::fetch($query)){
					//在同級類別裏有相同的類別名稱
					mystylewrite();
					showmessage('對不起，新類別名稱于現有的同一級或同一個類別中的名稱重複，請返回重新填寫！');
				}else{
					//新增
					DB::query("INSERT INTO hsk_vgallery_sort (sup, dps, sort) 
						VALUES ('$newsup', '$new_sort_dps', '$new_sort')");
					//緩存
				}
			}			
			mystylewrite();
			showmessage('恭喜，類別管理操作已經成功了！',dreferer());

		}else{
			$query = DB::query("SELECT sid, sup, sort, dps FROM hsk_vgallery_sort where sup=0 ORDER BY dps, sid");
			while($datarow = DB::fetch($query)){
				$datarow['sort'] = addslashes($datarow['sort']);
				$theloop[]=$datarow;
			}

			if($sid){
				$datarow = array();
				//取得二級目錄內容
				$query = DB::query("SELECT sid, sup, sort, dps FROM hsk_vgallery_sort where sup=$sid ORDER BY dps, sid");
				while($datarow = DB::fetch($query)){
					$datarow['sort'] = addslashes($datarow['sort']);
					$secloop[]=$datarow;
				}
			}
			include template("manage_style", 'Kannol', PTEM);
		}	
	
	}elseif($types == 'se'){

		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");

		$navname="編輯和刪除類別...";
		$sid = intval($_G['gp_sid']);
		$deletes = intval($_G['gp_deletes']);

		if(!submitcheck('reportsubmit')){

			$query = DB::query("SELECT sid,sup,sort FROM hsk_vgallery_sort where sid='$sid'");
			if(!$datarow = DB::fetch($query)){
				//沒找到
				showmessage('對不起，沒有找到您要編輯的類別，准備返回！');
			}

			include template("manage_style_edit", 'Kannol', PTEM);
		}else{
			//執行編輯

			$new_sort = trim($_G['gp_new_sort']);
			$newsup = intval($_G['gp_newsup']);

			if(!$new_sort){
				//沒有名稱
				showmessage('對不起，您必須輸入有效的類別名稱才能繼續，請返回重試！');
			}else{

				if($deletes){
					//刪除
					$query = DB::query("SELECT sid FROM hsk_vgallery_sort where sid=$sid");
					if(!$datarow = DB::fetch($query)){
						showmessage("對不起，沒有找到您要編輯的類別，准備返回！");
					}

					//檢查是否還有下屬分類
					$query = DB::query("SELECT sid FROM hsk_vgallery_sort where sup=$sid");
					if($datarow = DB::fetch($query)){
						showmessage("對不起，這個分類下還擁有一些下屬子分類，請先刪除它們再繼續，准備返回！");
					}else{
						//刪除
						DB::query("DELETE FROM hsk_vgallery_sort where sid='$sid'");
						mystylewrite();
						showmessage('提示：已經成功刪除此類別，現在准備轉入類別列表',PDIR.'&tion=manage&types=s');
					}

				}else{
					//看看有沒有重複的
					$query = DB::query("SELECT sid FROM hsk_vgallery_sort where sup=$newsup and sort='$new_sort' and sid!=$sid");
					if($datarow = DB::fetch($query)){
						//在同級類別裏有相同的類別名稱
						showmessage('對不起，新類別名稱于現有的同一級或同一個類別中的名稱重複，請返回重新填寫！');
					}else{
						//新增
						DB::query("UPDATE hsk_vgallery_sort SET sort='$new_sort', sup='$newsup' where sid='$sid'");
						//緩存
						mystylewrite();
						showmessage('恭喜，此類別的參數已經被成功的編輯了！',dreferer());
					}
				}
			}			
		}

	
		}elseif($types == 'f'){

		$navname="首頁TOP5管理...";
		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");


		if(submitcheck('reportsubmit')){

			//刪除
			$deletes = $_G['gp_deletes'];
			$deletesql = implode(",", $deletes);

			if($deletes)
				DB::query("DELETE FROM hsk_vgallery_top5 WHERE id IN ($deletesql)");

			
			//重新排序
			$newdps = ($_G['gp_newdps']);
			if(is_array($newdps)){
				foreach($newdps as $keys=>$value){
					DB::query("UPDATE hsk_vgallery_top5 SET dps='$value' where id='$keys'");
				}
			}
	
			
			indextop5();
			showmessage('提示：已經成功更新首頁5格視頻信息，請稍等...', dreferer());

		}else{


			$page = max(1, intval($page));
			$ppp = 15;

			//先取得頁面
			$query = DB::query("SELECT COUNT(*) FROM hsk_vgallery_top5");
			$maxpage = DB::result($query, 0);
			$countmax = $maxpage;
			$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types.$vidpage);

			$query = DB::query("SELECT m.*, n.vsubject, n.uid, n.dateline as postdate, n.purl, p.username
								FROM hsk_vgallery_top5 m
								LEFT JOIN hsk_vgallerys n ON n.id=m.vid
								LEFT JOIN ".DB::table('common_member')." p ON p.uid=n.uid
								order by m.dps, m.id desc limit ".(($page-1)*$ppp).", $ppp");
			$i=($page-1)*$ppp+1;
			while($datarow = DB::fetch($query)){
				$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 30, '..');
				if(!substr($datarow['purl'],0,7) == 'http://'){
					$thepicurl = DISCUZ_ROOT.$datarow['purl'];
					if(!file_exists("$thepicurl") || !$datarow['purl']){
						$datarow['purl'] = "./".MDIR."/noimages.gif";
					}
				}
				$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['timelong'] = checkthetime($datarow['timelong']);
				$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
				$datarow['distyle'] = $i;
				$i++;
				$datalist[] = $datarow;
			}

			include template("manage_top5", 'Kannol', PTEM);
		}

		}elseif($types == 't'){

		$navname="首頁類別管理...";
		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");

		if(!submitcheck('reportsubmit')){

			//首頁四個類別名稱 緩存
			if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_index_sortlist.hsk')){
				@require DISCUZ_ROOT.'./data/cache/vgallery_index_sortlist.hsk';
				for($i=1; $i<5; $i++){
					if($indexsidname['index_newlist_'.$i]){
						$mynell = 'isort'.$i;
						$mynall = 'isortname'.$i;
						$$mynell = substr($indexsidname['index_newlist_'.$i], strrpos($indexsidname['index_newlist_'.$i],"|")+1);
						$$mynall = substr($indexsidname['index_newlist_'.$i], 0, strrpos($indexsidname['index_newlist_'.$i],"|"));
					}
				}
			}
			include template("manage_index_sort", 'Kannol', PTEM);

		}else{
			$newsort1 = intval($_G['gp_newsort1']);
			$newsort2 = intval($_G['gp_newsort2']);
			$newsort3 = intval($_G['gp_newsort3']);
			$newsort4 = intval($_G['gp_newsort4']);

			//循環檢查是否爲二級分類
			for($i=1;$i<5;$i++){
				$mynell = 'newsort'.$i;
				if($$mynell){
					$query = DB::query("SELECT sid, sort FROM hsk_vgallery_sort where sid=".$$mynell." and sup>0");
					if(!$datarow = DB::fetch($query)){
						showmessage("對不起，檢查到您輸入的第 $i 個類別不存在或不是二級類別, 請返回重新修改...");
					}else{
						$mynall = 'cachesid_'.$i;
						$$mynall = $datarow['sort']."|".$datarow['sid'];
					}
				}
			}
			//正確，寫緩存

			DB::query("UPDATE hsk_vgallery_sort SET indexcap=0 WHERE indexcap!=0");
			for($i=1;$i<5;$i++){
				$mynell = 'newsort'.$i;
				if($$mynell){
					DB::query("UPDATE hsk_vgallery_sort SET indexcap=$i WHERE sid=".$$mynell);
				}
			}

			
			$cache_str = "\$indexsidname['index_newlist_1'] = '$cachesid_1';\n";
			$cache_str .= "\$indexsidname['index_newlist_2'] = '$cachesid_2';\n";
			$cache_str .= "\$indexsidname['index_newlist_3'] = '$cachesid_3';\n";
			$cache_str .= "\$indexsidname['index_newlist_4'] = '$cachesid_4';\n";
			writetocache("vgallery_index_sortlist", $cache_str);
			index_topstyle_all();
			showmessage('提示：已經成功更新首頁顯示的4個類別了，請稍等...', dreferer());

		}

	}elseif($types == 'i'){
		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");
		//首頁5格加入
		if(!$vid)
			showmessage('對不起，參數傳遞不正常，無法繼續，請返回重試！');
		$query = DB::query("SELECT id FROM hsk_vgallerys where id=$vid");
		if(!$datarow = DB::fetch($query)){
			showmessage("對不起，沒有找到您要進行操作的視頻，可能已被刪除，准備返回！");
		}

		//看是否已經在TOP5表裏了
		$query = DB::query("SELECT vid FROM hsk_vgallery_top5 where vid=$vid");
		if($datarow = DB::fetch($query)){
			showmessage("這個視頻已經被加入到首頁5格裏了，現在轉入到首頁5格界面進行操作...", PDIR."&tion=manage&types=f");
		}

		//加入
		DB::query("INSERT INTO hsk_vgallery_top5 (vid, uid, dateline, dps) VALUES ('$vid', '$discuz_uid', '$timestamp', 1)");
		indextop5();
		showmessage('恭喜，已經成功的把這個視頻加入到首頁5格中了！',dreferer());
	
	}else{

		if($adminid != 1 || !$groupon_3)
			showmessage("越權操作！");

	
		if(!submitcheck('reportsubmit')){
			//管理視頻
			$navname="管理視頻...";
			//獲取頁數信息

			$page = max(1, intval($page));
			$ppp = 20;

			$sid = intval($_G['gp_sid']);
			$fid = intval($_G['gp_fid']);
			if($sid){//如果查看的是二級分目錄
				foreach($styleloop as $datarow){
					if($datarow['sid'] == $sid){
						$fid = $datarow['sup'];		//得到一級目錄的ID
						$sidsname = $datarow['sort'];
					}
				}

				//先取得頁面
				$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sid='$sid' and sup=0");
				$maxpage = DB::result($query, 0);
				$countmax = $maxpage;
				$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types."&sid=".$sid);

				//寫出SQL語句，等會要用到
				$query = DB::query("SELECT m.id, m.album, m.vsubject, m.purl, t.views, t.replies as polls, m.valuate, m.timelong, m.tid, m.vprice, m.uid, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid WHERE m.sid='$sid' and m.sup=0 ORDER BY m.id DESC limit ".(($page-1)*$ppp).", $ppp");
				$fidsidpage = "&sid=".$sid;
			}elseif($fid){

				//先取得頁面
				$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys v LEFT JOIN hsk_vgallery_sort s USING(sid) where s.sup='$fid' and v.sup=0");
				$maxpage = DB::result($query, 0);
				$countmax = $maxpage;
				$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types."&fid=".$fid);

				//如果只選擇一級目錄，那麽SQL語句又不一樣
				$query = DB::query("SELECT m.id, m.album, m.vsubject, m.purl, t.views, t.replies as polls, m.valuate, m.timelong, m.tid, m.vprice, s.sid, s.sort, m.uid, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid LEFT JOIN hsk_vgallery_sort s ON s.sid=m.sid WHERE s.sup='$fid' and m.sup=0 ORDER BY m.id DESC limit ".(($page-1)*$ppp).", $ppp");
				$fidsidpage = "&fid=".$fid;
			}else{
				//先取得頁面
				$query = DB::query("SELECT COUNT(*) FROM hsk_vgallerys where sup=0");
				$maxpage = DB::result($query, 0);
				$countmax = $maxpage;
				$multipage = multi($maxpage, $ppp, $page, PDIR.'&tion=manage&types='.$types);
				//全部類別的SQL
				$query = DB::query("SELECT m.id, m.album, m.vsubject, m.dateline, m.purl, t.views, t.replies as polls, m.valuate, m.tid, m.vprice, m.timelong, m.uid, p.username FROM hsk_vgallerys m LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.tid LEFT JOIN ".DB::table('common_member')." p ON p.uid=m.uid WHERE m.sup=0 ORDER BY m.id DESC limit ".(($page-1)*$ppp).", $ppp");
				$fidsidpage = null;
			}

			foreach($styleloop as $datarow){
				if($datarow['sid'] == $fid){
					$fidsname = $datarow['sort'];
				}
			}
			while($datarow = DB::fetch($query)){
				$datarow['vsubjectc'] = cutstr($datarow['vsubject'], 22, '..');
				if(!substr($datarow['purl'],0,7) == 'http://'){
					$thepicurl = DISCUZ_ROOT.$datarow['purl'];
					if(!file_exists("$thepicurl") || !$datarow['purl']){
						$datarow['purl'] = "./".MDIR."/noimages.gif";
					}
				}
				$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
				$datarow['timelong'] = checkthetime($datarow['timelong']);
				$datarow['isalbum'] = $datarow['album'] ? 'ablist' : 'view';
				$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
				$datalist[] = $datarow;
			}
			include template("manage_list", 'Kannol', PTEM);
		}else{
			//提交後
			$deletes = $_G['gp_deletes'];
			$_G['forum']['fid'] = $thefid;
			if(empty($deletes)) showmessage("對不起，您沒有選中任何一個視頻，所以不能執行操作。", dreferer());
			$deletesql = implode(",", $deletes);

			$query = DB::query("SELECT uid, purl, tid FROM hsk_vgallerys WHERE id IN ($deletesql)");
			$delepic = 0;
			require_once libfile('function/delete');
			while($datadele = DB::fetch($query)){
				if(!substr($datadele['purl'],0,7) == 'http://'){
					$thepicurl = DISCUZ_ROOT.$datadele['purl'];
					if(file_exists("$thepicurl") && $datadele['purl']){
						$datadele['purl'] = $thepicurl;
						$pslist[] = $datadele;
						$delepic = 1;
					}
				}
				$delearray[$datadele['tid']] = '1';
			}
			DB::query("DELETE FROM hsk_vgallerys WHERE id IN ($deletesql)");
			$tids = array_keys($delearray);
			deletethread($tids, true, true);
			updateforumcount($thefid);

			//刪除文件....
			if($delepic){
				foreach($pslist as $id) {
					@unlink($id['purl']);
				}
			}
			index_topstyle_all();
			showmessage('提示：已經成功的刪除了您選擇的視頻！', dreferer());

		}
	}



/*


■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■管理頁面■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■



*/

}elseif($tion == 'selectstyle'){
	//選擇發布時的類別，手工發布還是自動采集

	$navname = $navtitle  = "選擇發布視頻的模式...";
	$oraudit = $adminid==1 || $groupon_3 || !$isaudit ? 1 : 0;
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能發布視頻.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
	//是否可以給普通會員發布
	if(!$groupon_2 && $adminid!=1)
		showmessage("對不起, 目前系統不允許您所在的組發布視頻！<br><br>--- [<a href='member.php?mod=logging&action=logout'>換個身份登錄</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");


	if(!submitcheck('reportsubmit')){
		include template("gallery_selectstyle", "Kannol", PTEM);
	}else{
		$gettypes = $vid ? 3 : 1;
		$theurl = dhtmlspecialchars($_G['gp_vurl']);
		$parseLink = parse_url($theurl);
		//print_r($parseLink);

		if(preg_match("/(youku.com|youtube.com|5show.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com|qiyi.com|56.com|joy.cn|m1905.com|6.cn|letv.com|imgo.tv|ifeng.com|gvodzi.net|)$/i", $parseLink['host'], $hosts)) {

			$flashimg = getflashimg($theurl, $hosts[1]);

			$arr['host'] = $hosts[1];
			$arr['flashvar'] = $flashimg[2];
			$arr['image'] = $flashimg[0];
			$arr['subject'] = $flashimg[1];
			$arr['message'] = $flashimg[3];
		}
		$query = DB::query("SELECT vsubject, id FROM hsk_vgallerys WHERE album=1 and uid='$discuz_uid' ORDER BY id");
		while($datarow = DB::fetch($query)){
			$dataloop2[] = $datarow;
		}
		$onestyle = null;
		foreach($styleloop as $datarow){
			if(!$onestyle && !$datarow['sup']){
				$onestyle = $datarow['sid'];
			}
		}
		include template("gallery_gethtml", "Kannol", PTEM);
	}

}elseif($tion == 'autoget'){
	//選擇發布時的類別，手工發布還是自動采集
	if(!$discuz_uid)
		showmessage("對不起, 必須登錄後才能發布視頻.  請您選擇以下操作...<br><br>--- [<a href='member.php?mod=logging&action=login'>登錄論壇</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
	//是否可以給普通會員發布
	if(!$groupon_2 && $adminid!=1)
		showmessage("對不起, 目前系統不允許您所在的組發布視頻！<br><br>--- [<a href='member.php?mod=logging&action=logout'>換個身份登錄</a>]&nbsp;&nbsp;&nbsp; --- 或返回 ---");
	include template("gallery_autoget", "Kannol", PTEM);


}else{//■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■首頁界面段代碼■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

	$navname = $navtitle  = "視頻展廳首頁...";
	//先加載首頁特別顯示的5個視頻 緩存信息
	if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_index.hsk')){
		@require DISCUZ_ROOT.'./data/cache/vgallery_index.hsk';
	}else{
		//如果沒有文件，那麽寫入
		$cache_str = "\$indexvid = \"0\";\n";
		writetocache("vgallery_index", $cache_str);
	}

	//首頁四個類別名稱 緩存
	if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_index_sortlist.hsk')){
		@require DISCUZ_ROOT.'./data/cache/vgallery_index_sortlist.hsk';
		for($i=1; $i<5; $i++){
			if($indexsidname['index_newlist_'.$i]){
				$indexsidname['index_newlist_'.$i.'_name'] = substr($indexsidname['index_newlist_'.$i], 0, strrpos($indexsidname['index_newlist_'.$i],"|"));
				$indexsidname['index_newlist_'.$i.'_sort'] = substr($indexsidname['index_newlist_'.$i], strrpos($indexsidname['index_newlist_'.$i],"|")+1);
			}
		}
	}else{
		$indexsidname['index_newlist_1'] = $indexsidname['index_newlist_2'] = $indexsidname['index_newlist_3'] = $indexsidname['index_newlist_4'] = '';
	}

	//再加載第四個類別的前四個視頻 緩存信息
	for($i=1; $i<5; $i++){
		if(file_exists(DISCUZ_ROOT.'./data/cache/vgallery_index_newlist_'.$i.'.hsk')){
			@require DISCUZ_ROOT.'./data/cache/vgallery_index_newlist_'.$i.'.hsk';
		}else{
			//如果沒有文件，那麽寫入
			//$cache_str .= "\t0\t=>\t array('id'=>0,\t'vsubject'=>'0',\t'uid'=>'0',\t'purl'=>'0',\t'username'=>'0'),\n";
			$cache_str = "\$index_newlist_".$i." = 0;\n";
			writetocache("vgallery_index_newlist_".$i, $cache_str);
		}
	}

	//最新視頻
	$query = DB::query("SELECT id, vsubject, purl FROM hsk_vgallerys where album=0 and audit=1 ORDER BY id desc limit 4");
	while($topdata = DB::fetch($query)){
		$topdata['vsubjectc'] = cutstr(addslashes($topdata['vsubject']), 18, '..');
		if(!substr($topdata['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$topdata['purl'];
			if(!file_exists("$thepicurl") || !$topdata['purl']){
				$topdata['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$index_newtop[] = $topdata;
	}




	include template("gallery_index", "Kannol", PTEM);

}




function getflashimg($link, $host) {
	$return='';
	if('tudou.com' == $host){
		$content = file_get_contents($link);
		preg_match_all("/,thumbnail[\w\W]=[\w\W]pic[\w\W]=[\w\W]\'(.*?)\'/i",$content,$img);
		preg_match_all("/<span id=\"vcate_title\">(.*?)<\/span>/i",$content,$title);
		$title = $title[1][0];
		preg_match_all("/view\/([\w\-]+)\/?/i", $link, $matches);
		if(!empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://www.tudou.com/v/".$geturl."/v.swf";
		}
	}elseif('sina.com.cn' == $host){
		$content = file_get_contents($link);
		preg_match_all("/pic:[\w\W]\'(.*?)\',/i",$content,$img);
		preg_match_all("/title:\'(.*?)\',/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);
		preg_match_all("/\/(\d+)-(\d+)\.html/", $link, $matches);
		if(!empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=".$geturl."/s.swf";
		}
	}elseif('youku.com' == $host){
		$content = file_get_contents($link);
		preg_match_all("/\+0800\|(.*?)\|\">/i",$content,$img);
		//keywords" content="
		preg_match_all("/keywords\" content=\"(.*?)\">/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);
		preg_match_all("/id\_(\w+)[=.]/", $link, $matches);
		if(!empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://player.youku.com/player.php/sid/".$geturl."/v.swf";
		}
	}elseif('gvodzi.net' == $host){
		$content = file_get_contents($link);
		preg_match_all("/td valign=\"middle\"><img src=\"(.*?)\" width=/i",$content,$img);
		//<td width="434">镖行天下前傳之漠上風雲</td><td valign="middle">
		preg_match_all("/width=\"434\">(.*?)<\/td>/i",$content,$title);
		$title = $title[1][0];
		preg_match_all("/<td>(.*?)<br \/><\/td>/i",$content,$message);
		$message = $message[1][0];
		preg_match_all("/gvod:\/\/(.*?)<\/a>/i", $content, $matches);
		if(!empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "gvod://".$geturl;
		}
	}elseif('ku6.com' == $host){
		$content = file_get_contents($link);
		preg_match_all("/<span class=\"s_pic\">(.*?)<\/span>/i",$content,$img);
		//title" content="美國媒體發現傑克遜寶藏 十億古董被變賣"/>
		preg_match_all("/title\" content=\"(.*?)\"/i",$content,$title);
		$title = $title[1][0];
		preg_match_all("/\/([\w\-]+)\.html/", $link, $matches);
		if(1 > preg_match("/\/index_([\w\-]+)\.html/", $link) && !empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://player.ku6.com/refer/".$geturl."/v.swf";
		}
	}elseif('sohu.com' == $host){
		$content = file_get_contents($link);
		preg_match_all("/var cover=\"(.*?)\";/i",$content,$img);
		//<em id='specialNum'>明星玩酷團：香港電影出鏡率最高的建築</em>		var nid = "312372978";
		preg_match_all("/<em id=\'specialNum\'>(.*?)<\/em>/i",$content,$title);
		$title = $title[1][0];
		preg_match_all("/var vid=\"(\d+)\"/", $content, $matches);
		if(1 > preg_match("/\/index_([\w\-]+)\.html/", $link) && !empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://share.vrs.sohu.com/".$geturl."/v.swf";
		}
	}elseif('joy.cn' == $host){
		$content = file_get_contents($link);
		preg_match_all("/<span class=\"s_pic\">(.*?)<\/span>/i",$content,$img);
		preg_match_all("/title:\"(.*?)\",/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
		preg_match_all("/(\d+)\.htm/", $link, $matches);
		if(!empty($matches[1][0])) {
			$geturl = $matches[1][0];
			$geturl = "http://client.joy.cn/flvplayer/".$geturl."_1_0_2.swf";
		}
	}elseif('6.cn' == $host){
		$content = file_get_contents($link);
		//<div class="summary"><img src="http://i2.6.cn/netbargame/b3/a0/k337491310032413.jpg" width="0" height="0" /></div>		//<h1 class="vt">珠海美女主管曝出“豔照門”<img
		preg_match_all("/<div class=\"summary\"><img src=\"(.*?)\" width=\"0\"/i",$content,$img);
		preg_match_all("/keywords\" content=\"(.*?)\"/i",$content,$title);
		//pageMessage.evid = 'ifE8fBvTssPpvpwY_YZhVg'		http://6.cn/p/ifE8fBvTssPpvpwY_YZhVg.swf
		preg_match_all("/pageMessage.evid = \'(.*?)\'/i",$content, $matches);
		$geturl = "http://6.cn/player.swf?vid=".$matches[1][0];
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
	}elseif('m1905.com' == $host){
		$content = file_get_contents($link);
		//<img alt="" src="	//<span class="pl10 left font14 font_w">[電影網]娛論大調查：哪個角色是理想戀愛對象？</span>
		preg_match_all("/<img alt=\"\" src=\"(.*?)[\w\W]\/></i",$content,$img);
		preg_match_all("/font_w\">(.*?)<\/span>/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
		$title = str_replace("[電影網]", '', $title);
		preg_match_all("/\/(\d+)\.shtml/", $link, $matches);
		$geturl = "http://www.m1905.com/video/t/".$matches[1][0]."/v.swf";
	}elseif('56.com' == $host){
		//http://www.56.com/u94/v_NjE3OTg2MTE.html
		preg_match_all("/\/v_(.*?)\.html/", $link, $matches);
		$geturl = "http://player.56.com/cpm_".$matches[1][0].".swf";
	}elseif('letv.com' == $host){
		$content = file_get_contents($link);
		//encodeURI('http://img1.c3.letv.com/mms/thumb/2010/07/21/e42c356d682b493a7db62027ad8d6b38/e42c356d682b493a7db62027ad8d6b38_2.jpg');title:"DSG變速器的“死亡閃爍”",//
		preg_match_all("/encodeURI\(\'(.*?)\'\)/i",$content,$img);
		preg_match_all("/title:\"(.*?)\",/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
		preg_match_all("/vid:(.*?),\/\//i",$content,$matches);
		$geturl = "http://www.letv.com/player/x".$matches[1][0].".swf";
	}elseif('ifeng.com' == $host){
		$content = file_get_contents($link);
		//"id":"d418f9be-3e80-4c85-9218-a65b9ba89ded","wapstatus":"1","name":"宋曉軍：日用F15攔截中國軍機侵犯中國主權","duration":"91","url":"http://v.ifeng.com/news/opinion/201107/d418f9be-3e80-4c85-9218-a65b9ba89ded.shtml","img":"http://img.ifeng.com/itvimg/2011/07/09/c7afeff8-a1f3-4b90-b796-21feccd3cd4e140.jpg"};
		preg_match_all("/\"img\":\"(.*?)\"/i",$content,$img);
		preg_match_all("/\"name\":\"(.*?)\"/i",$content,$title);
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
		preg_match_all("/\/([\w\-]+)\.shtml/", $link, $matches);
		$geturl = "http://v.ifeng.com/include/exterior.swf?guid=".$matches[1][0]."&AutoPlay=false";
	}elseif('imgo.tv' == $host){
		$content = file_get_contents($link);
		//ctid='1175' ccid='112174' cfid='122708' cym='201107' cfstid='1' cpname='2011快樂女聲總決賽發布會' cpic='http://1img.imgo.tv/preview/jinying/prev/jmh-0708.jpg'
		//http://www.imgo.tv/player/ref_imgo_player.swf?tid=1175&cid=112174&fid=122708
		preg_match_all("/cpic=\'(.*?)\'/i",$content,$img);
		preg_match_all("/cpname=\'(.*?)\'/i",$content,$title);
		preg_match_all("/ctid=\'(.*?)\'/i",$content,$tidstr);
		$tid=$tidstr[1][0];
		preg_match_all("/ccid=\'(.*?)\'/i",$content,$tidstr);
		$cid=$tidstr[1][0];
		preg_match_all("/cfid=\'(.*?)\'/i",$content,$tidstr);
		$fid=$tidstr[1][0];
		$title = iconv('UTF-8', 'UTF-8', $title[1][0]);	//得到視頻標題
		$geturl = "http://www.imgo.tv/player/ref_imgo_player.swf?tid=".$tid."&cid=".$cid."&fid=".$fid;
	}elseif('youtube.com' == $host) {
		preg_match_all("/v\=([\w\-]+)/", $link, $matches);
		$geturl = $matches[1][0];
	}elseif('qiyi.com' == $host){
		$content = file_get_contents($link);
		preg_match_all("/title[\w\W]:[\w\W]\"(.*?)\",/i",$content,$str2);
		preg_match_all("/videoId[\w\W]:[\w\W]\"(.*?)\",/i",$content,$str3);
		preg_match_all("/albumId[\w\W]:[\w\W]\"(.*?)\",/i",$content,$str4);
		preg_match_all("/tvId[\w\W]:[\w\W]\"(.*?)\",/i",$content,$str5);
		$title = iconv('UTF-8', 'UTF-8', $str2[1][0]);	//得到視頻標題

		$str1 = parse_url($link);
		preg_match_all("/\/(.*?)\//i",$str1['path'],$str);

		$vdate = $str[1][0]; //得到日期
		$vcode = $str3[1][0];
		$albid = $str4[1][0];  //所在專輯ID
		$vinid = $str5[1][0]; //視頻ID

		//生成圖片和視頻URL
		$img[1][0] = "http://www.qiyipic.com/thumb/".$vdate."/a".$albid."_160_90.jpg";
		$geturl = "http://player.video.qiyi.com/".$vcode;

	}
	$str[0] = $img[1][0];
	$str[1] = $title;
	$str[2] = $geturl;
	$str[3] = $message;
	if($str){     
		return  $str;
	}
}


function checkthetime($val){
	if(!$val){
		return "00'00";
	}else{
		$a = floor($val/60);
		if(strlen($a)<=2){
			$a = substr("00", 0, 2-strlen($a)).$a;
		}
		$b = $val%60;
		$c = $a."'".substr("00", 0, 2-strlen($b)).$b;
		return $c;
	}
}

function mydiscuzcode($str) {
	//@include DISCUZ_ROOT.'./forumdata/cache/cache_bbcodes.php';
	$str = preg_replace('/\s*\{([0-9])\}\s*/is', '<img src="'.MDIR.'/img\\1.gif" border="0" align="absmiddle">', $str);
	$str = preg_replace('/\s*\[b\](.+?)\[\/b\]\s*/is', '<font class="xs1"><span class="xg1">\\1</span></B></font><br>', $str);
	//$str = preg_replace('/\s*\[br\]\s*/is', '<br>', $str);
	$str = preg_replace('/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is', tpl_quote(), $str);
	return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $str));
}

function tpl_quote() {
$return = '<table border="0" width="98%" cellspacing="0" cellpadding="0" align="center"><tr><td height="8"></td></tr><tr><td class="alt"><div class="quote"><blockquote>\\1</blockquote></div></td></tr><tr><td height="8"></td></tr></table>';
return $return; 
}

function mystylewrite(){
	global $_G;
	$query = DB::query("SELECT sid, sup, sort FROM hsk_vgallery_sort ORDER BY sup, dps, sid");
	$i=0;
	while($datarow = DB::fetch($query)){
		$datarow['sort'] = addslashes($datarow['sort']);
		$cache_str .= "\t".$i."\t=>\t array('sid'=>".$datarow['sid'].",\t'sort'=>'".$datarow['sort']."',\t'sup'=>'".$datarow['sup']."'),\n";
		$i++;
	}
	$cache_str = "\$styleloop = array(\n".$cache_str.");";
	writetocache("vgallery_style", $cache_str);
}

function writetocache($script, $cachedata = '') {
	$dir = DISCUZ_ROOT.'./data/cache/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(@$fp = fopen("$dir$script.hsk", 'w')) {
		fwrite($fp, "<?php\n$cachedata?>");
		fclose($fp);
	} else {
		dexit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}
}


function index_topstyle_4($vstyle){

	$cache_str = '';
	$query = DB::query("SELECT indexcap FROM hsk_vgallery_sort WHERE sid='$vstyle'");
	$indexdata = DB::fetch($query);
	$indexcaps = $indexdata['indexcap'];
	if($indexcaps){
		//重新創建緩存
		$newvodlist = array();
		$i=0;
		$query = DB::query("SELECT v.id, v.vsubject, v.uid, m.username, v.purl FROM hsk_vgallerys v LEFT JOIN ".DB::table('common_member')." m USING(uid) where v.album=0 and v.audit=1 and v.sid='$vstyle' ORDER BY v.id desc limit 4");
		while($topdata = DB::fetch($query)){
			$topdata['vsubject'] = addslashes($topdata['vsubject']);
			$topdata['vsubjectc'] = cutstr($topdata['vsubject'], 20, '..');
			if(!substr($topdata['purl'],0,7) == 'http://'){
				$thepicurl = DISCUZ_ROOT.$topdata['purl'];
				if(!file_exists("$thepicurl") || !$topdata['purl']){
					$topdata['purl'] = "./".MDIR."/novod.gif";
				}
			}
			$topdata['purl'] = addslashes($topdata['purl']);
			$cache_str .= "\t".$i."\t=>\t array('id'=>".$topdata['id'].",\t'vsubjectc'=>'".$topdata['vsubjectc']."',\t'vsubject'=>'".$topdata['vsubject']."',\t'uid'=>'".$topdata['uid']."',\t'username'=>'".$topdata['username']."',\t'purl'=>'".$topdata['purl']."'),\n";
			$i++;
		}
		$cache_str = "\$index_newlist_$indexcaps = array(\n".$cache_str.");";
		writetocache("vgallery_index_newlist_".$indexcaps, $cache_str);
	}
}


function index_topstyle_all(){
	$cache_str = '';
	$query = DB::query("SELECT sid FROM hsk_vgallery_sort WHERE indexcap>0 order by sid limit 4");
	while($indexdata = DB::fetch($query)){
		index_topstyle_4($indexdata['sid']);
	}
}


function indextop5(){
	global $_G;

	
	$query = DB::query("SELECT m.*, n.vsubject, n.uid, n.dateline as postdate, n.purl, p.username
						FROM hsk_vgallery_top5 m
						LEFT JOIN hsk_vgallerys n ON n.id=m.vid
						LEFT JOIN ".DB::table('common_member')." p ON p.uid=n.uid
						order by m.dps, m.id desc limit 5");
	$i=0;
	while($datarow = DB::fetch($query)){
		$datarow['vsubjectc'] = cutstr(addslashes($datarow['vsubject']), 26, '..');
		if(!substr($datarow['purl'],0,7) == 'http://'){
			$thepicurl = DISCUZ_ROOT.$datarow['purl'];
			if(!file_exists("$thepicurl") || !$datarow['purl']){
				$datarow['purl'] = "./".MDIR."/noimages.gif";
			}
		}
		$datarow['dateline1'] = gmdate("Y-m-d", $datarow['dateline'] + 3600 * $timeoffset);
		$datarow['dateline2'] = gmdate("H:i:s", $datarow['dateline'] + 3600 * $timeoffset);
		$datarow['timelong'] = checkthetime($datarow['timelong']);
		$datarow['valuate'] = sprintf("%01.1f", $datarow['valuate']/100);
		$datarow['distyle'] = $i;
		$cache_str .= "\t".$i."\t=>\t array('id'=>".$datarow['vid'].",\t'vsubject'=>'".$datarow['vsubjectc']."',\t'uid'=>'".$datarow['uid']."',\t'username'=>'".$datarow['username']."',\t'purl'=>'".$datarow['purl']."'),\n";
		$i++;
	}
	$cache_str = "\$indexvod = array(\n".$cache_str.");";
	writetocache("vgallery_index", $cache_str);
}


function grabimage($url, $filename) { 

	if(!$url || !$filename)
		return false;
	
	$src_im = imagecreatefromjpeg($url);   
	$srcW = ImageSX($src_im);                                                       //獲得圖像的寬   
	$srcH = ImageSY($src_im);                                                       //獲得圖像的高   

	$dst_im = ImageCreateTrueColor($srcW,$srcH);                    //創建新的圖像對象   

	imagecopy($dst_im, $src_im, 0, 0, 0, 0, $srcW, $srcH);   
	imagejpeg($dst_im, $filename);                                               //創建縮略圖文件   

	return $filename;
}


function parsesmiles_myself($message, $maxsmilescode) {
	$mycachesmilies = cache_smilies();
	if(!empty($mycachesmilies) && is_array($mycachesmilies)) {
		foreach($mycachesmilies['replacearray'] AS $key => $smiley) {
			$mycachesmilies['replacearray'][$key] = '<img src="'.STATICURL.'image/smiley/'.$mycachesmilies['directory'][$key].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
		}
	}
	$message = preg_replace($mycachesmilies['searcharray'], $mycachesmilies['replacearray'], $message, $maxsmilescode);
	return $message;
}


function cache_smilies() {
	$data = array();
	$query = DB::query("SELECT s.id, s.code, s.url, t.typeid, t.directory FROM ".DB::table('common_smiley')." s
		LEFT JOIN ".DB::table('forum_imagetype')." t ON t.typeid=s.typeid WHERE s.type='smiley' AND s.code<>'' AND t.available='1' ORDER BY LENGTH(s.code) DESC");

	$data = array('searcharray' => array(), 'replacearray' => array(), 'typearray' => array());
	while($smiley = DB::fetch($query)) {
		$data['searcharray'][$smiley['id']] = '/'.preg_quote(dhtmlspecialchars($smiley['code']), '/').'/';
		$data['replacearray'][$smiley['id']] = $smiley['url'];
		$data['typearray'][$smiley['id']] = $smiley['typeid'];
		$data['directory'][$smiley['id']] = $smiley['directory'];
	}

	$return=$data;
	return $return;
}


function tpl_codedisp_myself($code) {
$randomid = 'code_'.random(3);
$return = <<<EOF
<div class="blockcode"><div id="{$randomid}"><ol><li>{$code}</ol></div><em onclick="copycode($('{$randomid}'));">複制代碼</em></div>
EOF;
return $return;
}

function tpl_quote_myself(){
$return = <<<EOF
<div class="quote"><blockquote>\\1</blockquote></div>
EOF;
return $return;
}

function codedisp($code) {
	global $_G;
	$_G['forum_discuzcode']['pcodecount']++;
	$code = dhtmlspecialchars(str_replace('\\"', '"', preg_replace("/^[\n\r]*(.+?)[\n\r]*$/is", "\\1", $code)));
	$code = str_replace("\n", "<li>", $code);
	$code = tpl_codedisp_myself($code);
	return $code;
}

function discuzcode($message, $maxsiliescode) {

	$msglower = strtolower($message);
	$message = dhtmlspecialchars($message);
	$message = parsesmiles_myself($message, $maxsiliescode);

	if(strpos($msglower, 'ed2k://') !== FALSE) {
		$message = preg_replace("/ed2k:\/\/(.+?)\//e", "parseed2k('\\1')", $message);
	}

	if(strpos($msglower, '[/url]') !== FALSE) {
		$message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5', '\\2')", $message);
	}

	if(strpos($msglower, '[/email]') !== FALSE) {
		$message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
	}

	$nest = 0;
	while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
		$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
		if(++$nest > 4) break;
	}

	$message = str_replace(array(
		'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
		'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
		'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
		), array(
		'</font>', '</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
		'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
		'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
		), preg_replace(array(
		"/\[color=([#\w]+?)\]/i",
		"/\[color=(rgb\([\d\s,]+?\))\]/i",
		"/\[backcolor=([#\w]+?)\]/i",
		"/\[backcolor=(rgb\([\d\s,]+?\))\]/i",
		"/\[size=(\d{1,2}?)\]/i",
		"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
		"/\[font=([^\[\<]+?)\]/i",
		"/\[align=(left|center|right)\]/i",
		"/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
		"/\[float=left\]/i",
		"/\[float=right\]/i"

		), array(
		"<font color=\"\\1\">",
		"<font style=\"color:\\1\">",
		"<font style=\"background-color:\\1\">",
		"<font style=\"background-color:\\1\">",
		"<font size=\"\\1\">",
		"<font style=\"font-size:\\1\">",
		"<font face=\"\\1\">",
		"<p align=\"\\1\">",
		"<p style=\"line-height:\\1px;text-indent:\\2em;text-align:\\3\">",
		"<span style=\"float:left;margin-right:5px\">",
		"<span style=\"float:right;margin-left:5px\">"
		), $message));

	if(strpos($msglower, '[/quote]') !== FALSE) {
		$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote_myself(), $message);
	}

	if(strpos($msglower, '[/img]') !== FALSE) {
		$message = preg_replace(array(
			"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
			"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
		), array(
			"bbcodeurl('\\1', '<img src=\"{url}\" alt=\"\" />')",
			"parseimg('\\1', '\\2', '\\3', ".intval($lazyload).")"
		), $message);
	}


	unset($msglower);

	if($jammer) {
		$message = preg_replace("/\r\n|\n|\r/e", "jammer()", $message);
	}
	$message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/ies", "codedisp('\\1')", $message);
	$message = nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));

	return $message;
}


function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
			$url = 'http://'.$url;
		}
		return str_replace(array('submit', 'member.php?mod=logging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
	} else {
		return '&nbsp;'.$url;
	}
}

function parseurl($url, $text, $scheme) {
	global $_G;
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		$url = !$scheme ? $_G['siteurl'].$url : $url;
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}

function parseflash($w, $h, $url) {
	$w = !$w ? 550 : $w;
	$h = !$h ? 400 : $h;
	preg_match("/((https?){1}:\/\/|www\.)[^\[\"']+/i", $url, $matches);
	$url = $matches[0];
	$randomid = 'swf_'.random(3);
	if(fileext($url) != 'flv') {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$url.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	} else {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/flvplayer.swf\', \'flashvars\', \'file='.rawurlencode($url).'\', \'quality\', \'high\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	}
}

function parseed2k($url) {
	global $_G;
	list(,$type, $name, $size,) = explode('|', $url);
	$url = 'ed2k://'.$url.'/';
	$name = addslashes($name);
	if($type == 'file') {
		$ed2kid = 'ed2k_'.random(3);
		return '<a id="'.$ed2kid.'" href="'.$url.'" target="_blank"></a><script language="javascript">$(\''.$ed2kid.'\').innerHTML=htmlspecialchars(unescape(decodeURIComponent(\''.$name.'\')))+\' ('.sizecount($size).')\';</script>';
	} else {
		return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
	}
}


function parseemail($email, $text) {
	$text = str_replace('\"', '"', $text);
	if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
		$email = trim($matches[0]);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	} else {
		return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
	}
}

function parsetable($width, $bgcolor, $message) {
	if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
		$rows = explode("\n", $message);
		$s = '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>');
		foreach($rows as $row) {
			$s .= '<tr><td>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</td><td>', "\n"), $row).'</td></tr>';
		}
		$s .= '</table>';
		return $s;
	} else {
		if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
		}
		if(substr($width, -1) == '%') {
			$width = substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%';
		} else {
			$width = intval($width);
			$width = $width ? ($width <= 560 ? $width.'px' : '98%') : '';
		}
		return '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>').
			str_replace('\\"', '"', preg_replace(array(
					"/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/ie",
					"/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/ie",
					"/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[\/tr\]\s*/i"
				), array(
					"parsetrtd('\\1', '0', '0', '\\2')",
					"parsetrtd('td', '0', '0', '\\1')",
					"parsetrtd('\\1', '\\2', '\\3', '\\4')",
					"parsetrtd('td', '\\1', '\\2', '\\3')",
					'</td></tr>'
				), $message)
			).'</table>';
	}
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
}



function loadmsg($msg, $title, $user, $vid, $url, $dateline, $price, $albid){
	
	//取得類型
	$thestyle = strtolower(substr(strrchr($url,"."),1));
	if('flv' == $thestyle){
		$urlcode = '[media=flv,640,480]'.$url.'[/media]';
	}elseif('qvod://' == strtolower(substr($url, 0, 7))){
		$urlcode = '[qvod=640,480]'.$url.'[/qvod]';
	}elseif('gvod://' == strtolower(substr($url, 0, 7))){
		$urlcode = '[gvod=640,480]'.$url.'[/gvod]';
	}elseif('mms://' == strtolower(substr($url, 0, 6))){
		$urlcode = '[media=mms,640,480]'.$url.'[/media]';
	}elseif('rtsp://' == strtolower(substr($url, 0, 7))){
		$urlcode = '[media=rtsp,640,480]'.$url.'[/media]';
	}elseif(in_array($thestyle, array('wmv','avi','wma','mp4','mp3','rm','rmvb','ram','ra','mov'))){
		$urlcode = '[media='.$thestyle.',640,480]'.$url.'[/media]';
	}else{
		$urlcode = '[media=swf,640,480]'.$url.'[/media]';
	}

	$ablist = !$albid ? null : '

所屬專輯：[url=plugin.php?id=vgallery:vgallery&tion=ablist&vid='.$albid.']打開專輯[/url]';

	$price_display = $price ? '[free]
[table=85%,black]
[tr][td]
[align=center][font=微軟雅黑][size=5][color=lemonchiffon]這個視頻需要付費才可以觀看！[/color][/size][/font][/align]
[/td][/tr]
[/table]

[/free]

' : null;

	$message = $price_display.'
[table=98%,darkred]
[tr][td][color=lime][size=4][font=微軟雅黑][b][color=#ffffff]視頻：'.$title.'[/color][/b][/font][/size][/color][/td][/tr]
[/table]
視頻播主：'.$user.'
發布時間：'.$dateline.$ablist.'


[table=98%,darkred]
[tr][td][color=#ffffff][font=微軟雅黑][size=4][b]觀看視頻：[/b][/size][/font][/color][/td][/tr]
[/table]
[font=微軟雅黑][b][size=3]您還可以：[/size][url=plugin.php?id=vgallery:vgallery&tion=view&vid='.$vid.'][size=3]到展廳內觀看[/size][/b][/url][/font][font=微軟雅黑][b][size=3]，體現更多精彩功能！[/size][/b][/font]
'.$urlcode.'
';

$message .= $msg ? '

[table=98%,darkred]
[tr][td][color=#ffffff][font=微軟雅黑][size=4][b]視頻簡介[/b]：[/size][/font][/color][/td][/tr]
[/table]
'.$msg.'
' : null;

	return $message;
}
?>