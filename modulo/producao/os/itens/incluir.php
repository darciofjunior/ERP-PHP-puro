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

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM(NS) DE OP COM MESMA CTT JÁ EXISTENTE.</font>";

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
//Busca de alguns dados da OS, vou precisar desses em algumas situações pouco mais pra baixo*/
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
em algumas situações pouco mais pra baixo*/
        $sql = "SELECT `id_produto_acabado_custo` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = ".$campos[0]['id_produto_acabado']." 
                AND `operacao_custo` = ".$campos[0]['operacao_custo']." LIMIT 1 ";
        $campos_custo = bancos::sql($sql);
        $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
//Busca a Marcação desse PI com o PA lá na 5ª Etapa do Custo p/ saber se este tem a Marcação de Lote Mínimo
        $sql = "SELECT `lote_minimo_fornecedor` 
                FROM `pacs_vs_pis_trat` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' 
                AND `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
        $campos_lote_minimo     = bancos::sql($sql);
        $lote_minimo_fornecedor = $campos_lote_minimo[0]['lote_minimo_fornecedor'];

        //Busca dos Produtos da OP agora através do id_op que está na OS
        $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia` 
                FROM `ops` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                WHERE ops.`id_op` = '".$campos[0]['id_op']."' ";
        $campos_pa = bancos::sql($sql);
        
        /*******************Baixas Manipulações*******************/
        //Verifico se já foi dada alguma Baixa de PI para esta OP que foi escolhida para ser vinculada a esta OS ...
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
//Quantidade de Saída
    if(!texto('form', 'txt_qtde_saida', '1', '1234567890', 'QUANTIDADE DE SAÍDA', '1')) {
        return false
    }
//Verificação de dados Inválidos na Quantidade de Saída
    if(document.form.txt_qtde_saida.value == 0) {
        alert('QUANTIDADE DE SAÍDA INVÁLIDA !')
        document.form.txt_qtde_saida.focus()
        document.form.txt_qtde_saida.select()
        return false
    }
//CTT
    if(!combo('form', 'cmb_ctt', '', 'SELECIONE UM CTT !')) {
        return false
    }
//Peso Total de Saída
    if(!texto('form', 'txt_peso_total_saida', '1', '1234567890,.', 'PESO TOTAL DE SAÍDA', '2')) {
        return false
    }
/*Se a Opção de Retrabalho não estiver marcada, então o Sistema força a ter um Preço <> de Zero,
agora caso esta opção esteje marcada, então eu ignoro o Preço Zero*/
    if(document.form.chkt_retrabalho.checked == false) {
//Verifico se o Preço Unitário do CTT é igual a Zero
        if(document.form.txt_preco_unitario.value == '0,00') {
            alert('CTT COM PREÇO UNITÁRIO INVÁLIDO !!!\nATUALIZE ESTE(S) NA LISTA DE PREÇO DESSE FORNECEDOR !')
            window.close()
            return false
        }
    }
//Se a Opção de Retrabalho estiver marcada, então o Sistema força a preencher a Observação de Retrabalho
    if(document.form.chkt_retrabalho.checked == true) {
//Forço o Preenchimento de Observação de Retrabalhado
        if(document.form.txt_observacao_retrabalho.value == '') {
            alert('DIGITE A OBSERVAÇÃO DE RETRABALHO !')
            document.form.txt_observacao_retrabalho.focus()
            return false
        }
    }
//Comparação entre os 2 pesos
    var peso_unitario_saida = eval(strtofloat(document.form.txt_peso_unit_saida.value))
    var peso_peca_corrigo = eval(strtofloat(document.form.txt_peso_peca_corrigido.value))

    if(((peso_unitario_saida / peso_peca_corrigo) > 1.01) || ((peso_peca_corrigo / peso_unitario_saida) > 1.01)) {
        var resposta = confirm('DIFERENÇA DE PESO UNITÁRIO SUPERIOR A 1% !\nDESEJA CONTINUAR ?')
        if(resposta == false) return false
    }
    /************************Em PAS 'ESP' existe segurança************************/
    //A condição é que a Qtde a ser Produzida pode variar no máximo de +- 10% da Qtde do Pedido
    var referencia = '<?=$campos_pa[0]['referencia'];?>'
    if(referencia == 'ESP') {
        var qtde_nominal 	= eval(strtofloat('<?=$campos[0]['qtde_produzir'];?>'))
        var qtde_saida 		= eval(strtofloat(document.form.txt_qtde_saida.value))
        if((0.9 * qtde_nominal > qtde_saida) || (qtde_saida > 1.1 * qtde_nominal)) {
            var resposta = confirm('QUANTIDADE NOMINAL INVÁLIDA !!!\n\nA QUANTIDADE DE SAÍDA É SUPERIOR OU INFERIOR A 10% DA QUANTIDADE NOMINAL, DESEJA CONTINUAR ?')
            if(resposta == false) {
                document.form.txt_qtde_saida.focus()
                document.form.txt_qtde_saida.select()
                return false
            }
        }
    }
    /*****************************************************************************/
//Nem sempre existe esse objeto, somente quando na 5ª Etapa do Custo foi marcada a opção de seguir o caminho de Lote Mínimo ...
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
        alert('ESSA OP NÃO TEM PI BAIXADO !!!\n\nACERTE A BAIXA DO PI !')
        window.close()
    }
}

function controlar_digitos(objeto) {
    if(objeto.value.length > 1) {//Se tiver pelo menos 2 dígitos ...
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
        }else {//Aqui é trodo tratamento antes do Pipe
            if(achou_pipe == 0) {//Parte do Id
                id_produto_insumo_ctt+= ctt.charAt(i)
            }else if(achou_pipe == 1) {//Parte do Preço Unitário
                preco_unitario+= ctt.charAt(i)
            }else if(achou_pipe == 2) {//Parte do Peso Aço
                peso_aco+= ctt.charAt(i)
            }
        }
    }
//Peso Aço
    document.form.peso_aco.value = peso_aco //Hidden
//Preço Unitário
    document.form.txt_preco_unitario.value = arred(preco_unitario, 2, 1)
    document.form.txt_preco_unitario.disabled = false
    document.form.id_produto_insumo_ctt.value = id_produto_insumo_ctt
    document.form.passo.value = 1
    document.form.submit()
}

function peso_total_saida() {
//Qtde de Saída
    var qtde_saida  = (document.form.txt_qtde_saida.value == '') ? 0 : eval(strtofloat(document.form.txt_qtde_saida.value))
//Peso Aço
    var peso_aco    = (document.form.peso_aco.value == '') ? 0 : eval(document.form.peso_aco.value)
//Peso Total de Saída Kg -> Qtde de Saída * Peso Aço
    var resultado   = String(qtde_saida * peso_aco)//Gambiarra (rsrs)
    document.form.txt_peso_total_saida.value = arred(resultado, 3, 1)
}

function calcular_preco_total() {
    var sigla                   = document.form.rotulo1.value
    var qtde_saida              = strtofloat(document.form.txt_qtde_saida.value)
    var peso_total_saida_kg     = strtofloat(document.form.txt_peso_total_saida.value)
    var preco_unitario          = strtofloat(document.form.txt_preco_unitario.value)
    var lote_minimo_custo_tt    = strtofloat(document.form.txt_lote_minimo_custo_tt.value)
    
    if(sigla == 'UN') {//Se a unidade do CTT = "Unidade", então utilizo o campo Qtde ... 
        var peso_qtde_total_utilizar = qtde_saida
    }else {//Se a unidade do CTT <> "Unidade", então utilizo o campo Peso Total  ... 
        var peso_qtde_total_utilizar = peso_total_saida_kg
    }
    
//Aki eu verifico se existe a marcação de Lote Mínimo p/ o Item ...
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
//Qtde de Saída
    if(document.form.txt_qtde_saida.value == '' || document.form.txt_qtde_saida.value == 0) {
        var qtde_saida = 1//Para não dar erro de Divisão por Zero
    }else {
        var qtde_saida = eval(strtofloat(document.form.txt_qtde_saida.value))
    }
//Peso Total de Saída em KG
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
            i = ctt.length//Faço assim para sair fora do laço
        }else {
            unidade+= ctt.charAt(i)
        }
    }
    if(unidade != 'SELECIONE') {
        //Atribuição da Unidade p/ esses PI(s) ...
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
//Se a Opção de Trabalho tiver marcada, então eu Zero o Preço Unitário, volto o Preço normal do CTT
    if(document.form.chkt_retrabalho.checked == true) {
        document.form.txt_preco_unitario.value = '0,00'
    }else {
        separar_preco()
    }
//Já utilizo as funções normais para recálculo
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
            OS N.º <font color='yellow'><?=$id_os;?></font> 
            - Fornecedor: <font color='yellow'><?=$razaosocial;?></font>
            - Data de Saída: <font color='yellow'><?=$data_saida;?></font>
            <br>Importar OP(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.º OP:</b>
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
                    <b>Matéria Prima:</b>
            </td>
            <td>
                    <?
                            //Verifica se Existe Matéria Prima na 2ª Etapa do Custo ...
                            $sql = "SELECT pi.id_produto_insumo, pi.discriminacao 
                                    FROM `produtos_acabados_custos` pac 
                                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pac.id_produto_insumo 
                                    WHERE pac.id_produto_acabado_custo = '$id_produto_acabado_custo' LIMIT 1 ";
                            $campos_materia_prima = bancos::sql($sql);
                            if(count($campos_materia_prima) == 0) {//Se não encontrar ...
                                echo '<font color="blue"><b>NÃO HÁ MATÉRIA PRIMA</b></font>';
                            }else {//Se encontrar o id_produto_insumo ...
                                $alert_divergencia = 0;//Valor Padrão ...
                                /*Aqui eu verifico se a última ação é "Baixa de PI" p/ o determinado PI que está na 
                                2ª Etapa do Custo e para a determinada OP consultada pelo usuário e que foi passado 
                                por parâmetro ...*/
                                $sql = "SELECT bp.status 
                                        FROM `produtos_insumos` pi 
                                        INNER JOIN `baixas_ops_vs_pis` bp ON bp.id_op = '".$campos[0]['id_op']."' AND bp.id_produto_insumo = pi.id_produto_insumo 
                                        WHERE pi.id_produto_insumo = '".$campos_materia_prima[0]['id_produto_insumo']."' ORDER BY bp.id_baixa_op_vs_pi DESC LIMIT 1 ";
                                $campos_baixa_op 	= bancos::sql($sql);
                                if(count($campos_baixa_op) == 0) {//Não foi dado Baixa ...
                                    $alert_divergencia = 1;
                                }else if(count($campos_baixa_op) == 1 && $campos_baixa_op[0]['status'] == 3) {
                                    $alert_divergencia = 1;//Já foi dado baixa, mas houve um Estorno ...
                                }
                                echo $campos_materia_prima[0]['discriminacao'];
                                if($alert_divergencia == 1) {
                    ?>
                                    <Script Language = 'JavaScript'>
                                        alert('ESTA OP ESTÁ SEM PI BAIXADO NA 2ª ETAPA !!!\n\nREQUISITE ACERTO DO ALMOXARIFADO P/ PODER CONTINUAR !')
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
            <b>Qtde de Saída:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_saida' value='0' title='Digite a Quantidade de Saída' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this);peso_total_saida();calcular_peso_unit_saida();calcular_preco_total()" size='12' class='caixadetexto'>
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
//Busca do Produto da OP agora através do id_op que está na OS
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
/*Aqui traz todos os PI(s) que estão relacionados ao id_produto_acabado_custo passado por parâmetro, que tenham 
CTT(s) atrelado(s) da 5ª Etapa "TRATAMENTO TÉRMICO" e 6ª Etapa "USINAGEM" 
 
A 6ª etapa não precisa de CTT porque o Código de Tratamento Térmico, só é utilizado na 5ª Etapa ...*/
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
            <b>Total de Saída:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total_saida' value="0,000" title='Digite o Peso Total de Saída' size='8' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_unit_saida()" class='caixadetexto'>
            &nbsp;
            <input type='text' name='rotulo1' class='caixadetexto2' style="color:black;font-weight:bold" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço Unitário R$:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' value="<?=$txt_preco_unitario;?>" title='Preço Unitário' size='8' class='textdisabled' disabled>
            <?
/***************************************************************************************************************/
//Se esse PI estiver com essa marcação p/ esse PA da OS na 5ª Etapa do Custo, então eu exibo esses campos abaixo ...
                if($lote_minimo_fornecedor == 1) {
                    echo '<font color="brown"><b>(Lote Mínimo)</b></font>';
                    //Busco na Lista de Preços o Lote Mínimo em R$ do Fornecedor e do PI na Lista de Preços ...
                    $sql = "SELECT lote_minimo_reais 
                            FROM `fornecedores_x_prod_insumos` 
                            WHERE `id_fornecedor` = '$id_fornecedor' 
                            AND `id_produto_insumo` = '$id_produto_insumo_ctt' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_lista   = bancos::sql($sql);
            ?>
            -&nbsp;R$ <input type='text' name='txt_lote_minimo_custo_tt' value="<?=number_format($campos_lista[0]['lote_minimo_reais'], 2, ',', '.');?>" title='Lote Mínimo do Custo TT' size='8' class='textdisabled' disabled>
            &nbsp;-
            <?
                    //Esse controle serve p/ quando carregar a Tela ...
                    if(!isset($_POST['chkt_cobrar_lote_minimo']) || $_POST['chkt_cobrar_lote_minimo'] == 'S') $checked_lote_minimo = 'checked';
            ?>
            <input type='checkbox' name='chkt_cobrar_lote_minimo' value='S' id='cobrar_lote_minimo' onclick='calcular_preco_total()' class='checkbox' <?=$checked_lote_minimo;?>>
            <label for='cobrar_lote_minimo'>Cobrar Lote Mínimo</label>
            <?
                }
/***************************************************************************************************************/
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Total Saída R$:</b>
            </td>
            <td>
                    <input type='text' name='txt_preco_total' value="0,00" title='Preço Total' size='8' class='textdisabled' disabled>
                    &nbsp;
                    <input type='checkbox' name='chkt_retrabalho' value='1' id='retrabalho' onclick='zerar_preco_unitario()' class='checkbox'>
                    <label for='retrabalho'>Retrabalho</label>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde Unitária de Saída:
        </td>
        <td>
        <?
            //Busca de um valor para fator custo para etapa 5
            $fator_custo5 = genericas::variavel(10);
            //A princípio busco esse Peso lá na Quinta Etapa ...
            $sql = "SELECT * 
                    FROM `pacs_vs_pis_trat` 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos_etapa5 = bancos::sql($sql);
            if(count($campos_etapa5) == 0) {//Se não encontrar ...
                $calculo_peso_aco = 0;
            }else {//Se encontrar o Peso
                $dados_pi   = custos::preco_custo_pi($campos_etapa5[0]['id_produto_insumo']);
                $preco_pi   = $dados_pi;
                //Ignora a multiplicação pelo fator_tt
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
            Observação de Retrabalho:
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
<!--Essa caixa eu utilizo para fazer os cálculos-->
<input type='hidden' name='peso_aco' value='<?=$peso_aco;?>'>
<!--Essas caixas eu utilizo poder gravar no BD-->
<input type='hidden' name='id_op' value="<?=$campos[0]['id_op'];?>">
<input type='hidden' name='id_os' value="<?=$id_os;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
<font color="darkblue">
* Quando o CTT só estiver como SELECIONE, significa que não existe CTT algum atrelado para este PI ou então 
não existe PI do Tipo "Trat" na 5ª Etapa do P.A.

* O PI-Trat tem de estar atrelado ao fornecedor para poder incluir este PI 
para o fornecedor desta OS .
</font>
</pre>
<?
	}
}else if($passo == 2) {
//Aqui existe essa separação porque esse objeto grava 3 valores, um que é id e outro que é Preço
    $id_ctt = strtok($cmb_ctt, '|');
//Aqui eu verifico se já existe essa OP com mesmo CTT nessa OS
    $sql = "SELECT id_os_item 
            FROM `oss_itens` 
            WHERE `id_os` = '$id_os' 
            AND `id_op` = '$id_op' 
            AND `id_produto_insumo_ctt` = '$id_ctt' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//OP não existente
//Aqui eu mudo o Status da OP para 1, p/ saber que já tem pelo menos 1 item dela importado em alguma OS
        $sql = "UPDATE `ops` SET `status_import` = '1' WHERE `id_op` = '$id_op' LIMIT 1 ";
        bancos::sql($sql);
//Gravação dos dados da OS
        $data_saida                     = date('Y-m-d');
        $cobrar_lote_minimo             = (!empty($_POST['chkt_cobrar_lote_minimo'])) ? 'S' : 'N';
        
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $id_produto_insumo_mat_prima    = (!empty($_POST[id_produto_insumo_mat_prima])) ? "'".$_POST[id_produto_insumo_mat_prima]."'" : 'NULL';
        
        $sql = "INSERT INTO `oss_itens` (`id_os_item`, `id_os`, `id_op`, `id_produto_insumo_mat_prima`, `id_produto_insumo_ctt`, `qtde_saida`, `data_saida`, `peso_total_saida`, `peso_unit_saida`, `preco_pi`, `obs_retrabalho`, `cobrar_lote_minimo`, `lote_minimo_custo_tt`, `retrabalho`) VALUES (NULL, '$id_os', '$id_op', $id_produto_insumo_mat_prima, '$id_ctt', '$txt_qtde_saida', '$data_saida', '$txt_peso_total_saida', '$_POST[txt_peso_unit_saida]', '$txt_preco_unitario', '$txt_observacao_retrabalho', '$cobrar_lote_minimo', '$_POST[txt_lote_minimo_custo_tt]', '$chkt_retrabalho') ";
        bancos::sql($sql);
/*******************************************************************************************************/
//Atualização do Custo lá na Etapa 5
        $chkt_peso_aco_manual = 1;
        $sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '$_POST[txt_peso_unit_saida]', `peso_aco_manual` = '$chkt_peso_aco_manual' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' AND `id_produto_insumo` = '$id_ctt' LIMIT 1 ";
        bancos::sql($sql);
/*Roberto pediu p/ comentar isso na Data 28/11/2013 porque senão nós perdemos o rastreamento de quem foi o último 
usuário que mexeu no Custo ...
//Atualização do Funcionário que alterou os dados no custo
        $data_sys = date('Y-m-d H:i:s');
        $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        bancos::sql($sql);*/
/*******************************************************************************************************/
        $valor = 2;
    }else {//Item de OP já existente
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
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar OPs por: Número da OP" id='label' checked>
            <label for='label'>
                Número da OP
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_incluir_op_atrelada' value='1' title="Incluir OP(s) já atrelada(s) a outra(s) OS(s)" id='label2' class="checkbox">
            <label for='label2'>
                Incluir OP(s) já atrelada(s) a outra(s) OS(s)
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