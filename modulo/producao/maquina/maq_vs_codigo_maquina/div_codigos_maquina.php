<table border="0" width="100%" align="center" cellspacing ='1' cellpadding='1'>
<?
	/***********************Significa que o usuário optou por Visitar Cliente(s)***********************/
	if($_POST['qtde_codigos_maquina'] > 0) {
		$cont_tab = 0;
		for($i = 0; $i < $_POST['qtde_codigos_maquina']; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td>
			<b>C&oacute;digo de M&aacute;quina:</b> 
		</td>
		<td>
			<input type="text" name="txt_codigo_maquina[]" value="<?=$_POST['txt_codigo_maquina'][$i];?>" id="txt_codigo_maquina<?=$i;?>" onkeyup="auto_complete('consultar_clientes_representante.php', 'txt_cliente<?=$i;?>', -38.8, 29.5, event)" autocomplete="off" title="Digite o C&oacute;digo de M&aacute;quina" size="95" maxlength="85" tabIndex="<?='10'.$cont_tab;?>" class="caixadetexto">
			&nbsp;
			<?
				if(($i + 1) == $_POST['qtde_codigos_maquina']) {
			?>
			<img src = "../../../../imagem/menu/excluir.png" border="0" title="Excluir C&oacute;digo de M&aacute;quina" alt="Excluir C&oacute;digo de M&aacute;quina" onClick="excluir_codigo_maquina()">
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