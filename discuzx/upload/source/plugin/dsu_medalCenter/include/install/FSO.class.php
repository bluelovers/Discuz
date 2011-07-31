<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: FSO.class.php 24 2011-01-07 20:25:04Z chuzhaowei@gmail.com $
*/

/**
 * 文件操作类，来自CZW Framework(build 509)。本类发布于 http://www.jhdxr.com/blog/html/tech/php-tech/fso-class-for-php.html
 *
 * @author 江湖大虾仁
 */
if(class_exists('FSO')) {
    class FSO {
        /**
         * 复制文件或文件夹
         * @param <string> $source: 待复制的文件或文件夹
         * @param <string> $dest: 目标文件或文件夹
         * @param <int> $rewrite: 当目标文件存在时是否覆盖（默认值1为覆盖）
         */
        public static function copy($source, $dest, $rewrite = 1) {
            $rewrite = 0 == $rewrite ? 0 : 1;
            self::_move($source, $dest, $rewrite, 1);
        }

        /**
         * 删除文件或文件夹
         * @param <string> $source: 待删除的文件或文件夹
         */
        public static function unlink($source) {
            self::_move($source, '', -1, 0);
            if(file_exists($source)){ //二次删除
            	self::_move($source, '', -1, 0);
            }
        }

        /**
         * 移动文件或文件夹
         * @param <string> $source: 待移动的文件或文件夹
         * @param <string> $dest: 目标文件或文件夹
         * @param <int> $rewrite: 当目标文件存在时是否覆盖（默认值1为覆盖）
         */
        public static function move($source, $dest, $rewrite = 1) {
            $rewrite= 0 == $rewrite ? 0 : 1;
            self::_move($source, $dest, $rewrite, 2);
            if(self::_isEmpty($source)){ //删除
            	self::unlink($source);
            }
        }
        
        /**
         * 建立多层目录，同时设定文件夹权限和创建index文件
         * @param <string> $dir 需要创建的多层目录
         * @param <int> $mode 模式，仅支持八进制数，例如0777
         * @param <bool> $makeindex 是否创建index.html文件
         */
        public static function mkdir($dir, $mode = 0777, $makeindex = TRUE){
			if(!is_dir($dir)){
				self::mkdir(dirname($dir));
				@mkdir($dir, $mode);
				if($makeindex){
					@touch($dir.'/index.html');
					@chmod($dir.'/index.html', 0777);
				}
			}
			return true;
		}
        
        /**
         * 改变文件夹及其里边的文件和文件夹的模式
         * @param <string> $source 需要被更改模式的文件夹
         * @param <int> $mode 模式，仅支持八进制数，例如0777
         * @return <bool>如果全部更改成功返回true，否则返回false
         */
        public static function dir_chmod($source, $mode) {
            $return=true;
            if(is_dir($source)) {
                $source.='/' == substr($source,-1)?'':'/';
                $dir=dir($source);
                while(false !== ($entry=$dir->read())) {
                    if($entry == '.' || $entry == '..') continue;
                    if(!self::chmod($source.$entry,$mode)) $return=false;
                }
                $dir->close();
            }else {
                if(!chmod($source, $mode)) return false;
            }
            return $return;
        }

        /**
         * 检查文件或文件夹是否具有777权限。
         * 本函数在win系列操作系统下无效（总是返回TRUE）
         * @param <string> $source 要检查的文件或文件夹
         * @return <bool> 返回是否具有777权限
         */
        public static function check777($source){
            return (';'==PATH_SEPARATOR || file_exists($source) && is_writeable($source));
        }

        /**
         * 复制、移动、删除文件或文件夹
         * @param <string> $source: 待移动的文件或文件夹
         * @param <string> $dest: 目标文件或文件夹
         * @param <int> $rewrite: 1为复制且覆盖，0仅复制，-1为不复制
         * @param <int> $reserved: 是否保留源文件（默认值0为不保留，1为保留，2为当未成功复制时保留）
         */
        private static function _move($source, $dest, $rewrite = 1, $reserved = 0) {
            if(is_dir($source)) {
                $source .= '/' == substr($source,-1) ? '' : '/';
                $dest .= $dest != '' && '/' == substr($dest,-1) ? '' : '/';
                $dir = dir($source);
                $rewrite >= 0 && @mkdir($dest);
                while(false !== ($entry = $dir->read())) {
                    if($entry == '.' || $entry == '..') continue;
                    self::_move($source.$entry, $dest.$entry, $rewrite, $reserved);
                }
                $dir->close();
                if(!$reserved) @rmdir($source);
            }else {
                //rename($source,$dest); url wrapper support??
                $destExist = (0 == $rewrite && 2 == $reserved) ? file_exists($dest) : false; //只有当需要时才进行判断
                if(1 == $rewrite || (0 == $rewrite && !$destExist)) copy($source,$dest);
                if(0 == $reserved || (2 == $reserved && !$destExist)) unlink($source);
            }
        }
        
        private static function _isEmpty($source) {
        	$source .= '/' == substr($source,-1) ? '' : '/';
        	if(is_dir($source)) {
        		$dir = dir($source);
        		while(false !== ($entry = $dir->read())) {
        			if($entry == '.' || $entry == '..' || (is_dir($entry) && self::_isEmpty($source.$entry))) continue;
        			return false;
        		}
        		$dir->close();
        	}
        	return true;
        }
    }
}
?>
