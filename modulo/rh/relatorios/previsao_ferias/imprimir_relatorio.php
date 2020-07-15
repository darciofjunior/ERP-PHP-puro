<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) FUNCION�RIO(S) PARA O CHEFE DIGITADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PREVIS�O DE F�RIAS ALTERADA COM SUCESSO.</font>";

if($passo == 1) {
//Atualizando a Previs�o de F�rias para os Funcion�rios ...
    foreach($_POST['hdd_funcionario'] as $i => $hdd_funcionario) {
//Controle referente ao campo Programa��o de F�rias ...
        $programacao_ferias = $_POST['cmb_ano'][$i].'-'.$_POST['cmb_mes'][$i].'-00';
/******************************************************************************************/
//Atualizando os dados na Tabela de Funcion�rios ...
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
<title>.:: Relat�rio de Previs�o de F�rias ::.</title>
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
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }

    for(i = 0; i < linhas; i++) {
/**************************Programa��o de F�rias**************************/
//For�o a preencher o Ano quando estiver preenchido o M�s ...
        if(document.getElementById('cmb_mes'+i).value != '' && document.getElementById('cmb_ano'+i).value == '') {
            alert('SELECIONE UM ANO PARA PROGRAMA��O DE F�RIAS !')
            document.getElementById('cmb_ano'+i).focus()
            return false
        }
//For�o a preencher o M�s quando estiver preenchido o Ano ...
        if(document.getElementById('cmb_mes'+i).value == '' && document.getElementById('cmb_ano'+i).value != '') {
            alert('SELECIONE UM M�S PARA PROGRAMA��O DE F�RIAS !')
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
            Relat�rio de Previs�o de F�rias
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='7'>
            Chefe: 
            <select name='cmb_chefe[]' id='cmb_chefe' title='Selecione um Chefe' class='combo' size='5' multiple>
            <?
                /*Nessa rela��o, eu s� listo os Funcion�rios que s�o Chefes ou Superiores conforme BD e 
                que ainda trabalham aqui na Empresa "F�rias 0, Ativo 1 ou Afastado 2" ...*/
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `status_superior` = '1' 
                        AND `status` <= '2' 
                        ORDER BY `nome` ";
                echo combos::combo($sql, $_POST['cmb_chefe']);
            ?>
            </select>
            &nbsp;-
            <input type='checkbox' name='chkt_somente_impressao' id='chkt_somente_impressao' value='1' title='Somente Impress�o' class='checkbox' <?=$checked;?>>
            <label for='chkt_somente_impressao'>
                Somente Impress�o
            </label>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='return validar()' class='botao'>
        </td>
    </tr>
<?
    if(!empty($_POST['cmb_chefe'])) {//Significa que o usu�rio j� consultou um Diretor ...
        $id_chefes = implode(',', $_POST['cmb_chefe']);
/*Verifico se existe pelo menos 1 funcion�rio que � subordinado ao chefe digitado pelo usu�rio, mas somente 
os que possuem Registro em Carteira, sendo assim n�o busco os funcion�rios da Empresa do Grupo*/
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
        if(count($campos) == 0) {//N�o existe nenhum funcion�rio ...
?>
    <tr><td></td></tr>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
        <!--Oculto o Bot�o de Imprimir, afinal o filtro n�o retornou nenhum Funcion�rio do chefe digitado pelo usu�rio...-->
        <Script Language = 'JavaScript'>
            parent.document.getElementById('linha_imprimir').style.visibility = 'hidden'
        </Script>
<?
        }else {//Existe pelo menos 1 funcion�rio ent�o ...
            $indice             = 0;
            
            //S� n�o listo funcion�rios da Empresa Grupo porque estes n�o possuem registro assinado em Carteira ...
            $vetor_empresas     = array(1, 2);//Albafer e Tool Master ...
            $linhas_empresas    = count($vetor_empresas);
            
//Listo de Loop de Empresas dos Funcion�rios referente ao chefe digitado de cada Empresa do Loop ...
            for($i = 0; $i < $linhas_empresas; $i++) {
/*Listagem de Funcion�rios independente da Empresa, que ainda est�o trabalhando
/*S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
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
//Se existir pelo menos 1 funcion�rio na Empresa Corrente ent�o eu listo a Empresa ...
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
            Funcion�rio
        </td>
        <td>
            Cargo
        </td>
        <td>
            Departamento
        </td>
        <td>
            Data de <br/>Admiss�o
        </td>
        <td>
            Venc. <br/>a Gozar
        </td>
        <td>
            Venc. Max. <br/>a Gozar
        </td>
        <td>
            Programa��o
        </td>
    </tr>
<?
                    for($j = 0; $j < $linhas; $j++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <!--Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" 
            da sess�o e o par�metro pop_up significa que est� tela est� sendo aberta como pop_up e sendo assim 
            � para n�o exibir o bot�o de Voltar que existe nessa tela ...-->
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
/*Se a Data Atual j� for maior do que o Vencimento das Pr�ximas f�rias, ent�o eu printo esta linha em 
vermelho para dizer que estas f�rias j� venceu ...*/
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
/*Se o usu�rio marcou a op��o de 'Somente Impress�o' ent�o eu exibo essas caixinhas para que o usu�rio 
possa estar fazendo alguma anota��o a m�o depois de impresso ...*/
                        if(!empty($chkt_somente_impressao)) {
        ?>
            <input type='text' name='txt_programacao[]' id='txt_programacao<?=$indice;?>' title='Digite a Programa��o' class='textdisabled' disabled>
        <?
/*Significa que o usu�rio n�o marcou esta op��o ent�o significa que a pessoa respons�vel pelo Depto. Pessoal 
deseja estar alterando os dados de Previs�o de F�rias do funcion�rio em Lote ...*/
                        }else {
                            $programacao_ferias = $campos[$j]['programacao_ferias'];
        ?>
            M�s: 
            <select name='cmb_mes[]' id='cmb_mes<?=$indice;?>' title='Selecione o M�s' class='combo'>
                <option value = '' selected style='color:red'>SELECIONE</option>
                <?
                            $mes_programacao_ferias = substr($programacao_ferias, 5, 2);
//Criei esse vetor aqui porque achei + facil, pra listagem dos Meses no Banco de Dados ...
                            $vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Mar�o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
                            for($mes = 1; $mes < count($vetor_meses); $mes++) {
//Se o m�s selecionado for igual ao m�s do Loop ...
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
//Se o usu�rio marcou a op��o de Somente Impress�o marcada ent�o s� exibo esse bot�o p/ Atualizar a Tela ...
            if(!empty($chkt_somente_impressao)) {
        ?>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' style='color:darkblue' onclick='document.form.submit()' class='botao'>
        <?
            }
/*Se o usu�rio n�o marcou a op��o de "Somente Impress�o" ent�o eu exibo o bot�o Salvar para que ele possa
gravar as modifica��es de Previs�o de F�rias ...*/
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
<!--Apresento o Bot�o de Imprimir p/ que o Usu�rio Imprima a Listagem caso desejar ...-->
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