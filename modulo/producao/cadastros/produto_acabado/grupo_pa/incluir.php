<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/cascates.php');
require('../../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='erro'>GRUPO P.A. J¡ EXISTENTE.</font>";

if(!empty($_POST['txt_grupo'])) {
    $sql = "SELECT id_grupo_pa 
            FROM `grupos_pas` 
            WHERE `nome` = '$_POST[txt_grupo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe
        $sql = "INSERT INTO `grupos_pas` (`id_grupo_pa`, `id_familia`, `nome`, `nome_ing`, `nome_esp`, `lote_min_producao_reais`, `prazo_entrega`, `tolerancia`, `observacao`, `ativo`) VALUES (NULL, '$_POST[cmb_familia]', '$_POST[txt_grupo]', '$_POST[txt_grupo_ingles]', '$_POST[txt_grupo_espanhol]', '$_POST[txt_lote_min_prod_reais]', '$_POST[cmb_prazo_entrega]', '$_POST[txt_tolerancia]', '$_POST[txt_observacao]', '1') ";
        bancos::sql($sql);
        $id_grupo_pa    = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('GRUPO P.A. INCLUIDO COM SUCESSO !')
        /*Redireciono o Usu·rio p/ a Tela de Alterar Grupo de PA p/ que o mesmo possa estar atrelando Empresas Divisıes
        se desejar ou se necess·rio ...*/
        window.location = 'alterar.php?passo=2&id_grupo_pa=<?=$id_grupo_pa;?>'
    </Script>
<?
    }else {
        $valor  = 1;
    }
}

if(cascate::incluir('familias') == 1) {//Verifica se existe FamÌlia ...
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../../html/index.php?valor=18'
    </Script>
<?
}
?>
<html>
<title>.:: Incluir Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(valor) {
//FamÌlia
    if(!combo('form', 'cmb_familia', '', 'SELECIONE A FAMÕLIA !')) {
        return false
    }
//Grupo P.A.
    if(!texto('form', 'txt_grupo', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/.-_∫™():', 'GRUPO P.A.', '2')) {
        return false
    }
//Grupo P.A. InglÍs
    if(document.form.txt_grupo_ingles.value != '') {
        if(!texto('form', 'txt_grupo_ingles', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'GRUPO P.A. INGL S', '2')) {
            return false
        }
    }
//Grupo P.A. Espanhol
    if(document.form.txt_grupo_espanhol.value != '') {
        if(!texto('form', 'txt_grupo_espanhol', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'GRUPO P.A. ESPANHOL', '2')) {
            return false
        }
    }
//Lote MÌn. ProduÁ„o R$
    if(!texto('form', 'txt_lote_min_prod_reais', '1', '0123456789,.', 'LOTE MÕNIMO PRODU«√O R$', '2')) {
        return false
    }
//Prazo de Entrega
    if(!combo('form', 'cmb_prazo_entrega', '', 'SELECIONE O PRAZO DE ENTREGA !')) {
        return false
    }
//Toler‚ncia
    if(document.form.txt_tolerancia.value != '') {
        if(!texto('form', 'txt_tolerancia', '1', "abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ '1234567890", 'TOLER¬NCIA', '1')) {
            return false
        }
    }
    document.form.passo.value = 1
    limpeza_moeda('form', 'txt_lote_min_prod_reais, ')
    if(valor == 2) {
        document.form.id_grupo_pa.value = ''
        document.form.grupo_pa_novo.value = 1
    }
    document.form.submit()
}
</Script>
<body>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>FamÌlia:</b>
        </td>
        <td>
            <select name='cmb_familia' title='Selecione uma FamÌlia' class='combo'>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $cmb_familia);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A.:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo' value='<?=$txt_grupo;?>' title='Digite o Grupo P.A.' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A. InglÍs:
        </td>
        <td>
            <input type='text' name='txt_grupo_ingles' value='<?=$txt_grupo_ingles;?>' title='Digite o Grupo P.A. InglÍs' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A. Espanhol:
        </td>
        <td>
            <input type='text' name='txt_grupo_espanhol' value='<?=$txt_grupo_espanhol;?>' title='Digite o Grupo P.A. Espanhol' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lote MÌnimo ProduÁ„o R$:</b>
        </td>
        <td>
            <input type='text' name='txt_lote_min_prod_reais' value='<?=number_format($txt_lote_min_prod_reais, 2, ',', '.');?>' title='Digite o Lote MÌnimo ProduÁ„o R$' size='14' maxlength='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>		
        <?
            $vetor_prazos_entrega = vendas::prazos_entrega();
        ?>
            <select name='cmb_prazo_entrega' title='Selecione o Prazo de Entrega' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                        if($cmb_prazo_entrega == $indice) {//Se igual seleciona esse valor
                ?>
                <option value='<?=$indice;?>' selected><?=$prazo_entrega;?></option>
                <?
                        }else {
                ?>
                <option value='<?=$indice;?>'><?=$prazo_entrega;?></option>
                <?
                        }
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Toler‚ncia:
        </td>
        <td>
            <input type='text' name='txt_tolerancia' title='Digite a Toler‚ncia' size='40' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='50' rows='5' title="Digite a ObservaÁ„o" maxlength='255' class='caixadetexto'><?=$txt_observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>