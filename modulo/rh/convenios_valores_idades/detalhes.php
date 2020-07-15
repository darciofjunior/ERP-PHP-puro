<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/categorizacoes/categorizacoes.php', '../../../../');

if($_GET['opcao'] == 1) {//Aqui eu listo todos os Clientes que estão com este Tipo em Uso  
    $condicao   = " `id_cliente_tipo` = '$_GET[id]' ";
    $rotulo     = 'Tipo';
}else {//Aqui eu listo todos os Clientes que estão com este Perfil em Uso
    $condicao   = " `id_cliente_perfil` = '$_GET[id]' ";
    $rotulo     = 'Perfil';
}

$sql = "SELECT razaosocial, nomefantasia, id_uf 
        FROM `clientes` 
        WHERE $condicao 
        AND `ativo` = '1' ORDER BY razaosocial ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Cliente(s) que utilizam este <?=$rotulo;?> no Cadastro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Cliente(s) que utilizam este <?=$rotulo;?> no Cadastro
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            UF
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT sigla 
                    FROM `ufs` 
                    WHERE `id_uf` = ".$campos[$i]['id_uf']." LIMIT 1 ";
            $campos_sigla = bancos::sql($sql);
            echo $campos_sigla[0]['sigla'];
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>