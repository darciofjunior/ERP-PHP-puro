<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/vendas.php');

segurancas::geral($PHP_SELF, '../../../../');

if($passo == 1) {
//Aqui eu verifico se existe pelo menos 1 NF Outra do Cliente que esteja em aberto sem Itens ...
    $sql = "SELECT `id_nf_outra` 
            FROM `nfs_outras` 
            WHERE `id_cliente` = '$_GET[id_cliente]' 
            AND `status` = '0' 
            AND id_nf_outra NOT IN 
            (SELECT DISTINCT(nfso.`id_nf_outra`) 
            FROM `nfs_outras` nfso 
            INNER JOIN `nfs_outras_itens` nfsoi ON nfsoi.`id_nf_outra` = nfso.`id_nf_outra` 
            WHERE nfso.`id_cliente` = '$_GET[id_cliente]') 
            ORDER BY `id_nf_outra` LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe uma NF Outra em aberto sem Itens, sendo assim eu só reaproveita esta ...
        $id_nf_outra = $campos[0]['id_nf_outra'];
?>
    <Script Language = 'JavaScript'>
        //Redireciono o sistema p/ a Primeira Nota Fiscal encontrada que esteja em aberto ...
        window.location = 'itens/index.php?id_nf_outra=<?=$id_nf_outra;?>'
    </Script>
<?
    }else {//Ainda não existe nenhuma NF Outra sem Itens, sendo assim eu vou gerar uma nova NF Outra ...
        vendas::transportadoras_padroes($_GET['id_cliente']);//Verifico se o cliente possui as transportadoras padrões cadastradas ...
        
        //Busca uma transportadora qualquer do 'id_cliente' passado por parâmetro para gerar a NF ...
        $sql = "SELECT `id_transportadora` 
                FROM `clientes_vs_transportadoras` 
                WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
        $campos_transportadora = bancos::sql($sql);
        //Gerando NF ...
        $sql = "INSERT INTO `nfs_outras` (`id_nf_outra`, `id_funcionario`, `id_cliente`, `id_empresa`, `id_transportadora`, `finalidade`, `frete_transporte`, `tipo_nfe_nfs`, `data_sys`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_GET[id_cliente]', '4', '".$campos_transportadora[0]['id_transportadora']."', 'R', '2', 'S', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_nf_outra = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('A EMPRESA GERADA PARA ESTA NF FOI COMO SENDO "GRUPO" - CERTIFIQUE-SE DE QUE ESTÁ ESTEJA CORRETA !')
        //A NF acabou de ser confeccionada nesse exato momento ...
        window.location = 'itens/index.php?id_nf_outra=<?=$id_nf_outra;?>'
    </Script>
<?
    }
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
    requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
    $qtde_por_pagina = 50;
    //Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../classes/cliente/tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro
?>
<html>
<head>
<title>.:: Cliente(s) p/ Incluir Nota Fiscal "Outra" ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente) {
    window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cliente(s) p/ Incluir Nota Fiscal "Outra"
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
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="prosseguir('<?=$campos[$i]['id_cliente'];?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="prosseguir('<?=$campos[$i]['id_cliente'];?>')" align='left'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            $sql = "SELECT id_conta_receber 
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
        <td>
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