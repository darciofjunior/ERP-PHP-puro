<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

//Aqui eu puxo o único Filtro de OP(s) que serve para toda parte de OP(s) ...
require('tela_geral_filtro.php');
if($linhas > 0) {//Se retornar pelo menos 1 registro ...
?>    
<html>
<head>
<title>.:: Consultar OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function imprimir() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        alert('/******************************REFERÊNCIA ESP******************************/\n\nANTES DE IMPRIMIR NÃO SE ESQUEÇA DE MUDAR O PAPEL PARA \n\n\nA   M   A   R   E   L   O !')
        nova_janela('relatorio/relatorio.php?arquivo_que_chamou_impressao=consultar', 'IMPRIMIR', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='relatorio/relatorio.php' onsubmit='return imprimir()' target='IMPRIMIR'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Consultar OP(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Imprimir
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td colspan='2'>
            N.º OP
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde Nominal / Restante
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Prazo de Entrega
        </td>
        <td>
            Pço. Unit
        </td>
        <td>
            Vlr. Total
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_op='.$campos[$i]['id_op'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_op[]' id='chkt_op<?=$i;?>' value="<?=$campos[$i]['id_op'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_op'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
                if($campos[$i]['desenho_para_op'] == '') {//Não existe desenho no Produto Acabado ...
            ?>
                    &nbsp;<img src='../../../imagem/folha_em_branco.png' width='12' height='12' border='0' title='Não Existe Desenho no Produto Acabado'>
            <?
                }else {//Já consta desenho anexado
            ?>
                    &nbsp;<img src='../../../imagem/folha_preenchida.png' width='12' height='12' border='0' title='Existe Desenho no Produto Acabado'>
            <?
                }
            ?>
            &nbsp;<img src='../../../imagem/impressora.gif' title='Imprimir OP' alt='Imprimir OP' border='0' onclick="document.getElementById('chkt_op<?=$i;?>').click();document.form.cmd_imprimir.click()" style='cursor:pointer'>
        </td>
        <td>
        <?
            echo number_format($campos[$i]['qtde_produzir'], 2, ',', '.').' / ';
            //Busca tudo o que foi produzido da OP ...
            $sql = "SELECT SUM(bop.`qtde_baixa`) AS qtde_produzido 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` AND bop.`id_produto_acabado` = ops.`id_produto_acabado` 
                    WHERE ops.`status_finalizar` = '0' 
                    AND ops.`id_op` = '".$campos[$i]['id_op']."' ";
            $campos_produzido 	= bancos::sql($sql);
            $qtde_restante 		= $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];
            echo number_format($qtde_restante, 2, ',', '.');
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT ged.`desc_medio_pa`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_preco_unit 	= bancos::sql($sql);
            $preco_lista 	= ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_list_desc'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_list_desc'];
            echo segurancas::number_format($preco_lista, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($qtde_restante > 0 && $preco_lista > 0) echo number_format($qtde_restante * $preco_lista, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
        }
        //Aqui eu faço o mesmo SQL só que dessa vez sem paginar ...
        $campos = bancos::sql($sql_todos_itens);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Busca tudo o que foi produzido da OP ...
            $sql = "SELECT SUM(bop.`qtde_baixa`) AS qtde_produzido 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` AND bop.`id_produto_acabado` = ops.`id_produto_acabado` 
                    WHERE ops.`status_finalizar` = '0' 
                    AND ops.id_op = '".$campos[$i]['id_op']."' ";
            $campos_produzido 	= bancos::sql($sql);
            $qtde_restante 		= $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];

            $sql = "SELECT ged.`desc_medio_pa`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_preco_unit 	= bancos::sql($sql);
            $preco_lista 	= ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_list_desc'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_list_desc'];

            if($qtde_restante > 0 && $preco_lista > 0) $valor_total_rs+= $qtde_restante * $preco_lista;
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
            <input type='submit' name='cmd_imprimir' value='Imprimir OP' title='Imprimir OP' style='color:purple' class='botao'>
        </td>
        <td colspan='3' align="right">
            Valor Total R$ <?=number_format($valor_total_rs, 2, ',', '.');?>
        </td>
    </tr>
</table>
<!--************Controle de Tela************-->
<input type='hidden' name='hdd_atualizar_alterar' value='S'>
<input type='hidden' name='hdd_arquivo_que_chamou_impressao' value='<?=basename($_SERVER['PHP_SELF']);?>'>
<!--****************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>