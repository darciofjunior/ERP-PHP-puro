<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">FAIXA DE DESCONTO DO CLIENTE EXCLUÍDA COM SUCESSO.</font>';
$mensagem[2] = '<font class="confirmacao">DESCONTO DE CLIENTE DESFEITO A ALTERAÇÃO COM SUCESSO.</font>';
$mensagem[3] = '<font class="confirmacao">DESCONTO DE CLIENTE ALTERADO COM SUCESSO.</font>';

/*********************************************************************************************/
if(!empty($_POST['id_descontos_clientes'])) {//Exclusão das Faixa(s) de Desconto(s) do Cliente
    $sql = "DELETE FROM `descontos_clientes` WHERE `id_descontos_clientes` = '$_POST[id_descontos_clientes]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
/*********************************************************************************************/
?>
<html>
<head>
<title>.:: Faixa de Desconto do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_descontos_clientes) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_descontos_clientes.value = id_descontos_clientes
        document.form.submit()
    }
}

function relatorio_desc_clientes_rep() {
    html5Lightbox.showLightbox(7, 'rel_desc_clientes_rep.php')
}

function desfazer_alterar_desconto_cliente() {
    var mensagem = confirm('DESEJA REALMENTE EXECUTAR ESSA FUNÇÃO ?')
    if(mensagem == true) {
        alert('ESSA ROTINA É UM POUCO DEMORADA !!!\n\nAGUARDE DE 2 A 3 MINUTOS P/ A SUA EXECUÇÃO COMPLETA !')
        window.location = '<?=$PHP_SELF.'?desfazer_alterar_desconto_cliente=1';?>'
    }
}

function alterar_desconto_cliente() {
    var mensagem = confirm('DESEJA REALMENTE EXECUTAR ESSA FUNÇÃO ?')
    if(mensagem == true) {
        alert('ESSA ROTINA É UM POUCO DEMORADA !!!\n\nAGUARDE DE 2 A 3 MINUTOS P/ A SUA EXECUÇÃO COMPLETA !')
        window.location = '<?=$PHP_SELF.'?alterar_desconto_cliente=1';?>'
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cota(s)
        </td>
    </tr>
<?
//Busca de todos os Representantes ativos que estão cadastrados no Sistema ...
    $sql = "SELECT id_representante, nome_fantasia 
            FROM `representantes` 
            WHERE `ativo` = '1' ORDER BY nome_fantasia ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            NÃO HÁ REPRESENTANTE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Representante</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Última Cota Cadastrada</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Data Inicial da Vigência</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Data Final da Vigência</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td align='right'>
        <?
            //Busco a última Cota mensal que foi cadastrada p/ o representante do Loop ...
            $sql = "SELECT SUM(cota_mensal) AS total_cota_mensal, data_inicial_vigencia, data_final_vigencia 
                    FROM `representantes_vs_cotas` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' 
                    GROUP BY data_final_vigencia ORDER BY data_inicial_vigencia DESC LIMIT 1 ";
            $campos_ultima_cota = bancos::sql($sql);
            echo 'R$ '.number_format($campos_ultima_cota[0]['total_cota_mensal'], 2, ',', '.');
        ?>
        </td>
        <td>
            <?=data::datetodata($campos_ultima_cota[0]['data_inicial_vigencia'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos_ultima_cota[0]['data_final_vigencia'], '/');?>
        </td>
        <td>
            <img src='../../../../imagem/menu/incluir.png' border='0' onclick="html5Lightbox.showLightbox(7, 'incluir.php?id_representante=<?=$campos[$i]['id_representante'];?>')" alt='Incluir Nova Cota p/ o Representante' title='Incluir Nova Cota p/ o Representante'>
        </td>
        <td>
            <img src='../../../../imagem/detalhes.png' border='0' onclick="html5Lightbox.showLightbox(7, 'detalhes.php?id_representante=<?=$campos[$i]['id_representante'];?>')" alt='Detalhes de Cotas do Representante' title='Detalhes de Cotas do Representante'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>