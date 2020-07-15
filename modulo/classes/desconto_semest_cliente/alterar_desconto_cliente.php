<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/classes/desconto_semest_cliente/desconto_semestral.php', '../../../');

//Retroagimos 10 dias porque desse jeito garantimos que só estamos pegando Notas Fiscais Despachadas ...
$data_atual = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -10), '-');
        
if($passo == 1) {
    //Controle feito somente na Primeira vez que carregarmos a Tela ...
    if(!isset($indice)) $indice = 0;
?>
<html>
<head>
<title>.:: Alterar Desconto de Cliente no Último Ano de Faturamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body topmargin='150'>
<center>
    <font class='confirmacao'>
        Total já atualizado =>
            <?=($indice).' Registro(s) de um Total de '.$_GET['total_clientes'].'.';?>
        <br/><br/>
        <font size='5' color='brown'>
            <b>Alterando Desconto de Cliente no Último Ano de Faturamento ...</b>
        </font>
    </font>
</center>
</body>
</html>
<?
    //Busca o que foi comprado, ordenando por Cliente no último "Ano" ...
    $sql = "SELECT nfs.`id_cliente` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`ativo` = '1' 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            WHERE nfs.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -365 DAY) 
            AND nfs.`status` = '4' 
            GROUP BY nfs.`id_cliente` 
            ORDER BY nfs.id_cliente ";
    $campos_cliente = bancos::sql($sql, $indice, 1);
    $id_cliente     = $campos_cliente[0]['id_cliente'];

    //Busca o que foi comprado, ordenando por Cliente no último "Ano" ...
    $sql = "SELECT nfsi.`id_produto_acabado`, 
            ((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS volume_compras_ultimo_ano 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            WHERE nfs.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -365 DAY) 
            AND nfs.`status` = '4' 
            AND nfs.`id_cliente` = '$id_cliente' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        //Faço um somatório e agrupamento por Divisão, não agrupo no 1º SQL acima pq fica muito pesado ...
        $sql = "SELECT ged.id_empresa_divisao 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_item_nfs    = bancos::sql($sql);

        //Vou acumulando o Total Comprado do Cliente vs Empresa Divisão na variável abaixo ...
        $vetor_volume_compras[$id_cliente][$campos_item_nfs[0]['id_empresa_divisao']]+= $campos[$i]['volume_compras_ultimo_ano'];

        //Essa variável acumula tudo o que foi Comprado do Cliente no último Ano ...
        $volume_compras_ultimo_ano[$id_cliente]+= $campos[$i]['volume_compras_ultimo_ano'];
    }

    /**************Entro na Tabela 1 que é Faixa de Desconto do Cliente do Grupo (Anual)**************/
    //Busco o Desconto do Cliente na Tabela Faixa de Desconto do Cliente por Grupo (Anual) ...
    $sql = "SELECT desconto_cliente 
            FROM `descontos_clientes` 
            WHERE `valor_semestral`> '".$volume_compras_ultimo_ano[$id_cliente]."' 
            AND `tabela_analise` = '0' ORDER BY desconto_cliente LIMIT 1 ";
    $campos_desconto_grupo      = bancos::sql($sql);
    $desconto_cliente_total     = (count($campos_desconto_grupo) > 0) ? $campos_desconto_grupo[0]['desconto_cliente'] : 0;

    /*************************************************************************************************/
    /*Só irei entrar na Segunda Condição se o Desconto do Cliente pelo Faturamento Total não atingiu o Valor 
    Máximo que seria 20% ...*/

    /**************Entro na Tabela 2 que é Faixa de Desconto do Cliente por Divisão (Anual)**************/
    if($desconto_cliente < 20) {
        //Aqui eu busco todas as Empresas Divisões cadastradas no Sistema ...
        $sql = "SELECT id_empresa_divisao 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ";
        $campos_empresa_divisao = bancos::sql($sql);
        $linhas_empresa_divisao = count($campos_empresa_divisao);
        for($i = 0; $i < $linhas_empresa_divisao; $i++) {
            if($vetor_volume_compras[$id_cliente][$campos_empresa_divisao[$i]['id_empresa_divisao']] > 0) {
                //Busco o Desconto do Cliente na Tabela Faixa de Desconto do Cliente por Divisão (Anual) ...
                $sql = "SELECT desconto_cliente 
                        FROM `descontos_clientes` 
                        WHERE `valor_semestral` > '".$vetor_volume_compras[$id_cliente][$campos_empresa_divisao[$i]['id_empresa_divisao']]."' 
                        AND `tabela_analise` = '1' ORDER BY desconto_cliente LIMIT 1 ";
                $campos_desconto_divisao    = bancos::sql($sql);
                //Fazemos o Desconto assumir o Maior Valor entre o do Geral do Faturamento e o de Divisão ...
                $desconto_cliente_divisao   = max($desconto_cliente_total, $campos_desconto_divisao[0]['desconto_cliente']);
                //Atualizo o Desconto dessa Divisão usando o Maior Valor entre o do Geral do Faturamento e o de Divisão ...
                $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_cliente_divisao' WHERE `id_cliente` = '$id_cliente' AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' ";
                bancos::sql($sql);
            }else {//Atualizo o Desconto dessa Divisão usando o Desconto pelo Total do Faturamento ...
                $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_cliente_total' WHERE `id_cliente` = '$id_cliente' AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' ";
                bancos::sql($sql);
            }
        }
    }else {
        //Atualizo o Novo Desconto do Cliente em todas as suas Divisões ...
        $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_cliente_total' WHERE `id_cliente` = '$id_cliente' ";
        bancos::sql($sql);
    }
 
    if(($indice + 1) == $_GET['total_clientes']) {//Chegou no Fim do Script ...
?>
    <Script Language = 'JavaScript'>
        alert('O SISTEMA FINALIZOU A OPERAÇÃO !!!\n\nDESCONTO DE CLIENTE ALTERADO COM SUCESSO !')
        parent.html5Lightbox.finish()
    </Script>
<?
    }else {//Ainda não Chegou no Fim do Script ...
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_desconto_cliente.php?passo=1&total_clientes=<?=$_GET['total_clientes'];?>&indice=<?=++$indice;?>'
    </Script>
<?
    }
}else {
    //Busco o Número de Clientes que efetuaram Compras no último "Ano" ...
    $sql = "SELECT nfs.`id_cliente` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`ativo` = '1' 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            WHERE nfs.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -365 DAY) 
            AND nfs.`status` = '4' 
            GROUP BY nfs.`id_cliente` 
            ORDER BY nfs.id_cliente ";
    $campos_total_clientes  = bancos::sql($sql);
    $total_clientes         = count($campos_total_clientes);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_desconto_cliente.php?passo=1&total_clientes=<?=$total_clientes;?>'
    </Script>
<?
}
?>