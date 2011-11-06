<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_attach extends _sco_dx_plugin {

	function __construct() {
		$this->identifier = $this->_get_identifier(__CLASS__);
	}

}

class plugin_sco_attach_forum extends plugin_sco_attach {

	function attachment_message() {
		$_v = $this->_parse_method(__METHOD__, 1);

		if (
			CURSCRIPT == $_v[1]
			&& CURMODULE == $_v[2]
		) {
			$this->_hook(
				'Func_dshowmessage:Before_custom', array(
					$this,
					'_my_hook_attachment_message'
			));
		}
	}

	function _my_hook_attachment_message($_EVENT, $_conf) {
		global
			$_G
			, $attach
		;

		// copy from forum_index
		list($navtitle, $metadescription, $metakeywords) = get_seosetting('forum');
		if(!$navtitle) {
			$navtitle = $_G['setting']['navs'][2]['navname'];
			$nobbname = false;
		} else {
			$nobbname = true;
		}
		if(!$metadescription) {
			$metadescription = $navtitle;
		}
		if(!$metakeywords) {
			$metakeywords = $navtitle;
		}

		if ($attach) {
			!empty($attach['description']) && $_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ',' . $attach['description'];

			$metakeywords =
				',' . strip_tags(dgmdate($attach['dateline'], 'u'))
				. ',' . strtolower(fileext($attach['filename']))
				. ',' . sizecount($attach['filesize'])
				. ',' . $metakeywords
			;

			$tid = $attach['tid'];
			$pid = $attach['pid'];
		}

		if (!empty($_G['forum_attach_filename'])) {
			$_conf['_data_dshowmessage_']['globalvars']['navtitle'] =
				lang('template', 'e_attach')
				. ': '
				. $_G['forum_attach_filename']
				. ' - '
				. $_conf['navtitle']
			;

			$_conf['_data_dshowmessage_']['globalvars']['navigation'] =
				'<em>&raquo;</em> '
				. '<span>' . lang('template', 'e_attach'). '</span> '
				. '<em>&raquo;</em> '
				. '<span>' . $_G['forum_attach_filename']. '</span> '
			;

			$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .=
				','.lang('template', 'e_attach')
				.','.$_G['forum_attach_filename']
			;
			$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .=
				','.$_G['forum_attach_filename']
				.','.lang('template', 'e_attach')
			;
		}

		include_once libfile('function/post');

		if ($pid && $post = get_post_by_pid($pid)) {
			$summary = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($post['message']), 160));

			$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ',' . $summary;

			if ($post['first']) $firstpost = &$post;

			$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ','.$post['subject'];
			$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .= ','.$post['subject'];

			$tagarray_all = explode("\t", $post['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$tag = explode(',', $var);
						$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .= ','.$tag[1];
					}
				}
			}
		}

		if ($tid && $thread = get_thread_by_tid($tid)) {

			if (!$firstpost) {
				$posttable = getposttablebytid($tid);
				$firstpost = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid='$tid' AND first = '1' LIMIT 1");

				$summary = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($firstpost['message']), 160));

				$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ',' . $summary;

				$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ','.$thread['subject'];
				$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .= ','.$thread['subject'];

				$tagarray_all = explode("\t", $firstpost['tags']);
				if($tagarray_all) {
					foreach($tagarray_all as $var) {
						if($var) {
							$tag = explode(',', $var);
							$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .= ','.$tag[1];
						}
					}
				}
			}

		}

		$_conf['_data_dshowmessage_']['globalvars']['metadescription'] .= ','.$metadescription;
		$_conf['_data_dshowmessage_']['globalvars']['metakeywords'] .= ','.$metakeywords;

		foreach (array(
			'navtitle',
			'metadescription',
			'metakeywords',
		) as $_k) {
			$_conf['_data_dshowmessage_']['globalvars'][$_k] = strreplace_strip_split(array(
				'&amp;nbsp;',
				'&nbsp;',
			), array(
				' ',
				' ',
			), $_conf['_data_dshowmessage_']['globalvars'][$_k]);
		}
	}

}

?>