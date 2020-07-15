<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Funcionários ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            Telefone
        </td>
        <td>
            Celular
        </td>
        <td>
            Cargo
        </td>
        <td>
            Depto.
        </td>
        <td>
            Chefe
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['ddd_celular'].' '.$campos[$i]['telefone_celular'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
        <?
            //Busca do Nome do Chefe do Funcionário ...
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_superior']."' LIMIT 1 ";
            $campos_chefe = bancos::sql($sql);
            echo $campos_chefe[0]['nome'];
        ?>
        </td>
        <td align='center'>
        <?
            if(substr($campos[$i]['nomefantasia'], 0, 1) == 'A') {
                echo '<font title="ALBAFER" style="cursor:help"><b>A</b></font>';
            }else if(substr($campos[$i]['nomefantasia'], 0, 1) == 'T') {
                echo '<font title="TOOL MASTER" style="cursor:help"><b>T</b></font>';
            }else if(substr($campos[$i]['nomefantasia'], 0, 1) == 'G') {
                echo '<font title="GRUPO" style="cursor:help"><b>G</b></font>';
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_resumido.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>