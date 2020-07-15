<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

//Busca de Todas as Variáveis do Sistema ...
$sql = "SELECT id_variavel, valor, opcao, modulo_obs 
        FROM `variaveis` 
        ORDER BY modulo_obs, opcao ";
$campos_variaveis = bancos::sql($sql);
$linhas_variaveis = count($campos_variaveis);
?>
<html>
<head>
<title>.:: Variável(is) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function alterar_variavel() {
    var id_variavel = 0
//Verifica se tem alguma opção selecionada p/ fazer a Alteração da Variável ...
    for(i = 0; i < document.form.opt_opcao.length; i++) {
        if(document.form.opt_opcao[i].checked == true) id_variavel = document.form.opt_opcao[i].value
    }
//Se não tiver nenhuma opção selecionada então ...
    if(id_variavel == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        window.location = 'alterar.php?id_variavel='+id_variavel
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='80%' cellpadding='1' cellspacing="1" align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Controle de Variáveis do Sistema
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Opções / Variável(is)
        </td>
        <td>
            ID(s)
        </td>
        <td>
            Valor(es)
        </td>
        <td>
            Módulo(s)
        </td>
    </tr>
<?
//Aqui traz todos os dados da tabela variáveis ...
	for($i = 0; $i < $linhas_variaveis; $i++) {
            if(strtok($campos_variaveis[$i]['modulo_obs'], ' ') != $modulo_obs_current) {
                $modulo_obs_current = strtok($campos_variaveis[$i]['modulo_obs'], ' ');
?>
    <tr>
        <td bgcolor='gray' colspan='4'>
            &nbsp;
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="<?=$campos_variaveis[$i]['id_variavel'];?>" ondblclick="alterar_variavel()" id="opt<?=$campos_variaveis[$i]['id_variavel'];?>">
            <label for="opt<?=$campos_variaveis[$i]['id_variavel'];?>">
                <?=$campos_variaveis[$i]['opcao'];?>
            </label>
        </td>
        <td align='center'>
            <?=$campos_variaveis[$i]['id_variavel'];?>
        </td>
        <td align='center'>
            <?=number_format($campos_variaveis[$i]['valor'], 4, ',', '.');?>
        </td>
        <td>
            <b><?=$campos_variaveis[$i]['modulo_obs'];?></b>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_incluir' value='Incluir Variável' title='Incluir Variável' onclick="window.location = 'incluir.php'" style='color:green' class='botao'>
            <input type='button' name='cmd_alterar' value='Alterar Variável' title='Alterar Variável' onclick='alterar_variavel()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>