<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/intermodular.php');
session_start('funcionarios');
?>
<html>
<head>
<title>.:: Etiqueta Teruya / Lojista ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function qtde_caixas(indice) {
    if(document.getElementById('txt_qtde_caixas'+indice).value == 0) {
        alert('QTDE DE CAIXAS INVÁLIDA !!! A QTDE DE CAIXAS NÃO PODE SER IGUAL A ZERO !')
        document.getElementById('txt_qtde_caixas'+indice).value = 1
    }
    if(eval(document.getElementById('txt_qtde_caixas'+indice).value) > eval(document.getElementById('hdd_qtde_nf'+indice).value)) {
        alert('QTDE DE CAIXAS INVÁLIDA !!! QTDE DE CAIXAS MAIOR DO QUE A QTDE DA NF !')
        document.getElementById('txt_qtde_caixas'+indice).focus()
        document.getElementById('txt_qtde_caixas'+indice).select()
        return false
    }else {
        ajax('div_etiqueta_teruya_lojista.php?indice='+indice+'&qtde_nf='+document.getElementById('hdd_qtde_nf'+indice).value+'&id_produto_acabado='+document.getElementById('hdd_produto_acabado'+indice).value, 'div_qtde_total'+indice)
    }
}

function validar() {
	var itens_preenchidos = 0
//Aqui eu verifico se tem algum item preenchido ...
	for(i = 0; i < document.form.elements['txt_qtde_total[]'].length; i++) {
		if(document.form.elements['txt_qtde_total[]'][i].value != '') {
			itens_preenchidos = 1
			break
		}
	}
//Verifico se tem algum Item que já foi preenchido ...
	if(itens_preenchidos == 0) {
		alert('DIGITE A QTDE TOTAL PARA ALGUM ITEM !')
		document.form.elements['txt_qtde_total[]'][0].focus()
		return false
	}
	
	for(i = 0; i < document.form.elements['hdd_qtde_nf[]'].length; i++) {
		var soma 	= 0
		var indice 	= -1//Começo com -1, porque o índice do 1º Elemento começa com 0 ... 
		for(j = 0; j < document.form.elements['txt_qtde_total[]'].length; j++) {
			if(document.getElementById('txt_qtde_total'+i+'|'+j) != null) {
				soma+= (eval(document.getElementById('txt_qtde_total'+i+'|'+j).value))
				indice++
			}
		}
		if(soma > 0) {//Significa que foi digitado algum valor em alguma linha ...
			if(document.form.elements['hdd_qtde_nf[]'][i].value != soma) {
				alert('QTDE TOTAL INVÁLIDA !!! QTDE TOTAL MAIOR DO QUE A QTDE DA NF ')
				document.getElementById('txt_qtde_total'+i+'|'+indice).focus()
				document.getElementById('txt_qtde_total'+i+'|'+indice).select()
				return false
			}
		}
	}
	nova_janela('imprimir.php', 'pop_up', 'F')
}
</Script>
</head>
<body>
<form name="form" method="post" action="imprimir.php" onsubmit="return validar()" target="pop_up">
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf']?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			Etiqueta Teruya / Lojista
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			Qtde Caixas
		</td>
		<td>
			Qtde de <br>Pçs na NF
		</td>
		<td>
			Qtde de <br>Pçs na Vide
		</td>                
		<td>
			Qtde Total <br>Pçs na NF
		</td>
		<td>
			Item
		</td>
		<td>
			Qtde Total
		</td>		
	</tr>
<?
	$vetor_produto_acabado = array();
	//Verifico se a NF possui Vide-Nota vinculada ...
	$sql = "SELECT id_nf_vide_nota 
                FROM `nfs` 
                WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
	$campos_vide_nota = bancos::sql($sql);
	if($campos_vide_nota[0]['id_nf_vide_nota'] > 0) {//Significa que essa NF possui Vide-Nota ...
            //Busco itens da NF normal e busco os itens da Vide Nota ...
            $sql = "(SELECT DISTINCT(id_produto_acabado), id_nfs_item, SUM(qtde) AS qtde 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '$_GET[id_nf]' GROUP BY id_produto_acabado) 
                    UNION ALL 
                    (SELECT DISTINCT(id_produto_acabado), id_nfs_item, SUM(qtde) AS qtde 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '".$campos_vide_nota[0]['id_nf_vide_nota']."' GROUP BY id_produto_acabado) ";
        }else {//Nesse caso a NF não possui Vide-Nota, mas pode ser vinculada de uma outra NF ...
            $sql = "(SELECT DISTINCT(nfsi.id_produto_acabado), nfsi.id_nfs_item, SUM(nfsi.qtde) AS qtde 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
                    WHERE nfs.`id_nf_vide_nota` = '$_GET[id_nf]' GROUP BY nfsi.id_produto_acabado) 
                    UNION ALL 
                    (SELECT DISTINCT(nfsi.id_produto_acabado), nfsi.id_nfs_item, SUM(nfsi.qtde) AS qtde 
                    FROM `nfs_itens` nfsi 
                    WHERE nfsi.`id_nf` = '$_GET[id_nf]' GROUP BY nfsi.id_produto_acabado) ";
	}
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
            /*Todo esse controle é pq as vezes o mesmo item pode estar em NFs diferentes, e esse só deve 
            aparecer uma vez, tirando toda a redundância, somando todas as suas quantidades ...*/
            if(in_array($campos[$i]['id_produto_acabado'], $vetor_produto_acabado)) {
                    $vetor_qtde[$campos[$i]['id_produto_acabado']]	+= $campos[$i]['qtde'];
            }else {
                    $vetor_produto_acabado[] 			= $campos[$i]['id_produto_acabado'];
                    $vetor_qtde[$campos[$i]['id_produto_acabado']] 	= $campos[$i]['qtde'];
            }
            $vetor_qtde_separada[$campos[$i]['id_produto_acabado']][] = $campos[$i]['qtde'];
	}
	//Busco todos os PA´s levantandos anteriormente ...
	$sql = "SELECT id_produto_acabado 
		FROM `produtos_acabados` 
		WHERE `id_produto_acabado` IN (".implode(',', $vetor_produto_acabado).") ORDER BY referencia, discriminacao ";
	$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
	$linhas = count($campos);
	for($i = 0 ; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td>
			<input type='text' name='txt_qtde_caixas[]' id='txt_qtde_caixas<?=$i;?>' value='1' size='4' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
			<input type='button' name='cmd_ok' value='OK' title='OK' onclick="qtde_caixas('<?=$i;?>')" class='botao'>
		</td>
		<td>		
			<?=intval($vetor_qtde_separada[$campos[$i]['id_produto_acabado']][0]);?>
		</td>
		<td>			
			<?=intval($vetor_qtde_separada[$campos[$i]['id_produto_acabado']][1]);?>
		</td>                
		<td>
			<input type='hidden' name='hdd_qtde_nf[]' id="hdd_qtde_nf<?=$i;?>" value="<?=intval($vetor_qtde[$campos[$i]['id_produto_acabado']]);?>" size='4' maxlength='3' class='caixadetexto'>
			<?=intval($vetor_qtde[$campos[$i]['id_produto_acabado']]);?>
		</td>
		<td align="left">
			<?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
		</td>
		<td>
                    <div id="div_qtde_total<?=$i;?>">
                        <input type='text' name='txt_qtde_total[]' value="<?=intval($vetor_qtde[$campos[$i]['id_produto_acabado']]);?>" size='4' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
                        <input type='hidden' name='hdd_produto_acabado[]' id='hdd_produto_acabado<?=$i;?>' value="<?=$campos[$i]['id_produto_acabado'];?>">
                    </div>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='6'>
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR')" class="botao">
			<input type="submit" name="cmd_imprimir" value="Imprimir" title="Imprimir" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>