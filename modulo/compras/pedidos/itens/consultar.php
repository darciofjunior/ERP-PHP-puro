<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../..';

/**********************Controle com o Descontabilizado*********************/
//Significa que o Usuário optou por mudar o Contabilizado do Pedido q foi passado por parâmetro ...
if(isset($_GET['novo_descontabilizado']) && isset($_GET['novo_ativo'])) {
    $sql = "UPDATE `pedidos` SET `programado_descontabilizado` = '$_GET[novo_descontabilizado]', `ativo` = '$_GET[novo_ativo]' WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    bancos::sql($sql);
}
/**************************************************************************/

//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
require('../tela_geral_filtro.php');

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function alterar_descontabilizado(id_pedido, novo_descontabilizado, novo_ativo) {
    window.location = '<?=$PHP_SELF.$parametro;?>&id_pedido='+id_pedido+'&novo_descontabilizado='+novo_descontabilizado+'&novo_ativo='+novo_ativo
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Consultar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Pedido
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Empresa
        </td>
        <td>
            Importa&ccedil;&atilde;o
        </td>
        <td>
            Data de Chegada
        </td>
        <td>
            Descontabilizado
        </td>
        <td>
            Total s/ IPI
        </td>
        <td>
            Valor Pend.
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
        $id_pedido = $campos[$vetor_data_chegada[$i][1]]['id_pedido'];
        $url = 'index.php?id_pedido='.$id_pedido;
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <a href="<?=$url;?>">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
            <?
                if($campos[$i]['status'] == '2') {//Significa a Pedido está Fechado ...
            ?>
                <font title="Pedido Fechado" style="cursor:help">
                    <?=$id_pedido;?>
                </font>
            <?
                }else {//Pedido em Aberto ...
            ?>
                <font title="Pedido em Aberto" style="cursor:help" color="red">
                    <?=$id_pedido;?>
                </font>

            <?
                }
            ?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$vetor_data_chegada[$i][1]]['data_emissao'], 0, 10), '/');?>
        </td>
        <td align='left'>
            <?=$campos[$vetor_data_chegada[$i][1]]['razaosocial'];?>
        </td>
        <td>
        <?
            $tp_nota[1] = 'NF';
            $tp_nota[2] = 'SGD';
            echo $campos[$vetor_data_chegada[$i][1]]['nomefantasia'].' ('.$tp_nota[$campos[$vetor_data_chegada[$i][1]]['tipo_nota']].') ';
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
            $sql = "SELECT i.`nome` 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.`id_importacao` = i.`id_importacao` 
                    WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) > 0) {
                echo $campos_importacao[0]['nome'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            $data_entrega = $campos[$vetor_data_chegada[$i][1]]['prazo_entrega'];
            $data_entrega = data::datetodata($campos[$vetor_data_chegada[$i][1]]['prazo_entrega'], '/');
//Verifica se o fornecedor é internacional
            if($campos[$vetor_data_chegada[$i][1]]['id_pais'] == 31) {
                echo $data_entrega;
            }else {
                $prazo_viagem_navio = $campos[$vetor_data_chegada[$i][1]]['prazo_navio'];
                echo data::adicionar_data_hora($data_entrega, $prazo_viagem_navio);
            }
        ?>
        </td>
        <td>
            <?
                $descontabilizado       = ($campos[$vetor_data_chegada[$i][1]]['programado_descontabilizado'] == 'S') ? 'SIM' : 'NÃO';
                //Aqui eu preparo a variável abaixo na situação de como é que ficaria o Pedido caso o usuário clicasse no link ...
                $novo_descontabilizado  = ($campos[$vetor_data_chegada[$i][1]]['programado_descontabilizado'] == 'S') ? 'N' : 'S';
                $novo_ativo             = ($campos[$vetor_data_chegada[$i][1]]['programado_descontabilizado'] == 'S') ? 1 : 0;
            ?>
            <a href="javascript:alterar_descontabilizado('<?=$id_pedido;?>', '<?=$novo_descontabilizado;?>', '<?=$novo_ativo;?>')" class='link'>
                <font color='black'>
                    <b><?=$descontabilizado;?></b>
                </font>
            </a>
        </td>
        <td align='right'>
            <?=$campos[$i]['simbolo'].number_format($campos[$i]['valor_ped'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$campos[$i]['simbolo'].number_format($campos[$i]['valor_pendencia'], 2, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
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