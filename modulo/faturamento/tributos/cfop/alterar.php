<?
require('../../../../lib/segurancas.php');
//Às vezes essa Tela pode ser aberta como Pop-UP e sendo assim não mostro o Menu ...
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `cfops` 
                    WHERE CONCAT(cfop, '.', num_cfop) LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY num_cfop, cfop ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `cfops` 
                    WHERE `ativo` = '1' ORDER BY num_cfop, cfop ";
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
<title>.:: Alterar CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar CFOP(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            CFOP
        </td>
        <td>
            CFOP Revenda
        </td>
        <td>
            ICMS
        </td>
        <td>
            IPI
        </td>
        <td>
            NF de <br>Vendas
        </td>
        <td>
            Natureza Op. Resumida
        </td>
        <td>
            Texto da Nota
        </td>
        <td>
            Natureza de Operação
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_cfop='.$campos[$i]['id_cfop'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_cfop=<?=$campos[$i]['id_cfop'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td window.location = "<?=$url;?>">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Alterar CFOP' class='link'>
            <?
                if(!empty($campos[$i]['cfop'])) {
                    echo $campos[$i]['cfop'].'.'.$campos[$i]['num_cfop'];
                }else {
                    echo '&nbsp;';
                }
            ?>
            </a>
        </td>
        <td align='center'>
        <?
            //Se existir CFOP de Revenda vinculada a CFOP Industrial, então eu apresento a mesma ...
            if($campos[$i]['id_cfop_revenda'] != 0) {
                $sql = "SELECT cfop, num_cfop 
                        FROM `cfops` 
                        WHERE `id_cfop` = '".$campos[$i]['id_cfop_revenda']."' LIMIT 1 ";
                $campos_cfop_revenda    = bancos::sql($sql);
                echo $campos_cfop_revenda[0]['cfop'].'.'.$campos_cfop_revenda[0]['num_cfop'];
            }
        ?>
        </td>
        <td align='center'>
        <?
            if (empty($campos[$i]['icms'])) {
                echo '&nbsp;-&nbsp;';
            }else {
                if($campos[$i]['icms']  == 1) {
                    echo 'TRI';
                }elseif($campos[$i]['icms']  == 2){
                    echo 'ISE';
                }else{
                    echo 'DIG';
                }
            }
        ?>
        </td>
        <td align='center'>
        <?
            if (empty($campos[$i]['ipi'])) {
                echo '&nbsp;-&nbsp;';
            }else {
                if($campos[$i]['ipi']  == 1) {
                    echo 'TRI';
                }elseif($campos[$i]['ipi']  == 2){
                    echo 'ISE';
                }else{
                    echo 'DIG';
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['cfop_nf_venda'] == 'S') {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>		
        </td>
        <td align='left'>
            <?=$campos[$i]['natureza_operacao_resumida'];?>
        </td>
        </td>
        <td align='left'>
            <?=$campos[$i]['descricao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['natureza_operacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<pre>
<font color='red'><b>Legenda dos Tipos ICMS / IPI:</b></font>

<font color='blue'><b>TRI</b></font> -> TRIBUTAÇÃO NORMAL
<font color='blue'><b>ISE</b></font> -> ISENTO
<font color='blue'><b>DIG</b></font> -> DIGITAR NA NF
</pre>
</form>
</body>
</html>
<?
	}
}else if($passo == 2) {
    //Aqui trago dados da CFOP passada por parâmetro ...
    $sql = "SELECT * 
            FROM `cfops` 
            WHERE `id_cfop` = '$_GET[id_cfop]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//ICMS
    if(!combo('form', 'cmb_icms', '', 'SELECIONE O ICMS !')) {
        return false
    }
//IPI
    if(!combo('form', 'cmb_ipi', '', 'SELECIONE O IPI !')) {
        return false
    }
//CFOP
    if(!texto('form', 'txt_cfop', '0', '1234567890.,', 'CFOP', '2')) {
        return false
    }
//Natureza Operação
    if(document.form.txt_natureza_operacao.value == '') {
        alert('DIGITE A NATUREZA DE OPERAÇÃO !')
        document.form.txt_natureza_operacao.focus()
        return false
    }
}
</Script>
<body onload='controlar_duplicatas()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_cfop' value='<?=$_GET['id_cfop'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar CFOP
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ICMS:</b>
        </td>
        <td>
            <b>IPI:</b>
        </td>
        <td>
            CFOP:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_icms' title="Selecione o ICMS" class='combo'>
                <option value='' style="color:red">SELECIONE</option>
                <?
                    if($campos[0]['icms'] == 1) {
                        $selected_icms1 = 'selected';
                    }else if($campos[0]['icms'] == 2) {
                        $selected_icms2 = 'selected';
                    }else if($campos[0]['icms'] == 3) {
                        $selected_icms3 = 'selected';
                    }
                ?>
                <option value="1" <?=$selected_icms1;?>>TRIBUTAÇÃO NORMAL</option>
                <option value="2" <?=$selected_icms2;?>>ISENTO</option>
                <option value="3" <?=$selected_icms3;?>>DIGITAR NF</option>
            </select>
        </td>
        <td>
            <select name="cmb_ipi" title="Selecione o IPI" class='combo'>
                <option value="" style="color:red">SELECIONE</option>
                <?
                    if($campos[0]['ipi'] == 1) {
                        $selected_ipi1 = 'selected';
                    }else if($campos[0]['ipi'] == 2) {
                        $selected_ipi2 = 'selected';
                    }else if($campos[0]['ipi'] == 3) {
                        $selected_ipi3 = 'selected';
                    }
                ?>
                <option value="1" <?=$selected_ipi1;?>>TRIBUTAÇÃO NORMAL</option>
                <option value="2" <?=$selected_ipi2;?>>ISENTO</option>
                <option value="3" <?=$selected_ipi3;?>>DIGITAR NF</option>
            </select>
        </td>
        <?
            if(!empty($campos[0]['cfop'])) {
                $cfop = $campos[0]['cfop'].'.'.$campos[0]['num_cfop'];
            }else {
                $cfop = '';
            }
        ?>
        <td>
            <input type='text' name="txt_cfop" value="<?=$cfop;?>" size="20" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font title="Essa CFOP é como se fosse extensão da Principal, p/ evitar seleção de 2 CFOPS na NF">
                CFOP de Revenda:
            </font>
        </td>
        <?
            $checked = ($campos[0]['cfop_nf_venda'] == 'S') ? 'checked' : '';
        ?>
        <td colspan='2'>
            <input type='checkbox' name='chkt_nf_venda' title='Utilizada em NF de Venda e Devolução de Venda' id='nf_venda' onclick='controlar_duplicatas()' class='checkbox' <?=$checked;?>>
            <label for='nf_venda'>Utilizada em NF de Venda e Devolução de Venda</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <select name='cmb_cfop_revenda' title="Selecione a CFOP Revenda" class='combo'>
            <?
                $sql = "SELECT id_cfop, CONCAT(cfop, '.', num_cfop, ' - ', natureza_operacao_resumida) AS cfop 
                        FROM `cfops` 
                        WHERE (`id_cfop_revenda` = '0' AND `ativo` = '1') ORDER BY cfop ";
                echo combos::combo($sql, $campos[0]['id_cfop_revenda']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>Natureza Operação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name="txt_natureza_operacao" title="Digite a Natureza de Operação" maxlength='255' cols='85' rows='3' class='caixadetexto'><?=$campos[0]['natureza_operacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Natureza de Operação Resumida:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name="txt_natureza_operacao_resumida" title="Digite a Natureza de Operação Resumida" maxlength='150' cols='50' rows='3' class='caixadetexto'><?=$campos[0]['natureza_operacao_resumida'];?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Descrição / Obs (Texto da Nota):
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name='txt_descricao_obs' title='Digite a Descrição / Obs (Texto da Nota)' maxlength='255' cols='85' rows='3' class='caixadetexto'><?=$campos[0]['descricao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
        <?
            if(empty($_GET['pop_up'])) {//Tela foi aberta de forma normal ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Limpar' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }else {//Tela aberta como Pop-UP ...
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//CFOP ...
    $achou_ponto    = 0;
    $num_cfop       = '';
    if(!empty($_POST['txt_cfop'])) {//Caixa Preenchida ...
        for($i = 0; $i < strlen($_POST['txt_cfop']); $i++) {
            if(substr($_POST['txt_cfop'], $i, 1) == '.') {
                $achou_ponto++;
            }else {
                if($achou_ponto == 0) {//Parte antes do número
                    $cfop.= substr($_POST['txt_cfop'], $i, 1);
                }else {
                    $num_cfop.= substr($_POST['txt_cfop'], $i, 1);
                }
            }
        }
    }
    $nf_venda = (!empty($_POST['chkt_nf_venda'])) ? 'S' : 'N';
	
    $sql = "UPDATE `cfops` SET `id_cfop_revenda` = '$_POST[cmb_cfop_revenda]', `cfop` = '$cfop', `num_cfop` = '$num_cfop', `natureza_operacao` = '$_POST[txt_natureza_operacao]', `natureza_operacao_resumida` = '$_POST[txt_natureza_operacao_resumida]', `icms` = '$_POST[cmb_icms]', `ipi` = '$_POST[cmb_ipi]', `descricao` = '$_POST[txt_descricao_obs]', `cfop_nf_venda` = '$nf_venda' WHERE `id_cfop` = '$_POST[id_cfop]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<body onLoad='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar CFOP
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" onclick='document.form.txt_consultar.focus()' id='cfop' title="Consultar cfop por: CFOP" checked>
            <label for='cfop'>CFOP</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' id="todos" value='1' title="Consultar todas as CFOPs" class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>