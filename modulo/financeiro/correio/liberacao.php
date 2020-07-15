<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
require('../../../lib/variaveis/intermodular.php');

segurancas::geral('/erp/albafer/modulo/financeiro/correio/correio.php', '../../../');

if(!empty($_POST['txt_observacao_conferencia'])) {
    if($_POST['cmb_tipo_documento'] == 'NF') {//NF ...
        $sql = "UPDATE `nfs` SET `valor_frete_pago` = '$_POST[txt_valor_frete_pago]', `id_funcionario_conferencia_correio` = '$_SESSION[id_funcionario]', `observacao_conferencia_correio` = '$_POST[txt_observacao_conferencia]', `data_sys_conferencia_correio` = '".date('Y-m-d H:i:s')."' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
        
        $sql = "SELECT nfs.`valor_frete`, CONCAT(c.`nomefantasia`, c.`razaosocial`) AS cliente 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.id_cliente = nfs.`id_cliente` 
                WHERE nfs.`id_nf` = '$_POST[id_nf]' 
                AND nfs.`ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        
        $numero_do_documento = 'NF N.º '.faturamentos::buscar_numero_nf($_POST['id_nf'], 'S');
    }else {//Vale ...
       $sql = "UPDATE `vales_vendas` SET `valor_frete_pago` = '$_POST[txt_valor_frete_pago]', `id_funcionario_conferencia_correio` = '$_SESSION[id_funcionario]', `observacao_conferencia_correio` = '$_POST[txt_observacao_conferencia]', `data_sys_conferencia_correio` = '".date('Y-m-d H:i:s')."' WHERE `id_vale_venda` = '$_POST[id_vale_venda]' LIMIT 1 ";
       bancos::sql($sql);
     
       $sql = "SELECT vv.`valor_frete`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')') AS cliente 
                FROM `vales_vendas` vv 
                INNER JOIN `vales_vendas_itens` vvi ON vvi.`id_vale_venda` = vv.`id_vale_venda` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = vvi.`id_pedido_venda_item` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE vv.`id_vale_venda` = '$_POST[id_vale_venda]' LIMIT 1 ";
       $campos = bancos::sql($sql);
       
       $numero_do_documento = 'Vale N.º '.$_POST[id_vale_venda];
    }
    /**************************************************************************/
    /***************************Controle para E-mail***************************/
    /**************************************************************************/
    if($_POST['valor_frete_pago'] > (0.1 * $campos[0]['valor_frete']) || $_POST['valor_frete_pago'] < (0.9 * $campos[0]['valor_frete'])) {
        $destino = $frete_pago_10_perc_mais_menos_dif_valor_frete_doc;
        $assunto = 'Frete do Documento com +/- 10% de Diferença p/ o Frete Pago à Transportadora - Cliente "'.$campos[0]['cliente'].'" - '.$numero_do_documento;

        $conteudo_email = 'O VALOR DO FRETE PAGO ESTÁ COM 10% A MAIS OU A MENOS DE DIFERENÇA DO VALOR DO FRETE DO DOCUMENTO: ';
        $conteudo_email.= '<br/>Valor do Frete no Documento (Debitado do Cliente): R$ '.number_format($campos[0]['valor_frete'], 2, ',', '.');
        $conteudo_email.= '<br/>Valor do Frete que Pagamos à Transportadora: R$ '.number_format($_POST['txt_valor_frete_pago'], 2, ',', '.');
        //$conteudo_email.= "<br/>Favor verificar junto p/ correção - idéia solta ";

        $conteudo_email.= '<p/>'.date('d/m/Y').' às '.date('H:i:s');
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $conteudo_email);
    }
    /**************************************************************************/
    
    if($_POST['cmb_tipo_documento'] == 'NF') {//NF ...
        $mensagem = 'NF CONFERIDA / LIBERADA COM SUCESSO !';
    }else {//Vale ...
        $mensagem = 'VALE DE VENDA CONFERIDO / LIBERADO COM SUCESSO !';
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem;?>')
        parent.location = 'correio.php<?=$parametro;?>'
    </Script>
<?
}

if(!empty($_GET['id_nf']) == 'NF') {//NF ...
    $sql = "SELECT nfs.`id_empresa`, nfs.`valor_frete`, nfs.`valor_frete_pago`, nfs.`data_emissao`, 
            nfs.`data_saida_entrada`, nfs.`peso_bruto_balanca` AS peso_bruto, 
            nfs.`observacao_conferencia_correio`, c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, 
            c.`cidade`, t.`nome` AS transportadora 
            FROM `nfs` 
            INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.id_nf = '$_GET[id_nf]' 
            AND nfs.ativo = '1' LIMIT 1 ";
    //Função para o cálculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor contém frete+impostos e etc.
    $calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
}else {//Vale ...
    $sql = "SELECT SUM(vvi.`qtde` * pvi.`preco_liq_final`) AS valor_total_vale, vv.`id_vale_venda`, 
            vv.`peso_bruto`, vv.`valor_frete`, vv.`valor_frete_pago`, vv.`observacao_conferencia_correio`, 
            SUBSTRING(vv.`data_sys`, 1, 10) AS data_emissao, c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, 
            c.`cidade`, pv.`id_empresa`, t.`nome` AS transportadora 
            FROM `vales_vendas` vv 
            INNER JOIN `vales_vendas_itens` vvi ON vvi.`id_vale_venda` = vv.`id_vale_venda` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = vvi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = vv.`id_transportadora` 
            WHERE vv.`id_vale_venda` = '$_GET[id_vale_venda]' LIMIT 1 ";
}
$campos                 = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Conferir / Liberar Correio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var id_funcionario      = eval('<?=$_SESSION['id_funcionario'];?>')
    var valor_frete         = eval('<?=$campos[0]['valor_frete'];?>')
    var peso_bruto          = eval('<?=$campos[0]['peso_bruto'];?>')
/*Se o Funcionário for diferente do Roberto 62, Dona Sandra 66 e Dárcio "98" porque Programa e a Mercadoria 
não foi em vale "Peso Bruto Balança > R$ 0,00", então eu faço a conferência no que tange ao valor do Frete*/
    if(id_funcionario != 62 && id_funcionario != 66 && id_funcionario != 98) {
        if(valor_frete == 0 && peso_bruto > 0) {
            alert('NÃO PODE SER ENVIADO VIA CORREIO !\n ESSA MERCADORIA NÃO FOI ENVIADA EM VALE E O VALOR DO FRETE = R$ 0,00 !')
            document.form.txt_observacao_conferencia.focus()
            return false
        }
    }
//Valor do Frete Pago ...
    if(document.form.txt_valor_frete_pago.value != '') {
        if(!texto('form', 'txt_valor_frete_pago', '1', '0123456789,.', 'VALOR DO FRETE PAGO', '2')) {
            return false
        }
/*Aqui eu faço uma comparação com o Valor Frete que acabou de ser digitado pelo Usuário em relação 
ao valor Frete do Banco de Dados ...
         
10% à mais ou à menos: significa que estamos tendo uma discrepância acima do normal ...*/
        var valor_frete_pago = eval(strtofloat(document.form.txt_valor_frete_pago.value))

        if(valor_frete_pago > (0.1 * valor_frete) || valor_frete_pago < (0.9 * valor_frete)) {
            var resposta = confirm('O VALOR DO FRETE PAGO ESTÁ COM 10% A MAIS OU A MENOS DE DIFERENÇA DO VALOR DO FRETE DO DOCUMENTO !!!\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
    }
//Observação / Conferência ...
    if(document.form.txt_observacao_conferencia.value == '') {
        alert('DIGITE A OBSERVAÇÃO / CONFERÊNCIA !')
        document.form.txt_observacao_conferencia.focus()
        document.form.txt_observacao_conferencia.select()
        return false
    }
    limpeza_moeda('form', 'txt_valor_frete_pago, ')
//Desabilito o Botão para o usuário não ficar incluindo várias vezes a mesma Nota no BD
    document.form.cmd_salvar.disabled = true
}
</Script>
<body onload='document.form.txt_valor_frete_pago.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--******************Controles de Tela*********************-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='id_vale_venda' value='<?=$_GET['id_vale_venda'];?>'>
<input type='hidden' name='cmb_tipo_documento' value='<?=$_GET['cmb_tipo_documento'];?>'>
<!--********************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Conferir / Liberar Correio - 
            <font color='yellow'>
            <?
                if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                    echo 'NF(S)';
                }else {//Vale ...
                    echo 'VALE DE VENDA';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                echo '<b>N.º NF(s):</b>';
            }else {//Vale ...
               echo '<b>N.º do Vale de Venda:</b>';
            }
        ?>
        </td>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                echo faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');
            }else {//Vale ...
                echo $_GET['id_vale_venda'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Emissão:</b>
        </td>
        <td>
        <?
            if($campos[0]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[0]['data_emissao'], '/');
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Saída:</b>
        </td>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                if($campos[0]['data_saida_entrada'] != '0000-00-00') echo data::datetodata($campos[0]['data_saida_entrada'], '/');
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
            <font title='Nome Fantasia: <?=$campos[0]['nomefantasia'];?>' style='cursor:help'>
                <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <?=$campos[0]['cidade'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>UF:</b>
        </td>
        <td>
        <?
            //Se existir UF para o Cliente ...
            if($campos[0]['id_uf'] > 0) {
                $sql = "SELECT sigla 
                        FROM `ufs` 
                        WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
                $campos_ufs = bancos::sql($sql);
                echo $campos_ufs[0]['sigla'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
        <?
//Busca da Empresa da NF ...
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = '".$campos[0]['id_empresa']."' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            echo $campos_empresa[0]['nomefantasia'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Bruto:</b>
        </td>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                echo number_format($campos[0]['peso_bruto'], 2, ',', '.');
//Se o Valor do Frete = 0 e o Peso Bruto = 0, então significa que a Mercadoria foi no Vale ...
                if($campos[0]['valor_frete'] == 0 && $campos[0]['peso_bruto'] == 0) echo '<font color="red"><b> (VALE)</b></font>';
            }else {//Vale ...
                echo number_format($campos[0]['peso_bruto'], 2, ',', '.');
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                echo '<b>Valor Total da Nota:</b>';
            }else {//Vale ...
                echo '<b>Valor Total do Vale:</b>';
            }
        ?>
        </td>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                echo number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
            }else {//Vale ...
                echo number_format($campos[0]['valor_total_vale'], 2, ',', '.');
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
        ?>
            <font title='Valor do Frete na NF c/ Impostos' style='cursor:help'>
                <b>V.F. na NF c/ I:</b>
            </font>
        <?
            }else {//Vale ...
                echo 'Valor do Frete no Vale:';
            }
        ?>
        </td>
        <td>
            <font color='red'>
                <?=number_format($campos[0]['valor_frete'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
        ?>
            <font title='Valor do Frete na NF s/ Impostos' color='red' style='cursor:help'>
                V.F. na NF s/ I
            </font>
        <?
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
        <td>
            <font color='red'>
            <?
                if($_GET['cmb_tipo_documento'] == 'NF') {//NF ...
                    $outros_impostos_federais   = genericas::variavel(34);
                    //Busca o Valor do Maior ICMS da NF ...
                    $sql = "SELECT `icms` AS maior_icms 
                            FROM `nfs_itens` 
                            WHERE `id_nf` = '$_GET[id_nf]' ORDER BY `icms` DESC LIMIT 1 ";
                    $campos_maior               = bancos::sql($sql);
                    $valor_frete_sem_impostos   = $campos[0]['valor_frete'] * (100 - $campos_maior[0]['maior_icms'] - $outros_impostos_federais) / 100;
                    echo number_format($valor_frete_sem_impostos, 2, ',', '.');
                }else {//Vale ...
                    echo '-';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Valor do Frete Pago:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_valor_frete_pago' value='<?=number_format($campos[0]['valor_frete_pago'], 2, ',', '.');?>' title='Digite o Valor do Frete Pago' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Transportadora:</b>
            </font>
        </td>
        <td>
            <font color='red'>
                <?=$campos[0]['transportadora'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação / Conferência:</b>
        </td>
        <td>
            <textarea name='txt_observacao_conferencia' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao_conferencia_correio'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_valor_frete_pago.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>