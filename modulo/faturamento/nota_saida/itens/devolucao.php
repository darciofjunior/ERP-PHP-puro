<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    if(!empty($chkt_nf_dev_aberto)) $condicao = " AND nfs.`devolucao_faturada` = 'N' ";

//Se o usuário consultar as NFs por número, então eu acrescento essa cláusula a mais no SQL ...
    if(!empty($txt_numero_nota_fiscal)) {
        $inner_nfs_num_notas    = "INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota AND nnn.numero_nf LIKE '%$txt_numero_nota_fiscal%' ";
        $numero_nota            = " AND nfs.`snf_devolvida` LIKE '%$txt_numero_nota_fiscal%' ";
    }

    $sql = "SELECT DISTINCT(nfs.id_nf) 
            FROM `nfs` 
            $inner_nfs_num_notas 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND (c.nomefantasia LIKE '%$txt_cliente%' OR c.razaosocial LIKE '%$txt_cliente%') AND c.`ativo` = '1' 
            WHERE nfs.`status` = '6' $condicao 
            UNION 
            SELECT DISTINCT(nfs.id_nf) 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND (c.nomefantasia LIKE '%$txt_cliente%' OR c.razaosocial LIKE '%$txt_cliente%') AND c.`ativo` = '1' 
            WHERE nfs.`status` = '6' $condicao $numero_nota ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($l = 0; $l < $linhas; $l++) $id_nfs[] = $campos[$l]['id_nf'];
//Arranjo Ténico
    if(count($id_nfs) == 0) $id_nfs[] = '0';
    $vetor_nfs = implode(',', $id_nfs);

    $sql = "SELECT nfs.id_nf, nfs.id_empresa, nfs.id_nf_num_nota, nfs.snf_devolvida, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.razaosocial, c.credito, t.nome as transportadora 
            FROM `nfs` 
            INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.`id_nf` IN ($vetor_nfs) $condicao 
            GROUP BY nfs.id_nf ORDER BY nfs.id_nf DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'devolucao.php?valor=1'
        </Script>
<?
    }else {
/***************************Script p/ Excluir as Notas Fiscais***************************/
//Aqui eu excluo as Notas Fiscais q não tiverem itens e q Empresa desta for Albafer ou Tool Master ...
        if(!empty($_GET['id_nf']) && empty($_GET['sair'])) {
            $sql = "DELETE FROM `nfs` WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
            bancos::sql($sql);
?>
            <Script Language = 'JavaScript'>
                alert('NOTA FISCAL EXCLUIDA COM SUCESSO !')
                window.location = 'devolucao.php<?=$parametro?>&sair=1'
            </Script>
<?
        }
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Consultar Notas Fiscais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_nota_fiscal(id_nf, executar) {
    if(executar == 1) {
        var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA NOTA FISCAL ?')
        if(resposta == true) {
            //Essa variável é uma jogadinha que eu faço p/ não ficar dando reload umas 500 vezes na Tela ...
            document.location = 'devolucao.php<?=$parametro;?>&id_nf='+id_nf+'&executar=0'
        }else {
            return false
        }
    }
}
</Script>
</head>
<body>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Consultar Nota(s) Fiscal(is) - <font color="yellow">Devolução</font>
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            N.&ordm; NF de Devolução
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Transportadora
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
        <td>
            <img src = "../../../../imagem/menu/excluir.png" border='0' title="Excluir Nota Fiscal" alt="Excluir Nota Fiscal">
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td width='10'>
            <a href='index.php?id_nf=<?=$campos[$i]['id_nf'];?>&opcao=4' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="index.php?id_nf=<?=$campos[$i]['id_nf'];?>&opcao=4" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'D');?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align="left">
        <?
            echo $campos[$i]['razaosocial'];
//Aqui verifica se a NF contém pelo menos 1 item
            $sql = "SELECT id_nfs_item 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_nf      = bancos::sql($sql);
            $qtde_itens_nf  = count($campos_nf);
            if($qtde_itens_nf == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td align="left">
        <?
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];

            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $apresentar = $nomefantasia.' (NF)';
            }else {
                $apresentar = $nomefantasia.' (SGD)';
            }
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
        <?
/*Só irá exibir esse link quando a Nota Fiscal não tiver nenhum Item e a Empresa desta 
for Albafer ou Tool Master ...*/
            if($qtde_itens_nf == 0) {
        ?>
                <img src = "../../../../imagem/menu/excluir.png" border='0' title="Excluir Nota Fiscal" alt="Excluir Nota Fiscal" onClick="excluir_nota_fiscal('<?=$campos[$i]['id_nf'];?>', 1)">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'devolucao.php'" class="botao">
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
<title>.:: Consultar Notas Fiscais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload="document.form.txt_numero_nota_fiscal.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Notas Fiscais para Incluir Itens
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da Nota Fiscal
        </td>
        <td>
            <input type="text" name="txt_numero_nota_fiscal" title="Digite o N.º da Nota Fiscal" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type="text" name="txt_cliente" title="Digite o Cliente" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_nf_dev_aberto' value='1' title='Só NF(s) de Devolução em aberto' id='label' class='checkbox' checked>
            <label for='label'>Só NF(s) de Devolução em aberto</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_nota_fiscal.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>