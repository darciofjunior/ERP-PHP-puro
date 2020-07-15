<?
if($_POST['status'] == 3) {//Enviado para processo Interno ...
?>
<table width='940' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormalescura' align='center'>
        <td>
            <input type='radio' name='opt_enviado' value='Edson' id='lbl_enviado1'>
            <label for='lbl_enviado1'>Edson</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_enviado' value='Outro Funcionário' id='lbl_enviado2'>
            <label for='lbl_enviado2'>Outro Funcion&aacute;rio</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_enviado' value='Embalagem' id='lbl_enviado3'>
            <label for='lbl_enviado3'>Embalagem</label>
        </td>
    </tr>
</table>
<?
}else if($_POST['status'] == 7) {//Enviado para Estoque ...
?>
<table width='940' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormalescura' align='center'>
        <td>
            <input type='radio' name='opt_enviado' value='Troca Produto Ind. em Garantia' onclick='controlar_options_enviar_estoque()' id='lbl_enviado1'>
            <label for='lbl_enviado1'>Troca Produto Ind. em Garantia</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_enviado' value='Troca Produto Rev. em Garantia' onclick='controlar_options_enviar_estoque()' id='lbl_enviado2'>
            <label for='lbl_enviado2'>Troca Produto Rev. em Garantia</label>
            &nbsp;<input type="button" id='cmd_vincular_fornecedor' value='Vincular Fornecedor' title='Vincular Fornecedor' onclick="if(this.disabled == false) nova_janela('vincular_fornecedor.php', 'CONSULTAR', '', '', '', '', '380', '900', 'c', 'c', '', '', 's', 's', '', '', '')" class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_enviado' value='Produto sem Garantia' onclick='controlar_options_enviar_estoque()' id='lbl_enviado3'>
            <label for='lbl_enviado3'>Produto sem Garantia</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_enviado' value='Peça consertada internamente' onclick='controlar_options_enviar_estoque()' id='lbl_enviado4'>
            <label for='lbl_enviado4'>Pe&ccedil;a consertada Internamente</label>
            <br>
            Fornecedor: <input type='text' id='txt_fornecedor' size='120' class='textdisabled' disabled>
            &nbsp;<img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Fornecedor' alt='Excluir Fornecedor' onclick="if(document.getElementById('txt_fornecedor').value != '') excluir_fornecedor('<?=$campos[0]['id_oc_item'];?>')">
        </td>
    </tr>
</table>
<?
}else if($_POST['status'] == 8) {//Manipular Estoque ...
    require('../../../../lib/bancos.php');
    
    $sql = "SELECT CONCAT(oi.`qtde`, ' ', u.`sigla`) AS quantidade, CONCAT(pa.`referencia`, ' / ' ,pa.`discriminacao`) AS produto_acabado 
            FROM `ocs_itens` oi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE oi.`id_oc_item` = '$_GET[id_oc_item]' LIMIT 1 ";
    $campos_ocs = bancos::sql($sql);
?>
<table width='940' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormalescura' align='center'>
        <td>
            Ser&aacute; descontado <font color='red'><?=$campos_ocs[0]['quantidade'];?></font> do Produto Acabado: <b><?=$campos_ocs[0]['produto_acabado']?></b>.
        </td>
    </tr>
</table>
<?
}else if($_POST['status'] == 10) {//Desdobrar Item ...
?>
<table width='940' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormalescura' align='center'>
        <td>
            Qtde p/ este Item atual: <input type='text' id='txt_qtde_para_este_item' maxlength='4' size='5' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
</table>
<?}?>