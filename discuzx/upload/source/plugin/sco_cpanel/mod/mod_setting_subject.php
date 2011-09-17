<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_cpanel_setting_subject extends plugin_sco_cpanel {

	function _my_strlen($str) {
		if(strtolower(CHARSET) == 'utf-8') return self::_my_utf8_strlen($str);

		return dstrlen($str);
	}

	function _my_utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}

	/**
	 * 預設行為
	 */
	function on_op_default() {
		global $_G;

		$url = "plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&";
		$url .= '&op=';
		$url .= "&cpmod=".$this->attr['global']['mod'];

		$setting = array();

		$_tables_map = array(
			'polloption' => array(
				'varchar' => array(
					'forum_polloption',
				),
			),

			'subject' => array(
				'char' => array(
					'forum_forumrecommend',
					'forum_rsscache',
					'forum_thread',
					'home_blog',
					'portal_rsscache',
				),
				'varchar' => array(
					'forum_post',
				),
			),
		);

		$_table_field = array();

		foreach ($_tables_map as $_k => $_v) {
			foreach ($_v as $_c => $_ts) {
				$_tableinfo = $this->_db()->loadtable($_ts);

				foreach ($_ts as $_t) {
					$_table_field[$_t][$_k] = $_tableinfo[$_t][$_k];

					$_table_field[$_t][$_k]['TypeDefault'] = $_c.'(80)';

					if ($_t == 'forum_thread') {
						$setting['subject_sql_size'] = preg_replace('/^(?:var)?char\((\d+)\)$/i', '\\1', $_table_field[$_t][$_k]['Type']);
					}
				}
			}
		}

		$setting['post_subject_maxsize'] = $_G['setting']['post_subject_maxsize'];
		$setting['post_subject_maxsize'] = max(80, $setting['post_subject_maxsize']);

		if ($this->submitcheck('typesubmit')) {

			$settingnew = array();

			global $_G;

			$settingnew['subject_sql_size'] = max(80, intval($_G['gp_settingnew']['subject_sql_size']));

			if ($this->_getglobal('debug', 'setting')) {
				var_dump($settingnew);
			}

			foreach ($_table_field as $_t => $_v) {
				foreach ($_v as $_k => $_f) {

					$_f['TypeNew'] = preg_replace('/\((\d+)\)/', '('.$settingnew['subject_sql_size'].')', $_f['Type']);

					$_fa = array(
						$_t, 'MODIFY', $_k, $_f['TypeNew']
					);

					$ret = $this->_db()->upgradetable($_fa);

					if ($this->_getglobal('debug', 'setting')) {
						var_dump(array(
							$_fa,
							$ret,
						));
					}
				}
			}

			$settingnew['post_subject_maxsize'] = max(80, intval($settingnew['post_subject_maxsize']));

			if ($settingnew['post_subject_maxsize'] != $setting['post_subject_maxsize']) {
				$ret = $this->_db()->query("REPLACE INTO ".$this->_db()->table_name('common_setting')." SET skey='post_subject_maxsize', svalue='{$settingnew[post_subject_maxsize]}'");
			}

			cpmsg(
				'succeed'
				, 'action='.$url
				, 'succeed'
			);

		} else {

			foreach ($_table_field as $_t => $_v) {
				foreach ($_v as $_k => $_f) {
					$_class = '';
					if ($_f['Type'] == $_f['TypeDefault']) $_class .= ' lightfont';

					$_html .= showtablerow('',
						array('class="td25"', "class=\"td25 td27 {$_class}\"", "class=\"td25 {$_class}\"", "class=\"td25 lightfont\"", 'class="td25"'),
						array(
						$_t,
						$_k,
						$_f['Type'],
						$_f['TypeDefault'],
						$_f['Collation'],
						$_f['Comment'],
					), true);
				}
			}

			showformheader($url);

			showtableheader('nav_setting_viewthread', 'nobottom');

			showsetting('post_subject_maxsize', 'settingnew[post_subject_maxsize]', $setting['post_subject_maxsize'], 'number');

			showsetting('subject_sql_size', 'settingnew[subject_sql_size]', $setting['subject_sql_size'], 'number');
			showtagfooter('tbody');

			showtableheader('tables');
			showsubtitle(array(
				'tablename',
				'field',
				'type',
				'type(default)',
				'charset',
				'comment',
			));

			echo $_html;

			showsubmit('typesubmit', 'submit', 'del');
			showtablefooter();
			showformfooter();

		}

		if ($this->_getglobal('debug', 'setting')) {
			var_dump($_table_field);
		}

		return $this;
	}
}

?>