<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/grupos_pas_vs_empresas_divisoes/vs_maquinas/vs_maquinas.php ', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>M�QUINA(S) INCLUIDA(S) PARA ESTE GRUPO vs EMPRESA DIVIS�O COM SUCESSO.</font>";

$id_gpa_vs_emp_div = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_gpa_vs_emp_div'] : $_GET['id_gpa_vs_emp_div'];

/******************************************************************************/
/***********Recadastro as M�quinas que j� foram cadastradas novamente**********/
/******************************************************************************/
if(!empty($_GET['id_maquinas_atreladas'])) {//Aqui eu recadastro essas M�quinas que anteriormente foram cadastradas ...
    $vetor_maquina = explode(',', $_GET['id_maquinas_atreladas']);
    foreach($vetor_maquina as $id_maquina) {
        $sql = "INSERT INTO `gpas_vs_emps_divs_vs_maquinas` (`id_gpa_vs_emp_div_vs_maquina`, `id_gpa_vs_emp_div`, `id_maquina`, `diametro_aco_menor_igual`) VALUES (NULL, '$_GET[id_gpa_vs_emp_div]', '$id_maquina', '$_GET[txt_diametro_aco_menor_igual]') ";
        bancos::sql($sql);
        $valor = 1;
    }
}
/******************************************************************************/

if(!empty($_POST['cmb_maquina'])) {
    $maquinas_atreladas = '';//Essa vari�vel ser� utilizada mais abaixo ...
    foreach($_POST['cmb_maquina'] as $id_maquina) {
        //Verifico se essa M�quina j� foi cadastrada anteriormente para esse Grupo vs Empresa Divis�o com esse Di�metro ...
        $sql = "SELECT id_gpa_vs_emp_div_vs_maquina 
                FROM `gpas_vs_emps_divs_vs_maquinas` 
                WHERE `id_gpa_vs_emp_div` = '$_POST[id_gpa_vs_emp_div]' 
                AND `id_maquina` = '$id_maquina' 
                AND `diametro_aco_menor_igual` = '$_POST[txt_diametro_aco_menor_igual]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//N�o encontrou a m�quina do Loop cadastrada p/ o Grupo vs Empresa Divis�o com esse Di�metro de A�o ...
            $sql = "INSERT INTO `gpas_vs_emps_divs_vs_maquinas` (`id_gpa_vs_emp_div_vs_maquina`, `id_gpa_vs_emp_div`, `id_maquina`, `diametro_aco_menor_igual`) VALUES (NULL, '$_POST[id_gpa_vs_emp_div]', '$id_maquina', '$_POST[txt_diametro_aco_menor_igual]') ";
            bancos::sql($sql);
            $valor = 1;
        }else {//M�quina j� existente ...
            $id_maquinas_atreladas.= $id_maquina.', ';

            //Busca o nome da M�quina do Loop p/ exibir no confirm abaixo ...
            $sql = "SELECT nome 
                    FROM `maquinas` 
                    WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
            $campos_maquina = bancos::sql($sql);
            $maquinas_atreladas.= strtoupper($campos_maquina[0]['nome']).', ';
        }
    }

    if(!empty($maquinas_atreladas)) {//Se existirem M�quinas atreladas ent�o ...
        $id_maquinas_atreladas  = substr($id_maquinas_atreladas, 0, strlen($id_maquinas_atreladas) - 2);
        $maquinas_atreladas     = substr($maquinas_atreladas, 0, strlen($maquinas_atreladas) - 2);
?>
    <Script Language = 'JavaScript'>
        var resposta = confirm('ESSA(S) M�QUINA(S): "<?=$maquinas_atreladas;?>"; \n\nJ� EST�(�O) CADASTRADA(S) P/ ESTE GRUPO vs EMPRESA DIVIS�O COM ESSE DI�METRO DE A�O !!!\n\nDESEJA CADASTR�-LA(s) NOVAMENTE ?')
        if(resposta == true) window.location = 'incluir_maquina.php?id_maquinas_atreladas=<?=$id_maquinas_atreladas;?>&id_gpa_vs_emp_div=<?=$_POST[id_gpa_vs_emp_div];?>&txt_diametro_aco_menor_igual=<?=$_POST['txt_diametro_aco_menor_igual'];?>'
    </Script>
<?
    }
}
?>
<html>
<head>
<title>.:: Incluir M�quina(s) para Grupo vs Empresa Divis�o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Di�metro do A�o <= ...
    if(!texto('form', 'txt_diametro_aco_menor_igual', '3', '0123456789,.', 'DI�METRO DO A�O <=', '2')) {
        return false
    }
//M�quina ...
    var elementos       = document.form.elements
    var selecionados    = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) selecionados ++
            }
        }
    }
    if(selecionados == 0) {
        alert('SELECIONE UMA M�QUINA !')
        return false
    }else if(selecionados > 100) {
        alert('EXCEDIDO O N�MERO DE M�QUINA(S) SELECIONADA(S) !\n\nPERMITIDO NO M�XIMO 100 REGISTROS POR VEZ !')
        return false
    }
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP ...
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_diametro_aco_menor_igual, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href
}
</Script>
</head>
<body onload='document.form.txt_diametro_aco_menor_igual.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='id_gpa_vs_emp_div' value="<?=$id_gpa_vs_emp_div;?>">
<!--********************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir M�quina(s) para este Grupo vs Empresa Divis�o: 
            <font color='yellow'>
            <?
                $sql = "SELECT CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS grupo_vs_empresa_divisao 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.`id_empresa_divisao` 
                        WHERE ged.`id_gpa_vs_emp_div` = '$_GET[id_gpa_vs_emp_div]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                echo $campos[0]['grupo_vs_empresa_divisao'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Di�metro do A�o <= :</b>
        </td>
        <td>
            <input type='text' name='txt_diametro_aco_menor_igual' title='Digite o Di�metro do A�o <=' size='5' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '1', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>M�quina: </b>
        </td>
        <td>
            <select name='cmb_maquina[]' size='5' class='combo' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
            <?
                $sql = "SELECT id_maquina, nome 
                        FROM `maquinas` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
            
                for($i = 0; $i < $linhas; $i ++) {
            ?>
                <option value="<?=$campos[$i]['id_maquina'];?>"><?=$campos[$i]['nome'];?></option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_diametro_aco_menor_igual.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>