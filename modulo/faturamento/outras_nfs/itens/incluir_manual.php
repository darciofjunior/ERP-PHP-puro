<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$pis    = round(genericas::variavel(70), 2);
$cofins = round(genericas::variavel(61), 2);

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ITEM(NS) J� EXISTENTE.</font>";

if(!empty($_POST['id_nf_outra'])) {
    $sql = "SELECT `id_cliente` 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_cliente = $campos[0]['id_cliente'];
//Se o cadastro do Cliente estiver inv�lido, ent�o este tem que ser corrigido, antes de qualquer outra coisa
    $cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
    if($cadastro_cliente_incompleto == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('O CADASTRO DESTE CLIENTE EST� INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
        window.close()
    </Script>
<?
        exit;
    }
/********************************************************************************************************/
    $data_sys           = date('Y-m-d H:i:s');		
//Verifico se j� foi cadastrado algum Item anteriormente com a mesma Discrimina��o nessa NF ...
    $sql = "SELECT `id_nf_outra_item` 
            FROM `nfs_outras_itens` 
            WHERE `id_nf_outra` = '$_POST[id_nf_outra]' 
            AND `discriminacao` = '$_POST[txt_discriminacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Significa que este Item ainda n�o foi cadastrado p/ esta NF ...
        $sql = "INSERT INTO `nfs_outras_itens` (`id_nf_outra_item`, `id_nf_outra`, `id_unidade`, `referencia`, `discriminacao`, `id_classific_fiscal`, `origem_mercadoria`, `situacao_tributaria`, `qtde`, `valor_unitario`, `peso_unitario`, `ipi`, `icms`, `reducao`, `icms_intraestadual`, `iva`, `imposto_importacao`, `valor_cif`, `bc_icms_item`, `pis`, `cofins`, `bc_pis_cofins`, `despesas_aduaneiras`, `despesas_acessorias`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_nf_outra]', '$_POST[cmb_unidade]', '$_POST[txt_referencia]', '$_POST[txt_discriminacao]', '$_POST[cmb_classific_fiscal]', '$_POST[cmb_origem_mercadoria]', '$_POST[cmb_situacao_tributaria]', '$_POST[txt_qtde]', '$_POST[txt_valor_unitario]', '$_POST[txt_peso_unitario]', '$_POST[txt_ipi]', '$_POST[txt_icms]', '$_POST[txt_reducao]', '$_POST[txt_icms_intraestadual]', '$_POST[txt_iva]', '$_POST[txt_imposto_importacao]', '$_POST[txt_valor_cif]', '$_POST[txt_bc_icms_item]', '$_POST[txt_pis]', '$_POST[txt_cofins]', '$_POST[txt_bc_pis_cofins]', '$_POST[txt_despesas_aduaneiras]', '$_POST[txt_despesas_acessorias]', '$_POST[txt_observacao]', '$data_sys') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Significa que este Item j� foi cadastrado anteriormente ...
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_manual.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>&valor=<?=$valor;?>'
    </Script>
<?
}

//Busca de alguns Campos de NF p/ auxiliar na l�gica ... 
$sql = "SELECT c.`id_pais`, nfso.`id_cliente`, nfso.`id_cfop`, nfso.`id_nf_comp`, nfso.`id_nf_outra_comp`, 
        CONCAT(cfops.`cfop`, '.', cfops.`num_cfop`) AS numero_cfop 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
        LEFT JOIN `cfops` ON cfops.`id_cfop` = nfso.`id_cfop` 
        WHERE nfso.id_nf_outra = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_pais            = $campos[0]['id_pais'];
$id_cliente         = $campos[0]['id_cliente'];
$id_cfop            = $campos[0]['id_cfop'];
$id_nf_comp         = $campos[0]['id_nf_comp'];
$id_nf_outra_comp   = $campos[0]['id_nf_outra_comp'];
$numero_cfop        = $campos[0]['numero_cfop'];
?>
<html>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<body>
<?
/*************************************************************************/
//Se o cadastro do Cliente estiver inv�lido, ent�o este tem que ser corrigido, antes de qualquer outra coisa
    $cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
    if($cadastro_cliente_incompleto == 1) {
?>
	<Script Language = 'JavaScript'>
		alert('O CADASTRO DESTE CLIENTE EST� INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
		window.close()
	</Script>
<?
        exit;
    }
/********************************************************************************************************/
?>
<html>
<head>
<title>.:: Incluir Item(ns) ::.</title>
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
//Refer�ncia ...
    if(!texto('form', 'txt_referencia', '3', "-=!@������{} 1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM,.'��������������������������.,%&*$()@#<>���:;\/", 'REFER�NCIA', '1')) {
        return false
    }
//Discrimina��o ...
    if(!texto('form', 'txt_discriminacao', '3', '1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,.:*/_-|��()%$"� ', 'DISCRIMINA��O', '1')) {
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
/*********************************************************************************************************/
/***********************************************CFOP 3.101 e 3.102************************************************/
/*********************************************************************************************************/
    if(id_cfop == 161) {//Se for essa CFOP 3.101 ent�o o Sys n�o for�a o preenchimento do Valor Unit�rio ...
        if(document.form.txt_valor_unitario.value != '') {
            if(!texto('form', 'txt_valor_unitario', '1', '0123456789,.', 'VALOR', '2')) {
                return false
            }
        }
    }else {
//Valor Unit�rio ...
        if(!texto('form', 'txt_valor_unitario', '1', '0123456789,.', 'VALOR', '2')) {
            return false
        }
    }
/*********************************************************************************************************/	
//Peso Unit�rio ...
    if(document.form.txt_peso_unitario.value != '') {
        if(!texto('form', 'txt_peso_unitario', '1', '0123456789,.', 'PESO UNIT�RIO', '2')) {
            return false
        }
    }
//Classifica��o Fiscal ...
    if(!combo('form', 'cmb_classific_fiscal', '', 'SELECIONE A CLASSIFICA��O FISCAL !')) {
        return false
    }
//Origem da Mercadoria ...
    if(!combo('form', 'cmb_origem_mercadoria', '', 'SELECIONE A ORIGEM DA MERCADORIA !')) {
        return false
    }
//Situa��o Tribut�ria ...
    if(!combo('form', 'cmb_situacao_tributaria', '', 'SELECIONE UMA SITUA��O TRIBUT�RIA !')) {
        return false
    }
/*********************************************************************************************************/
/***********************************************CFOP 3.101************************************************/
/*********************************************************************************************************/
    if(id_cfop == 161) {//Se for essa CFOP 3.101 ent�o o Sys for�a o preenchimento desses valores abaixo ...
//IPI ...
        if(!texto('form', 'txt_ipi', '1', '0123456789,.', 'IPI', '2')) {
            return false
        }
//ICMS ...
        if(!texto('form', 'txt_icms', '1', '0123456789,.', 'ICMS', '2')) {
            return false
        }
//Valor CIF ...
        if(!texto('form', 'txt_valor_cif', '1', '0123456789,.', 'VALOR CIF', '2')) {
            return false
        }
//Esse Valor nunca poder� ficar em Branco, porque atrav�s deste eu c�lculo o Valor Unit�rio do Item e Valor do IPI ...
        if(document.form.txt_valor_cif.value == '0,00') {
            alert('VALOR DE CIF INV�LIDO !')
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
//Despesas Acess�rias ...
        if(document.form.txt_despesas_acessorias.value != '') {
            if(!texto('form', 'txt_despesas_acessorias', '1', '0123456789,.', 'DESPESA ACESSORIAS', '1')) {
                return false
            }
        }
    }
//Destravo esses campos p/ poder gravar no Banco de Dados ...
    document.form.txt_discriminacao.disabled        = false
    document.form.txt_valor_unitario.disabled       = false
    document.form.txt_bc_pis_cofins.disabled        = false
    document.form.txt_pis.disabled                  = false
    document.form.txt_cofins.disabled               = false
    document.form.txt_ipi.disabled                  = false
    document.form.txt_icms.disabled                 = false
    document.form.txt_reducao.disabled              = false
    document.form.txt_imposto_importacao.disabled   = false
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    return limpeza_moeda('form', 'txt_qtde, txt_valor_unitario, txt_peso_unitario, txt_ipi, txt_icms, txt_reducao, txt_icms_intraestadual, txt_iva, txt_imposto_importacao, txt_valor_cif, txt_bc_icms_item, txt_pis, txt_cofins, txt_bc_pis_cofins, txt_despesas_aduaneiras, txt_despesas_acessorias, ')
}

function calcular_valor_unitario() {
    var numero_cfop = '<?=$numero_cfop;?>'
    if(numero_cfop == '3.101' || numero_cfop == '3.102') { // Esse calculo s� ser� feito quando efetuarmos uma nota de importa��o...
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

    document.form.txt_pis.value     = (bc_pis_cofins * pis) / 100//C�lculo do PIs ...
    document.form.txt_cofins.value  = (bc_pis_cofins * cofins) / 100//C�lculo do Cofins ...
    
    document.form.txt_pis.value     = arred(document.form.txt_pis.value, 2, 1)
    document.form.txt_cofins.value  = arred(document.form.txt_cofins.value, 2, 1)
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nf_outra' value="<?=$_GET['id_nf_outra'];?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Refer�ncia:</b>
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Refer�ncia' size='30' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Discrimina��o:</b>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discrimina��o' size='55' maxlength='100' class='caixadetexto'>
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
                echo combos::combo($sql);
            ?>
            </select>
        </td>	
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde' title='Digite a Quantidade' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Unit�rio:</b>
        </td>
        <td>
            <?
                if($numero_cfop == '3.101' || $numero_cfop == '3.102') {
                    $aviso      = '<font color="red"><b>Valor calculado automaticamente</b></font>';
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }else {
                    $class      = 'caixadetexto';
                    $disabled   = '';
                }
            ?>
            <input type='text' name='txt_valor_unitario' title='Digite o Valor Unit�rio' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='25' maxlength='20' class='<?=$class;?>' <?=$disabled;?>>
            <?=$aviso;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Unit�rio:
        </td>
        <td>
            <input type='text' name='txt_peso_unitario' title='Digite o Peso Unit�rio' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" size='14' maxlength='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Classifica��o Fiscal:</b>
        </td>
        <td>
<!--O id_cfop � passado por par�metro porque ele � a prioridade principal p/ a Busca dos Impostos "IPI, ICMS" 
na Tabela de CFOP(s) e caso n�o exista imposto nessa tabela, ent�o eu busco os impostos mediante a Classifica��o 
selecionada pelo usu�rio na combo abaixo-->
            <select name='cmb_classific_fiscal' title='Selecione a Classifica��o Fiscal' onchange="window.parent.buscar_impostos.location = 'buscar_impostos.php?id_cfop=<?=$id_cfop;?>&cmb_classific_fiscal='+document.form.cmb_classific_fiscal.value" class='combo'>
            <?
                $sql = "SELECT `id_classific_fiscal`, `classific_fiscal` 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY `classific_fiscal` ";
                echo combos::combo($sql);
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
                        //Quando o Cliente for de um Pa�s Estrangeiro, o sistema j� sugere a Origem como sendo 1 - Estrangeira ...
                        $selected = ($id_pais != 31 && $indice == 1) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$id_origem_mercadoria."</option>";
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Situa��o Tribut�ria:</b>
        </td>
        <td>
            <select name='cmb_situacao_tributaria' title='Selecione a Situa��o Tribut�ria' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_situacao_tributaria  = array_sistema::situacao_tributaria();
                    foreach($vetor_situacao_tributaria as $indice => $id_situacao_tributaria) {
                        if($id_pais != 31) {
                            /*Quando o Cliente for de um Pa�s Estrangeiro e a Nota Fiscal for Complementar, 
                            sempre sugiro a Situa��o Tribut�ria como sendo 41 para n�o dar erro na Sefaz ...*/
                            if($id_nf_comp > 0 || $id_nf_outra_comp > 0) {
                                $selected = ($indice == 41) ? 'selected' : '';
                                /*Quando o Cliente for de um Pa�s Estrangeiro, o sistema j� sugere a ST como 
                                sendo 00 - "Tributada Integralmente" p/ que possamos nos creditar do ICMS 
                                dessa Importa��o ...*/
                            }else {
                                $selected = ($indice == 00) ? 'selected' : '';
                            }
                        }
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
            <input type='text' name='txt_ipi' title='Digite o IPI' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS %:
        </td>
        <td>
            <input type='text' name='txt_icms' title='Digite o ICMS' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Redu��o %:
        </td>
        <td>
            <input type='text' name='txt_reducao' title='Digite a Redu��o' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS IntraEstadual%:
        </td>
        <td>
            <input type='text' name='txt_icms_intraestadual' title='Digite o ICMS IntraEstadual' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA %:
        </td>
        <td>
            <input type='text' name='txt_iva' title='Digite o IVA' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='iframe' align='center'>
        <td colspan='2'>
            DADOS EXCLUSIVOS NF IMPORTA��O
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Imposto de Importa��o %:
        </td>
        <td>
            <input type='text' name='txt_imposto_importacao' title='Digite o Imposto de Importa��o' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor CIF:
        </td>
        <td>
            <input type='text' name='txt_valor_cif' title='Digite o Valor CIF' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_unitario()" size='12' maxlength='10' class='caixadetexto'>
            <b><i> -> (VA ou BC II pela SPEEDWAY)</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            BC ICMS Item:
        </td>
        <td>
            <input type='text' name='txt_bc_icms_item' title='Digite a BC ICMS Item' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            BC PIS / Cofins:
        </td>
        <td>
            <input type='text' name='txt_bc_pis_cofins' title='Digite a BC PIS / Cofins' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            PIS <b>(<?=number_format($pis, 2, ',', '.');?>%)</b>:
        </td>
        <td>
            <input type='text' name='txt_pis' title='Digite o Pis' size='15' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cofins <b>(<?=number_format($cofins, 2, ',', '.');?>%)</b>:
        </td>
        <td>
            <input type='text' name='txt_cofins' title='Digite o Cofins' size='15' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Despesas Aduaneiras:
        </td>
        <td>
            <input type='text' name='txt_despesas_aduaneiras' title='Digite as Despesas Aduaneiras' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <b><i> -> (Taxa Siscomex pela SPEEDWAY)</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Despesas Acess�rias / AFRMM:
        </td>
        <td>
            <input type='text' name='txt_despesas_acessorias' title='Digite a Despesas Acessorias' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' maxlength='12' class='caixadetexto'>
            <b><i> -> (Marinha Mercante pela SPEEDWAY)</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = 'incluir.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_referencia.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <tr>
        <td>
            <iframe name='buscar_impostos' id='buscar_impostos' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
        </td>
    </tr>
</form>
</table>
</body>
</html>
<pre>
<font color='darkgreen'><b>
CFOP(s) definidas no Cabe�alho da NF: 
</b></font>
<?
    $sql = "SELECT `id_cfop_revenda`, CONCAT(`cfop`, '.', `num_cfop`) AS cfop_industrial, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop_industrial_descritivo 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
    $campos_cfop = bancos::sql($sql);
    echo '<b>CFOP 1: </b>'.$campos_cfop[0]['cfop_industrial_descritivo'];

    if($campos_cfop[0]['id_cfop_revenda'] != 0) {
        $sql = "SELECT CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop_revenda_descritivo 
                FROM `cfops` 
                WHERE `id_cfop` = ".$campos_cfop[0]['id_cfop_revenda']." 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_cfop_revenda = bancos::sql($sql);
        echo '<br><b>CFOP 2: </b>'.$campos_cfop_revenda[0]['cfop_revenda_descritivo'];
    }
?>
</pre>