<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
require('../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

//Significa que foi acessado do Mód. de Compras, sendo assim eu posso verificar a Sessão normalmente ...
if($atualizar_iframe != 1) segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ OP(S) ATRELADA(S) A ESTE PRODUTO ACABADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRAZO DE ENTREGA ALTERADO COM SUCESSO.</font>";

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_menos_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');

//Significa que já submeteu pelo menos 1 vez ...
if(!empty($_POST['cmd_salvar'])) {
//Aqui eu faço as Atualizações em todo(s) os Prazo(s) de Entrega de todas as OP(s) daquele PA ...
    foreach($_POST['chkt_op'] as $i => $id_op) {
//Busca de alguns dados p/ passar por e-mail mais abaixo ...
        $sql = "SELECT * 
                FROM `ops` 
                WHERE `id_op` = '$id_op' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $qtde_produzir          = $campos[0]['qtde_produzir'];
        $data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
        $produto_acabado        = intermodular::pa_discriminacao($campos[0]['id_produto_acabado'], 0);
        $prazo_entrega_antigo   = data::datetodata($campos[0]['prazo_entrega'], '/');
/**********************************Atualizando os Campos na Base de Dados**********************************/
//Aki eu trato o Novo Prazo de Entrega, p/ poder gravar no BD ...
        $novo_prazo_entrega[$i] = data::datatodate($_POST['txt_novo_prazo_entrega'][$i], '-');
//Atualizando a OP e atualizando com o Login de quem fez alteração nessa Tabela de OP(s) ...
        $sql = "UPDATE `ops` SET `id_funcionario_ocorrencia` = '$_SESSION[id_funcionario]', `prazo_entrega`= '$novo_prazo_entrega[$i]', `situacao` = '".$_POST['txt_situacao'][$i]."', `data_ocorrencia` = '".date('Y-m-d H:i:s')."' WHERE `id_op` = '$id_op' LIMIT 1 ";
        bancos::sql($sql);
/**********************************************************************************************************/
//Dados p/ enviar por e-mail ...
        $complemento_justificativa.= '<br><b> N.º OP: </b>'.$id_op.' <br><b>Produto: </b>'.$produto_acabado.' <br><b>Qtde a Produzir: </b>'.$qtde_produzir.' <br><b>Data de Emissão: </b>'.$data_emissao.' <br><b>Prazo de Entrega Antigo: </b>'.$prazo_entrega_antigo.' <br><b>Novo Prazo de Entrega: </b>'.data::datetodata($txt_novo_prazo_entrega[$i], '/').'<br>';
    }
    $valor = 2;
}

$sql = "SELECT `referencia`, `discriminacao` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos         = bancos::sql($sql);
$referencia     = $campos[0]['referencia'];
$discriminacao  = $campos[0]['discriminacao'];
?>
<html>
<title>.:: Alterar Prazo de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'Javascript'>
function validar() {
/******Só trampo com getElementById devido essa tela estar sendo acessada por Iframe dentro de outros arquivos******/
//Declaração das Variáveis ...
    var total_checkbox_ops = 0, total_checkbox_ops_desmarcadas = 0, valor = false
    //Verifico quantos checkbox existem na Tela ...
    var inputs = document.getElementsByTagName('input')
//Força o preenchimento de pelo menos 1 checkbox ...
    for (var i = 0; i < inputs.length; i++) {
        if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]') {
            if(inputs[i].checked == false) total_checkbox_ops_desmarcadas++
            total_checkbox_ops++
        }
    }

    //Significa que o usuário não selecionou nenhuma opção ...
    if(total_checkbox_ops == total_checkbox_ops_desmarcadas) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    indice = 0
//Controle p/ todos os Checkboxs ...
    for (var i = 0; i < inputs.length; i++) {
        if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]') {
            if(inputs[i].checked == true) {
                if(document.getElementById('txt_dias'+indice).value == '') {
                    alert('DIGITE A QUANTIDADE DE DIAS !')
                    document.getElementById('txt_dias'+indice).focus()
                    return false
                }
            }
            indice++
        }
    }

    indice = 0//Zero novamente para não herdar o valor atribuido a essa variável mais acima ...
//Aqui eu faço o Tratamento p/ poder gravar no BD
    for (var i = 0; i < inputs.length; i++) {
        if(inputs[i].type == 'checkbox' && inputs[i].name == 'chkt_op[]') {
            if(inputs[i].checked == true) document.getElementById('txt_novo_prazo_entrega'+indice).disabled = false
            indice++
        }
    }
    return true
}

function calcular_novo_prazo_entrega(indice) {
//Se tiver preenchido no campo hoje + x dias, então ...
    if(document.getElementById('txt_dias'+indice).value != '') {
        var incremento_prazo_entrega_dias = eval(strtofloat(document.getElementById('txt_dias'+indice).value))
        nova_data('<?=date("d/m/Y");?>', document.getElementById('txt_novo_prazo_entrega'+indice), incremento_prazo_entrega_dias)
    }else {
        document.getElementById('txt_novo_prazo_entrega'+indice).value = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function fecha_e_atualiza() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.getElementById('nao_atualizar').value == 0) {
/*Tenho que fazer esses controles de variáveis porque existem outras Telas que puxam esse arquivo e daí
pode dar problema se não tratar corretamente desses parâmetros*/
        var atualizar_iframe = '<?=$atualizar_iframe;?>'
//Significa que tem que atualizar o Iframe da Tela de Consultar Estoque ...
        if(atualizar_iframe == 1) {
            window.opener.document.location = '../produtos_acabados/prazo_entrega.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$operacao_custo;?>&operacao_custo_sub=<?=$operacao_custo_sub;?>'
        }else {
//Controle para atualização da tela de baixo, caso seja frame ou uma tela normal
            var tela1 = '<?=$tela1;?>'
            var tela2 = '<?=$tela2;?>'
//Se existir esse parâmetro - que com certeza sempre terá
            if(tela1 != '') {
                tela1 = eval(tela1)
                if(typeof(tela1) == 'object') tela1.document.form.submit()
            }
//Se existir esse parâmetro - nem sempre terá
            if(tela2 != '') {
                tela2 = eval(tela2)
                if(typeof(tela2) == 'object') tela2.document.form.submit()
            }
        }
    }
    window.close()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='operacao_custo' value='<?=$operacao_custo;?>'>
<input type='hidden' name='atualizar_iframe' value='<?=$atualizar_iframe;?>'>
<!--Parâmetros que servem para estar atualizando a tela de baixo-->
<input type='hidden' name='tela1' value='<?=$tela1;?>'>
<input type='hidden' name='tela2' value='<?=$tela2;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar' id='nao_atualizar'>
<!--************************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$referencia;?>
            -
            <font color='yellow'>
                <b>Discriminação: </b>
            </font>
            <?=$discriminacao;?>
        </td>
    </tr>
<?
/*Faço uma verificação de Toda(s) as OP(s) que estão em aberto e que ainda não foram excluídas 
do Sistema referente ao PA atrelado ...*/
	$sql = "SELECT `id_op`, `id_funcionario_ocorrencia`, `observacao`, `data_ocorrencia` 
                FROM `ops` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `status_finalizar` = '0' 
                AND `ativo` = '1' 
                ORDER BY prazo_entrega ";
	$campos_op = bancos::sql($sql);
	$linhas_op = count($campos_op);
//Printo a Mensagem ...
	if($linhas_op == 0) {
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
//Se tiver pelo menos 1 OP, então ...
	}else {
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            OP(s) Atrelado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar_tudo(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            N.º OP
        </td>
        <td>
            Data de <br/>Emissão
        </td>
        <td>
            Qtde OP / Saldo
        </td>
        <td>
            Prazo <br/>Entrega OP
        </td>
        <td>
            Hoje + <br/>x dias
        </td>
        <td>
            Novo Prazo <br/>de Entrega
        </td>
        <td>
            Situação
        </td>
        <td>
            <font title='Funcionário e Data da Ocorrência' style='cursor:help'>
                Func e Data Ocorr
            </font>
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_op; $i++) {
/*Aqui eu passo esse parâmetro de alterar_observacao=1, porque somente nessa tela é que além de enxergar
os detalhes da OP, que eu vou poder estar alterando a observação da OP também*/
            $url = "javascript:nova_janela('../../producao/ops/alterar.php?passo=1&id_op=".$campos_op[$i]['id_op']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')";
            $vetor_dados_op = intermodular::dados_op($campos_op[$i]['id_op']);
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='center'>
            <input type='checkbox' name='chkt_op[]' id="chkt_op<?=$i;?>" value="<?=$campos_op[$i]['id_op'];?>" onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='center'>
            <a href="<?=$url;?>" title='Detalhes de OP' alt='Detalhes de OP' class='link'>
                <?=$campos_op[$i]['id_op'].$vetor_dados_op['posicao_op'];?>
            </a>
        </td>
        <td>
            <?=$vetor_dados_op['data_emissao'];?>
        </td>
        <td>
            <?=$vetor_dados_op['qtde_produzir'].' / '.$vetor_dados_op['qtde_saldo'];?>
        </td>
        <td>
        <?
            /**********Controles para cor do link**********/
            /*Se esse Prazo de Entrega da OP foi atualizado recentemente, quer dizer em até 7 dias abaixo da data atual "HOJE", 
            ele printa a cor do link em verde*/
            /*if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_dias) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }*/
            $cor_link = (substr($campos_op[$i]['data_ocorrencia'], 0, 10) >= $data_atual_menos_sete) ? 'green' : '';
            /**********************************************/
        ?>
            <a href='#' class='link'>
                <font color='<?=$cor_link;?>' size='-2'>
                    <?=$vetor_dados_op['prazo_entrega'].'-'.$vetor_dados_op['situacao'];?>
                </font>
            </a>
        </td>
        <td>
            <input type='text' name='txt_dias[]' id='txt_dias<?=$i;?>' title='Digite a Qtde de Dias' maxlength='6' size='8' onclick="checkbox('<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == '00' || this.value == '000' || this.value == '0000' || this.value == '00000') {this.value = ''};calcular_novo_prazo_entrega('<?=$i;?>')" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_novo_prazo_entrega[]' id='txt_novo_prazo_entrega<?=$i;?>' title='Digite o Novo Prazo de Entrega' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <textarea name='txt_situacao[]' id='txt_situacao<?=$i;?>' cols='25' rows='1' maxlength='50' onclick="checkbox('<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled></textarea>
        </td>
        <td>
        <?
//Busca do Nome do Funcionário e da Data de Ocorrência de alteração da última OP ...
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos_op[$i]['id_funcionario_ocorrencia']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            if(count($campos_funcionario) == 1) {
//Aqui eu só listo o primeiro nome ...
                echo strtok($campos_funcionario[0]['nome'], ' ').' - '.data::datetodata(substr($campos_op[$i]['data_ocorrencia'], 0, 10), '/').' - '.substr($campos_op[$i]['data_ocorrencia'], 11, 8);
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos_op[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='10'>
            <iframe name='detalhes' id='detalhes' src = '../follow_ups/detalhes.php?identificacao=<?=$id_produto_acabado;?>&origem=19' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
        <td>
            <?=number_format($total_nao_entregue, 0, ',', '.');?>
        </td>
        <td colspan='7'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar_atualizar' value='Fechar e Atualizar' title='Fechar e Atualizar' onclick='fecha_e_atualiza()' style='color:black' class='botao'>
        </td>
    </tr>
<?
	}
?>
</table>
<br>
<?
//Aqui eu busco o Custo desse PA ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos_pac                 = bancos::sql($sql);
    $id_produto_acabado_custo   = $campos_pac[0]['id_produto_acabado_custo'];
//Aqui eu verifico se esse PA tem itens de Blank na 3ª Etapa ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `pacs_vs_pis` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
    $campos_etapa3 = bancos::sql($sql);
    $linhas_etapa3 = count($campos_etapa3);
    if($linhas_etapa3 > 0) {//Se existir pelo menos 1 item, então chamo a função que contém esses Itens ...
        $nao_chamar_biblioteca	= 1;
        $nao_exibir_voltar 		= 1;
        $atualizar_iframe		= 0;
        /*Aqui eu faço requisição da parte de Compra Produção onde eu consigo saber o prazo de Entrega 
        dos pedidos em Aberto ...*/
        require('compra_producao.php');
/*O índice aqui não pode ser $i, porque é puxado um outro aqui dentro desse em que uso o mesmo índice $i no loop, 
daí esse se perde ...*/
        for($a = 0; $a < $linhas_etapa3; $a++) {
//Aqui nessa parte eu chamo a função referente as Pendências desse Item Blank da 3ª Etapa ...
            $id_produto_insumo  = $campos_etapa3[$a]['id_produto_insumo'];
            require('../../compras/estoque_i_c/nivel_estoque/pendencias_item.php');
        }
    }
//Aqui nessa parte eu chamo a função referente ao Visualizar Pedidos ...
    $nao_chamar_biblioteca = 1;
    if($referencia == 'ESP') require('visualizar_pedidos.php');
    $nivel_reduzido = 1;
    require('visualizar_estoque.php');
?>
</form>
</body>
</html>