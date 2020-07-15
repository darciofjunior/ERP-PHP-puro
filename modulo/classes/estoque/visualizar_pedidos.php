<?
/*Eu tenho esse desvio aki para n�o redeclarar as bibliotecas novamente, isso porque tem alguns arquivos 
q essa parte de estoque embutida e sendo assim j� tem as bibliotecas declaradas logo no in�cio*/
//class_exists()
if($nao_chamar_biblioteca != 1) {
    require('../../../lib/segurancas.php');
    require('../../../lib/biblioteca.php');
    require('../../../lib/custos.php');
    require('../../../lib/vendas.php');
    require('../../../lib/data.php');
    require('../../../lib/intermodular.php');
    require('../../../lib/estoque_acabado.php');
    $nivel_arquivo = '../../';
    $nivel_imagem = '../../../';
}else {
/*Tem esse outro desvio, pq dependendo do lugar em que eu chamo essa fun��o, os n�veis tanto de arquivo
como de imagem s�o iguais aos n�veis menores como os acima ...*/
    if($nivel_reduzido == 1) {
        $nivel_arquivo = '../../';
        $nivel_imagem = '../../../';
    }else {
        $nivel_arquivo = '../../../';
        $nivel_imagem = '../../../../';
    }
}
if(!class_exists('custos')) { require('../../../lib/custos.php'); } // CASO EXISTA EU DESVIO A CLASSE
session_start('funcionarios');

//1) Se n�o existir Prazo de Entrega no Item do Or�amento, ent�o eu busco o Prazo no Grupo do PA ...
$sql = "SELECT LEAST(pvi.`prazo_entrega`, ovi.`prazo_entrega_tecnico`, gpa.`prazo_entrega`) AS prazo_producao_pedido, 
        gpa.`prazo_entrega`, ovi.`prazo_entrega_tecnico`, pvi.`id_pedido_venda`, pvi.`prazo_entrega` AS prazo_entrega_pedido, pvi.`status`, 
        pvi.`qtde`, DATE_FORMAT(pv.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        IF(pv.`faturar_em` <> '0000-00-00', pv.`faturar_em`, '') AS faturar_em, IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' AND pv.liberado = '1' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' 
        ORDER BY pv.`faturar_em` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Visualizar Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='95%' border='1' bgcolor='black' cellspacing='0' cellpadding='0' align='center'>
<?
    if($linhas == 0) {//Se n�o existir nenhum Pedido nas condi��es acima ...
?>

    <tr class='atencao' align='center'>
        <td>
            N�O EXISTE(M) PEDIDO(S) EM ABERTO.
        </td>
    </tr>
<?
    }else {//Se existir pelo menos 1 Pedido ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Visualizar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde Pedido
        </td>
        <td>
            N.� Pedido
        </td>
        <td>
            Cliente
        </td>
        <td>
            Data de <br/>Emiss�o
        </td>
        <td>
            Data � Faturar <br/>Pedido
        </td>
        <td>
            Prazo de Entrega <br/>calculado
        </td>
        <td>
            Prazo de Entrega Pedido / <br/>Depto. T�cnico / Grupo (dias)
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' style='cursor:help' class='link'>
                <?=$campos[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
        <?
            //Se o Prazo de Entrega do Pedido estiver em atraso e o Item do Pedido ainda n�o foi totalmente Faturado ent�o ...    
            if($campos[$i]['faturar_em'] <= date('Y-m-d') && $campos[$i]['status'] < 2) {//Vermelho ...
                $color  = 'red';
                $rotulo = '<font color="red"> (Atrasado)</font>';
            }else {//Normal ...
                $color  = '';
                $rotulo = '';
            }
            echo "<font color='$color'>".data::datetodata($campos[$i]['faturar_em'], '/')."</font>".$rotulo;
        ?>
        </td>
        <td>
        <?
            $data_entrega_minima    = data::adicionar_data_hora($campos[$i]['data_emissao'], intval($campos[$i]['prazo_producao_pedido']));
            $data_entrega_minima    = data::datatodate($data_entrega_minima, '-');
            $faturar_em             = $campos[$i]['faturar_em'];
            
            if($data_entrega_minima <= $faturar_em) {
                echo data::datetodata($faturar_em, '/').'<br/><font color="red"><b> (� Faturar em)</b></font>';
            }else {
                echo data::datetodata($data_entrega_minima, '/').'<br/><font color="red" title="� faturar em do pedido est� abaixo do prazo de entrega deste item no Pedido / Depto. T�cnico / Grupo" style="cursor:help"><b> (Data Cr�tica)</b></font>';
            }
        ?>
        </td>
        <td>
        <?
            //echo $campos[$i]['prazo_entrega_calculado'];
            echo $campos[$i]['prazo_entrega_pedido'].' / '.intval($campos[$i]['prazo_entrega_tecnico']).' / '.$campos[$i]['prazo_entrega'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>