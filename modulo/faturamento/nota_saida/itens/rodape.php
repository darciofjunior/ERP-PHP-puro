<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../lib/intermodular.php');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolução ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
    }
}

//Aki eu verifico quem é a empresa e o Cliente desta NF, p/ ver se estão preenc. corretamente os dados de End.
$sql = "SELECT c.`id_cliente`, c.`id_pais`, c.`id_uf`, c.`tipo_suframa`, c.`optante_simples_nacional`, c.`email_nfe`, nfs.`id_empresa`, 
        nfs.`id_nf_num_nota`, nfs.`natureza_operacao`, nfs.`snf_devolvida`, nfs.`data_emissao`, nfs.`suframa`, nfs.`texto_nf`, nfs.`status`, 
        nfs.`livre_debito`, nfs.`devolucao_faturada`, nfs.`gerar_duplicatas` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
	WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                     = bancos::sql($sql);
$id_cliente                 = $campos[0]['id_cliente'];
$id_pais                    = $campos[0]['id_pais'];
$tipo_suframa_cliente       = $campos[0]['tipo_suframa'];//Essa variável é imprescendível para fazer uma verificação importante ...
$optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
$email_nfe                  = $campos[0]['email_nfe'];
$id_empresa_nf              = $campos[0]['id_empresa'];
$id_nf_num_nota             = $campos[0]['id_nf_num_nota'];
$natureza_operacao          = $campos[0]['natureza_operacao'];
$snf_devolvida              = $campos[0]['snf_devolvida'];
$data_emissao               = $campos[0]['data_emissao'];
$suframa_nf                 = $campos[0]['suframa'];
$tamanho_texto_nf           = strlen($campos[0]['texto_nf']);
$status                     = $campos[0]['status'];
$livre_debito               = $campos[0]['livre_debito'];
$devolucao_faturada         = $campos[0]['devolucao_faturada'];
$gerar_duplicatas           = $campos[0]['gerar_duplicatas'];
$numero_nf                  = faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');

if($opcao == 4) {//Significa que veio do Menu de Devolução
    //Se a NF está com a Marcação de Devolução Faturada ...
    if($devolucao_faturada == 'S') {//Então a NF já não pode + ser alterada ...
        $travar_botao = "class='disabled' onclick='JavaScript:alert(".'"NOTA FISCAL FATURADA !"'.")'";
    }
}else {//Se for de Outros Menus ...
    if($status >= 1) {//Se a NF estiver com o Status de Liberada p/ Faturar ...
        $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"NOTA FISCAL TRAVADA !"'.")'";
    }else {
        /*Se a NF possuir GNRE, então o usuário não tem como excluir os Itens da NF de forma a não 
        permitir que a NF seja cancelada ...*/
        $sql = "SELECT gnre 
                FROM `nfs` 
                WHERE `id_nf` = '$_GET[id_nf]' 
                AND gnre <> '' LIMIT 1 ";
        $campos_gnre = bancos::sql($sql);
        if(count($campos_gnre) == 1) {//Se existe GNRE ...
            $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"NÃO É POSSÍVEL EXCLUIR PORQUE JÁ EXISTE GNRE !"'.")'";
        }else {
            $controle_botao = "class='botao' ";
        }
    }
}

//Verifico se tem pelo menos um item de Nota Fiscal, para poder exibir os botões alterar e excluir
$sql = "SELECT `id_nfs_item` 
        FROM `nfs_itens` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);

//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
$cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);

//Verificação com Suframa ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
$valor_ipi              = number_format($calculo_total_impostos['valor_ipi'], 2, '.', '');
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
        nova_janela('incluir.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'INCLUIR_ITENS', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function devolver_itens() {
    nova_janela('devolver_itens.php?id_nf=<?=$_GET['id_nf'];?>', 'DEVOLVER_ITENS', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')
}

function ajustes() {
    nova_janela('ajustes_nf.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'AJUSTES_NF', '', '', '', '', 250, 650, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir() {
    var id_empresa_nf   = '<?=$id_empresa_nf;?>'
    var data_emissao 	= '<?=$data_emissao;?>'
    var numero_nf       = '<?=$numero_nf;?>'
/*Se a Data de Emissão for zerada, então o Sistema força o usuário a preencher a Data de Emissão antes 
da Impressão da Nota Fiscal*/
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISSÃO NO CABEÇALHO P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
/*Se o Sistema ainda não possuir N.º na NF, então o Sistema força o usuário a colocar um número antes 
da Impressão da Nota Fiscal*/
    if(numero_nf == 0) {
        alert('COLOQUE UM NÚMERO DE NOTA FISCAL P/ QUE SE POSSA IMPRIMIR A NF !')
        return false
    }
    
    if(id_empresa_nf == 4) {//Se for Grupo então ...
        nova_janela('relatorio/imprimir_nota_sgd.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>', 'CONSULTAR', 'F')
    }else {//Se for qualquer outra empresa então ...
        nova_janela('relatorio/imprimir_copia_duplicata.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>', 'CONSULTAR', 'F')
    }
}

function gerar_nfe() {
    var id_cliente              = '<?=$id_cliente;?>'
    var id_pais                 = '<?=$id_pais;?>'
    var data_emissao            = '<?=$data_emissao;?>'
    var email_nfe               = '<?=$email_nfe;?>'
    var numero_nf 		= '<?=$numero_nf;?>'
    var tamanho_texto_nf        = '<?=$tamanho_texto_nf;?>'
    var natureza_operacao       = '<?=$natureza_operacao;?>'
    
//Se a Data de Emissão for zerada, então o Sistema força o usuário a preencher porque senão dá erro ao gerar o arquivo ...
    if(data_emissao == '0000-00-00') {
        alert('PREENCHA O CAMPO DATA DE EMISSÃO NO CABEÇALHO P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
/*Se o Sistema ainda não possuir N.º na NF, então o Sistema força o usuário a preencher porque senão dá erro ao gerar 
o arquivo ...*/
    if(numero_nf == 0 || numero_nf == '') {
        alert('COLOQUE UM NÚMERO DE NOTA FISCAL P/ QUE SE POSSA GERAR O ARQUIVO DE NFe !')
        return false
    }
//Verifico se o Cliente possui Suframa e se está sendo tributado IPI em Nota Fiscal, se estiver acontecendo está incorreto ...
    var tipo_suframa_cliente    = eval('<?=$tipo_suframa_cliente;?>')
    var valor_ipi               = eval('<?=$valor_ipi;?>')
    if(tipo_suframa_cliente > 0 && valor_ipi > 0) {
        alert('ESTÁ NOTA FISCAL ESTÁ ERRADA !!!\n\nESTÁ SENDO COBRADO IPI DE UM CLIENTE QUE POSSUI SUFRAMA - FAVOR VERIFICAR !')
        return false
    }
    /*******************************Prestação de Serviço*******************************/
    //Esse é o único tipo de NF que não podemos emitir pelo Sistema da Sefaz, somente pela site da Prefeitura ...
    if(natureza_operacao == 'PSE') {
        if(navigator.appName == 'Microsoft Internet Explorer') {
            alert('ESTE TIPO DE NOTA FISCAL É A ÚNICA QUE A COBRANÇA É FEITA ATRAVÉS DO SITE DA PREFEITURA (NFE DE SERVIÇOS) !!!\n\nCASO O MATERIAL TENHA NOTA FISCAL DE ENTRADA, PRECISAMOS EMITIR UMA NFE DE RETORNO DE CONSERTO, E ESSA SIM É FEITA PELO SISTEMA DA SEFAZ !')
            nova_janela('http://nfpaulistana.prefeitura.sp.gov.br/', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        }else {
            alert('ESSA (NFE DE SERVIÇOS) SÓ PODE SER FEITA ATRAVÉS DO SITE DA PREFEITURA !!!\n\n*************************P/ ESSE CASO UTILIZE SOMENTE O NAVEGADOR INTERNET EXPLORER DEVIDO O CERTIFICADO DIGITAL NÃO SER RECONHECIDO EM OUTROS BROWSERS !*************************')
        }
    }else {//Qualquer outro Tipo de NF, podemos emitir pelo Sistema da Sefaz normalmente ...
        if(id_pais != 31) {
/*Somente nessa CFOP que antes de Imprimir a NF, eu forço o usuário a preencher os dados de Importação, 
referentes a Quantidade, Espécie, Peso Bruto, Peso Líquido ...*/
            nova_janela('../../nfs_consultar/dados_volume.php?id_nf=<?=$_GET['id_nf'];?>', 'DADOS_VOLUME', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
	}else {
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
            //TUTTITOOLS DISTRIBUIDORA DE FERRAMENTAS LTDA ...
            if(id_cliente == 39271) if(id_cliente == 39271) alert('/**********************************************************************TUTTITOOLS**********************************************************************/\n\nESSE É O ÚNICO CLIENTE EM QUE VOCÊ TEM QUE ENTRAR NA SEFAZ E ALTERAR O MUNICÍPIO / CIDADE MANUALMENTE => "LAJEADO" !')
            
            nova_janela('../../nfs_consultar/gerar_txt_nfe.php?id_nf=<?=$_GET['id_nf'];?>', 'GERAR_NFE', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
	}
    }
}

function livre_debito() {
    var livre_debito    = '<?=$livre_debito;?>'
    
    if(livre_debito == 'S') {
        //Esse botão só irá aparecer quando a NF for uma NF mesmo, nunca p/ SGD ...
        if(typeof(document.form.cmd_imprimir) == 'object') {
            document.form.cmd_imprimir.disabled     = true
            document.form.cmd_imprimir.className    = 'textdisabled'
        }
        alert('NÃO PODE GERAR BOLETO OU DUPLICATA - LIVRE DE DÉBITO !!!')
    }
}
</Script>
</head>
<body onload='livre_debito()'>
<form name='form'>
<?
    //Controlo essa variável em um hidden, porque temos problema quando submetemos esse Frame ...
    $parametro_velho = (empty($parametro_velho)) ? $parametro : $parametro_velho;
?>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
<?
        switch($opcao) {
            case 1://Significa que veio do Menu Abertas / Liberadas ...
            case 2://Significa que veio do Menu de Liberadas / Faturadas ...
            case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
                $caminho = 'alterar_imprimir.php';
            break;
            default://Significa que veio do Menu de Devolução ...
                $caminho = 'devolucao.php';
            break;
        }
?>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.parent.location = '<?=$caminho.$parametro_velho;?>'" class='botao'>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho' title='Cabe&ccedil;alho' onclick="nova_janela('../alterar_cabecalho?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'CABECALHO', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
//Significa que veio do Menu de Devolução
        if($opcao == 4) {
?>
        <input type='button' name='cmd_devolver' <?=$travar_botao;?> value='Devolver Itens' title='Devolver Itens' onclick='devolver_itens()' class='botao'>
<?
//Se for de Outros Menus ...
        }else {
?>
        <input type='button' name='cmd_incluir' <?=$controle_botao;?> value='Incluir Itens' title='Incluir Itens' onclick='incluir_itens()' class='botao'>

<?
        }
/*Significa que está Nota contém pelo menos 1 Item, e sendo assim eu posso exibir os botões p/ a alteração
e exclusão de Itens*/
        if($linhas > 0) {
?>
        <input type='button' name='cmd_excluir' <?=$controle_botao;?> <?=$travar_botao;?> value='Excluir Item(ns)' title='Excluir Item(ns)' onclick="nova_janela('excluir_itens.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'EXCLUIR_ITENS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <input type='button' name='cmd_outras' value='Outras Opções' title='Outras Opções' onclick="nova_janela('outras_opcoes.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'OUTRAS_OPCOES', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
            //Se o Menu acessado for pelo de Liberadas / Faturadas / Canceladas ou Devolução ...
            if($opcao == 2 || $opcao == 4) {
?>
        <input type='button' name='cmd_ajustes' <?=$travar_botao;?> value='Ajustes' title='Ajustes' onclick="ajustes()" class='botao'>
<?
            }
//Texto da NF ...
            if($id_empresa_nf != 4) {//Se a empresa for diferente de Grupo = '4', sempre exibe o Botão ...
?>
        <input type='button' name='cmd_texto_nota' <?=$travar_botao;?> value='Texto da Nota' title='Texto da Nota' onclick="nova_janela('../../nfs_consultar/preencher_texto_nf.php?id_nf=<?=$_GET['id_nf'];?>', 'TEXTO_NOTA', '', '', '', '', '350', '850', 'c', 'c', '', '', 's', 's', '', '', '')" style="color:brown" class='botao'>
<?
            }
            //Se o Menu acessado for pelo de Liberadas / Faturadas / Canceladas ou Devolução ...
            if($opcao == 2 || $opcao == 4) {
                /*Algumas regras p/ exibir o Botão 

                1) Empresa diferente de Grupo = '4' ...
                2) Utilizamos um Número de Nosso Talonário ...*/
                if($id_empresa_nf != 4 && $id_nf_num_nota > 0) {
                    /*3) Se a Nota Fiscal estiver com Status entre "Liberada p/ Faturar e Despachada" ...
                      4) Status "Devolução" e Cliente NÃO Optante pelo Simples Nacional ou ...
                      5) Status "Devolução" Cliente Optante pelo Simples Nacional e o SNF preenchido ...*/
                    if(($status >= 1 && $status <= 4) || ($status == 6 && $optante_simples_nacional == 'N' || ($optante_simples_nacional == 'S' && !empty($snf_devolvida)))) {
?>
        <input type='button' name='cmd_gerar_nfe' value='Gerar NFe' title='Gerar NFe' onclick='gerar_nfe()' style='color:red' class='botao'>
<?
                    }
                }
                
                //Nota Fiscal de Venda Originada de Encomenda para Entrega Futura, é a única situação da qual não se gera Duplicatas ...
                if($gerar_duplicatas == 'N') {//
                    $class_imprimir     = 'textdisabled';
                    $disabled_imprimir  = 'disabled';
                }else {
                    $class_imprimir     = 'botao';
                    $disabled_imprimir  = '';
                }
?>
        <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='<?=$class_imprimir;?>' <?=$disabled_imprimir;?>>
<?
            }
        }
        //Somente nessas instâncias, que aparece esse botão "Menu Abertas / Liberadas" ou "Devolução" ...
        if($opcao == 1 || $opcao == 4) {
?>
        <input type='button' name='cmd_imprimir_prenota' value='Imprimir Pré-Nota' title='Imprimir Pa ré-Nota' onclick="nova_janela('relatorio/imprimir_prenota.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$opcao;?>', 'IMPRIMIR_PRE_NOTA', '', '', '', '', 580, 900, 'c', 'c', '', '', 's', 's', '', '', '')" style="color:red" class='botao'>
<?
        }
        //nova_janela('etiqueta_teruya_lojista/etiqueta_teruya_lojista.php?id_nf=$_GET['id_nf'];, 'POP', '', '', '', '', 580, 900, 'c', 'c', '', '', 's', 's', '', '', '')
?>
        <input type='button' name='cmd_etiqueta_teruya_lojista' value='Etiqueta Teruya / Lojista' title='Etiqueta Teruya / Lojista' onclick="alert('DESABILITADO !!! CASO NECESSITE UTILIZAR ESSA OPÇÃO, UTILIZE A IMPRESSÃO DE ETIQUETAS DA EMBALAGEM !')" style="color:purple" class='botao'>
<?
        //Só exibo esse botão p/ os funcionários: Rivaldo '27', Agueda '32', Roberto '62', Tampelini '72' e Dárcio '98' porque programa ...
        $vetor_funcionarios_exibir_gerenciar = array(27, 32, 62, 72, 98);

        if(in_array($_SESSION['id_funcionario'], $vetor_funcionarios_exibir_gerenciar)) {
            //Opção6, significa que se deseja trazer todos os Clientes (Pendentes) e Faturáveis ...
?>
            <input type='button' name='cmd_gerenciar' value='Gerenciar' title='Gerenciar' onclick="window.parent.location = '../../../producao/programacao/estoque/gerenciar/consultar.php?passo=1&opcao6=1'" class='botao'>
<?
        }
?>
    </td>
</table>
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf']?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
</form>
</body>
</html>