<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');


$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$txt_data_inicial = $_POST['txt_data_inicial'];
	$txt_data_final = $_POST['txt_data_final'];
	$cmd_consultar = $_POST['cmd_consultar'];
}else {
	$txt_data_inicial = $_GET['txt_data_inicial'];
	$txt_data_final = $_GET['txt_data_final'];
	$cmd_consultar = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relatório de Vendas por Ano ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
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
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

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
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Vendas por Ano
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'> 
            Data Inicial:
            <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial   = '01/01/'.(date('Y') - 1);
                    $txt_data_final     = date('31/12/').(date('Y') - 1);
                }
                if(!empty($chkt_deduzir_devolucoes))    $checked_devolucoes  = 'checked';
                if(!empty($chkt_deduzir_exportacoes))   $checked_exportacoes = 'checked';
            ?>
            <input type='text' name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = "../../../../imagem/calendario.gif" width='12' height='12' border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = "../../../../imagem/calendario.gif" width='12' height='12' border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            -
            <input type='checkbox' name="chkt_deduzir_devolucoes" id="chkt_deduzir_devolucoes" value="S" class="checkbox" <?=$checked_devolucoes;?>>
            <label for='chkt_deduzir_devolucoes'>
                Deduzir Devoluções
            </label>
            -
            <input type='checkbox' name="chkt_deduzir_exportacoes" id="chkt_deduzir_exportacoes" value="S" class="checkbox" <?=$checked_exportacoes;?>>
            <label for='chkt_deduzir_exportacoes'>
                Deduzir Exportações
            </label>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
    $data_inicial   = data::datatodate($txt_data_inicial, '-');
    $data_final     = data::datatodate($txt_data_final, '-');

    $campo_total_por_ano    = (!empty($chkt_deduzir_devolucoes))  ? 'SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS total_por_ano' : 'SUM(nfsi.`qtde` * nfsi.`valor_unitario`) AS total_por_ano ';
    if(!empty($chkt_deduzir_exportacoes)) $condicao_exportacao = " AND c.`id_pais` = '31' ";//Significa q o usuário só quer verificar as NF´s do Brasil ...
        
//Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas ...      
    $sql = "SELECT IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.`cnpj_cpf`, nfs.id_empresa, $campo_total_por_ano, p.pais 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            INNER JOIN `paises` p ON p.id_pais = c.id_pais $condicao_exportacao 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
            WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfs.`status` <> '5' 
            AND nfs.`id_empresa` IN (1, 2) GROUP BY nfs.id_empresa, c.razaosocial ORDER BY nfs.id_empresa, c.razaosocial ";

    $sql_extra = "SELECT COUNT(nfs.id_nf) AS total_registro 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                INNER JOIN `paises` p ON p.id_pais = c.id_pais $condicao_exportacao 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND nfs.`status` <> '5' 
                AND nfs.`id_empresa` IN (1, 2) GROUP BY nfs.id_empresa, c.razaosocial ORDER BY nfs.id_empresa, c.razaosocial ";
    $campos = bancos::sql($sql, $inicio, 2000, 'sim', $pagina);
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
        <td>
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
        <td colspan='2'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Cliente / CNPJ / CPF</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor R$</b>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal'>
        <td>
        <?
            echo $campos[$i]['cliente'].' - ';
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
            echo ' - '.$campos[$i]['pais'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total_por_ano'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_por_empresa+= $campos[$i]['total_por_ano'];
            $total_geral+= $campos[$i]['total_por_ano'];
        }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhacabecalho' align='right'>
        <td>
            <font color='yellow' size='-1'>
                Total por Empresa =>
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_por_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td>
            <font color='yellow' size='-1'>
                Total Geral => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_geral, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
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
        <td colspan='2'>
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