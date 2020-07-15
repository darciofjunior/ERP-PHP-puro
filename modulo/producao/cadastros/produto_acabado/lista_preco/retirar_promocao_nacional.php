<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>PROMOÇÃO RETIRADA COM SUCESSO.</font>";

//Retira a Promoção dos Produtos p/ as Empresas Divisões selecionadas
if(!empty($_POST['chkt_empresa_divisao'])) {
    foreach($_POST['chkt_empresa_divisao'] as $id_empresa_divisao) {
/*Traz todos os grupos_empresas_divisão p/ poder achar os produtos através
da id_empresa_divisao selecionada*/
        $sql = "SELECT id_gpa_vs_emp_div 
                FROM `gpas_vs_emps_divs` 
                WHERE `id_empresa_divisao` = '$id_empresa_divisao' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Aqui já retira promoção de todos os P.A. através do id_grupo_empresa_divisao ...
            $sql = "UPDATE `produtos_acabados` SET `qtde_promocional` = '0', `preco_promocional` = '0', `qtde_promocional_b` = '0', `preco_promocional_b` = '0' WHERE `id_gpa_vs_emp_div` = '".$campos[$i]['id_gpa_vs_emp_div']."' ";
            bancos::sql($sql);
        }
        $valor = 1;
    }
}
?>
<html>
<head>
<title>.:: Retirar Promoção Nacional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++)   {
        if (elementos[i].type == 'checkbox')  {
            if (elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        resposta = confirm('VOCÊ TEM CERTEZA QUE DESEJA RETIRAR A PROMOÇÃO ?')
        if(resposta == true) {
            return true
        }else {
            return false
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Retirar Promoção Nacional
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa Divisão / Item(ns) c/ Promoção
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
//Listagem das Empresas Divisões
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
            echo $campos[$i]['razaosocial'];
//Aqui eu verifico o Total de Promoção por Divisão ...
            $sql = "SELECT COUNT(pa.id_produto_acabado) AS total_promocao_divisao 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_empresa_divisao = ed.id_empresa_divisao 
                    INNER JOIN `produtos_acabados` pa ON pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div AND (pa.`preco_promocional` > '0' OR pa.`preco_promocional_b` > '0') 
                    WHERE ed.`id_empresa_divisao` = '".$campos[$i]['id_empresa_divisao']."' ";
            $campos_total_promocao_divisao = bancos::sql($sql);
            echo ' <b>('.$campos_total_promocao_divisao[0]['total_promocao_divisao'].')</b>';
        ?>
        </td>
        <td>
            <input type='checkbox' name='chkt_empresa_divisao[]' value='<?=$campos[$i]['id_empresa_divisao']?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_retirar' value='Retirar' title='Retirar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>