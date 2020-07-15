<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

$data_emissao = date('Y-m-d');

if(!empty($_POST['hdd_cotacao'])) $complemento_obs = ' Cotação de Compra N.º '.$_POST['hdd_cotacao'];

/*******************Aqui nessa parte eu gero as OP(s)*******************/
for($i = 0; $i < count($_POST['hdd_produto_acabado']); $i++) {
    /*Flag de Controle que a Princípio sempre será sim, essa só irá mudar se este PA for 
    'ESP', a Família deste = 'Machos' e OC / OC Sub = 'Ind' 'Ind'...*/
    $gerar_op_para_pa_da_tela = 'SIM';
    
    //Se tiver quantidade digitada para a OP então ...
    $qtde_compra = str_replace('.', '', $_POST['txt_qtde'][$i]);
    $qtde_compra = str_replace(',', '.', $qtde_compra);
    if($qtde_compra != '' && $qtde_compra != 0 && $_POST['hdd_produto_acabado'][$i] != '') {
        //Aqui eu busco alguns dados do PA do Loop ...
        $sql = "SELECT gpa.`id_familia`, gpa.`prazo_entrega`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                pa.`referencia`, pa.`desenho_para_op` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        /***************************Controle Especial**************************/
        //Se o PA do Loop = 'ESP', a Família deste = 'Machos' e OC / OC Sub = 'Ind' 'Ind' que significa que o Custo deste é Industrial, então ...
        if($campos[0]['referencia'] == 'ESP' && $campos[0]['id_familia'] == 9 && $campos[0]['operacao_custo'] == 0 && $campos[0]['operacao_custo_sub'] == 0) {
            //Busco o id_custo desse PA ...
            $sql = "SELECT `id_produto_acabado_custo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' 
                    AND `operacao_custo` = '0' LIMIT 1 ";
            $campos_custo               = bancos::sql($sql);
            $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];
            
            //Através desse $id_custo que encontrei acima eu busco o PA que está atrelado em sua 7ª Etapa ...
            $sql = "SELECT gpa.`prazo_entrega`, pp.`id_produto_acabado`, pp.`qtde` 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
            $campos_etapa7  = bancos::sql($sql);
            if(count($campos_etapa7) == 1) {//Só vamos realizar esse procedimento se realmente tivermos um PA na 7ª Etapa ...
                $prazo_entrega  = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), $campos_etapa7[0]['prazo_entrega']), '-');

                //Insertando a OP ...
                $sql = "INSERT INTO `ops` (`id_op`, `id_produto_acabado`, `id_funcionario_ocorrencia`, `qtde_produzir`, `data_emissao`, `prazo_entrega`, `observacao`, `lote_diferente_custo`, `data_ocorrencia`) VALUES (NULL, '".$campos_etapa7[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '".($qtde_compra * $campos_etapa7[0]['qtde'])."', '$data_emissao', '$prazo_entrega', 'OP gerada de forma automática.".$complemento_obs."', '".$_POST['hdd_lote_diferente_custo'][$i]."', '".date('Y-m-d H:i:s')."') ";
                bancos::sql($sql);
                estoque_acabado::atualizar_producao($campos_etapa7[0]['id_produto_acabado']);
                /*Pelo fato de acabar de gerarmos uma OP em cima deste PA correlato ao PA do Loop da Tela que veio por parâmetro, 
                consequentemente não é mais necessário gerar uma nova OP p/ o PA da Tela no trecho de código logo abaixo ...*/
                $gerar_op_para_pa_da_tela = 'NÃO';
            }
        }
        /**********************************************************************/
        if($gerar_op_para_pa_da_tela == 'SIM') {
            $prazo_entrega  = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), $campos[0]['prazo_entrega']), '-');

            //Insertando a OP ...
            $sql = "INSERT INTO `ops` (`id_op`, `id_produto_acabado`, `id_funcionario_ocorrencia`, `qtde_produzir`, `data_emissao`, `prazo_entrega`, `observacao`, `lote_diferente_custo`, `data_ocorrencia`) VALUES (NULL, '".$_POST['hdd_produto_acabado'][$i]."', '$_SESSION[id_funcionario]', '$qtde_compra', '$data_emissao', '$prazo_entrega', 'OP gerada de forma automática.".$complemento_obs."', '".$_POST['hdd_lote_diferente_custo'][$i]."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
            estoque_acabado::atualizar_producao($_POST['hdd_produto_acabado'][$i]);
        }
        //Pertinente ao último INSERT que gerou as OP(s) acima ...
        $id_op = bancos::id_registro();
        $ops_geradas.= $id_op.', ';
    }
}
/***********************************************************************/
//Nessa parte eu listo as OPs que acabaram de ser geradas ...
$ops_geradas 	= substr($ops_geradas, 0, strlen($ops_geradas) - 2);
$vetor_ops      = explode(',', $ops_geradas);
?>
<html>
<head>
<title>.:: Relação de OP(s) Gerada(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
document.oncontextmenu = function () { return false }
if(document.layers) {
    window.captureEvents(event.mousedown)
    window.onmousedown =
    function (e){
    if (e.target == document)
        return false
    }
}else {
    document.onmousedown = function (){ return false }
}

function rejeitaTecla(oEvent) {
    var cod_tecla = 116;//tecla que quer bloquear. 116 é o código da tecla F5
    var oEvent = oEvent ? oEvent : window.event
    var tecla = (oEvent.keyCode) ? oEvent.keyCode : oEvent.which
    if(oEvent.type == 'keydown' && navigator.appName.indexOf('Internet Explorer') < 0) {
            //se for keydown e não for o IE, vazarei pois o keypress já foi executado
            return false
    } 
    if(typeof(oEvent.keyCode) == 'number' && oEvent.keyCode == cod_tecla) {
        if (typeof(oEvent.preventDefault) == 'function') {
            oEvent.preventDefault()
        }else {
            oEvent.returnValue = false
            oEvent.keyCode = 0;
        }
    }
    return false
}
document.onkeypress = rejeitaTecla; //Pro Opereta e FF. O keydown nao tem preventDefault no OP.
document.onkeydown = rejeitaTecla; //Pro IE. O IE 6 não executa funcoes no keypress.

function imprimir() {
    var elementos = document.form.elements
    var id_ops = ''
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_ops+= elementos[i].value + ', '
            }
        }
    }
    if(id_ops == '') {
        alert('SELECIONE UMA OP PARA IMPRIMIR !')
        return false
    }
    id_ops = id_ops.substr(0, id_ops.length - 2)
    nova_janela('relatorio/relatorio.php?id_ops='+id_ops, 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name="form">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Relação de OP(s) Gerada(s)
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
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
	for($i = 0; $i < count($vetor_ops); $i++) {
            $url = 'alterar.php?passo=2&id_op='.$vetor_ops[$i].'&pop_up=1';

            //Aqui eu busco dados da OP do Loop ...
            $sql = "SELECT o.*, gpa.id_familia, pa.referencia, pa.`desenho_para_op` 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = o.id_produto_acabado 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    WHERE o.id_op = '".$vetor_ops[$i]."' 
                    AND o.ativo = '1' LIMIT 1 ";
            $campos = bancos::sql($sql);
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[0]['id_op'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado']);?>
        </td>
        <td>
            <?=number_format($campos[0]['qtde_produzir'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[0]['prazo_entrega'], '/');?>
        </td>
        <td>
        <?
            /*Só pode mostrar o Checkbox quando existir 'desenho anexado' na OP ou se não existir 
            desenho não pode ser das Famílias Pinos 2 ou Cossinetes 8 ...*/
            if($campos[0]['desenho_para_op'] != '' || ($campos[0]['desenho_para_op'] == '' && $campos[0]['id_familia'] != 2 && $campos[0]['id_familia'] != 8)) {
        ?>
                <input type='checkbox' name='chkt_op[]' value="<?=$campos[0]['id_op'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        <?
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr align='center'>
        <td colspan='8' class='linhacabecalho'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='return imprimir()' style='color:black' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='8'>
            Total de OP(s): <?=count($vetor_ops);?>
        </td>
    </tr>
</table>
</form>
</body>
</html>