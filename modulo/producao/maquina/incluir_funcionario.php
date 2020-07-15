<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/alterar.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>FUNCIONÁRIO(S) INCLUIDO(S) COM SUCESSO PARA MÁQUINA.</font>";
$mensagem[3] = "<font class='erro'>FUNCIONÁRIO(S) JÁ EXISTENTE PARA MÁQUINA.</font>";

$id_maquina = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_maquina'] : $_GET['id_maquina'];

//Inserção dos produtos acabados vs máquinas
if($passo == 1) {
    if($_POST['inserir'] == 1) {
        foreach($_POST['cmb_funcionario'] as $id_funcionario_loop) {
            $sql = "SELECT id_maquina_vs_funcionario 
                    FROM `maquinas_vs_funcionarios` 
                    WHERE `id_maquina` = '$id_maquina' 
                    AND `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                $sql = "INSERT INTO `maquinas_vs_funcionarios` (`id_maquina_vs_funcionario`, `id_maquina`, `id_funcionario`) VALUES (NULL, '$id_maquina', '$id_funcionario_loop') ";
                bancos::sql($sql);
                $valor = 2;
            }else {
                $valor = 3;
            }
        }
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_funcionario.php?id_maquina=<?=$id_maquina;?>&valor=<?=$valor;?>'
            var valor = '<?=$valor;?>'
            if(valor == 2) {//Se a máquina foi atrelada com sucesso ...
                parent.document.form.passo.onclick()
            }
        </Script>
<?
    }
}
//Fim da Inserção

if($passo == 1) {
/*Só exibe Funcionários que são Chefes ou Trabalham na fábrica e que o Salário é do Tipo 
Horista, pois tem funcionários Mensalistas que ganham bem e daí iria vazar essa informação*/
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT id_funcionario, nome 
                    FROM `funcionarios` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `id_departamento` IN (1, 19) 
                    AND `status` < '3' ORDER BY nome ";
        break;
        default:
            $sql = "SELECT id_funcionario, nome 
                    FROM `funcionarios` 
                    WHERE `id_departamento` IN (1, 19) 
                    AND `status` < '3' ORDER BY nome ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_funcionario.php?id_maquina=<?=$id_maquina;?>&valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) - Depto. Fábrica ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function enviar() {
    var i, elementos = document.form.elements
    var selecionados = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) selecionados ++
            }
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM FUNCIONÁRIO !')
        return false
    }else if(selecionados > 100) {
        alert('EXCEDIDO O NÚMERO DE FUNCIONÁRIO(S) SELECIONADO(S) !\n\nPERMITIDO NO MÁXIMO 100 REGISTROS POR VEZ !')
        return false
    }

    document.form.inserir.value = 1
    document.form.submit();
}

function selecionar_todos() {
    var elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
        }
    }
}

function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.chkt_todos.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='inserir'>
<?
    if(!empty($txt_consultar)) {
        $consultar = $txt_consultar;
    }else {
        $consultar = $txt_consultar2;
    }
?>
<input type='hidden' name='txt_consultar2' value="<?=$consultar;?>">
<?
	if(!empty($opt_opcao)) {
		$opcao = $opt_opcao;
	}else {
		$opcao = $opt_opcao2;
	}
?>
<input type='hidden' name='opt_opcao2' value="<?=$opcao;?>">
<input type='hidden' name='id_maquina' value="<?=$id_maquina;?>">
<table border='0' width='80%' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s) - Depto.
            <font color='yellow'> 
                Fábrica 
            </font>
            e 
            <font color='yellow'>	
                Chefia
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Funcionário' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" title="Consultar Funcionário por: Nome" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_todos' title="Consultar todos os funcionários" onclick='limpar()' value='2' class="checkbox" id='label2'>
            <label for="label2">Todos os registros</label>
        </td>
    </tr>
<?
	if($passo == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <select name='cmb_funcionario[]' size='5' class='combo' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
            <?
                for($i = 0; $i < $linhas; $i ++) {
            ?>
                <option value="<?=$campos[$i]['id_funcionario'];?>"><?=$campos[$i]['nome'];?></option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' style='color:#ff9900' value='Limpar' onclick="document.form.chkt_todos.checked = false;limpar()" title='Limpar' class='botao'>
<?
        if($passo == 1) {
?>
            <input type='button' name='cmd_selecionar' value='Selecionar Todos' title='Selecionar Todos' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_adicionar' value='Adicionar' title='Adicionar' onclick='enviar()' class='botao'>
<?
        }
?>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>