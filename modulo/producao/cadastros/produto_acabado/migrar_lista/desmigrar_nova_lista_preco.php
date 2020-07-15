<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/migrar_lista/migrar_lista.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>NOVA LISTA DE PREÇO DESMIGRADA COM SUCESSO.</font>";

if(!empty($_POST['chkt_gpa_vs_emp_div'])) {
    $data_sys = date('Y-m-d H:i:s');
    //Disparo de foreach, em cima dos Grupo(s) PA(s) - Empresas Divisões Selecionados
    foreach($_POST['chkt_gpa_vs_emp_div'] as $id_gpa_vs_emp_div) {
        /*Atualização dos Novos Descontos pelos Descontos Velhos, isso quando existiu alguma Migração anterior 
        evitando aí Futuros Transtornos claro ...*/
        $sql = "UPDATE `gpas_vs_emps_divs` 
                SET `id_func_desmigrador_lista` = '$_SESSION[id_funcionario]', `desc_base_a_nac` = `desc_base_a_nac_bkp`, `desc_base_b_nac` = `desc_base_b_nac_bkp`, `acrescimo_base_nac` = `acrescimo_base_nac_bkp`, `data_desmigrador_lista` = '$data_sys' 
                WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
                AND `desc_base_a_nac_bkp` > '0' ";
        bancos::sql($sql);
        
        /*Atualização do Novo Preço pelo Preço Velho, isso quando existiu alguma Migração anterior 
        evitando aí Futuros Transtornos claro ...*/
        $sql = "UPDATE `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                SET pa.`preco_unitario` = pa.`preco_unitario_bkp`, ged.`id_func_desmigrador_lista` = '$_SESSION[id_funcionario]', ged.`data_desmigrador_lista` = '$data_sys' 
                WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
                AND pa.`preco_unitario_bkp` > '0' ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Seleção de todos os Grupos, só não traz nada referente a Componentes
$sql = "SELECT ged.id_gpa_vs_emp_div, ged.id_func_migrador_lista, ged.id_func_desmigrador_lista, ged.data_migrador_lista, ged.data_desmigrador_lista, CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS rotulo 
        FROM `empresas_divisoes` ed 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_empresa_divisao = ed.id_empresa_divisao 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` <> '23' 
        WHERE gpa.`ativo` = '1' ORDER BY gpa.nome, ed.razaosocial ";
$campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Desmigrar Nova Lista de Preço ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var x, mensagem = '', valor = false, elementos = document.form.elements
    for (x = 0; x < elementos.length; x ++)   {
        if (elementos[x].type == 'checkbox')  {
            if (elementos[x].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        mensagem = confirm('DESEJA DESMIGRAR ESTE(S) GRUPO(S) PA(S) SELECIONADO(S) ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post' onsubmit="return validar()">
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='4'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Desmigrar Nova Lista de Preço
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Grupo P.A. (Empresa Divisão)
        </td>
        <td>
            Migrador * Data - Hora
        </td>
        <td>
            Desmigrador * Data - Hora
        </td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align="left">
            <?=$campos[$i]['rotulo'];?>
        </td>
        <td align="left">
        <?
//Se existir funcionário responsável
                if($campos[$i]['id_func_migrador_lista'] != 0) {
//Busca do Funcionário Responsável pela Alteração do Grupo P.A. (Empresa Divisão)
                    $sql = "SELECT nome 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '".$campos[$i]['id_func_migrador_lista']."' LIMIT 1 ";
                    $campos_funcionario = bancos::sql($sql);
                    echo $campos_funcionario[0]['nome'].' * ';
//Apresentação dos Dados de Data e Hora
                    echo data::datetodata(substr($campos[$i]['data_migrador_lista'], 0, 10), '/').' - ';
                    echo substr($campos[$i]['data_migrador_lista'], 11, 8);
                }
        ?>
        </td>
        <td align="left">
        <?
//Se existir funcionário responsável
                if($campos[$i]['id_func_desmigrador_lista'] != 0) {
//Busca do Funcionário Responsável pela Alteração do Grupo P.A. (Empresa Divisão)
                        $sql = "SELECT nome 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos[$i]['id_func_desmigrador_lista']."' LIMIT 1 ";
                        $campos_funcionario = bancos::sql($sql);
                        echo $campos_funcionario[0]['nome'].' * ';
//Apresentação dos Dados de Data e Hora
                        echo data::datetodata(substr($campos[$i]['data_desmigrador_lista'], 0, 10), '/').' - ';
                        echo substr($campos[$i]['data_desmigrador_lista'], 11, 8);
                }
        ?>
        </td>
        <td>
            <input type="checkbox" name="chkt_gpa_vs_emp_div[]" value="<?=$campos[$i]['id_gpa_vs_emp_div']?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
    }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'migrar_lista.php'" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_desmigrar" value="Desmigrar" title='Desmigrar' class="botao">
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>