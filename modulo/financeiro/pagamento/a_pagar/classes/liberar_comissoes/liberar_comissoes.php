<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/comunicacao.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/depto_pessoal.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/variaveis/intermodular.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>COMISSÃO LIBERADA COM SUCESSO.</font>";

if($_POST['passo'] == 1) {
/************************Variáveis Genéricas************************/
    $data_emissao       = date('Y-m-d');
    $dia                = substr($_POST['txt_data_vencimento'], 0, 2);
    $mes                = substr($_POST['txt_data_vencimento'], 3, 2);
    $ano                = substr($_POST['txt_data_vencimento'], 6, 4);
    $data_vencimento 	= data::datatodate($_POST['txt_data_vencimento'], '-');
    $semana 		= data::numero_semana($dia, $mes, $ano);
    $numero_conta       = 'COMISSÃO - '.$mes.'/'.$ano;
//Nunca a Data de Emissão poderá se maior que a Data de Vencimento, caso isso aconteça eu igualo a Data de Emissão c/ a de Vencimento ...
    if($data_emissao > $data_vencimento) $data_emissao = $data_vencimento;
//Vou utilizar essa variável p/ passar algumas informações por e-mail ...
    $cabecalho_justificativa = '<font color="blue">Comissões Liberadas p/ os seguintes representantes: </font>
                            <br><br>
                            <table border="1" width="750" cellspacing="0" cellpadding="0" align="center">
                                    <tr align="center">
                                            <td><b>Representante</b></td>
                                            <td width="150"><b>Albafer</b></td>
                                            <td width="150"><b>Tool Master</b></td>
                                            <td width="150"><b>Grupo</b></td>
                                    </tr>';
    
    foreach($_POST['chkt_representante'] as $i => $id_representante_indice) {
        $vetor              = explode('|', $id_representante_indice);
        $id_representante   = $vetor[0];
        $indice             = $vetor[1];
        /*************Explicação de alguns campos que são utilizados dentro dos Inserts abaixo**************/
        //Tipo da Moeda = 1, R$ ...
        //Previsão = 0, significa que está conta não é uma estimativa ...
        //Tipo de Pagamento = 3, Cheque ao Portador ...
        //Grupo 40 = Folha de Pagamento Variável - Representantes Autônomos ...
        /***************************************************************************************************/
        if($_POST['hdd_comissao_alba'][$i] != 0) {//Se a Empresa for Albafer ...
            $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_representante`, `id_vale_data`, `id_empresa`, `id_tipo_moeda`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `valor`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$id_representante', '$_POST[cmb_data_holerith]', '1', '".$_POST['hdd_tipo_moeda'][$indice]."' ,'$semana', '0', '$data_emissao', '$data_vencimento', '$data_vencimento', '3', '40', NULL, '100', '$numero_conta', '".$_POST['hdd_comissao_alba'][$i]."', '0', '1') ";
            bancos::sql($sql);
        }
        if($_POST['hdd_comissao_tool'][$i] != 0) {//Se a Empresa for Tool Master ...
            $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_representante`, `id_vale_data`, `id_empresa`, `id_tipo_moeda`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `valor`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$id_representante', '$_POST[cmb_data_holerith]', '2', '".$_POST['hdd_tipo_moeda'][$indice]."' ,'$semana', '0', '$data_emissao', '$data_vencimento', '$data_vencimento', '3', '40', NULL, '100', '$numero_conta', '".$_POST['hdd_comissao_tool'][$i]."', '0', '1') ";
            bancos::sql($sql);
        }
        if($_POST['hdd_comissao_grupo'][$i] != 0) {//Se a Empresa for Grupo ...
            $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_representante`, `id_vale_data`, `id_empresa`, `id_tipo_moeda`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `valor`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$id_representante', '$_POST[cmb_data_holerith]', '4', '".$_POST['hdd_tipo_moeda'][$indice]."' ,'$semana', '0', '$data_emissao', '$data_vencimento', '$data_vencimento', '3', '40', NULL, '100', '$numero_conta', '".$_POST['hdd_comissao_grupo'][$i]."', '0', '1') ";
            bancos::sql($sql);
        }
        //Só irá enviar no e-mail o representante que tiver pelo menos 1 valor diferente de Zero ...
        if($_POST['hdd_comissao_alba'][$i] != 0 || $_POST['hdd_comissao_tool'][$i] != 0 || $_POST['hdd_comissao_grupo'][$i] != 0) {
            //Dados p/ enviar por e-mail ...
            //Busca do nome do Representante p/ passar por e-mail ...
            $sql = "SELECT id_representante, nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            $campos     = bancos::sql($sql);
            $tipo_moeda = ($_POST['hdd_tipo_moeda'][$indice] == 1) ? 'R$ ' : 'U$ ';
            $justificativa.= '<tr>
                                    <td>'.$campos[0]['id_representante'].' - '.$campos[0]['nome_fantasia'].'</td>
                                    <td align="right">'.$tipo_moeda.number_format($_POST['hdd_comissao_alba'][$i], 2, ',', '.').'</td>
                                    <td align="right">'.$tipo_moeda.number_format($_POST['hdd_comissao_tool'][$i], 2, ',', '.').'</td>
                                    <td align="right">'.$tipo_moeda.number_format($_POST['hdd_comissao_grupo'][$i], 2, ',', '.').'</td>
                            </tr>';
        }
    }
    /*Se existir alguma comissão por Empresa que já foi importada, então se acrescenta esse trecho 
    de Justificativa na hora de se enviar por e-mail ...*/
    if(!empty($justificativa)) {
        $justificativa = $cabecalho_justificativa.$justificativa;
        $justificativa.= '</table><br/>';
        $justificativa.= '<font color="blue">As comissões por Empresa cujo o valor = 0, não são importado(s) p/ o Financeiro.</font>
                        <br/><br/>';
/************************E-mail************************/
        /*
        //-Se o Usuário estiver incluindo uma Conta Financeiro, então o Sistema dispara um e-mail informando 
        qual a Conta à Pagar que está sendo incluida ...
        //-Aqui eu trago alguns dados de Conta à Pagar p/ passar por e-mail via parâmetro ...
        //-Aqui eu busco o login de quem está incluindo a Conta à Pagar ...*/
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login 		= bancos::sql($sql);
        $login_liberando 	= $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $justificativa.= '<br/><b>N.º da Conta / Nota: </b>'.$numero_conta.' <br/><b>Login: </b>'.$login_liberando.'<br/><b>Data de Emissão: </b>'.data::datetodata($data_emissao, '/').'<br/><b>Data de Vencimento: </b>'.$_POST['txt_data_vencimento'].'<br/>'.date('d/m/Y H:i:s');
//Aqui eu mando um e-mail informando quem incluiu a Conta Financeiro ...
        $destino = $liberar_comissao;
        $mensagem = $justificativa;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Liberação de Comissão(ões) - Representante(s)', $mensagem);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'liberar_comissoes.php?valor=1'
    </Script>
<?
}else {
/***********************************Controle com a Data de Holerith***********************************/
    $data_atual_mais_10 = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 10), '-');
    if(empty($_POST['cmb_data_holerith'])) {//Na primeira vez em que carrega a Tela ...
        //Aqui eu busco a Última Data de Holerith anterior a Data atual ...
        $sql = "SELECT id_vale_data 
                FROM `vales_datas` 
                WHERE `data` <= '$data_atual_mais_10' ORDER BY data DESC LIMIT 1 ";
        $campos_data_holerith 	= bancos::sql($sql);
        $id_vale_data           = $campos_data_holerith[0]['id_vale_data'];
    }else {//Nas demais vezes, busca a opção carregada da Combo ...
        $id_vale_data           = $_POST['cmb_data_holerith'];
    }
    
    //Busca da Data de Holerith ...
    $sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y') AS data_holerith 
            FROM `vales_datas` 
            WHERE `id_vale_data` = '$id_vale_data' LIMIT 1 ";
    $campos_data_holerith = bancos::sql($sql);
    //Através da Data de Holerith eu busco as Datas do Período da Folha ...
    $datas_folha = depto_pessoal::periodo_folha($campos_data_holerith[0]['data_holerith']);
    
    $data_inicial_folha = $datas_folha['data_inicial_folha'];
    $data_final_folha   = $datas_folha['data_final_folha'];
    /*O sistema irá sugerir a Data de Pagamento como sendo dia "25" do mês e ano 
    da Data de Holerith selecioanda pelo usuário ...*/
    $data_pagamento     = '25/'.substr($campos_data_holerith[0]['data_holerith'], 3, 7);
/*****************************************************************************************************/
?>
<html>
<head>
<title>.:: Liberar Comissões - Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Data de Vencimento ...
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
    document.form.passo.value = 1
    //Mudo o valor do Hidden p/ 1, p/ não recarregar a Tela de Baixo ...
    document.form.nao_atualizar.value = 1
}

function validar_email() {
    var id_representantes_emails = ''
    for(i = 0; i < document.form.elements['chkt_representante_email[]'].length; i++) {
        if(document.getElementById('chkt_representante_email'+i).checked) {
            id_representantes_emails = id_representantes_emails + document.getElementById('chkt_representante_email'+i).value + ', '
        }
    }
    if(id_representantes_emails == '') {//Significa que não há nenhum e-mail selecionado ...
        alert('SELECIONE UM REPRESENTANTE PARA ENVIAR E-MAIL !')
        document.getElementById('chkt_representante_email0').focus()
        return false
    }else {
        id_representantes_emails = id_representantes_emails.substr(0, id_representantes_emails.length - 2)
        nova_janela('enviar_email_comissao.php?cmb_representante='+id_representantes_emails+'&data_inicial=<?=data::datatodate($data_inicial_folha, '-');?>&data_final=<?=data::datatodate($data_final_folha, '-');?>', 'CONSULTAR', 'F')
    }
}

function selecionar_representante(indice) {
    //Controle com os Checkbox de Representantes para liberar Comissão ...
    if(document.getElementById('chkt_representante'+indice) != null) {
        if(document.getElementById('chkt_representante'+indice).checked) {//Se checkado, eu desmarco ...
            document.getElementById('chkt_representante'+indice).checked    = false
            document.getElementById('hdd_comissao_alba'+indice).disabled    = true
            document.getElementById('hdd_comissao_tool'+indice).disabled    = true
            document.getElementById('hdd_comissao_grupo'+indice).disabled   = true
            document.getElementById('hdd_tipo_moeda'+indice).disabled       = true
        }else {//Se desmarcado eu checko ...
            document.getElementById('chkt_representante'+indice).checked    = true
            document.getElementById('hdd_comissao_alba'+indice).disabled    = false
            document.getElementById('hdd_comissao_tool'+indice).disabled    = false
            document.getElementById('hdd_comissao_grupo'+indice).disabled   = false
            document.getElementById('hdd_tipo_moeda'+indice).disabled       = false
        }
    }
}

function enviar_email_representante(indice) {
    //Controle com os Checkbox para Enviar E-mail para os Representantes ...
    if(document.getElementById('chkt_representante_email'+indice).checked) {
        document.getElementById('chkt_representante_email'+indice).checked = false
    }else {
        document.getElementById('chkt_representante_email'+indice).checked = true
    }
}

function selecionar_todos_representantes() {
    var elementos 	= document.form.elements
    var checked 	= (document.form.chkt_todos_representantes.checked == true) ? true : false
    var disabled 	= (document.form.chkt_todos_representantes.checked == true) ? false : true
    var linhas 		= (typeof(elementos['chkt_representante[]'][0]) == 'undefined') ? 1 : (elementos['chkt_representante[]'].length)

    if(linhas == 1) {
        elementos['chkt_representante[]'].checked = checked
    }else {
        for(i = 0; i < linhas; i++) {
            elementos['chkt_representante[]'][i].checked                = checked
            document.getElementById('hdd_comissao_alba'+i).disabled     = disabled
            document.getElementById('hdd_comissao_tool'+i).disabled     = disabled
            document.getElementById('hdd_comissao_grupo'+i).disabled    = disabled
            document.getElementById('hdd_tipo_moeda'+i).disabled        = disabled
        }
    }
}

function enviar_email_todos_representantes() {
    var elementos 	= document.form.elements
    var checked 	= (document.form.chkt_email_todos_representantes.checked == true) ? true : false
    var linhas 		= (typeof(elementos['chkt_representante_email[]'][0]) == 'undefined') ? 1 : (elementos['chkt_representante_email[]'].length)
    for(i = 0; i < linhas; i++) elementos['chkt_representante_email[]'][i].checked = checked
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(opener.parent.itens) == 'object') opener.parent.itens.document.location = opener.parent.itens.document.location.href
    }
}
</Script>
</head>
<body onload='document.form.txt_data_vencimento.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<!--*******************Hidden que serve de controle p/ não recarregar a Tela de Itens*******************-->
<input type='hidden' name='nao_atualizar' value='0'>
<!--******************************************Controle de Tela******************************************-->
<input type='hidden' name='passo'>
<!--****************************************************************************************************-->
<table width='96%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' onchange='document.form.submit()' class='combo'>
            <?
                    $ano_anterior = date('Y') - 5;
                    //Só lista nessa Combo as Datas de Holeriths do último ano < que a Data de Atual ...
                    $sql = "SELECT `id_vale_data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                            FROM `vales_datas` 
                            WHERE `data` BETWEEN '$ano_anterior-".date('m-d')."' AND '$data_atual_mais_10' ORDER BY `data` ";
                    echo combos::combo($sql, $id_vale_data);
            ?>
            </select>
        </td>
        <td colspan='7'>
            Liberar Comissões - Representante(s) - Data de Vencimento
            <input type='text' name='txt_data_vencimento' value='<?=$data_pagamento;?>' title='Digite a Data de Vencimento' size='10' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cod Rep
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            E-mail
        </td>
        <td>
            ALBAFER
        </td>
        <td>
            TOOL MASTER
        </td>
        <td>
            GRUPO
        </td>
        <td>
            Data do Sistema
        </td>
        <td>
            <input type='checkbox' name='chkt_todos_representantes' title='Selecionar Todos os Representantes' onClick='selecionar_todos_representantes()' id='todos_representantes' class='checkbox'>
        </td>
        <td>
            <label for="email_todos_representantes">E-mail</label>
            <input type='checkbox' name='chkt_email_todos_representantes' title='E-mail para Todos os Representantes' onClick="enviar_email_todos_representantes()" id="email_todos_representantes" class="checkbox">
        </td>
    </tr>
<?
    $indice = 0;//Essa variável só é somada quando o Somatório das Comissões dos Representantes <> '0' ...

    //Aqui só lista os Representantes q são autônomos ...
    $sql = "SELECT id_representante, id_pais, nome_fantasia, pgto_comissao_grupo, email 
            FROM `representantes` 
            WHERE `ativo` = '1' 
            AND `id_representante` NOT IN (SELECT id_representante 
            FROM `representantes_vs_funcionarios`) 
            ORDER BY nome_fantasia ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for ($i = 0;  $i < $linhas; $i++) {
//Verifico o País, p/ saber qual o Tipo de Moeda ...
        $tipo_moeda 	= ($campos[$i]['id_pais'] == 31) ? 'R$ ' : 'U$ ';
        $id_tipo_moeda 	= ($campos[$i]['id_pais'] == 31) ? 1 : 2;
        /***************Busca da comissão do Representante na Data de Holerith especificada**************/
        $sql = "SELECT comissao_alba, comissao_tool, comissao_grupo, data_sys_comissao 
                FROM `representantes_vs_comissoes` 
                WHERE `id_representante` = '".$campos[$i]['id_representante']."' 
                AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
        $campos_comissao = bancos::sql($sql);
        if(count($campos_comissao) == 1) {//Existem comissões p/ o Representante na Data de Holerith específicada ...
            /*Quando o representante tiver com essa Marcação, então significa que o representante só vai receber pela
            Empresa do Grupo, talvez vez esse só receba PF, devido não ter registro e tal ...*/
            if($campos[$i]['pgto_comissao_grupo'] == 'S') {
                $comissao_albafer       = 0;
                $comissao_tool          = 0;
                $comissao_grupo         = $campos_comissao[0]['comissao_alba'] + $campos_comissao[0]['comissao_tool'] + $campos_comissao[0]['comissao_grupo'];
                $informacao             = ' <font color="red"><b>(Pgto pelo Grupo)</b></font>';
            }else {
                $comissao_albafer       = $campos_comissao[0]['comissao_alba'];
                $comissao_tool          = $campos_comissao[0]['comissao_tool'];
                $comissao_grupo         = $campos_comissao[0]['comissao_grupo'];
                $informacao             = '';
            }
            $data_sys   = data::datetodata(substr($campos_comissao[0]['data_sys_comissao'], 0, 10) , '/').' - '.substr($campos_comissao[0]['data_sys_comissao'], 11, 8);
        }else {//Não existe Comissão p/ o Representante na data de Holerith específicada ...
            $comissao_albafer   = 0;
            $comissao_tool      = 0;
            $comissao_grupo     = 0;
            $informacao         = '';
        }
        /************************************************************************************************/
        //Se existir(em) valor(es) de Comissão p/ Importar ...
        if($comissao_albafer != 0 || $comissao_tool != 0 || $comissao_grupo != 0) {
            $url = '../../../../../vendas/representante/alterar2.php?passo=1&pop_up=1&id_representante='.$campos[$i]['id_representante'];
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_representante'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome_fantasia'].$informacao;?>
            &nbsp;
            <img src='../../../../../../imagem/imprimir_pdf.png' height='18' width='16' onclick="nova_janela('../../../../../faturamento/relatorio/comissoes_novo/relatorio_pdf/relatorio.php?txt_data_inicial=<?=$data_inicial_folha;?>&txt_data_final=<?=$data_final_folha;?>&cmb_representante=<?=$campos[$i]['id_representante'];?>', 'CONSULTAR', 'F')" title='Relatório de Comissão' style='cursor:pointer' border='1'>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='right'>
            <?=$tipo_moeda.number_format($comissao_albafer, 2, ',', '.');?>
            <input type='hidden' name='hdd_comissao_alba[]' id='hdd_comissao_alba<?=$indice;?>' value='<?=$comissao_albafer;?>' disabled>
        </td>
        <td align='right'>
            <?=$tipo_moeda.number_format($comissao_tool, 2, ',', '.');?>
            <input type='hidden' name='hdd_comissao_tool[]' id='hdd_comissao_tool<?=$indice;?>' value='<?=$comissao_tool;?>' disabled>
        </td>
        <td align='right'>
            <?=$tipo_moeda.number_format($comissao_grupo, 2, ',', '.');?>
            <input type='hidden' name='hdd_comissao_grupo[]' id='hdd_comissao_grupo<?=$indice;?>' value='<?=$comissao_grupo;?>' disabled>
        </td>
        <td align='center'>
            <?=$data_sys;?>
            <input type='hidden' name='hdd_tipo_moeda[]' id='hdd_tipo_moeda<?=$indice;?>' value="<?=$id_tipo_moeda;?>">
        </td>
        <td onclick="selecionar_representante('<?=$indice;?>')" align='center'>
        <?
            //Verifico se a Comissão desse Representante, nessa data de Débito já foram importados anteriormente ...
            $sql = "SELECT id_conta_apagar 
                    FROM `contas_apagares` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' 
                    AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
            $campos_importado = bancos::sql($sql);
            if(count($campos_importado) == 1) {//Se já tiver sido liberada anteriormente, então não mostra o checkbox
                echo '<b>Importado</b>';
        ?>
            <input type='hidden' name='hdd_representante[]' value="<?=$campos[$i]['id_representante'].'|'.$indice;?>">
        <?
            }else {//Ainda não foi então mostra o checkbox ...
                //Aqui eu guardo o índice da linha pq ao submeter no Foreach, o sistema se perde com os índices dos objetos ...
        ?>
            <input type='checkbox' name='chkt_representante[]' id="chkt_representante<?=$indice;?>" value="<?=$campos[$i]['id_representante'].'|'.$indice;?>" onclick="selecionar_representante('<?=$indice;?>')" class="checkbox">
        <?
            }
        ?>
        </td>
        <td onclick="enviar_email_representante('<?=$indice;?>')" align='center'>
        <?
            //Só é possível enviar e-mail p/ os Representantes daqui do Brasil onde a soma das 3 comissões sejam > 0 ...
            if($campos[$i]['id_pais'] == 31 && ($comissao_albafer + $comissao_tool + $comissao_grupo) > 0) {
        ?>
        <input type='checkbox' name='chkt_representante_email[]' id='chkt_representante_email<?=$indice;?>' value='<?=$campos[$i]['id_representante'];?>' onclick="enviar_email_representante('<?=$indice;?>')" class='checkbox'>
        <?
            }else {
                echo '-';
        ?>
        <input type='hidden' name='chkt_representante_email[]' id='chkt_representante_email<?=$indice;?>'>
        <?
            }
        ?>
        </td>
    </tr>
<?
            $indice++;
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="document.form.reset();document.form.txt_data_vencimento.focus()" style="color:#ff9900" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_enviar_email' value='Enviar E-mail' title='Enviar E-mail' onclick="return validar_email()" style="color:purple" class='botao'>
        </td>
    </tr>
</table>
</form>
<br>
<div class='confirmacao' align='center'>
    Total de Registro(s): <?=$indice;?>
</div>
</body>
</html>
<?}?>