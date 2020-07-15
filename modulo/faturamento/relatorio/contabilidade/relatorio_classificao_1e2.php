<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relatório de NF(s) CF: 84.66.93.30 / 84.66.93.40 ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relatório de NF(s) CF: 84.66.93.30 / 84.66.93.40
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
            <td colspan='4'> 
                    Data Inicial:
                    <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                            if(empty($txt_data_inicial)) {
                                    $txt_data_inicial = '01/07/2009';
                                    $txt_data_final = date('d/m/Y');
                            }
                    ?>
                    <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
                    <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
                    <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
                    <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
                    Empresa: 
                    <select name="cmb_empresa" title="Selecione a Empresa" class="combo">
                    <?
                            $sql = "Select id_empresa, nomefantasia 
                                    from empresas 
                                    where ativo = 1 order by nomefantasia ";
                            echo combos::combo($sql, $_POST['cmb_empresa']);
                    ?>
                    </select>
                    Tipo de Filtro:
                    <?
                            if($_POST['cmb_tipo_filtro'] == 1) {
                                $selected1 = 'selected';
                            }else if($_POST['cmb_tipo_filtro'] == 2) {
                                $selected2 = 'selected';
                            }else if($_POST['cmb_tipo_filtro'] == 3) {
                                $selected3 = 'selected';
                            }
                    ?>
                    <select name="cmb_tipo_filtro" title="Selecione o Tipo de Filtro" class="combo">
                        <option value="" style="color:red">SELECIONE</option>
                        <option value="1" <?=$selected1;?>>NACIONAL</option>
                        <option value="2" <?=$selected2;?>>ESTRANGEIRO</option>
                        <option value="3" <?=$selected3;?>>TRADING</option>
                    </select>
                    <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
            </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
    $data_inicial = data::datatodate($txt_data_inicial, '-');
    $data_final = data::datatodate($txt_data_final, '-');
/*Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas e com Itens em que 
a Operação de Faturamento sejam igual a Industrial ...*/
    if(!empty($_POST['cmb_empresa'])) {
        $condicao_empresa = "AND nfs.`id_empresa` = '".$_POST[cmb_empresa]."' ";
    }
    if(!empty($_POST['cmb_tipo_filtro'])) {
        if($_POST['cmb_tipo_filtro'] == 1) {
            $condicao_pais = "AND c.id_pais = 31 ";
        }else if($_POST['cmb_tipo_filtro'] == 2) {
            $condicao_pais = "AND c.id_pais <> 31 ";
        }else if($_POST['cmb_tipo_filtro'] == 3){
            $condicao_trading = "AND nfs.trading = 1";
        }
    }
    $sql = "SELECT c.id_pais, nfs.id_nf, nfs.id_empresa, date_format(nfs.data_emissao, '%d/%m/%Y') as data_emissao, nfs.suframa, nfs.status 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.`id_classific_fiscal` IN (1, 2) AND nfsi.`ipi` > '0.00' 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
            WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfs.`status` <> '5' $condicao_empresa $condicao_pais $condicao_trading 
            GROUP BY nfs.id_nf ORDER BY nfs.id_empresa, nfs.data_emissao ";
    $campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
            $id_empresa_anterior = '';
            for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada 
no loop, se for então eu atribuo o Empresa Atual p/ a Empresa Anterior ...*/
                    if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                            $id_empresa_anterior = $campos[$i]['id_empresa'];
                            $total_nf_por_empresa = 0;
?>
    <tr class='linhadestaque'>
            <td colspan='4'>
                    <font color="yellow">
                            <b>Empresa: </b>
                    </font>
                    <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
            </td>
    </tr>
    <tr class="linhanormal" align='center'>
            <td bgcolor='#CECECE'><b>N.º da NF</b></td>
            <td bgcolor='#CECECE'><b>Data de Emissão</b></td>
            <td bgcolor='#CECECE'><b>Valor do IPI R$</b></td>
            <td bgcolor='#CECECE'><b>Valor da NF R$</b></td>
    </tr>
<?
                    }

                    if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                            $nota_sgd = 'N';//var surti efeito lá embaixo
                    }else {
                            $nota_sgd = 'S'; //var surti efeito lá embaixo
                    }
?>
    <tr class='linhanormal' align='center'>
            <td>
                    <a href="javascript:nova_janela('../../../../modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'DETALHES_NFS', '', '', '', '', 550, 975, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes de Nota Fiscal" style="cursor:help" class="link">
                            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
                    </a>
            </td>
            <td>
                    <?=$campos[$i]['data_emissao'];?>
            </td>
            <td align="right">
            <?
                    $sql = "Select sum((ipi / 100) * (qtde * valor_unitario)) as total_ipi 
                            from nfs_itens 
                            where id_nf = '".$campos[$i]['id_nf']."' 
                            and id_classific_fiscal in (1, 2) 
                            and ipi > 0 ";
                    $campos_total_ipi = bancos::sql($sql);
                    echo number_format($campos_total_ipi[0]['total_ipi'], 2, ',', '.');
            ?>
            </td>
            <td align="right">
            <?
                    $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nf'], 'NF');
                    echo number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
            ?>
            </td>
    </tr>
<?
                    $total_nf_por_empresa+= $calculo_total_impostos['valor_total_nota'];
                    $total_nf_todas_empresas+= $calculo_total_impostos['valor_total_nota'];
            }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhacabecalho' align="right">
            <td colspan='3'>
                    <font color='yellow' size='-1'>
                            Total de NF por Empresa => 
                    </font>
            </td>
            <td>
                    <?='R$ '.number_format($total_nf_por_empresa, 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhacabecalho' align="right">
            <td colspan='3'>
                    <font color='yellow' size='-1'>
                            Total de NF Geral => 
                    </font>
            </td>
            <td>
                    <?='R$ '.number_format($total_nf_todas_empresas, 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
            <td colspan='4'>
                    <input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title="Atualizar Relatório" id="cmd_atualizar" class="botao">
            </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
//Se não foi passado nenhum representante por parâmetro ...
    }else {
?>
    <tr class='atencao' align='center'>
            <td colspan='3'>
                    <b><?=$mensagem[1];?></b>
            </td>
    </tr>
	
</table>
<?
    }
}
?>
</form>
</body>
</html>