<table border="0" width="100%" align="center" cellspacing ='1' cellpadding='1'>
<?
	/***********************Significa que o usuário optou por Visitar Cliente(s)***********************/
	if($_POST['qtde_operacoes'] > 0) {
		$cont_tab = 0;
		for($i = 0; $i < $_POST['qtde_operacoes']; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td>
			<b>Opera&ccedil;&atilde;o:</b> 
		</td>
		<td>
			<input type="text" name="txt_operacao[]" value="<?=$_POST['txt_operacao'][$i];?>" id="txt_operacao<?=$i;?>" autocomplete="off" title="Digite a Operação" size="65" maxlength="60" tabIndex="<?='10'.$cont_tab;?>" class="caixadetexto">
			&nbsp;
			<?
				if(($i + 1) == $_POST['qtde_operacoes']) {
			?>
			<img src = "../../../../imagem/menu/excluir.png" border="0" title="Excluir Opera&ccedil;&atilde;o" alt="Excluir Opera&ccedil;&atilde;o" onClick="excluir_operacao()">
			<?
				}
			?>
		</td>
	</tr>
<?
			$cont_tab++;
		}
	}
?>
</table>