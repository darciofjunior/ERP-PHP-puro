<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');//Essa biblioteca é requerida dentro do Custo ...
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>INVENTÁRIO REALIZADO COM SUCESSO.</font>";

if($passo == 1) {
    //Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
    $sql = "SELECT c.`id_pais`, c.`razaosocial`, c.`id_uf`, c.`id_cliente`, c.`credito`, pv.* 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE c.`id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_pais            = $campos[0]['id_pais'];
    $razao_social       = $campos[0]['razaosocial'];
    $credito            = $campos[0]['credito'];
    $id_uf_cliente      = $campos[0]['id_uf'];
    $tipo_moeda         = ($id_pais != 31) ? 'U$ ' : 'R$ ';//Verifica se o Cliente é do Tipo Internacional ...
    $data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
?>
<html>
<head>
<title>.:: Mandar no Vale ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
/*Aqui serve para controlar qual foi o último checkbox selecionado aonde acarretará efeito na Paginação do 
Pop-Up de alterar itens de Pedido*/
function controlar_clique(indice) {
    var elementos = document.form.elements
    var checkbox_selecionados = 0
//Significa que essa tela foi carregada com 1 item ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        if(elementos['chkt_pedido_venda_item[]'][0].checked == true) {//Se estiver checando o Elemento ...
            indice = 0
            checkbox_selecionados = 1
        }
//Significa que está tela foi carregada com vários itens ...
    }else {
        if(elementos['chkt_pedido_venda_item[]'][indice].checked == true) {//Se estiver checando o elemento ...
            checkbox_selecionados = 1;
        }else {//Se estiver desmarcando, verifico se tem algum outro que está selecionado ...
            var contador = 0;
            for(i = 0; i < elementos.length; i++) {//Ignoro o Primeiro Checkbox e verifico o 1º que está checado ...
                if(elementos[i].name == 'chkt_pedido_venda_item[]') {//Verifico se existe algum checkbox selecionado ...
                    if(elementos[i].checked == true) {
                        indice = contador
                        checkbox_selecionados = 1;
                        break//Faço assim p/ sair do loop ...
                    }
                    contador++
                }
            }
        }
    }
}

function mandar_vale() {
//A variável checkbox -> Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var id_pedido_venda_item = '', checkbox = 0, elemento = document.form.elements
    for(var i = 1; i < elemento.length; i++) {
        if(elemento[i].type == 'checkbox') {
            if(elemento[i].checked == true) {
                id_pedido_venda_item = id_pedido_venda_item + elemento[i].value + ','
                checkbox ++
            }
        }
    }
    id_pedido_venda_item = id_pedido_venda_item.substr(0, id_pedido_venda_item.length - 1)
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        nova_janela('../../../producao/programacao/estoque/gerenciar/mandar_vale.php?id_cliente=<?=$_GET['id_cliente'];?>&id_pedido_venda_item='+id_pedido_venda_item, 'MANDAR', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=1';?>' method='GET'>
<?
    //Aqui eu busco todos os Itens de Pedidos que estão Pendentes p/ este Cliente ...
    $sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, pvi.`margem_lucro`, 
            pv.`id_cliente`, pv.`id_empresa`, pv.`id_pedido_venda`, pv.`faturar_em`, 
            pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pvi.`id_pedido_venda_item`, 
            pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`qtde_faturada`, pvi.`preco_liq_final`, 
            pvi.`status` AS status_item, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao_custo`, pa.`operacao`, pa.`peso_unitario`, pa.`observacao` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            WHERE pv.`id_cliente` = '$_GET[id_cliente]' 
            ORDER BY pv.`id_empresa`, pv.`id_pedido_venda` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhum item de Pedido Pendente ...
?>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='erro' align='center'>
        <td>
            <b>NÃO HÁ PEDIDO(S) PENDENTE(S).</b>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            <br/>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar_cliente.php<?=$parametro;?>'" class='botao'>
        </td>
    </tr>
</table>
<?
    }else {//Existe pelo menos 1 item de Pedido Pendente ...
?>
<table width='80%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Mandar no Vale => 
            <font color='yellow'>&nbsp;Cliente: </font>
            <font color='#FFFFFF'><?=$razao_social;?></font>
            <font color='yellow'>/ Crédito:</font>
            <font color='#FFFFFF'><?=$credito;?></font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <input type='checkbox' name='chkt' onclick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td colspan='6'>
            Quantidade
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            <font title='Preço Líquido Final <?=$tipo_moeda;?>'>P. L.<br/>
                Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td rowspan='2'>
            IPI<br>%
        </td>
        <td rowspan='2'>
            Total<br><?=$tipo_moeda;?> Lote
        </td>
        <td rowspan='2'>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento'> Emp / Tp Nota<br> / Prazo Pgto</font>
        </td>
        <td rowspan='2'>
            Faturar em
        </td>
        <td rowspan='2'>
            <font title='N.º do Pedido'> N.º&nbsp;Ped</font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ini
        </td>
        <td>
            Fat
        </td>
        <td>
            Sep
        </td>
        <td>
            Pend
        </td>
        <td>
            Vale
        </td>
        <td>
            E.D.
        </td>
    </tr>
<?
        $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
	$id_pedido_venda_antigo                 = '';//Variável para controle das cores no Orçamento
	$id_empresa_corrente                    = $campos[0]['id_empresa'];
	$controle_linha_js                      = 0;//Variável que serve p/ controlar a cor da linha e índice do objeto em JavaScript
        
        for ($i = 0;  $i < $linhas; $i++) {
            $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt', '<?=($i + $controle_linha_js);?>', '#E8E8E8');controlar_clique('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_pedido_venda_item[]' value="<?=$campos[$i]['id_pedido_venda_item'];?>" onclick="checkbox('form', 'chkt', '<?=($i + $controle_linha_js);?>', '#E8E8E8')" class="checkbox">
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
        </td>
        <td>
        <?
            //Só aparecerá o Link do que já foi faturado, se tiver pelo menos 1 item q já está em NF ...
            if($campos[$i]['qtde_faturada'] > 0) {
        ?>
		<a href = '../../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>' class='html5lightbox'>
                    <?=segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');?>
		</a>
        <?
            }else {
                echo segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');
            }
        ?>
        </td>
        <td>
        <?
            $separado = $campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
            echo segurancas::number_format($separado, 0, '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['qtde_pendente'] > $vetor[3]) {
                echo '<font color="red"><b>'.segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.').'</b></font>';
            }else {
                echo segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.');
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['vale'], 0, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($vetor[3], 0, '.');?>
        </td>
        <td align='left'>
            <a href = '../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' class='html5lightbox'>
            <?                
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);
            ?>
            </a>
        </td>
        <td align='right'>
        <?
            $preco_liq_final = $campos[$i]['preco_liq_final'];
            
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                $tx_financeira          = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
                $margem                 = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $preco_liq_final);
                $custo_margem_lucro_zero= $margem[2];//preco_custo_zero
                $soma_margem+= $custo_margem_lucro_zero * $campos[$i]['qtde'];
        ?>
                <font color='#E8E8E8' title='Margem de Lucro Instantânea'>
                    <?='M.L.='.$margem[1];?>
                </font>
                <font color='#E8E8E8' style='cursor:help' title='Margem de Lucro Guardada'><br/>
                    <?='M.L.G.='.number_format($campos[$i]['margem_lucro'], 1, ',', '.');?>
                </font><br>
<?
            }
            echo number_format($preco_liq_final, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
//Quando o país é do Tipo Internacional, ou o Pedido for do Tipo SGD ou o Cliente possuir suframa, então não existe IPI ...
            if($id_pais != 31 || $campos[$i]['id_empresa'] == 4 || !empty($suframa) || $campos[$i]['operacao']==1) {
                $ipi = 'S/IPI';
                $id_classific_fiscal = '';
            }else { //Aqui tem que buscar a Classificação Fiscal para poder buscar o IPI
                $sql = "SELECT cf.`ipi`, cf.`id_classific_fiscal` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                        WHERE pa.`id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." LIMIT 1 ";
                $campos_temp = bancos::sql($sql);
                if(count($campos_temp) > 0) {
                    $ipi                    = number_format($campos_temp[0]['ipi'], 1, ',', '.');
                    $id_classific_fiscal    = $campos_temp[0]['id_classific_fiscal'];
                }else {
                    $id_classific_fiscal    = '';
                    $ipi                    = '&nbsp;';
                }
            }
            echo $ipi;
        ?>
        </td>
        <td align='right'>
        <?
            $preco_total_lote = $preco_liq_final * ($campos[$i]['qtde'] - $campos[$i]['qtde_faturada']);
            $total_geral+= $preco_total_lote;
            echo number_format($preco_total_lote, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }

            if($campos[$i]['id_empresa'] == 1) {
                $nomefantasia = 'ALBA - NF';
                $total_empresa+= $preco_total_lote;

                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa'] == 2) {
                $nomefantasia = 'TOOL - NF';
                $total_empresa+= $preco_total_lote;

                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==4) {
                $nomefantasia = 'GRUPO - SGD';
                $total_empresa+= $preco_total_lote;

                echo '(G - SGD) / '.$prazo_faturamento;
            }else {
                echo 'Erro';
            }
            $prazo_faturamento = '';//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</b></font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            $url = '../../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda='.$campos[$i]['id_pedido_venda'];
        ?>
            <a href='<?=$url;?>' class='html5lightbox'>
        <?
            if($id_pedido_venda_antigo != $campos[$i]['id_pedido_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                $id_pedido_venda_antigo = $campos[$i]['id_pedido_venda'];
        ?>
                <font color='red'>
                    <?=$campos[$i]['id_pedido_venda'];?>
                </font>
        <?
//Ainda são os mesmos Orçamentos
            }else {
                echo $campos[$i]['id_pedido_venda'];
            }
        ?>
            </a>
        </td>
    </tr>
    <?
//Se o Cliente estiver com o crédito OK, então realiza os cálculos
        if($credito == 'A' || $credito == 'B') {
//Se a Data de Programação for até a Data de Amanhã então é faturável
            if($campos[$i]['faturar_em'] <= $data_atual_mais_um) {
                if($campos[$i]['qtde_pendente'] >= $vetor[3]) {
                    $resultado = $vetor[3];
                }else {
                    $resultado = $campos[$i]['qtde_pendente'];
                }
                $valor_faturavel += ($separado + $campos[$i]['vale'] + $resultado) * ($campos[$i]['preco_liq_final']);
            }
        }
			
        if($id_empresa_corrente != $campos[$i+1]['id_empresa']) {
    ?>
    <tr class='linhanormal'>
        <td colspan='8' align='left' bgcolor='#CECECE'>
            <font color='green'>
                <b>Valor Faturável:</b>
                <?='R$ '.number_format($valor_faturavel, 2, ',', '.');?>
                <!--Esse hidden foi um macete que eu bolei p/ não ter problemas de índice ao clicar na linha em JavaScript-->
                <input type='hidden' name='objeto_auxiliar_indice[]'>
            </font>
        </td>
        <td colspan='6' align='right'  bgcolor='#CECECE'>
            <b>Total: <?=$nomefantasia.' => '.$tipo_moeda.number_format($total_empresa, 2, ',', '.');?></b>
        </td>
    </tr>
    <?
                $id_empresa_corrente = $campos[$i + 1]['id_empresa'];
                $valor_total_geral+= $total_empresa;
                //Zero essas variáveis p/ não dar conflito com os valores do próximo loop ...
                $valor_faturavel = 0;
                $total_empresa = 0;
                $controle_linha_js++;
            }
        }
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='11'>
            <font color='yellow'>Dólar Dia:</font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
        </td>
        <td colspan='3' align='right'>
            <span class='style12'>
                <b>
                    <font color='yellow'>TOTAL GERAL: </font>
                    <?=$tipo_moeda.number_format($total_geral, 2, ',', '.');?>
                </b>
            </span>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar_cliente.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_mandar_vale' value='Mandar Vale' title='Mandar Vale' onClick='mandar_vale()' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
</form>
</body>
</html>
<?
    }
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Clientes que serve para o Sistema Todo ...
    require('../../../classes/cliente/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro Função ...
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Mandar no Vale - Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Mandar no Vale - Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
	for($i = 0;  $i < $linhas; $i++) {
            $url = "window.location = 'consultar_cliente.php?passo=1&id_cliente=".$campos[$i]['id_cliente']."' ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <a href="javascript:<?=$url;?>" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_cliente'] == 0) {
                echo 'RA';
            }else if($campos[$i]['tipo_cliente'] == 1) {
                echo 'RI';
            }else if($campos[$i]['tipo_cliente'] == 2) {
                echo 'CO';
            }else if($campos[$i]['tipo_cliente'] == 3) {
                echo 'ID';
            }else if($campos[$i]['tipo_cliente'] == 4) {
                echo 'AT';
            }else if($campos[$i]['tipo_cliente'] == 5) {
                echo 'DT';
            }else if($campos[$i]['tipo_cliente'] == 6) {
                echo 'IT';
            }else if($campos[$i]['tipo_cliente'] == 7) {
                echo 'FN';
            }
        ?>
        </td>
        <td align="left">
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_cliente.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>