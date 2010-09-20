
/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: navgation.js 3776 2010-07-16 08:21:35Z yexinhao $
 */

$(
        function(){
        $("#forumnav dt a").focus(function(){$(this).blur();});
        if($("#forumnav").size() > 0){$("#showitemnav").navgation({obj:"forumnav"})};
        }
 );

(function($){
 $.fn.navgation = function(options){
 var $this = $(this);
 var $obj = $("#"+ options.obj);
 var tmpHeight = ($obj.height() * (-1));
 var timeout = pause = "";
 if($obj.is(".nav_forum_club")){
 $this.hover(function(){
     if($obj.css("top") < 0 && $obj.css("top") > tmpHeight){return false;}
     $obj.animate({top:"0px"},80,function(){
         clearTimeout(timeout);
         $obj.hover(
             function(){
             pause = true;
             $(this).show()
             },
             function(){
             timeout = setTimeout(
                 function(){
                 pause = false;
                 $obj.animate({top:tmpHeight + "px"},120)
                 },200)
             }
             )
         })
     },function(){
     timeout = setTimeout(
         function(){
         if(pause != true){
         $obj.animate({top:tmpHeight + "px"},120)
         }
         },300)
     }).find("a").click(function(){return false;});
 }
 }
})(jQuery);
$(document).ready(function(){
        $("#show_navmsg").hover(
        function(){
        var msglist = $("#show_navmsg .nav_msglist");
        var diff = $(window).width() - $(this).offset().left;
        var width = msglist.outerWidth();
        if(diff < width){
        msglist.addClass("nav_msglist_right");
        }
        $(this).addClass("nav_msg_active");
        },
        function(){
        $(this).removeClass("nav_msg_active");
        $(this).find(".nav_msglist").removeClass("nav_msglist_right");
        });
        });

