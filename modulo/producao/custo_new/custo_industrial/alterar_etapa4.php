<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";
$fator_custo4 = genericas::variavel(9);

if($passo == 1) {
    $sql = "UPDATE `pacs_vs_maquinas` SET `tempo_hs` = '$txt_tempo4' WHERE `id_pac_maquina` = '$_POST[id_pac_maquina]' limit 1";
    bancos::sql($sql);
    $valor = 1;
/*Atualização do Funcionário que alterou os dados no custo*/
    $data_sys = date('Y-m-d H:i:s');
    $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    if($_POST['nova_maquina'] == 1) {//Se desejar incluir uma nova máquina ...
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_maquina.php?id_produto_acabado_custo=<?=$_POST['id_produto_acabado_custo'];?>'
        </Script>
<?
    }
}

//Aqui eu busco a quantidade do lote para poder jogar na fórmula + abaixo
$sql = "SELECT id_produto_acabado, qtde_lote 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos_custo               = bancos::sql($sql);
$qtde_lote                  = $campos_custo[0]['qtde_lote'];
$preco_fat_nac_minimo_rs    = custos::preco_custo_pa($campos_custo[0]['id_produto_acabado']);

//Aqui tem esse tratamento, para não dar erro de divisão por zero
if($qtde_lote == 0) $qtde_lote = 1;

//Seleciona a qtde de itens que existe do produto acabado na etapa 4
$sql = "SELECT COUNT(pm.id_pac_maquina) AS qtde_itens 
        FROM `pacs_vs_maquinas` pm 
        INNER JOIN `maquinas` m ON pm.id_maquina = m.id_maquina 
        WHERE pm.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];
if(empty($posicao)) $posicao = $qtde_itens;

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "SELECT pm.id_pac_maquina, m.id_maquina, m.nome, m.custo_h_maquina, pm.tempo_hs 
        FROM `pacs_vs_maquinas` pm 
        INNER JOIN `maquinas` m ON pm.id_maquina = m.id_maquina 
        WHERE pm.`id_produto_acabado_custo` = '$id_produto_acabado_custo' 
        ORDER BY pm.id_pac_maquina ";
$campos     = bancos::sql($sql, ($posicao - 1), $posicao);
$total_rs   = ($campos[0]['tempo_hs'] * $campos[0]['custo_h_maquina'] * $fator_custo4) / $qtde_lote;

/*Desse preço faturado nacional mínimo em R$ eu desconto a Taxa Financeira de Estocagem desse PA, p/ 
ter uma taxa mais coerente e evitar um erro por redundância ...*/
$preco_fat_nac_minimo_rs-= $total_rs;
?>
<html>
<head>
<title>.:: Alterar Custo M&aacute;quina ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa4() {
    var id_maquina              = eval('<?=$campos[0]['id_maquina'];?>')
    var fator_custo             = eval('<?=$fator_custo4;?>')
    var qtde_lote               = eval('<?=$qtde_lote;?>')
    var preco_fat_nac_minimo_rs = eval('<?=$preco_fat_nac_minimo_rs;?>')
    var real_h                  = eval(strtofloat(document.form.txt_real_h4.value))

    if(id_maquina == 40) {//O sistema irá entrar nesse cálculo quando a Máquina for "Tx Financ Estocagem" ...
        if(document.form.txt_taxa_financeira_estocagem_perc.value != '') {
            var taxa_financeira_estocagem_perc                   = eval(strtofloat(document.form.txt_taxa_financeira_estocagem_perc.value))
            document.form.txt_taxa_financeira_estocagem_rs.value = (preco_fat_nac_minimo_rs * (1 + taxa_financeira_estocagem_perc / 100) - preco_fat_nac_minimo_rs) * 100
            //Esse mais 1 é para forçar um arredondamento de 1 centavo p/ cima ...
            document.form.txt_taxa_financeira_estocagem_rs.value = (parseInt(document.form.txt_taxa_financeira_estocagem_rs.value) + 1) / 100
            document.form.txt_taxa_financeira_estocagem_rs.value = arred(document.form.txt_taxa_financeira_estocagem_rs.value, 2, 1)

            //Essa variável será utilizada para calcular o tempo em Horas ...
            var taxa_financeira_estocagem_rs    = eval(strtofloat(document.form.txt_taxa_financeira_estocagem_rs.value))
            document.form.txt_tempo4.value      = taxa_financeira_estocagem_rs * qtde_lote / (real_h * fator_custo)
            document.form.txt_tempo4.value      = arred(document.form.txt_tempo4.value, 2, 1)
        }else {
            document.form.txt_taxa_financeira_estocagem_rs.value    = ''
        }
    }
    
    var tempo_hs                    = eval(strtofloat(document.form.txt_tempo4.value))
    document.form.txt_total4.value  = (tempo_hs * real_h * fator_custo) / qtde_lote
    document.form.txt_total4.value  = (isNaN(document.form.txt_total4.value)) ? '' : arred(document.form.txt_total4.value, 2, 1)
}

function validar(posicao, verificar) {
    var tempo = eval(strtofloat(document.form.txt_tempo4.value))
    if(tempo == 0 || typeof(tempo) == 'undefined') {
        alert('TEMPO INVÁLIDO ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
        document.form.txt_tempo4.focus()
        document.form.txt_tempo4.select()
        return false
    }
    limpeza_moeda('form', 'txt_tempo4, ')
    document.form.txt_tempo4.disabled   = false//Desabilito p/ poder gravar no BD ...
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value         = posicao;
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value   = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload="if(document.form.txt_tempo4.disabled == false) {document.form.txt_tempo4.focus()}else {document.form.txt_taxa_financeira_estocagem_perc.focus()}" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>', 1)">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_maquina' value="<?=$campos[0]['id_pac_maquina'];?>">
<input type='hidden' name='nao_atualizar'>
<!--Caixa que serve para controlar o redirecionamente p/ a tela de inclusão de máquinas-->
<input type='hidden' name='nova_maquina'>
<!--***********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            4&ordf; Etapa: Alterar Custo M&aacute;quina
        </td>
    </tr>
    <tr class='linhadestaque'>		
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                <font color='#FFFF00'>Máquina:</font> 
                    <?=$campos[0]['nome'];?>
                </font>
            </font>
        </td>
    </tr>
<?
    /***************************************Tx Financ Estocagem***************************************/
    if($campos[0]['id_maquina'] == 40) {//Só serão exibidos esses campos p/ a máquina "Tx Financ Estocagem" 
?>
    <tr class='linhanormal'>
        <td width='35%'>
            Preço Fat. Nac. Min. R$ s/ Tx Estocagem:
        </td>
        <td width='65%'>
            <?=str_replace('.', ',', $preco_fat_nac_minimo_rs);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Taxa Financeira Estocagem %: 
        </td>
        <td>
            <input type="text" name="txt_taxa_financeira_estocagem_perc" title='Digite a Taxa Financeira Estocagem %' size="12" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calculo_etapa4()" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Taxa Financeira Estocagem R$: 
        </td>
        <td>
            <input type="text" name="txt_taxa_financeira_estocagem_rs" title='Digite a Taxa Financeira Estocagem em R$' size="12" class="textdisabled" disabled>
        </td>
    </tr>
<?
        $disabled   = 'disabled';
        $class      = 'textdisabled';
    }else {
        $disabled = '';
        $class      = 'caixadetexto';
    }
    /*************************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Tempo (Hs):
        </td>
        <td>
            <input type="text" name="txt_tempo4" value="<?=number_format($campos[0]['tempo_hs'], 1, ',', '.');?>" size="12" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calculo_etapa4()" class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            R$ / h:
        </td>
        <td>
            <input type="text" name="txt_real_h4" value="<?=number_format($campos[0]['custo_h_maquina'], 2, ',', '.');?>" size="12" class="disabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total R$:
        </td>
        <td>
            <input type="text" name="txt_total4" id="txt_total4" value="<?=number_format($total_rs, 2, ',', '.');?>" size="12" class="disabled" disabled>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_adicionar_novo" value="Adicionar Novo" title="Adicionar Novo" onclick="document.form.nova_maquina.value = 1;validar('<?=$posicao;?>')" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa4()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" onclick="document.form.nova_maquina.value = 0" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao">
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr align="center">
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