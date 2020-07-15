<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
?>
<html>
<head>
<title>.:: Maiores Compradores por Família ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
<tr class='linhacabecalho' align='center'>
    <td colspan='7'>
        Maiores Compradores por Família<br/>
        <font color='yellow'>
            <b>Família: </b>
        </font>
        <select name='cmb_familia' title='Selecione uma Família' class='combo'>
        <?
            $sql = "SELECT `id_familia`, `nome` 
                    FROM `familias` 
                    WHERE `ativo` = '1' ORDER BY `nome` ";
            echo combos::combo($sql, $_POST[cmb_familia]);
        ?>
        </select>
        <p/>
        <font color='yellow'>
            Empresa Divisão: 
        </font>
        <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
        <?
            $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                    FROM `empresas_divisoes` 
                    WHERE `ativo` = '1' ORDER BY `razaosocial` ";
            echo combos::combo($sql, $_POST[cmb_empresa_divisao]);
        ?>
        </select>
        &nbsp;-&nbsp;
        <font color='yellow'>
            <b>Vendedor: </b>
        </font>
        <?
            //Foi passado um Vendedor por parâmetro em específico e não é Vazio ...
            if($representante != '%' && $representante != '') {
                $sql = "SELECT `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `id_representante` = '$representante' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
            }else {//Significa que NÃO foi passado um Vendedor específico por parâmetro, então listo uma combo ...
        ?>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
        <?
            $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                    FROM `representantes` 
                    WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
            echo combos::combo($sql, $cmb_representante);
        ?>
        </select>
        <?
            }
        ?>
        <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
    </td>
<?
    if($_POST['cmd_consultar'] != '') {
        if(empty($representante))   $representante  = '%';
        $condicao_representante     = (!empty($_POST['cmb_representante'])) ? "'".$_POST[cmb_representante]."'" : "'".$representante."'";
        
        //Se a combo Empresa Divisão estiver preenchida, então eu só trago Pedidos que contenham Produtos da Empresa Divisão selecionada ...
        if(!empty($_POST['cmb_familia'])) {
            if(!empty($_POST['cmb_empresa_divisao'])) $condicao_empresa_divisao = " AND ged.`id_empresa_divisao` = '$_POST[cmb_empresa_divisao]' ";
            
            $inner_join =  "INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` $condicao_empresa_divisao 
                            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` = '$_POST[cmb_familia]' ";
        }else if(empty($_POST['cmb_familia']) && !empty($_POST['cmb_empresa_divisao'])) {
            $inner_join =  "INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '$_POST[cmb_empresa_divisao]' ";
        }
        
        $sql = "SELECT SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.`preco_liq_final`) AS volume, 
                c.`id_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
                c.`cep`, c.`cidade`, c.`email`, ufs.`sigla` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                $inner_join 
                WHERE pvi.`id_representante` LIKE $condicao_representante 
                GROUP BY pv.`id_cliente` 
                ORDER BY volume DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Cep
        </td>
        <td>
            Email
        </td>
        <td>
            Representante
        </td>        
        <td>
            Volume R$
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=$campos[$i]['cep'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['email'];?>
        </td>
        <td>
        <?
            /*Aqui eu busco o Representante atual do Cliente, tive que fazer esse SQL aqui dentro do Loop 
            porque senão o Sistema traz o Representante da época da negociação e fica totalmente inviável 
            essa apresentação para o que precisamos fazer hoje ...*/
            $sql = "SELECT r.`nome_fantasia`
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
            <a href = '../apv/relatorio_vendas_referencia_ano.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&cmb_familia=<?=$_POST['cmb_familia'];?>' class='html5lightbox'>
                <?=number_format($campos[$i]['volume'], 2, ',', '.');?>
            </a>
        </td>        
    </tr>
<?
        } 
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'">
        </td>
    </tr>
    <tr align='center' class='confirmacao'>
        <td colspan='7'>
            <br/>
            Total de Registro(s): <?=$linhas;?>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>