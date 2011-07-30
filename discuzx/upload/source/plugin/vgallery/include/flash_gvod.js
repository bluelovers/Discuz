var GVOD_PLAYER = {
	moviename:'',
	MIN_WIDTH:480,
	MIN_HEIGHT:300,
	div_width:0,
	div_height:0,
	installPage:'http://www.gvodzi.net/player/install.htm',
	bufferPage:'',
	stickPage:'',
	pausePage:'',
	marquee:'',
	textlink:'',
	bufferEntry:5,
	bufferLeave:30,
	nextUrl:'',
	nextPage:'',
	play:function(suburl){
		this.div_width = this.width>this.MIN_WIDTH?this.width:this.MIN_WIDTH;
		this.div_height = this.height>this.MIN_HEIGHT?this.height:this.MIN_HEIGHT;
		if(!document.all){
			 notIE();
			 return;
		}

		try{
			var splitArr = suburl.split("/");
			this.moviename = decodeURIComponent(splitArr[splitArr.length-1].split("?")[0]);
			//alert(this.moviename);
		}catch(E){}

		try{
			//檢測有沒有GVODPlayer.VersionDetector，new失敗則沒有，提示全新安裝
			var versionDetector = new ActiveXObject("GVODPlayer.VersionDetector");
			var v_dapctrl = versionDetector.GetVersion("GVODS", "DapCtrl.dll");
			var v_gvods = versionDetector.GetVersion("GVODS", "GVODS.exe");
			var v_canfinstall = false;
			//alert("iVersion:"+v_dapctrl);
			//alert("GVODS:"+v_gvods);
			try{
				if(versionDetector.CanInstallGVOD()==1 && (versionDetector.GetVersion('Thunder5', 'Thunder5.exe') >=662 || versionDetector.GetVersion('Thunder6', 'Thunder.exe') >= 173) ){
					v_canfinstall = true;
				}
			}catch(E){
				v_canfinstall = false;
			}
			if ( v_canfinstall && (v_dapctrl < 158 || v_gvods < 56) )
			{
				//僞安裝所需文件完備，並且不是最新版本
				updateGVOD(versionDetector);
				return;
			}
			else if ( v_dapctrl < 158 || v_gvods < 56)
			{
				//不能進行僞安裝，則提示全新安裝
				noGVOD();
				return;
			}
			
			if (suburl.substr(0,10) == "thunder://" && v_dapctrl > 149)
			{
				//是專用鏈，則調用DapCtrl接口解專用鏈後繼續
				var dapCtrl = new ActiveXObject("DapCtrl.DapCtrl");
				suburl = dapCtrl.DecodeThunderLink(suburl);
				dapCtrl = null;
				try{
					var splitArr = suburl.split("/");
					this.moviename = decodeURIComponent(splitArr[splitArr.length-1].split("?")[0]);
				}catch(E){}
			}
			var queryStr='movie_name='+encodeURIComponent(this.moviename)+'&suburl='+encodeURIComponent(suburl)+'&installPage='+encodeURIComponent(this.installPage)+'&stickPage='+encodeURIComponent(this.stickPage)+'&pausePage='+encodeURIComponent(this.pausePage)+'&bufferPage='+this.bufferPage+'&bufferEntry='+this.bufferEntry +'&bufferLeave='+this.bufferLeave+'&nextUrl='+encodeURIComponent(this.nextUrl)+'&nextPage='+encodeURIComponent(this.nextPage)+'&divHeight='+this.div_height+'&divWidth='+this.div_width+'&marquee='+encodeURIComponent(this.marquee)+'&textlink='+encodeURIComponent(this.textlink);
			start(queryStr);
		}
		catch(E){
			noGVOD();
		}
	}
}

function updateGVOD(versionDetector){
	hint_stat(11);
	var pLctn = document.URL;
	genDivStr("http://www.gvodzi.net/player/install.htm");
}
function gvod_player(){
	return GVOD_PLAYER;
}

function hint_stat(stat_value){
	var URL = "http://stat.gvod.xunlei.com/fcg-bin/fcgi_gvod_stat.fcg?stat_key=1&stat_value="+stat_value+"&domain="+location.hostname+"&lasttime="+(new Date().getTime());
	pgvByImg(URL);
	
}
function genDivStr(src){  
	var iframe_proxy = document.getElementById('iframe_proxy');
	if(iframe_proxy){
		iframe_proxy.src = src;
		return;
	}
	var insertStr = "\
		<DIV id='gvod_div' style='WIDTH: "+GVOD_PLAYER.div_width+"px; HEIGHT: "+GVOD_PLAYER.div_height+"px'>\
		<iframe id = 'iframe_proxy' name='iframe_proxy' scrolling='no' frameborder='0' style='margin:0; width:100%; height:100%' src='' ></iframe>\
		</DIV>\
	"; 
	document.write(insertStr);
	var iframe_proxy = document.getElementById('iframe_proxy');
	if(iframe_proxy) iframe_proxy.src=src;
}
function noGVOD(){
	genDivStr(GVOD_PLAYER.installPage+"?hostname="+location.hostname);
	hint_stat(1);
	if(GVOD_PLAYER.nogvod){GVOD_PLAYER.nogvod()}
}
function notIE(){
	var ieWarnPage = 'http://gvod.xunlei.com/player/ie_warn.htm';
	hint_stat(0);
	genDivStr(ieWarnPage);
}
function start(queryStr){
			hint_stat(0);
			try{
				var dapCtrl = new ActiveXObject("DapCtrl.DapCtrl");
				dapCtrl.Put("iAlwaysUseGVODS", 1);
				if (dapCtrl.GetThunderVer("GVODS", "Running") == 0)
				{
					dapCtrl.Put("sRunThunder", "GVODS");
				}
				dapCtrl.Put("iOpenPort", 0);
				var playport = dapCtrl.get("iPlayPort");
				if (playport == 0)
				{
					alert("播放器加載失敗");
				}
				else
				{
					var godsrc = "http://127.0.0.1:" + playport + "/local_play.html?"+queryStr;
					genDivStr(godsrc);
				}
				dapCtrl = null;
			}
		catch (E)
		{
//			alert("err="+E);
			noGVOD();
		}
	}
	function pgvByImg(u) {
		var  statImg = new Image();
		statImg.src = u;
}