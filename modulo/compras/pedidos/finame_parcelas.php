<table width='100%' border="0" cellspacing ='1' cellpadding='1' align="center">
<?
    require('../../../lib/data.php');
//Gera a Qtde de Parcelas de acordo com a Qtde que foi passada por parâmetro pelo usuário ...
    $cont_tab = 0;
    for($i = 1; $i <= $qtde_parcelas; $i++) {
        if($i == 1) {//Primeira parcela só herda o valor digitado pelo Usuário ...
            $diferenca_dias = data::diferenca_data(data::datatodate($_POST[txt_data_entrega_atual], '-'), data::datatodate($_POST[data_primeira_parcela], '-'));
            $dias_loop = $diferenca_dias[0];
            $data_loop = $_POST[data_primeira_parcela];
        }else {//A partir da 2ª parcela tem controles ...
            if($tipo_negociacao == 1) {//Dia Fixo ...
                $data_antes_da_nova_data = $data_loop;//Data atual até o momento, antes de gerar a Nova Data ...
                //Gerando a Nova Data ...
                /************************/
                $dia_data = substr($data_loop, 0, 2);
                $mes_data = substr($data_loop, 3, 2);
                $ano_data = substr($data_loop, 6, 4);

                $mes_data++;
                if($mes_data == 13) {
                    $mes_data = 1;
                    $ano_data++;
                }
                if($mes_data < 10) $mes_data = '0'.$mes_data;
                /************************/
                $data_loop      = $dia_data.'/'.$mes_data.'/'.$ano_data;
                $diferenca_dias = data::diferenca_data(data::datatodate($data_antes_da_nova_data, '-'), data::datatodate($data_loop, '-'));
                $dias_loop+= $diferenca_dias[0];
            }else {//Intervalo Fixo ...
                $dias_loop+= $_POST[qtde_dias];
                $data_loop = data::adicionar_data_hora($data_loop, $_POST[qtde_dias]);
            }
        }
?>
    <tr class='linhanormal'>
        <td>
            <b>Parcela N.&#186; <?=$i;?>:</b>
        </td>
        <td>
            Dias: <input type='text' name='txt_dias[]' id='txt_dias<?=$i;?>' value='<?=$dias_loop;?>' title='Digite o N.º de Dias' size='8' maxlength='7' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_novo_prazo('txt_data<?=$i;?>', 'txt_dias<?=$i;?>')" tabIndex="<?='10'.$cont_tab;?>" class='caixadetexto'>
        </td>
        <td>
            Data: <input type='text' name='txt_data[]' id='txt_data<?=$i;?>' value='<?=$data_loop;?>' title='Data' size='12' class='textdisabled' disabled>&nbsp;&nbsp;
        </td>
    </tr>
<?
        $cont_tab++;
    }
/********************************************************************************************************/
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            &nbsp;
        </td>
    </tr>
<!--**********Essa caixa será utilizada p/ fazer um controle com relação a Qtde de Parcelas**********-->
<input type='hidden' name='qtde_parcelas_gerada' value='<?=$qtde_parcelas;?>'>
<!--*************************************************************************************************-->
</table>
</body>
</html>