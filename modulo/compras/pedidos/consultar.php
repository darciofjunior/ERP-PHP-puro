<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');
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
<title>.:: Consultar Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Pedido
        </td>
        <td>
            Data Emissão
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Empresa
        </td>
        <td>
            Importação
        </td>
    </tr>
<?
        $tp_nota = array('', 'NF', 'SGD');

	for($i = 0; $i < count($campos); $i++) {
            $url = 'itens/index.php?id_pedido='.$campos[$i]['id_pedido'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            echo $campos[$vetor_data_chegada[$i][1]]['nomefantasia'].' ('.$tp_nota[$campos[$i]['tipo_nota']].') ';
            if($campos[$i]['tipo_export'] == 'E') {
                echo '<font color="red"><b> (Exp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'I') {
                echo '<font color="red"><b> (Imp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'N') {
                echo '<font color="red"><b> (Nac)</b></font>';
            }
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT i.nome 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
                    WHERE p.id_pedido = '$id_pedido' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) > 0) {
                    echo $campos_importacao[0]['nome'];
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
        <td colspan='6'>
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