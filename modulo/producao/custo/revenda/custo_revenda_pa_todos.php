<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ PRODUTO(S) ACABADO(S) CADASTRADO(s) PARA ESSE FORNECEDOR.</font>";

if($passo == 1) {
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1://Razão Social
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 2://CNPJ
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        default://Todos
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'custo_revenda_pa_todos.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Custo Revenda - (Todos PAs) ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (Todos PAs)</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
            <?
                if($opt_internacional == 1) {
                    echo 'Internacional(s)';
                }else {
                    echo 'Nacional(s)';
                }
            ?>
            </font>
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='center'>
            <a href="custo_revenda_pa_todos2.php?id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td align='center'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'custo_revenda_pa_todos.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Custo Revenda - (Todos PAs) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (Todos PAs)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar fornecedores por: Razão Social' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar fornecedores por: CNPJ ou CPF' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title='Consultar Fornecedores Internacionais' id='label3' class='checkbox'>
            <label for='label3'>Internacionais</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar Todos os Fornecedores' id='label4' class='checkbox'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>