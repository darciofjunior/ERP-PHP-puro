<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

//Através do id_op eu verifico qual é o id_os_item ...
$sql = "SELECT oi.`id_os_item`, oi.`id_os`, oi.`id_op`, pi.`discriminacao` 
        FROM `oss_itens` oi 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = oi.`id_produto_insumo_ctt` 
        WHERE oi.`id_op` = '$_GET[txt_numero_op]' GROUP BY oi.`id_op` ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
//Se encontrou um Item de OS através do id_op então redireciono p/ o arquivo de dar Entrada ...
if($linhas == 1) {
    //Aqui eu verifico qual é a posição desse Item na OS ...
    $sql = "SELECT `id_op` 
            FROM `oss_itens` 
            WHERE `id_os` = '".$campos[0]['id_os']."' ";
    $campos_os_itens = bancos::sql($sql);
    $linhas_os_itens = count($campos_os_itens);
    for($i = 0; $i < $linhas_os_itens; $i++) {
        if($campos_os_itens[$i]['id_op'] == $_GET['txt_numero_op']) {
            $posicao = $i + 1;//Essa posição será passada por parâmetro abaixo ...
            break;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_entrada.php?id_os=<?=$campos[0]['id_os'];?>&id_os_item=<?=$campos[0]['id_os_item'];?>&posicao=<?=$posicao;?>'
    </Script>
<?

}else if($linhas > 1) {//Se encontrou mais de 1 item ...
?>
<html>
<head>
<title>.:: Controle de Entrada ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Controle de Entrada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OP
        </td>
        <td>
            CTT
        </td>
        <td>
            N.º OS
        </td>
    </tr>
<?
    //Aqui eu guardo a posição desses Itens da OS em um array, só a 1ª OS já resolve para mim ...
    $sql = "SELECT `id_op` 
            FROM `oss_itens` 
            WHERE `id_os` = '".$campos[0]['id_os']."' ";
    $campos_os_itens = bancos::sql($sql);
    $linhas_os_itens = count($campos_os_itens);
    for($i = 0; $i < $linhas_os_itens; $i++) {
        if($campos_os_itens[$i]['id_op'] == $_GET['txt_numero_op']) {
            $vetor_posicao[] = ($i + 1);//Essa posição será passada por parâmetro abaixo ...
        }
    }
    //Aqui eu listo todos os itens da OS ... 
    for ($i = 0;  $i < $linhas; $i++) {
        $url = "javascript:window.location = 'incluir_entrada.php?id_os=".$campos[$i]['id_os']."&id_os_item=".$campos[$i]['id_os_item']."&posicao=".$vetor_posicao[$i]."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href = "#">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href='#' class='link'>
                <?=$campos[$i]['id_op'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['id_os'];?>
        </td>
    </tr>
<?
    }
?>
      
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
}else {//Não encontrou nenhum Item na OS ...
?>
    <Script Language = 'Javascript'>
        alert('ITEM DE OS NÃO ENCONTRADO !')
        parent.html5Lightbox.finish()
    </Script>
<?    
}
?>