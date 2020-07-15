<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        case 4://Significa que veio do Menu de Devolução 
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolução ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
    }
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf      = $_POST['id_nf'];
    $opcao      = $_POST['opcao'];
    $acao       = $_POST['acao'];
}else {
    $id_nf      = $_GET['id_nf'];
    $opcao      = $_GET['opcao'];
    $acao       = $_GET['acao'];
}

if(!empty($_POST['hdd_atualizar_dados_iniciais'])) {
    /*Aqui eu reabro um outro N., garantindo um N.º de Nota Fiscal que foi escolhida anteriormente 
    foi disponibilizada para um novo uso, mesmo que seje para a mesma Nota ...*/
    $sql = "UPDATE `nfs_num_notas` nnn 
            INNER JOIN `nfs` ON nfs.`id_nf_num_nota` = nnn.`id_nf_num_nota` 
            SET nnn.`nota_usado` = '0' WHERE nfs.`id_nf` = '$_POST[id_nf]' ";
    bancos::sql($sql);
    
/*********************************Controle com os Checkbox********************************/
    $data_emissao_snf   = data::datatodate($_POST['txt_data_emissao_snf'], '-');
    //Quando a NF é Livre de Débito, representa que esta não tem Custo, a mercadoria está indo como Amostra ...
    $livre_debito       = (!empty($_POST['chkt_livre_debito'])) ? 'S' : 'N';
    
    if($_POST['cmb_empresa'] == 1 || $_POST['cmb_empresa'] == 2) {//Albafer ou Tool Master ...
        if(!empty($_POST['cmb_num_nota_fiscal'])) {//Significa que o Usuário escolheu um N.º do Talonário de Nota Fiscal ...
            //Verifico se o usuário trocou o N.º do Talonário em comparado ao que estava antes ...
            if($_POST['cmb_num_nota_fiscal'] != $_POST['id_nf_num_nota']) {//Escolheu outro N.º ...
                //Reabro o N.º escolhido anteriormente do Talonário ...
                $sql = "UPDATE `nfs_num_notas` SET `nota_usado` = '0' WHERE `id_nf_num_nota` = '$_POST[id_nf_num_nota]' LIMIT 1 ";
                bancos::sql($sql);
            }else {//Não trocou o N.º, resolveu manter o mesmo ainda ...
                
            }
            //A partir daqui o Sistema irá assumir o Novo N.º escolhido na Combo pelo Usuário ...
            $id_nf_num_nota = $_POST['cmb_num_nota_fiscal'];
        }else {//O usuário ainda não escolheu nenhum N.º do Talonário de Nota Fiscal ...
            //Busco o primeiro N.º de Nota Fiscal disponível p/ a Empresa do Pedido ...
            $id_nf_num_nota = 'NULL';
        }
    }else {//Significa que a Empresa escolhida foi Grupo ...
        //Busco o primeiro N.º de Nota Fiscal disponível p/ a Empresa do Pedido ...
        $id_nf_num_nota     = faturamentos::verificar_numero_disponivel($_POST['cmb_empresa']);
    }

    //Nota Fiscal de Venda Originada de Encomenda para Entrega Futura, é a única situação da qual não se gera Duplicatas ...
    $gerar_duplicatas = ($_POST[cmb_natureza_operacao] == 'VOF') ? 'N' : 'S';

    /*Verifico se esse Número de Nota Fiscal que foi escolhido pelo Usuário ou sugerido pelo Sistema, 
    realmente não está sendo utilizado por uma outra Nota Fiscal ...*/
    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_nf_num_nota` = '$id_nf_num_nota' 
            AND `id_nf` <> '$_POST[id_nf]' LIMIT 1 ";
    $campos_numero_nf = bancos::sql($sql);
    if(count($campos_numero_nf) == 1) {//Realmente este N.º de Talonário consta em uso por outra Nota Fiscal, não posso permitir o uso do mesmo ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_empresa` = '$_POST[cmb_empresa]', `finalidade` = '$_POST[cmb_finalidade]', `natureza_operacao` = '$_POST[cmb_natureza_operacao]', `snf_devolvida` = '$_POST[txt_snf_devolvida]', `data_emissao_snf` = '$data_emissao_snf', `data_sys` = '".date('Y-m-d H:i:s')."', `livre_debito` = '$livre_debito', `gerar_duplicatas` = '$gerar_duplicatas' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);

        $mensagem = 'ESSE NÚMERO DE NOTA FISCAL JÁ FOI UTILIZADO POR OUTRA NOTA, ESCOLHA OUTRO NÚMERO !!!\n\nDEMAIS DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !';
    }else {//Esse N.º de Talonário realmente se encontra disponível, posso utilizá-lo normalmente ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_empresa` = '$_POST[cmb_empresa]', `id_nf_num_nota` = $id_nf_num_nota, `finalidade` = '$_POST[cmb_finalidade]', `natureza_operacao` = '$_POST[cmb_natureza_operacao]', `snf_devolvida` = '$_POST[txt_snf_devolvida]', `data_emissao_snf` = '$data_emissao_snf', `data_sys` = '".date('Y-m-d H:i:s')."', `livre_debito` = '$livre_debito', `gerar_duplicatas` = '$gerar_duplicatas' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);

        //Uma vez já vinculado esse N.º em Nota Fiscal, marco o mesmo como reservado ...
        faturamentos::gerar_numero_nf($_POST['cmb_empresa'], $id_nf_num_nota);

        $mensagem = 'DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !';
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem;?>')
        if(opener != null) opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
}

//Aqui eu trago dados da "id_nf" passado por parâmetro ...
$sql = "SELECT c.`id_pais`, c.`tipo_faturamento`, c.`cod_suframa`, c.`optante_simples_nacional`, 
        nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`finalidade`, nfs.`tipo_nfe_nfs`, 
        nfs.`natureza_operacao`, nfs.`snf_devolvida`, nfs.`data_emissao_snf`, nfs.`data_bl`, 
        nfs.`valor_dolar_dia`, nfs.`status`, nfs.`livre_debito` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
$campos                     = bancos::sql($sql);
$id_pais                    = $campos[0]['id_pais'];
$tipo_faturamento           = $campos[0]['tipo_faturamento'];
$cod_suframa                = $campos[0]['cod_suframa'];
$optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
$id_cliente                 = $campos[0]['id_cliente'];
$id_empresa_nf              = $campos[0]['id_empresa'];
$id_nf_num_nota             = $campos[0]['id_nf_num_nota'];
$finalidade                 = $campos[0]['finalidade'];
$tipo_nfe_nfs               = $campos[0]['tipo_nfe_nfs'];
$natureza_operacao          = $campos[0]['natureza_operacao'];
$snf_devolvida              = $campos[0]['snf_devolvida'];

$data_emissao_snf   = ($campos[0]['data_emissao_snf'] != '0000-00-00') ? data::datetodata($campos[0]['data_emissao_snf'], '/') : '';
$data_bl            = data::datetodata($campos[0]['data_bl'], '/');

$valor_dolar_dia    = number_format($campos[0]['valor_dolar_dia'], 4, ',', '.');
$status             = $campos[0]['status'];
$livre_debito       = $campos[0]['livre_debito'];

//Controle com as Datas de Emissão do N.º de NF selecionado ...
if(!empty($cmb_num_nota_fiscal) || !empty($id_nf_num_nota)) {
//Somente p/ a primeira vez em que carrega a Tela ...
    if(empty($cmb_num_nota_fiscal)) $cmb_num_nota_fiscal = $id_nf_num_nota;
//Aqui eu chamo a função de Talonário que controla tudo referente à parte de NF(s) ...
    $talonario                  = faturamentos::buscar_numero_ant_post_talonario($cmb_num_nota_fiscal);
    $data_emissao_anterior      = $talonario['data_emissao_anterior'];
    $numero_nf_anterior         = $talonario['numero_nf_anterior'];
    $data_emissao_posterior     = $talonario['data_emissao_posterior'];
    $numero_nf_posterior        = $talonario['numero_nf_posterior'];
}

//Aqui verifica o Tipo de Nota
if($id_empresa_nf == 1 || $id_empresa_nf == 2) {
    $nota_sgd   = 'N';//var surti efeito lá embaixo
    $tipo_nota  = ' (NF)';
}else {
    $nota_sgd   = 'S'; //var surti efeito lá embaixo
    $tipo_nota  = ' (SGD)';
}

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver não pode alterar 
a Empresa e o Tipo de Nota*/
$sql = "SELECT `id_nfs_item` 
        FROM `nfs_itens` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos_qtde_itens  = bancos::sql($sql);
$qtde_itens_nf      = count($campos_qtde_itens);

if($acao == 'L') {//Significa que essa Tela foi aberta somente p/ Modo Leitura ...
    $disabled       = 'disabled';
    $class          = 'textdisabled';
    $class_combo    = 'textdisabled';
    $width          = '100%';
}else {//Significa que essa Tela foi aberta como Modo Gravação ...
    $disabled       = '';
    $class          = 'caixadetexto';
    $class_combo    = 'combo';
    $width          = '95%';
}
?>
<html>
<head>
<title>.:: DADOS INICIAIS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var optante_simples_nacional    = '<?=$optante_simples_nacional;?>'
    var qtde_itens_nf               = eval('<?=$qtde_itens_nf;?>')
    
    if(qtde_itens_nf == 0) {//Quando não há Itens na NF ainda ...
//Empresa
        if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
            return false
        }
    }
/*****Só irá exibir esse objeto p/ as NF(s) de Devolução e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
//Se estiver habilitada a opção de NNF e o Cliente não é "Optante pelo Simples Nacional" ...
        if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'N') {
//Força o Nosso N.º de NF de Devolução apenas (SEFAZ) ...
            if(!combo('form', 'cmb_num_nota_fiscal', '', 'SELECIONE O N.º DA NOSSA NF DE DEVOLUÇÃO !')) {
                return false
            }
//Se estiver habilitada a opção de NNF e o Cliente é "Optante pelo Simples Nacional" ...
        }else if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'S') {
/*****Força o Nosso N.º de NF de Devolução e o N.º da NF de Devolução do Cliente ...*****/
//Força o Nosso N.º de NF de Devolução (SEFAZ) ...
            if(!combo('form', 'cmb_num_nota_fiscal', '', 'SELECIONE O N.º DA NOSSA NF DE DEVOLUÇÃO !')) {
                return false
            }
//Força o N.º da NF de Devolução do Cliente ...
            if(!texto('form', 'txt_snf_devolvida', '3', '1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,..*/_-| ', 'N.º DA NF DE DEVOLUÇÃO DO CLIENTE', '2')) {
                return false
            }
//Força o Preenchimento da Data de Emissão da NF ...
            if(!data('form', 'txt_data_emissao_snf', "4000", 'EMISSÃO DA NF DE DEVOLUÇÃO DO CLIENTE')) {
                return false
            }
        }else {//Habilitada a opção de SNF ...
//Força o N.º da NF de Devolução do Cliente apenas ...
            if(!texto('form', 'txt_snf_devolvida', '3', '1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,..*/_-| ', 'N.º DA NF DE DEVOLUÇÃO DO CLIENTE', '2')) {
                return false
            }
//Força o Preenchimento da Data de Emissão da NF ...
            if(!data('form', 'txt_data_emissao_snf', "4000", 'EMISSÃO DA NF DE DEVOLUÇÃO DO CLIENTE')) {
                return false
            }
        }
    }
/************************************************************************************/
//Finalidade ...
    if(document.form.cmb_finalidade.value == '') {
        alert('SELECIONE A FINALIDADE !')
        document.form.cmb_finalidade.focus()
        return false
    }
    desabilitar_tratar_objetos()
/***********************************************************/
//Igualo esse campo à 'S' de Sim p/ poder gravar na Base de Dados ...
    document.form.hdd_atualizar_dados_iniciais.value = 'S'
}

function recarregar_notas_fiscais() {
    var id_empresa_nota = eval('<?=$id_empresa_nf;?>')
    var qtde_itens_nf   = eval('<?=$qtde_itens_nf;?>')
    
    //Existir pelo menos 1 item em Nota Fiscal, nunca posso trocar de com Nota "Alba ou Tool" p/ sem Nota "Grupo" ...
    if(qtde_itens_nf > 0) {
        //A empresa da Nota Fiscal está gravada como Sendo 1 "Albafer" ou 2 "Tool Master" e o usuário resolveu colocar 4 "Grupo" ...
        if((id_empresa_nota == 1 || id_empresa_nota == 2) && document.form.cmb_empresa.value == 4) {
            alert('NÃO PODE SER COLOCADA ESSA EMPRESA DEVIDO ESTAR EM UM FATURAMENTO COM NOTA FISCAL !\n\nESTA EMPRESA NÃO É PERMITIDA !!!')
            document.form.cmb_empresa.value = id_empresa_nota
            return false
        }
        //A empresa da Nota Fiscal está gravada como Sendo 4 "Grupo" e o usuário resolveu colocar 1 "Albafer" ou 2 "Tool Master" ...
        if(id_empresa_nota == 4 && (document.form.cmb_empresa.value == 1 || document.form.cmb_empresa.value == 2)) {
            alert('NÃO PODE SER COLOCADA ESSA EMPRESA DEVIDO ESTAR EM UM FATURAMENTO SEM NOTA FISCAL !\n\nESTA EMPRESA NÃO É PERMITIDA !!!')
            document.form.cmb_empresa.value = id_empresa_nota
            return false
        }
    }
/*Sempre que trocar a Empresa, não posso manter gravado o N.º do Talonário que foi escolhido 
anteriormente p/ não dar problema ...*/
    document.form.cmb_num_nota_fiscal.value = ''
    desabilitar_tratar_objetos()
/***********************************************************/
//Igualo esse campo à 'S' de Sim p/ poder gravar na Base de Dados ...
    document.form.hdd_atualizar_dados_iniciais.value = 'S'
    document.form.submit()
}

function desabilitar_tratar_objetos() {
    document.form.cmb_natureza_operacao.disabled    = false
    document.form.cmb_finalidade.disabled           = false
    
    if(typeof(document.form.cmb_num_nota_fiscal) == 'object') document.form.cmb_num_nota_fiscal.disabled = false
    
    if(typeof(document.form.txt_snf_devolvida) == 'object') {
        document.form.txt_snf_devolvida.disabled    = false
        document.form.txt_data_emissao_snf.disabled = false
    }
}

function controlar_numero() {
    /*****Só irá exibir esse objeto p/ as NF(s) de Devolução e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
        var optante_simples_nacional = '<?=$optante_simples_nacional;?>'
        //Se estiver habilitada a opção de NNF e o Cliente não é "Optante pelo Simples Nacional" ...
        if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'N') {
            //Habilita Nosso N.º de NF de Devolução apenas ...
            document.form.cmb_num_nota_fiscal.disabled      = false
            var id_nf_num_nota = eval('<?=$id_nf_num_nota;?>')
            if(id_nf_num_nota == 0) {
                document.form.cmb_num_nota_fiscal.value     = ''
            }else {
                document.form.cmb_num_nota_fiscal.value     = id_nf_num_nota
            }
            //Designer de Habilitado ...
            document.form.cmb_num_nota_fiscal.className     = 'combo'
            document.form.cmb_num_nota_fiscal.focus()
            //Desabilitado ...
            document.form.txt_snf_devolvida.disabled        = true
            document.form.txt_snf_devolvida.value           = ''
            document.form.txt_data_emissao_snf.disabled     = true
            document.form.txt_data_emissao_snf.value        = ''
            //Designer de Desabilitado ...
            document.form.txt_snf_devolvida.className       = 'textdisabled'
            document.form.txt_data_emissao_snf.className    = 'textdisabled'
            //Se estiver habilitada a opção de NNF e o Cliente é "Optante pelo Simples Nacional" ...
        }else if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'S') {
            //Habilita Nosso N.º de NF de Devolução e o N.º da NF de Devolução do Cliente ...
            document.form.cmb_num_nota_fiscal.disabled      = false
            var id_nf_num_nota                              = eval('<?=$id_nf_num_nota;?>')
            if(id_nf_num_nota == 0) {
                document.form.cmb_num_nota_fiscal.value     = ''
            }else {
                document.form.cmb_num_nota_fiscal.value     = id_nf_num_nota
            }
            document.form.txt_snf_devolvida.disabled        = false
            document.form.txt_snf_devolvida.value           = '<?=$snf_devolvida;?>'
            document.form.txt_data_emissao_snf.disabled     = false
            document.form.txt_data_emissao_snf.value        = '<?=$data_emissao_snf;?>'
            //Designer de Habilitado ...
            document.form.cmb_num_nota_fiscal.className     = 'combo'
            document.form.txt_snf_devolvida.className       = 'caixadetexto'
            document.form.txt_data_emissao_snf.className    = 'caixadetexto'
            document.form.cmb_num_nota_fiscal.focus()
        }else {//Habilitada a opção de SNF ...
            //Habilita o N.º da NF de Devolução do Cliente apenas ...
            document.form.txt_snf_devolvida.disabled        = false
            document.form.txt_snf_devolvida.value           = '<?=$snf_devolvida;?>'
            document.form.txt_data_emissao_snf.disabled     = false
            document.form.txt_data_emissao_snf.value        = '<?=$data_emissao_snf;?>'
            //Designer de Habilitado ...
            document.form.txt_snf_devolvida.className       = 'caixadetexto'
            document.form.txt_data_emissao_snf.className    = 'caixadetexto'
            document.form.txt_snf_devolvida.focus()
            //Desabilitado
            document.form.cmb_num_nota_fiscal.disabled      = true
            document.form.cmb_num_nota_fiscal.value         = ''
            //Designer de Desabilitado ...
            document.form.cmb_num_nota_fiscal.className     = 'textdisabled'
        }
    }
}

function controlar_natureza_operacao() {
    //Se essa opção estiver marcada então verifico se o checkbox "Livre de Débito" também está marcado ...
    if(document.form.cmb_natureza_operacao.value == 'RAG') {//REMESSA DE AMOSTRA GRÁTIS ...
        if(!document.form.chkt_livre_debito.checked) {
            var resposta = confirm('ESSA NATUREZA DE OPERAÇÃO SÓ PODE SER MARCADA SE O CHECKBOX LIVRE DE DÉBITO ESTIVER MARCADO !!!\n\nDESEJA MARCAR ESTE CHECKBOX ?')
            if(resposta == true) {//Usuário autorizou a marcar o respectivo checkbox ...
                document.form.chkt_livre_debito.checked = true
            }else {//Como o usuário não autorizou a marcar o checkbox, volto a assumir nessa combo o valor inicial que estava gravado no BD ...
                document.form.cmb_natureza_operacao.value = '<?=$natureza_operacao;?>'
            }
        }
    }
}

function controlar_checkbox() {
    if(document.form.chkt_livre_debito.checked) {//Se esse checkbox estiver marcado, então a "Natureza de Operação" tem de ser "REMESSA DE AMOSTRA GRÁTIS" ...
        document.form.cmb_natureza_operacao.value = 'RAG'
    }else {//Senão eu faço com que essa combo "Natureza de Operação" sugira como valor inicial a opção de Vendas ...
        document.form.cmb_natureza_operacao.value = 'VEN'
    }
}

function selecionar_option() {
/*****Só irá exibir esse objeto p/ as NF(s) de Devolução e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
//Aqui eu faço uma verificação p/ saber qual option selecionar quando carregar a Tela ...
        var id_nf_num_nota = '<?=$id_nf_num_nota;?>'
        if(id_nf_num_nota != 0) {//Significa que é a Nossa própria NF de Entrada ...
            document.form.opt_numero_nf[0].checked = true
        }else {//Significa que a NF de Entrada do Vendedor ...
            document.form.opt_numero_nf[1].checked = true
        }
    }
/************************************************************************************/
}

/*Esse controle só irá fazer quando a Empresa for Alba ou Tool, lembrando o usuário
de fazer a NF de Devolução dentro dos padrões do Cliente ...*/
function optante_simples_nacional() {
//Verifico se existe N.º p/ Nota de Devolução, se não existir busca o outro Tipo de Numeração do Sistema ...
    var id_empresa_nota = eval('<?=$id_empresa_nf;?>')
    var id_nf_num_nota  = eval('<?=$id_nf_num_nota;?>')//Numeração Nossa - NNF ...

    if(id_empresa_nota != 4) {//Se a Empresa da NF for Alba ou Tool Master ...
        if(id_nf_num_nota == 0 && id_nf_num_nota == '') {//Significa que a NF ainda ñ tem N.º
            var resposta = confirm('DESEJA FAZER ESSA NF PARA O CLIENTE COMO OPTANTE SIMPLES NACIONAL ?\n\nNESSE CASO A NF DE DEVOLUÇÃO É FEITA SOMENTE USANDO O NNF DE DEVOLUÇÃO !')
            if(resposta == true) {//Significa que a NF tem que ser feita com o Nosso N.º
                document.form.opt_numero_nf[0].click()
//Nesse caso não pode ser digitado o N.º da NF de Devolução do Cliente, por isso travo a caixa ...
                document.form.opt_numero_nf[1].disabled = true
            }
        }
    }
}
</Script>
<?
    //Se essa Tela foi aberta como Modo Gravação, então eu chamo essas funções abaixo ...
    $onload = ($acao == 'G') ? 'selecionar_option();controlar_numero();optante_simples_nacional()' : 'selecionar_option()';
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<!--******Esse Hidden é de extrema importância porque aqui eu guardo o N.º de Nota Fiscal do Talonário 
que está sendo utilizado no momento ...******-->
<input type='hidden' name='id_nf_num_nota' value='<?=$id_nf_num_nota;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='hdd_atualizar_dados_iniciais'>
<!--*************************************-->
<table width='<?=$width;?>' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            DADOS INICIAIS
            <?
                /*Significa que essa Tela foi aberta somente p/ Modo Leitura e que a mesma foi 
                acessada do Menu Em Aberto / Liberadas ou Devolução ...*/
                if($acao == 'L' && ($opcao == 1 || $opcao == 4)) {
            ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('dados_iniciais.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DADOS_INICIAIS', '', '', '', '', '290', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Dados Gerais' alt='Alterar Dados Gerais'>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
        <?
/*Se ainda não foi gravada nenhuma Empresa na NF e o Tipo de Faturamento é por Qualquer Empresa,
sugiro a Empresa definida nas variáveis ...*/
            if($id_empresa_nf == 0 && $tipo_faturamento == 'Q') $id_empresa_nf = intval(genericas::variavel(47));
            /*Se a NF estiver sem Itens ou estiver com Itens desde que o cadastro do Cliente seja = "Qualquer Empresa" 
            e a empresa escolhida em NF seja diferente de Grupo então habilito a combo de Empresa ...*/
            if($qtde_itens_nf == 0 || ($tipo_faturamento == 'Q' && $id_empresa != 4)) {
//Aqui busca as empresas
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                $campos_empresas    = bancos::sql($sql);
                $linhas             = count($campos_empresas);
        ?>
                <select name='cmb_empresa' title='Selecione a Empresa' onchange='return recarregar_notas_fiscais()' class='<?=$class_combo;?>' <?=$disabled;?>>
                    <option value='' style='color:red'>SELECIONE</option>
        <?
                for($i = 0; $i < $linhas; $i++) {
                    if($campos_empresas[$i]['id_empresa'] == 1 || $campos_empresas[$i]['id_empresa'] == 2) {
                        $tipo_nota = ' (NF)';
                    }else {
                        $tipo_nota = ' (SGD)';
                    }
//Significa que o usuário manipulou algum contato no Pop-UP
                    if(!empty($cmb_empresa)) {
                        if($cmb_empresa == $campos_empresas[$i]['id_empresa']) {
        ?>
                        <option value="<?=$campos_empresas[$i]['id_empresa'];?>" selected><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                        }else {
        ?>
                        <option value="<?=$campos_empresas[$i]['id_empresa'];?>"><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                        }
//Até então não foi feito nenhuma manipulação referente algum contato no Pop-UP
                    }else {//Só lista
                        if($id_empresa_nf == $campos_empresas[$i]['id_empresa']) {
        ?>
                        <option value="<?=$campos_empresas[$i]['id_empresa'];?>" selected><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                        }else {
        ?>
                        <option value="<?=$campos_empresas[$i]['id_empresa'];?>"><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                        }
                    }
                }
        ?>
                </select>
        <?
            }else {//Tem 1 item cadastrado
                $tipo_nota = ($id_empresa_nf == 1 || $id_empresa_nf == 2) ? ' (NF)' : ' (SGD)';

                $sql = "SELECT `nomefantasia` 
                        FROM `empresas` 
                        WHERE `id_empresa` = '$id_empresa_nf' LIMIT 1 ";
                $campos = bancos::sql($sql);
                echo $campos[0]['nomefantasia'].$tipo_nota;
//Aqui eu coloco esse objeto para não dar erro de programação no PHP
        ?>
                <input type='hidden' name='cmb_empresa' value='<?=$id_empresa_nf;?>'>
        <?
            }
        ?>
            &nbsp;-&nbsp;
            <font color='darkblue'>
                <b>
                <?
                    if($tipo_faturamento == 1) {//Significa que o Cliente fatura tudo pela Albafér ...
                        echo 'TUDO PELA ALBAFER';
                    }else if($tipo_faturamento == 2) {//Significa que o Cliente fatura tudo pela Tool Master ...
                        echo 'TUDO PELA TOOL MASTER';
                    }else if($tipo_faturamento == 'Q') {//Significa que o Cliente fatura por Ambas Empresas - Indiferente ...
                        echo 'QUALQUER EMPRESA';
                    }else if($tipo_faturamento == 'S') {//Significa que o Cliente fatura por Ambas Empresas - apenas itens da empresa escolhida ...
                        echo 'SEPARADAMENTE';
                    }
                ?>
                </b>
            </font>
        </td>
    </tr>
<?
/******************************************************************************/
/******************************Controles Especiais*****************************/
/******************************************************************************/
//1) 
    /*Quando a Nota Fiscal for de Saída "diferente de 6" existem opções de "Entrada e Saída", quando 
    for de Devolução $status = 6, só existe a opção de Entrada o que faz desnecessário exibir a 
    linha abaixo ...*/
    if($status != 6) {//Se a nota Fiscal for de Saída ...
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Nota:</b>
        </td>
        <td>
            <?
                if($tipo_nfe_nfs == 'S') {//NF de Saída ...
                    $checkeds = 'checked';
                }else {//NF de Entrada ...
                    $checkede = 'checked';
                }
            ?>
            <input type='radio' name='opt_nota' value='E' id='E' <?=$checkede;?> <?=$disabled;?>><label for='E'>Entrada</label>
            <input type='radio' name='opt_nota' value='S' id='S' <?=$checkeds;?> <?=$disabled;?>><label for='S'>Saída</label>
        </td>
    </tr>
<?
    }
    
    //Mesmo que a NF esteja aberta, só pode trocar a Natureza de Operação quando a mesma não conter Itens ...
    if($qtde_itens_nf == 0) {
        $disabled_natureza_operacao = $disabled;
        $class_natureza_operacao    = $class_combo;
    }else {//Como existe(m) item(ns) na NF, então eu não posso trocar p/ outra opção da "Natureza de Operação" ...
        $disabled_natureza_operacao = 'disabled';
        $class_natureza_operacao    = 'textdisabled';
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Natureza de Operação:</b>
        </td>
        <td>
            <select name='cmb_natureza_operacao' title='Selecione a Natureza de Operação' onchange='controlar_natureza_operacao()' class='<?=$class_natureza_operacao;?>' <?=$disabled_natureza_operacao;?>>
                <?
                    if($natureza_operacao == 'VEN') {
                        $selected_ven = 'selected';
                    }else if($natureza_operacao == 'DEV') {
                        $selected_dev = 'selected';
                    }else if($natureza_operacao == 'PSE') {
                        $selected_pse = 'selected';
                    }else if($natureza_operacao == 'BON') {
                        $selected_bon = 'selected';
                    }else if($natureza_operacao == 'VOF') {
                        $selected_vof = 'selected';
                    }else if($natureza_operacao == 'REC') {
                        $selected_rec = 'selected';
                    }else if($natureza_operacao == 'RAG') {
                        $selected_rag = 'selected';
                    }
                ?>
                <option value='VEN' <?=$selected_ven;?>>VEN - VENDA</option>
                <option value='DEV' <?=$selected_dev;?>>DEV - DEVOLUÇÃO DE VENDA</option>
                <option value='PSE' <?=$selected_pse;?>>PSE - PRESTAÇÃO DE SERVIÇOS</option>
                <option value='BON' <?=$selected_bon;?>>BON - REMESSA EM BONIFICAÇÃO</option>
                <option value='VOF' <?=$selected_vof;?>>VOF - VENDA ORIGINADA DE ENCOMENDA PARA ENTREGA FUTURA</option>
                <option value='REC' <?=$selected_rec;?>>REC - ENTRADA DE MERCADORIA DEVIDO A RECUSA DO CLIENTE</option>
                <option value='RAG' <?=$selected_rag;?>>RAG - REMESSA DE AMOSTRA GRÁTIS</option>
            </select>
        </td>
    </tr>
<?
//2)
    //Só para NF´s de Devolução e Empresas como Albafer ou Tool Master que aparecerão essas opções ...
    if($status == 6 && $id_empresa_nf != 4) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Tipo da NF de Devolução: </b>
            </font>
        </td>
        <td>
            <input type='radio' name='opt_numero_nf' value='1' onclick='controlar_numero()' id='label1' <?=$disabled;?>>
            <label for='label1'>
                <font title='Nosso N.º de Nota Fiscal' style='cursor:help'>
                    NNF
                </font>
            </label>
            &nbsp;
            <input type='radio' name='opt_numero_nf' value='2' onclick='controlar_numero()' id='label2' <?=$disabled;?>>
            <label for='label2'>
                <font title='N.º de Nota Fiscal do Cliente' style='cursor:help'>
                    SNF
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>NF(s) Referenciada(s): </b>
            </font>
        </td>
        <td>
            <font color='darkblue'>
        <?
            //Nesse vetor eu vou armazenar todas as NF(s) que estão atrelados a esta NF de Devolução ...
            $vetor_nfs = array();
            
            //Busco os Itens dessa Nota Fiscal que foi Devolvida ...
            $sql = "SELECT `id_nf_item_devolvida` 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '$id_nf' ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
            for($i = 0; $i < $linhas_itens; $i++) {
                //Busco o id_nf da Nota Fiscal de Saída ...
                $sql = "SELECT nfs.`id_nf`, nfs.`chave_acesso` 
                        FROM `nfs_itens` nfsi 
                        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                        WHERE nfsi.`id_nfs_item` = '".$campos_itens[$i]['id_nf_item_devolvida']."' LIMIT 1 ";
                $campos_nfs = bancos::sql($sql);
                //Insiro nesse $vetor_nfs o id_nf corrente ...
                if(!in_array($campos_nfs[0]['id_nf'], $vetor_nfs)) {
                    array_push($vetor_nfs, $campos_nfs[0]['id_nf']);
                    $vetor_codigo_barras[$campos_nfs[0]['id_nf']] = $campos_nfs[0]['chave_acesso'];
                }
            }
            //Aqui eu faço Tratamento com a Parte das NF(s) ...
            if(count($vetor_nfs) > 0) {//Se existir pelo menos 1 NF atrelada, então ...
                for($i = 0; $i < count($vetor_nfs); $i++) echo '<b>N.º '.faturamentos::buscar_numero_nf($vetor_nfs[$i], 'S').' - Chave de Acsso: '.$vetor_codigo_barras[$campos_nfs[0]['id_nf']].'</b><br/>';
            }
        ?>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.º da NF de Saída: </b>
            </font>
        </td>
        <td>
        <?
/************************************************************************************************/
                /*Essa função tem por objetivo trazer apenas os N.ºs de Talonário de Nota Fiscal 
                disponíveis p/ trabalho ...*/
                faturamentos::verificar_numero_disponivel($id_empresa_nf);

//Se a Empresa for diferente de Grupo então segue ...
                if($id_empresa_nf != 4) {
                    if($natureza_operacao == 'PSE') {//Nesse Tipo de Nota Fiscal utilizamos o N.º do Governo ...
                        //faturamentos::gerar_numero_nf($id_empresa_nf, 0, 'S');
                        //Busca de todos os N.ºs de Prestação de Serviço que estejam em aberto ...
                        $sql = "SELECT `id_nf_num_nota`, `numero_nf` 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'S' ORDER BY numero_nf ";
                    }else {//Em outro Tipo de NF utilizamos nosso número ...
                        //faturamentos::gerar_numero_nf($id_empresa_nf);
                        //Busca de todos os N.ºs que estejam em aberto da Empresa selecionada pelo usuário ...
                        $sql = "SELECT `id_nf_num_nota`, `numero_nf` 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'N' ORDER BY numero_nf ";
                    }
                    bancos::sql($sql);
                ?>
                <select name='cmb_num_nota_fiscal' title='Selecione o Número da Nota Fiscal' class='<?=$class_combo;?>' <?=$disabled;?>>
                    <?=combos::combo($sql, $id_nf_num_nota);?>
                </select>
                <?
/*Se eu tiver algum N.º de Nota selecionado na combo, então eu apresento o N.º Anterior e N.º Posterior 
da que está selecionada na combo*/
                    if(!empty($numero_nf_anterior) || !empty($id_nf_num_nota)) {
        ?>
                &nbsp;
                <font title='N.º Anterior de Nota Fiscal' style='cursor:help'>
                        <b>N.º Ant: </b>
                </font>
                <?=$numero_nf_anterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_anterior, '/');?>
        <?
//Se tiver N.º Posterior, então eu exibo este também ...
                        if(!empty($numero_nf_posterior)) {
        ?>
                &nbsp;|&nbsp;

                <font title='N.º Posterior de Nota Fiscal' style='cursor:help'>
                    <b>N.º Post: </b>
                </font>
                <?=$numero_nf_posterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_posterior, '/');?>
        <?
                        }
                    }
                }else {//Quando for SGD - coloco esse objeto para não dar erro de programação ...
                    echo faturamentos::buscar_numero_nf($id_nf, 'S');//Apenas apresentação do N.º do Talonário ...
        ?>
                    <input type='hidden' name='cmb_num_nota_fiscal' value='<?=$id_nf_num_nota;?>'>
        <?
                }
        ?>
            <font color='red'>
                <b>(NF acessada)</b>
            </font>
        </td>
    </tr>
<?
//3)
    //Só para NF´s de Devolução e Empresas como Albafer ou Tool Master que aparecerá essa opção ...
    if($status == 6 && $id_empresa_nf != 4) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.º da NF de Devolução do Cliente: </b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_snf_devolvida' value='<?=$snf_devolvida;?>' title='Digite a SNF Devolvida' size='16' maxlength='15' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;-&nbsp;
            <font color='darkblue'>
                <b>Data de Emissão da NF do Cliente: </b>
            </font>
            <input type='text' name='txt_data_emissao_snf' value='<?=$data_emissao_snf;?>' title='Digite a Data de Saída Entrada' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_emissao_snf.disabled == false) {javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_snf&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<?
    }

//Mesmo que a NF esteja aberta, só pode trocar a Finalidade quando a mesma não conter Itens ...
    if($qtde_itens_nf == 0) {
        $disabled_finalidade    = $disabled;
        $class_finalidade       = $class_combo;
    }else {//Como existe(m) item(ns) na NF, então eu não posso trocar p/ outra opção da "Finalidade" ...
        $disabled_finalidade    = 'disabled';
        $class_finalidade       = 'textdisabled';
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Finalidade:</b>
        </td>
        <td>
            <select name='cmb_finalidade' title='Selecione a Finalidade' class='<?=$class_finalidade;?>' <?=$disabled_finalidade;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
//Significa que o usuário manipulou algum contato no Pop-UP
                    if(!empty($cmb_finalidade)) {
                        if($cmb_finalidade == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($cmb_finalidade == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda           = 'selected';
                        }
                    }else {
                        if($finalidade == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($finalidade == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda           = 'selected';
                        }
                    }
                ?>
                <option value='C' <?=$selected_consumo;?>>CONSUMO</option>
                <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZAÇÃO</option>
                <option value='R' <?=$selected_revenda;?>>REVENDA</option>
            </select>
            <?
                //Significa que a Nota Fiscal é Livre de Débito ...
                $checked_livre_debito = ($livre_debito == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_livre_debito' value='S' id='livre_debito' onclick='controlar_checkbox()' class='checkbox' <?=$checked_livre_debito;?>>
            <label for='livre_debito'>
                <font color='darkblue'>
                    <b>Livre de Débito Propag / Mkt</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Dólar da Nota:
        </td>
        <td>
            <?=$valor_dolar_dia;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Gravação ...
            ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_cliente_transportadora.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
            <?
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
    if($status == 6) {//Somente quando a Nota Fiscal for de Devolução que mostro os dizeres abaixo ... 
?>
<pre>
<b><font color='red'>Lembre-se de verificar o Texto da Nota.</font></b>
<font color='darkblue'><b>
Cliente => "Optante pelo Simples Nacional":

    Marcada a opção de NNF:

        * Força o Nosso N.º de NF de Devolução (SEFAZ);
        * Força o N.º da NF de Devolução do Cliente.

    Marcada a opção de SNF:

        * Força o N.º da NF de Devolução do Cliente apenas.

Cliente => NÃO "Optante pelo Simples Nacional":

    Marcada a opção de NNF:

        * Força o Nosso N.º de NF de Devolução apenas (SEFAZ);

<font color='brown'>
*** Quando não valer a NF do Cliente <b>"SNF"</b>, então tem que aparecer os dizeres 
da Natureza de Operação no Campo Texto da NF.
</font>
</b></font>
</pre>
<?
    }
?>