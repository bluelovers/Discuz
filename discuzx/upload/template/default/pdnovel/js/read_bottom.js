var speed = 5;
var currentpos=1;
var timer;
var novelbgcolor=document.getElementById('novelbgcolor');
var txtcolor=document.getElementById('txtcolor');
var fonttype=document.getElementById('fonttype');
var scrollspeed=document.getElementById('scrollspeed');
function setSpeed()
{
	speed = parseInt(scrollspeed.value);	
}

function stopScroll()
{
    clearInterval(timer);
}

function beginScroll()
{
	timer=setInterval("scrolling()",300/speed);
}

function scrolling()
{
	var currentpos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    window.scroll(0, ++currentpos);
	var nowpos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    if(currentpos != nowpos) clearInterval(timer);
}

function setCookies(cookieName,cookieValue, expirehours)
{
  var today = new Date();
  var expire = new Date();
  expire.setTime(today.getTime() + 3600000 * 356 * 24);
  document.cookie = cookieName+'='+escape(cookieValue)+ ';expires='+expire.toGMTString()+'; path=/';
}
function ReadCookies(cookieName)
{
	var theCookie=''+document.cookie;
	var ind=theCookie.indexOf(cookieName);
	if (ind==-1 || cookieName=='') return ''; 
	var ind1=theCookie.indexOf(';',ind);
	if (ind1==-1) ind1=theCookie.length;
	return unescape(theCookie.substring(ind+cookieName.length+1,ind1));
}
function saveSet()
{
	setCookies("novelbgcolor",novelbgcolor.options[novelbgcolor.selectedIndex].value);
	setCookies("txtcolor",txtcolor.options[txtcolor.selectedIndex].value);
	setCookies("fonttype",fonttype.options[fonttype.selectedIndex].value);
	setCookies("scrollspeed",scrollspeed.value);
}
function loadSet()
{
	var tmpstr;
	tmpstr = ReadCookies("novelbgcolor");
	novelbgcolor.selectedIndex = 0;
	if (tmpstr != "")
	{
	    for (var i=0;i<novelbgcolor.length;i++)
		{
			if (novelbgcolor.options[i].value == tmpstr)
			{
				novelbgcolor.selectedIndex = i;
				break;
			}
		}
	}
	tmpstr = ReadCookies("txtcolor");
	txtcolor.selectedIndex = 0;
	if (tmpstr != "")
	{
		for (var i=0;i<txtcolor.length;i++)
		{
			if (txtcolor.options[i].value == tmpstr)
			{
				txtcolor.selectedIndex = i;
				break;
			}
		}
	}
	tmpstr = ReadCookies("fonttype");
	fonttype.selectedIndex = 0;
	if (tmpstr != "")
	{
		for (var i=0;i<fonttype.length;i++)
		{
			if (fonttype.options[i].value == tmpstr)
			{
				fonttype.selectedIndex = i;
				break;
			}
		}
	}
	
	tmpstr = ReadCookies("scrollspeed");
	if (tmpstr=='') tmpstr=5;
	scrollspeed.value=tmpstr;
	setSpeed();
	readcontentobj=document.getElementById('readcontent');
	if(readcontentobj){
		readcontentobj.style.backgroundColor=novelbgcolor.options[novelbgcolor.selectedIndex].value;
		readcontentobj.style.fontSize=fonttype.options[fonttype.selectedIndex].value;
		readcontentobj.style.color=txtcolor.options[txtcolor.selectedIndex].value;
	}
}
document.onmousedown=stopScroll;
document.ondblclick=beginScroll;
loadSet();