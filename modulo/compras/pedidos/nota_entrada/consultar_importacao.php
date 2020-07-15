<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_nfe     = $_POST['id_nfe'];
        $id_pais    = $_POST['id_pais'];
    }else {
        $id_nfe     = $_GET['id_nfe'];
        $id_pais    = $_GET['id_pais'];
    }
/***********************************************************************/
/***************Controle apenas p/ Fornecedor Estrangeiro***************/
/***********************************************************************/
    if($id_pais != 31) {
        /*Aqui eu trago todas as Importações que já foram importadas em NF(s) com excessão da NF 
        que o usuário está dentro ...*/
        $sql = "SELECT DISTINCT(`id_importacao`) 
                FROM `nfe` 
                WHERE `id_nfe` <> '$id_nfe' 
                AND `id_importacao` > '0' ORDER BY `id_importacao` ";
        $campos_importacao  = bancos::sql($sql);
        $linhas_importacao  = count($campos_importacao);
        for($i = 0; $i < $linhas_importacao; $i++) $id_importacoes.= $campos_importacao[$i]['id_importacao'].', ';
        $id_importacoes         = substr($id_importacoes, 0, strlen($id_importacoes) - 2);
        $condicao_importacao    = " AND `id_importacao` NOT IN ($id_importacoes) ";
    }
/***********************************************************************/
//Busca das Importações de acordo com o Filtro feito pelo Usuário ...
    $sql = "SELECT `id_importacao`, `nome` 
            FROM `importacoes` 
            WHERE `nome` LIKE '%$txt_nome%' 
            $condicao_importacao ORDER BY `nome` ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_importacao.php?id_nfe=<?=$id_nfe;?>&id_pais=<?=$id_pais;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Importação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function transportar_dados(id_importacao, nome) {
    parent.document.form.hdd_importacao.value       = id_importacao
    parent.document.form.txt_nome_importacao.value  = nome
    parent.html5Lightbox.finish()
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Importação(ões)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Nome
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:transportar_dados('".$campos[$i]['id_importacao']."', '".$campos[$i]['nome']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_importacao.php?id_nfe=<?=$id_nfe;?>&id_pais=<?=$id_pais;?>'" class='botao'>
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
<title>.:: Consultar Importação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<!--***************Controle de Tela***************-->
<input type='hidden' name='id_nfe' value='<?=$_GET[id_nfe];?>'>
<input type='hidden' name='id_pais' value='<?=$_GET[id_pais];?>'>
<input type='hidden' name='passo' value='1'>
<!--**********************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Importação(ões)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_nome.focus()' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>