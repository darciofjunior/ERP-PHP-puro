<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');

if(!empty($_POST['id_op'])) {
    //Aqui eu verifico qual foi a % de Redução da Qtde à Produzir da "OP atual p/ a Nova OP" ...
    $percentagem    = (100 * $_POST['txt_nova_qtde_produzir_da_op']) / $_POST['txt_atual_qtde_produzir_da_op'];
    
    /***********************************OP(s)**********************************/
    //Gerando uma Nova OP ...
    $sql = "SELECT `id_produto_acabado`, `prazo_entrega`, `lote_diferente_custo` 
            FROM `ops` 
            WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    
    $sql = "INSERT INTO `ops` (`id_op`, `id_produto_acabado`, `id_funcionario_ocorrencia`, `qtde_produzir`, `data_emissao`, `prazo_entrega`, `observacao`, `data_ocorrencia`, `lote_diferente_custo`) VALUES (NULL, '".$campos[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '$_POST[txt_qtde_nova_op]', '".date('Y-m-d')."', '".$campos[0]['prazo_entrega']."', 'OP dividida da OP N.º $_POST[id_op]', '".date('Y-m-d H:i:s')."', '".$campos[0]['lote_diferente_custo']."') ";
    bancos::sql($sql);
    $id_op = bancos::id_registro();
    
    //Atualizando a OP atual ...
    $sql = "UPDATE `ops` SET `qtde_produzir` = '$_POST[txt_nova_qtde_produzir_da_op]', `observacao` = CONCAT(`observacao`, ' - Esta OP foi dividida com a OP N.º $id_op em ".date('d/m/Y - H:i')."') WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
    bancos::sql($sql);
    
    /******************************PI(s) Baixado(s)****************************/
    
    //Aqui eu busco o "id_baixa_manipulacao" da OP ...
    $sql = "SELECT `id_baixa_manipulacao` 
            FROM `baixas_ops_vs_pis` 
            WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
    $campos_baixa_manipulacao = bancos::sql($sql);
    
    //Através desse "id_baixa_manipulacao", busco o somatório da "qtde_baixa" em suas diversas manipulações "PI(s)" e diversas OP(s) ...
    $sql = "SELECT SUM(`qtde_baixa`) AS total_qtde_baixa_pi 
            FROM `baixas_ops_vs_pis` 
            WHERE `id_baixa_manipulacao` = '".$campos_baixa_manipulacao[0]['id_baixa_manipulacao']."' ";
    $campos_total_qtde_baixa_de_pi  = bancos::sql($sql);
    $total_qtde_baixa_pi            = $campos_total_qtde_baixa_de_pi[0]['total_qtde_baixa_pi'];
    
    //Verifico os PI(s) Baixado(s) da OP atual ...
    $sql = "SELECT bm.*, bop.`id_baixa_op_vs_pi`, bop.`id_produto_insumo`, bop.`id_baixa_manipulacao`, bop.`qtde_baixa`, 
            bop.`observacao`, bop.`status` 
            FROM `baixas_ops_vs_pis` bop 
            INNER JOIN `baixas_manipulacoes` bm ON bm.`id_baixa_manipulacao` = bop.`id_baixa_manipulacao` 
            WHERE bop.`id_op` = '$_POST[id_op]' ";
    $campos_pis_baixados = bancos::sql($sql);
    $linhas_pis_baixados = count($campos_pis_baixados);
    for($i = 0; $i < $linhas_pis_baixados; $i++) {
        $fator_baixa_pi_da_op_vs_total_qtde_baixa_pi    = $campos_pis_baixados[$i]['qtde_baixa'] / $total_qtde_baixa_pi;
        $qtde_proporcional_baixa_manipulacao_pi_da_op   = $fator_baixa_pi_da_op_vs_total_qtde_baixa_pi * $campos_pis_baixados[$i]['qtde'];
        
        $nova_qtde_baixa_manipulacao_pi_da_op           = $qtde_proporcional_baixa_manipulacao_pi_da_op * ($percentagem / 100);
        $qtde_baixa_manipulacao_pi_da_nova_op           = round($campos_pis_baixados[$i]['qtde'] - $nova_qtde_baixa_manipulacao_pi_da_op, 2);
        
        $nova_qtde_baixa_pi_da_op   = round($campos_pis_baixados[$i]['qtde_baixa'] * ($percentagem / 100), 2);
        $qtde_baixa_pi_da_nova_op   = round($campos_pis_baixados[$i]['qtde_baixa'] - $nova_qtde_baixa_pi_da_op, 2);
        
       
        //Gerando uma Nova Baixa de PI, observação que já tem que vir com o "status" de contabilizada = 1 (Hum) ...
        $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `estoque_final`, `observacao`, `acao`, `status`, `troca`, `data_sys`) VALUES (NULL, '".$campos_pis_baixados[$i]['id_produto_insumo']."', '$_SESSION[id_funcionario]', '".$campos_pis_baixados[$i]['id_funcionario_retirado']."', '$qtde_baixa_manipulacao_pi_da_nova_op', '".$campos_pis_baixados[$i]['retirado_por']."', '".$campos_pis_baixados[$i]['estoque_final']."', '".$campos_pis_baixados[$i]['observacao'].'. OP dividida da OP N.º '.$_POST[id_op].".', '".$campos_pis_baixados[$i]['acao']."', '1', '".$campos_pis_baixados[$i]['troca']."', '".$campos_pis_baixados[$i]['data_sys']."') ";
        bancos::sql($sql);
        $id_baixa_manipulacao = bancos::id_registro();
        
        $sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '".$campos_pis_baixados[$i]['id_produto_insumo']."', '$id_op', '$id_baixa_manipulacao', '$qtde_baixa_pi_da_nova_op', '".$campos_pis_baixados[$i]['observacao'].' - OP dividida da OP N.º '.$_POST[id_op].".', '".$campos_pis_baixados[$i]['data_sys']."', '".$campos_pis_baixados[$i]['status']."') ";
        bancos::sql($sql);
        
        /*Pelo fato de sempre termos que acrescer no estoque_final com essa logística reversa, eu transformo a $qtde_baixa_manipulacao_pi_da_nova_op 
        em valor positivo ...*/
        $estoque_final = $campos_pis_baixados[$i]['estoque_final'] + abs($qtde_baixa_manipulacao_pi_da_nova_op);
        
        //Atualizando a Baixa de PI atual ...
        $sql = "UPDATE `baixas_manipulacoes` SET `qtde` = '$nova_qtde_baixa_manipulacao_pi_da_op', `estoque_final` = '$estoque_final', `observacao` = CONCAT(`observacao`, ' - Esta OP foi dividida com a OP N.º $id_op em ".date('d/m/Y - H:i')."') WHERE `id_baixa_manipulacao` = '".$campos_pis_baixados[$i]['id_baixa_manipulacao']."' LIMIT 1 ";
        bancos::sql($sql);
        
        $sql = "UPDATE `baixas_ops_vs_pis` SET `qtde_baixa` = '$nova_qtde_baixa_pi_da_op', `observacao` = CONCAT(`observacao`, ' - Esta OP foi dividida com a OP N.º $id_op em ".date('d/m/Y - H:i')."') WHERE `id_baixa_op_vs_pi` = '".$campos_pis_baixados[$i]['id_baixa_op_vs_pi']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    
    /******************************PA(s) Baixado(s)****************************/
    
    //Aqui eu busco o "id_baixa_manipulacao_pa" da OP ...
    $sql = "SELECT `id_baixa_manipulacao_pa` 
            FROM `baixas_ops_vs_pas` 
            WHERE `id_op` = '$_POST[id_op]' LIMIT 1 ";
    $campos_baixa_manipulacao_pa = bancos::sql($sql);
    
    //Através desse "id_baixa_manipulacao", busco o somatório da "qtde_baixa" em suas diversas manipulações "PI(s)" e diversas OP(s) ...
    $sql = "SELECT SUM(`qtde_baixa`) AS total_qtde_baixa_pa 
            FROM `baixas_ops_vs_pas` 
            WHERE `id_baixa_manipulacao_pa` = '".$campos_baixa_manipulacao_pa[0]['id_baixa_manipulacao_pa']."' LIMIT 1 ";
    $campos_total_qtde_baixa_de_pa  = bancos::sql($sql);
    $total_qtde_baixa_pa            = $campos_total_qtde_baixa_de_pa[0]['total_qtde_baixa_pa'];
    
    //Verifico os PA(s) Baixado(s) da OP que foi Dividida ...
    $sql = "SELECT bmp.*, bop.`id_baixa_op_vs_pa`, bop.`id_produto_acabado`, bop.`id_baixa_manipulacao_pa`, bop.`qtde_baixa`, 
            bop.`observacao`, bop.`status` 
            FROM `baixas_ops_vs_pas` bop 
            INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.`id_baixa_manipulacao_pa` = bop.`id_baixa_manipulacao_pa` AND bmp.`acao` IN ('B', 'S') 
            WHERE bop.`id_op` = '$_POST[id_op]' ";
    $campos_pas_baixados = bancos::sql($sql);
    $linhas_pas_baixados = count($campos_pas_baixados);
    for($i = 0; $i < $linhas_pas_baixados; $i++) {
        $fator_baixa_pa_da_op_vs_total_qtde_baixa_pa    = $campos_pas_baixados[$i]['qtde_baixa'] / $total_qtde_baixa_pa;
        $qtde_proporcional_baixa_manipulacao_pa_da_op   = $fator_baixa_pa_da_op_vs_total_qtde_baixa_pa * $campos_pas_baixados[$i]['qtde'];
        
        $nova_qtde_baixa_manipulacao_pa_da_op           = $qtde_proporcional_baixa_manipulacao_pa_da_op * ($percentagem / 100);
        $qtde_baixa_manipulacao_pa_da_nova_op           = round($campos_pas_baixados[$i]['qtde'] - $nova_qtde_baixa_manipulacao_pa_da_op, 2);
        
        $nova_qtde_baixa_pa_da_op   = round($campos_pas_baixados[$i]['qtde_baixa'] * ($percentagem / 100), 2);
        $qtde_baixa_pa_da_nova_op   = round($campos_pas_baixados[$i]['qtde_baixa'] - $nova_qtde_baixa_pa_da_op, 2);
        
        //Gerando uma Nova Baixa de PA ...
        $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `status`, `data_sys`) VALUES (NULL, '".$campos_pas_baixados[$i]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '".$campos_pas_baixados[$i]['id_funcionario_retirado']."', '".$campos_pas_baixados[$i]['retirado_por']."', '$qtde_baixa_manipulacao_pa_da_nova_op', '".$campos_pas_baixados[$i]['observacao'].'. OP dividida da OP N.º '.$_POST[id_op].".', '".$campos_pas_baixados[$i]['acao']."', '".$campos_pas_baixados[$i]['status']."', '".$campos_pas_baixados[$i]['data_sys']."') ";
        bancos::sql($sql);
        $id_baixa_manipulacao_pa = bancos::id_registro();
        
        $sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '".$campos_pas_baixados[$i]['id_produto_acabado']."', '$id_op', '$id_baixa_manipulacao_pa', '$qtde_baixa_pa_da_nova_op', '".$campos_pas_baixados[$i]['observacao'].' - OP dividida da OP N.º '.$_POST[id_op].".', '".$campos_pas_baixados[$i]['data_sys']."', '".$campos_pas_baixados[$i]['status']."') ";
        bancos::sql($sql);
        
        //Atualizando a Baixa de PA atual ...
        $sql = "UPDATE `baixas_manipulacoes_pas` SET `qtde` = '$nova_qtde_baixa_manipulacao_pa_da_op', `observacao` = CONCAT(`observacao`, ' - Esta OP foi dividida com a OP N.º $id_op em ".date('d/m/Y - H:i')."') WHERE `id_baixa_manipulacao_pa` = '".$campos_pas_baixados[$i]['id_baixa_manipulacao_pa']."' LIMIT 1 ";
        bancos::sql($sql);
        
        $sql = "UPDATE `baixas_ops_vs_pas` SET `qtde_baixa` = '$nova_qtde_baixa_pa_da_op', `observacao` = CONCAT(`observacao`, ' - Esta OP foi dividida com a OP N.º $id_op em ".date('d/m/Y - H:i')."') WHERE `id_baixa_op_vs_pa` = '".$campos_pas_baixados[$i]['id_baixa_op_vs_pa']."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        alert('OP DIVIDIDA COM SUCESSO !')
        var resposta = confirm('DESEJA IMPRIMIR A NOVA OP N.º <?=$id_op;?> ?')
        if(resposta == true) {
            nova_janela('relatorio/relatorio.php?id_op=<?=$id_op;?>', 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }
        parent.location = parent.location.href
    </Script>
<?    
}

//Faço a busca de alguns campos da OP que foi passada por parâmetro ...
$sql = "SELECT ops.`qtde_produzir`, u.`sigla` 
        FROM `ops` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE ops.`id_op` = '$_GET[id_op]' LIMIT 1 ";
$campos = bancos::sql($sql);

//Aqui eu busco o Total de Entradas da OP que foi passada por parâmetro ...
$sql = "SELECT SUM(bop.`qtde_baixa`) AS total_de_entradas 
        FROM `baixas_manipulacoes_pas` bmp 
        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$_GET[id_op]' 
        WHERE bmp.`acao` = 'E' ";
$campos_total_entrada = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Dividir OP N.º <?=$_GET[id_op];?> ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Nova Qtde à Produzir da OP ...
    if(document.form.txt_nova_qtde_produzir_da_op.value == '') {
        alert('DIGITE A NOVA QTDE À PRODUZIR DA OP !')
        document.form.txt_nova_qtde_produzir_da_op.focus()
        return false
    }
//Verificação p/ ver se o que o usuário digitou está dentro do valor permitido ...
    var total_de_entradas           = eval('<?=$campos_total_entrada[0]['total_de_entradas'];?>')
    var atual_qtde_produzir_da_op   = eval(strtofloat(document.form.txt_atual_qtde_produzir_da_op.value))
    var nova_qtde_produzir_da_op    = eval(strtofloat(document.form.txt_nova_qtde_produzir_da_op.value))
    
    //Nunca poderemos ter uma Nova Qtde à Produzir da OP sendo menor que o Total de Entradas ...
    if(nova_qtde_produzir_da_op < total_de_entradas) {
        alert('NOVA QTDE À PRODUZIR DA OP INVÁLIDA !!!\n\nNUNCA A "NOVA QTDE À PRODUZIR DA OP" PODE SER MENOR DO QUE O "TOTAL DE ENTRADAS" !')
        document.form.txt_nova_qtde_produzir_da_op.focus()
        document.form.txt_nova_qtde_produzir_da_op.select()
        return false
    }
        
    /*Nunca poderemos ter uma Nova Qtde à Produzir da OP sendo maior ou Igual ao Valor atual, afinal se é uma Divisão de OP(s) 
    a idéia então é de diminuir essa Nova Qtde à Produzir ...*/
    if(nova_qtde_produzir_da_op >= atual_qtde_produzir_da_op) {
        alert('NOVA QTDE À PRODUZIR DA OP INVÁLIDA !!!\n\nDIGITE UMA "NOVA QTDE À PRODUZIR DA OP" MENOR DO QUE A "ATUAL QTDE À PRODUZIR DA OP " !')
        document.form.txt_nova_qtde_produzir_da_op.focus()
        document.form.txt_nova_qtde_produzir_da_op.select()
        return false
    }
    limpeza_moeda('form', 'txt_nova_qtde_produzir_da_op, ')
}

function calcular() {
    if(document.form.txt_nova_qtde_produzir_da_op.value != '') {
        var atual_qtde_produzir_da_op           = eval(strtofloat(document.form.txt_atual_qtde_produzir_da_op.value))
        var nova_qtde_produzir_da_op            = eval(strtofloat(document.form.txt_nova_qtde_produzir_da_op.value))
        document.form.txt_qtde_nova_op.value    = atual_qtde_produzir_da_op - nova_qtde_produzir_da_op
    }else {
        document.form.txt_qtde_nova_op.value    = ''
    }
}
</Script>
</head>
<body onload='document.form.txt_nova_qtde_produzir_da_op.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='id_op' value='<?=$_GET['id_op'];?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Dividir OP N.º 
            <font color='yellow'>
                <?=$_GET[id_op];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            Atual Quantidade à Produzir da OP <u><?=$_GET[id_op];?></u>:
        </td>
        <td>
            <?
                $onkeyup            = ($campos[0]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '', event);if(this.value == '0.00') {this.value = ''}" : "verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}";
                $casas_decimais     = ($campos[0]['sigla'] == 'KG') ? 2 : 0;
                $separador_milhares = ($campos[0]['sigla'] == 'KG') ? '.' : '';
            ?>
            <input type='text' name='txt_atual_qtde_produzir_da_op' value='<?=number_format($campos[0]['qtde_produzir'], $casas_decimais, ',', $separador_milhares);?>' title='Atual Quantidade à Produzir da OP' maxlength='11' size='12' onkeyup="<?=$onkeyup;?>" onfocus='document.form.txt_nova_qtde_produzir_da_op.focus()' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nova Quantidade à Produzir da OP <u><?=$_GET[id_op];?></u>:</b>
        </td>
        <td>
            <input type='text' name='txt_nova_qtde_produzir_da_op' title='Digite a Nova Quantidade à Produzir da OP' maxlength='11' size='12' onkeyup="<?=$onkeyup;?>;calcular()" onblur='calcular()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Quantidade da Nova OP:
        </td>
        <td>
            <input type='text' name='txt_qtde_nova_op' title='Quantidade da Nova OP' maxlength='11' size='12' onfocus='document.form.txt_nova_qtde_produzir_da_op.focus()' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nova_qtde_produzir_da_op.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.html5Lightbox.finish()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<pre>
<b><font color='red'>Observação:</font></b>

    * Esta alteração de Qtde(s) implicará na Divisão Pró-Rata das Qtde(s) de PA(s) e PI(s) baixados.
<font color='darkblue'>
    <b>* Total de Entrada(s) => <?=number_format($campos_total_entrada[0]['total_de_entradas'], 2, ',', '.');?></b>
</font>
</pre>
</html>