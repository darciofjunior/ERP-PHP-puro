<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Representante(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cód Rep
        </td>
        <td>
            Nome do Representante
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Cargo
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Tel Cel / Fax
        </td>
        <td>
            Zona de Atuação
        </td>
        <td>
            E-mail
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <a href="javascript:nova_janela('alterar2.php?passo=1&id_representante=<?=$campos[$i]['id_representante'];?>&pop_up=1', 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos[$i]['id_representante'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome_representante'];?>
        </td>
        <td>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
        <?
            //Aqui eu verifico se o repres. também é um funcionário, se for retorna o cargo do Funcionário ...
            $sql = "SELECT c.`cargo` 
                    FROM `representantes_vs_funcionarios` rf 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    WHERE rf.`id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_cargo = bancos::sql($sql);
            //Significa que é funcionário ...
            if(count($campos_cargo) == 1) echo $campos_cargo[0]['cargo'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['fone'];?>
        </td>
        <td>
            <?=$campos[$i]['fax'];?>
        </td>
        <td>
            <?=$campos[$i]['zona_atuacao'];?>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>