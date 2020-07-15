<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ALTERADO(S) COM SUCESSO.</font>";

$pis    = round(genericas::variavel(70), 2);
$cofins = round(genericas::variavel(61), 2);

$id_nf_outra_item = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nf_outra_item'] : $_GET['id_nf_outra_item'];

if(!empty($_POST['txt_discriminacao'])) {
    $sql = "UPDATE `nfs_outras_itens` SET `referencia` = '$_POST[txt_referencia]', `discriminacao` = '$_POST[txt_discriminacao]', 
            `id_unidade` = '$_POST[cmb_unidade]', `qtde` = '$_POST[txt_qtde]', `valor_unitario` = '$_POST[txt_valor_unitario]', 
            `peso_unitario` = '$_POST[txt_peso_unitario]', `id_classific_fiscal` = '$_POST[cmb_classific_fiscal]', 
            `situacao_tributaria` = '$_POST[cmb_situacao_tributaria]', `origem_mercadoria` = '$_POST[cmb_origem_mercadoria]', 
            `ipi` = '$_POST[txt_ipi]', `icms` = '$_POST[txt_icms]', 
            `reducao` = '$_POST[txt_reducao]', `icms_intraestadual` = '$_POST[txt_icms_intraestadual]', `iva` = '$_POST[txt_iva]', 
            `imposto_importacao` = '$_POST[txt_imposto_importacao]', `valor_cif` = '$_POST[txt_valor_cif]', `bc_icms_item` = '$_POST[txt_bc_icms_item]', 
            `pis` = '$_POST[txt_pis]', `cofins` = '$_POST[txt_cofins]', `bc_pis_cofins` = '$_POST[txt_bc_pis_cofins]', 
            `despesas_aduaneiras` = '$_POST[txt_despesas_aduaneiras]', `despesas_acessorias` = '$_POST[txt_despesas_acessorias]', 
            `observacao` = '$_POST[txt_observacao]' 
            WHERE `id_nf_outra_item` = '$id_nf_outra_item' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
?>
    <Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        alert('ITEM ALTERADO COM SUCESSO !\n\nVERIFIQUE O TEXTO DA NOTA !!')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
        nova_janela('../../nfs_consultar/preencher_texto_nf.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>', 'CONSULTAR', '', '', '', '', '350', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    </Script>
<?
    
}

//Aqui eu busco dados 
$sql = "SELECT nfso.`id_nf_outra`, nfso.`id_cfop`, CONCAT(c.`cfop`, '.', c.`num_cfop`) AS numero_cfop, nfsoi.* 
        FROM `nfs_outras_itens` nfsoi 
        INNER JOIN `nfs_outras` nfso ON nfso.`id_nf_outra` = nfsoi.`id_nf_outra` 
        LEFT JOIN `cfops` c ON c.`id_cfop` = nfso.`id_cfop` 
        WHERE nfsoi.`id_nf_outra_item` = '$id_nf_outra_item' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_cfop        = $campos[0]['id_cfop'];
$numero_cfop    = $campos[0]['numero_cfop'];
?>
<title>.:: Alterar Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var id_cfop = '<?=$id_cfop;?>'
//Referência ...
    if(!texto('form', 'txt_referencia', '3', "-=!@¹²³£¢¬{} 1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNM,.'ÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.,%&*$()@#<>ªº°:;\/", 'REFERÊNCIA', '1')) {
        return false
    }
//Discriminação ...
    if(!texto('form', 'txt_discriminacao', '3', '1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.:*/_-|º°()%$"Ø ', 'DISCRIMINAÇÃO', '1')) {
        return false
    }
//Unidade ...
    if(!combo('form', 'cmb_unidade', '', 'SELECIONE A UNIDADE !')) {
        return false
    }
//Qtde ...
    if(!texto('form', 'txt_qtde', '1', '0123456789,.', 'QUANTIDADE', '1')) {
        return false
    }
//Valor Unitário ...
    if(!texto('form', 'txt_valor_unitario', '1', '0123456789,.', 'VALOR', '2')) {
        return false
    }
/*********************************************************************************************************/	
//Peso Unitário ...
    if(document.form.txt_peso_unitario.value != '') {
        if(!texto('form', 'txt_peso_unitario', '1', '0123456789,.', 'PESO UNITÁRIO', '2')) {
            return false
        }
    }
//Classificação Fiscal ...
    if(!combo('form', 'cmb_classific_fiscal', '', 'SELECIONE A CLASSIFICAÇÃO FISCAL !')) {
        return false
    }
//Origem da Mercadoria ...
    if(!combo('form', 'cmb_origem_mercadoria', '', 'SELECIONE A ORIGEM DA MERCADORIA !')) {
        return false
    }
//Situação Tributária ...
    if(!combo('form', 'cmb_situacao_tributaria', '', 'SELECIONE UMA SITUAÇÃO TRIBUTÁRIA !')) {
        return false
    }
/*********************************************************************************************************/
/***********************************************CFOP 3.101************************************************/
/*********************************************************************************************************/
    if(id_cfop == 161) {//Se for essa CFOP 3.101 então o Sys força o preenchimento desses valores abaixo ...
//IPI ...
        if(!texto('form', 'txt_ipi', '1', '0123456789,.', 'IPI', '2')) {
                return false
        }
    //ICMS ...
        if(!texto('form', 'txt_icms', '1', '0123456789,.', 'ICMS', '2')) {
                return false
        }
    //Imposto de Importação ...
        if(!texto('form', 'txt_imposto_importacao', '1', '0123456789,.', 'IMPOSTO DE IMPORTAÇÃO', '2')) {
                return false
        }
    //Valor CIF ...
        if(!texto('form', 'txt_valor_cif', '1', '0123456789,.', 'VALOR CIF', '2')) {
                return false
        }
//Esse Valor nunca poderá ficar em Branco, porque através deste eu cálculo o Valor Unitário do Item e Valor do IPI ...
        if(document.form.txt_valor_cif.value == '0,00') {
            alert('VALOR DE CIF INVÁLIDO !')
            document.form.txt_valor_cif.focus()
            document.form.txt_valor_cif.select()
            return false
        }
    //BC ICMS Item ...
        if(!texto('form', 'txt_bc_icms_item', '1', '0123456789,.', 'BASE ICMS ITEM', '1')) {
                return false
        }
    //BC PIS / Cofins ...
        if(!texto('form', 'txt_bc_pis_cofins', '4', '0123456789,.', 'BASE PIS / COFINS', '1')) {
            return false
        }
//Despesas Aduaneiras ...
        if(!texto('form', 'txt_despesas_aduaneiras', '1', '0123456789,.', 'DESPESA ADUANEIRA', '1')) {
            return false
        }
//Despesas Acessórias ...
        if(document.form.txt_despesas_acessorias.value != '') {
            if(!texto('form', 'txt_despesas_acessorias', '1', '0123456789,.', 'DESPESA ACESSORIAS', '1')) {
                return false
            }
        }
    }
//Destravo esses campos p/ poder gravar no Banco de Dados ...
    document.form.txt_discriminacao.disabled    = false
    document.form.txt_valor_unitario.disabled   = false
    document.form.txt_bc_pis_cofins.disabled        = false
    document.form.txt_pis.disabled              = false
    document.form.txt_cofins.disabled           = false
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value           = 1
    return limpeza_moeda('form', 'txt_qtde, txt_valor_unitario, txt_peso_unitario, txt_ipi, txt_icms, txt_reducao, txt_icms_intraestadual, txt_iva, txt_imposto_importacao, txt_valor_cif, txt_bc_icms_item, txt_pis, txt_cofins, txt_bc_pis_cofins, txt_despesas_aduaneiras, txt_despesas_acessorias, ')
}

function calcular_valor_unitario() {
    var numero_cfop = '<?=$numero_cfop;?>'
    if(numero_cfop == '3.101' || numero_cfop == '3.102') { // Esse calculo só será feito quando efetuarmos uma nota de importação...
        var valor_cif           = eval(strtofloat(document.form.txt_valor_cif.value))
        var imposto_importacao  = eval(strtofloat(document.form.txt_imposto_importacao.value))
        var qtde                = eval(strtofloat(document.form.txt_qtde.value))
        if(valor_cif != 0 && imposto_importacao != 0 && qtde != 0) {
            document.form.txt_valor_unitario.value =  valor_cif * (1 + imposto_importacao / 100) / qtde
            document.form.txt_valor_unitario.value = arred(document.form.txt_valor_unitario.value, 10, 1)
        }else {
            document.form.txt_valor_unitario.value = ''
        }
        document.form.txt_bc_pis_cofins.value = document.form.txt_valor_cif.value
        calcular_pis_cofins()
    }
}

function calcular_pis_cofins() {
    var bc_pis_cofins   = eval(strtofloat(document.form.txt_bc_pis_cofins.value))
    var pis             = '<?=$pis;?>'
    var cofins          = '<?=$cofins;?>'

    document.form.txt_pis.value     = (bc_pis_cofins * pis) / 100//Cálculo do PIs ...
    document.form.txt_cofins.value  = (bc_pis_cofins * cofins) / 100//Cálculo do Cofins ...
    
    document.form.txt_pis.value     = arred(document.form.txt_pis.value, 2, 1)
    document.form.txt_cofins.value  = arred(document.form.txt_cofins.value, 2, 1)
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*****************************Controle de Tela*****************************-->
<input type='hidden' name='id_nf_outra_item' value='<?=$id_nf_outra_item;?>'>
<input type='hidden' name='id_nf_outra' value='<?=$campos[0]['id_nf_outra'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--*****************************Controle de Tela*****************************-->
<!--**************************************************************************-->
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Alterar Item(ns)
        </td>
    </tr>
    <?
        if(!empty($campos[0]['id_produto_acabado'])) {//Se foi cadastrado o PA ...
            $sql = "SELECT `referencia`, `discriminacao` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
            $campos_pa      = bancos::sql($sql);
            $referencia     = $campos_pa[0]['referencia'];
            $discriminacao  = $campos_pa[0]['discriminacao'];
            $disabled       = 'disabled';
            $class          = 'textdisabled';
        }else if(!empty($campos[0]['id_produto_insumo'])) {//Se foi cadastrado o PI ...
            $sql = "SELECT g.`referencia`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
            $campos_pi      = bancos::sql($sql);
            $referencia     = $campos_pi[0]['referencia'];
            $discriminacao  = $campos_pi[0]['discriminacao'];
            $disabled       = 'disabled';
            $class          = 'textdisabled';
        }else if(!empty($campos[0]['discriminacao'])) {//Se foi cadastrado uma Discriminação ...
            $referencia     = $campos[0]['referencia'];
            $discriminacao  = $campos[0]['discriminacao'];
            $class          = 'caixadetexto';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Referência:</b>
        </td>
        <td>
            <input type='text' name='txt_referencia' value='<?=$referencia;?>' title='Digite a Referência' size='30' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Discriminação:</b>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' value='<?=$discriminacao?>' title='Digite a Discriminação' size='55' maxlength='150' class='<?=$class;?>' <?=$disabled;?>>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Unidade:</b>
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `sigla` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql, $campos[0]['id_unidade']);
            ?>
            </select>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde' value='<?=number_format($campos[0]['qtde'], 2, ',', '.')?>' title='Digite a Quantidade' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
                <b>Valor Unitário:</b>
        </td>
        <td>
            <?
                if($numero_cfop == '3.101') {
                    $aviso          = '<font color="red"><b>Valor calculado automaticamente</b></font>';
                    $class          = 'textdisabled';
                    $disabled       = 'disabled';
                    $casas_decimais = 10;
                }else {
                    $class          = 'caixadetexto';
                    $disabled       = '';
                    $casas_decimais = 2;
                }
            ?>
            <input type='text' name='txt_valor_unitario' value='<?=number_format($campos[0]['valor_unitario'], $casas_decimais, ',', '.')?>' title='Digite o Valor Unitário' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='25' maxlength='20' class='<?=$class;?>' <?=$disabled;?>>
            <?=$aviso;?>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Unitário:
        </td>
        <td>
            <input type='text' name='txt_peso_unitario' value='<?=number_format($campos[0]['peso_unitario'], 2, ',', '.')?>' title='Digite o Peso Unitário' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" size='14' maxlength='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Classificação Fiscal:</b>
        </td>
        <td>
            <select name='cmb_classific_fiscal' title='Selecione a Classificação Fiscal' class='combo'>
            <?
                $sql = "SELECT `id_classific_fiscal`, `classific_fiscal` 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY `classific_fiscal` ";
                echo combos::combo($sql, $campos[0]['id_classific_fiscal']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Origem da Mercadoria:</b>
        </td>
        <td>
            <select name='cmb_origem_mercadoria' title='Selecione a Origem da Mercadoria' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_origem_mercadoria  = array_sistema::origem_mercadoria();
                    foreach($vetor_origem_mercadoria as $indice => $id_origem_mercadoria) {
                        $selected = ($campos[0]['origem_mercadoria'] == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$id_origem_mercadoria."</option>";
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Situação Tributária:</b>
        </td>
        <td>
            <select name='cmb_situacao_tributaria' title='Selecione a Situação Tributária' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_situacao_tributaria  = array_sistema::situacao_tributaria();
                    foreach($vetor_situacao_tributaria as $indice => $id_situacao_tributaria) {
                        $selected = ($campos[0]['situacao_tributaria'] == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$id_situacao_tributaria."</option>";
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IPI %:
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=number_format($campos[0]['ipi'], 2, ',', '.');?>' title='Digite o IPI' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS %:
        </td>
        <td>
            <input type='text' name='txt_icms' value='<?=number_format($campos[0]['icms'], 2, ',', '.');?>' title='Digite o ICMS' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            Redução %:
        </td>
        <td>
            <input type='text' name='txt_reducao' value='<?=number_format($campos[0]['reducao'],2 , ',', '.');?>' title='Digite a Redução' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS IntraEstadual%:
        </td>
        <td>
            <input type='text' name='txt_icms_intraestadual' value='<?=number_format($campos[0]['icms_intraestadual'],2 , ',', '.')?>' title='Digite o ICMS IntraEstadual' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA %:
        </td>
        <td>
            <input type='text' name='txt_iva' value='<?=number_format($campos[0]['iva'],2 , ',', '.')?>' title='Digite o IVA' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='iframe' align='center'>
        <td colspan='2'>
            DADOS EXCLUSIVOS NF IMPORTAÇÃO
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Imposto de Importação %:
        </td>
        <td>
            <input type='text' name='txt_imposto_importacao' value='<?=number_format($campos[0]['imposto_importacao'], 2, ',', '.')?>' title="Digite o Imposto de Importação" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='8' maxlength='7' class='caixadetexto'>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor CIF:
        </td>
        <td>
            <input type='text' name='txt_valor_cif' value='<?=number_format($campos[0]['valor_cif'],2 , ',', '.')?>' title='Digite o Valor CIF' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='12' maxlength='10' class='caixadetexto'>
            <b><i> -> (VA ou BC II pela SPEEDWAY)</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            BC ICMS Item:
        </td>
        <td>
            <input type='text' name='txt_bc_icms_item' value='<?=number_format($campos[0]['bc_icms_item'], 2, ',', '.')?>' title='Digite a BC ICMS Item' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>	
    </tr>
     <tr class='linhanormal'>
        <td>
            BC PIS / COFINS:
        </td>
        <td>
            <input type='text' name='txt_bc_pis_cofins' value='<?=number_format($campos[0]['bc_pis_cofins'], 2, ',', '.');?>' title="Digite a BC PIS / COFINS" value="<?=number_format($campos[0]['bc_icms_item'], 2, ',', '.')?>" size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            PIS <b>(<?=number_format($pis, 2, ',', '.');?>%)</b>:
        </td>
        <td>
            <input type='text' name='txt_pis' value='<?=number_format($campos[0]['pis'], 2, ',', '.');?>' title='Digite o Pis' size='15' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cofins <b>(<?=number_format($cofins, 2, ',', '.');?>%)</b>:
        </td>
        <td>
            <input type='text' name='txt_cofins' value='<?=number_format($campos[0]['cofins'], 2, ',', '.');?>' title='Digite o Cofins' size='15' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Despesas Aduaneiras:
        </td>
        <td>
            <input type='text' name='txt_despesas_aduaneiras' value='<?=number_format($campos[0]['despesas_aduaneiras'], 2, ',', '.')?>' title='Digite as Despesas Aduaneiras' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <b><i> -> (Taxa Siscomex pela SPEEDWAY)</i></b>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            Despesas Acessórias / AFRMM:
        </td>
        <td>
            <input type='text' name='txt_despesas_acessorias' value='<?=number_format($campos[0]['despesas_acessorias'], 2, ',', '.')?>' title='Digite a Despesas Acessorias' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' maxlength='12' class='caixadetexto'>
            <b><i> -> (Marinha Mercante pela SPEEDWAY)</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao']?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_referencia.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</form>
</table>
</body>
</html>