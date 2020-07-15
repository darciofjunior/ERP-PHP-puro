<?
require('../../../lib/segurancas.php');

//Essa tela pode ser requirida através de outro arquivo e por isso faço essa segurança ...
if(empty($_GET['pop_up']) && empty($_GET['iframe'])) require('../../../lib/menu/menu.php');

require('../../../lib/data.php');
require('../../../lib/intermodular.php');
require('../../../lib/estoque_acabado.php');

if($passo == 1) {
//Busca de Todos os dados da O.E.
    $sql = "SELECT * 
            FROM `oes` 
            WHERE `id_oe` = '$_GET[id_oe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    
    //Faço a busca do ED do Produto Acabado de Retorno da OE ...
    $estoque_produto    = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado_e']);
    $qtde_disponivel    = $estoque_produto[3];
    if($qtde_disponivel == 0) {
        $qtde_disponivel_inicial = number_format(0, 2, ',', '.');
    }else {
        $qtde_disponivel_inicial = number_format($qtde_disponivel, 2, ',', '.');
    }
?>
<html>
<title>.:: Alterar OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var qtde_saida = eval('<?=$campos[0]['qtde_s'];?>')
//Quantidade de Retorno ...
    if(!texto('form', 'txt_qtde_retorno', '1', '-0123456789', 'QUANTIDADE DE RETORNO', '1')) {
        return false
    }
//Tipo de Entrada ...
    if(!combo('form', 'cmb_tipo_entrada', '', 'SELECIONE O TIPO DE ENTRADA !')) {
        return false
    }
//Verifica se o usuário digitou uma qtde = 0
    if((document.form.txt_qtde_retorno.value == '' || document.form.txt_qtde_retorno.value == 0) && document.form.txt_observacao.value == '') {
        alert('QUANTIDADE DE RETORNO = 0 !!!\n\nENTÃO DIGITE UMA OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        document.form.txt_observacao.select()
        return false
    }
//Quantidade de Retorno negativa ...
    if(document.form.txt_qtde_retorno.value < 0) {
        alert('ESTA ENTRADA (-) SÓ CORRIGE ESTOQUE DO PA RETORNADO !\n\nA QTDE DO PA ENVIADO NÃO É ALTERADA !!!')
    }
/***********************************************************************/
/******************************Comparações******************************/
//1) Estoque Real Final nunca pode ser Negativo ...
    var qtde_disponivel_final  = eval(strtofloat(document.form.txt_qtde_disponivel_final.value))
    if(qtde_disponivel_final < 0) {
        alert('ESTOQUE DISPONÍVEL FINAL NÃO PODE SER NEGATIVO !')
        document.form.txt_qtde_retorno.focus()
        document.form.txt_qtde_retorno.select()
        return false
    }
    
/*Verifica se o usuário digitou uma qtde de retorno diferente da qtde de saída, o Sistema
sugere para o Usuário se ele deseja fechar a O.E.*/
    if(document.form.txt_qtde_retorno.value != qtde_saida) {//Qtdes Diferentes
        var resposta = confirm('A QUANTIDADE DE SAÍDA E DE ENTRADA ESTÃO INCOMPATÍVEIS !!!\nTEM CERTEZA QUE DESEJA MANTER ESTA QUANTIDADE ?')
        if(resposta == false) {
            return false
        }
    }
//Faço uma sugestão para Finalizar a OE ...
    if(!document.form.chkt_finalizar_oe.checked) controle_finalizar_oe('N')
}

function controle_finalizar_oe(submeter) {
    /**********************************************************************/
    /**************************Entrada Antecipada**************************/
    /**********************************************************************/
    /*Somente quando NÃO estiver selecionado a opção "Entrada Antecipada" na combo que sugiro de finalizar a OP, afinal se é Entrada Antecipada eu ainda
    terei que retornar pelo menos uma vez aqui nessa OP ...*/
    if(document.form.cmb_tipo_entrada.value != 'A') {
        var status_finalizar = eval('<?=$campos[0]['status_finalizar'];?>')
        if(status_finalizar == 0) {//Signfica que eu estou finalizando a OE ...
            var pergunta = confirm('GOSTARIA DE FINALIZAR ESSA OE ?')
            if(pergunta == false) {
                document.form.chkt_finalizar_oe.checked = false
            }else {
                if(!document.form.chkt_finalizar_oe.checked) {
                    document.form.chkt_finalizar_oe.checked = true
                }
            }
        }
    }
    if(submeter == 'S') document.form.submit()
}

function desatrelar_pa() {
//PA Substitutivo ...
    if(!combo('form', 'cmb_pa_substitutivo', '', 'SELECIONE O P.A. SUBSTITUTIVO !')) {
        return false
    }
    var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE P.A. DO PA PRINCIPAL ?')
    if(resposta == true) {
        var id_pa_substitutivo = document.form.cmb_pa_substitutivo.value
        nova_janela('../../classes/produtos_acabados/desatrelar_pa.php?id_pa_a_ser_desatrelado='+id_pa_substitutivo+'&id_produto_acabado=<?=$campos[0]['id_produto_acabado_e'];?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function tratar_qtde(qtde) {
    qtde.value = qtde.value.replace('.', '')
    if(qtde.value == 0) qtde.value = ''
}

function calcular() {
    var qtde_retorno            = (document.form.txt_qtde_retorno.value == '') ? 0 : eval(strtofloat(document.form.txt_qtde_retorno.value))
    var qtde_disponivel_inicial = eval(strtofloat(document.form.txt_qtde_disponivel_inicial.value))
//Parte de Estoque
    if(typeof(qtde_retorno) == 'undefined') {
        document.form.txt_qtde_disponivel_inicial.value = '<?=$qtde_disponivel_inicial;?>'
    }else {
        document.form.txt_qtde_disponivel_final.value = qtde_retorno + qtde_disponivel_inicial
        document.form.txt_qtde_disponivel_final.value = arred(document.form.txt_qtde_disponivel_final.value, 2, 1)
    }
}

function retornar_estoques_pa() {
    //Se não foi escolhido um PA Substitutivo na Combo, então eu utilizo o PA Nominal da OP ...
    var id_produto_acabado_utilizar = (document.form.cmb_pa_substitutivo.value != '') ? document.form.cmb_pa_substitutivo.value : eval('<?=$campos[0]['id_produto_acabado_e'];?>')
    iframe_retornar_estoques_pa.location = '../../classes/produtos_acabados/retornar_estoques_pa.php?id_produto_acabado='+id_produto_acabado_utilizar
    
    /*Dou esse tempinho de 0,4 segundo p/ chamar essa função porque se leva um tempinho para atualizar o 
    Estoque Disponível na Caixa Estoque Disponível Inicial ...*/
    setTimeout('calcular()', 400)
}
</Script>
</head>
<body onload='calcular();document.form.txt_qtde_retorno.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<!--****************************************Controle de Tela****************************************-->
<input type='hidden' name='id_oe' value='<?=$_GET['id_oe'];?>'>
<input type='hidden' name='id_produto_acabado_e' value='<?=$campos[0]['id_produto_acabado_e'];?>'>
<!--Se esse parâmetro não estiver vazio, então eu exibo esse Botão porque significa que essa tela foi acessada 
de outro lugar ao invés de ter sido acessada de algum Menu ...-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='pop_up' value='<?=$_GET['pop_up'];?>'>
<input type='hidden' name='iframe' value='<?=$_GET['iframe'];?>'>
<!--************************************************************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            O.E. (Saída) N.º
            <font color='yellow'>
                <?=$id_oe;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>Produto Enviado: </font>
            <br/>
            <?
                //Faço esse Tratamento porque podemos ter vários PA(s) de Saída ...
                $vetor_produto_acabado = explode(',', $campos[0]['id_produto_acabado_s']);
                //Faço esse Tratamento porque podemos ter várias Qtde(s) de Saída ...
                $vetor_qtde_s = explode(',', $campos[0]['qtde_s']);
                
                foreach($vetor_produto_acabado as $i => $id_produto_acabado_loop) {
                    echo $vetor_qtde_s[$i].' - '.intermodular::pa_discriminacao($id_produto_acabado_loop, 0).'<br/>';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Data de Emissão:</b>
        </td>
        <td colspan='2'>
            <?=substr(data::datetodata($campos[0]['data_s'], '/'), 0, 10);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td colspan='2'>
        <?
//Significa que Existem Itens da 7ª Etapa que estão atrelados a esse Produto Principal
            if(!empty($campos[0]['id_pas_set_etapa'])) {
                echo '<b>'.$campos[0]['observacao_s'].': </b><br/>';
                $sql = "SELECT `discriminacao` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` IN (".$campos[0]['id_pas_set_etapa'].") ";
                $campos_pis = bancos::sql($sql);
                $linhas_pis = count($campos_pis);
                for($i = 0; $i < $linhas_pis; $i++) echo $campos_pis[$i]['discriminacao'].'<br/>';
//Não existem Itens da 7ª Etapa, sendo assim só listo a observação normalmente
            }else {
                echo $campos[0]['observacao_s'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            O.E. (Entrada)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>
                Produto Retorno:
            </font>
            <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado_e'], 0);?>
            &nbsp;
            <img src = '../../../imagem/menu/alterar.png' border='0' alt='Visualizar Custo' title='Visualizar Custo' onclick="html5Lightbox.showLightbox(7, '../custo_unificado/abrir_custo.php?id_produto_acabado=<?=$campos[0]['id_produto_acabado_e'];?>&pop_up=1')">
            &nbsp;
            <?
                $sql = "SELECT `referencia` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado_e']."' LIMIT 1 ";
                $campos_pa_retorno = bancos::sql($sql);

                $url = '../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($campos_pa_retorno[0]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$campos[0]['id_produto_acabado_e'].'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$campos_pa_retorno[0]['referencia'].'&pop_up=1';
                }
            ?>
            <img src = '../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipulações' alt='Baixas / Manipulações' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
            &nbsp;
            <img src = '../../../imagem/desbloquear.png' border='0' title='Desbloquear PAs' alt='Desbloquear PAs' width='20' height='20' onclick="html5Lightbox.showLightbox(7, '../programacao/desbloquear_pa/consultar.php?pop_up=1')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Retorno:</b>
        </td>
        <td>
            <?=date('d/m/Y');?> - Estoque Disponível Inicial: <input type='text' name='txt_qtde_disponivel_inicial' value='<?=$qtde_disponivel_inicial;?>' title='Estoque Disponível Inicial' size='12' class='caixadetexto2' disabled>
        </td>
        <td>
            Estoque Disponível Final: <input type='text' name='txt_qtde_disponivel_final' title='Estoque Disponível Final' size='12' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Qtde à Retornar:</b>
            </font>
        </td>
        <td colspan='2'>
            <?=$campos[0]['qtde_a_retornar'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Qtde de Retorno:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_qtde_retorno' title='Digite a Qtde de Retorno' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '0', '1', event);tratar_qtde(this);calcular()" class='caixadetexto'>
            <b>Tipo de Entrada:</b>
            <select name='cmb_tipo_entrada' title='Selecione o Tipo de Entrada' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='A'>ANTECIPADA</option>
                <option value='N'>NORMAL</option>
            </select>
            &nbsp;
            <img src = '../../../imagem/estornar_entrada_antecipada.png' border='0' title='Retorno de Entrada Antecipada' alt='Retorno de Entrada Antecipada' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '../../../modulo/vendas/estoque_acabado/retorno_entrada_antecipada.php?id_produto_acabado=<?=$campos[0]['id_produto_acabado_e'];?>')">
        </td>
        <td>
            <?
                if($campos[0]['status_finalizar'] == 1) {//A OP aqui já está finalizada
                    $class_botao            = 'disabled';
                    $class                  = 'textdisabled';
                    $disabled               = 'disabled';
                    $checked_finalizar_op   = 'checked';
                }else {
                    $class_botao            = 'botao';
                    $class                  = 'caixadetexto';
                    $disabled               = '';
                    $checked_finalizar_op   = '';
                }
            ?>
            <input type='checkbox' name='chkt_finalizar_oe' id='chkt_finalizar_oe' value='1' title='Finalizar OE' onclick="controle_finalizar_oe('S')" class='checkbox' <?=$checked_finalizar_op;?>>
            <label for='chkt_finalizar_oe'>Finalizar OE</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkgreen'>
                <b>PA(s) Substitutivo(s):</b> 
            </font>
        </td>
        <td colspan='2'>
            <select name='cmb_pa_substitutivo' title='Selecione o P.A. Substitutivo' onchange='retornar_estoques_pa()' class='combo'>
            <?
                //Aqui eu listo todos os PA(s) Padrões que já foram substituídos com o PA Principal ...
                $sql = "SELECT 
                        IF(ps.id_produto_acabado_1 = '".$campos[0]['id_produto_acabado_e']."', ps.id_produto_acabado_2, ps.id_produto_acabado_1) AS id_pa 
                        FROM `pas_substituires` ps 
                        WHERE 
                        (ps.id_produto_acabado_1 = '".$campos[0]['id_produto_acabado_e']."') 
                        OR (ps.id_produto_acabado_2 = '".$campos[0]['id_produto_acabado_e']."') ";
                $campos_pas_substituicao = bancos::sql($sql);
                $linhas_pas_substituicao = count($campos_pas_substituicao);
                if($linhas_pas_substituicao > 0) {//Encontrou pelo menos 1 PA Substituto ...
                    for($i = 0; $i < $linhas_pas_substituicao; $i++) $id_pas_substitutos.= $campos_pas_substituicao[$i]['id_pa'].', ';
                    $id_pas_substitutos = substr($id_pas_substitutos, 0, strlen($id_pas_substitutos) - 2);
                }
                //Se mesmo assim não veio nenhum PA Substituto, trato a variável abaixo p/ não furar o SQL abaixo ...
                if(empty($id_pas_substitutos)) $id_pas_substitutos = 0;
//Trago todos os PA(s) que estão atrelados na tab. relacional, + o outro selecionado pelo usuário no consultar P.A.
                $sql = "SELECT `id_produto_acabado`, CONCAT(`referencia`, ' * ', `discriminacao`) AS dados 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` IN ($id_pas_substitutos) ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_atrelar_pa' value='Atrelar PA' title='Atrelar PA' onclick="nova_janela('../../classes/produtos_acabados/atrelar_pa.php?id_pa_a_ser_atrelado=<?=$campos[0]['id_produto_acabado_e'];?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            &nbsp;
            <input type='button' name='cmd_desatrelar_pa' value='Desatrelar PA' title='Desatrelar PA' onclick='desatrelar_pa()' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td colspan='2'>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <?
                if(empty($_GET['pop_up'])) {//Se essa Tela foi aberta do modo Normal, então exibo os botões abaixo ...
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_retorno.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_dar_entrada' value='Dar Entrada' title='Dar Entrada' class='<?=$class_botao;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
            <?
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
</table>
<?
/*Verifico o Total de Entrada(s) Registrada(s) p/ esse OE $id_oe passador por parâmetro:

M -> "Manipulação" e Tipo seja: 

1) -> Manipulação p/ Substituição;
2) -> Manipulação p/ Substituição com Ordem de Embalagem;
3) -> Manipulação p/ Montagem de Jogos;

/* Aqui controlamos tanto as Entradas como PA(s) enviados da OE ... Sinal Positivo representa entrada, 
negativo PA(s) enviados ou correção de Entrada ...*/
    $sql = "SELECT COUNT(`id_baixa_manipulacao_pa`) AS entradas_registradas 
            FROM `baixas_manipulacoes_pas` 
            WHERE `id_oe` = '$_GET[id_oe]' 
            AND `acao` = 'M' 
            AND `tipo_manipulacao` IN (1, 2, 3) ";
    $campos_entradas        = bancos::sql($sql);
    $entradas_registradas   = $campos_entradas[0]['entradas_registradas'];
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('entradas_registradas'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                &nbsp;Entrada(s) Registrada(s):
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$entradas_registradas;?>
            </font>
            <span id='statusqtde_debito'>&nbsp;</span>
            <span id='statusqtde_debito'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe src='entradas_registradas.php?id_oe=<?=$id_oe;?>' name='entradas_registradas' id='entradas_registradas' marginwidth='0' marginheight='0' style='display:none' frameborder='0' height='160' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
</form>
<iframe name='iframe_retornar_estoques_pa' id='iframe_retornar_estoques_pa' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='yes' title='Retornar Estoques PA' width='0' height='0'></iframe>
</body>
</html>
<?
}else if($passo == 2) {
    $id_produto_acabado_utilizar    = (!empty($_POST['cmb_pa_substitutivo'])) ? $_POST['cmb_pa_substitutivo'] : $_POST['id_produto_acabado_e'];
    $resultado                      = estoque_acabado::verificar_manipulacao_estoque($id_produto_acabado_utilizar, $_POST['txt_qtde_retorno']);
    
    if($resultado['retorno'] == 'executar') {//Se foi possível acrescentar algo, então roda as funções de Estoque ...
        if(!empty($_POST['cmb_pa_substitutivo'])) {
            //Aqui eu busco a referência e a discriminacao do PA Substitutivo ...
            $_POST['txt_observacao'].= ' '.$_POST[txt_qtde_retorno].' - ('.intermodular::pa_discriminacao($_POST['cmb_pa_substitutivo'], 0, 0, 0, 0, 1).')';
        }
        /**********************************************************************/
        /**************************Entrada Antecipada**************************/
        /**********************************************************************/
        //Se o usuário marcou o Tipo de Entrada na combo como sendo "Entrada Antecipada" antes de clicar no Botão "Dar Entrada" então ...
        if($_POST['cmb_tipo_entrada'] == 'A') {
            //1) Concateno o texto abaixo junto da Observação / Justificativa ...
            $_POST['txt_observacao'].= ' (Entrada Antecipada)';
        }
//Aki registra a Data e Hora em q foi feita a alteração ...
        $data_sys           = date('Y-m-d H:i:s');
        $status_finalizar   = ($_POST['chkt_finalizar_oe'] == 1) ? 1 : 0;

        //Aqui eu controlo apenas o status de Finalização da OE ...
        $sql = "UPDATE `oes` SET `status_finalizar` = '$status_finalizar' WHERE `id_oe` = '$_POST[id_oe]' LIMIT 1 ";
        bancos::sql($sql);

        if($_POST['txt_qtde_retorno'] != 0) {//Só se a Qtde de Retorno for diferente de '0' então Registro a Baixa Manipulaçao e Rodo a Função de Estoque ...
            //1) Atualizando o P.A. Enviado
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_oe`, `retirado_por`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$id_produto_acabado_utilizar', '$_SESSION[id_funcionario]', '', '$_POST[id_oe]', '', '$_POST[txt_qtde_retorno]', '$_POST[txt_observacao]', 'M', '2', '$data_sys') ";
            bancos::sql($sql);
            //Busco a Família do PA 1 "da OE" para fazer um tratamento mais abaixo ...
            $sql = "SELECT gpa.`id_familia` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$_POST[id_produto_acabado_e]' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            if($campos_pa[0]['id_familia'] == 9) {//Nesse caso específico, o procedimento será um pouquinho diferenciado ...
                if(!empty($_POST['cmb_pa_substitutivo'])) {
                    //Busco o pecas_por_jogo do PA à Retornar ...
                    $sql = "SELECT `pecas_por_jogo` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado_e]' LIMIT 1 ";
                    $campos_pa_retornar = bancos::sql($sql);

                    //Busco o pecas_por_jogo do PA Substituvo "que é o PA que estou dando entrada nessa OE" ...
                    $sql = "SELECT `pecas_por_jogo` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$_POST[cmb_pa_substitutivo]' LIMIT 1 ";
                    $campos_pa_substitutivo = bancos::sql($sql);
                    $qtde_entrada           = $_POST[txt_qtde_retorno] * $campos_pa_substitutivo[0]['pecas_por_jogo'] / $campos_pa_retornar[0]['pecas_por_jogo'];
                }else {
                    $qtde_entrada           = $_POST[txt_qtde_retorno];
                }
            }else {
                $qtde_entrada               = $_POST[txt_qtde_retorno];
            }
            //Aqui eu controlo somente os dados de Entrada da OE ...
            $sql = "UPDATE `oes` SET `id_funcionario_resp_e` = '$_SESSION[id_funcionario]', `qtde_e` = `qtde_e` + $_POST[txt_qtde_retorno], `data_e` = '$data_sys', `observacao_e` = '$_POST[txt_observacao]' WHERE `id_oe` = '$_POST[id_oe]' LIMIT 1 ";
            bancos::sql($sql);
        }

        estoque_acabado::seta_nova_entrada_pa_op_compras($id_produto_acabado_utilizar);
        estoque_acabado::atualizar($id_produto_acabado_utilizar);
        estoque_acabado::controle_estoque_pa($id_produto_acabado_utilizar);
        //Aqui eu atualizo o campo de Produção do Estoque
        estoque_acabado::atualizar_producao($_POST['id_produto_acabado_e']);//Só atualizo o PA1 porque o Registro foi gerado em cima do mesmo ...
        /**********************************************************************/
        /**************************Entrada Antecipada**************************/
        /**********************************************************************/
        //Se o usuário marcou o Tipo de Entrada como sendo "Antecipada" antes de clicar no Botão "Dar Entrada" então ...
        if($_POST['cmb_tipo_entrada'] == 'A') {
            /*Comentado em 20/04/2018 ...
            2) Mudo o item da qual já foi dado Entrada Antecipada p/ Racionado ...
            $sql = "UPDATE `estoques_acabados` SET `racionado` = '1' WHERE `id_produto_acabado` = '$id_produto_acabado_utilizar' LIMIT 1 ";
            bancos::sql($sql);*/
            
            //3) Como sendo a última ação do PA, atualizo o campo Entrada Antecipada do PA na tabela de "estoques_acabados" ...
            $sql = "UPDATE `estoques_acabados` SET `entrada_antecipada` = `entrada_antecipada` + $qtde_entrada WHERE `id_produto_acabado` = '$id_produto_acabado_utilizar' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Desatrelo o Item do Pedido de Venda do id_oe que foi dada Entrada ...
        $sql = "UPDATE `pedidos_vendas_itens` SET id_oe = '0' WHERE `id_oe` = '$_POST[id_oe]' LIMIT 1 ";
        bancos::sql($sql);
        //Desatrelo o Item de NF de Compras do id_oe que foi dada Entrada ...
        $sql = "UPDATE `nfe_historicos` SET `id_oe` = '0' WHERE `id_oe` = '$_POST[id_oe]' LIMIT 1 ";
        bancos::sql($sql);
        $mensagem = 'OE ALTERADA COM SUCESSO ! ';
    }else {//O Produto provavelmente deve estar bloqueado ...
        $mensagem = 'NÃO TEM ESTOQUE DISPONÍVEL SUFICIENTE E/OU PRODUTO ACABADO ESTÁ BLOQUEADO !!!\n\nNÃO FOI POSSÍVEL DAR ENTRADA PARA ESTE PRODUTO ACABADO.';
    }
?>
    <Script Language= 'Javascript'>
        alert('<?=$mensagem;?>')
    </Script>
<?
/*********************************************************************************/
    if(!empty($_POST['id_oe'])) {
?>
	<Script Language= 'Javascript'>
            window.location = 'alterar.php?passo=1&id_oe=<?=$_POST['id_oe'];?>&pop_up=<?=$_POST['pop_up'];?>&iframe=<?=$_POST['iframe'];?>'
	</Script>
<?		
    }else if(!empty($id_produto_acabado)) {
?>
	<Script Language= 'Javascript'>
            window.location = 'alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>'
	</Script>
<?		
    }else {
?>
        <Script Language= 'Javascript'>
            window.location = 'alterar.php'
        </Script>
<?		
    }
}else {
    //Aqui eu puxo o único Filtro de OE(s) que serve para toda parte de OE(s) ...
    require('tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Alterar / Imprimir OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function imprimir() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        nova_janela('relatorio/relatorio.php', 'IMPRIMIR', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='relatorio/relatorio.php' onsubmit='return imprimir()' target='IMPRIMIR'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Alterar / Imprimir OE(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Imprimir
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td rowspan='2' colspan='2'>
            N.º O.E.
        </td>
        <td rowspan='2'>
            Data de Emissão
        </td>
        <td colspan='4'>
            Saída
        </td>
        <td colspan='2'>
            À retornar
        </td>
        <td colspan='5'>
            Entrada
        </td>
    </tr>
    <tr align='center'>
        <td class='linhadestaque'>
            Qtde
        </td>
        <td class='linhadestaque'>
            Produto
        </td>
        <td class='linhadestaque'>
            Observação
        </td>
        <td class='linhadestaque'>
            Login
        </td>
        <td class='linhadestaque'>
            Qtde
        </td>
        <td class='linhadestaque'>
            Produto
        </td>
        <td class='linhadestaque'>
            Qtde
        </td>
        <td class='linhadestaque'>
            Saldo
        </td>
        <td class='linhadestaque'>
            Data
        </td>
        <td class='linhadestaque'>
            Observação
        </td>
        <td class='linhadestaque'>
            Login
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//O parâmetro id_produto_acabado, significa que essa Tela foi acessada de um outro lugar ...
            $url = "alterar.php?passo=1&id_oe=".$campos[$i]['id_oe'].'&id_produto_acabado='.$id_produto_acabado.'&pop_up='.$pop_up.'&iframe='.$iframe;
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_oe[]' value="<?=$campos[$i]['id_oe'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['id_oe'];?>
            </a>
            <?
                if($campos[$i]['status_finalizar'] == 1) echo '<font color="red"><b>(Finalizada)</b></font>';
            ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_s'], '/').' - '.substr($campos[$i]['data_s'], 11, 8);?>
        </td>
        <td>
        <?
            //Faço esse Tratamento porque podemos ter várias Qtde(s) de Saída ...
            $vetor_qtde_s = explode(',', $campos[$i]['qtde_s']);
            foreach($vetor_qtde_s as $j => $qtde_s_loop) {
                echo $qtde_s_loop.'<br/>';
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Faço esse Tratamento porque podemos ter vários PA(s) de Saída ...
            $vetor_produto_acabado = explode(',', $campos[$i]['id_produto_acabado_s']);
            foreach($vetor_produto_acabado as $j => $id_produto_acabado_loop) {
                echo intermodular::pa_discriminacao($id_produto_acabado_loop).'<br/>';
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao_s'])) echo "<img width='28' height='23' title='".$campos[$i]['observacao_s']."' src = '../../../imagem/olho.jpg'><br/>";
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario_resp_s']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['qtde_a_retornar'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado_e']);?>
        </td>
        <td>
            <?=$campos[$i]['qtde_e'];?>
        </td>
        <td>
        <?
            $qtde_saldo = $campos[$i]['qtde_a_retornar'] - $campos[$i]['qtde_e'];
            //Esse tratamento é justamente p/ corrigir quando o "à retornar" é maior do que o retorno devido ...
            if($qtde_saldo > 0) {
                echo $qtde_saldo;
            }else {
                echo 0;
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_e'], '/').' - '.substr($campos[$i]['data_e'], 11, 8);?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao_e'])) echo "<img width='28' height='23' title='".$campos[$i]['observacao_e']."' src = '../../../imagem/olho.jpg'><br/>";
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario_resp_e']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
        <?
/*Se esse parâmetro não estiver vazio, então eu exibo esse Botão porque significa que essa tela foi acessada 
de outro lugar ao invés de ter sido acessada de algum Menu ...*/
            if(!empty($id_produto_acabado)) {
                if(empty($_GET['pop_up'])) {//Se essa Tela foi aberta do modo Normal, então exibo os botões abaixo ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../classes/produtos_acabados/substituir_estoque_pa.php?id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
        <?
                }else {
                    echo '&nbsp;';
                }
            }else {
        ?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
            <input type='submit' name='cmd_imprimir' value='Imprimir OE' title='Imprimir OE' style='color:purple' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<!--************Controle de Tela************-->
<input type='hidden' name='hdd_atualizar_alterar' value='S'>
<!--****************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>