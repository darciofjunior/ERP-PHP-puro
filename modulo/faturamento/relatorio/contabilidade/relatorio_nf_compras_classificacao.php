<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
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
<title>.:: Relatório de Compras por Ano / Classificação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
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
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relatório de Compras por Ano / Classificação
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'> 
            Data Inicial:
            <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial = '01/01/'.(date('Y') - 1);
                    $txt_data_final = date('31/12/').(date('Y') - 1);
                }
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
	$data_inicial = data::datatodate($txt_data_inicial, '-');
	$data_final = data::datatodate($txt_data_final, '-');
//Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas ...
	$sql = "SELECT pi.id_classific_fiscal, nfe.id_empresa, SUM(nfeh.qtde_entregue * nfeh.valor_entregue) AS total_por_ano_classific_fiscal 
                FROM `nfe` 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.id_nfe = nfe.id_nfe 
                INNER JOIN `itens_pedidos` ip ON ip.id_item_pedido = nfeh.id_item_pedido 
                INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
                WHERE SUBSTRING(nfe.`data_emissao`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
                AND nfe.`id_empresa` IN (1, 2) GROUP BY nfe.id_empresa, pi.id_classific_fiscal ORDER BY nfe.id_empresa ";
	$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
            $id_empresa_anterior = '';
            for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada no loop, se for 
então eu atribuo a Empresa Atual p/ a Empresa Anterior ...*/
                if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                    $id_empresa_anterior = $campos[$i]['id_empresa'];
//Só não mostro essa linha quando acaba de Entrar no Loop ...
                    if($i > 0) {
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_por_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                        $total_por_empresa = 0;//Zero p/ não ficar herdando valores do Loop Anterior ...
                    }
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
            <b>Classific Fiscal</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Família</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor R$</b>
        </td>
    </tr>
<?
                }
                //Busca a Classificação Fiscal através do PI ...
                $sql = "SELECT classific_fiscal 
                        FROM `classific_fiscais` 
                        WHERE `id_classific_fiscal` = '".$campos[$i]['id_classific_fiscal']."' LIMIT 1 ";
                $campos_classific_fiscais = bancos::sql($sql);
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if(count($campos_classific_fiscais) == 1) {//Achou Classificação Fiscal ...
                echo $campos_classific_fiscais[0]['classific_fiscal'];
            }else {//Não Achou Classificação Fiscal do PI ...
                echo 'S/ CLASSIF. FISCAL';
            }
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT nome 
                    FROM `familias` 
                    WHERE `id_classific_fiscal` = ".$campos[$i]['id_classific_fiscal']." LIMIT 1 ";
            $campos_familia = bancos::sql($sql);
            echo $campos_familia[0]['nome'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total_por_ano_classific_fiscal'], 2, ',', '.');?>
        </td>
    </tr>
<?
                $total_por_empresa+= $campos[$i]['total_por_ano_classific_fiscal'];
                $total_geral+= $campos[$i]['total_por_ano_classific_fiscal'];
            }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhacabecalho' align="right">
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total por Empresa =>
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_por_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align="right">
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total Geral => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_geral, 2, ',', '.');?>
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
            <?=$mensagem[1];?>
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