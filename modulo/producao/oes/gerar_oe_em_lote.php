<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/calculos.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
require('../../../modulo/classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/producao/oes/incluir.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='erro'>NÃO EXISTE(M) ITEM(NS) DE PEDIDO DE VENDA OU ITEM(NS) PRAC LIBERADO(S).</font>";
$mensagem[3] = "<font class='confirmacao'>OE(S) GERADA(S) EM LOTE COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>NÃO FOI POSSÍVEL GERAR OE EM LOTE.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_consultar  = $_POST['txt_consultar'];
        $opt_opcao      = $_POST['opt_opcao'];
    }else {
        $txt_consultar  = $_GET['txt_consultar'];
        $opt_opcao      = $_GET['opt_opcao'];
    }
    switch($opt_opcao) {
        case 1://Cliente(s) ...
            $sql = "SELECT DISTINCT(c.`id_cliente`), 
                    IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS dados_cliente, 
                    c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE (c.`nomefantasia` LIKE '%$txt_consultar%' OR c.`razaosocial` LIKE '%$txt_consultar%') 
                    AND c.`ativo` = '1' ORDER BY c.`razaosocial`, c.`nomefantasia` ";
        break;
        case 2://Fornecedor(es) ...
            $sql = "SELECT DISTINCT(f.`id_fornecedor`), 
                    IF(f.`nomefantasia` = '', f.`razaosocial`, f.`nomefantasia`) AS dados_fornecedor, 
                    f.`cidade`, ufs.`sigla` 
                    FROM `fornecedores` f 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
                    WHERE (f.`nomefantasia` LIKE '%$txt_consultar%' OR f.`razaosocial` LIKE '%$txt_consultar%') 
                    AND f.`ativo` = '1' ORDER BY f.`razaosocial`, f.`nomefantasia` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'gerar_oe_em_lote.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Gerar OE em Lote ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
	if($opt_opcao == 1) {/******************************Caminho por Cliente******************************/
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Gerar OE em Lote por Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Cidade
        </td>
        <td>
            Estado
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                $url = 'gerar_oe_em_lote.php?passo=2&id_cliente='.$campos[$i]['id_cliente'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['dados_cliente'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
    </tr>
<?
            }
	}else {/******************************Caminho por Fornecedor******************************/
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Gerar OE em Lote por Fornecedor
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Cidade
        </td>
        <td>
            Estado
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                $url = 'gerar_oe_em_lote.php?passo=2&id_fornecedor='.$campos[$i]['id_fornecedor'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['dados_fornecedor'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
    </tr>
<?
            }
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'gerar_oe_em_lote.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<b><font color="red">   Observação:</font></b>
<pre>
    * Não exibe na Tela Pós-Filtro:

    * Quantidade de OE do PA < Pendência do PA;
    * Se o Item do Pedido de Venda já tiver uma O.E atrelada.
</pre>
<?
    }
}else if($passo == 2) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_cliente     = $_POST['id_cliente'];
        $id_fornecedor  = $_POST['id_fornecedor'];
    }else {
        $id_cliente     = $_GET['id_cliente'];
        $id_fornecedor  = $_GET['id_fornecedor'];
    }
    
    if(!empty($id_cliente)) {/******************************Caminho por Cliente******************************/
        /*Somente itens em que a Qtde Pendente seja > 0, menos unidade Jogo porque pode dar diferença na Qtde de Peças por Jogo, 
        não se pode fazer substituição de um jogo de 2 peças para um de 3 peças ...*/
        $sql = "SELECT pvi.`id_pedido_venda`, pvi.`id_pedido_venda_item`, pvi.`qtde_pendente` AS qtde, pvi.`id_produto_acabado`, pa.`mmv` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`qtde_pendente` > '0' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`id_unidade` <> '12' AND pa.`ativo` = '1' 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` AND ea.`qtde_oe_em_aberto` < ea.`qtde_pendente` 
                WHERE pv.`id_cliente` = '$id_cliente' 
                ORDER BY pvi.`id_pedido_venda` DESC ";
    }else {/******************************Caminho por Fornecedor******************************/
        $data_anterior_15_dias = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -15), '-');
        
        /*Somente itens liberados e que não estejam em OE das Notas Fiscais dos últimos 15 dias menos unidade Jogo porque pode dar diferença 
        na Qtde de Peças por Jogo, não se pode fazer substituição de um jogo de 2 peças para um de 3 peças ...*/
        $sql = "SELECT nfe.`id_nfe`, nfe.`num_nota`, nfeh.`id_nfe_historico`, nfeh.`qtde_entregue` AS qtde, pa.`id_produto_acabado`, pa.`mmv` 
                FROM `nfe` 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` AND nfeh.`status` = '1' AND nfeh.`id_oe` IS NULL 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`id_unidade` <> '12' AND pa.`ativo` = '1' 
                WHERE nfe.`id_fornecedor` = '$id_fornecedor' 
                AND nfe.`data_emissao` >= '$data_anterior_15_dias' ORDER BY nfe.`num_nota` DESC ";
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'gerar_oe_em_lote.php?valor=2'
        </Script>
<?
    }else {
/****************************************************************************************/
        if(!empty($_POST['hdd_pa_enviado']) && !empty($_POST['hdd_pa_retornar'])) {//O usuário deseja desatrelar um P.A. Enviado da combo do P.A. Principal "à Retornar" ...
            //Verifico se o PA1 já estava atrelado com o PA2
            $sql = "SELECT `id_pa_substituir` 
                    FROM `pas_substituires` 
                    WHERE (`id_produto_acabado_1`= '$_POST[hdd_pa_enviado]' AND `id_produto_acabado_2`= '$_POST[hdd_pa_retornar]') 
                    OR (`id_produto_acabado_2`= '$_POST[hdd_pa_enviado]' AND `id_produto_acabado_1`= '$_POST[hdd_pa_retornar]') LIMIT 1 ";
            $campos_substituires = bancos::sql($sql);
            if(count($campos_substituires) == 1) {//Ainda não existe essa relação, então atrelo na Tab. Relacional ...
                //Desatrelando o P.A. Enviado da combo do P.A. Principal "à Retornar" na Tabela relacional `pas_substituires` ...
                $sql = "DELETE FROM `pas_substituires` WHERE `id_pa_substituir` = '".$campos_substituires[0]['id_pa_substituir']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Gerar OE em Lote ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'gerar_oe_em_lote.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    var cont_checkbox_selecionados = 0
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_produto_acabado[]') {//Só vasculho os checkbox de Produtos ...
                if(elementos[i].checked) {
                    cont_checkbox_selecionados++
                    break;
                }
            }
        }
    }
    if(cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        var total_linhas = (typeof(elementos['txt_quantidade_oe[]'][0]) == 'undefined') ? 1 : (elementos['txt_quantidade_oe[]'].length)
        for(var i = 0; i < total_linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {//Somente para as linhas selecionadas ...
                //Quantidade ...
                if(document.getElementById('txt_quantidade_oe'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_quantidade_oe'+i).focus()
                    return false
                }
                if(document.getElementById('txt_quantidade_oe'+i).value == '0,00') {
                    alert('QUANTIDADE INVÁLIDA !')
                    document.getElementById('txt_quantidade_oe'+i).focus()
                    document.getElementById('txt_quantidade_oe'+i).select()
                    return false
                }
                var quantidade          = eval(strtofloat(document.getElementById('txt_quantidade_oe'+i).value))
                //PA Enviado ...
                if(document.getElementById('cmb_pa_enviado'+i).value == '') {
                    alert('SELECIONE O PA ENVIADO !')
                    document.getElementById('cmb_pa_enviado'+i).focus()
                    return false
                }
                var estoque_disponivel_pa   = eval(strtofloat(document.getElementById('txt_estoque_disponivel_pa'+i).value))
                var estoque_comprometido    = eval(strtofloat(document.getElementById('hdd_estoque_comprometido'+i).value))
                var mmv_pa                  = eval(strtofloat(document.getElementById('hdd_mmv_pa'+i).value))
                //Verifica se o usuário digitou uma qtde à substituir / embalar > do que a qtde disponível do P.A. Enviado ...
                if(quantidade > estoque_disponivel_pa) {
                    alert('QUANTIDADE À SUBSTITUIR / EMBALAR INVÁLIDA !!!\n\nQUANTIDADE À SUBSTITUIR / EMBALAR MAIOR DO QUE A QTDE DISPONÍVEL DO P.A. ENVIADO !')
                    document.getElementById('txt_quantidade_oe'+i).focus()
                    document.getElementById('txt_quantidade_oe'+i).select()
                    return false
                }
                //Esse controle abaixo é p/ mantermos estoque de Itens mais Vendáveis ...
                if(quantidade < parseInt(mmv_pa - estoque_comprometido)) {
                    var resposta = confirm('A QUANTIDADE À SUBSTITUIR / EMBALAR ESTA INFERIOR AO "MMV - EC" = '+parseInt(mmv_pa - estoque_comprometido)+' !!!\n\nQUER POR MAIS PEÇAS EM ESTOQUE ?')
                    if(resposta == true) {
                        document.getElementById('txt_quantidade_oe'+i).focus()
                        document.getElementById('txt_quantidade_oe'+i).select()
                        return false
                    }
                }
            }
        }
        //Prepara para gravar no BD ...
        for(var i = 0; i < total_linhas; i++) {
            //Somente para as linhas selecionadas ...
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) document.getElementById('txt_quantidade_oe'+i).value = strtofloat(document.getElementById('txt_quantidade_oe'+i).value)
        }
        //Desabilito o botão de Salvar, p/ evitar de o usuário enviar as informações + de 1 vez p/ o Servidor
        document.form.cmd_salvar.disabled = true
        document.form.action = '<?=$PHP_SELF.'?passo=3';?>'//É nesse passo que o Sistema gera as OE(s) e Substituições dos PA(s) ...
    }
}

function desatrelar_pa(indice, pa_retornar) {
    if(document.getElementById('cmb_pa_enviado'+indice).value == '') {
        alert('SELECIONE UM PA ENVIADO PARA DESATRELAR !')
        document.getElementById('cmb_pa_enviado'+indice).focus()
        return false
    }
    var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE P.A. ?')
    if(resposta == true) {
        document.form.hdd_pa_enviado.value = document.getElementById('cmb_pa_enviado'+indice).value
        document.form.hdd_pa_retornar.value = pa_retornar
        document.form.submit()
    }else {
        return false
    }
}

//Passo no Iframe o id_produto_acabado Enviado p/ ver se Este contém Estoque Disponível ...
function consultar_estoque_disponivel(id_produto_acabado, indice) {
    ajax('estoque_disponivel_pa.php?id_produto_acabado='+id_produto_acabado, 'txt_estoque_disponivel_pa'+indice)
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<!--************************Controles de Tela************************-->
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<?
        //Controle para não dar conflito com a paginação do Pop-UP que atrela algum PA Enviado a um PA a Retornar ...
        $parametro_principal = (empty($_POST['parametro_principal'])) ? $parametro : $_POST['parametro_principal'];
?>
<input type='hidden' name='parametro_principal' value='<?=$parametro_principal;?>'>
<input type='hidden' name='hdd_pa_enviado'>
<input type='hidden' name='hdd_pa_retornar'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Gerar OE em Lote 
            <?
                if(!empty($id_cliente)) {
                    echo 'por Cliente';
                }else {
                    echo 'por Fornecedor';
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar_tudo_incluir(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <?
            if(!empty($id_cliente)) {
        ?>
        <td>
            N.º Pedido de Venda
        </td>
        <td>
            Qtde<br/>OE
        </td>
        <td>
            Qtde Pendente
        </td>
        <?
            }else {
        ?>
        <td>
            N.º Nota Fiscal
        </td>
        <td>
            Qtde<br/>OE
        </td>
        <td>
            Qtde de Entrada
        </td>
        <?
            }
        ?>
        </td>
        <td>
            PA Enviado / PA a Retornar
        </td>
        <td>
            ED P.A. <br/>Enviado
        </td>
        <td>
            EC P.A. a<br/>Retornar
        </td>
        <td>
            ED P.A. a<br/>Retornar
        </td>
        <td>
            MMV P.A. a<br/>Retornar
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                $id_pas_exibir = '';//Limpo esta variável p/ não herdar valores dos Loops anteriores ...
?>
    <tr class='linhanormal' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?
                if(!empty($_POST['chkt_produto_acabado'][$i])) {
                    $checked 	= 'checked';
                    $class      = 'caixadetexto';
                    $disabled	= '';
                }else {
                    $checked 	= '';
                    $class      = 'textdisabled';
                    $disabled	= 'disabled';
                }
            ?>
            <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
        <?
            if(!empty($id_cliente)) {
        ?>
            <a href = '../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>' class='html5lightbox'>
                <?=$campos[$i]['id_pedido_venda'];?>
                <img src= '../../../imagem/propriedades.png' title='Detalhes do Pedido de Venda' alt='Detalhes do Pedido de Venda' border='0'>
            </a>
        <?
            }else {
        ?>
            <a href = '../../compras/pedidos/nota_entrada/itens/index.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['num_nota'];?>
                <img src= '../../../imagem/propriedades.png' title='Detalhes da NFe' alt='Detalhes da NFe' border='0'>
            </a>
        <?
            }
        ?>
        </td>
        <td>
            <?$quantidade_oe = (!empty($_POST['txt_quantidade_oe'][$i])) ? $_POST['txt_quantidade_oe'][$i] : $campos[$i]['qtde'];?>
            <input type='text' name='txt_quantidade_oe[]' id='txt_quantidade_oe<?=$i;?>' value='<?=number_format($quantidade_oe, 2, ',', '.');?>' onKeyUp="verifica(this, 'moeda_especial', 2, '', event)" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" size='9' maxlength='8' class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <select name='cmb_pa_enviado[]' id='cmb_pa_enviado<?=$i;?>' title='Selecione o P.A. Enviado / Produto de Retorno' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" onchange="consultar_estoque_disponivel(this.value, '<?=$i;?>')" class="<?=$class;?>" <?=$disabled;?>>
            <?
                //Aqui eu listo todos os PA(s) Padrões que já foram substituídos com o PA Principal ...
                $sql = "SELECT 
                        IF(ps.`id_produto_acabado_1` = '".$campos[$i]['id_produto_acabado']."', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_pa 
                        FROM `pas_substituires` ps 
                        WHERE 
                        (ps.`id_produto_acabado_1` = '".$campos[$i]['id_produto_acabado']."') 
                        OR (ps.`id_produto_acabado_2` = '".$campos[$i]['id_produto_acabado']."') ";
                $campos_padroes = bancos::sql($sql);
                $linhas_padroes = count($campos_padroes);
                if($linhas_padroes > 0) {//Se encontrar pelo menos 1 PA, então ...
                    for($j = 0; $j < $linhas_padroes; $j++) $id_pas_exibir.= $campos_padroes[$j]['id_pa'].', ';
                    $id_pas_exibir = substr($id_pas_exibir, 0, strlen($id_pas_exibir) - 2);
                }else {
                    $id_pas_exibir = 0;//Para não dar erro de SQL ...
                }
                //Trago todos os PA(s) que estão atrelados na tab. relacional, + o outro selecionado pelo usuário no consultar P.A.
                $sql = "SELECT pa.`id_produto_acabado`, CONCAT(ROUND(ea.`qtde_disponivel`, 0), ' * ', pa.`referencia`, ' * ', pa.`discriminacao`, IF(ea.`status` = '0', '', ' (BLOQUEADO)')) AS dados 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                        WHERE pa.`id_produto_acabado` IN ($id_pas_exibir) ORDER BY pa.`referencia` ";
                echo combos::combo($sql, $id_produto_acabado_consultado);
            ?>
            </select>
            &nbsp;
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Atrelar PA' alt='Atrelar PA' onclick="if(document.getElementById('chkt_produto_acabado<?=$i;?>').checked == true) {nova_janela('../../classes/produtos_acabados/atrelar_pa.php?id_pa_a_ser_atrelado=<?=$campos[$i]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')};checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)">
            &nbsp;
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Desatrelar PA' alt='Desatrelar PA' onclick="if(document.getElementById('chkt_produto_acabado<?=$i;?>').checked == true) {desatrelar_pa('<?=$i;?>', '<?=$campos[$i]['id_produto_acabado'];?>')};checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)">
            <br>&nbsp;/&nbsp;
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class="link">
                <img src = '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class="link">
                <img src = '../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
        </td>
        <td>
            <input type='text' name='txt_estoque_disponivel_pa[]' id='txt_estoque_disponivel_pa<?=$i;?>' value='0,00' size='7' class='caixadetexto2' disabled>
        </td>
        <td>
        <?
            //Aqui eu verifico a qtde_disponível q eu tenho em estoque do PA a Retornar ...
            $estoque = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
            echo number_format($estoque[8], 2, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($estoque[3], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <textarea name='txt_observacao[]' id='txt_observacao<?=$i;?>' cols='50' rows='2' maxlength='95' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" class='<?=$class;?>' <?=$disabled;?>><?=$_POST['txt_observacao'][$i];?></textarea>
            <!--******************************Controles de Tela******************************-->
            <input type='hidden' name='hdd_quantidade_original[]' id='hdd_quantidade_original<?=$i;?>' value='<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>'>
            <input type='hidden' name='hdd_quantidade_disponivel[]' id='hdd_quantidade_disponivel<?=$i;?>' value='<?=number_format($qtde_disponivel, 2, ',', '.');?>'>
            <input type='hidden' name='hdd_estoque_comprometido[]' id='hdd_estoque_comprometido<?=$i;?>' value='<?=number_format($estoque[8], 2, ',', '.');?>'>
            <input type='hidden' name='hdd_mmv_pa[]' id='hdd_mmv_pa<?=$i;?>' value='<?=number_format($campos[$i]['mmv'], 2, ',', '.');?>'>
            <input type='hidden' name='hdd_pedido_venda_item[]' id='hdd_pedido_venda_item<?=$i;?>' value='<?=$campos[$i]['id_pedido_venda_item'];?>' disabled>
            <input type='hidden' name='hdd_nfe_historico[]' id='hdd_nfe_historico<?=$i;?>' value='<?=$campos[$i]['id_nfe_historico'];?>' disabled>
            <!--*****************************************************************************-->
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'gerar_oe_em_lote.php<?=$parametro_principal;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='10'>
            &nbsp;
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='10'>
            Total de Registro(s): <?=$linhas;?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
    }
}else if($passo == 3) {
    $data_sys = date('Y-m-d H:i:s');
    
    foreach($_POST['chkt_produto_acabado'] as $i=> $id_produto_acabado) {
        //Verifico se a Qtde de OE realmente é um Número Inteiro ...
        $quantidade_oe  = (strchr($_POST['txt_quantidade_oe'][$i], '.') == '00') ? intval($_POST['txt_quantidade_oe'][$i]) : $_POST['txt_quantidade_oe'][$i];

        //1) P.A. em que eu estou retirando algo do Estoque ...
        $resultado      = estoque_acabado::verificar_manipulacao_estoque($_POST['cmb_pa_enviado'][$i], -$quantidade_oe);
        
        if($resultado['retorno'] == 'executar') {//Se foi possível acrescentar algo, então roda as funções de Estoque ...
            if(!empty($_POST['hdd_pedido_venda_item'][$i])) {
                //Busca do Número do Pedido de Vendas ...
                $sql = "SELECT `id_pedido_venda` 
                        FROM `pedidos_vendas_itens` 
                        WHERE `id_pedido_venda_item` = '".$_POST['hdd_pedido_venda_item'][$i]."' LIMIT 1 ";
                $campos_pedido_venda        = bancos::sql($sql);
                $observacao_complementar    = " <b>PA ENVIADO - Pedido de Venda N.º </b>".$campos_pedido_venda[0]['id_pedido_venda'];
            }else if(!empty($_POST['hdd_nfe_historico'][$i])) {
                //Busca do Número da Nota Fiscal de Compras ...
                $sql = "SELECT nfe.`num_nota` 
                        FROM `nfe_historicos` nfeh 
                        INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                        WHERE nfeh.`id_nfe_historico` = '".$_POST['hdd_nfe_historico'][$i]."' LIMIT 1 ";
                $campos_nfe                 = bancos::sql($sql);
                $observacao_complementar    = " <b>PA ENVIADO - Nota Fiscal de Compras N.º </b>".$campos_nfe[0]['num_nota'];
            }
            
            //Gera OE ...
            $sql = "INSERT INTO `oes` (`id_oe`, `id_produto_acabado_s`, `id_produto_acabado_e`, `id_funcionario_resp_s`, `qtde_s`, `qtde_a_retornar`, `data_s`, `observacao_s`) VALUES (NULL, '".$_POST['cmb_pa_enviado'][$i]."', '$id_produto_acabado', '$_SESSION[id_funcionario]', '$quantidade_oe', '$quantidade_oe', '$data_sys', '".ucfirst(strtolower($_POST['txt_observacao'][$i])).$observacao_complementar."') ";
            bancos::sql($sql);
            $id_oe = bancos::id_registro();

            //Faz Substituição ...
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_oe`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '".$_POST['cmb_pa_enviado'][$i]."', '$_SESSION[id_funcionario]', '$id_oe', '-$quantidade_oe', '".$observacao_complementar.'. '.ucfirst(strtolower($_POST['txt_observacao'][$i]))."', 'M', '2', '$data_sys') ";
            bancos::sql($sql);

            estoque_acabado::atualizar($_POST['cmb_pa_enviado'][$i]);
            estoque_acabado::controle_estoque_pa($_POST['cmb_pa_enviado'][$i]);
            estoque_acabado::atualizar_producao($id_produto_acabado);//Atualizando apenas a Produção do Retornado ...

            if(!empty($_POST['hdd_nfe_historico'][$i])) {/******************************Caminho de NF de Entrada******************************/
                //Atualiza o Item da NF de Entrada com a Marcação de que foi gerada OE ...
                $sql = "UPDATE `nfe_historicos` SET `id_oe` = '$id_oe' WHERE `id_nfe_historico` = '".$_POST['hdd_nfe_historico'][$i]."' LIMIT 1 ";
                bancos::sql($sql);
            }
            $valor = 3;
        }else {
            $valor = 4;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'gerar_oe_em_lote.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Gerar OE em Lote ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Consultar
    if(document.form.txt_consultar.value == '') {
        alert('DIGITE O CAMPO CONSULTAR !')
        document.form.txt_consultar.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar OE em Lote
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Cliente(s)' onclick='document.form.txt_consultar.focus()' id='opt1' checked>
            <label for='opt1'>Cliente(s)</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Fornecedor(es)' onclick='document.form.txt_consultar.focus()' id='opt2'>
            <label for='opt2'>Fornecedor(es)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_consultar.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>