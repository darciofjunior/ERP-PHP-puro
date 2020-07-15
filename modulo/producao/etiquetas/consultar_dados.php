<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

if($_GET['opt_opcao'] == 1 || $_GET['opt_opcao'] == 5) {//Referência ou Referência Caixa Master ...
    $sql = "SELECT `id_produto_acabado`, `referencia`, `discriminacao`, `operacao_custo` 
            FROM `produtos_acabados` 
            WHERE `referencia` = '$_GET[txt_consultar]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}else if($_GET['opt_opcao'] == 2) {//N.º OP ...
    $sql = "SELECT ops.`id_op` AS numero, ops.`qtde_produzir`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo` 
            FROM `ops` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
            WHERE ops.`id_op` = '$_GET[txt_consultar]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}else if($_GET['opt_opcao'] == 3) {//N.º OE ...
    $sql = "SELECT oes.`id_oe` AS numero, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo` 
            FROM `oes` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oes.`id_produto_acabado_e` 
            WHERE oes.`id_oe` = '$_GET[txt_consultar]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}else if($_GET['opt_opcao'] == 4) {//N.º OC ...
    $sql = "SELECT oi.`id_oc` AS numero, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo` 
            FROM `ocs_itens` oi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` 
            WHERE oi.`id_oc` = '$_GET[txt_consultar]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}

//Se foi retornado algum resultado, então eu busco a Qtde de Peças por Embalagem do PA na 1ª Etapa do Custo
if(count($campos) == 1) {
    $sql = "SELECT pi.`id_produto_insumo_etiqueta`, UPPER(pi.`discriminacao`) AS embalagem_principal, ROUND(ppe.`pecas_por_emb`, 0) AS pecas_por_emb 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
            WHERE ppe.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
            AND ppe.`embalagem_default` = '1' LIMIT 1 ";
    $campos_pecas_embalagem = bancos::sql($sql);
    
    if($_GET['opt_opcao'] == 4) {//N.º OC ...
        //Verifico quantos itens de OC que existem ...
        $sql = "SELECT COUNT(`id_oc_item`) AS qtde_itens_oc 
                FROM `ocs_itens` 
                WHERE `id_oc` = '$_GET[txt_consultar]' ";
        $campos_itens_oc    = bancos::sql($sql);
        $qtde_itens_oc      = $campos_itens_oc[0]['qtde_itens_oc'];
        
        if($qtde_itens_oc > 1) {//Se existir mais de 1 item na OC então não é permitida a Impressão dessa Etiqueta, afinal qual é o item ??? ...
?>
    <Script Language = 'JavaScript'>
        alert('O SISTEMA ESTÁ TRABALHANDO COM A REFERÊNCIA DO PRIMEIRO ITEM ENCONTRADO NESSA OC !!!\n\nSE ESSA NÃO ESTIVER CORRETA, ENTÃO DIGITE A REFERÊNCIA DESEJADA NA OPÇÃO "REFERÊNCIA CAIXA MASTER" !')
    </Script>
<?
        }
        //Se o usuário selecionou essa opção "A4255 Caixa Master - Máx. 27 Etiq.", o sistema automaticamente sugere essa opção de Etiqueta ...
    }else if($_GET['opt_opcao'] == 5) {
        $url_tipo_etiqueta = 'imprimir_A4255caixa_master.php';
    }else {
        //Aqui eu verifico qual Etiqueta utilizar na Combo ...
        if($campos_pecas_embalagem[0]['id_produto_insumo_etiqueta'] == 360) {
            $url_tipo_etiqueta = 'imprimir_26x15.php';
        }else if($campos_pecas_embalagem[0]['id_produto_insumo_etiqueta'] == 9898) {
            $url_tipo_etiqueta = 'imprimir_A4251.php';
        }else if($campos_pecas_embalagem[0]['id_produto_insumo_etiqueta'] == 9895) {
            $url_tipo_etiqueta = 'imprimir_A4255.php';
        }else if($campos_pecas_embalagem[0]['id_produto_insumo_etiqueta'] == 9896) {
            $url_tipo_etiqueta = 'imprimir_6288.php';
        }else {//Sugere a primeira opção de Etiqueta que é a menor de todas ...
            $url_tipo_etiqueta = 'imprimir_26x15.php';
        }
    }
    //Aqui eu busco o nome da etiqueta da Embalagem Principal ...
    $sql = "SELECT UPPER(`discriminacao`) AS etiqueta 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '".$campos_pecas_embalagem[0]['id_produto_insumo_etiqueta']."' LIMIT 1 ";
    $campos_etiqueta = bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        var opt_opcao                                   = eval('<?=$_GET['opt_opcao'];?>')
        parent.document.form.hdd_produto_acabado.value 	= '<?=$campos[0]['id_produto_acabado'];?>'
        parent.document.form.txt_referencia.value       = '<?=$campos[0]['referencia'];?>'
        parent.document.form.txt_discriminacao.value 	= '<?=$campos[0]['discriminacao'];?>'

        //Somente na OP que eu carrego a Qtde a Produzir ...
        if(opt_opcao == 2) parent.document.form.txt_qtde_op.value = '<?=number_format($campos[0]['qtde_produzir'], 0, '', '.');?>'

        parent.document.form.txt_numero.value           = '<?=$campos[0]['numero'];?>'
        parent.document.form.txt_pcs_embalagem.value 	= '<?=$campos_pecas_embalagem[0]['pecas_por_emb'];?>'

        if(opt_opcao == 1) {//Se foi digitada a referência habilito o campo número para que seja digitado o N.º do Lote ...
            parent.document.form.txt_numero.disabled 	= false
            parent.document.form.txt_numero.className	= 'caixadetexto'
            parent.document.form.txt_consultar.value	= ''
            parent.document.form.txt_numero.focus()
        }else if(opt_opcao == 2 || opt_opcao == 3) {//Se foi digitado o N.º de OC habilito todos os campos para que sejam digitados ...
            parent.document.form.txt_numero.disabled 	= true
            parent.document.form.txt_numero.className	= 'textdisabled'
            parent.document.form.txt_consultar.value	= ''
            parent.document.form.txt_quantidade.focus()
        }
        parent.document.form.cmb_tipo_etiqueta.value	= '<?=$url_tipo_etiqueta;?>'

        var embalagem_principal = '<?=$campos_pecas_embalagem[0]['embalagem_principal'];?>'
        if(embalagem_principal == '') {
            alert('NÃO EXISTE EMBALAGEM PRINCIPAL PARA ESTE PRODUTO !!! SELECIONE UM TIPO DE ETIQUETA !')
            parent.document.getElementById('div_detalhes').innerHTML = ''
        }else {
            parent.document.getElementById('div_detalhes').innerHTML = 'Embalagem Principal: <?=$campos_pecas_embalagem[0]['embalagem_principal'];?><br>Etiqueta desta Embalagem: <?=$campos_etiqueta[0]['etiqueta'];?>'
        }
    </Script>
<?
}else {
?>
    <Script Language = 'JavaScript'>
        alert('OPÇÃO NÃO ENCONTRADA !')
        parent.document.form.hdd_produto_acabado.value 	= ''
        parent.document.form.txt_referencia.value       = ''
        parent.document.form.txt_discriminacao.value 	= ''
        parent.document.form.txt_qtde_op.value          = ''
        parent.document.form.txt_numero.value           = ''
        parent.document.form.txt_pcs_embalagem.value 	= ''
        parent.document.form.txt_quantidade.value       = ''
        parent.document.form.txt_consultar.value        = ''
        parent.document.getElementById('cmb_pa_substitutivo').length = 0
        parent.document.getElementById('lbl_pa_substitutivo').style.visibility = 'hidden'
        parent.document.getElementById('cmb_pa_substitutivo').style.visibility = 'hidden'
        parent.document.form.opt_opcao[1].onclick()
        parent.document.form.txt_consultar.focus()
        parent.document.form.cmb_tipo_etiqueta.value	= 'imprimir_26x15.php'
        parent.document.getElementById('div_detalhes').innerHTML = ''
    </Script>
<?}?>