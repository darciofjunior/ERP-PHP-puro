function sessao() {
    if(typeof(parent.relogio_sessao) == 'object') {
        parent.relogio_sessao.location = '/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S'
    }else if(typeof(parent.itens) == 'object') {
        parent.itens.relogio_sessao.location = '/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S'
    }else if(typeof(opener.parent.itens) == 'object') {
        opener.parent.itens.relogio_sessao.location = '/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S'
    }else if(typeof(opener.relogio_sessao) == 'object') {
        opener.relogio_sessao.location = '/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S'
    }
}
//Quando o usuário digitar ou clicar ...
document.onkeyup        = new Function("sessao()")
document.onclick        = new Function("sessao()")