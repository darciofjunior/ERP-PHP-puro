<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');//Essa biblioteca é requerida dentro da Vendas ...
require('../../../../lib/intermodular.php');//Essa biblioteca é requerida dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if(!empty($_POST['id_produto_acabado_custo'])) {
//Atualização do Funcionário que alterou os dados no custo ...
    $sql = "UPDATE `produtos_acabados_custos` SET `qtde_lote` = '$_POST[txt_qtde_lote]', `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    //Aqui chama a função para recalcular do preço do item do orçamento
    vendas::recalcular_item_orcamento($_POST['id_produto_acabado_custo']);
    $valor = 1;
}

//Tratamento com a variavel recebida por parametro ...
$id_produto_acabado_custo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];
    
$sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pac.qtde_lote, pac.peca_corte 
        FROM `produtos_acabados_custos` pac 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
        WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos         = bancos::sql($sql);
$pecas_corte    = ($campos[0]['peca_corte'] == 0) ? 1 : $campos[0]['peca_corte'];
?>
<html>
<head>
<title>.:: Alterar Quantidade do Lote ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_qtde_lote.value == 0) {
        alert('QUANTIDADE DO LOTE INVÁLIDA ! \nVALOR INFERIOR A HUM !')
        document.form.txt_qtde_lote.focus()
        document.form.txt_qtde_lote.select()
        return false
    }
//Aqui é uma verificação para saber se a qtde do lote é multiplo das peças / corte
    var pecas_corte     = eval('<?=$pecas_corte;?>')
    var referencia      = '<?=$campos[0]['referencia'];?>'
    var operacao_custo  = '<?=$campos[0]['operacao_custo'];?>'
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
    if(referencia == 'ESP' && operacao_custo == 0) {
        if(document.form.txt_qtde_lote.value % pecas_corte != 0) {//Significa que não é múltiplo de peças por corte
            alert('QUANTIDADE DO LOTE INVÁLIDA ! QUANTIDADE DO LOTE NÃO ESTÁ COMPATÍVEL COM A QTDE DE PEÇAS/CORTE !')
            document.form.txt_qtde_lote.focus()
            document.form.txt_qtde_lote.select()
            return false
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_qtde_lote.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            Alterar Quantidade do Lote
        </td>
    </tr>
    <tr class='linhadestaque'> 
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                <font color='#FFFF00'>Ref.:</font>
                <?=$campos[0]['referencia'];?>
                - <font color='#FFFF00'>Discrim.:</font>
                <?=$campos[0]['discriminacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <b>Operação de Custo:</b>
        </td>
        <td>
        <?
            if($campos[0]['operacao_custo'] == 0) {//Industrialização
                echo 'Industrialização';
            }else {//Revenda
                echo 'Revenda';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <b>Quantidade do Lote:</b>
        </td>
        <td> 
            <input type='text' name='txt_qtde_lote' value='<?=$campos[0]['qtde_lote'];?>' onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_qtde_lote.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>