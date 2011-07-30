<?php
class resizeimage
{
        //圖片類型
        var $type;
        //實際寬度
        var $width;
        //實際高度
        var $height;
        //改變後的寬度
        var $resize_width;
        //改變後的高度
        var $resize_height;
        //是否裁圖
        var $cut;
        //源圖象
        var $srcimg;
        //目標圖象地址
        var $dstimg;
        //臨時創建的圖象
        var $im;

        function resizeimage($img, $wid, $hei,$c)
        {
                //echo $img.$wid.$hei.$c;
                $this->srcimg = $img;
                $this->resize_width = $wid;
                $this->resize_height = $hei;
                $this->cut = $c;
                //圖片的類型
                $this->type = substr(strrchr($this->srcimg,"."),1);
                //初始化圖象
                $this->initi_img();
                //目標圖象地址
                $this -> dst_img();
                //imagesx imagesy 取得圖像 寬、高
                $this->width = imagesx($this->im);
                $this->height = imagesy($this->im);
                //生成圖象
                $this->newimg();
                ImageDestroy ($this->im);
        }
        function newimg()
        {

                // +----------------------------------------------------+
                // | 增加LOGO到縮略圖中
                // +----------------------------------------------------+
                //Add Logo
                //$logoImage = ImageCreateFromJPEG('t_a.jpg');
                //ImageAlphaBlending($this->im, true);
                //$logoW = ImageSX($logoImage);
                //$logoH = ImageSY($logoImage);
                // +----------------------------------------------------+

                //改變後的圖象的比例
                $resize_ratio = ($this->resize_width)/($this->resize_height);
                //實際圖象的比例
                $ratio = ($this->width)/($this->height);
                if(($this->cut)=="1")
                //裁圖
                {
                        if($ratio>=$resize_ratio)
                        //高度優先
                        {
                                //imagecreatetruecolor — 新建一個真彩色圖像
                                $newimg = imagecreatetruecolor($this->resize_width,$this->resize_height);
                                //imagecopyresampled — 重采樣拷貝部分圖像並調整大小
                                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width,$this->resize_height, (($this->height)*$resize_ratio), $this->height);


                                // +----------------------------------------------------+
                                // | 增加LOGO到縮略圖中
                                // +----------------------------------------------------+
                                //ImageCopy($newimg, $logoImage, 0, 0, 0, 0, $logoW, $logoH);
                                // +----------------------------------------------------+

                                //imagejpeg — 以 JPEG 格式將圖像輸出到瀏覽器或文件
                                ImageJpeg ($newimg,$this->dstimg);
                        }
                        if($ratio<$resize_ratio)
                        //寬度優先
                        {
                                $newimg = imagecreatetruecolor($this->resize_width,$this->resize_height);
                                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, $this->width, (($this->width)/$resize_ratio));


                                // +----------------------------------------------------+
                                // | 增加LOGO到縮略圖中
                                // +----------------------------------------------------+
                                //ImageCopy($newimg, $logoImage, 0, 0, 0, 0, $logoW, $logoH);
                                // +----------------------------------------------------+


                                ImageJpeg ($newimg,$this->dstimg);
                        }

                }
                else
                //不裁圖
                {
                        if($ratio>=$resize_ratio)
                        {
                                $newimg = imagecreatetruecolor($this->resize_width,($this->resize_width)/$ratio);
                                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, ($this->resize_width)/$ratio, $this->width, $this->height);


                                // +----------------------------------------------------+
                                // | 增加LOGO到縮略圖中
                                // +----------------------------------------------------+
                                //ImageCopy($newimg, $logoImage, 0, 0, 0, 0, $logoW, $logoH);
                                // +----------------------------------------------------+


                                ImageJpeg ($newimg,$this->dstimg);
                        }
                        if($ratio<$resize_ratio)
                        {
                                $newimg = imagecreatetruecolor(($this->resize_height)*$ratio,$this->resize_height);
                                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, ($this->resize_height)*$ratio, $this->resize_height, $this->width, $this->height);


                                // +----------------------------------------------------+
                                // | 增加LOGO到縮略圖中
                                // +----------------------------------------------------+
                                //ImageCopy($newimg, $logoImage, 0, 0, 0, 0, $logoW, $logoH);
                                // +----------------------------------------------------+


                                ImageJpeg ($newimg,$this->dstimg);
                        }
                }

                // +----------------------------------------------------+
                // | 釋放資源
                // +----------------------------------------------------+
                //ImageDestroy($logoImage);
                // +----------------------------------------------------+

        }
        //初始化圖象
        
        function initi_img()
        {
                if($this->type=="jpg")
                {
                        $this->im = imagecreatefromjpeg($this->srcimg);
                }
                if($this->type=="gif")
                {
                        $this->im = imagecreatefromgif($this->srcimg);
                }
                if($this->type=="png")
                {
                        $this->im = imagecreatefrompng($this->srcimg);
                }
        }
        /*
        function initi_img($f)
        {
            //GetImageSize獲取圖像信息，數組表示，print_r ($data);
            $data=GetImageSize($f);
            switch($data[2]){
            case 1:
                $this->im = imagecreatefromgif($f);
                break;
            case 2:
                $this->im = imagecreatefromjpeg($f);
                break;
            case 3:
                $this->im = imagecreatefrompng($f);
                break;
            }
        }
        */
        //圖象目標地址
        function dst_img()
        {
                $full_length  = strlen($this->srcimg);
                $type_length  = strlen($this->type);
                $name_length  = $full_length-$type_length;
				$length_1	  = strrpos($this->srcimg, "/");
				$length_2	  = substr($this->srcimg, 0, $length_1);
				$length_3	  = substr($this->srcimg, $length_1+1);
				$length_3	  = substr($length_3, 0, strrpos($length_3, "."));
                $this->dstimg = $length_2."/small_pic/".$length_3.".".$this->type;
        }
}
?>