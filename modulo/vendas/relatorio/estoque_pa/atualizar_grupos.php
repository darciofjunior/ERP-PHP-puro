<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/estoque_pa/estoque_pa.php', '../../../../');

$prazo_busca_pedidos    = intval(genericas::variavel(26));

if($passo == 1) {
    $valor_dolar_dia        = genericas::moeda_dia('dolar');
    $data_final             = date('Y-m-d');
    $data_inicial           = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -$prazo_busca_pedidos), '-');

    //Busca de todos os Pedidos Liberados que estão nas respectivas Datas de Emissão passadas por parâmetro ...
    $sql = "SELECT ged.`id_gpa_vs_emp_div`, gpa.`nome`, 
            IF((c.`id_pais` = '31'), (pvi.`qtde` * ovi.`preco_liq_fat`), (pvi.`qtde` * ovi.`preco_liq_fat`) * $valor_dolar_dia) AS total_s_desconto, 
            IF((c.`id_pais` = '31'), (pvi.`qtde` * ovi.`preco_liq_final`), (pvi.`qtde` * ovi.`preco_liq_final`) * $valor_dolar_dia) AS total_c_desconto, pvi.`margem_lucro` 
            FROM `clientes` c 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_cliente = c.id_cliente 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND pv.`liberado` = '1' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu faço separação do Filtro por Grupo do PA ...
        if(!in_array($campos[$i]['nome'], $vetor_grupo_pa)) {//Não existe no array ...
            $vetor_grupo_pa[]       = $campos[$i]['nome'];
            $vetor_gpa_vs_emp_div[] = $campos[$i]['id_gpa_vs_emp_div'];
        }
        //Os valores aqui nessas variáveis são organizados por "Grupo do PA" ...
        $total_c_desconto[$campos[$i]['nome']]+= $campos[$i]['total_c_desconto'];
        $total_s_desconto[$campos[$i]['nome']]+= $campos[$i]['total_s_desconto'];
        $vetor_total[$campos[$i]['nome']]+= $campos[$i]['total_c_desconto'];

        if($campos[$i]['margem_lucro'] != '-100.00') $vetor_custo_ml_zero[$campos[$i]['nome']]+= $campos[$i]['total_c_desconto'] / (1 + $campos[$i]['margem_lucro'] / 100);
    }

    //Aqui eu disparo um For em cima dos Grupo(s) do(s) PA(s) encontrados na Query anterior ...
    $linhas_grupo_pa = count($vetor_grupo_pa);
    for($i = 0; $i < $linhas_grupo_pa; $i++) {
        //Aqui eu calculo o Desconto Médio do PA por Grupo do PA ...
        $desc_medio_pa  = $total_c_desconto[$vetor_grupo_pa[$i]] / $total_s_desconto[$vetor_grupo_pa[$i]];
        $mlmg_pa        = round(($vetor_total[$vetor_grupo_pa[$i]] / $vetor_custo_ml_zero[$vetor_grupo_pa[$i]] - 1) * 100, 1);

        echo utf8_encode(strtoupper($vetor_grupo_pa[$i])).' ('.$vetor_gpa_vs_emp_div[$i].') <br/>';
        echo '<b>Total c/ Desconto:</b> '.$total_c_desconto[$vetor_grupo_pa[$i]].' / <b>Total s/ Desconto:</b> '.$total_s_desconto[$vetor_grupo_pa[$i]].'<br>';
        echo '<b>Desc M&eacute;dio</b> = '.$desc_medio_pa.'<br>';
        echo '<b>MLMG</b> = '.$mlmg_pa.'<br/><br/>';

        $sql = "UPDATE `gpas_vs_emps_divs` SET `desc_medio_pa` = '$desc_medio_pa', `mlmg` = '$mlmg_pa' WHERE `id_gpa_vs_emp_div` = '$vetor_gpa_vs_emp_div[$i]' LIMIT 1 ";
        bancos::sql($sql);
    }
}else {
?>
<html>
<head>
<title>.:: Relatório de Estoque P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function verificar() {
    var prazo_busca_pedidos = eval('<?=$prazo_busca_pedidos;?>')
    if(prazo_busca_pedidos < 90) {
        var resposta = confirm('A VARIÁVEL DE PRAZO DE BUSCA DOS PEDIDOS É INFERIOR A 90 DIAS !\nDESEJA ALTERAR ESSE VALOR ?')
        if(resposta == true) {
//Aqui eu já redireciono o usuário p/ a tela em que ele pode estar alterando esse valor ...
            window.location = '../../../compras/variaveis/alterar.php?id_variavel=26&tela_aces_por_rel=1'
        }
    }
    ajax('atualizar_grupos.php?passo=1', 'div_atualizar_grupos')
}
</Script>
</head>
<body topmargin='150' onload='verificar()'>
    <div id='div_atualizar_grupos'>
        <table width='98%' border='0' cellspacing ='1' cellpadding='1' align='center'>
            <tr class='confirmacao' align='center'>
                <td>
                    <b>Variável de Prazo de Busca dos Pedidos (Relatório de Estoque / Produção) => </b>
                    <font color='darkblue'>
                        <b><?=$prazo_busca_pedidos;?></b>
                    </font>
                </td>
            </tr>
            <tr class='atencao' align='center'>
                <td>
                    <br/><br/>
                    <img src = '../../../../css/little_loading.gif' width='60' height='60'/>
                    &nbsp;
                    <font size='6' color='brown'>
                        <b>Atualizando Grupo(s) ...</b>
                    </font>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
<?}?>