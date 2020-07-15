<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OP N.� <font class='erro'>".$id_op."</font> INCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.*, ed.razaosocial, gpa.nome 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.`referencia` LIKE '%$txt_consultar%' 
                    AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') ORDER BY pa.discriminacao ";
        break;
        case 2:
            $sql = "SELECT pa.*, ed.razaosocial, gpa.nome 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.`discriminacao` LIKE '%$txt_consultar%' 
                    AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') ORDER BY pa.discriminacao ";
        break;
        case 3:
            $sql = "SELECT pa.*, ed.razaosocial, gpa.nome 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE (gpa.nome LIKE '%$txt_consultar%' OR ed.razaosocial LIKE '%$txt_consultar%') 
                    AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') ORDER BY pa.discriminacao ";
        break;
        default:
            $sql = "SELECT pa.*, ed.razaosocial, gpa.nome 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') ORDER BY pa.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) p/ Incluir Nova OP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_produto_acabado, operacao_custo) {
    if(operacao_custo == 1) {//Quando a Opera��o de Custo o PA. Revenda
        alert('A OPERA��O DE CUSTO DESSE PA � DO TIPO REVENDA !')
    }
    window.location = 'incluir.php?passo=2&id_produto_acabado='+id_produto_acabado+'&operacao_custo='+operacao_custo+'&controle=1'
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Produto(s) Acabado(s) p/ Incluir Nova OP
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Refer�ncia
        </td>
        <td>
            Discrimina��o
        </td>
        <td>
            Grupo P.A. / Empresa Divis�o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "avancar('".$campos[$i]['id_produto_acabado']."', '".$campos[$i]['operacao_custo']."')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="<?=$url;?>">
            <a href="javascript:<?=$url;?>">
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href="javascript:<?=$url;?>" class='link'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td>
            <?
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
//Se o PI j� foi exclu�do, ent�o o sistema mostra essa identifica��o
                if($campos[$i]['ativo'] == 0) echo '<font color="red"><b> (EXCLU�DO) </b></font>';
            ?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}else if($passo == 2) {
//1) Nessa Primeira Parte eu chamo a Fun��o de Visualizar Estoque ...
    $nao_chamar_biblioteca = 1;
    $nivel_reduzido = 1;
    require('../../classes/estoque/visualizar_estoque.php');
//2) Aqui � a Parte de Inser��o de OP normalmente ...
    $sql = "SELECT gpa.`prazo_entrega`, pa.`referencia`, u.`sigla` 
            FROM `produtos_acabados` pa 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos = bancos::sql($sql);
    
    /*****************************Compra Produ��o******************************/
    //Verifico primeiro a Produ��o porque este � mais leve do que a Compra ...
    $vetor_estoque_pa   = estoque_acabado::qtde_estoque($id_produto_acabado, 0);
    $producao           = $vetor_estoque_pa[2];
    $compra             = estoque_acabado::compra_producao($id_produto_acabado);
    /**************************************************************************/
?>
<html>
<title>.:: Incluir OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Quantidade a Produzir
    var referencia 	= '<?=$campos[0]['referencia'];?>'
    var sigla 		= '<?=$campos[0]['sigla'];?>'
    var caracteres_aceitaveis = (sigla == 'KG') ? '0123456789,.' : '0123456789'
    
    if(!texto('form', 'txt_qtde_produzir', '1', caracteres_aceitaveis, 'QTDE � PRODUZIR', '1')) {
        return false
    }
//Data de Emiss�o
    if(!data('form', 'txt_data_emissao', "4000", 'EMISS�O')) {
        return false
    }
//Prazo de Entrega em Dias
    if(!texto('form', 'txt_prazo_entrega_dias', '1', '0123456789', 'PRAZO DE ENTREGA EM DIAS', '2')) {
        return false
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
/**********************************Prazo do Depto. T�cnico**********************************/
/*Verifico se o Prazo do Depto. T�cnico est� condizendo com o Prazo de Entrega que vem 
sugerido pelo Grupo ...*/
    if(typeof(iframe_pedidos_atrelados) != 'undefined') {
        primeiro_prazo_depto_tecnico = eval(iframe_pedidos_atrelados.document.form.primeiro_prazo_depto_tecnico.value)
        if(primeiro_prazo_depto_tecnico != 0) {//Significa que existe algum Prazo do Depto. T�cnico ...
            if(primeiro_prazo_depto_tecnico != document.form.txt_prazo_entrega_dias.value) {
                resposta1 = confirm('O PRAZO DE ENTREGA DA OP EST� DIFERENTE DO PRAZO DE ENTREGA DO DEPTO. T�CNICO !!!\n DESEJA CONTINUAR ?')
                if(resposta1 == false) {//Para o c�digo p/ q o usu�rio possa estar corrigindo o Valor ...
                    document.form.txt_prazo_entrega_dias.focus()
                    document.form.txt_prazo_entrega_dias.select()
                    return false
                }
            }
        }
    }
/*******************************************************************************************/
    var qtde_produzir = eval(document.form.txt_qtde_produzir.value)
    var qtde_lote_custo = eval(document.form.txt_qtde_lote_custo.value)
//Aqui eu verifico se a Qtde a Produzir est� dentro da especifica��o ...
    if((qtde_produzir / qtde_lote_custo > 1.15) || (qtde_produzir / qtde_lote_custo < 0.85)) {
        var resposta2 = confirm('O LOTE DO CUSTO � DE '+qtde_lote_custo+' P�S !!!\n\nA QUANTIDADE A PRODUZIR EST� COM DIFEREN�A SUPERIOR A 15% EM RELA��O A QTDE LOTE DO CUSTO !!!\n\nDESEJA INCLUIR ESSA OP ?')
        if(resposta2 == false) {//N�o submete o formul�rio p/ que o usu�rio possa estar corrigindo ...
            document.form.txt_qtde_produzir.focus()
            document.form.txt_qtde_produzir.select()
            document.form.hdd_lote_diferente_custo.value = 'N'
            return false
        }else {
            document.form.hdd_lote_diferente_custo.value = 'S'
        }
    }
    document.form.txt_data_emissao.disabled = false
    document.form.txt_prazo_entrega.disabled = false
    document.form.passo.value = 3
    limpeza_moeda('form', 'txt_qtde_produzir, ')
}

function verificar_compra_producao() {
    var referencia  = '<?=$campos[0]['referencia'];?>'
    var compra      = '<?=$compra;?>'
    var producao    = '<?=$producao;?>'
    
    if(referencia == 'ESP') {//Somente em itens Especiais "ESP" ...
        if(compra > 0 || producao > 0) alert('J� EXISTE "COMPRA / PRODU��O" DESTE ITEM NA OP !!!')
    }
}

function verificar() {
    if(document.form.txt_prazo_entrega_dias.value == '') {
        document.form.txt_prazo_entrega.value = ''
    }else {
        if(document.form.txt_prazo_entrega_dias.value != '') {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_prazo_entrega', 'document.form.txt_prazo_entrega_dias')
        }
    }
}

function visualizar_pis() {
//Quantidade a Produzir
    if(!texto('form', 'txt_qtde_produzir', '1', '0123456789', 'QTDE � PRODUZIR', '1')) {
        return false
    }
    nova_janela('visualizar_pis.php?id_produto_acabado=<?=$id_produto_acabado;?>&nova_qtde_produzir='+document.form.txt_qtde_produzir.value, 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_custo(id_produto_acabado, operacao_custo) {
    if(operacao_custo == 0) {//Industrial
        nova_janela('../custo/industrial/custo_industrial.php?id_produto_acabado='+id_produto_acabado+'&tela=2&pop_up=1', 'CUSTO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Revenda
        nova_janela('../custo/revenda/custo_revenda.php?id_produto_acabado='+id_produto_acabado, 'CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function alterar_cadastro_pa(id_produto_acabado) {
    nova_janela('../cadastros/produto_acabado/alterar.php?passo=1&id_produto_acabado='+id_produto_acabado+'&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')
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
</Script>
&nbsp;
<body onload='verificar_compra_producao();document.form.txt_qtde_produzir.focus()'>
<form name='form' method='post' onsubmit='return validar()'>
<!--*************************Controles de Tela*************************-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='operacao_custo' value='<?=$operacao_custo;?>'>
<input type='hidden' name='passo' value='2'>
<input type='hidden' name='hdd_lote_diferente_custo' value='N'>
<!--*******************************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir OP
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde � Produzir:</b>
        </td>
        <td>
            <?$onkeyup = ($campos[0]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '', event);if(this.value == '0.00') {this.value = ''}" : "verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}";?>
            <input type='text' name="txt_qtde_produzir" title="Digite a Quantidade a Produzir" maxlength="11" size="12" onkeyup="<?=$onkeyup;?>" class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_visualizar_pis' value="Visualizar PI's" title="Visualizar PI's" onClick='visualizar_pis()' class='caixadetexto'>
        </td>
        <td>
            <b>Qtde Lote do Custo:</b>
        </td>
        <td>
        <?
            //Aqui eu fa�o a Busca da Qtde do Lote do Custo ...
            $sql = "SELECT qtde_lote 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
            $campos_lote = bancos::sql($sql);
        ?>
            <input type='text' name="txt_qtde_lote_custo" value="<?=$campos_lote[0]['qtde_lote'];?>" title="Quantidade do Lote do Custo" maxlength="11" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Compra Produ��o:</b>
        </td>
        <td colspan='3'>
            <?=number_format($compra, 2, ',', '.').' / '.number_format($producao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estoque Comprometido:</b>
        </td>
        <td colspan='2'>
        <?
            if($estoque_comprometido < 0) {
                $font = '<font color="red"><b>';
            }else {
                $font = '<font color="blue"><b>';
            }
            echo $font.number_format($estoque_comprometido, 2, ',', '.');
        ?>
            &nbsp;
            <input type='button' name='cmd_alterar_custo' value='Alterar Custo' title='Alterar Custo' onclick="alterar_custo('<?=$id_produto_acabado;?>', '<?=$operacao_custo;?>')" class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_alterar_cadastro_pa' value='Alterar Cadastro P.A.' title='Alterar Cadastro P.A.' onclick="alterar_cadastro_pa('<?=$id_produto_acabado;?>')" class='caixadetexto'>
            &nbsp;
            <font color='black'>
                <b>P�s / Corte:</b>
            </font>
        </td>
        <td>
        <?
            //Busca a qtde de pe�as do PA que ser� gerado OP ...
            $sql = "SELECT `peca_corte` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
            $campos_pecas_corte = bancos::sql($sql);
            $pecas_corte        = ($campos_pecas_corte[0]['peca_corte'] == 0) ? 1 : $campos_pecas_corte[0]['peca_corte'];
        ?>
            <input type='text' name="txt_pecas_corte" value="<?=$pecas_corte;?>" title="P�s / Corte" maxlength="10" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emiss�o: 
        </td>
        <td>
            <input type='text' name="txt_data_emissao" value="<?=date('d/m/Y');?>" title="Data de Emiss�o" maxlength="10" size="12" class='textdisabled' disabled>
        </td>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>
            <font color="#0000FF">
                <input type='text' name="txt_prazo_entrega_dias" value="<?=$campos[0]['prazo_entrega'];?>" title="Digite o Prazo de Entrega em Dias" size="5" maxlength='3' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this);verificar()" class='caixadetexto'> DIAS &nbsp;&nbsp;
                <input type='text' name="txt_prazo_entrega" value="<?=data::adicionar_data_hora(date('d/m/Y'), $campos[0]['prazo_entrega']);?>" title="Data do Prazo de Entrega" size="12" maxlength="10" class='textdisabled' disabled>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Observa��o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <textarea name='txt_observacao' title="Digite a Observa��o" cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_qtde_produzir.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
//3) Listagem de todos os Pedidos que est�o atrelados a este Produto Acabado e q estejam em aberto ...
    $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, pv.`faturar_em`, pv.`condicao_faturamento`, 
            pv.`liberado`, pvi.`id_pedido_venda`, pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`qtde_pendente`, 
            pvi.`vale` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' 
            AND pvi.`status` < '2' ORDER BY pv.`id_pedido_venda` DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 1) {
/***************************************************************************************/
/*Consultar Compras, esse arquivo de Baixas, Manipula��es, faz um apontamento p/ 
o arquivo de Compras, o Roberto pediu p/ que ficasse tudo unificado em um �nico arquivo*/
?>
    <center>
        <iframe style='backgroud:#ccff00' name='iframe_pedidos_atrelados' id='iframe_pedidos_atrelados' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='auto' title='Pedidos Atrelado(s)' width='980' height='300' src='pedidos_atrelados.php?id_produto_acabado=<?=$id_produto_acabado;?>'></iframe>
    </center> 
<?
/***************************************************************************************/
    }
}else if($passo == 3) {
    $data_emissao 	= data::datatodate($_POST['txt_data_emissao'], '-');
    $prazo_entrega 	= data::datatodate($_POST['txt_prazo_entrega'], '-');
    $data_ocorrencia    = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO `ops` (`id_op`, `id_produto_acabado`, `qtde_produzir`, `data_emissao`, `prazo_entrega`, `observacao`, `data_ocorrencia`, `lote_diferente_custo`) VALUES (NULL, '$_POST[id_produto_acabado]', '$_POST[txt_qtde_produzir]', '$data_emissao', '$prazo_entrega', '$_POST[txt_observacao]', '$data_ocorrencia', '$_POST[hdd_lote_diferente_custo]') ";
    bancos::sql($sql);
    estoque_acabado::atualizar_producao($_POST['id_produto_acabado']);
    $id_op = bancos::id_registro();

    //Busca da Refer�ncia do PA da OP e verifico se esse possui desenho ...
    $sql = "SELECT `referencia`, `desenho_para_op` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    if($campos_pa[0]['desenho_para_op'] != '') {//Existe desenho p/ o PA da OP, ent�o abro a Tela de Impress�o da OP p/ facilitar ...
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'Javascript'>
        var referencia = '<?=$campos_referencia[0]['referencia'];?>'
        if(referencia == 'ESP') alert('ANTES DE IMPRIMIR N�O SE ESQUE�A DE MUDAR O PAPEL PARA \n\n\nA   M   A   R   E   L   O !')
        nova_janela('relatorio/relatorio.php?id_op=<?=$id_op;?>', 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    </Script>
<?
    }else {//N�o existe desenho p/ o PA da OP, ent�o pergunto p/ o usu�rio se este deseja Imprim�-la p/ facilitar ...
?>
    <Script Language = 'Javascript'>
        var resposta = confirm('O PRODUTO ACABADO DESSA OP N�O POSSUI DESENHO !!!\n\nDESEJA IMPRIMIR MESMO ASSIM ESSA OP ?')
        if(resposta == true) {
            var referencia = '<?=$campos_referencia[0]['referencia'];?>'
            if(referencia == 'ESP') alert('ANTES DE IMPRIMIR N�O SE ESQUE�A DE MUDAR O PAPEL PARA \n\n\nA   M   A   R   E   L   O !')
            nova_janela('relatorio/relatorio.php?id_op=<?=$id_op;?>', 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    </Script>
<?
    }
    //Aqui j� � a rotina normal, o Sistema volta p/ a Tela de Filtro ...
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?id_op=<?=$id_op;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Incluir Nova OP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3;i ++)  document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

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
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Incluir Nova OP
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Acabados por: Refer�ncia' id='label' checked>
            <label for='label'>
                Refer�ncia
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Acabados por: Discrimina��o' id='label2'>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="3" onclick='document.form.txt_consultar.focus()' title="Consultar Produtos Acabados por: Grupo P.A. / Empresa Divis�o" id='label3'>
            <label for='label3'>
                Grupo P.A. / Empresa Divis�o
            </labe>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos os Produtos Acabados' id='label4' class='checkbox'>
            <label for='label4'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>