<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

if($passo == 1) {
    //Verifico se essa "Nota Fiscal Outra" possui uma OS Atrelada ...
    $sql = "SELECT id_os 
            FROM `oss` 
            WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos_os = bancos::sql($sql);
    if(count($campos_os) == 1) {//Se a "NF Outra" tiver uma OS Importada ...
        //Desatrelo todos os Itens de NF da tabela de 'oss_itens' da OS que está voltando a ficar com o Status em aberto
        $sql = "UPDATE `oss_itens` SET `id_nf_outra_item` = NULL WHERE `id_os` = '".$campos_os[0]['id_os']."' ";
        bancos::sql($sql);
        //Desatrelo o id_nf_outra da OS para que essa possa ser importada novamente ...
        $sql = "UPDATE `oss` SET `id_nf_outra` = NULL, `nnf` = '', `status_nf` = '' WHERE `id_os` = '".$campos_os[0]['id_os']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Se não for array, transforma em Array, isso só acontecerá nos casos em que o sistema deleta todos os Itens de OS ...
    if(!is_array($_POST['chkt_nf_outra_item'])) $_POST['chkt_nf_outra_item'] = explode(',', $_POST['chkt_nf_outra_item']);
    
    //Deleta todos os Itens selecionados da "Nota Fiscal Outra" ...
    foreach($_POST['chkt_nf_outra_item'] as $id_nf_outra_item) {
        $sql = "DELETE FROM `nfs_outras_itens` WHERE `id_nf_outra_item` = '$id_nf_outra_item' LIMIT 1 ";
        bancos::sql($sql);
    }
    /*Registrando o Funcionário que fez modificações na NF Outra, retirando também os dados de Volume como Qtde, 
    Peso Bruto e Peso Líquido ...*/
    $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_volume` = '0', `especie_volume` = '', `peso_bruto_volume` = '0', `peso_liquido_volume` = '0', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('TODO(S) O(S) ITEM(NS) DA NOTA FISCAL FORAM EXCLUÍDO(S) COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}else {
//Verifico se está Nota já foi importada p/ o Financeiro ...
    $importado_financeiro = faturamentos::importado_financeiro_outras_nfs($_GET['id_nf_outra']);
    if($importado_financeiro == 'S') {//Significa que a NF já está importada no Financeiro ...
        echo '<font color="red"><div align="center"><b>NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO ! PORQUE ESTA NOTA FISCAL ESTÁ IMPORTADA NO FINANCEIRO !</b></div></font>';
        exit;
    }
    if(faturamentos::situacao_outras_nfs($_GET['id_nf_outra']) >= 1) {//Está liberado, então ñ posso excluir nada
        echo '<font color="red"><div align="center"><b>NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO ! PORQUE ESTA NOTA FISCAL ESTÁ TRAVADA !</b></div></font>';
        exit;
    }
?>
<html>
<head>
<title>.:: Excluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
//Confirmando ...
        var mensagem = confirm('DESEJA EXCLUIR O(S) ITEM(NS) SELECIONADO(S) ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    /************************************************************************************************/
    /***************************************Procedimento de OS***************************************/
    /************************************************************************************************/
    //Verifico se essa "Nota Fiscal Outra" possui uma OS Atrelada ...
    $sql = "SELECT `id_os` 
            FROM `oss` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos_os = bancos::sql($sql);
    //Se a NF tiver uma OS Importada ...
    if(count($campos_os) == 1) {
        //Busco todos os itens da "Nota Fiscal Outra" passada por parâmetro ...
        $sql = "SELECT `id_nf_outra_item` 
                FROM `nfs_outras_itens` 
                WHERE `id_nf_outra` = '$_GET[id_nf_outra]' ";
        $campos_itens = bancos::sql($sql);
        $linhas_itens = count($campos_itens);
        for($i = 0; $i < $linhas_itens; $i++) $id_nf_outras_itens.= $campos_itens[$i]['id_nf_outra_item'].', ';
        $id_nf_outras_itens = substr($id_nf_outras_itens, 0, strlen($id_nf_outras_itens) - 2);
?>
    <!--***************************Controle de Tela***************************-->
    <!--Esses hidden serão submetidos p/ a Próxima Tela ...-->
    <input type='hidden' name='chkt_nf_outra_item' value='<?=$id_nf_outras_itens;?>'>
    <input type='hidden' name='id_nf_outra' value='<?=$_GET[id_nf_outra];?>'>
    <!--**********************************************************************-->
    <Script Language = 'JavaScript'>
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR TODO(S) O(S) ITEM(NS) DESSA NOTA FISCAL OUTRA ?')
        if(resposta == true) {
            document.form.submit()
        }else {
            window.close()
        }
    </Script>
<?
        exit;
    }
    /************************************************************************************************/
    /**************************************Procedimento da Tela**************************************/
    /************************************************************************************************/
    //Aqui eu busco o id_pais através do "id_nf_outra" p/ saber qual o Tipo de Moeda do Cliente ...
    $sql = "SELECT c.`id_pais` 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_nf_outra` = '$_GET[id_nf_outra]' ";
    $campos_pais    = bancos::sql($sql);
    $id_pais        = $campos_pais[0]['id_pais'];
	
//Verifica se o Cliente é do Tipo Internacional ...
    $tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';

//Seleciona todos os itens do "id_nf_outra" passado por parâmetro ...
    $sql = "SELECT nfsoi.`id_nf_outra_item`, nfsoi.`id_produto_acabado`, nfsoi.`id_produto_insumo`, nfsoi.`referencia`, nfsoi.`discriminacao`, 
            nfsoi.`qtde`, nfsoi.`valor_unitario`, nfsoi.`observacao`, u.`sigla` 
            FROM `nfs_outras_itens` nfsoi 
            INNER JOIN `unidades` u ON u.`id_unidade` = nfsoi.`id_unidade` 
            WHERE nfsoi.`id_nf_outra` = '$_GET[id_nf_outra]' ";
    $campos_itens = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas_itens = count($campos_itens);
    if($linhas_itens == 0) {//Não existem Itens em aberto ...
?>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NÃO EXISTE(M) ITEM(NS) DE NOTA FISCAL OUTRA(S) EM ABERTO P/ SER(EM) EXCLUÍDO(S).
        </td>
    </tr>
<?
    }else {//Existe pelo menos um item em Aberto ...
?>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Itens de Nota Fiscal - N.º&nbsp;
            <font color='yellow'>
                <?=faturamentos::buscar_numero_nf($_GET['id_nf_outra'], 'O');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            Observação
        </td>
        <td>
            <font title='Pre&ccedil;o Liq. Final <?=$tipo_moeda;?>' style='cursor:help'>
                Pre&ccedil;o Liq. <br/>Final <?=$tipo_moeda;?>
            </font>
        </td>
        <td>
            <font title='Total Lote <?=$tipo_moeda;?>' style='cursor:help'>
                Total Lote <?=$tipo_moeda;?>
            </font>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas_itens; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_nf_outra_item[]' value="<?=$campos_itens[$i]['id_nf_outra_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=number_format($campos_itens[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos_itens[$i]['id_produto_acabado'])) {//Se foi cadastrado o PA ...
                echo intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado']);
            }else if(!empty($campos_itens[$i]['id_produto_insumo'])) {//Se foi cadastrado o PI ...
                $sql = "SELECT CONCAT(u.`sigla`, ' * ', g.`referencia`, ' * ', pi.`discriminacao`) AS dados_produto 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE pi.`id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
                echo $campos_pi[0]['dados_produto'];
            }else if(!empty($campos_itens[$i]['referencia']) || !empty($campos_itens[$i]['discriminacao'])) {//Se foi cadastrado uma Referência ou Discriminação ...
                echo $campos_itens[$i]['sigla'].' * '.$campos_itens[$i]['referencia'].' * '.$campos_itens[$i]['discriminacao'];
            }
        ?>
        </td>
        <td>
        <?
            if(empty($campos_itens[$i]['observacao'])) {
                echo '&nbsp';
            }else {
                echo "<img width='28' height='23' title='".$campos_itens[$i]['observacao']."' style='cursor:help;' src='../../../../imagem/olho.jpg'>";
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos_itens[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_itens+= $campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'];
	}
?>
    <tr align='right'>
        <td class='linhadestaque' colspan='5'>
            Total do(s) Item(ns) em <?=$tipo_moeda;?>: 
        </td>
        <td class='linhadestaque'>
            <font color='yellow' size='-1'>
                <?=number_format($total_itens, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
</form>
</html>
<?
    }
}
?>