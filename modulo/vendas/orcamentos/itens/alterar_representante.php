<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if(!empty($_POST['cmb_representante'])) {
    $sql = "UPDATE `orcamentos_vendas_itens` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_orcamento_venda_item` = '$_POST[id_orcamento_venda_item]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script language = 'JavaScript'>
        parent.ativar_loading()
        parent.fechar_pop_up_div()
    </Script>
<?
    exit;
}

//Busca os Dados do PA no qual está sendo alterado o Representante ...
$sql = "SELECT pa.referencia, pa.discriminacao 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
        WHERE ovi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$referencia 	= $campos[0]['referencia'];
$discriminacao 	= $campos[0]['discriminacao'];

$sql = "SELECT DISTINCT(cr.id_cliente_representante), ovi.id_representante AS id_representante_atual, ov.id_orcamento_venda, ov.congelar, ed.id_empresa_divisao id_empresa_divisao_ed, r.id_representante, r.nome_fantasia AS nome_fantasia_rep, ed.razaosocial AS razaosocial_divisao, c.razaosocial AS cliente_razaosocial 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente and c.id_cliente = '$_GET[id_cliente]' 
        INNER JOIN `clientes_vs_representantes` cr ON cr.id_cliente = c.id_cliente 
        INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = cr.id_empresa_divisao 
        WHERE ovi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' ";
$campos = bancos::sql($sql);
if($campos[0]['congelar'] == 'N') {
    echo "<Script language = 'JavaScript'>alert('ORÇAMENTO NÃO ESTÁ CONGELADO !');window.close();</Script>";
    exit;
}
$id_representante_atual = $campos[0]['id_representante_atual'];
?>
<html>
<title>.:: Alterar Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.cmb_representante.focus()'>
<form name='form' method='post' action=''>
<!--Controle de Tela-->
<input type='hidden' name='id_orcamento_venda_item' value='<?=$_GET['id_orcamento_venda_item'];?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$campos[0]['id_orcamento_venda'];?>'>
<!--****************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Representante
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$referencia;?>
        </td>
        <td>
            <font color='yellow'>
                <b>Discriminação: </b>
            </font>
            <?=$discriminacao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
            <?=$campos[0]['cliente_razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <select name="cmb_representante" title="Selecione o Representante" class="combo">
            <?
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) {
                    if($id_representante_atual == $campos[$i]['id_representante']) {
                        $selected = 'selected';
                    }else {
                        $selected = '';
                    }
                    echo "<option value='".$campos[$i]['id_representante']."'".$selected.">".$campos[$i]['nome_fantasia_rep'].' / '.$campos[$i]['razaosocial_divisao']."</option>";
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>