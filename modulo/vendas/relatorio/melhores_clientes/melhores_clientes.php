<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] 		= "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia 	= genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Faturamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
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
/**Verifico se o intervalo entre Datas é > do que 2 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 730) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio de Faturamento(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <p>Data Inicial: 
            <?
                $datas = genericas::retornar_data_relatorio();
                //Nas demais vezes em que já submetou para o Banco de Dados ...
                if(!empty($cmd_consultar)) {//Só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer
                    $data_inicial 	= $txt_data_inicial;
                    $data_final 	= $txt_data_final;
                }else {//Aqui é somente na Primeira vez em que carregar a Tela ...
                    $data_inicial = $datas['data_inicial'];
                    $data_final = $datas['data_final'];
                }
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
            &nbsp; <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type="text" name="txt_data_final" value="<?=$data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
            &nbsp; <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;&nbsp;&nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
    if(empty($cmd_consultar)) {//Por enquanto não clicou em Consultar do Relatório ...
?>
    <tr class='erro' align='center'>
        <td>
            CLIQUE EM CONSULTAR PARA GERAR O RELATÓRIO.
        </td>
        
        </td>
    </tr>
<?
    }else {//Já clicou no Consultar ...
?>
    <tr class='linhacabecalho'>
        <td align='right' colspan='3'>
        <?
            if(!empty($cmd_consultar)) {//Só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer ...
                $txt_data_faturado_mes 	= data::datatodate($txt_data_inicial, '-');
                $txt_data_faturavel_mes = data::datatodate($txt_data_final, '-');
                //Busco o Total Geral de Faturamento dentro do Período, independente da Qtde de Páginas ...
                $sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS total 
                        FROM `nfs` 
                        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                        WHERE nfs.data_emissao BETWEEN '$txt_data_faturado_mes' AND '$txt_data_faturavel_mes' ";
                $campos_fat_total_nac   = bancos::sql($sql);
                $total_geral            = $campos_fat_total_nac[0]['total'];
            }
        ?>
            TOTAL GERAL <font color='yellow'>(TODAS AS PÁGINAS)</font> => R$ <?=segurancas::number_format($total_geral, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    $sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS total, nfs.id_cliente, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.data_emissao BETWEEN '$txt_data_faturado_mes' AND '$txt_data_faturavel_mes' 
            GROUP BY c.id_cliente ORDER BY SUM(nfsi.qtde * nfsi.valor_unitario) DESC ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Total Faturado<font color="red">**</font>&nbsp;R$
        </td>
        <td>
            Porcentagem(ns)
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='center'>
        <?
            //Aqui eu busco o 1º Representante encontrado do Cliente, independente da Divisão ...
            $sql = "SELECT DISTINCT(nome_fantasia) as representante 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
                    WHERE cr.id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['representante'];
        ?>
        </td>
        <td>
            <a href = 'detalhes_divisao.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&data_inicial=<?=$txt_data_faturado_mes;?>&data_final=<?=$txt_data_faturavel_mes;?>' class='html5lightbox'>
                <font color='blue' size='2'>
                <?
                    $sub_total+= $campos[$i]['total'];
                    echo number_format($campos[$i]['total'], 2, ',', '.');
                ?>
                </font>
            </a>
        </td>
        <td>
            <?=number_format($campos[$i]['total'] / $total_geral * 100, 2, ',', '.');?> %
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal' align="right">
        <td colspan='3'>
            <font color='red' size='2'>
                Sub Total: <?=segurancas::number_format($sub_total, 2, '.');?>
            </font>
        </td>
        <td>
            <?=segurancas::number_format($sub_total / $total_geral * 100, 2, '.');?> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='4'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
            <input type='button' name='cmd_gerar_ranking' value='Gerar Ranking' title='Gerar Ranking' onclick="alert('ESTE RANKING DEMORA CERCA DE 1 À 2 MINUTOS P/ SER GERADO !!!');html5Lightbox.showLightbox(7, 'gerar_ranking.php')" style='color:red' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<pre> <font color="red">** Neste Relatório não consta os seguintes cálculos:</font>
<font color="blue">
 - Notas Fiscais sem Data de Emissão
 - Frete / IPI
 - Despesas Acessórias
 - Desconto de PIS + Cofins e ICMS = 7%
</font>
</pre>
</body>
</html>