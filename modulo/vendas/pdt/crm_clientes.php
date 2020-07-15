<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
?>
<html>
<head>
<title>.:: CRM de Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    
    if(document.form.cmb_representante.value == '') {
        alert('NÃO FOI SELECIONADO NENHUM REPRESENTANTE !!!\n\nSENDO ASSIM O RELATÓRIO SERÁ UM POUCO MAIS DEMORADO PARA PROCESSAR !')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            CRM de Clientes <p/> 
            <font color='yellow'>
                <b>Representante: </b>
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
            &nbsp;-&nbsp;
            <font color='yellow'>
                Estado:
            </font>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql, $_POST['cmb_uf']);
            ?>
            </select>
        <?
            if(empty($txt_data_inicial)) {
                $txt_data_inicial   = '01/'.date('m/Y');
                $txt_data_final     = '30/'.date('m/Y');
            }
        ?>
            <p/>
            Data Inicial:
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            Data Final:
            <input type='text' name='txt_data_final' value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
    if($_POST['cmd_consultar'] != '') {
        //Se a combo UF estiver preenchida, então eu só trago Clientes da UF selecionada ...
        if(!empty($_POST['cmb_uf'])) $condicao_filtro = " AND c.`id_uf` LIKE '$_POST[cmb_uf]' ";
        
        //Tratamento com os campos de Data p/ não Furar os SQLs mais abaixo ...
        $data_inicial   = data::datatodate($txt_data_inicial, '-');
        $data_final     = data::datatodate($txt_data_final, '-');
        
        /**********************************************************************/
        /**********************Selecionou um Representante*********************/
        /**********************************************************************/
        if(!empty($_POST['cmb_representante']) || !empty($representante)) {
            $id_representante   = (!empty($_POST['cmb_representante'])) ? $_POST[cmb_representante] : $representante;
            
            //Busco toda a carteira de clientes do "Representante" selecionado ou que foi passado por parâmetro / "Estado" selecionado ...
            $sql = "SELECT cr.`id_cliente` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` $condicao_filtro 
                    WHERE cr.`id_representante` LIKE '$id_representante' 
                    GROUP BY cr.`id_cliente` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas == 0) {//Não encontrou nenhum Cliente ...
?>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            REPRESENTANTE SEM CLIENTE(S).
        </td>
    </tr>
<?
                exit;
            }else {
                $vetor_data     = data::diferenca_data($data_inicial, $data_final);
                $qtde_meses     = round($vetor_data[0] / 30, 1);

                //Busco a última Cota que está em vigência para o determinado representante selecionado ou que foi passado por parâmetro ...
                $sql = "SELECT SUM(rc.cota_mensal) AS cota_representante 
                        FROM `representantes` r 
                        INNER JOIN `representantes_vs_cotas` rc ON rc.`id_representante` = r.`id_representante` AND rc.data_final_vigencia = '0000-00-00' 
                        WHERE r.`id_representante` = '$id_representante' 
                        AND r.`ativo` = '1' 
                        GROUP BY r.`id_representante` ";
                $campos_cota            = bancos::sql($sql);
                $cota_representante     = $campos_cota[0]['cota_representante'];
                $cota_dentro_do_periodo = $cota_representante * $qtde_meses;
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cota Mensal => 
            <font color='yellow'>
                R$ <?=number_format($cota_representante, 2, ',', '.');?>
            </font>
            &nbsp;-&nbsp;
            Qtde de Meses => 
            <font color='yellow'>
                <?=number_format($qtde_meses, 1, ',', '.');?>
            </font>
            &nbsp;-&nbsp;
            Cota dentro do Período => 
            <font color='yellow'>
                R$ <?=number_format($cota_dentro_do_periodo, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
            }
        /**********************************************************************/
        }else {
            //Busco todos os clientes que não sejam do Tipo Tele MKT do "Estado" selecionado ...
            $sql = "SELECT `id_cliente` 
                    FROM `clientes` 
                    WHERE `id_cliente_tipo` NOT IN (11, 12, 14) 
                    $condicao_filtro 
                    GROUP BY `id_cliente` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
        }
        /**********************************************************************/
        
        for($i = 0; $i < $linhas; $i++) $id_clientes.= $campos[$i]['id_cliente'].', ';
        $id_clientes = substr($id_clientes, 0, strlen($id_clientes) - 2);
        
        //Busco todos os Orçamentos que foram feitos em cima dos clientes do Representante e dentro do Período que foi digitado pelo Usuário ...
        $sql = "SELECT ov.`id_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, SUM(ovi.`qtde` * ovi.`preco_liq_final`) AS valor_total_dos_produtos 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                WHERE ov.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND ov.`id_cliente` IN ($id_clientes) 
                GROUP BY cliente ORDER BY cliente ";
        $campos_orcamentos_vendas = bancos::sql($sql);
        $linhas_orcamentos_vendas = count($campos_orcamentos_vendas);
        if($linhas_orcamentos_vendas > 0) {
?>
</table>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhadestaque' align='center'>
        <td>
            Orçamento(s)
        </td>
        <td>
            Valor Total dos Produtos
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_orcamentos_vendas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_orcamentos_vendas[$i]['cliente'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_orcamentos_vendas[$i]['valor_total_dos_produtos'], 2, ',', '.');?>
        </td>
    </tr>
<?
                $id_clientes_atendidos.= $campos_orcamentos_vendas[$i]['id_cliente'].', ';
                $valor_total_orcamentos+= $campos_orcamentos_vendas[$i]['valor_total_dos_produtos'];
            }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            R$ <?=number_format($valor_total_orcamentos, 2, ',', '.');?>
            &nbsp;
            <font color='yellow'>
                (<?=number_format($valor_total_orcamentos / $cota_dentro_do_periodo * 100, 2, ',', '.').'% da Meta)';?>
            </font>
        </td>
    </tr>
<?
        }
       
        //Busco todos os Pedidos que foram feitos em cima dos clientes do Representante e dentro do Período que foi digitado pelo Usuário ...
        $sql = "SELECT pv.`id_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_total_dos_produtos 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` $condicao_filtro 
                WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.`id_cliente` IN ($id_clientes) 
                GROUP BY cliente ORDER BY cliente ";
        $campos_pedidos_vendas = bancos::sql($sql);
        $linhas_pedidos_vendas = count($campos_pedidos_vendas);
        if($linhas_pedidos_vendas > 0) {
?>
</table>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhadestaque' align='center'>
        <td>
            Pedido(s)
        </td>
        <td>
            Valor Total dos Produtos
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_pedidos_vendas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_pedidos_vendas[$i]['cliente'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_pedidos_vendas[$i]['valor_total_dos_produtos'], 2, ',', '.');?>
        </td>
    </tr>
<?
                $id_clientes_atendidos.= $campos_pedidos_vendas[$i]['id_cliente'].', ';
                $valor_total_pedidos+= $campos_orcamentos_vendas[$i]['valor_total_dos_produtos'];
            }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            R$ <?=number_format($valor_total_pedidos, 2, ',', '.');?>
            &nbsp;
            <font color='yellow'>
                (<?=number_format($valor_total_pedidos / $cota_dentro_do_periodo * 100, 2, ',', '.').'% da Meta)';?>
            </font>
        </td>
    </tr>
<?
        }
        $id_clientes_atendidos = substr($id_clientes_atendidos, 0, strlen($id_clientes_atendidos) - 2);
       
        /**********************************************************************/
        /**********************Selecionou um Representante*********************/
        /**********************************************************************/
        if(!empty($_POST['cmb_representante']) || !empty($representante)) {
            /*Dessa vez, eu busco apenas os clientes que não foram atendidos de toda a carteira do Representante e dentro do Período que foi 
            digitado pelo Usuário ...*/
            $sql = "SELECT `id_cliente` 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_representante` = '$id_representante' 
                    AND `id_cliente` NOT IN ($id_clientes_atendidos) ";
        }else {
            //Dessa vez, eu busco apenas os clientes que não foram atendidos e dentro do Período que foi digitado pelo Usuário ...
            $sql = "SELECT `id_cliente` 
                    FROM `clientes` 
                    WHERE `id_cliente_tipo` NOT IN (11, 12, 14) 
                    AND `id_cliente` NOT IN ($id_clientes_atendidos) ";
        }
        $campos_clientes_nao_atendidos = bancos::sql($sql);
        $linhas_clientes_nao_atendidos = count($campos_clientes_nao_atendidos);
        
        for($i = 0; $i < $linhas_clientes_nao_atendidos; $i++) $id_clientes_nao_atendidos.= $campos_clientes_nao_atendidos[$i]['id_cliente'].', ';
        $id_clientes_nao_atendidos = substr($id_clientes_nao_atendidos, 0, strlen($id_clientes_nao_atendidos) - 2);
        
        $sql = "SELECT c.`id_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`credito`, c.`cidade`, c.`ddd_com`, 
                c.`telcom`, c.`email`, ct.`tipo`, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS volume_compras, ufs.`sigla` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                WHERE c.`id_cliente` IN ($id_clientes_nao_atendidos) 
                $condicao_filtro 
                GROUP BY pv.`id_cliente` ORDER BY volume_compras DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
?>
</table>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Cliente(s) s/ atendimento - Total de 
            <font color='yellow'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Tipo de Cliente
        </td>
        <td>
            Crédito
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Telefone
        </td>
        <td>
            Email
        </td>
        <td>
            Último Orçamento
        </td>
        <td>
            Último Pedido
        </td>
        <td>
            Última NF
        </td>
        <td>
            Volume de Compras
        </td>
        <td>
            Volume de Compras nos últimos 5 anos
        </td>
        <td>
            Total à Receber em Atraso
        </td>
        <td>
            Tempo s/ atendimento
        </td>
        <td>
            Representações Anteriores
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href = '../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[$i]['id_cliente'];?>&nao_exibir_menu=1' class='html5lightbox'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
            <?=$campos[$i]['credito'];?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=' ('.$campos[$i]['ddd_com'].') '.$campos[$i]['telcom'];?>
        </td>
        <td align='left'>
            <a href='mailto:<?=$campos[$i]['email'];?>'>
                <?=$campos[$i]['email'];?>
            </a>
        </td>
        <td>
        <?
            //Busco o Último Orçamento que este cliente realizou conosco ...
            $sql = "SELECT ov.`id_orcamento_venda`, DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, SUM(ovi.`qtde` * ovi.`preco_liq_final`) AS valor_total_dos_produtos 
                    FROM `orcamentos_vendas` ov 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
                    WHERE ov.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    GROUP BY ovi.`id_orcamento_venda` 
                    ORDER BY ov.`id_orcamento_venda` DESC LIMIT 1 ";
            $campos_orcamento_venda = bancos::sql($sql);
        ?>
            <a href = '../pedidos/itens/detalhes_orcamento.php?id_orcamento_venda=<?=$campos_orcamento_venda[0]['id_orcamento_venda'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_orcamento_venda[0]['id_orcamento_venda'];?>
            </a>
        <?
            echo ' em '.$campos_orcamento_venda[0]['data_emissao'].' - R$ '.number_format($campos_orcamento_venda[0]['valor_total_dos_produtos'], 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Busco o Último Pedido que este cliente realizou conosco ...
            $sql = "SELECT pv.`id_pedido_venda`, DATE_FORMAT(pv.`data_emissao`, '%d/%m/%Y') AS data_emissao, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_total_dos_produtos 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    GROUP BY pvi.`id_pedido_venda` 
                    ORDER BY pv.`id_pedido_venda` DESC LIMIT 1 ";
            $campos_pedido_venda = bancos::sql($sql);
        ?>
            <a href = '../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_pedido_venda[0]['id_pedido_venda'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_pedido_venda[0]['id_pedido_venda'];?>
            </a>
        <?    
            echo ' em '.$campos_pedido_venda[0]['data_emissao'].' - R$ '.number_format($campos_pedido_venda[0]['valor_total_dos_produtos'], 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Busco a Último NF que este cliente realizou conosco ...
            $sql = "SELECT nfs.`id_nf`, nnn.`numero_nf`, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
                    SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS valor_total_dos_produtos 
                    FROM `nfs` 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                    WHERE nfs.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    GROUP BY nfsi.`id_nf` 
                    ORDER BY nfs.`id_nf` DESC LIMIT 1 ";
            $campos_nfs = bancos::sql($sql);
        ?>
            <a href = '../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nfs[0]['id_nf'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_nfs[0]['numero_nf'];?>
            </a>
        <?
            echo ' em '.$campos_nfs[0]['data_emissao'].' - R$ '.number_format($campos_nfs[0]['valor_total_dos_produtos'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <a href = '../../classes/cliente/volume_de_compras.php?id_cliente=<?=$campos[$i]['id_cliente'];?>' class='html5lightbox'>
                <?=number_format($campos[$i]['volume_compras'], 2, ',', '.');?>
            </a>
        </td>
        <td align='right'>
            
            <?
                $sql = "SELECT SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS volume_compras_ultimos_5anos 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                        WHERE pv.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                        AND YEAR(pv.`data_emissao`) >= '".(date('Y') - 5)."' ";
                $campos_volume_compras_ultimos_5anos = bancos::sql($sql);
                echo 'R$ '.number_format($campos_volume_compras_ultimos_5anos[0]['volume_compras_ultimos_5anos'], 2, ',', '.');
            ?>
            </a>
        </td>
        <td align='right'>
            <a href = '../../financeiro/cadastro/credito_cliente/detalhes.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&pop_up=1' class='html5lightbox'>
            <?
                //Busco tudo o que o Cliente está devendo para nós ...
                $sql = "SELECT `id_conta_receber` 
                        FROM `contas_receberes` 
                        WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                        AND `data_vencimento_alterada` <= '".date('Y-m-d')."' 
                        AND `status` < '2' ";
                $campos_contas_receber = bancos::sql($sql);
                $linhas_contas_receber = count($campos_contas_receber);
                if($linhas_contas_receber > 0) {
                    for($j = 0; $j < $linhas_contas_receber; $j++) {
                        $calculos_conta_receber = financeiros::calculos_conta_receber($campos_contas_receber[$j]['id_conta_receber']);
                        $contas_receber+= $calculos_conta_receber['valor_reajustado'];
                    }
                    echo 'R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');
                }
            ?>
            </a>
        </td>
        <td>
        <?
            $vetor_data = data::diferenca_data(data::datatodate($campos_orcamento_venda[0]['data_emissao'], '-'), date('Y-m-d'));
            echo $vetor_data[0].' dias';
        ?>
        </td>
        <td align='left'>
        <?
                /*Busco todas as Representações anteriores do Cliente do Loop ...
                $sql = "SELECT DISTINCT(`observacao`) 
                        FROM `follow_ups`
                        WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                        AND `observacao` LIKE '%rep%alterado%' ";
                $campos_follow_ups = bancos::sql($sql);
                $linhas_follow_ups = count($campos_follow_ups);
                if($linhas_follow_ups > 0) {
                    for($j = 0; $j < $linhas_follow_ups; $j++) echo '<p/><p/><p/>'.$campos_follow_ups[$j]['observacao'];
                }*/
        ?>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = 'estrategia_vendas.php?representante=<?=$id_representante;?>&pop_up=<?=$pop_up;?>'">
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>