<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index_recshop.html.php 3776 2010-07-16 08:21:35Z yexinhao $
 */-->

<!--{if is_array($iarr)}-->
<div class="recommend">
	<h3>$lang['recommendshop']</h3>
	<div>
		<ul id="recommendbrand">
		<!--{loop $iarr $ikey $value}-->
			<!--{eval $value=$_BCACHE->getshopinfo($value['itemid'])}-->
			<!--{if !empty($value['itemid'])}-->
			<li a="3">
				<a href="store.php?id={$value['itemid']}" target="_blank"><img width="60" height="40" src="{$value['thumb']}" alt=""></a>
				<p><a href="store.php?id={$value['itemid']}" target="_blank">{$value['subject']}</a></p>
			</li>
			<!--{/if}-->
		<!--{/loop}-->
		</ul>
	</div>
</div>
<!--{else}-->
<div class="recommend">
	<h3>$lang['recommendshop']</h3>
	<div>
		<ul>
			<li></li>
		</ul>
	</div>
</div>
<!--{/if}-->
