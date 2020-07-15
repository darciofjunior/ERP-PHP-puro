<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/mda.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>GRUPO ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>GRUPO J¡ EXISTENTE.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.*, ccp.`conta_caixa` 
                    FROM `grupos` g 
                    INNER JOIN `contas_caixas_pagares` ccp ON ccp.`id_conta_caixa_pagar` = g.`id_conta_caixa_pagar` 
                    WHERE g.`nome` LIKE '%$txt_consultar%' 
                    AND g.`ativo` = '1' ORDER BY g.`nome` ";
        break;
        default:
            $sql = "SELECT g.*, ccp.`conta_caixa` 
                    FROM `grupos` g 
                    INNER JOIN `contas_caixas_pagares` ccp ON ccp.`id_conta_caixa_pagar` = g.`id_conta_caixa_pagar` 
                    WHERE g.`ativo` = '1' ORDER BY g.nome ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Grupo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/datatable.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
$(document).ready(function() {
    $('#example').dataTable( {
        'scrollY': '60%',
    });
});
</Script>
</head>
<body class='dt-example'>
<div class='container'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<table width='90%' id='example' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <thead>
        <tr class='linhacabecalho' align='center'>
            <th colspan='4'>
                Alterar Grupo(s)
            </th>
        </tr>
        <tr class='linhadestaque' align='center'>
            <th>
                ReferÍncia
            </th>
            <th>
                Grupo
            </th>
            <th>
                Conta Caixa
            </th>
            <th>
                Tipo de Custo
            </th>
        </tr>
    </thead>
    <tbody>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_grupo='.$campos[$i]['id_grupo'];
?>
        <tr class='linhanormal'>
            <td>
                <a href="<?=$url?>">
                    <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    &nbsp;
                    <?=$campos[$i]['referencia'];?>
                </a>
            </td>
            <td align='left'>
                <?=$campos[$i]['nome'];?>
            </td>
            <td align='left'>
                <?=$campos[$i]['conta_caixa'];?>
            </td>
            <td>
            <?
                if($campos[$i]['tipo_custo'] == 'V') {
                    echo 'Vari·vel';
                }else if($campos[$i]['tipo_custo'] == 'P') {
                    echo 'Processo';
                }else if($campos[$i]['tipo_custo'] == 'F') {
                    echo 'Fixo';
                }
            ?>
            </td>
        </tr>
<?
        }
?>
    </tbody>
    <tr align='center'>
        <td class='linhacabecalho' colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
</div>
</body>
</html>
<?
    }
}elseif($passo == 2) {
    //Trago dados do "id_grupo" passado por par‚metro ...
    $sql = "SELECT * 
            FROM `grupos` 
            WHERE `id_grupo` = '$_GET[id_grupo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Grupo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Conta Caixa ‡ Pagar
    if(!combo('form', 'cmb_conta_caixa_pagar', '', 'SELECIONE UMA CONTA CAIXA ¿ PAGAR !')) {
        return false
    }
//ReferÍncia
    if(document.form.txt_referencia.value != '') {
        if(!texto('form', 'txt_referencia', '3', "1234567890QWERTYUIOP«LKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuiklopÁ'", 'REFER NCIA', '1')) {
            return false
        }
    }
//Grupo
    if(!texto('form', 'txt_grupo', '1', "abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ '1234567890", 'GRUPO', '2')) {
        return false
    }
//Tipo de Custo
    if(!combo('form', 'cmb_tipo_custo', '', 'SELECIONE O TIPO DE CUSTO!')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()' enctype='multipart/form-data'>
<input type='hidden' name='id_grupo' value='<?=$id_grupo;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Grupo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Caixa ‡ Pagar:</b>
        </td>
        <td>
            <select name='cmb_conta_caixa_pagar' title="Selecione uma Conta Caixa ‡ Pagar" class='combo'>
            <?
                $sql = "SELECT `id_conta_caixa_pagar`, `conta_caixa` 
                        FROM `contas_caixas_pagares` 
                        WHERE `ativo` = '1' ORDER BY `conta_caixa` ";
                echo combos::combo($sql, $campos[0]['id_conta_caixa_pagar']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ReferÍncia:
        </td>
        <td>
            <input type='text' name='txt_referencia' value='<?=$campos[0]['referencia'];?>' title='Digite a ReferÍncia' size='8' maxlength='7' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo' value='<?=$campos[0]['nome'];?>' title='Digite o Grupo' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Custo:</b>
        </td>
        <td>
            <select name='cmb_tipo_custo' title='Selecione o Tipo de Custo' class='combo'>
                <option value=''>SELECIONE</option>
                <?
                    if($campos[0]['tipo_custo'] == 'P') {
                        $selectedp = 'selected';
                    }else if($campos[0]['tipo_custo'] == 'V') {
                        $selectedv = 'selected';
                    }else if($campos[0]['tipo_custo'] == 'F') {
                        $selectedf = 'selected';
                    }
                ?>
                <option value='P' <?=$selectedp;?>>Processo</option>
                <option value='V' <?=$selectedv;?>>Variavel</option>
                <option value='F' <?=$selectedf;?>>Fixo</option>	
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desenho p/ ConferÍncia:
        </td>
        <td>
            <input type='file' name='txt_desenho_para_conferencia' title='Digite ou selecione o Caminho do Desenho para ConferÍncia' size='80' class='caixadetexto'>
            <!--Este hidden ser· utilizado mais abaixo no passo 3 ...-->
            <input type='hidden' name='hdd_desenho_para_conferencia' value='<?=$campos[0]['desenho_para_conferencia'];?>'>
        </td>
    </tr>
<?
/******************************************************************************/
        if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo ent„o ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ ConferÍncia Atual:
        </td>
        <td>
            <img src = '../../../imagem/desenhos_grupos_pis/<?=$campos[0]['desenho_para_conferencia'];?>' width='180' height='120'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ ConferÍncia Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ ConferÍncia Atual
            </label>
        </td>
    </tr>
<?
        }
/******************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' cols='50' rows='5' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Verifico se existe algum outro "Grupo de PI" com esse nome alÈm do atual ...
    $sql = "SELECT `id_grupo` 
            FROM `grupos` 
            WHERE `nome` = '$_POST[txt_grupo]' 
            AND `id_grupo` <> '$_POST[id_grupo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
        $valor = 3;
    }else {
/*************************************************************/
/*Se o Usu·rio habilitou a opÁ„o de excluir o Desenho para ConferÍncia ou ent„o ele est· fazendo 
a substituiÁ„o de uma Imagem por outra, ent„o eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_conferencia'])) {
            $endereco_desenho_para_conferencia = '../../../../imagem/desenhos_grupos_pis/'.$_POST['hdd_desenho_para_conferencia'];
            unlink($endereco_desenho_para_conferencia);//Exclui a Imagem do Servidor ...
            $campo_desenho_para_conferencia = " , `desenho_para_conferencia` = '' ";
        }
        if(!empty($_FILES['txt_desenho_para_conferencia']['type'])) {
            if(!empty($_POST['hdd_desenho_para_conferencia'])) {//Se existir algum desenho antigo, aÌ sim eu posso removo este p/ substituir pelo novo ...
                if(file_exists('../../../imagem/desenhos_grupos_pis/'.$_POST['hdd_desenho_para_conferencia'])) {
                    unlink('../../../imagem/desenhos_grupos_pis/'.$_POST['hdd_desenho_para_conferencia']);
                }
            }
            switch ($_FILES['txt_desenho_para_conferencia']['type']) {
                case 'image/gif':
                case 'image/pjpeg':
                case 'image/jpeg':
                case 'image/x-png':
                case 'image/bmp':
                    $desenho_para_conferencia = copiar::copiar_arquivo('../../../imagem/desenhos_grupos_pis/', $_FILES['txt_desenho_para_conferencia']['tmp_name'], $_FILES['txt_desenho_para_conferencia']['name'], $_FILES['txt_desenho_para_conferencia']['size'], $_FILES['txt_desenho_para_conferencia']['type'], '2');
                break;
                default:
                    //echo "N„o È possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_conferencia = " , `desenho_para_conferencia` = '$desenho_para_conferencia' ";
        }
        $sql = "UPDATE `grupos` SET `id_conta_caixa_pagar` = '$_POST[cmb_conta_caixa_pagar]', `referencia` = '$_POST[txt_referencia]', `nome` = '$_POST[txt_grupo]', `tipo_custo` = '$_POST[cmb_tipo_custo]' $campo_desenho_para_conferencia, `observacao` = '".strtolower($_POST[txt_observacao])."' WHERE `id_grupo` = '$_POST[id_grupo]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
	<Script Language = 'Javascript'>
		window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Grupo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho'  align='center'>
        <td colspan='2'>
            Alterar Grupo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Grupos por: Grupo' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Grupo</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Grupos' onclick='limpar()' class='checkbox' id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>