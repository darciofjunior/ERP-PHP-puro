<?
require('../../../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../../../lib/menu/menu.php');//Se essa tela n�o foi aberta como sendo Pop-UP ent�o eu exibo o menu ...
require('../../../../../lib/data.php');
require('../../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>M�DIA MENSAL DE VENDAS ATUALIZADA COM SUCESSO.</font>";

if($passo == 1) {
    if(empty($order_by)) $order_by = 'pa.discriminacao';
//Tratamento com as vari�veis que vem por par�metro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $pop_up                     = $_POST['pop_up'];
        $id_pas_atrelados           = $_POST['id_pas_atrelados'];
        $txt_fornecedor             = $_POST['txt_fornecedor'];
        $chkt_produtos_irregulares  = $_POST['chkt_produtos_irregulares'];
        $chkt_so_custos_nao_liberados 	= $_POST['chkt_so_custos_nao_liberados'];
        $chkt_mostrar_componentes   = $_POST['chkt_mostrar_componentes'];
        $chkt_mostrar_esp           = $_POST['chkt_mostrar_esp'];
        $cmb_familia                = $_POST['cmb_familia'];
        $cmb_grupo_pa               = $_POST['cmb_grupo_pa'];
        $cmb_empresa_divisao        = $_POST['cmb_empresa_divisao'];
        $cmb_operacao               = $_POST['cmb_operacao'];
        $hidden_operacao_custo      = $_POST['hidden_operacao_custo'];
        $hidden_operacao_custo_sub  = $_POST['hidden_operacao_custo_sub'];
        $txt_referencia             = $_POST['txt_referencia'];
        $txt_discriminacao          = $_POST['txt_discriminacao'];
    }else {
        $pop_up                     = $_GET['pop_up'];
        $id_pas_atrelados           = $_GET['id_pas_atrelados'];
        $txt_fornecedor             = $_GET['txt_fornecedor'];
        $chkt_produtos_irregulares  = $_GET['chkt_produtos_irregulares'];
        $chkt_so_custos_nao_liberados   = $_GET['chkt_so_custos_nao_liberados'];
        $chkt_mostrar_componentes   = $_GET['chkt_mostrar_componentes'];
        $chkt_mostrar_esp           = $_GET['chkt_mostrar_esp'];
        $cmb_familia                = $_GET['cmb_familia'];
        $cmb_grupo_pa               = $_GET['cmb_grupo_pa'];
        $cmb_empresa_divisao        = $_GET['cmb_empresa_divisao'];
        $cmb_operacao               = $_GET['cmb_operacao'];
        $hidden_operacao_custo      = $_GET['hidden_operacao_custo'];
        $hidden_operacao_custo_sub  = $_GET['hidden_operacao_custo_sub'];
        $txt_referencia             = $_GET['txt_referencia'];
        $txt_discriminacao          = $_GET['txt_discriminacao'];
    }
    
    if(!empty($id_pas_atrelados)) {
        //S� cair� nesse caminho e realizar� essa Query quando essa tela for aberta como sendo Pop-UP ...
        $sql = "SELECT DISTINCT(pa.id_produto_acabado), pa.*, ed.`id_empresa_divisao`, 
                ed.`razaosocial`, gpa.`nome`, gpa.`prazo_entrega`, u.`unidade` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `empresas_divisoes` ed ON ged.id_empresa_divisao = ed.id_empresa_divisao 
                INNER JOIN `unidades` u ON pa.id_unidade = u.id_unidade 
                WHERE pa.`id_produto_acabado` IN ($id_pas_atrelados) 
                AND pa.`ativo` = '1' 
                GROUP BY pa.id_produto_acabado ORDER BY $order_by ";
    }else {
        //Aqui eu tenho esse Tratamento devido com o % e |, devido o usu�rio utilizar o % como caracter ...
        $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
	
        //Consulta por Fornecedor ...
        if(!empty($txt_fornecedor)) {
            $sql = "SELECT pa.id_produto_acabado 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.ativo = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' 
                    WHERE pa.`ativo` = '1' 
                    AND pa.`referencia` <> 'ESP' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Disparo do Loop caso encontre pelo menos 1 item ...
                for($i = 0; $i < $linhas; $i++) $id_produto_acabados.= $campos[$i]['id_produto_acabado'].', ';
                //Se achar 1 item pelo menos, ent�o faz o tratamento necess�rio ...
                $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
            }else {//Se n�o achar nenhum PI, ent�o tem esse Macete ...
                $id_produto_acabados = 0;//para n�o dar erro de SQL
            }
            $condicao_fornecedor = "AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
        }
        //Somente dar� efeito para a �ltima op��o, que � a sele��o de todos os produtos
        if(!empty($chkt_produtos_irregulares)) $condicao = ' AND (pa.operacao = 9 OR pa.operacao_custo = 9) ';
        //Este checkbox surte efeito em todas as op��es
        $condicao2 = (!empty($chkt_so_custos_nao_liberados)) ? ' AND pa.status_custo = 0' : '';
        //Se estiver habilitada essa ent�o mostra tamb�m os Produtos que s�o da Fam�lia de Componentes
        $condicao3 = (!empty($chkt_mostrar_componentes)) ? '' : ' AND gpa.id_familia <> 23 ';//Desabilitado s� n�o mostra Tipo Componentes ..
        //Se tiver habilitada essa op��o, ent�o mostra todos os P.A(s) que s�o Esp tamb�m ...
        $condicao4 = (!empty($chkt_mostrar_esp)) ? '' : " AND pa.referencia <> 'ESP' ";//Desabilitado s� n�o mostro os P.A(s) que s�o ESP ...
        if($cmb_familia == '')          $cmb_familia = '%';
        if($cmb_grupo_pa == '')         $cmb_grupo_pa = '%';
        if($cmb_empresa_divisao == '')  $cmb_empresa_divisao = '%';
        if($cmb_operacao == '')         $cmb_operacao = '%';
        /*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
        armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
        if($hidden_operacao_custo == 1) {//Opera��o de Custo = Industrial
            $cmb_operacao_custo = 0;
        }else if($hidden_operacao_custo == 2) {//Opera��o de Custo = Revenda
            $cmb_operacao_custo = 1;
        }else {//Independente da Opera��o de Custo
            if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
        }
        //Segunda adapta��o
        if($hidden_operacao_custo_sub == 1) {//Sub-Opera��o de Custo = Industrial
            $cmb_operacao_custo_sub = 0;
        }else if($hidden_operacao_custo_sub == 2) {//Sub-Opera��o de Custo = Revenda
            $cmb_operacao_custo_sub = 1;
        }else {//Independente da Sub-Opera��o de Custo
            if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
        }
    
        $sql = "SELECT DISTINCT(pa.id_produto_acabado), pa.*, ed.`id_empresa_divisao`, 
                ed.`razaosocial`, gpa.`nome`, gpa.`prazo_entrega`, u.`unidade` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' 
                INNER JOIN `empresas_divisoes` ed ON ged.id_empresa_divisao = ed.id_empresa_divisao AND ed.id_empresa_divisao LIKE '$cmb_empresa_divisao' 
                INNER JOIN `unidades` u ON pa.id_unidade = u.id_unidade 
                WHERE pa.`referencia` LIKE '%$txt_referencia%' 
                AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
                AND pa.`operacao_custo` LIKE '$cmb_operacao_custo' 
                AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
                AND pa.`operacao` LIKE '$cmb_operacao' 
                AND pa.`ativo` = '1' 
                $condicao $condicao2 $condicao3 $condicao4 $condicao_fornecedor 
                GROUP BY pa.id_produto_acabado ORDER BY $order_by ";
    }
    $campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        var nao_perguntar_novamente = eval('<?=$nao_perguntar_novamente;?>')
/*Significa que j� foi feita uma pergunta referente ao Filtro anteriormente e sendo assim
s� ir� redirecionar p/ a Tela de Filtro novamente ...*/
        if(nao_perguntar_novamente == 1) {
            window.location = 'mmv.php?valor=1'
        }else {
/*Se n�o foi encontrado nenhum P.A. pelo filtro normal, ent�o o Sistema pergunta p/ o usu�rio 
se ele deseja visualizar os ESP(s) de acordo com o Filtro que ele fez ...*/
            var resposta = confirm('DESEJA CONSULTAR OS ESPECIAIS ?')
            if(resposta == true) {//Ir� manter o Filtro do Usu�rio, acrescentando apenas a op��o de Especiais ...
            <?
//Aqui eu tenho esse Tratamento devido com o % e |, devido o usu�rio utilizar o % como caracter ...
                    $txt_discriminacao = str_replace('%', '|', $txt_discriminacao);
            ?>
                window.location = 'mmv.php?passo=1&txt_fornecedor=<?=$txt_fornecedor;?>&chkt_produtos_irregulares=<?=$chkt_produtos_irregulares;?>&chkt_so_custos_nao_liberados=<?=$chkt_so_custos_nao_liberados;?>&chkt_mostrar_componentes=<?=$chkt_mostrar_componentes;?>&chkt_mostrar_esp=1&cmb_familia=<?=$cmb_familia;?>&cmb_grupo_pa=<?=$cmb_grupo_pa;?>&cmb_empresa_divisao=<?=$cmb_empresa_divisao;?>&cmb_operacao=<?=$cmb_operacao;?>&hidden_operacao_custo=<?=$hidden_operacao_custo;?>&hidden_operacao_custo_sub=<?=$hidden_operacao_custo_sub;?>&txt_referencia=<?=$txt_referencia;?>&txt_discriminacao=<?=$txt_discriminacao;?>&nao_perguntar_novamente=1'
            }else {
                window.location = 'mmv.php?valor=1'
            }
        }
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: M�dia Mensal de Vendas ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'mmv.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos   = document.form.elements
    var valor       = false

    if(typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_produto_acabado[]'].length)
    }

    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_acabado'+i).checked == true && document.getElementById('chkt_produto_acabado'+i).disabled == false) {
            valor = true
            break
        }
    }

    if(valor == false) {//N�o tem nenhuma op��o de MMV selecionada ...
        alert('SELECIONE UMA OP��O !')
        return false
    }else {//Tem pelo menos uma op��o selecionada ...
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {//Se estiver checado o Shinebox (rsrs) ...
                if(document.getElementById('txt_mmv_inst_corr'+i).value == '') {
                    alert('DIGITE O MMV !')
                    document.getElementById('txt_mmv_inst_corr'+i).focus()
                    return false
                }
                var ajuste_mmv      = (document.getElementById('txt_ajuste_mmv'+i).value != '') ? eval(strtofloat(document.getElementById('txt_ajuste_mmv'+i).value)) : 0
                var mmv_inst_corr   = eval(strtofloat(document.getElementById('txt_mmv_inst_corr'+i).value))
                
                if(ajuste_mmv < -1000) {
                    alert('AJUSTE MMV INV�LIDO !!!\n\nAJUSTE MMV < -1000 !')
                    document.getElementById('txt_ajuste_mmv'+i).focus()
                    document.getElementById('txt_ajuste_mmv'+i).select()
                    return false
                }
                
                if(ajuste_mmv + mmv_inst_corr < 0) {
                    alert('O SOMAT�RIO DOS CAMPOS: \n\n"AJUSTE MMV" + "MMV INST. CORR" N�O PODEM SER < 0 !!!')
                    document.getElementById('txt_ajuste_mmv'+i).focus()
                    document.getElementById('txt_ajuste_mmv'+i).select()
                    return false
                }
            }
        }
        //Prepara para gravar no BD ...
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked == true) {//Se estiver checado o Shinebox (rsrs) ...
                //Deixa no Formato em que o Banco de Dados vai reconhecer ...
                document.getElementById('txt_ajuste_mmv'+i).value       = strtofloat(document.getElementById('txt_ajuste_mmv'+i).value)
                document.getElementById('txt_mmv_inst_corr'+i).value    = strtofloat(document.getElementById('txt_mmv_inst_corr'+i).value)
                //Desabilito p/ poder Gravar no Banco ...
                document.getElementById('txt_ajuste_mmv'+i).disabled    = false
                document.getElementById('txt_mmv_inst_corr'+i).disabled = false
            }
        }
        //Aqui eu desabilito o bot�o de atualizar p/ o usu�rio n�o clicar + de uma vez ...
        document.form.cmd_atualizar.disabled = true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<!--***********************Controle de Tela***********************-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='id_pas_atrelados' value='<?=$id_pas_atrelados;?>'>
<!--**************************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Consultar Produto(s) Acabado(s) - M�dia Mensal de Vendas
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Grupo P.A. / Empresa Divis�o
        </td>
        <td>
            Ref
        </td>
        <td>
            Discrimina��o
        </td>
        <td>
            Data de <br>Inclus�o <br>do P.A.
        </td>
        <td>
            Total P�s em ORCs - �lt<br>(12 meses)
        </td>
        <td>
            Total P�s em Vendas - �lt<br>(12 Meses)
        </td>
        <td>
            MMV Gravado
        </td>
        <td>
            Ajuste MMV
        </td>
        <td>
            MMV Inst. Corr.
        </td>
    </tr>
<?
        $ano_anterior 	= data::datatodate(data::adicionar_data_hora(date('d-m-Y'),-365), '-');//uma ano atr�s ...
        $ano_atual      = date('Y-m-d');

        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
            &nbsp;
            <a href = '../../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1' class='html5lightbox'>
                <img src = '../../../../../imagem/visualizar_detalhes.png' title="Visualizar Pedidos - �ltimos 6 meses" alt="Visualizar Pedidos - �ltimos 6 meses" border='0'>
            </a>
            &nbsp;
            <a href = '../../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1' class='html5lightbox'>
                <img src = '../../../../../imagem/propriedades.png' title='Visualizar Or�amentos - �ltimos 6 meses' alt='Visualizar Or�amentos - �ltimos 6 meses' border='0'>
            </a>
            &nbsp;
            <img src = "../../../../../imagem/menu/alterar.png" border='0' title="Alterar Produto Acabado" alt="Alterar Produto Acabado" onClick="nova_janela('../alterar.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/');?>
        </td>
        <td align='right'>
        <?
            /*$sql = "SELECT SUM(ovi.qtde) AS qtde_pecas_1_ano 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.data_emissao BETWEEN '$ano_anterior' AND '$ano_atual' 
                    WHERE ovi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_total_qtde = bancos::sql($sql);
            echo segurancas::number_format($campos_total_qtde[0]['qtde_pecas_1_ano'], 2, '.');*/
        ?>
        </td>
        <?
//Retorna em um Array o resultado da M�dia Mensal de Vendas
            $resultado = vendas::media_mensal_venda($campos[$i]['id_produto_acabado']);
        ?>
        <td align='right'>
            <?=segurancas::number_format($resultado['qtde_vendida'], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['mmv'], 2, '.');?>
        </td>
        <td>
            <input type='text' name='txt_ajuste_mmv[]' id='txt_ajuste_mmv<?=$i;?>' value='<?=number_format($campos[$i]['ajuste_mmv'], 2, ',', '.');?>' maxlength='8' size='8' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='textdisabled' disabled>
        </td>
        <td>
        <?
            /*
            Comentamos o trecho abaixo no dia 26/03/2015 pois agora utilizamos o maior MMV entre 6 e 12 meses ...

            if($campos[$i]['id_gpa_vs_emp_div'] == 122) {//Macho Manual HSS ...
                $fator_correcao = 1.5;//Estamos considerando que o MMV � 50% maior pois esperamos o incremento de Vendas nos pr�ximos meses ...
            }else if($campos[$i]['id_gpa_vs_emp_div'] == 123) {//Macho Manual WS ...
                $fator_correcao = 1.3;//Estamos considerando que o MMV � 30% maior pois esperamos o incremento de Vendas nos pr�ximos meses ...
            }else if($campos[$i]['id_gpa_vs_emp_div'] == 44 || $campos[$i]['id_gpa_vs_emp_div'] == 46) {//Suportes Intercambi�veis ou Cossinetes TOP(s) ...
                $fator_correcao = 0;//Porque paramos de Fabricar esses PA(s) ...
            }else {
                $fator_correcao = 1;
            }
            //$mmv = ($resultado['mmv'] > 0) ? number_format($resultado['mmv'] * $fator_correcao, 2, ',', '.') : '0,00';*/
        
            $mmv = ($resultado['mmv'] > 0) ? number_format($resultado['mmv'], 2, ',', '.') : '0,00';
        ?>
            <input type='text' name='txt_mmv_inst_corr[]' id='txt_mmv_inst_corr<?=$i;?>' value="<?=$mmv;?>" maxlength='8' size='8' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
        <?
            //if($fator_correcao != 1) echo '<font color="red" title="Acr�scimo de Previs�o de Vendas" style="cursor:help"><b> '.(($fator_correcao - 1) * 100).'% </b></font>';
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick = "window.location = 'mmv.php'" class='botao'>
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='10'>
<?
    if($valor == 2) {
        echo $mensagem[$valor];
    }else {
?>
        <font class='erro'>
            EST� PAGINA AINDA N�O FOI ATUALIZADA.
        </font>
<?        
    }
?>
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
}else if ($passo == 2) {
    foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado) {
        $sql = "UPDATE `produtos_acabados` SET `ajuste_mmv` = '".$_POST['txt_ajuste_mmv'][$i]."', `mmv` = '".($_POST['txt_ajuste_mmv'][$i] + $_POST['txt_mmv_inst_corr'][$i])."' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'mmv.php<?=$parametro?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: M�dia Mensal de Vendas ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
    document.form.cmb_operacao_custo_sub.className  = 'textdisabled'
    document.form.cmb_operacao_custo_sub.disabled   = true
    document.form.txt_referencia.focus()
}

//Controle com a Opera��o de Custo
function controle_hidden_operacao_custo() {
    var operacao_custo = document.form.cmb_operacao_custo[document.form.cmb_operacao_custo.selectedIndex].text
//Se n�o estiver selecionada nenhuma Opera��o de Custo
    if(operacao_custo == 'SELECIONE') {
        document.form.hidden_operacao_custo.value = ''
    }else if(operacao_custo == 'Industrializa��o') {
        document.form.hidden_operacao_custo.value = 1
    }else if(operacao_custo == 'Revenda') {
        document.form.hidden_operacao_custo.value = 2
    }
}

//Controle com a Sub-Opera��o de Custo
function controle_hidden_operacao_custo_sub() {
    var operacao_custo_sub = document.form.cmb_operacao_custo_sub[document.form.cmb_operacao_custo_sub.selectedIndex].text
//Se n�o estiver selecionada nenhuma Sub-Opera��o de Custo
    if(operacao_custo_sub == 'SELECIONE') {
        document.form.hidden_operacao_custo_sub.value = ''
    }else if(operacao_custo_sub == 'Industrializa��o') {
        document.form.hidden_operacao_custo_sub.value = 1
    }else if(operacao_custo_sub == 'Revenda') {
        document.form.hidden_operacao_custo_sub.value = 2
    }
}

function controle_operacao_custo() {
    var operacao_custo = eval(document.form.cmb_operacao_custo.value)
    if(operacao_custo == 0) {//Quando a Opera��o de Custo = Industrial, eu habilito a Sub-Opera��o de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className  = 'caixadetexto'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = false
//Quando a Opera��o de Custo = Revenda, eu desabilito a Sub-Opera��o de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className  = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = true
    }
}
</Script>
</head>
<body onload="controle_operacao_custo();iniciar()">
<form name="form" method="post" action="<?=$GLOBALS['PHP_SELF'];?>">
<input type="hidden" name="passo" value="1">
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro l� no outro
passo da consulta*/
-->
<input type="hidden" name="hidden_operacao_custo">
<input type="hidden" name="hidden_operacao_custo_sub">
<table border='0' width="70%" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) - M�dia Mensal de Vendas
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Refer�ncia
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Refer�ncia" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina��o
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discrimina��o" size="30" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name="txt_fornecedor" title="Digite o Fornecedor" size="35" class='caixadetexto'> <b>* Somente Produtos normais de Linha</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fam�lia
        </td>
        <td>
            <select name="cmb_familia" title="Selecione a Fam�lia" class="combo">
            <?
                $sql = "SELECT `id_familia`, `nome` 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo PA
        </td>
        <td>
            <select name="cmb_grupo_pa" title="Selecione o Grupo P.A." class="combo">
            <?
                $sql = "SELECT `id_grupo_pa`, `nome` 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divis�o
        </td>
        <td>
            <select name="cmb_empresa_divisao" title="Selecione a Empresa Divis�o" class="combo">
            <?
                $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Opera��o de Custo
        </td>
        <td>
            <select name="cmb_operacao_custo" title="Selecione a Opera��o de Custo" onchange="controle_operacao_custo();controle_hidden_operacao_custo()" class="combo">
                <option value="" style="color:red" selected>SELECIONE</option>
                <option value="0">Industrializa��o</option>
                <option value="1">Revenda</option>
            </select>
            &nbsp;
            <select name="cmb_operacao_custo_sub" title="Selecione a Sub-Opera��o" onchange="controle_hidden_operacao_custo_sub()" class='textdisabled' disabled>
                <option value="" style="color:red" selected>SELECIONE</option>
                <option value="0">Industrializa��o</option>
                <option value="1">Revenda</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Opera��o (Fat)
        </td>
        <td>
            <select name="cmb_operacao" title="Selecione a Opera��o (Fat)" class="combo">
                <option value="" style="color:red" selected>SELECIONE</option>
                <option value="0">Industrializa��o (c/ IPI)</option>
                <option value="1">Revenda (s/ IPI)</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_produtos_irregulares' value='1' title="Produtos Irregulares" id='label1' class='checkbox'>
            <label for='label1'>
                Todos Produtos Irregulares
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title="S� Custos n�o Liberados" class='checkbox' id='label2'>
            <label for='label2'>
                S� Custos n�o Liberados
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" class='checkbox' id='label3'>
            <label for='label3'>
                Mostrar Componentes
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_esp' value='1' title="Mostrar ESP" class='checkbox' id='label4'>
            <label for='label4'>
                Mostrar ESP
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="controle_operacao_custo();iniciar()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color="blue">L�gica de MMV</font>
- Seleciono o qtde total do itens comprado no �ltimo ano.
- Seleciono a data de inclus�o do P.A. desde q seja na condi��o acima, ou seja, comprado no �ltimo ano 
- se nao tiver historico
	retorno 0,00
  se n�o
	$diff_dias  = (Calculo a diferen�a de data entre a data atual e a data de inclus�o do P.A./ 30
    retorno $qtde_comprada/$diff_dias
</pre>