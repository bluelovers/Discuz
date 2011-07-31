<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_dzperm.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
class script_dzperm{

	var $name = '积分限制模块';
	var $version = '1.0';
	var $copyright = '<a href="www.dsu.cc">DSU Team</a>';
	
	function admincp_show(){
		global $_G, $lang, $medal;
		$medal['permission'] = is_array($medal['permission']) ? $medal['permission'] : unserialize($medal['permission']);
		$medal['permission'] = $medal['permission'][0];
		showtableheader('medals_perm', 'notop');
		$formulareplace .= '\'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\',\'<u>'.$lang['setting_credits_formula_posts'].'</u>\',\'<u>'.$lang['setting_credits_formula_oltime'].'</u>\',\'<u>'.$lang['setting_credits_formula_pageviews'].'</u>\'';
?>
<script type="text/JavaScript">

	function isUndefined(variable) {
		return typeof variable == 'undefined' ? true : false;
	}

	function insertunit(text, textend) {
		$('formulapermnew').focus();
		textend = isUndefined(textend) ? '' : textend;
		if(!isUndefined($('formulapermnew').selectionStart)) {
			var opn = $('formulapermnew').selectionStart + 0;
			if(textend != '') {
				text = text + $('formulapermnew').value.substring($('formulapermnew').selectionStart, $('formulapermnew').selectionEnd) + textend;
			}
			$('formulapermnew').value = $('formulapermnew').value.substr(0, $('formulapermnew').selectionStart) + text + $('formulapermnew').value.substr($('formulapermnew').selectionEnd);
		} else if(document.selection && document.selection.createRange) {
			var sel = document.selection.createRange();
			if(textend != '') {
				text = text + sel.text + textend;
			}
			sel.text = text.replace(/\r?\n/g, '\r\n');
			sel.moveStart('character', -strlen(text));
		} else {
			$('formulapermnew').value += text;
		}
		formulaexp();
	}

	var formulafind = new Array('digestposts', 'posts', 'threads');
	var formulareplace = new Array(<?=$formulareplace?>);
	function formulaexp() {
		var result = $('formulapermnew').value;
<?

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $lang['setting_credits_formula_extcredits'].$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.$extcredittitle.'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="insertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.$lang['setting_credits_formula_posts'].'</u>\');';
		echo 'result = result.replace(/threads/g, \'<u>'.$lang['setting_credits_formula_threads'].'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.$lang['setting_credits_formula_oltime'].'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_and'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_or'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';

?>
		$('formulapermexp').innerHTML = result;
	}
</script>
<tr><td colspan="2"><div class="extcredits">
<?=$extcreditsbtn?><br />
<a href="###" onclick="insertunit(' digestposts ')"><?=$lang['setting_credits_formula_digestposts']?></a>&nbsp;
<a href="###" onclick="insertunit(' posts ')"><?=$lang['setting_credits_formula_posts']?></a>&nbsp;
<a href="###" onclick="insertunit(' threads ')"><?=$lang['setting_credits_formula_threads']?></a>&nbsp;
<a href="###" onclick="insertunit(' oltime ')"><?=$lang['setting_credits_formula_oltime']?></a>&nbsp;
<a href="###" onclick="insertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' = ')">&nbsp;=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' and ')">&nbsp;<?=$lang['setting_credits_formulaperm_and']?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' or ')">&nbsp;<?=$lang['setting_credits_formulaperm_or']?>&nbsp;</a>&nbsp;<br />
</div><div id="formulapermexp" class="marginbot diffcolor2"><?=$formulapermexp?></div>
<textarea name="formulapermnew" id="formulapermnew" style="width: 80%" rows="3" onkeyup="formulaexp()"><?=dhtmlspecialchars($medal['permission'])?></textarea>
<br /><span class="smalltxt"><?=$lang['medals_permformula']?></span>
<br /><?=$lang['creditwizard_current_formula_notice']?>
<script type="text/JavaScript">formulaexp()</script>
</td></tr>
<?
		showtablefooter();
	}
	
	function admincp_check(){
		global $_G, $formulapermary;
		if(!checkformulaperm($_G['gp_formulapermnew'])) {
			cpmsg('forums_formulaperm_error', '', 'error');
		}
		
		$formulapermary[0] = $_G['gp_formulapermnew'];
		$formulapermary[1] = preg_replace("/(digestposts|posts|threads|oltime|extcredits[1-8])/", "getuserprofile('\\1')", $_G['gp_formulapermnew']);
	}
	
	function memcp_check(){
		global $_G,$medal;
		$medalpermission = $medal['permission'] ? unserialize($medal['permission']) : '';
		if($medalpermission[0]) {
			include libfile('function/forum');
			medalformulaperm(serialize(array('medal' => $medalpermission)), 1);
			if($_G['forum_formulamessage']) {
				showmessage('medal_permforum_nopermission', 'plugin.php?id=dsu_medalCenter:memcp', array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']));
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
		showmessage('medal_permforum_nopermission', 'plugin.php?id=dsu_medalCenter:memcp', array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']));
		return 0;
	}
	
	function memcp_show(){
		global $_G,$medal;
		include_once libfile('function/forum');
		$medal['permission'] = serialize(array('medal' => unserialize($medal['permission'])));
		$medal['permission'] = medalformulaperm($medal['permission'],2);
		return $medal['permission'];
	}
}
?>