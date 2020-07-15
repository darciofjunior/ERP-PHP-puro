<?
require('../../lib/segurancas.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA DE E-MAIL INCLUIDA(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>CONTA DE E-MAIL UTILIZADO POR OUTRO FUNCION¡RIO.</font>";

if($passo == 1) {
/****************************************************************************************************/
/*SÛ n„o exibo os funcion·rios Default (1,2), ADAMO 91 e DIRETO BR 114 porque estes n„o s„o 
funcion·rios, simplesmente sÛ possuem cadastrado no Sistema p/ poder acessar algumas telas 
e menos do cargo AUTON‘MO que ainda est„o trabalhando na empresa e que n„o possui algum 
tipo de Conta de E-mail*/
    $sql = "SELECT DISTINCT(f.`id_funcionario`), f.`id_funcionario_superior`, f.`nome`, f.`rg`, f.`codigo_barra`, 
            f.`ddd_residencial`, f.`telefone_residencial`, c.`cargo`, e.`nomefantasia` 
            FROM `funcionarios` f 
            INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` <> '82' 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            AND f.`email_externo` = '' 
            AND f.`id_funcionario` NOT IN (1, 2, 91, 114) 
            AND (f.`status` < 3 OR (f.`status` = '3' AND f.`email_externo` <> '')) ORDER BY f.`nome` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_emails.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Conta(s) de E-mail ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Incluir Conta(s) de E-mail - Consultar Funcion·rio(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            CÛdigo
        </td>
        <td>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Chefe
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_current, p/ n„o dar conflito com a vari·vel "id_funcion·rio" da sess„o
            $url = "incluir_emails.php?passo=2&id_funcionario_current=".$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <a href="<?=$url;?>" title='Visualizar Detalhes'>
                <img src = '../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center' onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" title="Visualizar Detalhes" class='link'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
        <?
            //Busca do Nome do Chefe do Funcion·rio ...
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_emails.php'" class='botao'>
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
}else if($passo == 2) {
//Aqui eu busco os dados do Funcion·rio passado por par‚metro ...
    $sql = "SELECT `nome`, `email_externo` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_GET[id_funcionario_current]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir Conta(s) de E-mails ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//E-mail Externo
    if(!texto('form', 'txt_email_externo', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ«„ı√’·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€_.- ', 'E-MAIL EXTERNO', '2')) {
        return false
    }
//Aqui È para n„o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_email_externo.focus()' onunload='atualizar_abaixo()' topmargin='60'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<!--Coloquei esse nome de $id_funcionario_current, p/ n„o dar conflito com a vari·vel "id_funcion·rio" da sess„o-->
<input type='hidden' name='id_funcionario_current' value='<?=$_GET['id_funcionario_current'];?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Incluir Conta(s) de E-mails - 
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='yellow'>
                <?=$campos[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Email Externo:</td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_email_externo' value='<?=strtok($campos[0]['email_externo'], '@');?>' size='35' maxlength='50' title='Digite o Email Externo' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_email_externo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $email_externo = $_POST['txt_email_externo'].'@grupoalbafer.com.br';
//Aqui eu verifico se esse e-mail est· sendo usado por outro funcion·rio ...
    $sql = "SELECT `id_funcionario` 
            FROM `funcionarios` 
            WHERE `id_funcionario` <> '".$_POST['id_funcionario_current']."' 
            AND `email_externo` = '$email_externo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o est· sendo usado ...
        $sql = "UPDATE `funcionarios` SET `email_externo` = '$email_externo' WHERE `id_funcionario` = '$_POST[id_funcionario_current]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {//J· est· sendo usado ...
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_emails.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Conta(s) de E-mail ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()' onunload='atualizar_abaixo()' topmargin='60'>
<form name='form' method='post' action='<?=$GLOBALS['PHP_SELF'].'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Funcion·rio(s) - Incluir Conta(s) de E-mail
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' size='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome.focus()' style='color:#ff9900' class='botao'>
<!--Aqui È para n„o atualizar a Tela abaixo desse Pop-UP-->
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value = 1;atualizar_abaixo()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>ObservaÁ„o:</font></b>
<pre>

* A tela pÛs filtro, sÛ exibe os funcion·rios que n„o possuem algum Tipo de Conta de E-mail 
<b>Conta Interna</b> ou <b>Conta Externa</b>
</pre>
<?}?>