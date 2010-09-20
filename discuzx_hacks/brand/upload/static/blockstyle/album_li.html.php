<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: album_li.html.php 3814 2010-07-20 08:16:05Z yexinhao $
 */-->

<!--{if $iarr}-->
<div class="box">
		<h3>$lang['jingcaituku']</h3>
		<ul class="goodpic">
			<!--{loop $iarr $ikey $value}-->
			<li><a target="_blank" href="store.php?id={$value['shopid']}&action=album&xid={$value['itemid']}"><img src="{$value['thumb']}" alt=""></a></li>
			<!--{/loop}-->
		</ul>
		<div class="viewall"><a href="store.php?id={$_SGLOBAL['shopid']}&action=album" class="c_dgreen">$lang['viewall']</a></div>
</div>
<!--{/if}-->