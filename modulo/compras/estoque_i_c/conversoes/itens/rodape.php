<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/conversoes/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Rodapé de Convers&otilde;es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function selecionar(valor) {
    var x, option  = 0
    elemento  = parent.itens.document.form.opt_item
    if (elemento.checked == true) return true
    if (elemento.checked == false) {
        window.alert('SELECIONE UM ITEM !')
        return false
    }
    for (x = 0; x < elemento.length; x ++) {
        if (elemento[x].checked == true) option ++
    }
    if (option == 0) {
        window.alert('SELECIONE UM ITEM !')
        return false
    }else {
        for (x = 0; x < elemento.length; x ++) {
            if (elemento[x].checked == true) {
                var id_item_conversoes_temps = elemento[x].value
                var posicao = x + 1
                break;
            }
        }
        if(valor == 1) {
            nova_janela('alterar.php?id_conversoes_temps=<?=$id_conversoes_temps;?>&posicao='+posicao, 'POP', '', '', '', '', 450, 850, 'c', 'c')
        }else {
            var valor = confirm('CONFIRMA A EXCLUSÃO ?')
            if (valor == true) {
                window.parent.itens.location = 'itens.php?passo=1&id_item_conversoes_temps='+id_item_conversoes_temps
            }else {
                return false
            }
        }
    }
}

function deletar() {
    var valor = confirm('CONFIRMA A EXCLUSÃO ?')
    if (valor == true) {
        window.location = 'rodape.php?id_conversoes_temps=<?=$id_conversoes_temps;?>'
    }else {
        return false
    }
}

function imprimir() {
    window.parent.itens.focus()
    window.parent.itens.print()
}
</Script>
</head>
<?
/*Esse parâmetro -> $clique_automatico_cabecalho

Dispara um clique automático no botão de Alterar Cabeçalho, assim que acaba de ser
clonado um novo da Opção -> Outras Opções*/

//Vai entrar aqui somente na primeira em que carregar a tela
if(empty($parametro_velho)) {
//Controle para o botão
    $parametro_velho = $parametro;
//Controle para o hidden
    $parametro_velho2 = $parametro;
}else {
/*Controle para o hidden, aqui tem q ter a urlencode, para não dar erro após q submeter, só q se tiver
isso diretamente no botão já da erro*/
    //$parametro_velho2 = urlencode($parametro_velho);
    $parametro_velho2 = $parametro_velho;
}

//Verifico a Qtde de Itens da Conversão ...
$sql = "SELECT id_item_conversoes_temps 
        FROM `itens_conversoes_temps` 
        WHERE `id_conversoes_temps` = '$id_conversoes_temps' ORDER BY id_item_conversoes_temps LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<body>
<form name='form'>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho2;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="javascript:window.parent.location = '../consultar.php<?=$parametro_velho;?>'" class='botao'>
        <input type='button' name='cmd_incluir_itens' value="Incluir Itens" title="Incluir Itens" onclick="javascript:nova_janela('incluir.php?id_conversoes_temps=<?=$id_conversoes_temps;?>', 'POP', '', '', '', '', 580, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
//Quando não existir nenhum item de Conversão, então mostro o botão de Excluir a Conversão
        if($linhas == 0) {
?>
        <input type='button' name='cmd_excluir_conversao' value="Excluir Conversão" title="Excluir Conversão" onclick="return deletar()" style="color:red" class='botao'>
<?
        }
//Quando existir pelo menos item de Conversão, então mostro o botão de Alterar e Excluir os Items
        if($linhas > 0) {
?>
        <input type='button' name='cmd_alterar_itens' value='Alterar Itens'  title='Alterar Itens' onclick="selecionar(1)" class='botao'>
        <input type='button' name='cmd_excluir_itens' value='Excluir Itens' title='Excluir Itens' onclick="selecionar(2)" class='botao'>
<?
        }
?>
        <input type='button' name='cmd_imprimir' value="Imprimir" title="Imprimir" onclick="imprimir()" class='botao'>
    </td>
</table>
<input type='hidden' name='id_conversoes_temps' value='<?=$id_conversoes_temps;?>'>
</form>
</body>
</html>
<?
if($passo == 1) {
    //Aqui eu deleto apenas o Item da Conversão passado por parâmetro ...
    if(!empty($_GET['id_item_conversoes_temps'])) {
        $sql = "DELETE FROM `itens_conversoes_temps` WHERE `id_conversoes_temps` = '$_GET[id_item_conversoes_temps]' LIMIT 1 ";
        bancos::sql($sql);
    }

    //Aqui eu deleto a Conversão passada por parâmetro ...
    if(!empty($_GET['id_conversoes_temps'])) {
        $sql = "DELETE FROM `conversoes_temps` WHERE `id_conversoes_temps` = '$_GET[id_conversoes_temps]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.parent.location = '../consultar.php?valor=2'
    </Script>
<?
    }
}
?>