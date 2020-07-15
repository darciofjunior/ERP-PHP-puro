<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = '<font class="confirmacao">PRODUTO INSUMO CLONADO COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">PRODUTO INSUMO JÁ EXISTENTE.</font>';

if($passo == 1) {
//Busca de Dados do PI passado por parâmetro ...
    $sql = "SELECT * 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos = bancos::sql($sql);

//Vai ser utilizado pelo array em JavaScript + abaixo
    $sql = "SELECT `sigla` 
            FROM `unidades` 
            WHERE `ativo` = '1' ORDER BY `unidade` ";
    $campos_siglas = bancos::sql($sql);
    for($i = 0; $i < count($campos_siglas); $i++) $siglas.= $campos_siglas[$i]['sigla'].', ';
    $siglas = substr($siglas, 0, strlen($siglas) - 5);
?>
<html>
<title>.:: Clonar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/string.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Grupo
    if(!combo('form', 'cmb_grupo', '', 'SELECIONE O GRUPO !')) {
        return false
    }
//Unidade Insumo
    if(!combo('form', 'cmb_unidade', '', 'SELECIONE A UNIDADE INSUMO !')) {
        return false
    }
//Unidade de Conversão
    if(document.form.txt_unidade_conversao.value != '') {
        if(!texto('form', 'txt_unidade_conversao', '3', '1234567890,.', 'UNIDADE DE CONVERSÃO', '1')) {
            return false
        }
    }
//Discriminação
    if(!texto('form', 'txt_discriminacao', '3', '1234567890QWERTYUIOPÇLKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuik.lopç;áéíóúÁÉÍÓÚÂÊÎÔÛâêîôûãõÃÕÜüÀà!@#$%¨&*()(_-¹²³££¢¬§ªº°|\.<>;:{[}]/Ø= "', 'DISCRIMINAÇÃO', '1')) {
        return false
    }
//Classificação Fiscal
    if(!combo('form', 'cmb_classificacao_fiscal', '', 'SELECIONE UMA CLASSIFICAÇÃO FISCAL !')) {
        return false
    }
//Consumo Mensal
    if(!texto('form', 'txt_estoque_mensal', '1', '1234567890.,', 'CONSUMO MENSAL', '2')) {
        return false
    }
//Prazo Entrega
    if(!texto('form', 'txt_prazo_entrega', '1', '1234567890,.', 'PRAZO DE ENTREGA', '2')) {
        return false
    }
//Durabilidade Mínima
    if(document.form.txt_durabilidade_minima.value != '') {
        if(!texto('form', 'txt_durabilidade_minima', '1', '1234567890', 'DURABILIDADE MÍNIMA', '1')) {
            return false
        }
    }
//Quando o Grupo selecionado pelo usuário for igual a Aço, então força a preencher o tipo de Aço
    if(document.form.cmb_grupo.value == 5 && document.form.opt_tipo_produto[1].checked == false) {
        alert('SELECIONE O TIPO DE PRODUTO COMO SENDO AÇO !')
        document.form.opt_tipo_produto[1].checked = true
        habilitar()
        return false
    }
    if(document.form.opt_tipo_produto[1].checked == true) {//PI do Tipo AÇO ...
//Geometria Aço
        if(!combo('form', 'cmb_geometria_aco', '', 'SELECIONE A GEOMETRIA DO AÇO !')) {
            return false
        }
//Qualidade Aço
        if(!combo('form', 'cmb_qualidade_aco', '', 'SELECIONE A QUALIDADE DO AÇO !')) {
            return false
        }
//Bitola1 Aço
        if(!texto('form', 'txt_bitola1_aco', '1', '1234567890,.', 'BITOLA 1 AÇO', '1')) {
            return false
        }
//Bitola2 Aço
        if(document.form.txt_bitola2_aco.disabled == false) {
            if(!texto('form', 'txt_bitola2_aco', '1', '1234567890,.', 'BITOLA 2 AÇO', '1')) {
                return false
            }
        }
        document.form.txt_densidade.disabled = false
//Geometria Aço
        var geometria_aco = document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text
        if(geometria_aco == 'Q') {
            geometria = 'QUAD'
        }else if(geometria_aco == 'R') {
            geometria = 'RED'
        }else if(geometria_aco == 'X') {
            geometria = 'RET'
        }else if(geometria_aco == 'TUBO') {
            geometria = 'TUBO'
        }else if(geometria_aco == 'SX') {
            geometria = 'SEXT'
        }else if(geometria_aco == 'TR') {
            geometria = 'TRIANG'
        }
        
        if(!verificar_string(geometria, document.form.txt_discriminacao, 'DISCRIMINAÇÃO NÃO CONFERE COM A GEOMETRIA DO AÇO !')) {
            return false
        }
//Qualidade Aço
        var qualidade_aco = document.form.cmb_qualidade_aco[document.form.cmb_qualidade_aco.selectedIndex].text

        if(qualidade_aco != 'Outros') {
            if(!verificar_string(qualidade_aco, document.form.txt_discriminacao, 'DISCRIMINAÇÃO NÃO CONFERE COM A QUALIDADE DO AÇO !')) {
                return false
            }
        }
//Bitola 1
        var bitola1 = strtofloat(document.form.txt_bitola1_aco.value)
        if(!verificar_string(bitola1, document.form.txt_discriminacao, 'DISCRIMINAÇÃO NÃO CONFERE COM A BITOLA 1 !')) {
            return false
        }
//Bitola 2
        if(geometria_aco == 'X' || geometria_aco == 'TUBO') {
            var bitola2 = strtofloat(document.form.txt_bitola2_aco.value)
            if(!verificar_string(bitola2, document.form.txt_discriminacao, 'DISCRIMINAÇÃO NÃO CONFERE COM A BITOLA 2 !')) {
                return false
            }
            var bitola1 = eval(strtofloat(document.form.txt_bitola1_aco.value))
            var bitola2 = eval(strtofloat(document.form.txt_bitola2_aco.value))

            if(bitola1 <= bitola2) {
                alert('BITOLA INVÁLIDA !!! \n VALOR DA BITOLA 1 MENOR QUE O VALOR DA BITOLA 2 !')
                document.form.txt_bitola1_aco.focus()
                document.form.txt_bitola1_aco.select()
                return false
            }
//Aqui verifica na String a Posição da Bitola
            posicao_bitola1 = document.form.txt_discriminacao.value.indexOf(document.form.txt_bitola1_aco.value)
            posicao_bitola2 = document.form.txt_discriminacao.value.indexOf(document.form.txt_bitola2_aco.value)

            if(posicao_bitola2 < posicao_bitola1) {
                alert('DISCRIMINAÇÃO INVÁLIDA !!! \n VALOR DA BITOLA 1 MENOR QUE O VALOR DA BITOLA 2 !')
                document.form.txt_discriminacao.focus()
                document.form.txt_discriminacao.select()
                return false
            }
        }
    }
//Peso ...
    if(document.form.txt_peso.value != '') {
        if(!texto('form', 'txt_peso', '1', '1234567890.,', 'PESO', '2')) {
            return false
        }
    }
//Altura Interna ...
    if(document.form.txt_altura.value != '') {
        if(!texto('form', 'txt_altura', '1', '1234567890', 'ALTURA INTERNA', '1')) {
            return false
        }
    }
//Largura Interna ...
    if(document.form.txt_largura.value != '') {
        if(!texto('form', 'txt_largura', '1', '1234567890', 'LARGURA INTERNA', '1')) {
            return false
        }
    }
//Comprimento Interno ...
    if(document.form.txt_comprimento.value != '') {
        if(!texto('form', 'txt_comprimento', '1', '1234567890', 'COMPRIMENTO INTERNO', '2')) {
            return false
        }
    }
//Altura Externa ...
    if(document.form.txt_altura_externo.value != '') {
        if(!texto('form', 'txt_altura_externo', '1', '1234567890', 'ALTURA EXTERNA', '1')) {
            return false
        }
    }
//Largura Externa ...
    if(document.form.txt_largura_externo.value != '') {
        if(!texto('form', 'txt_largura_externo', '1', '1234567890', 'LARGURA EXTERNA', '1')) {
            return false
        }
    }
//Comprimento Externo ...
    if(document.form.txt_comprimento_externo.value != '') {
        if(!texto('form', 'txt_comprimento_externo', '1', '1234567890', 'COMPRIMENTO EXTERNO', '2')) {
            return false
        }
    }
//Prepara a discriminação p/ Minúscula para não invadir espaço nos PDFs ...
    document.form.txt_discriminacao.value = document.form.txt_discriminacao.value.toUpperCase()
    return limpeza_moeda('form', 'txt_peso, txt_estoque_mensal, txt_unidade_conversao, txt_bitola1_aco, txt_bitola2_aco, txt_densidade, ')   
}

function preencher_caixa() {
//Array de Siglas
    var siglas = new Array('<?=$siglas;?>')
    var sigla_selecionada = siglas[document.form.cmb_unidade.selectedIndex - 1]
    if(typeof(sigla_selecionada) == 'undefined') {
        document.form.txt_caixa_unidade_conversao.value = ''
    }else {
        document.form.txt_caixa_unidade_conversao.value = 'Un / '+sigla_selecionada
    }
}

function geometria_aco() {
    var geometria_aco = document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text
    if(geometria_aco == 'X' || geometria_aco == 'TUBO') {//Habilita p/ digitar 2ª Bitola ...
        document.form.txt_bitola2_aco.disabled = false
//Layout de Habilitado ...
        document.form.txt_bitola2_aco.className = 'caixadetexto'
    }else {//Outras Geometrias desabilita a 2ª Bitola ...
        document.form.txt_bitola2_aco.disabled = true
        document.form.txt_bitola2_aco.value = ''
//Layout de Desabilitado ...
        document.form.txt_bitola2_aco.className = 'textdisabled'
    }
}

function calcular_densidade() {
    if(document.form.txt_bitola1_aco.value != '') {
        var id_qualidade_aco = ''
        var geometria_aco = document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text
        var qualidade_aco = document.form.cmb_qualidade_aco.value
        var achou = 0, id_qualidade = '', perc_aco = ''
        for(i = 0; i < qualidade_aco.length; i++) {
            if(qualidade_aco.charAt(i) == '|') {
                achou = 1
            }else {
                if(achou == 0) {
                    id_qualidade_aco = id_qualidade_aco + qualidade_aco.charAt(i)
                }else {
                    perc_aco+= qualidade_aco.charAt(i)
                }
            }
        }
        
        document.form.hdd_qualidade_aco.value = id_qualidade_aco
        
        bitola1 = eval(strtofloat(document.form.txt_bitola1_aco.value))
        bitola2 = eval(strtofloat(document.form.txt_bitola2_aco.value))

        if(geometria_aco == 'Q') {//Quadrado
            if(qualidade_aco != '') {
                printar = 1
                resultado = Math.pow(bitola1 / 1000, 2) * 7850 * (1 + perc_aco / 100)
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else if(geometria_aco == 'R') {//Redondo
            if(qualidade_aco != '') {
                printar = 1
                resultado = Math.PI / 4 * (Math.pow(bitola1 / 1000, 2) * 7850) * (1 + perc_aco / 100)
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else if(geometria_aco == 'X') {//Chato
            if((qualidade_aco != '') && (typeof(bitola2) != 'undefined')) {
                resultado = (bitola1 * bitola2 * 7850) * (1 + perc_aco / 100) / 1000 / 1000
                printar = 1
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else if(geometria_aco == 'TUBO') {//Tubo
            if((qualidade_aco != '') && (typeof(bitola2) != 'undefined')) {
                resultado = ((Math.pow(bitola1 / 2,2) * Math.PI) - (Math.pow(bitola2 / 2,2) * Math.PI)) * 7850 * (1 + perc_aco / 100)
                printar = 1
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else if(geometria_aco == 'SX') {//Sextavado
            if(qualidade_aco != '') {
                printar = 1
                resultado = Math.pow(bitola1, 2) * 0.68 / 100 * (1 + perc_aco / 100)
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else if(geometria_aco == 'TR') {//Triangular ...
            if(qualidade_aco != '') {
                printar = 1
                resultado = Math.pow(bitola1 / 1000, 2) / 2 * 7850 * (1 + perc_aco / 100)
            }else {
                document.form.txt_densidade.value = ''
                printar = 0
            }
        }else {
            document.form.txt_densidade.value = ''
            printar = 0
        }
    }
//Escreve o resultado Final ...
    if(printar == 1) {
        document.form.txt_densidade.value = resultado
        document.form.txt_densidade.value = arred(document.form.txt_densidade.value, 3, 1)
    }
}
</Script>
<body onload='preencher_caixa();habilitar();calcular_densidade()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_qualidade_aco'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Clonar Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <select name='cmb_grupo' title='Selecione o Grupo' class='combo'>
            <?
                $sql = "SELECT `id_grupo`, `nome` 
                        FROM `grupos` 
                        WHERE `ativo` = '1' ";
                if($campos[0]['id_grupo'] == 9) {//Verifica se o Grupo é do tipo PRAC ...
                    $sql.= " ORDER BY `nome` " ;
                }else {//Não é do tipo PRAC ...
                    $sql.= " AND `id_grupo` <> '9' ORDER BY `nome` " ;
                }
                echo combos::combo($sql, $campos[0]['id_grupo']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Unidade Insumo:</b>
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade Insumo' onchange='preencher_caixa()' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `unidade` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `unidade` ";
                echo combos::combo($sql, $campos[0]['id_unidade']);
            ?>
            </select>
            &nbsp;
            <font title='Unidade de Conversão'>
                U.C.:
            </font>
            &nbsp;<input type='text' name='txt_unidade_conversao' title='Digite a Unidade de Conversão' maxlength='15' size='14' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            &nbsp;<input type='text' name='txt_caixa_unidade_conversao' maxlength='10' size='10' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Discriminação:</b>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' value='<?=$campos[0]['discriminacao'];?>' title='Discriminação' maxlength='255' size='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CTT:
        </td>
        <td>
            <select name='cmb_ctt' title='Selecione o CTT' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $id_ctt = $campos[0]['id_ctt'];
                    
                    $sql = "SELECT `id_ctt`, `codigo`, `aplicacao_usual`, `descricao` 
                            FROM `ctts` 
                            WHERE `ativo` = '1' ORDER BY `codigo` ";
                    $campos_ctts = bancos::sql($sql);
                    $linhas_ctts = count($campos_ctts);

                    $espacos = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    for($i = 0; $i < $linhas_ctts; $i++) {
    //Se o que estiver cadastro para esse Produto for igual ao da listagem que eu estou varrendo, então ...
                        if($id_ctt == $campos_ctts[$i]['id_ctt']) {
                ?>
                <option value="<?=$campos_ctts[$i]['id_ctt'];?>" selected>
                    <?=$campos_ctts[$i]['codigo'].' - '.$campos_ctts[$i]['aplicacao_usual'];?>
                </option>
                <option value="<?=$campos_ctts[$i]['id_ctt'];?>">
                    <?=$espacos.$campos_ctts[$i]['descricao'];?>
                </option>
        <?
                        }else {
        ?>
                <option value="<?=$campos_ctts[$i]['id_ctt'];?>">
                    <?=$campos_ctts[$i]['codigo'].' - '.$campos_ctts[$i]['aplicacao_usual'];?>
                </option>
                <option value="<?=$campos_ctts[$i]['id_ctt'];?>">
                    <?=$espacos.$campos_ctts[$i]['descricao'];?>
                </option>
        <?
                        }
                    }
        ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Classificação Fiscal:</b>
        </td>
        <td>
            <select name='cmb_classificacao_fiscal' title='Selecione uma Classificação Fiscal' class='combo'>
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
            <b>Estocagem:</b>
        </td>
        <td>
            <select name='cmb_estocagem' title='Selecione a Estocagem' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                if($campos[0]['estocagem'] == 'S') {
            ?>
                    <option value='S' selected>SIM</option>
                    <option value='N'>NÃO</option>
            <?
                }else {
            ?>
                    <option value='S'>SIM</option>
                    <option value='N' selected>NÃO</option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?
                $checked = ($campos[0]['cobrar_lote_min_custo'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_cobrar_lote_min_custo' value='1' title='Cobrar Lote Mínimo do Custo' id='label1' class='checkbox' <?=$checked;?>>
            <label for='label1'>Cobrar Lote Mínimo do Custo</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?
                $checked = ($campos[0]['credito_icms'] == 0) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_credito_icms' value='0' title='Crédito ICMS' id='label2' class='checkbox' <?=$checked;?>>
            <label for='label2'>Sem Crédito ICMS</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CMM do Sistema (Consumo Mensal):</b>
        </td>
        <td>
            <input type='text' name='txt_estoque_mensal' title='Digite o Consumo Mensal' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CMM Últimos <?=intval(genericas::variavel(71));?> Meses:</b>
        </td>
        <td>
            <?=compras_new::consumo_medio_mensal($_GET['id_produto_insumo']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_entrega' value="<?=number_format($campos[0]['prazo_entrega'], 2, ',', '.');?>" title='Digite o Prazo de Entrega' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> DDL
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Durabilidade Mínima:
        </td>
        <td>
            <input type='text' name='txt_durabilidade_minima' title='Digite a Durabilidade Mínima' size='15' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>&nbsp;Dias
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Produto:</b>
        </td>
        <td>
        <?
            $sql = "SELECT pia.*, qa.`valor_perc` 
                    FROM `produtos_insumos_vs_acos` pia 
                    INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
                    WHERE pia.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
            $campos_acos = bancos::sql($sql);
            if(count($campos_acos) == 0) {
        ?>
                <label for='outros'>Outros</label>
                <input type='radio' name='opt_tipo_produto' id='outros' value='1' onclick='habilitar()' checked>
                <label for='aco'>Aço</label>
                <input type='radio' name='opt_tipo_produto' value='2' onclick='habilitar()' id='aco'>
        <?
            }else {
                if($campos_acos[0]['id_geometria_aco'] == '') {
        ?>
                <label for='outros'>Outros</label>
                <input type='radio' name='opt_tipo_produto' value='1' onclick='habilitar()' id='outros' checked>
                <label for='aco'>Aço</label>
                <input type='radio' name='opt_tipo_produto' value='2' onclick='habilitar()' id='aco'>
        <?
                }else {
        ?>
                <label for='outros'>Outros</label>
                <input type='radio' name='opt_tipo_produto' value='1' onclick='habilitar()' id='outros'>
                <label for='aco'>Aço</label>
                <input type='radio' name='opt_tipo_produto' value='2' onclick='habilitar()' id='aco' checked>
        <?
                }
                $id_geometria_aco 	= $campos_acos[0]['id_geometria_aco'];
                $id_qualidade_aco 	= $campos_acos[0]['id_qualidade_aco'];
                $valor_perc		= $campos_acos[0]['valor_perc'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Geometria do Aço:</b>
        </td>
        <td>
            <select name='cmb_geometria_aco' title='Selecione a Geometria do Aço' onclick='geometria_aco()' onchange='geometria_aco();calcular_densidade()' class='combo'>
            <?
                $sql = "SELECT `id_geometria_aco`, `nome` 
                        FROM `geometrias_acos` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql, $id_geometria_aco);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qualidade Aço:</b>
        </td>
        <td>
            <select name='cmb_qualidade_aco' title='Selecione a Qualidade do Aço' onchange='calcular_densidade()' class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_qualidade_aco`, '|', `valor_perc`) AS dados_qualidade, `nome` 
                        FROM `qualidades_acos` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql, $id_qualidade_aco.'|'.$valor_perc);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Bitola 1 Aço: </b>
        </td>
        <td>
            <input type='text' name='txt_bitola1_aco' title='Digite a Bitola 1 Aço' size='10' maxlength='20' onkeyup="verifica(this,'moeda_especial', '2', '', event);calcular_densidade()" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bitola 2 Aço:
        </td>
        <td>
            <input type='text' name='txt_bitola2_aco' title='Digite a Bitola 2 Aço' size='10' maxlength='14' onkeyup="verifica(this,'moeda_especial', '2', '', event);calcular_densidade()" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Densidade Aço:
        </td>
        <td>
            <input type='text' name='txt_densidade' title='Densidade Aço' size='10' maxlength='14' class='textdisabled' disabled> Kg / m
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?
                $checked = ($campos[0]['caixa_coletiva_nfs'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_caixa_coletiva_nfs' value='1' title='Selecione a Caixa Coletiva de NF' id='label2' class='checkbox' <?=$checked;?>>
            <label for='label2'>Caixa Coletiva de NF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso:
        </td>
        <td>
            <input type='text' name='txt_peso' title='Digite o Peso' size='12' maxlenght='10' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class='caixadetexto'> Kg
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura Interna:
        </td>
        <td>
            <input type='text' name='txt_altura' title='Digite a Altura Interna' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Largura Interna:
        </td>
        <td>
            <input type='text' name='txt_largura' title='Digite a Largura Interna' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comprimento Interno:
        </td>
        <td>
            <input type='text' name='txt_comprimento' title='Digite o Comprimento Interno' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura Externa:
        </td>
        <td>
            <input type='text' name='txt_altura_externo' title='Digite a Altura Externa' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Largura Externa:
        </td>
        <td>
            <input type='text' name='txt_largura_externo' title='Digite a Largura Externa' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comprimento Externo:
        </td>
        <td>
            <input type='text' name='txt_comprimento_externo' title='Digite o Comprimento Externo' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
<?
    /***********************************************************************************************************/
    if(!empty($campos[0]['desenho_para_conferencia'])) {//Se o PI tiver desenho apresento o mesmo ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Conferência Atual:
        </td>
        <td>
            <img src = '../../../imagem/fotos_produtos_insumos/<?=$campos[0]['desenho_para_conferencia'];?>' width='180' height='120'>
        </td>
    </tr>
<?
    }
    /***********************************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
<?
	if($existe == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <marquee loop='100' scrollamount='5'>
                <font size='2' color='blue'><b>ESSE PRODUTO INSUMO ESTÁ RELACIONADO COM O PRODUTO ACABADO !</b></font>
            </marquee>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'clonar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');habilitar();calcular_densidade()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<!--Esse hidden guarda a discriminação original do BD e já serve para comparar com a caixa de discriminação 
está visível ao usuário e verifica se o usuário chegou a fazer alguma alteração nessa discriminação-->
<input type='hidden' name='txt_discriminacao_inicial' value='<?=$campos[0]['discriminacao'];?>'>
</form>
</body>
</html>
<!--Joguei essa function aqui embaixo, porque a variável geometria do aço foi startada no meio do código-->
<Script Language = 'JavaScript'>
function habilitar() {
<?
//Significa que carregou a tela como sendo um produto do Tipo Aço
    if($geometria_aco != '') {
?>
//Controle para a Seleção dos Produtos Normais
        if(document.form.opt_tipo_produto[0].checked == true) {
//Desabilita os campos
            document.form.cmb_geometria_aco.disabled    = true
            document.form.cmb_qualidade_aco.disabled    = true
            document.form.txt_bitola1_aco.disabled      = true
            document.form.txt_bitola2_aco.disabled      = true
            document.form.txt_densidade.disabled        = true
//Limpa os campos
            document.form.cmb_geometria_aco.value       = ''
            document.form.cmb_qualidade_aco.value       = ''
            document.form.txt_bitola1_aco.value         = ''
            document.form.txt_bitola2_aco.value         = ''
            document.form.txt_densidade.value           = ''
//Controle para a Seleção dos Produtos que são Aço
        }else {
            document.form.cmb_geometria_aco.disabled = false
            document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text = '<?=$geometria_aco;?>'
            if(document.form.cmb_geometria_aco.value == 'X' || document.form.cmb_geometria_aco.value == 'TB') {
                document.form.txt_bitola2_aco.disabled  = false
                document.form.txt_bitola2_aco.value     = '<?=$bitola2_aco;?>'
            }else {
                document.form.txt_bitola2_aco.disabled = true
            }
            document.form.cmb_qualidade_aco.disabled    = false
            document.form.cmb_qualidade_aco.value       = ''
            document.form.txt_bitola1_aco.disabled      = false
            document.form.txt_bitola1_aco.value         = ''
            document.form.txt_densidade.value           = '<?=$densidade_aco;?>'
        }
<?
    }else {
?>
        if(document.form.opt_tipo_produto[0].checked == true) {
            document.form.cmb_geometria_aco.disabled = true
            document.form.cmb_qualidade_aco.disabled = true
            document.form.txt_bitola1_aco.disabled = true
            document.form.txt_bitola2_aco.disabled = true
        }else {
            document.form.cmb_geometria_aco.disabled = false
            if(document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'X' || document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'TB') {
                document.form.txt_bitola2_aco.disabled = false
            }else {
                document.form.txt_bitola2_aco.disabled = true
            }
            document.form.cmb_qualidade_aco.disabled = false
            document.form.txt_bitola1_aco.disabled = false
        }
<?
    }
?>
}
</Script>
<pre>
<font color='red'><b>Observação:</b></font>

* Mantenha a ordem das palavras substituindo apenas as medidas na discriminação, pois facilitará 
  mais a busca desse produto no sistema.
</pre>
<?
}else if($passo == 2) {
    //SELECIONA TODOS OS CNPJS JA CADASTRADOS
    $txt_icms = str_replace('%', '', $txt_icms);
    $txt_ipi = str_replace('%', '', $txt_ipi);

    $data_sys = date('Y-m-d H:i:s');
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_insumos` 
            WHERE `discriminacao` = '$_POST[txt_discriminacao]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $chkt_credito_icms  = ($chkt_credito_icms == '') ? 1 : 0;
        $observacao         = strtolower($_POST['txt_observacao']);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_classificacao_fiscal   = (!empty($_POST[cmb_classificacao_fiscal])) ? "'".$_POST[cmb_classificacao_fiscal]."'" : 'NULL';
        $cmb_ctt                    = (!empty($_POST[cmb_ctt])) ? "'".$_POST[cmb_ctt]."'" : 'NULL';

//Aqui cria um novo produto insumo que acabou de ser clonado
        $sql                = "INSERT INTO `produtos_insumos` (`id_produto_insumo`, `id_unidade`, `id_classific_fiscal`, `id_ctt`, `estocagem`, `cobrar_lote_min_custo`, `unidade_conversao`, `discriminacao`, `credito_icms`, `caixa_coletiva_nfs`, `peso`, `altura`, `largura`, `comprimento`, `altura_externo`, `largura_externo`, `comprimento_externo`, `estoque_mensal`, `prazo_entrega`, `data_sys`, `observacao`, `id_grupo`, `durabilidade_minima`, `ativo`) VALUES (NULL, '$_POST[cmb_unidade]', $cmb_classificacao_fiscal, $cmb_ctt, '".$_POST['cmb_estocagem']."','$chkt_cobrar_lote_min_custo', '$_POST[txt_unidade_conversao]', '$_POST[txt_discriminacao]', '$chkt_credito_icms', '$chkt_caixa_coletiva_nfs', '$_POST[txt_peso]', '$_POST[txt_altura]', '$_POST[txt_largura]', '$_POST[txt_comprimento]', '$_POST[txt_altura_externo]', '$_POST[txt_largura_externo]', '$_POST[txt_comprimento_externo]', '$_POST[txt_estoque_mensal]', '$_POST[txt_prazo_entrega]', '$data_sys', '$observacao', '$_POST[cmb_grupo]', '$_POST[txt_durabilidade_minima]', '1') ";       
        $campos             = bancos::sql($sql);
        $id_produto_insumo  = bancos::id_registro();
//Se esse PI for do Tipo Aço, então atualizo a parte de Dados referentes a Aço ...
        if($_POST['opt_tipo_produto'] == 2) {
            $sql = "INSERT INTO `produtos_insumos_vs_acos` (`id_produto_insumo_vs_aco`, `id_geometria_aco`, `id_qualidade_aco`, `id_produto_insumo`, `bitola1_aco`, `bitola2_aco`, `densidade_aco`) VALUES (NULL, '$_POST[cmb_geometria_aco]', '$_POST[hdd_qualidade_aco]', '$id_produto_insumo', '$_POST[txt_bitola1_aco]', '$_POST[txt_bitola2_aco]', '$_POST[txt_densidade]') ";
            bancos::sql($sql);
        }
//Também gera Registro para a Tabela de Estoque ...
        $sql = "INSERT INTO `estoques_insumos` (`id_estoque_insumo`, `id_produto_insumo`, `qtde`, `data_atualizacao`) VALUES (NULL, '$id_produto_insumo', '0', '$data_sys') ";
        bancos::sql($sql);
        $valor = 2;
    }else {//P.I. já existente
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'clonar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
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
<title>.:: Produto(s) Insumo(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Produto(s) Insumo(s) p/ Clonar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'clonar.php?passo=1&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="#" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'clonar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>