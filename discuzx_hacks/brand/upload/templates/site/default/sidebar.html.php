<?exit?>

<!--/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sidebar.html.php 3780 2010-07-16 08:53:02Z yexinhao $
 */-->

<div class="sidebar">
<!--{if CURSCRIPT == "goods"}-->
<!--{template 'static/blockstyle/sidebar_consume.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_consume']-->
<!--{template 'static/blockstyle/sidebar_groupbuy.html.php', 1}-->
<!--sidebar_groupbuy $lang['html_note_sidebar_groupbuy']-->
<!--{template 'static/blockstyle/sidebar_shop.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_shop']-->
<!--{elseif CURSCRIPT == "street"}-->
<!--{template 'static/blockstyle/sidebar_shop.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_shop']-->
<!--{template 'static/blockstyle/sidebar_consume.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_consume']-->
<!--{template 'static/blockstyle/sidebar_groupbuy.html.php', 1}-->
<!--sidebar_groupbuy $lang['html_note_sidebar_groupbuy']-->
<!--{elseif CURSCRIPT == "consume"}-->
<!--{template 'static/blockstyle/sidebar_consume.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_consume']-->
<!--{template 'static/blockstyle/sidebar_groupbuy.html.php', 1}-->
<!--sidebar_groupbuy $lang['html_note_sidebar_groupbuy']-->
<!--{template 'static/blockstyle/sidebar_shop.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_shop']-->
<!--{elseif CURSCRIPT == "groupbuy"}-->
<!--{template 'static/blockstyle/sidebar_groupbuy.html.php', 1}-->
<!--sidebar_groupbuy $lang['html_note_sidebar_groupbuy']-->
<!--{template 'static/blockstyle/sidebar_consume.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_consume']-->
<!--{template 'static/blockstyle/sidebar_shop.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_shop']-->
<!--{else}-->
<!--{template 'static/blockstyle/sidebar_consume.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_consume']-->
<!--{template 'static/blockstyle/sidebar_groupbuy.html.php', 1}-->
<!--sidebar_groupbuy $lang['html_note_sidebar_groupbuy']-->
<!--{template 'static/blockstyle/sidebar_shop.html.php', 1}-->
<!--sidebar_consume $lang['html_note_sidebar_shop']-->
<!--{/if}-->

</div>