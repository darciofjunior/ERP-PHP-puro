<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/data.php');
require('../../../../lib/custos.php');
require('../../../../lib/vendas.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/os/incluir.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM(NS) DE OP COM MESMA CTT J� EXISTENTE.</font>";

if($passo == 1) {
    $condicao = (!empty($chkt_incluir_op_atrelada)) ? ' AND o.status_import IN (0,1) ' : ' AND o.status_import = 0 ';
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT o.*, pa.`operacao_custo`, pa.`referencia` 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado` 
                    WHERE o.`id_op` = '$txt_consultar' 
                    AND o.`ativo` = '1' $condicao ORDER BY o.`id_op` DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?id_os=<?=$id_os;?>&valor=1'
        </Script>
<?
    }else {
//Busca de alguns dados da OS, vou precisar desses em algumas situa��es pouco mais pra baixo*/
        $sql = "SELECT f.`razaosocial`, oss.`id_fornecedor`, oss.`data_saida` 
                FROM `oss` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
                WHERE oss.`id_os` = '$id_os' LIMIT 1 ";
        $campos_fornecedor  = bancos::sql($sql);
        $id_fornecedor      = $campos_fornecedor[0]['id_fornecedor'];
        $razaosocial        = $campos_fornecedor[0]['razaosocial'];
        $data_saida         = ($campos_fornecedor[0]['data_saida'] != '0000-00-00') ? data::datetodata($campos_fornecedor[0]['data_saida'], '/') : '';
/****************************************************************************************************/
/*Aqui eu busco o id_produto_acabado_custo do produto_acabado corrente, vou precisar desse id
em algumas situa��es pouco mais pra baixo*/
        $sql = "SELECT `id_produto_acabado_custo` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = ".$campos[0]['id_produto_acabado']." 
                AND `operacao_custo` = ".$campos[0]['operacao_custo']." LIMIT 1 ";
        $campos_custo = bancos::sql($sql);
        $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
//Busca a Marca��o desse PI com o PA l� na 5� Etapa do Custo p/ saber se este tem a Marca��o de Lote M�nimo
        $sql = "SELECT `lote_minimo_fornecedor` 
                FROM `pacs_vs_pis_trat` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
                AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
        $campos_lote_minimo     = bancos::sql($sql);
        $lote_minimo_fornecedor = $campos_lote_minimo[0]['lote_minimo_fornecedor'];

        //Busca dos Produtos da OP agora atrav�s do id_op que est� na OS
        $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia` 
                FROM `ops` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                WHERE ops.`id_op` = '".$campos[0]['id_op']."' ";
        $campos_pa = bancos::sql($sql);
        
        /*******************Baixas Manipula��es*******************/
        //Verifico se j� foi dada alguma Baixa de PI para esta OP que foi escolhida para ser vinculada a esta OS ...
        $sql = "SELECT `id_baixa_op_vs_pi` 
                FROM `baixas_ops_vs_pis` 
                WHERE `id_op` = '".$campos[0]['id_op']."' LIMIT 1 ";
        $campos_baixa_pi = bancos::sql($sql);
        $linhas_baixa_pi = count($campos_baixa_pi);
        /*********************************************************/
?>
<html>
<head>
<title>.:: OP(s) p/ Incluir na OS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Quantidade de Sa�da
    if(!texto('form', 'txt_qtde_saida', '1', '1234567890', 'QUANTIDADE DE SA�DA', '1')) {
        return false
    }
//Verifica��o de dados Inv�lidos na Quantidade de Sa�da
    if(document.form.txt_qtde_saida.value == 0) {
        alert('QUANTIDADE DE SA�DA INV�LIDA !')
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
            window.close()
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
//Compara��o entre os 2 pesos
    var peso_unitario_saida = eval(strtofloat(document.form.txt_peso_unit_saida.value))
    var peso_peca_corrigo = eval(strtofloat(document.form.txt_peso_peca_corrigido.value))

    if(((peso_unitario_saida / peso_peca_corrigo) > 1.01) || ((peso_peca_corrigo / peso_unitario_saida) > 1.01)) {
        var resposta = confirm('DIFEREN�A DE PESO UNIT�RIO SUPERIOR A 1% !\nDESEJA CONTINUAR ?')
        if(resposta == false) return false
    }
    /************************Em PAS 'ESP' existe seguran�a************************/
    //A condi��o � que a Qtde a ser Produzida pode variar no m�ximo de +- 10% da Qtde do Pedido
    var referencia = '<?=$campos_pa[0]['referencia'];?>'
    if(referencia == 'ESP') {
        var qtde_nominal 	= eval(strtofloat('<?=$campos[0]['qtde_produzir'];?>'))
        var qtde_saida 		= eval(strtofloat(document.form.txt_qtde_saida.value))
        if((0.9 * qtde_nominal > qtde_saida) || (qtde_saida > 1.1 * qtde_nominal)) {
            var resposta = confirm('QUANTIDADE NOMINAL INV�LIDA !!!\n\nA QUANTIDADE DE SA�DA � SUPERIOR OU INFERIOR A 10% DA QUANTIDADE NOMINAL, DESEJA CONTINUAR ?')
            if(resposta == false) {
                document.form.txt_qtde_saida.focus()
                document.form.txt_qtde_saida.select()
                return false
            }
        }
    }
    /*****************************************************************************/
//Nem sempre existe esse objeto, somente quando na 5� Etapa do Custo foi marcada a op��o de seguir o caminho de Lote M�nimo ...
    if(typeof(document.form.txt_lote_minimo_custo_tt) == 'object') {
//Desabilito para poder gravar no BD ...
        document.form.txt_lote_minimo_custo_tt.disabled = false
        limpeza_moeda('form', 'txt_lote_minimo_custo_tt, ')
    }
    //Desabilito esses campos p/ gravar no BD ...
    document.form.txt_preco_unitario.disabled   = false
    document.form.txt_peso_unit_saida.disabled  = false
    document.form.txt_peso_total_saida.disabled = false
    document.form.passo.value = 2
    limpeza_moeda('form', 'txt_peso_total_saida, txt_preco_unitario, txt_peso_unit_saida, ')
}

function onload() {
    var linhas_baixa_pi = eval('<?=$linhas_baixa_pi;?>')
    if(linhas_baixa_pi == 0) {
        alert('ESSA OP N�O TEM PI BAIXADO !!!\n\nACERTE A BAIXA DO PI !')
        window.close()
    }
}

function controlar_digitos(objeto) {
    if(objeto.value.length > 1) {//Se tiver pelo menos 2 d�gitos ...
        if(objeto.value.substr(0, 1) == '0') {
            objeto.value = objeto.value.substr(1, 1)
        }
    }
}

function separar_preco() {
    var ctt = document.form.cmb_ctt.value
    var achou_pipe = 0
    var id_produto_insumo_ctt = '', preco_unitario = '', peso_aco = ''
    for(i = 0; i < ctt.length; i++) {
        if(ctt.charAt(i) == '|') {
            achou_pipe++
        }else {//Aqui � trodo tratamento antes do Pipe
            if(achou_pipe == 0) {//Parte do Id
                id_produto_insumo_ctt+= ctt.charAt(i)
            }else if(achou_pipe == 1) {//Parte do Pre�o Unit�rio
                preco_unitario+= ctt.charAt(i)
            }else if(achou_pipe == 2) {//Parte do Peso A�o
                peso_aco+= ctt.charAt(i)
            }
        }
    }
//Peso A�o
    document.form.peso_aco.value = peso_aco //Hidden
//Pre�o Unit�rio
    document.form.txt_preco_unitario.value = arred(preco_unitario, 2, 1)
    document.form.txt_preco_unitario.disabled = false
    document.form.id_produto_insumo_ctt.value = id_produto_insumo_ctt
    document.form.passo.value = 1
    document.form.submit()
}

function peso_total_saida() {
//Qtde de Sa�da
    var qtde_saida  = (document.form.txt_qtde_saida.value == '') ? 0 : eval(strtofloat(document.form.txt_qtde_saida.value))
//Peso A�o
    var peso_aco    = (document.form.peso_aco.value == '') ? 0 : eval(document.form.peso_aco.value)
//Peso Total de Sa�da Kg -> Qtde de Sa�da * Peso A�o
    var resultado   = String(qtde_saida * peso_aco)//Gambiarra (rsrs)
    document.form.txt_peso_total_saida.value = arred(resultado, 3, 1)
}

function calcular_preco_total() {
    var sigla                   = document.form.rotulo1.value
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
//Qtde de Sa�da
    if(document.form.txt_qtde_saida.value == '' || document.form.txt_qtde_saida.value == 0) {
        var qtde_saida = 1//Para n�o dar erro de Divis�o por Zero
    }else {
        var qtde_saida = eval(strtofloat(document.form.txt_qtde_saida.value))
    }
//Peso Total de Sa�da em KG
    if(document.form.txt_peso_total_saida.value == '') {
        var peso_total_saida_kg = 0
    }else {
        var peso_total_saida_kg = eval(strtofloat(document.form.txt_peso_total_saida.value))
    }
    document.form.txt_peso_unit_saida.value = (peso_total_saida_kg / qtde_saida)
    document.form.txt_peso_unit_saida.value = arred(document.form.txt_peso_unit_saida.value, 4, 1)
}

function igualar_peso2() {
//Igualo com o Valor do Hidden
    document.form.txt_peso_peca_corrigido.value = document.form.peso_aco.value
    document.form.txt_peso_peca_corrigido.value = arred(document.form.txt_peso_peca_corrigido.value, 4, 1)
}

function atribuir_rotulos() {
    var ctt     = document.form.cmb_ctt[document.form.cmb_ctt.selectedIndex].text
    var unidade = ''
//Vasculho nesse loop somente a Unidade desses PI(s)
    for(i = 0; i < ctt.length; i++) {
        if(ctt.charAt(i) == ' ') {
            i = ctt.length//Fa�o assim para sair fora do la�o
        }else {
            unidade+= ctt.charAt(i)
        }
    }
    if(unidade != 'SELECIONE') {
        //Atribui��o da Unidade p/ esses PI(s) ...
        document.form.rotulo1.value = unidade
        document.form.rotulo2.value = unidade
        //Controle especial p/ o campo "txt_peso_total_saida", somente se igual a "KG" que sempre fica habilitado ...
        if(unidade == 'KG') {
            document.form.txt_peso_total_saida.className    = 'caixadetexto'
            document.form.txt_peso_total_saida.disabled     = ''
        }else {
            document.form.txt_peso_total_saida.className    = 'textdisabled'
            document.form.txt_peso_total_saida.disabled     = 'disabled'
        }
    }else {
        document.form.rotulo1.value = ''
        document.form.rotulo2.value = ''
    }
}

function zerar_preco_unitario() {
//Se a Op��o de Trabalho tiver marcada, ent�o eu Zero o Pre�o Unit�rio, volto o Pre�o normal do CTT
    if(document.form.chkt_retrabalho.checked == true) {
        document.form.txt_preco_unitario.value = '0,00'
    }else {
        separar_preco()
    }
//J� utilizo as fun��es normais para rec�lculo
    peso_total_saida()
    calcular_peso_unit_saida()
    calcular_preco_total()
}
</Script>
</head>
<body onload='onload();atribuir_rotulos();calcular_preco_total()'>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            OS N.� <font color='yellow'><?=$id_os;?></font> 
            - Fornecedor: <font color='yellow'><?=$razaosocial;?></font>
            - Data de Sa�da: <font color='yellow'><?=$data_saida;?></font>
            <br>Importar OP(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.� OP:</b>
        </td>
        <td>
            <a href = '../../ops/alterar.php?passo=2&id_op=<?=$campos[0]['id_op'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[0]['id_op'];?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto:</b>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos_pa[0]['id_produto_acabado']);?>
        <?
            echo '&nbsp;';
            /*********************Links p/ abrir o Custo*********************/
            if($campos_gerais[0]['operacao_custo'] == 0) {//Industrial
?>
            <a href="javascript:nova_janela('../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_pa[0]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Industrial' style='cursor:help' class='link'>
<?
            }else {
?>
            <a href="javascript:nova_janela('../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_pa[0]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Revenda' style='cursor:help' class='link'>
<?
            }
?>
                <img src = '../../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>    
            <!--/****************************************************************/-->
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Mat�ria Prima:</b>
            </td>
            <td>
                    <?
                            //Verifica se Existe Mat�ria Prima na 2� Etapa do Custo ...
                            $sql = "SELECT pi.id_produto_insumo, pi.discriminacao 
                                    FROM `produtos_acabados_custos` pac 
                                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pac.id_produto_insumo 
                                    WHERE pac.id_produto_acabado_custo = '$id_produto_acabado_custo' LIMIT 1 ";
                            $campos_materia_prima = bancos::sql($sql);
                            if(count($campos_materia_prima) == 0) {//Se n�o encontrar ...
                                echo '<font color="blue"><b>N�O H� MAT�RIA PRIMA</b></font>';
                            }else {//Se encontrar o id_produto_insumo ...
                                $alert_divergencia = 0;//Valor Padr�o ...
                                /*Aqui eu verifico se a �ltima a��o � "Baixa de PI" p/ o determinado PI que est� na 
                                2� Etapa do Custo e para a determinada OP consultada pelo usu�rio e que foi passado 
                                por par�metro ...*/
                                $sql = "SELECT bp.status 
                                        FROM `produtos_insumos` pi 
                                        INNER JOIN `baixas_ops_vs_pis` bp ON bp.id_op = '".$campos[0]['id_op']."' AND bp.id_produto_insumo = pi.id_produto_insumo 
                                        WHERE pi.id_produto_insumo = '".$campos_materia_prima[0]['id_produto_insumo']."' ORDER BY bp.id_baixa_op_vs_pi DESC LIMIT 1 ";
                                $campos_baixa_op 	= bancos::sql($sql);
                                if(count($campos_baixa_op) == 0) {//N�o foi dado Baixa ...
                                    $alert_divergencia = 1;
                                }else if(count($campos_baixa_op) == 1 && $campos_baixa_op[0]['status'] == 3) {
                                    $alert_divergencia = 1;//J� foi dado baixa, mas houve um Estorno ...
                                }
                                echo $campos_materia_prima[0]['discriminacao'];
                                if($alert_divergencia == 1) {
                    ?>
                                    <Script Language = 'JavaScript'>
                                        alert('ESTA OP EST� SEM PI BAIXADO NA 2� ETAPA !!!\n\nREQUISITE ACERTO DO ALMOXARIFADO P/ PODER CONTINUAR !')
                                        window.close()
                                    </Script>
                    <?
                                }
                            }
                    ?>
                    <input type='hidden' name='id_produto_insumo_mat_prima' value='<?=$campos_materia_prima[0]['id_produto_insumo']?>'>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Qtde Nominal:</b>
            </td>
            <td>
                    <?=number_format($campos[0]['qtde_produzir'], 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Sa�da:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_saida' value='0' title='Digite a Quantidade de Sa�da' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this);peso_total_saida();calcular_peso_unit_saida();calcular_preco_total()" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>CTT / USI:</b>
            </font>
        </td>
        <td>
            <select name='cmb_ctt' title='Selecione o CTT' onchange="separar_preco();peso_total_saida();calcular_peso_unit_saida();calcular_preco_total();igualar_peso2();atribuir_rotulos()" class='combo'>
            <?
//Busca do Produto da OP agora atrav�s do id_op que est� na OS
                $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo` 
                        FROM `ops` 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                        WHERE ops.`id_op` = '".$campos[0]['id_op']."' LIMIT 1 ";
                $campos_produto_acabado = bancos::sql($sql);
//Aqui eu busco o id_produto_acabado_custo do produto_acabado corrente
                $sql = "SELECT `id_produto_acabado_custo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '".$campos_produto_acabado[0]['id_produto_acabado']."' 
                        AND `operacao_custo` = '".$campos_produto_acabado[0]['operacao_custo']."' LIMIT 1 ";
                $campos_custo               = bancos::sql($sql);
                $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];
/*Aqui traz todos os PI(s) que est�o relacionados ao id_produto_acabado_custo passado por par�metro, que tenham 
CTT(s) atrelado(s) da 5� Etapa "TRATAMENTO T�RMICO" e 6� Etapa "USINAGEM" 
 
A 6� etapa n�o precisa de CTT porque o C�digo de Tratamento T�rmico, s� � utilizado na 5� Etapa ...*/
                $sql = "(SELECT CONCAT(pi.id_produto_insumo, '|', fpi.preco, '|', ppt.peso_aco) AS id_produto_insumo, CONCAT(u.sigla, ' - ', pi.discriminacao, ' / ', ctts.codigo) AS dados 
                        FROM `pacs_vs_pis_trat` ppt 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                        INNER JOIN `ctts` ON `ctts`.id_ctt = pi.`id_ctt` 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo') 
                        UNION 
                        (SELECT CONCAT(pi.id_produto_insumo, '|', fpi.preco, '|', ppu.qtde) AS id_produto_insumo, CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                        FROM `pacs_vs_pis_usis` ppu 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` 
                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo') ORDER BY id_produto_insumo ";
                echo combos::combo($sql, $cmb_ctt);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total de Sa�da:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total_saida' value="0,000" title='Digite o Peso Total de Sa�da' size='8' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_unit_saida()" class='caixadetexto'>
            &nbsp;
            <input type='text' name='rotulo1' class='caixadetexto2' style="color:black;font-weight:bold" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Pre�o Unit�rio R$:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' value="<?=$txt_preco_unitario;?>" title='Pre�o Unit�rio' size='8' class='textdisabled' disabled>
            <?
/***************************************************************************************************************/
//Se esse PI estiver com essa marca��o p/ esse PA da OS na 5� Etapa do Custo, ent�o eu exibo esses campos abaixo ...
                if($lote_minimo_fornecedor == 1) {
                    echo '<font color="brown"><b>(Lote M�nimo)</b></font>';
                    //Busco na Lista de Pre�os o Lote M�nimo em R$ do Fornecedor e do PI na Lista de Pre�os ...
                    $sql = "SELECT lote_minimo_reais 
                            FROM `fornecedores_x_prod_insumos` 
                            WHERE `id_fornecedor` = '$id_fornecedor' 
                            AND `id_produto_insumo` = '$id_produto_insumo_ctt' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_lista   = bancos::sql($sql);
            ?>
            -&nbsp;R$ <input type='text' name='txt_lote_minimo_custo_tt' value="<?=number_format($campos_lista[0]['lote_minimo_reais'], 2, ',', '.');?>" title='Lote M�nimo do Custo TT' size='8' class='textdisabled' disabled>
            &nbsp;-
            <?
                    //Esse controle serve p/ quando carregar a Tela ...
                    if(!isset($_POST['chkt_cobrar_lote_minimo']) || $_POST['chkt_cobrar_lote_minimo'] == 'S') $checked_lote_minimo = 'checked';
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
                    <b>Total Sa�da R$:</b>
            </td>
            <td>
                    <input type='text' name='txt_preco_total' value="0,00" title='Pre�o Total' size='8' class='textdisabled' disabled>
                    &nbsp;
                    <input type='checkbox' name='chkt_retrabalho' value='1' id='retrabalho' onclick='zerar_preco_unitario()' class='checkbox'>
                    <label for='retrabalho'>Retrabalho</label>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde Unit�ria de Sa�da:
        </td>
        <td>
        <?
            //Busca de um valor para fator custo para etapa 5
            $fator_custo5 = genericas::variavel(10);
            //A princ�pio busco esse Peso l� na Quinta Etapa ...
            $sql = "SELECT * 
                    FROM `pacs_vs_pis_trat` 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos_etapa5 = bancos::sql($sql);
            if(count($campos_etapa5) == 0) {//Se n�o encontrar ...
                $calculo_peso_aco = 0;
            }else {//Se encontrar o Peso
                $dados_pi   = custos::preco_custo_pi($campos_etapa5[0]['id_produto_insumo']);
                $preco_pi   = $dados_pi;
                //Ignora a multiplica��o pelo fator_tt
                if($campos_etapa5[0]['peso_aco_manual'] == 1) {
                    $calculo_peso_aco = $preco_pi * $campos_etapa5[0]['peso_aco'] * $fator_custo5;
                }else {
                    //$calculo_peso_aco = $campos_etapa5[0]['fator'] * $preco_pi * $campos_etapa5[0]['peso_aco'] * $fator_custo5;
                    $calculo_peso_aco = 0;
                }
            }
        ?>
            <input type='text' name="txt_peso_unit_saida" id="txt_peso_unit_saida" value="0,0000" size='7' class='textdisabled' disabled>
            &nbsp;-&nbsp;
            <input type='text' name="txt_peso_peca_corrigido" id="txt_peso_peca_corrigido" value="<?=number_format($calculo_peso_aco, 4, ',', '.');?>" size="7" class='disabled' disabled>
            &nbsp;
            <input type='text' name='rotulo2' class='caixadetexto2' style="color:black;font-weight:bold" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o de Retrabalho:
        </td>
        <td>
            <textarea name='txt_observacao_retrabalho' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php?id_os=<?=$id_os;?>'" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
        </td>
    </tr>
</table>
<!--Essas caixas servem para fazer controle de Tela-->
<input type='hidden' name='passo'>
<input type='hidden' name='opt_opcao' value='<?=$opt_opcao;?>'>
<input type='hidden' name='chkt_incluir_op_atrelada' value='<?=$chkt_incluir_op_atrelada;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='id_produto_insumo_ctt' value='<?=$id_produto_insumo_ctt;?>'>
<!--Essa caixa eu utilizo para fazer os c�lculos-->
<input type='hidden' name='peso_aco' value='<?=$peso_aco;?>'>
<!--Essas caixas eu utilizo poder gravar no BD-->
<input type='hidden' name='id_op' value="<?=$campos[0]['id_op'];?>">
<input type='hidden' name='id_os' value="<?=$id_os;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
</form>
</body>
</html>
<pre>
<b><font color="red">Observa��o:</font></b>
<pre>
<font color="darkblue">
* Quando o CTT s� estiver como SELECIONE, significa que n�o existe CTT algum atrelado para este PI ou ent�o 
n�o existe PI do Tipo "Trat" na 5� Etapa do P.A.

* O PI-Trat tem de estar atrelado ao fornecedor para poder incluir este PI 
para o fornecedor desta OS .
</font>
</pre>
<?
	}
}else if($passo == 2) {
//Aqui existe essa separa��o porque esse objeto grava 3 valores, um que � id e outro que � Pre�o
    $id_ctt = strtok($cmb_ctt, '|');
//Aqui eu verifico se j� existe essa OP com mesmo CTT nessa OS
    $sql = "SELECT id_os_item 
            FROM `oss_itens` 
            WHERE `id_os` = '$id_os' 
            AND `id_op` = '$id_op' 
            AND `id_produto_insumo_ctt` = '$id_ctt' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//OP n�o existente
//Aqui eu mudo o Status da OP para 1, p/ saber que j� tem pelo menos 1 item dela importado em alguma OS
        $sql = "UPDATE `ops` SET `status_import` = '1' WHERE `id_op` = '$id_op' LIMIT 1 ";
        bancos::sql($sql);
//Grava��o dos dados da OS
        $data_saida                     = date('Y-m-d');
        $cobrar_lote_minimo             = (!empty($_POST['chkt_cobrar_lote_minimo'])) ? 'S' : 'N';
        
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
        $id_produto_insumo_mat_prima    = (!empty($_POST[id_produto_insumo_mat_prima])) ? "'".$_POST[id_produto_insumo_mat_prima]."'" : 'NULL';
        
        $sql = "INSERT INTO `oss_itens` (`id_os_item`, `id_os`, `id_op`, `id_produto_insumo_mat_prima`, `id_produto_insumo_ctt`, `qtde_saida`, `data_saida`, `peso_total_saida`, `peso_unit_saida`, `preco_pi`, `obs_retrabalho`, `cobrar_lote_minimo`, `lote_minimo_custo_tt`, `retrabalho`) VALUES (NULL, '$id_os', '$id_op', $id_produto_insumo_mat_prima, '$id_ctt', '$txt_qtde_saida', '$data_saida', '$txt_peso_total_saida', '$_POST[txt_peso_unit_saida]', '$txt_preco_unitario', '$txt_observacao_retrabalho', '$cobrar_lote_minimo', '$_POST[txt_lote_minimo_custo_tt]', '$chkt_retrabalho') ";
        bancos::sql($sql);
/*******************************************************************************************************/
//Atualiza��o do Custo l� na Etapa 5
        $chkt_peso_aco_manual = 1;
        $sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '$_POST[txt_peso_unit_saida]', `peso_aco_manual` = '$chkt_peso_aco_manual' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' AND `id_produto_insumo` = '$id_ctt' LIMIT 1 ";
        bancos::sql($sql);
/*Roberto pediu p/ comentar isso na Data 28/11/2013 porque sen�o n�s perdemos o rastreamento de quem foi o �ltimo 
usu�rio que mexeu no Custo ...
//Atualiza��o do Funcion�rio que alterou os dados no custo
        $data_sys = date('Y-m-d H:i:s');
        $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        bancos::sql($sql);*/
/*******************************************************************************************************/
        $valor = 2;
    }else {//Item de OP j� existente
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?id_os=<?=$id_os;?>&valor=<?=$valor;?>'
        //window.opener.parent.itens.document.form.valor.value = 1
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar OP(s) p/ Incluir na OS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='id_os' value="<?=$id_os;?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar OP(s) p/ Incluir na OS
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name="txt_consultar" size="45" maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar OPs por: N�mero da OP" id='label' checked>
            <label for='label'>
                N�mero da OP
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_incluir_op_atrelada' value='1' title="Incluir OP(s) j� atrelada(s) a outra(s) OS(s)" id='label2' class="checkbox">
            <label for='label2'>
                Incluir OP(s) j� atrelada(s) a outra(s) OS(s)
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false; document.form.txt_consultar.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>