<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLIENTE EXCLUIDO COM SUCESSO.</font>";

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal    = '../../../../';
$qtde_por_pagina            = 50;
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('../../../classes/cliente/tela_geral_filtro.php');

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Registrar Follow-Up(s) do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Registrar Follow-Up(s) do Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            Nome Fantasia
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
            CNPJ / CPF
        </td>
        <td>
            <img src='../../../../imagem/propriedades.png' width='16' height='16' border='0'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
            &nbsp;
            <img src='../../../../imagem/menu/incluir.png' border='0' title='Registrar Follow-UP' alt='Registrar Follow-UP' onclick="nova_janela('../../../classes/cliente/follow_up.php?identificacao=<?=$campos[$i]['id_cliente'];?>&origem=8', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_cliente'] == 0) {
                echo 'RA';
            }else if($campos[$i]['tipo_cliente'] == 1) {
                echo 'RI';
            }else if($campos[$i]['tipo_cliente'] == 2) {
                echo 'CO';
            }else if($campos[$i]['tipo_cliente'] == 3) {
                echo 'ID';
            }else if($campos[$i]['tipo_cliente'] == 4) {
                echo 'AT';
            }else if($campos[$i]['tipo_cliente'] == 5) {
                echo 'DT';
            }else if($campos[$i]['tipo_cliente'] == 6) {
                echo 'IT';
            }else if($campos[$i]['tipo_cliente'] == 7) {
                echo 'FN';
            }
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td>
            <font color='blue'>
                <?=$credito;?>
            </font>
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
        <td>
        <?
/*Eu criei essa variável $chamar_segurancas, para que a tela de detalhes do cliente seja a mesma tanto 
para a consulta deste através do módulo de Produção, como através de Vendas, porque sendo assim 
a manutenção é única ... */
            $url = "javascript:nova_janela('../../../classes/cliente/detalhes.php?chamar_segurancas=1&id_cliente=".$campos[$i]['id_cliente']."', 'POP', '', '', '', '', '450', '700', 'c', 'c', '', '', 's', 's', '', '', '')";
        ?>
            <a href="<?=$url;?>" title='Detalhes do Cliente' class='link'>
                <img src='../../../../imagem/propriedades.png' border='0' alt='Detalhes do Cliente' title='Detalhes do Cliente' width='16' height='16'>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'follow_up_cliente.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color='blue'><b>RA</b></font> -> Revenda Ativa
 <font color='blue'><b>RI</b></font> -> Revenda Inativa
 <font color='blue'><b>CO</b></font> -> Cooperado
 <font color='blue'><b>ID</b></font> -> Indústria
 <font color='blue'><b>AT</b></font> -> Atacadista
 <font color='blue'><b>DT</b></font> -> Distribuidor
 <font color='blue'><b>IT</b></font> -> Internacional
 <font color='blue'><b>FN</b></font> -> Fornecedor
</pre>
<?}?>