<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>EMPRESA ALTERADA COM SUCESSO.</font>";

/**************************Ferramentinha para Trocar a Empresa do Pedido**************************/
//Toda vez em que se desejar alterar a Empresa do Pedido, o Sistema ir� cair nessa rotina ...
if(!empty($_POST['cmb_empresa'])) {//Alterando a Empresa do Pedido ...
//Aqui eu busco qual � o Desconto do Pedido p/ substituir tamb�m ...
    $sql = "SELECT desc_ddl 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $desc_ddl   = $campos[0]['desc_ddl'];

    if($_POST['cmb_empresa'] == 4) {//Se a Empresa desejada pelo usu�rio for Grupo ...
        $desc_ddl = str_replace('NF', 'SGD', $desc_ddl);
        $tipo_nota = 2;
    }else {//Se a Empresa desejada pelo usu�rio for Alba ou Tool ...
        $desc_ddl = str_replace('SGD', 'NF', $desc_ddl);
        $tipo_nota = 1;
    }
//Alterando os dados de Empresa do Pedido ...
    $sql = "UPDATE `pedidos` SET `id_empresa` = '$_POST[cmb_empresa]', `desc_ddl` = '$desc_ddl', `tipo_nota` = '$tipo_nota' WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
?>
    <Script Language = 'Javascript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}
/*************************************************************************************************/

//Procedimento Normal da Tela de Outras Op��es ...
//Verifica a situa��o do Pedido de Compras
$sql = "SELECT id_empresa, status 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Tenho que renomear essa vari�vel pq j� existe uma vari�vel com esse de id_empresa na Sess�o do Sistema ...
$id_empresa_pedido  = $campos[0]['id_empresa'];
$status             = $campos[0]['status'];
if($status == 2) {//Se o Pedido estiver conclu�do
    $disabled_opcao_2 = 'disabled';//n�o posso fazer requisi��o de Materiais
}else {
    $disabled_opcao_2 = '';
}

/*************************Crit�rios para a Importa��o de uma OS*************************/
//1) Verifica a qtde de Item(ns) desse Pedido
$sql = "SELECT COUNT(id_item_pedido) AS qtde_itens_pedidos 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' ";
$campos = bancos::sql($sql);
$qtde_itens_pedidos = $campos[0]['qtde_itens_pedidos'];
if($qtde_itens_pedidos > 0) {//Se existir pelo menos 1 item, ent�o ...
    $disabled_opcao_7 = 'disabled';//Eu n�o posso importar OS
}else {//Se n�o tiver nenhum item de pedido, desabilito a Op��o
    $disabled_opcao_7 = '';//Eu posso importar OS
}
//2) O pedido corrente s� pode estar em apenas 1 OS atrelada
$sql = "SELECT id_os 
        FROM `oss` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {//Se existir pelo menos 1 item, ent�o eu n�o posso importar OS
    $disabled_opcao_3 = 'disabled';//Eu n�o posso mexer na Op��o de Atualizar Pre�os da Lista de Pre�o
    $disabled_opcao_4 = 'disabled';//Eu n�o posso transportar Itens Pedido quando tiver OS
    $disabled_opcao_5 = 'disabled';//Eu n�o posso importar Cota��o quando tiver OS
    $disabled_opcao_6 = 'disabled';//Eu n�o posso importar OS
}
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Outras Op��es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Antecipa��o de Pedido ...
        window.location = '../antecipacoes.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Requisi��o de Material ...
        window.location = '../requisicao_materiais/consultar_pedido.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[2].checked == true) {//Atualizar Lista de Pre�o ...
        var resposta = confirm('VOC� DESEJA ATUALIZAR O PRE�O DESSE(S) ITEM(NS) COM OS PRE�O(S) DA LISTA ?\n\nOBS: ESTA ALTERA��O AFETAR� SOMENTE ITEM(NS) EM ABERTO E ITEM(NS) PARCIAL(IS) EM QUE O PRE�O UNIT�RIO SEJA DIFERENTE DE 0,00 E NORMAL DE LINHA !')
        if(resposta == true) window.location = 'atualizar_precos.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[3].checked == true) {//Transportar outro Pedido ...
        window.location = 'transportar_outro_pedido.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[4].checked == true) {//Importar Cota��o ...
        window.location = 'importar_cotacao.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[5].checked == true) {//Importar OS ...
        window.location = 'importar_os.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }
}

function alterar_empresa() {
    if(document.form.cmb_empresa.value == '') {
        alert('SELECIONE UMA EMPRESA !')
        document.form.cmb_empresa.focus()
        return false
    }else {
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA SUBSTITUIR A EMPRESA DO PEDIDO ?')
        if(resposta == true) {
            document.form.submit()
        }else {
            return false
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Op��es
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Antecipa��o(�es) de Pedido' id='label'>
            <label for='label'>Antecipa��o(�es) de Pedido</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Requisi��o de Materiais' id='label2' <?=$disabled_opcao_2;?>>
            <label for='label2'>Requisi��o de Materiais</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Atualizar Pre�os da Lista Pre�o (Somente p/ Itens em Aberto e Itens Parciais)' id='label3' <?=$disabled_opcao_3;?>>
            <label for='label3'>Atualizar Pre�os da Lista Pre�o <b>(Somente p/ Itens em Aberto e Itens Parciais em que o Pre�o Unit�rio � diferente de 0,00 e Normais de Linha)</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Transportar Itens p/ outro Pedido (Mesmo Fornecedor)' id='label4' <?=$disabled_opcao_4;?>>
            <label for='label4'>Transportar Itens p/ outro Pedido (Mesmo Fornecedor)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='5' title='Importar Cota��o' id='label5' <?=$disabled_opcao_5;?> checked>
            <label for='label5'>Importar Cota��o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='6' title='Importar OS(s)' id='label6' <?=$disabled_opcao_6;?>>
            <label for='label6'>Importar OS(s)</label>
        </td>
    </tr>
<?
/*Somente para o Usu�rio do D�rcio, Gladys, Roberto ou Fabio que posso estar fazendo esta altera��o da Empresa 
da Nota Fiscal, mesmo quando este j� estiver fechado ou importado para Nota Fiscal*/
    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 20 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 25) {
        /*Verifico se esse Pedido possui pelo menos uma antecipa��o, se sim j� n�o � mais 
        permitido se trocar a empresa do mesmo ...*/
        $sql = "SELECT `id_antecipacao` 
                FROM `antecipacoes` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_antecipacao = bancos::sql($sql);
        if(count($campos_antecipacao) == 1) {
            $class      = 'textdisabled';
            $disabled   = 'disabled';
            $rotulo     = '<font color="red"><b>EXISTE(M) ANTECIPA��O(�ES) NESSE PEDIDO</b></font>';
        }else {
            $class      = 'combo';
            $disabled   = '';
            $rotulo     = '';
        }
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                &nbsp;&nbsp;&nbsp;*&nbsp;Alterar Empresa do Pedido: 
            </font>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='alterar_empresa()' class='<?=$class;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $id_empresa_pedido);
            ?>
            </select>
            &nbsp;<?=$rotulo;?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
</form>
</body>
</html>