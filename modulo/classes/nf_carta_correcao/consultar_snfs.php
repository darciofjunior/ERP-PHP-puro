<?
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) de Entrada ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_nfe, id_carta_correcao) {
    if(id_carta_correcao > 0) {
        var resposta = confirm('JÁ FOI GERADA UMA CARTA DE CORREÇÃO P/ ESTA NF !\n DESEJA EDITAR ESTA CARTA ?')
        if(resposta == true) {
            window.location = 'itens/consultar.php?passo=1&id_carta_correcao='+id_carta_correcao
        }else {
            return false
        }
    }else {
        var resposta = confirm('DESEJA GERAR UMA CARTA DE CORREÇÃO P/ ESTA NF ?')
        if(resposta == true) window.location = 'incluir.php?passo=2&id_nfe='+id_nfe
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Consultar Nota(s) Fiscal(s) de Entrada
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Importa&ccedil;&atilde;o
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Data Ent.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
//Aqui eu verifico se já foi feita alguma Carta de Correção p/ esta NF ...
            $sql = "SELECT id_carta_correcao 
                    FROM `cartas_correcoes` 
                    WHERE `id_nfe` = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
            $campos_carta_correcao = bancos::sql($sql);
            if(count($campos_carta_correcao) == 1) $id_carta_correcao = $campos_carta_correcao[0]['id_carta_correcao'];
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td onclick="javascript:avancar('<?=$campos[$i]['id_nfe'];?>', '<?=$id_carta_correcao;?>')" width="10">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="javascript:avancar('<?=$campos[$i]['id_nfe'];?>')">
            <a href="javascript:avancar('<?=$campos[$i]['id_nfe'];?>')" class='link'>
                <?=$campos[$i]['num_nota'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            $sql = "SELECT i.nome 
                    FROM `importacoes` i 
                    INNER JOIN `nfe` ON nfe.id_importacao = i.id_importacao 
                    WHERE nfe.`id_nfe` = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) > 0) {
                echo $campos_importacao[0]['nome'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
(<?
            if($campos[$i]['tipo'] == 1) {
                echo 'NF';
            }else {
                echo 'SGD';
            }
?>)
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>