<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) DE ENTRADA ATUALIZADO(S) COM SUCESSO.</font>";

$id_os  = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_os'] : $_GET['id_os'];


if(!empty($_POST['hdd_os_item'])) {
    if($_POST['hdd_status'] == 0) {//Significa que esse item ainda n�o foi importado em NF, sendo assim posso estar alterando normalmente ...
        if($_POST['txt_numero_nf'] > 0 && !empty($_POST['txt_qtde_entrada']) && !empty($_POST['txt_total_entrada']) && !empty($_POST['txt_dureza_fornecedor'])) {
            /***************Tratamento com a NF de Entrada - Compras***************/
            /*Verifico se j� existe alguma NF de Entrada em Compras com o N.� digitado pelo usu�rio para esse 
            Fornecedor e Empresa da OS que esteja em aberto*/
            $sql = "SELECT `id_nfe` 
                    FROM `nfe` 
                    WHERE `id_empresa` = '$_POST[hdd_empresa]' 
                    AND `id_fornecedor` = '$_POST[hdd_fornecedor]' 
                    AND `num_nota` = '$_POST[txt_numero_nf]' 
                    AND `situacao` < '2' LIMIT 1 ";
            $campos_nfe = bancos::sql($sql);
            if(count($campos_nfe) == 0) {//N�o existe uma NF de Entrada ainda ...
                $tipo   = ($_POST['hdd_empresa'] == 1 || $_POST['hdd_empresa'] == 2) ? 1 : 2;
                //O sistema ir� criar uma NF de Entrada p/ Compras automaticamente ...
                $sql    = "INSERT INTO `nfe` (`id_nfe`, `id_empresa`, `id_fornecedor`, `id_tipo_pagamento_recebimento`, `id_tipo_moeda`, `num_nota`, `tipo`, `data_emissao`, `data_entrega`) VALUES (NULL, '$_POST[hdd_empresa]', '$_POST[hdd_fornecedor]', '1', '1', '$_POST[txt_numero_nf]', '$tipo', '".date('Y-m-d')."', '".date('Y-m-d')."') ";
                bancos::sql($sql);
                $id_nfe = bancos::id_registro();
            }else {//Ainda existe uma NF de Entrada em aberto ...
                //O sistema ir� reaproveitar uma NF de Entrada j� existente ...
                $id_nfe = $campos_nfe[0]['id_nfe'];
            }
            /***************Tratamento com a OS - Produ��o***************/
            //Gero um Registro de Entrada para o Item da OS ...
            $sql = "INSERT INTO `oss_itens` (`id_os_item`, `id_os`, `id_op`, `id_produto_insumo_ctt`, `id_nfe`, `qtde_entrada`, `dureza_fornecedor`, `dureza_interna`, `peso_total_entrada`, `id_funcionario_entrada`, `data_entrada`) 
                    VALUES (NULL, '$_POST[id_os]', '$_POST[id_op]', '$_POST[id_produto_insumo_ctt]', '$id_nfe', '$_POST[txt_qtde_entrada]', '$_POST[txt_dureza_fornecedor]', '$_POST[txt_dureza_interna]', '$_POST[txt_total_entrada]', '$_SESSION[id_funcionario]', '".date('Y-m-d')."') ";
            bancos::sql($sql);
            $id_os_item_entrada = bancos::id_registro();
            
            //V�nculo nessa Entrada a sua respectiva Sa�da - "Toda Entrada acontece depois que t�vemos uma Sa�da do Item" ...
            $sql = "UPDATE `oss_itens` SET `id_os_item_saida` = '$_POST[hdd_os_item]' WHERE `id_os_item` = '$id_os_item_entrada' LIMIT 1 ";
            bancos::sql($sql);
            
            $valor = 1;
        }
    }
}

/******************************Rotina normal do arquivo******************************/
//Seleciona a qtde de itens que existe na OS que n�o possuem Entrada ...
$sql = "SELECT COUNT(`id_os_item`) AS qtde_itens 
        FROM `oss_itens` 
        WHERE `id_os` = '$id_os' 
        AND `id_nfe` IS NULL ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

if(empty($posicao)) $posicao = 1;

//Seleciona os itens da OS que n�o possuem Entrada ...
$sql = "SELECT * 
        FROM `oss_itens` 
        WHERE `id_os` = '$id_os' 
        AND `id_nfe` IS NULL ORDER BY `id_os_item` ";
$campos                         = bancos::sql($sql, ($posicao - 1), $posicao);
$id_os_item 			= $campos[0]['id_os_item'];
$id_op                          = $campos[0]['id_op'];
$id_nfe                         = $campos[0]['id_nfe'];
$id_produto_insumo_mat_prima    = $campos[0]['id_produto_insumo_mat_prima'];
$id_produto_insumo_ctt          = $campos[0]['id_produto_insumo_ctt'];
$id_item_pedido                 = $campos[0]['id_item_pedido'];
$qtde_saida                     = $campos[0]['qtde_saida'];
$data_saida                     = data::datetodata($campos[0]['data_saida'], '/');
$dureza_fornecedor		= $campos[0]['dureza_fornecedor'];
$dureza_interna			= $campos[0]['dureza_interna'];
$peso_total_saida		= $campos[0]['peso_total_saida'];
$obs_retrabalho			= $campos[0]['obs_retrabalho'];
$cobrar_lote_minimo		= $campos[0]['cobrar_lote_minimo'];
$lote_minimo_custo_tt           = $campos[0]['lote_minimo_custo_tt'];
$retrabalho                     = $campos[0]['retrabalho'];
$status                         = $campos[0]['status'];

//Busca do Produto da OP agora atrav�s do id_op que est� na OS - vou utilizar isso aki + abaixo
$sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo` 
        FROM `ops` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
        WHERE ops.`id_op` = '$id_op' LIMIT 1 ";
$campos_op = bancos::sql($sql);

//Aqui eu busco o id_produto_acabado_custo do produto_acabado corrente
$sql = "SELECT `id_produto_acabado_custo` 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado` = '".$campos_op[0]['id_produto_acabado']."' 
        AND `operacao_custo` = '".$campos_op[0]['operacao_custo']."' LIMIT 1 ";
$campos2 = bancos::sql($sql);
$id_produto_acabado_custo = $campos2[0]['id_produto_acabado_custo'];

//Busca de alguns dados da OS, vou precisar desses em algumas situa��es pouco mais pra baixo ...
$sql = "SELECT f.`razaosocial`, f.`nf_minimo_tt` AS nf_minimo_tt_cad, oss.* 
        FROM `oss` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
        WHERE oss.`id_os` = '$id_os' LIMIT 1 ";
$campos_os      = bancos::sql($sql);
$razaosocial    = $campos_os[0]['razaosocial'];
$id_fornecedor  = $campos_os[0]['id_fornecedor'];
$data_saida     = ($campos_os[0]['data_saida'] != '0000-00-00') ? data::datetodata($campos_os[0]['data_saida'], '/') : '';
$id_pedido      = $campos_os[0]['id_pedido'];
//Aqui eu renomeio para esse nome, para n�o dar conflito com a vari�vel $_SESSION['id_empresa'} ...
$id_empresa_os  = $campos_os[0]['id_empresa'];

/**********************************************Seguran�a*********************************************/
if($id_empresa_os == 0) {//Nunca podemos dar andamento nesse processo s/ termos a Empresa de OS preenchida no Cabe�alho ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA OS N.� <?=$id_os?> EST� SEM EMPRESA PREENCHIDA NO SEU CABE�ALHO !!!\n\nPOR FAVOR ATUALIZE O CABE�ALHO DA MESMA P/ VOC� POSSA PROSSEGUIR ESTA ROTINA !')
        parent.fechar_pop_up_div()
    </Script>
<?
}
/********************************Controle para apresenta��o dos Dados********************************/
/*Busco o Status da OS para saber de quais locais de qual local que eu vou buscar 
o Lote M�nimo, Nf M�nima, Pre�o Unit�rio, Qtde_Unitaria de Sa�da que � o antigo "peso_unit_saida - BD"*/
if($id_pedido == 0) {//Essa OS ainda n�o foi importada, ent�o busco do Cadastro do Fornecedor
//Como a OS ainda n�o est� travada, eu busco esses dados direto do cadastro do Fornecedor
    $nf_minimo_tt           = $campos_os[0]['nf_minimo_tt_cad'];//Uso para comparar no JavaScript ...
//Parte de Item da OS
//Como a OS ainda n�o est� travada, eu busco o Pre�o do Produto - CTT direto da Lista de Pre�o do Fornecedor
    $sql = "SELECT `preco` 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo_ctt' 
            AND `id_fornecedor` = '$id_fornecedor' ";
    $campos_preco   = bancos::sql($sql);
    $preco_unitario = $campos_preco[0]['preco'];
//Como a OS ainda n�o est� travada, o sistema tenta buscar o Peso desse Item l� na 5� Etapa se o mesmo for TRAT ...
    $sql = "SELECT `id_pac_pi_trat`, `peso_aco` 
            FROM `pacs_vs_pis_trat` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
            AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
    $campos_peso        = bancos::sql($sql);
    if(count($campos_peso) == 1) {//Encontrou dados na 5� Etapa ...
        $id_pac_pi_trat = $campos_peso[0]['id_pac_pi_trat'];
        $peso_aco       = $campos_peso[0]['peso_aco'];
    }else {//Como n�o encontrou dados na 5� Etapa, o sistema tenta buscar dados na 6� Etapa ...
        $sql = "SELECT `qtde` 
                FROM `pacs_vs_pis_usis` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
                AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
        $campos_peso    = bancos::sql($sql);
        $id_pac_pi_trat = 0;//Essa � uma vari�vel que n�o existe nessa 6� Etapa ...
        $peso_aco       = $campos_peso[0]['qtde'];//Como n�o existe Peso na 6� Etapa, ent�o trabalhamos com a Qtde ...
    }
}else {//Essa OS j� foi importada sendo assim, eu busco os valores das pr�prias tabelas de OS e Itens de OS
//Dados correspondentes a tabela de OS
    $nf_minimo_tt           = $campos_os[0]['nf_minimo_tt'];//Uso para comparar no JavaScript ...
//Dados correspondentes a tabela de Item da OS
    $preco_unitario         = $campos[0]['preco_pi'];
    $peso_aco               = $campos[0]['peso_unit_saida'];
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
/****************************************************************************************************/
//Busca a Marca��o desse PI com o PA l� na 5� Etapa do Custo p/ saber se este tem a Marca��o de Lote M�nimo
    $sql = "SELECT `lote_minimo_fornecedor` 
            FROM `pacs_vs_pis_trat` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
            AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
    $campos_lote_minimo     = bancos::sql($sql);
    $lote_minimo_fornecedor = $campos_lote_minimo[0]['lote_minimo_fornecedor'];

    //Busca dos Produtos da OP agora atrav�s do id_op que est� na OS
    $sql = "SELECT ops.`qtde_produzir`, pa.`id_produto_acabado`, pa.`referencia` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            WHERE ops.`id_op` = '$id_op' ";
    $campos_pa = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir Entrada da OS N.�&nbsp;<?=$id_os;?> ::.</title>
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
//Numero NF
        if(!texto('form', 'txt_numero_nf', '1', '1234567890', 'NUMERO DA NF', '2')) {
            return false
        }
//Verifica��o de dados Inv�lidos no N�mero da NF de Entrada ...
        if(document.form.txt_numero_nf.value == 0) {
            alert('N�MERO DE NF DE ENTRADA INV�LIDO ! \nVALOR IGUAL A ZERO !')
            document.form.txt_numero_nf.focus()
            document.form.txt_numero_nf.select()
            return false
        }
//Quantidade de Entrada
        if(!texto('form', 'txt_qtde_entrada', '1', '1234567890', 'QUANTIDADE DE ENTRADA', '1')) {
            return false
        }        
//Verifica��o de dados Inv�lidos na Quantidade de Entrada
        if(document.form.txt_qtde_entrada.value == 0) {
            alert('QUANTIDADE DE ENTRADA INV�LIDA ! \nVALOR IGUAL A ZERO !')
            document.form.txt_qtde_entrada.focus()
            document.form.txt_qtde_entrada.select()
            return false
        }
//Peso Total de Entrada
        if(!texto('form', 'txt_total_entrada', '1', '1234567890,.', 'TOTAL DE ENTRADA', '2')) {
            return false
        }
//Verifica��o de dados Inv�lidos na Quantidade de Entrada
        if(document.form.txt_total_entrada.value == '0,000') {
            alert('TOTAL DE ENTRADA INV�LIDA ! \nVALOR IGUAL A ZERO !')
            document.form.txt_total_entrada.focus()
            document.form.txt_total_entrada.select()
            return false
        }
        //Significa que esses item s�o pertinentes a 5� Etapa do Custo "TRAT" ...
        if(document.form.hdd_validar_durezas.value == 'SIM') {//Significa que temos que validar esse campo de QQ jeito ...
//Dureza Interna ...
            if(!texto('form', 'txt_dureza_interna', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQ'WERTYUIOPLK�J.|HGFDSAZXCVBNM,.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'DUREZA INTERNA', '1')) {
                return false
            }
//Dureza Fornecedor ...
            if(!texto('form', 'txt_dureza_fornecedor', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQ'WERTYUIOPLK�J.|HGFDSAZXCVBNM,.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'DUREZA DO FORNECEDOR', '1')) {
                return false
            }
        }
/*********************Seguran�a de Confer�ncia da Qtde - Refugo*********************/
        var qtde_saida          = eval('<?=$qtde_saida;?>')
        if(Math.abs(qtde_saida - document.form.txt_qtde_entrada.value) > 0.01 * qtde_saida) {
            var resposta = confirm("A QTDE DE ENTRADA EST� C/ DIFEREN�A DE +- 1% !!!\nCONFIRMA ESTA QTDE?")
            if(resposta == false) return false;
        }
/*********************Seguran�a de Confer�ncia do Peso - Refugo*********************/
        var peso_total_saida    = eval('<?=$peso_total_saida;?>')
        var total_entrada       = eval(strtofloat(document.form.txt_total_entrada.value))
        
        if(Math.abs(peso_total_saida - total_entrada) > 0.01 * peso_total_saida) {
            var resposta = confirm("O TOTAL DE ENTRADA EST� C/ DIFEREN�A DE +- 1% !!!\nCONFIRMA ESTE TOTAL?")
            if(resposta == false) return false;
        }
/***********************************************************************************/
    }
//Desabilito para poder gravar no BD
    document.form.txt_total_entrada.disabled    = false
    document.form.txt_dureza_interna.disabled   = false
    limpeza_moeda('form', 'txt_total_entrada, ')
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
    document.form.posicao.value = posicao;
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formul�rio
    document.form.submit()
}

function copiar_dados_saida() {
    document.form.txt_qtde_entrada.value    = '<?=$qtde_saida;?>'
    document.form.txt_total_entrada.value   = '<?=number_format($peso_total_saida, 3, ',', '.');?>'
}

function calcular() {
    var qtde_saida          = eval('<?=$qtde_saida;?>')
    var peso_total_saida    = eval('<?=$peso_total_saida;?>')
    
    document.form.txt_total_entrada.value = (peso_total_saida * document.form.txt_qtde_entrada.value) / qtde_saida
    document.form.txt_total_entrada.value = arred(document.form.txt_total_entrada.value, 3, 1)
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_entrada.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Controle de Sa�da
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            OS N.� <font color='yellow'><?=$id_os;?></font> - 
            Fornecedor: <font color='yellow'><?=$razaosocial;?></font> - 
            Data de Sa�da: <font color='yellow'><?=$data_saida;?></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.� OP:</b>
        </td>
        <td>
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
            $sql = "SELECT `discriminacao` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo_mat_prima' LIMIT 1 ";
            $campos_materia_prima = bancos::sql($sql);
            if(count($campos_materia_prima) == 0) {//Se n�o encontrar ...
                echo '<font color="blue"><b>N�O H� MAT�RIA PRIMA</b></font>';
            }else {//Se encontrar o id_produto_insumo ...
                echo $campos_materia_prima[0]['discriminacao'];
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
            <b>CTT / USI:</b>
        </td>
        <td>
        <?
/*Aqui traz todos os PI(s) que est�o relacionados ao id_produto_acabado_custo passado por par�metro, que tenham 
CTT(s) atrelado(s) da 5� Etapa "TRATAMENTO T�RMICO" e 6� Etapa "USINAGEM" 

A 6� etapa n�o precisa de CTT porque o C�digo de Tratamento T�rmico, s� � utilizado na 5� Etapa ...*/
            $sql = "(SELECT CONCAT(u.`sigla`, ' - ', pi.`discriminacao`) AS dados 
                    FROM `pacs_vs_pis_trat` ppt 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` AND pi.`id_produto_insumo` = '$id_produto_insumo_ctt' 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                    INNER JOIN `ctts` ON `ctts`.id_ctt = pi.`id_ctt` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo') 
                    UNION 
                    (SELECT CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                    FROM `pacs_vs_pis_usis` ppu 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` AND pi.`id_produto_insumo` = '$id_produto_insumo_ctt' 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo') ";
            $campos_pi_relacionado = bancos::sql($sql);
            echo $campos_pi_relacionado[0]['dados'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                <b>Pre�o Unit�rio R$:</b>
            </td>
            <td>
            <?=number_format($preco_unitario, 2, ',', '.');?>
            &nbsp;
            <?
/***************************************************************************************************************/
//Se esse PI estiver com essa marca��o p/ esse PA da OS na 5� Etapa do Custo, ent�o eu exibo esses campos abaixo ...
                if($lote_minimo_fornecedor == 1) {
                    echo '<font color="brown"><b>(Lote M�nimo)</b></font>';
            ?>
            -&nbsp;R$ <?=number_format($lote_minimo_custo_tt, 2, ',', '.');?>
            &nbsp;-
            <?
                    if($cobrar_lote_minimo == 'S') echo '<font color="red"><b> (Cobrar Lote M�nimo)</b></font>';
                }
/***************************************************************************************************************/
            ?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                <b>Total Sa�da R$:</b>
            </td>
            <td>
                <?=number_format($peso_total_saida * $preco_unitario, 2, ',', '.');?>
                &nbsp;
                <?
                    if($retrabalho == 1) echo '<font color="red"><b> (Retrabalho)</b></font>';
                ?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                <b>Qtde Unit�ria de Sa�da:</b>
            </td>
            <td>
                <?=number_format($peso_aco, 4, ',', '.');?>
                &nbsp;-&nbsp;
                <?=number_format($peso_aco, 4, ',', '.');?>
                &nbsp;
                <strong><?=$sigla;?></strong>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                <b>Observa��o de Retrabalho:</b>
            </td>
            <td>
                <?=$obs_retrabalho;?>
            </td>
    </tr>
</table>
<!--*************************************************************************************-->
        <?
            //Busca da sigla do PI quando carrega a tela
            $sql = "SELECT `sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
            $campos_unidade = bancos::sql($sql);
            $sigla          = $campos_unidade[0]['sigla'];
            $variavel_total = ($sigla == 'KG') ? 'Peso Entrada' : 'Entrada';
        ?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Controle de Entrada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td></td>
        <td>
            Entrada
        </td>
        <td>
            Sa�da
        </td>
    </tr>
    <tr class='linhanormal'>
        <td><b>N� N.F</b></td>            
        <td>
            <?
                if($id_nfe > 0) {
                    //Traz o N.� da NF que foi gravada no BD para este item ...
                    $sql = "SELECT `num_nota` 
                            FROM `nfe` 
                            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                    $campos_nfe         = bancos::sql($sql);
                    $numero_nf_entrada  = $campos_nfe[0]['num_nota'];
                }else {
                    //Trago a 1� NF do Fornecedor e da Empresa da OS que n�o esteja liberada ...
                    $sql = "SELECT `num_nota` 
                            FROM `nfe` 
                            WHERE `situacao` = '0' 
                            AND `id_fornecedor` = '$id_fornecedor' 
                            AND `id_empresa` = '$id_empresa_os' 
                            AND SUBSTRING(`data_emissao`, 1, 10) <= '".date('Y-m-d')."' 
                            ORDER BY `id_nfe` DESC LIMIT 1 ";
                    $campos_nfe         = bancos::sql($sql);
                    $numero_nf_entrada  = $campos_nfe[0]['num_nota'];
                }
            ?>
            <input type='text' name="txt_numero_nf" value="<?=$numero_nf_entrada;?>" size="8" title="Digite o Numero da NF Entrada" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
        <td></td>
    </tr>        
    <tr class='linhanormal'>
        <td>
            <b>Qtde Entrada</b>
        </td>
        <td>
            <?
                //Aqui eu busco o total de Entradas do id_os_item que saiu ...
                $sql = "SELECT SUM(`qtde_entrada`) AS total_entradas, SUM(`peso_total_entrada`) AS peso_total_entradas 
                        FROM `oss_itens` 
                        WHERE `id_os_item_saida` = '$id_os_item' ";
                $campos_entradas        = bancos::sql($sql);
                $qtde_entrada           = ($qtde_saida - $campos_entradas[0]['total_entradas']);
                $peso_total_entrada     = ($peso_total_saida - $campos_entradas[0]['peso_total_entradas']);
            ?>
            <input type='text' name='txt_qtde_entrada' value='<?=$qtde_entrada;?>' size='8' title='Digite a Qtde de Entrada' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular()" class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_copiar_dados_saida' value=' <= Copiar Dados de Sa�da' title='Copiar Dados de Sa�da' onclick='copiar_dados_saida()' style='color:black' class='botao'>
        </td>
        <td>
            <?=$qtde_saida;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><?=$variavel_total;?></b>
        </td>
        <td> 
            <input type='text' name='txt_total_entrada' value="<?=number_format($peso_total_entrada, 3, ',', '.');?>" size="8" title="Digite o Total de Entrada" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
            <b><?=$sigla;?></b>
        </td> 
        <td>
            <?=number_format($peso_total_saida, 3, ',', '.').' <strong>'.$sigla;?></strong>
        </td>            
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dureza Interna</b>
        </td>
        <td>
            <?
                if(empty($dureza_interna)) {//Se � existe Dureza Interna p/ este Item, sugere ent�o a Dureza do Cadastro de CTT ...
                    $sql = "SELECT ctts.`dureza_interna` 
                            FROM `produtos_insumos` pi 
                            INNER JOIN `ctts` ON ctts.`id_ctt` = pi.`id_ctt` 
                            WHERE pi.`id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
                    $campos_ctts    = bancos::sql($sql);
                    if(count($campos_ctts) == 1) {//Se existe CTT, significa que esse item � da 5� Etapa do Custo "TRAT" ...
                        $validar_durezas    = 'SIM';
                        $dureza_interna     = $campos_ctts[0]['dureza_interna'];
                    }else {//Se n�o existe CTT, significa que esse item � da 6� Etapa do Custo "USI" ...
                        $validar_durezas    = 'NAO';
                    }
                }
            ?>
            <input type='text' name='txt_dureza_interna' value='<?=$dureza_interna;?>' title='Digite a Dureza Interna' size='35' maxlength='30' class='textdisabled' disabled>
            <!--*****Essa hidden nos servir� de controle p/ fazermos valida��o desse campo em JavaScript*****-->
            <input type='hidden' name='hdd_validar_durezas' value='<?=$validar_durezas;?>'>
            <!--*********************************************************************************************-->
        </td>
        <td></td>            
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dureza Fornecedor</b>
        </td>
        <td>
            <input type='text' name="txt_dureza_fornecedor" value="<?=$dureza_fornecedor;?>" title='Digite a Dureza do Fornecedor' size='35' maxlength='30' class='caixadetexto'>
        </td>
        <td></td>
    </tr>
<!--*************************************************************************************-->
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <?
                if($status == 0) {
            ?>
                <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_entrada.focus()" class='botao'>
                <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?  
                }
            ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='3'>
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
<!--************************Controles de Tela************************-->
<input type='hidden' name='hdd_os_item' value='<?=$id_os_item;?>'>
<!--Esses 3 campos abaixo s�o usados no INSERT quando o usu�rio d� entrada-->
<input type='hidden' name='id_os' value='<?=$id_os;?>'>
<input type='hidden' name='id_op' value='<?=$id_op;?>'>
<input type='hidden' name='id_produto_insumo_ctt' value='<?=$id_produto_insumo_ctt;?>'>
<!--**********************************************************************-->
<input type='hidden' name='hdd_status' value='<?=$campos[0]['status'];?>'>
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_excluir_dados_entrada'>
<!--Com essas 2 vari�veis eu n�o preciso fazer uma outra Query quando submeter-->
<input type='hidden' name='hdd_empresa' value='<?=$id_empresa_os;?>'>
<input type='hidden' name='hdd_fornecedor' value='<?=$id_fornecedor;?>'>
<!--*****************************************************************-->
</form>
</body>
</html>