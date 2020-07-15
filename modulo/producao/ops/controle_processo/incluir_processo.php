<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/controle_processo/controle_processo.php', '../../../../');

/*****************************************Vincular Processo*****************************************/
//Isso significa que o usuário deseja vincular um processo a outro processo ...
if(!empty($_GET['id_op_processo'])) {//Busco dados do Processo da OP que foi passada por parâmetro ...
    $sql = "SELECT id_op, id_maquina 
            FROM `ops_vs_processos` 
            WHERE `id_op_processo` = '$_GET[id_op_processo]' LIMIT 1 ";
    $campos_op_processo = bancos::sql($sql);
    $id_op          = $campos_op_processo[0]['id_op'];
    $id_maquina     = $campos_op_processo[0]['id_maquina'];//Aqui é uma mutreta p/ que venha sugerida a máquina escolhida pelo usuário ...
    $class          = 'textdisabled';
    $disabled       = 'disabled';
}else {
    $class          = 'combo';
    $disabled       = '';
}
/***************************************************************************************************/

//Existe(m) caso(s) em que o usuário não passa o $id_op, dependendo do botão onde clica ...
if(!isset($id_op)) {
    $id_op 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_op'] : $_GET['id_op'];
}

if(!empty($_POST['cmb_maquina'])) {
    $data_sys       = date('Y-m-d H:i:s');
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
	
    $sql = "INSERT INTO `ops_vs_processos` (`id_op_processo`, `id_funcionario_registrou`, `id_op`, `id_funcionario`, `id_maquina`, `id_maquina_codigo_maquina`, `id_maquina_operacao`, `hora_inicial`, `data_inicial`, `data_sys`) 
            VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[id_op]', '$_POST[cmb_func_maquina]', '$_POST[cmb_maquina]', '$_POST[cmb_cod_maquina]', '$_POST[cmb_operacoes]', '$_POST[txt_hora_inicial]', '$data_inicial', '$data_sys') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('PROCESSO INCLUÍDO COM SUCESSO !')
        parent.location = 'controle_processo.php?passo=2&id_op=<?=$id_op;?>'
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Incluir Processo de OP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Máquina ...
    if(!combo('form', 'cmb_maquina', '', 'SELECIONE A MÁQUINA !')) {
        return false
    }
//Código da Máquina	...
    if(!combo('form', 'cmb_cod_maquina', '', 'SELECIONE UM CÓDIGO DA MÁQUINA !')) {
        return false
    }
//Funcionário da Máquina ...
    if(!combo('form', 'cmb_func_maquina', '', 'SELECIONE UM FUNCIONÁRIO DA MÁQUINA !')) {
        return false
    }
//Processo da Máquina ...
    if(!combo('form', 'cmb_operacoes', '', 'SELECIONE UM PROCESSO DA MÁQUINA !')) {
        return false
    }
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Hora Inicial ...
    if(!texto('form', 'txt_hora_inicial', '3', '0123456789:', 'HORA INICIAL', '1')) {
        return false
    }
    //Desabilito a Máquina p/ poder gravar no BD ...
    document.form.cmb_maquina.disabled = false
}
</Script>
</head>
<body onload='document.form.cmb_maquina.focus()'>
<form name='form' action='' method='post' onsubmit='return validar()'>
<input type='hidden' name='id_op' value='<?=$id_op;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Processo da OP N.º
            <font color='yellow'>
                <?=$id_op;?>
            </font> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Máquina: </b>
        </td>
        <td>
            <select name='cmb_maquina' onchange="window.location = 'incluir_processo.php?id_op=<?=$id_op;?>&id_maquina='+this.value" title='Selecione uma Máquina' class='<?=$class;?>' <?=$disabled;?>>
            <?
                //Aqui eu busco o custo desse PA ...
                $sql = "SELECT ops.id_produto_acabado, pac.id_produto_acabado_custo 
                        FROM `ops` 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ops.id_produto_acabado 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado = pa.id_produto_acabado AND pac.operacao_custo = pa.operacao_custo 
                        WHERE ops.`id_op` = '$id_op' ";
                $campos_custo               = bancos::sql($sql);
                //Aqui eu só trago as máquinas que estão atreladas ao Custo do PA dessa OP que foi passada por parâmetro ...
                $sql = "SELECT m.id_maquina, nome 
                        FROM `maquinas` m 
                        INNER JOIN `pacs_vs_maquinas` pm ON pm.id_maquina = m.id_maquina AND pm.id_produto_acabado_custo = '".$campos_custo[0]['id_produto_acabado_custo']."' 
                        WHERE ativo = '1' ORDER BY nome ";
                echo combos::combo($sql, $id_maquina);
            ?>
            </select>
            &nbsp;
            <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Custo' alt='Alterar Custo' onclick="nova_janela('../../custo/prod_acabado_componente/custo_industrial.php?id_produto_acabado=<?=$campos_custo[0]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Código(s) da Máquina: </b>
        </td>
        <td>	
            <select name='cmb_cod_maquina' title='Selecione um Código da Máquina' class='combo'>
            <?
                $sql = "SELECT mcm.`id_maquina_codigo_maquina`, CONCAT(mcm.`codigo_maquina`, ' - ', m.`caracteristica`) AS dados 
                        FROM `maquinas_vs_codigos_maquinas` mcm 
                        INNER JOIN `maquinas` m ON m.`id_maquina` = mcm.`id_maquina` 
                        WHERE mcm.`id_maquina` = '$id_maquina' ORDER BY mcm.`codigo_maquina` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário(s) da Máquina: </b>
        </td>
        <td>
            <select name='cmb_func_maquina' title='Selecione um Funcionário da Máquina' class='combo'>
            <?
                $sql = "SELECT f.`id_funcionario`, f.`nome` 
                        FROM `maquinas_vs_funcionarios` mf 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = mf.`id_funcionario` AND mf.`id_maquina` = '$id_maquina' 
                        ORDER BY f.`nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Operação(ões) da Máquina: </b>
        </td>
        <td>	
            <select name="cmb_operacoes" title="Selecione uma Operação da Máquina" class='combo'>
            <?
                $sql = "SELECT id_maquina_operacao, operacao 
                        FROM `maquinas_vs_operacoes` 
                        WHERE id_maquina = '$id_maquina' ORDER BY operacao ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Inicial:</b>
        </td>
        <td>
            <input type="text" name="txt_data_inicial" value="<?=date('d/m/Y');?>" title="Digite a Data Inicial" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_processo&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Hora Inicial:</b> 
        </td>
        <td>	
            <input type="text" name="txt_hora_inicial" value="<?=date('H:i');?>" title='Digite a Hora Inicial' size="7" maxlength="5" onkeyup="verifica(this, 'hora', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.cmb_maquina.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>