<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/biblioteca.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$fornecedores = biblioteca::controle_itens($id_fornecedor, $id_fornecedor2, $acao);

if($passo == 1) {
    $condicao = ($_POST['opt_internacional'] == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$_POST[txt_consultar]%' 
                    AND `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
        case 2:
            $consultar = str_replace('-', '', $_POST['txt_consultar']);
            $consultar = str_replace('/', '', $consultar);
            $consultar = str_replace('.', '', $consultar);
            
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj` LIKE '%$consultar%' 
                    AND `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
        case 3:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `codigo` LIKE '%$_POST[txt_consultar]%' 
                    AND `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";    
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'emails.php?valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) p/ Gerar Lista de E-mails ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*************************************************************************/
/*Funções referentes a segunda tela depois da consulta - Passo = 1*/
function enviar() {
    var elementos = document.form.elements
    var selecionados = 0, id_fornecedor = ''
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) {
                    selecionados ++
                    id_fornecedor = id_fornecedor + document.form.elements[i][j].value + ',';
                }
            }
            i = elementos.length
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM FORNECEDOR !')
        return false
    }

    document.form.id_fornecedor2.value      = id_fornecedor.substr(0, id_fornecedor.length - 1);
    document.form.txt_consultar.value       = '<?=$_POST['txt_consultar'];?>'
    document.form.opcao_selecionada.value   = '<?=$_POST['opt_opcao'];?>'
    document.form.action                    = 'emails.php'
    document.form.target                    = '_self'
    document.form.passo.value               = 1
    document.form.exibir.value              = 1
    document.form.submit()
}

function selecionar_todos() {
    var elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(var j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
            return false
        }
    }
}
/*************************************************************************/
/*Funções referentes a terceira tela depois da consulta - Exibir = 1*/
function retirar_fornecedor() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, fornecedor_sel = ''
    var achou_combo = 0
    for(var i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM FORNECEDOR !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) fornecedor_sel = fornecedor_sel + document.form.elements[i][j].value + ','
                    }
                }
                flag++
            }
        }
    }
    fornecedor_sel                          = fornecedor_sel.substr(0, fornecedor_sel.length - 1)
    document.form.id_fornecedor2.value      = fornecedor_sel
    document.form.txt_consultar.value       = '<?=$_POST['txt_consultar'];?>'
    document.form.opcao_selecionada.value   = '<?=$_POST['opt_opcao'];?>'
    document.form.acao.value                = 1
    document.form.exibir.value              = 1
    document.form.passo.value               = 1
    document.form.action                    = 'emails.php'
    document.form.target                    = '_self'
    document.form.submit()
}

function selecionar_todos_fornecedors() {
    var elementos       = document.form.elements
    var selecionados    = ''
    var achou_combo     = 0
    for (var i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                for(var j = 1; j < document.form.elements[i].length; j ++) document.form.elements[i][j].selected = true
            }
        }
    }
}

function gerar_lista() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, fornecedor_sel = ''
    var achou_combo = 0, selecionados = 0
    selecionar_todos_fornecedors()
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM FORNECEDOR !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) selecionados ++
                    }
                }
                flag++
            }
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM FORNECEDOR !')
        return false
    }
    document.form.action = 'lista_emails.php'
    document.form.target = 'novajanela'
    nova_janela('lista_emails.php', 'novajanela', '', '', '', '', 450, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo'>
<input type='hidden' name='exibir'>
<input type='hidden' name='opcao_selecionada'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fornecedor(es) p/ Gerar Lista de E-mails
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar fornecedor por: Razão Social' onclick='desabilitar()' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar fornecedor por: CNPJ ou CPF' onclick='desabilitar()' id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar fornecedor por: Código de Barra' onclick='desabilitar()' id='label3'>
            <label for='label3'>Código de Barra</label>
        </td>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title='Consultar fornecedores internacionais' id='label4' class='checkbox'>
            <label for='label4'>Internacionais</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os fornecedores' onclick='limpar()' id='label5' class='checkbox'>
            <label for='label5'>Todos os registros</label>
        </td>
    </tr>
<?
    if($passo == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <select name='cmb_fornecedor[]' class='combo' size='5' multiple>
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
                <option value="<?=$campos[$i]['id_fornecedor'];?>"><?=$campos[$i]['razaosocial'];?></option>
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
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
<?
        if($passo == 1) {
?>
            <input type='button' name='cmd_selecionar' value='Selecionar Todos' title='Selecionar Todos' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_adicionar' value='Adicionar' title='Adicionar' onclick='enviar()' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<?
/*Nessa parte é simplesmente para mostrar a segunda combo com os fornecedors
selecionados da primeira combo*/
    if(!empty($fornecedores)) {
        $sql = "SELECT `id_fornecedor`, `razaosocial` 
                FROM `fornecedores` 
                WHERE `id_fornecedor` IN ($fornecedores) ORDER BY `razaosocial` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Fornecedor(es) Selecionado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <select name='cmb_fornecedores_selecionados[]' class='combo' size='5' multiple>
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
            for($i = 0; $i < $linhas; $i++) {
    ?>
                <option value='<?=$campos[$i]['id_fornecedor']?>'><?=$campos[$i]['razaosocial']?></option>
    <?
            }
    ?>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='button' name='cmd_retirar2' value='Retirar' title='Retirar' onclick='retirar_fornecedor()' class='botao'>
            <input type='button' name='cmd_gerar_lista' value='Gerar Lista' title='Gerar Lista' onclick='gerar_lista()' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
<input type='hidden' name='id_fornecedor' value='<?=$fornecedores;?>'>
<input type='hidden' name='id_fornecedor2'>
<input type='hidden' name='acao'>
</form>
</body>
<Script Language = 'JavaScript'>
/*Funções referentes a primeira tela antes de fazer a consulta*/
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled        = true
        document.form.txt_consultar.className       = 'textdisabled'
    }else {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
        document.form.opt_opcao[1].checked          = true
        document.form.txt_consultar.disabled        = false
        document.form.txt_consultar.className       = 'caixadetexto'
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
    document.form.action        = 'emails.php'
    document.form.target        = '_self'
    document.form.exibir.value  = 0
    document.form.passo.value   = 1
}

function desabilitar() {
    document.form.txt_consultar.value           = ''
    if(document.form.opt_opcao[3].checked == true) {
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}
</Script>
</html>