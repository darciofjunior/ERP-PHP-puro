<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>DADOS DE EXPORTAÇÃO ALTERADO COM SUCESSO.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_pedido_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_pedido_venda'] : $_GET['id_pedido_venda'];

if(!empty($_POST['id_pedido_venda'])) {
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_conta_corrente = (!empty($_POST[cmb_conta_corrente])) ? "'".$_POST[cmb_conta_corrente]."'" : 'NULL';
    $faturar_em         = data::datatodate($_POST[txt_faturar_em], '-');
    $fecha              = data::datatodate($_POST[txt_fecha], '-');
    $fecha_de_entrega   = data::datatodate($_POST[txt_fecha_de_entrega], '-');
    
    $sql = "UPDATE `pedidos_vendas` SET `id_contacorrente` = $cmb_conta_corrente, `faturar_em` = '$faturar_em', `fecha` = '$fecha', `consignatario` = '$_POST[txt_consignatario]', `embarque` = '$_POST[cmb_embarque]', `marcas` = '$_POST[txt_marcas]', `informe_importacion` = '$_POST[txt_informe_importacion]', `incoterm` = '$_POST[txt_incoterm]', `fecha_de_entrega` = '$fecha_de_entrega' WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Aqui eu trago alguns de Exportação do $id_pedido_venda passado por parâmetro ...
$sql = "SELECT `id_empresa`, `id_contacorrente`, DATE_FORMAT(`faturar_em`, '%d/%m/%Y') AS faturar_em, 
        DATE_FORMAT(`fecha`, '%d/%m/%Y') AS fecha, `consignatario`, `embarque`, `marcas`, 
        `informe_importacion`, `incoterm`, `fecha_de_entrega` 
        FROM `pedidos_vendas` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Dados de Exportação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Faturar em ...
    if(document.form.txt_faturar_em.value != '') {
        if(!data('form', 'txt_faturar_em', '4000', 'FATURAR')) {
            return false
        }
    }
//Fecha ...
    if(document.form.txt_fecha.value != '') {
        if(!data('form', 'txt_fecha', '4000', 'FECHA')) {
            return false
        }
    }
//Fecha de Entrega ...
    if(document.form.txt_fecha_de_entrega.value != '') {
        if(!data('form', 'txt_fecha_de_entrega', '4000', 'ENTREGA')) {
            return false
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP ...
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.document.location = opener.document.location.href
    }
}
</Script>
</head>
<body onload='document.form.txt_consignatario.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='nao_atualizar'>
<!--*************************************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dados de Exportação
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Datas
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Faturar em:
        </td>
        <td>
            <input type='text' name='txt_faturar_em' value='<?=$campos[0]['faturar_em'];?>' title='Digite o Faturar em' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_faturar_em&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fecha:
        </td>
        <td>
            <input type='text' name='txt_fecha' value='<?=$campos[0]['fecha'];?>' title='Digite a Fecha' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_fecha&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Outras Informações
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Consignatário:
        </td>
        <td>
            <input type='text' name='txt_consignatario' value='<?=$campos[0]['consignatario'];?>' title='Digite o Consignatário' size='60' maxlength='100' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Embarque:
        </td>
        <td>
            <select name='cmb_embarque' title='Selecione o Embarque' class='combo'>
                <?
                    if($campos[0]['embarque'] == 'A') {//Aéreo ...
                        $selecteda = 'selected';
                    }else if($campos[0]['embarque'] == 'M') {//Marítimo ...
                        $selectedm = 'selected';
                    }else if($campos[0]['embarque'] == 'R') {//Rodoviário ...
                        $selectedr = 'selected';
                    }else {
                        $selectedr = 'selected';//Na primeira vez o sistema sugere Rodoviário ...
                    }
                ?>
                <option value='A' <?=$selecteda;?>>Aéreo</option>
                <option value='M' <?=$selectedm;?>>Marítimo</option>
                <option value='R' <?=$selectedr;?>>Rodoviário</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Corrente:
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='combo'>
            <?
                //Aqui eu só listo as Contas Correntes que possuem a Marcação de Exportação da Empresa do Pedido ...
                $sql = "SELECT cc.id_contacorrente, CONCAT('BANCO: ', b.banco, ' | AGÊNCIA: ', a.cod_agencia, ' | CONTA CORRENTE: ', cc.conta_corrente) AS dados 
                        FROM `contas_correntes` cc 
                        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                        INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                        WHERE cc.`id_empresa` = '".$campos[0]['id_empresa']."' 
                        AND cc.`conta_exportacao` = 'S' 
                        ORDER BY cc.conta_corrente ";
                echo combos::combo($sql, $campos[0]['id_contacorrente']);
            ?>
            </select>
        </td>
    </tr>
    <?
        //Verifico se temos algum item desse Pedido vinculado à algum Packing List ...
        $sql = "SELECT pl.`id_packing_list`, pl.`id_packing_list`, pl.`qtde_caixas`, pl.`peso_bruto`, 
                pl.`peso_liquido`, pl.`volume` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `packings_lists_itens` pli ON pli.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
                INNER JOIN `packings_lists` pl ON pl.`id_packing_list` = pli.`id_packing_list` 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        $campos_packing_list = bancos::sql($sql);
        $linhas_packing_list = count($campos_packing_list);
        if($linhas_packing_list > 0) {
    ?>
    <tr class='linhanormal'>
        <td>
            Packing List:
        </td>
        <td>

            <select name='cmb_packing_list' title='Packing List' class='textdisabled' disabled>
                <?=combos::combo($sql, $campos_packing_list[0]['id_packing_list']);?>
            </select>
            &nbsp;
            <img src = '../../../imagem/lista.jpg' width='20' height='20' border='0' title='Packing List' style='cursor:help' onclick="nova_janela('../../producao/programacao/estoque/gerenciar/packing_list/relatorio/relatorio.php?id_packing_list=<?=$campos_packing_list[0]['id_packing_list'];?>', 'PACKING_LIST', 'F')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde Caixas "Bultos":
        </td>
        <td>
            <font color='darkblue'>
                <b><?=$campos_packing_list[0]['qtde_caixas']?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Bruto:
        </td>
        <td>
            <font color='darkblue'>
                <b><?=number_format($campos_packing_list[0]['peso_bruto'], 3, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Neto:
        </td>
        <td>
            <font color='darkblue'>
                <b><?=number_format($campos_packing_list[0]['peso_liquido'], 3, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Volume:
        </td>
        <td>
            <font color='darkblue'>
                <b><?=number_format($campos_packing_list[0]['volume'], 4, ',', '.');?></b>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td>
            Marcas:
        </td>
        <td>
            <textarea name='txt_marcas' cols='70' rows='2' maxlength='100' title='Digite as Marcas' class='caixadetexto'><?=$campos[0]['marcas'];?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Informe Importación:
        </td>
        <td>
            <input type='text' name='txt_informe_importacion' value='<?=$campos[0]['informe_importacion'];?>' title='Digite o Informe de Importación' maxlength='20' size='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Incoterm:
        </td>
        <td>
            <input type='text' name='txt_incoterm' value='<?=$campos[0]['incoterm'];?>' title='Digite o Incoterm' maxlength='20' size='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fecha de Entrega:
        </td>
        <td>
            <?
                $fecha_de_entrega = ($campos[0]['fecha_de_entrega'] != '0000-00-00') ? data::datetodata($campos[0]['fecha_de_entrega'], '/') : ''
            ?>
            <input type='text' name='txt_fecha_de_entrega' value='<?=$fecha_de_entrega;?>' title='Digite a Fecha de Entrega' onkeyup="verifica(this, 'data', '', '', event)" size='10' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_fecha_de_entrega&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'LIMPAR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>