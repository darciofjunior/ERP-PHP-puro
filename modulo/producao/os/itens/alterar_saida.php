<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>ITEM(NS) DE SA�DA ATUALIZADO(S) COM SUCESSO.</font>";

$id_os = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_os'] : $_GET['id_os'];

if(!empty($_POST['txt_qtde_saida'])) {
    $cobrar_lote_minimo = (!empty($_POST['chkt_cobrar_lote_minimo'])) ? 'S' : 'N';
    $sql = "UPDATE `oss_itens` set `qtde_saida` = '$_POST[txt_qtde_saida]', `peso_total_saida` = '$_POST[txt_peso_total_saida]', `preco_pi` = '$_POST[txt_preco_unitario]', `peso_unit_saida` = '$_POST[txt_peso_unit_saida]', `obs_retrabalho` = '$_POST[txt_observacao_retrabalho]', `cobrar_lote_minimo` = '$cobrar_lote_minimo', `retrabalho` = '$chkt_retrabalho' WHERE `id_os_item` = '$_POST[id_os_item]' LIMIT 1 ";
    bancos::sql($sql);
/*******************************************************************************************************/
//Verifico se a OSS j� foi importada p/ Pedido ...
    $sql = "SELECT id_pedido 
            FROM `oss` 
            WHERE `id_os` = '$id_os' LIMIT 1 ";
    $campos_os  = bancos::sql($sql);
    $id_pedido  = $campos_os[0]['id_pedido'];
    //Se a OSS ainda n�o foi importada, ent�o o sistema tenta atualizar o Peso do A�o na 5� Etapa do Custo se existir ...
    if($id_pedido == 0) {
        if($id_pac_pi_trat > 0) {//Existe 5� Etapa e sendo assim atualizam-se os dados desse Custo nessa Etapa ...
            $chkt_peso_aco_manual = 1;
            $sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '$txt_peso_unit_saida', `peso_aco_manual` = '$chkt_peso_aco_manual' WHERE `id_pac_pi_trat` = '$id_pac_pi_trat' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}

//Seleciona a qtde de itens que existe na OS
$sql = "SELECT COUNT(id_os_item) AS qtde_itens 
        FROM `oss_itens` 
        WHERE `id_os` = '$id_os' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

if(empty($posicao)) $posicao = 1;

//Seleciona os itens da OS
$sql = "SELECT * 
        FROM `oss_itens` 
        WHERE `id_os` = '$id_os' ORDER BY id_os_item ";
$campos                     = bancos::sql($sql, ($posicao - 1), $posicao);
$id_os_item                 = $campos[0]['id_os_item'];
$id_op                      = $campos[0]['id_op'];
$id_produto_insumo_mat_prima	= $campos[0]['id_produto_insumo_mat_prima'];
$id_produto_insumo_ctt      = $campos[0]['id_produto_insumo_ctt'];
$qtde_saida                 = $campos[0]['qtde_saida'];
$qtde_entrada               = $campos[0]['qtde_entrada'];
$data_saida                 = data::datetodata($campos[0]['data_saida'], '/');
$dureza_fornecedor          = $campos[0]['dureza_fornecedor'];
$dureza_interna             = $campos[0]['dureza_interna'];
$peso_total_saida           = $campos[0]['peso_total_saida'];
$peso_total_entrada         = $campos[0]['peso_total_entrada'];
$obs_retrabalho             = $campos[0]['obs_retrabalho'];
$cobrar_lote_minimo         = $campos[0]['cobrar_lote_minimo'];
$lote_minimo_custo_tt       = $campos[0]['lote_minimo_custo_tt'];
$retrabalho                 = $campos[0]['retrabalho'];

//Busca do Produto da OP agora atrav�s do id_op que est� na OS - vou utilizar isso aki + abaixo
$sql = "Select pa.id_produto_acabado, pa.operacao_custo 
		from ops 
		inner join produtos_acabados pa on pa.id_produto_acabado = ops.id_produto_acabado 
		where ops.id_op = '$id_op' limit 1 ";
$campos2 = bancos::sql($sql);
//Aqui eu busco o id_produto_acabado_custo do produto_acabado corrente
$sql = "Select id_produto_acabado_custo 
		from produtos_acabados_custos 
		where id_produto_acabado = ".$campos2[0]['id_produto_acabado']." 
		and operacao_custo = ".$campos2[0]['operacao_custo']." limit 1 ";
$campos2 = bancos::sql($sql);
$id_produto_acabado_custo = $campos2[0]['id_produto_acabado_custo'];

//Busca de alguns dados da OS, vou precisar desses em algumas situa��es pouco mais pra baixo*/
$sql = "SELECT f.`razaosocial`, f.`nf_minimo_tt` AS nf_minimo_tt_cad, oss.* 
        FROM `oss` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
        WHERE oss.`id_os` = '$id_os' LIMIT 1 ";
$campos_os      = bancos::sql($sql);
$razaosocial    = $campos_os[0]['razaosocial'];
$id_fornecedor  = $campos_os[0]['id_fornecedor'];
$data_saida     = ($campos_os[0]['data_saida'] != '0000-00-00') ? data::datetodata($campos_os[0]['data_saida'], '/') : '';
$id_pedido      = $campos_os[0]['id_pedido'];

/********************************Controle para apresenta��o dos Dados********************************/
/*Busco o Status da OS para saber de quais locais de qual local que eu vou buscar 
o Lote M�nimo, Nf M�nima, Pre�o Unit�rio, Qtde_Unitaria de Sa�da que � o antigo "peso_unit_saida - BD"*/
if($id_pedido == 0) {//Essa OS ainda n�o foi importada, ent�o busco do Cadastro do Fornecedor
    $nf_minimo_tt = $campos2[0]['nf_minimo_tt_cad'];//Uso para comparar no JavaScript ...
    //Parte de Item da OS
    //Como a OS ainda n�o est� travada, eu busco o Pre�o do Produto - CTT direto da Lista de Pre�o do Fornecedor
    $sql = "SELECT preco 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo_ctt' 
            AND `id_fornecedor` = '$id_fornecedor' ";
    $campos_preco   = bancos::sql($sql);
    $preco_unitario = $campos_preco[0]['preco'];
    //Como a OS ainda n�o est� travada, o sistema tenta buscar o Peso desse Item l� na 5� Etapa se o mesmo for TRAT ...
    $sql = "SELECT id_pac_pi_trat, peso_aco 
            FROM `pacs_vs_pis_trat` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
            AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
    $campos_peso        = bancos::sql($sql);
    if(count($campos_peso) == 1) {//Encontrou dados na 5� Etapa ...
        $id_pac_pi_trat = $campos_peso[0]['id_pac_pi_trat'];
        $peso_aco       = $campos_peso[0]['peso_aco'];
    }else {//Como n�o encontrou dados na 5� Etapa, o sistema tenta buscar dados na 6� Etapa ...
        $sql = "SELECT qtde 
                FROM `pacs_vs_pis_usis` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
                AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
        $campos_peso    = bancos::sql($sql);
        $id_pac_pi_trat = 0;//Essa � uma vari�vel que n�o existe nessa 6� Etapa ...
        $peso_aco       = $campos_peso[0]['qtde'];//Como n�o existe Peso na 6� Etapa, ent�o trabalhamos com a Qtde ...
    }
}else {//Essa OS j� foi importada sendo assim, eu busco os valores das pr�prias tabelas de OS e Itens de OS
    //Dados correspondentes a tabela de OS ...
    $nf_minimo_tt   = $campos2[0]['nf_minimo_tt'];//Uso para comparar no JavaScript ...
    //Dados correspondentes a tabela de Item da OS ...
    $preco_unitario = $campos[0]['preco_pi'];
    $peso_aco       = $campos[0]['peso_unit_saida'];
}
/****************************************************************************************************/
/****************************Controle para Travamento dos Bot�es****************************/
//Se essa OS j� estiver importada p/ Pedido, eu travo todos os campos de Sa�da, e libero os campos de Entrada
if($id_pedido == 0) {//N�o est� importada ainda p/ Pedido, controle para os campos de Sa�da
    $acao               = 1;
    $class_saida        = 'caixadetexto';
    $disabled_saida     = '';
    $class_entrada      = 'textdisabled';
    $disabled_entrada   = 'disabled';
}else {//J� est� importada em Pedido, controle para os campos de Entrada
    $acao               = 2;
    $class_saida        = 'textdisabled';
    $disabled_saida     = 'disabled';
    $class_entrada      = 'caixadetexto';
    $disabled_entrada   = '';
}

//Busca da sigla do PI ...
$sql = "SELECT sigla 
        FROM `produtos_insumos` pi
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE pi.`id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
$campos_unidade = bancos::sql($sql);
$sigla          = $campos_unidade[0]['sigla'];

/****************************************************************************************************/
//Busca a Marca��o desse PI com o PA l� na 5� Etapa do Custo p/ saber se este tem a Marca��o de Lote M�nimo
$sql = "SELECT lote_minimo_fornecedor 
        FROM `pacs_vs_pis_trat` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
        AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
$campos_lote_minimo     = bancos::sql($sql);
$lote_minimo_fornecedor = $campos_lote_minimo[0]['lote_minimo_fornecedor'];

//Busca dos Produtos da OP agora atrav�s do id_op que est� na OS
$sql = "SELECT ops.qtde_produzir, pa.id_produto_acabado, pa.referencia 
        FROM `ops` 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ops.id_produto_acabado 
        WHERE ops.`id_op` = '$id_op' ";
$campos_pa = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Itens da OS N.�&nbsp;<?=$id_os;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formul�rio atrav�s do bot�o submit, sendo assim
faz requisi��o das condi��es de valida��o*/
    if(typeof(verificar) != 'undefined') {
//Quantidade de Sa�da
        if(!texto('form', 'txt_qtde_saida', '1', '1234567890', 'QUANTIDADE DE SA�DA', '1')) {
            return false
        }
//Verifica��o de dados Inv�lidos na Quantidade de Sa�da
        if(document.form.txt_qtde_saida.value == 0) {
            alert('QUANTIDADE DE SA�DA INV�LIDA ! \nVALOR IGUAL A ZERO !')
            document.form.txt_qtde_saida.focus()
            document.form.txt_qtde_saida.select()
            return false
        }
//CTT
        if(!combo('form', 'cmb_ctt', '', 'SELECIONE UM CTT !')) {
            return false
        }
//Peso Total de Sa�da
        if(!texto('form', 'txt_peso_total_saida', '1', '1234567890,.', 'PESO TOTAL DE SA�DA', '2')) {
            return false
        }
/*Se a Op��o de Retrabalho n�o estiver marcada, ent�o o Sistema for�a a ter um Pre�o <> de Zero,
agora caso esta op��o esteje marcada, ent�o eu ignoro o Pre�o Zero*/
        if(document.form.chkt_retrabalho.checked == false) {
//Verifico se o Pre�o Unit�rio do CTT � igual a Zero
            if(document.form.txt_preco_unitario.value == '0,00') {
                alert('CTT COM PRE�O UNIT�RIO INV�LIDO !!!\nATUALIZE ESTE(S) NA LISTA DE PRE�O DESSE FORNECEDOR !')
                return false
            }
        }
//Se a Op��o de Retrabalho estiver marcada, ent�o o Sistema for�a a preencher a Observa��o de Retrabalho
        if(document.form.chkt_retrabalho.checked == true) {
//For�o o Preenchimento de Observa��o de Retrabalhado
            if(document.form.txt_observacao_retrabalho.value == '') {
                alert('DIGITE A OBSERVA��O DE RETRABALHO !')
                document.form.txt_observacao_retrabalho.focus()
                return false
            }
        }
    }
//Compara��o entre os 2 pesos
    var peso_unitario_saida = eval(strtofloat(document.form.txt_peso_unit_saida.value))
    var peso_peca_corrigo = eval(strtofloat(document.form.txt_peso_peca_corrigido.value))
    if(((peso_unitario_saida / peso_peca_corrigo) > 1.01) || ((peso_peca_corrigo / peso_unitario_saida) > 1.01)) {
        var resposta = confirm('DIFEREN�A DE PESO UNIT�RIO SUPERIOR A 1% !\nDESEJA CONTINUAR ?')
        if(resposta == false) return false
    }
/*Qtde de Sa�da eu desabilito para poder comparar com a qtde_de_entrada no outro passo e verificar qual
vai ser o status correto de acordo com a f�rmula do Roberto*/
    document.form.txt_qtde_saida.disabled       = false
//Desabilito para poder gravar no BD
    document.form.txt_preco_unitario.disabled   = false
    document.form.txt_peso_unit_saida.disabled  = false
    document.form.txt_peso_total_saida.disabled = false
    limpeza_moeda('form', 'txt_peso_total_saida, txt_preco_unitario, txt_peso_unit_saida, ')
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
    document.form.posicao.value                 = posicao
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value           = 1
    atualizar_abaixo()
//Submetendo o Formul�rio
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function controlar_digitos(objeto) {
    if(objeto.value.length > 1) {//Se tiver pelo menos 2 d�gitos ...
        if(objeto.value.substr(0, 1) == '0') {
            objeto.value = objeto.value.substr(1, 1)
        }
    }
}

function peso_total_saida() {
//Qtde de Sa�da
    var qtde_saida  = (document.form.txt_qtde_saida.value == '') ? 0 : eval(strtofloat(document.form.txt_qtde_saida.value))
//Peso A�o
    var peso_aco    = (document.form.peso_aco.value == '') ? 0 : eval(document.form.peso_aco.value)
//Peso Total de Sa�da Kg -> Qtde de Sa�da * Peso A�o
    var resultado = String(qtde_saida * peso_aco)//Gambiarra (rsrs)
    document.form.txt_peso_total_saida.value = arred(resultado, 3, 1)
}

function calcular_preco_total() {
    var sigla                   = '<?=$sigla;?>'
    var qtde_saida              = strtofloat(document.form.txt_qtde_saida.value)
    var peso_total_saida_kg     = strtofloat(document.form.txt_peso_total_saida.value)
    var preco_unitario          = strtofloat(document.form.txt_preco_unitario.value)
    var lote_minimo_custo_tt    = strtofloat(document.form.txt_lote_minimo_custo_tt.value)
    
    if(sigla == 'UN') {//Se a unidade do CTT = "Unidade", ent�o utilizo o campo Qtde ... 
        var peso_qtde_total_utilizar = qtde_saida
    }else {//Se a unidade do CTT <> "Unidade", ent�o utilizo o campo Peso Total  ... 
        var peso_qtde_total_utilizar = peso_total_saida_kg
    }

//Aki eu verifico se existe a marca��o de Lote M�nimo p/ o Item ...
    if(document.form.chkt_cobrar_lote_minimo.checked == true) {
        if(peso_qtde_total_utilizar * preco_unitario < lote_minimo_custo_tt) {
            document.form.txt_preco_total.value = lote_minimo_custo_tt
        }else {
            document.form.txt_preco_total.value = peso_qtde_total_utilizar * preco_unitario
        }
    }else {
        document.form.txt_preco_total.value = peso_qtde_total_utilizar * preco_unitario
    }
    document.form.txt_preco_total.value = arred(document.form.txt_preco_total.value, 2, 1)
}

function calcular_peso_unit_saida() {
//Qtde de Sa�da - Para n�o dar erro de Divis�o por Zero
    var qtde_saida = (document.form.txt_qtde_saida.value == '' || document.form.txt_qtde_saida.value == 0) ? 1 : eval(strtofloat(document.form.txt_qtde_saida.value))
//Peso Total de Sa�da em KG
    var peso_total_saida_kg = (document.form.txt_peso_total_saida.value == '') ? 0 : eval(strtofloat(document.form.txt_peso_total_saida.value))
    document.form.txt_peso_unit_saida.value = (peso_total_saida_kg / qtde_saida)
    document.form.txt_peso_unit_saida.value = arred(document.form.txt_peso_unit_saida.value, 4, 1)
}

function zerar_preco_unitario() {
//Se a Op��o de Trabalho tiver marcada, ent�o eu Zero o Pre�o Unit�rio, volto o Pre�o normal do CTT
    if(document.form.chkt_retrabalho.checked == true) {
        document.form.txt_preco_unitario.value = '0,00'
    }else {
        document.form.txt_preco_unitario.value = "<?=number_format($preco_unitario, 2, ',', '.');?>"
    }
}
</Script>
</head>
<body onload="zerar_preco_unitario();calcular_preco_total()" onunload="atualizar_abaixo()">
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Controle de Sa�da
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan="2">
            OS N.� <font color='yellow'><?=$id_os;?></font> - 
            Fornecedor: <font color='yellow'><?=$razaosocial;?></font> - 
            Data de Sa�da: <font color='yellow'><?=$data_saida;?></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>N.� OP:</b>
        </td>
        <td width='80%'>
            <?=$id_op;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto:</b>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos_pa[0]['id_produto_acabado']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Mat�ria Prima:</b>
        </td>
        <td>
        <?
            $sql = "SELECT discriminacao 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo_mat_prima' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            if(count($campos_pi) == 0) {//Se n�o encontrar ...
                echo '<font color="blue"><b>N�O H� MAT�RIA PRIMA</b></font>';
            }else {//Se encontrar o id_produto_insumo ...
                echo $campos_pi[0]['discriminacao'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde Nominal:</b>
        </td>
        <td>
            <?=$campos_pa[0]['qtde_produzir'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Sa�da:</b>
        </td>
        <td>
            <input type='text' name="txt_qtde_saida" value="<?=$qtde_saida;?>" title='Digite a Qtde de Sa�da' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this);peso_total_saida();calcular_peso_unit_saida();calcular_preco_total()" size="10" maxlength="10" class="<?=$class_saida;?>" <?=$disabled_saida;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CTT / USI:</b>
        </td>
        <td>
            <select name='cmb_ctt' title='Selecione o CTT' class='textdisabled' disabled>
            <?
/*Aqui traz todos os PI(s) que est�o relacionados ao id_produto_acabado_custo passado por par�metro, que tenham 
CTT(s) atrelado(s) da 5� Etapa "TRATAMENTO T�RMICO" e 6� Etapa "USINAGEM" 

A 6� etapa n�o precisa de CTT porque o C�digo de Tratamento T�rmico, s� � utilizado na 5� Etapa ...*/
                $sql = "(SELECT pi.id_produto_insumo, CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                        FROM `pacs_vs_pis_trat` ppt 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                        INNER JOIN `ctts` ON `ctts`.id_ctt = pi.`id_ctt` 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo') 
                        UNION 
                        (SELECT pi.id_produto_insumo, CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                        FROM `pacs_vs_pis_usis` ppu 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo') ORDER BY id_produto_insumo ";
                echo combos::combo($sql, $id_produto_insumo_ctt);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total de Sa�da:</b>
        </td>
        <?
            if($sigla != 'KG') {//Controle especial somente p/ este campo, se diferente de KG sempre fica travado ...
                $class_peso_total_saida     = 'textdisabled';
                $disabled_peso_total_saida  = 'disabled';
            }else {
                $class_peso_total_saida     = $class_saida;
                $disabled_peso_total_saida  = $disabled_saida;
            }
        ?>
        <td>
            <input type='text' name='txt_peso_total_saida' value="<?=number_format($peso_total_saida, 3, ',', '.');?>" title='Digite o Peso Total de Sa�da' size='8' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_unit_saida()" class="<?=$class_peso_total_saida;?>" <?=$disabled_peso_total_saida;?>>
            &nbsp;
            <input type='text' name='rotulo1' value='<?=$sigla;?>' class='caixadetexto2' style="color:black;font-weight:bold" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pre�o Unit�rio R$:
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' value="<?=number_format($preco_unitario, 2, ',', '.');?>" title='Pre�o Unit�rio' size='8' class='textdisabled' disabled>
            &nbsp;
            <?
/***************************************************************************************************************/
//Se esse PI estiver com essa marca��o p/ esse PA da OS na 5� Etapa do Custo, ent�o eu exibo esses campos abaixo ...
                if($lote_minimo_fornecedor == 1) {
                    echo '<font color="brown"><b>(Lote M�nimo)</b></font>';
            ?>
            -&nbsp;R$ <input type='text' name='txt_lote_minimo_custo_tt' value="<?=number_format($lote_minimo_custo_tt, 2, ',', '.');?>" title='Lote M�nimo do Custo TT' size='8' class='textdisabled' disabled>
            &nbsp;-
            <?
                    if($cobrar_lote_minimo == 'S') $checked_lote_minimo = 'checked';
            ?>
            <input type='checkbox' name='chkt_cobrar_lote_minimo' value='S' id='cobrar_lote_minimo' onclick='calcular_preco_total()' class='checkbox' <?=$checked_lote_minimo;?>>
            <label for='cobrar_lote_minimo'>Cobrar Lote M�nimo</label>
            <?
                }
/***************************************************************************************************************/
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total Sa�da R$:
        </td>
        <td>
            <input type='text' name='txt_preco_total' value="<?=number_format($peso_total_saida * $preco_unitario, 2, ',', '.');?>" title='Pre�o Total' size='8' class='textdisabled' disabled>
            &nbsp;
            <?
                if($retrabalho == 1) $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_retrabalho' value='1' id='retrabalho' onclick='zerar_preco_unitario()' class='checkbox' <?=$checked;?> <?=$disabled_saida;?>>
            <label for='retrabalho'>Retrabalho</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde Unit�ria de Sa�da:
        </td>
        <td>
            <input type='text' name="txt_peso_unit_saida" value="<?=number_format($peso_aco, 4, ',', '.');?>" id="txt_peso_unit_saida" size="7" class='textdisabled' disabled>
            &nbsp;-&nbsp;
            <input type='text' name="txt_peso_peca_corrigido" value="<?=number_format($peso_aco, 4, ',', '.');?>" id="txt_peso_peca_corrigido" size="7" class="disabled" disabled>
            &nbsp;
            <input type='text' name='rotulo2' value='<?=$sigla;?>' class='caixadetexto2' style="color:black;font-weight:bold" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o de Retrabalho:
        </td>
        <td>
            <textarea name='txt_observacao_retrabalho' cols='85' rows='3' maxlength='255' class="<?=$class_saida;?>" <?=$disabled_saida;?>><?=$obs_retrabalho;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');peso_total_saida();calcular_peso_unit_saida();calcular_preco_total()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Pr�xima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
<!--Essa caixa eu utilizo para fazer os c�lculos-->
<input type='hidden' name='peso_aco' value='<?=$peso_aco;?>'>
<!--Essas caixas eu utilizo poder gravar no BD-->
<input type='hidden' name='id_op' value="<?=$id_op;?>">
<input type='hidden' name='id_os' value="<?=$id_os;?>">
<input type='hidden' name='id_os_item' value="<?=$id_os_item;?>">
<input type='hidden' name='id_pac_pi_trat' value="<?=$id_pac_pi_trat;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='nao_atualizar'>
<!--<input type='hidden' name='acao' value='<?=$acao;?>'>-->
<!--//Por enquanto o controle s� ir� vai ser feito em cima da Parte de Sa�da da OS ...-->
<input type='hidden' name='acao' value='1'>
</form>
</body>
</html>