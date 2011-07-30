document.write('<object classid="clsid:F3D0D36F-23F8-4682-A195-74C92B03D4AF" id="QvodPlayer" name="QvodPlayer" onError=if(window.confirm("請您先安裝QvodPlayer軟件,然後刷新本頁才可以正常播放.")){window.open("http://www.qvod.com/download.htm")}else{self.location="http://www.qvod.com"} width="'+ swf_width +'" height="'+ swf_height +'">');
document.write('<PARAM NAME="URL" VALUE="'+swf_url+'">');
document.write('<PARAM NAME="Autoplay" VALUE="1">');
document.write('</object>');