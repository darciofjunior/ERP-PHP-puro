<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>LISTA DE PREÇOS ATUALIZADA COM SUCESSO.</font>";

if(!empty($_POST['id_fornecedor_prod_insumo'])) {
    $ipi_incluso        = (!empty($_POST['chkt_ipi_incluso'])) ? 'S' : 'N';
    $condicao_padrao    = (!empty($_POST['chkt_condicao_padrao'])) ? 1 : 0;
    
    //Zero os adicionais da Lista de Preço ...
    $sql = "UPDATE `fornecedores_x_prod_insumos` 
            SET `id_funcionario` = '$_SESSION[id_funcionario]', `preco_faturado` = '$_POST[txt_preco_faturado]', 
            `preco_faturado_adicional` = '0', `prazo_pgto_ddl` = '$_POST[txt_prazo_pgto_dias]', 
            `desc_vista` = '$_POST[txt_desconto_vista]', `desc_sgd` = '$_POST[txt_desconto_sgd]', 
            `ipi` = '$_POST[txt_ipi]', `ipi_incluso` = '$ipi_incluso', `icms` = '$_POST[txt_icms]', 
            `reducao` = '$_POST[txt_reducao]', `iva` = '$_POST[txt_iva]', 
            `lote_minimo_reais` = '$_POST[txt_lote_minimo_reais]', `iva` = '$_POST[txt_iva]', 
            `forma_compra` = '$_POST[cmb_forma_compra]', `preco` = '$_POST[txt_preco_compra_nac]', 
            `tp_moeda` = '$_POST[cmb_tipo_moeda]', `preco_faturado_export` = '$_POST[txt_preco_fat_import_export]', 
            `preco_faturado_export_adicional` = '0', `valor_moeda_compra` = '$_POST[txt_valor_moeda_compra]', 
            `condicao_padrao` = '$condicao_padrao', `preco_exportacao` = '$_POST[txt_preco_compra_internac]', 
            `valor_moeda_custo` = '$_POST[txt_valor_moeda_custo]', `data_sys` = '".date('Y-m-d H:i:s')."' 
            WHERE `id_fornecedor_prod_insumo` = '$_POST[id_fornecedor_prod_insumo]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Procedimento quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_fornecedor_prod_insumo  = $_POST['id_fornecedor_prod_insumo'];
    $id_prods_insumos           = $_POST['id_prods_insumos'];
}else {
    $id_fornecedor_prod_insumo  = $_GET['id_fornecedor_prod_insumo'];
    $id_prods_insumos           = $_GET['id_prods_insumos'];
}

//Vou utilizar essas variáveis para o cálculo em JavaScript ...
$taxa_financeiro    = genericas::variavel(4);
$desconto_snf       = genericas::variavel(5);

$sql = "SELECT f.`id_pais`, f.`razaosocial`, fpi.*, g.`referencia`, pi.`discriminacao`, pi.`credito_icms` 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
        WHERE fpi.`id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' 
        AND fpi.`ativo` = '1' ";
$campos         = bancos::sql($sql);
$credito_icms   = $campos[0]['credito_icms'];
$rotulo         = ($credito_icms == 0) ? 'ICMS S/C:' : 'ICMS:';
$id_pais        = $campos[0]['id_pais'];
$id_fornecedor  = $campos[0]['id_fornecedor'];
//Variável de Controle que eu vou utilizar + embaixo p/ forçar a validação no JavaScript ...
$pipa = 0;
if($campos[0]['referencia'] == 'PRAC') $pipa = 1;//Significa que esse PI é um PA ...
/********Aqui é um select para trazer a condição padrão de preços do fornecedor********/
$sql = "SELECT `prazo_pgto_ddl`, `desc_vista`, `desc_sgd`, `ipi`, `ipi_incluso`, `icms`, `reducao`, 
        `iva`, `lote_minimo_reais`, `forma_compra`, `tp_moeda`, `condicao_padrao` 
        FROM `fornecedores_x_prod_insumos` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `condicao_padrao` = '1' LIMIT 1 ";
$campos_padrao = bancos::sql($sql);
$linhas_padrao = count($campos_padrao);
if($linhas_padrao == 1) {
    $prazo_pgto_padrao          = ($campos_padrao[0]['prazo_pgto_ddl'] == '0.0') ?  '0,0' : number_format($campos_padrao[0]['prazo_pgto_ddl'], 1, ',', '.');
    $desc_vista_padrao          = ($campos_padrao[0]['desc_vista'] == '0.0') ?  '0,0' : number_format($campos_padrao[0]['desc_vista'], 1, ',', '.');
    $desc_sgd_padrao            = ($campos_padrao[0]['desc_sgd'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['desc_sgd'], 1, ',', '.');
    $ipi_padrao                 = $campos_padrao[0]['ipi'];
    $ipi_incluso_padrao         = ($campos_padrao[0]['ipi_incluso'] == 'S') ? true : false;
    $icms_padrao                = ($campos_padrao[0]['icms'] == '0.0') ? '0,00' : number_format($campos_padrao[0]['icms'], 2, ',', '.');
    $reducao_padrao             = ($campos_padrao[0]['reducao'] == '0.0') ? '0,00' : number_format($campos_padrao[0]['reducao'], 2, ',', '.');
    $iva_padrao                 = ($campos_padrao[0]['iva'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['iva'], 2, ',', '.');
    $lote_minimo_reais_padrao   = ($campos_padrao[0]['lote_minimo_reais'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['lote_minimo_reais'], 2, ',', '.');
    $forma_compra_padrao        = $campos_padrao[0]['forma_compra'];
    $tp_moeda_padrao            = ($campos_padrao[0]['tp_moeda'] == 0) ? '' : $campos_padrao[0]['tp_moeda'];
    $condicao_padrao            = ($campos_padrao[0]['condicao_padrao'] == 1) ? true : false;
}
/**************************************************************************************/
?>
<html>
<head>
<title>.:: Alterar Lista de Preço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var pipa = '<?=$pipa;?>'
//IPI Incluso ...
    if(document.form.chkt_ipi_incluso.checked == true) {
        var ipi = (document.form.txt_ipi.value == '') ? 0 : eval(document.form.txt_ipi.value)
        //IPI ...
        if(ipi == 0) {
            alert('IPI INVÁLIDO !!!\n\nIPI IGUAL A ZERO !')
            document.form.txt_ipi.focus()
            document.form.txt_ipi.select()
            return false
        }
    }
//Forma de Compra ...
    if(!combo('form', 'cmb_forma_compra', '', 'SELECIONE UMA FORMA DE COMPRA !')) {
        return false
    }
<?
    //Aqui eu verifico se o funcionário tem permissão na lista de preço
    $sql = "SELECT id_lista_preco_permissao 
            FROM `listas_precos_permissoes` 
            WHERE `id_fornecedor` = '$id_fornecedor' 
            AND `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_lista   = bancos::sql($sql);
    $linhas         = count($campos_lista);
    if($linhas == 0) {
        //Verifico se é do Depto. de Compras, só esse Depto. tem permissão para manipular a lista ...
        $sql = "SELECT id_departamento 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_depto = bancos::sql($sql);
//Se o usuário for de Compras, Roberto ou o Dárcio então tem acesso para alterar os dados de lista de pço ...
        if($campos_depto[0]['id_departamento'] == 4 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
//Caso o usuário tenha selecionado uma Forma de Compra ...
            if(document.form.cmb_forma_compra.value != '') {
                var indice_combo = document.form.cmb_forma_compra.selectedIndex
                var forma_compra = document.form.cmb_forma_compra[indice_combo].text
//Comparação da Forma de Compra selecionada pelo usuário com as Condições do Tipo de Nota ...
                var resposta1 = document.form.txt_resposta1.value
                var resposta2 = document.form.txt_resposta2.value
                var resposta3 = document.form.txt_resposta3.value
                var resposta4 = document.form.txt_resposta4.value
//Verifico se a Forma de Compra selecionada pelo Usuário é compatível com as Condições do Tipo de Nota
                if((forma_compra == 'FAT/SGD' && resposta1 != 'OK') || (forma_compra == 'AV/SGD' && resposta2 != 'OK') || (forma_compra == 'AV/NF' && resposta3 != 'OK') || (forma_compra == 'FAT/NF' && resposta4 != 'OK')) {
                    alert('A CONDIÇÃO DE COMPRA SELECIONADA ESTÁ INCOMPATÍVEL COM A CONDIÇÃO DE COMPRA DA LISTA DE PREÇO DESTE FORNECEDOR !!!')
                    return false
                }
            }
//Significa que o PI que é PA, então eu verifico se neste está preenchido o campo da coluna ICMS
            if(pipa == 1) {
/*Se estiver vazio o campo ICMS ou zerado desse PI que é PA, então eu forço o preenchimento desse campo 
de qualquer jeito*/
                if(document.form.txt_icms.value == '' || document.form.txt_icms.value == '0,00') {
//ICMS %
                    alert('DIGITE O ICMS % !')
                    document.form.txt_icms.focus()
                    document.form.txt_icms.select()
                    return false
                }
            }
//Aqui eu verifico se o Preço Fat.Import/Export está preenchido ...
            if(document.form.txt_preco_fat_import_export.value != '' && document.form.txt_preco_fat_import_export.value != '0,00') {
//Tipo de Moeda ...
                if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DE MOEDA !')) {
                    return false
                }
            }
//Pergunto se o Usuário realmente deseja salvar os valores por causa da Segurança do Sistema ...
            var resposta = confirm('DESEJA SALVAR OS VALORES ?')
            if(resposta == false) {
                return false
            }else {
//Destravo essas caixas para poder guardar no BD ...
                document.form.txt_preco_compra_nac.disabled         = false
                document.form.txt_preco_compra_internac.disabled    = false
/*Aqui trava o botão para não ter perigo de o usuário clicar novamente no botão e dar complicação
na caixa com os valores por causa da função de tratamento do JavaScript*/
                document.form.cmd_salvar.disabled = true
                document.form.passo.value = 1
//Aqui é para não atualizar o frames abaixo desse Pop-UP
                document.form.nao_atualizar.value = 1
                atualizar_abaixo()
                return limpeza_moeda('form', 'txt_preco_faturado, txt_preco_fat_import_export, txt_desconto_vista, txt_desconto_sgd, txt_ipi, txt_icms, txt_reducao, txt_iva, txt_valor_moeda_compra, txt_prazo_pgto_dias, txt_valor_moeda_custo, txt_preco_custo, txt_preco_compra_nac, txt_preco_compra_internac, ')
            }
<?
//Não tem acesso, não é do departamento de Compras
        }else {
?>
            alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A LISTA DE PREÇO !')
            return false
<?
//Não tem permissão para manipular a lista de preços
        }
    }else {
?>
        alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A LISTA DE PREÇO !')
        return false
<?
    }
?>
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(window.opener.parent.itens) == 'object') {
            opener.parent.itens.location.href = opener.parent.itens.location
        }else {
            opener.location.href = opener.location
        }
    }
}

function calcular() {
//Variáveis globais ...
    var financeiro      = '<?=$taxa_financeiro;?>'
    var desconto_snf 	= '<?=$desconto_snf;?>'
    var id_pais         = '<?=$id_pais;?>'
//Variáveis digitadas pelo usuário ...
//Preço Faturado
    var preco_faturado      = (document.form.txt_preco_faturado.value != '') ? eval(strtofloat(document.form.txt_preco_faturado.value)) : 0
//Preço Fat. Import/Export:
    var preco_faturado_exp  = (document.form.txt_preco_fat_import_export.value != '') ? eval(strtofloat(document.form.txt_preco_fat_import_export.value)) : 0
//Desconto à Vista
    var desconto_vista      = (document.form.txt_desconto_vista.value != '') ? eval(strtofloat(document.form.txt_desconto_vista.value)) : 0
//Desconto SGD
    var desconto_sgd        = (document.form.txt_desconto_sgd.value != '') ? eval(strtofloat(document.form.txt_desconto_sgd.value)) : 0
//Valor Moeda p/ Compra
    if(id_pais != 31) {//Se o País do Fornecedor for <> de Brasil
        //Igualo a 1 p/ facilitar para o Roberto, mas somente quando for País Estrangeiro, por causa do Dólar, Euro
        var valor_moeda_compra = (document.form.txt_valor_moeda_compra.value == '' || document.form.txt_valor_moeda_compra.value == '0,0000') ? 1 : eval(strtofloat(document.form.txt_valor_moeda_compra.value))
    }else {//Se o País for nacional, segue o procedimento normal ...
        var valor_moeda_compra = (document.form.txt_valor_moeda_compra.value != '') ? eval(strtofloat(document.form.txt_valor_moeda_compra.value)) : 0
    }
//Prazo Pgto Dias
    var prazo_pgto_dias         = (document.form.txt_prazo_pgto_dias.value != '') ?  eval(strtofloat(document.form.txt_prazo_pgto_dias.value)) : 0
<?
    if($credito_icms == 0) {
?>
        var icms = 0
<?
    }else {
?>
        var icms = (document.form.txt_icms.value != '') ? eval(strtofloat(document.form.txt_icms.value)) : 0
<?
    }
?>
    var reducao             = eval(strtofloat(document.form.txt_reducao.value))
    var icms_com_reducao    = icms * (1 - reducao / 100)
/*Observação -> Cálculo o Preço de Compra (Inter.) primeiro, pq o cálculo de Condições é baseado nessas
mesmas variáveis abaixo que estão baseadas no cálculo de Preço de Compra (Nac.)*/

//Parte onde prepara as variáveis do Cálculo referente Preço de Compra (Inter.):
    precoavnf       = (preco_faturado_exp * (100 - desconto_vista) / 100)
    precominimo     = (preco_faturado_exp * (100 - (prazo_pgto_dias / 30) * financeiro) / 100)
    precofatsgd     = (preco_faturado_exp * (100 - desconto_sgd) / 100)
    precominimo2    = (preco_faturado_exp * (100 - (icms_com_reducao - desconto_snf)) / 100)
    precoavsgd      = (precofatsgd * (100 - desconto_vista) / 100)
    precominimo3    = (precominimo * (100 - (icms_com_reducao - desconto_snf)) / 100)

    resposta1       = (Math.round((precoavnf * 100))) / 100
    resposta2       = (Math.round((precofatsgd * 100))) / 100
    resposta3       = (Math.round((precoavsgd * 100))) / 100
    precominimo     = (Math.round((precominimo * 100))) / 100
    precominimo2    = (Math.round((precominimo2 * 100))) / 100
    precominimo3    = (Math.round((precominimo3 * 100))) / 100
/************************************************************************************************/
/************************ Cálculo referente Preço de Compra (Inter.): ***************************/
/************************************************************************************************/
/*A caixa de resultado Preço de Compra Nacional receberá o Preço FAT/SGD - */
    if(document.form.cmb_forma_compra.value == 1) {
        document.form.txt_preco_compra_internac.value = preco_faturado_exp * valor_moeda_compra
        document.form.txt_preco_compra_internac.value = arred(document.form.txt_preco_compra_internac.value, 2, 1)
    }else if(document.form.cmb_forma_compra.value == 2) {
        document.form.txt_preco_compra_internac.value = resposta2 * valor_moeda_compra
        document.form.txt_preco_compra_internac.value = arred(document.form.txt_preco_compra_internac.value, 2, 1)
    }else if(document.form.cmb_forma_compra.value == 3) {
        document.form.txt_preco_compra_internac.value = resposta1 * valor_moeda_compra
        document.form.txt_preco_compra_internac.value = arred(document.form.txt_preco_compra_internac.value, 2, 1)
    }else if(document.form.cmb_forma_compra.value == 4) {
        document.form.txt_preco_compra_internac.value = resposta3 * valor_moeda_compra
        document.form.txt_preco_compra_internac.value = arred(document.form.txt_preco_compra_internac.value, 2, 1)
    }
//Parte onde prepara as variáveis do Cálculo referente Preço de Compra (Nac.)
    precoavnf       = (preco_faturado * (100 - desconto_vista) / 100)
    precominimo     = (preco_faturado * (100 - (prazo_pgto_dias / 30) * financeiro) / 100)
    precofatsgd     = (preco_faturado * (100 - desconto_sgd) / 100)
    precominimo2    = (preco_faturado * (100 - (icms_com_reducao - desconto_snf)) / 100)
    precoavsgd      = (precofatsgd * (100 - desconto_vista) / 100)
    precominimo3    = (precominimo * (100 - (icms_com_reducao - desconto_snf)) / 100)

    resposta1       = (Math.round((precoavnf * 100))) / 100
    resposta2       = (Math.round((precofatsgd * 100))) / 100
    resposta3       = (Math.round((precoavsgd * 100))) / 100
    precominimo     = (Math.round((precominimo * 100))) / 100
    precominimo2    = (Math.round((precominimo2 * 100))) / 100
    precominimo3    = (Math.round((precominimo3 * 100))) / 100
/************************************************************************************************/
/************************** Cálculo referente Preço de Compra (Nac.): ***************************/
/************************************************************************************************/
/*A caixa de resultado Preço de Compra Nacional receberá o Preço FAT/SGD - */
    if(document.form.cmb_forma_compra.value == 1) {
        document.form.txt_preco_compra_nac.value = document.form.txt_preco_faturado.value
    }else if(document.form.cmb_forma_compra.value == 2) {
        document.form.txt_preco_compra_nac.value = arred(String(resposta2), 2, 1)
    }else if(document.form.cmb_forma_compra.value == 3) {
        document.form.txt_preco_compra_nac.value = arred(String(resposta1), 2, 1)
    }else if(document.form.cmb_forma_compra.value == 4) {
        document.form.txt_preco_compra_nac.value = arred(String(resposta3), 2, 1)
    }
/************************************************************************************************/
/************************** Cálculo referente as Melhores Condições: ****************************/
/************************************************************************************************/
    document.form.txt_preco_fat_sgd.value = resposta2
    document.form.txt_preco_fat_sgd.value = document.form.txt_preco_fat_sgd.value.replace('.', ',')
    document.form.txt_preco_minimo1.value = precominimo
    document.form.txt_preco_minimo1.value = document.form.txt_preco_minimo1.value.replace('.', ',')
/*A caixa de resultado Preço de Compra Nacional receberá o Preço AV/NF - */
    document.form.txt_preco_avnf.value = resposta1
    document.form.txt_preco_avnf.value = document.form.txt_preco_avnf.value.replace('.', ',')
    document.form.txt_preco_minimo2.value = precominimo2
    document.form.txt_preco_minimo2.value = document.form.txt_preco_minimo2.value.replace('.', ',')
/*A caixa de resultado Preço de Compra Nacional receberá o Preço AV/SGD - */
    document.form.txt_preco_av_sgd.value = resposta3
    document.form.txt_preco_av_sgd.value = document.form.txt_preco_av_sgd.value.replace('.', ',')
    document.form.txt_preco_minimo3.value = precominimo3
    document.form.txt_preco_minimo3.value = document.form.txt_preco_minimo3.value.replace('.', ',')
/* A CAIXA DE RESULTADOS PRECO FATURADO COM NF */
    document.form.txt_preco_fat_nf.value = document.form.txt_preco_faturado.value
    document.form.txt_preco_minimo4.value = document.form.txt_preco_faturado.value
    resposta1 = precominimo / resposta1   //Preço AV/NF
    resposta2 = precominimo2 / resposta2  //Preço FAT/SGD
    resposta3 = precominimo3 / resposta3  //Preço AV/SGD
//Atribuição para os OKs
    document.form.txt_resposta1.value = 'Não'
    document.form.txt_resposta2.value = 'Não'
    document.form.txt_resposta3.value = 'Não'
    document.form.txt_resposta4.value = 'Não'

    desconto_sgd = parseFloat(desconto_sgd)
    if(isNaN(desconto_sgd)) desconto_sgd = 0
    desconto_snf = parseFloat(desconto_snf)
    if(isNaN(desconto_snf)) desconto_snf = 0
    financeiro = parseFloat(financeiro)
    if(isNaN(financeiro)) financeiro = 0
    desconto_vista = parseFloat(desconto_vista)
    if(isNaN(desconto_vista)) desconto_vista = 0
    icms_com_reducao = parseFloat(icms_com_reducao)
    if(isNaN(icms_com_reducao)) icms_com_reducao = 0
    

    if(((icms_com_reducao - desconto_sgd) <= desconto_snf) && (desconto_vista < financeiro)) {
        document.form.txt_resposta1.value = 'OK'
    }else if(((icms_com_reducao - desconto_sgd) <= desconto_snf) && (desconto_vista >= financeiro)) {
        document.form.txt_resposta2.value = 'OK'
    }else if(((icms_com_reducao - desconto_sgd) > desconto_snf) && (desconto_vista >= financeiro)) {
        document.form.txt_resposta3.value = 'OK'
    }else {
        document.form.txt_resposta4.value = 'OK'
    }
}

function copiar_condicao_padrao(linhas_condicao_padrao) {
    if(linhas_condicao_padrao == 1) {//Se existir condição padrão então ...
        document.form.txt_prazo_pgto_dias.value     = '<?=$prazo_pgto_padrao;?>'
        document.form.txt_desconto_vista.value      = '<?=$desc_vista_padrao;?>'
        document.form.txt_desconto_sgd.value        = '<?=$desc_sgd_padrao;?>'
        document.form.txt_ipi.value                 = '<?=$ipi_padrao;?>'
        document.form.chkt_ipi_incluso.checked      = '<?=$ipi_incluso_padrao;?>'
        document.form.txt_icms.value                = '<?=$icms_padrao;?>'
        document.form.txt_reducao.value             = '<?=$reducao_padrao;?>'
        document.form.txt_iva.value                 = '<?=$iva_padrao;?>'
        document.form.txt_lote_minimo_reais.value   = '<?=$lote_minimo_reais_padrao;?>'
        document.form.cmb_forma_compra.value        = '<?=$forma_compra_padrao;?>'
        document.form.cmb_tipo_moeda.value          = '<?=$tp_moeda_padrao;?>'
        document.form.chkt_condicao_padrao.checked  = '<?=$condicao_padrao;?>'
//Aqui calcula novamente
        calcular()
    }else {
        resposta = confirm('ESSE FORNECEDOR NÃO POSSUI NENHUM ITEM COM CONDIÇÃO PADRÃO !!!\nDESEJA ATRELAR ALGUM ITEM COMO SENDO PADRÃO P/ ESSE FORNECEDOR ?')
        if(resposta == true) nova_janela('atrelar_item_padrao_fornec.php?id_fornecedor=<?=$id_fornecedor;?>', 'POP', '', '', '', '', 200, 600, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body onload='calcular();document.form.txt_preco_faturado.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*************Controle de Tela**************-->
<input type='hidden' name='id_fornecedor_prod_insumo' value='<?=$id_fornecedor_prod_insumo;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<!--Esse parâmetro vem pela tela de Comparativo-->
<input type='hidden' name='id_prods_insumos' value='<?=$id_prods_insumos;?>'>
<!--*******************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar Lista de Preço
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Refer&ecirc;ncia:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['referencia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Discrimina&ccedil;&atilde;o:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#990000'>
                <b>Preço Fat. Nacional: </b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_preco_faturado' value='<?=number_format($campos[0]['preco_faturado'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            <font color='#990000'>
                <b>Preço Fat. Import/Export:</b>
            </font>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_preco_fat_import_export' value='<?=number_format($campos[0]['preco_faturado_export'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desc. à Vista:
        </td>
        <td>
            <input type='text' name='txt_desconto_vista' value='<?=number_format($campos[0]['desc_vista'], 1, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            Desc. SGD:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_desconto_sgd' value='<?=number_format($campos[0]['desc_sgd'], 1, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IPI:
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=$campos[0]['ipi'];?>' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            IPI Incluso:
        </td>
        <td colspan='2'>
            <?
                $checked = ($campos[0]['ipi_incluso'] == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_ipi_incluso' value='S' <?=$checked;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$rotulo;?>
        </td>
        <td>
            <input type='text' name='txt_icms' value='<?=number_format($campos[0]['icms'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            Redução:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_reducao' value='<?=number_format($campos[0]['reducao'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA:
        </td>
        <td>
            <input type='text' name='txt_iva' value='<?=number_format($campos[0]['iva'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            Lote Min R$:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_lote_minimo_reais' value='<?=number_format($campos[0]['lote_minimo_reais'], 2, ',', '.');?>' size='6' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Forma de Compra:</b>
        </td>
        <td>
            <select name='cmb_forma_compra' onchange='calcular()' class='combo'>
                <?
                    if($campos[0]['forma_compra'] == 1) {
                        $selected_forma_compra1 = 'selected';
                    }else if($campos[0]['forma_compra'] == 2) {
                        $selected_forma_compra2 = 'selected';
                    }else if($campos[0]['forma_compra'] == 3) {
                        $selected_forma_compra3 = 'selected';
                    }else if($campos[0]['forma_compra'] == 4) {
                        $selected_forma_compra4 = 'selected';
                    }
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected_forma_compra1;?>>FAT/NF</option>
                <option value='2' <?=$selected_forma_compra2;?>>FAT/SGD</option>
                <option value='3' <?=$selected_forma_compra3;?>>AV/NF</option>
                <option value='4' <?=$selected_forma_compra4;?>>AV/SGD</option>
            </select>
        </td>
        <td>
            Tipo Moeda:
        </td>
        <td colspan='2'>
            <select name='cmb_tipo_moeda' onchange='calcular()' class='combo'>
            <?
                if($campos[0]['tp_moeda'] == 1) {
                    $selected_tipo_moeda1 = 'selected';
                }else if($campos[0]['tp_moeda'] == 2) {
                    $selected_tipo_moeda2 = 'selected';
                }
            ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected_tipo_moeda1;?>>DÓLAR - U$</option>
                <option value='2' <?=$selected_tipo_moeda2;?>>EURO - &euro;</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Moeda p/ Compra:
        </td>
        <td>
            <input type='text' name='txt_valor_moeda_compra' value='<?=number_format($campos[0]['valor_moeda_compra'], 4, ',', '.')?>' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
        <td>
            Prazo Pgto. Dias:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_prazo_pgto_dias' value='<?=number_format($campos[0]['prazo_pgto_ddl'], 1, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular()" size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Moeda p/ Custo:
        </td>
        <td>
            <input type='text' name='txt_valor_moeda_custo' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' class='caixadetexto'>
        </td>
        <td>
            Preço de Custo:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_preco_custo' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#0000FF'>
                <b>Preço de Compra (Nac.):</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_preco_compra_nac' value='<?=number_format($campos[0]['preco'], 2, ',', '.');?>' size='15' class='textdisabled' disabled>
        </td>
        <td>
            <font color='#0000FF'>
                <b>Preço de Compra (Inter.):</b>
            </font>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_preco_compra_internac' value='<?=number_format($campos[0]['preco_exportacao'], 2, ',', '.');?>' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cond. Padrão:
        </td>
        <td>
            <?
                $checked = ($campos[0]['condicao_padrao'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_condicao_padrao' value='1' title='Condição Padrão' <?=$checked;?> class='checkbox'>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_usar_condicao_padrao' value='Usar Condi&ccedil;&otilde;es Padr&atilde;o' title='Usar Condi&ccedil;&otilde;es Padr&atilde;o' onclick="copiar_condicao_padrao('<?=$linhas_padrao;?>')" style='color:green' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='5'>
            <font color='#FF0000'>
                <b>C&Aacute;LCULO</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <b>Preço FAT/SGD:</b>
        </td>
        <td colspan='2'>
            <b>Preço m&aacute;ximo:</b>
        </td>
        <td>
            <b>Situa&ccedil;&atilde;o:</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_fat_sgd' size='20' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_minimo2' size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_resposta1' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <b>Preço AV/SGD:</b>
        </td>
        <td colspan='2'>
            <b>Pre&ccedil;o m&aacute;ximo:</b>
        </td>
        <td>
            <b>Situa&ccedil;&atilde;o:</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_av_sgd' size='20' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_minimo3' size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_resposta2' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <b>Preço AV/NF:</b>
        </td>
        <td colspan='2'>
            <b>Preço m&aacute;ximo:</b>
        </td>
        <td>
            <b>Situa&ccedil;&atilde;o:</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_avnf' size='20' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_minimo1' size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_resposta3' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <b>Preço FAT/NF:</b>
        </td>
        <td colspan='2'>
            <b>Pre&ccedil;o m&aacute;ximo:</b>
        </td>
        <td>
            <b>Situa&ccedil;&atilde;o:</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_fat_nf' size='20' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            R$ <input type='text' name='txt_preco_minimo4' size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_resposta4' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
        <?
            /**************************************************************/
            //Esses botões só aparecerão quando essa Tela for acessada de dentro do Comparativo de Compras ...
            if(empty($_GET['veio_lista_preco'])) {//Esse parâmetro só vai existir quando essa Tela for acessada da Lista de Preço de Compras que fará com que não exiba esses 2 botões abaixo ...
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular();document.form.txt_preco_faturado.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
            /**************************************************************/
        ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>