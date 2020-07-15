<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');

/*Eu tenho esse desvio aki para n�o verificar a sess�o desse arkivo, fa�o isso pq esse arquivo aki � um 
pop-up em outras partes do sistema e se eu n�o fizer esse desvio d� erro de permiss�o*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        case 4://Significa que veio do Menu de Devolu��o 
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolu��o ...
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
    /*Aqui eu reabro um outro N., garantindo um N.� de Nota Fiscal que foi escolhida anteriormente 
    foi disponibilizada para um novo uso, mesmo que seje para a mesma Nota ...*/
    $sql = "UPDATE `nfs_num_notas` nnn 
            INNER JOIN `nfs` ON nfs.`id_nf_num_nota` = nnn.`id_nf_num_nota` 
            SET nnn.`nota_usado` = '0' WHERE nfs.`id_nf` = '$_POST[id_nf]' ";
    bancos::sql($sql);
    
/*********************************Controle com os Checkbox********************************/
    $data_emissao_snf   = data::datatodate($_POST['txt_data_emissao_snf'], '-');
    //Quando a NF � Livre de D�bito, representa que esta n�o tem Custo, a mercadoria est� indo como Amostra ...
    $livre_debito       = (!empty($_POST['chkt_livre_debito'])) ? 'S' : 'N';
    
    if($_POST['cmb_empresa'] == 1 || $_POST['cmb_empresa'] == 2) {//Albafer ou Tool Master ...
        if(!empty($_POST['cmb_num_nota_fiscal'])) {//Significa que o Usu�rio escolheu um N.� do Talon�rio de Nota Fiscal ...
            //Verifico se o usu�rio trocou o N.� do Talon�rio em comparado ao que estava antes ...
            if($_POST['cmb_num_nota_fiscal'] != $_POST['id_nf_num_nota']) {//Escolheu outro N.� ...
                //Reabro o N.� escolhido anteriormente do Talon�rio ...
                $sql = "UPDATE `nfs_num_notas` SET `nota_usado` = '0' WHERE `id_nf_num_nota` = '$_POST[id_nf_num_nota]' LIMIT 1 ";
                bancos::sql($sql);
            }else {//N�o trocou o N.�, resolveu manter o mesmo ainda ...
                
            }
            //A partir daqui o Sistema ir� assumir o Novo N.� escolhido na Combo pelo Usu�rio ...
            $id_nf_num_nota = $_POST['cmb_num_nota_fiscal'];
        }else {//O usu�rio ainda n�o escolheu nenhum N.� do Talon�rio de Nota Fiscal ...
            //Busco o primeiro N.� de Nota Fiscal dispon�vel p/ a Empresa do Pedido ...
            $id_nf_num_nota = 'NULL';
        }
    }else {//Significa que a Empresa escolhida foi Grupo ...
        //Busco o primeiro N.� de Nota Fiscal dispon�vel p/ a Empresa do Pedido ...
        $id_nf_num_nota     = faturamentos::verificar_numero_disponivel($_POST['cmb_empresa']);
    }

    //Nota Fiscal de Venda Originada de Encomenda para Entrega Futura, � a �nica situa��o da qual n�o se gera Duplicatas ...
    $gerar_duplicatas = ($_POST[cmb_natureza_operacao] == 'VOF') ? 'N' : 'S';

    /*Verifico se esse N�mero de Nota Fiscal que foi escolhido pelo Usu�rio ou sugerido pelo Sistema, 
    realmente n�o est� sendo utilizado por uma outra Nota Fiscal ...*/
    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_nf_num_nota` = '$id_nf_num_nota' 
            AND `id_nf` <> '$_POST[id_nf]' LIMIT 1 ";
    $campos_numero_nf = bancos::sql($sql);
    if(count($campos_numero_nf) == 1) {//Realmente este N.� de Talon�rio consta em uso por outra Nota Fiscal, n�o posso permitir o uso do mesmo ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_empresa` = '$_POST[cmb_empresa]', `finalidade` = '$_POST[cmb_finalidade]', `natureza_operacao` = '$_POST[cmb_natureza_operacao]', `snf_devolvida` = '$_POST[txt_snf_devolvida]', `data_emissao_snf` = '$data_emissao_snf', `data_sys` = '".date('Y-m-d H:i:s')."', `livre_debito` = '$livre_debito', `gerar_duplicatas` = '$gerar_duplicatas' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);

        $mensagem = 'ESSE N�MERO DE NOTA FISCAL J� FOI UTILIZADO POR OUTRA NOTA, ESCOLHA OUTRO N�MERO !!!\n\nDEMAIS DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !';
    }else {//Esse N.� de Talon�rio realmente se encontra dispon�vel, posso utiliz�-lo normalmente ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_empresa` = '$_POST[cmb_empresa]', `id_nf_num_nota` = $id_nf_num_nota, `finalidade` = '$_POST[cmb_finalidade]', `natureza_operacao` = '$_POST[cmb_natureza_operacao]', `snf_devolvida` = '$_POST[txt_snf_devolvida]', `data_emissao_snf` = '$data_emissao_snf', `data_sys` = '".date('Y-m-d H:i:s')."', `livre_debito` = '$livre_debito', `gerar_duplicatas` = '$gerar_duplicatas' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);

        //Uma vez j� vinculado esse N.� em Nota Fiscal, marco o mesmo como reservado ...
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

//Aqui eu trago dados da "id_nf" passado por par�metro ...
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

//Controle com as Datas de Emiss�o do N.� de NF selecionado ...
if(!empty($cmb_num_nota_fiscal) || !empty($id_nf_num_nota)) {
//Somente p/ a primeira vez em que carrega a Tela ...
    if(empty($cmb_num_nota_fiscal)) $cmb_num_nota_fiscal = $id_nf_num_nota;
//Aqui eu chamo a fun��o de Talon�rio que controla tudo referente � parte de NF(s) ...
    $talonario                  = faturamentos::buscar_numero_ant_post_talonario($cmb_num_nota_fiscal);
    $data_emissao_anterior      = $talonario['data_emissao_anterior'];
    $numero_nf_anterior         = $talonario['numero_nf_anterior'];
    $data_emissao_posterior     = $talonario['data_emissao_posterior'];
    $numero_nf_posterior        = $talonario['numero_nf_posterior'];
}

//Aqui verifica o Tipo de Nota
if($id_empresa_nf == 1 || $id_empresa_nf == 2) {
    $nota_sgd   = 'N';//var surti efeito l� embaixo
    $tipo_nota  = ' (NF)';
}else {
    $nota_sgd   = 'S'; //var surti efeito l� embaixo
    $tipo_nota  = ' (SGD)';
}

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver n�o pode alterar 
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
}else {//Significa que essa Tela foi aberta como Modo Grava��o ...
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
    
    if(qtde_itens_nf == 0) {//Quando n�o h� Itens na NF ainda ...
//Empresa
        if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
            return false
        }
    }
/*****S� ir� exibir esse objeto p/ as NF(s) de Devolu��o e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
//Se estiver habilitada a op��o de NNF e o Cliente n�o � "Optante pelo Simples Nacional" ...
        if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'N') {
//For�a o Nosso N.� de NF de Devolu��o apenas (SEFAZ) ...
            if(!combo('form', 'cmb_num_nota_fiscal', '', 'SELECIONE O N.� DA NOSSA NF DE DEVOLU��O !')) {
                return false
            }
//Se estiver habilitada a op��o de NNF e o Cliente � "Optante pelo Simples Nacional" ...
        }else if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'S') {
/*****For�a o Nosso N.� de NF de Devolu��o e o N.� da NF de Devolu��o do Cliente ...*****/
//For�a o Nosso N.� de NF de Devolu��o (SEFAZ) ...
            if(!combo('form', 'cmb_num_nota_fiscal', '', 'SELECIONE O N.� DA NOSSA NF DE DEVOLU��O !')) {
                return false
            }
//For�a o N.� da NF de Devolu��o do Cliente ...
            if(!texto('form', 'txt_snf_devolvida', '3', '1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,..*/_-| ', 'N.� DA NF DE DEVOLU��O DO CLIENTE', '2')) {
                return false
            }
//For�a o Preenchimento da Data de Emiss�o da NF ...
            if(!data('form', 'txt_data_emissao_snf', "4000", 'EMISS�O DA NF DE DEVOLU��O DO CLIENTE')) {
                return false
            }
        }else {//Habilitada a op��o de SNF ...
//For�a o N.� da NF de Devolu��o do Cliente apenas ...
            if(!texto('form', 'txt_snf_devolvida', '3', '1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,..*/_-| ', 'N.� DA NF DE DEVOLU��O DO CLIENTE', '2')) {
                return false
            }
//For�a o Preenchimento da Data de Emiss�o da NF ...
            if(!data('form', 'txt_data_emissao_snf', "4000", 'EMISS�O DA NF DE DEVOLU��O DO CLIENTE')) {
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
//Igualo esse campo � 'S' de Sim p/ poder gravar na Base de Dados ...
    document.form.hdd_atualizar_dados_iniciais.value = 'S'
}

function recarregar_notas_fiscais() {
    var id_empresa_nota = eval('<?=$id_empresa_nf;?>')
    var qtde_itens_nf   = eval('<?=$qtde_itens_nf;?>')
    
    //Existir pelo menos 1 item em Nota Fiscal, nunca posso trocar de com Nota "Alba ou Tool" p/ sem Nota "Grupo" ...
    if(qtde_itens_nf > 0) {
        //A empresa da Nota Fiscal est� gravada como Sendo 1 "Albafer" ou 2 "Tool Master" e o usu�rio resolveu colocar 4 "Grupo" ...
        if((id_empresa_nota == 1 || id_empresa_nota == 2) && document.form.cmb_empresa.value == 4) {
            alert('N�O PODE SER COLOCADA ESSA EMPRESA DEVIDO ESTAR EM UM FATURAMENTO COM NOTA FISCAL !\n\nESTA EMPRESA N�O � PERMITIDA !!!')
            document.form.cmb_empresa.value = id_empresa_nota
            return false
        }
        //A empresa da Nota Fiscal est� gravada como Sendo 4 "Grupo" e o usu�rio resolveu colocar 1 "Albafer" ou 2 "Tool Master" ...
        if(id_empresa_nota == 4 && (document.form.cmb_empresa.value == 1 || document.form.cmb_empresa.value == 2)) {
            alert('N�O PODE SER COLOCADA ESSA EMPRESA DEVIDO ESTAR EM UM FATURAMENTO SEM NOTA FISCAL !\n\nESTA EMPRESA N�O � PERMITIDA !!!')
            document.form.cmb_empresa.value = id_empresa_nota
            return false
        }
    }
/*Sempre que trocar a Empresa, n�o posso manter gravado o N.� do Talon�rio que foi escolhido 
anteriormente p/ n�o dar problema ...*/
    document.form.cmb_num_nota_fiscal.value = ''
    desabilitar_tratar_objetos()
/***********************************************************/
//Igualo esse campo � 'S' de Sim p/ poder gravar na Base de Dados ...
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
    /*****S� ir� exibir esse objeto p/ as NF(s) de Devolu��o e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
        var optante_simples_nacional = '<?=$optante_simples_nacional;?>'
        //Se estiver habilitada a op��o de NNF e o Cliente n�o � "Optante pelo Simples Nacional" ...
        if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'N') {
            //Habilita Nosso N.� de NF de Devolu��o apenas ...
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
            //Se estiver habilitada a op��o de NNF e o Cliente � "Optante pelo Simples Nacional" ...
        }else if(document.form.opt_numero_nf[0].checked == true && optante_simples_nacional == 'S') {
            //Habilita Nosso N.� de NF de Devolu��o e o N.� da NF de Devolu��o do Cliente ...
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
        }else {//Habilitada a op��o de SNF ...
            //Habilita o N.� da NF de Devolu��o do Cliente apenas ...
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
    //Se essa op��o estiver marcada ent�o verifico se o checkbox "Livre de D�bito" tamb�m est� marcado ...
    if(document.form.cmb_natureza_operacao.value == 'RAG') {//REMESSA DE AMOSTRA GR�TIS ...
        if(!document.form.chkt_livre_debito.checked) {
            var resposta = confirm('ESSA NATUREZA DE OPERA��O S� PODE SER MARCADA SE O CHECKBOX LIVRE DE D�BITO ESTIVER MARCADO !!!\n\nDESEJA MARCAR ESTE CHECKBOX ?')
            if(resposta == true) {//Usu�rio autorizou a marcar o respectivo checkbox ...
                document.form.chkt_livre_debito.checked = true
            }else {//Como o usu�rio n�o autorizou a marcar o checkbox, volto a assumir nessa combo o valor inicial que estava gravado no BD ...
                document.form.cmb_natureza_operacao.value = '<?=$natureza_operacao;?>'
            }
        }
    }
}

function controlar_checkbox() {
    if(document.form.chkt_livre_debito.checked) {//Se esse checkbox estiver marcado, ent�o a "Natureza de Opera��o" tem de ser "REMESSA DE AMOSTRA GR�TIS" ...
        document.form.cmb_natureza_operacao.value = 'RAG'
    }else {//Sen�o eu fa�o com que essa combo "Natureza de Opera��o" sugira como valor inicial a op��o de Vendas ...
        document.form.cmb_natureza_operacao.value = 'VEN'
    }
}

function selecionar_option() {
/*****S� ir� exibir esse objeto p/ as NF(s) de Devolu��o e Empresas Alba ou Tool*****/
    if(typeof(document.form.opt_numero_nf) == 'object') {
//Aqui eu fa�o uma verifica��o p/ saber qual option selecionar quando carregar a Tela ...
        var id_nf_num_nota = '<?=$id_nf_num_nota;?>'
        if(id_nf_num_nota != 0) {//Significa que � a Nossa pr�pria NF de Entrada ...
            document.form.opt_numero_nf[0].checked = true
        }else {//Significa que a NF de Entrada do Vendedor ...
            document.form.opt_numero_nf[1].checked = true
        }
    }
/************************************************************************************/
}

/*Esse controle s� ir� fazer quando a Empresa for Alba ou Tool, lembrando o usu�rio
de fazer a NF de Devolu��o dentro dos padr�es do Cliente ...*/
function optante_simples_nacional() {
//Verifico se existe N.� p/ Nota de Devolu��o, se n�o existir busca o outro Tipo de Numera��o do Sistema ...
    var id_empresa_nota = eval('<?=$id_empresa_nf;?>')
    var id_nf_num_nota  = eval('<?=$id_nf_num_nota;?>')//Numera��o Nossa - NNF ...

    if(id_empresa_nota != 4) {//Se a Empresa da NF for Alba ou Tool Master ...
        if(id_nf_num_nota == 0 && id_nf_num_nota == '') {//Significa que a NF ainda � tem N.�
            var resposta = confirm('DESEJA FAZER ESSA NF PARA O CLIENTE COMO OPTANTE SIMPLES NACIONAL ?\n\nNESSE CASO A NF DE DEVOLU��O � FEITA SOMENTE USANDO O NNF DE DEVOLU��O !')
            if(resposta == true) {//Significa que a NF tem que ser feita com o Nosso N.�
                document.form.opt_numero_nf[0].click()
//Nesse caso n�o pode ser digitado o N.� da NF de Devolu��o do Cliente, por isso travo a caixa ...
                document.form.opt_numero_nf[1].disabled = true
            }
        }
    }
}
</Script>
<?
    //Se essa Tela foi aberta como Modo Grava��o, ent�o eu chamo essas fun��es abaixo ...
    $onload = ($acao == 'G') ? 'selecionar_option();controlar_numero();optante_simples_nacional()' : 'selecionar_option()';
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<!--******Esse Hidden � de extrema import�ncia porque aqui eu guardo o N.� de Nota Fiscal do Talon�rio 
que est� sendo utilizado no momento ...******-->
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
                acessada do Menu Em Aberto / Liberadas ou Devolu��o ...*/
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
/*Se ainda n�o foi gravada nenhuma Empresa na NF e o Tipo de Faturamento � por Qualquer Empresa,
sugiro a Empresa definida nas vari�veis ...*/
            if($id_empresa_nf == 0 && $tipo_faturamento == 'Q') $id_empresa_nf = intval(genericas::variavel(47));
            /*Se a NF estiver sem Itens ou estiver com Itens desde que o cadastro do Cliente seja = "Qualquer Empresa" 
            e a empresa escolhida em NF seja diferente de Grupo ent�o habilito a combo de Empresa ...*/
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
//Significa que o usu�rio manipulou algum contato no Pop-UP
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
//At� ent�o n�o foi feito nenhuma manipula��o referente algum contato no Pop-UP
                    }else {//S� lista
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
//Aqui eu coloco esse objeto para n�o dar erro de programa��o no PHP
        ?>
                <input type='hidden' name='cmb_empresa' value='<?=$id_empresa_nf;?>'>
        <?
            }
        ?>
            &nbsp;-&nbsp;
            <font color='darkblue'>
                <b>
                <?
                    if($tipo_faturamento == 1) {//Significa que o Cliente fatura tudo pela Albaf�r ...
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
    /*Quando a Nota Fiscal for de Sa�da "diferente de 6" existem op��es de "Entrada e Sa�da", quando 
    for de Devolu��o $status = 6, s� existe a op��o de Entrada o que faz desnecess�rio exibir a 
    linha abaixo ...*/
    if($status != 6) {//Se a nota Fiscal for de Sa�da ...
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Nota:</b>
        </td>
        <td>
            <?
                if($tipo_nfe_nfs == 'S') {//NF de Sa�da ...
                    $checkeds = 'checked';
                }else {//NF de Entrada ...
                    $checkede = 'checked';
                }
            ?>
            <input type='radio' name='opt_nota' value='E' id='E' <?=$checkede;?> <?=$disabled;?>><label for='E'>Entrada</label>
            <input type='radio' name='opt_nota' value='S' id='S' <?=$checkeds;?> <?=$disabled;?>><label for='S'>Sa�da</label>
        </td>
    </tr>
<?
    }
    
    //Mesmo que a NF esteja aberta, s� pode trocar a Natureza de Opera��o quando a mesma n�o conter Itens ...
    if($qtde_itens_nf == 0) {
        $disabled_natureza_operacao = $disabled;
        $class_natureza_operacao    = $class_combo;
    }else {//Como existe(m) item(ns) na NF, ent�o eu n�o posso trocar p/ outra op��o da "Natureza de Opera��o" ...
        $disabled_natureza_operacao = 'disabled';
        $class_natureza_operacao    = 'textdisabled';
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Natureza de Opera��o:</b>
        </td>
        <td>
            <select name='cmb_natureza_operacao' title='Selecione a Natureza de Opera��o' onchange='controlar_natureza_operacao()' class='<?=$class_natureza_operacao;?>' <?=$disabled_natureza_operacao;?>>
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
                <option value='DEV' <?=$selected_dev;?>>DEV - DEVOLU��O DE VENDA</option>
                <option value='PSE' <?=$selected_pse;?>>PSE - PRESTA��O DE SERVI�OS</option>
                <option value='BON' <?=$selected_bon;?>>BON - REMESSA EM BONIFICA��O</option>
                <option value='VOF' <?=$selected_vof;?>>VOF - VENDA ORIGINADA DE ENCOMENDA PARA ENTREGA FUTURA</option>
                <option value='REC' <?=$selected_rec;?>>REC - ENTRADA DE MERCADORIA DEVIDO A RECUSA DO CLIENTE</option>
                <option value='RAG' <?=$selected_rag;?>>RAG - REMESSA DE AMOSTRA GR�TIS</option>
            </select>
        </td>
    </tr>
<?
//2)
    //S� para NF�s de Devolu��o e Empresas como Albafer ou Tool Master que aparecer�o essas op��es ...
    if($status == 6 && $id_empresa_nf != 4) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Tipo da NF de Devolu��o: </b>
            </font>
        </td>
        <td>
            <input type='radio' name='opt_numero_nf' value='1' onclick='controlar_numero()' id='label1' <?=$disabled;?>>
            <label for='label1'>
                <font title='Nosso N.� de Nota Fiscal' style='cursor:help'>
                    NNF
                </font>
            </label>
            &nbsp;
            <input type='radio' name='opt_numero_nf' value='2' onclick='controlar_numero()' id='label2' <?=$disabled;?>>
            <label for='label2'>
                <font title='N.� de Nota Fiscal do Cliente' style='cursor:help'>
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
            //Nesse vetor eu vou armazenar todas as NF(s) que est�o atrelados a esta NF de Devolu��o ...
            $vetor_nfs = array();
            
            //Busco os Itens dessa Nota Fiscal que foi Devolvida ...
            $sql = "SELECT `id_nf_item_devolvida` 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '$id_nf' ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
            for($i = 0; $i < $linhas_itens; $i++) {
                //Busco o id_nf da Nota Fiscal de Sa�da ...
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
            //Aqui eu fa�o Tratamento com a Parte das NF(s) ...
            if(count($vetor_nfs) > 0) {//Se existir pelo menos 1 NF atrelada, ent�o ...
                for($i = 0; $i < count($vetor_nfs); $i++) echo '<b>N.� '.faturamentos::buscar_numero_nf($vetor_nfs[$i], 'S').' - Chave de Acsso: '.$vetor_codigo_barras[$campos_nfs[0]['id_nf']].'</b><br/>';
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
                <b>N.� da NF de Sa�da: </b>
            </font>
        </td>
        <td>
        <?
/************************************************************************************************/
                /*Essa fun��o tem por objetivo trazer apenas os N.�s de Talon�rio de Nota Fiscal 
                dispon�veis p/ trabalho ...*/
                faturamentos::verificar_numero_disponivel($id_empresa_nf);

//Se a Empresa for diferente de Grupo ent�o segue ...
                if($id_empresa_nf != 4) {
                    if($natureza_operacao == 'PSE') {//Nesse Tipo de Nota Fiscal utilizamos o N.� do Governo ...
                        //faturamentos::gerar_numero_nf($id_empresa_nf, 0, 'S');
                        //Busca de todos os N.�s de Presta��o de Servi�o que estejam em aberto ...
                        $sql = "SELECT `id_nf_num_nota`, `numero_nf` 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'S' ORDER BY numero_nf ";
                    }else {//Em outro Tipo de NF utilizamos nosso n�mero ...
                        //faturamentos::gerar_numero_nf($id_empresa_nf);
                        //Busca de todos os N.�s que estejam em aberto da Empresa selecionada pelo usu�rio ...
                        $sql = "SELECT `id_nf_num_nota`, `numero_nf` 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'N' ORDER BY numero_nf ";
                    }
                    bancos::sql($sql);
                ?>
                <select name='cmb_num_nota_fiscal' title='Selecione o N�mero da Nota Fiscal' class='<?=$class_combo;?>' <?=$disabled;?>>
                    <?=combos::combo($sql, $id_nf_num_nota);?>
                </select>
                <?
/*Se eu tiver algum N.� de Nota selecionado na combo, ent�o eu apresento o N.� Anterior e N.� Posterior 
da que est� selecionada na combo*/
                    if(!empty($numero_nf_anterior) || !empty($id_nf_num_nota)) {
        ?>
                &nbsp;
                <font title='N.� Anterior de Nota Fiscal' style='cursor:help'>
                        <b>N.� Ant: </b>
                </font>
                <?=$numero_nf_anterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_anterior, '/');?>
        <?
//Se tiver N.� Posterior, ent�o eu exibo este tamb�m ...
                        if(!empty($numero_nf_posterior)) {
        ?>
                &nbsp;|&nbsp;

                <font title='N.� Posterior de Nota Fiscal' style='cursor:help'>
                    <b>N.� Post: </b>
                </font>
                <?=$numero_nf_posterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_posterior, '/');?>
        <?
                        }
                    }
                }else {//Quando for SGD - coloco esse objeto para n�o dar erro de programa��o ...
                    echo faturamentos::buscar_numero_nf($id_nf, 'S');//Apenas apresenta��o do N.� do Talon�rio ...
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
    //S� para NF�s de Devolu��o e Empresas como Albafer ou Tool Master que aparecer� essa op��o ...
    if($status == 6 && $id_empresa_nf != 4) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.� da NF de Devolu��o do Cliente: </b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_snf_devolvida' value='<?=$snf_devolvida;?>' title='Digite a SNF Devolvida' size='16' maxlength='15' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;-&nbsp;
            <font color='darkblue'>
                <b>Data de Emiss�o da NF do Cliente: </b>
            </font>
            <input type='text' name='txt_data_emissao_snf' value='<?=$data_emissao_snf;?>' title='Digite a Data de Sa�da Entrada' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_emissao_snf.disabled == false) {javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_snf&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<?
    }

//Mesmo que a NF esteja aberta, s� pode trocar a Finalidade quando a mesma n�o conter Itens ...
    if($qtde_itens_nf == 0) {
        $disabled_finalidade    = $disabled;
        $class_finalidade       = $class_combo;
    }else {//Como existe(m) item(ns) na NF, ent�o eu n�o posso trocar p/ outra op��o da "Finalidade" ...
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
//Significa que o usu�rio manipulou algum contato no Pop-UP
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
                <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZA��O</option>
                <option value='R' <?=$selected_revenda;?>>REVENDA</option>
            </select>
            <?
                //Significa que a Nota Fiscal � Livre de D�bito ...
                $checked_livre_debito = ($livre_debito == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_livre_debito' value='S' id='livre_debito' onclick='controlar_checkbox()' class='checkbox' <?=$checked_livre_debito;?>>
            <label for='livre_debito'>
                <font color='darkblue'>
                    <b>Livre de D�bito Propag / Mkt</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor D�lar da Nota:
        </td>
        <td>
            <?=$valor_dolar_dia;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Grava��o ...
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
    if($status == 6) {//Somente quando a Nota Fiscal for de Devolu��o que mostro os dizeres abaixo ... 
?>
<pre>
<b><font color='red'>Lembre-se de verificar o Texto da Nota.</font></b>
<font color='darkblue'><b>
Cliente => "Optante pelo Simples Nacional":

    Marcada a op��o de NNF:

        * For�a o Nosso N.� de NF de Devolu��o (SEFAZ);
        * For�a o N.� da NF de Devolu��o do Cliente.

    Marcada a op��o de SNF:

        * For�a o N.� da NF de Devolu��o do Cliente apenas.

Cliente => N�O "Optante pelo Simples Nacional":

    Marcada a op��o de NNF:

        * For�a o Nosso N.� de NF de Devolu��o apenas (SEFAZ);

<font color='brown'>
*** Quando n�o valer a NF do Cliente <b>"SNF"</b>, ent�o tem que aparecer os dizeres 
da Natureza de Opera��o no Campo Texto da NF.
</font>
</b></font>
</pre>
<?
    }
?>