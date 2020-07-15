<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

//Somente as Notas Fiscais que s�o Alba ou Tool ...
$sql = "SELECT COUNT(DISTINCT(nfs.`id_nf`)) AS total_registro 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` AND nfsi.`icms` > '0' 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`operacao` = '1' 
        WHERE nfs.`ativo` = '1' 
        AND nfs.`id_empresa` <> '4' 
        AND nfs.`status` <= '4' 
        AND (nfs.`finalidade` = 'C' or (nfs.`finalidade` = 'R' AND c.`id_uf` <> '1')) 
        AND SUBSTRING(nfs.`data_emissao`, 1, 10) >= '2009-04-01' ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

if($total_registro == $indice) {//P/ n�o ficar em loop infinito ...
//if($indice == 20) {//P/ n�o ficar em loop infinito ...
	exit;
}

$sql = "SELECT distinct(nfs.id_nf), nfs.id_empresa, nfs.suframa, c.id_pais 
        FROM nfs 
        INNER JOIN clientes c on c.id_cliente = nfs.id_cliente 
        INNER JOIN nfs_itens nfsi ON nfsi.id_nf = nfs.id_nf and nfsi.icms > 0 
        INNER JOIN pedidos_vendas_itens pvi on pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
        INNER JOIN produtos_acabados pa on pa.id_produto_acabado = pvi.id_produto_acabado and pa.operacao = '1' 
        WHERE nfs.ativo =1 
        AND nfs.id_empresa <> '4' 
        AND nfs.status <= '4' 
        AND (nfs.`finalidade` = 'C' OR (nfs.`finalidade` = 'R' and c.id_uf <> 1)) 
        AND substring(nfs.data_emissao, 1, 10) >= '2009-04-01' ";
$campos_nfs = bancos::sql($sql, $indice, 1);
$linhas = count($campos_nfs);
for($a = 0; $a < $linhas; $a++) {
    $id_pais            = $campos_nfs[$a]['id_pais'];
    $id_nf              = $campos_nfs[$a]['id_nf'];
    $id_empresa_nota 	= $campos_nfs[$a]['id_empresa'];
    $suframa            = $campos_nfs[$a]['suframa'];
	
//Busca de alguns dados de Cabe�alho da NF e de Cliente ...
    $sql = "SELECT c.cidade, c.tributar_ipi_rev, nfs.`finalidade`, nfs.despesas_acessorias, nfs.valor_frete frete, nfs.status 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
            WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $cidade                 = $campos[0]['cidade'];
    $tributar_ipi_rev       = $campos[0]['tributar_ipi_rev'];
    $finalidade             = $campos[0]['finalidade'];
    $frete                  = $campos[0]['frete'];
    $despesas_acessorias    = $campos[0]['despesas_acessorias'];
/*Busca o Peso do Lote de Todos os Itens em Kg da NF, vou utilizar mais abaixo para 
fazer os c�lculos ...*/
    $sql = "SELECT IF(nfs.status = 6, SUM(nfsi.qtde_devolvida * pa.peso_unitario), SUM(nfsi.qtde * pa.peso_unitario)) AS peso_lote_total_kg 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado AND pa.`operacao` = '1' 
            WHERE nfsi.`id_nf` = '$id_nf' 
            AND nfsi.`icms` > '0' ";
    $campos = bancos::sql($sql);
//Aqui eu arredondo o Peso Total ...
    $peso_lote_total_kg = round($campos[0]['peso_lote_total_kg'], 4);
//Aqui eu fa�o a busca dos Itens da Nota Fiscal passada por par�metro ...
//A opera��o de Fat. do PA sempre ser� Industrial quando o Cliente possuir a marca��o de Tributar IPI REV e for daqui do Brasil ...
    $sql = "SELECT nfsi.id_nfs_item, nfsi.id_classific_fiscal, if(nfs.status = 6, nfsi.qtde_devolvida, nfsi.qtde) qtde_nota, nfsi.valor_unitario, nfsi.valor_unitario_exp, 
            nfsi.ipi as ipi_perc_item_current, nfsi.icms, nfsi.reducao, nfsi.iva, if('$tributar_ipi_rev' = 'S', 0, pa.operacao) as operacao, pa.peso_unitario 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado AND pa.`operacao` = '1' 
            WHERE nfsi.`id_nf` = '$id_nf' 
            AND nfsi.`icms` > '0' ";
    $campos         = bancos::sql($sql);
    $linhas_itens   = count($campos);
    for($i = 0; $i < $linhas_itens; $i++) {
        $total_parcial		= $campos[$i]['qtde_nota']*$campos[$i]['valor_unitario'];
        $total_parcial_exp	= $campos[$i]['qtde_nota']*$campos[$i]['valor_unitario_exp'];
        $ipi_perc_item_current = $campos[$i]['ipi_perc_item_current'];
/*O Peso Total em KG de todos os itens da NF calculado na linha 406, vai me servir 
de base para calcular o Frete + Despesas Acess�rias de forma individual em Kg 
na Tela de Itens da NF ...*/
        $peso_lote_item_current_kg = $campos[$i]['qtde_nota']*$campos[$i]['peso_unitario'];
/*C�lculo p/ achar o "Frete + Desp. Acess�rias" do Item corrente, em cima do 
"Frete Total em R$ + Despesas Acess�rias em R$", achar o "Frete Individual + 
Despesas Acess�rias Individual", achar a sua fatia dentro do Total ...*/
        $frete_desp_acessorias_item_current_rs = (($frete + $despesas_acessorias) * $peso_lote_item_current_kg);
        if($peso_lote_total_kg > 0) {//P/ evitar erro de Divis�o por zero, s� existe a Divis�o se > 0
            $frete_desp_acessorias_item_current_rs/= $peso_lote_total_kg;
        }
        if($ipi_perc_item_current == '0.00') {//Se n�o existir IPI para o Item corrente ...
            $ipi_item_current_rs = 0;
            $ipi_frete_desp_aces_item_current_rs = 0;
        }else {//Existe algum IPI ...
            $ipi_item_current_rs = round(($ipi_perc_item_current / 100) * $total_parcial, 2);//C�lculo o Valor do IPI em R$ ...
            $total_ipi_itens_rs+= $ipi_item_current_rs;
            $ipi_frete_desp_aces_item_current_rs = ($ipi_perc_item_current / 100) * $frete_desp_acessorias_item_current_rs;
        }
//Arrendodamento p/ n�o comprometer os valores abaixo ...	
        $ipi_item_current_para_st_rs = round($ipi_item_current_para_st_rs, 2);
//Acumula o Total de todos os IPI(s) Frete Desp Acess�rias em R$ ...
        $ipi_frete_desp_aces_todos_itens+= $ipi_frete_desp_aces_item_current_rs;
			
        if($campos[$i]['icms'] == '0.00') {//Se n�o existir ICMS para o Item corrente ...
            $icms_frete_desp_aces_item_current_rs = 0;
        }else {
/*Quando for Consumo, tem de somar o Valor de IPI do Frete do Item no c�lculo 
do Icms do Frete ...*/
            if($finalidade == 'C') {
                    $icms_frete_desp_aces_item_current_rs = (($frete_desp_acessorias_item_current_rs + $ipi_frete_desp_aces_item_current_rs) * ($campos[$i]['icms'] / 100));
            }else {//Revenda n�o precisa ...
                    $icms_frete_desp_aces_item_current_rs = (($frete_desp_acessorias_item_current_rs) * ($campos[$i]['icms'] / 100));
            }
//Obs: Se existir redu��o, ent�o eu preciso aplicar est� no ICMS do Frete + DA do Item ...
            if($campos[$i]['reducao'] != '0.00') {
                    $icms_frete_desp_aces_item_current_rs*= (100 - $campos[$i]['reducao']) / 100;
            }
        }
//Acumula o Total de todos os ICMS(s) Frete Desp Acess�rias em R$ ...
        $icms_frete_desp_aces_todos_itens+= $icms_frete_desp_aces_item_current_rs;
			
/*Criei essa vari�vel de 'ipi_ST' pois preciso desse valor para os c�lculos de ST + abaixo. Lembrando que as vari�veis de IPI s�o zeradas 
por causa das Bases de C�lculo ...*/
        $ipi_item_current_para_st_rs = $ipi_item_current_rs + $ipi_frete_desp_aces_item_current_rs;

/*Verifico a Finalidade da NF - sempre que a NF for revenda eu zero o 
valor dessas vari�veis que foi calculado anteriormente, porque ir� influenciar nos resultados 
de bases de c�lculo ...*/
        if($finalidade == 'R') {
            $ipi_item_current_rs = 0;
            $ipi_frete_desp_aces_item_current_rs = 0;
        }
        $total_geral+=$total_parcial;
        $total_geral_exp+=$total_parcial_exp;

        if($campos[$i]['reducao'] != '0.00') {//Quando o item tiver Redu��o B. C.
            if($finalidade == 'C') {
                $icms_item_current_rs = (($total_parcial + $ipi_item_current_rs) * $campos[$i]['icms'] / 100 * (100 - $campos[$i]['reducao']) / 100) + $icms_frete_desp_aces_item_current_rs;
            }else {
                $icms_item_current_rs = ($total_parcial * $campos[$i]['icms'] / 100 * (100 - $campos[$i]['reducao']) / 100) + $icms_frete_desp_aces_item_current_rs;
            }
//Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo quando n�o tiver o IVA ou quando possuir iva + com PA de Op. Fat = 'Ind' ...
            if($campos[$i]['iva'] == 0 || ($campos[$i]['iva'] > 0 && $campos[$i]['operacao'] == 0)) {
//C�lculo com Redu��o � em cima do Total dos Itens e do IPI dos Item ...
                $base_calculo_icms_c_red+= (($frete_desp_acessorias_item_current_rs + $total_parcial) * (100 - $campos[$i]['reducao']) / 100);
/*Somente quando a NF � do Tipo Consumo, que acrescenta o IPI do Frete R$ + 
IPI do Item em R$ ...*/
                if($finalidade == 'C') $base_calculo_icms_c_red+= (($ipi_frete_desp_aces_item_current_rs + $ipi_item_current_rs) * (100 - $campos[$i]['reducao']) / 100);
            }
        }else {
            if($finalidade == 'C') {
                $icms_item_current_rs = (($total_parcial + $ipi_item_current_rs) * $campos[$i]['icms'] / 100 + $icms_frete_desp_aces_item_current_rs);
            }else {
                $icms_item_current_rs = ($total_parcial * $campos[$i]['icms'] / 100 + $icms_frete_desp_aces_item_current_rs);
            }
//Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo quando n�o tiver o IVA ou quando possuir iva + com PA de Op. Fat = 'Ind' ...
            if($campos[$i]['iva'] == 0 || ($campos[$i]['iva'] > 0 && $campos[$i]['operacao'] == 0)) {
/*Aqui eu verifico a Classifica��o Fiscal do Produto Corrente, se no caso for id_4 ou id_6 
que equivale a Classifa��o Fiscal 82.07.90.00 ou 90.17.20.00, ir� abastecer a vari�vel 
$base_calculo_icms_bits_bedames_riscador ...*/
                if($campos[$i]['id_classific_fiscal'] == 4 || $campos[$i]['id_classific_fiscal'] == 6) {//82.07.90.00 ou 90.17.20.00 ...
                    $base_calculo_icms_bits_bedames_riscador+= $total_parcial + $frete_desp_acessorias_item_current_rs + $ipi_frete_desp_aces_item_current_rs + $ipi_item_current_rs;
/*Esse desvio ser� muito raro de acontecer, pois n�o ser� mais feito uma Nota de Conserto 
junto com uma Nota Fiscal de Venda, s� aconteceu no in�cio p/ a Nota 4333 da Albaf�r ...*/
                }else if($campos[$i]['id_classific_fiscal'] == 14) {//00.00.00.00 - Isento de M�o de Obra ...
                    echo '<font color="red"><b>Est� Correto ? </b></font>'.$isento+= $total_parcial + $frete_desp_acessorias_item_current_rs + $ipi_frete_desp_aces_item_current_rs + $ipi_item_current_rs;
                }else {//Outra Classifica��o fiscal ...
                    $base_calculo_icms_s_red+= $total_parcial + $frete_desp_acessorias_item_current_rs + $ipi_frete_desp_aces_item_current_rs + $ipi_item_current_rs;
                }
            }
        }
//Atualizando o Item de Nota Fiscal com o ICMS a Creditar ...
        $sql = "UPDATE `nfs_itens` SET `icms_creditar_rs` = '$icms_item_current_rs' WHERE `id_nfs_item` = '".$campos[$i]['id_nfs_item']."' LIMIT 1 ";
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
    window.location = 'script_nfs_icms_creditar.php?indice=<?=++$indice;?>'
</Script>