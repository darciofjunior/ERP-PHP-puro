<?
//Aqui chama a parte de Itens de NF
$nao_verificar_sessao  = 1;
$pop_up = 1;
require('itens.php');

//Aki eu verifico qual � a Data de Emiss�o da NF ...
$sql = "SELECT id_empresa, id_nf_num_nota, id_cfop, data_emissao, SUBSTRING_INDEX(texto_nf, ' ', 1) AS texto_nf 
        FROM `nfs_outras` 
        WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos = bancos::sql($sql);
$id_empresa_nf      = $campos[0]['id_empresa'];
$id_nf_num_nota     = $campos[0]['id_nf_num_nota'];
$data_emissao       = $campos[0]['data_emissao'];
$texto_nf           = $campos[0]['texto_nf'];

if($id_empresa_nf != 4 && $id_cfop != 0) {//Se a Empresa for Alba ou Tool e existir CFOP ...
    $sql = "SELECT CONCAT(cfop, '.', num_cfop) AS cfop, natureza_operacao 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
    $campos_cfop        = bancos::sql($sql);
    $cfop               = $campos_cfop[0]['cfop'];
    $natureza_operacao  = $campos_cfop[0]['natureza_operacao'];
}
?>
<html>
<head>
<title>.:: Detalhes de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function imprimir() {
    var data_emissao = '<?=$data_emissao;?>'
/*Se a Data de Emiss�o for zerada, ent�o o Sistema for�a o usu�rio a preencher a Data de Emiss�o 
antes da Impress�o da Nota Fiscal*/
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISS�O NO CABE�ALHO P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
    nova_janela('relatorio/imprimir_copia_duplicata.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'CONSULTAR', 'F')
}

function gerar_nfe() {
    var data_emissao        = '<?=$data_emissao;?>'
    var id_nf_num_nota      = '<?=$id_nf_num_nota;?>'
    var texto_nf            = '<?=$texto_nf;?>'
    var natureza_operacao   = '<?=$natureza_operacao;?>'
//Se a Data de Emiss�o for zerada, ent�o o Sistema for�a o usu�rio a preencher porque sen�o d� erro ao gerar o arquivo ...
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISS�O NO CABE�ALHO P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*Se o Sistema ainda n�o possuir N.� na NF, ent�o o Sistema for�a o usu�rio a preencher porque sen�o d� erro ao gerar 
o arquivo ...*/
    if(id_nf_num_nota == 0) {
        alert('COLOQUE UM N�MERO DE NOTA FISCAL P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*Se o Sistema ainda n�o possuir os dizeres dos Dados Adicionais, ent�o o Sistema for�a o usu�rio a preencher porque 
sen�o d� erro ao gerar o arquivo ...*/
    if(texto_nf == '') {
        alert('COLOQUE O TEXTO DA NOTA QUE FICA DENTRO DO CABE�ALHO P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*******************************Presta��o de Servi�o*******************************/
//Esse � o �nico tipo de NF que n�o podemos emitir pelo Sistema da Sefaz, somente pela site da Prefeitura ...
    if(natureza_operacao == 'Presta��o de Servi�o') {
        alert('ESTE TIPO DE NOTA FISCAL � A �NICA QUE A COBRAN�A � FEITA ATRAV�S DO SITE DA PREFEITURA (NFE DE SERVI�OS) !!!\n\nCASO O MATERIAL TENHA NOTA FISCAL DE ENTRADA, PRECISAMOS EMITIR UMA NFE DE RETORNO DE CONSERTO, E ESSA SIM � FEITA PELO SISTEMA DA SEFAZ !')
        nova_janela('http://nfpaulistana.prefeitura.sp.gov.br/', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Qualquer outro Tipo de NF, podemos emitir pelo Sistema da Sefaz normalmente ...
        nova_janela('../../nfs_consultar/gerar_txt_nfe.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
    <?
            //Se essa tela n�o foi aberta como sendo Pop-UP ent�o eu exibo o bot�o ...
            if(empty($_GET['pop_up'])) {
    ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../nfs_consultar/consultar.php<?=$parametro;?>'" class='botao'>
    <?
            }
    ?>
            <input type='button' name='cmd_cabecalho' value='Cabe�alho' title='Cabe�alho' onclick="nova_janela('../alterar_cabecalho.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>&pop_up=1', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" style="color:purple" class='botao'>
    <?
            if($id_empresa_nf == 1 || $id_empresa_nf == 2) {//S� exibe esse bot�o p/ as Empresas Alba ou Tool ...
    ?>
            <input type='button' name='cmd_gerar_nfe' value='Gerar NFe' title='Gerar NFe' onclick='gerar_nfe()' style="color:red" class='botao'>
    <?
            }
    ?>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>