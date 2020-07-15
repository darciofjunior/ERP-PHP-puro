<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');

require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');

segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

$ano_inicial    = 2006;
$colspan        = 10 + (date('Y') - $ano_inicial + 1) + 1;//Colunas Fixas + Colunas Dinâmicas + 1 que é o próprio ano atual + 1 que é o Total de todos os anos ...
?>
<html>
<head>
<title>.:: Relatório de Pedido(s) Emitido(s) vs Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function mostrar_todos() {
    var mostrar_todos = (document.form.chkt_mostrar_todos.checked == true) ? 'S' : 'N'
    window.location = '<?=$PHP_SELF;?>?mostrar_todos='+mostrar_todos
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='1' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$colspan;?>'>
            Vendedor:
            <?
//Verifico se o Vendedor foi passado por Parâmetro ...
                if(!empty($representante)) {
                    $sql = "SELECT `nome_fantasia` 
                            FROM `representantes` 
                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    echo $campos_representante[0]['nome_fantasia'];
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
                }else {
            ?>
                    <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                        //Esse parâmetro só é abastecido nessa tela quando o usuário clica no Checkbox Somente Inativos ...
                        if($_GET['mostrar_todos'] == 'S') {
                            $condicao   = '';
                            $checked    = 'checked';
                        }else {
                            $condicao   = " WHERE `ativo` = '1' ";
                            $checked    = '';
                        }
            
                        $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                                FROM `representantes` 
                                $condicao ORDER BY `nome_fantasia` ";
                        echo combos::combo($sql, $cmb_representante);
            ?>
                    </select>
                    &nbsp;
                    <input type='checkbox' name='chkt_mostrar_todos' value='S' title='Mostrar Todos' onclick='mostrar_todos()' class='checkbox' id='label' <?=$checked;?>>
                    <label for='label'>
                        Mostrar Todos
                    </label>
                    <br/>
                    Empresa Divisão: 
                    <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
                    <?
                        $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                                FROM `empresas_divisoes` 
                                WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                        echo combos::combo($sql, $_POST['cmb_empresa_divisao']);
                    ?>
                    </select>
                    &nbsp;-&nbsp;
                    Estado:
                    <select name='cmb_uf' title='Selecione o Estado' class='combo'>
                    <?
                        $sql = "SELECT `id_uf`, `sigla` 
                                FROM `ufs` 
                                WHERE `ativo` = '1' ORDER BY `sigla` ";
                        echo combos::combo($sql, $_POST['cmb_uf']);
                    ?>
                    </select>
                    &nbsp;
                <?
                    if($_POST['chkt_somente_com_pedidos'] == 'S') $checked_somente_com_pedidos = 'checked';
                ?>
                <input type='checkbox' name='chkt_somente_com_pedidos' value='S' id='chkt_somente_com_pedidos' class='checkbox' <?=$checked_somente_com_pedidos;?>>
                <label for='chkt_somente_com_pedidos'>
                    Somente com Pedido(s)
                </label>
                &nbsp;
                <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
<?
/*Se foi passado um parâmetro de Representante, ou foi selecionado algum Representante na Combo, ou foi selecionado 
algum Estado na Combo então eu realizo o SQL abaixo ...*/
    if(!empty($_POST['cmd_consultar'])) {
        if(empty($_POST['representante']) && empty($_POST['cmb_representante'])) {//Nessa situação não foi selecionado representante, somente UF ...
            $id_representante   = '%';
            $moeda              = ' R$';//Nesse caso é do Brasil mesmo, afinal foi selecionada uma Unidade Federal ...
        }else {//Foi selecionado algum Representante, independente de termos alguma uma UF ...
            $id_representante = (!empty($_POST['representante'])) ? $_POST['representante'] : $_POST['cmb_representante'];
            //Verificar qual o País do Representante p/ saber qual o Tipo de Moeda ...
            $sql = "SELECT `id_pais` 
                    FROM `representantes` 
                    WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            $campos_pais    = bancos::sql($sql);
            $moeda          = ($campos_pais[0]['id_pais'] == 31 || is_null($campos_pais[0]['id_pais'])) ? ' R$' : ' U$';
        }
        
        //Se a combo Empresa Divisão estiver preenchida, então eu só trago Pedidos que contenham Produtos da Empresa Divisão selecionada ...
        if(!empty($_POST['cmb_empresa_divisao'])) $inner_join_empresa_divisao = "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`id_gpa_vs_emp_div` = '$_POST[cmb_empresa_divisao]' ";
        
        /***************************************************************************************************/
        //Busco todos os Pedidos de Vendas agrupando por Ano dentro dos últimos 5 anos, que serão apresentados mais abaixo ...
        $sql = "SELECT pv.`id_cliente`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total_pedidos_emitidos_ano, 
                YEAR(pv.`data_emissao`) AS ano_corrente 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                $inner_join_empresa_divisao 
                WHERE YEAR(pv.`data_emissao`) >= '$ano_inicial' 
                AND pv.`liberado` = '1' 
                GROUP BY pv.`id_cliente`, YEAR(pv.`data_emissao`) ORDER BY pv.`id_cliente` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $total_emitido_por_cliente_no_ano[$campos[$i]['ano_corrente']][$campos[$i]['id_cliente']] = $campos[$i]['total_pedidos_emitidos_ano'];
            $total_emitido_por_ano[$campos[$i]['ano_corrente']]+= $campos[$i]['total_pedidos_emitidos_ano'];
        }
        /***************************************************************************************************/
        
        //Se a combo UF estiver preenchida, então eu só trago Clientes da UF selecionada ...
        if(!empty($_POST['cmb_uf'])) $condicao_filtro = " AND c.`id_uf` LIKE '$_POST[cmb_uf]' ";
        
        /*Se essa opção estiver marcada, trago todos os Clientes que compraram pelo menos uma vez 
        na Vida aqui conosco dentro dos últimos 5 anos ...*/
        if($_POST['chkt_somente_com_pedidos'] == 'S') {
            //Aqui só trago os clientes de acordo com o Ano do Loop e Pedidos que contenham Produtos da Empresa Divisão selecionada ...
            $sql = "SELECT pv.`id_cliente` 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`ativo` = '1' $condicao_filtro 
                    /*Nem sempre será utilizado esse JOIN abaixo, mas deixei este preparado por causa da variável $inner_join_empresa_divisao ...*/
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    $inner_join_empresa_divisao 
                    WHERE YEAR(pv.`data_emissao`) >= '$ano_inicial' 
                    AND pv.`liberado` = '1' 
                    GROUP BY pv.`id_cliente` ";
            $campos_clientes = bancos::sql($sql);
            $linhas_clientes = count($campos_clientes);
            for($i = 0; $i < $linhas_clientes; $i++) $id_clientes.= $campos_clientes[$i]['id_cliente'].', ';
            $id_clientes        = substr($id_clientes, 0, strlen($id_clientes) - 2);
            $condicao_clientes  = " AND c.`id_cliente` IN ($id_clientes) ";
        }

        $sql = "SELECT c.`id_cliente`, 
                IF(c.`nomefantasia` <> '', CONCAT(c.`nomefantasia`, '  ---> (', ct.`tipo`, ')'), 
                CONCAT(c.`razaosocial`, ' ---> (', ct.`tipo`, ')')) AS cliente, c.`bairro`, c.`cep`, 
                c.`cidade`, c.`email`, r.`nome_fantasia`, ufs.`sigla` 
                FROM `clientes` c 
                LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$id_representante' 
                INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                WHERE c.`ativo` = '1' 
                $condicao_filtro 
                $condicao_clientes 
                GROUP BY c.`id_cliente` ORDER BY cliente ";
        $campos_clientes    = bancos::sql($sql);
        $linhas_clientes    = count($campos_clientes);
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='<?=$colspan;?>'>
            Relatório de Pedido(s) Emitido(s) vs Cliente(s)
            &nbsp;
            <font color='darkblue' size='-1'>
                PEDIDO
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Bairro
        </td>
        <td>
            Cidade
        </td>
        <td>
            Cep
        </td>
        <td>
            UF
        </td>
        <td>
            Email
        </td>
        <td>
            Última NF
        </td>
        <td>
            Data Última NF
        </td>
<?
        for($j = $ano_inicial; $j <= date('Y'); $j++) {
?>
        <td>
            <?=$j.$moeda;?>
        </td>
<?
        }
?>
        <td>
            Total
        </td>
        <td>
            Ultimos Follow Ups
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_clientes; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href= '../apv/informacoes_apv.php?id_clientes=<?=$campos_clientes[$i]['id_cliente'];?>' title='APV do Cliente' style='cursor:help' class='link'>
                <?=$campos_clientes[$i]['cliente'];?>
            </a>
        </td>
        <td align='center'>
            <?=$campos_clientes[$i]['nome_fantasia'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos_clientes[$i]['bairro'])) {
                echo $campos_clientes[$i]['bairro'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos_clientes[$i]['cidade'];?>
        </td>
        <td align='left'>
            <?=$campos_clientes[$i]['cep'];?>
        </td>
        <td>	
        <?
            if(!empty($campos_clientes[$i]['sigla'])) {
                echo $campos_clientes[$i]['sigla'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos_clientes[$i]['email'];?>
        </td>
        <td>
            <?
                //Busco última NF que foi emitida para o determinado Cliente do Loop ...
                $sql = "SELECT `id_nf`, DATE_FORMAT(`data_emissao`, '%d/%m/%Y') AS data_emissao 
                        FROM `nfs` 
                        WHERE `id_cliente` = '".$campos_clientes[$i]['id_cliente']."' ORDER BY `id_nf` DESC LIMIT 1 ";
                $campos_ultima_nf = bancos::sql($sql);
            ?>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_ultima_nf[0]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 1010, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes' class='link'>
                <?=faturamentos::buscar_numero_nf($campos_ultima_nf[0]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
            <?=$campos_ultima_nf[0]['data_emissao'];?>
        </td>
        <?
            for($j = $ano_inicial; $j <= date('Y'); $j++) {
        ?>
        <td>
        <?
            $total_de_todos_clientes_por_ano[$j]+= $total_emitido_por_cliente_no_ano[$j][$campos_clientes[$i]['id_cliente']];
            $total_de_todos_anos_por_cliente+= $total_emitido_por_cliente_no_ano[$j][$campos_clientes[$i]['id_cliente']];
            
            echo segurancas::number_format($total_emitido_por_cliente_no_ano[$j][$campos_clientes[$i]['id_cliente']], 2, '.');
        ?>
        </td>
        <?
            }
        ?>
        <td>
            <?=segurancas::number_format($total_de_todos_anos_por_cliente, 2, '.');?>
        </td>
        <td align='center'>
        <?
            /*if(!$total_parcial_ano_atual && !$total_parcial_ano_atual_menos1 && !$total_parcial_ano_atual_menos2 && !$total_parcial_ano_atual_menos3 && !$total_parcial_ano_atual_menos4) {
                $sql = "SELECT fu.`data_sys` 
                        FROM `follow_ups` fu 
                        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = fu.`id_cliente_contato` AND cc.`id_cliente` = '".$campos_clientes[$i]['id_cliente']."' 
                        ORDER BY fu.`data_sys` DESC LIMIT 1";
                $campos_follow_ups  = bancos::sql($sql);
                echo data::datetodata(substr($campos_follow_ups[0]['data_sys'], 0, 10), '/');
            }else {
                echo '&nbsp';
            }*/
        ?>
        </td>
    </tr>
<?
            //Limpo essa variável p/ não herdar valores do Loop anterior ...
            $total_de_todos_anos_por_cliente = 0;
        }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='8' align='left'>
            <font color='red'>
                <b>TOTAL DE PEDIDO(S) EMITIDO(S) VISÍVEL(IS)</b>
            </font>
        </td>
        <?
            for($j = $ano_inicial; $j <= date('Y'); $j++) {
        ?>
        <td>
        <?
            echo number_format($total_de_todos_clientes_por_ano[$j], 2, ',', '.');
        ?>
        </td>
        <?
            }
        ?>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='8' align='left'>
            <font color='red'>
                <b>TOTAL DE PEDIDO(S) EMITIDO(S) POR ANO</b>
            </font>
        </td>
        <?
            for($j = $ano_inicial; $j <= date('Y'); $j++) {
        ?>
        <td>
            <b>
        <?
            echo number_format($total_emitido_por_ano[$j], 2, ',', '.');
        ?>
            </b>
        </td>
        <?
            }
        ?>
        <td colspan='2'>
            &nbsp;
        </td>                
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='8' align='left'>
            <font color='red'>
                <b>TOTAL DE PEDIDO(S) EMITIDO(S) - CLIENTES COM DIVISÃO DE LINHA</b>
            </font>
        </td>
        <?
            for($j = $ano_inicial; $j <= date('Y'); $j++) {
        ?>
        <td>
        <?
            echo number_format($total_emitido_por_ano[$j] - $total_de_todos_clientes_por_ano[$j], 2, ',', '.');
        ?>
        </td>
        <?
            }
        ?>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$colspan;?>'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'estrategia_vendas.php?representante=<?=$id_representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' class='botao'>
        </td>
    </tr>
</table>
<?
//Se não foi passado nenhum representante por parâmetro ...
    }else {
?>
    <tr></tr>
    <tr class='atencao' align='center'>
        <td colspan='<?=$colspan;?>'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$id_representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>