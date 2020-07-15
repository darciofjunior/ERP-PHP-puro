<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

//Aki eu verifico quem é a empresa e o Cliente desta NF, p/ ver se estão preenc. corretamente os dados de End.
$sql = "SELECT c.email_nfe, nfso.id_cliente, nfso.id_empresa, nfso.id_nf_num_nota, nfso.id_cfop, 
        nfso.data_emissao, nfso.texto_nf 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes` c ON c.id_cliente = nfso.`id_cliente` 
        WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
$campos             = bancos::sql($sql);
$email_nfe          = $campos[0]['email_nfe'];
$id_cliente         = $campos[0]['id_cliente'];
$id_empresa_nf      = $campos[0]['id_empresa'];
$id_nf_num_nota     = $campos[0]['id_nf_num_nota'];
$id_cfop            = $campos[0]['id_cfop'];
$data_emissao       = $campos[0]['data_emissao'];
$tamanho_texto_nf   = strlen($campos[0]['texto_nf']);

if($id_empresa_nf != 4 && $id_cfop != 0) {//Se a Empresa for Alba ou Tool e existir CFOP ...
    $sql = "SELECT natureza_operacao 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
    $campos_cfop        = bancos::sql($sql);
    $natureza_operacao  = $campos_cfop[0]['natureza_operacao'];
}

//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
$cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
$status = faturamentos::situacao_outras_nfs($id_nf_outra);

/**********************************************************************************************/
//Esse controle eu vou utilizar um pouco mais abaixo para controle dos Botões do Rodapé
//Se esta NF estiver atrelado a uma OS, então eu travo os Botões do Rodapé
$sql = "SELECT id_os 
        FROM `oss` 
        WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if(count($campos_os) == 1) {//Está importado p/ OS
    $tem_os_importada = 1;
}else {//Ainda não está importado p/ OS
    $tem_os_importada = 0;
}
/**********************************************************************************************/

//Se a NF tiver uma OS Importada, de jeito maneira que eu posso manipular os Itens da Nota Fiscal ...
if($tem_os_importada == 1) {
    $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"ESTA NOTA FISCAL POSSUI UMA O.S. IMPORTADA !"'.")'";
}else {
    if($status >= 1) {
//Quando alterar o cabecalho ele tem q reler a Nota Fiscal
        $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"NOTA FISCAL TRAVADA !"'.")'";
    }else {
        $controle_botao = "class='botao' ";
    }
}

//Verifico se tem pelo menos um item de Nota Fiscal, para poder exibir os botões alterar e excluir
$sql = "SELECT id_nf_outra_item 
        FROM `nfs_outras_itens` 
        WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Rodapé de Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function incluir_itens() {
    var cadastro_cliente_incompleto = eval('<?=$cadastro_cliente_incompleto;?>')
    if(cadastro_cliente_incompleto == 1) {//Está incompleto
        alert('O CADASTRO DESTE CLIENTE ESTÁ INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
    }else {//Está tudo OK
        nova_janela('incluir.php?id_nf_outra=<?=$id_nf_outra;?>', 'INCLUIR_ITENS', '', '', '', '', 580, 920, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function alterar_itens() {
    var option  = 0
    if(typeof(parent.itens.document.form) == 'undefined') {
        return false
    }else {
        var elementos = parent.itens.document.form
    }
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].checked == true && elementos[i].type == 'radio') option ++
    }
    if (option == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        for(var i = 0; i < elementos.length; i++) {
            if(elementos[i].checked == true && elementos[i].type == 'radio') {
                var id_nf_outra_item = elementos[i].value
                break;
            }
        }
        nova_janela('alterar.php?id_nf_outra_item='+id_nf_outra_item, 'ALTERAR ITENS', '', '', '', '', 580, 920, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function ajustes_impostos() {
    nova_janela('ajustes_impostos_nf.php?id_nf_outra=<?=$id_nf_outra;?>', 'AJUSTES_IMPOSTOS', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir() {
    var data_emissao 	= '<?=$data_emissao;?>'
    var id_nf_num_nota 	= '<?=$id_nf_num_nota;?>'
/*Se a Data de Emissão for zerada, então o Sistema força o usuário a preencher a Data de Emissão antes 
da Impressão da Nota Fiscal*/
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISSÃO NO CABEÇALHO P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
/*Se o Sistema ainda não possuir N.º na NF, então o Sistema força o usuário a colocar um número antes 
da Impressão da Nota Fiscal*/
    if(id_nf_num_nota == 0) {
        alert('COLOQUE UM NÚMERO DE NOTA FISCAL P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
    nova_janela('relatorio/imprimir_copia_duplicata.php?id_nf_outra=<?=$id_nf_outra;?>', 'CONSULTAR', 'F')
}

function gerar_nfe() {
    var data_emissao            = '<?=$data_emissao;?>'
    var email_nfe               = '<?=$email_nfe;?>'
    var id_nf_num_nota          = '<?=$id_nf_num_nota;?>'
    var natureza_operacao       = '<?=$natureza_operacao;?>'
    var tamanho_texto_nf        = '<?=$tamanho_texto_nf;?>'
//Se a Data de Emissão for zerada, então o Sistema força o usuário a preencher porque senão dá erro ao gerar o arquivo ...
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISSÃO NO CABEÇALHO P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*Se o Sistema ainda não possuir N.º na NF, então o Sistema força o usuário a preencher porque senão dá erro ao gerar 
o arquivo ...*/
    if(id_nf_num_nota == 0) {
        alert('COLOQUE UM NÚMERO DE NOTA FISCAL P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
    /*******************************Prestação de Serviço*******************************/
    //Esse é o único tipo de NF que não podemos emitir pelo Sistema da Sefaz, somente pela site da Prefeitura ...
    if(natureza_operacao == 'Prestação de Serviço') {
        alert('ESTE TIPO DE NOTA FISCAL É A ÚNICA QUE A COBRANÇA É FEITA ATRAVÉS DO SITE DA PREFEITURA (NFE DE SERVIÇOS) !!!\n\nCASO O MATERIAL TENHA NOTA FISCAL DE ENTRADA, PRECISAMOS EMITIR UMA NFE DE RETORNO DE CONSERTO, E ESSA SIM É FEITA PELO SISTEMA DA SEFAZ !')
        nova_janela('http://nfpaulistana.prefeitura.sp.gov.br/', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Qualquer outro Tipo de NF, podemos emitir pelo Sistema da Sefaz normalmente ...
        //O cliente nunca pode ficar sem E-mail de NFe Eletrônica cadastrada ...
        if(email_nfe == '') {
            alert('ESSE CLIENTE NÃO POSSUI E-MAIL DE NF-e CADASTRADO !!!\n\nÉ NECESSÁRIO TER UM E-MAIL DE NF-e CADASTRADO P/ "GERAR ESSE ARQUIVO DE NFe" !')
            return false
        }
        //Se não foi preenchido o Texto da NF, não é possível gerar o arquivo de NFe ...
        if(tamanho_texto_nf == 0) {
            alert('PREENCHA O TEXTO DA NOTA FISCAL !')
            document.form.cmd_texto_nota.focus()
            return false
        }
        nova_janela('../../nfs_consultar/gerar_txt_nfe.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<?
//Apenas na 1ª vez que esse parâmetro será vazio ...
$parametro_velho = (empty($parametro_velho)) ? $parametro : $parametro_velho;
?>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.parent.location = 'alterar_imprimir.php<?=$parametro_velho;?>'">
            <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' title='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' onclick="nova_janela('../alterar_cabecalho.php?id_nf_outra=<?=$id_nf_outra;?>', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <input type='button' name='cmd_incluir' <?=$controle_botao;?> value='Incluir Itens' title='Incluir Itens' onclick="incluir_itens()" class='botao'>
<?
/*Significa que está Nota contém pelo menos 1 Item, e sendo assim eu posso exibir os botões p/ a alteração
e exclusão de Itens*/
            if($linhas > 0) {
?>
            <input type='button' name='cmd_alterar' <?=$controle_botao;?> value='Alterar Item' title='Alterar Item' onclick="return alterar_itens()" class='botao'>
            <input type='button' name='cmd_excluir' value='Excluir Item(ns)' title='Excluir Item(ns)' onclick="nova_janela('excluir_itens.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <input type='button' name='cmd_outras' <?=$travar_botao;?> value='Outras Opções' title='Outras Opções' onclick="nova_janela('outras_opcoes.php?id_nf_outra=<?=$id_nf_outra;?>', 'POP', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
            }
?>
            <input type='button' name='cmd_ajustes_impostos' <?=$controle_botao;?> <?=$travar_botao;?> value='Ajustes / Impostos' title='Ajustes / Impostos' onclick='ajustes_impostos()' class='botao'>
<?
//Texto da NF ...
            if($id_empresa_nf != 4) {//Se a empresa for diferente de Grupo = '4', sempre exibe o Botão ...
?>
        <input type='button' name='cmd_texto_nota' <?=$travar_botao;?> value='Texto da Nota' title='Texto da Nota' onclick="nova_janela('../../nfs_consultar/preencher_texto_nf.php?id_nf_outra=<?=$id_nf_outra;?>', 'CONSULTAR', '', '', '', '', '350', '850', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:brown' class='botao'>
<?
            }
            if($status >= 1) {//Só exibe esse botão p/ as NFs com Status de "Liberadas p/ Faturar" p/ cima ...
                if($id_empresa_nf != 4) {//Se a empresa for diferente de Grupo = '4', sempre exibe o Botão ...
?>
                    <input type='button' name='cmd_gerar_nfe' value='Gerar NFe' title='Gerar NFe' onclick='gerar_nfe()' style="color:red" class='botao'>

<?
                }
?>			
                <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='botao'>
<?
            }
?>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nf_outra' value='<?=$id_nf_outra?>'>
</form>
</body>
</html>