<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/cotas/cotas.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>COTA(S) ALTERADA(S) COM SUCESSO.</font>";

if(!empty($_POST['hdd_representante_cota'])) {
    foreach($_POST['hdd_representante_cota'] as $i => $id_representante_cota) {
        //Altera a última cota do Representante que está vigência ...
        $sql = "UPDATE `representantes_vs_cotas` SET `cota_mensal` = '".$_POST['txt_cota_mensal'][$i]."', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_representante_cota` = '$id_representante_cota' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

/*Primeiramente busco qual é a Data Inicial de Vigência do "id_representante_cota" passado por parâmetro pq essa 
será utilizada no SQL abaixo ...*/
$sql = "SELECT id_representante, data_inicial_vigencia, data_final_vigencia 
        FROM `representantes_vs_cotas` 
        WHERE `id_representante_cota` = '$_GET[id_representante_cota]' LIMIT 1 ";
$campos_gerais = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Cota(s) do Representante ::.</title>
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
//Conta Mensal ...
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]' && elementos[i].value == '') {
            alert('DIGITE UMA COTA MENSAL P/ ESTA DIVISÃO !')
            elementos[i].focus()
            return false
        }
    }
//Preparo as caixas antes de gravar na BD ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]') elementos[i].value = strtofloat(elementos[i].value)
    }
    //Controle p/ não recarregar a Tela de baixo ...
    document.form.nao_atualizar.value = 1
}

function copiar_cotas() {
    var elementos = document.form.elements
    var contador = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_cota_mensal[]') {
            //Significa que estou na 1ª caixa e que vou armazenar este Valor p/ abastecer as outras caixas ...
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
    //Controle p/ não recarregar a Tela de baixo ...
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href
}
</Script>
</head>
<body onload='cota_mensal_geral()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--***********Controles de Tela************-->
<input type='hidden' name='nao_atualizar' value='0'>
<input type='hidden' name='hdd_representante' value='<?=$id_representante;?>'>
<!--****************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cota(s) do Representante => 
            <font color='yellow'>
            <?
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos_gerais[0]['id_representante']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Data Inicial de Vigência: 
            <input type='text' name='txt_data_inicial_vigencia' value='<?=data::datetodata($campos_gerais[0]['data_inicial_vigencia'], '/');?>' title='Digite a Data Inicial de Vigência' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
<?
    //Busca todas as Empresas Divisões que estão ativas e cadastradas no Sistema ...
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos_empresa_divisao = bancos::sql($sql);
    $linhas_empresa_divisao = count($campos_empresa_divisao);
    if($linhas_empresa_divisao > 0) {
?>
    <tr class='linhanormaldestaque' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Divisão(ões)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Cota Mensal</i></b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_empresa_divisao; $i++) {
            //Busco a Cota Mensal do Representante na Empresa Divisão do Loop e na Data de Vigência buscada acima ...
            $sql = "SELECT id_representante_cota, cota_mensal 
                    FROM `representantes_vs_cotas` 
                    WHERE `id_representante` = '".$campos_gerais[0]['id_representante']."' 
                    AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                    AND `data_inicial_vigencia` = '".$campos_gerais[0]['data_inicial_vigencia']."' 
                    AND `data_final_vigencia` = '".$campos_gerais[0]['data_final_vigencia']."' LIMIT 1 ";
            $campos = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_empresa_divisao[$i]['razaosocial'];?>
        </td>
        <td>
            <input type='text' name='txt_cota_mensal[]' value='<?=number_format($campos[0]['cota_mensal'], 2, ',', '.');?>' title='Digite a Cota Mensal' onkeyup="verifica(this, 'moeda_especial', '2', '', event);cota_mensal_geral()" maxlength='10' size='12' class='caixadetexto'>
<?
            if($i == 0) {//Somente para a primeira linha
?>
                &nbsp;<img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' title='Copiar Cota Mensal' alt='Copiar Cota Mensal' onclick='copiar_cotas()'>
<?
            }
?>
            <!--*****************Controle de Tela*****************-->
            <input type='hidden' name='hdd_representante_cota[]' value='<?=$campos[0]['id_representante_cota'];?>'>
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
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="document.form.nao_atualizar.value = 1;window.location = 'detalhes.php?id_representante=<?=$campos_gerais[0]['id_representante'];?>'">
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>