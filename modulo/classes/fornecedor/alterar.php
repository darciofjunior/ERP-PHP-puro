<?
if(!class_exists('segurancas'))     require '../../../lib/segurancas.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(empty($pop_up))                  require '../../../lib/menu/menu.php';//Essa tela as vezes é aberta como sendo Pop-UP ...
if(!class_exists('data'))           require '../../../lib/data.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('financeiros'))    require '../../../lib/financeiros.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas'))      require '../../../lib/genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
session_start('funcionarios');

if($passo == 1) {
?>
<html>
<head>
<title>.:: Alterar Fornecedor(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function gerenciar_telas(tela) {
    if(tela == 1) {//Dados Básicos ...
        window.tela.location = 'alterar_dados_basicos.php?id_fornecedor='+document.form.id_fornecedor.value+'&pop_up=<?=$_GET['pop_up'];?>&detalhes=<?=$_GET['detalhes'];?>'
    }else if(tela == 2) {//Dados Comerciais ...
        window.tela.location = 'alterar_dados_comerciais.php?id_fornecedor='+document.form.id_fornecedor.value+'&pop_up=<?=$_GET['pop_up'];?>&detalhes=<?=$_GET['detalhes'];?>'
    }
}
</Script>
</head>
<body onload='gerenciar_telas(1)'>
<form name='form' method='post'>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<table width='880' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td id='aba0' onclick="gerenciar_telas(1);aba(this, 2, 650)" width='50%' class='aba_ativa'>
            Dados Básicos
        </td>
        <td id='aba1' onclick="gerenciar_telas(2);aba(this, 2, 650)" width='50%' class='aba_inativa'>
            Dados Comerciais
        </td>
    </tr>
</table>
<table border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <iframe name='tela' marginwidth='0' marginheight='0' frameborder='0' height='1050' width='900'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Alterar Fornecedor(es)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Fone 1
        </td>
        <td>
            Fone 2
        </td>
        <td>
            Fax
        </td>
        <td>
            Produtos
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = '../../classes/fornecedor/alterar.php?passo=1&id_fornecedor='.$campos[$i]['id_fornecedor'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone1'].' '.$campos[$i]['fone1'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone2'].' '.$campos[$i]['fone2'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fax'].' '.$campos[$i]['fax'];?>
        </td>
        <td>
            <?=$campos[$i]['produto'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
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