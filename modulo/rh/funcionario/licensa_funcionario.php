<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>NÃO EXISTE NENHUM FUNCIONÁRIO CADASTRADO.</font>";

/*Listagem de Todos os Funcionários que ainda estão trabalhando*/
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
$sql = "SELECT c.`cargo`, d.`id_departamento`, d.`departamento`, e.`nomefantasia`, f.`id_funcionario`, f.`nome` 
        FROM `funcionarios` f 
        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
        WHERE f.`status` < '3' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY d.`departamento`, f.`nome` ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {//Não encontrou nenhum funcionário ...
    echo $mensagem[1];
    exit;
}
?>
<html>
<head>
<title>.:: Relação de Licença de Férias / Banco de Horas ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tratamento com os objetos antes de gravar BD ...
	var elementos = document.form.elements
//Aqui eu verifico se existe pelo menos 1 Hora Extra selecionada p/ gravar no BD ...
	var selecionado = 0
	var id_funcs_selecionados = ''
//Significa que está tela foi carregada com apenas 1 linha ...
	if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
		if(elementos['chkt_funcionario[]'].checked == true) {
			selecionado++
			id_funcs_selecionados = elementos['chkt_funcionario[]'].value + ','
		}
	}else {
		for(var i = 0; i < elementos.length; i++) {
			if(elementos['chkt_funcionario[]'][i] == '[object HTMLInputElement]' || elementos['chkt_funcionario[]'][i] == '[object]') {
				if(elementos['chkt_funcionario[]'][i].checked == true) {
					selecionado++
					id_funcs_selecionados+= elementos['chkt_funcionario[]'][i].value + ','
				}
			}
		}
	}
//Se não tiver nenhum funcionário selecionado, então retorno uma Mensagem p/ o usuário ...
	if(selecionado == 0) {
		alert('SELECIONE UM FUNCIONÁRIO P/ GERAR A LICENÇA !')
		return false
	}
	
	if(document.form.datas.value == '') {//Se não tiver nenhuma Data selecionada ...
		alert('SELECIONE UMA DATA P/ GERAR A LICENÇA !')
		document.form.cmd_incluir_datas.focus()
		return false
	}
        //Motivo ...
        if(!texto('form', 'txt_motivo', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZáéíóúÁÉÍÓÚãõÃÕâêîôûÂÊÎÔÛçÇ()-_[]{} ', 'MOTIVO', '2')) {
            return false
        }
//Aqui eu trato a variável id_funcs_selecionados p/ não furar o SQL no Relatório do PDF em PHP ... 
	id_funcs_selecionados = id_funcs_selecionados.substr(0, id_funcs_selecionados.length - 1)
	document.form.hdd_funcs_selecionados.value = id_funcs_selecionados
	document.form.action = 'relatorios/relatorio.php'
	document.form.target = 'CONSULTAR'
	nova_janela('relatorios/relatorio.php', 'CONSULTAR', 'F')
}

function selecionar_departamento(indice_departamento, indice_funcionario, id_departamento) {
	var elementos = document.form.elements
	var procedimento = ''
//Significa que está tela foi carregada com apenas 1 linha ...
	if(typeof(elementos['chkt_departamento[]'][0]) == 'undefined') {
		if(elementos['chkt_departamento[]'] == '[object HTMLInputElement]' || elementos['chkt_departamento[]'] == '[object]') {
/*Se o checkbox Principal do Departamento estiver selecionado, terá que selecionar todos os departamentos
daquele grupo ...*/
			if(elementos['chkt_departamento[]'].checked == true) {
				elementos['chkt_funcionario[]'].checked = true
/*Se o checkbox Principal do Departamento estiver desmarcado, irá desmarcar todos os departamentos
daquele grupo ...*/
			}else {
				elementos['chkt_funcionario[]'].checked = false
			}
		}
//Mais de 1 linha ...
	}else {
		if(elementos['chkt_departamento[]'][indice_departamento] == '[object HTMLInputElement]' || elementos['chkt_departamento[]'][indice_departamento] == '[object]') {
/*Se o checkbox Principal do Departamento estiver selecionado, terá que selecionar todos os departamentos
daquele grupo ...*/
			if(elementos['chkt_departamento[]'][indice_departamento].checked == true) {
				procedimento = true
//Layout de Habilitado
				cor_fonte = 'Brown'
				cor_fundo = '#FFFFFF'
				situacao = false
/*Se o checkbox Principal do Departamento estiver desmarcado, irá desmarcar todos os departamentos
daquele grupo ...*/
			}else {
				procedimento = false
//Layout de Desabilitado
				cor_fonte = 'gray'
				cor_fundo = '#FFFFE1'
				situacao = true
			}

			for(var i = indice_funcionario; i < elementos.length; i++) {
/*Enquanto o Departamento do Loop for igual ao Departamento Corrente do Hidden que eu passei por parâmetro, 
então eu vou marcando os funcionários ...*/
				if(elementos['hdd_departamento[]'][i] == '[object HTMLInputElement]' || elementos['hdd_departamento[]'][i] == '[object]') {
					if(elementos['hdd_departamento[]'][i].value == id_departamento) {
						elementos['chkt_funcionario[]'][i].checked = procedimento
					}
				}
			}
		}
	}
}

function selecionar_funcionario(indice) {
	var elementos = document.form.elements
//Significa que está tela foi carregada com apenas 1 linha ...
	if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
		if(elementos['chkt_funcionario[]'] == '[object HTMLInputElement]' || elementos['chkt_funcionario[]'] == '[object]') {
			if(elementos['chkt_funcionario[]'].checked == true) {//Se checado, então desmarca ...
				elementos['chkt_funcionario[]'].checked = false
			}else {//Se não estiver checado então eu marco ...
				elementos['chkt_funcionario[]'].checked = true
			}
		}
//Mais de 1 linha ...
	}else {
		if(elementos['chkt_funcionario[]'][indice] == '[object HTMLInputElement]' || elementos['chkt_funcionario[]'][indice] == '[object]') {
			if(elementos['chkt_funcionario[]'][indice].checked == true) {//Se checado, então desmarca ...
				elementos['chkt_funcionario[]'][indice].checked = false
			}else {//Se não estiver checado então eu marco ...
				elementos['chkt_funcionario[]'][indice].checked = true
			}
		}
	}
}

function incluir_datas(qtde_datas) {
//Aqui eu verifico se a opção de Meio Dia está selecionada ...
	if(document.form.chkt_meio_dia.checked == true) {//Se estiver checado ...
		if(document.form.datas.value == '') {//Não existem datas selecionadas ...
			nova_janela('../../../calendario/calendario.php?campo=txt_data_corrente&tipo_retorno=1&caixa_auxiliar=txt_data_corrente', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')
		}else {
			alert('A OPÇÃO LICENÇA DE ½ (MEIO) DIA ESTÁ SELECIONADA, ENTÃO SÓ PERMITIDA A INCLUSÃO P/ UMA ÚNICA DATA !!! CASO DESEJE INCLUIR MAIS DATA(S), DESMARQUE ESSA OPÇÃO !')
			return false
		}
	}else {//Caso a opção não esteje selecionada, então posso incluir N datas ...
		nova_janela('../../../calendario/calendario.php?campo=txt_data_corrente&tipo_retorno=1&caixa_auxiliar=txt_data_corrente', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')
	}
}

function atualizar_datas() {
	if(document.form.datas.value != '') {//Se já existir alguma Data ...
		document.form.datas.value+= ',' + document.form.txt_data_corrente.value		
	}else {//Se ainda não existe nenhuma data ...
		document.form.datas.value = document.form.txt_data_corrente.value	
	}
	document.form.action = 'licensa_funcionario.php'
	document.form.target = '_self'
	document.form.submit()
}

function excluir_datas(remover) {
	var resposta = confirm('TEM CERTEZA DE QUE DESEJA REMOVER ESSA DATA ?')
	if(resposta == true) {
		document.form.datas.value = document.form.datas.value.replace(remover, '')
		document.form.action = 'licensa_funcionario.php'
		document.form.target = '_self'
		document.form.submit()
	}
}

function controle_meio_dia(qtde_datas) {
	if(document.form.chkt_meio_dia.checked == true) {//Se estiver checado ...
//Aqui eu verifico se já foi selecionado uma Data p/ poder gerar a meia licença do func ...
		if(document.form.datas.value == '') {
			alert('SELECIONE UMA DATA P/ GERAR A LICENÇA DE ½ (MEIO) DIA !')
			document.form.chkt_meio_dia.checked = false
			document.form.cmd_incluir_datas.focus()
			return false
		}else {
			if(qtde_datas > 1) {
				alert('QUANTIDADE DE DATA(S) INVÁLIDA(S) !!!\nSELECIONE APENAS UMA DATA P/ GERAR A LICENÇA DE ½ (MEIO) DIA !')
				document.form.chkt_meio_dia.checked = false
				return false
			}
		}
	}
}
</Script>
</head>
<body>
<form name='form' method='post' onsubmit="return validar()">
<?
//Transformo o campo datas em array ...
if(!empty($datas)) {
	$ultimo_digito = substr($datas, strlen($datas) - 1, 1);
//Macete (rs) ...
	if($ultimo_digito == ',') $datas = substr($datas, 0, strlen($datas) - 1);
	$array_datas = explode(',', $datas);
	$array_datas = array_unique($array_datas);//Retiro do Vetor os elementos duplicados ...
	if(count($array_datas) == 1) {//Significa que o array está com apenas 1 elemento ...
            $datas = $array_datas[0]; 
	}else {//O array está com mais de 1 elemento ...
            sort($array_datas);//Ordena o array de Datas ...
            $datas = implode(',', $array_datas);
	}
}
?>
<!--Controle de Tela-->
<input type='hidden' name='datas' value='<?=$datas;?>'>
<input type='hidden' name='txt_data_corrente' onclick='atualizar_datas()'>
<input type='hidden' name='hdd_funcs_selecionados'>
<!--****************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Relação de Licença de Férias / Banco de Horas
            - 
            <input type='button' name='cmd_incluir_datas' value='Incluir Datas' title='Incluir Datas' onclick="incluir_datas('<?=count(explode(',', $datas));?>')" class='botao'>
            <br/>
            <?
                if(!empty($chkt_meio_dia)) {
                    $checked_licensa = (empty($datas)) ? '' : 'checked';
                }else {
                    $checked_licensa = '';
                }
            ?>
            <input type="checkbox" name="chkt_meio_dia" value="1" title="½ (Meio Dia)" id="meio_dia" onclick="controle_meio_dia('<?=count(explode(',', $datas));?>')" class="checkbox" <?=$checked_licensa;?>>
            <label for="meio_dia">½ (Meio Dia)</label>
            &nbsp;-&nbsp;Motivo:
            <input type='text' name='txt_motivo' value='<?=$_POST['txt_motivo']?>' size='65' maxlength='50' title='Digite o Motivo' class='caixadetexto'>
        </td>
    </tr>
<?
/*************************************************************************/
//Exibo essa linha quando existir pelo menos 1 data ...
	if(!empty($datas)) {
?>
    <tr class="linhacabecalho">
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='yellow' size='-1'>
                Datas:  
            </font> 
            <?
//Transformo o campo datas em array p/ disparar os seus valores no loop ...
                $array_datas = explode(',', $datas);
                for($i = 0; $i < count($array_datas); $i++) {
    //Enquanto não chegar no último elemento, eu vou imprimindo a vírgula ...
                        if($i + 1 != count($array_datas)) {
                                $virgula = ',';
                        }else {
                                $virgula = '';
                        }
                        echo '&nbsp;'.$array_datas[$i];
    ?>
        &nbsp;<img src = "../../../imagem/menu/excluir.png" border="0" title="Excluir Data" alt="Excluir Data" onClick="excluir_datas('<?=$array_datas[$i].$virgula;?>')">
    <?
                        echo $virgula;
                }
            ?>
        </td>
    </tr>
<?
	}
/*************************************************************************/	
	$departamento_anterior = '';
	$d = 0;
	for ($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se o Departamento Anterior é Diferente do Departamento Atual que está sendo listado
no loop, se for então eu atribuo o Departamento Atual p/ o Departamento Anterior ...*/
		if($departamento_anterior != $campos[$i]['departamento']) {
			$departamento_anterior = $campos[$i]['departamento'];
?>
    <tr class="linhadestaque">
        <td colspan='3'>
            <font color="yellow">
                <b>Departamento: </b>
            </font>
            <?=$campos[$i]['departamento'];?>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td bgcolor='#CECECE'>
            <label for='departamento<?=$i;?>'><b>Depto </b></label>
            <input type='checkbox' name='chkt_departamento[]' value="<?=$campos[$i]['id_departamento'];?>" title='Selecionar todos' onClick="selecionar_departamento('<?=$d;?>', '<?=$i;?>', '<?=$campos[$i]['id_departamento'];?>')" id="departamento<?=$i;?>" class="checkbox">
        </td>
        <td bgcolor='#CECECE'><b>Nome</b></td>
        <td bgcolor='#CECECE'><b>Empresa</b></td>
    </tr>
<?
			$d++;
		}
?>
    <tr class="linhanormal" onclick="selecionar_funcionario('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?
                if(is_array($chkt_funcionario)) {
                    if(in_array($campos[$i]['id_funcionario'], $chkt_funcionario)) {
                        $checked = 'checked';
                    }else {
                        $checked = '';
                    }
                }
            ?>
            <input type="checkbox" name="chkt_funcionario[]" value="<?=$campos[$i]['id_funcionario'];?>" title="Digite a Hora Inicial" onClick="selecionar_funcionario('<?=$i;?>')" maxlength="5" size="6" class="checkbox" <?=$checked;?>>
        </td>
        <td align="left">
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
            <!--Utilizado p/ ajudar nos Controles com o JavaScript-->
            <input type="hidden" name="hdd_departamento[]" value="<?=$campos[$i]['id_departamento'];?>">
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='submit' name='cmd_imprimir_licenca' value='Imprimir Licença' title='Imprimir Licença' style="color:black" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>