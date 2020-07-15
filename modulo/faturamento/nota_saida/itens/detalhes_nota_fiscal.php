<?
//Aqui chama a parte de Itens de NF
$nao_verificar_sessao  = 1;
$pop_up = 1;
require('itens.php');

//Aki eu verifico qual � a Data de Emiss�o da NF ...
$sql = "SELECT c.`optante_simples_nacional`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, 
        nfs.`id_cfop`, nfs.`snf_devolvida`, nfs.`data_emissao`, nfs.`suframa`, nfs.`suframa_ativo`, nfs.`status` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                     = bancos::sql($sql);
$optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
$id_cliente                 = $campos[0]['id_cliente'];
$id_empresa_nf              = $campos[0]['id_empresa'];
$id_nf_num_nota             = $campos[0]['id_nf_num_nota'];
$id_cfop                    = $campos[0]['id_cfop'];
$snf_devolvida              = $campos[0]['snf_devolvida'];
$data_emissao               = $campos[0]['data_emissao'];
$suframa_nf                 = $campos[0]['suframa'];
$suframa_ativo_nf           = $campos[0]['suframa_ativo'];
$status                     = $campos[0]['status'];
$numero_nf                  = faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');

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
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function imprimir() {
    var id_empresa_nf   = '<?=$id_empresa_nf;?>'
    var data_emissao 	= '<?=$data_emissao;?>'
/*Se a Data de Emiss�o for zerada, ent�o o Sistema for�a o usu�rio a preencher a Data de Emiss�o antes 
da Impress�o da Nota Fiscal*/
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISS�O NO CABE�ALHO P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
    
    if(id_empresa_nf == 4) {//Se for Grupo ent�o ...
        nova_janela('relatorio/imprimir_nota_sgd.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>', 'CONSULTAR', 'F')
    }else {//Se for qualquer outra empresa ent�o ...
        nova_janela('relatorio/imprimir_copia_duplicata.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>', 'CONSULTAR', 'F')
    }
}

function gerar_pin_xml() {
    var ano_atual   = '<?=date('Y')?>'
    var numero_nf   = '<?=$numero_nf?>'
    numero_lote     = numero_nf + ano_atual
/*Aqui eu dou como se fosse um reload na Tela p/ que se leia a NF j� com um Novo N�mero 
na Base de Dados ...*/
    alert('/******************RECOMENDA��ES PARA SALVAR O ARQUIVO XML: ******************/ \n\nPARA SALVAR O ARQUIVO XML QUE SER� GERADO: \n\n* CLIQUE NO MENU ARQUIVO DO CONTE�DO XML QUE SER� EXIBIDO; \n* CLIQUE NA OP��O SALVAR COMO E SELECIONE A PASTA ONDE DESEJA GRAVAR O ARQUIVO.')
    alert('ATEN��O UTILIZADOR: \n\n AO IMPORTAR O ARQUIVO NA P�GINA DO SUFRAMA UTILIZAR A OP��O NOTAS FISCAIS ELETR�NICAS.')
    nova_janela('gerar_pin_xml_nfe.php?id_nf=<?=$_GET['id_nf'];?>&numero_lote='+numero_lote, 'POP', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', 's', '')
}

function gerar_riex_xml(opcao) {
    alert('/******************RECOMENDA��ES PARA SALVAR O ARQUIVO XML: ******************/ \n\nPARA SALVAR O ARQUIVO XML QUE SER� GERADO: \n\n* CLIQUE NO MENU ARQUIVO DO CONTE�DO XML QUE SER� EXIBIDO; \n* CLIQUE NA OP��O SALVAR COMO E SELECIONE A PASTA ONDE DESEJA GRAVAR O ARQUIVO.')
    if(opcao == 1) {
        nova_janela('gerar_riex_remessa_xml.php?id_nf=<?=$_GET['id_nf'];?>', 'POP', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', 's', '')
    }else if(opcao == 2) {
        nova_janela('gerar_riex_exportacao_xml.php?id_nf=<?=$_GET['id_nf'];?>', 'POP', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', 's', '')
    }
}

function gerar_nfe() {
    var id_cliente          = '<?=$id_cliente;?>'
    var data_emissao        = '<?=$data_emissao;?>'
    var numero_nf           = '<?=$numero_nf;?>'
    var cfop                = '<?=$cfop;?>'
    var natureza_operacao   = '<?=$natureza_operacao;?>'
//Se a Data de Emiss�o for zerada, ent�o o Sistema for�a o usu�rio a preencher porque sen�o d� erro ao gerar o arquivo ...
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISS�O NO CABE�ALHO P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*Se o Sistema ainda n�o possuir N.� na NF, ent�o o Sistema for�a o usu�rio a preencher porque sen�o d� erro ao gerar 
o arquivo ...*/
    if(numero_nf == 0 || numero_nf == '') {
        alert('COLOQUE UM N�MERO DE NOTA FISCAL P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*******************************Presta��o de Servi�o*******************************/
//Esse � o �nico tipo de NF que n�o podemos emitir pelo Sistema da Sefaz, somente pela site da Prefeitura ...
    if(natureza_operacao == 'Presta��o de Servi�o') {
        if(navigator.appName == 'Microsoft Internet Explorer') {
            alert('ESTE TIPO DE NOTA FISCAL � A �NICA QUE A COBRAN�A � FEITA ATRAV�S DO SITE DA PREFEITURA (NFE DE SERVI�OS) !!!\n\nCASO O MATERIAL TENHA NOTA FISCAL DE ENTRADA, PRECISAMOS EMITIR UMA NFE DE RETORNO DE CONSERTO, E ESSA SIM � FEITA PELO SISTEMA DA SEFAZ !')
            nova_janela('http://nfpaulistana.prefeitura.sp.gov.br/', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        }else {
            alert('ESSA (NFE DE SERVI�OS) S� PODE SER FEITA ATRAV�S DO SITE DA PREFEITURA !!!\n\n*************************P/ ESSE CASO UTILIZE SOMENTE O NAVEGADOR INTERNET EXPLORER DEVIDO O CERTIFICADO DIGITAL N�O SER RECONHECIDO EM OUTROS BROWSERS !*************************')
        }
    }else {//Qualquer outro Tipo de NF, podemos emitir pelo Sistema da Sefaz normalmente ...
        if(cfop == 7.101) {
    /*Somente nessa CFOP que antes de Imprimir a NF, eu for�o o usu�rio a preencher os dados de Importa��o, 
    referentes a Quantidade, Esp�cie, Peso Bruto, Peso L�quido ...*/
            nova_janela('../../nfs_consultar/dados_volume.php?id_nf=<?=$_GET['id_nf'];?>', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
        }else {
            //TUTTITOOLS DISTRIBUIDORA DE FERRAMENTAS LTDA ...
            if(id_cliente == 39271) alert('/**********************************************************************TUTTITOOLS**********************************************************************/\n\nESSE � O �NICO CLIENTE EM QUE VOC� TEM QUE ENTRAR NA SEFAZ E ALTERAR O MUNIC�PIO / CIDADE MANUALMENTE => "LAJEADO" !')
            nova_janela('../../nfs_consultar/gerar_txt_nfe.php?id_nf=<?=$_GET['id_nf'];?>', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../nfs_consultar/consultar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_cabecalho' value='Cabe�alho' title='Cabe�alho' onclick="nova_janela('../alterar_cabecalho.php?id_nf=<?=$_GET['id_nf'];?>', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
            <?
                //Se o Menu acessado for pelo de Liberadas / Faturadas / Canceladas ou Devolu��o ...
                if($opcao == 2 || $opcao == 4) {
                    /*Algumas regras p/ exibir o Bot�o 

                    1) Empresa diferente de Grupo = '4' ...
                    2) Utilizamos um N�mero de Nosso Talon�rio ...*/
                    if($id_empresa_nf != 4 && $id_nf_num_nota > 0) {
                        /*3) Se a Nota Fiscal estiver com Status entre "Liberada p/ Faturar e Despachada" ...
                          4) Status "Devolu��o" e Cliente N�O Optante pelo Simples Nacional ou ...
                          5) Status "Devolu��o" Cliente Optante pelo Simples Nacional e o SNF preenchido ...*/
                        if(($status >= 1 && $status <= 4) || ($status == 6 && $optante_simples_nacional == 'N' || ($optante_simples_nacional == 'S' && !empty($snf_devolvida)))) {
            ?>
            <input type='button' name='cmd_gerar_nfe' value='Gerar NFe' title='Gerar NFe' onclick='gerar_nfe()' style='color:red' class='botao'>
            <?
                        }
                    }
                }
            ?>
            <input type='button' name='cmd_certificado_qualidade' value='Certificado de Qualidade' title='Certificado de Qualidade' onclick="nova_janela('../../../vendas/pedidos/itens/certificado_qualidade.php?id_nf=<?=$_GET[id_nf];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:green' class='botao'>
            <?
                //So exibo esse bot�o quando a NF estiver c/ Estagio de Faturada p/ cima ...
                if($status >= 2) {
            ?>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='botao'>
            <?
                }
//Se a NF for do Tipo Trading 6.501 / 6.502 - Ser� um Layout p/ o Riex em XML ...
                if($id_cfop == 149 || $id_cfop == 180) {//NFs de Exporta��o de Remessa - 6.501 / 6.502 - Trading ...
?>
            <input type='button' name="cmd_gerar_riex_remessa_xml" value="Gerar RIEX (XML Remessa)" title="Gerar RIEX (XML Remessa)" onclick='gerar_riex_xml(1)' style='color:black' class="botao">
<?
//Se a NF for do Tipo Trading 7.101 - Ser� um Outro Tipo de Layout p/ o Riex em XML ...
                }else if($id_cfop == 156) {//NFs de Exporta��o de Remessa - 7.101 ...
?>
            <input type='button' name="cmd_gerar_riex_exportacao_xml" value="Gerar RIEX (XML Exporta��o)" title="Gerar RIEX (XML Exporta��o)" onclick='gerar_riex_xml(2)' style='color:black' class="botao">
<?
                }else {
/*Se existir Suframa na NF, ent�o eu exibo esse bot�o p/ poder Gerar um arquivo "XML" que 
tem por fun��o acoplar todos os Itens da NF, Valor de NF, Frete, Base de C�lculo ... p/ que 
depois esse arquivo seje importado diretamente por um Sistema da Receita Federal gerando 
um "PIN" que � um Documento que possui um N.� de identifica��o, permitindo que o Cliente possa 
trafegar com a Mercadoria

Obs: Evitando tamb�m de que lancemos item a item nesse Sistema...*/
                    if($suframa_nf > 0 && $suframa_ativo_nf == 'S') {//Somente se o Cliente possuir o Suframa Ativo ...
?>
            <input type='button' name="cmd_gerar_pin_xml" value="Gerar PIN (XML Suframa)" title="Gerar PIN (XML Suframa)" onclick='gerar_pin_xml()' style='color:black' class="botao">
<?
                    }
                }
            ?>
        </td>
    </tr>
</table>
</body>
</html>