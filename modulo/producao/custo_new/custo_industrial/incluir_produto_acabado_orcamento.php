<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
session_start('funcionarios');

$mensagem[1] = '<font class="erro">ESTE PRODUTO ACABADO JÁ EXISTENTE PARA ESTE ORÇAMENTO.</font>';
$mensagem[2] = '<font class="confirmacao">PRODUTO ACABADO INCLUÍDO COM SUCESSO PARA O ORC.</font>';
$mensagem[3] = '<font class="erro">ESTE ORÇAMENTO NÃO EXISTE OU ESTÁ CONGELADO.</font>';

if(!empty($_POST['cmd_incluir'])) {
    $sql_orcamento = "SELECT `id_orcamento_venda` 
                      FROM `orcamentos_vendas`
                      WHERE id_orcamento_venda = '$_POST[txt_num_orc]' 
                      AND `congelar` = 'N' LIMIT 1 ";
    $campos_orcamento = bancos::sql($sql_orcamento);
    $linhas_orcamento = count($campos_orcamento);
    if($linhas_orcamento == 0) {
        $valor = 3;
    }else {
        $sql = "SELECT id_orcamento_venda_item 
                FROM `orcamentos_vendas_itens`
                WHERE id_orcamento_venda = '$_POST[txt_num_orc]' 
                AND id_produto_acabado = '$id_produto_acabado' LIMIT 1";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            $valor = 1;
        }else {
            $sql = "SELECT gp.prazo_entrega
                    FROM `produtos_acabados` pa
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div
                    INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa
                    WHERE pa.id_produto_acabado = '$id_produto_acabado' LIMIT 1 ";
            $campos_prazo_entrega = bancos::sql($sql);
            $prazo_entrega_grupo = $campos_prazo_entrega[0]['prazo_entrega'];
            $data_sys = date("Y-m-d");
            
            $sql = "INSERT INTO `orcamentos_vendas_itens` (`id_orcamento_venda_item`, `id_orcamento_venda`, `id_produto_acabado`, `qtde`, `prazo_entrega`, `data_sys`) 
                    VALUES (NULL, '$_POST[txt_num_orc]', '$id_produto_acabado', '$_POST[txt_qtde]', '$prazo_entrega_grupo', '$data_sys') ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
}
?>
<html>
<head>
<title>.:: Incluir Produto(s) Acabado(s) no Orçamento ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_num_orc.value == '') {
        alert("DIGITE O NÚMERO DO ORÇAMENTO!!!");
        document.form.txt_num_orc.focus();
        return false;
    }
    if(document.form.txt_qtde.value == '') {
        alert("DIGITE A QTDE!!!");
        document.form.txt_qtde.focus();
        return false;
    }    
}
</Script>
</head>
<form name="form" method="post" action="" onsubmit="return validar();">
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2' width="750">
            <b><?=$mensagem[$valor];?></b>
        </td>
	</tr>
	<tr class='linhacabecalho'>
            <td colspan="2" align='center'>
                <font color='#FFFFFF' size='-1'>
                    Incluir Produto(s) Acabado(s) no Orçamento
                </font>
            </td>
        </tr>
        <tr class='linhanormal' align="center">
            <td>
                N° Orc: <input type="text" name="txt_num_orc" value="" class="caixadetexto" onkeyup="verifica(this, 'aceita', 'numeros', '', event)">
            </td>
        </tr>
        <tr class='linhanormal' align="center">    
            <td>
                Qtde: <input type="text" name="txt_qtde" value="" class="caixadetexto" onkeyup="verifica(this, 'aceita', 'numeros', '', event)">
            </td>
        </tr>
	<tr class='linhacabecalho' align="center">
            <td>
                <input type="submit" name="cmd_incluir" value="Salvar" tabindex="5" class="botao" title="Salvar">
                <input type="button" name="cmd_fechar" style="color:red" value="Fechar" title="Fechar" onclick="window.close()" class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>
