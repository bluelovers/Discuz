<?php
$langtemp = <<<EOF
<div class="t_smallfont cl" style="font-size: 12px">
<font color=red> 相机型号：</font>{相机型号}<br>
<font color=red> 曝光时间：</font>{曝光时间}
<font color=red> 光 圈：</font>{光圈}
<font color=red> 曝光补偿：</font>{曝光补偿}EV
<font color=red> 曝光模式：</font>{曝光模式}<br>
<font color=red> 白 平 衡：</font>{白平衡}
<font color=red> ISO感光度：</font>{ISO}
<font color=red> 焦距：</font>{焦距}mm<br>
<font color=red> 拍摄时间：</font>{拍摄时间}
<font color=red> 分 辨 率：</font>{分辨率}
</div>
EOF;
$lang[temp][2] = $langtemp;

$langtemp = <<<EOF
<div class="t_smallfont cl" style="font-size: 12px;margin:0; padding:0 0 5px 20px;background:transparent url(static/image/common/info_small.gif) no-repeat;">
<font color=red>相机型号：</font>{相机型号}
<a onclick="toggle_collapse('exif_info_{aid}');" alt="收起/展开" title="收起/展开" ><B>查看更多Exif信息&raquo;</B></a>
<div id='exif_info_{aid}' style="display:none;">
<font color=red> 曝光时间：</font>{曝光时间}
<font color=red> 光 圈：</font>{光圈}
<font color=red> 曝光补偿：</font>{曝光补偿}EV
<font color=red> 曝光模式：</font>{曝光模式}<br>
<font color=red> 白 平 衡：</font>{白平衡}
<font color=red> ISO感光度：</font>{ISO}
<font color=red> 焦距：</font>{焦距}mm<br>
<font color=red> 拍摄时间：</font>{拍摄时间}
<font color=red> 分 辨 率：</font>{分辨率}
</div>
</div>
EOF;
$lang[temp][3] = $langtemp;

$langtemp = <<<EOF
<div id="exif_{aid}" class="t_smallfont cl" onmouseover="showMenu({'ctrlid':this.id,'pos':'12'})" style="font-size: 12px;margin:0; padding:0 0 5px 20px;background:transparent url(static/image/common/info_small.gif) no-repeat;">
<B>相机型号：</B>{相机型号}
<a title="显示更多EXIF信息" style="cursor:pointer"><B>&raquo;详情</B></a>
</div>
<div id="exif_{aid}_menu" class="p_pop cl" style="position: absolute;display:none;width:auto;white-space:nowrap;color:#000;font-size: 12px;">
<B> 曝光时间：</B>{曝光时间}
<B> 光 圈：</B>{光圈}
<B> 曝光补偿：</B>{曝光补偿}EV
<B> 曝光模式：</B>{曝光模式}<br>
<B> 白 平 衡：</B>{白平衡}
<B> ISO感光度：</B>{ISO}
<B> 焦距：</B>{焦距}mm<br>
<B> 拍摄时间：</B>{拍摄时间}
<B> 分 辨 率：</B>{分辨率}
</div>
EOF;
$lang[temp][4] = $langtemp;

$langtemp = <<<EOF
<div class="t_smallfont cl">
<div id="exif_{aid}" onmouseover="showMenu({'ctrlid':this.id,'pos':'12'})" style="font-size: 12px;float:left;margin:0; padding:0 0 5px 20px;background:transparent url(static/image/common/info_small.gif) no-repeat;">
<B>相机型号：</B>{相机型号}
<a onclick="toggle_collapse('exif_info_{aid}');" title="显示更多EXIF信息" style="cursor:pointer"><B>&raquo;详情</B></a>
</div></div>
<div id="exif_{aid}_menu" class="p_pop cl" style="position: absolute;display:none;width:auto;white-space:nowrap;color:#000;font-size: 12px;">
<B> 曝光时间：</B>{曝光时间}
<B> 光 圈：</B>{光圈}
<B> 曝光补偿：</B>{曝光补偿}EV
<B> 曝光模式：</B>{曝光模式}<br>
<B> 白 平 衡：</B>{白平衡}
<B> ISO感光度：</B>{ISO}
<B> 焦距：</B>{焦距}mm<br>
<B> 拍摄时间：</B>{拍摄时间}
<B> 分 辨 率：</B>{分辨率}
</div>
EOF;
$lang[temp][5] = $langtemp;
?>