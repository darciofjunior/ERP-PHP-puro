<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/********************************************************/
/***********************Cabeçalhos***********************/
/********************************************************/
//Cabeçalho 1)
function cabecalho_fornecedor($razao_social, $id_fornecedor) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                Fornecedor:
                <font color='#FFFFFF'>
                    <?=$razao_social;?>
                </font>
            </font>
            <a href = '../../../classes/fornecedor/alterar.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&pop_up=1' class='html5lightbox'>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Fornecedor' alt='Detalhes de Fornecedor' style='cursor:pointer' border='0'>
            </a>
        </td>
    </tr>
</table>
<?
}

//Cabeçalho 2)
function cabecalho_oss($id_os, $data_saida, $nossa_nf_num) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                &nbsp;OS N.º:
            </font>
            <font color='#FFFFFF'>
                <?=$id_os;?>
            </font>
            <font color='yellow'>
                / Data de Saída:
            </font>
            <font color='#FFFFFF'>
                <?=$data_saida;?>
            </font>
            <font color='yellow'>
                / Nossa Nota Fiscal N.º:
            </font>
            <font color='#FFFFFF'>
                <?=$nossa_nf_num;?>
            </font>
        </td>
    </tr>
</table>
<!--//Pré-Cabeçalho de Itens-->
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr><td></td></tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            N.º OP
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            Qtde de
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            Dif.<br/>Qtde
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            Produto
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            Matéria Prima
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            Total de
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            CTT
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            Preço<br/>Unit. R$
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            Total<br/>Saída R$
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            Dureza
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            Saída
        </td>
        <td bgcolor='#CECECE'>
            Ent.
        </td>
        <td bgcolor='#CECECE'>
            Saída
        </td>
        <td bgcolor='#CECECE'>
            Ent.
        </td>
        <td bgcolor='#CECECE'>
            Fornecedor
        </td>
        <td bgcolor='#CECECE'>
            Interno
        </td>
    </tr>
<?
}
/********************************************************/

if($passo == 1) {
    $sql = "SELECT f.`id_fornecedor`, f.`razaosocial`, 
            DATE_FORMAT(oss.`data_saida`, '%d/%m/%Y') AS data_saida, IF(oss.`nnf` = '', 'O.S. do Tipo SGD', nnf) AS nnf_sgd, 
            ossi.* 
            FROM `fornecedores` f 
            INNER JOIN `oss` ON oss.`id_fornecedor` = f.`id_fornecedor` AND oss.`status_nf` IN (0, 1) 
            INNER JOIN `oss_itens` ossi ON ossi.`id_os` = oss.`id_os` AND ossi.`status` < '2' 
            INNER JOIN `ops` ON ops.`id_op` = ossi.`id_op` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY f.razaosocial, oss.id_os ";
    $campos = bancos::sql($sql, $inicio, 10000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'pendencia.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Relatório de Pendêcia de O.S(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post'>
<?
//Variáveis p/ controlarmos a hora em que se trocaram os Fornecedores e O.S. no Loop ...
        $id_fornecedor_antigo = '';
        $id_os_antiga = '';
        for($i = 0; $i < $linhas; $i++) {
//1) Controle com Fornecedores
//Aki significa que mudou para outro Fornecedor ...
            if($id_fornecedor_antigo != $campos[$i]['id_fornecedor']) {
//Igualo a variável de Fornecedor Antigo, com a do Novo Fornecedor Corrente ...
                $id_fornecedor_antigo = $campos[$i]['id_fornecedor'];
//Então eu chamo o Cabeçalho p/ apresentar os dados desse novo Fornecedor ...
                cabecalho_fornecedor($campos[$i]['razaosocial'], $campos[$i]['id_fornecedor']);
            }
//2) Controle com as OS(s)
            if($id_os_antiga != $campos[$i]['id_os']) {
//Igualo a variável de O.S. Antiga, com a da Nova O.S. Corrente ...
                $id_os_antiga = $campos[$i]['id_os'];
//Então eu chamo o Cabeçalho de O.S. p/ apresentar os dados dessa nova O.S. ...
                cabecalho_oss($campos[$i]['id_os'], $campos[$i]['data_saida'], $campos[$i]['nnf_sgd']);
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_op'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_saida'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_entrada'];?>
        </td>
        <td>
        <?
//Comparação entre as 2 Quantidades - Faço controle de Cores ...
            if((($campos[$i]['qtde_entrada'] / $campos[$i]['qtde_saida']) > 1.01) || (($campos[$i]['qtde_entrada'] / $campos[$i]['qtde_saida']) < 0.99)) {
                $color = 'red';
            }else {
                $color = 'blue';
            }
            $resultado = $campos[$i]['qtde_entrada'] - $campos[$i]['qtde_saida'];
            echo "<font color=$color>".$resultado."</font>";
        ?>
        </td>
        <td align='left'>
        <?
//Busca dos Produtos da OP agora através do id_op que está na OS
            $sql = "SELECT pa.id_produto_acabado, pa.referencia 
                    FROM `ops` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                    WHERE ops.`id_op` = ".$campos[$i]['id_op']." ";
            $campos_op = bancos::sql($sql);
            echo intermodular::pa_discriminacao($campos_op[0]['id_produto_acabado']);
//Aki eu printo se é Retrabalho na Frente da Discriminação ...
            if($campos[$i]['retrabalho'] == 1) echo ' <font color="red"><b>RETRABALHO</b></font>';
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT discriminacao 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_mat_prima']."' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            if(!empty($campos_pi[0]['discriminacao'])) {
                echo $campos_pi[0]['discriminacao'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_total_saida'], 3, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_total_entrada'], 2, ',', '.');?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            if(!empty($campos_pi[0]['dados'])) {
                echo $campos_pi[0]['dados'];
            }else {
                echo '&nbsp;';
            }
//Verifico se esse PI tem algum CTT, atrelado ...
            $sql = "SELECT ctts.id_ctt, ctts.codigo AS dados_ctt 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `ctts` ON ctts.`id_ctt` = pi.`id_ctt` 
                    WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' ";
            $campos_ctts = bancos::sql($sql);
            //Se encontrar CTT atrelado ao PI, então eu printo este ...
            if(count($campos_ctts) == 1) echo ' / <font color="darkblue">'.$campos2[0]['dados_ctt'].'</font>';
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_pi'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $valor_item_os = $campos[$i]['peso_total_saida'] * $campos[$i]['preco_pi'];
            $valor_total_os+= $valor_item_os;
            echo number_format($valor_item_os, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['dureza_fornecedor'])) {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['dureza_fornecedor'];
            }
        ?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['dureza_interna'])) {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['dureza_interna'];
            }
        ?>
        </td>
    </tr>
<?
        }
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'pendencia.php'" class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Relatório de Pendência de O.S(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Pendência de O.S(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' title='Digite o Fornecedor' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>