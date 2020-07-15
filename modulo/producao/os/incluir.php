<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_razao_social   = $_POST['txt_razao_social'];
        $txt_cnpj_cpf       = $_POST['txt_cnpj'];
        $txt_produto        = $_POST['txt_produto'];
        $chkt_internacional = $_POST['chkt_internacional'];
    }else {
        $txt_razao_social   = $_GET['txt_razao_social'];
        $txt_cnpj_cpf       = $_GET['txt_cnpj'];
        $txt_produto        = $_GET['txt_produto'];
        $chkt_internacional = $_GET['chkt_internacional'];
    }
    
    if(!empty($txt_cnpj_cpf)) {
        $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
        $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
        $txt_cnpj_cpf   = str_replace('/', '', $txt_cnpj_cpf);
        $txt_cnpj_cpf   = str_replace('-', '', $txt_cnpj_cpf);
    }
    
    $condicao_internacional = ($chkt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
            FROM `fornecedores` 
            WHERE `razaosocial` LIKE '%$txt_razao_social%' 
            AND `cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
            AND `produto` LIKE '%$txt_produto%' 
            $condicao_internacional 
            AND `ativo` = '1' 
            AND `razaosocial` <> '' 
            ORDER BY `razaosocial` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language= 'Javascript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Fornecedor(es) p/ Incluir OS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_fornecedor, oss_abertas) {
    if(oss_abertas != '') {//O Fornecedor possui alguma O.S. em aberto
        alert('ESTE FORNECEDOR JÁ POSSUI O.S(S) EM ABERTO(S) E QUE ESTÃO SEM ITEM(NS) !\nESCOLHA UMA DESSA(S) O.S(S) N.º -> '+oss_abertas+' !')
    }else {//O Fornecedor não possui nenhuma O.S. em aberto, portanto pode continuar
        window.location = 'incluir.php?passo=2&id_fornecedor='+id_fornecedor
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr>
        <td colspan='4'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Fornecedor(es) p/ Incluir OS
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endereço
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas; $i++) {
        $url = "incluir.php?passo=2&id_fornecedor=".$campos[$i]['id_fornecedor'];
        //Aki verifico todas as O.S. que contém pelo menos 1 item ...
        $sql = "SELECT DISTINCT(oss.`id_os`) 
                FROM `fornecedores` f 
                INNER JOIN `oss` ON oss.`id_fornecedor` = f.`id_fornecedor` AND oss.`status_nf` < '2' 
                RIGHT JOIN `oss_itens` oi ON oi.`id_os` = oss.`id_os` 
                WHERE f.`id_fornecedor` = '".$campos[$i]['id_fornecedor']."' ";
        $campos_os = bancos::sql($sql);
        $linhas_os = count($campos_os);
        //Dispara outro For
        $id_oss = '';
        for($j = 0; $j < $linhas_os; $j++) $id_oss.= $campos_os[$j]['id_os'].',';
        //Esse macete é para forçar a entrar no sql da linha 181 ...
        if(strlen($id_oss) == 0) $id_oss = '0, ';
        $id_oss = substr($id_oss, 0, strlen($id_oss) - 2);
        //Se foi encontrado mais de 2 O.S., caso isso vai refletir na condição do Sql mais abaixo
        if(strpos($id_oss, ',') > 0) {//Se existir vírgula, então significa q tem no mínimo 2 O.S.
            $tipo_comparacao = ' NOT IN ('.$id_oss.') ';
        }else {
            $tipo_comparacao = ' <> '.$id_oss;
        }
        //Aki verifico todas as O.S. que estão em aberto e q não possuem nenhum item ...
        $sql = "SELECT oss.`id_os` 
                FROM `oss` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
                WHERE oss.`id_fornecedor` = '".$campos[$i]['id_fornecedor']."' 
                AND oss.`id_os` $tipo_comparacao 
                AND oss.`status_nf` < '2' ORDER BY `id_os` LIMIT 10 ";
        $campos_os = bancos::sql($sql);
        $linhas_os = count($campos_os);
        //Aki sempre limpa a variável, para não dar problema com o Paçoquinha (rsrsrs)
        $id_oss2 = '';
        //Dispara outro For, aki concatena as O.S. que não possui item(ns)
        for($j = 0; $j < $linhas_oss; $j++) $id_oss2.= $campos_os[$j]['id_os'].',';
        $id_oss2 = substr($id_oss2, 0, strlen($id_oss2) - 1);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="prosseguir('<?=$campos[$i]['id_fornecedor'];?>', '<?=$id_oss2;?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_fornecedor'];?>', '<?=$id_oss2;?>')" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td>
        <?
            $endereco = $campos[$i]['endereco'];
            if(empty($endereco)) $endereco = '';
            echo $endereco;
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}else if($passo == 2) {
    $sql = "INSERT INTO `oss` (`id_os`, `id_empresa`, `id_fornecedor`, `data_sys`) VALUES (NULL, '4', '$_GET[id_fornecedor]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $id_os = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('A EMPRESA GERADA PARA ESTA OS FOI COMO SENDO "GRUPO" - CERTIFIQUE-SE DE QUE ESTÁ ESTEJA CORRETA !')
        window.location = 'itens/index.php?id_os=<?=$id_os;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) p/ Incluir OS :::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fornecedor(es) p/ Incluir OS
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' size='55' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ / CPF
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ ou CPF' size='20' maxlength='18' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Produto
        </td>
        <td>
            <input type='text' name='txt_produto' title='Digite o Produto' size='60' maxlength='255' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_internacional' value='1' title='Consultar fornecedores internacionais' id='label1' class='checkbox'>
            <label for='label1'>Internacionais</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_razao_social.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>