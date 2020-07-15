<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) FUNCIONÁRIO(S) PARA O CHEFE DIGITADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PREVISÃO DE FÉRIAS ALTERADA COM SUCESSO.</font>";

if($passo == 1) {
//Atualizando a Previsão de Férias para os Funcionários ...
    foreach($_POST['hdd_funcionario'] as $i => $hdd_funcionario) {
//Controle referente ao campo Programação de Férias ...
        $programacao_ferias = $_POST['cmb_ano'][$i].'-'.$_POST['cmb_mes'][$i].'-00';
/******************************************************************************************/
//Atualizando os dados na Tabela de Funcionários ...
        $sql = "UPDATE `funcionarios` SET `programacao_ferias` = '$programacao_ferias' WHERE `id_funcionario` = '$hdd_funcionario' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'imprimir_relatorio.php?txt_chefe=<?=$_POST['txt_chefe'];?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório de Previsão de Férias ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Chefe ...
    var selecionados = 0
    
    for(var i = 1; i < document.getElementById('cmb_chefe').length; i++) {
        if(document.getElementById('cmb_chefe')[i].selected == true) {
            selecionados++;
            break;//P/ sair fora do Loop ...
        } 
    }

    if(selecionados == 0) {
        alert('SELECIONE UM CHEFE !')
        document.getElementById('cmb_chefe').focus()
        return false
    }
}

function salvar() {
    var elementos = document.form.elements

    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }

    for(i = 0; i < linhas; i++) {
/**************************Programação de Férias**************************/
//Forço a preencher o Ano quando estiver preenchido o Mês ...
        if(document.getElementById('cmb_mes'+i).value != '' && document.getElementById('cmb_ano'+i).value == '') {
            alert('SELECIONE UM ANO PARA PROGRAMAÇÃO DE FÉRIAS !')
            document.getElementById('cmb_ano'+i).focus()
            return false
        }
//Forço a preencher o Mês quando estiver preenchido o Ano ...
        if(document.getElementById('cmb_mes'+i).value == '' && document.getElementById('cmb_ano'+i).value != '') {
            alert('SELECIONE UM MÊS PARA PROGRAMAÇÃO DE FÉRIAS !')
            document.getElementById('cmb_mes'+i).focus()
            return false
        }
    }
    document.form.passo.value = 1
}
</Script>
</head>
<body onload='document.form.txt_chefe.focus()'>
<form name='form' method='post'>
<input type='hidden' name='passo'>
<table width='90%' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relatório de Previsão de Férias
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='7'>
            Chefe: 
            <select name='cmb_chefe[]' id='cmb_chefe' title='Selecione um Chefe' class='combo' size='5' multiple>
            <?
                /*Nessa relação, eu só listo os Funcionários que são Chefes ou Superiores conforme BD e 
                que ainda trabalham aqui na Empresa "Férias 0, Ativo 1 ou Afastado 2" ...*/
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `status_superior` = '1' 
                        AND `status` <= '2' 
                        ORDER BY `nome` ";
                echo combos::combo($sql, $_POST['cmb_chefe']);
            ?>
            </select>
            &nbsp;-
            <input type='checkbox' name='chkt_somente_impressao' id='chkt_somente_impressao' value='1' title='Somente Impressão' class='checkbox' <?=$checked;?>>
            <label for='chkt_somente_impressao'>
                Somente Impressão
            </label>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='return validar()' class='botao'>
        </td>
    </tr>
<?
    if(!empty($_POST['cmb_chefe'])) {//Significa que o usuário já consultou um Diretor ...
        $id_chefes = implode(',', $_POST['cmb_chefe']);
/*Verifico se existe pelo menos 1 funcionário que é subordinado ao chefe digitado pelo usuário, mas somente 
os que possuem Registro em Carteira, sendo assim não busco os funcionários da Empresa do Grupo*/
        $sql = "SELECT c.`cargo`, d.`departamento`, f.`id_funcionario`, f.`nome`, f.`data_admissao`, 
                f.`data_prox_ferias`, f.`data_max_ferias`, f.`programacao_ferias` 
                FROM `funcionarios` f 
                INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                WHERE f.`status` < '3' 
                AND f.`id_funcionario_superior` IN ($id_chefes) 
                AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
                AND f.`id_empresa` <> '4' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Não existe nenhum funcionário ...
?>
    <tr><td></td></tr>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
        <!--Oculto o Botão de Imprimir, afinal o filtro não retornou nenhum Funcionário do chefe digitado pelo usuário...-->
        <Script Language = 'JavaScript'>
            parent.document.getElementById('linha_imprimir').style.visibility = 'hidden'
        </Script>
<?
        }else {//Existe pelo menos 1 funcionário então ...
            $indice             = 0;
            
            //Só não listo funcionários da Empresa Grupo porque estes não possuem registro assinado em Carteira ...
            $vetor_empresas     = array(1, 2);//Albafer e Tool Master ...
            $linhas_empresas    = count($vetor_empresas);
            
//Listo de Loop de Empresas dos Funcionários referente ao chefe digitado de cada Empresa do Loop ...
            for($i = 0; $i < $linhas_empresas; $i++) {
/*Listagem de Funcionários independente da Empresa, que ainda estão trabalhando
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
                $sql = "SELECT c.`cargo`, d.`departamento`, f.`id_funcionario`, f.`nome`, f.`data_admissao`, 
                        f.`data_prox_ferias`, f.`data_max_ferias`, f.`programacao_ferias` 
                        FROM `funcionarios` f 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                        WHERE f.`id_empresa` = '".$vetor_empresas[$i]."' 
                        AND f.`status` < '3' 
                        AND f.`id_funcionario_superior` IN ($id_chefes) 
                        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
//Se existir pelo menos 1 funcionário na Empresa Corrente então eu listo a Empresa ...
                if($linhas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <font color='yellow'>
                Empresa: 
            </font>
            <?=genericas::nome_empresa($vetor_empresas[$i]);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Cargo
        </td>
        <td>
            Departamento
        </td>
        <td>
            Data de <br/>Admissão
        </td>
        <td>
            Venc. <br/>a Gozar
        </td>
        <td>
            Venc. Max. <br/>a Gozar
        </td>
        <td>
            Programação
        </td>
    </tr>
<?
                    for($j = 0; $j < $linhas; $j++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <!--Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" 
            da sessão e o parâmetro pop_up significa que está tela está sendo aberta como pop_up e sendo assim 
            é para não exibir o botão de Voltar que existe nessa tela ...-->
            <a href = '../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=<?=$campos[$j]['id_funcionario'];?>&pop_up=1' class='html5lightbox'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?=$campos[$j]['nome'];?>
                </font>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$j]['cargo'];?>
        </td>
        <td align='left'>
            <?=$campos[$j]['departamento'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$j]['data_admissao'], '/');?>
        </td>
        <td>
        <?
/*Se a Data Atual já for maior do que o Vencimento das Próximas férias, então eu printo esta linha em 
vermelho para dizer que estas férias já venceu ...*/
            if(date('Y-m-d') > $campos[$j]['data_prox_ferias']) echo '<font color="red">';
            echo data::datetodata($campos[$j]['data_prox_ferias'], '/');
        ?>
        </td>
        <td>
        <?
                        if($campos[$j]['data_max_ferias'] != '0000-00-00') echo data::datetodata($campos[$j]['data_max_ferias'], '/');
        ?>
        </td>
        <td>
        <?
/*Se o usuário marcou a opção de 'Somente Impressão' então eu exibo essas caixinhas para que o usuário 
possa estar fazendo alguma anotação a mão depois de impresso ...*/
                        if(!empty($chkt_somente_impressao)) {
        ?>
            <input type='text' name='txt_programacao[]' id='txt_programacao<?=$indice;?>' title='Digite a Programação' class='textdisabled' disabled>
        <?
/*Significa que o usuário não marcou esta opção então significa que a pessoa responsável pelo Depto. Pessoal 
deseja estar alterando os dados de Previsão de Férias do funcionário em Lote ...*/
                        }else {
                            $programacao_ferias = $campos[$j]['programacao_ferias'];
        ?>
            Mês: 
            <select name='cmb_mes[]' id='cmb_mes<?=$indice;?>' title='Selecione o Mês' class='combo'>
                <option value = '' selected style='color:red'>SELECIONE</option>
                <?
                            $mes_programacao_ferias = substr($programacao_ferias, 5, 2);
//Criei esse vetor aqui porque achei + facil, pra listagem dos Meses no Banco de Dados ...
                            $vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
                            for($mes = 1; $mes < count($vetor_meses); $mes++) {
//Se o mês selecionado for igual ao mês do Loop ...
                                if($mes_programacao_ferias == $mes) {
                ?>
                <option value='<?=$mes;?>' selected><?=$vetor_meses[$mes];?></option>
                <?
                                }else {
                ?>
                <option value='<?=$mes;?>'><?=$vetor_meses[$mes];?></option>
                <?
                                }
                            }
                ?>
                </select>
                Ano: 
                <select name='cmb_ano[]' id='cmb_ano<?=$indice;?>' title='Selecione o Ano' class='combo'>
                    <option value = '' selected style='color:red'>SELECIONE</option>
                    <?
                            $ano_programacao_ferias = substr($programacao_ferias, 0, 4);
                            for($ano = date('Y'); $ano < (date('Y') + 10); $ano++) {
//Se o ano selecionado for igual ao ano do Loop ...
                                if($ano_programacao_ferias == $ano) {
                    ?>
                    <option value='<?=$ano;?>' selected><?=$ano;?></option>
                    <?
                                }else {
                    ?>
                    <option value='<?=$ano;?>'><?=$ano;?></option>
                    <?
                                }
                            }
                    ?>
                </select>
                <input type='hidden' name='hdd_funcionario[]' id='hdd_funcionario<?=$indice;?>' value='<?=$campos[$j]['id_funcionario'];?>'>
<?
                        }
?>
        </td>
    </tr>
<?
                        $indice++;
                    }
                }
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
        <?
//Se o usuário marcou a opção de Somente Impressão marcada então só exibo esse botão p/ Atualizar a Tela ...
            if(!empty($chkt_somente_impressao)) {
        ?>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' style='color:darkblue' onclick='document.form.submit()' class='botao'>
        <?
            }
/*Se o usuário não marcou a opção de "Somente Impressão" então eu exibo o botão Salvar para que ele possa
gravar as modificações de Previsão de Férias ...*/
            if(empty($chkt_somente_impressao)) {
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_chefe.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='return salvar()' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<!--Apresento o Botão de Imprimir p/ que o Usuário Imprima a Listagem caso desejar ...-->
<Script Language = 'JavaScript'>
    parent.document.getElementById('linha_imprimir').style.visibility = 'visible'
</Script>
<?
        }
    }
?>
</form>
</body>
</html>
<?}?>