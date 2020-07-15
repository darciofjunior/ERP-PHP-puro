<html>
<head>
<Script Language = 'JavaScript'>
function travar_cliques_mouse(event) {
    if(event.button == 0) {//Botão Esquerdo do Mouse ...
        alert('Ops! Botão esquerdo do mouse bloqueado !')
    }else if(event.button == 1) {//Botão Scroll do Mouse ...   
    }else if(event.button == 2 || event.button == 3) {//Botão Direito do Mouse ...
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