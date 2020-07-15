<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ATRASO / FALTA INCLUIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>DATA DE OCORRÊNCIA INVÁLIDA.</font>";

if($passo == 1) {
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas e menos do cargo AUTONÔMO*/
    $sql = "SELECT DISTINCT (f.id_funcionario), f.id_funcionario_superior, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
            INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.`id_cargo` <> '82' 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`status` < '3' ORDER BY f.nome ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Funcionário(s) p/ Incluir Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Funcionário(s) p/ Incluir Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Chefe
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
            $url = "incluir.php?passo=2&id_funcionario_loop=".$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='center' onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" title="Visualizar Detalhes" class='link'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
        <?
//Busca do Nome do Chefe do Funcionário ...
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
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
?>
<html>
<head>
<title>.:: Incluir Atraso / Falta / Saída ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Ocorrência ...
    if(!data('form', 'txt_data_ocorrencia', "4000", 'OCORRÊNCIA')) {
        return false
    }
//Horário da Ocorrência
    if(document.form.txt_horario_ocorrencia.disabled == false) {
        if(!texto('form', 'txt_horario_ocorrencia', '1', '1234567890:', 'HORÁRIO OCORRÊNCIA', '2')) {
            return false
        }
    }
    var elementos = document.form.elements
    var option = 0
//Aqui eu verifico se tem alguma opção selecionada ...
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].checked == true && elementos[i].name == 'opt_motivo') {
            option ++
        }
    }
//Se não tiver nenhuma opção selecionada então forço o usuário a selecionar uma opção ...
    if(option == 0) {
        alert('SELECIONE UM MOTIVO P/ GERAR O ATESTADO DE ATRASO / FALTA / SAÍDA !')
        document.form.opt_motivo[0].focus()
        return false
    }
//Observação ...
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
//Verifica se a Observação está incompleta ...
    if(document.form.txt_observacao.value.length < 5) {
        alert('OBSERVAÇÃO INCOMPLETA !')
        document.form.txt_observacao.focus()
        return false
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function controlar_objetos(valor) {
    if(valor == 3) {//Somente quando for falta q eu travo o horário e habilito a opção de Descontar no PLR ...
        document.form.txt_horario_ocorrencia.value = ''
        document.form.txt_horario_ocorrencia.disabled = true
//Layout de Desabilitado ...
        document.form.txt_horario_ocorrencia.className = 'textdisabled'
//Move o foco p/ a observação ...
        document.form.txt_observacao.focus()
    }else {//Demais opções funcionará normalmente ...
        document.form.txt_horario_ocorrencia.disabled = false
//Layout de Habilitado ...
        document.form.txt_horario_ocorrencia.className = 'caixadetexto'
//Move o foco p/ o Horário da Ocorrência ...
        document.form.txt_horario_ocorrencia.focus()
    }
}
</Script>
</head>
<body onload="document.form.txt_data_ocorrencia.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<!--Coloquei esse nome p/ não dar conflito com a variável id_funcionario da Sessão-->
<input type='hidden' name='hdd_funcionario' value="<?=$_GET['id_funcionario_loop'];?>">
<table border="0" width="60%" align='center' cellspacing ='1' cellpadding='1'>
    <tr class="atencao" align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='15%'>
            Funcionário:
        </td>
        <td width='45%'>
        <?
            $sql = "SELECT nome, id_funcionario_superior 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $id_funcionario_superior = $campos[0]['id_funcionario_superior'];
            echo $campos[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Chefe:</b>
        </td>
        <td>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$id_funcionario_superior' LIMIT 1 ";
            $campos_chefe = bancos::sql($sql);
            echo $campos_chefe[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Porteiro / Emissor:</b>
            </font>
        </td>
        <td colspan='2'>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
            $campos_porteiro_emissor = bancos::sql($sql);
            echo $campos_porteiro_emissor[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Ocorrência:</b>
        </td>
        <td>
            <input type="text" name="txt_data_ocorrencia" value="<?=date('d/m/Y');?>" title="Digite a Data de Comparecimento" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_ocorrencia&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Horário da Ocorrência:</b>
        </td>
        <td>
            <input type="text" name="txt_horario_ocorrencia" value="<?=date('H:i');?>" title="Digite o Horário da Ocorrência" onkeyup="verifica(this, 'hora', '', '', event)" size="8" maxlength="5" class="caixadetexto">
            &nbsp;-
            <input type='checkbox' name='chkt_sem_cracha' value='S' title='Sem Crachá' id="sem_cracha" class="checkbox">
            <label for="sem_cracha">
                Sem Crachá
            </label>
            <?
                $data_30_dias_atras = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -30), '-');
                /*Verifico quantas vezes que o Funcionário no qual está sendo feito a Ocorrência veio sem Crachá 
                nos últimos 30 dias ...*/
                $sql = "SELECT id_funcionario_acompanhamento 
                        FROM `funcionarios_acompanhamentos` 
                        WHERE `id_funcionario_acompanhado` = '$_GET[id_funcionario_loop]' 
                        AND SUBSTRING(`data_ocorrencia`, 1, 10) >= '$data_30_dias_atras' 
                        AND `sem_cracha` = 'S' 
                        GROUP BY SUBSTRING(`data_ocorrencia`, 1, 10) ";
                $campos_dias_sem_cracha = bancos::sql($sql);
                if(count($campos_dias_sem_cracha) > 0) echo ' <font color="red"><b> - '.count($campos_dias_sem_cracha).' DIA(S) SEM CRACHÁ NOS ÚLTIMOS 30 DIAS</b></font>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Motivo:</b>
        </td>
        <td>
            <input type='radio' name='opt_motivo' value='0' onclick='controlar_objetos(1)' id='label1'>
            <label for='label1'>Entrada</label>
            &nbsp;
            <input type='radio' name='opt_motivo' value='1' onclick='controlar_objetos(2)' id='label2'>
            <label for='label2'>Saída</label>
            &nbsp;
            <?
                /*Aqui está sendo feita algumas restrições em alguns usuários por causa que estão lançando falta e isso está 
                atrapalhando no RH, o exemplo do funcionário abaixo é o Márcio e o Mauro ...*/
                if($_SESSION['id_funcionario'] == 95 || $_SESSION['id_funcionario'] == 156) $disabled = 'disabled';
            ?>
            <input type='radio' name='opt_motivo' value='2' onclick='controlar_objetos(3)' id='label3' <?=$disabled;?>>
            <label for='label3'>Falta</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='button' name="cmd_limpar" value="Limpar" title="Limpar" class='botao' style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data_ocorrencia.focus()">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* O funcionário que não comunicar as faltas ou atrasos, ficará passível das punições previstas na CLT.
</pre>
<?
}else if($passo == 3) {
    if($_POST['txt_data_ocorrencia'] == '00/00/0000') {//Caso a Data de Ocorrência seja vazia não permite incluir registro ...
        $valor = 3;
    }else {//Caso a Data esteja preenchida de forma correta, então registra a ocorrência ...
        //Tratamento p/ não furar mais abaixo ...
        if(!empty($_POST['txt_horario_ocorrencia'])) {
            $data_ocorrencia = data::datatodate($_POST['txt_data_ocorrencia'], '-').' '.$_POST['txt_horario_ocorrencia'].date(':s');
        }else {//No caso de Falta, o campo de Horas não é preenchido, daí tem esse tratamento ...
            $data_ocorrencia = data::datatodate($_POST['txt_data_ocorrencia'], '-').' 00:00:00';
        }
        //Busca do Nome do Porteiro / Emissor ou quem gerou o registro p/ visualização em Relatório ...
        $sql = "SELECT nome 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $nome   = $campos[0]['nome'];
        //Controle com o Sem Crachá ...
        if(!empty($_POST['chkt_sem_cracha'])) {
            $sem_cracha                 = 'S';
            $status_andamento            = 1;
            $observacao_complementar    = '<br/><b>Chefia: </b>Liberação automática pelo ERP.';
        }else {
            $sem_cracha                 = 'N';
            $status_andamento            = 0;
        }
        //Inserindo na Base de Dados ...
        $sql = "INSERT INTO `funcionarios_acompanhamentos` (`id_funcionario_acompanhamento`, `id_funcionario_registrou`, `id_funcionario_acompanhado`, `observacao`, `data_ocorrencia`, `motivo`, `status_andamento`, `sem_cracha`, `registro_portaria`) VALUES (NULL, '$_SESSION[id_funcionario]', '".$_POST['hdd_funcionario']."', '".$_POST['txt_observacao'].' <b>('.$nome.')</b>'.$observacao_complementar."', '$data_ocorrencia', '".$_POST['opt_motivo']."', '$status_andamento', '$sem_cracha', 'S') ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) p/ Incluir Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onLoad="document.form.txt_nome.focus()">
<form name="form" method="post" action="<?=$GLOBALS['PHP_SELF'].'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="60%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s) p/ Incluir Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type="text" name="txt_nome" title="Digite o Nome" size="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_nome.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>