<?php

function multipage($num, $perpage, $curpage, $mpurl) {
	global $realpages, $lang;
	$realpages = 1;
	if($num > $perpage) {
		$realpages = @ceil($num / $perpage);
		if($realpages < 10) {
			$from = 1;
			$to = $realpages;
		} else {
			$from = $curpage - 5;
			$to = $from + 9;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < 10) {
					$to = 10;
				}
			} elseif($to > $realpages) {
				$from = $realpages - 9;
				$to = $realpages;
			}
		}
		$multipage = '<a href="'.$mpurl.'&page=1">'.$lang['list_index_page'].'</a> |&nbsp;&nbsp;';
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<strong><a href="'.$mpurl.'&page='.$i.'" class="f_s">'.$i.'</a></strong>' : '<a href="'.$mpurl.'&page='.$i.'" class="f_a">'.$i.'</a>';
		}
		$multipage .= ' | <a href="'.$mpurl.'&page='.$realpages.'">'.$lang['list_end_page'].'&nbsp;<input type="hidden" id="pageindex1" value="'.$realpages.'"><input type="text" name="custompage" size="3" class="storelistinput" id="pageindex2" onkeydown="if(event.keyCode==13) {window.location=\''.$mpurl.'&page=\'+this.value; doane(event);}"> '.$lang['list_page'].' <button class="storelistbutton"  onclick="GoPage()">GO</button>&nbsp;&nbsp;&nbsp;&nbsp;'.$curpage.'/'.$realpages.$lang['list_page_gong'].$num.$lang['list_pian'];
		$multipage = '<div class="storelistbottom">'.$multipage.'</div>';
	}
	return $multipage;
}

function pdupdateclick($novelid, $lastvisit){
	$now = time(); //当前时间
	$visitupdate = "lastvisit=$now,allvisit=allvisit+1";
	if(date("d",$lastvisit)==date("d",$now)){//同一天
		$visitupdate = $visitupdate.",dayvisit = dayvisit+1";
	}else{
		$visitupdate = $visitupdate.",dayvisit = 1";
	}
	if(date("W",$lastvisit)==date("W",$now)){//同一周
		$visitupdate = $visitupdate.",weekvisit = weekvisit+1";
	}else{
		$visitupdate = $visitupdate.",weekvisit = 1";
	}
	if(date("m",$lastvisit)==date("m",$now)){//同一月
		$visitupdate = $visitupdate.",monthvisit = monthvisit+1";
	}else{
		$visitupdate = $visitupdate.",monthvisit = 1";
	}
	DB::query("UPDATE LOW_PRIORITY ".DB::table('pdnovel_view')." SET $visitupdate WHERE novelid=$novelid", 'UNBUFFERED');
}

function mysql2html($message){
	$message = str_replace(' ', '&nbsp;', $message);
	$message = nl2br($message);
	return $message;
}

function get_initial($str){
	$asc=ord(substr($str,0,1));
	if ($asc<160) {
		if ($asc>=48 && $asc<=57){
			return '1';
		}elseif ($asc>=65 && $asc<=90){
			return chr($asc);
		}elseif ($asc>=97 && $asc<=122){
			return chr($asc-32);
		}else{
			return '1';
		}
	}else {
		$asc=$asc*1000+ord(substr($str,1,1));
		if ($asc>=176161 && $asc<176197){
			return 'A';
		}elseif ($asc>=176197 && $asc<178193){
			return 'B';
		}elseif ($asc>=178193 && $asc<180238){
			return 'C';
		}elseif ($asc>=180238 && $asc<182234){
			return 'D';
		}elseif ($asc>=182234 && $asc<183162){
			return 'E';
		}elseif ($asc>=183162 && $asc<184193){
			return 'F';
		}elseif ($asc>=184193 && $asc<185254){
			return 'G';
		}elseif ($asc>=185254 && $asc<187247){
			return 'H';
		}elseif ($asc>=187247 && $asc<191166){
			return 'J';
		}elseif ($asc>=191166 && $asc<192172){
			return 'K';
		}elseif ($asc>=192172 && $asc<194232){
			return 'L';
		}elseif ($asc>=194232 && $asc<196195){
			return 'M'; 
		}elseif ($asc>=196195 && $asc<197182){
			return 'N';
		}elseif ($asc>=197182 && $asc<197190){
			return 'O';
		}elseif ($asc>=197190 && $asc<198218){
			return 'P';
		}elseif ($asc>=198218 && $asc<200187){
			return 'Q';
		}elseif ($asc>=200187 && $asc<200246){
			return 'R';
		}elseif ($asc>=200246 && $asc<203250){
			return 'S';
		}elseif ($asc>=203250 && $asc<205218){
			return 'T';
		}elseif ($asc>=205218 && $asc<206244){
			return 'W';
		}elseif ($asc>=206244 && $asc<209185){
			return 'X';
		}elseif ($asc>=209185 && $asc<212209){
			return 'Y';
		}elseif ($asc>=212209){
			return 'Z';
		}else{
			return '1';
		}
	}
}

function getstr($string, $length, $in_slashes=0, $out_slashes=0, $bbcode=0, $html=0) {
	global $_G;

	$string = trim($string);
	if($in_slashes) {
		$string = dstripslashes($string);
	}
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = dhtmlspecialchars($string);
	}

	if($length) {
		$string = cutstr($string, $length);
	}

	if($bbcode) {
		require_once DISCUZ_ROOT.'./source/class/class_bbcode.php';
		$bb = & bbcode::instance();
		$string = $bb->bbcode2html($string, $bbcode);
	}
	if($out_slashes) {
		$string = daddslashes($string);
	}
	return trim($string);
}
?>