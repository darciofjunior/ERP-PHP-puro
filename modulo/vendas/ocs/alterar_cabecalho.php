<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>OC ALTERADA COM SUCESSO.</font>";

//Tratamento com as variáveis que vem por parâmetro ...
$id_oc = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_oc'] : $_GET['id_oc'];

//Se o usuário pediu para finalizar a OC ou alterou algum campo qualquer ...
if(!empty($_POST['id_oc'])) {
    if($_POST['hdd_finalizar_oc'] == 1 || !empty($_POST['cmb_representante'])) {//Objetos de Formulário - HABILITADOS ...
        $data_conclusao = data::datatodate($_POST['txt_data_conclusao'], '-');
        $txt_observacao = ucfirst(strtolower($_POST['txt_observacao']));
        
        if(!empty($_POST['chkt_finalizar_oc'])) {//Finalizar OC ...
            /*Aqui o usuário está na intenção de Finalizar a OC, mas antes verifico se não existe algum item 
            nessa mesma que esteja com a Marcação de "Cliente vai Devolver Peça" ...*/
            $sql = "SELECT `id_oc_item` 
                    FROM `ocs_itens` 
                    WHERE `id_oc` = '$id_oc' 
                    AND `cliente_vai_devolver_peca` = 'S' LIMIT 1 ";
            $campos_cliente_dev = bancos::sql($sql);//Se existir um item nessa Situação não posso Finalizar a OC ...
            $status             = (count($campos_cliente_dev) == 1) ? 0 : 1;
            $data_conclusao     = '';//Limpo a Data de Conclusão, afinal existe uma Irregularidade ...
?>
    <Script Language = 'JavaScript'>
        alert('NÃO É POSSÍVEL FINALIZAR ESSA OC !!!\n\nEXISTE(M) ITEM(NS) EM QUE O CLIENTE IRÁ DEVOLVER A PEÇA !')
    </Script>
<?
        }else {//Abrir OC ...
            $status = 0;
        }
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_cliente_contato    = (!empty($_POST[cmb_cliente_contato])) ? "'".$_POST[cmb_cliente_contato]."'" : 'NULL';
        $cmb_representante      = (!empty($_POST[cmb_representante])) ? "'".$_POST[cmb_representante]."'" : 'NULL';

        $sql = "UPDATE `ocs` SET `id_cliente_contato` = $cmb_cliente_contato, `id_representante` = $cmb_representante, `id_funcionario` = '$_SESSION[id_funcionario]', `data_conclusao` = '$data_conclusao', 
                `nf_entrada` = '$_POST[txt_nf_entrada]', `observacao` = '$_POST[txt_observacao]', `data_sys` = '".date('Y-m-d H:i:s')."', `status` = '$status' WHERE `id_oc` = '$id_oc' LIMIT 1 ";
        bancos::sql($sql);
        //Se o usuário pediu para abrir a OC só atualizo o campo status ...
    }else if(isset($_POST['hdd_finalizar_oc']) && $_POST['hdd_finalizar_oc'] == 0) {//Objetos de Formulário - DESABILITADOS ...
        $sql = "UPDATE `ocs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."', `status` = '0' WHERE `id_oc` = '$id_oc' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Exclusão de contatos
if(!empty($_POST['id_cliente_contato'])) {
    $sql = "UPDATE `clientes_contatos` SET ativo = '0' where id_cliente_contato = '$_POST[id_cliente_contato]' LIMIT 1 ";
    bancos::sql($sql);
}

//Aqui traz os dados da OC ...
$sql = "SELECT o.*, c.razaosocial 
        FROM `ocs` o 
        INNER JOIN `clientes` c ON c.id_cliente = o.id_cliente 
        WHERE o.id_oc = '$id_oc' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_cliente     = $campos[0]['id_cliente'];
$id_fornecedor  = $campos[0]['id_fornecedor'];
$data_emissao   = data::datetodata($campos[0]['data_emissao'], '/');

$data_conclusao = ($campos[0]['data_conclusao'] != '0000-00-00') ? data::datetodata($campos[0]['data_conclusao'], '/') : '';

if($campos[0]['status'] == 0) {//Se a OC ainda não foi finalizada, posso estar mexendo em tudo normalmente ...
    $disabled       = '';
    $class          = 'caixadetexto';
    $class_botao    = 'botao';
}else {//Se já estiver finalizada, então travo todos os campos ...
    $disabled       = 'disabled';
    $class          = 'textdisabled';
    $class_botao    = 'textdisabled';
}
?>
<html>
<head>
<title>.:: Alterar OC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Contato ...
    if(!combo('form', 'cmb_cliente_contato', '', 'SELECIONE O CONTATO DO CLIENTE !')) {
        return false
    }
//Representante
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
//Aqui verifico se a Data de Conclusão é menor do que a Data de Emissão ...
    if(document.form.txt_data_conclusao.value != '') {
        var data_emissao 	= document.form.txt_data_emissao.value
        data_emissao 		= data_emissao.substr(6, 4) + data_emissao.substr(3, 2) + data_emissao.substr(0, 2)
        data_emissao 		= eval(data_emissao)

        var data_conclusao 	= document.form.txt_data_conclusao.value
        data_conclusao 		= data_conclusao.substr(6,4) + data_conclusao.substr(3,2) + data_conclusao.substr(0, 2)
        data_conclusao 		= eval(data_conclusao)
        //Comparando as Datas
        if(data_conclusao < data_emissao) {
            alert('DATA DE CONCLUSÃO INVÁLIDA !!! \nDATA DE CONCLUSÃO MENOR QUE A DATA DE EMISSÃO !')
            document.form.txt_data_conclusao.focus()
            document.form.txt_data_conclusao.select()
            return false
        }
    }
//Se a data de conclusao for diferente de vazio e finalizar OC ñ estiver chekado intao...
    if(typeof(document.form.chkt_finalizar_oc) == 'object') {
        if(!document.form.chkt_finalizar_oc.checked && document.form.txt_data_conclusao.value != '') {
            var resposta = confirm('DESEJA FINALIZAR A OC ?')
            if(resposta == true) document.form.chkt_finalizar_oc.checked = true
        }
    }
//Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value   = 1
}

function controle_finalizar_oc() {
    document.form.hdd_finalizar_oc.value = (document.form.chkt_finalizar_oc.checked) ? 1 : 0
//Força o preenchimento da Data de Conclusão se estiver finalizando a OC ...
    if(document.form.chkt_finalizar_oc.checked && (document.form.cmb_cliente_contato.value == '' || document.form.cmb_representante.value == '' || document.form.txt_data_conclusao.value == '')) {
//Desmarco a opção Finalizar OC até que o usuário preencha com uma Data de Conclusão ...
        document.form.chkt_finalizar_oc.checked = false
//Contato ...
        if(!combo('form', 'cmb_cliente_contato', '', 'SELECIONE O CONTATO DO CLIENTE !')) {
            return false
        }
    //Representante
        if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
            return false
        }
//Data de Conclusão ...
        if(!data('form', 'txt_data_conclusao', '4000', 'CONCLUSÃO')) {
            return false
        }
    }
//Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value   = 1
    document.form.submit()
}

function atualizar() {
    //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value       = 1
    document.form.id_cliente_contato.value  = ''
    document.form.submit()
}

function alterar_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        nova_janela('../../classes/cliente/alterar_contatos.php?id_cliente_contato='+document.form.cmb_cliente_contato.value, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Exclusão de Contatos
function excluir_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
            document.form.nao_atualizar.value       = 1
            document.form.id_cliente_contato.value  = document.form.cmb_cliente_contato.value
            document.form.submit()
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = 'itens/itens.php?id_oc=<?=$id_oc;?>'
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<!--Controles de Tela-->
<input type='hidden' name='id_oc' value='<?=$id_oc;?>'>
<input type='hidden' name='passo' onclick="atualizar()">
<input type='hidden' name='id_cliente_contato'>
<input type='hidden' name='hdd_finalizar_oc' value='<?=$campos[0]['status'];?>'>
<input type='hidden' name='controle'>
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar OC
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contato:</b>
        </td>
        <td>
            <select name="cmb_cliente_contato" title="Selecione os Contatos do Cliente" class='<?=$class;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT id_cliente_contato, nome 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente` = '$id_cliente' 
                        AND `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[0]['id_cliente_contato']);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title="Incluir Contato" alt="Incluir Contato" onClick="if(document.form.cmb_cliente_contato.disabled == false) nova_janela('../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title="Alterar Contato" alt="Alterar Contato" onClick="if(document.form.cmb_cliente_contato.disabled == false) alterar_contato()">
            &nbsp;&nbsp; <img src = '../../../imagem/menu/excluir.png' border='0' title="Excluir Contato" alt="Excluir Contato" onClick="if(document.form.cmb_cliente_contato.disabled == false) excluir_contato()">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <select name="cmb_representante" title="Selecione os Representantes do Cliente" class='<?=$class;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT DISTINCT(r.id_representante), r.nome_fantasia 
                        FROM `clientes_vs_representantes` cr
                        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                        WHERE cr.`id_cliente` = '$id_cliente' 
                        AND r.`ativo` = '1' ORDER BY r.nome_fantasia ";
                echo combos::combo($sql, $campos[0]['id_representante']);
            ?>
            </select>
        </td>
    </tr>
<?
    /*Se o funcionário logado for Roberto "Diretor" 62, Dárcio 98 e Netto 147 porque programam, exibo 
    a parte abaixo ...*/
    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
?>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?$checked_status = ($campos[0]['status'] == 1) ? 'checked' : '';?>
            <input type='checkbox' name='chkt_finalizar_oc' id='chkt_finalizar_oc' onclick='controle_finalizar_oc()' class='checkbox' <?=$checked_status;?>>
            <label for='chkt_finalizar_oc' id='chkt_finalizar_oc'><b>FINALIZAR OC</b></label>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            Data de Emissão:
        </td>
        <td>
            <input type='text' name="txt_data_emissao" value="<?=$data_emissao;?>" title="Data de Emissão" size="12" maxlength="10" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Conclusão:
        </td>
        <td>
            <input type='text' name="txt_data_conclusao" value="<?=$data_conclusao;?>" title="Data de Conclusão" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='<?=$class;?>' <?=$disabled;?>>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_conclusao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º NF de Entrada:
        </td>
        <td>
            <input type='text' name="txt_nf_entrada" value="<?=$campos[0]['nf_entrada'];?>" title="Digite a NF de Entrada" size="7" maxlength='11' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='60' rows='4' maxlenght='255' class='<?=$class;?>' <?=$disabled;?>><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_observacao.focus()" class='<?=$class_botao;?>' <?=$disabled;?>>
            <input type='submit' name='cmd_salvar' value='Salvar' title="Salvar" style="color:green" class='<?=$class_botao;?>' <?=$disabled;?>>
        </td>
    </tr>
</table>
</form>
</body>
</html>