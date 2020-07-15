<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/custos.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>MÁQUINA ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>MÁQUINA JÁ EXISTENTE.</font>";

$horas_efetivamente_trabalhadas_mes = genericas::variavel(86);

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `maquinas` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY nome ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `maquinas` 
                    WHERE `ativo` = '1' ORDER BY nome ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 500, 'nao', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar / Alterar Máquinas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Consultar / Alterar Máquina(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Máquina
        </td>
        <td>
            Valor
        </td>
        <td>
            Máq. por Func.
        </td>
        <td>
            Anos p/ Amort.
        </td>
        <td>
            Porc. Ferr.
        </td>
        <td>
            Setup
        </td>
        <td>
            Sal. Médio Máq.
        </td>
        <td>
            Custo Hora Máq.
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="window.location = 'alterar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>'" align='center'>
        <td bgcolor='#D8D8D8' width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td bgcolor='#D8D8D8' align='left'>
            <a href='alterar.php?passo=2&id_maquina=<?=$campos[$i]['id_maquina'];?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td bgcolor='#D8D8D8' align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8'>
            <?=number_format($campos[$i]['qtde_maq_vs_func'], 2, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8'>
            <?=number_format($campos[$i]['duracao'], 2, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8'>
            <?=number_format($campos[$i]['porc_ferramental'], 2, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8'>
            <?=number_format($campos[$i]['setup'], 1, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8' align='right'>
            <?='R$ '.number_format($campos[$i]['salario_medio'], 2, ',', '.');?>
        </td>
        <td bgcolor='#D8D8D8' align='right'>
            <?='R$ '.number_format($campos[$i]['custo_h_maquina'], 2, ',', '.');?>
        </td>
    </tr>
<?
//Aqui traz todos os funcionários que estão relacionados a está máquina aqui do loop ...
            $sql = "SELECT f.nome, f.tipo_salario, f.salario_pd, f.salario_pf, f.salario_premio, f.status 
                    FROM `maquinas_vs_funcionarios` mf 
                    INNER JOIN `funcionarios` f ON f.id_funcionario = mf.id_funcionario 
                    WHERE mf.id_maquina = '".$campos[$i]['id_maquina']."' ORDER BY f.nome ";
            $campos_maquinas_funcionarios = bancos::sql($sql);
            $linhas_maquinas_funcionarios = count($campos_maquinas_funcionarios);
            if($linhas_maquinas_funcionarios > 0) {
                for($j = 0; $j < $linhas_maquinas_funcionarios; $j++) {
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>NOME: </b>
            <?
                echo $campos_maquinas_funcionarios[$j]['nome'];
//Aki mostra a Situação do Funcionário
                if($campos_maquinas_funcionarios[$j]['status'] == 0) {
                    echo " <font color='blue'>(Férias)</font>";
                }else if($campos_maquinas_funcionarios[$j]['status'] == 1) {
                    echo " <font color='blue'>(Ativo)</font>";
                }else if($campos_maquinas_funcionarios[$j]['status'] == 2) {
                    echo " <font color='blue'>(Demissionário)</font>";
                }else if($campos_maquinas_funcionarios[$j]['status'] == 3) {
                    echo " <font color='red'>(Demitido)</font>";
                }
            ?>
        </td>
        <td colspan='3' align='left'>
            <b>TIPO DE SALÁRIO: </b>
            <?
                if($campos_maquinas_funcionarios[$j]['tipo_salario'] == 1) {
                    echo 'HORISTA';
                }else if($campos_maquinas_funcionarios[$j]['tipo_salario'] == 2) {
                    echo 'MENSALISTA';
                }
            ?>
        </td>
        <td colspan='3'>
            <b>SALÁRIO: </b>
            <?
                if($campos_maquinas_funcionarios[$j]['tipo_salario'] == 1) {//Horista ...
                    $salario_funcionario = ($campos_maquinas_funcionarios[$j]['salario_pd'] + $campos_maquinas_funcionarios[$j]['salario_pf'] + $campos_maquinas_funcionarios[$j]['salario_premio']) * 220 / $horas_efetivamente_trabalhadas_mes;
                }else {//Mensalista ...
                    $salario_funcionario = ($campos_maquinas_funcionarios[$j]['salario_pd'] + $campos_maquinas_funcionarios[$j]['salario_pf'] + $campos_maquinas_funcionarios[$j]['salario_premio']) / $horas_efetivamente_trabalhadas_mes;
                }
                echo 'R$ '.number_format($salario_funcionario, 2, ',', '.');
            ?>
        </td>
    </tr>
<?
                }
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
}elseif($passo == 2) {
    //Desatrelo os Funcionários da Máquina ...
    if(!empty($_POST['id_maquina_vs_funcionario'])) {
        $sql = "DELETE 
                FROM `maquinas_vs_funcionarios` 
                WHERE `id_maquina_vs_funcionario` = '$_POST[id_maquina_vs_funcionario]' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Procedimento normal quando se Carrega a Tela ...
    $id_maquina = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_maquina'] : $_GET['id_maquina'];
    
    custos::custos_hora_maquina($id_maquina);//Função que recalcula a hora custo do funcionário ...

    //Seleção dos Dados da Máquina passada por parâmetro ...
    $sql = "SELECT * 
            FROM `maquinas` 
            WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $maquina        = (!empty($_POST['txt_maquina'])) ? $_POST['txt_maquina'] : $campos[0]['nome'];
    $valor_maquina  = (!empty($_POST['txt_valor'])) ? number_format($_POST['txt_valor'], 2, ',', '.') : number_format($campos[0]['valor'], 2, ',', '.');
    $qtde_maq_func  = (!empty($_POST['txt_qtde_maq_func'])) ? number_format($_POST['txt_qtde_maq_func'], 2, ',', '.') : number_format($campos[0]['qtde_maq_vs_func'], 2, ',', '.');
    $duracao        = (!empty($_POST['txt_duracao'])) ? number_format($_POST['txt_duracao'], 2, ',', '.') : number_format($campos[0]['duracao'], 2, ',', '.');
    $porc_ferramental = (!empty($_POST['txt_porc_ferramental'])) ? str_replace('.', ',', $_POST['txt_porc_ferramental']) : str_replace('.', ',', $campos[0]['porc_ferramental']);
    $setup          = (!empty($_POST['txt_setup'])) ? number_format($_POST['txt_setup'], 1, ',', '.') : number_format($campos[0]['setup'], 1, ',', '.');
    $caracteristica = (!empty($_POST['txt_caracteristica'])) ? $_POST['txt_caracteristica'] : $campos[0]['caracteristica'];
    $observacao     = (!empty($_POST['txt_observacao'])) ? $_POST['txt_observacao'] : $campos[0]['observacao'];
    $salario_medio  = 'R$ '.number_format($campos[0]['salario_medio'], 2, ',', '.');
    $custo_h_maquina = 'R$ '.number_format($campos[0]['custo_h_maquina'], 2, ',', '.');

    //Essas variáveis serão utilizadas mais abaixo ...
    $dias_trab_mes          = 'R$ '.number_format(genericas::variavel(13), 2, ',', '.');
    $horas_trab_dia         = 'R$ '.number_format(genericas::variavel(14), 2, ',', '.');
    $aumento_sal_provisorio = number_format(genericas::variavel(15), 2, ',', '.').' %';
?>
<html>
<title>.:: Consultar / Alterar Máquinas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Máquina ...
    if(!texto('form', 'txt_maquina', '1', "abcdefghijkçÇlmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ÁÉÍÓÚáéíóúãõÃÕàÀ '1234567890", 'MÁQUINA', '1')) {
        return false
    }
//Valor R$ ...
    if(!texto('form', 'txt_valor', '1', '1234567890.,', 'VALOR R$', '2')) {
        return false
    }
//Qtde de Máq. por Func ...
    if(!texto('form', 'txt_qtde_maq_func', '1', '1234567890.,', 'QUANTIDADE DE MÁQUINA POR FUNCIONÁRIO', '1')) {
        return false
    }
//Anos p/ Amortização ...
    if(!texto('form', 'txt_duracao', '1', '1234567890.,', 'ANO P/ AMORTIZAÇÃO', '2')) {
        return false
    }
//Porc Ferramental ...
    if(!texto('form', 'txt_porc_ferramental', '1', '1234567890.,', 'PORCENTAGEM FERRAMENTAL', '1')) {
        return false
    }
//Setup ...
    if(!texto('form', 'txt_setup', '1', '1234567890.,', 'SETUP', '2')) {
        return false
    }
    document.form.passo.value = 3
    tratar_campos()
}

//Exclusão de Funcionários
function excluir_item(valor) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        tratar_campos()
        document.form.passo.value = 2
        document.form.id_maquina_vs_funcionario.value = valor
        document.form.submit()
    }
}

//Controle do Pop-Up
function submeter() {
    tratar_campos()
    document.form.passo.value = 2
    document.form.submit()
}

//Função que trata os campos em um formato de BD p/ não dar erro na hora de gravar no Mysql ...
function tratar_campos() {
    limpeza_moeda('form', 'txt_valor, txt_qtde_maq_func, txt_duracao, txt_porc_ferramental, txt_setup, ')
}
</script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_maquina' value='<?=$id_maquina;?>'>
<input type='hidden' name='id_maquina_vs_funcionario'>
<input type='hidden' name='passo' onclick='submeter()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar / Alterar Máquina
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>Máquina:</b>
        </td>
        <td>
            <input type='text' name='txt_maquina' value='<?=$maquina;?>' title='Digite a Máquina' size='50' maxlength='80' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor R$: </b>
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=$valor_maquina;?>' title='Digite o Valor R$' size='15' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Máquina por Funcionário:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_maq_func' value='<?=$qtde_maq_func;?>' title='Digite a Quantidade de Máquina por Funcionário' size='7' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>&nbsp;&nbsp;Quantidade
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Anos p/ Amortização:</b>
        </td>
        <td>
            <input type='text' name='txt_duracao' value='<?=$duracao;?>' title='Digite os Anos p/ Amortização' size='7' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Porcentagem Ferramental:</b>
        </td>
        <td>
            <input type='text' name='txt_porc_ferramental' value='<?=$porc_ferramental;?>' title='Digite a Porcentagem Ferramental' size='10' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Setup:</b>
        </td>
        <td>
            <input type='text' name='txt_setup' value='<?=$setup;?>' title='Digite o Setup' size='10' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '1', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <font color='blue'>
                <b>Variáveis</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dias Trabalhado por mês:</b>
            <?=$dias_trab_mes;?>
        </td>
        <td>
            <b>Horas Trabalhado por dia:</b>
            <?=$horas_trab_dia;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Aumento salarial provisório:</b>
            <?=$aumento_sal_provisorio;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Salário Médio desta Máquina</b>
            </font>
        </td>
        <td>
            <font color='red'>
                <b>Custo da hora Máquina</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$salario_medio;?>
        </td>
        <td>
            <?=$custo_h_maquina;?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <a href = 'incluir_funcionario.php?id_maquina=<?=$id_maquina;?>' class='html5lightbox'>
                <font color='#FFFF00'>
                    <b><i>Atrelar Funcionário(s) / Operador(es) à Máquina</i></b>
                </font>
            </a>
        </td>
    </tr>
<?
//Aqui traz todos os funcionários que estão relacionado a máquina com a qual estou aqui nesse momento ...
    $sql = "SELECT f.`nome`, f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, f.`salario_premio`, 
            f.`status`, mf.`id_maquina_vs_funcionario` 
            FROM `maquinas_vs_funcionarios` mf 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = mf.`id_funcionario` 
            WHERE mf.`id_maquina` = '$id_maquina' ORDER BY f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Nome</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Tipo de Salário</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Salário</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
    <?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='left'>
        <?
            echo $campos[$i]['nome'];
//Aki mostra a Situação do Funcionário
            if($campos[$i]['status'] == 0) {
                echo " <font color='blue'>(Férias)</font>";
            }else if($campos[$i]['status'] == 1) {
                echo " <font color='blue'>(Ativo)</font>";
            }else if($campos[$i]['status'] == 2) {
                echo " <font color='blue'>(Demissionário)</font>";
            }else if($campos[$i]['status'] == 3) {
                echo " <font color='red'>(Demitido)</font>";
            }
        ?>
      </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_salario'] == 1) {
                echo 'HORISTA';
            }else if($campos[$i]['tipo_salario'] == 2) {
                echo 'MENSALISTA';
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Horista ...
                $salario_funcionario = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio']) * 220 / $horas_efetivamente_trabalhadas_mes;
            }else {//Mensalista ...
                $salario_funcionario = ($campos[$i]['salario_pd'] + $campos[$i]['salario_pf'] + $campos[$i]['salario_premio']) / $horas_efetivamente_trabalhadas_mes;
            }
            echo 'R$ '.number_format($salario_funcionario, 2, ',', '.');
        ?>
        </td>
        <td align='center'>
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_maquina_vs_funcionario'];?>')">
        </td>
    </tr>
<?
        }
    }
?>
</table>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td width='30%'>
            Característica:
        </td>
        <td>
            <textarea name='txt_caracteristica' title='Digite a Característica' cols='50' rows='2' maxlength='100' class='caixadetexto'><?=$caracteristica;?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a Observação" cols='40' rows='2' maxlength='80' class='caixadetexto'><?=$observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php?passo=1'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_maquina.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<pre>
    <font color='red'><b>Fórmula:</b></font>
    <font color='blue'>salario_medio_fun</font> =
        <b>- se horista</b>-> pego a média de ((sal_pd + sal_pf + premio) * 220 / <?=number_format($horas_efetivamente_trabalhadas_mes, 1, ',', '.');?>)
        <b>- se mensalista</b>-> pego a média (sal_pd + sal_pf + premio) / <?=number_format($horas_efetivamente_trabalhadas_mes, 1, ',', '.');?>
	<font color='blue'>Sal_media_maq</font> = (aumento_sal_provisorio / 100 + 1) * salario_medio_func / qtde_maq_vs_func
	Divisao	= anos_amortizacao * 12 * dias_trab_mes * horas_trab_dia
	- Se a divisão der um resultado de zero forço ele a dividir por 1
	<font color='blue'>Custo_hora_maq</font> = round((valor_maquina / (divisao) * (1 + porc_ferramental / 100)) + sal_media_maq * <?=number_format(genericas::variavel(63), 1, ',', '.');?>)

        <b><?=number_format(genericas::variavel(63), 1, ',', '.')?> = São os Encargos Sociais sobre Funcionários.</b>

	<font color='green'><b>Custo da hora máquina:</b></font>
	Este custo é acionado somente quando sofre alterações:
	 - No cadastro de máquinas.
	 - No atrelamento de funcionario para está máquina.
	 - No cadastro de funcionarios como (alteração de salário ou excclusão do mesmo).
	 - No controle de váriaveis como (Dias/Horas tralhados e aumento salarial provisório).
</pre>
</body>
</html>
<?
}else if($passo == 3) {
    //Verifico se existe uma Máquina cadastrada com esse nome, diferente da que está sendo alterada no momento ...
    $sql = "SELECT id_maquina 
            FROM `maquinas` 
            WHERE `nome` = '$_POST[txt_maquina]' 
            AND `id_maquina` <> '$_POST[id_maquina]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe, pode alterar normalmente ...
        $sql = "UPDATE `maquinas` SET `nome` = '$_POST[txt_maquina]', `valor` = '$_POST[txt_valor]', `qtde_maq_vs_func` = '$_POST[txt_qtde_maq_func]', `duracao` = '$_POST[txt_duracao]', `porc_ferramental` = '$_POST[txt_porc_ferramental]', `setup` = '$_POST[txt_setup]', `caracteristica` = '$_POST[txt_caracteristica]', `observacao` = '$_POST[txt_observacao]' WHERE `id_maquina` = '$_POST[id_maquina]' LIMIT 1 ";
        bancos::sql($sql);
        custos::custos_hora_maquina($_POST['id_maquina']);//Função que recalcula a hora custo do funcionário ...
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?passo=2&id_maquina=<?=$id_maquina;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<title>.:: Consultar / Alterar Máquina(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar / Alterar Máquina(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label' value='1' title='Consultar máquinas por: Máquina' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label'>Máquina</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='label2' value='2' title='Consultar todos as máquinas' onclick='limpar()' class='checkbox'>
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