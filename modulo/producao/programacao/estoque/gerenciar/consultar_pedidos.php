<?
//Executa o sql passado por parâmetro
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);

if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Gerenciar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Gerenciar Estoque - Consultar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Pedido
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Faturar Em
        </td>
        <td>
            Condição de<br/>Faturamento
        </td>
        <td>
            Cliente
        </td>
        <td>
            Empresa
        </td>
        <td>
            Transportadora
        </td>
    </tr>
<?
    for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick = "window.location = 'index.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>'" title='Gerenciar por Pedido de Venda' width='10'>
            <a href = 'index.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = 'index.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>'" title='Gerenciar por Pedido de Venda'>
            <a href="index.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>" class='link'>
                <?=$campos[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
            if($credito == 'C' || $credito == 'D') {
                echo '<font color="red">CRÉDITO '.$credito.'</font>';
            }else {
                $condicao_faturamento = array_sistema::condicao_faturamento();
                echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
            }
        ?>
        </td>
        <td onclick="window.location = 'index.php?id_cliente=<?=$campos[$i]['id_cliente'];?>'" title="Gerenciar por Cliente" align="left">
            <a href="#" class='link'>
                <font title="Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>" style='cursor:help'>
                <?
                    echo $campos[$i]['razaosocial'];
                    if($campos[$i]['liberado'] == 0) echo "<font color='red' title='Não Liberado' style='cursor:help'><b> Ñ LIB</b></font>";
                ?>
                </font>
            </a>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = '".$campos[$i]['id_empresa']."' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            echo $campos_empresa[0]['nomefantasia'];

            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                echo ' (NF)';
            }else {
                echo ' (SGD)';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Se o gerenciamento for feito através desse link do Cliente, então o botão <b>"Voltar"</b> da próxima tela,
não vai voltar exatamente nessa tela de filtro.
</pre>