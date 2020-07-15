<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>PROGRAMAÇÃO SEMANAL DE VISITA INCLUÍDA COM SUCESSO.</font>";

if(!empty($_POST['cmb_representante'])) {   
    if(!empty($_POST['cmb_cliente'])) {
        $id_cliente         = $_POST['cmb_cliente'];
//Busco o primeiro contato cadastrado do Cliente passado por parâmetro ...
        $sql = "SELECT id_cliente_contato
                FROM clientes_contatos 
                WHERE `id_cliente` = '$_POST[cmb_cliente]' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_contato     = bancos::sql($sql);
        $linhas_contato     = count($campos_contato);
        $id_cliente_contato = ($linhas_contato == 0) ? 'NULL' : $campos_contato[0]['id_cliente_contato'];
    }else {
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $id_cliente         = 'NULL';
        $id_cliente_contato = 'NULL';
    }
    $data_registro = data::datatodate($_POST['txt_data_registro'], '-');
//Inserindo Programação Semanal de Visita na parte da Manhã ...
    if(!empty($_POST['chkt_periodo_m'])) {
        $sql = "INSERT INTO `programacoes_semanais_visitas` (`id_programacao_semanal_visita`, `id_representante`, `id_cliente`, `id_cliente_contato`, `data_registro`, `periodo`, `perspectiva_periodo`, `comentario`)
                VALUES (NULL, '$_POST[cmb_representante]', $id_cliente, $id_cliente_contato, '$data_registro', '$_POST[chkt_periodo_m]', '$_POST[txt_pespectiva_pedido]', '$_POST[txt_comentario]') ";
        bancos::sql($sql);
    }
//Inserindo Programação Semanal de Visita na parte da Tarde ...
    if(!empty($_POST['chkt_periodo_t'])) {
        $sql = "INSERT INTO `programacoes_semanais_visitas` (`id_programacao_semanal_visita`, `id_representante`, `id_cliente`, `id_cliente_contato`, `data_registro`, `periodo`, `perspectiva_periodo`, `comentario`)
                VALUES (NULL, '$_POST[cmb_representante]', $id_cliente, $id_cliente_contato, '$data_registro', '$_POST[chkt_periodo_t]', '$_POST[txt_pespectiva_pedido]', '$_POST[txt_comentario]') ";
        bancos::sql($sql);
    }
    
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Incluir Programação Semanal de Visita ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Representante ...
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
//Data de Registro ...
    if(!data('form', 'txt_data_registro', '4000', 'REGISTRO')) {
        return false
    }
/******************************************************************************/
//A Data de Registro nunca pode ser menor do que a Data Atual, se for maior aí tudo bem ...
    var data_atual      = '<?=date('Ymd')?>'
    var data_registro   = document.form.txt_data_registro.value
    data_registro       = data_registro.substr(6, 4) + data_registro.substr(3, 2) + data_registro.substr(0, 2)
    data_registro       = eval(data_registro)

    if(data_registro < data_atual) {
        alert('DATA DE REGISTRO INVÁLIDA !!!\n\nDATA DE REGISTRO NÃO PODE SER MENOR DO QUE A DATA ATUAL !')
        document.form.txt_data_registro.focus()
        document.form.txt_data_registro.select()
        return false
    }
/******************************************************************************/
//Período ...
    if(document.form.chkt_periodo_m.checked == false && document.form.chkt_periodo_t.checked == false) {
        alert('SELECIONE O PERÍODO !')
        return false
    }
//Comentário ...
    if(document.form.txt_comentario.value == '') {
        alert('DIGITE O COMENTÁRIO !')
        document.form.txt_comentario.focus()
        return false
    }
    limpeza_moeda('form', 'txt_pespectiva_pedido, ')
}

function carregar_clientes() {
    ajax('consultar_clientes.php', 'cmb_cliente')
}
</Script>
</head>
<body onload='document.form.cmb_representante.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Programação Semanal de Visita
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' onchange='carregar_clientes()' class='combo'>
            <?
                /*Observação: O Nishimura é o único Representante que pode ter acesso a todos Representantes 
                porque ele é Gerente de Vendas ...*/
            
                //Aqui eu verifico se o Funcionário que está Logado no Sistema é um Representante ...
                $sql = "SELECT id_representante 
                        FROM `representantes_vs_funcionarios` 
                        WHERE id_funcionario = '$_SESSION[id_funcionario]' LIMIT 1 ";
                $campos_representante   = bancos::sql($sql);
                if(count($campos_representante) == 1 && $_SESSION['id_funcionario'] != 136) {//Sim é um Representante e diferente do Nishimura ...
                    //Aqui eu busco o próprio Representante logado + os seus subordinados no 2º SQL ...
                    $sql = "(SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                            FROM `representantes` 
                            WHERE `id_representante` = '".$campos_representante[0]['id_representante']."') 
                            UNION 
                            (SELECT r.`id_representante`, CONCAT(r.`nome_fantasia`, ' / ', r.`zona_atuacao`) AS dados 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                            WHERE rs.`id_representante_supervisor` = '".$campos_representante[0]['id_representante']."') 
                            ORDER BY dados ";
                }else {//Não é Representante, então trago todos os Representantes que estão cadastrados no Sistema ...
                    $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' ORDER BY dados ";
                }
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <select name='cmb_cliente' id='cmb_cliente' title='Selecione o Cliente' class='combo'>
                <option value=''> - </option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>	
        <td>
            <b>Data de Registro:</b>
        </td>
        <td>
            <input type='text' name='txt_data_registro' onkeyup="verifica(this, 'data', '', '', event)" maxlength='10' size='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_registro&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Periodo:</b>
        </td>
        <td>
            <input type='checkbox' name='chkt_periodo_m' id='chkt_periodo_m' value='M' class='checkbox'>
            <label for='chkt_periodo_m'>
                Manhã
            </label>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_periodo_t' id='chkt_periodo_t' value='T' class='checkbox'>
            <label for='chkt_periodo_t'>
                Tarde
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>	
        <td>
            Pespectiva de Pedido R$:
        </td>
        <td>
            <input type='text' name='txt_pespectiva_pedido' maxlength='10' size='11' onkeyup="verifica(this, 'moeda_especial', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Comentário:</b>
        </td>
        <td>
            <textarea name='txt_comentario' title='Digite o Comentário' rows='5' cols='100' maxlength='500' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.cmb_representante.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>