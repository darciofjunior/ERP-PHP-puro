<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_new.php');
require('../../../../lib/estoque_acabado.php');

segurancas::geral('/erp/albafer/modulo/compras/produtos/cmm/cmm.php', '../../../../');

if($passo == 1) {
    //Aqui eu mudo campo de Estocagem do PI p/ Não Estocável ...
    $sql = "UPDATE `produtos_insumos` SET `estocagem` = 'N' WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    bancos::sql($sql);

    if($_POST[qtde_estoque] != 0) {//Se existir qtde_estoque, gravo a Manipulação que está sendo feita ...
        $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$_POST[id_produto_insumo]', '$_SESSION[id_funcionario]', NULL, '', '".(-1)*$_POST[qtde_estoque]."', '0', 'Manipulação automática zerando o Estoque e mudança de p/ PI não Estocável feita dentro do alterar CMM.', 'M', 'N', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        estoque_ic::atualizar($_POST['id_produto_insumo'], 0);
    }
}
?>
<html>
<head>
<title>.:: Alterar Estoque / Estocagem ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<?
    //Esse campo será utilizado mais abaixo p/ atualização do campo Estocagem do PI ...
    $cmm_ultimos_12_meses = compras_new::consumo_medio_mensal($_POST['id_produto_insumo']);

    $sql = "SELECT ei.`qtde`, pi.`estocagem` 
            FROM `produtos_insumos` pi 
            INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
            WHERE pi.`id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
    <a href="javascript:alterar_estoque_estocagem('<?=$_POST[id_produto_insumo];?>', '<?=$campos[0]['qtde'];?>', '<?=$_POST[indice];?>')">
        <font title='Zerar estoque e marcar "N&atilde;o Estocar" no cadastro' style='cursor:help'>
<?
    //Se o PI atender a condição abaixo, apresento um Link ...
    if($cmm_ultimos_12_meses == 0 && $campos[0]['qtde'] != 0 && $campos[0]['estocagem'] == 'S') {
        echo segurancas::number_format($campos[0]['qtde'], 2, '.');
    }else if($cmm_ultimos_12_meses == 0 && $campos[0]['qtde'] == 0 && $campos[0]['estocagem'] == 'S') {
        echo 'ESTOC&Aacute;VEL';
    }
?>
        </font>
    </a>
</body>
</html>