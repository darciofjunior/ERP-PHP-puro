<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/atualizar_cotas/atualizar_cotas.php', '../../../');
$mensagem[1] = '<font class="confirmacao">COTA(S) MENSAL(IS) ALTERADA(S) COM SUCESSO.</font>';

if(!empty($_POST['hdd_pais'])) {//Atualiza a Cota Mensal do País ...
    foreach($_POST['hdd_pais'] as $i => $id_pais) {
        $sql = "UPDATE `paises` SET `cota_mensal` = '".$_POST['txt_cota_mensal'][$i]."' WHERE `id_pais` = '$id_pais' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Aqui eu Busco todos os País(es) Cadastrado(s) ...
$sql = "SELECT * 
        FROM `paises` 
        ORDER BY pais ";
$campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: País(es) vs Cota(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
            //Preparo os campos de Cota p/ poder gravar no BD ...
            elementos[i].value = elementos[i].value.replace('.', '')
            elementos[i].value = elementos[i].value.replace(',', '.')
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            País(es) vs Cota(s)
        </td>
    </tr>
<?
	if($linhas == 0) {
?>
    <tr class="atencao" align="center">
        <td colspan='3'>
            <font size='-1'>
                NÃO HÁ PAÍS(ES) CADASTRADO(S).
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class="linhanormal" align="center">
        <td bgcolor="#CCCCCC">
            <b>Código País</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>País</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Cota Mensal</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['codigo_pais'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['pais'];?>
        </td>
        <td>
            <input type='text' name='txt_cota_mensal[]' value='<?=number_format($campos[$i]['cota_mensal'], 2, ',', '.');?>' title='Digite a Cota Mensal' onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <!--*********************Controle de Tela*********************-->
            <input type='hidden' name='hdd_pais[]' value='<?=$campos[$i]['id_pais'];?>'>
            <!--**********************************************************-->
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'atualizar_cotas.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>