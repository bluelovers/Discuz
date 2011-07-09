<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$do = $_G['gp_do'];
$novelid = $_G['gp_novelid'];

if($do == 'rank'){

$coverpath = 'data/attachment/pdnovel/cover/';
$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid = $novelid");

$startext = array('暂无评分', '不知所云', '随便看看', '值得一读', '不容错过', '经典必读');
$novel[cover] = $novel[cover] ? $coverpath.$novel[cover] : 'template/default/pdnovel/img/nocover.jpg';
$novel[lastupdate] = strftime("%y-%m-%d %H:%M",$novel[lastupdate]);
$novel[lastchaptercontent] = str_replace("\r\n", "", $novel[lastchaptercontent]);
$novel[lastchaptercontent] = str_replace("\n", "", $novel[lastchaptercontent]);
for($p=1;$p<6;$p++){
	$percentage[$p] = round($novel['click'.$p]*100/$novel[click],1);
	$width[$p] = ceil($percentage[$p]*0.85)+1;
	$sum_score += $novel['click'.$p]*$p;
}
$novel[score] = round($sum_score*2/$novel[click],1);
$stari = ceil($novel[score]/2);

}

include_once template('common/header_ajax');
include_once template('pdnovel/ajax_rank');
include_once template('common/footer_ajax');

//echo "{value:{A:'$novel[score]', AT:'$startext[$stari]', S1:['$novel[click1]','$percentage[1]'], S2:['$novel[click2]','$percentage[2]'], S3:['$novel[click3]','$percentage[3]'], S4:['$novel[click4]','$percentage[4]'],S5:['$novel[click5]','$percentage[5]'],CV:false,CI:'$novel[lastchapterid]',CD:'$novel[lastupdate]',CN:'$novel[lastchapter]',CC:'$novel[lastchaptercontent]',BI:'$novel[cover]',AI:'$novel[authorid]',AN:'$novel[author]'}}";

?>