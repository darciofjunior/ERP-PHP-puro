<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../../');
}

/*Através da variável "$id_produto_acabado_custo" passada por parâmetro, eu busco alguns dados p/ submeter 
via formulário abaixo ...*/
$vetor_valores_pa   = custos::dados_pa_para_custo_padrao($_GET['id_produto_acabado_custo']);
$id_gpa_vs_emp_div  = $vetor_valores_pa['id_gpa_vs_emp_div'];
$id_grupo_pa        = $vetor_valores_pa['id_grupo_pa'];
?>
<html>
<head>
<title>.:: Custo Padrão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Diâmetro Menor ...
    if(typeof(document.form.txt_diametro_menor) == 'object') {
        if(!texto('form', 'txt_diametro_menor', '1', '0123456789,. ', 'DIÂMETRO MENOR', '2')) {
            return false
        }
        return limpeza_moeda('form', 'txt_diametro_menor, ')
    }
}
</Script>
</head>
<body onload='document.form.elements[4].focus()'>
<form name='form' method='post' action = '../grupos_pas_vs_empresas_divisoes/vs_maquinas/vs_maquinas.php?passo=1' onsubmit='return validar()'>
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='id_gpa_vs_emp_div' value="<?=$id_gpa_vs_emp_div;?>">
<input type='hidden' name='id_grupo_pa' value="<?=$id_grupo_pa;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$_GET['id_produto_acabado_custo'];?>">
<input type='hidden' name='tela' value="<?=$_GET['tela'];?>">
<!--********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    /**********************************************************************/
    /**************************Grupo de Cossinetes*************************/
    /**********************************************************************/
    if($id_grupo_pa == 9) {//Cossinetes Manual ...
        //Esse Grupo específico não irá pedir nenhum parâmetro ...
?>
    <Script Language = 'JavaScript'>
        document.form.submit()
    </Script>
<?
        exit;
    }
?>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            Custo Padrão
        </td>
    </tr>
<?
    /**********************************************************************/
    /****************************Grupo de Pinos****************************/
    /**********************************************************************/
    if($id_grupo_pa == 39 || $id_grupo_pa == 45) {//Pinos DIN 1 ou Pinos 1:50 ou Pinos 1:48 ...
?>
    <tr class='linhanormal'>
        <td width='25%'>
            <b>Diâmetro Menor (mm):</b>
        </td>
        <td>
            <input type='text' name='txt_diametro_menor' onkeyup="verifica(this, 'moeda_especial', '1', '', event)" size='10' class='caixadetexto'>
        </td>
    </tr>
<?
    }else {
        echo 'EM DESENVOLVIMENTO ! ';
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.elements[4].focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>