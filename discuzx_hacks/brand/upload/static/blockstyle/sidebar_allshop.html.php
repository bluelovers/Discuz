<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar_allshop.html.php 3776 2010-07-16 08:21:35Z yexinhao $
 */-->

<div class="box hot">
	<h3>$lang['allshop']</h3>
	<ol>
	<!--{loop $iarr $ikey $value}-->
	<!--{eval $value = $_BCACHE->getshopinfo($value['itemid'])}-->
	<!--{if !empty($value['itemid'])}-->
		<li><a href="#" onclick="showalbumlist({$value['itemid']});">{$value['subject']}</a></li>
	<!--{/if}-->
	<!--{/loop}-->
	</ol>
</div>
