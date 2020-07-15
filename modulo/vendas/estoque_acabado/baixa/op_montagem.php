<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/estoque_new.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DADO BAIXA COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_op 		= $_POST['txt_numero_op'];
        $txt_referencia 	= $_POST['txt_referencia'];
        $txt_discriminacao 	= $_POST['txt_discriminacao'];
    }else {
        $txt_numero_op 		= $_GET['txt_numero_op'];
        $txt_referencia 	= $_GET['txt_referencia'];
        $txt_discriminacao 	= $_GET['txt_discriminacao'];
    }
	
    //Mostro todas as OP(s) independente de estarem abertas ou concluídas ...
    $sql = "SELECT ops.`id_op` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            WHERE ops.`id_op` LIKE '$txt_numero_op%' 
            AND ops.`ativo` = '1' ORDER BY ops.`id_op` DESC ";
    $campos_ops = bancos::sql($sql);
    $linhas_ops = count($campos_ops);
    //Aqui eu verifico a se OP que foi filtrada pelo Usuário, teve alguma baixa anteriormente ...
    for($i = 0; $i < $linhas_ops; $i++) $id_ops.= $campos_ops[$i]['id_op'].', ';
    $id_ops = (!isset($id_ops)) ? 0 : substr($id_ops, 0, strlen($id_ops) - 2);
    
    //Aqui eu só listo as OPs que realmente não tiveram nenhuma baixa ...
    $sql = "SELECT ops.*, pa.`referencia` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            WHERE ops.`id_op` IN ($id_ops) 
            AND ops.`ativo` = '1' ORDER BY ops.`id_op` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'op_montagem.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Dar Baixa em OP de Montagem ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>       
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Dar Baixa em OP de Montagem
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OP
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde a Produzir
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Prazo de Entrega
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = "op_montagem.php?passo=2&id_op=".$campos[$i]['id_op'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['id_op'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_produzir'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'op_montagem.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $sql = "SELECT ops.`qtde_produzir`, ops.`status_finalizar`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao_custo`, pa.`observacao`, u.`sigla` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE ops.`id_op` = '$_GET[id_op]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $qtde_produzir      = $campos[0]['qtde_produzir'];
    $status_finalizar	= $campos[0]['status_finalizar'];
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
    $referencia         = $campos[0]['referencia'];
    $discriminacao      = $campos[0]['discriminacao'];
    $operacao_custo 	= $campos[0]['operacao_custo'];
    $observacao_produto = $campos[0]['observacao'];
    $sigla              = $campos[0]['sigla'];
//Aki faz Controle no Rótulo
    $rotulo_oc          = ($operacao_custo == 1) ? 'Revenda' : 'Industrial';
/*Aqui eu verifico a quantidade desse item em Estoque e já trago o status do Estoque para saber se este
pode ser manipulado pelo Estoquista*/
    $vetor              = estoque_acabado::qtde_estoque($id_produto_acabado, 1);
    $qtde_estoque_real	= number_format($vetor[0], 2, ',', '.');
?>
<html>
<title>.:: Dar Baixa em OP de Montagem ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function consultar_funcionario() {
    html5Lightbox.showLightbox(7, 'consultar_funcionario.php')
}

function igualar() {
    if(document.form.txt_solicitado_por.value == '') {
        document.form.chkt_mesmo.checked = false
        alert('CONSULTE UM FUNCIONÁRIO !')
        document.form.cmd_consultar_funcionario.onclick()
        return false
    }
    document.form.txt_retirado_por.value = (document.form.chkt_mesmo.checked == true) ? document.form.txt_solicitado_por.value : ''
}

function calcular_necessidade_atual() {
    var elementos       = document.form.elements
    var qtde_produzir   = strtofloat(document.form.txt_qtde_produzir.value)
    var linhas          = (typeof(elementos['hdd_papi_por_pecas[]'][0]) == 'undefined') ? 1 : elementos['hdd_papi_por_pecas[]'].length

    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_necessidade_atual'+i).value = document.getElementById('hdd_papi_por_pecas'+i).value * qtde_produzir
        document.getElementById('txt_necessidade_atual'+i).value = arred(document.getElementById('txt_necessidade_atual'+i).value, 2, 1)
        quantidade_calculada(i)
    }
}

//Nessa função eu faço um somatório de todas as Qtdes dadas em OP (Baixas) ...
function quantidade_calculada(indice) {
    var necessidade_atual   = eval(strtofloat(document.getElementById('txt_necessidade_atual'+indice).value))
    var qtde_estoque        = eval(strtofloat(document.getElementById('txt_qtde_estoque'+indice).value))
    
    if(document.getElementById('chkt_dar_baixa'+indice).checked == true) {//Se a linha estiver marcada, então desconto a qtde_produzir ...
        document.getElementById('txt_nova_qtde_estoque'+indice).value = qtde_estoque - necessidade_atual
        
        if(document.getElementById('txt_nova_qtde_estoque'+indice).value < 0) {
            document.getElementById('txt_nova_qtde_estoque'+indice).style.background    = 'red'
            document.getElementById('txt_nova_qtde_estoque'+indice).style.color         = 'white'
        }else {
            document.getElementById('txt_nova_qtde_estoque'+indice).style.background    = '#FFFFE1'
            document.getElementById('txt_nova_qtde_estoque'+indice).style.color         = 'gray'
        }
        document.getElementById('txt_nova_qtde_estoque'+indice).value = arred(document.getElementById('txt_nova_qtde_estoque'+indice).value, 2, 1)
    }else {
        document.getElementById('txt_nova_qtde_estoque'+indice).value = ''
        document.getElementById('txt_nova_qtde_estoque'+indice).style.background    = '#FFFFE1'
        document.getElementById('txt_nova_qtde_estoque'+indice).style.color         = 'gray'
    }
}

function validar() {
//Variável que será utilizada mais abaixo ...
    var elementos = document.form.elements
//Solicitado por ...
    if(document.form.txt_solicitado_por.value == '') {
        alert('CONSULTE UM FUNCIONÁRIO !')
        document.form.cmd_consultar_funcionario.onclick()
        return false
    }
//Retirado por ...
    if(!texto('form', 'txt_retirado_por', '1', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'RETIRADO POR', '2')) {
        return false
    }
    
    //Coloquei esse campo "txt_necessidade_atual[]" porque é um dos que sempre está em evidência independente de estar trabalho com PI ou PA ...
    var linhas = (typeof(elementos['txt_necessidade_atual[]'][0]) == 'undefined') ? 1 : elementos['txt_necessidade_atual[]'].length
    
    if(linhas > 0) {
        //Verifico se a Nova Quantidade em Estoque ficou negativa para algum Item ...
        for(i = 0; i < linhas; i++) {
            //Somente quando o Produto for Diferente "Mão de Obra" é que o Sistema não irá fazer a verificação abaixo ...
            if(document.getElementById('hdd_mao_obra'+i).value == 'NAO') {
                if(document.getElementById('chkt_dar_baixa'+i).checked == true) {//Apenas se o checkbox estiver marcado ...
                    if(strtofloat(document.getElementById('txt_nova_qtde_estoque'+i).value) < 0) {
                        alert('QUANTIDADE SOLICITADA INVÁLIDA !!!\n\nQUANTIDADE SOLICITADA MAIOR DO QUE A QUANTIDADE DISPONÍVEL EM ESTOQUE !')
                        document.getElementById('chkt_dar_baixa'+i).focus()
                        return false
                    }
                }
            }
        }
    }
/************************************************************************************/
    if(linhas > 0) {
        for(i = 0; i < linhas; i++) {
            //Preparo os campos p/ gravar no BD ...
            document.getElementById('txt_necessidade_atual'+i).value    = strtofloat(document.getElementById('txt_necessidade_atual'+i).value)
            document.getElementById('txt_nova_qtde_estoque'+i).value    = strtofloat(document.getElementById('txt_nova_qtde_estoque'+i).value)
            //Desabilito esses campos ...
            document.getElementById('txt_necessidade_atual'+i).disabled = false
            document.getElementById('txt_nova_qtde_estoque'+i).disabled = false
        }
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled = true
}
</Script>
</head>
<body onload='document.form.txt_retirado_por.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_op' value='<?=$_GET['id_op'];?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='erro' align='center'>
        <td colspan='2'>
            <?if($status_finalizar == 1) echo '"ESTA OP JÁ FOI FINALIZADA !!!"';?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dar Baixa em OP de Montagem
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font size='2'>
                <b>OP N.º </b>
            </font>
            <a href = '../../../producao/ops/alterar.php?passo=1&id_op=<?=$_GET['id_op'];?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                <?=$_GET['id_op'];?>
            </a>
            &nbsp;-&nbsp;
            <font size='2'>
                <b>Produto: 
            </font>
            </b><?=$referencia.' - '.$discriminacao;?>
            &nbsp;-&nbsp;
            <font size='2'>
                <b>O.C: </b>
            </font>
            <?=$rotulo_oc;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>Solicitado por</b>
        </td>
        <td width='40%'>
            <b>Retirado por</b>
            <?$checado_mesmo = ($chkt_mesmo == 1) ? 'checked' : '';?>
            <input type='checkbox' name='chkt_mesmo' value='1' onclick='igualar()' id='label2' <?=$checado_mesmo;?> class='checkbox'>
            <label for='label2'> O MESMO</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_solicitado_por' title='Digite o nome da pessoa que fez a Solicitação' size='35' class='textdisabled' disabled>
            <!--*****Esse id é guardado na Tabela de Baixa*****-->
            <input type='hidden' name='hdd_funcionario_solicitador'>
            &nbsp;
            <input type='button' name='cmd_consultar_funcionario' value='Consultar Funcionário' title='Consultar Funcionário' onclick='consultar_funcionario()' style='color:red' class='botao'>
        </td>
        <td>
            <input type='text' name='txt_retirado_por' value='<?=$txt_retirado_por;?>' title='Digite o nome da pessoa que esta retirando' size='55' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Qtde à Produzir
            &nbsp;
            <input type='text' name='txt_qtde_produzir' value='<?=number_format($qtde_produzir, 2, ',', '.');?>' title='Quantidade à Produzir' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
    </tr>
</table>
<?
/************************Itens do Custo vinculados ao PA da OP************************/
    //Aqui traz todos os PI(s) -Embalagens que estão relacionado ao id_produto_acabado da OP - 1ª Etapa ...
    $sql = "SELECT ei.`qtde` AS qtde_estoque, pi.`id_produto_insumo`, pi.`discriminacao`, ppe.`pecas_por_emb` AS qtde 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
            INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
            WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pi.`discriminacao` ";
    $campos_etapa1 = bancos::sql($sql);
    $linhas_etapa1 = count($campos_etapa1);

    //Busca do id_produto_acabado_custo com o id_produto_acabado e operacao_custo do PA da OP passado por parâmetro ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos_custo 				= bancos::sql($sql);
    $id_produto_acabado_custo 	= $campos_custo[0]['id_produto_acabado_custo'];

    //Aqui traz todos os PI(s) que estão relacionado ao id_produto_acabado da OP - 3ª Etapa ... 
    $sql = "SELECT ei.`qtde` AS qtde_estoque, pi.`id_produto_insumo`, pi.`discriminacao`, pp.`qtde` 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` 
            INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pi.`discriminacao` ";
    $campos_etapa3 = bancos::sql($sql);
    $linhas_etapa3 = count($campos_etapa3);

    //Aqui traz todos os PA(s) que estão relacionado ao id_produto_acabado da OP - 7ª Etapa ...
    $sql = "SELECT ea.`qtde_disponivel` AS qtde_estoque, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pp.`qtde` 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
            INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pa.`discriminacao` ";
    $campos_etapa7 = bancos::sql($sql);
    $linhas_etapa7 = count($campos_etapa7);
    if($linhas_etapa1 > 0 || $linhas_etapa3 > 0 || $linhas_etapa7 > 0) {
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Produto(s) Vinculado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Dar Baixa 
            <font color='yellow'>
                <?=$sigla;?>
            </font>
        </td>
        <td>
            <font title='Quantidade (PA - PI) / Peças' style='cursor:help'>
                (PA - PI) / Pç
            </font>
        </td>
        <td>
            <font title='Unidade' style='cursor:help'>
                UN
            </font>
        </td>
        <td>
            <font title='Necessidade Atual' style='cursor:help'>
                Nec. Atual
            </font>
        </td>
        <td>
            <font title='Quantidade Estoque Disponível' style='cursor:help'>
                Qtde Estoque
            </font>
        </td>
        <td>
            <font title='Nova Quantidade Estoque Disponível' style='cursor:help'>
                Nova Qtde Estoque
            </font>
        </td>
        <td>
            Produto
        </td>
    </tr>
<?
/**************************************Listando os PA(s) da 1ª Etapa**************************************/
        for($i = 0; $i < $linhas_etapa1; $i++) {
            $nec_atual1 = $campos_etapa1[$i]['qtde'] * $qtde_produzir;
            $mao_obra 	= (strpos($campos_etapa1[$i]['discriminacao'], 'MAO DE OBRA') !== false) ? 'SIM' : 'NAO';
?>
    <tr class='linhanormal' align='center'>
        <td>
            -
            <input type='hidden' name='hdd_produto_insumo_ignorar[]' value='<?=$campos_etapa1[$i]['id_produto_insumo'];?>'>
        </td>
        <td align='right'>
            <?=number_format($campos_etapa1[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[0]['sigla'];?>
        </td>
        <td>
            -
        </td>
        <td>
            <?=number_format($campos_etapa1[$i]['qtde_estoque'], 2, ',', '.');?>
        </td>
        <td>
            -
            <input type='hidden' name='txt_nova_qtde_estoque_ignorar[]' value='<?=$campos_etapa1[$i]['qtde_estoque'];?>'>
        </td>
        <td align='left'>
            <?=strtoupper($campos_etapa1[$i]['discriminacao']);?>
            <!--<input type='hidden' name='hdd_mao_obra[]' id='hdd_mao_obra<?=$indice_linha;?>' value='<?=$mao_obra;?>'></a>-->
        </td>
    </tr>
<?
        }
/**************************************Listando os PA(s) da 3ª Etapa**************************************/
        $indice_linha = 0;//Controle de Tela e de Objetos ...
        for($i = 0; $i < $linhas_etapa3; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
            //Verifico se teve alguma baixa do PI p/ a OP em questão ...
            $sql = "SELECT `id_baixa_op_vs_pi` 
                    FROM `baixas_ops_vs_pis` 
                    WHERE `id_produto_insumo` = '".$campos_etapa3[$i]['id_produto_insumo']."' 
                    AND `id_op` = '$_GET[id_op]' LIMIT 1 ";
            $campos_baixa_ops_vs_pis = bancos::sql($sql);
            if(count($campos_baixa_ops_vs_pis) == 0) {//Ainda não teve baixa do PI p/ a OP ...
                $nec_atual3 = $campos_etapa3[$i]['qtde'] * $qtde_produzir;
                $mao_obra 	= (strpos($campos_etapa3[$i]['discriminacao'], 'MAO DE OBRA') !== false) ? 'SIM' : 'NAO';
?>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_dar_baixa<?=$indice_linha;?>' value='<?=$campos_etapa3[$i]['id_produto_insumo'];?>' onclick="quantidade_calculada('<?=$indice_linha;?>')" class='checkbox'>
<?
            }else {//Já teve baixa do PI p/ a OP ...
                echo '-';
            }
?>
        </td>
        <td align='right'>
            <?=number_format($campos_etapa3[$i]['qtde'], 2, ',', '.');?>
            <input type='hidden' name='hdd_papi_por_pecas[]' id='hdd_papi_por_pecas<?=$indice_linha;?>' value='<?=$campos_etapa3[$i]['qtde'];?>'>
        </td>
        <td>
            <?=$campos[0]['sigla'];?>
        </td>
        <td align='right'>
            <input type='text' name='txt_necessidade_atual[]' id='txt_necessidade_atual<?=$indice_linha;?>' value='<?=number_format($nec_atual3, 2, ',', '.');?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_estoque[]' id='txt_qtde_estoque<?=$indice_linha;?>' value='<?=number_format($campos_etapa3[$i]['qtde_estoque'], 2, ',', '.');?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_nova_qtde_estoque[]' id='txt_nova_qtde_estoque<?=$indice_linha;?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td align='left'>
            <?=strtoupper($campos_etapa3[$i]['discriminacao']);?>
            <input type='hidden' name='hdd_mao_obra[]' id='hdd_mao_obra<?=$indice_linha;?>' value='<?=$mao_obra;?>'>
        </td>
    </tr>
<?
            $indice_linha++;
        }
/**************************************Listando os PA(s) da 7ª Etapa**************************************/
        for($i = 0; $i < $linhas_etapa7; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
            //Verifico se teve alguma baixa do PI p/ a OP em questão ...
            $sql = "SELECT `id_baixa_op_vs_pa` 
                    FROM `baixas_ops_vs_pas` 
                    WHERE `id_produto_acabado` = '".$campos_etapa7[$i]['id_produto_acabado']."' 
                    AND `id_op` = '$_GET[id_op]' LIMIT 1 ";
            $campos_baixa_ops_vs_pas = bancos::sql($sql);
            if(count($campos_baixa_ops_vs_pas) == 0) {//Ainda não teve baixa do PA p/ a OP ...
                $nec_atual7 = $campos_etapa7[$i]['qtde'] * $qtde_produzir;
                $mao_obra 	= (strpos($campos_etapa7[$i]['discriminacao'], 'MAO DE OBRA') !== false) ? 'SIM' : 'NAO';
?>
            <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_dar_baixa<?=$indice_linha;?>' value='<?=$campos_etapa7[$i]['id_produto_acabado'];?>' onclick="quantidade_calculada('<?=$indice_linha;?>')" class='checkbox'>
<?
            }else {//Já teve baixa do PA p/ a OP ...
                echo '-';
            }
?>
        </td>
        <td align='right'>
            <?=number_format($campos_etapa7[$i]['qtde'], 2, ',', '.');?>
            <input type='hidden' name='hdd_papi_por_pecas[]' id='hdd_papi_por_pecas<?=$indice_linha;?>' value='<?=$campos_etapa7[$i]['qtde'];?>'>
        </td>
        <td>
            <?=$campos[0]['sigla'];?>
        </td>
        <td align='right'>
            <input type='text' name='txt_necessidade_atual[]' id='txt_necessidade_atual<?=$indice_linha;?>' value='<?=number_format($nec_atual7, 2, ',', '.');?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_estoque[]' id='txt_qtde_estoque<?=$indice_linha;?>' value='<?=number_format($campos_etapa7[$i]['qtde_estoque'], 2, ',', '.');?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_nova_qtde_estoque[]' id='txt_nova_qtde_estoque<?=$indice_linha;?>' size='10' maxlength='8' class='textdisabled' disabled>
        </td>
        <td align='left'>
            <?=strtoupper($campos_etapa7[$i]['referencia'].' - '.$campos_etapa7[$i]['discriminacao']);?>
            <input type='hidden' name='hdd_mao_obra[]' id='hdd_mao_obra<?=$indice_linha;?>' value='<?=$mao_obra;?>'>
        </td>
    </tr>
<?
            $indice_linha++;
        }
?>        
</table>
<?
    }
/*************************************************************************************/
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Observação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' rows='2' cols='90' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Observação do Produto:</b>
            <?=$observacao_produto;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'op_montagem.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_retirado_por.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<pre>
<font color='darkblue'>
<b>* Na exibição do(s) Produto(s) Vinculado(s), só está levando em conta o(s) PA(s) que estão atrelado(s) à 1ª, 3ª e 7ª Etapa do Custo.</b>

<b>* A MÃO DE OBRA é a única opção em que o Sistema aceita dar baixa como "0,00", porque não tem a necessidade de termos estoque.</b>
</font>
</pre>
</body>
</html>
<?
}else if($passo == 3) {
/*******************************************************************/
//Verifico se a Sessão não caiu ...
    if(!(session_is_registered('id_funcionario'))) {
?>
        <Script Language = 'JavaScript'>
            window.location = '../../../../html/index.php?valor=1'
        </Script>
<?
        exit;
    }
/*******************************************************************/
    $data_sys = date('Y-m-d H:i:s');
    
/*Esse trecho de código foi comentado em 23/10/2018 porque na teoria era como 
se estivéssemos dando Entrada em dobro na Etapa 1 o que não era pra fazer ...*/

/*1) Controle com a Parte de Baixas na parte de PI(s) - 1ª Etapa, essa é a única Etapa em que eu não disponibilizo os
objetos para o usuário digitar, devido ser mais vantajoso ser feita uma única Baixa a parte com o Estoque do PI para mais de uma OP ...*/
    /*if(count($_POST['hdd_produto_insumo_ignorar']) > 0) {
        foreach($_POST['hdd_produto_insumo_ignorar'] as $i => $id_produto_insumo_ignorar) {
            //Verifico se teve alguma baixa do PI p/ a OP em questão ...
            /*$sql = "SELECT `id_baixa_op_vs_pi` 
                    FROM `baixas_ops_vs_pis` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo_ignorar' 
                    AND `id_op` = '$_POST[id_op]' LIMIT 1 ";
            $campos_baixa_ops_vs_pis = bancos::sql($sql);
            if(count($campos_baixa_ops_vs_pis) == 0) {//Ainda não teve baixa do PI p/ a OP ...
                //Inserindo os Dados no BD ...
                $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$id_produto_insumo_ignorar', '$_SESSION[id_funcionario]', '$_POST[hdd_funcionario_solicitador]', '0', '$_POST[txt_retirado_por]', '".$_POST['txt_nova_qtde_estoque_ignorar'][$i]."', '$_POST[txt_observacao]', 'B', 'N', '$data_sys') ";
                bancos::sql($sql);
                $id_baixa_manipulacao = bancos::id_registro();
                estoque_ic::atualizar($id_produto_insumo_ignorar, 0);
                /************************Novo Controle com a Parte de OP(s)************************/
                /*$sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `data_sys`, `status`) VALUES (NULL, '$id_produto_insumo_ignorar', '$_POST[id_op]', '$id_baixa_manipulacao', '0', '$data_sys', '2') ";
                bancos::sql($sql);
            }
        }
    }*/
    
    $indice = 0;
//2) Controle com a Parte de Baixas na parte de PI(s) ...
    foreach($_POST['chkt_produto_insumo'] as $id_produto_insumo) {
        //Verifico se teve alguma baixa do PI p/ a OP em questão ...
        $sql = "SELECT `id_baixa_op_vs_pi` 
                FROM `baixas_ops_vs_pis` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `id_op` = '$_POST[id_op]' LIMIT 1 ";
        $campos_baixa_ops_vs_pis = bancos::sql($sql);
        if(count($campos_baixa_ops_vs_pis) == 0) {//Ainda não teve baixa do PI p/ a OP ...
            //Aqui eu inverto o Sinal da Quantidade, p/ não dar erro de CMM ...
            $txt_qtde_gravar = $_POST['txt_necessidade_atual'][$indice] * (-1);

            //Inserindo os Dados no BD ...
            $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$id_produto_insumo', '$_SESSION[id_funcionario]', '$_POST[hdd_funcionario_solicitador]', '$txt_qtde_gravar', '$_POST[txt_retirado_por]', '".$_POST['txt_nova_qtde_estoque'][$indice]."', '$_POST[txt_observacao]', 'B', 'N', '$data_sys') ";
            bancos::sql($sql);
            $id_baixa_manipulacao = bancos::id_registro();
            estoque_ic::atualizar($id_produto_insumo, 0);
            /************************Novo Controle com a Parte de OP(s)************************/
            $sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `data_sys`, `status`) VALUES (NULL, '$id_produto_insumo', '$_POST[id_op]', '$id_baixa_manipulacao', '$txt_qtde_gravar', '$data_sys', '2') ";
            bancos::sql($sql);
        }
        $indice++;
    }
//3) Controle com a Parte de Baixas na parte de PA(s) ...
    foreach($_POST['chkt_produto_acabado'] as $id_produto_acabado) {
        //Verifico se teve alguma baixa do PA p/ a OP em questão ...
        $sql = "SELECT `id_baixa_op_vs_pa` 
                FROM `baixas_ops_vs_pas` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `id_op` = '$_POST[id_op]' LIMIT 1 ";
        $campos_baixa_ops_vs_pas = bancos::sql($sql);
        if(count($campos_baixa_ops_vs_pas) == 0) {//Ainda não teve baixa do PA p/ a OP ...
            $txt_qtde_gravar = $_POST['txt_necessidade_atual'][$indice] * (-1);

            //Inserindo os Dados no BD ...
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `status`, `data_sys`) VALUES (NULL, '$id_produto_acabado', '$_SESSION[id_funcionario]', '$_POST[hdd_funcionario_solicitador]', '$_POST[txt_retirado_por]', '$txt_qtde_gravar', '$_POST[txt_observacao]', 'B', '1', '$data_sys') ";
            bancos::sql($sql);
            $id_baixa_manipulacao_pa = bancos::id_registro();
            estoque_acabado::manipular($id_produto_acabado, $txt_qtde_gravar);
            estoque_acabado::qtde_estoque($id_produto_acabado, 1);
            /************************Novo Controle com a Parte de OP(s)************************/
            $sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `data_sys`, `status`) VALUES (NULL, '$id_produto_acabado', '$_POST[id_op]', '$id_baixa_manipulacao_pa', '$txt_qtde_gravar', '$data_sys', '2') ";
            bancos::sql($sql);
        }
        $indice++;
    }
?>
	<Script Language = 'JavaScript'>
            window.location = 'op_montagem.php?valor=2'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Dar Baixa em OP de Montagem ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_numero_op.value == '' && document.form.txt_referencia.value == '' && document.form.txt_discriminacao.value == '') {
        alert('PREENCHA ALGUM CAMPO !')
        document.form.txt_numero_op.focus()
        return false
    }
}
</Script>
</head>
<body onLoad="document.form.txt_numero_op.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="75%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dar Baixa em OP de Montagem
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º OP
        </td>
        <td>
            <input type='text' name="txt_numero_op" title="Digite o N.º OP" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Referência" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.txt_numero_op.focus()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>