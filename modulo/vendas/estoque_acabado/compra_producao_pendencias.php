<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');//Essa biblioteca é chamada de Dentro da Biblioteca de 'Custos' ...
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

$mensagem[1]    = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_embarque                  = $_POST['txt_data_embarque'];
    $txt_fator_correcao_mmv             = $_POST['txt_fator_correcao_mmv'];
    $txt_qtde_meses                     = $_POST['txt_qtde_meses'];
    $cmb_tipo_compra                    = $_POST['cmb_tipo_compra'];
    $txt_desconto                       = $_POST['txt_desconto'];
    $chkt_mostrar_oc_ii_nas_cotacoes    = $_POST['chkt_mostrar_oc_ii_nas_cotacoes'];
    $hdd_acao                           = $_POST['hdd_acao'];
}else {
    $txt_data_embarque                  = $_GET['txt_data_embarque'];
    $txt_fator_correcao_mmv             = $_GET['txt_fator_correcao_mmv'];
    $txt_qtde_meses                     = $_GET['txt_qtde_meses'];
    $cmb_tipo_compra                    = $_GET['cmb_tipo_compra'];
    $txt_desconto                       = $_GET['txt_desconto'];
    $chkt_mostrar_oc_ii_nas_cotacoes    = $_GET['chkt_mostrar_oc_ii_nas_cotacoes'];
    $hdd_acao                           = $_GET['hdd_acao'];
}

//Na primeira vez que carregar a Tela, o Sistema sugere Normal para o Tipo de Compra ...
if(empty($cmb_tipo_compra)) {
    $cmb_tipo_compra                    = 'N';
    $checked_mostrar_oc_ii_nas_cotacoes = 'checked';
}else {
    $checked_mostrar_oc_ii_nas_cotacoes = (!empty($_POST['chkt_mostrar_oc_ii_nas_cotacoes'])) ? 'checked' : '';
}

if($hdd_acao == 'COMPRA_PRODUCAO') {//Compra Produção ...
    $title      = 'Compra / Produção';
    $rotulo1    = 'Qtde Compra';
    $rotulo2    = 'Neces Compra / Prod';
    
    $lista_pas = substr($_POST['hdd_vetor_compra_producao'], 0, strlen($_POST['hdd_vetor_compra_producao']) - 1);
}else {//Pendências ...
    $title      = 'Pendências';
    $rotulo1    = 'Qtde Urgente';
    $rotulo2    = 'Qtde Urgente Sug';
    
    $lista_pas  = substr($_POST['hdd_vetor_pendencia'], 0, strlen($_POST['hdd_vetor_pendencia']) - 1);
}
?>
<html>
<head>
<title>.:: <?=$title;?> ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular(indice) {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    var desconto = strtofloat(document.form.txt_desconto.value)
    var total_geral = 0
//Significa que o usuário não está digitando em uma linha específica ...
    if(typeof(indice) == 'undefined') {
        for(var i = 0; i < linhas; i++) {
            var qtde_compra = strtofloat(document.getElementById('txt_qtde'+i).value)
            var preco_unitario = strtofloat(document.getElementById('hdd_preco_unitario'+i).value)
            
            document.getElementById('txt_preco_unitario'+i).value = (preco_unitario * (100 - desconto) / 100)
            document.getElementById('txt_preco_unitario'+i).value = arred(document.getElementById('txt_preco_unitario'+i).value, 2, 1)

            var preco_unitario_com_desconto = strtofloat(document.getElementById('txt_preco_unitario'+i).value)
            document.getElementById('txt_preco_total'+i).value = qtde_compra * preco_unitario_com_desconto
            document.getElementById('txt_preco_total'+i).value = arred(document.getElementById('txt_preco_total'+i).value, 2, 1)
        }
    }else {//Significa que o usuário está digitando em uma única linha ...
        var qtde_compra 	= strtofloat(document.getElementById('txt_qtde'+indice).value)
        var qtde_lote 		= strtofloat(document.getElementById('txt_qtde_lote'+indice).value)

        /********************************************************************/
        //Controle com a cor da Qtde de Compra em comparado a Qtde de Lote em 15% abaixo ou acima ...
        if(0.85 * qtde_lote > qtde_compra || qtde_compra > 1.15 * qtde_lote) {
            document.getElementById('txt_qtde'+indice).style.background 	= 'red'
            document.getElementById('txt_qtde'+indice).style.color       = 'white'
            document.getElementById('hdd_lote_diferente_custo'+indice).value	= 'S'
        }else {
            document.getElementById('txt_qtde'+indice).style.background 	= 'white'
            document.getElementById('txt_qtde'+indice).style.color       = 'brown'
            document.getElementById('hdd_lote_diferente_custo'+indice).value	= 'N'
        }
        /********************************************************************/

        var preco_unitario 	= strtofloat(document.getElementById('hdd_preco_unitario'+indice).value)
        document.getElementById('txt_preco_unitario'+indice).value = (preco_unitario * (100 - desconto) / 100)
        document.getElementById('txt_preco_unitario'+indice).value = arred(document.getElementById('txt_preco_unitario'+indice).value, 2, 1)

        var preco_unitario_com_desconto = strtofloat(document.getElementById('txt_preco_unitario'+indice).value)
        document.getElementById('txt_preco_total'+indice).value = qtde_compra * preco_unitario_com_desconto
        document.getElementById('txt_preco_total'+indice).value = arred(document.getElementById('txt_preco_total'+indice).value, 2, 1)
    }
    //Aqui eu faço o somatório de todos os Preços Totais
    for(var i = 0; i < linhas; i++) total_geral+= eval(strtofloat(document.getElementById('txt_preco_total'+i).value))
    document.form.txt_total_geral.value = total_geral
    document.form.txt_total_geral.value = arred(document.form.txt_total_geral.value, 2, 1)
}

function controlar_cor_desconto(desconto) {
    var valor_desconto = eval(strtofloat(desconto.value))
    
    if(valor_desconto >= 0 || typeof(valor_desconto) == 'undefined') {//Valores Positivos ou Caixa Vazia ...
        desconto.style.background = 'white'
        desconto.style.color      = 'Brown'
    }else {//Valores Negativos ...
        desconto.style.background = 'red'
        desconto.style.color      = 'white'
    }
}

function repassar_cotacao_hidden() {
    document.form.hdd_cotacao.value = document.getElementById('div_cotacao').innerHTML
    var id_cotacao = document.form.hdd_cotacao.value.split(' ')
    document.form.hdd_cotacao.value = id_cotacao[3]
}

function gerar_cotacao() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        var qtde_compra         = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
        var pecas_por_embalagem = eval(strtofloat(document.getElementById('hdd_pecas_por_emb'+i).value))
        var resto_divisao       = eval(qtde_compra) % (pecas_por_embalagem)//Verifica o Mod (Resto da Divisão) ...
        /*
        Comentado no dia 05/05/2014 porque o Roberto não tem certeza de esse ser melhor o caminho ...
            
        if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Representa que não está compatível a Qtde de Peças / Embalagem ...
            var sugestao = (parseInt(qtde_compra / pecas_por_embalagem) + 1) * pecas_por_embalagem
            alert('NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGESTÃO  =  '+sugestao+'  .')
            document.getElementById('txt_qtde'+i).focus()
            document.getElementById('txt_qtde'+i).select()
            return false
        }*/
    }
    
    var valor_desconto = eval(strtofloat(document.form.txt_desconto.value))
    if(valor_desconto < 0) {
        var resposta = confirm('VOCÊ ESTÁ USANDO ACRÉSCIMO AO INVÉS DE DESCONTO !!!\n\nDESEJA CONTINUAR ?')
        if(resposta == false) return false
    }
    ajax('consultar_cotacao.php', 'div_cotacao')
    //Preciso colocar um timer aki pq o Sys leva um tempo pra atualizar o valor da Div pro Hidden ...
    setTimeout('repassar_cotacao_hidden()', '300')
    
    document.form.action = '../../classes/cotacao/gerar_cotacao.php?vendas=1'//Significa q está sendo gerado de Vendas ...
    document.form.target = 'GERAR_COTACAO'
    nova_janela('', 'GERAR_COTACAO', '', '', '', '', 510, 910, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function gerar_ops() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_blank'+i) != null) {//Se existir algum PI que é BLANK então ...
            var qtde_compra = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
            if(qtde_compra > 0) {//Se a "Qtde Compra" digitada nesse item > 0, faço a verificação abaixo ...
                if(document.form.hdd_cotacao.value == '') {//Verifico se foi gerada uma Cotação anteriormente ...
                    alert('GERE UMA COTAÇÃO ANTES DE GERAR OP !')
                    document.form.cmd_gerar_cotacao.focus()
                    return false
                }
                break;
            }
        }
    }
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA GERAR OP(S) ?')
    if(resposta == true) {
        //Significa que o usuário não está digitando em uma linha específica ...
        for(var i = 0; i < linhas; i++) {
            var qtde_compra         = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
            var pecas_por_embalagem = eval(strtofloat(document.getElementById('hdd_pecas_por_emb'+i).value))
            var resto_divisao       = eval(qtde_compra) % (pecas_por_embalagem)//Verifica o Mod (Resto da Divisão) ...
            /*
            Comentado no dia 05/05/2014 porque o Roberto não tem certeza de esse ser melhor o caminho ...

            if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Representa que não está compatível a Qtde de Peças / Embalagem ...
                var sugestao = (parseInt(qtde_compra / pecas_por_embalagem) + 1) * pecas_por_embalagem
                alert('NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGESTÃO  =  '+sugestao+'  .')
                document.getElementById('txt_qtde'+i).focus()
                document.getElementById('txt_qtde'+i).select()
                return false
            }*/
            /************************Lógica para comparar Qtde de Peças por Corte***********************/
            //Só pode fazer a comparação se o Produto for do tipo ESP ...
            if(document.getElementById('hdd_referencia'+i).value == 'ESP' && document.getElementById('hdd_produto_acabado'+i).value != '') {
                var resto_divisao = eval(strtofloat(document.getElementById('txt_qtde'+i).value)) % (document.getElementById('txt_pecas_corte'+i).value)
                if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Qtde ñ está Compatível
                    alert('A QUANTIDADE À PRODUZIR NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / CORTE !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
            }
        }
        /*Pode acontecer que a função em Ajax teve algum delay na hora de atualizar o hidden Cotação com o N.º da Cotação que está na 
        da Div e sendo assim gerou a palavra undefined, para evitar isso chamo novamente a função que atualiza o hidden cotação ...*/
        if(document.form.hdd_cotacao.value == 'undefined') repassar_cotacao_hidden()

        document.form.action = '../../producao/ops/gerar_ops.php'
        document.form.target = 'GERAR_OPS'
        nova_janela('', 'GERAR_OPS', '', '', '', '', 560, 960, 'c', 'c', '', '', 's', 's', '', '', '')
        document.form.submit()
    }
}

function tipo_compra() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    //Faço esse tramiti p/ não dar erro na hora de Submeter os obj ...
    for(var i = 0; i < linhas; i++) document.getElementById('txt_qtde'+i).value = strtofloat(document.getElementById('txt_qtde'+i).value)
    document.form.action = '<?=$PHP_SELF;?>'
    document.form.target = '_SELF'
    document.form.submit()
}

function visualizar_pis(id_produto_acabado, qtde_produzir) {
    var nova_qtde_produzir  = eval(strtofloat(qtde_produzir))
    nova_janela('../../producao/ops/visualizar_pis.php?id_produto_acabado='+id_produto_acabado+'&nova_qtde_produzir='+nova_qtde_produzir, 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')
}

function opcoes_produto_acabado(id_produto_acabado, status_estoque) {
    if(status_estoque == 0) {//Produto Acabado Liberado ...
        nova_janela('../../classes/produtos_acabados/substituir_estoque_pa.php?id_produto_acabado='+id_produto_acabado, 'POP', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Produto Acabado Bloqueado ...
        alert('ESTE PRODUTO ACABADO ESTÁ BLOQUEADO !!!')
    }
}

function gerar_impressao() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        var qtde_urgente    = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
        var compra_producao = eval(strtofloat(document.getElementById('hdd_compra_producao'+i).value))

        //Nunca a Qtde Digitada pelo Usuário pode ser maior do que a Compra / Produção ...
        if(qtde_urgente > compra_producao) {
            var resposta = confirm('QUANTIDADE INVÁLIDA !!! QUANTIDADE MAIOR DO QUE A COMPRA / PRODUÇÃO !\n\nDESEJA CONTINUAR COM AS QUANTIDADES INVÁLIDAS ?')
            if(resposta == true) {
                break;//Esse break é para sair fora do Loop ...
            }else {
                document.getElementById('txt_qtde'+i).focus()
                document.getElementById('txt_qtde'+i).select()
                return false
            }
        }
    }
    
    for(var i = 0; i < linhas; i++) {
        //Deixo no Formato em que o Banco de Dados vai reconhecer ...
        //document.getElementById('txt_preco_total'+i).value      = strtofloat(document.getElementById('txt_preco_total'+i).value)
        //Desabilito esse campo p/ que os valores desse subam como parâmetro p/ a próxima Tela de Impressão ...
        document.getElementById('txt_preco_total'+i).disabled   = false
    }

    document.form.action = 'impressao_pendencias.php'//Significa q está sendo gerado de Vendas ...
    document.form.target = 'IMPRESSAO_PENDENCIAS'
    nova_janela('impressao_pendencias.php', 'IMPRESSAO_PENDENCIAS', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')
    limpeza_moeda('form', 'txt_fator_correcao_mmv, txt_qtde_meses, txt_desconto, ')
    document.form.submit()
}
</Script>
</head>
<body onload='calcular();document.form.txt_desconto.focus()'>
<form name='form' action='' method='post'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='21'>
            <?=$title;?> - 
            <font color='yellow' size='-1'>
                Data: 
            </font>
            <?=date('d/m/Y');?>
            <br>
            <?
                if(!empty($_POST['txt_data_embarque'])) {
            ?>
            <font color='yellow' size='-1'>
                    Data Embarque: 
            </font>
            <?
                    echo $txt_data_embarque.'&nbsp;-&nbsp;';
                }
            ?>
            <font color='yellow' size='-1'>
                Fat. MMV = 
            </font>
            <?=$_POST['txt_fator_correcao_mmv'];?>&nbsp;e
            <?=str_replace('.', ',', $_POST['txt_qtde_meses']);?> 
            <font color='yellow' size='-1'>
                    Mês(es)
            </font>
            &nbsp;-&nbsp;
            <select name='cmb_tipo_compra' title='Selecione o Tipo de Compra' onchange='tipo_compra()' class='combo'>
            <?
                if($cmb_tipo_compra == 'N') {
                    $selectedn = 'selected';
                }else {
                    $selectede = 'selected';
                }
            ?>
                <option value='N' <?=$selectedn;?>>Nacional</option>
                <option value='E' <?=$selectede;?>>Export</option>
            </select>
            &nbsp;-&nbsp;
            <?
                //Somente os usuários Roberto 62 e Dárcio 98, que podem dar Desconto Negativo ... "Acréscimo"
                $onkeyup = ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) ? "verifica(this, 'moeda_especial', '2', '1', event)" : "verifica(this, 'moeda_especial', '2', '', event)";
            ?>
            Desconto: <input type='text' name='txt_desconto' value='<?=$txt_desconto;?>' onKeyUp="<?=$onkeyup;?>;controlar_cor_desconto(this);calcular()" size='9' maxlength='8' class='caixadetexto'>
            &nbsp;-
            <input type='checkbox' name='chkt_mostrar_oc_ii_nas_cotacoes' id='chkt_mostrar_oc_ii_nas_cotacoes' value='S' onclick='document.form.submit()' class='checkbox' <?=$checked_mostrar_oc_ii_nas_cotacoes;?>>
            <label for='chkt_mostrar_oc_ii_nas_cotacoes'>
                Mostrar OC-II nas Cotações
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <?=$rotulo1;?>
        </td>
        <td>
            Pçs / Corte
        </td>
        <td>
            <?=$rotulo2;?>
        </td>
        <td>
            Lote do Custo
        </td>
        <td>
            <font style='cursor:help' size='-2' title='Operação de Custo'>
                O.C.
            </font>
            / 7ª Etapa
        </td>
        <td>
            Compra<br> Produção Tot
        </td>
        <td>
            <font style='cursor:help' size='-2' title='Estoque Comprometido'>
                E.C. Tot
            </font>
        </td>
        <td>
            <font title='Estoque do Fornecedor' size='-2' style='cursor:help'>
                E Forn
            </font>
        </td>
        <td>
            <font title='Estoque do Porto' size='-2' style='cursor:help'>
                E Porto
            </font>
        </td>
        <td>
            Prog. Tot
        </td>
        <td>
            MMV Tot
            <img src = '../../../imagem/bloco_negro.gif' title='Necessidade p/ Qtde Meses' style='cursor:help' width='6' height='6'>
        </td>
        <td>
            MMV Cor.
            <img src = '../../../imagem/bloco_negro.gif' title='Corrigido pelo Fator Top' style='cursor:help' width='6' height='6'>
        </td>
        <td>
            Compra Prod.
            <br>Filhos
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Pço. Unit.
        </td>
        <td>
            Pço. Tot
        </td>
        <td>
            MLM Tot
        </td>
        <td>
            EC Pai
        </td>
        <td>
            EC p/x meses Tot
        </td>
        <td>
            Prioridade
        </td>
    </tr>
<?
	$data_atual     = date('Y-m-d');
        $fator_custo_4  = genericas::variavel(9);

	$lista_pas = explode(';', $lista_pas);

	for($i = 0; $i < count($lista_pas); $i++) {
            $contador = 0;
            
            $id_produto_acabado = ''; $qtde_a_produzir = ''; $soma_compra_prod_todos_niveis = '';
            $soma_qtde_oes_todos_niveis = ''; $soma_est_comp_todos_niveis = ''; 
            $soma_est_prog_todos_niveis = ''; $soma_mmv_todos_niveis = ''; $mmv_corrigido_total = ''; 
            $total_compra_producao_pa_nivel1 = ''; $total_mlm_todos_niveis = ''; $ec_pa_princ = ''; 
            $ec_p_x_meses_pa_princ = ''; $urgencia = ''; $prioridade = '';

//Aqui eu vasculho cada caractér do Item da Lista ...
            for($j = 0; $j < strlen($lista_pas[$i]); $j++) {
                if(substr($lista_pas[$i], $j, 1) == '|') {
                    $contador++;
                }else {
                    if($contador == 0) {
                        $id_produto_acabado.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 1) {
                        $qtde_a_produzir.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 2) {
                        $soma_compra_prod_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 3) {
                        $soma_qtde_oes_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 4) {
                        $soma_est_comp_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 5) {
                        $soma_est_prog_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 6) {
                        $soma_mmv_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 7) {
                        $mmv_corrigido_total.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 8) {
                        $total_compra_producao_pa_nivel1.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 9) {
                        $total_mlm_todos_niveis.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 10) {
                        $ec_pa_princ.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 11) {
                        $ec_p_x_meses_pa_princ.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 12) {
                        $urgencia.= substr($lista_pas[$i], $j, 1);
                    }else if($contador == 13) {
                        $prioridade.= substr($lista_pas[$i], $j, 1);
                    }
                }
            }
            $vetor_produto_acabado[]                = $id_produto_acabado;
            $vetor_qtde_a_produzir[]                = $qtde_a_produzir;
            $vetor_soma_compra_prod_todos_niveis[]  = $soma_compra_prod_todos_niveis;
            $vetor_soma_qtde_oes_todos_niveis[]     = $soma_qtde_oes_todos_niveis;
            $vetor_soma_est_comp_todos_niveis[]     = $soma_est_comp_todos_niveis;
            $vetor_soma_est_prog_todos_niveis[]     = $soma_est_prog_todos_niveis;
            $vetor_soma_mmv_todos_niveis[]          = $soma_mmv_todos_niveis;
            $vetor_mmv_corrigido_total[]            = $mmv_corrigido_total;
            $vetor_total_compra_producao_pa_nivel1[]= $total_compra_producao_pa_nivel1;
            $vetor_total_mlm_todos_niveis[]         = $total_mlm_todos_niveis;
            $vetor_ec_pa_princ[]                    = $ec_pa_princ;
            $vetor_ec_p_x_meses_pa_princ[]          = $ec_p_x_meses_pa_princ;
            $vetor_urgencia[]                       = $urgencia;
            $vetor_prioridade[]                     = $prioridade;
	}
	$qtde_pipas = 0;//Aqui é para eu saber Qtdes PIs de PAs que existem nessa listagem ...
	$gerar_ops = 0;//Aqui é para eu saber Qtdes de PA´s que tem Operação de Custo II ou IR ...
        
        /*Esse vetor será utilizado mais abaixo: 

        Machos Manuais WS Jogos "22", Machos Manuais HSS Jogos "83", Broca MD "143", Brocas Hss "144", 
        Machos Manuais WS Avulsos "162", Machos Manuais HSS Avulsos "165" ...*/
        $vetor_machos_manuais_warrior = array(22, 83, 143, 144, 162, 165);
        
        /*Machos Manuais HSS Jogos "122", Machos Manuais WS Jogos "123", Machos Manuais WS Avulsos "163", 
        Machos Manuais HSS Avulsos "164" ...*/
        $vetor_machos_manuais_heinz = array(122, 123, 163, 164);
        
        $indice = 0;//Essa variável será utilizada mais abaixo ...
        
	for($i = 0; $i < count($vetor_produto_acabado); $i++) {
            $id_produto_acabado_loop 	= '';//Como só posso gerar OP´s para PAs com Operação de Custo = II então sempre zero essa var ...
            $mostrar_msn_blank 		= 0;
            $mostrar_usinagem           = 0;
            //1) Busca da Operação do Produto Acabado e mais alguns Dados ...
            $sql = "SELECT ged.`id_gpa_vs_emp_div`, gpa.`id_familia`, pa.`id_produto_acabado`, 
                    pa.`operacao_custo`, pa.`referencia`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    pa.`desenho_para_op`, pa.`observacao`, pa.`status_top` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$vetor_produto_acabado[$i]' LIMIT 1 ";
            $campos_gerais = bancos::sql($sql);

            //2) Busco o Custo desse PA na mesma Operação de Custo do PA ...
            $sql = "SELECT SUBSTRING_INDEX(f.`nome`, ' ', 1) AS nome, pac.`id_produto_acabado_custo`, pac.`qtde_lote`, pac.`peca_corte`, SUBSTRING(pac.`data_sys`, 1, 10) AS data_sys, DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y') AS data_atualizacao 
                    FROM `produtos_acabados_custos` pac 
                    LEFT JOIN `funcionarios` f ON f.`id_funcionario` = pac.`id_funcionario` 
                    WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                    AND `operacao_custo` = '".$campos_gerais[0]['operacao_custo']."' LIMIT 1 ";
            $campos_pa_custo    = bancos::sql($sql);
            $qtde_lote          = (count($campos_pa_custo[0]['qtde_lote']) == 1) ? $campos_pa_custo[0]['qtde_lote'] : 0;
            
            //Mudança realizada no dia 03/11/2014, antes esse código abaixo só feito pelo caminho Não Componente ...
            
            /*******************************************************************************************************/
            /****************************Código somente para fazer verificação de BLANKS****************************/
            /*******************************************************************************************************/
            //Esse lógica serve tanto p/ PI como PA, mas mesmo assim a Qtde de PIs se sobressai sobre PA, por isso que a Query do PI vem antes ...
            
            //1)Verifico se o PA tem algum PI que é do Grupo Blank na 3ª Etapa do Custo p/ buscar o seu preço e poder gerar Cotação ...
            $sql = "SELECT pp.`id_produto_insumo` 
                    FROM `pacs_vs_pis` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` AND pi.`id_grupo` = '22' 
                    WHERE pp.`id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            if(count($campos_pi) == 0) {
                //2) Verifico se o PA tem algum PA que tem a Discriminação BLANK em sua 7ª Etapa do Custo ...
                $sql = "SELECT pp.`id_produto_acabado` 
                        FROM `pacs_vs_pas` pp 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` AND pa.`discriminacao` LIKE '%BLANK%' 
                        WHERE `id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
                $campos_pa = bancos::sql($sql);
                if(count($campos_pa) == 1) $gerar_ops++;
            }else {
                $mostrar_usinagem = 1;
            }
            /*******************************************************************************************************/
            
            if(count($campos_pi) == 0) {//Se não achou PI na 3ª do Custo ...
                //3)Verifico se o PA tem algum PI na 6ª Etapa do Custo p/ buscar o seu preço e poder gerar Cotação ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `pacs_vs_pis_usis` 
                        WHERE `id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
                if(count($campos_pi) == 1) {
                    $qtde_pipas++;
                }else {
                    /**************************************************************/
                    /*Eram 2 arquivos antigamente e unifiquei em um só à partir de 
                    12/05/2016. Na lógica de Compra Produção nem sempre tinha que 
                    verificar se o PA é um PIPA, esse Controle só era feito com o 
                    Checkbox ou se a OC fosse I-R ou R, já na Pendência sempre 
                    tinha que verificar se o PA é um PIPA ...*/
                    /**************************************************************/
                    if($hdd_acao == 'COMPRA_PRODUCAO') {//Compra Produção ...
                        $executar_sql_pipa = 'NAO';//Default ...
                        if(!empty($checked_mostrar_oc_ii_nas_cotacoes)) {
                            $executar_sql_pipa = 'SIM';
                        }else {
                            //Somente se a OC for Industrial e a Sub OC Revenda ou a OC for Revenda que irá fazer o SQL abaixo ...
                            if(($campos_gerais[0]['operacao_custo'] == 0 && $campos_gerais[0]['operacao_custo_sub'] == 1) || $campos_gerais[0]['operacao_custo'] == 1) $executar_sql_pipa = 'SIM';
                        }
                    }else {//Pendências ...
                        $executar_sql_pipa = 'SIM';
                    }
                    /**************************************************************/
                    $executar_sql_pipa = 'NAO';//Default ...
                    if(!empty($checked_mostrar_oc_ii_nas_cotacoes)) {
                        $executar_sql_pipa = 'SIM';
                    }else {
                        //Somente se a OC for Industrial e a Sub OC Revenda ou a OC for Revenda que irá fazer o SQL abaixo ...
                        if(($campos_gerais[0]['operacao_custo'] == 0 && $campos_gerais[0]['operacao_custo_sub'] == 1) || $campos_gerais[0]['operacao_custo'] == 1) $executar_sql_pipa = 'SIM';
                    }
                    if($executar_sql_pipa == 'SIM') {
                        //Verifico se o PA é um PI que foi importado e está atrelado p/ buscar o seu preço e poder gerar Cotação ...
                        $sql = "SELECT `id_produto_insumo` 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                                AND `id_produto_insumo` IS NOT NULL 
                                AND `ativo` = '1' LIMIT 1 ";
                        $campos_pi = bancos::sql($sql);
                        if(count($campos_pi) == 1) $qtde_pipas++;
                    }
                }
            }else {
                $mostrar_msn_blank = 1;
                $qtde_pipas++;
            }

            //3) Verifico se o PA é da Família Componente ...
            if($campos_gerais[0]['id_familia'] == 23 || $campos_gerais[0]['id_familia'] == 24) {//Sim é Componente ...
                //Nessa caso que é Componente trago o Preço do Custo do PA ...
                $preco_produto  = custos::preco_custo_pa($vetor_produto_acabado[$i]);
            }else {//Não é da Família Componente ...
                /*Se encontrou um PI "PIPA" então busco o seu Preço na Lista de Preço de Compras 
                de seu Fornecedor Default ...*/
                if(count($campos_pi) == 1) {
                    //Aqui eu busco o Fornecedor Default do PI ...
                    $sql = "SELECT `id_fornecedor_default` 
                            FROM `produtos_insumos` 
                            WHERE `id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' 
                            AND `id_fornecedor_default` > '0' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_fornecedor_default  = bancos::sql($sql);
                    $id_fornecedor_default      = $campos_fornecedor_default[0]['id_fornecedor_default'];
                    //Aqui eu busco o Preço de Lista do PI e do Fornecedor default ...
                    $sql = "SELECT `preco`, `preco_exportacao`, `preco_faturado`, `lote_minimo_pa_rev` 
                            FROM `fornecedores_x_prod_insumos` 
                            WHERE `id_fornecedor` = '$id_fornecedor_default' 
                            AND `id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_lista   = bancos::sql($sql);
                    $preco_produto  = ($cmb_tipo_compra == 'N') ? $campos_lista[0]['preco'] : $campos_lista[0]['preco_exportacao'];

                    //A "qtde do Lote" só será sobreposta se a OC e Sub-OC forem diferentes de II ...
                    if($campos_gerais[0]['operacao_custo'] != 0 && $campos_gerais[0]['operacao_custo_sub'] != 0) {
                        $qtde_lote  = $campos_lista[0]['lote_minimo_pa_rev'];
                    }
                }else {
                    $id_fornecedor_default  = 0;
                    $array_valores  = vendas::preco_venda($vetor_produto_acabado[$i]);
                    $preco_produto  = $array_valores['preco_venda_medio_rs'];//Traz o Preço de Vendas ...
                }
            }
            
            //Desse PA Principal, eu busco a Quantidade de Peças por Embalagem da "Embalagem Default" ...
            $sql = "SELECT `pecas_por_emb` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$vetor_produto_acabado[$i]."' LIMIT 1 ";
            $campos_pecas_por_emb   = bancos::sql($sql);
            $pecas_por_embalagem    = (count($campos_pecas_por_emb) == 1) ? intval($campos_pecas_por_emb[0]['pecas_por_emb']) : 1;
            
            $resto_divisao = $vetor_qtde_a_produzir[$i] % $pecas_por_embalagem;//Verifica o Mod (Resto da Divisão) ...
            //Representa que não está compatível a Qtde de Peças / Embalagem ...
            if($resto_divisao != 0) $vetor_qtde_a_produzir[$i] = (intval($vetor_qtde_a_produzir[$i] / $pecas_por_embalagem) + 1) * $pecas_por_embalagem;
            
            if($hdd_acao == 'COMPRA_PRODUCAO') {//Compra Produção ...
                $qtde = (empty($txt_qtde[$i])) ? trim($vetor_qtde_a_produzir[$i]) : $txt_qtde[$i];
            }else {//Pendências ...
                $qtde = (empty($txt_qtde[$i])) ? trim($vetor_urgencia[$i]) : $txt_qtde[$i];
            }
//Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra 	= ($vetor_soma_compra_prod_todos_niveis[$i] < - ($vetor_soma_est_comp_todos_niveis[$i])) ? "<font color='red'><b>" : "<font color='black'>";
            
            if($hdd_acao == 'PENDENCIAS') {//Só nessa parte de Pendências que temos o tratamento com o campo "Qtde" se for Digitada uma Data de Embarque ...
                //Quando esse campo "Great" for Preenchido - nunca iremos descontar o programado ...
                if(!empty($txt_data_embarque)) {
                    /*Aqui eu busco os Pedidos da Great em Aberto que estejam com Importação atrelada 
                    e esta importação comece com as iniciais GH ...*/
                    $sql = "SELECT SUM(ip.`qtde`) AS qtde_compras_embarcadas 
                            FROM `itens_pedidos` ip 
                            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` < '2' 
                            INNER JOIN `importacoes` i ON i.`id_importacao` = p.`id_importacao` AND SUBSTRING(i.`nome`, 1, 2) = 'GH' 
                            WHERE ip.`id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' ";
                    $campos_qtde_compras_embarcadas = bancos::sql($sql);
                    if($mostrar_msn_blank == 1) {
                        /**************************Cálculo de Blanks em nosso Estoque**************************/
                        $sql = "SELECT SUM(ip.`qtde`) AS qtde_pedidos_em_aberto_great 
                                FROM `itens_pedidos` ip 
                                INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` < '2' 
                                WHERE ip.`id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' ";
                        $campos_pedidos_em_aberto_great = bancos::sql($sql);
                        $compras_nao_embarcadas         = $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'] - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                        $blanks_em_estoque              = $vetor_soma_compra_prod_todos_niveis[$i] - $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'];
                        //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente e a Qtde em Estoque ...
                        $qtde_necessaria                = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'] - $blanks_em_estoque;
                    }else {
                        //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente ...
                        $qtde_necessaria                = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                    }
                }else {
                    $qtde_necessaria                    = $qtde;
                }
            }else {//Compra Produção ...
                $qtde_necessaria                        = $qtde;
            }
            /********************************************************************/
            /******************Na hora em que se carrega a Tela******************/
            /********************************************************************/
            //Controle com a cor da Qtde em comparado a Qtde de Lote em 15% abaixo ou acima ...
            if(0.85 * $qtde_lote > $qtde_necessaria || $qtde_necessaria > 1.15 * $qtde_lote) {
                $style 			= 'background:red;color:white';
                $lote_diferente_custo 	= 'S';
            }else {
                $style 			= 'background:white;color:brown';
                $lote_diferente_custo 	= 'N';
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$indice;?>' value='<?=number_format($qtde_necessaria, 2, ',', '.');?>' onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular('<?=$indice;?>')" style="<?=$style;?>" size='10' maxlength='9' class='caixadetexto'>
            <input type='hidden' name='hdd_lote_diferente_custo[]' id='hdd_lote_diferente_custo<?=$indice;?>' value='<?=$lote_diferente_custo;?>'>
        </td>
        <td>
            <input type='text' name='txt_pecas_corte[]' id='txt_pecas_corte<?=$indice;?>' value="<?=$campos_pa_custo[0]['peca_corte'];?>" size='5' class='textdisabled' disabled>
        </td>
        <td>
            <?
                echo number_format($qtde_necessaria, 2, ',', '.');
                echo '<br/>('.$pecas_por_embalagem.' pçs / emb)';
                //Verificação de Item Faltante p/ o PA do Loop ...
                $estoque_produto            = estoque_acabado::qtde_estoque($vetor_produto_acabado[$i], 0);
                $status_estoque             = $estoque_produto[1];
                $qtde_pa_e_item_faltante    = $estoque_produto[10];
                $est_fornecedor             = $estoque_produto[12];
                $est_porto                  = $estoque_produto[13];
                if($qtde_pa_e_item_faltante > 0) echo '<br/><font color="red"><b>'.$qtde_pa_e_item_faltante.' F.I</b></font>';
            ?>
            <input type='hidden' name='hdd_pecas_por_emb[]' id='hdd_pecas_por_emb<?=$indice;?>' value="<?=$pecas_por_embalagem;?>">
            <input type='hidden' name='hdd_neces_compra_prod[]' id='hdd_neces_compra_producao<?=$indice;?>' value="<?=$qtde_necessaria;?>">
        </td>
        <td>
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos_gerais[0]['operacao_custo'] == 0) {//Industrial
            ?>
            <img src='../../../imagem/icones/calculadora3.jpg' title='Visualizar PI(s)' style='cursor:help' onclick="visualizar_pis('<?=$vetor_produto_acabado[$i];?>', document.getElementById('txt_qtde<?=$i?>').value)" width='20' height='20'>
            &nbsp;
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Industrial' style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Revenda' style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src='../../../imagem/menu/alterar.png' title="Visualizar Custo" alt='Visualizar Custo' border='0'>
            </a>
            <!--/****************************************************************/-->
            <img src = '../../../imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="html5Lightbox.showLightbox(7, '../../classes/producao/visualizar_compra_producao.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>')">
            &nbsp;
            <img src = '../../../imagem/ferramenta.png' border='0' title='Substituir Estoque' style='cursor:pointer' onclick="opcoes_produto_acabado('<?=$vetor_produto_acabado[$i];?>', '<?=$status_estoque;?>')">
            &nbsp;
            <input type='text' name='txt_qtde_lote[]' value='<?=$qtde_lote;?>' id='txt_qtde_lote<?=$indice;?>' size='5' class='textdisabled' disabled>
            <?
                echo ' = '.number_format($qtde_lote / $vetor_soma_mmv_todos_niveis[$i], 1, ',', '.').' MMV Tot';
                if(!empty($campos_gerais[0]['observacao'])) {//Só irá exibir esta linha quando existir Observação ...
                    echo '<br><b>OBS: </b>'.$campos_gerais[0]['observacao'];
                }
                $preco_maximo_custo_fat_rs = custos::preco_custo_pa($vetor_produto_acabado[$i]);
                /*Verifico se existe na 4ª Etapa do Custo do PA uma máquina chamada Tx Financ Estocagem, 
                que tem por objetivo calcular quando o Lote de Produção é maior do que 3 meses MMV ...*/
                $sql = "SELECT m.`custo_h_maquina`, pm.`tempo_hs` 
                        FROM `pacs_vs_maquinas` pm 
                        INNER JOIN `maquinas` m ON m.`id_maquina` = pm.`id_maquina` 
                        WHERE pm.id_produto_acabado_custo = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' 
                        AND pm.`id_maquina` = '40' LIMIT 1 ";
                $campos_etapa4  = bancos::sql($sql);
                if(count($campos_etapa4) == 1) {
                    $total_rs       = ($campos_etapa4[0]['tempo_hs'] * $campos_etapa4[0]['custo_h_maquina'] * $fator_custo_4) / $qtde_lote;
                    echo '<br><b>TX = R$ </b>'.number_format($total_rs, 2, ',', '.').' -> '.number_format($total_rs / ($preco_maximo_custo_fat_rs - $total_rs) * 100, 1, ',', '.').' %';
                }
                /*A partir dessa Data 01/01/2014 nós começamos a corrigir os Lotes dos Custos levando em Conta a 
                Taxa Financeira de Estocagem ...*/
                $color = ($campos_pa_custo[0]['data_sys'] < '2014-01-01') ? 'red' : '';
                //Aqui eu apresento dados de Data e de quem foi o último que fez a Alteração no Custo desse PA ...
                echo '<br/><font color="'.$color.'"><b>'.$campos_pa_custo[0]['nome'].' - '.$campos_pa_custo[0]['data_atualizacao'].'</b></font>';
            ?>
        </td>
        <td>
        <?
            if($campos_gerais[0]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos_gerais[0]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
        ?>
            <a href = '../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_gerais[0]['id_produto_acabado'];?>' class='html5lightbox'>
        <?
            if($campos_gerais[0]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos_gerais[0]['operacao_custo_sub'] == 0) {
                    echo '-I';
                    $gerar_ops++;
                    $id_produto_acabado_loop = $campos_gerais[0]['id_produto_acabado'];
                }else if($campos_gerais[0]['operacao_custo_sub'] == 1) {
                    echo '-R';
                    if($mostrar_msn_blank == 1) {
                        $gerar_ops++;
                        $id_produto_acabado_loop = $campos_gerais[0]['id_produto_acabado'];
                    }
                }else {
                    echo '-';
                }
                /*Somente nesse Tipo de OC = 'Industrial' que eu verifico se esse PA do Loop possui 
                em sua 7ª Etapa um PA que não seja da Família "Componente ou Mão de Obra" ...*/
                $sql = "SELECT pa.`referencia` 
                        FROM `pacs_vs_pas` pp 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
                        WHERE pp.`id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' ";
                $campos_etapa7 = bancos::sql($sql);
                $linhas_etapa7 = count($campos_etapa7);
                if($linhas_etapa7 == 1) echo '<br/><font color="red"><b>'.$campos_etapa7[0]['referencia'].'</b></font>';
            }else if($campos_gerais[0]['operacao_custo'] == 1) {
                echo 'R';
                /*Nesse Tipo de OC = 'Revenda', eu trago a Razão Social do "Fornecedor Default" 
                que já foi buscado anteriormente mais acima ...*/
                $sql = "SELECT IF(`nomefantasia` = '', 'VAZIO', `nomefantasia`) AS fornecedor_default 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
                //$campos_fornecedor = bancos::sql($sql);
                if(count($campos_fornecedor) == 1) echo '<br/><font color="red"><b>'.$campos_fornecedor[0]['fornecedor_default'].'</b></font>';
            }else {
                echo '-';
            }
        ?>
            </a>
        <?
            if(in_array($campos_gerais[0]['id_gpa_vs_emp_div'], $vetor_machos_manuais_warrior)) {
                //Aqui eu busco o PA com a mesma referência acrescida da Letra "T" que representa NVO/TDC ...
                $vetor_referencia   = explode('-', $campos_gerais[0]['referencia']);
                $referencia_nvo_tdc = $vetor_referencia[0].'T-'.$vetor_referencia[1];
                
                //Busco o id_produto_acabado da $referencia_nvo_tdc encontrada acima ...
                $sql = "SELECT `id_produto_acabado`, `id_produto_insumo` 
                        FROM `produtos_acabados` 
                        WHERE `referencia` = '$referencia_nvo_tdc' LIMIT 1 ";
                $campos_referencia_nvo_tdc = bancos::sql($sql);
                if(count($campos_referencia_nvo_tdc) == 1) {
                    //Busco dados de Estoque do $campos_referencia_nvo_tdc[0]['id_produto_acabado'] encontrado acima ...
                    $estoque_produto_nvo_tdc    = estoque_acabado::qtde_estoque($campos_referencia_nvo_tdc[0]['id_produto_acabado'], 0);
                    $producao_nvo_tdc           = $estoque_produto_nvo_tdc[2];
                    $est_comp_nvo_tdc           = $estoque_produto_nvo_tdc[8];
                    $est_fornecedor_nvo_tdc     = $estoque_produto_nvo_tdc[12];
                    $est_porto_nvo_tdc          = $estoque_produto_nvo_tdc[13];
                    
                    $compra_nvo_tdc             = estoque_acabado::compra_producao($campos_referencia_nvo_tdc[0]['id_produto_acabado']);
                    $compra_producao_nvo_tdc    = $compra_nvo_tdc + $producao_nvo_tdc;
                    
                    //Se esse PA possui um PI, ou seja "PIPA" então busco o seu fornecedor Default ...
                    $id_fornecedor_default_tdc  = custos::preco_custo_pi($campos_referencia_nvo_tdc[0]['id_produto_insumo'], 0, 1);
                    
                    $sql = "SELECT `preco_faturado` 
                            FROM `fornecedores_x_prod_insumos` 
                            WHERE `id_fornecedor` = '$id_fornecedor_default_tdc' 
                            AND `id_produto_insumo` = '".$campos_referencia_nvo_tdc[0]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_lista_tdc   = bancos::sql($sql);
                    echo '<br/><font title="Preço Fat Nac: Warrior R$ '.number_format($campos_lista[0]['preco_faturado'], 2, ',', '.').' - TDC R$ '.number_format($campos_lista_tdc[0]['preco_faturado'], 2, ',', '.').'" style="cursor:help" color="darkgreen"><b>'.$referencia_nvo_tdc.'</b></font>';
                }else {
                    echo '<br/><font title="Preço Fat Nac: Warrior R$ '.number_format($campos_lista[0]['preco_faturado'], 2, ',', '.').'" style="cursor:help" color="darkgreen"><b>S/SIMILAR</b></font>';
                }
            }
            
            if(in_array($campos_gerais[0]['id_gpa_vs_emp_div'], $vetor_machos_manuais_heinz)) {
                //Nessa situação substituo a "H" pela "T" que representa NVO/TDC ...
                $referencia_nvo_tdc = str_replace('H-', 'T-', $campos_gerais[0]['referencia']);
                
                echo '<br/><font color="darkgreen"><b>'.$referencia_nvo_tdc.'</b></font>';
                
                //Busco o id_produto_acabado da $referencia_nvo_tdc encontrada acima ...
                $sql = "SELECT `id_produto_acabado` 
                        FROM `produtos_acabados` 
                        WHERE `referencia` = '$referencia_nvo_tdc' LIMIT 1 ";
                $campos_referencia_nvo_tdc = bancos::sql($sql);
                if(count($campos_referencia_nvo_tdc) == 1) {
                    //Busco dados de Estoque do $campos_referencia_nvo_tdc[0]['id_produto_acabado'] encontrado acima ...
                    $estoque_produto_nvo_tdc    = estoque_acabado::qtde_estoque($campos_referencia_nvo_tdc[0]['id_produto_acabado'], 0);
                    $producao_nvo_tdc           = $estoque_produto_nvo_tdc[2];
                    $est_comp_nvo_tdc           = $estoque_produto_nvo_tdc[8];
                    $est_fornecedor_nvo_tdc     = $estoque_produto_nvo_tdc[12];
                    $est_porto_nvo_tdc          = $estoque_produto_nvo_tdc[13];
                    
                    $compra_nvo_tdc             = estoque_acabado::compra_producao($campos_referencia_nvo_tdc[0]['id_produto_acabado']);
                    $compra_producao_nvo_tdc    = $compra_nvo_tdc + $producao_nvo_tdc;
                }
            }
        ?>
        </td>
        <td>
        <?
            echo $font_compra.number_format($vetor_soma_compra_prod_todos_niveis[$i], 0, '', '.');
            
            if($vetor_soma_qtde_oes_todos_niveis[$i] > 0) echo '<br/><font color="purple"><b>(OE='.number_format($vetor_soma_qtde_oes_todos_niveis[$i], 0, '', '.').')</b></font>';
            
            $color = ($compra_producao_nvo_tdc < 0) ? 'red' : 'darkgreen';
            echo '<br/><br/><font color="'.$color.'"><b>'.number_format($compra_producao_nvo_tdc, 2, ',', '.').'</b></font>';
        ?>
            <!--Esse parâmetro será utilizado por Cotações em que o PI é um PIPA ...-->
            <input type='hidden' name='hdd_compra_producao[]' value='<?=$vetor_soma_compra_prod_todos_niveis[$i];?>' id='hdd_compra_producao<?=$indice;?>'>
        </td>
        <td>
        <?
            if($vetor_soma_est_comp_todos_niveis[$i] < 0) {
                echo "<font color='red'>".number_format($vetor_soma_est_comp_todos_niveis[$i], 0, ',', '.')."</font>";
            }else {
                echo number_format($vetor_soma_est_comp_todos_niveis[$i], 0, ',', '.');
            }
            
            $color = ($est_comp_nvo_tdc < 0) ? 'red' : 'darkgreen';
            echo '<br/><br/><font color="'.$color.'"><b>'.number_format($est_comp_nvo_tdc, 2, ',', '.').'</b></font>';
        ?>
            <!--Esse parâmetro será utilizado por Cotações em que o PI é um PIPA ...-->
            <input type='hidden' name='hdd_estoque_comprometido[]' value='<?=$vetor_soma_est_comp_todos_niveis[$i];?>' id='hdd_estoque_comprometido<?=$indice;?>'>
        </td>
        <td>
        <?
            echo segurancas::number_format($est_fornecedor, 2, '.');
            
            $color = ($est_fornecedor_nvo_tdc < 0) ? 'red' : 'darkgreen';
            echo '<br/><br/><font color="'.$color.'"><b>'.number_format($est_fornecedor_nvo_tdc, 2, ',', '.').'</b></font>';
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($est_porto, 2, '.');
            
            $color = ($est_porto_nvo_tdc < 0) ? 'red' : 'darkgreen';
            echo '<br/><br/><font color="'.$color.'"><b>'.number_format($est_porto_nvo_tdc, 2, ',', '.').'</b></font>';
        ?>
        </td>
        <td>
        <?
            if($vetor_soma_est_prog_todos_niveis[$i] < 0) {
                echo "<font color='red'>".segurancas::number_format($vetor_soma_est_prog_todos_niveis[$i], 2, '.')."</font>";
            }else {
                echo segurancas::number_format($vetor_soma_est_prog_todos_niveis[$i], 2, '.');
            }
        ?>
        </td>
        <td bgcolor='#C0C0C0'>
            <?=number_format($vetor_soma_mmv_todos_niveis[$i], 1, ',', '.');?>
            &nbsp;
            <?
                $title = '06 -> '.number_format(6 * $vetor_soma_mmv_todos_niveis[$i], 0, ',', '.').'  |  ';
                $title.= '12 -> '.number_format(12 * $vetor_soma_mmv_todos_niveis[$i], 0, ',', '.').'  |  ';
                $title.= '18 -> '.number_format(18 * $vetor_soma_mmv_todos_niveis[$i], 0, ',', '.').'  |  ';
                $title.= '24 -> '.number_format(24 * $vetor_soma_mmv_todos_niveis[$i], 0, ',', '.');
            ?>
            <img src='../../../imagem/bloco_negro.gif' title='<?=$title;?>' width='8' height='8' style='cursor:pointer' border='0'>
            <!--Esse parâmetro será utilizado por Cotações em que o PI é um PIPA ...-->
            <input type='hidden' name='hdd_soma_mmv_todos_niveis[]' value='<?=$vetor_soma_mmv_todos_niveis[$i];?>' id='hdd_soma_mmv_todos_niveis<?=$indice;?>'>
        </td>
        <td>
            <?=number_format($vetor_mmv_corrigido_total[$i], 1, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_total_compra_producao_pa_nivel1[$i], 1, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos_gerais[0]['referencia'];?>
            <input type='hidden' name='hdd_referencia[]' value='<?=$campos_gerais[0]['referencia'];?>' id='hdd_referencia<?=$indice;?>' size='5'>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('detalhes.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                <?=intermodular::pa_discriminacao($vetor_produto_acabado[$i]);?>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <?
/*Se existir algum desenho anexado p/ essa P.A., então eu exibo essa palavra de desenho 
junto desse ícone de Impressora ...*/
                if(!empty($campos_gerais[0]['desenho_para_op'])) {
            ?>
                <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' title='Existe Desenho anexado p/ este P.A' style='cursor:help' color='darkgreen' size='1'>
                    - <b>DESENHO</b>
                </font>
                <img src = '../../../imagem/impressora.gif' border='0' title='Existe Desenho anexado p/ este P.A' alt='Existe Desenho anexado p/ este P.A' style='cursor:pointer'>
            <?
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario[]' value="<?=number_format($preco_produto, 2, ',', '.');?>" id='txt_preco_unitario<?=$indice;?>' size='9' maxlength='8' class='textdisabled' disabled>
            <!--Esse preço em oculto, eu utilizo para os cálculos em JavaScript quando acrescento desconto ...-->
            <input type='hidden' name='hdd_preco_unitario[]' value="<?=number_format($preco_produto, 2, ',', '.');?>" id='hdd_preco_unitario<?=$indice;?>'>
            <?
                if($mostrar_msn_blank == 1) {//Existe Blank ...
                    echo '<font color="red"><b>BLANK</b></font>';
            ?>
            <input type='hidden' name='txt_blank[]' id='txt_blank<?=$indice;?>'>
            <?
                }
                //Existe Usinagem ...
                if($mostrar_usinagem == 1) echo '<font color="red"><b>USINAGEM</b></font>';
            ?>
        </td>
        <td>
            <?
                $preco_total = $qtde * $preco_produto;
                $total_geral+= $preco_total;
            ?>
            <input type='text' name='txt_preco_total[]' id='txt_preco_total<?=$indice;?>' value='<?=number_format($preco_total, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
            <?if($id_fornecedor_default == 0) echo '<font color="red" title="Sem Fornecedor" style="cursor:help"><b>S/ FORN</b></font>';?>
        </td>
        <td>
            <a href="javascript:nova_janela('calculo_desconto_ideal_compra.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&mlm_total=<?=number_format($vetor_total_mlm_todos_niveis[$i], 1, ',', '.');?>', 'CALCULO_DESCONTO_IDEAL_COMPRA', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Calcular Desconto Ideal p/ Compra" class='link'>
                <?=number_format($vetor_total_mlm_todos_niveis[$i], 1, ',', '.');?>
            </a>
            <!--Aqui eu guardo o ID do produto Insumo caso eu deseje gerar uma Cotação p/ este-->
            <input type='hidden' name='hdd_produto_insumo[]' value='<?=$campos_pi[0]['id_produto_insumo'];?>'>
            <?
                if($hdd_acao == 'COMPRA_PRODUCAO') {//Compra Produção ...
            ?>
            <!--Aqui eu guardo o ID do produto Acabado caso eu deseje gerar uma OP p/ este-->
            <input type='hidden' name='hdd_produto_acabado[]' id='hdd_produto_acabado<?=$indice;?>' value='<?=$vetor_produto_acabado[$i];?>'>
            <?
                }else {//Pendências ...
            ?>
            <!--Aqui eu guardo o ID PA caso eu deseje gerar uma Impressão ...-->
            <input type='hidden' name='hdd_produto_acabado[]' value="<?=$vetor_produto_acabado[$i].'|'.$vetor_urgencia[$i].'|'.$vetor_soma_compra_prod_todos_niveis[$i].'|'.$vetor_soma_est_comp_todos_niveis[$i].'|'.$vetor_soma_est_prog_todos_niveis[$i].'|'.$vetor_mmv_corrigido_total[$i].'|'.$vetor_prioridade[$i];?>">
            <?
                }
            ?>
            <input type='hidden' name='hdd_mlm[]' value='<?=$vetor_total_mlm_todos_niveis[$i];?>'>
        </td>
        <td>
            <?=number_format($vetor_ec_pa_princ[$i], 1, ',', '.');?>
        </td>
        <td>
            <?=number_format($vetor_ec_p_x_meses_pa_princ[$i], 1, ',', '.');?>
        </td>
        <td>
            <?=$vetor_prioridade[$i];?>
        </td>
    </tr>
<?
            //Já deleto essas variáveis p/ não herdar esses valores no próximo Loop ...
            unset($producao_nvo_tdc);
            unset($est_comp_nvo_tdc);
            unset($est_fornecedor_nvo_tdc);
            unset($est_porto_nvo_tdc);
            unset($compra_nvo_tdc);
            unset($compra_producao_nvo_tdc);
            unset($id_fornecedor_default_tdc);

            $indice++;
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <?
                if($hdd_acao == 'COMPRA_PRODUCAO') {//Compra Produção ...
                    if($qtde_pipas > 0) {//Só pode gerar Cotação, se existir pelo menos 1 PI dentre esses PAS ...
            ?>
            <input type='button' name='cmd_gerar_cotacao' value='Gerar Cotação' title='Gerar Cotação' onclick='gerar_cotacao()' style='color:darkgreen' class='botao'>
            <?
                    }
                    if($gerar_ops > 0) {//Só pode gerar OP, se existir pelo menos 1 PA com OC II ...
            ?>
            <input type='button' name='cmd_gerar_op' value='Gerar OP(s)' title='Gerar OP(s)' onclick='gerar_ops()' style='color:darkblue' class='botao'>
            <?
                    }
            ?>
            <input type='button' name='cmd_consultar_cotacao' value='Consultar Cotação' title='Consultar Cotação' onclick="html5Lightbox.showLightbox(7, '../../classes/cotacao/consultar.php')" style='color:red' class='botao'>
            <?
                }else {//Pendências ...
            ?>
            <input type='button' name='cmd_gerar_impressao' value='Gerar Impressão' title='Gerar Impressão' onclick='gerar_impressao()' style='color:darkgreen' class='botao'>
            <?
                }
            ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
        <td>
            Total =>
        </td>
        <td>
            <input type='text' name='txt_total_geral' value='<?=number_format($total_geral, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='21'>
            <div id='div_cotacao' style="background-color: #FFFFFF; position:relative; left:0px; top:3px; height:42px; width:800px; border-width:0px;border-style:solid;border-color:#000000; color:darkblue; font:bold 16px verdana" align='center'></div>
        </td>
    </tr>
</table>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='hdd_cotacao'>
<input type='hidden' name='hdd_vetor_compra_producao' value='<?=$_POST['hdd_vetor_compra_producao'];?>'>
<input type='hidden' name='hdd_vetor_pendencia' value='<?=$_POST['hdd_vetor_pendencia'];?>'>
<input type='hidden' name='txt_fator_correcao_mmv' value='<?=str_replace(',', '.', $txt_fator_correcao_mmv);?>'>
<input type='hidden' name='txt_qtde_meses' value='<?=str_replace(',', '.', $txt_qtde_meses);?>'>
<input type='hidden' name='hdd_acao' value='<?=$hdd_acao;?>'>
<!--*************************************************************************-->
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>

* Quando importamos o PA para PI para tabelarmos um Preço do Fornecedor desse Produto Acabado, 
por exemplo (CR-, MC-419) não deve aparecer o botão gerar cotação nessa instância.

* A quantidade programada só entra no cálculo de Pendências. 

* O campo Compra Produção / Filhos só aparece se o PA for componente ou referência = 'FIX' (não estamos levando em Conta 
a Compra / Produção dos Netos).
</pre>