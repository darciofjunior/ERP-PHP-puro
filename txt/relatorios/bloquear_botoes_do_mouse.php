<html>
<head>
<Script Language = 'JavaScript'>
function travar_cliques_mouse(event) {
    if(event.button == 0) {//Bot�o Esquerdo do Mouse ...
        alert('Ops! Bot�o esquerdo do mouse bloqueado !')
    }else if(event.button == 1) {//Bot�o Scroll do Mouse ...   
    }else if(event.button == 2 || event.button == 3) {//Bot�o Direito do Mouse ...
    }
}
document.onmousedown = travar_cliques_mouse
</Script>
</head>
<body onclick='travar_cliques_mouse(event)' topmargin='300'>
<form name='form'>
    <center>
        <input type='button' name='cmd_button' value='Button' title='Button' onclick="alert('Testando')"/>
    </center>
</form>
</body>
</html>