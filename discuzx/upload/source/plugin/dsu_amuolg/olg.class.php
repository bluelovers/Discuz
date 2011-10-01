<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_dsu_amuolg {
	function index_middle_output(){
		global $_G,$whosonline,$onlinenum,$onlineinfo,$guestcount,$membercount;
		if($_G['cache']['onlinelist']['legend']){
			$hbset=$_G['cache']['plugin']['dsu_amuolg']['hbset'];
			$wrts=$_G['cache']['plugin']['dsu_amuolg']['wrts'];
			$xnyk=$_G['cache']['plugin']['dsu_amuolg']['xnyk'];
			$jsgs=$_G['cache']['plugin']['dsu_amuolg']['jsgs'];
			preg_match_all('/(.*)=(.*);/',$hbset,$dateo,PREG_SET_ORDER);
			$oladta=$_G['cache']['onlinelist']['legend'];
			$oladta=str_replace('&nbsp;', "", $oladta);
			$a='/<img src=\"static\/image\/common\/([\w_\.]+)\" \/>([^<]*)/';  
			preg_match_all($a,$oladta,$c,PREG_SET_ORDER);
			
			for($i=0;$i<count($dateo,0);$i++){
				$img=$dateo[$i][1];$name=$dateo[$i][2];
				$out='<img src="static/image/common/'.$img.'"> '.$name;
				for($k=0;$k<count($c,0);$k++){
					if($img==$c[$k][1]){$j=$k;$c[$k][0]='';$yes = 1;}
				}
				if($yes){$c[$j][0]=$out; $yes= 0;}
			}
			
			if($xnyk>$onlinenum){
				$bbb='/^((\d+|guestcount|membercount)([\+\-\*\/)]{1}|$))+(\d+|guestcount|membercount)$/';
				$str=str_replace("%", "", $jsgs);$jsgs=str_replace("%", "$", $jsgs);
			    if(preg_match($bbb,$str)){
					@eval("\$jsgs = $jsgs;");
					$xnzz=$jsgs;
					$guestcount+=$xnzz;
					$onlinenum+=$xnzz;
				}
			}
			if($onlinenum>=$onlineinfo[0]){$onlineinfo[0]=($onlineinfo[0]+$xnyk)*3;}
			for($k=0;$k<count($c,0);$k++){
				$url='forum.php?showoldetails=yes&online='.$c[$k][1].'#online';
				if($c[$k][0]){$d[$k]='<A HREF="'.$url.'" alt="LOOK ME!">'.$c[$k][0].'</A>';}
			}
			ksort($d);
			$str = implode("&nbsp;&nbsp;&nbsp;", $d); 
			$_G['cache']['onlinelist']['legend']= $str;
			if(isset($_G['gp_online'])){
				for($k=0;$k<count($whosonline,0);$k++){
					if($whosonline[$k]['icon']==$_G['gp_online']){$onl[$k]=$whosonline[$k];}
				}
				ksort($onl);
				if($onl){$whosonline=$onl;}else{
					$whosonline='';
					$whosonline[0]=array(
						'uid' => '',
						'username' => '<A HREF="forum.php">'.$wrts.'</A>',
						'groupid' => '',
						'invisible' => '0',
						'lastactivity' => '0_0',
						'fid' => '0',
						'icon' => $_G['gp_online'],
						);
				}
			}			
		}
		return '';
	}
}

class plugin_dsu_amuolg_forum extends plugin_dsu_amuolg {
}
?>