<?php 
@require_once DISCUZ_ROOT.'source/plugin/qqcat_picexif/ver.php';
?>
<form id="form1" name="form1" method="post" action="<?=$p_url?>?a=bug&u=<?=$boardurl?>&pn=<?=$p_name?>&v=<?=$p_ver?>&dz=<?=$version?>">
  <table class="tb tb2 " id="tips3">
    <tr>
      <th  class="partition"><span style='float:right'><a href="http://www.rmbl.cn" target="_blank">容米部落 www.rmbl.cn</a></span>说明</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul class="nofloat" ><li>本表单会将新对本插件的要求的建议提交给我们</li>
        <li>
        &nbsp;&nbsp;论坛地址：<?=$boardurl?> (版本:<?=$version?>)<BR />
        &nbsp;&nbsp;插件名称：
        <?=$p_name?> (版本:<?=$p_ver?>)</li>
      </ul></td>
    </tr>
  </table>
  <table class="tb tb2 " id="tips">
<tr>
  <th  class="partition"><span style='float:right'><a href="http://www.rmbl.cn" target="_blank">容米部落 www.rmbl.cn</a></span>联系方式：QQ/MSN/EMAIL/TEL</th>
</tr>
<tr><td class="tipsblock">
  <input name="tel" type="text" id="tel" size="80" maxlength="80" />
</td></tr></table>
<table class="tb tb2 " id="tips2">
  <tr>
    <th  class="partition"><span style='float:right'><a href="http://www.rmbl.cn" target="_blank">容米部落 www.rmbl.cn</a></span>BUG或建议内容 <a href="http://www.rmbl.cn/" target="_blank">进入论坛（插件专区）</a></th>
  </tr>
  <tr>
    <td class="tipsblock"><textarea name="txt" cols="80" rows="10" id="txt"></textarea></td>
  </tr>
</table>
<input type="submit" class="btn" id="submit_bigqi_setting_submit" name="bigqi_setting_submit" title="按 Enter 键可随时提交您的修改" value="提交" />
</form>
