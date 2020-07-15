<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
segurancas::geral('/erp/albafer/modulo/producao/oes/incluir.php', '../../../');

if(!empty($_GET['id_produto_acabado'])) {
//Aqui eu verifico a qtde_disponível q eu tenho em estoque do PA Padrão passado por parâmetro ...
    $vetor_estoque_pa   = estoque_acabado::qtde_estoque($_GET['id_produto_acabado']);
    $qtde_disponivel    = number_format($vetor_estoque_pa[3], 0, ',', '');
    $entrada_antecipada = number_format($vetor_estoque_pa[15], 0, ',', '');
}
?>
<table width='100%' border='0' cellspacing='1' cellpadding='1'>
    <tr class='linhanormal'>
        <td>
            <b>Estoque Dispon&iacute;vel P.A. Enviado: </b>&nbsp;
            <input type='text' name='txt_estoque_disponivel' value='<?=$qtde_disponivel;?>' title='Estoque Dispon&iacute;vel P.A. Enviado' size='8' maxlength='7' onfocus='document.form.txt_qtde_enviada_a_retornar.focus()' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Entrada Antecipada: </b>&nbsp;
            <input type='text' name='txt_entrada_antecipada' value='<?=$entrada_antecipada;?>' title='Entrada Antecipada' size='8' maxlength='7' onfocus='document.form.txt_qtde_enviada_a_retornar.focus()' class='textdisabled'>
            &nbsp;
            <input type='checkbox' name='chkt_estornar_entrada_antecipada_nf_compras' id='chkt_estornar_entrada_antecipada_nf_compras' value='S' title='Estornar Entrada Antecipada NF de Compras' class='checkbox'>
            <label for='chkt_estornar_entrada_antecipada_nf_compras'>
                <font color='red'>
                    <b>ESTORNAR ENTRADA ANTECIPADA NF DE COMPRAS DA QTDE ENVIADA / &Agrave; RETORNAR</b>
                </font>
            </label>
        </td>
    </tr>
</table>