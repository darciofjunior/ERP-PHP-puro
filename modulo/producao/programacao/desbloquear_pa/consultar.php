<?
require('../../../../lib/segurancas.php');
if(empty($pop_up)) require '../../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) À SER(EM) DESBLOQUEADO(S).</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO(S) ACABADO(S) DESBLOQUEADO(S) COM SUCESSO.</font>";

if(!empty($_POST['chkt_produto_acabado'])) {
    foreach($_POST['chkt_produto_acabado'] as $id_produto_acabado) {
        $sql = "UPDATE `estoques_acabados` SET `status` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 2;
}
?>
<html>
<head>
<title>.:: Desbloquear Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?    
    //Essa query verifica se existe(m) PA(s) Bloqueados ...
    $sql = "SELECT pa.*, ed.razaosocial, gpa.nome 
            FROM `produtos_acabados` pa 
            INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado AND ea.`status` = '1' 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            WHERE pa.`ativo` = '1' ORDER BY pa.discriminacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhum PA Bloqueado ...
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../estoque/gerenciar/consultar.php<?=$parametro;?>'" class='botao'>
        </td>
    </tr>
<?
    }else {//Existem PAs Bloqueados ...
?>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Desbloquear Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Grupo P.A. / Empresa Divisão
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado[]' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../estoque/gerenciar/consultar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick="window.location = 'consultar.php'" class='botao'>
            <input type='submit' name='cmd_desbloquear' value='Desbloquear' title='Desbloquear' style='color:green' class='botao'>
            <!--****************************************************************************
            Essa variável serve para saber se essa tela é uma tela simples ou é um Pop-Up
            Se for igual 1, então significa q essa tela é um Pop-Up e que não podemos mostrar o menu nesta ...
            *****************************************************************************-->
            <input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>