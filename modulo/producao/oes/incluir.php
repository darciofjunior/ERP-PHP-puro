<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OE N.º <font class='erro'>".$id_oe."</font> INCLUIDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>OE(S) GERADA(S) EM LOTE COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>NÃO FOI POSSÍVEL GERAR OE EM LOTE.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_consultar      = $_POST['txt_consultar'];
        $opt_opcao          = $_POST['opt_opcao'];
        $opcao              = $_POST['opcao'];
        $chkt_gerar_em_lote = $_POST['chkt_gerar_em_lote'];
    }else {
        $txt_consultar      = $_GET['txt_consultar'];
        $opt_opcao          = $_GET['opt_opcao'];
        $opcao              = $_GET['opcao'];
        $chkt_gerar_em_lote = $_GET['chkt_gerar_em_lote'];
    }
    /*******************************************************************/
    if($chkt_gerar_em_lote == 'S') exit(header('Location: gerar_oe_em_lote.php'));
    /*******************************************************************/
    require('../../../lib/menu/menu.php');//Está dando conflito com algum comando, por isso coloquei aqui em baixo ...
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, ed.razaosocial, gpa.nome 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div and pa.referencia like '%$txt_consultar%' 
                    INNER JOIN grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed on ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.ativo = '1' 
                    AND (pa.operacao < '9' AND pa.operacao_custo < '9') ORDER BY pa.discriminacao ";
        break;
        case 2:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, ed.razaosocial, gpa.nome 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div and pa.discriminacao like '%$txt_consultar%' 
                    INNER JOIN grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed on ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.ativo = '1' 
                    AND (pa.operacao < '9' AND pa.operacao_custo < '9') ORDER BY pa.discriminacao ";
        break;
        case 3:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, ed.razaosocial, gpa.nome 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa and gpa.nome like '%$txt_consultar%' 
                    INNER JOIN empresas_divisoes ed on ed.id_empresa_divisao = ged.id_empresa_divisao and ed.razaosocial like '%$txt_consultar%' 
                    WHERE pa.ativo = '1' 
                    AND (pa.operacao < '9' AND pa.operacao_custo < '9') ORDER BY pa.discriminacao ";
        break;
        default:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, ed.razaosocial, gpa.nome 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed on ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.ativo = '1' 
                    AND (pa.operacao < '9' AND pa.operacao_custo < '9') ORDER BY pa.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Consultar Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Grupo P.A. / Empresa Divisão
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = '../../classes/produtos_acabados/gerar_oe.php?id_produto_acabado='.$campos[$i]['id_produto_acabado']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}else {
    require('../../../lib/menu/menu.php');//Está dando conflito com algum comando, por isso coloquei aqui em baixo ...
?>
<html>
<head>
<title>.:: Incluir OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true || document.form.chkt_gerar_em_lote.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled 	= true
        document.form.txt_consultar.className 	= 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled 	= false
        document.form.txt_consultar.className 	= 'caixadetexto'
        document.form.txt_consultar.focus()
    }
    document.form.txt_consultar.value 		= ''
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
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Nova OE
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Acabados por: Referência' id='label' checked>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Acabados por: Discriminação' id='label2'>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Acabados por: Grupo P.A. / Empresa Divisão' id='label3'>
            <label for='label3'>
                Grupo P.A. / Empresa Divisão
            </labe>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' onclick='limpar()' title='Consultar Todos os registros' id='label4' class='checkbox'>
            <label for='label4'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_gerar_em_lote' value='S' onclick='limpar()' title='Gerar OE em Lote' id='label5' class='checkbox'>
            <label for='label5'>
                <font color='red'>
                    <b>GERAR OE EM LOTE</b>
                </font>
            </label>
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