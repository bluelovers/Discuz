<?php

/**
 *      敏捷 PHP 框架 CodeIgniter 中国官方社区
 *      http://codeigniter.org.cn
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_newbie_task {
}

class plugin_newbie_task_member extends plugin_newbie_task {

	function register_task_output($a) {
		global $_G;

		// 取插件的配置
		$vars_config = $_G['cache']['plugin']['newbie_task'];
		$order = empty($vars_config['order']) ? 100 : $vars_config['order'];

		// 注册完成的时候才会执行下面的内容
		if ($_G['uid'])
		{
			// 取符合条件的任务
			$query = DB::query("SELECT t.*, mt.csc, mt.dateline FROM ".DB::table('common_task')." t
				LEFT JOIN ".DB::table('common_mytask')." mt ON mt.taskid=t.taskid AND mt.uid='$_G[uid]'
				WHERE '$_G[timestamp]' > starttime AND (mt.taskid IS NULL OR (ABS(mt.status)='1' AND t.period>0)) AND t.available='2' AND t.displayorder>" . $order . " ORDER BY t.displayorder, t.taskid DESC");

			// 循环插入到用户的任务列表中
			$i = 0;
			while ($task = DB::fetch($query))
			{
				// 装载插件，并执行 condition 方法
				require_once libfile('task/'.$task['scriptname'], 'class');
				$taskclassname = 'task_'.$task['scriptname'];
				$taskclass = new $taskclassname;
				if (method_exists($taskclass, 'condition'))
				{
					$taskclass->condition();
				}

				DB::query("REPLACE INTO ".DB::table('common_mytask')." (uid, username, taskid, csc, dateline)
					VALUES ('$_G[uid]', '$_G[username]', '".$task['taskid']."', '0\t$_G[timestamp]', '$_G[timestamp]')");
				DB::query("UPDATE ".DB::table('common_task')." SET applicants=applicants+1 WHERE taskid='".$task['taskid']."'", 'UNBUFFERED');
				++$i;

				// 执行 preprocess 方法
				if (method_exists($taskclass, 'preprocess'))
				{
					$taskclass->preprocess($task);
				}
			}

			// 设置提醒
			if ($i > 0)
			{
				dsetcookie('taskdoing_'.$_G['uid'], 1, 7776000);
			}
		}
	}
}


/* End of file newbie_task.class.php */
/* Location: ./source/plugin/newbie_task/newbie_task.class.php */
