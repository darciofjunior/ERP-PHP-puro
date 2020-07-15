<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');
$vetor_ctts = explode(',', $vetor_ctts);
?>
<html>
<head>
<title>.:: Resumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='100%' border='1' cellpadding='0' cellspacing='0'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <font color='yellow'>
                Resumo de Lote Mínimo / CTT
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            CTT
        </td>
        <td>
            Discriminação CTT
        </td>
        <td>
            Total de Saída
        </td>
        <td>
            Preço Unit. R$
        </td>
        <td>
            Total de Saída R$
        </td>
        <td>
            Lote Mínino R$
        </td>
        <td>
            Status
        </td>
    </tr>
<?
    for($i = 0; $i < count($vetor_ctts); $i++) {
        //Busca de alguns dados do CTT ...
        $sql = "SELECT `codigo`, `aplicacao_usual`, `descricao` 
                FROM `ctts` 
                WHERE `id_ctt` = '$vetor_ctts[$i]' LIMIT 1 ";
        $campos = bancos::sql($sql);
?>
    <tr class="linhanormal" align="center">
        <td>
            <?=$campos[0]['codigo'];?>
        </td>
        <td align="left">
                <?=$campos[0]['aplicacao_usual'].'<br>'.$campos[0]['descricao'];?>
        </td>
        <td>
        <?
            /*Busco o Somatório do Total de Saída somente daquele CTT, Maior Preço Unit. R$ 
            somente daquele CTT e Total de Saída R$ somente daquele CTT*/
            $sql = "SELECT SUM(oi.`peso_total_saida`) AS peso_total_saida, MAX(oi.`preco_pi`) AS maior_preco_pi, SUM(oi.`peso_total_saida` * oi.`preco_pi`) AS total_saida_rs, oi.`lote_minimo_custo_tt` 
                    FROM `oss_itens` oi 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = oi.`id_produto_insumo_ctt` 
                    INNER JOIN `ctts` ON ctts.`id_ctt` = pi.`id_ctt` AND ctts.`id_ctt` = '$vetor_ctts[$i]' 
                    WHERE oi.`id_os` = '$_GET[id_os]' ";
            $campos = bancos::sql($sql);
            echo number_format($campos[0]['peso_total_saida'], 3, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($campos[0]['maior_preco_pi'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[0]['total_saida_rs'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[0]['lote_minimo_custo_tt'], 2, ',', '.');?>
        </td>
        <td>
        <?
            //Fórmula p/ Controle de Status ...
            if($campos[0]['total_saida_rs'] > $campos[0]['lote_minimo_custo_tt']) {
                echo '<font color="blue">OK</font>';
            }else {
                //Faço esse tratamento p/ não dar erro na fórmula de Divisão por Zero ...
                if($campos[0]['maior_preco_pi'] == 0) $campos[0]['maior_preco_pi'] = 1;
                //Fórmula p/ saber o quanto que ainda resta p/ poder enviar ...
                $resultado = ($campos[0]['lote_minimo_custo_tt'] - $campos[0]['total_saida_rs']) / $campos[0]['maior_preco_pi'];
                
                //Faço a busca da Sigla do CTT p/ ver se é em Kilos, Unidades, ...
                $sql = "SELECT u.`sigla` 
                        FROM `ctts` 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_ctt` = ctts.`id_ctt` 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE ctts.`id_ctt` = '$vetor_ctts[$i]' ";
                $campos_unidade = bancos::sql($sql);
                
                echo '<font color="red">NÃO ENVIAR !<br>FALTAM '.number_format($resultado, 1, ',', '.').' '.$campos_unidade[0]['sigla'].' </font>';
            }
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
</body>
</html>