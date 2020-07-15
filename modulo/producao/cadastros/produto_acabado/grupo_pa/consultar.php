<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT gpa.*, cf.`classific_fiscal`, f.`nome` AS familia 
                    FROM `grupos_pas` gpa 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE gpa.`nome` LIKE '%$txt_consultar%' 
                    AND gpa.`ativo` = '1' ORDER BY gpa.`nome` ";
        break;
        default:
            $sql = "SELECT gpa.*, cf.`classific_fiscal`, f.`nome` AS familia 
                    FROM `grupos_pas` gpa 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE gpa.`ativo` = '1' ORDER BY gpa.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Grupo P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Grupo P.A.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Família
        </td>
        <td>
            Lote Mín.<br/>Prod.
        </td>
        <td>
            Prazo de Entrega
        </td>
        <td>
            Classificação<br/>Fiscal
        </td>
        <td>
            Tolerância
        </td>			
        <td>
            Observação
        </td>
    </tr>
<?
        $vetor_prazos_entrega = vendas::prazos_entrega();

        for ($i = 0; $i < $linhas; $i++) {
            $url = 'detalhes.php?id_grupo_pa='.$campos[$i]['id_grupo_pa'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' class='html5lightbox'>
            <?
                $sql = "SELECT SUBSTRING(ed.`razaosocial`, 1, 1) AS nome 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                        WHERE ged.`id_grupo_pa` = ".$campos[$i]['id_grupo_pa'];
                $campos_ed = bancos::sql($sql);
                $linhas_ed = count($campos_ed);
                $ed = ' ( ';
                for ($j = 0; $j < $linhas_ed; $j++) $ed.= $campos_ed[$j]['nome'].'/ ';
                $ed = substr($ed, 0, (strlen($ed) - 2));
                $ed.= ' )';
                echo $campos[$i]['nome'].$ed;
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['familia'];?>
        </td>
        <td align="right">
            <?='R$ '.number_format($campos[$i]['lote_min_producao_reais'], 2, ',', '.');?>
        </td>
        <td>
        <?
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                //Compara o valor do Banco com o valor do Vetor antes do Loop ...
                //Se igual seleciona esse valor
                if($campos[$i]['prazo_entrega'] == $indice) echo $prazo_entrega;
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['tolerancia'];?>
        </td>			
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
?>
<html>
<head>
<title>.:: Consultar Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
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
            Consultar Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label' value='1' title='Consultar Grupos P.A. por: Grupo P.A.' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label'>Grupo P.A.</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='label2' value='1' title='Consultar todos os Grupos P.A.' onclick='limpar()' class='checkbox'>
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