<?
require('../../../../lib/segurancas.php');
//Tenho esse controle porque em alguns momentos essa Tela � aberta como sendo Pop-UP ...
if(empty($pop_up)) require('../../../../lib/menu/menu.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
require('../../../../lib/variaveis/intermodular.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/financeiro/cadastro/credito_cliente/credito_cliente.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CR�DITO ALTERADO COM SUCESSO.</font>";

$data_hoje          = date('Y-m-d');
$data_ocorrencia    = date('Y-m-d H:i:s');

if($_POST['passo'] == 1) {
//Se o Cr�dito, Limite de Cr�dito ou Pref. de Pagto, ent�o precisa ser modificada a Justificativa ...
    if(!empty($_POST['hdd_justificativa'])) {
/*********************************Controle com os Checkbox*********************************/
        if(empty($_POST['chkt_lembrete_credito'])) $_POST['chkt_lembrete_credito'] = 'N';
//1)
/************************Busca de Dados************************/
        $mensagem_texto = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
//Aqui eu trago alguns dados do Cliente p/ passar por e-mail via par�metro ...
        $sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente, `credito`, `limite_credito`, `forma_pagamento` 
                FROM `clientes` 
                WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
        $campos_clientes = bancos::sql($sql);
//Aqui eu verifico se o Cliente possui pelo menos 1 Contato ...
        $sql = "SELECT `id_cliente_contato` 
                FROM `clientes_contatos` 
                WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $id_cliente_contato = $campos[0]['id_cliente_contato'];
        }else {//N�o existe nenhum contato, sendo assim eu cadastro um ...
            $sql = "INSERT INTO `clientes_contatos` (`id_cliente_contato`, `id_cliente`, `id_departamento`, `nome`) VALUES (NULL, '$_POST[id_cliente]', '4', '".$campos_clientes[0]['cliente']."') ";
            bancos::sql($sql);
            $id_cliente_contato = bancos::id_registro();
        }
//Dados p/ enviar por e-mail - Controle com as Mensagens de Altera��o ...
        $dados_alterados = '';
        if($campos_clientes[0]['credito'] != $_POST['cmb_credito']) $dados_alterados.= '<br><b>Cr�dito Alterado de: </b>'.$campos_clientes[0]['credito'].' <b>para </b>'.$cmb_credito;
        if($campos_clientes[0]['limite_credito'] != $_POST['txt_limite_credito']) $dados_alterados.= '<br><b>Limite de Cr�dito Alterado de: </b>'.number_format($campos_clientes[0]['limite_credito'], 2, ',', '.').' <b>para </b>'.number_format($txt_limite_credito, 2, ',', '.');

        $observacao_follow_up   = $mensagem_texto.' - '.$dados_alterados.' - <b>Justificativa: </b>'.$_POST['hdd_justificativa'];
//Registrando Follow-UP(s) ...
        $id_representante       = genericas::buscar_id_representante($id_cliente_contato);
        
//Registrando Follow-UP(s) ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_cliente]', '$id_cliente_contato', '$id_representante', '$_SESSION[id_funcionario]', '4', '$observacao_follow_up', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);

        if($_SESSION['id_login'] != 29) {//S� n�o ir� enviar esse e-mail quando for a pr�pria da Dona Sandra que estiver fazendo essa a��o ...
//2)
/************************E-mail************************/
//Se o Usu�rio estiver alterando o Cr�dito do Cliente, o Sys dispara um e-mail informando qual o Cliente que est� sendo alterado ...
//Aqui eu busco o login de quem est� alterando o Cr�dito ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $mensagem_texto.= '<br><br>O Depto. Financeiro acabou de mudar o cr�dito do Cliente abaixo: ';
            $mensagem_texto.= '<br><b>Cliente: </b>'.$campos_clientes[0]['cliente'].' <br><b>Login: </b>'.$campos_login[0]['login'];
            $mensagem_texto.= $dados_alterados.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$_POST['hdd_justificativa'].'<br>'.$PHP_SELF;
//Os e-mails est�o especificados dentro da biblioteca intermodular na pasta vari�veis ...
            $destino = $credito_cliente;
//Aqui eu busco todos os representantes do Cliente ...
            $sql = "SELECT DISTINCT(r.`id_representante`), r.`nome_fantasia` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '$id_cliente' ";
            $campos_representantes = bancos::sql($sql);
            $linhas_representantes = count($campos_representantes);
            for($i = 0; $i < $linhas_representantes; $i++) {
                //Se o Representante for Direto n�o precisa pq o e-mail j� vai para a Dona Sandra e para o Wilson ...
                if($campos_representantes[$i]['id_representante'] != 1) {
//Aqui eu verifico se o Representante � Funcion�rio ...
                    $sql = "SELECT f.`email_externo` 
                            FROM `representantes_vs_funcionarios` rf 
                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                            WHERE rf.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
                    $campos_funcionario = bancos::sql($sql);
                    if(count($campos_funcionario) == 1) {//Se for funcion�rio ...
                        $vendedores.= $campos_funcionario[0]['email_externo'].', ';
                    }else {//Significa que � aut�nomo, sendo assim eu busco o Supervisor do Representate p/ passar e-mail ...
                        $sql = "SELECT r.`id_representante`, r.`nome_fantasia` 
                                FROM `representantes_vs_supervisores` rs 
                                INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                                WHERE rs.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
                        $campos_supervisores = bancos::sql($sql);
                        //Tratamento com alguns e-mails ...
                        if($campos_supervisores[0]['id_representante'] == 42) {//Arnaldo Nogueira ...
                            $vendedores.= 'nogueira@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 69) {//Carlos Junior ...
                            $vendedores.= 'carlos.junior@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 6) {//Edson Gon�alves ...
                            $vendedores.= 'edson.goncalves@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 93) {//Izael Pedreira ...
                            $vendedores.= 'noronha@grupoalbafer.com.br'.', ';
                        }else {
                            $vendedores.= strtolower($campos_supervisores[0]['nome_fantasia']).'@grupoalbafer.com.br, ';
                        }
                    }
                }
            }
            $vendedores = substr($vendedores, 0, strlen($vendedores) - 2);
            $copia      = $vendedores;//Aqui eu envio e-mail para os Vendedores do Cliente estarem a par ...
            $assunto 	= 'Mudan�a(s) de Cr�dito do Cliente '.$campos_clientes[0]['cliente'];
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, $copia, $assunto, $mensagem_texto);
        }
    }
//3)
/************************Altera��o************************/
    $sql = "UPDATE `clientes` SET `id_funcionario_mudou_credito` = '$_SESSION[id_funcionario]', `credito` = '$_POST[cmb_credito]', `limite_credito` = '$_POST[txt_limite_credito]', `credito_data` = '$data_ocorrencia', `credito_observacao` = '$_POST[txt_credito_observacao]', `forma_pagamento` = '$_POST[cmb_forma_pagamento]', `lembrete_credito` = '$_POST[chkt_lembrete_credito]' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
    
    if($_POST['pop_up'] == 1) {
?>
    <Script Language = 'JavaScript'>
        //Aqui significa que essa Tela � um Pop-Up q est� sendo puxada de dentro do Pagar Contas � Receber ...
        if(opener != null) {
            opener.parent.itens.document.location = opener.parent.itens.document.location.href
        }else {//Foi puxada de alguma outra Tela qualquer ...
            parent.document.location = parent.document.location.href
        }
    </Script>
<?
    }
}

//Busca dados do Cliente ...
$sql = "SELECT `id_funcionario`, `id_funcionario_mudou_credito`, `nomefantasia`, `razaosocial`, `credito`, `limite_credito`, `credito_observacao`, 
        `forma_pagamento`, `lembrete_credito`, `email_financeiro`, `data_fundacao`, 
        CONCAT(DATE_FORMAT(SUBSTRING(`credito_data`, 1, 10), '%d/%m/%Y'), ' �S ', SUBSTRING(`credito_data`, 12, 8)) AS data_hora_credito 
        FROM `clientes` 
        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
$campos_clientes = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Cr�dito do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Cr�dito
    if(!combo('form', 'cmb_credito', '', 'SELECIONE O CR�DITO !')) {
        return false
    }
    if(document.form.cmb_credito.value == 'B') {//Se o Cr�dito escolhido = 'B', for�o preencher o Limite de Cr�dito ...
//Limite Cr�dito
        if(!texto('form', 'txt_limite_credito', '1', '1234567890,.', 'LIMITE CR�DITO', '2')) {
            return false
        }
        var limite_credito = eval(strtofloat('<?=intval(genericas::variavel(44));?>'))
        if(document.form.txt_limite_credito.value == '0,00') {
            alert('LIMITE DE CR�DITO INV�LIDO !')
            document.form.txt_limite_credito.focus()
            document.form.txt_limite_credito.select()
            return false
        }
        var limite_credito_digitado = eval(strtofloat(document.form.txt_limite_credito.value))
        if(limite_credito_digitado > limite_credito) {
            var limite_credito_alert = '<?=number_format(genericas::variavel(44), 2, ',', '.');?>'
            var resposta = confirm('O CR�DITO DIGITADO FOI DE R$ '+document.form.txt_limite_credito.value+' E EST� ACIMA DE R$ '+limite_credito_alert+' !\nDESEJA MANTER ESSE VALOR ?')
            if(resposta == false) return false
        }
    }
/******************Controle com algum Dado q foi alterado pelo usu�rio******************/
//Aqui eu verifico se foi alterado algum desses valores carregados diretamente do BD pelo Usu�rio ...
    var credito_bd              = '<?=$campos_clientes[0]['credito'];?>'
    var limite_credito_bd       = '<?=number_format($campos_clientes[0]['limite_credito'], 2, ',', '.');?>'
    var forma_pagamento_bd      = '<?=$campos_clientes[0]['forma_pagamento'];?>'

    var credito_dg              = document.form.cmb_credito.value//Selecionado pelo usu�rio ...
    var limite_credito_dg       = document.form.txt_limite_credito.value//Digitado pelo usu�rio ...
    var forma_pagamento_dg      = document.form.cmb_forma_pagamento.value//Digitado pelo usu�rio ...
//Verifico se o Cr�dito, Limite de Cr�dito ou Pref. de Pagto foi alterado pelo usu�rio ...
    if((credito_bd != credito_dg) || (credito_dg != 'A' && limite_credito_bd != limite_credito_dg) || (forma_pagamento_bd != forma_pagamento_dg)) {
//Verifico se a Data de Vencimento foi alterada pelo usu�rio ...
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ ALTERA��O DE DADO(S): ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INV�LIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ ALTERA��O DE DADO(S) !')
            return false
        }
    }
    //Trava o bot�o p/ o usu�rio n�o ficar submetendo mais de uma vez e dar erro ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
/*********************************************************************/
    limpeza_moeda('form', 'txt_limite_credito, ')
    document.form.passo.value = 1//Aqui � um controle para submeter a Tela ...
    document.form.nao_atualizar.value = 1
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up ...
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(opener.parent.itens == 'object')) opener.parent.itens.location = '../../recebimento/a_receber/classes/itens.php<?=$parametro;?>'
    }
}

function chamar_follow_up() {
    window.close()
    nova_janela('../../../classes/follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=11', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}

function controlar_periodo() {
    if(document.getElementById('qtde_quitacao').style.display == 'block') {//Quando habilitar o Iframe
        document.getElementById('qtde_quitacao').src = '../../../classes/cliente/qtde_quitacao.php?id_cliente=<?=$id_cliente;?>&cmb_periodo='+document.form.cmb_periodo.value
    }
}
</Script>
</head>
<?
//Aqui significa que essa Tela � um Pop-Up q est� sendo puxada de dentro do Pagar Contas � Receber ...
    if($pop_up == 1) {//Se n�o passar nenhum par�metro, ent�o sempre que fechar essa tela tem que atualizar a Tela Abaixo ...
        if(empty($onunload)) $onunload = 'onunload="atualizar_abaixo()"';
    }
?>
<body onload='document.form.txt_credito_observacao.focus()' <?=$onunload;?>>
<form name='form' method='post' action='' onsubmit="return validar()">
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_justificativa'>
<input type='hidden' name='passo'>
<!--**********************************************-->
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cr�dito do Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            Raz&atilde;o Social / C�digo do Cliente (Lotus):
        </td>
        <td width='50%'>
            Nome Fantasia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color="darkblue" size="-1">
                <b><?=$campos_clientes[0]['razaosocial'];?></b>
            </font>
        </td>
        <td>
            <font color="darkblue" size="-1">
                <b><?=$campos_clientes[0]['nomefantasia'];?></b>
            </font>
            <!--Esse par�metro nao_exibir_menu=1 � para que o Sistema n�o exiba o Menu que fica no Topo da Tela ...-->
            <a href="javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&nao_exibir_menu=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Cliente' class='link'>
                <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Cliente' alt='Alterar Cliente'>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b>Cr�dito:</b>
            </font>
        </td>
        <td>
            Limite de Cr�dito:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_credito' title='Selecione o Cr�dito' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $limite_credito = number_format($campos_clientes[0]['limite_credito'], 2, ',', '.');//Default ...
                    if($campos_clientes[0]['credito'] == 'B') {
                        $selectedb  = 'selected';
                        $class      = 'caixadetexto';
                    }else if($campos_clientes[0]['credito'] == 'C') {
                        $selectedc  = 'selected';
                        $class      = 'caixadetexto';
                    }else if($campos_clientes[0]['credito'] == 'D') {
                        $option_d   = '<option value="D" selected>D</option>';
                        $class      = 'caixadetexto';
                    }
                ?>
                <option value='B' <?=$selectedb;?>>B</option>
                <option value='C' <?=$selectedc;?>>C</option>
                <?=$option_d;?>
            </select>
        </td>
        <td>
            <input type='text' name='txt_limite_credito' value='<?=$limite_credito;?>' title='Digite o Limite de Cr�dito' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="20" class="<?=$class;?>" <?=$disabled;?>>
            <?$checked = ($campos_clientes[0]['lembrete_credito'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_lembrete_credito' value='S' id='lembrete_credito' class='checkbox' <?=$checked;?>>
            <label for='lembrete_credito'><b>LEMBRETE DE CR�DITO</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Forma de Pagamento:
        </td>
        <td>
            E-Mail Financeiro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_forma_pagamento' title='Selecione a Forma de Pagamento' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_forma_pagamento  = array_sistema::forma_pagamento();
                    foreach($vetor_forma_pagamento as $indice => $rotulo) {
                        $selected = (!empty($campos_clientes[0]['forma_pagamento']) && $campos_clientes[0]['forma_pagamento'] == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$rotulo."</option>";
                    }
                ?>
            </select>
        </td>
        <td>
            <a href='mailto:<?=$campos_clientes[0]['email_financeiro'];?>' title='Enviar E-mail Financeiro' class='link'>
                <?=$campos_clientes[0]['email_financeiro'];?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='green'>
                <b>�LTIMO CR�DITO ALTERADO POR: </b>
            </font>
            <?
                if($campos_clientes[0]['id_funcionario_mudou_credito'] > 0) {
                    $sql = "SELECT `nome` 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '".$campos_clientes[0]['id_funcionario_mudou_credito']."' LIMIT 1 ";
                    $campos_funcionario = bancos::sql($sql);
                    echo '<b> '.strtoupper($campos_funcionario[0]['nome']).'</b> EM <b>'.$campos_clientes[0]['data_hora_credito'].'</b>';
                }else {
                    echo '<b>ERP (AUTOM�TICO)</b> EM <b> '.$campos_clientes[0]['data_hora_credito'].'</b>';
                }
            ?>
        </td>
        <td>
            Data de Funda��o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observa��o de Cr�dito:</b>
        </td>
        <td>
        <?
            if($campos_clientes[0]['data_fundacao'] != '0000-00-00') echo data::datetodata($campos_clientes[0]['data_fundacao'], '/');
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_credito_observacao' rows='8' cols='125' maxlength='1000' class='caixadetexto'><?=$campos_clientes[0]['credito_observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
//Aqui significa que essa Tela � uma Tela Normal que est� acessando acessada do Menu Alterar Cr�d do Cliente
            if($pop_up != 1) {
?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'credito_cliente.php<?=$parametro;?>'" class='botao'>
<?
            }
?>
                <input type='button' name='cmd_redefinir' value='Redefinir' onclick="redefinir('document.form','REDEFINIR');document.form.cmb_credito.focus()" style='color:#ff9900' class='botao'>
                <input type='submit' name="cmd_salvar" value='Salvar' title='Salvar' style='color:green' class='botao'>
<?
//Aqui significa que essa Tela � um Pop-Up q est� sendo puxada de dentro do Pagar Contas � Receber
            if($pop_up == 1) {
?>
                <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
<?
            }
?>
                <input type='button' name='cmd_follow_up_cliente' value='Follow-Up do Cliente' title='Follow-Up do Cliente' onclick='chamar_follow_up()' class='botao'>
        </td>
    </tr>
</table>
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' onClick="showHide('qtde_quitacao'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>&nbsp;Quita��o(�es) nos �ltimos 
                <select name='cmb_periodo' onclick="document.getElementById('qtde_quitacao').style.display = 'none'" onchange="controlar_periodo();document.getElementById('qtde_quitacao').style.display = 'none'" class='combo'>
                    <option value="6">6 meses</option>
                    <option value="12">1 ano</option>
                    <option value="24">2 anos</option>
                    <option value="36">3 anos</option>
                    <option value="48">4 anos</option>
                    <option value="60">5 anos</option>
                </select>
            </font>
            <span id='statusqtde_quitacao'>&nbsp;</span>
            <span id='statusqtde_quitacao'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Eu passo a origem por par�metro tamb�m para n�o dar erro de URL na parte de detalhes da conta e de cheque-->
            <iframe src = "../../../classes/cliente/qtde_quitacao.php?id_cliente=<?=$id_cliente;?>" name="qtde_quitacao" id="qtde_quitacao" marginwidth="0" marginheight="0" style="display: none;" frameborder='0' height='250' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
/************************Visualiza��o das Contas � Receber************************/
    //Visualizando as Contas � Receber
    $retorno    = financeiros::contas_em_aberto($id_cliente, 1, '', 2);
    $linhas     = count($retorno['id_contas']);
    if($linhas > 0) {
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;D�bito(s) � Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por par�metro porque utilizo dentro da Fun��o de Receber-->
            <iframe src = '../../../classes/cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
/*********************************************************************************/
?>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* S� quando o Cr�dito � B que o Sistema analisa o campo limite de Cr�dito com as Pend�ncias de Pagamento 
do Cliente junto ao valor comprado ...
</pre>