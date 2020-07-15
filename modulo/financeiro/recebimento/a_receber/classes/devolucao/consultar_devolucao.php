<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>BORDERO NÃO POSSUI NENHUMA CONTA ATRELADA.</font>";

if($passo == 1) {
//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
    if(empty($cmb_representante))   $cmb_representante = '%';
    if($cmb_tipo_lancamento == '')  $cmb_tipo_lancamento = '%';
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
    if($hidden_tipo_lancamento == 1) {//Tipo de Lançamento = 'DEVOLUÇÃO DE CANCELAMENTO'
        $cmb_tipo_lancamento = 0;
    }else if($hidden_tipo_lancamento == 2) {//Tipo de Lançamento = 'ATRASO DE PAGAMENTO'
        $cmb_tipo_lancamento = 1;
    }else if($hidden_tipo_lancamento == 3) {//Tipo de Lançamento = 'ABATIMENTO / DIF. PREÇOS'
        $cmb_tipo_lancamento = 2;
    }else if($hidden_tipo_lancamento == 4) {//Tipo de Lançamento = 'REEMBOLSO'
        $cmb_tipo_lancamento = 3;
    }else if($hidden_tipo_lancamento == 5) {//Tipo de Lançamento = 'NF DE ENTRADA'
        $cmb_tipo_lancamento = 4;
    }

    if(!empty($txt_data_lancamento)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_lancamento, 4, 1) != '-') $txt_data_lancamento = data::datatodate($txt_data_lancamento, '-');
    }
//Só Lista os Estornos de Comissão da Empresa Corrente -> $id_emp2 passado por parâmetro ...
    $sql = "SELECT ce.*, DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada 
            FROM `comissoes_estornos` ce
            INNER JOIN `contas_receberes` cr ON cr.id_conta_receber = ce.id_conta_receber AND cr.`num_conta` LIKE '$txt_numero_conta%'
            INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND c.`razaosocial` LIKE '%$txt_cliente%'
            WHERE ce.`id_representante` LIKE '$cmb_representante'
            AND SUBSTRING(ce.`data_lancamento`, 1, 10) LIKE '%$txt_data_lancamento%'
            AND ce.tipo_lancamento LIKE '$cmb_tipo_lancamento'
            ORDER BY ce.id_comissao_estorno DESC ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar_devolucao.php?id_emp2=<?=$id_emp2;?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Devolução(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='10'>
            Consultar Devolução(ões)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Data de <br>Lançamento
        </td>
        <td>
            Tipo de Lançamento
        </td>
        <td>
            SNF
        </td>
        <td>
            NNF / Duplicata
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Data de Vencimento
        </td>
        <td>
            Valor Dupl R$
        </td>
        <td>
            Com. Média %
        </td>
        <td>
            Com. R$
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=data::datetodata(substr($campos[$i]['data_lancamento'], 0, 10), '/');?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_lancamento'] == 0) {
                echo 'DEVOLUÇÃO DE CANCELAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 1) {
                echo 'ATRASO DE PAGAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 2) {
                echo 'ABATIMENTO / DIF. PREÇOS';
            }else if($campos[$i]['tipo_lancamento'] == 3) {
                echo 'REEMBOLSO';
            }else if($campos[$i]['tipo_lancamento'] == 4) {
                echo 'NF DE ENTRADA';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['num_nf_devolvida'];?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes" class="link">
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf']);?>
            </a>
            <?
                if($campos[$i]['id_conta_receber'] > 0) {
                    //Busca o Nº da Duplicata ...
                    $sql = "SELECT num_conta
                            FROM `contas_receberes`
                            WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
                    $campos_contas_receber = bancos::sql($sql);
                    echo ' / '.$campos_contas_receber[0]['num_conta'];
                }
            ?>
        </td>
        <td align="left">
        <?
//Busca do Nome do Cliente da Nota que está sendo Devolvida ...
            $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente
                    FROM `nfs`
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente
                    WHERE nfs.`id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
        <td>
        <?
//Busca do Nome do Representante
            $sql = "SELECT nome_fantasia
                    FROM `representantes`
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['data_vencimento_alterada'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_duplicata'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['porc_devolucao'], 1, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $comissao = ($campos[$i]['valor_duplicata'] * $campos[$i]['porc_devolucao']) / 100;
            if($campos[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
                echo '<font color="blue">'.number_format($comissao, 2, ',', '.').'</font>';
            }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                echo '<font color="red">'.number_format($comissao * (-1), 2, ',', '.').'</font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='10'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar_devolucao.php?id_emp2=<?=$id_emp2;?>'" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Devolução(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Controle com o Tipo de Lançamento
function controle_tipo_lancamento() {
    var tipo_lancamento = document.form.cmb_tipo_lancamento[document.form.cmb_tipo_lancamento.selectedIndex].text
//Se não estiver selecionada nenhum Tipo de Lançamento
    if(tipo_lancamento == 'SELECIONE') {
        document.form.hidden_tipo_lancamento.value = ''
    }else if(tipo_lancamento == 'DEVOLUÇÃO DE CANCELAMENTO') {
        document.form.hidden_tipo_lancamento.value = 1
    }else if(tipo_lancamento == 'ATRASO DE PAGAMENTO') {
        document.form.hidden_tipo_lancamento.value = 2
    }else if(tipo_lancamento == 'ABATIMENTO / DIF. PREÇOS') {
        document.form.hidden_tipo_lancamento.value = 3
    }else if(tipo_lancamento == 'REEMBOLSO') {
        document.form.hidden_tipo_lancamento.value = 4
    }else if(tipo_lancamento == 'NF DE ENTRADA') {
        document.form.hidden_tipo_lancamento.value = 5
    }
}
</Script>
</head>
<body onload="document.form.txt_numero_conta.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<input type="hidden" name="id_emp2" value="<?=$id_emp2;?>">
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro lá no outro
passo da consulta*/
-->
<input type="hidden" name="hidden_tipo_lancamento">
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Devolução(ões)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da Conta
        </td>
        <td>
            <input type="text" name="txt_numero_conta" title="Digite o N.º da Conta" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type="text" name="txt_cliente" title="Digite o Cliente" size="40" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Lançamento
        </td>
        <td>
            <input type="text" name="txt_data_lancamento" title="Digite a Data de Lançamento" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_lancamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Representante
        </td>
        <td>
            <select name="cmb_representante" title="Selecione o Representante" class="combo">
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados
                        FROM `representantes`
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Tipo de Lançamento
        </td>
        <td>
            <select name="cmb_tipo_lancamento" title="Selecione o Tipo de Lançamento" onchange="controle_tipo_lancamento()" class="combo">
                <option value='' style="color:red">SELECIONE</option>
                <option value='0'>DEVOLUÇÃO DE CANCELAMENTO</option>
                <option value='1'>ATRASO DE PAGAMENTO</option>
                <option value='2'>ABATIMENTO / DIF. PREÇOS</option>
                <option value='3'>REEMBOLSO</option>
                <option value='4'>NF DE ENTRADA</option>
            </select>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_numero_conta.focus()" style="color:#ff9900" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>