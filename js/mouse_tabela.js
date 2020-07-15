// calculo de residuo => atualizar o valor caso haja residuo no primeiro caso
var offsetx = 9; var offsety = 9; var x = 0; var y = 0; var snow = 0; var sw = 0; var cnt = 0; var dir = 1; var tr = 1; var ns4, ie4, ie5, ns6, over;
function iniciar_layer() {
ns4 = (document.layers)? true:false;
ie4 = (document.all)? true:false;
ns6 = (!document.all && document.getElementById)? true:false;
    if(ie4){
        if(navigator.userAgent.indexOf('MSIE 5')>0 || navigator.userAgent.indexOf('MSIE 6')>0){
            ie5 = true;
        }else {
            ie5 = false;
        }
    }else {
        ie5 = false;
    }
    if (ns4) over = document.overDiv;
    if (ie4) over = overDiv.style;
    if (ns6) over = document.getElementById('overDiv').style; document.onmousemove = mouseMove;
    if (ns4) document.captureEvents(Event.MOUSEMOVE);
}

function nd(){if(cnt >= 1){sw = 0}; if(sw == 0){snow = 0; hideObject(over);}else{cnt++;}}
function dif(exibir){layerWrite(exibir); disp();}

function disp(){
    if(snow == 0){
        if(dir == 2){ 
            moveTo(over,x+offsetx-(width/2),y+offsety);
        }
        if(dir == 1){
            moveTo(over,x+offsetx,y+offsety);
        } 
        if (dir == 0){
            moveTo(over,x-offsetx-width,y+offsety);
        }
        showObject(over); 
        snow = 1;
    }
}

function mouseMove(e) {
    if(ns4){
        x=e.pageX; y=e.pageY;
    } 
    if(ie4){
        x=event.x; y=event.y;
    } 
    if(ie5){
        x=event.x+document.body.scrollLeft; y=event.y+document.body.scrollTop;
    }
    if(ns6){
        x=e.pageX+10; y=e.pageY;
    }
    if(snow){
        if(dir == 2){
            moveTo(over,x+offsetx-(width/2),y+offsety);
        } 
        if(dir == 1){
            moveTo(over,x+offsetx,y+offsety);
        }
        if(dir == 0){
            moveTo(over,x-offsetx-width,y+offsety);
        }
    }
}

function layerWrite(txt){if(ns4){var lyr = document.overDiv.document; lyr.write(txt); lyr.close();}else if (ie4){document.all["overDiv"].innerHTML = txt;}	else if (ns6){document.getElementById("overDiv").innerHTML = txt}}
function showObject(obj){if (ns4) obj.visibility = "show"; else if (ie4) obj.visibility = "visible"; else if (ns6) obj.visibility = "visible";}
function hideObject(obj){if (ns4) obj.visibility = "hide"; else if (ie4) obj.visibility = "hidden"; else if (ns6) obj.visibility = "hidden";}

function moveTo(obj,xL,yL){
    obj.left = xL; obj.top = yL;
}