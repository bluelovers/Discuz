<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//error_reporting(E_ALL ^E_NOTICE ^E_STRICT);

include_once dirname(__FILE__).'/./sco_cpanel.class.php';

class plugin_sco_cpanel_threadsorts extends plugin_sco_cpanel {

	function plugin_sco_cpanel_threadsorts() {
		global $plugin, $module;
		$this->_init($plugin['identifier']);

		$this->_this(&$this);

		$this->_fix_plugin_setting();
		$this->attr['profile'] = $this->attr['db']['common_plugin'];
	}

	function set($attr) {
		/*
		$this->attr['global'] = $attr;
		*/
		foreach ($attr as $_k => $_v) {
			$this->attr['global'][$_k] = $_v;
		}

		return $this;
	}

	function run() {
		$operation = $this->attr['global']['op'];

		$operation = $operation ? $operation : 'default';

		$method = 'on_op_'.$operation;

		$this->cpheader();

		$this->$method();

		if ($this->_getglobal('debug', 'setting')) {
			var_dump($this);
		}

		$this->cpfooter();

		return $this;
	}

	function cpheader() {
		/*
		global $lang;
		*/

		parent::cpheader();

		$url = "plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&";

		$op_list = array(
			'list_fups' => $this->cplang('threadtype_infotypes'),
		);

		echo '<div class="extcredits" style="margin: 0px;"><ul class="rowform">';
		foreach ($op_list as $key => $name) {
			echo '<li'.($this->attr['global']['op'] == $key ? ' class="current" style="font-weight: bold;"' : '').'><a href="'.ADMINSCRIPT."?action=".$url.'&op='.$key.'"><span>'.$name.'</span></a></li>';
		}
		echo '</ul></div>';

		return $this;
	}

	function on_op_default() {
		/*
		$this->on_op_list_fups();
		*/
	}

	function on_op_list_fups() {
		/*
		global $lang;
		*/

		$tablename = 'forum_typeoption';
		$url = "plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&";
		$url .= '&op=list_fups';

		if ($this->submitcheck('typesubmit')) {
			global $_G;

			$deleted = $modified = array();

			if(is_array($_G['gp_delete'])) {

				$query = DB::query("SELECT * FROM ".DB::table($tablename)." WHERE classid > 0 AND classid IN (".dimplode($_G['gp_delete']).")");
				if (DB::num_rows($query)) {
					$this->cpmsg('無法刪除含有項目的分類類別', '', 'error');
				}

				foreach($_G['gp_delete'] as $optionid) {
					if (empty($optionid)) continue;

					$query = DB::query("SELECT * FROM ".DB::table($tablename)." WHERE classid='$optionid'");
					if (DB::num_rows($query)) {
						$this->cpmsg('無法刪除含有項目的分類類別, 目前已經刪除 '.dimplode($deleted), '', 'error');
					} else {
						$deleted[] = $optionid;
						DB::delete($tablename, array(
							'optionid' => $optionid,
							'classid' => 0,
						), 1);
					}
				}
			}

			if(is_array($_G['gp_namenew']) && $_G['gp_namenew']) {
				foreach($_G['gp_namenew'] as $optionid => $val) {
					DB::update($tablename, array(
						'title' => trim($_G['gp_namenew'][$optionid]),
						'displayorder' => intval($_G['gp_displayordernew'][$optionid]),
					), array(
						'optionid' => $optionid,
						'classid' => 0,
					));
					if(DB::affected_rows()) {
						$modified[] = $optionid;
					}
				}
			}

			if(is_array($_G['gp_newname'])) {
				foreach($_G['gp_newname'] as $key => $value) {
					if($newname1 = trim(strip_tags($value))) {
						$query = DB::query("SELECT optionid FROM ".DB::table($tablename)." WHERE classid='0' AND title='$newname1'");
						if(DB::num_rows($query)) {
							$this->cpmsg('forums_threadtypes_duplicate', '', 'error');
						}
						$data = array(
							'title' => $newname1,
							'displayorder' => $_G['gp_newdisplayorder'][$key],
							'classid' => 0,
						);
						DB::insert($tablename, $data);
					}
				}
			}

			if ($this->_getglobal('debug', 'setting')) {
				var_dump(array(
					'gp_delete' => $_G['gp_delete'],
					'gp_namenew' => $_G['gp_namenew'],
					'gp_newname' => $_G['gp_newname'],
				));
			}

			cpmsg(
				'forums_threadtypes_succeed'
				, 'action='.$url
				, 'succeed'
			);

		} else {

			$threadtypes = '';

			$query = DB::query("SELECT * FROM ".DB::table($tablename)." WHERE classid='0' ORDER BY displayorder, title");
			while($option = DB::fetch($query)) {
				$threadtypes .= showtablerow('',
					array('class="td25"', 'class="td25 td27 lightfont"', 'class="td25"', 'class="td29"'),
					array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
					'('.$option['optionid'].')',
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$option[optionid]]\" value=\"$option[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\">",
					"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=typeoption&classid=$option[optionid]\" class=\"act nowrap\">".$this->cplang('detail')."</a>"
				), TRUE);
			}

			?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, ''],
		[1, '<input type="text" class="txt" name="newdisplayorder[]" size="2" value="">', 'td28'],
		[1, '<input type="text" class="txt" name="newname[]" size="15">'],
		[1, '']
	],
];
</script>
<?php

			showformheader($url);
			showtableheader('threadtype_infotypes');
			showsubtitle(array('', '', 'display_order', 'name', ''));

			echo $threadtypes;
			echo '<tr><td class="td25"></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$this->cplang('threadtype_infotypes_add').'</a></div></td>';

			showsubmit('typesubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		}
	}
}

$_cpanel = new plugin_sco_cpanel_threadsorts();

$_cpanel
	->set(array(
		'op' => $_G['gp_op'],
		'module' => &$module,
	))
	->run()
;

?>