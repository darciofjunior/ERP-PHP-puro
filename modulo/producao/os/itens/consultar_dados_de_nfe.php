<?
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_numero_os              = $_POST['txt_numero_os'];
    $txt_numero_nf              = $_POST['txt_numero_nf'];
    $txt_fornecedor             = $_POST['txt_fornecedor'];
    $chkt_os_em_aberto          = $_POST['chkt_os_em_aberto'];
    $chkt_ops_nao_finalizadas   = $_POST['chkt_ops_nao_finalizadas'];
}else {
    $txt_numero_os              = $_GET['txt_numero_os'];
    $txt_numero_nf              = $_GET['txt_numero_nf'];
    $txt_fornecedor             = $_GET['txt_fornecedor'];
    $chkt_os_em_aberto          = $_GET['chkt_os_em_aberto'];
    $chkt_ops_nao_finalizadas   = $_GET['chkt_ops_nao_finalizadas'];
}

if(!empty($chkt_os_em_aberto))          $condicao_oss_em_aberto         = " AND oss.`status_nf` < '2' ";
if(!empty($chkt_ops_nao_finalizadas))   $condicao_ops_nao_finalizadas   = "INNER JOIN `ops` ON ops.`id_op` = oi.`id_op` AND ops.`status_finalizar` = '0' ";

//Trago todas as NFs de Entrada de acordo com o N.º e Fornecedor digitados pelo Usuário, tendo uma uma Empresa vinculada ...
$sql = "SELECT f.`razaosocial`, nfe.`id_nfe`, nfe.`num_nota`, DATE_FORMAT(SUBSTRING(nfe.`data_emissao`, 1, 10), '%d/%m/%Y') AS data_emissao 
        FROM `nfe` 
        INNER JOIN `oss_itens` oi ON oi.`id_nfe` = nfe.`id_nfe` 
        INNER JOIN `oss` ON oss.`id_os` = oi.`id_os` $condicao_oss_em_aberto $condicao_ops_nao_finalizadas 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` AND f.`razaosocial` LIKE '$txt_fornecedor%' 
        WHERE nfe.`num_nota` LIKE '$txt_numero_nf%' 
        AND nfe.`id_empresa` > '0' GROUP BY oi.`id_nfe` ORDER BY nfe.`num_nota` DESC, nfe.`data_emissao` DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
}else {
    /***************************************************************************************************/
    //Se encontrou apenas um único Registro, já redireciono o usuário diretamente p/ a Tela de Itens ...
    if($linhas == 1) {
?>
    <Script Language = 'Javascript'>
        window.location = 'itens.php?id_nfe=<?=$campos[0]['id_nfe'];?>&acesso_sem_link=S'
    </Script>
<?        
        exit;
    }
    /***************************************************************************************************/
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
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar / Imprimir OS(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º NFe
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Emissão
        </td>
    </tr>
<?
    for($i = 0;  $i < $linhas; $i++) {
        $url = "javascript:window.location = 'itens.php?id_nfe=".$campos[$i]['id_nfe']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href='#' class='link'>
                <?=$campos[$i]['num_nota'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
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