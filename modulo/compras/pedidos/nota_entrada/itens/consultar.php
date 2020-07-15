<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../../');

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
require('../tela_geral_filtro.php');

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Nota(s) Fiscal(s) de Entrada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Importa&ccedil;&atilde;o
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Data Ent.
        </td>
        <td>
            Empresa
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
    //Esse vetor será utilizado mais abaixo dentro do Loop ...
    $vetor_tipo_nota = array('', 'NF', 'SGD');

    for ($i = 0; $i < $linhas; $i++) {
        $url = 'index.php?id_nfe='.$campos[$i]['id_nfe'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <a href="<?=$url;?>">
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
            <?
                if($campos[$i]['situacao'] == '2') {//Significa a Nota Fiscal está Fechada ...
            ?>
                <font title='Nota Fiscal Fechada' style='cursor:help'>
                    <?=$campos[$i]['num_nota'];?>
                </font>
            <?
                }else {//Nota Fiscal em Aberto ...
            ?>
                <font title='Nota Fiscal em Aberto' style='cursor:help' color='red'>
                    <?=$campos[$i]['num_nota'];?>
                </font>

            <?
                }
            ?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Caixa de Compras ...
            if($campos[$i]['pago_pelo_caixa_compras'] == 'S') echo '<font color="blue"><b> (CAIXA DE COMPRAS)</b></font>';
            //Só mostramos quando Ignorar Impostos no Financiamento ...
            if($campos[$i]['ignorar_impostos_financiamento'] == 'S') echo '<font color="darkred" title="Ignorar Impostos no Financiamento" style="cursor:help"><b> (IIF)</b></font>';
            //Nota já liberada ...
            if($campos[$i]['situacao'] == 2) echo '<font color="red"><b> (Liberada)</b></font>';
            //Nota Importada no Financeiro ...
            if($campos[$i]['importado_financeiro'] == 'S') echo '<font color="darkblue" title="Importado no Financeiro" style="cursor:help"><b> (Import. Financ.)</b></font>';
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT i.nome 
                    FROM `nfe` 
                    INNER JOIN `importacoes` i ON i.id_importacao = nfe.id_importacao 
                    WHERE nfe.id_nfe = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) > 0) {
                echo $campos_importacao[0]['nome'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td>
        <?
            if(!is_null($campos[$i]['data_entrega'])) echo data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'].' ('.$vetor_tipo_nota[$campos[$i]['tipo']].') ';?>
        </td>
        <td align='left'>
            
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