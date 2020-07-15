<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/faturamentos.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if($representante == '') $representante = '%';
$retorno                = pdt::funcao_geral_faturamentos($tipo_retorno, $dias, $representante, $inicio, $pagina, $paginacao = 'sim');
$campos                 = $retorno['campos'];
$linhas_notas_fiscais   = count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) Nota(s) Fiscal(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas_notas_fiscais == 0) {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) NOTA(S) FISCAL(IS) PENDENTE(S) NESTA CONDIÇÃO
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Detalhe(s) Nota(s) Fiscal(is)
            <?
                if(!empty($dias)) echo ' nos últimos <font color="yellow">'.$dias.'</font> dias';
            ?>
            -
            <font color='yellow'> 
            <?
                if($tipo_retorno == 0) {//Nota não despachada ...
                    echo 'Não Despachada(s)';
                }else {//Devolução ...
                    echo 'Devolvida(s)';
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota Fiscal
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
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br/>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
	$vetor          = array_sistema::nota_fiscal();
        $tipo_despacho  = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'TAM');
	for ($i = 0; $i < $linhas_notas_fiscais; $i++) {
//Aqui eu trago dados da Nota Fiscal do Loop ...
            $sql = "SELECT DISTINCT(nfs.id_nf), nfs.id_empresa, nfs.id_nf_num_nota, nfs.snf_devolvida, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.nomefantasia, c.razaosocial, c.credito, t.nome AS transportadora 
                    FROM `nfs` 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                    WHERE `nfs`.id_nf = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_nfs = bancos::sql($sql);
            
            $url = "javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$campos_nfs[0]['id_nf']."&pop_up=1', 'NOTA_FISCAL', '', '', '', '', 550, 975, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=faturamentos::buscar_numero_nf($campos_nfs[0]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
        <?
            if($campos_nfs[0]['data_emissao'] != '0000-00-00') echo data::datetodata($campos_nfs[0]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
            <font title='Nome Fantasia: <?=$campos_nfs[0]['nomefantasia'];?>' style='cursor:help'>
                <?=$campos_nfs[0]['razaosocial'];?>
            </font>
        </td>
        <td>
            <?=$campos_nfs[0]['transportadora'];?>
        </td>
        <td>
        <?
            echo $vetor[$campos_nfs[0]['status']];
            if($campos_nfs[0]['status'] == 4) echo ' ('.$tipo_despacho[$campos_nfs[0]['tipo_despacho']].')';
        ?>
        </td>
        <td align='left'>
        <?
            //Busca o nome da Empresa pela qual foi faturada a NF ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = '".$campos_nfs[0]['id_empresa']."' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];

            if($campos_nfs[0]['id_empresa'] == 1 || $campos_nfs[0]['id_empresa'] == 2) {
                $apresentar = $nomefantasia.' (NF)';
            }else {
                $apresentar = $nomefantasia.' (SGD)';
            }

            if($campos_nfs[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_nfs[0]['vencimento4'];
            if($campos_nfs[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos_nfs[0]['vencimento3'].$prazo_faturamento;
            if($campos_nfs[0]['vencimento2'] > 0) {
                $prazo_faturamento= $campos_nfs[0]['vencimento1'].'/'.$campos_nfs[0]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_nfs[0]['vencimento1'] == 0) ? 'À vista' : $campos_nfs[0]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            &nbsp;
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