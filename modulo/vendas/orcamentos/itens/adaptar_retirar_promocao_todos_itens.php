<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');

segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SELECIONE UMA PROMO��O P/ ADAPTAR NESTE OR�AMENTO !</font>";
$mensagem[2] = "<font class='erro'>N�O EXISTE(M) ITEM(NS) EM QUE SEJA POSS�VEL COLOCAR ESTE TIPO DE PRE�O PROMOCIONAL !</font>";

//Tratamento com as vari�veis que vem por par�metro ...
$id_orcamento_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_orcamento_venda'] : $_GET['id_orcamento_venda'];

if($passo == 1) {
    foreach ($_POST['chkt_orcamento_venda_item'] as $i => $id_orcamento_venda_item) {
        /*$preco_liq_fat = vendas::calcular_orcamentos_itens($_POST['id_orcamento_venda'], $id_orcamento_venda_item);
        $preco_liq_fat = str_replace('.', '', $preco_liq_fat);
        $preco_liq_fat = str_replace(',', '.', $preco_liq_fat);*/
        
        //Aqui eu busco o Pre�o L�q. Fat que est� gravado no Item de Venda de Or�amento ...
        $sql = "SELECT `preco_liq_fat` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $preco_liq_fat  = $campos[0]['preco_liq_fat'];

        if($preco_liq_fat != 'DEPTO T�CNICO' && $preco_liq_fat != 'Or�ar') {
            if($preco_liq_fat == 0) {//Se n�o existir pre�o L�quido Faturado do PA, ent�o retorna um dos alerts abaixo ...
                if($_POST['hdd_referencia'][$i] == 'ESP') {//Especial - Depto. T�cnico ...
                    exit('O PRE�O L�QUIDO FATURADO = R$ 0,00 ! PRECISA SER DEFINIDO O CUSTO DESSE PRODUTO, AVISAR DEPTO. T�CNICO !!!');
                }else {//Normal de Linha - Roberto ...
                    exit('O PRE�O L�QUIDO FATURADO = R$ 0,00 ! PRECISA SER DEFINIDO O PRE�O DE LISTA DESSE PRODUTO, AVISAR ROBERTO !!!');
                }
            }
        }else {
            $preco_liq_fat = 1;
        }
        
        $vetor_valores  = vendas::alt_c($preco_liq_fat, $_POST['hdd_desc_cliente'][$i], $_POST['txt_preco_promocional'][$i]);
        
        //Controle com a Parte de Promo��o ...
        if($_POST['cmb_promocao'] == 1) {//N�o ser� mais usado, aki � o modo antigo ...
            $promocao = 'S';
        }else if($_POST['cmb_promocao'] == 'A') {//Modo Novo
            $promocao = 'A';
        }else if($_POST['cmb_promocao'] == 'B') {//Modo Novo
            $promocao = 'B';
        }else if($_POST['cmb_promocao'] == 'C') {//Modo Novo
            $promocao = 'C';
        }else {
            $promocao = 'N';
        }
        
        //N�o existe acr�scimo quando se aplica promo��o, por isso que eu passo o valor como sendo Zero ...
        $sql = "UPDATE `orcamentos_vendas_itens` SET `id_produto_acabado_discriminacao` = NULL, `qtde` = '".$_POST['txt_quantidade'][$i]."', `promocao` = '$promocao', `desc_extra` = '".$vetor_valores['desconto_extra']."', `acrescimo_extra` = '0', `preco_liq_final` = '".$_POST['txt_preco_promocional'][$i]."' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
/*******************************************************************************************************/
        vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item);
        //Aqui eu atualizo a ML Est do Iem do Or�amento ...
        custos::margem_lucro_estimada($id_orcamento_venda_item);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
        vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $id_orcamento_venda_item);
    }
?>
    <Script Language = 'JavaScript'>
        alert('PROMO��O(�ES) ADAPTADA(S) COM SUCESSO !')
        parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
    </Script>
<?	
}else {
    $cmb_promocao = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_promocao'] : $_GET['cmb_promocao'];
    
    //Verifico a Situa��o do Or�amento passado por par�metro ...
    $sql = "SELECT `nota_sgd` 
            FROM `orcamentos_vendas` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $nota_sgd   = $campos[0]['nota_sgd'];

    if($cmb_promocao == '') {//Se n�o foi selecionado nenhum tipo de Promo��o ...
        $selected                   = 'selected';
    }else if($cmb_promocao == 'A') {
        $selecteda                  = 'selected';
        $condicao                   = " AND pa.`preco_promocional` <> '0' ";
        $campo_preco_promocional    = ', pa.`preco_promocional` AS preco_promocional ';
        $campo_qtde_promocional     = ', pa.`qtde_promocional` AS qtde_promocional ';
    }else if($cmb_promocao == 'B') {
        $selectedb                  = 'selected';
        $condicao                   = " AND pa.`preco_promocional_b` <> '0' ";
        $campo_preco_promocional    = ', pa.`preco_promocional_b` AS preco_promocional ';
        $campo_qtde_promocional     = ', pa.`qtde_promocional_b` AS qtde_promocional_b ';
    }
?>
<html>
<head>
<title>.:: Adaptar Promo��o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'adaptar_retirar_promocao_todos_itens.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }else {
        var linhas = (typeof(elementos['chkt_orcamento_venda_item[]'][0]) == 'undefined') ? 1 : elementos['chkt_orcamento_venda_item[]'].length
        for(i = 0; i < linhas; i++) {
            //Aqui nessa parte do Script compara a quantidade de pe�as por embalagem para os produtos normais de linha
            if(document.getElementById('hdd_referencia'+i).value != 'ESP') {
                //Essa verifica��o abaixo s� ser� feita quando for selecionada um Tipo de Promo��o na Combo ...
                if(document.form.cmb_promocao.value != '') {
                    if(document.getElementById('txt_quantidade'+i).value > document.getElementById('txt_quantidade_promocional'+i).value) {
                        var pergunta_a = confirm('QUANTIDADE ABAIXO DA QUANTIDADE PROMOCIONAL !!!       SUGEST�O  =  '+document.getElementById('txt_quantidade_promocional'+i).value+'  . CONFIRMA A QUANTIDADE ? ')
                        if(pergunta_a == false) {
                            document.getElementById('txt_quantidade'+i).focus()
                            document.getElementById('txt_quantidade'+i).select()
                            return false
                        }
                    }
                }
                //Verifica o Mod (Resto da Divis�o)
                var resto_divisao = eval(document.getElementById('txt_quantidade'+i).value) % (document.getElementById('hdd_pcs_embalagem'+i).value)
                if(resto_divisao != 0 && !isNaN(resto_divisao)) {//N�o est� Compat�vel
                    var sugestao = (parseInt(document.getElementById('txt_quantidade'+i).value / document.getElementById('hdd_pcs_embalagem'+i).value) + 1) * document.getElementById('hdd_pcs_embalagem'+i).value
                    //Se a Fam�lia = 'PINOS' ou Fam�lia = 'CHAVES PARA MANDRIL', n�o d� op��o p/ o usu�rio abrir a embalagem ...
                    if(document.getElementById('hdd_familia'+i).value == 2 || document.getElementById('hdd_familia'+i).value == 19) {
                        alert('A QTDE DO     '+document.getElementById('hdd_referencia'+i).value+'     N�O EST� COMPAT�VEL COM A QTDE DE P�S / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGEST�O  =  '+sugestao+'  .')
                        document.getElementById('txt_quantidade'+i).focus()
                        document.getElementById('txt_quantidade'+i).select()
                        return false
                    }else {
                        var pergunta = confirm('A QTDE DO     '+document.getElementById('hdd_referencia'+i).value+'     N�O EST� COMPAT�VEL COM A QTDE DE P�S / EMBALAGEM ! \n DESEJA MANTER EST� QUANTIDADE ?        SUGEST�O  =  '+sugestao+'  .')
                        if(pergunta == false) {//N�o aceitou a qtde incompat�vel
                            document.getElementById('txt_quantidade'+i).focus()
                            document.getElementById('txt_quantidade'+i).select()
                            return false
                        }
                    }
                }
            }
        }
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true) {
                document.getElementById('txt_preco_liq_final'+i).value = strtofloat(document.getElementById('txt_preco_liq_final'+i).value)
                document.getElementById('txt_preco_promocional'+i).value = strtofloat(document.getElementById('txt_preco_promocional'+i).value)
            }
        }
        document.form.action = '<?=$PHP_SELF;?>?passo=1'
        document.getElementById('lbl_mensagem').innerHTML = '<img src="../../../../css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<!--****************-->
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Adaptar Promo��o p/ todos os Itens do Or�amento N.�&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
            &nbsp;-&nbsp;
            <select name='cmb_promocao' title='Selecione a Promo��o' onchange='document.form.submit()' class='combo'>
                <option value='' style='color:red' <?=$selected;?>>SEM PROMO��O</option>
                <?
                    if($nota_sgd == 'N') {//Existe Pre�o A p/ o PA somente se for "NF" ...
                ?>
                <option value='A' <?=$selecteda;?>>PROMO��O A</option>
                <?
                    }

                    if($nota_sgd == 'S') {//Existe Pre�o B p/ o PA somente se for "SGD" ...
                ?>
                <option value='B' <?=$selectedb;?>>PROMO��O B</option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
<?
    //Aqui eu busco todos os Itens dos Or�amentos ...
    $sql = "SELECT gpa.`id_familia`, ovi.`id_orcamento_venda_item`, ovi.`id_produto_acabado`, ovi.`qtde`, 
            ovi.`preco_liq_fat`, ovi.`desc_cliente`, pa.`referencia` 
            $campo_preco_promocional $campo_qtde_promocional 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` $condicao 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//N�o encontrou nenhum Item de Or�amento em que � poss�vel colocar Pre�o Promocional ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar_tudo(totallinhas, '#E8E8E8')" title='Selecionar Todos' class='checkbox'>
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td>
            <font title='Quantidade Promocional' style='cursor:help'>
                Qtde <br>Promocional
            </font>
        </td>
        <td>
            <font title='Pre&ccedil;o Liq. Final / P�' style='cursor:help'>
                Pre&ccedil;o Liq. <br>Final / P�
            </font>
        </td>
        <td>
            <font title='Pre&ccedil;o Promocional' style='cursor:help'>
                Pre&ccedil;o Promocional
            </font>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_orcamento_venda_item[]' id='chkt_orcamento_venda_item<?=$i;?>' value='<?=$campos[$i]['id_orcamento_venda_item'];?>' onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox'>
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
            echo '&nbsp;';
            //Aqui eu verifico se existe alguma Promo��o ...
            if($_POST['cmb_promocao'] == 'A') {
                echo "<font color='#ff9900' title='Promo�&atilde;o A' style='cursor:help'><b>(PA)</b></font>";
            }else if($_POST['cmb_promocao'] == 'B') {
                echo "<font color='#ff9900' title='Promo�&atilde;o B' style='cursor:help'><b>(PB)</b></font>";
            }
        ?>
            <input type='hidden' name='hdd_referencia[]' id='hdd_referencia<?=$i;?>' value='<?=$campos[$i]['referencia'];?>'>
            <input type='hidden' name='hdd_familia[]' id='hdd_familia<?=$i;?>' value='<?=$campos[$i]['id_familia'];?>'>
            <input type='hidden' name='hdd_desc_cliente[]' id='hdd_desc_cliente<?=$i;?>' value='<?=$campos[$i]['desc_cliente'];?>'>
        </td>
        <td>
            <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' value="<?=(integer)$campos[$i]['qtde'];?>" onclick="checkbox('<?=$i;?>', '#E8E8E8');focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" maxlength='7' size='9' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_quantidade_promocional[]' id='txt_quantidade_promocional<?=$i;?>' value="<?=(integer)$campos[$i]['qtde_promocional'];?>" maxlength='7' size='9' class='textdisabled' disabled>
            <?
                //Traz a quantidade de pe�as por embalagem da embalagem principal daquele produto ...
                $sql = "SELECT pecas_por_emb 
                        FROM `pas_vs_pis_embs` 
                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND `embalagem_default` = '1' LIMIT 1 ";
                $campos_pecas_embalagem = bancos::sql($sql);
                $pecas_embalagem        = (count($campos_pecas_embalagem) == 1) ? number_format($campos_pecas_embalagem[0]['pecas_por_emb'], 0, ',', '.') : 0;
            ?>
            <input type='hidden' name='hdd_pcs_embalagem[]' id='hdd_pcs_embalagem<?=$i;?>' value='<?=$pecas_embalagem;?>'>
        </td>
        <td>
        <?
            //Pre�o L�quido -> "Pre�o Faturado - Desc. Cliente" do Item do ORC que j� foram gravados anteriormente ...
            $preco_liq_sem_extras = $campos[$i]['preco_liq_fat'] * (1 - $campos[$i]['desc_cliente'] / 100);
        ?>
            <input type='text' name='txt_preco_liq_final[]' id='txt_preco_liq_final<?=$i;?>' value="<?=number_format($preco_liq_sem_extras, 2, ',', '.');?>" onclick="checkbox('<?=$i;?>', '#E8E8E8');focos(this)" onfocus="document.getElementById('chkt_orcamento_venda_item<?=$i;?>').focus()" maxlength='7' size='9' class='textdisabled'>
        </td>
        <td>
            <?
                /*Se o usu�rio N�O selecionou a op��o de Promo��o na combo, ent�o o Pre�o L�quido Final ser� 
                o pr�prio Pre�o Promocional ...*/
                $preco_promocional = (empty($cmb_promocao)) ? $preco_liq_sem_extras : $campos[$i]['preco_promocional'];
            ?>
            <input type='text' name='txt_preco_promocional[]' id='txt_preco_promocional<?=$i;?>' value="<?=number_format($preco_promocional, 2, ',', '.');?>" onclick="checkbox('<?=$i;?>', '#E8E8E8');focos(this)" onfocus="document.getElementById('chkt_orcamento_venda_item<?=$i;?>').focus()" maxlength='7' size='9' class='textdisabled'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
        </td>
    </tr>
</table>
<br/>
<div align='center'>
    <font size='-2' color='#0066ff' face='verdana, arial, helvetica, sans-serif'><b>
        <center>
            <?=paginacao::print_paginacao('sim');?>
        </center>
        <br/><br/>
        <label for='chkt_congelar' id='lbl_mensagem'></label>
    </b></font>
</div>
</body>
</form>
</html>
<pre>
<font color='red'><b>Observa��o:

* O sistema s� exibe os P.A(s) que realmente possuem algum Pre�o Promocional em seu cadastro.
</b></font>
</pre>
<?}?>