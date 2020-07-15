<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) COMPONENTE(S) P/ ESSE PRODUTO ACABADO.</font>";

if($passo == 1) {
    $data_sys   = date('Y-m-d H:i:s');
    $observacao = $_POST['txt_observacao'];
/*****************************************************************************************/
/***************************************** A��es *****************************************/
/*****************************************************************************************/
    if($_POST['opt_opcao'] == 1) {//Montando Jogo ...
        $itens_com_inadimplencia  = 'N';
/*****************************************************************************************/
/** Verifica��o Fundamental com os PA(s) do Loop p/ saber se poder dar Baixa e Gerar OE **/
/*****************************************************************************************/
        foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado_loop) {//Controle com o Estoque dos P.A(s) atrelados ...
            /**************************************************************************/
            /*****************************Fam�lia de Machos****************************/
            /**************************************************************************/
            if($_POST['id_familia'] == 9) {
                //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                $qtde_montar_desmontar = $_POST['txt_qtde'][$i] * -1;
            /**************************************************************************/
            /******************************Outras Fam�lias*****************************/
            /**************************************************************************/
            }else {
                //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                $qtde_montar_desmontar = $_POST['txt_qtde_montar_desmontar'] * $_POST['hdd_qtde_necessaria_por_pa'][$i] * -1;
            }
            
            /*Fam�lia de Machos sempre ter� que ter uma Qtde preenchida, as demais n�o tenho esse problema 
            porque os valores de Qtdes est�o em Hidden ...*/
            if(($_POST['id_familia'] == 9 && !empty($qtde_montar_desmontar)) || $_POST['id_familia'] != 9) {
                //2) P.A. atrelado em que eu estou acrescentando ou retirando algo no Estoque
                $resultado2 = estoque_acabado::verificar_manipulacao_estoque($id_produto_acabado_loop, $qtde_montar_desmontar);
                if($resultado2['retorno'] == 'nao executar') {
                    //Se existir algum P.A atrelado que furar com o controle ent�o, incrementa nesta vari�vel ...
                    $itens_com_inadimplencia = 'S';
                    break;//Sai fora do Loop
                }
            }
        }
/*****************************************************************************************/
        if($itens_com_inadimplencia == 'N') {//Significa que esta tudo em Ordem ...
            $id_produtos_acabados_de_saida = '';//Valor Inicial ...
            /*Gero uma O.E. pois este � um documento de rastreamento, 

            *** Gero uma OE p/ o PA Principal que esta retornando ...
            
            J� esse controle referente aos PA(s) do Loop "Sa�da", s� podem ser mencionados os PA(s) cuja 
            Qtde de Sa�da estiverem preenchidas ...*/
            foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado_loop) {//Controle com o Estoque dos P.A(s) atrelados ...
                /*Fam�lia de Machos sempre ter� que ter uma Qtde preenchida, as demais n�o tenho esse problema 
                porque os valores de Qtdes est�o em Hidden ...*/
                if(($_POST['id_familia'] == 9 && !empty($_POST['txt_qtde'][$i])) || $_POST['id_familia'] != 9) {
                    $id_produtos_acabados_de_saida.= $id_produto_acabado_loop.', ';
                    /**************************************************************************/
                    /*****************************Fam�lia de Machos****************************/
                    /**************************************************************************/
                    if(!empty($_POST['txt_qtde'][$i])) {
                        //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                        $qtdes_saida.= $_POST['txt_qtde'][$i].', ';
                    /**************************************************************************/
                    /******************************Outras Fam�lias*****************************/
                    /**************************************************************************/
                    }else {
                        //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                        $qtdes_saida.= $_POST['txt_qtde_montar_desmontar'] * $_POST['hdd_qtde_necessaria_por_pa'][$i].', ';
                    }
                }
            }
            
            $id_produtos_acabados_de_saida  = substr($id_produtos_acabados_de_saida, 0, strlen($id_produtos_acabados_de_saida) - 2);
            $qtdes_saida                    = substr($qtdes_saida, 0, strlen($qtdes_saida) - 2);
            
            $sql = "INSERT INTO `oes` (`id_oe`, `id_produto_acabado_s`, `id_produto_acabado_e`, `id_funcionario_resp_s`, `qtde_s`, `qtde_a_retornar`, `data_s`, `observacao_s`, `id_pas_atrelados`) VALUES (NULL, '$id_produtos_acabados_de_saida', '$_POST[id_produto_acabado]', '$_SESSION[id_funcionario]', '$qtdes_saida', '$_POST[txt_qtde_montar_desmontar]', '".date('Y-m-d H:i:s')."', '$observacao', '$id_produtos_acabados_de_saida') ";
            bancos::sql($sql);
            $id_oe = bancos::id_registro();

            estoque_acabado::atualizar_producao($_POST['id_produto_acabado']);

            /*****************************************************************************************/
            /***************************** Atualizando os PA(s) do Loop ******************************/
            /*****************************************************************************************/
            foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado_loop) {//Controle com o Estoque dos P.A(s) atrelados ...
                //Infelizmente o c�digo aqui tem que ser dobrado, porque esse controle � por Looping ...
                /**************************************************************************/
                /*****************************Fam�lia de Machos****************************/
                /**************************************************************************/
                if($_POST['id_familia'] == 9) {
                    //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                    $qtde_montar_desmontar = $_POST['txt_qtde'][$i] * -1;
                /**************************************************************************/
                /******************************Outras Fam�lias*****************************/
                /**************************************************************************/
                }else {
                    /*Aqui eu pego a Qtde que eu estou "montando ou desmontando" e m�ltiplico pela qtde de Pe�as 
                    que vai dentro de uma caixa do Item corrente*/
                    //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                    $qtde_montar_desmontar = $_POST['txt_qtde_montar_desmontar'] * $_POST['hdd_qtde_necessaria_por_pa'][$i] * -1;
                }
                /*Fam�lia de Machos sempre ter� que ter uma Qtde preenchida, as demais n�o tenho esse problema 
                porque os valores de Qtdes est�o em Hidden ...*/
                if(($_POST['id_familia'] == 9 && !empty($qtde_montar_desmontar)) || $_POST['id_familia'] != 9) {
                    //Gero registro de Baixa em cima do PA do Loop que estou enviando ...
                    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_oe`, `retirado_por`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$id_produto_acabado_loop', '$_SESSION[id_funcionario]', '', '$id_oe', '', '$qtde_montar_desmontar', '', 'M', '3', '$data_sys') ";
                    bancos::sql($sql);

                    estoque_acabado::atualizar($id_produto_acabado_loop);
                    estoque_acabado::controle_estoque_pa($id_produto_acabado_loop);
                    if($_POST['opt_opcao'] == 2) {//Significa que eu estou desmontando pelo menos 1 Jogo ...
                        //Busco a Refer�ncia do PA que est� retornando ...
                        $sql = "SELECT `referencia`, `discriminacao`  
                                FROM `produtos_acabados` 
                                WHERE `id_produto_acabado` = '$id_produto_acabado_loop' LIMIT 1 ";
                        $campos_pa  = bancos::sql($sql);
                        $observacao.= '<br/>'.$qtde_montar_desmontar.' - '.$campos_pa[0]['referencia'].' - '.$campos_pa[0]['discriminacao'];
                    }
                }
            }
?>
        <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
        <Script Language = 'JavaScript'>
            alert('OE N.� <?=$id_oe;?> INCLUIDA COM SUCESSO !\n\nJOGO MONTADO COM SUCESSO PARA ESTE P.A. !')
            var resposta = confirm('DESEJA IMPRIMIR ESTA OE ?')
            if(resposta == true) {
                nova_janela('../../producao/oes/relatorio/relatorio.php?chkt_oe[]=<?=$id_oe;?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
            }
        </Script>
<?
        }else {//Inadipl�ncia ...
?>
        <Script Language = 'JavaScript'>
            alert('H� ITEM(NS) DE P.A. ATRELADO(S) EM QUE N�O SE PODE MONTAR OU DESMONTAR JOGO !!!\nO ESTOQUE DISPON�VEL PODE ESTAR RACIONADO, MANIPULADO OU A QTDE DISPON�VEL EST� INCOMPAT�VEL !')
            window.close()
        </Script>
<?
        }
    }else {//Desmontando Jogos ...
        //Eu acrescento a Qtde que estou desmontando do P.A. Principal ...
        $qtde_pa_principal      = -$_POST['txt_qtde_montar_desmontar'];
        $resultado1 = estoque_acabado::verificar_manipulacao_estoque($_POST['id_produto_acabado'], $qtde_pa_principal);
        if($resultado1['retorno'] == 'executar') {//Significa que esta tudo em Ordem ...
            //Na situa��o de desmontagem concateno mais esse trecho junto j� da Observa��o digitada anteriormente ...
            $observacao.= ' .Retornando: ';
            
            /*****************************************************************************************/
            /***************************** Atualizando os PA(s) do Loop ******************************/
            /*****************************************************************************************/

            /**************************************************************************/
            /*****************************Fam�lia de Machos****************************/
            /**************************************************************************/
            if($_POST['id_familia'] == 9) {
                /*Gero uma O.E. pois este � um documento de rastreamento, 

                *** Gero uma OE p/ o PA do Loop que esta retornando ...*/
                foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado_loop) {
                    /*Fam�lia de Machos sempre ter� que ter uma Qtde preenchida, as demais n�o tenho esse problema 
                    porque os valores de Qtdes est�o em Hidden ...*/
                    if(($_POST['id_familia'] == 9 && !empty($_POST['txt_qtde'][$i])) || $_POST['id_familia'] != 9) {
                        $sql = "INSERT INTO `oes` (`id_oe`, `id_produto_acabado_s`, `id_produto_acabado_e`, `id_funcionario_resp_s`, `qtde_s`, `qtde_a_retornar`, `data_s`, `observacao_s`, `id_pas_atrelados`) VALUES (NULL, '$_POST[id_produto_acabado]', '$id_produto_acabado_loop', '$_SESSION[id_funcionario]', '$_POST[txt_qtde_montar_desmontar]', '".$_POST['txt_qtde'][$i]."', '".date('Y-m-d H:i:s')."', '$observacao', '".implode(',', $_POST['chkt_produto_acabado'])."') ";
                        bancos::sql($sql);

                        estoque_acabado::atualizar_producao($id_produto_acabado_loop);
                    }
                }
?>
            <Script Language = 'JavaScript'>
                alert('OE(S) INCLUIDA(S) COM SUCESSO !\n\nJOGO DESMONTADO COM SUCESSO PARA ESTE P.A. !')
                window.close()
            </Script>
<?
            }else {
                /**************************************************************************/
                /******************************Outras Fam�lias*****************************/
                /**************************************************************************/
                foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado_loop) {
                    /*Aqui eu pego a Qtde que eu estou "montando ou desmontando" e m�ltiplico pela qtde de Pe�as 
                    que vai dentro de uma caixa do Item corrente*/
                    
                    //Eu desconto das etapas a qtde em que eu estou montando do P.A. Principal
                    $qtde_montar_desmontar = $_POST['txt_qtde_montar_desmontar'] * $_POST['hdd_qtde_necessaria_por_pa'][$i];

                    //Gero registro de Baixa em cima do PA do Loop que estou enviando ...
                    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$id_produto_acabado_loop', '$_SESSION[id_funcionario]', '', '', '$qtde_montar_desmontar', '', 'M', '3', '$data_sys') ";
                    bancos::sql($sql);

                    estoque_acabado::atualizar($id_produto_acabado_loop);
                    estoque_acabado::controle_estoque_pa($id_produto_acabado_loop);
                }
            }

            //Gero registro de Baixa em cima do PA Principal que estou enviando ...
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$_POST[id_produto_acabado]', '$_SESSION[id_funcionario]', '', '', '$qtde_pa_principal', '$observacao', 'M', '3', '$data_sys') ";
            bancos::sql($sql);
            
            estoque_acabado::atualizar($_POST['id_produto_acabado']);
            estoque_acabado::controle_estoque_pa($_POST['id_produto_acabado']);
        }else {//Inadipl�ncia ...
?>
        <Script Language = 'JavaScript'>
            alert('H� ITEM(NS) DE P.A. ATRELADO(S) EM QUE N�O SE PODE MONTAR OU DESMONTAR JOGO !!!\nO ESTOQUE DISPON�VEL PODE ESTAR RACIONADO, MANIPULADO OU A QTDE DISPON�VEL EST� INCOMPAT�VEL !')
            window.close()
        </Script>
<?
        }
    }
?>
    <Script Language = 'JavaScript'>
        alert('MONTAGEM / DESMONTAGEM DE JOGO(S) FEITA COM SUCESSO !')
        //Atualizo a Tela de Baixo da qual chamou esse arquivo ...
        opener.parent.location = '../../producao/programacao/estoque/gerenciar/consultar.php<?=$parametro;?>'
        window.close()
    </Script>
<?
}else {
/******************************************************************************/
/*************************Primeiro N�vel da Cascata****************************/
/******************************************************************************/
/*1) Aqui eu busco a OC do P.A passado por par�metro para saber qual � o id_produto_acabado_custo ...

Tamb�m verifico se o PA que foi passado por par�metro tem a marca��o de visualiza��o ou seja se ele for 
componente de um outro, esse n�o pode ser exibido ...*/
    $sql = "SELECT gpa.`id_familia`, pa.`operacao_custo`, pa.`pecas_por_jogo`, pa.`mmv`, pa.`explodir_view_estoque` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    
    //Traz a Qtde Dispon�vel em Estoque do Produto Acabado Principal
    $estoque            = estoque_acabado::qtde_estoque($_GET['id_produto_acabado'], 1);
    $qtde_disponivel    = $estoque[3];
    
    /**************************************************************************/
    /*****************************Fam�lia de Machos****************************/
    /**************************************************************************/
    if($campos_pa[0]['id_familia'] == 9) {
        if($campos_pa[0]['explodir_view_estoque'] == 'S') $vetor_pas_atrelados = custos::pas_atrelados($_GET[id_produto_acabado]);//Aqui eu tamb�m retorno o pr�prio PA que foi passado por par�metro ...
        
        //Quando essa op��o esta marcado, o sistema aqui tamb�m acrescenta os PA(s) atrelado(s) manualmente ...
        if($_POST['chkt_mostrar_pas_atrelados_manualmente'] == 'S') {
            /*Na 1� Query eu trago todos os PA(s) que foram atrelados ao $id_produto_acabado que foi 
            passado por par�metro e no outro SQL trago ele pr�prio ...*/
            $sql = "SELECT 
                    IF(ps.`id_produto_acabado_1` = '$_GET[id_produto_acabado]', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_produto_acabado 
                    FROM `pas_substituires` ps 
                    WHERE 
                    (ps.`id_produto_acabado_1` = '$_GET[id_produto_acabado]') 
                    OR (ps.`id_produto_acabado_2` = '$_GET[id_produto_acabado]') 
                    UNION 
                    SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' ";
            $campos_pas_substituicao = bancos::sql($sql);
            $linhas_pas_substituicao = count($campos_pas_substituicao);
            for($i = 0; $i < $linhas_pas_substituicao; $i++) {
                if(is_array($vetor_pas_atrelados)) $vetor_pas_atrelados[] = $campos_pas_substituicao[$i]['id_produto_acabado'];
                $vetor_pas_atrelados    = array_unique($vetor_pas_atrelados);//Retiro os elementos j� existentes no Vetor ...
            }
        }

//Aqui � um SQL que traz a Fus�o de todos os Custos do PA passado por par�metro - Pa(s) Atrelado(s) ...
        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`preco_unitario`, pa.`mmv`, 
                pa.`pecas_por_jogo`, pa.`status_custo` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
                WHERE pa.`id_produto_acabado` IN (".implode(',', $vetor_pas_atrelados).") 
                ORDER BY pa.`referencia` ";
    }else {
    /**************************************************************************/
    /******************************Outras Fam�lias*****************************/
    /**************************************************************************/
        $operacao_custo = $campos[0]['operacao_custo'];
        
        //2) Busco qual � o id_produto_acabado_custo "Custo do PA" passado por par�metro ...
        $sql = "SELECT `id_produto_acabado_custo` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
                AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        $id_produto_acabado_custo   = $campos[0]['id_produto_acabado_custo'];
        
/*Aqui eu trago todos os PA(s) atrelado(s) desde que n�o sejam da Fam�lia Componentes de Produ��o Interna, 
Componentes de M�quina ou M�o de Obra ...*/
        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pp.`id_pac_pa` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
                WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.`id_pac_pa` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//N�o existe nenhum PA atrelado desse PA passado por par�metro ...
    ?>        
        <Script Language = 'JavaScript'>
            alert('N�O SE PODE MONTAR OU DESMONTAR JOGO COM ESTE P.A. !!!\n\nN�O EXISTE(M) ATRELAMENTO(S) !')
            window.close()
        </Script>
    <?
            exit;
        }else {//Existe pelo menos um PA atrelado desse PA passado por par�metro ...
            for($i = 0; $i < $linhas; $i++) {
                if($campos[$i]['operacao_custo'] == 0) {//Se a OC do PA do Loop atrelado = 'Industrial', pego o Custo desse PA ...
    /******************************************************************************/
    /**************************Segundo N�vel da Cascata****************************/
    /******************************************************************************/
    /*Aqui eu busco o id_produto_acabado_custo "Custo do PA" desse item do Loop, esse n�vel � o que 
    me interessa para apresenta��o de Dados na Tela ...*/
                    $sql = "SELECT `id_produto_acabado_custo` 
                            FROM `produtos_acabados_custos` 
                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                            AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' ";
                    $campos_custo = bancos::sql($sql);
    /*Aqui eu trago todos os PA(s) atrelados desse Custo desde que n�o sejam da Fam�lia Componentes de 
    Produ��o Interna, Componentes de M�quina ou M�o de Obra ...*/
                    $sql = "SELECT pp.`id_pac_pa` 
                            FROM `pacs_vs_pas` pp 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
                            WHERE pp.`id_produto_acabado_custo` = '".$campos_custo[0]['id_produto_acabado_custo']."' 
                            ORDER BY pp.`id_pac_pa` ";
                    $campos_etapa7 = bancos::sql($sql);
                    $linhas_etapa7 = count($campos_etapa7);
                    if($linhas_etapa7 > 0) {//Encontrou pelo menos um PA nessa condi��o, trago o PA q est� no "Segundo N�vel da Cascata" ...
                        for($j = 0; $j < $linhas_etapa7; $j++) $vetor_pas_atrelados[] = $campos_etapa7[$j]['id_pac_pa'];
                    }else {//Como n�o encontrou um PA nessa condi��o, trago o pr�prio PA mesmo "Primeiro N�vel da Cascata" ...
                        $vetor_pas_atrelados[] = $campos[$i]['id_pac_pa'];
                    }
    /******************************************************************************/
                }else {//OC do PA do Loop atrelado, trago o pr�prio PA mesmo "Primeiro N�vel da Cascata" ...
                    $vetor_pas_atrelados[] = $campos[$i]['id_pac_pa'];
                }
            }
        }
/*Aqui � um SQL que traz a Fus�o de todos os Custos do PA passado por par�metro - atrelados ...*/
        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`preco_unitario`, pa.`mmv`, 
                pa.`status_custo`, pp.`id_pac_pa`, pp.`qtde` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` NOT IN (23, 24, 25) 
                WHERE pp.`id_pac_pa` IN (".implode(',', $vetor_pas_atrelados).") 
                ORDER BY pa.`referencia` ";
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<title>.:: Montar / Desmontar Jogo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Quantidade � Montar / Desmontar
    if(!texto('form', 'txt_qtde_montar_desmontar', '1', '0123456789-.', 'QUANTIDADE � MONTAR / DESMONTAR', '1')) {
        return false
    }
//Verifica se o usu�rio digitou a qtde de montar / desmontar = 0
    if(document.form.txt_qtde_montar_desmontar.value == '' || document.form.txt_qtde_montar_desmontar.value == 0) {
        alert('QUANTIDADE � MONTAR / DESMONTAR INV�LIDA !\nQUANTIDADE � MONTAR / DESMONTAR = 0 !!!')
        document.form.txt_qtde_montar_desmontar.focus()
        document.form.txt_qtde_montar_desmontar.select()
        return false
    }
    
    var elementos = document.form.elements
    
    if(typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_produto_acabado[]'].length)
    }

    var id_familia = eval('<?=$campos_pa[0]['id_familia'];?>')
    
    /**************************************************************************/
    /*****************************Fam�lia de Machos****************************/
    /**************************************************************************/
    if(id_familia == 9) {
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('txt_qtde'+i).value != '') {//Qtde Preenchida ...
                var estoque_disponivel  = eval(strtofloat(document.getElementById('txt_estoque_disponivel'+i).value))
                var qtde                = (document.getElementById('txt_qtde'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_qtde'+i).value))
                
                /*Nunca a Qtde Digitada poder� ser maior do que o Estoque Dispon�vel quando o usu�rio 
                estiver "Montando Jogos", porque isso retira do Estoque Dispon�vel ...*/
                if(qtde > estoque_disponivel && document.form.opt_opcao[0].checked) {
                    alert('QUANTIDADE � MONTAR / DESMONTAR INV�LIDA !\n\nQUANTIDADE MAIOR QUE A DE ESTOQUE DISPON�VEL !!!')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
            }
        }
        
        var qtde_blanks_a_retornar      = document.getElementById('txt_qtde_blanks_a_retornar').value
        var qtde_blanks_a_enviar_total  = document.getElementById('txt_qtde_blanks_a_enviar_total').value
                
        if(qtde_blanks_a_retornar != qtde_blanks_a_enviar_total) {
            alert('A QTDE DE BLANKS ENVIADOS E � RETORNAR EST�O DIVERGENTES !!!')
            return false
        }
    /**************************************************************************/
    /******************************Outras Fam�lias*****************************/
    /**************************************************************************/
    }else {
        //Significa que eu estou querendo montar pelo menos 1 Jogo ...
        if(document.form.opt_opcao[0].checked == true) {
            for(var i = 0; i < linhas; i++) {
                var estoque_disponivel  = eval(strtofloat(document.getElementById('txt_estoque_disponivel'+i).value))
                var qtde                = (document.getElementById('txt_qtde'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_qtde'+i).value))
                
                var qtde_montar_desmontar = eval(document.form.txt_qtde_montar_desmontar.value)
                //Aqui eu verifico se a qtde solicitada pelo usu�rio * qtde_pcs_prod_loop � > do que o q est� em est dispon�vel
                if((qtde_montar_desmontar * qtde) > estoque_disponivel) {
                    alert('QUANTIDADE � MONTAR / DESMONTAR INV�LIDA !\n\nQUANTIDADE MAIOR QUE A DE ESTOQUE DISPON�VEL !!!')
                    document.form.txt_qtde_montar_desmontar.focus()
                    document.form.txt_qtde_montar_desmontar.select()
                    return false
                }
            }
        }else {//Significa que eu estou querendo desmontar pelo menos 1 Jogo ...
            var qtde_montar_desmontar   = eval(document.form.txt_qtde_montar_desmontar.value)
            var qtde_disponivel         = eval(strtofloat('<?=$qtde_disponivel;?>'))

            if(qtde_montar_desmontar > qtde_disponivel) {
                alert('QUANTIDADE � MONTAR / DESMONTAR INV�LIDA !\n\nQUANTIDADE MAIOR QUE A DO ESTOQUE DISPON�VEL !!!')
                document.form.txt_qtde_montar_desmontar.focus()
                document.form.txt_qtde_montar_desmontar.select()
                return false
            }
        }
    }
//Aqui eu travo o bot�o salvar para o usu�rio n�o ficar clicando v�rias vezes no bot�o ...
    document.form.cmd_salvar_fechar.disabled = true
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    return limpeza_moeda('form', 'txt_qtde_montar_desmontar, ')
}

function calcular_qtde_blanks_a_retornar() {
    if(typeof(document.form.txt_qtde_blanks_a_retornar) == 'object') {
        if(document.form.txt_qtde_montar_desmontar.value != '') {
            var pecas_por_jogo                              = eval('<?=$campos_pa[0]['pecas_por_jogo']?>')
            document.form.txt_qtde_blanks_a_retornar.value  = document.form.txt_qtde_montar_desmontar.value * pecas_por_jogo
        }else {
            document.form.txt_qtde_blanks_a_retornar.value = ''
        }
    }
}

function calcular_qtde_blanks_a_enviar(indice) {
    if(document.getElementById('txt_qtde'+indice).value != '' && document.getElementById('txt_qtde'+indice).value > 0) {//Qtde Preenchida ...
        var qtde_necessaria_por_pa  = (document.getElementById('hdd_qtde_necessaria_por_pa'+indice).value == '') ? 0 : eval(strtofloat(document.getElementById('hdd_qtde_necessaria_por_pa'+indice).value))
        var qtde                    = (document.getElementById('txt_qtde'+indice).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_qtde'+indice).value))
        document.getElementById('txt_qtde_blanks_a_enviar'+indice).value = (qtde_necessaria_por_pa * qtde)
    }else {
        document.getElementById('txt_qtde_blanks_a_enviar'+indice).value = ''
    }
    
    var elementos = document.form.elements
        
    if(typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_produto_acabado[]'].length)
    }
    
    var qtde_blanks_a_enviar_total = 0

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_qtde'+i).value != '' && document.getElementById('txt_qtde'+i).value > 0) {
            qtde_blanks_a_enviar_total+= eval(strtofloat(document.getElementById('txt_qtde_blanks_a_enviar'+i).value))
        }
    }
    document.getElementById('txt_qtde_blanks_a_enviar_total').value = qtde_blanks_a_enviar_total
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        var tela1 = eval(document.form.tela1.value)//Referente aos frames da Tela da parte de baixo
        var tela2 = eval(document.form.tela2.value)//Referente aos frames da Tela da parte de baixo
//Atualiza a parte de Itens se existir
        if(typeof(tela1) == 'object') tela1.document.form.submit()
//Atualiza a parte de Rodap� se existir
        if(typeof(tela2) == 'object') tela2.document.form.submit()
    }
}

function controlar_label() {
//Se estiver marcada a Op��o Montar Jogos ent�o no r�tulo aparecer� Produto Enviado ...
    if(document.form.opt_opcao[0].checked == true) {
        document.form.rotulo1.value = 'Produto � Retornar:'
        document.form.rotulo2.value = 'Produto Enviado'
//Se estiver marcada a Op��o Desontar Jogos ent�o no r�tulo aparecer� Produto � Retornar ...
    }else {
        document.form.rotulo1.value = 'Produto Enviado:'
        document.form.rotulo2.value = 'Produto � Retornar'
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_montar_desmontar.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<input type='hidden' name='tela1' value='<?=$tela1;?>'>
<input type='hidden' name='tela2' value='<?=$tela2;?>'>
<input type='hidden' name='id_familia' value='<?=$campos_pa[0]['id_familia'];?>'>
<!--*************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' >
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'substituir_estoque_pa.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Montar / Desmontar Jogo(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font color='yellow'>
                <input type='text' name='rotulo1' value='Produto � Retornar:' size='16' style='color:yellow;font-size:12' class='caixadetexto2' disabled>
            </font>
            <?
                echo intermodular::pa_discriminacao($_GET['id_produto_acabado'], 0);
                
                //Busco a OC do PA p/ apresentar ...
                $sql = "SELECT IF(`operacao_custo`, 'Industrial', 'Revenda') AS operacao_custo 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
                $campos_operacao_custo = bancos::sql($sql);
            ?>
            <font color='black'>
                - OC: <?=$campos_operacao_custo[0]['operacao_custo'];?>
            </font>
            &nbsp;
            <input type='button' name='cmd_atrelar_pa' value='Atrelar PA' title='Atrelar PA' onclick="nova_janela('atrelar_pa.php?id_pa_a_ser_atrelado=<?=$_GET['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <b>Estoque Dispon�vel:</b>
            &nbsp;
            <?=number_format($qtde_disponivel, 2, ',', '.');?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Pe�as por Jogo:</b>
            &nbsp;
            <?=number_format($campos_pa[0]['pecas_por_jogo'], 2, ',', '.');?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>M.M.V.:</b>
            &nbsp;
            <?=number_format($campos_pa[0]['mmv'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <b>Qtde � Montar / Desmontar: </b>
            &nbsp;
            <input type='text' name='txt_qtde_montar_desmontar' title='Digite a Qtde Montar / Desmontar' size='8' maxlength='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_qtde_blanks_a_retornar()" class='caixadetexto'>
            <?
                /**************************************************************************/
                /*****************************Fam�lia de Machos****************************/
                /**************************************************************************/
                if($campos_pa[0]['id_familia'] == 9) {//Essa caixa s� aparece quando for dessa Fam�lia ...
            ?>  
            <input type='text' name='txt_qtde_blanks_a_retornar' id='txt_qtde_blanks_a_retornar' title='Qtde de Blanks � Retornar' size='8' maxlength='6' class='textdisabled' disabled> BLANK(S)
            <?
                }
            ?>
            &nbsp;-&nbsp;
            <input type='radio' name='opt_opcao' id='label1' value='1' title='Montar Jogos' onclick='controlar_label()' checked>
            <label for='label1'>Montar Jogos</label>
            &nbsp;
            <input type='radio' name='opt_opcao' id='label2' value='2' title='Desmontar Jogos' onclick='controlar_label()'>
            <label for='label2'>
                Desmontar Jogos
                <font color='red' size='2'><b>(N�O GERA OE)</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font title='Opera��o de Custo'>
                <b><i>O.C.</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='M�dia Mensal de Vendas' style='cursor:help'>
                <b><i>M.M.V.</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='Estoque Dispon�vel'>
                <b><i>Est Disp</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. PA - Discrimina��o</i></b>
            <input type='text' name='rotulo2' value='Produto Enviado' size='14' style='color:black; font-weight:bold' class='caixadetexto2' disabled>
            &nbsp;
            <?
                if($_POST['chkt_mostrar_pas_atrelados_manualmente'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_mostrar_pas_atrelados_manualmente' id='chkt_mostrar_pas_atrelados_manualmente' value='S' onclick='document.form.submit()' class='checkbox' <?=$checked;?>>
            <label for='chkt_mostrar_pas_atrelados_manualmente'>
                Mostrar PA(s) atrelados manualmente
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {//Industrializa��o
        ?>
                <p title='Industrializa��o'>I</p>
        <?
            }else {//Revenda
        ?>
                <p title='Revenda'>R</p>
        <?	
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
        </td>
<?
//Traz a Qtde Dispon�vel em Estoque do Produto Acabado do Loop
        $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], '1');
        $estoque_disponivel = $estoque_produto[3];
?>
        <td align='center'>
            <?=number_format($estoque_disponivel, 2, ',', '.');?>
            <input type='hidden' name='txt_estoque_disponivel[]' id='txt_estoque_disponivel<?=$i;?>' value='<?=$estoque_disponivel;?>'>
        </td>
        <td align='center'>
            <?
                /**************************************************************************/
                /*****************************Fam�lia de Machos****************************/
                /**************************************************************************/
                if($campos_pa[0]['id_familia'] == 9) {
                    $type   = 'text';
                    $qtde   = intval($campos[$i]['pecas_por_jogo']);
                /**************************************************************************/
                /******************************Outras Fam�lias*****************************/
                /**************************************************************************/
                }else {
                    $type   = 'hidden';
                    $qtde   = intval($campos[$i]['qtde']);
                }
                echo $qtde;
            ?>
            <input type='hidden' name='hdd_qtde_necessaria_por_pa[]' id='hdd_qtde_necessaria_por_pa<?=$i;?>' value='<?=$qtde;?>'>
            <input type='<?=$type;?>' name='txt_qtde[]' id='txt_qtde<?=$i;?>' title='Digite a Qtde' size='8' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_qtde_blanks_a_enviar('<?=$i;?>')" autocomplete='off' class='caixadetexto'>
            <?
                /**************************************************************************/
                /*****************************Fam�lia de Machos****************************/
                /**************************************************************************/
                if($campos_pa[0]['id_familia'] == 9) {//Essa caixa s� aparece quando for dessa Fam�lia ...
            ?>
            <input type='text' name='txt_qtde_blanks_a_enviar[]' id='txt_qtde_blanks_a_enviar<?=$i;?>' title='Qtde de Blanks � Enviar' size='8' maxlength='6' class='textdisabled' disabled>
            <?
                }
            ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['status_custo'] == 1) {//J� est� liberado
        ?>
            <font title='Custo Liberado'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
            </font>
        <?
            }else {//N�o est� liberado
        ?>
            <font title='Custo n�o Liberado' color='red'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
            </font>
        <?
            }
        ?>
            <input type='hidden' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td colspan='5'>
            Observa��o: &nbsp;<textarea name='txt_observacao' cols='100' rows='5' maxlength='500' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4' align='right'>
            <input type='text' name='txt_qtde_blanks_a_enviar_total' id='txt_qtde_blanks_a_enviar_total' title='Qtde de Blanks � Enviar' size='8' maxlength='6' class='textdisabled' disabled>
            &nbsp;&nbsp;
        </td>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'substituir_estoque_pa.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_montar_desmontar.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar_fechar' value='Salvar e Fechar' title='Salvar e Fechar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* Precisa melhorar a seguran�a.

Se a Fam�lia for Machos:

1) Na parte debaixo da tela, apresentamos apenas os PA(s) atrelados � 7� Etapa do PA Principal e desde que a OC
de Custo seja igual a OC do PA Principal.

2) Se marcarmos o checkbox <b>Mostrar PA(s) atrelados manualmente</b> al�m do que foi retornado da cla�sula 1,
acrescentamos os PA(s) atrelado(s) manualmente.
</pre>
<?
    }
}
?>