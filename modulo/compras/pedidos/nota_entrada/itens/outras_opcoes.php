<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>EMPRESA ALTERADA COM SUCESSO.</font>";

/**************************Ferramentinha para Trocar a Empresa da Nota Fiscal**************************/
//Toda vez em que se desejar alterar a Empresa da Nota Fiscal, o Sistema irá cair nessa rotina ...
if(!empty($_POST['cmb_empresa'])) {//Alterando a Empresa da Nota Fiscal ...
    $tipo = ($_POST['cmb_empresa'] == 4) ? 2 : 1;
//Alterando os dados de Empresa da Nota Fiscal ...
    $sql = "UPDATE `nfe` SET `id_empresa` = '$_POST[cmb_empresa]', `tipo` = '$tipo' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    bancos::sql($sql);
//Através da Nota Fiscal, eu busco quais os Pedidos que estão atrelados a esta ...
    $sql = "SELECT DISTINCT(id_pedido) 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' ORDER BY id_pedido ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Alterando todos os Pedidos atrelados a Nota Fiscal ...
    for($i = 0; $i < $linhas; $i++) {
//Aqui eu busco qual é o Desconto do Pedido p/ substituir também ...
        $sql = "SELECT desc_ddl 
                FROM `pedidos` 
                WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
        $campos_pedido  = bancos::sql($sql);
        $desc_ddl       = $campos_pedido[0]['desc_ddl'];

        if($_POST['cmb_empresa'] == 4) {//Se a Empresa desejada pelo usuário for Grupo ...
            $desc_ddl = str_replace('NF', 'SGD', $desc_ddl);
        }else {//Se a Empresa desejada pelo usuário for Alba ou Tool ...
            $desc_ddl = str_replace('SGD', 'NF', $desc_ddl);
        }
//Alterando os dados de Empresa do Pedido ...
        $sql = "UPDATE `pedidos` SET `id_empresa` = '$_POST[cmb_empresa]', `desc_ddl` = '$desc_ddl', `tipo_nota` = '$tipo' WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;//Mensagem de Retorno ...
?>
    <Script Language = 'Javascript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}
/*************************************************************************************************/

//Aqui eu busco o id_empresa da Nota Fiscal ...
$sql = "SELECT id_empresa, situacao 
        FROM `nfe` 
        WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);
$id_empresa_nf  = $campos_empresa[0]['id_empresa'];
$situacao       = $campos_empresa[0]['situacao'];

//Verifico a Qtde de Itens existentes nessa Nota Fiscal ...
$sql = "SELECT COUNT(`id_nfe_historico`) AS qtde_itens 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = '$_GET[id_nfe]' ";
$campos_qtde_itens  = bancos::sql($sql);
$qtde_itens         = $campos_qtde_itens[0]['qtde_itens'];

//Se a Nota Fiscal de Entrada estiver Liberada, tenho de travar as opções ...
if($situacao == 0 || $situacao == 1) {
    $disabled_opcoes = '';
}else {
    $disabled_opcoes = 'disabled';
}
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    for (i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio' && document.form.elements[i].checked == true) {
            var valor = document.form.elements[i].value
            break;
        }
    }
    if(valor == 1) {
        window.location = 'incluir_antecipacao.php?id_nfe=<?=$_GET['id_nfe'];?>'
    }else if(valor == 2) {
        window.location = 'excluir_antecipacao.php?id_nfe=<?=$_GET['id_nfe'];?>'
    }else if(valor == 3) {
        window.location = 'conferencia_entrega_pi.php?id_nfe=<?=$_GET['id_nfe'];?>'
    }else if(valor == 4) {
        window.location = 'conferencia_entrega_prac.php?id_nfe=<?=$_GET['id_nfe'];?>'
    }
}

function alterar_empresa(qtde_itens_os) {
    if(qtde_itens_os == 1) {
        alert('A EMPRESA DESSA NF NÃO PODE SER SUBSTITUÍDA DEVIDO CONTER ITEM(NS) DE OS !!!')
        return false
    }else {
        if(document.form.cmb_empresa.value == '') {
            alert('SELECIONE UMA EMPRESA !')
            document.form.cmb_empresa.focus()
            return false
        }else {
            var id_empresa_nf   = eval('<?=$id_empresa_nf;?>')
            var qtde_itens      = eval('<?=$qtde_itens;?>')
            /*Se a Empresa atual da Nota Fiscal = 'ALBAFER' ou 'TOOL MASTER' e o usuário está tentando mudar p/ 'GRUPO' ...
            Se a Empresa atual da Nota Fiscal = 'GRUPO' e o usuário está tentando mudar p/ 'ALBAFER' ou 'TOOL MASTER' ...
            
            Somente quando existir pelo menos 1 item em NF que o Sistema fará essa verificação ...*/
            if(((id_empresa_nf == 1 || id_empresa_nf == 2) && document.form.cmb_empresa.value == 4 && qtde_itens > 0) || (id_empresa_nf == 4 && (document.form.cmb_empresa.value == 1 || document.form.cmb_empresa.value == 2) && qtde_itens > 0)) {
                alert('NÃO É POSSÍVEL SUBSTITUIR A EMPRESA DESSA NOTA FISCAL !!!\n\nVOCÊ ESTÁ ALTERANDO DE "NF p/ SGD" OU "SGD p/ NF", O QUE INFLUENCIA NOS IMPOSTOS, SENDO ASSIM EXCLUA O(S) ITEM(NS) E IMPORTE-OS NOVAMENTE !')
                document.form.reset()
                return false
            }

            //Nesse caso representa que o usuário realmente esta fazendo uma mudança ...
            if(id_empresa_nf != document.form.cmb_empresa.value) {
                var resposta = confirm('TEM CERTEZA DE QUE DESEJA SUBSTITUIR A EMPRESA DA NOTA FISCAL ?')
                if(resposta == true) document.form.submit()
            }else {
                document.form.reset()
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
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
            <input type='radio' name='opt_opcao' value='1' title='Antecipação(ões) Pendente(s)' id='label' <?=$disabled_opcoes;?>>
            <label for='label'>Antecipação(ões) Pendente(s)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Excluir Antecipação(ões)' id='label2' <?=$disabled_opcoes;?>>
            <label for='label2'>Excluir Antecipação(ões)</label>
        </td>
    </tr>
<?
//Verifico se tem pelo menos 1 item da Nota Fiscal que é do Tipo Aço para poder Habilitar essa Opção
    $sql = "SELECT nfeh.id_nfe_historico 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = nfeh.id_produto_insumo 
            INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = pi.id_produto_insumo 
            WHERE nfeh.id_nfe = '$_GET[id_nfe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Se não encontrar nenhum item, desabilito essa Opção
        $disabled = 'disabled';
    }else {//Se encontrou pelo menos 1 item a opção é habilitada
        $disabled = '';
    }
?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Conferência de Entrega do PI (Aço)' id='label3' checked <?=$disabled;?>>
            <label for='label3'>Conferência de Entrega do PI (Aço)</label>
        </td>
    </tr>
<?
//Verifico se tem pelo menos 1 item da Nota Fiscal que é PRAC para poder Habilitar essa Opção
    $sql = "SELECT nfeh.id_nfe_historico 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` 
            WHERE nfeh.id_nfe = '$_GET[id_nfe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Se não encontrar nenhum item, desabilito essa Opção
        $disabled = 'disabled';
    }else {//Se encontrou pelo menos 1 item a opção é habilitada
        $disabled = '';
    }
?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Conferência de Entrega dos demais PI´s / PRAC' id='label4' <?=$disabled_opcoes;?> <?=$disabled;?>>
            <label for='label4'>Conferência de Entrega dos demais PI´s / PRAC</label>
        </td>
    </tr>
<?
/*Somente p/ os usuários Gladys 14, Roberto 62, Fabio Petroni 64, Dárcio 98 e Netto 147 
porque programam que posso estar fazendo esta alteração da Empresa da Nota Fiscal, 
mesmo quando este já estiver fechado ou importado para Nota Fiscal*/
    if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        /*Aqui eu verifico se na NF existe algum item de Pedido que 
        tem algum vínculo com OS ...*/
        $sql = "SELECT id_os_item 
                FROM `oss_itens` oi 
                INNER JOIN `itens_pedidos` ip ON ip.id_item_pedido = oi.id_item_pedido 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.id_item_pedido = ip.id_item_pedido 
                WHERE oi.`id_nfe` = '$id_nfe' LIMIT 1 ";
        $campos_itens_os    = bancos::sql($sql);
        $qtde_itens_os      = count($campos_itens_os);
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                &nbsp;&nbsp;&nbsp;*&nbsp;Alterar Empresa da Nota Fiscal: 
            </font>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange="alterar_empresa('<?=$qtde_itens_os;?>')" class='combo' <?=$disabled_opcoes;?>>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nfe' value="<?=$_GET['id_nfe'];?>">
</form>
</body>
</html>