<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');

//Aqui eu verifico qual é a Unidade do P.A. p/ travar alguns Options ...
$sql = "SELECT gpa.`id_familia`, pa.`discriminacao`, u.`sigla` 
	FROM `produtos_acabados` pa 
	INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
	INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
	INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
	WHERE pa.`id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_familia     = $campos[0]['id_familia'];
$discriminacao  = $campos[0]['discriminacao'];
$unidade        = $campos[0]['sigla'];

/*A última opção sempre estará habilitada se:

* Se a Unidade for Jogo e a Família for ("Lima" 3 / ou "Rosca Postiça" 30).
* Se a Família for ("Lima" 3 com a palavra "Cabo" na Discriminação) independente da Unidade.
* Se a Família "Macho" 9 independente da Unidade;

Caso contrário esta opção fica desabilitada e as 2 primeiras ficam habilitadas.*/

if(($unidade == 'JG' && ($id_familia == 3 || $id_familia == 30)) || (strpos($discriminacao, 'CABO') > 0) || $id_familia == 9) {
    $disabled1 = 'disabled';
    $disabled2 = 'disabled';
    $disabled3 = '';
}else {//Qualquer outra unidade, habilitado todas as Opções, menos a unidade de Jogo ...
    $disabled1 = '';
    $disabled2 = '';
    $disabled3 = 'disabled';
}
?>
<html>
<head>
<title>.:: Substituir Estoque do Produto Acabado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Gerar O.E. (Saída) ...
        window.location = 'gerar_oe.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Gerar O.E. em Lote ...
        window.location = '../../producao/oes/gerar_oe_em_lote.php'
    }else if(document.form.opt_opcao[2].checked == true) {//Montar / Desmontar Jogos ...
        window.location = 'montar_jogos.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>'
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Substituir Estoque do Produto Acabado
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow'>Produto: </font>
            <?=intermodular::pa_discriminacao($id_produto_acabado, 0);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Gerar O.E. (Saída)' id='label1' <?=$disabled1;?>>
            <label for='label1'>Gerar O.E. (Saída)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Gerar O.E. em Lote' id='label2' <?=$disabled2;?>>
            <label for='label2'>Gerar O.E. em Lote</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' title='Montar / Desmontar Jogo(s)' id='label3' <?=$disabled3;?>>
            <label for='label3'>
                Montar Jogo(s) / Desmontar Jogo(s) 
                <font color='red' size='2'><b>(NÃO GERA OE)</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>

A última opção sempre estará habilitada se:

* Se a Unidade for Jogo e a Família for ("Lima" 3 / ou "Rosca Postiça" 30).
* Se a Família for ("Lima" 3 com a palavra "Cabo" na Discriminação) independente da Unidade.
* Se a Família "Macho" 9 independente da Unidade;

Caso contrário esta opção fica desabilitada e as 2 primeiras ficam habilitadas.
</pre>