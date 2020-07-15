<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($cmb_opcao_entrada != '') $condicao_entrada = " AND `acao` = '$cmb_opcao_entrada' ";

if($passo == 1) {
//Tratamento com a variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_produto_acabado         = $_POST['id_produto_acabado'];
        $pop_up                     = $_POST['pop_up'];
        $txt_referencia             = $_POST['txt_referencia'];
        $txt_discriminacao          = $_POST['txt_discriminacao'];
        $txt_grupo_pa               = $_POST['txt_grupo_pa'];
        $txt_observacao             = $_POST['txt_observacao'];
        if(!empty($_POST['txt_data_inicial'])) {
            $txt_data_inicial_usa   = data::datatodate($_POST['txt_data_inicial'], '-');
            $txt_data_final_usa     = data::datatodate($_POST['txt_data_final'], '-');
        }else {
            $txt_data_inicial_usa   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), - 180), '-');
            $txt_data_final_usa     = date('Y-m-d');
        }
        $chkt_mostrar_componentes   = $_POST['chkt_mostrar_componentes'];
    }else {
        $id_produto_acabado         = $_GET['id_produto_acabado'];
        $pop_up                     = $_GET['pop_up'];
        $txt_referencia             = $_GET['txt_referencia'];
        $txt_discriminacao          = $_GET['txt_discriminacao'];
        $txt_grupo_pa               = $_GET['txt_grupo_pa'];
        $txt_observacao             = $_GET['txt_observacao'];
        if(!empty($_GET['txt_data_inicial'])) {
            $txt_data_inicial_usa   = data::datatodate($_GET['txt_data_inicial'], '-');
            $txt_data_final_usa     = data::datatodate($_GET['txt_data_final'], '-');
        }else {
            $txt_data_inicial_usa   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), - 180), '-');
            $txt_data_final_usa     = date('Y-m-d');
        }
        $chkt_mostrar_componentes   = $_GET['chkt_mostrar_componentes'];
    }
    $condicao_datas     = " AND SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' ";
    $txt_data_inicial   = data::datetodata($txt_data_inicial_usa, '/');
    $txt_data_final     = data::datetodata($txt_data_final_usa, '/');

    if($veio_compras == 1) {//Significa que essa Tela foi acessada do Módulo de Compras ...
        $checked_mostrar_componentes    = 'checked';//Só mostra P.A. do Tipo Componentes ...
        $disabled_mostrar_componentes   = 'disabled';//Sempre travo o checkbox ...
        //Em compras tem uma diferença, ou só traz componentes ou tudo que for diferente de componentes ...
        $condicao = (!empty($chkt_mostrar_componentes)) ? " AND gpa.`id_familia` = '23' " : " AND gpa.`id_familia` <> '23' ";
    }else {//Tela acessada do Módulo de Vendas ...
        $checked_mostrar_componentes    = (!empty($chkt_mostrar_componentes)) ? 'checked' : '';
        $disabled_mostrar_componentes   = '';//Em vendas sempre esse checkbox será habilitado ...
        //Em vendas só traz todos os PAs ou tudo que for diferente de componentes ...
        $condicao = (!empty($chkt_mostrar_componentes)) ? '' : " AND gpa.`id_familia` <> '23' ";
    }
        
    if($id_produto_acabado > 0) {//Se foi passado o $id_produto_acabado, trago dados somente desse PA ...
        $sql = "SELECT u.sigla, bmp.*, bmp.observacao AS observacao_manipulacao, bmp.data_sys AS data_lancamento, pa.id_produto_acabado, f.nome 
                FROM `baixas_manipulacoes_pas` bmp 
                LEFT JOIN `funcionarios` f ON f.id_funcionario = bmp.id_funcionario 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = bmp.id_produto_acabado AND pa.`ativo` = '1' 
                INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` fm ON fm.id_familia = gpa.id_familia 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
                $condicao_entrada $condicao_datas ORDER BY bmp.data_sys DESC, pa.discriminacao ";
    }else {//Traz dados de acordo com o Filtro pelo usuário na Tela Anterior ...
        $sql = "SELECT u.sigla, bmp.*, bmp.observacao AS observacao_manipulacao, bmp.data_sys AS data_lancamento, pa.id_produto_acabado, f.nome 
                FROM `baixas_manipulacoes_pas` bmp 
                LEFT JOIN `funcionarios` f ON f.id_funcionario = bmp.id_funcionario 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = bmp.id_produto_acabado AND pa.`ativo` = '1' AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%".str_replace(' ', '%', $txt_discriminacao)."%' 
                INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`nome` LIKE '%$txt_grupo_pa%' $condicao 
                INNER JOIN `familias` fm ON fm.id_familia = gpa.id_familia 
                WHERE bmp.`observacao` LIKE '%$txt_observacao%' 
                $condicao_entrada $condicao_datas ORDER BY bmp.data_sys DESC, pa.discriminacao ";
    }
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Movimentação do Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function verificar_datas() {
    if(document.form.txt_data_inicial.value == '' && document.form.txt_data_final.value != '') {
        alert('DIGITE A DATA INICIAL !')
        document.form.txt_data_inicial.focus()
        return false
    }
    if(document.form.txt_data_inicial.value != '' && document.form.txt_data_final.value == '') {
        alert('DIGITE A DATA FINAL !')
        document.form.txt_data_final.focus()
        return false
    }
    data_inicial = document.form.txt_data_inicial.value
    data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.replace('/', '')
    data_inicial_sem_formatacao = data_inicial.replace('/', '')

    data_final = data_final.replace('/', '')
    data_final_sem_formatacao = data_final.replace('/', '')

    data_inicial_invertida = data_inicial_sem_formatacao.substr(4, 4)+data_inicial_sem_formatacao.substr(2, 2)+data_inicial_sem_formatacao.substr(0, 2)
    data_final_invertida = data_final_sem_formatacao.substr(4, 4)+data_final_sem_formatacao.substr(2, 2)+data_final_sem_formatacao.substr(0, 2)

    data_inicial_invertida = eval(data_inicial_invertida)
    data_final_invertida = eval(data_final_invertida)

    if(data_inicial_invertida > data_final_invertida) {
        alert('DATAS INVÁLIDAS !')
        document.form.txt_data_inicial.focus()
        return false
    }
    //Desabilita o Checkbox p/ poder levá-lo como parâmetro ...
    document.form.chkt_mostrar_componentes.disabled = false
    var mostrar_componentes = (document.form.chkt_mostrar_componentes.checked) ? 1 : 0
    //Redirecionando ...
    window.location = 'consultar.php<?=$parametro;?>&txt_data_inicial='+document.form.txt_data_inicial.value+'&txt_data_final='+document.form.txt_data_final.value+'&chkt_mostrar_componentes='+mostrar_componentes
}

function alterar_opcao_entrada(id_baixa_manipulacao_pa) {
    var resposta = confirm('VOCÊ DESEJA MUDAR ESTA OPÇÃO DE ENTRADA PARA MANIPULAÇÃO DO ESTOQUE ?')
    if(resposta == true) {
        window.location = 'consultar.php?passo=2&id_baixa_manipulacao_pa='+id_baixa_manipulacao_pa
    }else {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_data_inicial.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class="linhacabecalho" align='center'>
        <td colspan='11'>
            Relatório de Movimentação do Estoque
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='11'>
            <b>Data Inicial:</b>
            <input type='text' name='txt_data_inicial' value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            at&eacute;<b> </b>
            <input type='text' name='txt_data_final' value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <b>Data Final</b>
            &nbsp;-
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title='Mostrar Componentes' id='mostrar_componentes' class='checkbox' <?=$checked_mostrar_componentes;?> <?=$disabled_mostrar_componentes;?>>
            <label for='mostrar_componentes'>Mostrar Componentes</label>
            &nbsp;
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='verificar_datas()' class='botao'>
        </td>
    </tr>
<?
    if($linhas == 0) {
        if($pop_up == 1) {//Significa então que essa Tela foi aberta como sendo Pop-Up ...
?>	
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
            exit;
        }else {//Significa então que essa Tela foi aberta como sendo normal ...
?>
            <Script Language = 'Javascript'>
                window.location = 'consultar.php?valor=1'
            </Script>
<?
        }
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>Produto</b>
        </td>
        <td>
            <b>Qtde<br> Estoque</b>
        </td>
        <td>
            <b>Qtde<br> Produção</b>
        </td>
        <td>
            <b>Retirada em</b>
        </td>
        <td>
            <b>Funcionário</b>
        </td>
        <td>
            <b>N.º OC /
            <font color='yellow'>
                <br>Cliente</b>
            </font>
        </td>
        <td>
            <b>N.º OP / OE</b>
        </td>
        <td>
            <b>Observação</b>
        </td>
        <td>
            <b>Ação</b>
        </td>
        <td title='Tipo de Manipulação' style='cursor:help'>
            <b>Tipo</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['qtde_producao'], 2, ',', '.');?>
        </td>
        <td>
        <?
            //Esse campo será utilizado mais abaixo ...
            $data_baixa_manipulacao = substr($campos[$i]['data_lancamento'], 0, 10);
            echo data::datetodata($data_baixa_manipulacao, '/');
        ?>
        </td>
        <td align='left'>
            <font color="darkblue">
                <b>Estoquista:</b>
            </font>
            <?
                $sql = "SELECT nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = ".$campos[$i]['id_funcionario']." LIMIT 1 ";
                $campos_func = bancos::sql($sql);
                echo $campos_func[0]['nome'];
            ?>
            <font color="darkblue">
                <br><b>Solicitado por:</b>
            </font>
            <?
                $sql = "SELECT `nome` AS nome_solicitador 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_retirado']." LIMIT 1 ";
                $campos_func = bancos::sql($sql);
                echo $campos_func[0]['nome_solicitador'];
            ?>
            <font color="darkblue">
                <br><b>Retirado por:</b>
            </font>
            <?=$campos[$i]['retirado_por'];?>
        </td>
        <td>
        <?
            $sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente 
                    FROM `clientes` 
                    WHERE `id_cliente` = ".$campos[$i]['id_cliente']." LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
        ?>
            <font title="Cliente: <?=$campos_cliente[0]['cliente'];?>" style="cursor:help">
                <?=$campos[$i]['numero_oc'];?>
            </font>
        </td>
        <td align='left'>
        <?
/*Aqui eu busco os dados na Tabela de Baixa_de_Ops através do campo id_baixa_manipulacao*/
/*************************Modo Novo*************************/
            $sql = "SELECT `id_op` 
                    FROM `baixas_ops_vs_pas` 
                    WHERE `id_baixa_manipulacao_pa` = ".$campos[$i]['id_baixa_manipulacao_pa']." 
                    AND SUBSTRING(`data_sys`, 1, 10) = '$data_baixa_manipulacao' ORDER BY id_op ";
            $campos_ops = bancos::sql($sql);
            $linhas_ops = count($campos_ops);
            if($linhas_ops == 0) {
/*Aqui eu busco os dados na Tabela de Baixa_de_Ops através do campo id_produto_acabado*/
/*************************Modo Velho*************************/
                $sql = "SELECT id_op 
                        FROM `baixas_ops_vs_pas` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND SUBSTRING(`data_sys`, 1, 10) = '$data_baixa_manipulacao' 
                        AND `id_baixa_manipulacao_pa` = '0' ORDER BY id_op ";
                $campos_ops = bancos::sql($sql);
                $linhas_ops = count($campos_ops);
            }
            $id_ops = '';//Sempre limpa a variável p/ não herdar valores do Loop anterior ...
            if($linhas_ops > 0) {//Se existir OPs então apresento as mesmas ...
                for($j = 0; $j < $linhas_ops; $j++) {
                    if(($j + 1) == $linhas_ops) {//Se for o último registro não quebra a linha. 
                        $quebra_linha = '';
                    }else {//Do contrário vai quebrando a linha ...
                        $quebra_linha = '<br> ';
                    }
        ?>
                    <a href = '../../../producao/ops/alterar.php?passo=1&id_op=<?=$campos_ops[$j]['id_op'].'&pop_up=1';?>' class='html5lightbox'>
                        OP <?=$campos_ops[$j]['id_op'];?>
                    </a>
        <?
                    echo $quebra_linha;
                }
            }else {//Podemos ter uma O.E então ... 
                if($campos[$i]['id_oe'] > 0) {
        ?>
                <a href = '../../../producao/oes/alterar.php?passo=1&id_oe=<?=$campos[$i]['id_oe'].'&pop_up=1';?>' class='html5lightbox'>
                    OE <?=$campos[$i]['id_oe'];?>
                </a>
        <?
                }
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['acao'] == 'B') {
                echo '<font color="darkblue"><b>Baixa</b></font>';
            }else if($campos[$i]['acao'] == 'E') {
//Só apresenta link para os usuários do Dárcio e do Roberto
                if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22) {//id_login do Dárcio e Roberto
        ?>
                    <a href="javascript:alterar_opcao_entrada('<?=$campos[$i]['id_baixa_manipulacao_pa'];?>')" title='Entrada de Produção' class='link'>
                        <font color="#ff9900"><b>Entrada de Produção</b></font>
                    </a>
        <?
//Aqui nesse caso só printa normalmente
                }else {
                        echo '<font color="#ff9900"><b>Entrada de Produção</b></font>';
                }
            }else if($campos[$i]['acao'] == 'I') {
                echo '<font color="green"><b>Inventário</b></font>';
            }else if($campos[$i]['acao'] == 'M') {
                echo '<font color="darkgreen"><b>Manipulação</b></font>';
            }else if($campos[$i]['acao'] == 'O') {
                echo '<font color="#ff33ff"><b>OC</b></font>';
            }else if($campos[$i]['acao'] == 'P') {
                echo '<font color="brown"><b>OP Nova</b></font>';
            }else if($campos[$i]['acao'] == 'R') {
                echo '<font color="blue"><b>Refugo</b></font>';
            }else if($campos[$i]['acao'] == 'S') {
                echo '<font color="red"><b>Estorno de Baixa</b></font>';
            }else if($campos[$i]['acao'] == 'U') {
                echo '<font color="gray"><b>Uso p/ Fábrica</b></font>';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_manipulacao'] == 0) {
                echo '<font title="MANIPULAÇÃO NORMAL" style="cursor:help"><b>N</b></font>';
            }else if($campos[$i]['tipo_manipulacao'] == 1) {
                echo '<font title="MANIPULAÇÃO P/ SUBSTITUIÇÃO" style="cursor:help"><b>S</b></font>';
            }else if($campos[$i]['tipo_manipulacao'] == 2) {
                echo '<font title="MANIPULAÇÃO P/ SUBSTITUIÇÃO COM ORDEM DE EMBALAGEM" style="cursor:help"><b>S.O.E.</b></font>';
            }else if($campos[$i]['tipo_manipulacao'] == 3) {
                echo '<font title="MANIPULAÇÃO P/ MONTAGEM DE JOGOS" style="cursor:help"><b>M.J.</b></font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <?
// Significa então que essa Tela foi aberta como sendo Pop-Up e por isso que não mostro o Botão Consultar Novamente ...
                if($pop_up == 1) {
            ?>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
            <?		
                }else {
            ?>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $sql = "UPDATE `baixas_manipulacoes_pas` SET `acao` = 'M' WHERE `id_baixa_manipulacao_pa` = '$id_baixa_manipulacao_pa' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php<?=$parametro;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório de Movimentação do Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onLoad='document.form.txt_referencia.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Movimentação do Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' maxlength='15' size='18' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' maxlength='42' size='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A.
        </td>
        <td>
            <input type='text' name='txt_grupo_pa' title='Digite o Grupo P.A.' maxlength='20' size='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' maxlength='55' size='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Opção de Entrada
        </td>
        <td>
            <select name='cmb_opcao_entrada' title='Opção de Entrada' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='B'>BAIXA DO ESTOQUE</option>
                <option value='E'>ENTRADA DE PRODUÇÃO</option>
                <option value='S'>ESTORNO DE BAIXA</option>
                <option value='I'>INVENTÁRIO</option>
                <option value='M'>MANIPULAÇÃO DO ESTOQUE</option>
                <option value='O'>OC</option>
                <option value='P'>OP NOVA</option>
                <option value='R'>REFUGO</option>
                <option value='U'>USO P/ FÁBRICA</option>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
        <?
            $txt_data_inicial = data::adicionar_data_hora(date('d/m/Y'), - 180);
            $txt_data_final = date('d/m/Y');
        ?>
            Data Inicial: <input type='text' name="txt_data_inicial" value="<?=$txt_data_inicial;?>" title="Digite a Data de Recebimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../imagem/calendario.gif' width="12" height="12" border="0" alt='Calend&aacute;rio Normal' style='cursor:hand' onClick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">  até&nbsp; 
            <input type='text' name="txt_data_final" value="<?=$txt_data_final;?>" title="Digite a Data de Recebimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../imagem/calendario.gif' width="12" height="12" border="0" alt='Calend&aacute;rio Normal' style='cursor:hand' onClick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Data Final
        </td>
    </tr>
<?
//Aqui está sendo acessado do Mód. de Vendas, então pode mostrar o checkbox de Mostrar Componentes*/
    if($veio_compras != 1) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" id="mostrar_componentes" class="checkbox">
            <label for="mostrar_componentes">Mostrar Componentes</label>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>