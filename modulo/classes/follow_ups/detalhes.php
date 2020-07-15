<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>FOLLOW-UP EXCLU�DO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>FOLLOW-UP ALTERADO COM SUCESSO.</font>";

//Vetor para auxiliar as Identifica��es de Follow-UP, que busca de outro arquivo ...
$vetor = array_sistema::follow_ups();

if($passo == 2) {//Exclus�o do Follow-up, caso este foi registrado errado ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "DELETE FROM `follow_ups` WHERE `id_follow_up` = '$_POST[id_follow_up]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
}else {//Alterar Follow-UP ...
    if(!empty($_POST['txt_observacao'][0])) {
        //N�s s� podemos ter uma Impress�o de Follow-UP para cada assunto ...
        if(!empty($_POST['chkt_exibir_no_pdf'][0])) {
            /*Antes de qualquer coisa, desmarco todas as outras marca��es de Exibir no Follow-UP, 
            afinal s� posso ter uma �nica marca��o p/ cada assunto ...*/
            $sql = "SELECT `identificacao`, `origem` 
                    FROM `follow_ups` 
                    WHERE `id_follow_up` = '".$_POST['chkt_follow_up'][0]."' LIMIT 1 ";
            $campos_follow_up = bancos::sql($sql);
            
            //Atualizando ...
            $sql = "UPDATE `follow_ups` SET `exibir_no_pdf` = 'N' WHERE `identificacao` = '".$campos_follow_up[0]['identificacao']."' AND `origem` = '".$campos_follow_up[0]['origem']."' ";
            bancos::sql($sql);
            
            $exibir_no_pdf = 'S';
        }else {
            $exibir_no_pdf = 'N';
        }
        $sql = "UPDATE `follow_ups` SET `observacao` = '".$_POST['txt_observacao'][0]."', `exibir_no_pdf` = '$exibir_no_pdf', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_follow_up` = '".$_POST['chkt_follow_up'][0]."' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmb_origem = $_POST['cmb_origem'];
    $cmb_login  = $_POST['cmb_login'];
}else {
    $cmb_origem = $_GET['cmb_origem'];
    $cmb_login  = $_GET['cmb_login'];
}
?>
<html>
<head>
<title>.:: Follow-up(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function habilitar_desabilitar_linha(indice) {
    if(document.getElementById('chkt_follow_up'+indice).checked == true) {//Linha habilitada ...
        //Habilitando objetos ...
        document.getElementById('txt_observacao'+indice).disabled       = false
        document.getElementById('chkt_exibir_no_pdf'+indice).disabled   = false
        //Layout de Habilitado ...
        document.getElementById('txt_observacao'+indice).className      = 'caixadetexto'
    }else {//Linha desabilitada ...
        //Desabilitando objetos ...
        document.getElementById('txt_observacao'+indice).disabled       = true
        document.getElementById('chkt_exibir_no_pdf'+indice).disabled   = true
        //Layout de Desabilitado ...
        document.getElementById('txt_observacao'+indice).className      = 'textdisabled'
    }
}

function incluir_salvar() {
    var elementos               = document.form.elements
    var origem                  = eval('<?=$origem;?>')
    var follow_up_selecionado   = 0
    
    if(origem == 15) {//Caminho Cadastro ...
        if(typeof(elementos['chkt_follow_up[]']) == 'object') {//Esse objeto s� existir� se existir pelo menos 1 Follow-UP registrado ...
            for(i = 0; i < elementos.length; i++) {
                if(elementos[i].name == 'chkt_follow_up[]' && elementos[i].checked == true) {
                    follow_up_selecionado++
                    break;
                }
            }
            if(follow_up_selecionado == 1) {//Caminho do Alterar Follow-UP ...
                document.form.submit()
            }else {//No caminho de Cadastro eu s� posso ter um �nico Registro e por isso que eu n�o abro a op��o de Incluir ...
                alert('SELECIONE UMA OP��O P/ ALTERAR !')
            }
        }else {//Caminho do Incluir Follow-UP ...
            nova_janela('../follow_ups/incluir.php?identificacao=<?=$identificacao;?>&id_cliente=<?=$id_cliente;?>&id_fornecedor=<?=$id_fornecedor;?>&origem=<?=$origem;?>', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }else {//Outros Caminhos ...
        if(typeof(elementos['chkt_follow_up[]']) == 'object') {//Esse objeto s� existir� se existir pelo menos 1 Follow-UP registrado ...
            for(i = 0; i < elementos.length; i++) {
                if(elementos[i].name == 'chkt_follow_up[]' && elementos[i].checked == true) {
                    follow_up_selecionado++
                    break;
                }
            }
            if(follow_up_selecionado == 1) {//Caminho do Alterar Follow-UP ...
                document.form.submit()
            }else {//Como n�o foi selecionado nenhum Option, ent�o sugiro o Caminho do Incluir Follow-UP ent�o ...
                if(origem == 16) {//Caminho de Pedido de Compras ...
                    nova_janela('../follow_ups/pedidos_compras/incluir.php?identificacao=<?=$identificacao;?>&id_cliente=<?=$id_cliente;?>&id_fornecedor=<?=$id_fornecedor;?>&origem=<?=$origem;?>', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
                }else {//Outros Caminhos ...
                    nova_janela('../follow_ups/incluir.php?identificacao=<?=$identificacao;?>&id_cliente=<?=$id_cliente;?>&id_fornecedor=<?=$id_fornecedor;?>&origem=<?=$origem;?>', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
                }
            }
        }else {//Caminho do Incluir Follow-UP ...
            //Nesse �nico caminho abro uma outra Tela devido ser uma programa��o mais complexa ...
            if(origem == 16) {//Caminho de Pedido de Compras ...
                nova_janela('../follow_ups/pedidos_compras/incluir.php?identificacao=<?=$identificacao;?>&id_cliente=<?=$id_cliente;?>&id_fornecedor=<?=$id_fornecedor;?>&origem=<?=$origem;?>', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
            }else {
                nova_janela('../follow_ups/incluir.php?identificacao=<?=$identificacao;?>&id_cliente=<?=$id_cliente;?>&id_fornecedor=<?=$id_fornecedor;?>&origem=<?=$origem;?>', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
            }
        }
    }
}
    
function excluir_follow_up(id_follow_up, origem) {
    //Quando est� origem n�o for "Cadastro" ent�o n�o posso excluir, do contr�rio posso excluir normalmente p/ fazer manuten��es se necess�rio ...
    if(origem != 15) {
        alert('TEMPORARIAMENTE DESABILITADO !')
        return false
    }
    var resposta = confirm('VOC� TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE FOLLOW-UP ?')
    if(resposta == true) {
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
        document.form.id_follow_up.value = id_follow_up
        document.form.passo.value = 2
        document.form.submit()
    }else {
        return false
    }
}

function enviar_email(id_follow_up) {
    /*Passo os par�metros $identificacao e $origem p/ que seja poss�vel incluir um Novo Follow-UP em cima 
    do Follow-UP que irei visualizar dados que esta no par�metro -> id_follow_up ...*/
    nova_janela('../follow_ups/incluir.php?identificacao=<?=$identificacao;?>&origem=<?=$origem;?>&id_follow_up='+id_follow_up, 'ENVIAR_EMAIL', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}

function reler_tela_itens_pedido() {
    top.itens.location = top.itens.location.href
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<!--Aki � o id_orcamento, id_pedido, sei l� ... qualquer id-->
<input type='hidden' name='identificacao' value='<?=$identificacao;?>'>
<input type='hidden' name='id_follow_up'>
<input type='hidden' name='passo'>
<!--Tipo de Tela-->
<input type='hidden' name='origem' value='<?=$origem;?>'>
<!--*************************************************************************-->
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Follow-up(s) Registrado(s)
            <br/>
            Origem: 
            <select name='cmb_origem' title='Selecione a Origem' onchange='document.form.submit()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    for($i = 1; $i < count($vetor); $i++) {
                        $selected = ($cmb_origem == $i) ? 'selected' : '';
                ?>
                    <option value='<?=$i?>' <?=$selected;?>><?=$vetor[$i];?></option>
                <?
                    }
                ?>
            </select>
            - Login: 
            <select name='cmb_login' title='Selecione o Login' onchange='document.form.submit()' class='combo'>
            <?
                //Eu n�o trago os funcion�rios que est�o demitidos ...
                $sql = "SELECT l.`id_login`, l.`login` 
                        FROM `logins` l 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` AND f.`status` < '3' 
                        WHERE l.`ativo` = '1' 
                        ORDER BY l.`login` ";
                echo combos::combo($sql, $cmb_login);
            ?>
            </select>
        </td>
    </tr>
<?
    $condicao = '';//Valor Inicial ...

    if(!empty($id_fornecedor))  $condicao.= " AND fu.`id_fornecedor` = '$id_fornecedor' ";
    if(!empty($id_cliente))     $condicao.= " AND fu.`id_cliente` = '$id_cliente' ";
    if(!empty($identificacao))  $condicao.= " AND fu.`identificacao` = '$identificacao' ";
    if(!empty($cmb_origem)) {
        $condicao.= " AND fu.`origem` = '$cmb_origem' ";
    }else {
        /*Na tela de Follow-Ups "Atendimento Interno" e "Pend�ncias" eu tenho que enxergar todos os Follow-UPs 
        do Cliente independente da onde essa Tela "Fun��o" foi chamada ...*/
        if(!empty($origem) && ($origem != 7 && $origem != 9)) $condicao.= "AND `origem` = '$origem' ";
    }
    if(!empty($cmb_login))      $inner_join = "INNER JOIN `logins` l ON l.`id_funcionario` = fu.`id_funcionario` AND l.`id_login` = '$cmb_login' ";

    //Aqui eu busco todos os Follow_ups Registrados ...
    $sql = "SELECT fu.* 
            FROM `follow_ups` fu 
            $inner_join 
            WHERE 1 
            $condicao ORDER BY fu.`data_sys` DESC ";
    $campos = bancos::sql($sql, $inicio, 3, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='10'>
            <br/><br/>
            N�O EXISTEM FOLLOW-UP(S) REGISTRADOS.
            <p/>
            <input type='button' name='cmd_incluir' value='Incluir' title='Incluir' onclick='incluir_salvar()' style='color:black' class='botao'>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            &nbsp;
        </td>
        <td>
            Origem
        </td>
        <td>
            N.�
        </td>
        <td>
            Login
        </td>
        <td style='cursor:help'>
            <font title='Data e Hora da Ocorr�ncia'>
                Ocorr�ncia
            </font>
        </td>
        <td>
            Contato
        </td>
        <td>
            Observa��o
        </td>
        <td>
            Exibir no PDF
        </td>
        <td style='cursor:help'>
            <img src = '../../../imagem/novo_email.jpeg' border='0' title='Enviar E-mail' alt='Enviar E-mail' width='20' height='20'>
        </td>
        <td style='cursor:help'>
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Follow Up' alt='Excluir Follow Up'>
        </td>
    </tr>
<?        
        //Roberto 62 "Diretor", D�rcio 98 "porque programa", Nishimura 136 "Gerente de Vendas" ...
        $vetor_funcionarios_com_acesso = array(62, 98, 136);

        for($i = 0; $i < $linhas; $i++) {
            /*Vari�vel de controle p/ exibir apenas um s� checkbox e figura de Manipula��o como excluir 
            do �ltimo Registro do usu�rio Logado ...
            
            Ou p/ Roberto 62 "Diretor", D�rcio 98 porque programa e Nishimura 136 "Gerente de Vendas" ...*/
            if($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario'] || (in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) {
                $exibir_ultimo_registro = ($i == 0) ? 'S' : 'N';
                
                //$exibir_ultimo_registro = (empty($exibir_ultimo_registro)) ? 'S' : 'N';
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            /*Controle p/ manipula��o de Registros apenas do usu�rio Logado e somente o �ltimo 
            que est� sendo exibido ...*/
            if((($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario']) || (in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) && $exibir_ultimo_registro == 'S') {
        ?>
            <input type='checkbox' name='chkt_follow_up[]' id='chkt_follow_up<?=$i;?>' value='<?=$campos[$i]['id_follow_up'];?>' onclick="habilitar_desabilitar_linha('<?=$i;?>')" class='checkbox'>
        <?
            }
        ?>
        </td>
        <td>
            <?=$vetor[$campos[$i]['origem']];?>
        </td>
        <td>
        <?
            if($campos[$i]['origem'] == 3) {//Tela de Gerenciar Estoque
                //echo 'Cliente';
            }else if($campos[$i]['origem'] == 4) {//Contas � Receber
                $sql = "SELECT `num_conta` 
                        FROM `contas_receberes` 
                        WHERE `id_conta_receber` = '".$campos[$i]['identificacao']."' LIMIT 1 ";
                $campos_numero = bancos::sql($sql);
                echo $campos_numero[0]['num_conta'];
            }else if($campos[$i]['origem'] == 5) {//Nota Fiscal
                $sql = "SELECT `id_nf_num_nota` 
                        FROM `nfs` 
                        WHERE `id_nf` = '".$campos[$i]['identificacao']."' LIMIT 1 ";
                $campos_numero = bancos::sql($sql);
                echo faturamentos::buscar_numero_nf($campos[$i]['identificacao'], 'S');
            }else if($campos[$i]['origem'] == 6) {//APV
//Significa que um Follow-Up que est� sendo registrado pela parte de Vendas (Antigo Sac)
                if($campos[$i]['modo_venda'] == 1) {
                    echo 'FONE';
                }else {
                    echo 'VISITA';
                }
            }else if($campos[$i]['origem'] == 7) {//Atend. Interno
                //echo 'Atend. Interno';
            }else if($campos[$i]['origem'] == 8) {//Depto. T�cnico
                //echo 'Depto. T�cnico';
            }else if($campos[$i]['origem'] == 9) {//Pend�ncias
                //echo 'Pend�ncias';
            }else if($campos[$i]['origem'] == 10) {//TeleMarketing
                //echo 'TeleMkt';
            }else if($campos[$i]['origem'] == 11) {//Acompanhamento
                //echo 'Acompanhamento';
            }else {
                if($campos[$i]['identificacao'] > 0) {
                    echo $campos[$i]['identificacao'];
                }else {
                    echo '-';
                }
            }
        ?>
        </td>
        <td>
        <?
            //Aqui busca o Login na Tabela Relacional ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_sys'], '/').' - '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['id_cliente_contato'])) {
//Aqui busca o Contato na Tabela Relacional
                $sql = "SELECT `nome` 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
                echo $campos_contato[0]['nome'];
            }
        ?>
        </td>
        <td align='left'>
        <?
            /*Controle p/ manipula��o de Registros apenas do usu�rio Logado e somente o �ltimo 
            que est� sendo exibido ...*/
            if((($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario']) || (in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) && $exibir_ultimo_registro == 'S') {
        ?>
            <textarea name='txt_observacao[]' id='txt_observacao<?=$i;?>' maxlength='500' cols='55' rows='2' class='textdisabled' disabled><?=strip_tags($campos[$i]['observacao']);?></textarea>
        <?
            }else {
        ?>
            <font size='2'>
                <b><?=$campos[$i]['observacao'];?></b>
            </font>
        <?
            }
        ?>
        </td>
        <td>
        <?
            /*Controle p/ manipula��o de Registros apenas do usu�rio Logado e somente o �ltimo 
            que est� sendo exibido ...*/
            if((($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario']) || (in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) && $exibir_ultimo_registro == 'S') {
                $checked = ($campos[$i]['exibir_no_pdf'] == 'S') ? 'checked' : '';
        ?>
            <input type='checkbox' name='chkt_exibir_no_pdf[]' id='chkt_exibir_no_pdf<?=$i;?>' value='S' title='Exibir no PDF' class='checkbox' <?=$checked;?> disabled>
        <?
            }
        ?>
        </td>
        <td align='center'>
            <img src = '../../../imagem/novo_email.jpeg' border='0' title='Enviar e-mail p/ quem Registrou este Follow-UP' alt='Enviar e-mail p/ quem Registrou este Follow-UP' width='20' height='20' onclick="enviar_email('<?=$campos[$i]['id_follow_up'];?>')" style='cursor:pointer'>
        </td>
        <td align='center'>
        <?
            /*Controle p/ manipula��o de Registros apenas do usu�rio Logado e somente o �ltimo que est� sendo exibido ou ...

            no caso do cadastro "Origem 15" n�o necessariamente precisa ser o usu�rio Logado que � o autor da Ocorr�ncia, pode ser tamb�m os 
            funcion�rios com acesso que foram especificados na vari�vel $vetor_funcionarios_com_acesso para dar manuten��es se necess�rio ...*/
            if(($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario'] && $exibir_ultimo_registro == 'S') || (($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario'] || (in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) && $campos[$i]['origem'] == 15)) {
        ?>
                <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Follow-UP' alt='Excluir Follow-UP' style='cursor:pointer' onclick="excluir_follow_up('<?=$campos[$i]['id_follow_up'];?>', '<?=$campos[$i]['origem'];?>')">
        <?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <?
                /*Origem = '15' representa "Cadastro" e essa � a �nica op��o que s� pode ter um 
                �nico registro, demais op��es sempre poder� exibir o bot�o Cadastro ...*/
                if($origem == 15 && $linhas <= 1 || $origem != 15) {
            ?>
                <input type='button' name='cmd_incluir_salvar' value='Incluir / Salvar' title='Incluir / Salvar' onclick='incluir_salvar()' style='color:black' class='botao'>
            <?
                }
            ?>
            
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</form>
</body>
</html>