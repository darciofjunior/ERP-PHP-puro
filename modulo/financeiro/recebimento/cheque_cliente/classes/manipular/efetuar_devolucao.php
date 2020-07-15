<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');
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
$mensagem[2] = "<font class='confirmacao'>DEVOLUÇÃO DE CHEQUE EFETUADO COM SUCESSO.</font>";

if($passo == 1) {
    //Independente da opção de Filtro que foi selecionada pelo o Usuário, só mostro cheques "Compensados" ...
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`num_cheque` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`status` = '2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 2:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`correntista` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`status` = '2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 3:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`valor` LIKE '$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`status` = '2' 
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
                    AND cc.`status` = '2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        case 5:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` AND c.`razaosocial` LIKE '%$txt_consultar%' 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`status` = '2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
        default:
            $sql = "SELECT cc.*, c.`razaosocial` 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`status` = '2' 
                    AND cc.`ativo` = '1' 
                    GROUP BY cc.`num_cheque` ORDER BY cc.`data_vencimento` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'efetuar_devolucao.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Efetuar Devolução de Cheque de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Efetuar Devolução de Cheque de Cliente
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
            Correntista
        </td>
        <td>
            Valor
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data de Vencimento
        </td>
        <td>
            <img src = '../../../../../../imagem/propriedades.png' width='16' height='16' border='0'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "efetuar_devolucao.php?passo=2&id_cheque_cliente=".$campos[$i]['id_cheque_cliente'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if(substr($campos[$i]['data_sys'], 0, 10) != '0000-00-00') echo data::datetodata($campos[$i]['data_sys'], '/');
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td align='center'>
            <img src = '../../../../../../imagem/propriedades.png' onclick="nova_janela('detalhes.php?id_cheque_cliente=<?=$campos[$i]['id_cheque_cliente'];?>', 'CONSULTA_DETALHADA', '', '', '', '', 450, 800, 'c', 'c')" title='Propriedades' width='16' height='16' border='0'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'efetuar_devolucao.php'" class='botao'>
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
    //Aqui eu trago dados do Cheque do Cliente passado por parâmetro ...
    $sql = "SELECT * 
            FROM `cheques_clientes` 
            WHERE `id_cheque_cliente` = '$_GET[id_cheque_cliente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Efetuar Devolução de Cheque de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var resposta = confirm('DESEJA REALMENTE EFETUAR A DEVOLUÇÃO DE CHEQUE ?')
    if(resposta == false) return false
}
</Script>
</head>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_cheque_cliente' value='<?=$_GET['id_cheque_cliente'];?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Efetuar Devolução de Cheque de Cliente
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Número do Cheque:</b>
        </td>
        <td colspan='4'>
                <?=$campos[0]['num_cheque'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Banco:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['banco'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Correntista:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['correntista'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Valor:</b>
        </td>
        <td colspan='4'>
            <?='R$ '.number_format($campos[0]['valor'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Data de Emissão:</b>
        </td>
        <td colspan='4'>
        <?
            if($campos[0]['data_sys'] == '0000-00-00') {
                echo '&nbsp;';
            }else {
                echo data::datetodata($campos[0]['data_sys'], '/');
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Data de Vencimento:</b>
        </td>
        <td colspan='4'>
        <?
            if($campos[0]['data_vencimento'] == '0000-00-00') {
                echo '&nbsp;';
            }else {
                echo data::datetodata($campos[0]['data_vencimento'], '/');
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Tipo de Cobrança:</b>
        </td>
        <td colspan='4'>
        <?
            if($campos[0]['tipo_cobranca'] == 0) {
                echo 'Carteira';
            }else {
                echo 'Cobrança Bancária';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'><b>Condição:</b></td>
        <td colspan='4'>
        <?
            $texto = array('Cancelado', 'A compensar', 'Concluído / Compensado', 'Devolvido');
            echo $texto[$campos[0]['status']];
            //Aqui é para o caso de ser predatado ...
            if($campos[0]['predatado'] == 1) echo ' / Pré-datado';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Histórico:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['historico'];?>
        </td>
    </tr>
<?
    $sql = "SELECT c.razaosocial, cr.`id_conta_receber`, cr.`descricao_conta`, cr.`num_conta`, cr.`semana`, cr.`valor`, cr.`valor_pago`, tm.`simbolo` 
            FROM `cheques_clientes` cc 
            INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
            INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` 
            INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
            WHERE cc.`id_cheque_cliente` = '$_GET[id_cheque_cliente]' 
            AND (cc.`status` = '1' OR cc.`status` = '2') ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Contas Recebidas
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Semana
        </td>
        <td>
            N.º / Conta
        </td>
        <td>
            Cliente / Descrição
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Valor Reajustado
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
        <td>
            <a href = '../../../alterar.php?pop_up=1&id_conta_receber=<?=$campos[$i]['id_conta_receber'];?>' title='Detalhes' class='html5lightbox'>
                <?=$campos[$i]['num_conta'];?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            if($campos[$i]['descricao_conta'] != '') echo $campos[$i]['descricao_conta'];
        ?>
        </td>
        <td align='right'>
            <?=$campos[$i]['simbolo'].' '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$campos[$i]['simbolo'].' '.number_format($campos[$i]['valor_pago'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'efetuar_devolucao.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    financeiros::cheque_devolvido($_POST['id_cheque_cliente']);
?>
    <Script Language = 'JavaScript'>
        window.location = 'efetuar_devolucao.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Efetuar Devolução de Cheque(s) de Cliente ::.</title>
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
            Efetuar Devolução de Cheque(s) de Cliente 
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