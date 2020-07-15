<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">LOGIN INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">LOGIN JÁ EXISTENTE.</font>';

//Cria-se um Login para o "Login" que foi digitado pelo administrador ...
if(!empty($_POST['txt_login']) && !empty($_POST['txt_senha'])) {
    //Verifico se esse Login digitado pelo usuário, já existe no sistema ...
    $sql = "SELECT `id_login` 
            FROM `logins` 
            WHERE `login` = '$_POST[txt_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Login não existente, sendo assim posso criar o mesmo no ERP ...
        //Criptografo a Senha digitada pelo usuário ...
        $senha_criptografada = segurancas::criptografia($_POST[txt_login], $_POST[txt_senha]);
        
        /*******************************************************************************/
        //Tratamento com os campos que tem que ficar NULL se não tiver preenchidos  ...
        /*******************************************************************************/
        $cmb_funcionario = (!empty($_POST[cmb_funcionario])) ? "'".$_POST[cmb_funcionario]."'" : 'NULL';
        
        $sql = "INSERT INTO `logins` (`id_login`, `id_funcionario`, `id_modulo`, `login`, `senha`) VALUES (NULL, $cmb_funcionario, '18', '$_POST[txt_login]', '$senha_criptografada') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Login já existente ...
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Login(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.cmb_empresa.value != '') {//Se foi selecionada uma Empresa ...
        //O sistema força a seleção de um Funcionário ...
        if(!combo('form', 'cmb_funcionario', '', 'SELECIONE O  FUNCIONÁRIO !')) {
            return false
        }
    }
//Login
    if(!texto('form', 'txt_login', '3', 'abcdefghkijlmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUkVWXYZ-_', 'LOGIN ', '2')) {
        return false
    }
//Senha
    if(!texto('form', 'txt_senha', '3', 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIKJLMNOPQRSTUVWXYZ-_0123456789', 'SENHA ', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_login.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='passo'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Login(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='15%'>
            Empresa:
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='document.form.submit()' class='combo'>
            <?
                //Listagem de todas as Empresas ativas cadastradas no Sistema ...
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                if(empty($_POST['cmb_empresa'])) {
                    echo combos::combo($sql);
                    $disabled = 'disabled';
                    $class = 'textdisabled';
                }else {
                    echo combos::combo($sql, $_POST['cmb_empresa']);
                    //Aqui verifica se na Empresa selecionada tem pelo menos um funcionário cadastrado ...
                    $sql = "SELECT `id_funcionario` 
                            FROM `funcionarios` 
                            WHERE `id_empresa` = '$_POST[cmb_empresa]' ";
                    $campos = bancos::sql($sql);
                    if(count($campos) == 0) $nao_tem = 0;
                    $disabled = '';
                    $class = 'combo';
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcionário:
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione o Funcionário' class='<?=$class;?>' <?=$disabled;?>>
            <?
                if(!empty($_POST['cmb_empresa'])) {
                    //Listo todos os funcionários de "Férias ou Ativo" da combo Empresa que foi selecionada ...
                    $sql = "SELECT `id_funcionario`, `nome` 
                            FROM `funcionarios` 
                            WHERE `status` <= '1' 
                            AND `id_empresa` = '$_POST[cmb_empresa]' ORDER BY `nome` ";
                    echo combos::combo($sql);
                }
            ?>
            </select>
            <?
                if(isset($nao_tem)) {
            ?>
                    &nbsp;&nbsp;&nbsp;
                    <input type='button' name='cmd_gerar_login' value='Gerar Login' title='Gerar Login' onclick="window.location = 'incluir.php?cmb_empresa='+document.form.cmb_empresa.value" class='botao'>
            <?
                }
            ?>
            <font color='red'>
                &nbsp;<b>(Sistema traz todo(s) o(s) funcionário(s) ativo(s) da Empresa selecionada)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Login:</b>
        </td>
        <td>
            <input type='text' name='txt_login' title='Digite o Login' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Senha:</b>
        </td>
        <td>
            <input type='password' name='txt_senha' title='Digite a Senha' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_login.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>