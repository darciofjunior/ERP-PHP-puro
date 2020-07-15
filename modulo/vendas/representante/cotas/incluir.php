<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/cotas/cotas.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>NOVA(S) COTA(S) INCLU�DA(S) COM SUCESSO.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_representante = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_representante'] : $_GET['id_representante'];

if(!empty($_POST['hdd_empresa_divisao'])) {
    $data_inicial_vigencia  = data::datatodate($_POST['txt_data_inicial_vigencia'], '-');
    
/*Antes de qualquer coisa, verifico se a "Data Inicial de Vig�ncia" que est� sendo Inclusa, n�o � 
a mesma "Data Inicial Vig�ncia" que est� em vigor ...*/
    $sql = "SELECT `id_representante_cota` 
            FROM `representantes_vs_cotas` 
            WHERE `id_representante` = '$_POST[id_representante]' 
            AND `data_inicial_vigencia` = '$data_inicial_vigencia' 
            AND `data_final_vigencia` = '0000-00-00' LIMIT 1 ";
    $campos = bancos::sql($sql);
    /*Significa que o usu�rio simplesmente est� fazendo uma "Altera��o de Cotas", porque est� mantendo a 
    mesma "Data Inicial de Vig�ncia" que ainda est� em Vigor ...*/
    if(count($campos) == 1) {
        foreach($_POST['hdd_empresa_divisao'] as $i => $id_empresa_divisao) {
            //Atualiza a Data Final por Empresa Divis�o ...
            $sql = "SELECT id_representante_cota 
                    FROM `representantes_vs_cotas` 
                    WHERE `id_representante` = '$_POST[id_representante]' 
                    AND `id_empresa_divisao` = '$id_empresa_divisao' 
                    ORDER BY data_inicial_vigencia DESC ";
            $campos = bancos::sql($sql);
            //Altera a �ltima cota do Representante que est� vig�ncia ...
            $sql = "UPDATE `representantes_vs_cotas` SET `cota_mensal` = '".$_POST['txt_cota_mensal'][$i]."', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_representante_cota` = '".$campos[0]['id_representante_cota']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    /*Significa que o Usu�rio realmente est� incluindo uma Nova Cota, devido ter sido inclusa 
    uma Nova "Data Inicial de Vig�ncia" ...*/
    }else {
/******************Procedimento de Altera��o******************/
/*Antes de Incluir uma Nova Data Inicial p/ Vig�ncia com as suas respectivas Cotas, atualizo na �ltima Data 
de Vig�ncia que est� em vigor a Data Final ...*/
        $data_final_vigencia = data::datatodate(data::adicionar_data_hora($_POST['txt_data_inicial_vigencia'], -1), '-');

        foreach($_POST['hdd_empresa_divisao'] as $i => $id_empresa_divisao) {
            //Atualiza a Data Final por Empresa Divis�o ...
            $sql = "SELECT id_representante_cota 
                    FROM `representantes_vs_cotas` 
                    WHERE `id_representante` = '$_POST[id_representante]' 
                    AND `id_empresa_divisao` = '$id_empresa_divisao' 
                    ORDER BY data_inicial_vigencia DESC ";
            $campos = bancos::sql($sql);
            //Altera a �ltima cota do Representante que est� vig�ncia ...
            $sql = "UPDATE `representantes_vs_cotas` SET `data_final_vigencia` = '$data_final_vigencia', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_representante_cota` = '".$campos[0]['id_representante_cota']."' LIMIT 1 ";
            bancos::sql($sql);
        }
/******************Procedimento de Inclus�o*******************/
        foreach($_POST['hdd_empresa_divisao'] as $i => $id_empresa_divisao) {
            //Insere uma Nova Cota p/ o Representante por Empresa Divis�o ...
            $sql = "INSERT INTO `representantes_vs_cotas` (`id_representante_cota`, `id_representante`, `id_empresa_divisao`, `cota_mensal`, `data_inicial_vigencia`, `data_sys`) VALUES (NULL, '$_POST[id_representante]', '$id_empresa_divisao', '".$_POST['txt_cota_mensal'][$i]."', '$data_inicial_vigencia', '".date('Y-m-d')."') ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}

/*Antes de qualquer coisa busco a "Data Inicial de Vig�ncia" em vigor do Representante passado 
por par�metro ...*/
$sql = "SELECT DATE_FORMAT(`data_inicial_vigencia`, '%d/%m/%Y') AS data_inicial_vigencia_atual 
        FROM `representantes_vs_cotas` 
        WHERE `id_representante` = '$id_representante' 
        AND `data_final_vigencia` = '0000-00-00' LIMIT 1 ";
$campos_data_inicial_vigencia = bancos::sql($sql);
?>
<html>
<title>.:: Incluir Nova(s) Cota(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/data.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Data Inicial de Vig�ncia ...
    if(!data('form', 'txt_data_inicial_vigencia', '4000', 'IN�CIO DE VIG�NCIA')) {
        return false
    }
/*Verifico se o dia 26 foi digitado nesse campo de Data Inicial, sempre tem que iniciar com 26 
que � o Per�odo Inicial da Folha ...*/
    if(document.form.txt_data_inicial_vigencia.value.indexOf('26') == -1) {
        alert('DATA INICIAL DE VIG�NCIA INV�LIDA !!!\n\nTODA A DATA INICIAL DE VIG�NCIA COME�A COM DIA 26 !')
        document.form.txt_data_inicial_vigencia.focus()
        document.form.txt_data_inicial_vigencia.select()
        return false
    }
//Conta Mensal ...
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]' && elementos[i].value == '') {
            alert('DIGITE UMA COTA MENSAL P/ ESTA DIVIS�O !')
            elementos[i].focus()
            return false
        }
    }
/*Verifico se o Usu�rio est� mantendo uma "Data Inicial de Vig�ncia" que est� em Vigor ou se realmente 
est� cadastrando uma Nova Data Inicial de Vig�ncia ...*/
    var data_inicial_vigencia_atual     = '<?=$campos_data_inicial_vigencia[0]['data_inicial_vigencia_atual'];?>'
    var data_inicial_vigencia_digitada  = document.form.txt_data_inicial_vigencia.value
    
    if(data_inicial_vigencia_atual == data_inicial_vigencia_digitada) {
        var resposta = confirm('VOC� EST� MANTENDO UMA "DATA INICIAL DE VIG�NCIA" EM VIGOR !!!\n\nO SISTEMA SIMPLESMENTE IR� ATUALIZAR A(S) COTA(S) DO REPRESENTANTE, DESEJA CONTINUAR ?')
        if(resposta == false) return false
    }else {
        var resposta = confirm('VOC� EST� CADASTRANDO UMA NOVA "DATA INICIAL DE VIG�NCIA" !!!\n\nO SISTEMA IR� CADASTRAR NOVA(S) COTA(S) P/ ESTE REPRESENTANTE, DESEJA CONTINUAR ?')
        if(resposta == false) return false
    }
//Preparo as caixas antes de gravar na BD ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]') elementos[i].value = strtofloat(elementos[i].value)
    }
    //Desabilita p/ poder gravar no BD ...
    document.form.txt_data_inicial_vigencia.disabled = false
    //Controle p/ n�o recarregar a Tela de baixo ...
    document.form.nao_atualizar.value = 1
}

function copiar_cotas() {
    var elementos = document.form.elements
    var contador = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]') {
            //Significa que estou na 1� caixa e que vou armazenar este Valor p/ abastecer as outras caixas ...
            if(contador == 0) {
                valor_caixa = elementos[i].value
                contador++
            }else {
                elementos[i].value = valor_caixa
            }
        }
    }
    cota_mensal_geral()
}

function cota_mensal_geral() {
    //Conta Mensal ...
    var elementos           = document.form.elements
    var cota_mensal_geral   = 0
//Preparo as caixas antes de gravar na BD ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]') {
            cota_mensal_geral+= (elementos[i].value != '') ? eval(strtofloat(elementos[i].value)) : 0
        }
    }
    document.form.txt_cota_mensal_geral.value = cota_mensal_geral
    document.form.txt_cota_mensal_geral.value = arred(document.form.txt_cota_mensal_geral.value, 2, 1)
}

function atualizar_abaixo() {
    //Controle p/ n�o recarregar a Tela de baixo ...
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--************Controle de Tela************-->
<input type='hidden' name='nao_atualizar' value='0'>
<!--****************************************-->
<input type='hidden' name='id_representante' value='<?=$id_representante;?>'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir Nova(s) Cota(s) p/ o Representante => 
            <font color='yellow'>
            <?
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Data Inicial de Vig�ncia: 
            <?
                //Aqui eu busco o Per�odo atual da Folha p/ gerar a Nova Data Vig�ncia ...
                $datas                  = genericas::retornar_data_relatorio();
                /*A Data Inicial de Vig�ncia Sugestiva, sempre ser� o 1� dia do pr�ximo 
                Per�odo da Folha ou seja 26/?/? ...*/
                $data_inicial_vigencia  = data::adicionar_data_hora($datas['data_final'], 1);
            ?>
            <input type='text' name='txt_data_inicial_vigencia' value='<?=$data_inicial_vigencia;?>' title='Data Inicial de Vig�ncia' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
<?
    //Busca todas as Empresas Divis�es que est�o ativas e cadastradas no Sistema ...
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos_empresa_divisao = bancos::sql($sql);
    $linhas_empresa_divisao = count($campos_empresa_divisao);
    if($linhas_empresa_divisao > 0) {
?>
    <tr class='linhanormaldestaque' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Divis�o(�es)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Cota Mensal</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_empresa_divisao; $i++) {
            /*Busco a Cota Mensal do Representante passado por par�metro na Empresa Divis�o do Loop, 
            na �ltima Data de Vig�ncia que est� em vigor ...*/
            $sql = "SELECT id_representante_cota, cota_mensal 
                    FROM `representantes_vs_cotas` 
                    WHERE `id_representante` = '$id_representante' 
                    AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                    ORDER BY data_inicial_vigencia DESC ";
            $campos = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_empresa_divisao[$i]['razaosocial'];?>
            <!--*****************Controle de Tela*****************-->
            <input type='hidden' name='hdd_empresa_divisao[]' value='<?=$campos_empresa_divisao[$i]['id_empresa_divisao'];?>'>
        </td>
        <td>
            <input type='text' name='txt_cota_mensal[]' title='Digite a Cota Mensal' onkeyup="verifica(this, 'moeda_especial', '2', '', event);cota_mensal_geral()" maxlength='10' size='12' class='caixadetexto'>
<?
            if($i == 0) {//Somente para a primeira linha
?>
                &nbsp;<img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' title='Copiar Cota Mensal' alt='Copiar Cota Mensal' onclick='copiar_cotas()'>
<?
            }
?>
        </td>
        <td>
            <?='<font color="darkblue"><b>(Cota vigente => <font color="black">R$ '.number_format($campos[0]['cota_mensal'], 2, ',', '.').'</font>)</b></font>';?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td>
            Total => 
        </td>
        <td>
            <input type='text' name='txt_cota_mensal_geral' title='Cota Mensal Geral' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');cota_mensal_geral()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observa��o:</font></b>
<pre>
* A Data Inicial de Vig�ncia sempre ser� o 1� dia do pr�ximo Per�odo da Folha, ou seja 26/?/?.
</pre>