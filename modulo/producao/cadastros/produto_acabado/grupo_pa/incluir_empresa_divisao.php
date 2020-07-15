<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
session_start('funcionarios');

if($permissao == 'inc') {//Significa q vem de incluir grupo P.A.
    segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/incluir.php', '../../../../../');
}else {//Significa q vem de alterar grupo P.A.
    segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/alterar.php', '../../../../../');
}

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>EMPRESA(S) DIVISÃO(ÕES) INCLUÍDA(S) COM SUCESSO PARA GRUPO P.A.</font>";
$mensagem[3] = "<font class='erro'>EMPRESA(S) DIVISÃO(ÕES) JÁ EXISTENTE(S) PARA ESTE GRUPO P.A.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT ed.id_empresa_divisao, ed.razaosocial, e.nomefantasia 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON ed.`id_empresa` = e.`id_empresa` 
                    WHERE ed.`razaosocial` LIKE '%$txt_consultar%' 
                    AND ed.`ativo` = '1' ORDER BY ed.razaosocial ";
        break;
        default:
            $sql = "SELECT ed.id_empresa_divisao, ed.razaosocial, e.nomefantasia 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON ed.`id_empresa` = e.`id_empresa` 
                    WHERE ed.`ativo` = '1' ORDER BY ed.razaosocial ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_empresa_divisao.php?permissao=<?=$_POST['permissao'];?>&id_grupo_pa=<?=$_POST['id_grupo_pa'];?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Atrelar Empresa(s) Divisão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_empresa_divisao[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_empresa_divisao[]'].length)
    }

    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_empresa_divisao'+i).checked == true) {
//Desconto Base A Nacional
            if(document.getElementById('txt_desc_base_nac_a'+i).value == '') {
                alert('DIGITE O DESCONTO BASE A NACIONAL !')
                document.getElementById('txt_desc_base_nac_a'+i).focus()
                return false
            }
//Desconto Base B Nacional
            if(document.getElementById('txt_desc_base_nac_b'+i).value == '') {
                alert('DIGITE O DESCONTO BASE B NACIONAL !')
                document.getElementById('txt_desc_base_nac_b'+i).focus()
                return false
            }
//Acréscimo Base Nacional
            if(document.getElementById('txt_acrescimo_base_nac'+i).value == '') {
                alert('DIGITE O ACRÉSCIMO BASE NACIONAL !')
                document.getElementById('txt_acrescimo_base_nac'+i).focus()
                return false
            }
//Margem de Lucro Min. Exportação ...
            if(document.getElementById('txt_ml_min_exp'+i).value == '') {
                alert('DIGITE A MARGEM DE LUCRO MÍNIMA DE EXPORTAÇÃO !')
                document.getElementById('txt_ml_min_exp'+i).focus()
                return false
            }
//Margem de Lucro Min. Nacional ...
            if(document.getElementById('txt_ml_min_nac'+i).value == '') {
                alert('DIGITE A MARGEM DE LUCRO MÍNIMA NACIONAL !')
                document.getElementById('txt_ml_min_nac'+i).focus()
                return false
            }
        }
    }
//Prepara no formato moeda antes de submeter para o BD
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_empresa_divisao'+i).checked == true) {
            document.getElementById('txt_desc_base_nac_a'+i).value      = strtofloat(document.getElementById('txt_desc_base_nac_a'+i).value)
            document.getElementById('txt_desc_base_nac_b'+i).value      = strtofloat(document.getElementById('txt_desc_base_nac_b'+i).value)
            document.getElementById('txt_acrescimo_base_nac'+i).value   = strtofloat(document.getElementById('txt_acrescimo_base_nac'+i).value)
            document.getElementById('txt_ml_min_exp'+i).value           = strtofloat(document.getElementById('txt_ml_min_exp'+i).value)
            document.getElementById('txt_ml_min_nac'+i).value           = strtofloat(document.getElementById('txt_ml_min_nac'+i).value)
            document.getElementById('txt_comissao_extra'+i).value       = strtofloat(document.getElementById('txt_comissao_extra'+i).value)
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Atrelar Empresa(s) Divisão(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Itens <input type='hidden' name='chkt_tudo'>
        </td>
        <td>
            Empresa
        </td>
        <td>
            Razão Social
        </td>
        <td>
            Desc. Base A Nac.
        </td>
        <td>
            Desc. Base B Nac.
        </td>
        <td>
            Acrésc. Base Nac.
        </td>
        <td>
            ML Min Exp.
        </td>
        <td>
            ML Min Nac.
        </td>
        <td>
            Comissão Extra
        </td>
        <td>
            Data Limite
        </td>
        <td>
            Caminho do PDF do Site
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_empresa_divisao[]' id='chkt_empresa_divisao<?=$i;?>' value="<?=$campos[$i]['id_empresa_divisao'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <input type='text' name='txt_desc_base_nac_a[]' id='txt_desc_base_nac_a<?=$i;?>' title='Digite o Desconto Base Nacional A' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_desc_base_nac_b[]' id='txt_desc_base_nac_b<?=$i;?>' title='Digite o Desconto Base Nacional B' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_acrescimo_base_nac[]' id='txt_acrescimo_base_nac<?=$i;?>' title='Digite o Acréscimo Base Nacional' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_ml_min_exp[]' id='txt_ml_min_exp<?=$i;?>' title='Digite a ML Min Exp' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_ml_min_nac[]' id='txt_ml_min_nac<?=$i;?>' title='Digite a ML Min Nac' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_comissao_extra[]' id='txt_comissao_extra<?=$i;?>' title='Digite a Comissão Extra' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '1', '', event)" maxlength='6' size='8' class='textdisabled' disabled>&nbsp;%
        </td>
        <td>
            <input type='text' name='txt_data_limite[]' id='txt_data_limite<?=$i;?>' title='Digite a Data Limite' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'data', '', '', event)" maxlength='10' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_caminho_pdf_site[]' id='txt_caminho_pdf_site<?=$i;?>' title='Digite o Caminho do PDF do Site' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" maxlength='85' size='30' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_empresa_divisao.php?permissao=<?=$permissao;?>&id_grupo_pa=<?=$id_grupo_pa;?>'" class='botao'>
            <input type='submit' name='cmd_atrelar' value='Atrelar' title='Atrelar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='permissao' value='<?=$permissao;?>'>
<input type='hidden' name='id_grupo_pa' value='<?=$id_grupo_pa;?>'>
</form>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Inserção das Empresas Divisões vs Grupos P.A.
    for($i = 0; $i < count($_POST['chkt_empresa_divisao']); $i++) {
        $sql = "SELECT id_gpa_vs_emp_div 
                FROM `gpas_vs_emps_divs` 
                WHERE `id_grupo_pa` = '$_POST[id_grupo_pa]' 
                AND `id_empresa_divisao` = '".$_POST['chkt_empresa_divisao'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            $data_limite = data::datatodate($_POST['txt_data_limite'][$i], '-');
            $sql = "INSERT INTO `gpas_vs_emps_divs` (`id_gpa_vs_emp_div`, `id_grupo_pa`, `id_empresa_divisao`, `desc_base_a_nac`, `desc_base_b_nac`, `acrescimo_base_nac`, `margem_lucro_exp`, `margem_lucro_minima`, `comissao_extra`, `data_limite`, `path_pdf`) VALUES (NULL, '".$_POST['id_grupo_pa']."', '".$_POST['chkt_empresa_divisao'][$i]."', '".$_POST['txt_desc_base_nac_a'][$i]."', '".$_POST['txt_desc_base_nac_b'][$i]."', '".$_POST['txt_acrescimo_base_nac'][$i]."', '".$_POST['txt_ml_min_exp'][$i]."', '".$_POST['txt_ml_min_nac'][$i]."', '".$_POST['txt_comissao_extra'][$i]."', '$data_limite', '".$_POST['txt_caminho_pdf_site'][$i]."') ";
            bancos::sql($sql);
            $valor = 2;
        }else {
            $valor = 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_empresa_divisao.php?permissao=<?=$permissao;?>&id_grupo_pa=<?=$id_grupo_pa;?>&valor=<?=$valor;?>'
        parent.location = parent.location.href//Atualiza a Tela de Baixo ...
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Atrelar Empresa(s) Divisão(ões) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='id_grupo_pa' value="<?=$id_grupo_pa;?>">
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Atrelar Empresa(s) Divisão(ões)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Empresas Divisões por: Razão Social' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as Empresas Divisões' onclick='limpar()' class='checkbox' id='label2'>
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
<input type='hidden' name='permissao' value='<?=$permissao;?>'>
</form>
</body>
</html>
<?}?>