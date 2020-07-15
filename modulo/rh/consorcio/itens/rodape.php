<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');

/*Aqui eu verifico se j� foi gerado Vale p/ este Cons�rcio, caso foi gerado, ent�o eu travo os bot�es 
de Rodap� do Cons�rcio ...*/
$sql = "SELECT gerado_vale 
	FROM `consorcios` 
	WHERE `id_consorcio` = '$id_consorcio' LIMIT 1 ";
$campos         = bancos::sql($sql);
$gerado_vale    = $campos[0]['gerado_vale'];

if(strtoupper($gerado_vale) == 'S') {
//Quando alterar o cabecalho ele tem q reler o Cons�rcio
    $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"CONS�RCIO BLOQUEADO !!!\nJ� FOI GERADO VALE PARA ESTE CONS�RCIO !"'.")'";
}else {
    $controle_botao = "class='botao' ";
}

//Verifica se existe pelo menos 1 funcion�rio no Cons�rcio para fazer a exibi��o dos bot�es de rodap�
$sql = "SELECT id_consorcio_vs_funcionario 
	FROM `consorcios_vs_funcionarios` 
	WHERE `id_consorcio` = '$id_consorcio' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
/**********************************************************************************************/
?>
<html>
<head>
<title>.:: Rodap� de Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function outras_opcoes() {
    nova_janela('outras_opcoes.php?id_consorcio=<?=$id_consorcio;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}

function selecionar(opcao) {
    var x, option  = 0
    if(typeof(parent.itens.document.form) == 'undefined') {
        return false
    }else {
        elemento = parent.itens.document.form
    }
    if (elemento.checked == false) {
        alert('SELECIONE UM ITEM !')
        return false
    }
    for (x = 0; x < elemento.length; x ++) {
        if (elemento[x].checked == true && elemento[x].type == 'radio') option ++
    }
    if(option == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        for (x = 0; x < elemento.length; x ++) {
            if (elemento[x].checked == true && elemento[x].type == 'radio') {
                var id_consorcio_vs_funcionario = elemento[x].value
                var posicao = x + 1
                break;
            }
        }
//Significa que o Usu�rio deseja alterar um Funcion�rio como ganhador do Pr�mio do Cons�rcio ...
        if(opcao == 1) {
            nova_janela('alterar.php?passo=0&id_consorcio=<?=$id_consorcio;?>&posicao='+posicao, 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
//Significa que o Usu�rio deseja excluir um Funcion�rio do Cons�rcio
        }else {
            var valor = confirm('CONFIRMA A EXCLUS�O ?')
            if(valor == true) {
                window.parent.itens.location = 'itens.php?passo=1&id_consorcio=<?=$id_consorcio;?>&id_consorcio_vs_funcionario='+id_consorcio_vs_funcionario
            }else {
                return false
            }
        }
        return true
    }
}

function imprimir_consorcio() {
    nova_janela('relatorio/relatorio.php?id_consorcio=<?=$id_consorcio;?>', 'CONSULTAR', 'F')
}
</Script>
</head>
<?
/*Esse par�metro -> $clique_automatico_cabecalho

Dispara um clique autom�tico no bot�o de Alterar Cabe�alho, assim que acaba de ser
clonado um novo da Op��o -> Outras Op��es*/

//Vai entrar aqui somente na primeira em que carregar a tela
if(empty($parametro_velho)) {
//Controle para o bot�o
    $parametro_velho = $parametro;
//Controle para o hidden
    $parametro_velho2 = $parametro;
}else {
/*Controle para o hidden, aqui tem q ter a urlencode, para n�o dar erro ap�s q submeter, s� q se tiver
isso diretamente no bot�o j� da erro*/
    //$parametro_velho2 = urlencode($parametro_velho);
    $parametro_velho2 = $parametro_velho;
}
?>
<body>
<form name='form'>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho2;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <td align='center'>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="javascript:window.parent.location = 'consultar.php<?=$parametro_velho;?>'" class='botao'>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' title='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' <?=$controle_botao;?> onclick="javascript:nova_janela('../alterar_cabecalho.php?id_consorcio=<?=$id_consorcio;?>', 'POP', '', '', '', '', 580, 900, 'c', 'c', '', '', 's', 's', '', '', '')">
        <input type='button' name='cmd_incluir_item' value='Incluir Item' title='Incluir Item' <?=$controle_botao;?> onclick="javascript:nova_janela('incluir.php?passo=0&id_consorcio=<?=$id_consorcio;?>', 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')">
<?
    if($linhas > 0) {
?>
        <input type='button' name='cmd_alterar_item' value='Alterar Item' title='Alterar Item' onclick="selecionar(1)" class='botao'>
        <input type='button' name='cmd_excluir_item' value='Excluir Item' title='Excluir Item' <?=$controle_botao;?> onclick="selecionar(2)">
<?
    }
?>
        <input type='button' name='cmd_outras_opcoes' value='Outras Op��es' title='Outras Op��es' onclick="outras_opcoes()" class='botao'>
<?
    if($linhas > 0) {
/*****************************Controle com a Parte de Datas do Cons�rcio*****************************/
        $data_atual = date('Y-m-d');
//Aqui eu verifico a qtde de Datas de Holerith que existem cadastradas no Sistema a partir da Data Atual ...
        $sql = "SELECT COUNT(id_vale_data) AS total_data_holerith_cadast 
                FROM `vales_datas` 
                WHERE `data` > '$data_atual' ";
        $campos_data_holerith       = bancos::sql($sql);
        $total_data_holerith_cadast = $campos_data_holerith[0]['total_data_holerith_cadast'];
/*Se a Qtde de Meses do Cons�rcio for > que a Qtde de Cadastro de Datas de Holerith, ent�o o link
aparece em vermelho p/ dizer q esse Cons�rcio est� em inadipl�ncia ...*/
        if($meses > $total_data_holerith_cadast) {
            $script = "javascript:alert('A QTDE DE MESES DO CONS�RCIO � SUPERIOR A QTDE DE DATA(S) DE HOLERITH(S) CADASTRADA(S) NO SISTEMA !!!') ";
        }else {
            $script = "javascript:imprimir_consorcio() ";
        }
?>
        <input type='button' name="cmd_imprimir_consorcio" value='Imprimir Cons�rcio' title='Imprimir Cons�rcio' onclick='<?=$script;?>' class='botao'>
<?
    }
?>
    </td>
</table>
<input type='hidden' name='id_consorcio' value='<?=$id_consorcio?>'>
</form>
</body>
</html>