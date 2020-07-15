<?
// Substituir o id_empresa que está fixo para uma variável
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

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
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align='center'>
        <td colspan='9'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            CPF
        </td>
        <td>
            Telefone
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
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
        $url = 'detalhes.php?id_funcionario_loop='.$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
        </td>
        <td>
                <?=$campos[$i]['nome'];?>
        </td>
        <td align="center">
                <?=substr($campos[$i]['cpf'], 0, 3).'.'.substr($campos[$i]['cpf'], 3, 3).'.'.substr($campos[$i]['cpf'], 6, 3).'-'.substr($campos[$i]['cpf'], 9, 2);?>
        </td>
        <td align="center">
                <?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>
        </td>
        <td>
                <?=$campos[$i]['cargo'];?>
        </td>
        <td>
                <?=$campos[$i]['departamento'];?>
        </td>
        <td>
        <?
            //Só o busco o nome de Chefe se o "Funcionário do Loop" possuir ...
            if(!empty($campos[$i]['id_funcionario_superior'])) {
                $sql = "SELECT nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_superior']."' LIMIT 1 ";
                $campos_funcionario = bancos::sql($sql);
                echo $campos_funcionario[0]['nome'];
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>