<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

if($passo == 1) {
    //Aqui eu verifico se existe pelo menos 1 OC que esteja em aberta sem Itens ...
    $sql = "SELECT o.`id_oc` 
            FROM `ocs` o 
            WHERE o.`ativo` = '1' AND o.`id_oc` NOT IN 
            (SELECT DISTINCT(o.`id_oc`) 
            FROM `ocs` o 
            INNER JOIN `ocs_itens` oi ON oi.`id_oc` = o.`id_oc` 
            WHERE o.`ativo` = '1') ORDER BY o.`id_oc` LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe um OC em aberto sem Itens, sendo assim eu só reaproveita esta ...
        $id_oc = $campos[0]['id_oc'];
        //Atualiza o Cliente e a Data de Emissão do OC que foi reaproveitado com a Data Atual ...
        $sql = "UPDATE `ocs` SET `id_cliente` = '$_GET[id_cliente]', `data_emissao` = '".date('Y-m-d')."' WHERE `id_oc` = '$id_oc' LIMIT 1 ";
        bancos::sql($sql);
    }else {//Ainda não existe nenhum OC sem Itens, sendo assim eu vou gerar uma OC ...
        $sql = "INSERT INTO `ocs` (`id_oc`, `id_cliente`, `id_funcionario`, `data_emissao`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '$_SESSION[id_funcionario]', '".date('Y-m-d')."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_oc = bancos::id_registro();
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens/itens.php?id_oc=<?=$id_oc;?>'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal    = '../../..';
    $qtde_por_pagina            = 50;
    $veio_incluir_orcamento     = 1;
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../classes/cliente/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Cliente(s) p/ Incluir OC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente) {
    window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cliente(s) p/ Incluir OC
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp Cliente
        </td>
        <td>
            Duplicatas
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" width='10'>
            <a href='#' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')">
            <a href='#' class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `id_conta_receber` 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_contas_receberes = bancos::sql($sql);
            if(count($campos_contas_receberes) == 1) {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>