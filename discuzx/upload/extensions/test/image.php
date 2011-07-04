<?php

/**
 *
 */

//define('APPTYPEID', 100);
//define('CURSCRIPT', 'misc');

require './../../source/class/class_core.php';

$discuz = & discuz_core::instance();

require_once libfile('class/image');

$_img_r = './image/210_mantis.png';
$_img_d = './test/210_mantis.png';

$i = 0;

$img = new image;
$img->Thumb($_img_r, $_img_d.'.test.'.$i.'.png', 48, 48, 'fixwr');

$i++;

$img = new image;
$img->Thumb($_img_r, $_img_d.'.test.'.$i.'.png', 600, 600, 'fixwr');

$i++;

$img = new image;
$img->Thumb($_img_r, $_img_d.'.test.'.$i.'.png', 48, 48, 'fixnone');

echo '<style>body { background-image: url(image/bg-0062.gif); }</style>';

for ($k=0; $k<=$i; $k++) {
	echo '<img src="'.$_img_d.'.test.'.$k.'.png'.'"><br>';
}

?>