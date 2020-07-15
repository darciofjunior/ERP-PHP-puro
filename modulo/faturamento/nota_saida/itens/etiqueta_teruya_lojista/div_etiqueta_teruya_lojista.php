<?
	$qtde 	= intval($_POST['qtde_nf'] / $_POST['txt_qtde_caixas'][$_POST[indice]]);
	$resto 	= $_POST['qtde_nf'] - ($qtde * $_POST['txt_qtde_caixas'][$_POST[indice]]);	
	for($i = 0; $i < $_POST['txt_qtde_caixas'][$_POST[indice]]; $i++) {
            //Quando estivermos na última linha do Loop, verifico se existe resto e acrescento o mesmo na $qtde ...
            if(($i + 1) == $_POST['txt_qtde_caixas'][$_POST[indice]] && $resto > 0) $qtde+= $resto;
?>
            <!--O índice é o mesmo independente do N.º de Caixas para cada PA ...-->
            <input type='text' name='txt_qtde_total[]' id='txt_qtde_total<?=$_POST['indice'].'|'.$i;?>' value='<?=$qtde?>' size='4' maxlength='3' class='caixadetexto'>
            <input type='hidden' name='hdd_produto_acabado[]' value="<?=$_POST['id_produto_acabado'];?>">
<?		
	}
?>