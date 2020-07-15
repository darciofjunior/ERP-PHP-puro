<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');//Se arrancar essa biblioteca, dá pau nesse arquivo, pq o Total do Custo a utiliza ...
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if(!empty($_POST['id_pac_pa'])) {
    $usar_este_lote_para_orc = (!empty($_POST['chkt_usar_este_lote_para_orc'])) ? 'S' : 'N';
    
    /*****************************************************************************************************************
    Significa que o usuário marcou a opção "$usar_este_lote_para_orc" e só podemos ter essa opção marcada p/ apenas um único 
    PA nessa 7ª Etapa. Sendo assim retiro essa marcação de todos os Itens dessa Etapa e só atribuo p/ o item desejado 
    pelo usuário. Essa marcação foi criada p/ calcularmos o MMV Total e a Taxa de Estocagem no Orçamento ...*/
    if($usar_este_lote_para_orc == 'S') {
        $sql = "UPDATE `pacs_vs_pas` SET `usar_este_lote_para_orc` = 'N' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' ";
        bancos::sql($sql);
    }
    /*****************************************************************************************************************/
    $sql = "UPDATE `pacs_vs_pas` SET `qtde` = '$_POST[txt_qtde7]', `usar_este_lote_para_orc` = '$usar_este_lote_para_orc' WHERE `id_pac_pa` = '$_POST[id_pac_pa]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
//Atualização do Funcionário que alterou os dados no custo ...
    $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    
    if($_POST['hdd_adicionar_novo'] == 'S') {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_produto_acabado.php?id_produto_acabado_custo=<?=$_POST[id_produto_acabado_custo];?>'
    </Script>
<?
    }
}

$id_produto_acabado_custo   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];
//$fator_custo7             = genericas::variavel(12);//Não me lembro agora o do porque comentamos - Dárcio ???
$fator_custo7               = 1;

//Seleciona a qtde de itens que existe do produto acabado na etapa 7
$sql = "SELECT COUNT(pp.id_pac_pa) AS qtde_itens 
        FROM `pacs_vs_pas` pp 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "SELECT pa.referencia, pa.id_produto_acabado, pa.discriminacao, pa.operacao_custo, pa.preco_unitario, pp.id_pac_pa, pp.qtde, pp.usar_este_lote_para_orc, u.sigla 
        FROM `pacs_vs_pas` pp 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pa ";
if(empty($posicao)) 	$posicao = $qtde_itens;
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
?>
<html>
<head>
<title>.:: Alterar Produto Acabado / Componente ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa7() {
    var fator_custo         = eval('<?=$fator_custo7;?>')
    var qtde                = eval(strtofloat(document.form.txt_qtde7.value))
    var preco_unitario_rs   = eval(strtofloat(document.form.txt_preco_unitario_rs7.value))
    document.form.txt_total7.value = qtde * preco_unitario_rs
    document.form.txt_total7.value = (isNaN(document.form.txt_total7.value)) ? '' : arred(document.form.txt_total7.value, 2, 1)
}

function validar(posicao) {
//Força o Preenchimento da Quantidade p/ ser Digitada ...
    if(document.form.txt_qtde7.value == '') {
        alert('DIGITE A QUANTIDADE !')
        document.form.txt_qtde7.focus()
        document.form.txt_qtde7.select()
        return false
    }
    var quantidade = eval(strtofloat(document.form.txt_qtde7.value))
//Se a quantidade for igual a Zero ...
    if(quantidade == 0) {
        var resposta = confirm('A QUANTIDADE DIGITADA É IGUAL A ZERO !!!\n TEM CERTEZA QUE DESEJA CONTINUAR ?')
        if(resposta == false) {
            document.form.txt_qtde7.focus()
            document.form.txt_qtde7.select()
            return false
        }
    }
//Tratamento para gravar no Banco de Dados ...
    limpeza_moeda('form', 'txt_qtde7, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_qtde7.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>')">
    <!--********************************Controle de Tela********************************-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_pa' value="<?=$campos[0]['id_pac_pa'];?>">
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_adicionar_novo'>
<!--********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            7&ordf; Etapa: Alterar Produto Acabado / Componente
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                <font color="#FFFF00">Ref.:</font> 
                PA
                <?=$campos[0]['referencia'];?>
                - <font color="#FFFF00">Unid.:</font> 
                <?=$campos[0]['sigla'];?>
                - <font color="#FFFF00">Discrim.:</font> 
                <?=$campos[0]['discriminacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            O.C.:
        </td>
        <td>
        <?
            if($campos[0]['operacao_custo'] == 0) {//Industrialização
                echo 'Industrialização';
            }else {//Revenda
                echo 'Revenda';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            Estoque Real:
        </td>
        <td> 
        <?
//Traz a quantidade em estoque do produto acabado
            $estoque_produto    = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado'], '1');
            $estoque_real       = number_format($estoque_produto[0], 2, ',', '.');
        ?>
            <input type='text' name='txt_qtde_estoque7' value='<?=$estoque_real;?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Quantidade do Lote:
        </td>
        <td>
	<?
            $sql = "SELECT qtde_lote 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            $campos_qtde_lote = bancos::sql($sql);
	?>
            <input type='text' name='txt_qtde_lote7' value='<?=$campos_qtde_lote[0]['qtde_lote'];?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Quantidade:
        </td>
        <td>
            <input type='text' name='txt_qtde7' value='<?=number_format($campos[0]['qtde'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calculo_etapa7()" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            P. Unit. (s/ Tx + s/ Emb R$):
        </td>
        <td>
        <?
            if($campos[0]['operacao_custo'] == 0) {//Industrialização
                $preco_custo = custos::todas_etapas($campos[0]['id_produto_acabado'], 0);
            }else {
                $preco_custo = custos::pipa_revenda($campos[0]['id_produto_acabado']) / (genericas::variaveis('taxa_financeira_vendas') / 100 + 1);
            }
        ?>
            <input type='text' name='txt_preco_unitario_rs7' value='<?=number_format($preco_custo, 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='textdisabled' disabled>
            &nbsp;
            <?=$campos[0]['sigla'];?>
      </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total R$:
        </td>
        <td>
            <?$total = $campos[0]['qtde'] * $preco_custo * $fator_custo7;?>
            <input type='text' name='txt_total7' value='<?=number_format($total, 2, ',', '.');?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?$checked = ($campos[0]['usar_este_lote_para_orc'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_usar_este_lote_para_orc' value='S' id='chkt_usar_este_lote_para_orc' <?=$checked;?> class='checkbox'>
            <label for='chkt_usar_este_lote_para_orc'>Usar este Lote de Custo p/ Orc</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_adicionar_novo' value='Adicionar Novo' title='Adicionar Novo' onclick="document.form.hdd_adicionar_novo.value = 'S';validar('<?=$posicao;?>')" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa7()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'> 
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Essa marcação <b>"Usar este Lote de Custo p/ Orc"</b> foi criada p/ calcularmos o MMV Total 
e a Taxa de Estocagem no Orçamento. 

* Temos de marcar quando ... ???
</pre>