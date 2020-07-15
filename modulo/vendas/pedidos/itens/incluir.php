<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ORÇAMENTO(S) PENDENTE(S) PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM(NS) JÁ EXISTENTE.</font>";
$mensagem[4] = "<font class='atencao'>NEM TODOS OS ITEM(NS) PODEM SER IMPORTADO(S), POIS EXISTE(M) ITEM(NS) QUE JÁ FORAM IMPORTADO(S) ANTERIORMENTE.</font>";
$mensagem[5] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) PENDENTE(S) PARA ESSE ORÇAMENTO.</font>";
$mensagem[6] = "<font class='confirmacao'>ORÇAMENTO CONGELADO COM SUCESSO.</font>";
$mensagem[7] = "<font class='atencao'>NÃO FOI POSSÍVEL CONGELAR SEU ORÇAMENTO ! EXISTEM ITENS SEM CUSTO / BLOQUEADO / SEM PRAZO DE DEPTO. TÉCNICO.</font>";
$mensagem[8] = "<font class='atencao'>NÃO É POSSÍVEL INCLUIR ITEM(NS) ! ESTE PEDIDO ESTÁ LIBERADO.</font>";
$mensagem[9] = "<font class='erro'>ORÇAMENTO DESCONGELADO.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_pedido_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_pedido_venda'] : $_GET['id_pedido_venda'];

/********************************************************************************************************/
//Aki eu verifico quem é o Cliente deste Pedido, p/ ver se estão preenc. corretamente os dados de Endereço
$sql = "SELECT `id_cliente` 
        FROM `pedidos_vendas` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos     = bancos::sql($sql);
$id_cliente = $campos[0]['id_cliente'];
//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
$cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
if($cadastro_cliente_incompleto == 1) {
?>
<Script Language = 'JavaScript'>
    alert('O CADASTRO DESTE CLIENTE ESTÁ INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
    window.close()
</Script>
<?
    exit;
}
/********************************************************************************************************/

//Aki é segurança, por isso q está fora de todos os passos
//Verificação para ver se o Pedido está liberado
if(vendas::situacao_pedido($id_pedido_venda) == 1) {
?>
<html>
<head>
<title>.:: Incluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[8];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    exit;
}

if($passo == 1) {
    $congelar   = 'S';
    $valor      = 6;
	
/*Verifico a situação dos itens de orçamento, caso existe algum produto em ORÇAR ou DEP. TÉCNICO no 
orçamento corrente não posso congelar*/
    $sql = "SELECT `preco_liq_fat_disc` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
            AND `preco_liq_fat_disc` <> '' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) > 0) {//Não posso congelar ...
        $congelar   = 'N';
        $valor      = 7;//Variável de Retorno de Mensagem
    }else {//Caso eu possa congelar, então eu verifico se existe custo bloqueado
        $sql = "SELECT ovi.`id_produto_acabado` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
                AND pa.`status_custo` = '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {//Também não posso congelar o orçamento ...
            $congelar   = 'N';
            $valor      = 7;//Variável de Retorno de Mensagem
        }else {//Aqui eu verifico se existe algum Item no Orc = 'ESP' e que esteje sem Pzo. Técnico
            $sql = "SELECT ovi.`id_produto_acabado` 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`referencia` = 'ESP' 
                    WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
                    AND ovi.`prazo_entrega_tecnico` = '0.0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {//Também não posso congelar o orçamento ...
                $congelar   = 'N';
                $valor      = 7;//Variável de Retorno de Mensagem
            }else {//Caso eu possa congelar, então eu verifico se existe pelo menos 1 item no Orc.
                $sql = "SELECT `preco_liq_fat_disc` 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//Significa q não possui item nenhum item no Orc.
                    $congelar   = 'N';
                    $valor      = 7;//Variável de Retorno de Mensagem
                }
            }
        }
    }

    //Aki eu recalculo os preços do Orçamento
    if(strtoupper($congelar) == 'S') {
        //Aqui simplesmente congela o Orçamento caso o usuário, clicar em OK no passo anterior
        $sql = "UPDATE `orcamentos_vendas` SET `congelar` = '$congelar' WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
        bancos::sql($sql);
    }
    if($valor == 6) {//Orçamento Congelado
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?passo=2&id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&valor=6'
    </Script>
<?
    }else {//Não foi possível congelar o Orçamento
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&valor=7'
    </Script>
<?
    }
}else if($passo == 2) {
//Aqui verifica a qtde de itens existentes para esse Pedido
    $sql = "SELECT COUNT(`id_pedido_venda_item`) AS total_itens_pedidos 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '$id_pedido_venda' ";
    $campos_itens_pedidos   = bancos::sql($sql);
    $total_itens_pedidos    = $campos_itens_pedidos[0]['total_itens_pedidos'];
	
//Busca o nome País do Cliente através do id_orcamento_venda ...
    $sql = "SELECT ov.`nota_sgd`, c.`id_cliente`, c.`id_pais`, c.`tipo_faturamento` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    //Significa que o Cliente é do Tipo Internacional
    $tipo_moeda = ($campos[0]['id_pais'] != 31) ? 'U$' : 'R$';
	
    if($campos[0]['nota_sgd'] == 'S') {//Se for SGD - Grupo, traz Orçamentos do Tipo SGD ...
        $condicao = 'ed.`id_empresa` IN (1, 2) ';
    }else {//Se for NF - Alba e Tool, traz Orçamentos do Tipo NF ...
        //Significa que o Cliente fatura tudo pela Albafér e Tool Master ...
        if($campos[0]['tipo_faturamento'] == 1 || $campos[0]['tipo_faturamento'] == 2 || $campos[0]['tipo_faturamento'] == 'Q') {
            $condicao = 'ed.`id_empresa` IN (1, 2) ';
            if($campos[0]['tipo_faturamento'] == 1) {//Significa que o Cliente fatura tudo pela Albafér ...
                $texto  = 'TUDO PELA ALBAFER';
            }else if($campos[0]['tipo_faturamento'] == 2) {//Significa que o Cliente fatura tudo pela Tool Master ...
                $texto  = 'TUDO PELA TOOL MASTER';
            }else if($campos[0]['tipo_faturamento'] == 'Q') {//Significa que o Cliente fatura por Ambas Empresas - Indiferente ...
                $texto  = 'QUALQUER EMPRESA';
            }
        }else {//Se o Cliente tem o Faturamento do Tipo Separadamente, traz só da Empresa do Pedido ...
            $sql = "SELECT `id_empresa` 
                    FROM `pedidos_vendas` 
                    WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            $campos     = bancos::sql($sql);
            $condicao 	= "ed.`id_empresa` = '".$campos[0]['id_empresa']."' ";
            $texto      = 'SEPARADAMENTE';
        }
    }
/*Seleciona todos os itens com o id_orcamento q foi passado, só q traz somente os produtos da divisão
da empresa do pedido*/
    $sql = "SELECT ed.`id_empresa_divisao`, gpa.`id_familia`, ov.`nota_sgd`, ov.`prazo_a`, 
            ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, ovi.*, pa.`id_produto_acabado`, pa.`referencia`, 
            pa.`discriminacao`, pa.`operacao_custo`, pa.`peso_unitario`, pa.`observacao` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND $condicao 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' 
            AND ovi.`status` < '2' ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Incluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/pecas_por_embalagem.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
//Esse parâmetro é para controlar quantos itens que eu já tenho nesse Pedido
function validar(total_itens_pedidos) {
    var elementos = document.form.elements
    var cont_checkbox_selecionados = 0, total_linhas = 0
//Verificação de Itens Selecionados
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            //Só vasculho os checkbox de Orcs ...
            if(elementos[i].name == 'chkt_orcamento_venda_item[]') {
                if(elementos[i].checked) cont_checkbox_selecionados++
                total_linhas++
            }
        }
    }
    if (cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        total_itens_pedidos = eval(total_itens_pedidos) + total_linhas
    }
//Aki verifica se passou da qtde limite de itens para o Pedido
    if(total_itens_pedidos > 200) {
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSE PEDIDO N.º <?=$id_pedido_venda;?> !\nDESMARQUE ALGUM(NS) ITEM(NS) SELECIONADO(S) !\n\nOBS: A QTDE MÁXIMA PERMITIDA POR PEDIDO É DE NO MÁXIMO 200 ITEM(NS) !')
        return false
    //Ainda não ultrapassou a margem de itens permitidos, então pode continuar inclui0ndo itens
    }else {
        for(var i = 0; i < total_linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true) {
                if(document.getElementById('txt_quantidade'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_quantidade'+i).focus()
                    return false
                }
                if(document.getElementById('txt_quantidade'+i).value == 0) {
                    alert('QUANTIDADE INVÁLIDA !')
                    document.getElementById('txt_quantidade'+i).focus()
                    document.getElementById('txt_quantidade'+i).select()
                    return false
                }
                //Verifica se o valor digitado no Pedido é maior do que o valor do Orçamento
                if(eval(strtofloat(document.getElementById('txt_quantidade'+i).value)) > eval(strtofloat(document.getElementById('txt_qtde_real'+i).value))) {
                    alert('QUANTIDADE PEDIDA INVÁLIDA !\nQUANTIDADE PEDIDA MAIOR DO QUE A QUANTIDADE SOLICITADA EM ORÇAMENTO !')
                    document.getElementById('txt_quantidade'+i).focus()
                    document.getElementById('txt_quantidade'+i).select()
                    return false
                }
                //Aqui nessa parte do Script compara a quantidade de peças por embalagem para os produtos normais de linha
                if(document.getElementById('hdd_referencia'+i).value != 'ESP') {
                    /***********************************Controle de Peças por Embalagem***********************************/
                    //Todo o controle é feito dentro da Função de Peças por Embalagem ...
                    var resultado = pecas_por_embalagem(document.getElementById('hdd_referencia'+i).value, document.getElementById('hdd_familia'+i).value, document.getElementById('txt_quantidade'+i).value, document.getElementById('hdd_pecas_emb'+i).value)
                    if(resultado == 0) {
                        document.getElementById('txt_quantidade'+i).focus()
                        document.getElementById('txt_quantidade'+i).select()
                        return false
                    }
                    /*****************************************************************************************************/
                }
            }
        }
    }
/*Desabilita as caixas de qtde para poder gravar no BD porque não é desabilitado a caixa de qtde quando 
o produto é do tipo ESP*/
    for(var i = 0; i < total_linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
        if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true) {
            document.getElementById('txt_quantidade'+i).value       = eval(strtofloat(document.getElementById('txt_quantidade'+i).value))
            document.getElementById('txt_quantidade'+i).disabled    = false
        }
    }
//Aqui trava o botão para evitar de gerar + de uma vez o mesmo pedido
    document.form.cmd_salvar.disabled = true
}

//Aqui recebe o índice da linha e o Valor Original do Estoque
function calcular_estoque_real(indice, estoque_original) {
    var qtde                = (document.getElementById('txt_quantidade'+indice).value != '') ? eval(document.getElementById('txt_quantidade'+indice).value) : 0
    var estoque_original    = eval(estoque_original)
//Novo valor do Estoque Real
    if(estoque_original > qtde) {
        document.getElementById('txt_novo_estoque'+indice).value = estoque_original - qtde
    }else {
        document.getElementById('txt_novo_estoque'+indice).value = 0
    }
    document.getElementById('txt_novo_estoque'+indice).value = arred(document.getElementById('txt_novo_estoque'+indice).value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit="return validar('<?=$total_itens_pedidos;?>')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[5];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php?id_pedido_venda=<?=$id_pedido_venda;?>'" class='botao'>
            &nbsp;
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>	
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Importando Itens do Orçamento N.º&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
            &nbsp;-&nbsp;Faturar 
            <!--Esse parâmetro pop_up=1 é para que o Sistema não exiba o botão Voltar ...-->
            <a href="javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[0]['id_cliente'];?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Cliente' class='link'>
                <font color='yellow'>
                    <?=$texto;?>
                </font>
            </a>
            para este Cliente.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Qtde
        </td>
        <td>
            P&ccedil;s /<br>Emb.
        </td>
        <td>
            <font title='Estoque Disponível Novo' style='cursor:help'>
                E.D.N.
            </font>
        </td>
        <td>
            <font title='Estoque Disponível' style='cursor:help'>
                E.D.
            </font>
        </td>
        <td>
            Ref. * Discriminação
        </td>
        <td>
            <font title='Preço L. Final <?=$tipo_moeda;?>' style='cursor:help'>
                Preço <br>L. Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td>
            <font title='Total <?=$tipo_moeda;?> Lote' style='cursor:help'>
                Total <br/><?=$tipo_moeda;?> Lote
            </font>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', '<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_orcamento_venda_item[]' id='chkt_orcamento_venda_item<?=$i;?>' value="<?=$campos[$i]['id_orcamento_venda_item'];?>" onclick="checkbox('form', '<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" class='checkbox'>
        </td>
        <td>
        <?
                //Esse aqui é um vetor de controle p/ o JavaScript, qtdes nessas situações não podem ser alteradas ...
                if($campos[$i]['referencia'] == 'ESP') $vetor_esp.= $i.',';
                $qtde_orcamento = $campos[$i]['qtde'];
                /*Aqui eu verifico o quanto que eu tenho já importado desse item de orçamento em todos os pedidos 
com exceção do pedido corrente ...*/
                $sql = "SELECT SUM(`qtde`) AS qtde_total_em_pedido 
                        FROM `pedidos_vendas_itens` 
                        WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' ";
                $campos_qtde_pedido 	= bancos::sql($sql);
                $qtde_total_em_pedido 	= $campos_qtde_pedido[0]['qtde_total_em_pedido'];
                $restante               = ($qtde_orcamento - $qtde_total_em_pedido);
                /*Aqui eu verifico a qtde disponível desse item em Estoque e a qtde dele em Produção*/
                $estoque_produto        = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
                $racionado 		= $estoque_produto[5];
                $qtde_estoque           = $estoque_produto[3];
                //Se retornar nulo do banco
                if($qtde_estoque == '') $qtde_estoque = 0;
                if($racionado == 1) {
                    $type = 'hidden';
                    $qtde_estoque = 0;
                    $qtde_estoque_calculo = 0;
                    $msg_racionado = "<font color='red'><b>Racionado</b></font>";
                }else {
                    $type = 'text';
                    if($qtde_estoque > $restante) {
                        $qtde_estoque_calculo = $qtde_estoque - $restante;
                    }else {
                        $qtde_estoque_calculo = 0;
                    }
                }
            ?>
            <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' value='<?=(integer)$restante;?>' title='Digite a Quantidade' maxlength='8' size='8' onclick="checkbox_gerar_pedido('<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '0', '', event);calcular_estoque_real('<?=$i;?>', '<?=$qtde_estoque;?>')" class='textdisabled' disabled>
            <input type='hidden' name='txt_qtde_real[]' id='txt_qtde_real<?=$i;?>' value='<?=(integer)$restante;?>'>
        </td>
        <td>
        <?
//Traz a quantidade de peças por embalagem da embalagem principal daquele produto
            $sql = "SELECT `pecas_por_emb` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_pecas_emb   = bancos::sql($sql);
            $pecas_embalagem    = (count($campos_pecas_emb) == 1) ? $campos_pecas_emb[0]['pecas_por_emb'] : 0;
            echo number_format($pecas_embalagem, 0, ',', '.');
        ?>
            <input type='hidden' id='hdd_pecas_emb<?=$i;?>' value='<?=$pecas_embalagem;?>'>
        </td>
        <td>
            <?=$msg_racionado;?>
            <input type='<?=$type;?>' name='txt_novo_estoque[]' id='txt_novo_estoque<?=$i;?>' value='<?=number_format($qtde_estoque_calculo, 2, ',', '.');?>' title='Estoque Disponível Novo' maxlength='8' size='8' class='textdisabled' disabled>
            <input type='hidden' id='hdd_referencia<?=$i;?>' value='<?=$campos[$i]['referencia'];?>'>
            <input type='hidden' id='hdd_familia<?=$i;?>' value='<?=$campos[$i]['id_familia'];?>'>
        </td>
        <td>
        <?
            if($racionado == 1) {
                echo '&nbsp;';
            }else {
                echo number_format($qtde_estoque, 2, ',', '.');
            }
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['referencia'] != 'ESP') {
                echo $campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);
            }else {
        ?>            
                <?=$campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>            
        <?
            }
            if($campos[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../imagem/queima_estoque.png" title="Excesso de Estoque" alt="Excesso de Estoque" border="0">';
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote = $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
            echo number_format($preco_total_lote, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php?id_pedido_venda=<?=$id_pedido_venda;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
    $vetor_esp = substr($vetor_esp, 0, strlen($vetor_esp) - 1);
?>
<input type='hidden' name='vetor_esp' value="<?=$vetor_esp;?>">
<input type='hidden' name='id_pedido_venda' value="<?=$id_pedido_venda;?>">
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

Aqui só exibe itens de Orçamento cujo os produtos são referentes ao Tipo de Empresa (Divisão)
selecionada no Pedido:

Pedido - Albafer     => Produtos da Divisão Albafer
Pedido - Tool Master => Produtos da Divisão Tool Master
Pedido - Grupo       => Produtos da Albafer e Tool Master
</pre>
<?
    }
}else if($passo == 3) {
//Primeira verificação a ser feita, é ver se o Orçamento realmente está congelado ...
    $sql = "SELECT ov.congelar 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            WHERE ovi.`id_orcamento_venda_item` = '".$_POST['chkt_orcamento_venda_item'][0]."' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if($campos[0]['congelar'] == 'N') {//Se não estiver congelado, então não posso importar nenhum Item
        $valor = 9;
    }else {//Orçamento congelado, posso estar importando os Itens normalmente p/ o Pedido ...
//Variável para fazer o controle da Mensagem
        $cont_itens_ignorados = 0;
        $cont_itens_aceitos = 0;
        //Aqui é a parte da inserção dos itens do Pedido
        foreach($_POST['chkt_orcamento_venda_item'] as $i => $id_orcamento_venda_item) {
            //Verifica se já foi incluido aquele item no pedido
            $sql = "SELECT id_pedido_venda_item 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' 
                    AND `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Item não existente
                $sql = "SELECT id_produto_acabado, id_representante, preco_liq_final, prazo_entrega, margem_lucro 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                $campos_pa              = bancos::sql($sql);
                $id_produto_acabado     = $campos_pa[0]['id_produto_acabado'];
                $id_representante 	= $campos_pa[0]['id_representante'];
                $preco_liq_final	= $campos_pa[0]['preco_liq_final'];
                $prazo_entrega 		= $campos_pa[0]['prazo_entrega'];
                $margem_lucro 		= $campos_pa[0]['margem_lucro'];
                $retorno                = estoque_acabado::qtde_estoque($id_produto_acabado);//busco a qtde do estoque do PA corrente
                $status_estoque		= $retorno[1]; //status do estoque para saber se ele está bloqueado
                $racionado              = $retorno[5]; //status do estoque para saber se ele está racionado
                if($status_estoque == 1 || $racionado == 1) { //então tá bloqueado ou racioado
                    $qtde_pendente = $_POST['txt_quantidade'][$i];
                }else {
                    /*Mudamos isso no dia 14/11/2016 porque não queremos mais separação automática na hora de 
                    gerar Pedido, devido muitos erros de Estoque com a Entrada dos Machos ...

                    $qtde_pendente = $_POST['txt_quantidade'][$i] - $retorno[3];*/
                    //Preciso deste macete para quando eu incluir uma qtde de item menos q a est. disnivel para ele nao daá erro ...
                    if($qtde_pendente < 0) $qtde_pendente = 0;
                }
                $sql = "INSERT INTO `pedidos_vendas_itens` (`id_pedido_venda_item`, `id_pedido_venda`, `id_orcamento_venda_item`, `id_produto_acabado`, `id_representante`, `id_funcionario`, `qtde`, `qtde_pendente`, `preco_liq_final`, `prazo_entrega`, `margem_lucro`, `data_sys`) VALUES (NULL, '$_POST[id_pedido_venda]', '$id_orcamento_venda_item', '$id_produto_acabado', '$id_representante', '$_SESSION[id_funcionario]', '".$_POST['txt_quantidade'][$i]."', '$qtde_pendente', '$preco_liq_final', '$prazo_entrega', '$margem_lucro', '".date('Y-m-d')."') ";
                bancos::sql($sql);
                $cont_itens_aceitos ++;
                $id_pedido_venda_item = bancos::id_registro();
                estoque_acabado::controle_pedidos_vendas_itens($id_pedido_venda_item, 1);// é só para controle de importacao dos itens do orçamentos e tb chama a função que atualiza o Estoque PA
                faturamentos::pedidos_vendas_status($id_pedido_venda_item);
            }else {//Item já existente
                $cont_itens_ignorados ++;
            }
        }
//Significa que foram inclusos todos os itens do Orçamento perfeitamente no Pedido
        if($cont_itens_aceitos != 0 && $cont_itens_ignorados == 0) $valor = 2;
//Significa que nenhum dos itens do Orçamento podem ter sido Importado
        if($cont_itens_aceitos == 0 && $cont_itens_ignorados != 0) $valor = 3;
//Nem todos os item(ns) podem ter sido importado(s)
        if($cont_itens_aceitos != 0 && $cont_itens_ignorados != 0) $valor = 4;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?id_pedido_venda=<?=$_POST['id_pedido_venda'];?>&valor=<?=$valor;?>'
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}else {
//Aqui eu verifico quem é o cliente do pedido e de qual empresa que é este pedido ...
    $sql = "SELECT `id_cliente`, `id_empresa`, `finalidade`, `prazo_medio` 
            FROM `pedidos_vendas` 
            WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_cliente         = $campos[0]['id_cliente'];
    $id_empresa_pedido  = $campos[0]['id_empresa'];
    $finalidade         = $campos[0]['finalidade'];
    $prazo_medio_pedido = $campos[0]['prazo_medio'];

//Caso o pedido seja Albafer, só trará produtos em que a divisão for Albafer
    if($id_empresa_pedido == 1) {
        $condicao = "AND ed.`id_empresa` = '1' ";
//Caso o pedido seja Albafer, só trará produtos em que a divisão for Tool Master
    }else if($id_empresa_pedido == 2) {
        $condicao = "AND ed.`id_empresa` = '2' ";
//Caso o pedido seja Grupo, trará produtos independente da Divisão Albafer ou Tool Master
    }else {
        $condicao = "AND ed.`id_empresa` IN (1, 2) ";
    }
	
//Significa que são pedidos da Albafer e da Tool Master, sendo assim só trago Orçamentos do Tipo NF
    if($id_empresa_pedido == 1 || $id_empresa_pedido == 2) {
        $nota_sgd = 'N';
//Só irá existir essa opção quando for com NF ...
        $condicao_finalidade = " AND ov.`finalidade` = '$finalidade' ";
//Significa que são pedidos do Grupo, sendo assim só trago Orçamentos do Tipo SGD
    }else {
        $nota_sgd = 'S';
    }

    $data_anterior_variavel = data::adicionar_data_hora(date('d/m/Y'), -genericas::variavel(38));
    $data_anterior_variavel = data::datatodate($data_anterior_variavel, '-');
    $condicao_data          = "and substring(ov.data_emissao, 1, 10) >= '$data_anterior_variavel' ";
	
/*Aqui eu trago todos os orçamentos pendentes que contém pelo menos 1 item pendente da Divisão selecionada
em Pedido e que estejam congelados do cliente e também só do tipo de orçamento gerado - NF ou SGD e de 
acordo com a opção selecionada em checkbox caso o usuário deseje trazer os dos últimos 30 dias*/
    $sql = "SELECT DISTINCT(ov.`id_orcamento_venda`), ov.`id_cliente_contato`, ov.`nota_sgd`, 
            DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, ov.`prazo_medio`, 
            ov.`congelar`, c.`razaosocial`, c.`credito` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`status` < '2' AND ov.`nota_sgd` = '$nota_sgd' $condicao_data $condicao_finalidade 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` AND c.`id_cliente` = '$id_cliente' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` $condicao 
            WHERE ovi.`status` < '2' ORDER BY ov.`id_orcamento_venda` DESC ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Incluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function congelar(id_pedido_venda, id_orcamento_venda) {
    var resposta = confirm('DESEJA CONGELAR ESTE ORÇAMENTO ?')
    if(resposta == true) {
        window.location = 'incluir.php?passo=1&id_pedido_venda='+id_pedido_venda+'&id_orcamento_venda='+id_orcamento_venda
    }else {
        return false
    }
}

function avisar() {
    alert('ESSE ORÇAMENTO NÃO PODE SER INCLUSO NESTE PEDIDO !\n A DATA DE VALIDADE DESTE ORÇAMENTO É MENOR DO QUE A DATA ATUAL !')
}

function prazos() {
    alert('ESSE ORÇAMENTO NÃO PODE SER INCLUSO NESTE PEDIDO !\n A DIFERENÇA ENTRE O PRAZO MÉDIO DO ORÇAMENTO E O PRAZO MÉDIO DO PEDIDO É SUPERIOR A 15 DIAS !')
}
</Script>
</head>
<form name='form'>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>	
<?
    }else {
?>    
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Importar Orçamento(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Orc
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Contato
        </td>
        <td>
            Tipo de Nota
        </td>
        <td>
            Data de Validade
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
//Variável que será utilizada mais abaixo ...
        for ($i = 0; $i < $linhas; $i++) {
            $id_orcamento_venda     = $campos[$i]['id_orcamento_venda'];
            $prazo_medio_orcamento  = $campos[$i]['prazo_medio'];
            $credito                = $campos[$i]['credito'];

            if($campos[$i]['congelar'] == 'S') {//Está Congelado
                if($credito == 'C' || $credito == 'D') {
                    $script = "javascript:alert('CLIENTE COM CRÉDITO ".$credito." ! NÃO É PERMITIDO A INCLUSÃO DE ITEM(NS) PARA ESTE PEDIDO !')";
                }else {
/*Se a Diferença entre o Prazo Médio do Orçamento e o Prazo Médio do Pedido for > que 15 dias, 
então o Sistema não deixa importar o Orçamento para o Pedido, primeiramente o usuário vai ter que estar 
acertando os Prazos do Pedido que está muito distante com a do Orçamento*/
                    if($prazo_medio_orcamento > ($prazo_medio_pedido + 15)) {
                        $script = "prazos()";
                    }else {
/*Aqui se a Data de Validade do Orçamento for maior ou igual a Data Atual, eu ainda posso 
incluir esse Pedido ...*/
                        $vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
                        $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];
                        if($data_validade_orc >= date('Y-m-d')) {
                            $script = "window.location = 'incluir.php?passo=2&id_pedido_venda=".$_GET['id_pedido_venda'].'&id_orcamento_venda='.$id_orcamento_venda."'";
                        }else {//A data é menor do que a data atual , então não pode
                            $script = "avisar()";
                        }
                    }
                }
            }else {//Não está congelado
                if($credito == 'C' || $credito == 'D') {
                    $script = "javascript:alert('CLIENTE COM CRÉDITO ".$credito." ! NÃO É PERMITIDO A INCLUSÃO DE ITEM(NS) PARA ESTE PEDIDO !')";
                }else {
/*Se a Diferença entre o Prazo Médio do Orçamento e o Prazo Médio do Pedido for > que 15 dias, 
então o Sistema não deixa importar o Orçamento para o Pedido, primeiramente o usuário vai ter que estar 
acertando os Prazos do Pedido que está muito distante com a do Orçamento*/
                    if(($prazo_medio_pedido - $prazo_medio_orcamento) > 15) {
                        $script = "prazos()";
                    }else {
/*Aqui se a Data de Validade do Orçamento for maior ou igual a Data Atual, eu ainda posso 
incluir esse Pedido ...*/
                        $vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
                        $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];
                        if($data_validade_orc >= date('Y-m-d')) {
                            $script = 'javascript:congelar('.$_GET['id_pedido_venda'].', '.$id_orcamento_venda.')';
                        }else {//A data é menor do que a data atual , então não pode
                            $script = "avisar()";
                        }
                    }
                }
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick = "<?=$script;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:<?=$script;?>" class='link'>
                <?=$id_orcamento_venda;?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['congelar'] == 'S') {//Está Congelado
        ?>
                <font color='blue' title='Orçamento Congelado'>
        <?
                    echo $campos[$i]['razaosocial'];
        ?>
                </font>
        <?
            }else {//Não está congelado
        ?>
                <font color='red' title='Orçamento Descongelado'>
        <?
                    echo $campos[$i]['razaosocial'];
        ?>
                </font>
        <?
            }
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `nome` 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            echo $campos_contato[0]['nome'];
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['nota_sgd'] == 'S') {
                echo 'SGD';
            }else {
                echo 'NF';
            }
        ?>
        </td>
        <td>
        <?
            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
            echo data::datetodata($vetor_dados_gerais['data_validade_orc'], '/');
        ?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['observacao'])) {
                echo '&nbsp';
            }else {
                echo "<img width='28' height='23' title='".$campos[$i]['observacao']."' src='../../../../imagem/olho.jpg'>";
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<input type='hidden' name='id_pedido_venda' value='<?=$_GET['id_pedido_venda'];?>'>
</form>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

Só exibe Orçamento(s) do mesmo Tipo de Nota que foi selecionado em Pedido

Ped - Consumo           => Orc - Consumo
Ped - Industrialização  => Orc - Industrialização
Ped - Revenda           => Orc - Revenda
Ped - SGD => Orc - SGD
Ped - NF  => Orc - NF

<b>Só exibe Orçamento(s) dos últimos <font color='red'><?=number_format(genericas::variavel(38), 0);?></font> dias</b>
</pre>
<?
    }
}
?>