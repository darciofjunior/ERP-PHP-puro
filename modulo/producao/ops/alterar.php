<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up))  require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../lib/cascates.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OP ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>ENTRADA REGISTRADA COM SUCESSO.</font>";
$mensagem[4] = "<font class='confirmacao'>OP FINALIZADA COM SUCESSO.</font>";
$mensagem[5] = "<font class='confirmacao'>OP ABERTA COM SUCESSO.</font>";
$mensagem[6] = "<font class='erro'>ESSA OP N�O PODE SER FINALIZADA, � NECESS�RIO DAR BAIXA(S) NA MAT�RIA PRIMA PRIMEIRO.</font>";
$mensagem[7] = "<font class='erro'>OP ALTERADA COM SUCESSO, MAS N�O PODE SER FINALIZADA, � NECESS�RIO DAR BAIXA(S) NA MAT�RIA PRIMA PRIMEIRO.</font>";
$mensagem[8] = "<font class='erro'>EXISTE(M) ITEM(NS) DE OS(S) EM ABERTO P/ ESTA OP !!!<br/>FINALIZE ESTE(S) ITEM(NS) DE OS.</font>";
$mensagem[9] = "<font class='erro'>A QTDE DE ENTRADA ESTA COM DIFEREN�A ACIMA DE 2% DA QTDE DE SA�DA DA OS !!!<br/>AVISAR P/ ROBERTO LIBERAR.</font>";
$mensagem[10] = "<font class='erro'>PRODUTO ACABADO EST� BLOQUEADO.</font>";

/*A princ�pio, criei essa fun��o somente aqui, porque por enquanto, � o �nico local que 
chama a parte de Finalizar Status da OP ...*/
function finalizar_op($id_op, $id_produto_acabado) {
//Se o Usu�rio estiver finalizando a OP, ent�o fa�o essa verifica��o antes ...
    $pis_baixados   = 0;//Essa vari�vel ser� utilizada mais abaixo ...
/*Trago todos os PI(s) que est�o relacionados a esse P.A da OP da 2� Etapa c/ Comprimento + Corte > 0, 
3� e 7� Etapa em que o PI � diferente de Zero ...*/
    $sql = "(SELECT id_produto_insumo 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '0' 
            AND `id_produto_insumo` <> '0' AND (comprimento_1 + comprimento_2 > 0)) 
            UNION 
            (SELECT pp.id_produto_insumo 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN  `pacs_vs_pis` pp ON pp.id_produto_acabado_custo = pac.id_produto_acabado_custo 
            WHERE pac.`id_produto_acabado` = '$id_produto_acabado' 
            AND pac.`operacao_custo` = '0' 
            AND pp.`id_produto_insumo` <> '0') 
            UNION 
            (SELECT pac.id_produto_insumo 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `pacs_vs_pas` pp ON pp.id_produto_acabado_custo = pac.id_produto_acabado_custo 
            WHERE pac.`id_produto_acabado` = '$id_produto_acabado' 
            AND pac.`operacao_custo` = '0' 
            AND pac.`id_produto_insumo` <> '0') ";
    $campos_pis = bancos::sql($sql);
    $linhas_pis = count($campos_pis);
//Armazeno nesse Vetor todos o(s) PI(s) ...
    for($i = 0; $i < $linhas_pis; $i++) {
//Agora verifico se j� foi dada alguma Baixa p/ esse(s) PI(s) nessa respectiva OP ...
        $sql = "SELECT `id_baixa_op_vs_pi` 
                FROM `baixas_ops_vs_pis` 
                WHERE `id_produto_insumo` = '".$campos_pis[$i]['id_produto_insumo']."' 
                AND `id_op` = '$id_op' 
                AND `status` = '2' LIMIT 1 ";
        $campos_baixa = bancos::sql($sql);
        if(count($campos_baixa) == 1) $pis_baixados++;
    }

/*Se a Quantidade de PI(s) baixados for a mesma q a qtde PI(s) encontrados no Custo atrav�s 
do PA, ent�o, posso estar finalizando esta OP ...*/
    if($pis_baixados == $linhas_pis) {//Pode Finalizar a OP ...
        $valor = 3;
    }else {//Ainda n�o pode, deve existir alguma diverg�ncia ...
        $valor = 0;
    }
//Verifico se tenho pelo menos uma Entrada de item de OS p/ a respectiva OP ...
    $sql = "SELECT `id_os_item` 
            FROM `oss_itens` 
            WHERE `id_op` = '$id_op' 
            AND `qtde_entrada` > '0' LIMIT 1 ";
    $campos_status = bancos::sql($sql);
    if(count($campos_status) > 0) {//Existe(m) Entrada(s) de item de OS ...
        /*Verifico quais OS(s) est�o vinculadas a esta OP, desde que tenha a maior Qtde de Sa�da

        Exemplo: Se tivermos 2 OS(s) p/ esta OP, s� irei trazer uma ...*/
        $sql = "SELECT `qtde_saida` 
                FROM `oss_itens` 
                WHERE `id_op` = '$id_op' 
                ORDER BY `qtde_saida` DESC LIMIT 1 ";
        $campos_os_item = bancos::sql($sql);
        if($campos_os_item == 1) {//Se realmente existir uma OS p/ esta OP ...
            //Verifico se essa OP possui Entrada(s) Registrada(s), trago as suas N Entradas se existir ...
            $sql = "SELECT SUM(`qtde_baixa`) AS total_entradas 
                    FROM `baixas_ops_vs_pas` 
                    WHERE `id_op` = '$id_op' ";
            $campos_total_entradas = bancos::sql($sql);
            if($campos_total_entradas[0]['total_entradas'] >= (0.98 * $campos_os_item[0]['qtde_saida'])) {
                $valor = 3;//Pode Finalizar ...
            }else {
                $valor = 2;//Ainda n�o pode, deve existir alguma diverg�ncia ...
            }
        }else {//N�o existe nenhuma OS p/ esta OP, ent�o ...
            $valor = 3;//Pode Finalizar ...
        }
    }else {
        //Verifico se tenho uma Sa�da de item de OS p/ a respectiva OP ...
        $sql = "SELECT `id_os_item` 
                FROM `oss_itens` 
                WHERE `id_op` = '$id_op' 
                AND `qtde_saida` > '0' LIMIT 1 ";
        $campos_os_item = bancos::sql($sql);
        if(count($campos_os_item) == 1) $valor = 8;//N�o pode finalizar porque tem Sa�da de OS sem Entrada ...
    }
    return $valor;
}

if($passo == 1) {
/*************************************************************************************************/
//Aqui � a Inser��o das Qtdes de Entrada na(s) OP(s)
/*Eu passo a a��o = 2, porque � equivalente a Op��o do menu Estoque Acabado -> Manipular/Entrada, Entrada
do M�dulo de Vendas*/
    if($dar_entrada == 1) {//Objeto do Hidden
/*********************************************************************/
        $id_produto_acabado_utilizar    = (!empty($_POST['cmb_pa_substitutivo'])) ? $_POST['cmb_pa_substitutivo'] : $_POST['id_produto_acabado'];
        $resultado                      = estoque_acabado::verificar_manipulacao_estoque($id_produto_acabado_utilizar, $_POST['txt_qtde_entrada']);
        
        if($resultado['retorno'] == 'executar') {
            if(!empty($_POST['cmb_pa_substitutivo'])) {
                //Aqui eu busco a refer�ncia e a discriminacao do PA Substitutivo ...
                $_POST['txt_justificativa'].= ' '.$_POST[txt_qtde_entrada].' - ('.intermodular::pa_discriminacao($_POST['cmb_pa_substitutivo'], 0, 0, 0, 0, 1).')';
            }
            /**********************************************************************/
            /**************************Entrada Antecipada**************************/
            /**********************************************************************/
            //Se o usu�rio marcou o Tipo de Entrada na combo como sendo "Entrada Antecipada" antes de clicar no Bot�o "Dar Entrada" ent�o ...
            if($_POST['cmb_tipo_entrada'] == 'A') {
                //1) Concateno o texto abaixo junto da Observa��o / Justificativa ...
                $_POST['txt_justificativa'].= ' (Entrada Antecipada)';
            }
//Aki registra a Data e Hora em q foi feita a altera��o ...
            $data_sys = date('Y-m-d H:i:s');
//Tenho que chamar essa fun��o para Setar o P.A., para o Pa�oquinha saber que ele poder liberar os Pedidos...
            estoque_acabado::seta_nova_entrada_pa_op_compras($id_produto_acabado_utilizar);
//Procedimento normal para registro da Entrada ...
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `qtde`, `observacao`, `acao`, `data_sys`) VALUES (NULL, '$id_produto_acabado_utilizar', '$_SESSION[id_funcionario]', '$_POST[txt_qtde_entrada]', '$_POST[txt_justificativa]', 'E', '$data_sys') ";
            bancos::sql($sql);
            $id_baixa_manipulacao_pa = bancos::id_registro();
//************************Novo Controle com a Parte de OP(s)************************
            //Busco a Fam�lia do PA 1 "da OP" para fazer um tratamento mais abaixo ...
            $sql = "SELECT gpa.`id_familia` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            if($campos_pa[0]['id_familia'] == 9) {//Nesse caso espec�fico, o procedimento ser� um pouquinho diferenciado ...
                //Busco o pecas_por_jogo do PA 2 "que � o PA que estou dando entrada nessa OP" ...
                $sql = "SELECT `pecas_por_jogo` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado_utilizar' LIMIT 1 ";
                $campos_pa      = bancos::sql($sql);
                $qtde_entrada   = $_POST[txt_qtde_entrada] * $campos_pa[0]['pecas_por_jogo'];
            }else {
                $qtde_entrada   = $_POST[txt_qtde_entrada];
            }
            //Sempre ser� em cima do PA 1, na justificativa fa�o um adendo ao PA 2 ...
            $sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '$_POST[id_produto_acabado]', '$id_op', '$id_baixa_manipulacao_pa', '$qtde_entrada', '$_POST[txt_justificativa]', '$data_sys', '2') ";
            bancos::sql($sql);
            sleep(2);
            //Aqui eu chamo essa fun��o que Corrige o Estoque Real e o Estoque Faturado ...
            estoque_acabado::atualizar($id_produto_acabado_utilizar);
            //Aqui eu chamo essa fun��o que Corrige o Estoque Dispon�vel e Separado ...
            estoque_acabado::controle_estoque_pa($id_produto_acabado_utilizar);
            //Aqui eu atualizo o campo de Produ��o do Estoque
            estoque_acabado::atualizar_producao($_POST['id_produto_acabado']);//S� atualizo o PA1 porque o Registro foi gerado em cima do mesmo ...
            /**********************************************************************/
            /**************************Entrada Antecipada**************************/
            /**********************************************************************/
            //Se o usu�rio marcou o Tipo de Entrada como sendo "Antecipada" antes de clicar no Bot�o "Dar Entrada" ent�o ...
            if($_POST['cmb_tipo_entrada'] == 'A') {
                /*Comentado em 20/04/2018 ...
                2) Mudo o item da qual j� foi dado Entrada Antecipada p/ Racionado ...
                $sql = "UPDATE `estoques_acabados` SET `racionado` = '1' WHERE `id_produto_acabado` = '$id_produto_acabado_utilizar' LIMIT 1 ";
                bancos::sql($sql);*/
                
                //3) Como sendo a �ltima a��o do PA, atualizo o campo Entrada Antecipada do PA na tabela de "estoques_acabados" ...
                $sql = "UPDATE `estoques_acabados` SET `entrada_antecipada` = `entrada_antecipada` + $_POST[txt_qtde_entrada] WHERE `id_produto_acabado` = '$id_produto_acabado_utilizar' LIMIT 1 ";
                bancos::sql($sql);
            }
            $valor = 3;
        }else {
            $valor = 10;
        }
//Caso o usu�rio tenha pedido para Finalizar a OP, ent�o ...
        if(!empty($_POST['chkt_finalizar_op'])) {
//Se o Usu�rio estiver finalizando a OP, ent�o fa�o essa verifica��o antes ...
            $finalizar_op = finalizar_op($id_op, $id_produto_acabado_utilizar);
/*Significa que est� OP, n�o pode ser finalizada, ainda n�o foi dado baixa dos PI(s) 
Mat�ria(s) Prima(s) l� no Almoxarifado ...*/
            if($finalizar_op <= 2) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
            }else {//Pode Finalizar normalmente ...
                $sql = "UPDATE `ops` SET `status_finalizar` = '$_POST[chkt_finalizar_op]' WHERE `id_op` = '$id_op' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }
/*************************************************************************************************/
/*************************************************************************************************/
//Aqui � o Controle para Finalizar a OP
    if($_POST['hdd_finalizar_op'] == 1) {//Objeto do Hidden
        if($_POST['hdd_analisar_todas_segurancas'] == 'S') {//Procedimento normal p/ todos os Usu�rios ...
//Se o Usu�rio estiver finalizando a OP, ent�o fa�o essa verifica��o antes ...
            $finalizar_op = finalizar_op($id_op, $_POST['id_produto_acabado']);
/*Significa que est� OP, n�o pode ser finalizada, ainda n�o foi dado baixa dos PI(s) 
Mat�ria(s) Prima(s) l� no Almoxarifado ...*/
            if($finalizar_op == 0) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 6;
            }else if($finalizar_op == 1) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 8;
            }else if($finalizar_op == 2) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 9;
            }else if($finalizar_op == 8) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 8;
            }
        }else {//Somente p/ os usu�rios "Roberto" e "D�rcio" que pode cair nessa Situa��o ...
            $valor = 3;
        }
//Caso o usu�rio tenha pedido para Finalizar a OP, ent�o ...
        $sql = "UPDATE `ops` SET `status_finalizar` = '$_POST[chkt_finalizar_op]' WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
        bancos::sql($sql);
//Controle para o Retorno de Mensagens
        if(!empty($_POST['chkt_finalizar_op'])) {
            $valor = 4;
        }else {
/*Se essa vari�vel n�o estiver com o 

Valor = 6: "ESSA OP N�O PODE SER FINALIZADA, � NECESS�RIO DAR BAIXA(S) NA MAT�RIA PRIMA PRIMEIRO", 
Valor = 8: "EXISTE(M) ITEM(NS) DE OS(S) EM ABERTO P/ ESTA OP!!!<br/>FINALIZE ESTE(S) ITEM(NS) DE OS", 
Valor = 9: "A QTDE DE ENTRADA ESTA COM DIFEREN�A ACIMA DE 2% DA QTDE DE SA�DA DA OS !!!<br/>AVISAR P/ ROBERTO LIBERAR", 

ent�o significa que o usu�rio realmente est� tentando abrir a OP novamente ...*/
            if($valor != 6 && $valor != 8 && $valor != 9) $valor = 5;
        }
//Aqui, eu nada mais nada menos s� atualizo o Estoque ...
        estoque_acabado::atualizar($_POST['id_produto_acabado']);
//Aqui eu atualizo o campo de Produ��o do Estoque
        estoque_acabado::atualizar_producao($_POST['id_produto_acabado']);
    }
/*************************************************************************************************/
/*Se foi realizado algum dos procedimentos acima como 'dar_entrada' ou 'finalizar_op', ent�o o Sistema 
redireciona p/ a Tela principal retornando uma Mensagem ...*/
    if(($dar_entrada == 1 || $_POST['hdd_finalizar_op']) && !empty($valor)) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
        </Script>
<?
    }
/*************************************************************************************************/
    $sql = "SELECT ops.*, pa.`peso_unitario`, pa.`referencia`, pa.`operacao_custo`, 
            pa.`desenho_para_op`, u.`sigla` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            INNER JOIN `unidades` u on u.`id_unidade` = pa.`id_unidade` 
            WHERE ops.`id_op` = '$id_op' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
    $operacao_custo     = $campos[0]['operacao_custo'];
//Nesse SQL verifico o Total de Entrada(s) Registrada(s) p/ essa OP ...
    $sql = "SELECT COUNT(bmp.`id_baixa_manipulacao_pa`) AS entradas_registradas 
            FROM `baixas_manipulacoes_pas` bmp 
            INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$id_op' 
            WHERE bmp.`acao` = 'E' ";
    $campos_entradas = bancos::sql($sql);
    $entradas_registradas = $campos_entradas[0]['entradas_registradas'];
//Esse sql � um controle para auxiliar no JavaScript - aki eu fa�o o somat�rio do total de Entrada(s)
    $sql = "SELECT SUM(bmp.`qtde`) AS total_entradas 
            FROM `baixas_manipulacoes_pas` bmp 
            INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$id_op' 
            WHERE bmp.`acao` = 'E' ";
    $campos2 = bancos::sql($sql);
    if($campos2[0]['total_entradas'] == '') {//Se n�o existir nenhuma entrada ...
        $total_entradas = 0;//Igualo a vari�vel a zero para n�o retornar nenhum valor nulo ...
    }else {//Se a entrada existir ent�o igualo esta as entradas ...
        $total_entradas = $campos2[0]['total_entradas'];
    }
//Fa�o a busca do ED do Produto Acabado ...
    $estoque_produto    = estoque_acabado::qtde_estoque($id_produto_acabado);
    $qtde_disponivel    = $estoque_produto[3];
    if($qtde_disponivel == 0) {
        $qtde_disponivel_inicial = number_format(0, 2, ',', '.');
    }else {
        $qtde_disponivel_inicial = number_format($qtde_disponivel, 2, ',', '.');
    }
//Aqui eu busco a Qtde de Entrada da OS p/ apresentar mais abaixo na caixinha de texto ...
    $sql = "SELECT oi.`qtde_entrada` 
            FROM `oss_itens` oi 
            INNER JOIN `ops` ON ops.`id_op` = oi.`id_op` AND ops.`id_produto_acabado` = '$id_produto_acabado' 
            WHERE oi.`id_op` = '$id_op' ";
    $campos_os = bancos::sql($sql);
    if(count($campos_os) == 1) {//Se encontrar a Qtde de Entrada da OP ...
        $qtde_entrada = $campos_os[0]['qtde_entrada'];
/*Se n�o encontrar a Qtde de Entrada, ent�o eu zero essa vari�vel p/ que n�o d� problema com os 
c�lculos em JavaScript ...*/
    }else {
        $qtde_entrada = 0;
    }
?>
<html>
<title>.:: Alterar OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var total_entradas          = eval('<?=$total_entradas;?>')
    var referencia              = '<?=$campos[0]['referencia'];?>'
    var sigla                   = '<?=$campos[0]['sigla'];?>'
    var caracteres_aceitaveis   = (sigla == 'KG') ? '0123456789,.' : '0123456789'
    if(!texto('form', 'txt_qtde_produzir', '1', caracteres_aceitaveis, 'QUANTIDADE A PRODUZIR', '1')) {
        return false
    }
//Quantidade de Pe�as Cortadas 
    if(document.form.txt_qtde_pecas_cortadas.value != '') {
        if(!texto('form', 'txt_qtde_pecas_cortadas', '1', '0123456789', 'QUANTIDADE DE PE�AS CORTADAS', '1')) {
            return false
        }
    }
//Data de Emiss�o
    if(!data('form', 'txt_data_emissao', '4000', 'EMISS�O')) {
        return false
    }
//Incremento do Prazo de Entrega em Dias
    if(document.form.txt_incremento_prazo_entrega_dias.value != '') {
        if(!texto('form', 'txt_incremento_prazo_entrega_dias', '1', '0123456789-', 'INCREMENTO DO PRAZO DE ENTREGA EM DIAS', '2')) {
            return false
        }
    }
/************************L�gica para comparar Qtde de Pe�as por Corte***********************/
    if(referencia == 'ESP') {//S� pode fazer a compara��o se o Produto for do tipo ESP ...
        var resto_divisao = eval(document.form.txt_qtde_produzir.value) % (document.form.txt_pecas_corte.value)
        if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Qtde � est� Compat�vel
            alert('A QUANTIDADE � PRODUZIR N�O EST� COMPAT�VEL COM A QTDE DE P�S / CORTE !')
            document.form.txt_qtde_produzir.focus()
            document.form.txt_qtde_produzir.select()
            return false
        }
    }
//Quando o (Total de Entradas j� lan�adas + o valor da entrada atual) for > do que a qtde a produzir ...
    var qtde_entrada = eval(strtofloat(document.form.txt_qtde_entrada.value))
    if((total_entradas + qtde_entrada) > document.form.txt_qtde_produzir.value) {
        var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OP ?')
        if(pergunta == true) {
            document.form.chkt_finalizar_op.checked = true
            document.form.hdd_finalizar_op.value = 1//Hidden de Controle ...
            document.form.txt_justificativa.value = document.form.txt_justificativa.value + ' (OP FINALIZADA)'
        }
    }
    document.form.txt_qtde_produzir.disabled    = false
    document.form.txt_prazo_entrega.disabled    = false
    document.form.passo.value                   = 2
    limpeza_moeda('form', 'txt_qtde_produzir, ')
    document.form.submit()
}

function dividir_ops(id_op) {
    var status_finalizar    = eval('<?=$campos[0]['status_finalizar'];?>')
    var qtde_os             = eval('<?=count($campos_os);?>')
    
    if(status_finalizar == 1) {
        alert('ESTA OP N�O PODE SER DIVIDIDA PORQUE ESTA OP EST� FINALIZADA !')
    }else {
        if(qtde_os == 0) {
            html5Lightbox.showLightbox(7, 'dividir_ops.php?id_op='+id_op)
        }else {
            alert('ESTA OP N�O PODE SER DIVIDIDA PORQUE POSSUI OS(S) ATRELADA(S) !')
        }
    }
}

function verificar() {
    if(document.form.txt_incremento_prazo_entrega_dias.value == '') {
        //document.form.txt_prazo_entrega.value = '<?=data::datetodata($campos[0]['prazo_entrega'], '/');?>'
        document.form.txt_prazo_entrega.value = '<?=data::datetodata($campos[0]['prazo_entrega'], '/');?>'
    }else {
        var incremento_prazo_entrega_dias = eval(strtofloat(document.form.txt_incremento_prazo_entrega_dias.value))
        if(document.form.txt_incremento_prazo_entrega_dias.value != '') {
            //nova_data('<?=data::datetodata($campos[0]['prazo_entrega'], '/');?>', 'document.form.txt_prazo_entrega', incremento_prazo_entrega_dias)
            nova_data('<?=date('d/m/Y');?>', 'document.form.txt_prazo_entrega', incremento_prazo_entrega_dias)
        }
    }
}

function visualizar_pis() {
//Quantidade a Produzir
    var sigla = '<?=$campos[0]['sigla'];?>'
    var caracteres_aceitaveis = (sigla == 'KG') ? '0123456789,.' : '0123456789'
    if(!texto('form', 'txt_qtde_produzir', '1', caracteres_aceitaveis, 'QUANTIDADE A PRODUZIR', '1')) {
        return false
    }
    nova_janela('visualizar_pis.php?id_produto_acabado=<?=$id_produto_acabado;?>&nova_qtde_produzir='+document.form.txt_qtde_produzir.value+'&id_op=<?=$id_op;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')
}

function falta_baixar_componentes() {
    var resposta = confirm('FALTA DAR BAIXA(S) DE COMPONENTE(S) !!!\n\nDAR ENTRADA ASSIM MESMO ?')
    if(resposta == true) {
        dar_entradas()
    }else {
        return false
    }
}

function dar_entradas() {
/*Agora, caso o Peso Unit�rio do PA esteje zerado, ent�o eu preciso atualizar com o Peso 
correto antes de dar Entrada p/ n�o dar Erro na Nota Fiscal ...*/
    var peso_unitario = eval('<?=$campos[0]['peso_unitario'];?>')
    if(peso_unitario == 0) {
        alert('N�O EXISTE PESO UNIT�RIO P/ ESTE PRODUTO !!!\nCOLOQUE UM PESO UNIT�RIO P/ O MESMO ! ')
        document.getElementById('link_peso_unitario').focus()
        return false
    }
/******************************Procedimento Normal******************************/
//Quantidade de Entrada
    var sigla = '<?=$campos[0]['sigla'];?>'
    var caracteres_aceitaveis = (sigla == 'KG') ? '0123456789,.-' : '0123456789-'
    if(!texto('form', 'txt_qtde_entrada', '1', caracteres_aceitaveis, 'QUANTIDADE DE ENTRADA', '1')) {
        return false
    }
//Verifica��o de Entradas Inv�lidas
    if(document.form.txt_qtde_entrada.value == '-' || document.form.txt_qtde_entrada.value == '-0') {
        alert('QUANTIDADE DE ENTRADA INV�LIDA !')
        document.form.txt_qtde_entrada.focus()
        document.form.txt_qtde_entrada.select()
        return false
    }
//Tipo de Entrada ...
    if(!combo('form', 'cmb_tipo_entrada', '', 'SELECIONE O TIPO DE ENTRADA !')) {
        return false
    }
/***********************************************************************/
//Se a Qtde for Zero ...
    if(document.form.txt_qtde_entrada.value == 0) {
//Se a Justificativa estiver vazia, ent�o eu for�o o usu�rio a digitar uma Justificativa ...
        if(document.form.txt_justificativa.value == '') {
            alert('DIGITE UMA JUSTIFICATIVA REFERENTE A ESTE VALOR DE ENTRADA !')
            document.form.txt_justificativa.focus()
            return false
        }
/*Se a Justificativa tiver menos que 10 d�gitos, ent�o eu considero est� como 
sendo incompleta ...*/
        if(document.form.txt_justificativa.value.length < 10) {
            alert('JUSTIFICATIVA INCOMPLETA !')
            document.form.txt_justificativa.focus()
            return false
        }
        /**********************************************************************/
        /**************************Entrada Antecipada**************************/
        /**********************************************************************/
        /*Somente quando N�O estiver selecionado a op��o "Entrada Antecipada" na combo que sugiro de finalizar a OP, afinal se � Entrada Antecipada eu ainda
        terei que retornar pelo menos uma vez aqui nessa OP ...*/
        if(document.form.cmb_tipo_entrada.value != 'A') {
            //Tamb�m j� pergunto p/ o usu�rio, se ele deseja finalizar a OP ...
            var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OP ?')
            if(pergunta == true) {
                document.form.chkt_finalizar_op.checked = true
                document.form.hdd_finalizar_op.value = 1//Hidden de Controle ...
                document.form.txt_justificativa.value = document.form.txt_justificativa.value + ' (OP FINALIZADA)'
            }
        }
        /**********************************************************************/
    }
/***********************************************************************/
/******************************Compara��es******************************/
//1) Estoque Real Final nunca pode ser Negativo ...
    var qtde_disponivel_final  = eval(strtofloat(document.form.txt_qtde_disponivel_final.value))
    if(qtde_disponivel_final < 0) {
        alert('ESTOQUE DISPON�VEL FINAL N�O PODE SER NEGATIVO !')
        document.form.txt_qtde_entrada.focus()
        document.form.txt_qtde_entrada.select()
        return false
    }
/**************************************Somente quando existir OS**************************************/
//2) Dar Entrada com Qtde de Entrada ...
/*S� ir� fazer essa verifica��o quando existir esse campo -> "txt_qtde_entrada_ultima_os" 
que s� aparecer� quando existir uma OS*/
    if(typeof(document.form.txt_qtde_entrada_ultima_os) == 'object') {
        var qtde_entrada    = eval(document.form.txt_qtde_entrada.value)
        var qtde_entrada_ultima_os = eval(document.form.txt_qtde_entrada_ultima_os.value)
        //var porc_menor_um   = (qtde_entrada_ultima_os - (qtde_entrada_ultima_os * 0.01))
        var porc_maior_um   = (qtde_entrada_ultima_os + (qtde_entrada_ultima_os * 0.01))
//Comparando ...
        if(qtde_entrada > porc_maior_um) {//Qtde Maior q 1% ...
            alert('A QTDE DE ENTRADA � MAIOR DO QUE 1% DA QTDE DE ENTRADA DA �LTIMA OS !')
            document.form.txt_qtde_entrada.focus()
            document.form.txt_qtde_entrada.select()
            //return false
        }else {//Qtde Menor q 1% ...
            alert('A QTDE DE ENTRADA � MENOR DO QUE 1% DA QTDE DE ENTRADA DA �LTIMA OS !')
            document.form.txt_qtde_entrada.focus()
            document.form.txt_qtde_entrada.select()
            //return false
        }
    }
/***********************************************************************/
    var total_entradas = eval('<?=$total_entradas;?>')
//Quando o (Total de Entradas j� lan�adas + o valor da entrada atual) for > do que a qtde a produzir ...
    var qtde_entrada = eval(strtofloat(document.form.txt_qtde_entrada.value))
    if((total_entradas + qtde_entrada) >= (0.9 * document.form.txt_qtde_produzir.value)) {
        var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OP ?')
        if(pergunta == true) {
            document.form.chkt_finalizar_op.checked = true
            document.form.hdd_finalizar_op.value = 1//Hidden de Controle ...
            document.form.txt_justificativa.value = document.form.txt_justificativa.value + ' (OP FINALIZADA)'
        }
    }
/***********************************************************************/
//Desabilito o bot�o de Entrada, p/ q o usu�rio n�o submeta a(s) Entrada(s) mais de 1 vez ...
    document.form.cmd_dar_entrada.disabled = true
    document.form.dar_entrada.value = 1
    limpeza_moeda('form', 'txt_qtde_entrada, ')
    document.form.submit()
}

function desatrelar_pa() {
//PA Substitutivo ...
    if(!combo('form', 'cmb_pa_substitutivo', '', 'SELECIONE O P.A. SUBSTITUTIVO !')) {
        return false
    }
    var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE P.A. DO PA PRINCIPAL ?')
    if(resposta == true) {
        var id_pa_substitutivo = document.form.cmb_pa_substitutivo.value
        nova_janela('../../classes/produtos_acabados/desatrelar_pa.php?id_pa_a_ser_desatrelado='+id_pa_substitutivo+'&id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function controle_finalizar_op() {
//Enquanto o usu�rio n�o der entrada da Qtde de P�s da OP, o Sistema n�o permitir� finalizar a mesma ...
    if(document.form.txt_qtde_entrada.value != '') {
        alert('ESSA OP N�O PODE SER FINALIZADA ENQUANTO N�O DER ENTRADA !')
        document.form.chkt_finalizar_op.checked = false
        return false
    }
//Do contr�rio, segue o procedimento para finalizar a OP ...
    var status_finalizar = eval('<?=$campos[0]['status_finalizar'];?>')
    if(status_finalizar == 0) {//Signfica que eu estou finalizando a OP ...
        var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>')
        if(id_funcionario == 62 || id_funcionario == 98) {//Somente p/ o Roberto 62 "diretor" e D�rcio 98 "porque programa" ...
            var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OP ANALISANDO TODAS AS SEGURAN�AS ?')
            if(pergunta == false) document.form.hdd_analisar_todas_segurancas.value = 'N'
            document.form.txt_justificativa.value = document.form.txt_justificativa.value + ' (OP FINALIZADA)'
        }else {
            var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OP ?')
            if(pergunta == false) {
                document.form.chkt_finalizar_op.checked = false
                return false
            }else {
                document.form.txt_justificativa.value = document.form.txt_justificativa.value + ' (OP FINALIZADA)'
            }
        }
    }
    document.form.hdd_finalizar_op.value = 1
    limpeza_moeda('form', 'txt_qtde_entrada, ')
    document.form.submit()
}

function controlar_digitos(objeto) {
    if(objeto.value == '00' || objeto.value == '01' || objeto.value == '02') {
        objeto.value = objeto.value.substr(1, objeto.value.length)
    }else if(objeto.value == '03' || objeto.value == '04' || objeto.value == '05') {
        objeto.value = objeto.value.substr(1, objeto.value.length)
    }else if(objeto.value == '06' || objeto.value == '07') {
        objeto.value = objeto.value.substr(1, objeto.value.length)
    }else if(objeto.value == '08' || objeto.value == '09') {
        objeto.value = objeto.value.substr(1, objeto.value.length)
    }
}

function confirmar_alterar_discriminacao(mensagem, id_op, id_produto_acabado) {
    if(mensagem == 1) {
        var resposta = confirm('ESTE PA N�O PODE SER ALTERADO, DEVIDO ESSA OP EST� SENDO UTILIZADA POR ALGUMA OS !\n\nDESEJA CONTINUAR ?')
    }else if(mensagem == 2) {
        var resposta = confirm('ESTE PA N�O PODE SER ALTERADO, DEVIDO ESSA OP EST� SENDO UTILIZADA POR ALGUMA OS OU POSSUIR ALGUMA ENTRADA !\n\nDESEJA CONTINUAR ?')
    }
    if(resposta == true) alterar_discriminacao(id_op, id_produto_acabado)
}

function alterar_discriminacao(id_op, id_produto_acabado) {
    html5Lightbox.showLightbox(7, 'alterar_pa_op.php?id_op='+id_op+'&id_pa_substituir='+id_produto_acabado)
}

function calcular() {
    var qtde_entrada            = (document.form.txt_qtde_entrada.value == '') ? 0 : eval(strtofloat(document.form.txt_qtde_entrada.value))
    var qtde_disponivel_inicial = eval(strtofloat(document.form.txt_qtde_disponivel_inicial.value))
//Parte de Estoque
    if(typeof(qtde_entrada) == 'undefined') {
        document.form.txt_qtde_disponivel_inicial.value = '<?=$qtde_disponivel_inicial;?>'
    }else {
        document.form.txt_qtde_disponivel_final.value = qtde_entrada + qtde_disponivel_inicial
        document.form.txt_qtde_disponivel_final.value = arred(document.form.txt_qtde_disponivel_final.value, 2, 1)
    }
}

function detalhes_os(id_os, id_op) {
    html5Lightbox.showLightbox(7, '../os/itens/itens.php?pop_up=1&id_os='+id_os+'&id_op='+id_op)
}

function est_baixa_pi(id_produto_insumo, id_op) {
    html5Lightbox.showLightbox(7, 'dar_baixa_pi.php?id_produto_insumo='+id_produto_insumo+'&id_op='+id_op)
}

function est_baixa_pa(id_produto_acabado, id_op) {
    html5Lightbox.showLightbox(7, 'dar_baixa_pa.php?id_produto_acabado='+id_produto_acabado+'&id_op='+id_op)
}

function retornar_estoques_pa() {
    //Se n�o foi escolhido um PA Substitutivo na Combo, ent�o eu utilizo o PA Nominal da OP ...
    var id_produto_acabado_utilizar = (document.form.cmb_pa_substitutivo.value != '') ? document.form.cmb_pa_substitutivo.value : eval('<?=$id_produto_acabado;?>')
    iframe_retornar_estoques_pa.location = '../../classes/produtos_acabados/retornar_estoques_pa.php?id_produto_acabado='+id_produto_acabado_utilizar
    
    /*Dou esse tempinho de 0,4 segundo p/ chamar essa fun��o porque se leva um tempinho para atualizar o 
    Estoque Dispon�vel na Caixa Estoque Dispon�vel Inicial ...*/
    setTimeout('calcular()', 400)
}
</Script>
<body onload='calcular();document.form.txt_observacao.focus()'>
<form name='form' method='post'>
<input type='hidden' name='id_op' value='<?=$id_op;?>'>
<!--*********************Controles de Tela*********************-->
<!--Esse campo de id_produto_acabado eu guardo aki para facilitar a vida-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='dar_entrada'>
<input type='hidden' name='hdd_finalizar_op'>
<input type='hidden' name='hdd_analisar_todas_segurancas' value='S'>
<input type='hidden' name='passo' value='1'>
<!--***********************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar OP N.�
            <font color='yellow'>
                <?=$id_op;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>Produto: </font>
            <font size='-1'>
            <?
//S� exibe esse link p/ os seguintes usu�rios: Rivaldo "27", Roberto "62", D�rcio "98" porque programa e Rodrigo Bispo "125" ...
                $vetor_funcionarios_com_acesso_alterar_pa = array(27, 62, 98, 125);
            
                if(in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso_alterar_pa)) {
//Aki verifica se a OP est� sendo utilizado em lugares comprometedores ...
                    if(cascate::consultar('id_op', 'oss_itens', $id_op) == 1) {
                        $javascript = "confirmar_alterar_discriminacao(1, '$id_op', '$id_produto_acabado')";
                    }else {
//Busca do �ltimo Status de Baixa referente a OP caso exista ...
                        $sql = "SELECT `status` 
                                FROM `baixas_ops_vs_pis` 
                                WHERE `id_op` = '$id_op' ORDER BY `id_baixa_op_vs_pi` DESC LIMIT 1 ";
                        $campos_status_baixa_op = bancos::sql($sql);
/*Se a �ltima situa��o de PI = "baixa", ent�o significa que eu posso estar contabilizando 
essa OP no processo de confec��o, pois saiu PI(s) do Almoxarifado p/ a Produ��o de PA...*/
                        if($campos_status_baixa_op[0]['status'] == 2) {
                            $javascript = "confirmar_alterar_discriminacao(2, '$id_op', '$id_produto_acabado')";
                        }else {
                            $javascript = "alterar_discriminacao('$id_op', '$id_produto_acabado')";
                        }
                    }
            ?>
                <a href="javascript:<?=$javascript;?>" class='link'>
            <?
                }
                echo intermodular::pa_discriminacao($id_produto_acabado, 0);
            ?>
                </a>
            </font>
            &nbsp;
            <input type='button' name='cmd_substituir_estoque' value='Substituir Estoque' title='Substituir Estoque' onclick="nova_janela('../../classes/produtos_acabados/substituir_estoque_pa.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            &nbsp;
            <img src = '../../../imagem/menu/alterar.png' border='0' alt='Visualizar Custo Industrial' title='Visualizar Custo Industrial' onclick="html5Lightbox.showLightbox(7, '../custo/industrial/custo_industrial.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela=2&pop_up=1')">
            &nbsp;
            <?
                $url = '../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudan�a feita em 17/05/2016 - Antigamente os detalhes da consulta s� eram feitos pela 
                refer�ncia independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que s�o similares em seu cadastro na parte de refer�ncia, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica invi�vel 
                vindo todos os ESP�s do Sistema e trazendo informa��es que n�o tinham nada haver ...*/
                if($campos[0]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em espec�fico ...
                    $url.= '&id_produto_acabado='.$id_produto_acabado.'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Refer�ncia ...
                    $url.= '&txt_referencia='.$campos[0]['referencia'].'&pop_up=1';
                }
            ?>
            <img src = '../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipula��es' alt='Baixas / Manipula��es' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
            &nbsp;
            <img src = '../../../imagem/desbloquear.png' border='0' title='Desbloquear PAs' alt='Desbloquear PAs' width='20' height='20' onclick="html5Lightbox.showLightbox(7, '../programacao/desbloquear_pa/consultar.php?pop_up=1')">
            &nbsp;
            <img src = '../../../imagem/dividir.png' border='0' title='Dividir OP(s)' alt='Dividir OP(s)' width='20' height='20' onclick="dividir_ops('<?=$id_op;?>')">
        </td>
    </tr>
    <?
        if($campos[0]['status_finalizar'] == 1) {//A OP aqui j� est� finalizada
            $class_botao            = 'disabled';
            $class                  = 'textdisabled';
            $disabled               = 'disabled';
            $checked_finalizar_op   = 'checked';
        }else {
            $class_botao            = 'botao';
            $class                  = 'caixadetexto';
            $disabled               = '';
            $checked_finalizar_op   = '';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade a Produzir:</b>
        </td>
        <td>
            <?
                $onkeyup            = ($campos[0]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '', event);if(this.value == '0.00') {this.value = ''}" : "verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}";
                $casas_decimais     = ($campos[0]['sigla'] == 'KG') ? 2 : 0;
                $separador_milhares = ($campos[0]['sigla'] == 'KG') ? '.' : '';
            ?>
            <input type='text' name='txt_qtde_produzir' value='<?=number_format($campos[0]['qtde_produzir'], $casas_decimais, ',', $separador_milhares);?>' title='Digite a Quantidade � Produzir' maxlength='11' size='12' onkeyup="<?=$onkeyup;?>" class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;
            <input type='button' name='cmd_visualizar_pis' value="Visualizar PI's" title="Visualizar PI's" onclick='visualizar_pis()' class='botao'>
        </td>
        <td>
            Novo Prazo de Entrega: Hoje +
        </td>
        <td>
            <input type='text' name='txt_incremento_prazo_entrega_dias' title='Digite o Incremento do Prazo de Entrega em Dias' maxlength='6' size='7' onkeyup="verifica(this, 'moeda_especial', '0', '1', event);if(this.value == '-') {this.value = ''};verificar()" class='<?=$class;?>' <?=$disabled;?>> dias
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Quantidade de Pe�as Cortadas: 
        </td>
        <td>
            <input type='text' name='txt_qtde_pecas_cortadas' value='<?=$campos[0]['qtde_pecas_cortadas']?>' title='Digite a Quantidade de Pe�as Cortadas' maxlength='11' size='12' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            P�s / Corte: 
        </td>
        <td>
        <?
            //Busca a qtde de pe�as do PA que ser� gerado OP ...
            $sql = "SELECT `peca_corte` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
            $campos_pecas_corte = bancos::sql($sql);
            $pecas_corte = ($campos_pecas_corte[0]['peca_corte'] == 0) ? 1 : $campos_pecas_corte[0]['peca_corte'];
        ?>
            <input type='text' name='txt_pecas_corte' value='<?=$pecas_corte;?>' title='P�s / Corte' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emiss�o:
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=data::datetodata($campos[0]['data_emissao'], '/');?>' title='Digite a Data de Emiss�o' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_entrega' value='<?=data::datetodata($campos[0]['prazo_entrega'], '/');?>' title='Data do Prazo de Entrega' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Situa��o:
        </td>
        <td colspan='3'>
            <input type='text' name='txt_situacao' value='<?=$campos[0]['situacao'];?>' title='Digite a Situa��o' maxlength='30' size='33' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Observa��o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <textarea name='txt_observacao' title='Digite a Observa��o' cols='90' rows='2' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
        <?
//Significa que � uma tela normal, sendo assim pode exibir o bot�o de Voltar ...
            if(empty($pop_up)) {
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
        <?
            }
/*Significa que � uma tela normal e sendo assim ent�o exibo os Bot�es abaixo ou se aberta como Pop-Up 
somente o Roberto "62" ou Darcio "98" porque programa que podem salvar dados de OP ...*/
            if(empty($pop_up) || ($pop_up == 1 && ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98))) {
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular();document.form.txt_observacao.focus()" class='<?=$class_botao;?>' <?=$disabled;?>>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='return validar()' class='<?=$class_botao;?>' <?=$disabled;?>>
        <?
            }
        ?>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
<?
/************************************************OS************************************************/
//Verifico toda(s) as O.S(s) que est�o atrelada(s) a essa OP ...
    $sql = "SELECT f.`razaosocial`, oss.`observacao`, oi.`id_os`, oi.`qtde_saida`, oi.`qtde_entrada`, 
            DATE_FORMAT(oi.`data_saida`, '%d/%m/%Y') AS data_saida, 
            DATE_FORMAT(oi.`data_entrada`, '%d/%m/%Y') AS data_entrada, oi.`status` 
            FROM `oss_itens` oi 
            INNER JOIN `oss` ON oss.`id_os` = oi.`id_os` AND oss.`ativo` = '1' 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
            WHERE oi.`id_op` = '$id_op' 
            ORDER BY oi.`id_os_item` ";
    $campos_os  = bancos::sql($sql);
    $linhas     = count($campos_os);
    if($linhas > 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            O.S. Atrelada(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.� OS
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Qtde de Sa�da
        </td>
        <td>
            Data de Sa�da
        </td>
        <td>
            Qtde de Entrada
        </td>
        <td>
            Data de Entrada
        </td>
        <td>
            Status do Item
        </td>
    </tr>
<?
//Disparo do Loop ...
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td onclick="detalhes_os('<?=$campos_os[$i]['id_os'];?>', '<?=$id_op;?>')" title='Detalhes de O.S.' alt='Detalhes de O.S.' width='10'>
            <a href="#" title='Detalhes de O.S.' alt='Detalhes de O.S.'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="detalhes_os('<?=$campos_os[$i]['id_os'];?>', '<?=$id_op;?>')" title='Detalhes de O.S.' alt='Detalhes de O.S.'>
            <a href="#" title='Detalhes de O.S.' alt='Detalhes de O.S.' class='link'>
                <?=$campos_os[$i]['id_os'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos_os[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if($campos_os[$i]['qtde_saida'] > 0) echo $campos_os[$i]['qtde_saida'];
        ?>
        </td>
        <td>
        <?
            if($campos_os[$i]['data_saida'] != '00/00/0000') echo $campos_os[$i]['data_saida'];
        ?>
        </td>
        <td>
        <?
            if($campos_os[$i]['qtde_entrada'] > 0) echo $campos_os[$i]['qtde_entrada'];
        ?>
        </td>
        <td>
        <?
            if($campos_os[$i]['data_entrada'] != '00/00/0000') echo $campos_os[$i]['data_entrada'];
        ?>
        </td>
        <td>
        <?
            if($campos_os[$i]['qtde_saida'] > 0) {
                if($campos_os[$i]['status'] == 2) echo 'EM PEDIDO';
            }else {
                if($campos_os[$i]['status'] == 2) echo 'EM NF';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
/**************************************************************************************************/
/***********************************PI(s) Baixado(s) para esta OP**********************************/
//Retorna toda(s) as Baixas de PI(s) - Mat�ria Prima desta OP ...
    $sql = "SELECT bop.`id_produto_insumo`, bop.`qtde_baixa`, bop.`observacao`, 
            SUBSTRING(DATE_FORMAT(bop.`data_sys`, '%d/%m/%Y'), 1, 10) AS data, 
            SUBSTRING(bop.`data_sys`, 12, 8) AS hora, bop.`status`, pi.`discriminacao` 
            FROM `baixas_ops_vs_pis` bop 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = bop.`id_produto_insumo` 
            WHERE bop.`id_op` = '$id_op' 
            ORDER BY bop.`data_sys` DESC ";
    $campos_pis_baixados = bancos::sql($sql);
    $linhas = count($campos_pis_baixados);
    if($linhas > 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr><td></td></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            PI(s) Baixado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Qtde
        </td>
        <td>
            Observa��o
        </td>
        <td>
            Data e Hora
        </td>
        <td>
            Status
        </td>
    </tr>
<?
//Disparo do Loop ...
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos_pis_baixados[$i]['discriminacao'];?>
        </td>
        <td>
            <?=number_format($campos_pis_baixados[$i]['qtde_baixa'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos_pis_baixados[$i]['observacao'];?>
        </td>
        <td>
            <?=$campos_pis_baixados[$i]['data'].' - '.$campos_pis_baixados[$i]['hora'];?>
        </td>
        <td>
        <?
            if($campos_pis_baixados[$i]['status'] == 0) {
                echo '<font color="red"><b>ABERTO</b></font>';
            }else if($campos_pis_baixados[$i]['status'] == 1) {
                echo '<font color="darkblue"><b>BAIXA PARCIAL</b></font>';
            }else if($campos_pis_baixados[$i]['status'] == 2) {
                echo '<font color="darkgreen"><b>BAIXA TOTAL (CONCLU�DO)</b></font>';
/*Significa que � uma tela normal e sendo assim ent�o exibo o �cone abaixo ou se aberta como Pop-Up 
somente o Roberto "62" ou Darcio "98" porque programa que podem estornar essa Baixa ...*/
                if(empty($pop_up) || ($pop_up == 1 && ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98))) {
        ?>
            <img src = '../../../imagem/estornar.jpeg' title='Estornar Baixa do PI no Estoque' alt='Estornar Baixa do PI no Estoque' onclick="est_baixa_pi('<?=$campos_pis_baixados[$i]['id_produto_insumo'];?>', '<?=$id_op;?>')" style='cursor:help' border='0'>
        <?
                }
            }else if($campos_pis_baixados[$i]['status'] == 3) {
                echo '<font color="#ff9900"><b>BAIXA ESTORNADA</b></font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
/**************************************************************************************************/
/***********************************PA(s) Baixado(s) para esta OP**********************************/
//Retorna toda(s) as Baixas e Estorno(s) de Baixas de PA(s) - Mat�ria Prima desta OP ...
    $sql = "SELECT bop.`id_produto_acabado`, bop.`qtde_baixa`, bop.`observacao`, 
            SUBSTRING(DATE_FORMAT(bop.`data_sys`, '%d/%m/%Y'), 1, 10) AS data, 
            SUBSTRING(bop.`data_sys`, 12, 8) AS hora, bop.`status` 
            FROM `baixas_ops_vs_pas` bop 
            INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` AND bmp.`acao` IN ('B', 'S') 
            WHERE bop.`id_op` = '$id_op' ORDER BY bop.`data_sys` DESC ";
    $campos_pas_baixados = bancos::sql($sql);
    $linhas = count($campos_pas_baixados);
    if($linhas > 0) {
?>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr><td></td></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            PA(s) / Componente(s) Baixado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Qtde
        </td>
        <td>
            Observa��o
        </td>
        <td>
            Data e Hora
        </td>
        <td>
            Status
        </td>
    </tr>
<?
//Disparo do Loop ...
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos_pas_baixados[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=number_format($campos_pas_baixados[$i]['qtde_baixa'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos_pas_baixados[$i]['observacao'];?>
        </td>
        <td>
            <?=$campos_pas_baixados[$i]['data'].' - '.$campos_pas_baixados[$i]['hora'];?>
        </td>
        <td>
        <?
            if($campos_pas_baixados[$i]['status'] == 0) {
                echo '<font color="red"><b>ABERTO</b></font>';
            }else if($campos_pas_baixados[$i]['status'] == 1) {
                echo '<font color="darkblue"><b>BAIXA PARCIAL</b></font>';
            }else if($campos_pas_baixados[$i]['status'] == 2) {
                echo '<font color="darkgreen"><b>BAIXA TOTAL (CONCLU�DO)</b></font>';
/*Significa que � uma tela normal e sendo assim ent�o exibo o �cone abaixo ou se aberta como Pop-Up 
somente o Roberto "62" ou Darcio "98" porque programa que podem estornar essa Baixa ...*/
                if(empty($pop_up) || ($pop_up == 1 && ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98))) {
        ?>
            <img src = '../../../imagem/estornar.jpeg' title='Estornar Baixa do PA no Estoque' alt='Estornar Baixa do PA no Estoque' onclick="est_baixa_pa('<?=$campos_pas_baixados[$i]['id_produto_acabado'];?>', '<?=$id_op;?>')" style='cursor:help' border='0'>
        <?
                }
            }else if($campos_pas_baixados[$i]['status'] == 3) {
                echo '<font color="#ff9900"><b>BAIXA ESTORNADA</b></font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
/**************************************************************************************************/
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr></tr>
    <tr class='linhadestaque' align='center'>
    <td colspan='4'>
        Registrar Entrada(s)
    </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dar Entrada
                <font color='red'>
                    / Peso Unit�rio
                </font>
            </b>
        </td>
<?
/*Se existir O.S. ent�o significa que essa O.P. teve um servi�o fora da Empresa e da� 
eu apresento o campo de Qtde de Entrada da �ltima OS ...*/
    if($linhas > 0) {
?>
        <td>
            <b>Qtde de Entrada da �ltima OS:</b>
        </td>
<?
    }else {
        $colspan = 'colspan="2"';
    }
?>
        <td>
            Estoque Dispon�vel Inicial: <input type='text' name='txt_qtde_disponivel_inicial' value='<?=$qtde_disponivel_inicial;?>' title='Estoque Dispon�vel Inicial' size='20' class='caixadetexto2' disabled>
        </td>
        <td <?=$colspan;?>>
            Estoque Dispon�vel Final: <input type='text' name='txt_qtde_disponivel_final' title='Estoque Dispon�vel Final' size='20' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            //Esse campo de Entrada tem que permitir que sejam lan�ados valores negativos ...
            $onkeyup_qtde_entrada = ($campos[0]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '1', event);" : "verifica(this, 'moeda_especial', '0', '1', event);";
            $onkeyup_qtde_entrada.= "if(this.value == '0.00') {this.value = ''};this.value = this.value.replace('.', '');";
        ?>
            <input type='text' name='txt_qtde_entrada' title='Digite a Qtde de Entrada' onkeyup="<?=$onkeyup_qtde_entrada;?>;calcular()" maxlength="11" size="12" class='<?=$class;?>' <?=$disabled;?>> <b>/ </b>
            <a href="javascript:nova_janela('../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '');document.form.passo.value=1" title="Atualizar Peso do Produto" id="link_peso_unitario" class='link'>
                <font color='#6473D4'>
                    <?=number_format($campos[0]['peso_unitario'], 4, ',', '.');?>
                </font>
            </a>
        </td>
        <td colspan='3'>
        <?
/*Se existir O.S. ent�o significa que essa O.P. teve um servi�o fora da Empresa e da� 
eu apresento o campo de Qtde de Entrada da �ltima OS ...*/
            if($linhas > 0) {
        ?>
            <input type='text' name='txt_qtde_entrada_ultima_os' value='<?=$qtde_entrada;?>' title='Qtde de Entrada da �ltima OS' maxlength='11' size='12' class='textdisabled' disabled>&nbsp;
        <?
            }
        ?>
            <b>Tipo de Entrada:</b>
            <select name='cmb_tipo_entrada' title='Selecione o Tipo de Entrada' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='A'>ANTECIPADA</option>
                <option value='N'>NORMAL</option>
            </select>
            &nbsp;
            <img src = '../../../imagem/estornar_entrada_antecipada.png' border='0' title='Retorno de Entrada Antecipada' alt='Retorno de Entrada Antecipada' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '../../../modulo/vendas/estoque_acabado/retorno_entrada_antecipada.php?id_produto_acabado=<?=$id_produto_acabado;?>')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td colspan='2'>
            <font color='darkgreen'>
                <b>PA(s) Substitutivo(s):</b>
            </font>
            <select name='cmb_pa_substitutivo' title='Selecione o P.A. Substitutivo' onchange='retornar_estoques_pa()' class='combo'>
            <?
                //Aqui eu listo todos os PA(s) Padr�es que j� foram substitu�dos com o PA Principal ...
                $sql = "SELECT 
                        IF(ps.`id_produto_acabado_1` = '$id_produto_acabado', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_pa 
                        FROM `pas_substituires` ps 
                        WHERE 
                        (ps.`id_produto_acabado_1` = '$id_produto_acabado') 
                        OR (ps.`id_produto_acabado_2` = '$id_produto_acabado') ";
                $campos_pas_substituicao = bancos::sql($sql);
                $linhas_pas_substituicao = count($campos_pas_substituicao);
                if($linhas_pas_substituicao > 0) {//Encontrou pelo menos 1 PA Substituto ...
                    for($i = 0; $i < $linhas_pas_substituicao; $i++) $id_pas_substitutos.= $campos_pas_substituicao[$i]['id_pa'].', ';
                    $id_pas_substitutos = substr($id_pas_substitutos, 0, strlen($id_pas_substitutos) - 2);
                }
                //Se mesmo assim n�o veio nenhum PA Substituto, trato a vari�vel abaixo p/ n�o furar o SQL abaixo ...
                if(empty($id_pas_substitutos)) $id_pas_substitutos = 0;
//Trago todos os PA(s) que est�o atrelados na tab. relacional, + o outro selecionado pelo usu�rio no consultar P.A.
                $sql = "SELECT `id_produto_acabado`, CONCAT(`referencia`, ' * ', `discriminacao`) AS dados 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` IN ($id_pas_substitutos) ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_atrelar_pa' value='Atrelar PA' title='Atrelar PA' onclick="nova_janela('../../classes/produtos_acabados/atrelar_pa.php?id_pa_a_ser_atrelado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            &nbsp;
            <input type='button' name='cmd_desatrelar_pa' value='Desatrelar PA' title='Desatrelar PA' onclick='desatrelar_pa()' class='botao'>
        </td>
        <td>
            <input type='checkbox' name='chkt_finalizar_op' value='1' title='Finalizar OP' id='finalizar' onclick='controle_finalizar_op()' class='checkbox' <?=$checked_finalizar_op;?>>
            <label for='finalizar'>Finalizar OP</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>ATEN��O:</b>
            <marquee>
                O ESTOQUE REAL FINAL N�O PODE SER MENOR DO QUE O TOTAL SEPARADO !! GERENCIE O ESTOQUE DO ITEM, SE NECESS�RIO !!!
            </marquee>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Justificativa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <textarea name='txt_justificativa' title='Digite a Justificativa' cols='90' rows='2' maxlength='255' class='<?=$class;?>' <?=$disabled;?>></textarea>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
        <?
/*Aqui eu verifico a quantidade desse item em Estoque e j� trago o status do Estoque para saber se este 
pode ser manipulado pelo Estoquista ...*/
            $vetor          = estoque_acabado::qtde_estoque($id_produto_acabado, '1');
            $status_estoque = $vetor[1];
            $racionado      = $vetor[5];
//$status_estoque => para saber se o estoquista esta manpulando o  produto 0-free  1-locked
//$status_estoque_item => � para saber se o item poder ser manipulado ou liberado para manipular 0-free 1-lock
            if($status_estoque == 0 && $racionado == 0) {
                if($status_estoque_item == 0) {
                    $botao_submit = 1;//Quer dizer q pode mostrar o bot�o de submit
                    echo '<font color="blue"><b>PRODUTO LIBERADO PARA USO !</b></font>';
                }else {
                    $botao_submit = 1;//Quer dizer q pode mostrar o bot�o de submit
                    echo '<font color="red"><b>PRODUTO BLOQUEADO !!! ESTE PRODUTO J� FOI MANIPULADO PELO ESTOQUISTA !</b></font>';
                }
            }else if($status_estoque == 1) {//tive q retirara a clausula racionado deste if
                $botao_submit = 0;//Quer dizer q pode n�o mostrar o bot�o de submit
                echo '<font color="red"><b>PRODUTO BLOQUEADO !!! EST� SENDO MANIPULADO PELO ESTOQUISTA !</b></font>';
            }else {
                $botao_submit = 1;//Quer dizer q pode mostrar o bot�o de submit
                echo '<font color="red"><b>PRODUTO RACIONADO !</b></font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
        <?
            $sql = "SELECT gpa.`id_familia` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_familia = bancos::sql($sql);
            if($campos_familia[0]['id_familia'] == 23) {//Se for fam�lia componente ...
                //Se o funcion�rio for Rivaldo '27', M�rcio Dion�sio '95' ou Bispo = '125' o bot�o estar� OK ...
                $id_funcionarios_com_permissao = array(27, 95, 125);
            }else {//Se n�o for fam�lia componente e Funcion�rios Rivaldo '27', Agueda '32' ou Sueli '141' o bot�o estar� OK ...
                $id_funcionarios_com_permissao = array(27, 32, 141);
            }
            if(in_array($_SESSION['id_funcionario'], $id_funcionarios_com_permissao)) $dar_entrada = 1;
            /*Pode mostrar este bot�o apenas p/ Roberto e D�rcio e p/ os funcion�rios que se enquadram 
            em alguma das situa��es acima por enquanto ...*/
            if($botao_submit == 1 && ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $dar_entrada == 1)) {
                $op_montagem = 0;//A princ�pio n�o � uma OP de Montagem ...
                /************************Itens do Custo vinculados ao PA da OP************************/
                //Busca do id_produto_acabado_custo com o id_produto_acabado e operacao_custo do PA da OP passado por par�metro ...
                $sql = "SELECT `id_produto_acabado_custo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
                $campos_custo               = bancos::sql($sql);
                $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];

                //Aqui traz todos os PI(s) que est�o relacionado ao id_produto_acabado da OP - 3� Etapa ...
                $sql = "SELECT `qtde` 
                        FROM `pacs_vs_pis` 
                        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
                $campos_etapa3 = bancos::sql($sql);
                $linhas_etapa3 = count($campos_etapa3);

                //Aqui traz todos os PA(s) que est�o relacionado ao id_produto_acabado da OP - 7� Etapa ...
                $sql = "SELECT `qtde` 
                        FROM `pacs_vs_pas` 
                        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
                $campos_etapa7 = bancos::sql($sql);
                $linhas_etapa7 = count($campos_etapa7);
                if($linhas_etapa3 > 0 || $linhas_etapa7 > 0) {
                    $op_montagem = 1;//Significa que � uma OP de Montagem ...
                    //Verifico se existe Baixa de PI p/ a determinada OP ...
                    $sql = "SELECT id_baixa_op_vs_pi 
                            FROM `baixas_ops_vs_pis` 
                            WHERE `id_op` = '$id_op' LIMIT 1 ";
                    $campos_baixa_pi = bancos::sql($sql);
                    $linhas_baixa_pi = count($campos_baixa_pi);
                    //Verifico se existe Baixa de PA p/ a determinada OP ...
                    $sql = "SELECT id_baixa_op_vs_pa 
                            FROM `baixas_ops_vs_pas` 
                            WHERE `id_op` = '$id_op' LIMIT 1 ";
                    $campos_baixa_pa = bancos::sql($sql);
                    $linhas_baixa_pa = count($campos_baixa_pa);
                }
                /*************************************************************************************/
                /*Se � uma OP de Montagem e n�o existe Baixa nem de PI / PA, n�o � poss�vel dar entrada de OP, 
                a n�o ser que o usu�rio seja o "Rivaldo" 27 e o pr�prio "Roberto" 62 que � o diretor ... */
                if($op_montagem == 1 && $linhas_baixa_pi == 0 && $linhas_baixa_pa == 0 && ($_SESSION['id_funcionario'] != 27 && $_SESSION['id_funcionario'] != 62)) {
                    echo '<font color="yellow" size="3">N�o � poss�vel dar entrada pois � necess�rio dar baixa nos componentes !!! <br>Passar p/ o estoquista verificar.</font>';
                }else {
                    /*Apesar de o pr�prio "Roberto" 62 que � o diretor ter a flexibilidade de dar Entrada, este 
                    tem uma confirma��o especial para dar andamento em outra fun��o ...*/
                    if($op_montagem == 1 && $linhas_baixa_pi == 0 && $linhas_baixa_pa == 0 && $_SESSION['id_funcionario'] == 62) {
                        $function = 'falta_baixar_componentes()';
                    }else {
                        $function = 'dar_entradas()';
                    }
                    
                    /*Funcion�rios que podem enxergar o bot�o "Dar Entrada" mesmo quando essa tela for aberta como sendo Pop-UP ...
                    "27" Rivaldo, "32" Agueda, "62" Roberto e "98" D�rcio porque programa ...*/
                    $id_funcionarios = array(27, 32, 62, 98);

                    if(empty($pop_up) || ($pop_up == 1 && in_array($_SESSION['id_funcionario'], $id_funcionarios))) {
        ?>
            <input type='button' name='cmd_dar_entrada' value='Dar Entrada' title='Dar Entrada' onclick='<?=$function;?>' class='<?=$class_botao;?>' <?=$disabled;?>>
        <?
                    }
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
</table>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr><td></td></tr>
    <tr class='iframe' onclick="showHide('entradas_registradas'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                &nbsp;Entrada(s) Registrada(s):
            </font>
<?
//Nesse SQL verifico o Total de Entrada(s) Registrada(s) p/ essa OP ...
                $sql = "SELECT COUNT(bmp.`id_baixa_manipulacao_pa`) AS qtde_total_entrada 
                        FROM `baixas_manipulacoes_pas` bmp 
                        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$_GET[id_op]' 
                        WHERE bmp.`acao` = 'E' ";
                $campos_total_entrada = bancos::sql($sql);
?>
            <font color='#FFFFFF' size='2'>
                <?=$campos_total_entrada[0]['qtde_total_entrada'];?>
            </font>
            <span id='statusqtde_debito'>&nbsp;</span>
            <span id='statusqtde_debito'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Eu passo a origem por par�metro tamb�m para n�o dar erro de URL na parte de detalhes da conta e de cheque-->
            <iframe src="/erp/albafer/modulo/producao/ops/entradas_registradas.php?id_produto_acabado=<?=$id_produto_acabado;?>&id_op=<?=$id_op;?>&nao_chamar_biblioteca=1" name='entradas_registradas' id='entradas_registradas' marginwidth="0" marginheight="0" style='display: none' frameborder='0' height='160' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
</form>
<iframe name='iframe_retornar_estoques_pa' id='iframe_retornar_estoques_pa' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='yes' title='Retornar Estoques PA' width='0' height='0'></iframe>
</body>
</html>
<?
//Aqui nessa parte eu chamo a fun��o referente ao Visualizar Pedidos ...
    $nao_chamar_biblioteca = 1;
    if($campos[0]['referencia'] == 'ESP') require('../../classes/estoque/visualizar_pedidos.php');
}else if($passo == 2) {
    $data_ocorrencia    = date('Y-m-d H:i:s');
    $prazo_entrega      = data::datatodate($_POST['txt_prazo_entrega'], '-');
//Se o Usu�rio estiver finalizando a OP, ent�o fa�o essa verifica��o antes ...
    if($_POST['chkt_finalizar_op'] == 1) {
        if($_POST['hdd_analisar_todas_segurancas'] == 'S') {//Procedimento normal p/ todos os Usu�rios ...
            $finalizar_op = finalizar_op($_POST['id_op'], $_POST['id_produto_acabado']);

            if($finalizar_op == 0) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 7;
            }else if($finalizar_op == 1) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 8;
            }else if($finalizar_op == 2) {//N�o pode finalizar ...
                $_POST['chkt_finalizar_op'] = 0;
                $valor = 9;
            }else {
                $valor = 2;
            }
        }else {//Somente p/ os usu�rios "Roberto" e "D�rcio" que pode cair nessa Situa��o ...
            $valor = 2;
        }
    }else {//Significa que o usu�rio s� est� salvando os dados ...
        $valor = 2;
    }
//Atualizando os dados de OP ...
    $sql = "UPDATE `ops` SET `qtde_produzir` = '$_POST[txt_qtde_produzir]', `qtde_pecas_cortadas` = '$_POST[txt_qtde_pecas_cortadas]', `prazo_entrega` = '$prazo_entrega', `situacao` = '$_POST[txt_situacao]', `data_ocorrencia` = '$data_ocorrencia', `observacao` = '$_POST[txt_observacao]', `status_finalizar` = '$_POST[chkt_finalizar_op]' WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
    bancos::sql($sql);
//Aqui eu atualizo o campo de Produ��o do Estoque ...
    estoque_acabado::atualizar_producao($_POST['id_produto_acabado']);
    
    if($pop_up == 1) {
?>
    <Script Language= 'Javascript'>
        window.location = 'alterar.php?passo=1&id_op=<?=$_POST[id_op];?>&pop_up=<?=$pop_up;?>&valor=<?=$valor;?>'
    </Script>
<?
    }else {
?>
    <Script Language= 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>&pop_up=<?=$pop_up;?>'
    </Script>
<?
    }
}else {
    //Aqui eu puxo o �nico Filtro de OP(s) que serve para toda parte de OP(s) ...
    require('tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Alterar OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function imprimir() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }else {
        alert('/******************************REFER�NCIA ESP******************************/\n\nANTES DE IMPRIMIR N�O SE ESQUE�A DE MUDAR O PAPEL PARA \n\n\nA   M   A   R   E   L   O !')
        nova_janela('relatorio/relatorio.php', 'IMPRIMIR', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='relatorio/relatorio.php' onsubmit='return imprimir()' target='IMPRIMIR'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='15'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            Alterar OP(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Imprimir
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td rowspan='2' colspan='2'>
            N.� OP
        </td>
        <td rowspan='2'>
            Refer�ncia
        </td>
        <td rowspan='2'>
            Discrimina��o
        </td>
        <td colspan='4'>
            Qtde
        </td>
        <td rowspan='2'>
            Data de Emiss�o
        </td>
        <td rowspan='2'>
            Prazo de Entrega
        </td>
        <td rowspan='2'>
            Situa��o
        </td>
        <td rowspan='2'>
            Observa��o
        </td>
        <td rowspan='2'>
            <font title='Funcion�rio e Data da Ocorr�ncia' style='cursor:help'>
                Func e Data Ocorr
            </font>
        </td>
        <td rowspan='2'>
            P�o. Unit
        </td>
        <td rowspan='2'>
            Vlr. Total
        </td>
    </tr>
    <tr align='center'>
        <td class='linhadestaque'>
            Nominal
        </td>
        <td class='linhadestaque'>
            �ltima O.S. Atrelada
        </td>
        <td class='linhadestaque'>
            Entrada
        </td>
        <td class='linhadestaque'>
            Restante
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "alterar.php?passo=1&id_op=".$campos[$i]['id_op'].'&pop_up='.$pop_up;
            estoque_acabado::atualizar_producao($campos[$i]['id_produto_acabado']);
            
            $vetor_dados_op = intermodular::dados_op($campos[$i]['id_op']);
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_op[]' id='chkt_op<?=$i;?>' value="<?=$campos[$i]['id_op'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <a href='#' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'" align='center'>
            <a href='#' class='link'>
                <?=$campos[$i]['id_op'].$vetor_dados_op['posicao_op'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
        <?
            echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
            if($campos[$i]['`desenho_para_op`'] == '') {//N�o existe desenho no Produto Acabado ...
        ?>
                &nbsp;<img src='../../../imagem/folha_em_branco.png' width='12' height='12' border='0' title='N�o Existe Desenho no Produto Acabado'>
        <?
            }else {//J� consta desenho anexado
        ?>
                &nbsp;<img src='../../../imagem/folha_preenchida.png' width='12' height='12' border='0' title='Existe Desenho no Produto Acabado'>
        <?
            }
        ?>
        &nbsp;<img src='../../../imagem/impressora.gif' title='Imprimir OP' alt='Imprimir OP' border='0' onclick="document.getElementById('chkt_op<?=$i;?>').click();document.form.cmd_imprimir.click()" style='cursor:pointer'>
        <?
            if($campos[$i]['lote_diferente_custo'] == 'S') {//N�o existe desenho anexado ...
        ?>
        &nbsp;<img src="../../../imagem/ponto_interrogacao_vermelho.png" width="16" height="16" border="0" title="Lote Dif. > 15% do Custo" alt="Lote Dif. > 15% do Custo" style='cursor:pointer'>	
        <?		
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_produzir'], 2, ',', '.');?>
        </td>
        <td>
        <?
            //Busco a �ltima Qtde que foi Importada dessa OP do Loop em OS ...
            $sql = "SELECT IF(`qtde_entrada` = 0, `qtde_saida`, `qtde_entrada`) AS qtde_na_unidade, 
                    IF(`peso_total_entrada` = 0, `peso_total_saida`, `peso_total_entrada`) AS qtde_em_kilos 
                    FROM `oss_itens` 
                    WHERE `id_op` = '".$campos[$i]['id_op']."' ORDER BY `id_os_item` DESC LIMIT 1 ";
            $campos_os = bancos::sql($sql);
            if($campos[$i]['sigla'] == 'KG') {
                if(count($campos_os) == 1) $qtde_de_saida_na_os = $campos_os[0]['qtde_em_kilos'];
            }else {
                if(count($campos_os) == 1) $qtde_de_saida_na_os = $campos_os[0]['qtde_na_unidade'];
            }
            echo number_format($qtde_de_saida_na_os, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            /*Aqui eu busco o somat�rio de todas as Entradas que foram dadas para a OP do Loop, 
            ou seja de tudo que foi Produzido para aquela OP ...*/
            $sql = "SELECT SUM(bop.`qtde_baixa`) AS qtde_entrada 
                    FROM `baixas_manipulacoes_pas` bmp 
                    INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '".$campos[$i]['id_op']."' 
                    WHERE bmp.`acao` = 'E' ";
            $campos_entrada = bancos::sql($sql);
            if($campos_entrada[0]['qtde_entrada'] > 0) echo number_format($campos_entrada[0]['qtde_entrada'], 2, ',', '.');
        ?>    
        </td>
        <td>
        <?
            /*Essa tela � a �nica diferenciada em comparada a todo o sistema porque esta leva 
            em considera��o a Qtde Efetiva Produzida que est� na parte de OS(s) ...*/
            if($campos_entrada[0]['qtde_entrada'] > 0) {//Se existir alguma Entrada, faz as f�rmulas abaixo ...
                if($qtde_de_saida_na_os > 0) {
                    $qtde_restante = $qtde_de_saida_na_os - $campos_entrada[0]['qtde_entrada'];
                }else {
                    $qtde_restante = $campos[$i]['qtde_produzir'] - $campos_entrada[0]['qtde_entrada'];
                }
                $font = ($qtde_restante < 0) ? '<font color="red"><b>' : '';
                echo $font.number_format($qtde_restante, 2, ',', '.');
            }else {
                if($qtde_de_saida_na_os > 0) {//Se existir alguma OS ...
                    $qtde_restante = $qtde_de_saida_na_os;
                    echo number_format($qtde_restante, 2, ',', '.');
                }else {//Se n�o exibo a pr�pria Quantidade da OP ...
                    $qtde_restante = $campos[$i]['qtde_produzir'];
                    echo number_format($qtde_restante, 2, ',', '.');
                }
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['situacao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
        <?
//Busca do Nome do Funcion�rio e da Data de Ocorr�ncia de altera��o da �ltima OP ...
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_ocorrencia']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            if(count($campos_funcionario) == 1) {
//Aqui eu s� listo o primeiro nome ...
                echo strtok($campos_funcionario[0]['nome'], ' ').' - '.data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/').' - '.substr($campos[$i]['data_ocorrencia'], 11, 8);
            }
        ?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT ged.`desc_medio_pa`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_preco_unit  = bancos::sql($sql);
            $preco_lista        = ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_list_desc'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_list_desc'];
            echo segurancas::number_format($preco_lista, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($qtde_restante > 0 && $preco_lista > 0) echo number_format($qtde_restante * $preco_lista, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
            /*No fim de cada Loop, eu sempre zero essa vari�vel -> $qtde_de_saida_na_os p/ n�o transportar 
            o valor desta p/ os pr�ximos Loops ...*/
            unset($qtde_de_saida_na_os);
        }
        /****************************************************************************************/
        /*S� ir� fazer esse procedimento se essa op��o "$chkt_ops_aberto" estiver marcada, do contr�rio o sistema ir� fazer um processamento
        desnecess�rio em cima de OP(s) j� fechadas o que n�o precisa ...*/
        if(!empty($chkt_ops_aberto)) {
            //Aqui eu fa�o o mesmo SQL s� que dessa vez sem paginar ...
            $campos = bancos::sql($sql_todos_itens);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) {
                /*Aqui eu busco o somat�rio de todas as Entradas que foram dadas para a OP do Loop, 
                ou seja de tudo que foi Produzido para aquela OP ...*/
                $sql = "SELECT SUM(bop.`qtde_baixa`) AS qtde_produzido 
                        FROM `ops` 
                        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_op` = ops.`id_op` AND bop.`id_produto_acabado` = ops.`id_produto_acabado` 
                        INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` AND bmp.`acao` = 'E' 
                        WHERE ops.`status_finalizar` = '0' 
                        AND ops.`id_op` = '".$campos[$i]['id_op']."' ";
                $campos_produzido 	= bancos::sql($sql);
                $qtde_restante      = $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];

                $sql = "SELECT ged.`desc_medio_pa`, (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.desc_base_b_nac / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                $campos_preco_unit 	= bancos::sql($sql);
                $preco_lista 	= ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_list_desc'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_list_desc'];

                if($qtde_restante > 0 && $preco_lista > 0) $valor_total_rs+= $qtde_restante * $preco_lista;
            }
            /****************************************************************************************/
        }
?>
    <tr class='linhacabecalho' align='center'>
        <?
            $colspan = (!empty($chkt_ops_aberto)) ? 12 : 16;
        ?>
        <td colspan='<?=$colspan;?>'>
<?
            //Essa tela pode ser requirida atrav�s de outro arquivo e por isso fa�o essa seguran�a ...
            if(empty($pop_up)) {
?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
<?
            }
?>
            <input type='submit' name='cmd_imprimir' value='Imprimir OP' title='Imprimir OP' style='color:purple' class='botao'>
        </td>
<?
        //S� ir� exibir esse r�tulo de "Valor Total R$" se essa op��o "$chkt_ops_aberto" estiver marcada ...
        if(!empty($chkt_ops_aberto)) {
?>
        <td colspan='3' align='right'>
            Valor Total R$ <?=number_format($valor_total_rs, 2, ',', '.');?>
        </td>
<?
        }
?>
    </tr>
</table>
<!--************Controle de Tela************-->
<input type='hidden' name='hdd_atualizar_alterar' value='S'>
<input type='hidden' name='hdd_arquivo_que_chamou_impressao' value='<?=basename($_SERVER['PHP_SELF']);?>'>
<!--****************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
}
?>