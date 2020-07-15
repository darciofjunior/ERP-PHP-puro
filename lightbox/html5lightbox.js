//Importa o Estilo de CSS e a Fun��o Jquery JS...
document.write("<link href = '/erp/albafer/lightbox/jquery.js' type = 'text/css' rel = 'stylesheet'>")

//Mover Div ...
var Drag = {
    obj : null,

    init : function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef, bSwapVertRef, fXMapper, fYMapper) {
        o.onmousedown	= Drag.start;
        o.hmode         = bSwapHorzRef ? false : true;
        o.vmode         = bSwapVertRef ? false : true;

        o.root = oRoot && oRoot != null ? oRoot : o ;

        if (o.hmode  && isNaN(parseInt(o.root.style.left  ))) o.root.style.left   = "0px";
        if (o.vmode  && isNaN(parseInt(o.root.style.top   ))) o.root.style.top    = "0px";
        if (!o.hmode && isNaN(parseInt(o.root.style.right ))) o.root.style.right  = "0px";
        if (!o.vmode && isNaN(parseInt(o.root.style.bottom))) o.root.style.bottom = "0px";

        o.minX	= typeof minX != 'undefined' ? minX : null;
        o.minY	= typeof minY != 'undefined' ? minY : null;
        o.maxX	= typeof maxX != 'undefined' ? maxX : null;
        o.maxY	= typeof maxY != 'undefined' ? maxY : null;

        o.xMapper = fXMapper ? fXMapper : null;
        o.yMapper = fYMapper ? fYMapper : null;

        o.root.onDragStart	= new Function();
        o.root.onDragEnd	= new Function();
        o.root.onDrag		= new Function();
    },

    start : function(e) {
        var o = Drag.obj = this;
        e = Drag.fixE(e);
        var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
        var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
        o.root.onDragStart(x, y);

        o.lastMouseX	= e.clientX;
        o.lastMouseY	= e.clientY;

        if (o.hmode) {
            if (o.minX != null)	o.minMouseX	= e.clientX - x + o.minX;
            if (o.maxX != null)	o.maxMouseX	= o.minMouseX + o.maxX - o.minX;
        }else {
            if (o.minX != null) o.maxMouseX = -o.minX + e.clientX + x;
            if (o.maxX != null) o.minMouseX = -o.maxX + e.clientX + x;
        }

        if (o.vmode) {
            if (o.minY != null)	o.minMouseY	= e.clientY - y + o.minY;
            if (o.maxY != null)	o.maxMouseY	= o.minMouseY + o.maxY - o.minY;
        }else {
            if (o.minY != null) o.maxMouseY = -o.minY + e.clientY + y;
            if (o.maxY != null) o.minMouseY = -o.maxY + e.clientY + y;
        }
        document.onmousedown    = function() {ocultar_div()}
        document.onmousemove    = Drag.drag;
        document.onmouseup      = Drag.end;
        return false;
    },

    drag : function(e) {
        e = Drag.fixE(e);
        var o = Drag.obj;

        var ey	= e.clientY;
        var ex	= e.clientX;
        var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
        var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
        var nx, ny;

        if (o.minX != null) ex = o.hmode ? Math.max(ex, o.minMouseX) : Math.min(ex, o.maxMouseX);
        if (o.maxX != null) ex = o.hmode ? Math.min(ex, o.maxMouseX) : Math.max(ex, o.minMouseX);
        if (o.minY != null) ey = o.vmode ? Math.max(ey, o.minMouseY) : Math.min(ey, o.maxMouseY);
        if (o.maxY != null) ey = o.vmode ? Math.min(ey, o.maxMouseY) : Math.max(ey, o.minMouseY);

        nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
        ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));

        if (o.xMapper)		nx = o.xMapper(y)
        else if (o.yMapper)	ny = o.yMapper(x)

        Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
        Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
        Drag.obj.lastMouseX	= ex;
        Drag.obj.lastMouseY	= ey;

        Drag.obj.root.onDrag(nx, ny);
        return false;
    },

    end : function() {
        document.onmousemove = null;
        document.onmouseup   = null;aparecer_div();
        Drag.obj.root.onDragEnd(	parseInt(Drag.obj.root.style[Drag.obj.hmode ? "left" : "right"]),
                                                                parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]));
        Drag.obj = null;
    },

    fixE : function(e) {
        if (typeof e == 'undefined') e = window.event;
        if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
        if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
        return e;
    }
};

/**************************************************************************************/
//Essas fun��es tem a id�ia de facilitar na hora de Clique Arraste do "DragDrop" ...
function ocultar_div() {
    //Essa DIV "html5lightbox-web" equivale ao Iframe que � carregado dentro da DIV "html5-lightbox-box" Principal ...
    document.getElementById('html5lightbox-web').style.visibility   = 'hidden';
    document.getElementById('html5-elem-box').style.height          = 20+'px';//Essa DIV "html5-elem-box" est� dentro da DIV "html5lightbox-web" Principal ...
}

function aparecer_div() {
    //Essa DIV "html5lightbox-web" equivale ao Iframe que � carregado dentro da DIV "html5-lightbox-box" Principal ...
    document.getElementById('html5lightbox-web').style.visibility   = 'visible'
    document.getElementById('html5-elem-box').style.height          = document.getElementById('html5-lightbox').style.height//Reassume o Tamanho da DIV "html5-lightbox-box" Principal ...
}
/**************************************************************************************/

/** HTML5 LightBox - jQuery Image and Video LightBox Plugin
 * Copyright 2014 Magic Hills Pty Ltd All Rights Reserved
 * Website: http://html5box.com
 * Version 3.5 
 */
(function() {
    var scripts = document.getElementsByTagName("script");
    var jsFolder = "";
    for (var i = 0; i < scripts.length; i++)
        if (scripts[i].src && scripts[i].src.match(/html5lightbox\.js/i))
            jsFolder = scripts[i].src.substr(0, scripts[i].src.lastIndexOf("/") + 1);
    var loadjQuery = false;
    if (typeof jQuery == "undefined")
        loadjQuery = true;
    else {
        var jVersion = jQuery.fn.jquery.split(".");
        if (jVersion[0] < 1 || jVersion[0] == 1 && jVersion[1] < 6)
            loadjQuery = true
    }
    if (loadjQuery) {
        var head = document.getElementsByTagName("head")[0];
        var script = document.createElement("script");
        script.setAttribute("type", "text/javascript");
        if (script.readyState)
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    loadHtml5LightBox(jsFolder)
                }
            };
        else
            script.onload = function() {
                loadHtml5LightBox(jsFolder)
            };
        script.setAttribute("src", jsFolder + "jquery.js");
        head.appendChild(script)
    } else
        loadHtml5LightBox(jsFolder)
})();
function loadHtml5LightBox(jsFolder) {
    (function($) {
        $.fn.html5lightbox = function(options) {
            var inst = this;
            inst.options = jQuery.extend({freelink: "http://html5box.com/", autoplay: true, html5player: true, responsive: true, shownavigation: true, thumbwidth: 96, thumbheight: 72, thumbgap: 4, thumbmargin: 12, thumbborder: 0, thumbbordercolor: "#fff", thumbhighlightborder: 1, thumbhighlightbordercolor: "#fff", thumbopacity: 1, navbuttonwidth: 32, overlaybgcolor: "#000", overlayopacity: 0.7, bgcolor: "#fff", bordersize: 8, borderradius: 0, bordermargin: 16, 
                barautoheight: true, barheight: 48, loadingwidth: 64, loadingheight: 64, resizespeed: 400, fadespeed: 400, jsfolder: jsFolder, skinsfoldername: "skins/default/", loadingimage: "lightbox-loading.gif", nextimage: "lightbox-next.png", previmage: "lightbox-prev.png", closeimage: "lightbox-close.png", playvideoimage: "lightbox-playvideo.png", titlebgimage: "lightbox-titlebg.png", navarrowsprevimage: "nav-arrows-prev.png", navarrowsnextimage: "nav-arrows-next.png", titlestyle: "bottom", titlecss: "{color:#333; font-size:14px; font-family:Armata,sans-serif,Arial; overflow:hidden; text-align:left}",
                /*Na estilo de css "titlebottom:", adicionei a propriedade visibility:hidden; p/ nunca mostrar 
                nenhum Texto no rodap� da DIV caso seja colocada a propriedade title na tag <a href>*/
                titleinside: {titlecss: "{color:#fff; font-size:14px; font-family:Armata,sans-serif,Arial; overflow:hidden; text-align:left;}"}, titlebottom: {titlecss: "{color:#333; font-size:14px; font-family:Armata,sans-serif,Arial; overflow:hidden; visibility:hidden; text-align:left;}"}, errorwidth: 280, errorheight: 48, errorcss: "{text-align:center; color:#ff0000; font-size:14px; font-family:Arial, sans-serif;}", supportesckey: true, supportarrowkeys: true, version: "3.3", stamp: true, freemark: "", watermark: "", watermarklink: ""},//Esse atributo freemark: "Colocava uma T�tulo nessa DIV com o endere�o p/ abrir o Site" -> Default: freemark: "hmtamgli5cboxh.iclolms" 
            options);
            if (typeof html5lightbox_options != "undefined" && html5lightbox_options)
                jQuery.extend(inst.options, html5lightbox_options);
            inst.options.htmlfolder = window.location.href.substr(0, window.location.href.lastIndexOf("/") + 1);
            inst.options.skinsfolder = inst.options.skinsfoldername;
            if (inst.options.skinsfolder.length > 0 && inst.options.skinsfolder[inst.options.skinsfolder.length - 1] != "/")
                inst.options.skinsfolder += "/";
            if (inst.options.skinsfolder.charAt(0) != "/" && inst.options.skinsfolder.substring(0, 5) != "http:" &&
                    inst.options.skinsfolder.substring(0, 6) != "https:")
                inst.options.skinsfolder = inst.options.jsfolder + inst.options.skinsfolder;
            var i;
            var l;
            var mark = inst.options.freemark;
            for (i = 1; i <= 5; i++)
                mark = mark.slice(0, i) + mark.slice(i + 1);
            l = mark.length;
            for (i = 0; i < 5; i++)
                mark = mark.slice(0, l - 9 + i) + mark.slice(l - 8 + i);
            inst.options.freemark = mark;
            if (inst.options.htmlfolder.indexOf(inst.options.freemark) != -1)
                inst.options.stamp = false;
            inst.options.navheight = 0;
            inst.options.thumbheight += 2 * Math.max(inst.options.thumbborder, inst.options.thumbhighlightborder);
            inst.options.thumbgap += 2 * Math.max(inst.options.thumbborder, inst.options.thumbhighlightborder);
            inst.options.types = ["IMAGE", "FLASH", "VIDEO", "YOUTUBE", "VIMEO", "PDF", "MP3", "WEB", "FLV"];
            inst.elemArray = new Array;
            inst.options.curElem = -1;
            inst.options.flashInstalled = false;
            try {
                if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash"))
                    inst.options.flashInstalled = true
            } catch (e) {
                if (navigator.mimeTypes["application/x-shockwave-flash"])
                    inst.options.flashInstalled = true
            }
            inst.options.html5VideoSupported = !!document.createElement("video").canPlayType;
            inst.options.isChrome = navigator.userAgent.match(/Chrome/i) != null;
            inst.options.isFirefox = navigator.userAgent.match(/Firefox/i) != null;
            inst.options.isOpera = navigator.userAgent.match(/Opera/i) != null || navigator.userAgent.match(/OPR\//i) != null;
            inst.options.isSafari = navigator.userAgent.match(/Safari/i) != null;
            inst.options.isIE = navigator.userAgent.match(/MSIE/i) != null && !inst.options.isOpera;
            inst.options.isIE9 = navigator.userAgent.match(/MSIE 9/i) != null && !inst.options.isOpera;
            inst.options.isIE8 = navigator.userAgent.match(/MSIE 8/i) !=
                    null && !inst.options.isOpera;
            inst.options.isIE7 = navigator.userAgent.match(/MSIE 7/i) != null && !inst.options.isOpera;
            inst.options.isIE6 = navigator.userAgent.match(/MSIE 6/i) != null && !inst.options.isOpera;
            inst.options.isIE678 = inst.options.isIE6 || inst.options.isIE7 || inst.options.isIE8;
            inst.options.isIE6789 = inst.options.isIE6 || inst.options.isIE7 || inst.options.isIE8 || inst.options.isIE9;
            inst.options.isAndroid = navigator.userAgent.match(/Android/i) != null;
            inst.options.isIPad = navigator.userAgent.match(/iPad/i) !=
                    null;
            inst.options.isIPhone = navigator.userAgent.match(/iPod/i) != null || navigator.userAgent.match(/iPhone/i) != null;
            inst.options.isIOS = inst.options.isIPad || inst.options.isIPhone;
            inst.options.isMobile = inst.options.isAndroid || inst.options.isIPad || inst.options.isIPhone;
            inst.options.isIOSLess5 = inst.options.isIPad && inst.options.isIPhone && (navigator.userAgent.match(/OS 4/i) != null || navigator.userAgent.match(/OS 3/i) != null);
            inst.options.supportCSSPositionFixed = !inst.options.isIE6 && !inst.options.isIOSLess5;
            inst.options.resizeTimeout =
                    -1;
            if (inst.options.isMobile)
                inst.options.autoplay = false;
            inst.init = function() {
                inst.showing = false;
                inst.readData();
                inst.createMarkup();
                inst.supportKeyboard()
            };
            var ELEM_TYPE = 0, ELEM_HREF = 1, ELEM_TITLE = 2, ELEM_GROUP = 3, ELEM_WIDTH = 4, ELEM_HEIGHT = 5, ELEM_HREF_WEBM = 6, ELEM_HREF_OGG = 7, ELEM_THUMBNAIL = 8;
            inst.readData = function() {
                inst.each(function() {
                    if (this.nodeName.toLowerCase() != "a" && this.nodeName.toLowerCase() != "area")
                        return;
                    var $this = $(this);
                    var fileType = inst.checkType($this.attr("href"));
                    if (fileType < 0)
                        return;
                    for (var i =
                            0; i < inst.elemArray.length; i++)
                        if ($this.attr("href") == inst.elemArray[i][ELEM_HREF])
                            return;
                    inst.elemArray.push(new Array(fileType, $this.attr("href"), $this.attr("title"), $this.data("group"), $this.data("width"), $this.data("height"), $this.data("webm"), $this.data("ogg"), $this.data("thumbnail")))
                })
            };
            inst.createMarkup = function() {
                var fontRef = ("https:" == document.location.protocol ? "https" : "http") + "://fonts.googleapis.com/css?family=Armata";
                var fontLink = document.createElement("link");
                fontLink.setAttribute("rel",
                        "stylesheet");
                fontLink.setAttribute("type", "text/css");
                fontLink.setAttribute("href", fontRef);
                document.getElementsByTagName("head")[0].appendChild(fontLink);
                if (inst.options.titlestyle == "inside")
                    inst.options.titlecss = inst.options.titleinside.titlecss;
                else if (inst.options.titlestyle == "bottom")
                    inst.options.titlecss = inst.options.titlebottom.titlecss;
                var styleCss = "#html5-text " + inst.options.titlecss;
                styleCss += ".html5-error " + inst.options.errorcss;
                $("head").append("<style type='text/css'>" + styleCss + "</style>");
                inst.$lightbox = jQuery("<div id='html5-lightbox' style='display:none;top:0px;left:0px;width:100%;height:100%;z-index:9999998;'>" + "<div id='html5-lightbox-overlay' style='display:block;position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:" + inst.options.overlaybgcolor + ";opacity:" + inst.options.overlayopacity + ";filter:alpha(opacity=" + Math.round(inst.options.overlayopacity * 100) + ");'></div>" + "<div id='html5-lightbox-box' style='display:block;position:relative;margin:0px auto;'>" + "<div id='html5-elem-box' style='display:block;position:relative;margin:0px auto;text-align:center;overflow:hidden;'>" +
                        "<div id='html5-elem-wrap' style='display:block;position:relative;margin:0px auto;text-align:center;background-color:" + inst.options.bgcolor + ";'>" + "<div id='html5-loading' style='display:none;position:absolute;top:0px;left:0px;text-align:center;width:100%;height:100%;background:url(\"" + inst.options.skinsfolder + inst.options.loadingimage + "\") no-repeat center center;'></div>" + "<div id='html5-error' class='html5-error' style='display:none;position:absolute;padding:" + inst.options.bordersize + "px;text-align:center;width:" +
                        inst.options.errorwidth + "px;height:" + inst.options.errorheight + "px;'>" + "The requested content cannot be loaded.<br />Please try again later." + "</div>" + "<div id='html5-image' style='display:none;position:absolute;top:0px;left:0px;padding:" + inst.options.bordersize + "px;text-align:center;'></div>" + "<div id='html5-next' style='display:none;cursor:pointer;position:absolute;right:" + inst.options.bordersize + "px;top:50%;margin-top:-16px;'><img src='" + inst.options.skinsfolder + inst.options.nextimage + "'></div>" +
                        "<div id='html5-prev' style='display:none;cursor:pointer;position:absolute;left:" + inst.options.bordersize + "px;top:50%;margin-top:-16px;'><img src='" + inst.options.skinsfolder + inst.options.previmage + "'></div>" + "</div>" + "</div>" + "<div id='html5-watermark' style='display:none;position:absolute;left:" + String(inst.options.bordersize + 2) + "px;top:" + String(inst.options.bordersize + 2) + "px;'></div>" + "</div>" + "</div>");
                inst.$lightbox.css({position: inst.options.supportCSSPositionFixed ? "fixed" : "absolute"});
                inst.$lightbox.appendTo("body");
                inst.$lightboxBox = $("#html5-lightbox-box", inst.$lightbox);
                inst.$elem = $("#html5-elem-box", inst.$lightbox);
                inst.$elemWrap = $("#html5-elem-wrap", inst.$lightbox);
                inst.$loading = $("#html5-loading", inst.$lightbox);
                inst.$error = $("#html5-error", inst.$lightbox);
                inst.$image = $("#html5-image", inst.$lightbox);
                inst.$next = $("#html5-next", inst.$lightbox);
                inst.$prev = $("#html5-prev", inst.$lightbox);
                var elemText = "<div id='html5-elem-data-box' style='display:none;'><div id='html5-text' style='display:block;overflow:hidden;'></div></div>";
                inst.$elem.append(elemText);
                inst.$elemData = $("#html5-elem-data-box", inst.$lightbox);
                inst.$text = $("#html5-text", inst.$lightbox);
                if (inst.options.borderradius > 0)
                    if (inst.options.titlestyle == "inside")
                        inst.$elemWrap.css({"border-radius": inst.options.borderradius + "px", "-moz-border-radius": inst.options.borderradius + "px", "-webkit-border-radius": inst.options.borderradius + "px"});
                    else {
                        inst.$elemWrap.css({"border-top-left-radius": inst.options.borderradius + "px", "-moz-top-left-border-radius": inst.options.borderradius +
                                    "px", "-webkit-top-left-border-radius": inst.options.borderradius + "px", "border-top-right-radius": inst.options.borderradius + "px", "-moz-top-right-border-radius": inst.options.borderradius + "px", "-webkit-top-right-border-radius": inst.options.borderradius + "px"});
                        inst.$elemData.css({"border-bottom-left-radius": inst.options.borderradius + "px", "-moz-top-bottom-border-radius": inst.options.borderradius + "px", "-webkit-bottom-left-border-radius": inst.options.borderradius + "px", "border-bottom-right-radius": inst.options.borderradius +
                                    "px", "-moz-bottom-right-border-radius": inst.options.borderradius + "px", "-webkit-bottom-right-border-radius": inst.options.borderradius + "px"})
                    }
                if (inst.options.titlestyle == "inside") {
                    inst.$elemData.css({position: "absolute", margin: inst.options.bordersize + "px", bottom: 0, left: 0, "background-color": "#333", "background-color":"rgba(51, 51, 51, 0.6)"});
                    inst.$text.css({padding: inst.options.bordersize + "px " + 2 * inst.options.bordersize + "px"})
                } else {
                    inst.$elemData.css({position: "relative", width: "100%", height: inst.options.barautoheight ?
                                "auto" : inst.options.barheight + "px", "padding": "0 0 " + inst.options.bordersize + "px" + " 0", "background-color": inst.options.bgcolor, "text-align": "left"});
                    inst.$text.css({"margin": "0 " + inst.options.bordersize + "px"})
                }
                inst.$lightboxBox.append("<div id='html5-close' style='display:none;cursor:pointer;position:absolute;top:0;right:0;margin-top:-16px;margin-right:-16px;'><img src='" + inst.options.skinsfolder + inst.options.closeimage + "'></div>");
                inst.$close = $("#html5-close", inst.$lightbox);
                inst.$watermark = $("#html5-watermark",
                        inst.$lightbox);
                if (inst.options.stamp)
                    inst.$watermark.html("<a href='" + inst.options.freelink + "' style='text-decoration:none'><div style='display:block;width:100px;height:20px;text-align:center;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;background-color:#fff;color:#333;font:12px Armata,sans-serif,Arial;'><div style='line-height:20px;'>" + inst.options.freemark + "</div></div></a>");
                else if (inst.options.watermark) {
                    var html = "<img src='" + inst.options.watermark + "' style='border:none;'/>";
                    if (inst.options.watermarklink)
                        html = "<a href='" + inst.options.watermarklink + "' target='_blank'>" + html + "</a>";
                    inst.$watermark.html(html)
                }
                //$("#html5-lightbox-overlay", inst.$lightbox).click(inst.finish);//N�o fecha a DIV quando se clica do lado de fora ...
                inst.$close.click(inst.finish);
                inst.$next.click(function() {
                    inst.gotoSlide(-1)
                });
                inst.$prev.click(function() {
                    inst.gotoSlide(-2)
                });
                $(window).resize(function() {
                    if (!inst.options.isMobile) {
                        clearTimeout(inst.options.resizeTimeout);
                        inst.options.resizeTimeout = setTimeout(function() {
                            inst.resizeWindow()
                        }, 500)
                    }
                });
                $(window).scroll(function() {
                    inst.scrollBox()
                });
                $(window).bind("orientationchange", function(e) {
                    if (inst.options.isMobile)
                        inst.resizeWindow()
                });
                if (inst.options.isIPhone) {
                    inst.options.windowInnerHeight = window.innerHeight;
                    setInterval(function() {
                        if (inst.options.windowInnerHeight != window.innerHeight) {
                            inst.options.windowInnerHeight = window.innerHeight;
                            inst.resizeWindow()
                        }
                    }, 500)
                }
                inst.enableSwipe()
            };
            inst.calcNextPrevElem = function() {
                inst.options.nextElem = -1;
                inst.options.prevElem = -1;
                var j, curGroup = inst.elemArray[inst.options.curElem][ELEM_GROUP];
                if (curGroup !=
                        undefined && curGroup != null) {
                    for (j = inst.options.curElem + 1; j < inst.elemArray.length; j++)
                        if (inst.elemArray[j][ELEM_GROUP] == curGroup) {
                            inst.options.nextElem = j;
                            break
                        }
                    if (inst.options.nextElem < 0)
                        for (j = 0; j < inst.options.curElem; j++)
                            if (inst.elemArray[j][ELEM_GROUP] == curGroup) {
                                inst.options.nextElem = j;
                                break
                            }
                    if (inst.options.nextElem >= 0) {
                        for (j = inst.options.curElem - 1; j >= 0; j--)
                            if (inst.elemArray[j][ELEM_GROUP] == curGroup) {
                                inst.options.prevElem = j;
                                break
                            }
                        if (inst.options.prevElem < 0)
                            for (j = inst.elemArray.length - 1; j > inst.options.curElem; j--)
                                if (inst.elemArray[j][ELEM_GROUP] ==
                                        curGroup) {
                                    inst.options.prevElem = j;
                                    break
                                }
                    }
                }
            };
            inst.clickHandler = function() {
                if (inst.elemArray.length <= 0)
                    return true;
                var $this = $(this);
                inst.hideObjects();
                for (var i = 0; i < inst.elemArray.length; i++)
                    if (inst.elemArray[i][ELEM_HREF] == $this.attr("href"))
                        break;
                if (i == inst.elemArray.length)
                    return true;
                inst.options.curElem = i;
                inst.options.nextElem = -1;
                inst.options.prevElem = -1;
                inst.calcNextPrevElem();
                inst.$next.hide();
                inst.$prev.hide();
                inst.reset();
                inst.$lightbox.show();
                if (!inst.options.supportCSSPositionFixed)
                    inst.$lightbox.css("top",
                            $(window).scrollTop());
                var boxW = inst.options.loadingwidth + 2 * inst.options.bordersize;
                var boxH = inst.options.loadingheight + 2 * inst.options.bordersize;
                var winH = window.innerHeight ? window.innerHeight : $(window).height();
                var boxT = Math.round(winH / 2 - boxH / 2);
                if (inst.options.titlestyle != "inside")
                    boxT -= Math.round(inst.options.barheight / 2);
                inst.$lightboxBox.css({"margin-top": boxT, "width": boxW, "height": boxH});
                inst.$elemWrap.css({"width": boxW, "height": boxH});
                inst.loadCurElem();
                return false
            };
            inst.showNavigation = function() {
                if (!inst.options.shownavigation)
                    return;
                if (!inst.currentElem || !inst.currentElem[ELEM_GROUP])
                    return;
                var i;
                var showNav = false;
                var group = inst.currentElem[ELEM_GROUP];
                for (i = 0; i < inst.elemArray.length; i++)
                    if (group == inst.elemArray[i][ELEM_GROUP])
                        if (inst.elemArray[i][ELEM_THUMBNAIL] && inst.elemArray[i][ELEM_THUMBNAIL].length > 0) {
                            showNav = true;
                            break
                        }
                if (!showNav)
                    return;
                inst.options.navheight = inst.options.thumbheight + 2 * inst.options.thumbmargin;
                if ($(".html5-nav").length > 0)
                    return;
                $("body").append("<div class='html5-nav' style='display:block;position:fixed;bottom:0;left:0;width:100%;height:" +
                        inst.options.navheight + "px;z-index:9999999;'>" + "<div class='html5-nav-container' style='margin:" + inst.options.thumbmargin + "px auto;'>" + "<div class='html5-nav-prev' style='display:block;position:absolute;cursor:pointer;width:" + inst.options.navbuttonwidth + 'px;height:100%;left:0;top:0;background:url("' + inst.options.skinsfolder + inst.options.navarrowsprevimage + "\") no-repeat left center;'></div>" + "<div class='html5-nav-mask' style='display:block;position:relative;margin:0 auto;overflow:hidden;'>" + "<div class='html5-nav-list'></div>" +
                        "</div>" + "<div class='html5-nav-next' style='display:block;position:absolute;cursor:pointer;width:" + inst.options.navbuttonwidth + 'px;height:100%;right:0;top:0;background:url("' + inst.options.skinsfolder + inst.options.navarrowsnextimage + "\") no-repeat right center;'></div>" + "</div>" + "</div>");
                var index = 0;
                for (i = 0; i < inst.elemArray.length; i++)
                    if (group == inst.elemArray[i][ELEM_GROUP])
                        if (inst.elemArray[i][ELEM_THUMBNAIL] && inst.elemArray[i][ELEM_THUMBNAIL].length > 0) {
                            $(".html5-nav-list").append("<div class='html5-nav-thumb' data-arrayindex='" +
                                    i + "' style='float:left;cursor:pointer;opacity:" + inst.options.thumbopacity + ";margin: 0 " + inst.options.thumbgap / 2 + "px;width:" + inst.options.thumbwidth + "px;height:" + inst.options.thumbheight + "px;'><img style='width:100%;border:" + inst.options.thumbborder + "px solid " + inst.options.thumbbordercolor + ";' src='" + inst.elemArray[i][ELEM_THUMBNAIL] + "' /></div>");
                            index++
                        }
                $(".html5-nav-thumb").hover(function() {
                    $(this).css({opacity: 1});
                    $(this).children("img").css({border: inst.options.thumbhighlightborder + "px solid " +
                                inst.options.thumbhighlightbordercolor})
                }, function() {
                    $(this).css({opacity: inst.options.thumbopacity});
                    $(this).children("img").css({border: inst.options.thumbborder + "px solid " + inst.options.thumbbordercolor})
                });
                $(".html5-nav-thumb").click(function() {
                    var index = $(this).data("arrayindex");
                    if (index >= 0)
                        inst.gotoSlide(index)
                });
                inst.options.totalwidth = index * (inst.options.thumbgap + inst.options.thumbwidth);
                $(".html5-nav-list").css({display: "block", position: "relative", "margin-left": 0, width: inst.options.totalwidth +
                            "px"}).append("<div style='clear:both;'></div>");
                var $navMask = $(".html5-nav-mask");
                var $navPrev = $(".html5-nav-prev");
                var $navNext = $(".html5-nav-next");
                $navPrev.click(function() {
                    var $navList = $(".html5-nav-list");
                    var $navNext = $(".html5-nav-next");
                    var maskWidth = $(window).width() - 2 * inst.options.navbuttonwidth;
                    var marginLeft = parseInt($navList.css("margin-left")) + maskWidth;
                    if (marginLeft >= 0) {
                        marginLeft = 0;
                        $(this).css({"background-position": "center left"})
                    } else
                        $(this).css({"background-position": "center right"});
                    if (marginLeft <= maskWidth - inst.options.totalwidth)
                        $navNext.css({"background-position": "center left"});
                    else
                        $navNext.css({"background-position": "center right"});
                    $navList.animate({"margin-left": marginLeft})
                });
                $navNext.click(function() {
                    var $navList = $(".html5-nav-list");
                    var $navPrev = $(".html5-nav-prev");
                    var maskWidth = $(window).width() - 2 * inst.options.navbuttonwidth;
                    var marginLeft = parseInt($navList.css("margin-left")) - maskWidth;
                    if (marginLeft <= maskWidth - inst.options.totalwidth) {
                        marginLeft = maskWidth - inst.options.totalwidth;
                        $(this).css({"background-position": "center left"})
                    } else
                        $(this).css({"background-position": "center right"});
                    if (marginLeft >= 0)
                        $navPrev.css({"background-position": "center left"});
                    else
                        $navPrev.css({"background-position": "center right"});
                    $navList.animate({"margin-left": marginLeft})
                });
                var winWidth = $(window).width();
                if (inst.options.totalwidth <= winWidth) {
                    $navMask.css({width: inst.options.totalwidth + "px"});
                    $navPrev.hide();
                    $navNext.hide()
                } else {
                    $navMask.css({width: winWidth - 2 * inst.options.navbuttonwidth + "px"});
                    $navPrev.show();
                    $navNext.show()
                }
            };
            inst.loadElem = function(elem) {
                inst.currentElem = elem;
                inst.showing = true;
                inst.showNavigation();
                inst.$elem.unbind("mouseenter").unbind("mouseleave").unbind("mousemove");
                inst.$loading.show();
                switch (elem[ELEM_TYPE]) {
                    case 0:
                        var imgLoader = new Image;
                        $(imgLoader).load(function() {
                            inst.showImage(elem, imgLoader.width, imgLoader.height)
                        });
                        $(imgLoader).error(function() {
                            inst.showError()
                        });
                        imgLoader.src = elem[ELEM_HREF];
                        break;
                    case 1:
                        inst.showSWF(elem);
                        break;
                    case 2:
                    case 8:
                        inst.showVideo(elem);
                        break;
                    case 3:
                    case 4:
                        inst.showYoutubeVimeo(elem);
                        break;
                    case 5:
                        inst.showPDF(elem);
                        break;
                    case 6:
                        inst.showMP3(elem);
                        break;
                    case 7:
                        inst.showWeb(elem);
                        break
                    }
            };
            inst.loadCurElem = function() {
                inst.loadElem(inst.elemArray[inst.options.curElem])
            };
            inst.showError = function() {
                inst.$loading.hide();
                inst.resizeLightbox(inst.options.errorwidth, inst.options.errorheight, true, function() {
                    inst.$error.show();
                    inst.$elem.fadeIn(inst.options.fadespeed, function() {
                        inst.showData()
                    })
                })
            };
            inst.calcTextWidth = function(objW) {
                return objW -
                36
            };
            inst.showTitle = function(w, t) {
                if (inst.options.titlestyle == "inside")
                    inst.$elemData.css({width: w + "px"});
                inst.$text.html(t)
            }, inst.showImage = function(elem, imgW, imgH) {
                var elemW, elemH;
                if (elem[ELEM_WIDTH])
                    elemW = elem[ELEM_WIDTH];
                else {
                    elemW = imgW;
                    elem[ELEM_WIDTH] = imgW
                }
                if (elem[ELEM_HEIGHT])
                    elemH = elem[ELEM_HEIGHT];
                else {
                    elemH = imgH;
                    elem[ELEM_HEIGHT] = imgH
                }
                var sizeObj = inst.calcElemSize({w: elemW, h: elemH});
                inst.resizeLightbox(sizeObj.w, sizeObj.h, true, function() {
                    inst.showTitle(sizeObj.w, elem[ELEM_TITLE]);
                    inst.$image.css({width: sizeObj.w,
                        height: sizeObj.h}).show();
                    inst.$image.html("<img src='" + elem[ELEM_HREF] + "' width='100%' height='100%' />");
                    inst.$elem.fadeIn(inst.options.fadespeed, function() {
                        inst.showData()
                    })
                })
            };
            inst.showSWF = function(elem) {
                var dataW = elem[ELEM_WIDTH] ? elem[ELEM_WIDTH] : 640;
                var dataH = elem[ELEM_HEIGHT] ? elem[ELEM_HEIGHT] : 360;
                var sizeObj = inst.calcElemSize({w: dataW, h: dataH});
                dataW = sizeObj.w;
                dataH = sizeObj.h;
                inst.resizeLightbox(dataW, dataH, true, function() {
                    inst.showTitle(sizeObj.w, elem[ELEM_TITLE]);
                    inst.$image.css({width: sizeObj.w,
                        height: sizeObj.h}).html("<div id='html5lightbox-swf' style='display:block;width:100%;height:100%;'></div>").show();
                    inst.embedFlash($("#html5lightbox-swf"), elem[ELEM_HREF], "window", {width: dataW, height: dataH});
                    inst.$elem.show();
                    inst.showData()
                })
            };
            inst.showVideo = function(elem) {
                var dataW = elem[ELEM_WIDTH] ? elem[ELEM_WIDTH] : 640;
                var dataH = elem[ELEM_HEIGHT] ? elem[ELEM_HEIGHT] : 360;
                var sizeObj = inst.calcElemSize({w: dataW, h: dataH});
                dataW = sizeObj.w;
                dataH = sizeObj.h;
                inst.resizeLightbox(dataW, dataH, true, function() {
                    inst.showTitle(sizeObj.w,
                            elem[ELEM_TITLE]);
                    inst.$image.css({width: sizeObj.w, height: sizeObj.h}).html("<div id='html5lightbox-video' style='display:block;width:100%;height:100%;'></div>").show();
                    var isHTML5 = false;
                    if (inst.options.isIE6789 || elem[ELEM_TYPE] == 8)
                        isHTML5 = false;
                    else if (inst.options.isMobile)
                        isHTML5 = true;
                    else if ((inst.options.html5player || !inst.options.flashInstalled) && inst.options.html5VideoSupported)
                        if (!inst.options.isFirefox && !inst.options.isOpera || (inst.options.isFirefox || inst.options.isOpera) && (elem[ELEM_HREF_OGG] ||
                                elem[ELEM_HREF_WEBM]))
                            isHTML5 = true;
                    if (isHTML5) {
                        var videoSrc = elem[ELEM_HREF];
                        if (inst.options.isFirefox || inst.options.isOpera || !videoSrc)
                            videoSrc = elem[ELEM_HREF_WEBM] ? elem[ELEM_HREF_WEBM] : elem[ELEM_HREF_OGG];
                        inst.embedHTML5Video($("#html5lightbox-video"), videoSrc, inst.options.autoplay)
                    } else {
                        var videoFile = elem[ELEM_HREF];
                        if (videoFile.charAt(0) != "/" && videoFile.substring(0, 5) != "http:" && videoFile.substring(0, 6) != "https:")
                            videoFile = inst.options.htmlfolder + videoFile;
                        inst.embedFlash($("#html5lightbox-video"),
                                inst.options.jsfolder + "html5boxplayer.swf", "transparent", {width: dataW, height: dataH, videofile: videoFile, autoplay: inst.options.autoplay ? "1" : "0", errorcss: ".html5box-error" + inst.options.errorcss, id: 0})
                    }
                    inst.$elem.show();
                    inst.showData()
                })
            };
            inst.getYoutubeParams = function(href) {
                var result = {};
                if (href.indexOf("?") < 0)
                    return result;
                var params = href.substring(href.indexOf("?") + 1).split("&");
                for (var i = 0; i < params.length; i++) {
                    var value = params[i].split("=");
                    if (value && value.length == 2 && value[0].toLowerCase() != "v")
                        result[value[0].toLowerCase()] =
                                value[1]
                }
                return result
            };
            inst.prepareYoutubeHref = function(href) {
                var youtubeId = "";
                var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\??v?=?))([^#\&\?]*).*/;
                var match = href.match(regExp);
                if (match && match[7] && match[7].length == 11)
                    youtubeId = match[7];
                var result = "http://www.youtube.com/embed/" + youtubeId;
                var params = this.getYoutubeParams(href);
                var first = true;
                for (var key in params) {
                    if (first) {
                        result += "?";
                        first = false
                    } else
                        result += "&";
                    result += key + "=" + params[key]
                }
                return result
            };
            inst.showYoutubeVimeo =
                    function(elem) {
                        var dataW = elem[ELEM_WIDTH] ? elem[ELEM_WIDTH] : 640;
                        var dataH = elem[ELEM_HEIGHT] ? elem[ELEM_HEIGHT] : 360;
                        var sizeObj = inst.calcElemSize({w: dataW, h: dataH});
                        dataW = sizeObj.w;
                        dataH = sizeObj.h;
                        inst.resizeLightbox(dataW, dataH, true, function() {
                            inst.showTitle(sizeObj.w, elem[ELEM_TITLE]);
                            inst.$image.css({width: sizeObj.w, height: sizeObj.h}).html("<div id='html5lightbox-video' style='display:block;width:100%;height:100%;'></div>").show();
                            var href = elem[ELEM_HREF];
                            if (elem[ELEM_TYPE] == 3)
                                href = inst.prepareYoutubeHref(href);
                            if (inst.options.autoplay)
                                if (href.indexOf("?") < 0)
                                    href += "?autoplay=1";
                                else
                                    href += "&autoplay=1";
                            if (elem[ELEM_TYPE] == 3)
                                if (href.indexOf("?") < 0)
                                    href += "?wmode=transparent&rel=0";
                                else
                                    href += "&wmode=transparent&rel=0";
                            $("#html5lightbox-video").html("<iframe width='100%' height='100%' src='" + href + "' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>");
                            inst.$elem.show();
                            inst.showData()
                        })
                    };
            inst.showPDF = function(elem) {
            };
            inst.showMP3 = function(elem) {
            };
            inst.showWeb = function(elem) {
                var winH =
                        window.innerHeight ? window.innerHeight : $(window).height();
                var dataW = elem[ELEM_WIDTH] ? elem[ELEM_WIDTH] : $(window).width();
                var dataH = elem[ELEM_HEIGHT] ? elem[ELEM_HEIGHT] : winH - inst.options.navheight;
                var sizeObj = inst.calcElemSize({w: dataW, h: dataH});
                dataW = sizeObj.w;
                dataH = sizeObj.h;
                inst.resizeLightbox(dataW, dataH, true, function() {
                    inst.$loading.hide();
                    inst.showTitle(sizeObj.w, elem[ELEM_TITLE]);
                    inst.$image.css({width: sizeObj.w, height: sizeObj.h}).html("<div id='html5lightbox-web' style='display:block;width:100%;height:100%;margin-top:20px'></div>").show();
                    $("#html5lightbox-web").html("<iframe width='100%' height='100%' src='" + elem[ELEM_HREF] + "' frameborder='0'></iframe>");
                    inst.$elem.show();
                    inst.showData()
                })
            };
            inst.scrollBox = function() {
                if (!inst.options.supportCSSPositionFixed)
                    inst.$lightbox.css("top", $(window).scrollTop())
            };
            inst.resizeWindow = function() {
                if (!inst.currentElem)
                    return;
                if (!inst.options.responsive)
                    return;
                var elemW = inst.currentElem[ELEM_WIDTH] ? inst.currentElem[ELEM_WIDTH] : 640;
                var elemH = inst.currentElem[ELEM_HEIGHT] ? inst.currentElem[ELEM_HEIGHT] :
                        360;
                var sizeObj = inst.calcElemSize({w: elemW, h: elemH});
                var winH = window.innerHeight ? window.innerHeight : $(window).height();
                var boxW = sizeObj.w + 2 * inst.options.bordersize;
                var boxH = sizeObj.h + 2 * inst.options.bordersize;
                var boxT = Math.round((winH - inst.options.navheight) / 2 - boxH / 2);
                if (inst.options.titlestyle != "inside")
                    boxT -= Math.round(inst.options.barheight / 2);
                inst.$lightboxBox.css({"margin-top": boxT});
                inst.$lightboxBox.css({"width": boxW, "height": boxH});
                inst.$elemWrap.css({width: boxW, height: boxH});
                inst.$image.css({width: sizeObj.w,
                    height: sizeObj.h});
                if ($(".html5-nav").length <= 0)
                    return;
                var $navMask = $(".html5-nav-mask");
                var $navPrev = $(".html5-nav-prev");
                var $navNext = $(".html5-nav-next");
                var winWidth = $(window).width();
                if (inst.options.totalwidth <= winWidth) {
                    $navMask.css({width: inst.options.totalwidth + "px"});
                    $navPrev.hide();
                    $navNext.hide()
                } else {
                    $navMask.css({width: winWidth - 2 * inst.options.navbuttonwidth + "px"});
                    $navPrev.show();
                    $navNext.show()
                }
            };
            inst.calcElemSize = function(sizeObj) {
                var winH = window.innerHeight ? window.innerHeight : $(window).height();
                var h0 = winH - inst.options.navheight - 2 * inst.options.bordersize - 2 * inst.options.bordermargin;
                if (inst.options.titlestyle != "inside")
                    h0 -= inst.options.barheight;
                if (sizeObj.h > h0) {
                    sizeObj.w = Math.round(sizeObj.w * h0 / sizeObj.h);
                    sizeObj.h = h0
                }
                var w0 = $(window).width() - 2 * inst.options.bordersize - 2 * inst.options.bordermargin;
                if (sizeObj.w > w0) {
                    sizeObj.h = Math.round(sizeObj.h * w0 / sizeObj.w);
                    sizeObj.w = w0
                }
                return sizeObj
            };
            inst.showData = function() {
                if (inst.$text.text().length > 0)
                    inst.$elemData.show();
                if (inst.$text.text().length >
                        0 && inst.options.titlestyle != "inside")
                    inst.$lightboxBox.css({height: String(inst.$lightboxBox.height() + inst.options.barheight) + "px"})
            };
            inst.resizeLightbox = function(elemW, elemH, bAnimate, onFinish) {
                var winH = window.innerHeight ? window.innerHeight : $(window).height();
                var speed = bAnimate ? inst.options.resizespeed : 0;
                var boxW = elemW + 2 * inst.options.bordersize;
                var boxH = elemH + 2 * inst.options.bordersize;
                var boxT = Math.round((winH - inst.options.navheight) / 2 - boxH / 2);
                if (inst.options.titlestyle != "inside")
                    boxT -= Math.round(inst.options.barheight /
                            2);
                if (boxW == inst.$elemWrap.width() && boxH == inst.$elemWrap.height())
                    speed = 0;
                inst.$loading.hide();
                inst.$watermark.hide();
                inst.$elem.bind("mouseenter mousemove", function() {
                    if (inst.options.prevElem >= 0 || inst.options.nextElem >= 0) {
                        inst.$next.fadeIn();
                        inst.$prev.fadeIn()
                    }
                });
                inst.$elem.bind("mouseleave", function() {
                    inst.$next.fadeOut();
                    inst.$prev.fadeOut()
                });
                inst.$lightboxBox.css({"margin-top": boxT});
                inst.$lightboxBox.css({"width": boxW, "height": boxH});
                inst.$elemWrap.animate({width: boxW}, speed).animate({height: boxH},
                speed, function() {
                    inst.$loading.show();
                    inst.$watermark.show();
                    inst.$close.show();
                    inst.$elem.css({"background-color": inst.options.bgcolor});
                    onFinish()
                })
            };
            inst.reset = function() {
                if (inst.options.stamp)
                    inst.$watermark.hide();
                inst.showing = false;
                inst.$image.empty();
                inst.$text.empty();
                inst.$error.hide();
                inst.$loading.hide();
                inst.$image.hide();
                inst.$elemData.hide();
                inst.$close.hide();
                inst.$elem.css({"background-color": ""})
            };
            inst.resetNavigation = function() {
                inst.options.navheight = 0;
                $(".html5-nav").remove()
            };
            inst.finish = function() {
                inst.reset();
                inst.resetNavigation();
                inst.$lightbox.hide();
                inst.showObjects()
            };
            inst.pauseSlide = function() {
            };
            inst.playSlide = function() {
            };
            inst.gotoSlide = function(slide) {
                if (slide == -1) {
                    if (inst.options.nextElem < 0)
                        return;
                    inst.options.curElem = inst.options.nextElem
                } else if (slide == -2) {
                    if (inst.options.prevElem < 0)
                        return;
                    inst.options.curElem = inst.options.prevElem
                } else if (slide >= 0)
                    inst.options.curElem = slide;
                inst.calcNextPrevElem();
                inst.reset();
                inst.loadCurElem()
            };
            inst.supportKeyboard = function() {
                $(document).keyup(function(e) {
                    if (!inst.showing)
                        return;
                    if (inst.options.supportesckey && e.keyCode == 27)
                        inst.finish();
                    else if (inst.options.supportarrowkeys)
                        if (e.keyCode == 39)
                            inst.gotoSlide(-1);
                        else if (e.keyCode == 37)
                            inst.gotoSlide(-2)
                })
            };
            inst.enableSwipe = function() {
                inst.$elem.touchSwipe({preventWebBrowser: true, swipeLeft: function() {
                        inst.gotoSlide(-1)
                    }, swipeRight: function() {
                        inst.gotoSlide(-2)
                    }})
            };
            inst.hideObjects = function() {
                $("select, embed, object").css({"visibility": "hidden"})
            };
            inst.showObjects = function() {
                $("select, embed, object").css({"visibility": "visible"})
            };
            inst.embedHTML5Video = function($container, src, autoplay) {
                $container.html("<div style='display:block;width:100%;height:100%'><video width='100%' height='100%'" + (autoplay ? " autoplay" : "") + " controls='controls' src='" + src + "'></div>")
            };
            inst.embedFlash = function($container, src, wmode, flashVars) {
                if (inst.options.flashInstalled) {
                    var htmlOptions = {pluginspage: "http://www.adobe.com/go/getflashplayer", quality: "high", allowFullScreen: "true", allowScriptAccess: "always", type: "application/x-shockwave-flash"};
                    htmlOptions.width =
                            "100%";
                    htmlOptions.height = "100%";
                    htmlOptions.src = src;
                    htmlOptions.flashVars = $.param(flashVars);
                    htmlOptions.wmode = wmode;
                    var htmlString = "";
                    for (var key in htmlOptions)
                        htmlString += key + "=" + htmlOptions[key] + " ";
                    $container.html("<embed " + htmlString + "/>")
                } else
                    $container.html("<div class='html5lightbox-flash-error' style='display:block; position:relative;text-align:center; width:100%; left:0px; top:40%;'><div class='html5-error'><div>The required Adobe Flash Player plugin is not installed</div><br /><div style='display:block;position:relative;text-align:center;width:112px;height:33px;margin:0px auto;'><a href='http://www.adobe.com/go/getflashplayer'><img src='http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' width='112' height='33'></img></a></div></div>")
            };
            inst.checkType = function(href) {
                if (!href)
                    return-1;
                if (href.match(/\.(jpg|gif|png|bmp|jpeg)(.*)?$/i))
                    return 0;
                if (href.match(/[^\.]\.(swf)\s*$/i))
                    return 1;
                if (href.match(/\.(mp4|m4v|ogv|ogg|webm)(.*)?$/i))
                    return 2;
                if (href.match(/\:\/\/.*(youtube\.com)/i) || href.match(/\:\/\/.*(youtu\.be)/i))
                    return 3;
                if (href.match(/\:\/\/.*(vimeo\.com)/i))
                    return 4;
                if (href.match(/[^\.]\.(pdf)\s*$/i))
                    return 5;
                if (href.match(/[^\.]\.(mp3)\s*$/i))
                    return 6;
                if (href.match(/[^\.]\.(flv)\s*$/i))
                    return 8;
                return 7
            };
            inst.showLightbox =
                    function(type, href, title, width, height, webm, ogg) {
                        inst.$next.hide();
                        inst.$prev.hide();
                        inst.reset();
                        inst.$lightbox.show();
                        if (!inst.options.supportCSSPositionFixed)
                            inst.$lightbox.css("top", $(window).scrollTop());
                        var winH = window.innerHeight ? window.innerHeight : $(window).height();
                        var boxW = inst.options.loadingwidth + 2 * inst.options.bordersize;
                        var boxH = inst.options.loadingheight + 2 * inst.options.bordersize;
                        var boxT = Math.round(winH / 2 - boxH / 2);
                        if (inst.options.titlestyle != "inside")
                            boxT -= Math.round(inst.options.barheight /
                                    2);
                        inst.$lightboxBox.css({"margin-top": boxT, "width": boxW, "height": boxH});
                        inst.$elemWrap.css({"width": boxW, "height": boxH});
                        inst.loadElem(new Array(type, href, title, null, width, height, webm, ogg))
                    };
            inst.addItem = function(href, title, group, width, height, webm, ogg) {
                type = inst.checkType(href);
                inst.elemArray.push(new Array(type, href, title, group, width, height, webm, ogg))
            };
            inst.showItem = function(href) {
                if (inst.elemArray.length <= 0)
                    return true;
                inst.hideObjects();
                for (var i = 0; i < inst.elemArray.length; i++)
                    if (inst.elemArray[i][ELEM_HREF] ==
                            href)
                        break;
                if (i == inst.elemArray.length)
                    return true;
                inst.options.curElem = i;
                inst.options.nextElem = -1;
                inst.options.prevElem = -1;
                inst.calcNextPrevElem();
                inst.$next.hide();
                inst.$prev.hide();
                inst.reset();
                inst.$lightbox.show();
                if (!inst.options.supportCSSPositionFixed)
                    inst.$lightbox.css("top", $(window).scrollTop());
                var winH = window.innerHeight ? window.innerHeight : $(window).height();
                var boxW = inst.options.loadingwidth + 2 * inst.options.bordersize;
                var boxH = inst.options.loadingheight + 2 * inst.options.bordersize;
                var boxT =
                        Math.round(winH / 2 - boxH / 2);
                if (inst.options.titlestyle != "inside")
                    boxT -= Math.round(inst.options.barheight / 2);
                inst.$lightboxBox.css({"margin-top": boxT, "width": boxW, "height": boxH});
                inst.$elemWrap.css({"width": boxW, "height": boxH});
                inst.loadCurElem();
                return false
            };
            inst.init();
            return inst.unbind("click").click(inst.clickHandler)
        }
    })(jQuery);
    (function($) {
        $.fn.touchSwipe = function(options) {
            var defaults = {preventWebBrowser: false, swipeLeft: null, swipeRight: null, swipeTop: null, swipeBottom: null};
            if (options)
                $.extend(defaults,
                        options);
            return this.each(function() {
                var startX = -1, startY = -1;
                var curX = -1, curY = -1;
                function touchStart(event) {
                    var e = event.originalEvent;
                    if (e.targetTouches.length >= 1) {
                        startX = e.targetTouches[0].pageX;
                        startY = e.targetTouches[0].pageY
                    } else
                        touchCancel(event)
                }
                function touchMove(event) {
                    if (defaults.preventWebBrowser)
                        event.preventDefault();
                    var e = event.originalEvent;
                    if (e.targetTouches.length >= 1) {
                        curX = e.targetTouches[0].pageX;
                        curY = e.targetTouches[0].pageY
                    } else
                        touchCancel(event)
                }
                function touchEnd(event) {
                    if (curX >
                            0 || curY > 0) {
                        triggerHandler();
                        touchCancel(event)
                    } else
                        touchCancel(event)
                }
                function touchCancel(event) {
                    startX = -1;
                    startY = -1;
                    curX = -1;
                    curY = -1
                }
                function triggerHandler() {
                    if (curX > startX) {
                        if (defaults.swipeRight)
                            defaults.swipeRight.call()
                    } else if (defaults.swipeLeft)
                        defaults.swipeLeft.call();
                    if (curY > startY) {
                        if (defaults.swipeBottom)
                            defaults.swipeBottom.call()
                    } else if (defaults.swipeTop)
                        defaults.swipeTop.call()
                }
                try {
                    $(this).bind("touchstart", touchStart);
                    $(this).bind("touchmove", touchMove);
                    $(this).bind("touchend",
                            touchEnd);
                    $(this).bind("touchcancel", touchCancel)
                } catch (e) {
                }
            })
        }
    })(jQuery);
    jQuery(document).ready(function() {
        if (typeof html5Lightbox === "undefined")
            html5Lightbox = jQuery(".html5lightbox").html5lightbox()
            Drag.init(document.getElementById('html5-lightbox-box'))//Representa a parte Branca da Div que carrega o conte�do que est� no Iframe ...
    })
};