<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca � utilizada dentro da Biblioteca 'custos' ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) INCLU�DO(S) COM SUCESSO.</font>";

/**********************************************************************************************************/
/***********************************************Interpola��o***********************************************/
/**********************************************************************************************************/
/*Na data do dia 21/11/2013 trabalh�vamos com uma interpola��o +/- 5x a Qtde e mudamos para +/- 2x porque que gerava 
muito erro de Custo ...*/
$interpolacao = intval(genericas::variavel(60));
/**********************************************************************************************************/

///Tratamento com a vari�vel que vem por par�metro ...
$id_orcamento_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_orcamento_venda'] : $_GET['id_orcamento_venda'];

/*********************************Procedimento p/ Incluir os Itens*****************************************/
if(isset($_POST['chkt_produto_acabado'])) {
    $data_sys = date('Y-m-d H:i:s');
    $situacao_orcamento = vendas::situacao_orcamento($id_orcamento_venda);

    if($situacao_orcamento == 'N') {//Or�amento Descongelado ent�o pode estar sendo manipulado ...
        for($i = 0; $i < count($_POST['chkt_produto_acabado']); $i++) {
//Busca de alguns dados do PA que ser�o utilizados + abaixo, independente do caso ...
            $sql = "SELECT gpa.`prazo_entrega`, ged.`id_empresa_divisao`, pa.`operacao_custo` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' LIMIT 1 ";
            $campos_dados_gerais = bancos::sql($sql);
            
//Aqui eu mudo o status de um P.A. q foi migrado, p/ 0, p/ q possa estar se fazendo o custo desse P.A.
            $sql = "UPDATE `produtos_acabados` SET `pa_migrado` = '0' where `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' LIMIT 1 ";
            bancos::sql($sql);//Verifica se j� foi incluido aquele item no or�amento
            vendas::verificar_pa_custo($_POST['chkt_produto_acabado'][$i]);//caso ele(ESP) ou os atrelados(ESP) a ele tiverem passado de X dias bloqueia o PA - Variavel "43" ...
                       
            $estoque_pa         = estoque_acabado::qtde_estoque($_POST['chkt_produto_acabado'][$i]);
            $qtde_disponivel    = $estoque_pa[3];
            $racionado          = $estoque_pa[5];
            $qtde_fornecedor    = $estoque_pa[12];
            $qtde_porto         = $estoque_pa[13];

            /******************************************************************/
            /********************Regra p/ Prazos de Entrega********************/
            /******************************************************************/
            if($racionado == 1) {//PA Racionado ...
                $prazo_entrega = 'S';
            }else {//PA em abund�ncia, rsrs ...
                //Aqui eu verifico se o PA desse Or�amento � ESP ...
                $sql = "SELECT referencia 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' LIMIT 1 ";
                $campos_referencia = bancos::sql($sql);
                if($campos_referencia[0]['referencia'] == 'ESP') {//ESP ...
                    $prazo_entrega = $campos_dados_gerais[0]['prazo_entrega'];//Prazo do Grupo ...
                }else {//Normal de Linha ...
                    if($_POST['txt_quantidade'][$i] <= $qtde_disponivel) {//Imediato ...
                        $prazo_entrega = 'I';
                    }else if(($_POST['txt_quantidade'][$i] > $qtde_disponivel) && $qtde_disponivel > 0) {//Parcial ...
                        $prazo_entrega = 'P';
                    }else if($_POST['txt_quantidade'][$i] <= $qtde_fornecedor) {
                        $prazo_entrega = 3;
                    }else if($_POST['txt_quantidade'][$i] <= ($qtde_fornecedor + $qtde_porto)) {
                        $prazo_entrega = ($qtde_fornecedor > 0) ? 'P3' : 45;//Parcial 3 ...
                    }else if($_POST['txt_quantidade'][$i] > $qtde_porto && $campos_dados_gerais[0]['id_empresa_divisao'] == 9) {//Divis�o TDC ...
                        $prazo_entrega = ($qtde_porto > 0) ? 'P45' : 120;//Parcial 45 ...
                    }else {//N�o tem nada em Estoque p/ Entregar, outras divis�es, prazo do Grupo ...
                        $prazo_entrega = $campos_dados_gerais[0]['prazo_entrega'];
                    }
                }
            }
            /******************************************************************/
            
            //Aqui eu busco o $id_cliente do Or�amento atrav�s do $id_orcamento_venda ...
            $sql = "SELECT `id_cliente` 
                    FROM `orcamentos_vendas` 
                    WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            //Aqui eu busco o Representante do Cliente no Or�amento na Respectiva Empresa Divis�o do PA ...
            $sql = "SELECT `id_representante` 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '".$campos_cliente[0]['id_cliente']."' 
                    AND `id_empresa_divisao` = '".$campos_dados_gerais[0]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) == 0) {//N�o encontrou nenhum Representante na Query acima ...
                exit('REPRESENTANTE N�O ENCONTRADO, VERIFIQUE SE O REPRESENTANTE ESTE CLIENTE PARA ESTA DIVIS�O !');
            }
            //Aqui eu insiro o Item no Or�amento ...
            $sql = "INSERT INTO `orcamentos_vendas_itens` (`id_orcamento_venda_item`, `id_orcamento_venda`, `id_produto_acabado`, `id_representante`, `qtde`, `desc_cliente`, `prazo_entrega`, `prazo_entrega_tecnico`, `data_sys`) VALUES (NULL, '$id_orcamento_venda', '".$_POST['chkt_produto_acabado'][$i]."', '".$campos_representante[0]['id_representante']."', '".$_POST['txt_quantidade'][$i]."', '0', '$prazo_entrega', 'I', '$data_sys') ";
            bancos::sql($sql);
            $id_orcamento_venda_item = bancos::id_registro();
/******************************Verifica��o p/ Retorno de Mensagem desse PA******************************/
//Verifica��o p/ ver se esse PA do Or�amento � 'ESP' com Custo n�o Liberado ...
            $sql = "SELECT `discriminacao` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' 
                    AND `referencia` = 'ESP' 
                    AND `status_custo` = '0' ";
            $campos_custo = bancos::sql($sql);
            if(count($campos_custo) == 1) {//Se for ..., insiro uma mensagem de aviso na tab.
                //Antes de inserir a msn de aviso na tabela, verifico se tivemos algum func. que solicitou uma provis�o ...
                $sql = "SELECT `id_login_novos_pas` 
                        FROM `orcamentos_vendas` 
                        WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
                        AND `id_login_novos_pas` > '0' LIMIT 1 ";
                $campos_login       = bancos::sql($sql);
                /*Se sim, uma futura mensagem ir� aparecer p/ esse Funcion�rio no mural, do contr�rio ir� aparecer uma msn 
                p/ o usu�rio que est� logado no momento Inserindo os PA(s) Especiais dentro do Or�amento ...*/
                $id_login_mensagem  = (count($campos_login) == 1) ? $campos_login[0]['id_login_novos_pas'] : $_SESSION['id_login'];
                
                $frase = 'O Custo do Produto '.$campos_custo[0]['discriminacao'].' do Or�amento N.� <a href="../modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda='.$id_orcamento_venda.'">'.$id_orcamento_venda.'</a> foi (Liberado).';
                $sql = "INSERT INTO `mensagens_esps` (`id_mensagem_esp`, `id_orcamento_venda_item`, `id_login`, `mensagem`, `data_sys`) VALUES (NULL, '$id_orcamento_venda_item', '$id_login_mensagem', '$frase', '$data_sys') ";
                bancos::sql($sql);
            }
/*******************************************************************************************************/
/***********************************Corrigindo Campos de ML Est do PI***********************************/
/*******************************************************************************************************/
            if($campos_dados_gerais[0]['operacao_custo'] == 1) {//Se a OC do PA = 'Revenda' ...
//Aqui verifico se o PA � um PI "PIPA" para poder executar a fun��o abaixo ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pipa = bancos::sql($sql);
                if(count($campos_pipa) == 1) {//E se o PA = 'PIPA' ...
                    //Aqui eu busco a �ltima Data de Atualiza��o de ML Est do PI ...
                    $sql = "SELECT `data_ultima_atualizacao_ml_est` 
                            FROM `produtos_insumos` 
                            WHERE `id_produto_insumo` = '".$campos_pipa[0]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_data_ultima_atualizacao = bancos::sql($sql);
                    /*Se a Qtde de "dias_passados" for superior a 60 ent�o devo fazer uma nova atualiza��o 
                    em alguns campos de ML Est do PI, porque isso representa que n�o h� compras h� um certo 
                    tempo desse PI, manipula��o de Estoque Acabado, etc ...*/
                    $diferenca_data     = data::diferenca_data($campos_data_ultima_atualizacao[0]['data_ultima_atualizacao_ml_est'], date('Y-m-d'));
                    $dias_passados       = $diferenca_data[0];
                    if($dias_passados >= 60) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_pipa[0]['id_produto_insumo']);
                }
            }
/*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item, 'S');
            //Aqui eu atualizo a ML Est do Iem do Or�amento ...
            custos::margem_lucro_estimada($id_orcamento_venda_item);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($id_orcamento_venda, $id_orcamento_venda_item);
        }
    }
    //Significa que o usu�rio desejou al�m de incluir um Item no Or�amento, ir diretamente p/ a Tela de Alterar ...
    if(empty($_POST['hdd_incluir_permanecer'])) {
        //Aqui verifica novamente a qtde de itens existentes no Or�amento atual, porque acabei de incluir + item(ns) ...
        $sql = "SELECT COUNT(`id_orcamento_venda_item`) AS total_itens_orcamentos 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
        $campos                 = bancos::sql($sql);
        $total_itens_orcamentos = $campos[0]['total_itens_orcamentos'];
    }
?>
    <Script Language = 'JavaScript'>
        var hdd_incluir_permanecer = eval('<?=$_POST['hdd_incluir_permanecer'];?>')
        if(hdd_incluir_permanecer == 1) {//Incluir - significa que o usu�rio prefiriu permanecer na mesma Tela ...
            window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/incluir_lote.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&valor=1&hdd_incluir_permanecer=<?=$_POST['hdd_incluir_permanecer'];?>'
        }else {//Alterar ...
            //Aqui eu passo a posi��o como sendo do 1� Item que acabou de ser incluso na �ltima remessa ...
            window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/alterar.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&posicao=<?=$total_itens_orcamentos;?>'    
        }
    </Script>
<?
}
/**********************************************************************************************************/

//Aqui verifica a qtde de itens existentes do Or�amento atual ...
$sql = "SELECT COUNT(`id_orcamento_venda_item`) AS total_itens_orcamentos 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
$campos = bancos::sql($sql);
$total_itens_orcamentos = $campos[0]['total_itens_orcamentos'];

//N�o pode prosseguir, pois excedeu o n�mero de itens nesse Or�amento ...
if($total_itens_orcamentos > 100) {
?>
    <Script Language = 'JavaScript'>    
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESTE OR&Ccedil;AMENTO !')
    </Script>
<?
    exit;
}

//Aqui eu verifico a UF do Cliente para evitar de o usu�rio cadastrar itens no Or�amento com os Impostos de IPI/ ICMS / ICMS ST errados ...
$sql = "SELECT c.`id_cliente`, c.`id_pais`, c.`id_uf` 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
$campos_cliente = bancos::sql($sql);
if($campos_cliente[0]['id_pais'] == 31) {//Essa verifica��o s� ser� feita quando o Cliente for do Brasil ...
    if($campos_cliente[0]['id_uf'] == '') {//Se estiver sem a UF preenchida ...
?>
    <Script Language = 'JavaScript'>    
        alert('ESTE CLIENTE EST� SEM A UNIDADE FEDERAL PREENCHIDA !!!\n\nACERTE ESSA INFORMA��O PARA QUE N�O SEJAM CADASTRADO(S) ITEM(NS) COM DADO(S) DE IMPOSTO(S) ERRADO(S) !')
        window.location = '../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos_cliente[0]['id_cliente'];?>&nao_exibir_menu=1'
    </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/pecas_por_embalagem.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'incluir_itens_orcamento.js'></Script>
<Script Language = 'JavaScript'>
function verificar_teclas(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13) pesquisar_itens_incluir_lote()//Se Enter faz a Consulta ...
    }else {
        if(event.which == 13) pesquisar_itens_incluir_lote()//Se Enter faz a Consulta ...
    }
}

function validar_itens(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13) {//Se Enter faz a Consulta ...
            /*A maioria dos vendedores, preferem que ao incluir um Item, o Sistema ainda permane�a na mesma tela de 
            Filtro para Incluir Novos Itens ao inv�s de ir para o Alterar e colocar o Pre�o ...*/
            document.form.hdd_incluir_permanecer.value = 1
            return validar()
        }
    }else {
        if(event.which == 13) {//Se Enter faz a Consulta ...
            /*A maioria dos vendedores, preferem que ao incluir um Item, o Sistema ainda permane�a na mesma tela de 
            Filtro para Incluir Novos Itens ao inv�s de ir para o Alterar e colocar o Pre�o ...*/
            document.form.hdd_incluir_permanecer.value = 1
            return validar()
        }
    }
}

function pesquisar_itens_incluir_lote() {
    for(var i = 0; i < document.form.txt_referencia.value.length; i++) {
        //Transformo o caract�r % em "|", pq o Mysql ignora tudo que foi digitado pelo usu�rio a partir desse caract�r ...
        if(document.form.txt_referencia.value.charAt(i) == '%')     document.form.txt_referencia.value = document.form.txt_referencia.value.replace('%', '|')
    }
        
    for(var i = 0; i < document.form.txt_discriminacao.value.length; i++) {
        //Transformo o caract�r % em "|", pq o Mysql ignora tudo que foi digitado pelo usu�rio a partir desse caract�r ...
        if(document.form.txt_discriminacao.value.charAt(i) == '%')  document.form.txt_discriminacao.value = document.form.txt_discriminacao.value.replace('%', '|')
    }
    
    for(var i = 0; i < document.form.txt_codigo_produto_cliente.value.length; i++) {
        //Transformo o caract�r % em "|", pq o Mysql ignora tudo que foi digitado pelo usu�rio a partir desse caract�r ...
        if(document.form.txt_codigo_produto_cliente.value.charAt(i) == '%')  document.form.txt_codigo_produto_cliente.value = document.form.txt_codigo_produto_cliente.value.replace('%', '|')
    }
    ajax('pesquisar_itens_incluir_lote.php?id_orcamento_venda=<?=$id_orcamento_venda;?>', 'pesquisar_itens_incluir_lote')
    
    document.form.txt_referencia.value              = ''
    document.form.txt_discriminacao.value           = ''
    document.form.txt_codigo_produto_cliente.value  = ''
    document.form.txt_referencia.focus()
}

function provisionar_novo_pa_esp() {
    if(document.form.hdd_referencia.value != '' && document.form.hdd_discriminacao.value != '') {
        novo_produto_acabado = document.form.hdd_referencia.value + ' - ' + document.form.hdd_discriminacao.value
    }else if(document.form.hdd_referencia.value != '') {
        novo_produto_acabado = document.form.hdd_referencia.value
    }else if(document.form.hdd_discriminacao.value != '') {
        novo_produto_acabado = document.form.hdd_discriminacao.value
    }
    var quantidade = prompt('DIGITE A QUANTIDADE PARA ESTE NOVO PA => '+novo_produto_acabado+' !')
    document.form.hdd_quantidade.value = quantidade
    if(document.form.hdd_quantidade.value == 0) {
        alert('QTDE INV�LIDA !');
        return false;
    }else {
        if(document.form.hdd_quantidade.value != '') {
            if(!texto('form', 'hdd_quantidade', '1', '0123456789', 'QUANTIDADE', '1')) {
                return false
            }
        }
    }
    //Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.action = 'provisionar_novo_pa.php'
    document.form.submit()
}

function validar() {
    var elementos                           = document.form.elements
    var chamar_funcao_pecas_por_embalagem   = 'S'//Foi criada uma vari�vel desse Tipo nessa rotina, pelo fato de estar em Loop ...
    var total_itens_orcamentos              = eval('<?=$total_itens_orcamentos;?>')
    var interpolacao                        = eval('<?=$interpolacao;?>')
    var cont_checkbox_selecionados = 0, total_linhas = 0
    
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_produto_acabado[]') {//S� vasculho os checkbox de Produtos ...
                if(elementos[i].checked) cont_checkbox_selecionados++
                total_linhas++
            }
        }
    }
    if (cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OP��O !')
        return false
    }
//Aki ultrapassou a qtde de itens permitidos por or�amento
    if((total_itens_orcamentos + cont_checkbox_selecionados) > 100) {
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSE OR�AMENTO N.� <?=$id_orcamento_venda;?> !\n\nOBS: DESMARQUE ALGUM(NS) ITEM(NS), POIS A QTDE M�XIMA PERMITIDA POR OR�AMENTO � DE NO M�XIMO 100 ITEM(NS) !')
        return false
//Ainda n�o ultrapassou a margem de itens permitidos, ent�o pode continuar incluindo itens
    }else {
        for(var i = 0; i < total_linhas; i++) {
    //For�a o Preenchimento do Campo Quantidade ...
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
                if(document.getElementById('txt_quantidade'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_quantidade'+i).focus()
                    return false
                }
                if(document.getElementById('txt_quantidade'+i).value == 0) {
                    alert('QUANTIDADE INV�LIDA !')
                    document.getElementById('txt_quantidade'+i).focus()
                    document.getElementById('txt_quantidade'+i).select()
                    return false
                }
            }
        }
//Verifica se a Qtde est� compat�vel com a Qtde de p�as / corte ...
        for(var i = 0; i < total_linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
//S� pode fazer a compara��o se o Produto for do tipo Esp e a Opera��o de Custo for do Tipo Industrial
                if(document.getElementById('hdd_referencia'+i).value == 'ESP' && document.getElementById('hdd_operacao_custo'+i).value == 0) {
                    /**********************L�gica para comparar Lotes Or��veis**********************/
                    //Significa que esse PA, � pode ser vendido com a Qtde menor do que a Qtde do Lote, nesse caso n�o se trabalha c/ a Faixa Or��vel ...
                    if(document.getElementById('hdd_lote_minimo_ignora_faixa_orcavel'+i).value == 'S') {		
                        var qtde_minima = document.getElementById('hdd_qtde_lote'+i).value
                        var qtde_maxima = document.getElementById('hdd_qtde_lote'+i).value * interpolacao
                        if((document.getElementById('txt_quantidade'+i).value < qtde_minima) || (document.getElementById('txt_quantidade'+i).value > qtde_maxima)) {
                            alert('A QUANTIDADE DO '+document.getElementById('hdd_referencia'+i).value+' EST� ABAIXO DA QTDE DE LOTE OR��VEL !\n\nEM CASO DE D�VIDAS CONSULTE O DEPTO. T�CNICO.')
                            document.getElementById('txt_quantidade'+i).focus()
                            document.getElementById('txt_quantidade'+i).select()
                            return false
                        }
                    }else {
                        var qtde_minima = document.getElementById('hdd_qtde_lote'+i).value / interpolacao
                        var qtde_maxima = document.getElementById('hdd_qtde_lote'+i).value * interpolacao
                        if((document.getElementById('txt_quantidade'+i).value < qtde_minima) || (document.getElementById('txt_quantidade'+i).value > qtde_maxima)) {
                            var resposta = confirm('A QTDE EST� INCOMPAT�VEL COM A QTDE DE LOTES OR��VEIS E IMPLICAR� EM VERIFICA��O PELO DEPTO. T�CNICO !!!\nDESEJA MANTER ESSA QTDE ?')
                            if(resposta == false) {
                                document.getElementById('txt_quantidade'+i).focus()
                                document.getElementById('txt_quantidade'+i).select()
                                return false
                            }
                        }
                    }
                    /*******************************************************************************/
                }
                //Aqui nessa parte do Script compara a quantidade de pe�as por embalagem para os produtos normais de linha
                if(document.getElementById('hdd_referencia'+i).value != 'ESP') {
                    if(chamar_funcao_pecas_por_embalagem == 'S') {
                        /***********************************Controle de Pe�as por Embalagem***********************************/
                        //Todo o controle � feito dentro da Fun��o de Pe�as por Embalagem ...
                        var resultado = pecas_por_embalagem(document.getElementById('hdd_referencia'+i).value, document.getElementById('hdd_discriminacao'+i).value, document.getElementById('hdd_familia'+i).value, document.getElementById('txt_quantidade'+i).value, document.getElementById('hdd_pecas_emb'+i).value)
                        if(resultado == 1) {//Usu�rio clicou em Cancelar ...
                            document.getElementById('txt_quantidade'+i).focus()
                            document.getElementById('txt_quantidade'+i).select()
                            return false
                        }else if(resultado == 0 || resultado == 2) {//"Alert" 0, "Confirm" 2 bot�o OK ...
                            /***********************************Controle por Funcion�rios***********************************/
                            var id_funcionario_logado = String('<?=$_SESSION['id_funcionario'];?>')

                            for(var j = 0; j < vetor_funcionarios_ignorar_pecas_por_embalagem.length; j++) {
                                /*Verifico se o Funcion�rio que est� logado pode colocar qualquer valor no que se refere � "Pe�as por Embalagem" ...
                                Essa vari�vel "vetor_funcionarios_ignorar_pecas_por_embalagem" est� dentro da biblioteca pecas_por_embalagem.js ...*/
                                var indice = id_funcionario_logado.indexOf(vetor_funcionarios_ignorar_pecas_por_embalagem[j])
                                if(indice == 0) {//Significa que esse Funcion�rio pode fazer o que bem entender ...
                                    var pergunta = confirm('PODE SER QUE EXISTA(M) MAIS ITEM(NS) COM QTDE(S) N�O COMPAT�VEL(IS) COM A(S) QTDE DE P�S / EMBALAGEM !!!\n\nDESEJA MANTER ESTA(S) QUANTIDADE(S) P/ TODO(S) O(S) ITEM(NS) ?')
                                    if(pergunta == true) {//Usu�rio clicou em OK, sai Loop Principal ...
                                        //P/ n�o chamar mais est� fun��o porque o pr�prio funcion�rio ignorou e n�o perder a valida��o no restante do Script ...
                                        chamar_funcao_pecas_por_embalagem = 'N'
                                    }else {//Usu�rio clicou em Cancelar, sistema barra ...
                                        document.getElementById('txt_quantidade'+i).focus()
                                        document.getElementById('txt_quantidade'+i).select()
                                        return false
                                    }
                                    break//P/ sair do Loop ...
                                }
                            }
                        }
                    }
                    /*****************************************************************************************************/
                }
            }
        }
//Tratamento com a Caixa de Qtde para gravar no BD ...
        for(var i = 0; i < total_linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
                document.getElementById('txt_quantidade'+i).value = strtofloat(document.getElementById('txt_quantidade'+i).value)
            }
        }
    }
    //Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    //Somente na Primeira vez que o Usu�rio teclar a tecla "Enter" que ser�o enviadas as Informa��es ...
    if(document.form.hdd_bloquear_tecla_enter.value == 0) {
        document.form.submit()
        document.form.hdd_bloquear_tecla_enter.value = 1//Aqui mudo essa vari�vel p/ 1 que n�o se submeta mais de uma vez ...
    }
}

function controlar_hdd_checkbox_comprados() {
    if(document.form.chkt_mostrar_comprados.checked == true) {
        document.form.hdd_checkbox_mostrar_comprados.value = 1
    }else {
        document.form.hdd_checkbox_mostrar_comprados.value = 0
    }
    document.form.txt_referencia.focus()
}

function controlar_hdd_checkbox_esp() {
    if(document.form.chkt_mostrar_especiais.checked == true) {
        document.form.chkt_mostrar_comprados.checked = true
        document.form.hdd_checkbox_mostrar_comprados.value = 1
        document.form.hdd_checkbox_mostrar_esp.value = 1
    }else {
        document.form.hdd_checkbox_mostrar_esp.value = 0
    }
    document.form.txt_referencia.focus()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.ativar_loading()
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action=''>
<!--************************Controles de Tela************************-->
<input type='hidden' name='hdd_checkbox_mostrar_esp' value='1'><!--Macete-->
<input type='hidden' name='hdd_checkbox_mostrar_comprados' value='0'><!--Macete-->
<!--Macete p/ "Google Chrome" principalmente p/ o usu�rio n�o submeter mais de uma vez o(s) mesmo(s) PA(s)-->
<input type='hidden' name='hdd_bloquear_tecla_enter' value='0'>
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr>
        <td>
            <fieldset>
                <legend>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                        <b>CONSULTAR PRODUTOS ACABADOS</b>
                    </font>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                    <tr class='linhanormal'>
                        <td>
                            Refer�ncia
                        </td>
                        <td>
                            <input type='text' name='txt_referencia' id='txt_referencia' title='Digite a Refer�ncia' maxlength='30' size='32' onkeyup='verificar_teclas(event)' class='caixadetexto'>
                            &nbsp;
                            <input type='checkbox' name='chkt_mostrar_especiais' value='1' title='Mostrar Especiais' onclick='controlar_hdd_checkbox_esp()' id='label1' class='checkbox' checked>
                            <label for='label1'>
                                Mostrar Especiais
                            </label>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
                        <td>
                            Discrimina��o
                        </td>
                        <td>
                            <input type='text' name='txt_discriminacao' id='txt_discriminacao' title='Digite a Discrimina��o' size='50' onkeyup='verificar_teclas(event)' class='caixadetexto'>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
                        <td>
                            C�digo Produto do Cliente
                        </td>
                        <td>
                            <input type='text' name='txt_codigo_produto_cliente' id='txt_codigo_produto_cliente' title='Digite o C�digo Produto do Cliente' maxlength='25' size='27' onkeyup='verificar_teclas(event)' class='caixadetexto'>
                            &nbsp;	
                            <input type='checkbox' name='chkt_mostrar_comprados' value='1' title="Mostrar PA's Comprados" onclick='controlar_hdd_checkbox_comprados()' id='label2' class='checkbox'>
                            <label for='label2'>
                                <font color='red'><b>Mostrar apenas PA's Comprados nos �ltimos 5 Anos.</b></font>
                            </label>
                        </td>
                    </tr>
                    <tr class='linhacabecalho' align='center'>
                        <td colspan='2'>
                            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='pesquisar_itens_incluir_lote()' class='botao'>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br>
<div id='pesquisar_itens_incluir_lote'></div>
</form>
</body>
</html>
<?
/*********************Fun��o de onload*********************/
//Foi colocada especificamente aqui ao inv�s de <head> porque aqui j� se carregou todos os objetos html do form ...
/*Significa que o Usu�rio ap�s a inclus�o dos PA(s) deseja permanecer na mesma Tela de Filtro de onde 
parou, pois l� ele j� est� com PA(s) pesquisados ...*/
if($_GET['hdd_incluir_permanecer'] == 1) {
?>
    <Script Language = 'JavaScript'>
        ajax('pesquisar_itens_incluir_lote.php<?=$parametro;?>', 'pesquisar_itens_incluir_lote')
    </Script>
<?}?>