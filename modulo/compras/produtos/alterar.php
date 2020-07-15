<?
require('../../../lib/segurancas.php');
/*Essa tela as vezes é aberta como Pop-Up quando isso acontecer não posso requisitar o arquivo de menu, 
porque senão teremos 2 menus na mesma tela ...*/
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/mda.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO INSUMO ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTE PRODUTO JÁ EXISTE OU FOI EXCLUÍDO E NÃO PODE SER MAIS UTILIZADO COM ESTA REFERÊNCIA.</font>";

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

//Aqui eu verifico se o PI também é um PA ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' 
            AND `id_produto_insumo` >= '0' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    if(count($campos_pa) == 1) $id_produto_acabado = $campos_pa[0]['id_produto_acabado'];
?>
<html>
<title>.:: Alterar Produtos Insumos ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
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
    if(document.form.cmb_grupo.value == 5 && document.form.opt_tipos[1].checked == false) {
        alert('SELECIONE O TIPO DE PRODUTO COMO SENDO AÇO !')
        document.form.opt_tipos[1].checked = true
        habilitar()
        return false
    }
    if(document.form.opt_tipos[1].checked == true) {//PI do Tipo AÇO ...
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
        }else if(geometria_aco == 'TB') {
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

function copiar_cmm_ult_12_meses(cmm_ult_12_meses) {
    document.form.txt_estoque_mensal.value = cmm_ult_12_meses
    document.form.fechar_pop.value = 1
    document.form.cmd_salvar.click()
}
</Script>
<body onLoad="preencher_caixa();habilitar();calcular_densidade()">
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()' enctype='multipart/form-data'>
<input type='hidden' name='id_produto_insumo' value="<?=$_GET['id_produto_insumo'];?>">
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<!--Essas variáveis servem p/ controlar o Nome do Produto Insumo-->
<input type='hidden' name='txt_resultado'>
<input type='hidden' name='hdd_qualidade_aco'>
<!--Significa que essa tela foi aberta como sendo um Pop-Up-->
<input type='hidden' name='pop_up' value="<?=$pop_up;?>">
<!--Controle de Tela-->
<input type='hidden' name='fechar_pop'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Produto Insumo
        </td>
    </tr>
	<tr class='linhanormal'>
		<td><b>Grupo:</b></td>
		<td>
			<select name="cmb_grupo" title='Grupo' class='combo'>
			<?
//Verifica se o grupo é do tipo PRAC
				if($campos[0]['id_grupo'] == 9) {
					$sql = "SELECT id_grupo, nome 
                                                FROM `grupos` 
                                                WHERE `ativo` = '1' ORDER BY nome " ;
//Não é do tipo PRAC
				}else {
					$sql = "Select id_grupo, nome 
                                                FROM `grupos` 
                                                WHERE `ativo` = '1' 
                                                AND `id_grupo` <> '9' ORDER BY nome " ;
				}
				echo combos::combo($sql, $campos[0]['id_grupo']);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Unidade Insumo:</b></td>
		<td>
			<select name="cmb_unidade" title="Unidade Insumo" onchange="preencher_caixa()" class='combo'>
				<?
					$sql = "SELECT id_unidade, unidade 
                                                FROM `unidades` 
                                                WHERE `ativo` = '1' ORDER BY unidade ";
					echo combos::combo($sql, $campos[0]['id_unidade']);
				?>
			</select>
			&nbsp;<font title="Unidade de Conversão">U.C.:</font>&nbsp;<input type='text' name="txt_unidade_conversao" value="<?=number_format($campos[0]['unidade_conversao'], 2, ',', '.');?>" title="Digite a Unidade de Conversão" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength="15" size="14" class='caixadetexto'>
			&nbsp;<input type='text' name="txt_caixa_unidade_conversao" maxlength="10" size="10" class="caixadetexto2" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Discriminação:</b></td>
		<td>
			<input type='text' name="txt_discriminacao" value="<?=$campos[0]['discriminacao'];?>" title="Discriminação" maxlength="255" size="50" class='caixadetexto'>
		</td>
	</tr>
        <?
            if($campos[0]['id_grupo'] == 8) {//Se for Grupo de Embalagem, então mostro essa linha abaixo ...
        ?>
	<tr class='linhanormal'>
		<td>Etiqueta Desta Embalagem</td>
		<td>
			<select name="cmb_etiqueta_embalagem" title="Etiqueta Desta Embalagem" class='combo'>
                        <?
                            $sql = "SELECT id_produto_insumo, discriminacao 
                                    FROM `produtos_insumos`
                                    WHERE `id_grupo` = 8
                                    AND `discriminacao` like '%etiqueta%' AND `ativo` = 1 ORDER BY discriminacao ";
                            echo combos::combo($sql, $campos[0]['id_produto_insumo_etiqueta']);
                        ?>
			</select>
		</td>
	</tr>
        <?
            }
        ?>
	<tr class='linhanormal'>
		<td>CTT:</td>
		<td>
			<select name="cmb_ctt" title="Selecione o CTT" class='combo'>
				<option value="" style="color:red">SELECIONE</option>
			<?
				$id_ctt = $campos[0]['id_ctt'];
				$sql = "SELECT id_ctt, codigo, aplicacao_usual, descricao 
                                        FROM `ctts` 
                                        WHERE `ativo` = '1' ORDER BY codigo ";
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
                <select name="cmb_classificacao_fiscal" title="Selecione uma Classificação Fiscal" class='combo'>
                <?
                    $sql = "SELECT id_classific_fiscal, classific_fiscal 
                            FROM `classific_fiscais` 
                            WHERE `ativo` = '1' ORDER BY classific_fiscal ";
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
		<td colspan="2">
			<?
				$checked = ($campos[0]['cobrar_lote_min_custo'] == 1) ? 'checked' : '';
			?>
			<input type="checkbox" name="chkt_cobrar_lote_min_custo" value="1" title="Cobrar Lote Mínimo do Custo" id="label1" class="checkbox" <?=$checked;?>>
			<label for="label1">Cobrar Lote Mínimo do Custo</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<?
                            $checked = ($campos[0]['credito_icms'] == 0) ? 'checked' : '';
			?>
			<input type="checkbox" name="chkt_credito_icms" value="0" title="Crédito ICMS" id="label2" class="checkbox" <?=$checked;?>>
			<label for="label2">Sem Crédito ICMS</label>
		</td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>CMM do Sistema (Consumo Mensal):</b>
            </td>
            <td>
            <?
                $estoque_mensal = $campos[0]['estoque_mensal'];
                if($estoque_mensal == '0.00') {
                    $estoque_mensal = 0;
                }else {
                    $estoque_mensal = number_format($estoque_mensal, 2, ',', '.');
                }
            ?>
                <input type='text' name="txt_estoque_mensal" value="<?=$estoque_mensal;?>" title="Digite o Consumo Mensal" size="12" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>CMM Últimos <?=intval(genericas::variavel(71));?> Meses:</b>
            </td>
            <td>
                <a href="javascript:copiar_cmm_ult_12_meses('<?=compras_new::consumo_medio_mensal($_GET['id_produto_insumo']);?>')" title="Copiar CMM Últimos 12 Meses" style='cursor:help' class='link'>
                    <?=compras_new::consumo_medio_mensal($_GET['id_produto_insumo']);?>
                </a>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Prazo de Entrega:</b>
            </td>
            <td>
                <input type='text' name="txt_prazo_entrega" value="<?=number_format($campos[0]['prazo_entrega'], 2, ',', '.');?>" title="Digite o Prazo de Entrega" size="12" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> DDL
            </td>
	</tr>
<?
//Quando o PI for do Tipo PA, então terá que exibir também o Peso do PA
	if(!empty($id_produto_acabado)) {
		$sql = "SELECT peso_unitario 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
		$campos_pa = bancos::sql($sql);
?>
	<tr class='linhanormal'>
		<td>Peso Unitário (Kg):</td>
		<td>
			<input type='text' name="txt_peso_unitario" value="<?=number_format($campos_pa[0]['peso_unitario'], 3, ',', '.');;?>" maxlength="9" size="15" title="Peso Unitário (Kg)" class="textdisabled" disabled>
		</td>
	</tr>
<?
	}
?>
	<tr class='linhanormal'>
		<td>Durabilidade Mínima:</td>
		<td>
			<input type='text' name="txt_durabilidade_minima" value="<?=$campos[0]['durabilidade_minima'];?>" title="Digite a Durabilidade Mínima" size="15" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>&nbsp;Dias
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Tipo de Produto:</b></td>
		<td>
		<?
			$sql = "SELECT pia.*, qa.valor_perc 
                                FROM `produtos_insumos_vs_acos` pia 
                                INNER JOIN `qualidades_acos` qa ON qa.id_qualidade_aco = pia.id_qualidade_aco 
                                WHERE pia.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
			$campos_acos = bancos::sql($sql);
			if(count($campos_acos) == 0) {
		?>
			<label for="outros">Outros</label><input type="radio" name="opt_tipos" value="1" onclick="habilitar()" id="outros" checked>
			<label for="aco">Aço</label><input type="radio" name="opt_tipos" value="2" onclick="habilitar()" id="aco">
		<?
			}else {
				if($campos_acos[0]['id_geometria_aco'] == 0) {
		?>
			<label for="outros">Outros</label><input type="radio" name="opt_tipos" value="1" onclick="habilitar()" id="outros" checked>
			<label for="aco">Aço</label><input type="radio" name="opt_tipos" value="2" onclick="habilitar()" id="aco">
		<?
				}else {
		?>
			<label for="outros">Outros</label><input type="radio" name="opt_tipos" value="1" onclick="habilitar()" id="outros">
			<label for="aco">Aço</label><input type="radio" name="opt_tipos" value="2" onclick="habilitar()" id="aco" checked>
		<?
				}
				$id_geometria_aco 	= $campos_acos[0]['id_geometria_aco'];
				$id_qualidade_aco 	= $campos_acos[0]['id_qualidade_aco'];
				$valor_perc			= $campos_acos[0]['valor_perc'];
				$bitola1_aco 		= ($campos_acos[0]['bitola1_aco'] == '0.00') ? '' : number_format($campos_acos[0]['bitola1_aco'], 2, ',', '.');
				$bitola2_aco 		= ($campos_acos[0]['bitola2_aco'] == '0.00') ? '' : number_format($campos_acos[0]['bitola2_aco'], 2, ',', '.');
				$densidade_aco 		= ($campos_acos[0]['densidade_aco'] == '0.000') ? '' : number_format($campos_acos[0]['densidade_aco'], 3, ',', '.');
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Geometria do Aço:</b></td>
		<td>
			<select name="cmb_geometria_aco" title="Selecione a Geometria do Aço" onclick="geometria_aco()" onchange="geometria_aco();calcular_densidade()" class='combo'>
			<?
				$sql = "SELECT id_geometria_aco, nome 
                                        FROM `geometrias_acos` 
                                        WHERE `ativo` = '1' ORDER BY nome ";
				echo combos::combo($sql, $id_geometria_aco);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Qualidade Aço:</b> </td>
		<td>
			<select name='cmb_qualidade_aco' title="Selecione a Qualidade do Aço" onchange="calcular_densidade()" class='combo'>
			<?
                            $sql = "SELECT CONCAT(id_qualidade_aco, '|', valor_perc) AS dados_qualidade, nome 
                                    FROM `qualidades_acos` 
                                    WHERE `ativo` = '1' ORDER BY nome ";
                            echo combos::combo($sql, $id_qualidade_aco.'|'.$valor_perc);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Bitola 1 Aço: </b> </td>
		<td>
			<input type='text' name="txt_bitola1_aco" value="<?=$bitola1_aco;?>" title="Digite a Bitola 1 Aço" onkeyup="verifica(this,'moeda_especial', '2', '', event);calcular_densidade()" size="10" maxlength="20" class='caixadetexto'> mm
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Bitola 2 Aço:</td>
		<td>
			<input type='text' name="txt_bitola2_aco" value="<?=$bitola2_aco;?>" title="Digite a Bitola 2 Aço" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_densidade()" size="10" maxlength="14" class='caixadetexto'> mm
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Densidade Aço:</td>
		<td>
			<input type='text' name="txt_densidade" value="<?=$densidade_aco;?>" title="Densidade Aço" size="10" maxlength="14" class="textdisabled" disabled> Kg / m
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<?
                            $checked = ($campos[0]['caixa_coletiva_nfs'] == 1) ? 'checked' : '';
			?>
			<input type="checkbox" name="chkt_caixa_coletiva_nfs" value="1" title="Selecione a Caixa Coletiva de NF" id="label3" class="checkbox" <?=$checked;?>>
			<label for="label3">Caixa Coletiva de NF</label>
		</td>
	</tr>
    <tr class='linhanormal'>
        <td>
            Peso:
        </td>
        <td>
            <input type='text' name="txt_peso" value="<?=number_format($campos[0]['peso'], 4, ',', '.');?>" title='Digite o Peso' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" size="12" maxlenght="10" class='caixadetexto'> Kg
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura Interna:
        </td>
        <td>
            <input type='text' name="txt_altura" value="<?=$campos[0]['altura'];?>" title='Digite a Altura Interna' size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Largura Interna:
        </td>
        <td>
            <input type='text' name="txt_largura" value="<?=$campos[0]['largura'];?>" title='Digite a Largura Interna' size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comprimento Interno:
        </td>
        <td>
            <input type='text' name="txt_comprimento" value="<?=$campos[0]['comprimento'];?>" title='Digite o Comprimento Interno' size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura Externo:
        </td>
        <td>
            <input type='text' name="txt_altura_externo" value="<?=$campos[0]['altura_externo'];?>" title="Digite a Altura Externo" size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Largura Externo:
        </td>
        <td>
            <input type='text' name="txt_largura_externo" value="<?=$campos[0]['largura_externo'];?>" title="Digite a Largura Externo" size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comprimento Externo:
        </td>
        <td>
            <input type='text' name="txt_comprimento_externo" value="<?=$campos[0]['comprimento_externo'];?>" title="Digite o Comprimento Externo" size="12" maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Conferência:
        </td>
        <td>
            <input type='file' name='txt_desenho_para_conferencia' title='Digite ou selecione o Caminho do Desenho para Conferência' size='80' class='caixadetexto'>
            <!--Este hidden será utilizado mais abaixo no passo 2 ...-->
            <input type='hidden' name='hdd_desenho_para_conferencia' value='<?=$campos[0]['desenho_para_conferencia'];?>'>
        </td>
    </tr>
<?
        if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo então ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Conferência Atual:
        </td>
        <td>
            <img src = '../../../imagem/fotos_produtos_insumos/<?=$campos[0]['desenho_para_conferencia'];?>' width='400' height='100'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ Conferência Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ Conferência Atual
            </label>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='64' rows='4' maxlength='255' title='Digite a Observação' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
<?
//Quando o PI for do Tipo PA, então terá que exibir também terá que exibir a Mensagem
	if(!empty($id_produto_acabado)) {
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
<?
    if($pop_up != 1) {//Se essa Tela foi aberta do modo Normal, então exibo esse Botão de Voltar ...
?>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
<?
    }
    //Quando o PI não for do Tipo PA, então poderá fazer as alterações normalmente no cadastro do PI
    if(empty($id_produto_acabado)) {
?>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='habilitar();calcular_densidade()' style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
<?
    }else {
        echo '&nbsp;';
    }
?>
        </td>
    </tr>
</table>
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
		if(document.form.opt_tipos[0].checked == true) {
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
//Layout de Desabilitado ...
			document.form.cmb_geometria_aco.className   = 'textdisabled'
			document.form.cmb_qualidade_aco.className   = 'textdisabled'
			document.form.txt_bitola1_aco.className     = 'textdisabled'
			document.form.txt_bitola2_aco.className     = 'textdisabled'
			document.form.txt_densidade.className       = 'textdisabled'
//Controle para a Seleção dos Produtos que são Aço
		}else {
			document.form.cmb_geometria_aco.disabled = false
			document.form.cmb_geometria_aco.value = '<?=$geometria_aco;?>'
//Layout de Habilitado ...
			document.form.cmb_geometria_aco.className = 'caixadetexto'
			if(document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'X' || document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'TB') {
				document.form.txt_bitola2_aco.disabled = false
				document.form.txt_bitola2_aco.value = '<?=$bitola2_aco;?>'
//Layout de Habilitado ...
				document.form.txt_bitola2_aco.className = 'caixadetexto'
			}else {
				document.form.txt_bitola2_aco.disabled = true
//Layout de Desabilitado ...
				document.form.txt_bitola2_aco.className = 'caixadetexto'
			}
			document.form.cmb_qualidade_aco.disabled = false
//Layout de Habilitado ...
			document.form.cmb_qualidade_aco.className = 'caixadetexto'

			document.form.cmb_qualidade_aco.value = '<?=$id_qualidade_aco.'|'.$perc_aco_bd;?>'
			document.form.txt_bitola1_aco.disabled = false
			document.form.txt_bitola1_aco.value = '<?=$bitola1_aco;?>'
//Layout de Habilitado ...
			document.form.txt_bitola1_aco.className = 'caixadetexto'
			document.form.txt_densidade.value = '<?=$densidade_aco;?>'
		}
<?
	}else {
?>
		if(document.form.opt_tipos[0].checked == true) {
			document.form.cmb_geometria_aco.disabled    = true
			document.form.cmb_qualidade_aco.disabled    = true
			document.form.txt_bitola1_aco.disabled      = true
			document.form.txt_bitola2_aco.disabled      = true
//Limpa os campos
			document.form.cmb_geometria_aco.value       = ''
			document.form.cmb_qualidade_aco.value       = ''
			document.form.txt_bitola1_aco.value         = ''
			document.form.txt_bitola2_aco.value         = ''
//Layout de Desabilitado ...
			document.form.cmb_geometria_aco.className   = 'textdisabled'
			document.form.cmb_qualidade_aco.className   = 'textdisabled'
			document.form.txt_bitola1_aco.className     = 'textdisabled'
			document.form.txt_bitola2_aco.className     = 'textdisabled'
		}else {
			document.form.cmb_geometria_aco.disabled = false
//Layout de Habilitado ...
			document.form.cmb_geometria_aco.className = 'caixadetexto'
			if(document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'X' || document.form.cmb_geometria_aco[document.form.cmb_geometria_aco.selectedIndex].text == 'TB') {
				document.form.txt_bitola2_aco.disabled = false
//Layout de Habilitado ...
				document.form.txt_bitola2_aco.className = 'caixadetexto'
			}else {
				document.form.txt_bitola2_aco.disabled  = true
//Layout de Desabilitado ...
				document.form.txt_bitola2_aco.className = 'textdisabled'
			}
			document.form.cmb_qualidade_aco.disabled        = false
//Layout de Habilitado ...
			document.form.cmb_qualidade_aco.className       = 'caixadetexto'
			document.form.txt_bitola1_aco.disabled          = false
//Layout de Habilitado ...
			document.form.txt_bitola1_aco.className         = 'caixadetexto'
		}
<?
	}
?>
}
</Script>
<?
}else if($passo == 2) {
    $txt_icms   = str_replace('%' ,'', $txt_icms);
    $txt_ipi    = str_replace('%', '', $txt_ipi);

    $sql = "SELECT id_produto_insumo 
            FROM `produtos_insumos` 
            WHERE `discriminacao` = '$_POST[txt_discriminacao]' 
            AND `id_produto_insumo` <> '$_POST[id_produto_insumo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
/*************************************************************/
/*Se o Usuário habilitou a opção de excluir o Desenho para Conferência ou então ele está fazendo 
a substituição de uma Imagem por outra, então eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_conferencia'])) {
            $endereco_desenho_para_conferencia = '../../../imagem/fotos_produtos_insumos/'.$_POST['hdd_desenho_para_conferencia'];
            unlink($endereco_desenho_para_conferencia);//Exclui a Imagem do Servidor ...
            $campo_desenho_para_conferencia = " , `desenho_para_conferencia` = '' ";
        }
        if($_FILES['txt_desenho_para_conferencia']['error'] == 1) {//Tratamento p/ Desenhos muito grandes ...
?>
    <Script Language = 'Javascript'>
        alert('ESSE DESENHO DE OP NÃO SERÁ UPADO !!!\n\nDESENHO DE OP MUITO PESADO P/ SUBIR NO SERVIDOR !')
    </Script>
<?
        }else {
            //Fazendo Upload da Imagem para o Servidor ...
            if(!empty($_FILES['txt_desenho_para_conferencia']['type'])) {
                //Tratamento com a Imagem 
                switch ($_FILES['txt_desenho_para_conferencia']['type']) {
                    case 'image/gif':
                    case 'image/pjpeg':
                    case 'image/jpeg':
                    case 'image/x-png':
                    case 'image/png':
                    case 'image/bmp':
                        $desenho_para_conferencia = copiar::copiar_arquivo('../../../imagem/fotos_produtos_insumos/', $_FILES['txt_desenho_para_conferencia']['tmp_name'], $_FILES['txt_desenho_para_conferencia']['name'], $_FILES['txt_desenho_para_conferencia']['size'], $_FILES['txt_desenho_para_conferencia']['type'], '2');
                    break;
                    default:
                        //echo "Não é possivel copiar a imagem";
                    break;
                }
            }
            $campo_desenho_para_conferencia = ", `desenho_para_conferencia` = '$desenho_para_conferencia' ";
        }
        $chkt_credito_icms          = ($chkt_credito_icms == '') ? 1 : 0;
        $data_sys                   = date('Y-m-d H:i:s');
        $observacao                 = strtolower($_POST['txt_observacao']);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_classificacao_fiscal   = (!empty($_POST[cmb_classificacao_fiscal])) ? "'".$_POST[cmb_classificacao_fiscal]."'" : 'NULL';
        $cmb_ctt                    = (!empty($_POST[cmb_ctt])) ? "'".$_POST[cmb_ctt]."'" : 'NULL';
        $cmb_etiqueta_embalagem     = (!empty($_POST[cmb_etiqueta_embalagem])) ? "'".$_POST[cmb_etiqueta_embalagem]."'" : 'NULL';
        
//Atualização do PI normalmente ...
        $sql = "UPDATE `produtos_insumos` SET `id_unidade` = '$_POST[cmb_unidade]', `id_classific_fiscal` = $cmb_classificacao_fiscal, `estocagem` = '$_POST[cmb_estocagem]',`id_ctt` = $cmb_ctt, `id_produto_insumo_etiqueta` = $cmb_etiqueta_embalagem, `cobrar_lote_min_custo` = '$chkt_cobrar_lote_min_custo', `unidade_conversao` = '$_POST[txt_unidade_conversao]', `discriminacao` = '$_POST[txt_discriminacao]', `credito_icms` = '$chkt_credito_icms', `caixa_coletiva_nfs` = '$chkt_caixa_coletiva_nfs', `peso` = '$_POST[txt_peso]', `altura` = '$_POST[txt_altura]', `largura` = '$_POST[txt_largura]', `comprimento` = '$_POST[txt_comprimento]', `altura_externo` = '$_POST[txt_altura_externo]', `largura_externo` = '$_POST[txt_largura_externo]', `comprimento_externo` = '$_POST[txt_comprimento_externo]', `estoque_mensal` = '$_POST[txt_estoque_mensal]', `prazo_entrega` = '$_POST[txt_prazo_entrega]', `data_sys` = '$data_sys', `observacao` = '".addslashes($observacao)."', `id_grupo` = '$_POST[cmb_grupo]', `durabilidade_minima` = '$_POST[txt_durabilidade_minima]' $campo_desenho_para_conferencia WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
        bancos::sql($sql);

        if($_POST['opt_tipos'] == 2) {//Se for aço ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_insumos_vs_acos` 
                    WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                $sql = "INSERT INTO `produtos_insumos_vs_acos` (`id_geometria_aco`, `id_qualidade_aco`, `id_produto_insumo`, `bitola1_aco`, `bitola2_aco`, `densidade_aco`) VALUES ('$_POST[cmb_geometria_aco]', '$_POST[hdd_qualidade_aco]', '$_POST[id_produto_insumo]', '$_POST[txt_bitola1_aco]', '$_POST[txt_bitola2_aco]', '$_POST[txt_densidade]') ";
                bancos::sql($sql);
            }else {
                $sql = "UPDATE `produtos_insumos_vs_acos` set `id_geometria_aco` = '$_POST[cmb_geometria_aco]', `id_qualidade_aco` = '$_POST[hdd_qualidade_aco]', `bitola1_aco` = '$_POST[txt_bitola1_aco]', `bitola2_aco` = '$_POST[txt_bitola2_aco]', `densidade_aco` = '$_POST[txt_densidade]' where `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
                bancos::sql($sql);
            }
        }else {//se nao for aço e for outros
            $sql = "DELETE FROM `produtos_insumos_vs_acos` WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
            bancos::sql($sql);
        }
//Se essa variável não for vazia, significa que o PA tem relação com o PI
        if(!empty($_POST['id_produto_acabado'])) {
            $sql = "UPDATE `produtos_acabados` SET `id_unidade` = '$_POST[cmb_unidade]', `discriminacao` = '$_POST[txt_discriminacao]', `data_sys` = '$data_sys' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            bancos::sql($sql);
        }
        $valor = 2;
    }else {
        $valor = 3;
    }
/**************************Redirecionamento de Tela**************************/
//Significa que essa tela é um Pop-Up, sendo assim ele exibe o botão de Fechar
    if($pop_up == 1) {
/*Significa que foi acionada a ferramentinha de copiar o CMM do Sistema, e sendo assim ele fecha o 
Pop-Up de dentro do Nível de Estoque automaticamente*/
        if($fechar_pop == 1) {
?>
	<Script Language = 'Javascript'>
            /*Atualizando a Tela de Itens do Nível de Estoque ...
             
             Como essa tela tem muitos objetos, eu faço o Sistema clicar sozinho nesse botão 'cmd_pesquisar' 
             p/ que não perder os parâmetros de Filtro ...*/
            top.opener.parent.itens.document.form_cabecalho.cmd_pesquisar.click()
            window.close()
	</Script>
<?
        }else {
?>
	<Script Language = 'Javascript'>
            /*Atualizando a Tela de Itens do Nível de Estoque ...
             
             Como essa tela tem muitos objetos, eu faço o Sistema clicar sozinho nesse botão 'cmd_pesquisar' 
             p/ que não perder os parâmetros de Filtro ...*/
            top.opener.parent.itens.document.form_cabecalho.cmd_pesquisar.click()
/*Levo o parâmetro de pop_up igual a 1, p/ q o Sistema não abra esse arquivo como sendo uma Tela Normal, 
evitando erro de redirecionamento da Tela, após a atualização dos dados do Produto Insumo*/
            window.location = 'alterar.php?passo=1&id_produto_insumo=<?=$_POST['id_produto_insumo'];?>&pop_up=1&valor=<?=$valor;?>'
	</Script>
<?
        }
    }else {
?>
	<Script Language = 'Javascript'>
            window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
	</Script>
<?
    }
/****************************************************************************/
}else if($passo == 3) {//Equivale a Atualização das Classificações Fiscais ...
    foreach($_POST['hdd_produto_insumo'] as $indice => $id_produto_insumo) {
//Atualiza a Classificação Fiscal do PI no Loop ...
        $sql = "UPDATE `produtos_insumos` SET `id_classific_fiscal` = '".$_POST['cmb_classificacao_fiscal'][$indice]."' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=2'
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
<title>.:: Alterar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function copiar_classificacao_fiscal() {
    var elementos = document.form.elements
    if(elementos['cmb_classificacao_fiscal[]'].length > 1) {
        var indice = 1
        for(i = 0; i < elementos.length; i++) {
            if(elementos[i].name == 'cmb_classificacao_fiscal[]' && typeof(elementos['cmb_classificacao_fiscal[]'][indice]) != 'undefined') {
                elementos['cmb_classificacao_fiscal[]'][indice].value = elementos['cmb_classificacao_fiscal[]'][0].value
                indice++
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=3';?>' method='post'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='18'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            Alterar Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            <font title='Unidade de Conversão' style='cursor:help'>
                U. C.
            </font>
        </td>
        <td>
            <font title='Unidade Insumo' style='cursor:help'>
                U. I.
            </font>
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Clas. <br>Fiscal
        </td>
        <td>
            Estocagem
        </td>
        <td>
            Créd. <br>ICMS
        </td>
        <td>
            CMM
        </td>
        <td>
            Pz. <br>Entr.
        </td>
        <td>
            Durab. <br>Mínima
        </td>
        <td>
            Geom. <br>Aço
        </td>
        <td>
            Qual. <br>Aço
        </td>
        <td>
            <font title="Bitola 1" style='cursor:help'>
                Bit. 1
            </font>
        </td>
        <td>
            <font title="Bitola 2" style='cursor:help'>
                Bit. 2
            </font>
        </td>
        <td>
            Dens. <br>Aço
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
		$url = "window.location = 'alterar.php?passo=1&id_produto_insumo=".$campos[$i]['id_produto_insumo']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            <input type="hidden" name="hdd_produto_insumo[]" value="<?=$campos[$i]['id_produto_insumo'];?>">
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
            <?=number_format($campos[$i]['unidade_conversao'], 2, ',', '.');?>
        </td>
        <td onclick="<?=$url;?>">
            <?=$campos[$i]['sigla'];?>
        </td>
        <td onclick="<?=$url;?>">
            <?=$campos[$i]['referencia'];?>
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <select name="cmb_classificacao_fiscal[]" title="Selecione uma Classificação Fiscal" class='combo'>
            <?
                $sql = "SELECT id_classific_fiscal, classific_fiscal 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY classific_fiscal ";
                echo combos::combo($sql, $campos[$i]['id_classific_fiscal']);
            ?>
            </select>
            <?
                if($i == 0) {//Só mostra a Seta na 1ª Linha ...
            ?>
            <img src="../../../imagem/seta_abaixo.gif" width="12" height="12" title="Copiar Classificação Fiscal" alt="Copiar Classificação Fiscal" onclick="copiar_classificacao_fiscal()">
            <?
                }
            ?>
        </td>
        <td>
        <?
            if($campos[$i]['estocagem'] == 'S') {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['credito_icms'] == 0) {
                echo 'Não';
            }else {
                echo 'Sim';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['estoque_mensal'] == '0.00') {
                echo 0;
            }else {
                echo number_format($campos[$i]['estoque_mensal'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['prazo_entrega'] == 0) {
                echo '0';
            }else {
                echo str_replace('.', ',', $campos[$i]['prazo_entrega']);
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['durabilidade_minima'];?>
        </td>
        <td>
        <?
            $sql = "SELECT pia.bitola1_aco, pia.bitola2_aco, pia.densidade_aco, ga.nome as geometria_aco, qa.nome 
                    FROM `produtos_insumos_vs_acos` pia 
                    INNER JOIN `geometrias_acos` ga on ga.id_geometria_aco = pia.id_geometria_aco 
                    INNER JOIN `qualidades_acos` qa on qa.id_qualidade_aco = pia.id_qualidade_aco 
                    WHERE pia.id_produto_insumo = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_acos = bancos::sql($sql);
            if(count($campos_acos) == 1) {
                $geometria_aco 		= $campos_acos[0]['geometria_aco'];
                $qualidade_aco 		= $campos_acos[0]['nome'];
                $bitola1_aco 		= ($campos_acos[0]['bitola1_aco'] == '0.00') ? '' : number_format($campos_acos[0]['bitola1_aco'], 2, ',', '.');
                $bitola2_aco 		= ($campos_acos[0]['bitola2_aco'] == '0.00') ? '' : number_format($campos_acos[0]['bitola2_aco'], 2, ',', '.');
                $densidade_aco 		= ($campos_acos[0]['densidade_aco'] == '0.000') ? '' : number_format($campos_acos[0]['densidade_aco'], 3, ',', '.');
            }else {
                $geometria_aco = '';
                $qualidade_aco = '';
                $bitola1_aco = '';
                $bitola2_aco = '';
                $densidade_aco = '';
            }
            echo $geometria_aco;
        ?>
        </td>
        <td>
            <?=$qualidade_aco;?>
        </td>
        <td>
            <?=$bitola1_aco;?>
        </td>
        <td>
            <?=$bitola2_aco;?>
        </td>
        <td>
            <?=$densidade_aco;?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'alterar.php'" class='botao'>
            <input type='submit' name="cmd_atualizar" value="Atualizar" title="Atualizar" style="color:brown" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>