<?php
/*
	amucallme_dzx admin BY 阿牧
*/
!defined('IN_DISCUZ') && exit('Access Denied');
!defined('IN_ADMINCP') && exit('Access Denied');
DEFINE('OFFSET_DELIMETER', "\t");
//公共部分
$file = './data/plugindata/amucallme_dzx.data.php';
if(!$_G['gp_submit']){
	$exsel = extc2seled(0,$_G['setting']['extcredits']);
	loadcache('usergroups');
	$usergroups = $_G['cache']['usergroups'];
	showformheader('plugins&operation=config&identifier=amucallme_dzx&pmod=admin');
	showtips(lang("plugin/amucallme_dzx","admin2_p1"));
	showtableheader(lang("plugin/amucallme_dzx","admin2_h1"));
	showsubtitle(array(lang("plugin/amucallme_dzx","admin2_t4"),lang("plugin/amucallme_dzx","admin2_t2"), lang("plugin/amucallme_dzx","admin2_t3")));
	if(file_exists($file)){
		require_once DISCUZ_ROOT.'./data/plugindata/amucallme_dzx.data.php';
		$data_f2a = dstripslashes($data_f2a);
	}
	foreach ($usergroups as $id => $result){
		$exinp = $data_f2a[$id];
		$exsel = extc2seled($exinp['extcredits'],$_G['setting']['extcredits']);
		showtablerow('', array(' ', ' ', ' '), array(
			''.$result['grouptitle'].'<INPUT TYPE="hidden" NAME="usergid[]" value="'.$id.'">',
			''.$exsel.'',
			'<input type="text" class="txt" name="cost[]" value="'.$exinp['cost'].'" size="7" />',
		));
	}
	showsubmit('submit', lang("plugin/amucallme_dzx","admin2_s2"));
	
	showtablefooter();
	showformfooter();
}elseif($_G['adminid']=='1' && $_G['gp_formhash']==FORMHASH){
	$mrcs = array();
	//var_dump($_POST);
	$max_i = max(count($_G['gp_usergid']), count($_G['gp_extcredits']), count($_G['gp_cost']));
	for($i=0;$i<$max_i;$i++){
		if(intval($_G['gp_extcredits'][$i]) && intval($_G['gp_usergid'][$i])){
			$k = intval($_G['gp_usergid'][$i]);
			$mrcs[$k]['usergid']=intval($_G['gp_usergid'][$i]);
			$mrcs[$k]['extcredits']=intval($_G['gp_extcredits'][$i]);
			$mrcs[$k]['cost']=intval($_G['gp_cost'][$i]*100)/100;
		}
	}
	array2php($mrcs,$file,'data_f2a');
	cpmsg('amucallme_dzx:admin2_i', 'action=plugins&operation=config&identifier=amucallme_dzx&pmod=admin','succeed');
}


//自定义函数

function extc2seled($id,$array){
	$extc_sel = '<select name="extcredits[]">';
	foreach($array as $i => $value){
		if($id == $i ){
			$extc_sel .='<option value="'.$i.'" selected>'.$value['title'].'</option>' ;
		}else{
			$extc_sel .='<option value="'.$i.'">'.$value['title'].'</option>' ;
		}
	}
	$extc_sel .= '</select>';
	return $extc_sel;
}

function array2file($file,$array){
    $fp = fopen($file, "wb");
    fwrite($fp, serialize($array));
    fclose($fp);
}

function file2array($file){
    if(!file_exists($file)){
        //echo " does no exist";
    }
    $handle=fopen($file,"rb");
    $contents=fread($handle,filesize($file));
    fclose($handle);
    return unserialize($contents);
}


function array2php($array,$file,$arrayname)  {
	$of = fopen($file,'w');
	if($of){
		$txt = array2txt($array);
		$text = "<?php\n\$".$arrayname." = array( \n".$txt.");\n?>";
		fwrite($of,$text);
	}
    return '';
}
function array2txt($array, $offset = OFFSET_DELIMETER)  {
    $text = "";
    foreach($array as $k => $v) {
        if (is_array($v)) {
            $text .= "{$offset}'{$k}' => array(\n".array2txt($v, $offset.OFFSET_DELIMETER)."$offset)";
        } else {
            $text .= "{$offset}'{$k}' => ".(is_string($v)? "'$v'": $v);
        }
        $text .= ",\n";
    }	
    return $text;
}
?>