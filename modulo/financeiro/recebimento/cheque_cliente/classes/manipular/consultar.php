<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=1';
    $endereco_volta = 'opcoes.php?id_emp2=1';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=2';
    $endereco_volta = 'opcoes.php?id_emp2=2';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=4';
    $endereco_volta = 'opcoes.php?id_emp2=4';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`num_cheque` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 2:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`correntista` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 3:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`valor` LIKE '$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 4:
            $txt_consultar = data::datatodate($txt_consultar, '-');
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`data_vencimento` LIKE '$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 5:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` AND c.`razaosocial` LIKE '%$txt_consultar%' 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        default:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
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
<title>.:: Consultar Cheque(s) de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Cheque(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Cheque
        </td>
        <td>
            Banco
        </td>
        <td>
            Cliente
        </td>
        <td>
            Correntista
        </td>
        <td>
            Valor
        </td>
        <td>
            Condição
        </td>
        <td>
            Data Venc.
        </td>
    </tr>
<?
        //Vetor de Status de Cheque ...
        $vetor_status[0] = '<font title="Cancelado" style="cursor:help">Cancelado</font>';
        $vetor_status[1] = '<font title="A compensar" style="cursor:help">A comp</font>';
        $vetor_status[2] = '<font title="Concluido / Compensar" style="cursor:help">Conc / Comp</font>';
        $vetor_status[3] = '<font title="Devolvido" style="cursor:help">Devolvido</font>';

        for($i = 0; $i < $linhas; $i++) {
            $url    = 'detalhes.php?id_cheque_cliente='.$campos[$i]['id_cheque_cliente'];
            //Se o Cheque for Devolvido, então tem que apresentar o número do cheque em Vermelho ...
            $color  = ($campos[$i]['status'] == 3) ? 'red' : '';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <font color='<?=$color;?>'>
                    <?=$campos[$i]['num_cheque'];?>
                </font>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
        <?
            echo $vetor_status[$campos[$i]['status']];
            if($campos[$i]['predatado'] == 1) echo '<font title="(Pré-Datado)" style="cursor:help"> <b>(Pré-Dat)</b></font>';
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
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
<title>.:: Consultar Cheque(s) de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 5; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 5;i ++) document.form.opt_opcao[i].disabled = false
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) de Cliente 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Cheque por Número do Cheque' onclick='document.form.txt_consultar.focus()' id='opt1' checked>
            <label for='opt1'>Número do Cheque</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Cheque por Correntista' onclick='document.form.txt_consultar.focus()' id='opt2'>
            <label for='opt2'>Correntista</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Cheque por Valor' onclick='document.form.txt_consultar.focus()' id='opt3'>
            <label for='opt3'>Valor</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Consultar Cheque por Data de Vencimento' onclick='document.form.txt_consultar.focus()' id='opt4'>
            <label for='opt4'>Data de Vencimento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value="5" title="Consultar Cheque por Cliente" onclick='document.form.txt_consultar.focus()' id='opt5'>
            <label for='opt5'>Cliente</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Cheques' onclick='limpar()' id='todos' class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt' title='Voltar' onclick="window.location = '<?=$endereco_volta;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>