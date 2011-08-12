/*
	ClearBox JS by pyro
*/

var CB_version = '2.0';
var CB_Show = 1;

var CB_ActThumbSrc, CB_IEShowBug = '',
CB_AllThumbsWidth, CB_ResizeTimer, CB_IsAnimating, CB_ImgWidthOrig, CB_ImgHeightOrig, CB_ieRPBug = 0,
CB_ie6RPBug = '',
CB_ClearBox, CB_AnimX, CB_AnimY, CB_BodyMarginX = CB_BodyMarginLeft + CB_BodyMarginRight,
CB_BodyMarginY = CB_BodyMarginTop + CB_BodyMarginBottom,
FF_ScrollbarBug, CB_Links, CB_SlideBW = 0,
CB_SSTimer, CB_SS = 'start',
CB_ii = 0,
CB_jj = 0,
CB_Hide, CB_LoadingImg, CB_JumpX, CB_JumpY, CB_MarginL, CB_MarginT, CB_Content, CB_ImgWidth = CB_WinBaseW,
CB_ImgHeight = CB_WinBaseH - CB_TextH,
CB_ImgRate, CB_Win, CB_Txt, CB_Img, CB_Prv, CB_Nxt, CB_ImgWidthOld, CB_ImgHeightOld, CB_ActImgId, CB_Gallery, CB_Count, CB_preImages, CB_Loaded, CB_Header, CB_Footer, CB_Left, CB_Right;
CB_PicDir += '/';

var CB_PrePictures = new Array();
CB_PrePictures[0] = new Image();
CB_PrePictures[0].src = CB_PicDir + 'noprv.gif';
CB_PrePictures[1] = new Image();
CB_PrePictures[1].src = CB_PicDir + 'loading.gif';

function CB_Init() {
	if (!document.getElementById('CB_All') && CB_Show != 0) {
		document.body.style.position = "static";
		var a = '<div class="CB_RoundPixBugFix" style="width: ' + CB_RoundPix + 'px; height: ' + CB_RoundPix + 'px;"></div>';
		if (jQuery.browser.msie) {
			CB_IEShowBug = '<img id="CB_ShowEtc" alt="" src="' + CB_PicDir + 'blank.gif" /><img id="CB_ShowTh" alt="" src="' + CB_PicDir + 'blank.gif" />'
		} else {
			CB_IEShowBug = '<div id="CB_ShowTh"></div><div id="CB_ShowEtc"></div>'
		}
		var b = document.getElementsByTagName("body").item(0);
		var c = document.createElement("div");
		c.setAttribute('id', 'CB_All');
		b.appendChild(c);
		document.getElementById('CB_All').innerHTML = '<table cellspacing="0" cellpadding="0" id="CB_Window"><tr id="CB_Header"><td id="CB_TopLeft">' + a + '</td><td id="CB_Top"></td><td id="CB_TopRight">' + a + '</td></tr><tr id="CB_Body"><td id="CB_Left"></td><td id="CB_Content" valign="top" align="left"><div id="CB_Padding"><div id="CB_ImgContainer"><iframe frameborder="0" id="CB_iFrame" src=""></iframe>' + CB_IEShowBug + '<div id="CB_Etc"><img src="' + CB_PicDir + 'max.gif" alt="maximize" /></div><div id="CB_Thumbs"><div id="CB_Thumbs2"></div></div><img id="CB_LoadingImage" alt="loading" src="' + CB_PicDir + CB_PictureLoading + '" /><img id="CB_Image" alt="" src="' + CB_PicDir + 'blank.gif" /><div id="CB_PrevNext"><div id="CB_ImgHide"></div><img id="CB_CloseWindow" alt="x" src="' + CB_PicDir + CB_PictureClose + '" /><img id="CB_SlideShowBar" src="' + CB_PicDir + 'white.gif" /><img id="CB_SlideShowP" alt="Pause SlideShow" src="' + CB_PicDir + CB_PicturePause + '" /><img id="CB_SlideShowS" alt="Start SlideShow" src="' + CB_PicDir + CB_PictureStart + '" /><a id="CB_Prev" href="javascript:void(0)"></a><a id="CB_Next" href="javascript:void(0)"></a></div></div><div id="CB_Text"></div></div></td><td id="CB_Right"></td></tr><tr id="CB_Footer"><td id="CB_BtmLeft">' + a + '</td><td id="CB_Btm"></td><td id="CB_BtmRight">' + a + '</td></tr></table><div id="CB_ContentHide"></div>';
		if (navigator.userAgent.indexOf("MSIE 6") != -1 && CB_RoundPix == 0) {
			CB_ie6RPBug = 1
		}
		if (jQuery.browser.msie && CB_RoundPix < 2) {
			CB_ieRPBug = 6
		}
		document.getElementById('CB_Padding').style.padding = CB_Padd + 'px';
		CB_ShTh = document.getElementById('CB_ShowTh');
		CB_ShEt = document.getElementById('CB_ShowEtc');
		CB_ImgHd = document.getElementById('CB_ImgHide');
		CB_ImgHd.style.backgroundColor = '#fff';

		jQuery(CB_ImgHd).css('opacity', 0.75);

		CB_Win = document.getElementById('CB_Window');
		CB_Thm = document.getElementById('CB_Thumbs');
		CB_Thm2 = document.getElementById('CB_Thumbs2');
		CB_Et = document.getElementById('CB_Etc');
		CB_HideContent = document.getElementById('CB_ContentHide');
		CB_HideContent.style.backgroundColor = CB_HideColor;

		jQuery(CB_HideContent).css('opacity', 0);

		CB_Img = document.getElementById('CB_Image');
		CB_LoadingImg = document.getElementById('CB_LoadingImage');
		CB_ImgCont = document.getElementById('CB_ImgContainer');
		CB_Img.style.border = CB_ImgBorder + 'px solid ' + CB_ImgBorderColor;
		CB_Cls = document.getElementById('CB_CloseWindow');
		CB_SlideS = document.getElementById('CB_SlideShowS');
		CB_SlideP = document.getElementById('CB_SlideShowP');
		CB_SlideB = document.getElementById('CB_SlideShowBar');

		jQuery(CB_SlideB).css('opacity', 0.5);

		CB_Prv = document.getElementById('CB_Prev');
		CB_Nxt = document.getElementById('CB_Next');
		CB_Txt = document.getElementById('CB_Text');
		CB_Txt.style.height = (CB_TextH - CB_PadT) + 'px';
		CB_Txt.style.marginTop = CB_PadT + 'px';
		CB_Txt.style.fontFamily = CB_Font;
		CB_Txt.style.fontSize = CB_FontSize + 'px';
		CB_Txt.style.fontWeight = CB_FontWeigth;
		CB_Txt.style.color = CB_FontColor;
		CB_Header = document.getElementById('CB_Header').style;
		CB_Header.height = CB_RoundPix + 'px';
		CB_Footer = document.getElementById('CB_Footer').style;
		CB_Footer.height = CB_RoundPix + 'px';
		CB_Left = document.getElementById('CB_Left').style;
		CB_Left.width = CB_RoundPix + CB_ie6RPBug + 'px';
		CB_Right = document.getElementById('CB_Right').style;
		CB_Right.width = CB_RoundPix + 'px';
		CB_iFr = document.getElementById('CB_iFrame');
		CB_PrvNxt = document.getElementById('CB_PrevNext').style;
		CB_ShTh.onmouseover = function() {
			CB_ShowThumbs();
			return
		};
		CB_ShEt.onmouseover = function() {
			CB_ShowEtc();
			return
		};
		CB_ImgHd.onmouseover = function() {
			CB_HideThumbs();
			CB_HideEtc();
			return
		};
		CB_Txt.onmouseover = function() {
			CB_HideThumbs();
			CB_HideEtc();
			return
		};
		CB_HideContent.onmouseover = function() {
			CB_HideThumbs();
			CB_HideEtc();
			return
		};
		if (jQuery.browser.opera) {
			CB_BodyMarginX = 0;
			CB_BodyMarginY = 0
		}
		if (jQuery.browser.firefox) {
			CB_BodyMarginY = 0
		}
	}
	jQuery('#CB_Thumbs').mousemove(jQuery.clearbox.getMouseXY);
	var d = 0;
	var e = 0;

	CB_Links = [];
	jQuery('a[rel^="clearbox"]').each(function(index, elem){
		CB_Links[index] = elem;
	});

	for (i = 0; i < CB_Links.length; i++) {
		CB_Rel = jQuery(CB_Links[i]).attr('rel');
		CB_URL = jQuery(CB_Links[i]).attr('href');
		if (CB_Rel.match('clearbox') != null && CB_Show != 0) {
			if (CB_Rel == 'clearbox') {
				CB_Links[i].onclick = function() {
					CB_ClickIMG(this.rel + '+\\+' + this.getAttribute('href') + '+\\+' + this.getAttribute('title'));
					return false
				}
			} else {
				if (CB_Rel.substring(0, 8) == 'clearbox' && CB_Rel.charAt(8) == '[' && CB_Rel.charAt(CB_Rel.length - 1) == ']') {
					if (CB_Rel.substring(9, CB_Rel.length - 1).split(',')[0] != 'clearbox') {
						CB_Links[i].onclick = function() {
							CB_ClickIMG(this.rel.substring(9, this.rel.length - 1) + '+\\+' + this.getAttribute('href') + '+\\+' + this.getAttribute('title'));
							return false
						}
					} else {
						alert('ClearBox HIBA:\n\nClearBox galeria neve NEM lehet "clearbox[clearbox]"!\n(Helye: dokumentum, a ' + i + '. <a> tag-en belul.)')
					}
				} else if (CB_Rel.substring(0, 8) == 'clearbox' && CB_Rel.charAt(8) == '(' && CB_Rel.charAt(CB_Rel.length - 1) == ')') {
					if (CB_Rel.substring(9, CB_Rel.length - 1).split(',')[2] == 'click') {
						CB_Links[i].onclick = function() {
							CB_ClickURL(this.rel.substring(9, this.rel.length - 1) + '+\\+' + this.getAttribute('href') + '+\\+' + this.getAttribute('title'));
							return false
						}
					} else {
						CB_Links[i].onmouseover = function() {
							CB_ClickURL(this.rel.substring(9, this.rel.length - 1) + '+\\+' + this.getAttribute('href') + '+\\+' + this.getAttribute('title'));
							return false
						}
					}
				} else {
					alert('ClearBox HIBA:\n\nHibasan megadott clearbox REL azonosito: "' + CB_Rel + '"!\n(Helye: dokumentum, a ' + i + '. <a> tag-en belul.)')
				}
			}
		}
	}
}
function CB_ClickIMG(a) {
	if (CB_Show == 0) {
		return false
	}
	CB_Cls.onclick = '';
	CB_SlideS.onclick = '';
	CB_SlideP.onclick = '';
	CB_Clicked = a.split('+\\+');
	CB_Rel = CB_Clicked[0].split(',');
	if (CB_Rel[1] > 0) {
		CB_SlShowTimer = parseInt(CB_Rel[1]) * 1000
	} else {
		CB_SlShowTimer = CB_SlShowTime
	}
	if (CB_Rel[2] == 'start') {
		CB_SS = 'pause'
	}
	if (CB_Gallery && CB_Rel[0] == CB_Gallery[0][0] && CB_Gallery[0][0] != 'clearbox') {} else {
		CB_Gallery = new Array;
		CB_Gallery.push(new Array(CB_Rel[0], CB_Rel[1], CB_Rel[2]));

		jQuery.log(['CB_Gallery.push', CB_Rel[0], CB_Rel[1], CB_Rel[2]]);

		if (CB_Clicked[0] == 'clearbox') {
			CB_Gallery.push(new Array(CB_Clicked[1], CB_Clicked[2]));
		} else {
			for (i = 0; i < CB_Links.length; i++) {
				if (jQuery(CB_Links[i]).attr('rel').substring(9, jQuery(CB_Links[i]).attr('rel').length - 1).split(',')[0] == CB_Gallery[0][0]) {
					CB_ActThumbSrc = CB_PicDir + 'noprv.gif';
					if (jQuery(CB_Links[i]).attr('tnhref') == null || jQuery(CB_Links[i]).attr('tnhref') == 'null') {
						for (j = 0; j < CB_Links[i].childNodes.length; j++) {
							if (CB_Links[i].childNodes[j].src != undefined) {
								CB_ActThumbSrc = CB_Links[i].childNodes[j].getAttribute('src');
							}
						}
					} else {
						CB_ActThumbSrc = jQuery(CB_Links[i]).attr('tnhref');
					}

					// bluelovers
					CB_ActThumbSrc = CB_ActThumbSrc || jQuery(CB_Links[i]).attr('href');
					// bluelovers

					CB_Gallery.push(new Array(jQuery(CB_Links[i]).attr('href'), CB_Links[i].getAttribute('title'), CB_ActThumbSrc))
				}
			}
		}
	}
	CB_ActImgId = 0;
	while (CB_Gallery[CB_ActImgId][0] != CB_Clicked[1]) {
		CB_ActImgId++
	}
	CB_ImgWidthOld = CB_WinBaseW;
	CB_ImgHeightOld = CB_WinBaseH - CB_TextH;
	jQuery.clearbox.CB_SetAllPositions();
	CB_HideDocument()
}

function CB_ClickURL(a) {
	if (CB_Show == 0) {
		return false
	}
	CB_ClearBox = 'ki';
	CB_Clicked = a.split('+\\+');
	CB_PrvNxt.display = 'none';
	CB_Cls.style.display = 'none';
	CB_Rel = CB_Clicked[0].split(',');
	jQuery.clearbox.CB_SetAllPositions();
	CB_ImgWidth = parseInt(CB_Rel[0]);
	CB_ImgHeight = parseInt(CB_Rel[1]);
	CB_ImgWidthOld = CB_WinBaseW;
	CB_ImgHeightOld = CB_WinBaseH - CB_TextH;
	if (CB_ImgWidth > BrSizeX - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd))) {
		CB_ImgWidth = BrSizeX - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd))
	}
	if (CB_ImgHeight > BrSizeY - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd)) - CB_TextH) {
		CB_ImgHeight = BrSizeY - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd)) - CB_TextH
	}
	CB_Img.style.width = CB_WinBaseW + 'px';
	CB_Img.style.height = (CB_WinBaseH - CB_TextH) + 'px';
	CB_Img.style.display = 'block';
	CB_Img.style.visibility = 'hidden';
	CB_Win.style.visibility = 'visible';
	jQuery(CB_SlideS).hide();
	jQuery(CB_SlideP).hide();
	CB_HideDocument('x')
}
function CB_HideDocument(a) {
	var b = a;
	if (CB_ii < CB_HideOpacity) {
		CB_ii += CB_OpacityStep;

		jQuery(CB_HideContent).css('opacity', (CB_ii / 100));

		CB_Hide = CB_ii;
		CB_Blur = setTimeout("CB_HideDocument('" + b + "')", 5)
	} else {
		CB_ii = 0;
		CB_HideContent.style.height = DocSizeY + CB_BodyMarginY + 'px';
		if (CB_HideOpacity != 0) {
			clearTimeout(CB_Blur)
		}
		if (b == 'x') {
			CB_LoadingImg.style.visibility = 'visible';
			CB_AnimatePlease('x')
		} else {
			CB_NewWindow()
		}
		return
	}
}
function CB_NewWindow() {
	CB_Img.style.width = CB_WinBaseW + 'px';
	CB_Img.style.height = (CB_WinBaseH - CB_TextH) + 'px';
	CB_Img.style.display = 'block';
	CB_Img.style.visibility = 'hidden';
	CB_Win.style.visibility = 'visible';
	CB_LoadImage()
}
function CB_LoadImage(a) {
	CB_ShTh.style.visibility = 'hidden';
	CB_ShEt.style.visibility = 'hidden';
	CB_Thm.style.display = 'none';
	CB_Thm.style.width = 0 + 'px';
	CB_Et.style.display = 'none';
	CB_Et.style.width = 0 + 'px';
	CB_ImgHd.style.width = 0 + 'px';
	CB_ImgHd.style.height = 0 + 'px';
	CB_ImgHd.style.visibility = 'hidden';
	CB_ClearBox = 'ki';
	CB_jj = 0;
	CB_HideContent.onclick = '';
	if (CB_Gallery.length < 3) {
		jQuery(CB_SlideS).hide();
		jQuery(CB_SlideP).hide();
	} else {
		if (CB_SS == 'start') {
			jQuery(CB_SlideS).show();
			jQuery(CB_SlideP).hide();
		} else {
			jQuery(CB_SlideP).show();
			jQuery(CB_SlideS).hide();
		}
	}
	CB_Prv.style.display = 'none';
	CB_Nxt.style.display = 'none';
	if (a) {
		CB_ActImgId = parseInt(a)
	}
	CB_JumpX = CB_Jump_X;
	CB_JumpY = CB_Jump_Y;
	if (CB_Animation != 'warp') {
		CB_Img.style.visibility = 'hidden';
		CB_LoadingImg.style.visibility = 'visible'
	}
	CB_Txt.innerHTML = CB_LoadingText;
	CB_Count = 0;
	CB_preImages = new Image();
	CB_preImages.src = CB_Gallery[CB_ActImgId][0];
	CB_Loaded = false;
	CB_preImages.onerror = function() {
		CB_ShowImage();
		alert('ClearBox HIBA:\n\nA kepet nem lehet betolteni: ' + CB_Gallery[CB_ActImgId][0]);
		return
	};
	CB_CheckLoaded()
}
function CB_CheckLoaded() {
	if (CB_Count == 1) {
		CB_Loaded = true;
		clearTimeout(CB_ImgLoadTimer);
		CB_GetImageSize();
		return
	}
	if (CB_Loaded == false && CB_preImages.complete) {
		CB_Count++
	}
	CB_ImgLoadTimer = setTimeout("CB_CheckLoaded()", 5);
	return
}
function CB_GetImageSize() {

	// bluelovers
	CB_Img.style.marginLeft = 0;
	// bluelovers

	CB_ImgWidth = CB_preImages.width;
	CB_ImgHeight = CB_preImages.height;
	CB_ImgWidthOrig = CB_ImgWidth;
	CB_ImgHeightOrig = CB_ImgHeight;
	CB_ImgRate = CB_ImgWidth / CB_ImgHeight;
	jQuery.clearbox.CB_FitToBrowser();
	CB_Img.src = CB_Gallery[CB_ActImgId][0];
	CB_AnimatePlease();
	return
}
function CB_AnimatePlease(a) {
	CB_JumpX = CB_Jump_X;
	CB_JumpY = CB_Jump_Y;
	CB_AnimX = 'false';
	CB_AnimY = 'false';
	CB_IsAnimating = 1;
	if (CB_Animation == 'double') {
		jQuery.clearbox.CB_WindowResizeX();
		CB_WindowResizeY();
	} else if (CB_Animation == 'warp') {
		if (!a) {
			CB_LoadingImg.style.visibility = 'hidden';
			CB_Img.style.visibility = 'visible'
		}
		jQuery.clearbox.CB_WindowResizeX();
		CB_WindowResizeY();
	} else if (CB_Animation == 'ki') {
		CB_SetMargins();
		CB_ImgCont.style.height = CB_ImgHeight + (2 * CB_ImgBorder) + 'px';
		CB_Img.style.width = CB_ImgWidth + 'px';
		CB_Img.style.height = CB_ImgHeight + 'px';
		CB_AnimX = 'true';
		CB_AnimY = 'true'
	} else if (CB_Animation == 'normal') {
		jQuery.clearbox.CB_WindowResizeX();
	}
	if (a) {
		CB_CheckResize2();
	} else {
		CB_CheckResize();
	}
	return
}

function CB_WindowResizeY() {
	if (CB_ImgHeight == CB_ImgHeightOld) {
		if (CB_TimerY) {
			clearTimeout(CB_TimerY)
		}
		CB_AnimY = 'true';
		return
	} else {
		if (CB_ImgHeight < CB_ImgHeightOld) {
			if (CB_ImgHeightOld < CB_ImgHeight + 100 && CB_Jump_Y > 20) {
				CB_JumpY = 20
			}
			if (CB_ImgHeightOld < CB_ImgHeight + 60 && CB_Jump_Y > 10) {
				CB_JumpY = 10
			}
			if (CB_ImgHeightOld < CB_ImgHeight + 30 && CB_Jump_Y > 5) {
				CB_JumpY = 5
			}
			if (CB_ImgHeightOld < CB_ImgHeight + 15 && CB_Jump_Y > 2) {
				CB_JumpY = 2
			}
			if (CB_ImgHeightOld < CB_ImgHeight + 4) {
				CB_JumpY = 1
			}
			CB_ImgHeightOld -= CB_JumpY
		} else {
			if (CB_ImgHeightOld > CB_ImgHeight - 100 && CB_Jump_Y > 20) {
				CB_JumpY = 20
			}
			if (CB_ImgHeightOld > CB_ImgHeight - 60 && CB_Jump_Y > 10) {
				CB_JumpY = 10
			}
			if (CB_ImgHeightOld > CB_ImgHeight - 30 && CB_Jump_Y > 5) {
				CB_JumpY = 5
			}
			if (CB_ImgHeightOld > CB_ImgHeight - 15 && CB_Jump_Y > 2) {
				CB_JumpY = 2
			}
			if (CB_ImgHeightOld > CB_ImgHeight - 4) {
				CB_JumpY = 1
			}
			CB_ImgHeightOld += CB_JumpY
		}
		CB_Img.style.height = CB_ImgHeightOld + 'px';
		CB_ImgCont.style.height = CB_ImgHeightOld + (2 * CB_ImgBorder) + 'px';
		CB_MarginT = parseInt(DocScrY - (CB_ieRPBug + CB_ImgHeightOld + CB_TextH + (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd))) / 2);
		CB_Win.style.marginTop = (CB_MarginT - (FF_ScrollbarBug / 2)) + 'px';
		CB_TimerY = setTimeout("CB_WindowResizeY()", CB_AnimTimeout);
	}
}
function CB_CheckResize() {
	if (CB_AnimX == 'true' && CB_AnimY == 'true') {
		if (CB_ResizeTimer) {
			clearTimeout(CB_ResizeTimer)
		}
		CB_ShowImage();
		return
	} else {
		CB_ResizeTimer = setTimeout("CB_CheckResize()", 5)
	}
}
function CB_CheckResize2() {
	if (CB_AnimX == 'true' && CB_AnimY == 'true') {
		if (CB_ResizeTimer) {
			clearTimeout(CB_ResizeTimer)
		}
		CB_Gallery = '';
//		CB_iFr.src = CB_Clicked[1];
//		CB_iFr.src = CB_Clicked[1]+'&inclearbox=1';

		// bluelovers
		if (CB_Clicked[1].search(/inclearbox=true$/)<0) {
			if (CB_Clicked[1].search(/\?/)>0) CB_Clicked[1] += '&'; else CB_Clicked[1] += '?';
			CB_Clicked[1] += 'inclearbox=true';
		}

		CB_iFr.src = CB_Clicked[1];
		// bluelovers

		CB_Img.style.visibility = 'visible';
		CB_LoadingImg.style.visibility = 'hidden';
		CB_iFr.style.top = CB_ImgBorder + 'px';
		CB_iFr.style.left = CB_ImgBorder + 'px';
		CB_iFr.style.width = CB_ImgWidth + 'px';
		CB_iFr.style.height = CB_ImgHeight + 'px';
		if (CB_Clicked[2] && CB_Clicked[2] != 'null' && CB_Clicked[2] != null) {
			CB_Txt.innerHTML = CB_Clicked[2]
		} else {
			CB_Txt.innerHTML = CB_Clicked[1]
		}
		CB_Txt.innerHTML += ' ' + CB_ImgNumBracket.substring(0, 1) + '<a class="CB_TextNav" href="javascript:void(0)" onclick="CB_Close();">' + CB_NavTextCls + '</a>' + CB_ImgNumBracket.substring(1, 2);
		CB_HideContent.onclick = function() {
			CB_Close();
			return false
		};
		CB_ClearBox = 'be';
		CB_IsAnimating = 0;
		return
	} else {
		CB_ResizeTimer = setTimeout("CB_CheckResize2()", 5)
	}
}
function CB_ShowImage() {
	CB_Cls.onclick = function() {
		CB_Close()
	};
	CB_SlideS.onclick = function() {
		jQuery.clearbox.CB_SSStart();
		return false
	};
	CB_SlideP.onclick = function() {
		jQuery.clearbox.CB_SSPause();
		return false
	};
	CB_PrvNxt.display = 'block';
	if (CB_Animation != 'warp') {
		CB_Txt.innerHTML = '';
		CB_LoadingImg.style.visibility = 'hidden';
		CB_Img.src = CB_Gallery[CB_ActImgId][0];
		CB_Img.style.visibility = 'visible'
	}
	CB_Cls.style.display = 'block';
	CB_HideContent.onclick = function() {
		CB_Close();
		return false
	};
	CB_Prv.style.height = CB_ImgHeight + 'px';
	CB_Nxt.style.height = CB_ImgHeight + 'px';
	if (CB_Gallery[CB_ActImgId][1] && CB_Gallery[CB_ActImgId][1] != 'null' && CB_Gallery[CB_ActImgId][1] != null) {
		CB_Txt.innerHTML = CB_Gallery[CB_ActImgId][1]
	} else {
		if (CB_ShowImgURL == 'be') {
			CB_Txt.innerHTML = (CB_Gallery[CB_ActImgId][0].split('/'))[(CB_Gallery[CB_ActImgId][0].split('/').length) - 1]
		}
	}
	if (CB_ImgNum == 'be' && CB_Gallery.length > 2) {
		CB_Txt.innerHTML += ' ' + CB_ImgNumBracket.substring(0, 1) + CB_ActImgId + '/' + (CB_Gallery.length - 1) + CB_ImgNumBracket.substring(1, 2)
	}
	CB_PrevNext();
	CB_Txt.style.visibility = 'visible';
	if (CB_Gallery.length > 0) {
		CB_ImgWidthOld = CB_ImgWidth;
		CB_ImgHeightOld = CB_ImgHeight
	}
	if (CB_Gallery.length > 2) {
		if (CB_SS == 'pause') {
			jQuery(CB_SlideP).show();
			jQuery(CB_SlideB).show();
			jQuery.clearbox.CB_SlideShow()
		} else {
			CB_SlideS.style.display = 'block'
		}
	} else {
		CB_SS = 'start'
	}
	CB_ClearBox = 'be';
	CB_IsAnimating = 0;
	CB_ImgHd.style.width = CB_ImgWidth + 2 + 'px';
	CB_ImgHd.style.height = CB_ImgHeight + 2 + 'px';
	if (CB_ImgWidth < CB_preImages.width || CB_ImgHeight < CB_preImages.height) {
		CB_ShEt.style.visibility = 'visible';
		CB_Et.style.width = CB_ImgWidth + 2 + 'px'
	}
	if (CB_Gallery.length > 2) {
		CB_ShTh.style.visibility = 'visible';
		CB_Thm.style.width = CB_ImgWidth + 2 + 'px';
		var a = '';
		var b = 5;
		var c = 0;
		CB_AllThumbsWidth = 0;
		for (i = 1; i < CB_Gallery.length; i++) {
			CB_preThumbs = new Image();
			CB_preThumbs.src = CB_Gallery[i][2];
			c = Math.round(CB_preThumbs.width / CB_preThumbs.height * 50);
			if (c > 0) {} else {
				c = 50
			}
			CB_AllThumbsWidth += c
		}
		CB_AllThumbsWidth += (CB_Gallery.length - 2) * b;
		var d = 0;
		for (i = 1; i < CB_Gallery.length; i++) {
			CB_preThumbs = new Image();
			CB_preThumbs.src = CB_Gallery[i][2];
			a += '<a href="javascript:void(0)" onclick="if(CB_SSTimer){jQuery.clearbox.CB_SlideShowJump();}CB_LoadImage(' + i + ')"><img style="border: 0; left: ' + d + 'px;" " src="' + CB_Gallery[i][2] + '" height="50" class="CB_ThumbsImg" /></a>';
			d += Math.round(CB_preThumbs.width / CB_preThumbs.height * 50) + b
		}
		CB_Thm2.style.width = CB_AllThumbsWidth + 'px';
		CB_Thm2.innerHTML = a;
		CB_Thm2.style.marginLeft = (CB_ImgWidth - CB_AllThumbsWidth) / 2 + 'px'
	}

	// bluelovers
	jQuery.clearbox.CB_fix_center(CB_Img.style.width);
	// bluelovers

	return true
}
function CB_ShowEtc() {
	CB_ImgHd.style.visibility = 'visible';
	CB_Et.style.display = 'block';
	return
}
function CB_HideEtc() {
	CB_ImgHd.style.visibility = 'hidden';
	CB_Et.style.display = 'none';
	return
}
function CB_ShowThumbs() {
	CB_ImgHd.style.visibility = 'visible';
	CB_Thm.style.display = 'block';
	return
}
function CB_HideThumbs() {
	CB_ImgHd.style.visibility = 'hidden';
	CB_Thm.style.display = 'none';
	return
}

function CB_FullSize() {
	CB_Img.style.width = CB_ImgWidthOrig + 'px';
	CB_Img.style.height = CB_ImgHeightOrig + 'px';
	CB_ImgCont.style.height = CB_ImgHeightOrig + (2 * CB_ImgBorder) + 'px'
}


function CB_SetMargins() {
	CB_MarginL = parseInt(DocScrX - (CB_ImgWidth + (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd))) / 2);
	CB_MarginT = parseInt(DocScrY - (CB_ieRPBug + CB_ImgHeight + CB_TextH + (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd))) / 2);
	CB_Win.style.marginLeft = CB_MarginL + 'px';
	CB_Win.style.marginTop = (CB_MarginT - (FF_ScrollbarBug / 2)) + 'px';
	return
}
function CB_PrevNext() {
	if (CB_ActImgId > 1) {
		if (CB_Preload == 'be') {
			PreloadPrv = new Image();
			PreloadPrv.src = CB_Gallery[CB_ActImgId - 1][0]
		}
		if (CB_TextNav == 'be') {
			var a = CB_Txt.innerHTML;
			CB_Txt.innerHTML = '<a class="CB_TextNav" href="javascript:void(0)" onclick="if(CB_SSTimer){jQuery.clearbox.CB_SlideShowJump();}CB_LoadImage(' + (CB_ActImgId - 1) + ')" alt="&lt;">' + CB_NavTextPrv + '</a> ' + a
		}
		CB_Prv.style.display = 'block';
		CB_Prv.onclick = function() {
			if (CB_SSTimer) {
				jQuery.clearbox.CB_SlideShowJump()
			}
			CB_LoadImage(CB_ActImgId - 1);
			return false
		}
	}
	if (CB_ActImgId < CB_Gallery.length - 1) {
		if (CB_Preload == 'be') {
			PreloadNxt = new Image();
			PreloadNxt.src = CB_Gallery[CB_ActImgId + 1][0]
		}
		if (CB_TextNav == 'be') {
			CB_Txt.innerHTML += ' <a class="CB_TextNav" href="javascript:void(0)" onclick="if(CB_SSTimer){jQuery.clearbox.CB_SlideShowJump();}CB_LoadImage(' + (CB_ActImgId + 1) + ')" alt="&gt;">' + CB_NavTextNxt + '</a>'
		}
		CB_Nxt.style.display = 'block';
		CB_Nxt.onclick = function() {
			if (CB_SSTimer) {
				jQuery.clearbox.CB_SlideShowJump()
			}
			CB_LoadImage(CB_ActImgId + 1);
			return false
		}
	}
	return
}
function CB_Close() {
	CB_ImgHd.style.width = '0px';
	CB_ImgHd.style.height = '0px';
	CB_ImgHd.style.visibility = 'hidden';
	CB_ShTh.style.visibility = 'hidden';
	CB_ShEt.style.visibility = 'hidden';
	jQuery.clearbox.CB_SlideShowStop();
	CB_Txt.innerHTML = "";
	CB_Img.src = "";
	CB_ImgWidth = CB_WinBaseW;
	CB_ImgHeight = CB_WinBaseH - CB_TextH;
	CB_ImgCont.style.height = CB_ImgHeight + (2 * CB_ImgBorder) + 'px';
	CB_Img.style.display = 'none';
	CB_Win.style.visibility = 'hidden';
	CB_HideContent.onclick = "";
	CB_iFr.src = '';
	CB_iFr.style.top = '0px';
	CB_iFr.style.left = '0px';
	CB_iFr.style.width = '0px';
	CB_iFr.style.height = '0px';
	CB_ShowDocument();
	return
}
function CB_ShowDocument() {
	if (CB_Hide > 0) {
		jQuery(CB_HideContent).css('opacity', (CB_Hide / 100));

		CB_Hide -= CB_OpacityStep;
		CB_Blur = setTimeout("CB_ShowDocument()", 5)
	} else {
		CB_HideContent.style.visibility = 'hidden';
		CB_HideContent.style.width = '0px';
		CB_HideContent.style.height = '0px';
		if (CB_HideOpacity != 0) {
			clearTimeout(CB_Blur)
		}
		CB_ClearBox = 'ki';
		return
	}
}





(function($, undefined){
	var _this;

 	$.extend({
 		log : function(a){
			console.log(a);
		},
 		clearbox : {
 			defaults : {
				dir : '',
 			},
			init : function(options) {
				_this.setup(options);

				if (!jQuery.browser.msie) document.captureEvents(Event.MOUSEMOVE);
				$(document).keypress(_this.keyeven);

				CB_Init();
			},
			setup : function (options) {
				var options = $.extend(true, {}, _this.defaults, options);

				CB_AnimTimeout = parseInt(CB_AnimTimeout);
				if (CB_AnimTimeout < 5) {
					CB_AnimTimeout = 5
				}
				CB_BodyMarginLeft = parseInt(CB_BodyMarginLeft);
				if (CB_BodyMarginLeft < 0) {
					CB_BodyMarginLeft = 0
				}
				CB_BodyMarginRight = parseInt(CB_BodyMarginRight);
				if (CB_BodyMarginRight < 0) {
					CB_BodyMarginRight = 0
				}
				CB_BodyMarginTop = parseInt(CB_BodyMarginTop);
				if (CB_BodyMarginTop < 0) {
					CB_BodyMarginTop = 0
				}
				CB_BodyMarginBottom = parseInt(CB_BodyMarginBottom);
				if (CB_BodyMarginBottom < 0) {
					CB_BodyMarginBottom = 0
				}
				CB_HideOpacity = parseInt(CB_HideOpacity);
				if (CB_HideOpacity < 0 || CB_HideOpacity > 100) {
					CB_HideOpacity = 70
				}
				CB_OpacityStep = parseInt(CB_OpacityStep);
				if (CB_OpacityStep < 1 || CB_OpacityStep > CB_HideOpacity) {
					CB_OpacityStep = 10
				}
				CB_WinBaseW = parseInt(CB_WinBaseW);
				if (CB_WinBaseW < 25 || CB_WinBaseW > 1000) {
					CB_WinBaseW = 120
				}
				CB_WinBaseH = parseInt(CB_WinBaseH);
				if (CB_WinBaseH < 50 || CB_WinBaseH > 1000) {
					CB_WinBaseH = 110
				}
				CB_WinPadd = parseInt(CB_WinPadd);
				if (CB_WinPadd < 0) {
					CB_WinPadd = 5
				}
				if (CB_Animation != 'ki' && CB_Animation != 'normal' && CB_Animation != 'double' && CB_Animation != 'warp') {
					CB_Animation = 'double'
				}
				CB_Jump_X = parseInt(CB_Jump_X);
				if (CB_Jump_X < 1 || CB_Jump_X > 99) {
					CB_Jump_X = 50
				}
				CB_Jump_Y = parseInt(CB_Jump_Y);
				if (CB_Jump_Y < 1 || CB_Jump_Y > 99) {
					CB_Jump_Y = 50
				}
				CB_ImgBorder = parseInt(CB_ImgBorder);
				if (CB_ImgBorder < 0) {
					CB_ImgBorder = 1
				}
				CB_Padd = parseInt(CB_Padd);
				if (CB_Padd < 0) {
					CB_Padd = 2
				}
				if (CB_ShowImgURL != 'be' && CB_ShowImgURL != 'ki') {
					CB_ShowImgURL = 'ki'
				}
				CB_PadT = parseInt(CB_PadT);
				if (CB_PadT < 0) {
					CB_PadT = 10
				}
				CB_RoundPix = parseInt(CB_RoundPix);
				if (CB_RoundPix < 0) {
					CB_RoundPix = 12
				}
				CB_TextH = parseInt(CB_TextH);
				if (CB_TextH < 25) {
					CB_TextH = 40
				}
				CB_FontSize = parseInt(CB_FontSize);
				if (CB_FontSize < 6) {
					CB_FontSize = 13
				}
				if (CB_ImgNum != 'be' && CB_ImgNum != 'ki') {
					CB_ImgNum = 'be'
				}
				CB_SlShowTime = parseInt(CB_SlShowTime);
				if (CB_SlShowTime < 1) {
					CB_SlShowTime = 5
				}
				CB_SlShowTime *= 1000;
				if (CB_CheckDuplicates != 'be' && CB_CheckDuplicates != 'ki') {
					CB_CheckDuplicates = 'ki'
				}
				if (CB_Preload != 'be' && CB_Preload != 'ki') {
					CB_Preload = 'be'
				}
			},
			log : function (s) {
				$.log(s);
			},
			keyeven : function(event){
				var b;

				if (event.keyCode) b = event.keyCode;
				else if (event.which) b = event.which;

				var c = String.fromCharCode(b);

				$.log([b, c, event]);

				var stop = 0;

				if (CB_ClearBox == 'be') {
					if (CB_ActImgId > 1 && (c == "%" || b == 37 || b == 52 || b == 38 || b == 33)) {
						if (CB_SSTimer) {
							jQuery.clearbox.CB_SlideShowJump()
						}
						CB_LoadImage(CB_ActImgId - 1);

						stop = true;
					}
					if (CB_ActImgId < CB_Gallery.length - 1 && (c == "'" || b == 39 || b == 54 || b == 40 || b == 34)) {
						if (CB_SSTimer) {
							jQuery.clearbox.CB_SlideShowJump()
						}
						CB_LoadImage(CB_ActImgId + 1);

						stop = true;
					}
					if ((c == " " || b == 32) && CB_IsAnimating == 0) {
						if (CB_Gallery.length < 3) {
							stop = true;
						}
						if (CB_SS == 'start') {
							jQuery.clearbox.CB_SSStart();
							stop = true;
						} else {
							jQuery.clearbox.CB_SSPause();
							stop = true;
						}
					}
					if (c == "" || b == 27) {
						CB_Close();
						stop = true;
					}
					if (b == 13) {
						stop = true;
					}
				} else {
					if (CB_IsAnimating == 1 && (c == " " || b == 32 || b == 13)) {
						stop = true;
					}
				}

				if (b == 38 || b == 40 || b == 33 || b == 34) {
					stop = 2;
				}

				if (stop) {
					event.preventDefault();
					if (stop > 1) event.stopPropagation();
				}
			},
			getScrollPosition : function getScrollPosition() {
				this.DocScrX = 0;
				this.DocScrY = 0;
				/*
				if (typeof(window.pageYOffset) == 'number') {
					DocScrY = window.pageYOffset;
					DocScrX = window.pageXOffset
				} else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
					DocScrY = document.body.scrollTop;
					DocScrX = document.body.scrollLeft
				} else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
					DocScrY = document.documentElement.scrollTop;
					DocScrX = document.documentElement.scrollLeft
				}
				*/
				DocScrX = jQuery(document).scrollLeft();
				DocScrY = jQuery(document).scrollTop();

				this.log(['getScrollPosition', DocScrX, DocScrY]);

				return
			},
			getMouseXY : function (e) {
				if (CB_AllThumbsWidth > CB_ImgWidth) {
					if (jQuery.browser.msie) {
						tempX = event.clientX
					} else {
						tempX = e.pageX
					}
					if (tempX < 0) {
						tempX = 0
					}
					CB_Thm2.style.marginLeft = ((BrSizeX - CB_ImgWidth) / 2 - tempX) / (CB_ImgWidth / (CB_AllThumbsWidth - CB_ImgWidth)) + 'px'
				}
			},
			CB_SlideShow : function() {
				if (CB_SlShowTimer > CB_jj) {
					CB_SSTimer = setTimeout("jQuery.clearbox.CB_SlideShow()", 25);
					CB_jj += 25;
					CB_SlideBW += (CB_ImgWidth - 44) / (CB_SlShowTimer / 25);
					CB_SlideB.style.width = CB_SlideBW + 'px'
				} else {
					clearTimeout(CB_SSTimer);
					CB_SlideBW = 0;
					CB_SlideB.style.width = CB_SlideBW + 'px';
					if (CB_ActImgId == CB_Gallery.length - 1) {
						CB_LoadImage(1)
					} else {
						CB_LoadImage(CB_ActImgId + 1)
					}
					return
				}
			},
			CB_SlideShowStop : function () {
				CB_SS = 'start';
				jQuery.clearbox.CB_SlideShowJump();
			},
			CB_SlideShowJump : function () {
				if (CB_SSTimer) {
					clearTimeout(CB_SSTimer);
				}
				CB_jj = 0;
				CB_SlideBW = 0;
				jQuery(CB_SlideB).hide();
			},
			CB_SSStart : function () {
				jQuery(CB_SlideS).hide();
				jQuery(CB_SlideP).show();
				CB_SS = 'pause';
				jQuery(CB_SlideB).show();
				jQuery.clearbox.CB_SlideShow();
			},
			CB_SSPause : function () {
				jQuery(CB_SlideP).hide();
				jQuery(CB_SlideS).show();
				jQuery.clearbox.CB_SlideShowStop();
			},
			getDocumentSize : function () {
				this.DocSizeX = 0;
				this.DocSizeY = 0;
				/*
				if (window.innerWidth && window.scrollMaxX) {
					DocSizeX = window.innerWidth + window.scrollMaxX;
					DocSizeY = window.innerHeight + window.scrollMaxY
				} else if (document.body.scrollWidth > document.body.offsetWidth) {
					DocSizeX = document.body.scrollWidth;
					DocSizeY = document.body.scrollHeight
				} else {
					DocSizeX = document.body.offsetWidth;
					DocSizeY = document.body.offsetHeight
				}
				if (jQuery.browser.msie || jQuery.browser.opera) {
					DocSizeX = document.body.scrollWidth;
					DocSizeY = document.body.scrollHeight
				}
				if (jQuery.browser.firefox || navigator.userAgent.indexOf("Netscape") != -1) {
					DocSizeX = BrSizeX + window.scrollMaxX;
					DocSizeY = BrSizeY + window.scrollMaxY
				}
				*/

				DocSizeX = jQuery(document).width();
				DocSizeY = jQuery(document).height();

				jQuery.clearbox.log(['DocSizeX', DocSizeX, jQuery(document).width(), jQuery(document).outerWidth()]);
				jQuery.clearbox.log(['DocSizeY', DocSizeY, jQuery(document).height(), jQuery(document).outerHeight()]);

				return
			},
			getBrowserSize : function () {
				this.BrSizeX = 0;
				this.BrSizeY = 0;
				/*
				if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
					BrSizeX = document.documentElement.clientWidth;
					BrSizeY = document.documentElement.clientHeight
				} else if (typeof(window.innerWidth) == 'number') {
					BrSizeX = window.innerWidth;
					BrSizeY = window.innerHeight
				} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
					BrSizeX = document.body.clientWidth;
					BrSizeY = document.body.clientHeight;
					return
				}
				if (jQuery.browser.opera) {
					BrSizeX = document.documentElement.clientWidth;
					BrSizeY = document.body.clientHeight
				}
				if (document.compatMode != undefined) {
					if (document.compatMode.match('Back') && jQuery.browser.firefox) {
						BrSizeY = document.body.clientHeight
					}
				}
				*/

				jQuery.clearbox.log(['BrSizeX', BrSizeX, jQuery(window).width()]);
				jQuery.clearbox.log(['BrSizeY', BrSizeY, jQuery(window).height()]);

				return
			},
			CB_SetAllPositions : function () {
				jQuery.clearbox.getBrowserSize();
				jQuery.clearbox.getDocumentSize();
				jQuery.clearbox.getScrollPosition();
				if (BrSizeY > DocSizeY) {
					DocSizeY = BrSizeY
				}
				if ((navigator.userAgent.indexOf("Netscape") != -1 || jQuery.browser.firefox) && BrSizeX != DocSizeX) {
					FF_ScrollbarBug = window.scrollMaxY + window.innerHeight - DocSizeY
				} else {
					FF_ScrollbarBug = 0
				}
				CB_SetMargins();
				if (CB_BodyMarginX == 0) {
					if (DocSizeX > BrSizeX) {
						CB_HideContent.style.width = DocSizeX + 'px'
					} else {
						CB_HideContent.style.width = BrSizeX + 'px'
					}
				} else {
					CB_HideContent.style.width = DocSizeX + CB_BodyMarginX + 'px'
				}
				CB_HideContent.style.height = BrSizeY + DocScrY + 'px';
				CB_HideContent.style.visibility = 'visible';
				return
			},
			CB_fix_center : function (w) {
				var _w = CB_ImgCont.offsetWidth || CB_ImgCont.width;

				w = parseInt(w);

				if (_w > w) {
					CB_Img.style.marginLeft = Math.floor((_w - w) / 2) + 'px';
				} else {
					CB_Img.style.marginLeft = 0;
				}

				/*
				console.log(
					[
						CB_Img.style.marginLeft,
						CB_ImgCont.width,
						CB_ImgCont.offsetWidth,
						_w,
						w,
					]
				);
				*/
			},
			CB_FitToBrowser : function () {
				if (CB_ImgWidth > BrSizeX - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd))) {
					CB_ImgWidth = BrSizeX - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd));
					CB_ImgHeight = Math.round(CB_ImgWidth / CB_ImgRate)
				}
				if (CB_ImgHeight > BrSizeY - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd)) - CB_TextH) {
					CB_ImgHeight = BrSizeY - (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd + CB_WinPadd)) - CB_TextH;
					CB_ImgWidth = Math.round(CB_ImgRate * CB_ImgHeight)
				}
				return
			},
			CB_WindowResizeX : function () {
				if (CB_ImgWidth == CB_ImgWidthOld) {
					if (CB_TimerX) {
						clearTimeout(CB_TimerX)
					}
					if (CB_Animation == 'normal') {
						CB_AnimX = 'true';
						CB_WindowResizeY()
					} else {
						CB_AnimX = 'true'
					}
					return
				} else {
					if (CB_ImgWidth < CB_ImgWidthOld) {
						if (CB_ImgWidthOld < CB_ImgWidth + 100 && CB_Jump_X > 20) {
							CB_JumpX = 20
						}
						if (CB_ImgWidthOld < CB_ImgWidth + 60 && CB_Jump_X > 10) {
							CB_JumpX = 10
						}
						if (CB_ImgWidthOld < CB_ImgWidth + 30 && CB_Jump_X > 5) {
							CB_JumpX = 5
						}
						if (CB_ImgWidthOld < CB_ImgWidth + 15 && CB_Jump_X > 2) {
							CB_JumpX = 2
						}
						if (CB_ImgWidthOld < CB_ImgWidth + 4) {
							CB_JumpX = 1
						}
						CB_ImgWidthOld -= CB_JumpX
					} else {
						if (CB_ImgWidthOld > CB_ImgWidth - 100 && CB_Jump_X > 20) {
							CB_JumpX = 20
						}
						if (CB_ImgWidthOld > CB_ImgWidth - 60 && CB_Jump_X > 10) {
							CB_JumpX = 10
						}
						if (CB_ImgWidthOld > CB_ImgWidth - 30 && CB_Jump_X > 50) {
							CB_JumpX = 5
						}
						if (CB_ImgWidthOld > CB_ImgWidth - 15 && CB_Jump_X > 2) {
							CB_JumpX = 2
						}
						if (CB_ImgWidthOld > CB_ImgWidth - 4) {
							CB_JumpX = 1
						}
						CB_ImgWidthOld += CB_JumpX
					}
					CB_Img.style.width = CB_ImgWidthOld + 'px';
					CB_MarginL = parseInt(DocScrX - (CB_ImgWidthOld + (2 * (CB_RoundPix + CB_ImgBorder + CB_Padd))) / 2);
					CB_Win.style.marginLeft = CB_MarginL + 'px';
					CB_TimerX = setTimeout("jQuery.clearbox.CB_WindowResizeX()", CB_AnimTimeout);
				}
			}
 		},
 	});

 	_this = $.clearbox;

 	function intval(n) {
 		return parseInt(n);
 	}
})(jQuery);
