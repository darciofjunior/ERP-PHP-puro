<?
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_numero_os              = $_POST['txt_numero_os'];
    $txt_fornecedor             = $_POST['txt_fornecedor'];
    $txt_numero_op              = $_POST['txt_numero_op'];
    $txt_referencia             = $_POST['txt_referencia'];
    $txt_discriminacao          = $_POST['txt_discriminacao'];
    $chkt_os_em_aberto          = $_POST['chkt_os_em_aberto'];
    $chkt_ops_nao_finalizadas   = $_POST['chkt_ops_nao_finalizadas'];
}else {
    $txt_numero_os              = $_GET['txt_numero_os'];
    $txt_fornecedor             = $_GET['txt_fornecedor'];
    $txt_numero_op              = $_GET['txt_numero_op'];
    $txt_referencia             = $_GET['txt_referencia'];
    $txt_discriminacao          = $_GET['txt_discriminacao'];
    $chkt_os_em_aberto          = $_GET['chkt_os_em_aberto'];
    $chkt_ops_nao_finalizadas   = $_GET['chkt_ops_nao_finalizadas'];
}

if(empty($txt_numero_os))               $txt_numero_os = '%';
if(empty($txt_numero_op))               $txt_numero_op = '%';
if(!empty($chkt_os_em_aberto))          $condicao_oss_em_aberto         = " AND oss.`status_nf` < '2' ";
if(!empty($chkt_ops_nao_finalizadas))   $condicao_ops_nao_finalizadas   = " AND ops.`status_finalizar` = '0' ";

$sql = "SELECT oss.*, f.`razaosocial`, pa.`referencia`, pa.`discriminacao`, oi.`qtde_saida`, oi.`qtde_entrada`, oi.`id_op`, oi.`status` 
        FROM `oss` 
        LEFT JOIN `oss_itens` oi ON oi.`id_os` = oss.`id_os` AND oi.`id_op` LIKE '$txt_numero_op' 
        $inner_join_nfe 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` AND f.`razaosocial` LIKE '$txt_fornecedor%' 
        INNER JOIN `ops` ON ops.`id_op` = oi.`id_op` $condicao_ops_nao_finalizadas 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` AND pa.`referencia` LIKE '$txt_referencia%' AND pa.`discriminacao` LIKE '$txt_discriminacao%' 
        WHERE oss.`id_os` LIKE '$txt_numero_os' 
        AND oss.`ativo` = '1' 
        $condicao_oss_em_aberto ORDER BY oss.`id_os` DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar / Imprimir OS(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OS
        </td>
        <td>
            Data de Saída
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Qtde Saída
        </td>
        <td>
            Qtde Entrada
        </td>
        <td>
            N.° OP
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:window.location = 'index.php?id_os=".$campos[$i]['id_os']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width="10">
            <a href='#'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href='#' class='link'>
                <?=$campos[$i]['id_os'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_saida'], '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_saida'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_entrada'];?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['id_op'];
            if($campos[$i]['status'] == 2) {
                echo ' <b><font color="red">(Finalizada)</font></b>';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>                           
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
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