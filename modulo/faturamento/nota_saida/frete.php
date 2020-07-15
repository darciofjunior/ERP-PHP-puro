<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/faturamentos.php');

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

function verificar_vide_notas($id_nf, $id_cliente, $id_empresa_nf, $numero_nf_ac = '') {
//Aqui vai acumulando todos os N�ms. de Nota
    $numero_nf_ac.= faturamentos::buscar_numero_nf($id_nf, 'S').' <- ';

    $sql = "SELECT id_nf 
            FROM `nfs` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_nf_vide_nota` = '$id_nf' ORDER BY id_nf ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($j = 0; $j < $linhas; $j++) $numero_nf_ac = verificar_vide_notas($campos[$j]['id_nf'], $id_cliente, $id_empresa_nf, $numero_nf_ac);
    return $numero_nf_ac;
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf  = $_POST['id_nf'];
    $acao   = $_POST['acao'];
}else {
    $id_nf  = $_GET['id_nf'];
    $acao   = $_GET['acao'];
}

if(!empty($_POST['hdd_atualizar_dados_frete'])) {
    if(!empty($_POST['cmb_packing_list'])) {//Aqui eu finalizo a Packing List ...
        $sql = "UPDATE `packings_lists` SET `status` = '1' WHERE `id_packing_list` = '$_POST[cmb_packing_list]' LIMIT 1 ";
        bancos::sql($sql);
    }else {//aqui eu reabro o packing list ...
        //Aqui eu busco qual era o Packing List que estava atrelado a NF, p/ poder reabr�-lo ...
        $sql = "SELECT id_packing_list 
                FROM `nfs` 
                WHERE `id_nf` = '$_POST[id_nf]' 
                AND `id_packing_list` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $sql = "UPDATE `packings_lists` SET `status` = '0' WHERE `id_packing_list` = '".$campos[0]['id_packing_list']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
    $id_packing_list = (!empty($_POST[cmb_packing_list])) ? "'".$_POST[cmb_packing_list]."'" : 'NULL';
    
    $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_transportadora` = '$_POST[cmb_cliente_transportadora]', `id_packing_list` = $id_packing_list, `frete_transporte` = '$_POST[cmb_frete_transporte]', `despesas_acessorias` = '$_POST[txt_despesas_acessorias]', `valor_frete` = '$_POST[txt_valor_frete]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
    
    //Se houver mudan�a no campo Frete ou no campo Despesas Acess�rias, � mais do que necess�rio se recalcular o valor das Duplicatas ...
    if($_POST['txt_valor_frete'] != $_POST['hdd_valor_frete'] || $_POST['txt_despesas_acessorias'] != $_POST['hdd_despesas_acessorias']) {
        //Depois que foi atualizado o valor do Frete, Aqui eu busco alguns dados do id_nf para poder executar a fun��o mais abaixo ...
        $sql = "SELECT nfs.`id_empresa`, nfs.`suframa`, c.`id_pais` 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $id_empresa_nf  = $campos[0]['id_empresa'];
        $suframa_nf     = $campos[0]['suframa'];

        //Aqui verifica o Tipo de Nota
        $nota_sgd       = ($id_empresa_nf == 1 || $id_empresa_nf == 2) ? 'N' : 'S';
        $id_pais        = $campos[0]['id_pais'];

        faturamentos::valor_duplicata($_POST[id_nf], $suframa_nf, $nota_sgd, $id_pais, 'S');
    }
?>
    <Script Language = 'JavaScript'>
        alert('DADO(S) DE FRETE ATUALIZADO(S) COM SUCESSO !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
}

//Exclus�o de Transportadoras
if(!empty($_POST['id_transportadora_excluir'])) {
//Se a Transportadora for N/Carro ou Retira, ent�o n�o pode ser excluido do Cliente
    if($_POST['id_transportadora_excluir'] != 795 && $_POST['id_transportadora_excluir'] != 796) {
        $sql = "DELETE FROM `clientes_vs_transportadoras` WHERE `id_cliente`  = '$_POST[id_cliente]' AND `id_transportadora` = '$_POST[id_transportadora_excluir]' LIMIT 1 ";
        bancos::sql($sql);
    }
}

//Aqui eu trago dados da "id_nf" passado por par�metro ...
$sql = "SELECT c.`id_pais`, c.`razaosocial`, c.`cidade`, e.`nomefantasia`, nfs.`id_cliente`, 
        nfs.`id_empresa`, nfs.`id_transportadora`, nfs.`id_nf_vide_nota`, 
        nfs.`id_packing_list`, nfs.`frete_transporte`, nfs.`despesas_acessorias`, nfs.`valor_frete`, 
        nfs.`peso_bruto_balanca`, nfs.`importado_financeiro` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `empresas` e ON e.`id_empresa` = nfs.`id_empresa` 
        WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_pais                = $campos[0]['id_pais'];
$razaosocial            = $campos[0]['razaosocial'];
$cidade                 = $campos[0]['cidade'];
$empresa                = $campos[0]['nomefantasia'];
$id_cliente             = $campos[0]['id_cliente'];
$id_empresa_nf          = $campos[0]['id_empresa'];
$id_transportadora      = $campos[0]['id_transportadora'];
$id_nf_vide_nota        = $campos[0]['id_nf_vide_nota'];
$id_packing_list        = $campos[0]['id_packing_list'];
$despesas_acessorias    = number_format($campos[0]['despesas_acessorias'], 2, ',', '.');
$frete_transporte       = $campos[0]['frete_transporte'];
$valor_frete            = number_format($campos[0]['valor_frete'], 2, ',', '.');
$peso_bruto_balanca     = number_format($campos[0]['peso_bruto_balanca'], 2, ',', '.');
$importado_financeiro   = $campos[0]['importado_financeiro'];

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

$calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
?>
<html>
<head>
<title>.:: TRANSPORTADOR / VOLUMES TRANSPORTADOS ::.</title>
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
//Transportadora ...
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }
//Frete Transporte ...
    if(!combo('form', 'cmb_frete_transporte', '', 'SELECIONE O FRETE TRANSPORTE !')) {
        return false
    }
//Isso s� acontecer� quando existir pelo menos 1 item na Nota Fiscal ...
    if(typeof(document.form.hdd_frete_transporte_orcamento) == 'object') {
        var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>')
        
        //Esses s�o os �nicos funcion�rios que podem colocar qualquer Tipo de Frete -> Roberto 62 e D�rcio 98 porque programa ...
        var vetor_funcionarios_podem_colocar_qualquer_frete = [62, 98]
        
        if(vetor_funcionarios_podem_colocar_qualquer_frete.indexOf(id_funcionario) != 1) {//Representa que o id_funcionario � diferente do que est� no Vetor ...
            //Nunca o Frete Transporte da Nota Fiscal poder� ser diferente do Frete Transporte do Or�amento ...
            if(document.form.cmb_frete_transporte.value != document.form.hdd_frete_transporte_orcamento.value) {
                alert('"FRETE POR CONTA" DA NOTA FISCAL EST� DIFERENTE DO "FRETE POR CONTA" DO OR�AMENTO !')
                document.form.cmb_frete_transporte.focus()
                return false
            }
        }
    }
/****************************************Valor do Frete****************************************/
//1) Se o Frete = 'REMETENTE' e o campo Valor do Frete > 0 ...
    var valor_frete = (document.form.txt_valor_frete.value != '') ? eval(strtofloat(document.form.txt_valor_frete.value)) : 0

    if(document.form.cmb_frete_transporte.value == 'C' && valor_frete > 0) {
        alert('QUANDO O FRETE � POR CONTA DO REMETENTE, O VALOR DO FRETE TEM DE SER ZERADO !!!')
        document.form.txt_valor_frete.focus()
        document.form.txt_valor_frete.select()
        return false
    }
//2) ...
    var transportadora_para_confiscar = 0
/*Se a Transportadora = 797 - Sedex, 1050 - Correio Encomenda P.A.C., 1092 - Sedex 10, 1093 - Motoboy ou 1265 - Tam Linhas A�reas
e Valor do Frete = 0, ent�o for�o a calcular ...*/
    var vetor_transportadoras       = ['797', '1050', '1092', '1093', '1265']
    if(vetor_transportadoras.indexOf(document.form.cmb_cliente_transportadora.value) != -1) transportadora_para_confiscar = 1

/*Se o Valor de Frete = Zero, for uma das 4 Transportadoras acima e o Frete Transporte = 'DESTINAT�RIO', 
for�o esse campo p/ preenchimento de Valor do Frete ...*/
    if(document.form.txt_valor_frete.value == '0,00' && transportadora_para_confiscar == 1 && document.form.cmb_frete_transporte.value == 'F') {
        alert('VALOR DO FRETE INV�LIDO !!!\nCALCULE O VALOR DO FRETE PARA ESSA TRANSPORTADORA !')
        document.form.cmd_calcular_frete.focus()
        return false
    }
/**********************************************************************************************/
//Desabilito esse campo p/ poder gravar na Base de Dados ...
    document.form.txt_valor_frete.disabled = false
    document.form.hdd_atualizar_dados_frete.value = 'S'
    limpeza_moeda('form', 'txt_valor_frete, txt_despesas_acessorias, ')
}

//Exclus�o das Transportadoras
function excluir_transportadora() {
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.id_transportadora_excluir.value = document.form.cmb_cliente_transportadora.value
            document.form.submit()
        }
    }
}

function imprimir_endereco_cliente() {
    var cliente     = '<?=addSlashes($razaosocial);?>'
    var cidade      = '<?=addSlashes($cidade);?>'
    var nota_fiscal = '<?=faturamentos::buscar_numero_nf($id_nf, 'S');?>'
    var empresa     = '<?=$empresa;?>'
    var resposta1   = confirm('TEM CERTEZA DE QUE DESEJA ENVIAR SEDEX PARA ESSE CLIENTE "'+cliente+'", DA CIDADE "'+cidade+'" E NOTA FISCAL N.� "'+nota_fiscal+'" DA EMPRESA "'+empresa+'" ?')

    if(resposta1 == true) {
        var resposta2 = confirm('DESEJA IMPRIMIR O ENDERE�O DE COBRAN�A ?')
        if(resposta2 == true) {//Se quiser imprimir o Endere�o de Cobran�a ...
            var imp_endereco_cobranca = 1
        }else {//Se n�o quiser imprimir o Endere�o de Cobran�a ...
            var imp_endereco_cobranca = 0
        }
//Abrindo o Pop-Up do Sedex ...
/*Par�metros

- Endere�o de Cobran�a - se o usu�rio n�o deseje imprimir o Endere�o de Cobran�a, ser� impresso o Normal ...
- O remetente no caso vem ser a pr�pria Empresa da NF em quest�o ...
- O $id_cliente_contato - � o Contato respons�vel da NF, com este eu j� pego o Cliente, Departamento*/
        nova_janela('../../producao/relatorio/controle_sedex/imprimir.php?imp_endereco_cobranca='+imp_endereco_cobranca+'&remetente_emp=<?=$id_empresa_nf;?>&id_nf=<?=$id_nf;?>', 'CONSULTAR', '', '', '', '', '450', '800', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function comprovante_entrega_material() {
    var resposta = confirm('DESEJA DIGITAR UMA OBSERVA��O P/ ENTREGA ?')
    if(resposta == true) {//Se quiser Digitar uma Observa��o p/ Entrega ...
        var observacao_entrega = prompt('OBSERVA��O P/ ENTREGA: ')
        document.form.hdd_observacao_entrega.value = observacao_entrega
//Controle com a Justificativa ...
        if(document.form.hdd_observacao_entrega.value == '' || document.form.hdd_observacao_entrega.value == 'null' || document.form.hdd_observacao_entrega.value == 'undefined') {
            alert('OBSERVA��O P/ ENTREGA INV�LIDA !!!\nDIGITE UMA OBSERVA��O P/ ENTREGA !')
            return false
        }
    }else {//Se n�o quiser nenhuma Observa��o p/ Entrega ...
        observacao_entrega = ''
    }
    nova_janela('comprovante_entrega_material.php?id_nf=<?=$id_nf;?>&observacao_entrega='+observacao_entrega, 'COMPROVANTE_ENTREGA_MATERIAL', '', '', '', '', '450', '800', 'c', 'c', '', '', 's', 's', '', '', '')
}

function calcular_frete() {
    if(document.form.cmb_modo_envio.value == 'CORREIO') {
        nova_janela('../../classes/cliente/calcular_frete_correio.php?id_nf=<?=$id_nf;?>', 'CALCULAR_FRETE', '', '', '', '', '150', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        nova_janela('../../classes/cliente/calcular_frete_tam.php?id_nf=<?=$id_nf;?>&valor_total_produtos=<?=$calculo_total_impostos['valor_total_produtos'];?>', 'CALCULAR_FRETE', '', '', '', '', '250', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function packing_list() {
    if(document.form.cmb_packing_list.value == '') {
        alert('SELECIONE O PACKING LIST !')
        document.form.cmb_packing_list.focus()
    }else {
        nova_janela('../../producao/programacao/estoque/gerenciar/packing_list/relatorio/relatorio.php?id_packing_list='+document.form.cmb_packing_list.value, 'CONSULTAR', 'F')
    }
}
</Script>
<body onload='document.form.cmb_cliente_transportadora.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='id_transportadora_atrelar'>
<input type='hidden' name='id_transportadora_excluir'>
<input type='hidden' name='hdd_atualizar_dados_frete'>
<input type='hidden' name='hdd_observacao_entrega'>
<!--*************************************-->
<table width='<?=$width;?>' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            TRANSPORTADOR / VOLUMES TRANSPORTADOS
            <?
                /*Significa que essa Tela foi aberta somente p/ Modo Leitura, 
                n�o est� importada no Financeiro e que a mesma foi acessada do 
                Menu Em Aberto / Liberadas ou Devolu��o ou sempre o l�pis ir� aparecer 
                quando for os usu�rios abaixo 
                
                Rivaldo 27, Agueda 32, Roberto 62 ou D�rcio 98 porque programa ...*/
                if($importado_financeiro == 'N') {
                    if($acao == 'L' && ($opcao == 1 || $opcao == 4) || ($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 32 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98)) {
            ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('frete.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'FRETE', '', '', '', '', '300', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Dados de Frete' alt='Alterar Dados de Frete'>
            <?
                    }
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Raz�o Social / Transportadora:</b>
        </td>
        <td>
            <select name='cmb_cliente_transportadora' title='Selecione a Transportadora do Cliente' class='<?=$class_combo;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT t.`id_transportadora`, t.`nome` 
                        FROM `clientes_vs_transportadoras` ct 
                        INNER JOIN `transportadoras` t ON t.`id_transportadora` = ct.`id_transportadora` AND t.`ativo` = '1' 
                        WHERE ct.`id_cliente` = '$id_cliente' ORDER BY t.`nome` ";
//Significa que o usu�rio atrelou uma transportadora no Pop-UP de Transportadoras
                if(!empty($_POST['id_transportadora_atrelar'])) {
                    echo combos::combo($sql, $_POST['id_transportadora_atrelar']);
                }else {//Aqui carrega a transportadora j� escolhida em Nota Fiscal
//Significa que o usu�rio manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($_POST['cmb_cliente_transportadora'])) {
                        echo combos::combo($sql, $_POST['cmb_cliente_transportadora']);
//At� ent�o n�o foi feito nenhuma manipula��o referente a transportadora ou algum contato no Pop-UP
                    }else {//Aqui carrega a transportadora j� escolhida em Nota Fiscal
                        echo combos::combo($sql, $id_transportadora);
                    }
                }
            ?>
            </select>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Grava��o ...
            ?>
            &nbsp;&nbsp;
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Atrelar Transportadora' alt='Atrelar Transportadora' onclick="nova_janela('../../classes/cliente/atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp;
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Transportadora' alt='Excluir Transportadora' onclick='excluir_transportadora()'>
            <?
                }
/*S� exibir� esse link p/ essas Transportadoras abaixo: 

797 - "Sedex", 849 - "Correio Encomenda Simples", 1050 - "Correio Encomenda P.A.C.", 
1092 - "Correio Sedex 10" ...*/
                if($id_transportadora == 797 || $id_transportadora == 849 || $id_transportadora == 1050 || $id_transportadora == 1092) {
            ?>
                    &nbsp;
                    <a href='javascript:imprimir_endereco_cliente()' title='Controle de Sedex' style='cursor:help' class='link'>
                        IMPRIMIR ENDER. CLIENTE
                    </a>
            <?
                }

                //Se a Nota Fiscal for do Tipo SGD ent�o exibo esse link de Comprovante de Entrega de Material ...
                if($id_empresa_nf == 4) {
                    echo ' - ';
            ?>
                    <a href='javascript:comprovante_entrega_material()' title='Comprovante de Entrega de Material' style='cursor:help' class='link'>
                        <font color='red'>
                            COMPROVANTE DE ENTREGA DE MATERIAL
                        </font>
                    </a>
            <?
                }
            ?>
        </td>
    </tr>
    <?
        /******************************************************************************/
        /*Busco Dados de Frete que est�o no Or�amento de Venda do maior Pedido de Vendas que gerou esta 
        Nota Fiscal ...*/
        $sql = "SELECT ov.`tipo_frete`, ov.`valor_frete_estimado` 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                WHERE nfs.`id_nf` = '$id_nf' 
                GROUP BY pvi.`id_pedido_venda` ORDER BY SUM(pvi.`qtde` * pvi.`preco_liq_final`) DESC LIMIT 1 ";
        $campos_orcamento = bancos::sql($sql);
        if(count($campos_orcamento) == 1) {//Se o sistema j� encontrou 1 item Incluso na NF ...
?>
    <tr class='linhanormal'>
        <td>
            Frete por Conta (Or�amento):
        </td>
        <td>
        <?
            if($campos_orcamento[0]['tipo_frete'] == 'F') {
                echo 'FOB (POR CONTA DO CLIENTE - DESTINAT�RIO)';
                $frete_transporte_orcamento = 'F';
            }else if($campos_orcamento[0]['tipo_frete'] == 'C') {
                echo 'CIF (POR NOSSA CONTA - REMETENTE)';
                $frete_transporte_orcamento = 'C';
            }
        ?>
            <!--************Esse hidden ser� utilizado p/ valida��o em JavaScript************-->
            <input type='hidden' name='hdd_frete_transporte_orcamento' value='<?=$frete_transporte_orcamento?>'>
        </td>
    </tr>
<?
        }
        /******************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <b>Frete por Conta:</b>
        </td>
        <td>
            <select name='cmb_frete_transporte' title='Selecione o Frete Transporte' onchange="if(this.value == 'C') {document.getElementById('lbl_frete_transporte').style.visibility = 'visible'}else {document.getElementById('lbl_frete_transporte').style.visibility = 'hidden'}" class='<?=$class_combo;?>' <?=$disabled;?>>
                <option value='' style='color:red'>SELECIONE</option>
            <?
//Significa que o usu�rio manipulou uma transportadora ou algum contato no Pop-UP
                if(!empty($_POST['cmb_frete_transporte'])) {
                    if($_POST['cmb_frete_transporte'] == 'C') {
                        $selectedr = 'selected';
                    }else {
                        $selectedd = 'selected';
                    }
//At� ent�o n�o foi feito nenhuma manipula��o referente a transportadora ou algum contato no Pop-UP
                }else {
                    if($frete_transporte == 'C') {
                        $selectedr = 'selected';
                    }else {
                        $selectedd = 'selected';
                    }
                }
            ?>
                <option value='C' <?=$selectedr;?>>CIF (POR NOSSA CONTA - REMETENTE)</option>
                <option value='F' <?=$selectedd;?>>FOB (POR CONTA DO CLIENTE - DESTINAT�RIO)</option>
            </select>
            <label id='lbl_frete_transporte' style='visibility: hidden'>
                <font color='red'>
                    &nbsp;<b>N�O FAZ PARTE DOS C�LCULOS DA NF</b>
                </font>
            </label>
        <?
//Significa que o Cliente � do Tipo Internacional
            if($id_pais != 31) echo 'Exporta��o';
        ?>
        </td>
    </tr>
    <?
        //Aqui verifico se a Nota Fiscal tem pelo menos 1 item cadastrado ...
        $sql = "SELECT `id_nfs_item` 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        $campos_qtde_itens  = bancos::sql($sql);
        $qtde_itens_nf      = count($campos_qtde_itens);
    
        if($qtde_itens_nf > 0) {
            $calculo_peso_nf    = faturamentos::calculo_peso_nf($id_nf);
            //1)
            $qtde_volume        = $calculo_peso_nf['qtde_caixas'];
            //2)
            $peso_liquido_vol   =  $calculo_peso_nf['peso_liq_total_nf'];
        }
    ?>
    <tr class='linhanormal'>
        <td>
            Qtde de Volume:
        </td>
        <td>
            <?=$qtde_volume;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Esp�cie:
        </td>
        <td>
            <?=$calculo_peso_nf['especie'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Bruto de Volume (Balan�a):
        </td>
        <td>
            <?=$peso_bruto_balanca;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso L�quido de Volume:
        </td>
        <td>
            <?=number_format($peso_liquido_vol, 4, ',', '.');?>
        </td>
    </tr>
<?
        if(count($campos_orcamento) == 1) {//Se o sistema j� encontrou 1 item Incluso na NF ...
?>
    <tr class='linhanormal'>
        <td>
            Valor de Frete Estimado:
        </td>
        <td>
            <?=number_format($campos_orcamento[0]['valor_frete_estimado'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
//Significa que o usu�rio ainda n�o manipulou alguma transportadora ...
        if(!empty($_POST['txt_valor_frete'])) $valor_frete = number_format($_POST['txt_valor_frete'], 2, ',', '.');
    ?>
    <tr class='linhanormal'>
        <td>
            Valor do Frete:
        </td>
        <td>
            <input type='text' name='txt_valor_frete' value='<?=$valor_frete;?>' title='Valor do Frete' size='11' maxlength='9' class='textdisabled' disabled>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Grava��o ...
            ?>
                &nbsp;
                -
                &nbsp;
                <select name='cmb_modo_envio' title='Modo de Envio' class='combo'>
                    <option value='CORREIO'>CORREIO</option>
                    <option value='TAM'>TAM</option>
                </select>
                &nbsp;
                <input type='button' name='cmd_calcular_frete' value='Calcular Frete' title='Calcular Frete' onclick='calcular_frete()' class='botao'>
                &nbsp;
                <a href="javascript:nova_janela('http://www2.correios.com.br/sistemas/precosPrazos/', 'CORREIOS', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Consultar Sedex (Correios)' class='link'>
                    Consultar Sedex (Correios)
                </a>
                <!--Controle que ir� me auxiliar p/ saber se houve altera��o no campo Frete ...-->
                <input type='hidden' name='hdd_valor_frete' value='<?=$campos[0]['valor_frete'];?>'>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Despesas Acess�rias:
        </td>
        <td>
            <input type='text' name='txt_despesas_acessorias' value='<?=$despesas_acessorias;?>' title='Digite as Despesas Acessorias' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' maxlength='12' class='<?=$class;?>' <?=$disabled;?>>
            <!--Controle que ir� me auxiliar p/ saber se houve altera��o no campo Despesas Acess�rias ...-->
            <input type='hidden' name='hdd_despesas_acessorias' value='<?=$campos[0]['despesas_acessorias'];?>'>
        </td>
    </tr>
<?
//Significa que est� nota possui Vide Notas atreladas
        if($id_nf_vide_nota == 0) {
            $rotulo = 'NFs Atreladas:';
            $vide_notas = verificar_vide_notas($id_nf, $id_cliente, $id_empresa_nf);
            $vide_notas = substr($vide_notas, 0, strlen($vide_notas) - 4);
//Significa que est� NF j� � a principal
        }else {
            $rotulo = 'Vide Nota:';
            $vide_notas = faturamentos::buscar_numero_nf($id_nf_vide_nota, 'S');
        }
?>
    <tr class='linhanormal'>
        <td>
            <?=$rotulo;?>
        </td>
        <td>
            <?=$vide_notas;?>
        </td>
    </tr>
    <?
        //S� listo os packing_list em aberto do Cliente com pelo menos 1 item ou o pr�prio Packing List que j� foi atrelado a esta NF ...
        $sql = "SELECT DISTINCT(pl.`id_packing_list`), pl.`id_packing_list` 
                FROM `packings_lists` pl 
                INNER JOIN `packings_lists_itens` pli ON pli.`id_packing_list` = pl.`id_packing_list` 
                WHERE pl.`id_cliente` = '$id_cliente' 
                AND (pl.`status` = '0' OR pl.`id_packing_list` = '$id_packing_list') ";
        $campos_packing_list = bancos::sql($sql);
        $linhas_packing_list = count($campos_packing_list);
        if($linhas_packing_list > 0) {
    ?>
    <tr class='linhanormal'>
        <td>
            Packing List:
        </td>
        <td>
            <select name='cmb_packing_list' title='Selecione o Packing List' class='<?=$class_combo;?>' <?=$disabled;?>>
                <?=combos::combo($sql, $id_packing_list);?>
            </select>
            &nbsp;
            <img src = '../../../imagem/lista.jpg' width='20' height='20' border='0' title='Packing List' style='cursor:help' onclick='packing_list()'>
        </td>
    </tr>
    <?
        }
    ?>
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