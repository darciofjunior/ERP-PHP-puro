<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA N&Atilde;O RETORNOU NENHUM RESULTADO.</font>';

//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hdd_checkbox_mostrar_esp 	= $_POST['hdd_checkbox_mostrar_esp'];
    $hdd_mostrar_componentes    = $_POST['hdd_mostrar_componentes'];
    $txt_referencia 		= $_POST['txt_referencia'];
    $txt_discriminacao 		= $_POST['txt_discriminacao'];
    $id_oc                      = $_POST['id_oc'];
}else {
    $hdd_checkbox_mostrar_esp 	= $_GET['hdd_checkbox_mostrar_esp'];
    $hdd_mostrar_componentes    = $_GET['hdd_mostrar_componentes'];
    $txt_referencia 		= $_GET['txt_referencia'];
    $txt_discriminacao 		= $_GET['txt_discriminacao'];
    $id_oc			= $_GET['id_oc'];
}

//Se essa opção estiver desmarcada, então eu só mostro os P.A(s) que são do Tipo Normal de Linha ...
if($hdd_checkbox_mostrar_esp == 0)  $condicao_esp           = " AND pa.referencia <> 'ESP' ";
if($hdd_mostrar_componentes == 0)   $condicao_componentes   = " AND gpa.id_familia <> '23' ";

/*Aqui eu transformo o | em % porque é o caractér padrão que o Mysql utiliza p/ fazer os Filtros, só não lista os 
PA(s) que são Componentes ...*/
$sql = "SELECT DISTINCT(pa.id_produto_acabado) 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao_componentes 
        WHERE pa.`referencia` LIKE '%$txt_referencia%' 
        AND pa.`discriminacao` LIKE '%".str_replace('|', '%', $txt_discriminacao)."%' 
        AND pa.`ativo` = '1' 
        $condicao_esp ORDER BY pa.referencia ";
$campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina, 'ajax', 'div_pesquisar_itens_incluir');
$linhas = count($campos);
if($linhas == 0) {//Caso não encontrou nenhum Item ...
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table border="0" width="90%" cellspacing='1' cellpadding='1' align='center'>
	<tr class="atencao" align='center'>
		<td colspan="3">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<?=utf8_encode($mensagem[1]);?>
			</font>
		</td>
	</tr>
</table>
<!--**************Esses objetos serão submetidos**************-->
<input type='hidden' name='id_oc' value='<?=$id_oc;?>'>
<input type='hidden' name='chkt_produto_acabado[]'>
<!--**********************************************************-->
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='85%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>
            <fieldset>
                <legend>
                    <span>
                        <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                            <b>INCLUIR PRODUTOS ACABADOS</b>
                        </font>
                    </span>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
                    <tr class='linhacabecalho' align='center'>
                        <td>
                            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onClick="selecionar_tudo_incluir(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
                        </td>
                        <td>
                            Produto
                        </td>
                        <td>
                            NFs Sa&iacute;da
                        </td>
                        <td>
                            Qtde
                        </td>
                        <td>
                            Defeito Alegado
                        </td>
                    </tr>
<?
	//Busco quem é o Cliente da OC, porque irei precisar no SQL mais abaixo ...
	$sql = "SELECT id_cliente 
			FROM `ocs` 
			WHERE `id_oc` = '$id_oc' LIMIT 1 ";
	$campos_cliente = bancos::sql($sql);

	for($i = 0; $i < $linhas; $i++) {
?>
                    <tr class="linhanormal" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
                        <td>
                            <input type='checkbox' name='chkt_produto_acabado[]' id="chkt_produto_acabado<?=$i;?>" value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" class='checkbox'>
                        </td>
                        <td align="left">
                            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
                        </td>
                        <td>
                        <?
                            //Aqui eu verifico se existe pelo menos 1 NF de Saída para esse PA e Cliente ...
                            $sql = "SELECT DISTINCT(nfs.id_nf) 
                                    FROM `nfs` 
                                    INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                                    WHERE nfs.`id_cliente` = '".$campos_cliente[0]['id_cliente']."' LIMIT 1 ";
                            $campos_nfs = bancos::sql($sql);
                            if(count($campos_nfs) == 1) {//Se existe pelo menos 1 NF, exibo esse Botão p/ q seja visualizado todas as NFs desse Cliente, desse PA ...
                        ?>
                                <input type="button" name="cmd_nfs_saida" id="cmd_nfs_saida<?=$i;?>" value="NFs Sa&iacute;da" title="NFs Sa&iacute;da" onclick="javascript:nova_janela('nfs_saida.php?id_cliente=<?=$campos_cliente[0]['id_cliente']?>&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'NFS_SAIDA', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '');checkbox_incluir('<?=$i;?>', '#E8E8E8')" style='color:purple' class='textdisabled' disabled>
                        <?
                            }else {//Para não dar erro de JavaScript e Paginação ...
                        ?>
                                <input type="hidden" name="cmd_nfs_saida" id="cmd_nfs_saida<?=$i;?>" class='textdisabled' disabled>
                        <?	
                            }
                        ?>
                        </td>
                        <td>
                            <input type="text" name="txt_quantidade[]" id="txt_quantidade<?=$i;?>" title="Digite a Quantidade" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event);validar_itens(event)" maxlength="7" size="9" class='textdisabled' disabled>
                        </td>
                        <td>
                            <textarea name="txt_defeito_alegado[]" id="txt_defeito_alegado<?=$i;?>" title="Digite o Defeito Alegado" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" maxlength="255" rows='2' cols='50' class='textdisabled' disabled></textarea>
                        </td>
                    </tr>
<?
	}
?>
                    <tr class='linhacabecalho' align='center'>
                        <td colspan='5'>
                            <input type='button' name='cmd_incluir' value='Incluir' title='Incluir' onclick='return validar()' style='color:green' class='botao'>
                        </td>
                    </tr>
                    <tr align='center'>
                        <td colspan='5'>
                            <?=paginacao::print_paginacao('sim');?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<input type='hidden' name='id_oc' value='<?=$id_oc;?>'>
</body>
</html>
<?}?>