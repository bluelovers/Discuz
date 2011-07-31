<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myrepeats.class.php 21730 2011-04-11 06:23:46Z lifangming $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_v63ht {

	function global_header(){
		global $_G;
        include 'source/plugin/v63ht/config.inc.php';
        if($setting['ispop']=='1' || $setting['qz']=='1'){
        //print_r($_G);
        if($_G[uid] !=''){
            $query = DB::query("select * from ".DB::table("forum_post")." where tid='$setting[htid]' and authorid = '$_G[uid]'");
            $isArr = DB::fetch($query);
            
            if(!is_array($isArr)){
                if($setting['qz']=='1'){
                    $a = 'hta_a=0';
                }
                    return '<script>function isttht(){var date=new Date();date.setTime(date.getTime()+1*3600*1000);
document.cookie="isttht=999;expire="+date.toGMTString();}
function getCookie(name){
var strCookie=document.cookie;
var arrCookie=strCookie.split("; ");
for(var i=0;i<arrCookie.length;i++){
var arr=arrCookie[i].split("=");
if(arr[0]==name)return arr[1];
}
return "";
}

var hta_a = getCookie("isttht");
'.$a.'
if(hta_a !="999"){
    showWindow("v63ht","plugin.php?id=v63ht:ajax&vac=getht","get",0);
}
</script> ';
                }
            }
        
        }
    }
        

}


class plugin_v63ht_forum extends plugin_v63ht {

	function index_top(){
	   require_once libfile('function/discuzcode');
	   include 'source/plugin/v63ht/config.inc.php';
	   $query = DB::query("select * from ".DB::table("forum_post")." where tid='$setting[htid]' and first != '1' order by pid desc limit 20");
       $list = '';
       while($htlist = DB::fetch($query)){
            $list = $list."<li><a href='home.php?mod=space&uid=".$htlist['authorid']."'>".$htlist[author]." </a>说：".discuzcode($htlist['message'],0,0)."</li>";
       }
	   
    return '<style>
#v63ht_list{height:30px;border: 1px solid #CDCDCD;margin-bottom:5px}
#v63ht_list ul{ float:left; margin:0; width:700px;height:30px; overflow:hidden}
#v63ht_list ul li{ line-height:30px; height:30px; overflow:hidden}
</style>

<div id="v63ht_list"><span style=" font-weight:bold; float:left;line-height:30px;">&nbsp;今日互动话题：</span><ul id="ht_list">'.$list.'</ul><span style="float:right;line-height:30px;"><a href="javascript:" onclick=showWindow("v63ht","plugin.php?id=v63ht:ajax&vac=getht","get",0)>参与</a>&nbsp;&nbsp;</span></div>

<script>
var box=document.getElementById("ht_list"),can=true;
box.innerHTML+=box.innerHTML;
box.onmouseover=function(){can=false};
box.onmouseout=function(){can=true};
new function (){
var stop=box.scrollTop%30==0&&!can;
if(!stop)box.scrollTop==parseInt(box.scrollHeight/2)?box.scrollTop=0:box.scrollTop++;
setTimeout(arguments.callee,box.scrollTop%30?10:1500);
};
</script>';
  }

}


?>