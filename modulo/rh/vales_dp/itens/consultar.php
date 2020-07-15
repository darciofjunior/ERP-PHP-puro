<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/depto_pessoal.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Gerenciar Vale(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function incluir_data_holerith() {
    html5Lightbox.showLightbox(7, '../../class_data_holerith/incluir.php')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        html5Lightbox.showLightbox(7, '../../class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value)
    }
}

function atualizar() {
    document.location = '<?=$PHP_SELF;?>'
}

function controlar_objetos() {
    if(document.form.chkt_data_holerith_vencida.checked == true) {
        situacao    = true
        cor         = 'gray'
        fundo       = '#FFFFE1'
    }else {
        situacao    = false
        cor         = 'Brown'
        fundo       = '#FFFFFF'
    }
//Controle para habilitar e desabilitar os objetos ...
    document.form.txt_funcionario.disabled = situacao
    document.form.cmb_data_holerith.disabled = situacao
    document.form.cmb_empresa.disabled = situacao
    document.form.cmb_tipo_vale.disabled = situacao
    document.form.cmb_descontar_pd_pf.disabled = situacao
    document.form.chkt_somente_nao_descontado.disabled = situacao
    document.form.chkt_somente_vales_zerados.disabled = situacao
//Controle com as cores dos objetos ...
    document.form.txt_funcionario.style.color = cor
    document.form.cmb_data_holerith.style.color = cor
    document.form.cmb_empresa.style.color = cor
    document.form.cmb_tipo_vale.style.color = cor
    document.form.cmb_descontar_pd_pf.style.color = cor
    document.form.chkt_somente_nao_descontado.style.color = cor
    document.form.chkt_somente_vales_zerados.style.color = cor
//Controle com as cores de Fundo dos objetos ...
    document.form.txt_funcionario.style.background = fundo
    document.form.cmb_data_holerith.style.background = fundo
    document.form.cmb_empresa.style.background = fundo
    document.form.cmb_tipo_vale.style.background = fundo
    document.form.cmb_descontar_pd_pf.style.background = fundo
    document.form.chkt_somente_nao_descontado.style.background = fundo
    document.form.chkt_somente_vales_zerados.style.background = fundo
}
</Script>
</head>
<body onload='document.form.txt_funcionario.focus()'>
<form name='form' method='post' action='itens.php'>
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerenciar Vale(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcionário
        </td>
        <td>
            <input type='text' name='txt_funcionario' title='Digite o Funcionário' size='30' maxlength='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Holerith
        </td>
        <td>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' class='combo'>
            <?
                $data_atual_menos_180 = data::adicionar_data_hora(date('d/m/Y'), -180);
                $data_atual_menos_180 = data::datatodate($data_atual_menos_180, '-');
            
/*Só listo nessa Combo as Datas de Holeriths que sejam maiores que a Data de 2 meses atrás, eu só 
mantenho esses 2 meses ainda p/ que se possa consultar algum dado de vale antigo dentro desse período ...*/
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_atual_menos_180' ORDER BY data ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick="if(document.form.cmb_data_holerith.disabled == false) {incluir_data_holerith()}">
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick="if(document.form.cmb_data_holerith.disabled == false) {alterar_data_holerith()}">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Vale
        </td>
        <td>
            <select name='cmb_tipo_vale' title='Selecione o Tipo de Vale' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_tipos_vale = depto_pessoal::tipos_vale();
                
                    foreach($vetor_tipos_vale as $indice => $tipo_vale) {
                ?>
                <option value='<?=$indice;?>'><?=$tipo_vale;?></option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Financeira
        </td>
        <td>
            <input type='text' name='txt_financeira' title='Digite a Financeira' size='23' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descontar
        </td>
        <td>
            <select name='cmb_descontar_pd_pf' title='Selecione o Descontar' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='PD'>PD</option>
                <option value='PF'>PF</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' size='45' maxlength='42' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_nao_descontado' id='chkt_somente_nao_descontado' value='N' title='Somente não Descontado' class='checkbox' checked>
            <label for='chkt_somente_nao_descontado'>
                Somente não Descontado</font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_vales_zerados' id='chkt_somente_vales_zerados' value='1' title='Somente Vales Zerados' class='checkbox'>
            <label for='chkt_somente_vales_zerados'>Somente Vales Zerados</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_data_holerith_vencida' id='chkt_data_holerith_vencida' value='1' title='Data de Holerith Vencida e não Descontado' onclick='controlar_objetos()' class='checkbox'>
            <label for='chkt_data_holerith_vencida'>Data de Holerith Vencida e não Descontado</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_mostrar_vales_excluidos' id='chkt_mostrar_vales_excluidos' value='1' title='Mostrar Vales Excluídos' class='checkbox'>
            <label for='chkt_mostrar_vales_excluidos'>Mostrar Vales Excluídos</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_funcionario.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_incluir_vale' value='Incluir Vale' title='Incluir Vale' onclick="html5Lightbox.showLightbox(7, 'incluir.php')" style='color:red' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>