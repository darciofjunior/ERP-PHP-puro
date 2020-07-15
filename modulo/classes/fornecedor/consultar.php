<?
require('../../../lib/genericas.php');
require '../../../lib/menu/menu.php';
require('../../../lib/data.php');
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
<title>.:: Consultar Fornecedor ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Consultar Fornecedor(es)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Fone 1
        </td>
        <td>
            Fone 2
        </td>
        <td>
            Fax
        </td>
        <td>
            Produtos
        </td>
        <td>
            C/C
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = '../../classes/fornecedor/alterar.php?passo=1&id_fornecedor='.$campos[$i]['id_fornecedor'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone1'].' '.$campos[$i]['fone1'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone2'].' '.$campos[$i]['fone2'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fax'].' '.$campos[$i]['fax'];?>
        </td>
        <td>
            <?=$campos[$i]['produto'];?>
        </td>
        <td align='center'>
        <?
            //Verifico se este Fornecedor possui pelo menos 1 Conta Bancária cadastrada ...
            $sql = "SELECT `id_fornecedor_propriedade` 
                    FROM `fornecedores_propriedades` 
                    WHERE `id_fornecedor` = '".$campos[$i]['id_fornecedor']."' LIMIT 1 ";
            $campos_propriedades = bancos::sql($sql);
            $linhas_propriedades = count($campos_propriedades);
            //Se sim, abro um relatório em PDF com todas as Contas Bancárias disponíveis do Fornecedor ...
            if($linhas_propriedades == 1) {
?>
                <a href='dados_bancarios/pdf/dados_bancarios.php?id_fornecedor=<?=$campos[0]['id_fornecedor'];?>' class='html5lightbox'>
                    <img src = '../../../imagem/cifrao.png' width='30' height='25' alt='Visualizar Dados Bancários' border='0'>
                </a>
<?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
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