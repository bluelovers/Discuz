


/*                                                                                                                                                                              
	clearbox by Kreatura Media
	
	script home:			http://www.clearbox.hu
							http://kreaturamedia.com
						
	Facebook: 				http://www.facebook.com/ClearBoxJS
							http://www.facebook.com/kreaturamedia

	LICENSZ FELTÉTELEK:

	A clearbox szabadon felhasználható bármely honlapon, 
	A clearbox a készítő beleegyezése nélkül pénzért harmadik félnek tovább nem adható!

	LICENSE:

	ClearBox can be used free for all web pages.

*/



var	CB_ScriptDir='clearbox'; // RELATIVE to your html file!
var	CB_Language='en';



//
//	ClearBox load:
//

	var CB_Scripts = document.getElementsByTagName('script');
	for(i=0;i<CB_Scripts.length;i++){
		if (CB_Scripts[i].getAttribute('src')){
			var q=CB_Scripts[i].getAttribute('src');
			if(q.match('clearbox.js')){
				var url = q.split('clearbox.js');
				var path = url[0];
				var query = url[1].substring(1);
				var pars = query.split('&');
				for(j=0; j<pars.length; j++) {
					par = pars[j].split('=');
					switch(par[0]) {
						case 'config': {
							CB_Config = par[1];
							break;
						}
						case 'dir': {
							CB_ScriptDir = par[1];
							break;
						}
						case 'lng': {
							CB_Language = par[1];
							break;
						}
						case 'debugmode': {
							CB_DebugMode = 'on';
							break;
						}
					}
				}
			}
		}
	}

	if(!CB_Config){
		var CB_Config='default';
	}

	document.write('<link rel="stylesheet" type="text/css" href="'+CB_ScriptDir+'/config/'+CB_Config+'/cb_style.css" />');
	document.write('<script type="text/javascript" src="'+CB_ScriptDir+'/config/'+CB_Config+'/cb_config.js"></script>');
	document.write('<script type="text/javascript" src="'+CB_ScriptDir+'/language/'+CB_Language+'/cb_language.js"></script>');
	document.write('<script type="text/javascript" src="'+CB_ScriptDir+'/core/cb_core.js"></script>');