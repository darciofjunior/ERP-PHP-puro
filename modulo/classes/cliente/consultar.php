<?
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
session_start('funcionarios');

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('tela_geral_filtro.php');
if($linhas > 0) {//Se retornar pelo menos 1 registro
    $sql_imprimir_pesquisa = $sql;//Variável que será utilizada mais abaixo ...
?>
<html>
<head>
<title>.:: Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <?=genericas::order_by('c.razaosocial', 'Razão Social', 'Razão Social', $order_by, '../../../');?>
        </td>
        <td>
            <?=genericas::order_by('c.nomefantasia', 'Nome Fantasia', 'Nome Fantasia', $order_by, '../../../');?>
        </td>
        <td>
            Representante
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Cr
        </td>
        <td>
            Últ Visita
        </td>
        <td>
            Endereço
        </td>
        <td>
            Cidade
        </td>
        <td>
            Duplicatas
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Cidade / UF
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = '/erp/albafer/modulo/classes/cliente/alterar.php?passo=1&id_cliente='.$campos[$i]['id_cliente'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick='<?=$url;?>' width='10' class='html5lightbox'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['cod_cliente'].' - '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
        <?	
            $sql = "SELECT DISTINCT(r.`nome_fantasia`) AS nome_fantasia 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN representantes r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = ".$campos[$i]['id_cliente']." LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=financeiros::controle_credito($campos[$i]['id_cliente']);?>
            </font>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['data_ultima_visita'] != '0000-00-00') echo data::datetodata($campos[$i]['data_ultima_visita'], '/');
        ?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            //Se existir endereço aí sim printa o Número / Complemento ...
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `id_conta_receber` 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_receber = bancos::sql($sql);
            if(count($campos_receber) == 1) {
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
        <td>
            <?=$campos[$i]['cidade'];?> / <?=$campos[$i]['sigla'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
            <input type='button' name='cmd_imprimir_pesquisa' value='Imprimir Pesquisa' title='Imprimir Pesquisa' onclick="nova_janela('../../classes/cliente/imprimir_pesquisa.php?sql=<?=urlencode($sql_imprimir_pesquisa);?>', 'CONSULTAR', 'F')" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>