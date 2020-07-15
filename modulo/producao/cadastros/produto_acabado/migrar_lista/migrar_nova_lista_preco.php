<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/migrar_lista/migrar_lista.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>NOVA LISTA DE PREÇO MIGRADA COM SUCESSO.</font>";

if(!empty($_POST['chkt_gpa_vs_emp_div'])) {
    $data_sys = date('Y-m-d H:i:s');
    //Disparo de foreach, em cima dos Grupo(s) PA(s) - Empresas Divisões Selecionados
    foreach($_POST['chkt_gpa_vs_emp_div'] as $id_gpa_vs_emp_div) {
        //Aqui eu faço o Backup(s) de Todo(s) o(s) Preço(s) e Desconto(s) / Acréscimo(s) Antigo(s) ...
        $sql_backup = "UPDATE `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div ";
        //Atualizo o(s) Preço(s) e Desconto(s)/Acréscimo(s) Velho(s) pelo Preço(s) e Desconto(s)/Acréscimo(s) Novo(s) ...
        $sql = "UPDATE produtos_acabados pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div ";
        if($_POST['opt_migrar'] == 1) {//Migrar apenas Descontos ...
            $sql_backup.= " SET ged.desc_base_a_nac_bkp = ged.desc_base_a_nac, ged.desc_base_b_nac_bkp = ged.desc_base_b_nac, ged.acrescimo_base_nac_bkp = ged.acrescimo_base_nac 
                            WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' ";
            bancos::sql($sql_backup);
            
            $sql.= "SET ged.id_func_migrador_lista = '$_SESSION[id_funcionario]', ged.desc_base_a_nac = ged.desc_a_lista_nova, ged.desc_base_b_nac = ged.desc_b_lista_nova, ged.acrescimo_base_nac = ged.acrescimo_lista_nova, ged.data_migrador_lista = '$data_sys', ged.`tipo_migracao` = '$_POST[opt_migrar]' 
                    WHERE ged.id_gpa_vs_emp_div = '$id_gpa_vs_emp_div' ";
            bancos::sql($sql);
        }else if($_POST['opt_migrar'] == 2) {//Migrar Descontos e Preços Brutos ...
            $sql_backup.= " SET pa.preco_unitario_bkp = pa.preco_unitario, ged.desc_base_a_nac_bkp = ged.desc_base_a_nac, ged.desc_base_b_nac_bkp = ged.desc_base_b_nac, ged.acrescimo_base_nac_bkp = ged.acrescimo_base_nac 
                            WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' ";
            bancos::sql($sql_backup);
            
            $sql.= "SET pa.preco_unitario = pa.preco_unitario_simulativa, 
                    ged.id_func_migrador_lista = '$_SESSION[id_funcionario]', ged.desc_base_a_nac = ged.desc_a_lista_nova, ged.desc_base_b_nac = ged.desc_b_lista_nova, ged.acrescimo_base_nac = ged.acrescimo_lista_nova, ged.data_migrador_lista = '$data_sys', ged.`tipo_migracao` = '$_POST[opt_migrar]' 
                    WHERE ged.id_gpa_vs_emp_div = '$id_gpa_vs_emp_div' ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}

//Seleção de todos os Grupos, só não traz nada referente a Componentes
$sql = "SELECT ged.id_gpa_vs_emp_div, ged.id_func_migrador_lista, ged.data_migrador_lista, ged.tipo_migracao, CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS rotulo 
        FROM `grupos_pas` gpa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_grupo_pa = gpa.id_grupo_pa 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        WHERE gpa.ativo = '1' 
        AND gpa.id_familia <> '23' ORDER BY gpa.nome, ed.razaosocial ";
$campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Migrar Nova Lista de Preço ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox')  if(elementos[i].checked == true) valor = true
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        mensagem = confirm('DESEJA MIGRAR ESTE(S) GRUPO(S) PA(S) SELECIONADO(S) ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post' onsubmit="return validar()">
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" height='21' align="center">
        <td colspan='4'>
            Migrar Nova Lista de Preço
        </td>
    </tr>
    <tr align='center'>
        <td class='linhanormal' colspan='4'>
            <input type='radio' name='opt_migrar' value='1' id='opt_migrar1' checked>
            <label for='opt_migrar1'>
                <font size='2'>
                    <b>Migrar apenas Descontos</b>
                </font>
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_migrar' value='2' id='opt_migrar2'>
            <label for='opt_migrar2'>
                <font size='2'>
                    <b>Migrar Descontos e Preços Brutos</b>
                </font>
            </label>
        </td>
    </tr>
    <tr align='center'>
        <td class='linhadestaque'>
            Grupo P.A. (Empresa Divisão)
        </td>
        <td class='linhadestaque'>
            Migrador * Data - Hora
        </td>
        <td class='linhadestaque'>
            Tipo de Migração
        </td>
        <td class='linhadestaque'>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=($i + 2);?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align="left">
            <?=$campos[$i]['rotulo'];?>
        </td>
        <td align="left">
        <?
            if($campos[$i]['id_func_migrador_lista'] != 0) {//Se existir funcionário responsável
                //Busca do Funcionário Responsável pela Alteração do Grupo P.A. (Empresa Divisão)
                $sql = "SELECT nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos[$i]['id_func_migrador_lista']."' LIMIT 1 ";
                $campos_grupo = bancos::sql($sql);
                echo $campos_grupo[0]['nome'].' * ';
                //Apresentação dos Dados de Data e Hora
                echo data::datetodata(substr($campos[$i]['data_migrador_lista'], 0, 10), '/').' - ';
                echo substr($campos[$i]['data_migrador_lista'], 11, 8);
            }
        ?>
        </td>
        <td>
            <?
                if($campos[$i]['tipo_migracao'] == 1) {
                    echo 'Migrou apenas Descontos';
                }else if($campos[$i]['tipo_migracao'] == 2) {
                    echo 'Migrou Descontos e Preços Brutos';
                }
            ?>
        </td>
        <td>
            <input type="checkbox" name="chkt_gpa_vs_emp_div[]" value="<?=$campos[$i]['id_gpa_vs_emp_div']?>" onclick="checkbox('form', 'chkt', '<?=($i + 2);?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'migrar_lista.php'" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_migrar" value="Migrar" title='Migrar' class="botao">
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>