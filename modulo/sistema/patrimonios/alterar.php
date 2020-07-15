<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
segurancas::geral('/erp/albafer/modulo/sistema/patrimonios/opcoes.php', '../../../');

//Depois que o usu�rio submeteu esse patrimonio ...
if(!empty($_POST['cmb_tipo_patrimonio'])) {
    //Antes de se alterar um Patrim�nio da Empresa, � enviado um e-mail ao Roberto p/ que este fique ciente do ocorrido ...
    $sql = "SELECT * 
            FROM `patrimonios` 
            WHERE `id_patrimonio` = '$_POST[id_patrimonio]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    /*Mais de antes de enviar o e-mail verifico se realmente foi feita alguma mudan�a no Cadastro, �s vezes o funcion�rio 
    s� entra na tela e clica em Salvar de alegre sem ter mudado nada ...*/
    if($campos[0]['marca_modelo'] != $_POST['txt_marca_modelo'] || $campos[0]['numero_serie'] != $_POST['txt_numero_serie'] || $campos[0]['sistema_operacional'] != $_POST['txt_so'] || $campos[0]['processador'] != $_POST['txt_processador'] || $campos[0]['memoria'] != $_POST['txt_memoria'] || $campos[0]['hd'] != $_POST['txt_hd'] || $campos[0]['valor'] != $_POST['txt_valor'] || $campos[0]['observacao'] != $_POST['txt_observacao']) {
        $texto_email.=  '<br/><b>Marca / Modelo: </b>'.$campos[0]['marca_modelo'];

        if(!empty($campos[0]['numero_serie']))          $texto_email.= '<br/><b>N�mero de S�rie: </b>'.$campos[0]['numero_serie'];
        if(!empty($campos[0]['sistema_operacional']))   $texto_email.= '<br/><b>Sistema Operacional: </b>'.$campos[0]['sistema_operacional'];
        if(!empty($campos[0]['processador']))           $texto_email.= '<br/><b>Processador: </b>'.$campos[0]['processador'];
        if(!empty($campos[0]['memoria']))               $texto_email.= '<br/><b>Mem�ria: </b>'.$campos[0]['memoria'];
        if(!empty($campos[0]['hd']))                    $texto_email.= '<br/><b>HD: </b>'.$campos[0]['hd'];
        if(!empty($campos[0]['valor']))                 $texto_email.= '<br/><b>Valor: </b>'.number_format($campos[0]['valor'], 2, ',', '.');
        if(!empty($campos[0]['observacao']))            $texto_email.= '<br/><b>Observa��o: </b>'.$campos[0]['observacao'];
    
        //Nessa parte relaciono todas mudan�as ocorridas referente ao Patrim�nio em Quest�o ...
        $texto_email.= '<br/><br/><b>MUDAN�A(S): </b><br/>';
        
        if($campos[0]['marca_modelo'] != $_POST['txt_marca_modelo'])    $texto_email.= '<br/><b>Nova Marca / Modelo: </b>'.$_POST['txt_marca_modelo'];
        if($campos[0]['numero_serie'] != $_POST['txt_numero_serie'])    $texto_email.= '<br/><b>Novo N�mero de S�rie: </b>'.$_POST['txt_numero_serie'];
        if($campos[0]['sistema_operacional'] != $_POST['txt_so'])       $texto_email.= '<br/><b>Novo Sistema Operacional: </b>'.$_POST['txt_so'];
        if($campos[0]['processador'] != $_POST['txt_processador'])      $texto_email.= '<br/><b>Novo Processador: </b>'.$_POST['txt_processador'];
        if($campos[0]['memoria'] != $_POST['txt_memoria'])              $texto_email.= '<br/><b>Nova Mem�ria: </b>'.$_POST['txt_memoria'];
        if($campos[0]['hd'] != $_POST['txt_hd'])                        $texto_email.= '<br/><b>Novo HD: </b>'.$_POST['txt_hd'];
        if($campos[0]['valor'] != $_POST['txt_valor'])                  $texto_email.= '<br/><b>Novo Valor: </b>'.number_format($_POST['txt_valor'], 2, ',', '.');
        if($campos[0]['observacao'] != $_POST['txt_observacao'])        $texto_email.= '<br/><b>Nova Observa��o: </b>'.$_POST['txt_observacao'];
    
        //Busco o nome do Funcion�rio que est� alterando o Patrim�nio ...
        $sql = "SELECT `nome` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_funcionario = bancos::sql($sql);
        $mensagem_email     = 'O Patrim�nio <b>"'.$campos[0]['tipo_patrimonio'].'"</b>: <br/>'.$texto_email.'<br/><br/>Foi alterado pelo funcion�rio <b>'.$campos_funcionario[0]['nome'].'</b> no dia '.date('d/m/Y').' �s '.date('H:i:s').'.';
        comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br', '', 'Exclus�o de Patrim�nio', $mensagem_email);
    }
    //Aqui o Patrim�nio � alterado ...
    $sql = "UPDATE `patrimonios` SET `id_funcionario_registrou` = '$_SESSION[id_funcionario]', 
            `id_departamento` = '$_POST[cmb_departamento]', `id_funcionario` = '$_POST[cmb_funcionario]', 
            `tipo_patrimonio` = '$_POST[cmb_tipo_patrimonio]', `marca_modelo` = '$_POST[txt_marca_modelo]', 
            `numero_serie` = '$_POST[txt_numero_serie]', `sistema_operacional` = '$_POST[txt_so]', 
            `processador` = '$_POST[txt_processador]', `memoria` = '$_POST[txt_memoria]', 
            `hd` = '$_POST[txt_hd]', `valor` = '$_POST[txt_valor]', `observacao` = '$_POST[txt_observacao]', 
            `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_patrimonio` = '$_POST[id_patrimonio]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('PATRIM�NIO ALTERADO COM SUCESSO !')
        parent.location = parent.location.href
    </Script>
<?
}

//Aqui eu trago dados do Patrim�nio passado por par�metro ...
$sql = "SELECT * 
        FROM `patrimonios` 
        WHERE `id_patrimonio` = '$_GET[id_patrimonio]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Patrim�nio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Departamento ...
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE UM DEPARTAMENTO !')) {
        return false
    }
//Tipo de Patrim�nio ...
    if(!combo('form', 'cmb_tipo_patrimonio', '', 'SELECIONE UM TIPO DE PATRIM�NIO !')) {
        return false
    }
//Marca / Modelo ...
    if(!texto('form', 'txt_marca_modelo', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ�������������������������� 1234567890.,-_()[]{},.:;*+/', 'MARCA / MODELO', '1')) {
        return false
    }
//Valor ...
    if(document.form.txt_valor.value != '') {
        if(!texto('form', 'txt_valor', '1', '0123456789,.', 'VALOR', '2')) {
            return false
        }
    }
    limpeza_moeda('form', 'txt_valor, ')
}

function carregar_funcionarios(id_funcionario_gravado) {
//Se existir um funcion�rio gravado p/ o Patrim�nio carrego a combo de Funcion�rios com esse Funcion�rio selecionado ...
    if(id_funcionario_gravado != '') {
        ajax('carregar_funcionarios.php', 'cmb_funcionario', id_funcionario_gravado)
    }else {
        ajax('carregar_funcionarios.php', 'cmb_funcionario')
    }
}
</Script>
<body onload="carregar_funcionarios('<?=$campos[0]['id_funcionario'];?>');document.form.cmb_departamento.focus()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_patrimonio' value='<?=$_GET['id_patrimonio'];?>'>   
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Patrim�nio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <select name='cmb_departamento' title='Selecione o Departamento' onchange='carregar_funcionarios()' class='combo'>
            <?   
                $sql = "SELECT `id_departamento`, `departamento` 
                        FROM `departamentos`                         
                        WHERE `ativo` = '1' ORDER BY `departamento` ";
                echo combos::combo($sql, $campos[0]['id_departamento']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Para Funcion�rio:
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione o Para Funcion�rio' class='combo'>
                <option value=''>SELECIONE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Patrim�nio:</b>
        </td>
        <td>
            <?
                if($campos[0]['tipo_patrimonio'] == 'CELULAR') {
                    $selected_celular = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'COMPUTADOR') {
                    $selected_computador = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'IMPRESSORA') {
                    $selected_impressora = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'INSTRUMENTO DE MEDI��O') {
                    $selected_inst_medicao = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'MONITOR') {
                    $selected_monitor = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'TELEFONE') {
                    $selected_telefone = 'selected';
                }else if($campos[0]['tipo_patrimonio'] == 'UMIDIFICADOR') {
                    $selected_umidificador = 'selected';
                }
            ?>
            <select name='cmb_tipo_patrimonio' title='Selecione o Tipo de Patrim�nio' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='CELULAR' <?=$selected_celular;?>>CELULAR</option>
                <option value='COMPUTADOR' <?=$selected_computador;?>>COMPUTADOR</option>
                <option value='IMPRESSORA' <?=$selected_impressora;?>>IMPRESSORA</option>
                <option value='INSTRUMENTO DE MEDI��O' <?=$selected_inst_medicao;?>>INSTRUMENTO DE MEDI��O</option>
                <option value='MONITOR' <?=$selected_monitor;?>>MONITOR</option>
                <option value='TELEFONE' <?=$selected_telefone;?>>TELEFONE</option>
                <option value='UMIDIFICADOR' <?=$selected_umidificador;?>>UMIDIFICADOR</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Marca/Modelo:</b>
        </td>
        <td>
            <input type='text' name='txt_marca_modelo' value="<?=$campos[0]['marca_modelo'];?>" title='Digite a Marca / Modelo' size='65' maxlength='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N�mero de S�rie:
        </td>
        <td>
            <input type='text' name='txt_numero_serie' value="<?=$campos[0]['numero_serie'];?>" title='Digite o N�mero de S�rie' size='40' maxlength='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Sistema Operacional:
        </td>
        <td>
            <input type='text' name='txt_so' title='Digite o Sistema Operacional' value="<?=$campos[0]['sistema_operacional'];?>" size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Processador:
        </td>
        <td>
            <input type='text' name='txt_processador' title='Digite o processador' value="<?=$campos[0]['processador'];?>" size='65' maxlength='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Mem�ria:
        </td>
        <td>
            <input type='text' name='txt_memoria' title='Digite a Mem�ria' value="<?=$campos[0]['memoria'];?>" size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            HD:
        </td>
        <td>
            <input type='text' name='txt_hd' title='Digite o HD' value="<?=$campos[0]['hd'];?>" size='17' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor:
        </td>
        <td>
            <input type='text' name='txt_valor' value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a observacao' maxlength='255' cols='64' rows='4' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');carregar_funcionarios('<?=$campos[0]['id_funcionario'];?>');document.form.cmb_departamento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>