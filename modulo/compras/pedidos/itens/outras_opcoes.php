<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>EMPRESA ALTERADA COM SUCESSO.</font>";

/**************************Ferramentinha para Trocar a Empresa do Pedido**************************/
//Toda vez em que se desejar alterar a Empresa do Pedido, o Sistema irá cair nessa rotina ...
if(!empty($_POST['cmb_empresa'])) {//Alterando a Empresa do Pedido ...
//Aqui eu busco qual é o Desconto do Pedido p/ substituir também ...
    $sql = "SELECT desc_ddl 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $desc_ddl   = $campos[0]['desc_ddl'];

    if($_POST['cmb_empresa'] == 4) {//Se a Empresa desejada pelo usuário for Grupo ...
        $desc_ddl = str_replace('NF', 'SGD', $desc_ddl);
        $tipo_nota = 2;
    }else {//Se a Empresa desejada pelo usuário for Alba ou Tool ...
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

//Procedimento Normal da Tela de Outras Opções ...
//Verifica a situação do Pedido de Compras
$sql = "SELECT id_empresa, status 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Tenho que renomear essa variável pq já existe uma variável com esse de id_empresa na Sessão do Sistema ...
$id_empresa_pedido  = $campos[0]['id_empresa'];
$status             = $campos[0]['status'];
if($status == 2) {//Se o Pedido estiver concluído
    $disabled_opcao_2 = 'disabled';//não posso fazer requisição de Materiais
}else {
    $disabled_opcao_2 = '';
}

/*************************Critérios para a Importação de uma OS*************************/
//1) Verifica a qtde de Item(ns) desse Pedido
$sql = "SELECT COUNT(id_item_pedido) AS qtde_itens_pedidos 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' ";
$campos = bancos::sql($sql);
$qtde_itens_pedidos = $campos[0]['qtde_itens_pedidos'];
if($qtde_itens_pedidos > 0) {//Se existir pelo menos 1 item, então ...
    $disabled_opcao_7 = 'disabled';//Eu não posso importar OS
}else {//Se não tiver nenhum item de pedido, desabilito a Opção
    $disabled_opcao_7 = '';//Eu posso importar OS
}
//2) O pedido corrente só pode estar em apenas 1 OS atrelada
$sql = "SELECT id_os 
        FROM `oss` 
        WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {//Se existir pelo menos 1 item, então eu não posso importar OS
    $disabled_opcao_3 = 'disabled';//Eu não posso mexer na Opção de Atualizar Preços da Lista de Preço
    $disabled_opcao_4 = 'disabled';//Eu não posso transportar Itens Pedido quando tiver OS
    $disabled_opcao_5 = 'disabled';//Eu não posso importar Cotação quando tiver OS
    $disabled_opcao_6 = 'disabled';//Eu não posso importar OS
}
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Antecipação de Pedido ...
        window.location = '../antecipacoes.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Requisição de Material ...
        window.location = '../requisicao_materiais/consultar_pedido.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[2].checked == true) {//Atualizar Lista de Preço ...
        var resposta = confirm('VOCÊ DESEJA ATUALIZAR O PREÇO DESSE(S) ITEM(NS) COM OS PREÇO(S) DA LISTA ?\n\nOBS: ESTA ALTERAÇÃO AFETARÁ SOMENTE ITEM(NS) EM ABERTO E ITEM(NS) PARCIAL(IS) EM QUE O PREÇO UNITÁRIO SEJA DIFERENTE DE 0,00 E NORMAL DE LINHA !')
        if(resposta == true) window.location = 'atualizar_precos.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[3].checked == true) {//Transportar outro Pedido ...
        window.location = 'transportar_outro_pedido.php?id_pedido=<?=$_GET['id_pedido'];?>'
    }else if(document.form.opt_opcao[4].checked == true) {//Importar Cotação ...
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
            Outras Opções
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Antecipação(ões) de Pedido' id='label'>
            <label for='label'>Antecipação(ões) de Pedido</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Requisição de Materiais' id='label2' <?=$disabled_opcao_2;?>>
            <label for='label2'>Requisição de Materiais</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Atualizar Preços da Lista Preço (Somente p/ Itens em Aberto e Itens Parciais)' id='label3' <?=$disabled_opcao_3;?>>
            <label for='label3'>Atualizar Preços da Lista Preço <b>(Somente p/ Itens em Aberto e Itens Parciais em que o Preço Unitário é diferente de 0,00 e Normais de Linha)</b></label>
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
            <input type='radio' name='opt_opcao' value='5' title='Importar Cotação' id='label5' <?=$disabled_opcao_5;?> checked>
            <label for='label5'>Importar Cotação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='6' title='Importar OS(s)' id='label6' <?=$disabled_opcao_6;?>>
            <label for='label6'>Importar OS(s)</label>
        </td>
    </tr>
<?
/*Somente para o Usuário do Dárcio, Gladys, Roberto ou Fabio que posso estar fazendo esta alteração da Empresa 
da Nota Fiscal, mesmo quando este já estiver fechado ou importado para Nota Fiscal*/
    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 20 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 25) {
        /*Verifico se esse Pedido possui pelo menos uma antecipação, se sim já não é mais 
        permitido se trocar a empresa do mesmo ...*/
        $sql = "SELECT `id_antecipacao` 
                FROM `antecipacoes` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_antecipacao = bancos::sql($sql);
        if(count($campos_antecipacao) == 1) {
            $class      = 'textdisabled';
            $disabled   = 'disabled';
            $rotulo     = '<font color="red"><b>EXISTE(M) ANTECIPAÇÃO(ÕES) NESSE PEDIDO</b></font>';
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
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
</form>
</body>
</html>