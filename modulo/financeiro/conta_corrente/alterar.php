<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA CORRENTE ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>CONTA CORRENTE ALTERADA COM SUCESSO. </font><font class='erro'>MAS JÁ EXISTE UMA CONTA QUE UTILIZA CONTA CORRENTE / BANCO PARA FATURAMENTO SGD.</font>";
$mensagem[4] = "<font class='erro'>CONTA CORRENTE JÁ EXISTENTE.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cc.*, a.`cod_agencia` AS cod_agencia, b.`banco` AS banco, e.`nomefantasia` AS nomefantasia 
                    FROM `contas_correntes` cc 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = cc.`id_empresa` 
                    WHERE cc.`conta_corrente` LIKE '$txt_consultar%' 
                    AND cc.`ativo` = '1' ORDER BY cc.`conta_corrente` ";
        break;
        default:
            $sql = "SELECT cc.*, a.`cod_agencia` AS cod_agencia, b.`banco` AS banco, e.`nomefantasia` AS nomefantasia 
                    FROM `contas_correntes` cc 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = cc.`id_empresa` 
                    WHERE cc.`ativo` = '1' ORDER BY cc.`conta_corrente` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
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
<title>.:: Alterar Conta(s) Corrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Alterar Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Conta Corrente
        </td>
        <td>
            Cód. Agência
        </td>
        <td>
            Banco
        </td>
        <td>
            Empresa
        </td>
        <td>
            Uso de Fat.
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_conta_corrente=<?=$campos[$i]['id_contacorrente'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href = "alterar.php?passo=2&id_conta_corrente=<?=$campos[$i]['id_contacorrente'];?>" class='link'>
                <?=$campos[$i]['conta_corrente'];?>
            </a>        
        </td>
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
        <?
            if($campos[$i]['status_faturamento_sgd'] == 1) {
                echo '<font color="blue">SIM</font>';
            }else {
                echo '<font color="red">NÃO</font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    //Aqui eu busco dados da Conta Corrente ...
    $sql = "SELECT * 
            FROM `contas_correntes` 
            WHERE `id_contacorrente` = '$_GET[id_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Conta(s) Corrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Empresa ...
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Conta Corrente ...
    if(document.form.txt_conta_corrente.value == '') {
        alert('DIGITE A CONTA CORRENTE !')
        document.form.txt_conta_corrente.focus()
        return false
    }
//Se foi marcado o Checkbox de Conta p/ Exportação, então forço o usuário a preencher o campo abaixo ...
    if(document.form.chkt_conta_exportacao.checked == true) {
        if(document.form.txt_swift_code.value == '') {
            alert('DIGITE O SWIFT CODE !')
            document.form.txt_swift_code.focus()
            return false
        }
    }
}

function conta_exportacao() {
    if(document.form.chkt_conta_exportacao.checked == true) {
        //Layout de Habilitado ...
        document.form.txt_swift_code.className                  = 'caixadetexto'
        document.form.txt_iban.className                        = 'caixadetexto'
        document.form.txt_banco_correspondente.className        = 'caixadetexto'
        document.form.txt_swift_code_correspondente.className   = 'caixadetexto'
        document.form.txt_agencia_correspondente.className      = 'caixadetexto'
        document.form.txt_conta_corrente_correspondente.className = 'caixadetexto'
        //Habilito as caixas ...
        document.form.txt_swift_code.disabled                   = false
        document.form.txt_iban.disabled                         = false
        document.form.txt_banco_correspondente.disabled         = false
        document.form.txt_swift_code_correspondente.disabled    = false
        document.form.txt_agencia_correspondente.disabled       = false
        document.form.txt_conta_corrente_correspondente.disabled= false
        //Redefino as caixas ...
        document.form.txt_swift_code.value                      = '<?=$campos[0]['swift_code'];?>'
        document.form.txt_iban.value                            = '<?=$campos[0]['iban'];?>'
        document.form.txt_banco_correspondente.value            = '<?=$campos[0]['banco_correspondente'];?>'
        document.form.txt_swift_code_correspondente.value       = '<?=$campos[0]['swift_code_correspondente'];?>'
        document.form.txt_agencia_correspondente.value          = '<?=$campos[0]['agencia_correspondente'];?>'
        document.form.txt_conta_corrente_correspondente.value   = '<?=$campos[0]['conta_corrente_correspondente'];?>'
        
        document.form.txt_swift_code.focus()
    }else {
        //Layout de Desabilitado ...
        document.form.txt_swift_code.className                  = 'textdisabled'
        document.form.txt_iban.className                        = 'textdisabled'
        document.form.txt_banco_correspondente.className        = 'textdisabled'
        document.form.txt_swift_code_correspondente.className   = 'textdisabled'
        document.form.txt_agencia_correspondente.className      = 'textdisabled'
        document.form.txt_conta_corrente_correspondente.className = 'textdisabled'
        //Desabilito as caixas ...
        document.form.txt_swift_code.disabled                   = true
        document.form.txt_iban.disabled                         = true
        document.form.txt_banco_correspondente.disabled         = true
        document.form.txt_swift_code_correspondente.disabled    = true
        document.form.txt_agencia_correspondente.disabled       = true
        document.form.txt_conta_corrente_correspondente.disabled= true
        //Limpo as caixas ...
        document.form.txt_swift_code.value                      = ''
        document.form.txt_iban.value                            = ''
        document.form.txt_banco_correspondente.value            = ''
        document.form.txt_swift_code_correspondente.value       = ''
        document.form.txt_agencia_correspondente.value          = ''
        document.form.txt_conta_corrente_correspondente.value   = ''
    }
}
</Script>
<body onload='conta_exportacao();document.form.txt_conta_corrente.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onSubmit='return validar()'>
<!--******************************Controle de Tela******************************-->
<input type='hidden' name='hdd_agencia' value='<?=$campos[0]['id_agencia'];?>'>
<input type='hidden' name='hdd_conta_corrente' value='<?=$_GET['id_conta_corrente'];?>'>
<!--****************************************************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Corrente:</b>
        </td>
        <td>
            <input type='text' name='txt_conta_corrente' value='<?=$campos[0]['conta_corrente'];?>' title='Digite a Conta Corrente' maxlength='15' size='17' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
        <?
            if($campos[0]['status_faturamento_sgd'] == 1) $checked = 'checked';
        ?>
            <input type='checkbox' name="chkt_status_faturamento_sgd" value="1" title="Selecione o Usar Banco para Faturamento SGD" id="label" class='checkbox' <?=$checked;?>>
            <label for="label">
                Usar Conta Corrente / Banco para Faturamento SGD
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados para Exportação
            <?
                if($campos[0]['conta_exportacao'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_conta_exportacao' value='S' title='Selecione Conta p/ Exportação' onclick='conta_exportacao()' id='label1' class='checkbox' <?=$checked;?>>
            <label for='label1'>
                Conta p/ Exportação
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Swift Code:
        </td>
        <td>
            <input type='text' name='txt_swift_code' value='<?=$campos[0]['swift_code'];?>' title='Digite o Swift Code' maxlength='20' size='23' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Iban:
        </td>
        <td>
            <input type='text' name='txt_iban' value='<?=$campos[0]['iban'];?>' title='Digite o Iban' maxlength='40' size='42' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Banco Correspondente:
        </td>
        <td>
            <input type='text' name='txt_banco_correspondente' value='<?=$campos[0]['banco_correspondente'];?>' title='Digite o Banco Correspondente' maxlength='30' size='33' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Swift Code Correspondente:
        </td>
        <td>
            <input type='text' name='txt_swift_code_correspondente' value='<?=$campos[0]['swift_code_correspondente'];?>' title='Digite o Swift Correspondente' maxlength='20' size='23' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Agência Correspondente:
        </td>
        <td>
            <input type='text' name='txt_agencia_correspondente' value='<?=$campos[0]['agencia_correspondente'];?>' title='Digite a Agência Correspondente' maxlength='20' size='23' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Corrente Correspondente:
        </td>
        <td>
            <input type='text' name='txt_conta_corrente_correspondente' value='<?=$campos[0]['conta_corrente_correspondente'];?>' title='Digite a Conta Corrente Correspondente' maxlength='20' size='23' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if(empty($_GET['pop_up'])) {
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form','REDEFINIR');document.form.txt_conta_corrente.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?
                }
            ?>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Se for selecionado a Opção "Usar Conta Corrente / Banco para Faturamento SGD", então faz essa verificação
    if($_POST['chkt_status_faturamento_sgd'] == 1) {
        //Aqui eu busco o banco da Conta Corrente ...
        $sql = "SELECT `id_banco` 
                FROM `agencias` 
                WHERE `id_agencia` = '$_POST[hdd_agencia]' LIMIT 1 ";
        $campos = bancos::sql($sql);
/*Aqui verifico se existe alguma Conta Corrente da agência passada por parâmetro e da empresa selecionada em combo diferente da
Conta corrente atual que está sendo alterada que tenham a "preferência de Banco selecionada" a marcação, 
obs: só podemos ter um único registro com essa marcação ...*/
        $sql = "SELECT cc.`id_contacorrente` 
                FROM `contas_correntes` cc 
                INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` AND b.`id_banco` = '".$campos[0]['id_banco']."' 
                WHERE cc.`id_contacorrente` <> '$_POST[hdd_conta_corrente]' 
                AND cc.`id_empresa` = '$_POST[cmb_empresa]' 
                AND cc.`status_faturamento_sgd` = '1' ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Significa que já existe uma conta diferente da atual que está marcada como default
            $_POST['chkt_status_faturamento_sgd'] = 0;//Eu só posso ter uma conta marcada como default para o banco e empresa
            $trocar_mensagem = 1;
        }
    }
    /*Verifico se já existe uma Conta Corrente com esse nome existente p/ essa Empresa, agência, diferente da atual que 
    está sendo alterada ...*/
    $sql = "SELECT `id_contacorrente` 
            FROM `contas_correntes` 
            WHERE `id_empresa` = '$_POST[cmb_empresa]' 
            AND `conta_corrente` = '$_POST[txt_conta_corrente]' 
            AND `id_agencia` = '$_POST[hdd_agencia]' 
            AND `id_contacorrente` <> '$_POST[hdd_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $conta_exportacao = (!empty($_POST['chkt_conta_exportacao'])) ? 'S' : 'N';
        
        $sql = "UPDATE `contas_correntes` SET `id_empresa` = '$_POST[cmb_empresa]', `conta_corrente` = '$_POST[txt_conta_corrente]', `conta_exportacao` = '$conta_exportacao', `swift_code` = '$_POST[txt_swift_code]', `iban` = '$_POST[txt_iban]', `banco_correspondente` = '$_POST[txt_banco_correspondente]', `swift_code_correspondente` = '$_POST[txt_swift_code_correspondente]', `agencia_correspondente` = '$_POST[txt_agencia_correspondente]', `conta_corrente_correspondente` = '$_POST[txt_conta_corrente_correspondente]', `status_faturamento_sgd` = '$_POST[chkt_status_faturamento_sgd]' WHERE `id_contacorrente` = '$_POST[hdd_conta_corrente]' LIMIT 1 ";
        bancos::sql($sql);
        if($trocar_mensagem == 1) {
            $valor = 3;//Mensagem de que foi alterado com sucesso, mas que só pode ter uma conta padrão
        }else {
            $valor = 2;//Mensagem Padrão de que foi alterado com sucesso
        }
    }else {
        $valor = 4;
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
<title>.:: Alterar Conta(s) Corrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' id='opt1' value='1' title='Consultar Conta Corrente por Conta Corrente' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt1'>Conta Corrente</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todos as Contas Correntes' onclick='limpar()' class='checkbox'>
            <label for='todos'>Todos os registros</label>
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