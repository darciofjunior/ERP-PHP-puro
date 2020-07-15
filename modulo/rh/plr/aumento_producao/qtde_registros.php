<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal' align='center'>
        <td>
            <b><?=utf8_encode('Registro N.º');?></b>
        </td>
        <td>
            <b>Producao Valor Anual</b>
        </td>
        <td>
            <b>Valor Premio Anual</b>
        </td>
    </tr>
<?
//Gera a Qtde de Registros de acordo com a Qtde que foi passada por parâmetro pelo usuário ...
    for($i = 1; $i <= $_POST['qtde_registros']; $i++) {
        if($i == 1) {//Somente a primeira caixa vem habilitada ...
            $class = 'caixadetexto';
            $disabled = '';
        }else {//As demais caixas vem desabilitadas ...
            $class = 'textdisabled';
            $disabled = 'disabled';
        }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$i;?>
        </b>
        <td>
            <input type='text' name='txt_producao_anual[]' id='txt_producao_anual<?=$i;?>' title='Digite a Producao Anual' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type="text" name="txt_valor_premio_anual[]" id="txt_valor_premio_anual<?=$i;?>" title="Digite o Valor Premio Anual" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="15" class="<?=$class;?>" <?=$disabled;?>>
            <?
                if($i == 1 && $_POST['qtde_registros'] > 1) {
            ?>
                <img src='../../../../imagem/seta_abaixo.gif' width='12' height='12' border='0' onclick='calcular_proximos_registros()'>
            <?
                }else {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
    </tr>
<?
	}
/********************************************************************************************************/
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            &nbsp;
        </td>
    </tr>
<!--**********Essa caixa será utilizada p/ fazer um controle com relação a Qtde de Registros*********-->
<input type='hidden' name='qtde_registros_gerado' value='<?=$_POST['qtde_registros'];?>'>
<!--*************************************************************************************************-->
</table>