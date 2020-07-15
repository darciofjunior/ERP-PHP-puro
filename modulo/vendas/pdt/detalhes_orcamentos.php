<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if($representante == '') $representante = '%';
$retorno            = pdt::funcao_geral_orcamentos($tipo_retorno, $dias, $representante, $inicio, $pagina, $paginacao = 'sim');
$campos             = $retorno['campos'];
$linhas_orcamentos  = count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) Orçamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_orcamento_venda) {
    parent.document.location = '../orcamentos/itens/itens.php?id_orcamento_venda='+id_orcamento_venda
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas_orcamentos == 0) {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) ORÇAMENTO(S) PENDENTE(S) NESTA CONDIÇÃO
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Detalhe(s) Orçamento(s)
            <?
                if(!empty($dias)) echo ' nos últimos <font color="yellow">'.$dias.'</font> dias';
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Orçamento
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cidade / Estado
        </td>
        <td>
            Contato
        </td>
        <td>
            Data de Validade
        </td>
        <td>
            Valor do Orc.
        </td>                
    </tr>
<?
	$prazo_validade_orc_dias = (int)genericas::variavel(38);
	for ($i = 0; $i < $linhas_orcamentos; $i++) {
            $url = "javascript:avancar('".$campos[$i]['id_orcamento_venda']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['id_orcamento_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=data::datetodata(data::datatodate(data::adicionar_data_hora($campos[$i]['data_emissao'], $prazo_validade_orc_dias), '-'), '/');?>
        </td>
        <td align='right'>
            <?=$campos[$i]['valor_orc'];?>
        </td>
    </tr>
<?
            $total_orcs+= $campos[$i]['valor_orc'];
	}
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='7'>
            Total Orc(s) =>
        </td>
        <td align='right'>
            <?=number_format($total_orcs, 2, ',', '.');?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>