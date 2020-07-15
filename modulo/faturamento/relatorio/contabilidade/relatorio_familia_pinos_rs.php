<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
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
<title>.:: Relatório de NF(s) p/ Rio Grande do Sul (RS) - CF: 73.18.29.00 (Alíquota de 7%) ::.</title>
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
<form name='form' method='POST' action='' onsubmit="return validar()">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relatório de NF(s) p/ Rio Grande do Sul (RS) - CF: 73.18.29.00 (Alíquota de 7%)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'> 
            Data Inicial:
            <?
                //Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial = '12/09/2008';
                    $txt_data_final = date('d/m/Y');
                }
            ?>
            <input type='text' name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src='../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src='../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
    $data_inicial = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final = data::datatodate($_POST['txt_data_final'], '-');
/*Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas e com Itens em que 
a Operação de Faturamento sejam igual a Industrial ...*/
    $sql = "SELECT c.id_pais, nfs.id_nf, nfs.id_empresa, nfs.id_nf_num_nota, nfs.snf_devolvida, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, nfs.suframa, nfs.status 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.id_uf = '6' 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.id_classific_fiscal = '3' AND nfsi.icms = '7.00' 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
            WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfs.`status` <> '5' 
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
        <td colspan='3'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>N.º da NF</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de Emissão</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor R$</b>
        </td>
    </tr>
<?
            }
            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $nota_sgd = 'N';//var surti efeito lá embaixo
            }else {
                $nota_sgd = 'S'; //var surti efeito lá embaixo
            }
?>
    <tr class='linhanormal' align="center">
        <td>
            <a href="javascript:nova_janela('../../../../modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1', 'DETALHES_NFS', '', '', '', '', 550, 975, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Nota Fiscal' style='cursor:help' class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='right'>
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
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total de NF por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_nf_por_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align="right">
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total de NF Geral => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_nf_todas_empresas, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
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