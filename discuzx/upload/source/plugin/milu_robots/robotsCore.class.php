<?php
$miluRobots = new miluRobots();
class miluRobots{
	
	var $robots_type; //爬虫名称
	var $robots_arr;
	var $savelog_dir;
	var $robots_list;//后台设定的需要记录的爬虫
	var $swf_dir;
	var $swf_path;
	var $plugin_dir;
	function miluRobots() {
		$this->_ini();
		$this->clear_log();
	}
	
	function _ini(){
		global $_G;
		$set = $_G['cache']['plugin']['milu_robots'];
		$robots_list = (array)unserialize($set['robotsType']);
		$this->robots_type = strtolower($_SERVER['HTTP_USER_AGENT']);
		$this->robots_arr = array('googlebot' => '谷歌','msnbot' => 'MSN','slurp' => 'Yahoo','baiduspider' => '百度','sohu-search' => '搜狐','lycos' => 'lycos','robozilla' => 'Robozilla', 'Soso' => '搜搜','sogou' => '搜狗');
		$this->plugin_dir = "./source/plugin/milu_robots/";
		$this->savelog_dir = $this->plugin_dir."data/".date("Ym", time()).'/';
		$this->file_name = $this->savelog_dir.date("d", time()).'.txt';
		$this->robots_list = $robots_list;
		$this->swf_dir = $this->plugin_dir."MyFCPHPClassCharts";
		$this->swf_path=$_G['siteurl']."source/plugin/milu_robots/MyFCPHPClassCharts/FusionCharts/";
	}
	//获取爬虫类型
	function get_robots(){
		if(!is_array($this->robots_arr)) return false;
		foreach($this->robots_arr as $k => $v){
		   if (strpos($this->robots_type, $k) !== false && in_array($k, $this->robots_list)){ 
			   return $k;
		   } 
		}
	}
	//定期清除日志
	function clear_log() {
		$d = date('d', time());
		$Y = date('Y', time());
		$m = date('m', time());
		$m = $m == '01' ? '13' : $m;
		if($d < 5){
			$m = str_pad($m - 1,2, "0", STR_PAD_LEFT);
			deldir($this->plugin_dir.'/data/'.$Y.$m.'/');
		}
	}
	//记录日志
	function add_log() {
		global $_G;
		$this->robots_type = $this->get_robots();
		if(!$this->robots_type) return false;
		$dir_name = $this->savelog_dir;
		if(!is_dir($dir_name)) mkdir($dir_name,777);
		$robots_url = "http://".$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
		$fp = fopen($this->file_name, "a");
		$robots_time = time();
  		fwrite($fp,$robots_time."|".$this->robots_type."|".$robots_url."\r\n");
	}
	//取得某天的数据
	function day_log($time = '',$list = 'all') {
		if(!$time){
			$data_file = $this->file_name;
		}else{
			$dir = date("Ym", $time);
			$name = date("d", $time).'.txt';
			
			$data_file = $this->plugin_dir."data/".$dir.'/'.$name;
		}
		if(!file_exists ($data_file)) return false;
		$handle = fopen($data_file, "r");
		$data = fread($handle, filesize($data_file));
		$data = explode("\r\n", $data);
		arsort($data);
		if(!is_array($data)) return false;
		foreach($data as $k => $v){
			$v = explode("|", $v);
			if(($v[1] == $list || $list == 'all') && $v[1]){
				$list_data[$k]['time'] = date("Y-m-d H:i:s",$v[0]);
				$list_data[$k]['robots_name'] = $this->robots_arr[$v[1]];
				$list_data[$k]['view_url'] = $v[2];
			}
		}
		return $list_data;
	}
	function list_log() {
		$q = addslashes($_REQUEST['q']);
		$q = $q ? $q : 'all';
		$time = time();
		$list_data = $this->day_log($time, $q);
		$robots_arr = $this->robots_arr;
		unset($data);
		include template('milu_robots:list_log');	
	}
	//统计日志
	function count_log() {
		include_once($this->swf_dir."/class/FusionCharts_Gen.php");
		$js_path = $this->swf_path;
		$q = addslashes($_REQUEST['q']);
		$show_type = $_POST['show_type'];
		$q = $q ? $q : 'baiduspider';
		$show_type = $show_type ? $show_type : 'MSArea';
		$m = date("m",time());
		$Y = date("Y",time());
		//$day_count = cal_days_in_month(CAL_GREGORIAN, $m, $Y);//一个月有多少天
		$day_count = date("d",time());
		for($i = 1; $i < $day_count+1;$i++){
			$i_f = str_pad($i,2,"0",STR_PAD_LEFT);
			$file = $this->plugin_dir."data/".date("Ym", time())."/".$i_f.".txt";
			if(file_exists($file)){
				$day_time = strtotime($Y."-".$m."-".$i_f." 00:00:00");
				$data = $this->day_log($day_time, $q); 
				$list_data[$i.'日'] = count($data);
			}else{
				$list_data[$i.'日'] = 0;
			}	
		}
		
		/*开始生成图形*/
		$FC = new FusionCharts($show_type,"950","400"); 
		$FC->setSWFPath($js_path);
		$show_title=$m."月份".$this->robots_arr[$q]."爬虫趋向图";
		$strParam="caption=$show_title;lang=CN;yAxisName=次数;numberPrefix=;showValues=1;baseFontSize=13;decimalPrecision=0;formatNumberScale=1";
		$FC->setChartParams($strParam);
		$FC->addDataset("爬行次数","color=008E8E");
		if(is_array($list_data)){
			foreach($list_data as $k=>$v){
				$FC->addCategory($k);
				$FC->addChartData($v);
			}
		}
		$js_path .= "FusionCharts.js";
		$chart_flash=$FC->renderChart("",false);
		/*结束*/
		$robots_arr = $this->robots_arr;
		include template('milu_robots:list_count');				
	}
	
	//今天统计
	function day_count_log() {
		include_once($this->swf_dir."/class/FusionCharts_Gen.php");
		$js_path = $this->swf_path;
	    $FC= new FusionCharts("Pie3D","500","350"); 
		$FC->setSWFPath($js_path);
	  	$caption_title=$count_str."今日统计";
		$strParam="caption=$title ;subCaption=$caption_title;lang=CN;yAxisName=Revenue;pieSliceDepth=20;numberPrefix=;baseFontSize=13;decimalPrecision=0;formatNumberScale=1;showNames=1";
		$FC->setChartParams($strParam);
		foreach($this->robots_arr as $k => $v){
			$arr = $this->day_log('', $k);
			if(is_array($arr)){
				$c = count($this->day_log('', $k));
			}
			if($c != 0){
				$FC->addChartData($c, "name=".$v);
				$c = '';
			}
		}
		$js_path .= "FusionCharts.js";
		$chart_flash=$FC->renderChart("",false);
		include template('milu_robots:day_count_log');	
	}
}


//删除非空文件夹函数
function deldir($dir) {
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}
?>