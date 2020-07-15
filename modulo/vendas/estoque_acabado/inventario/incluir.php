<?
require('../../../../lib/segurancas.php');

//Se essa Tela foi aberta de modo normal ent�o exibo o Menu abaixo normalmente, se for aberta como Pop-UP N�O ...
if(empty($pop_up)) require '../../../../lib/menu/menu.php';

require('../../../../lib/comunicacao.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>INVENT�RIO REALIZADO COM SUCESSO.</font>";

if($passo == 1) {
    $qtde_pas_inventariados = 0;
    $data_sys               = date('Y-m-d H:i:s');
    
    for($i = 0; $i < count($_POST['hdd_produto_acabado']); $i++) {
//Aqui eu busco o Valor de Estoque Real antes de fazer a atualiza��o para o Novo Valor ...
        $vetor = estoque_acabado::qtde_estoque($_POST['hdd_produto_acabado'][$i]);
        $estoque_real_antigo = $vetor[0];
            
//Atualizo o Estoque com o Novo Valor ...
        $sql = "UPDATE `estoques_acabados` SET `qtde` = '".$_POST['txt_novo_er'][$i]."' WHERE `id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
        $qtde_a_gravar  = $_POST['txt_novo_er'][$i] - $estoque_real_antigo;//Essa vari�vel ser� gravada no Rel de Baixas.
        $sinal          = ($qtde_a_gravar >= 0) ? '+' : '-';

//Aqui eu busco alguns dados do PA ...
        $sql = "SELECT pa.`referencia`, pa.`discriminacao`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' LIMIT 1 ";
        $campos_sigla   = bancos::sql($sql);
        $sigla          = $campos_sigla[0]['sigla'];
//Informa��es a serem passadas por e-mail ...
        $conteudo_email.= 'O Produto Acabado => <b>'.$campos_sigla[0]['referencia'].' - '.$campos_sigla[0]['discriminacao'].'</b><p/>';
        $conteudo_email.= 'foi inventariado com Est. Prateleira = <b>'.$_POST['txt_estoque_prateleira'][$i].' '.$sigla.'</b>, Ent. Antecipada = <b>'.$_POST['txt_entrada_antecipada'][$i].' '.$sigla.'</b>,<p/>';
        $conteudo_email.= 'ER alterado para <b> ('.$sinal.''.$qtde_a_gravar.') '.$sigla.', tinha ER = <b>'.$estoque_real_antigo.' '.$sigla.'</b> e agora ER = '.($estoque_real_antigo + $qtde_a_gravar).' '.$sigla.'</b><p/>';
        
//Busca dados do �ltimo Invent�rio feito nesse PA ...
        $sql = "SELECT `qtde`, DATE_FORMAT(SUBSTRING(`data_sys`, 1, 10), '%d/%m/%Y') AS data_lancamento 
                FROM `baixas_manipulacoes_pas` 
                WHERE `id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' 
                AND `acao` = 'I' ORDER BY `id_baixa_manipulacao_pa` DESC LIMIT 1 ";
        $campos_inventario = bancos::sql($sql);
        if(count($campos_inventario) == 0) {//Significa que nunca existiu um Invent�rio deste PA no ERP ...
            $conteudo_email.= '<br><font color="red">N�o existe Invent�rio Anterior para este PA, devido o mesmo nunca ter sido inventariado no ERP.</b></font><br>';
        }else {//Se existiu pelo menos 1 Invent�rio, eu apresento os dados do �ltimo Invent�rio ...
            $conteudo_email.= '<br><font color="red">Invent�rio anterior: '.$campos_inventario[0]['data_lancamento'].' - Inventariado com ER = <b>'.intval($campos_inventario[0]['qtde']).' '.$sigla.'</b></font><br/>';
        }
//Procedimento normal para registro da Entrada ...
        $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `qtde`, `observacao`, `acao`, `status`, `data_sys`) VALUES (NULL, '".$_POST['hdd_produto_acabado'][$i]."', '$_SESSION[id_funcionario]', '$qtde_a_gravar', 'Invent�rio feito para Corre��o de Estoque para ER = ".$_POST['txt_novo_er'][$i]." $sigla.', 'I', '1', '$data_sys') ";
        bancos::sql($sql);
        estoque_acabado::atualiza_qtde_pendente($_POST['hdd_produto_acabado'][$i]);
//Eu preciso passar essa fun��o p/ que alguns campos na tabela de estoque acabado j� se atualizem automaticamente ...
        estoque_acabado::controle_estoque_pa($_POST['hdd_produto_acabado'][$i]);
//Aqui verifico se o PA � um PI "PIPA" para poder executar a fun��o abaixo ...
        $sql = "SELECT `id_produto_insumo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_pipa[0]['id_produto_insumo']);
        
        $qtde_pas_inventariados++;
    }
/*Assim que foi feito o Invent�rio, independente de o usu�rio ter preenchido ou n�o a Qtde do PA Racionado 
eu j� desraciono todos os PA(s) que estavam Racionado(s) ...*/
    if(strpos($nome_completo, ',') === false) {//Nesse caso � existe a v�rgula na vari�vel $id_pas_racionados ...
        $sql = "UPDATE `estoques_acabados` SET `racionado` = '0' WHERE `id_produto_acabado` IN ($id_pas_racionados) ";
    }else {//Aqui j� existe a v�rgula ...
        $sql = "UPDATE `estoques_acabados` SET `racionado` = '0' WHERE `id_produto_acabado` IN (".implode(',', $id_pas_racionados).") ";
    }
    bancos::sql($sql);
/*****************************************E-mail*****************************************/
    if($qtde_pas_inventariados > 0) {//Se existir pelo menos 1 Produto Inventariado ent�o ...
//Aqui eu mando um e-mail de quem Inventariou o(s) Produto(s) ...
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_inventariou  = $campos_login[0]['login'];
//Aqui eu mando um e-mail informando quem Inventariou e quais os Produtos que foram Inventariados ...
        $destino = $inventario_pas;
        $mensagem_email = 'Rela��o de Produto(s) Acabado(s) que foi(ram) Inventariado(s): <p>';
        $mensagem_email.= $conteudo_email;
        $mensagem_email.= '<br><b>Login: </b>'.$login_inventariou.' - '.date('d/m/Y H:i:s').'<p>'.$PHP_SELF;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Invent�rio de Estoque', $mensagem_email);
    }
/****************************************************************************/
?>
    <Script Language = 'JavaScript'>
        var id_produto_acabado = '<?=$id_produto_acabado;?>'
        if(id_produto_acabado != '') {//Significa que essa tela foi acessada como Pop-UP ...
            window.location = 'incluir.php?id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=<?=$pop_up;?>&valor=2'
        }else {//Significa que essa tela foi acessada por dentro do pr�prio Menu de Invent�rio ...
            window.location = 'incluir.php<?=$parametro;?>&valor=2'
        }
    </Script>
<?
}else {
/*Esse par�metro de n�vel vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisi��o desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';

    if(!empty($id_produto_acabado)) {//Significa que essa tela foi acessada como Pop-UP ...
        $vetor_pas_atrelados = custos::pas_atrelados($id_produto_acabado);//Aqui eu tamb�m retorno o pr�prio PA que foi passado por par�metro ...
        
        $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pa.`id_produto_acabado` IN (".implode(',', $vetor_pas_atrelados).") 
                AND pa.`ativo` = '1' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
    }else {//Significa que essa tela foi acessada por dentro do pr�prio Menu de Invent�rio ...
        //Aqui eu vou puxar a Tela �nica de Filtro de Produtos Acabados que serve para o Sistema Todo ...
        require('../../../classes/produtos_acabados/tela_geral_filtro.php');
    }
    
    if($linhas > 0) {//Se retornar pelo menos 1 registro na Fun��o ...
    //Fun��o ...
    /**********Atualizando o campo de Racionado**********/
        if($_GET['valor'] != 2) {//Aki � p/ n�o racionar/desracionar quando acabar de voltar do Proc. de Invent�rio ...
//Aqui eu atualizo o campo de racionado do PA ...
            if(!empty($_GET['id_produto_acabado_racionado']) && $_GET['racionado_future_param'] != '') {
                $sql = "UPDATE `estoques_acabados` SET `racionado` = '$_GET[racionado_future_param]' WHERE `id_produto_acabado` = '$_GET[id_produto_acabado_racionado]' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
/********************************************************/
?>
<html>
<head>
<title>.:: Incluir Invent�rio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') ? 1 : (elementos['hdd_produto_acabado[]'].length)
//Aqui eu verifico se existe algum item em que o Estoque Real � menor do que o Estoque Separado ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_novo_er'+i).value != '') {
            var novo_er     = eval(strtofloat(document.getElementById('txt_novo_er'+i).value))
            var separado    = eval(document.getElementById('txt_separado'+i).value)
            if(novo_er < separado) {//Caso exista algum Item em que o Real � menor, o Sistema retorna uma msn de Erro ...
                alert('NOVO ESTOQUE REAL INV�LIDO !!!\nNOVO ESTOQUE REAL POSSUI VALOR MENOR DO QUE O SEPARADO !')
                document.getElementById('txt_novo_er'+i).focus()
                document.getElementById('txt_novo_er'+i).select()
                return false
            }
            if(novo_er == 0) {//Caso exista algum Item em que o Real � igual a Zero ...
                var resposta = confirm('O NOVO ESTOQUE REAL EST� IGUAL A ZERO !!!\nTEM CERTEZA DE QUE DESEJA LAN�AR ESSE VALOR COMO SENDO ZERO ?')
                if(resposta == false) {
                    document.getElementById('txt_novo_er'+i).focus()
                    document.getElementById('txt_novo_er'+i).select()
                    return false
                }
            }
        }
    }
//Aqui prepara p/ gravar no Banco de Dados ...	
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_novo_er'+i).value != '') {
            document.getElementById('txt_novo_er'+i).value = eval(strtofloat(document.getElementById('txt_novo_er'+i).value))
            document.getElementById('txt_novo_er'+i).disabled = false//Habilito a caixa p/ gravar no BD ...
        }
    }
//Aqui eu desabilito o bot�o Salvar p/ n�o acontecer de o usu�rio clicar v�rias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function calcular_novo_er(indice, estoque_excedente) {
    //Controle com os objetos ...
    if(document.getElementById('txt_estoque_prateleira'+indice).value != '' || document.getElementById('txt_entrada_antecipada'+indice).value != '') {
        /*Se o usu�rio digitou uma Qtde de Estoque na Prateleira, ent�o habilito os hiddens p/ n�o dar erro de 
        �ndice na outra tela depois que submeter ...*/
        document.getElementById('txt_estoque_excedente'+indice).disabled    = false
        document.getElementById('txt_separado'+indice).disabled             = false
        document.getElementById('hdd_produto_acabado'+indice).disabled      = false
    }else {
        /*Se o usu�rio zerou a Qtde de Estoque digitada na Prateleira, ent�o desabilito os hiddens p/ n�o dar 
        erro de �ndice na outra tela depois que submeter ...*/
        document.getElementById('txt_estoque_excedente'+indice).disabled    = true
        document.getElementById('txt_separado'+indice).disabled             = true
        document.getElementById('hdd_produto_acabado'+indice).disabled      = true
    }
    
    var estoque_excedente   = eval(strtofloat(document.getElementById('txt_estoque_excedente'+indice).value))
    var separado            = eval(strtofloat(document.getElementById('txt_separado'+indice).value))
    var estoque_prateleira  = (document.getElementById('txt_estoque_prateleira'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_estoque_prateleira'+indice).value)) : 0
    var entrada_antecipada  = (document.getElementById('txt_entrada_antecipada'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_entrada_antecipada'+indice).value)) : 0
    
    document.getElementById('txt_novo_er'+indice).value = estoque_excedente + separado + estoque_prateleira + entrada_antecipada
    document.getElementById('txt_novo_er'+indice).value = arred(document.getElementById('txt_novo_er'+indice).value, 2, 1)
}

function atualizar_racionado(id_produto_acabado_racionado, racionado_future_param) {
    var id_produto_acabado = '<?=$id_produto_acabado;?>'
    
    if(id_produto_acabado != '') {//Significa que essa tela foi acessada como Pop-UP ...
        window.location = 'incluir.php?id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=<?=$pop_up;?>&id_produto_acabado_racionado='+id_produto_acabado_racionado+'&racionado_future_param='+racionado_future_param
    }else {//Significa que essa tela foi acessada por dentro do pr�prio Menu de Invent�rio ...
        window.location = 'incluir.php<?=$parametro;?>&id_produto_acabado=<?=$id_produto_acabado;?>&id_produto_acabado_racionado='+id_produto_acabado_racionado+'&racionado_future_param='+racionado_future_param
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Incluir Invent�rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='13'>
            <font color='darkblue' size='-2'>
                <b>Regras:<p/>

                1) No campo Estoque da Prateleira � partir de agora s� poremos o que est� Dispon�vel para separa��o, n�o incluindo o Excedente, 
                nem o que est� separado para clientes, nem o que j� foi dado entrada antecipada;<br/>
                2) Se existe material separado fisicamente para algum cliente e j� est� incluso em uma NF, esta quantidade n�o deve ser utilizada para invent�rio e esta tamb�m j� aparece na coluna �NFs Faturadas�, que mostra as NFs c/ Status < Empacotadas;<br/>
                3) A quantidade do Vale n�o entra no invent�rio;<br/>
                4) O novo E.R. passa a ser a soma do E.E + Separado + E.P + Entrada Antecipada.</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <?=genericas::order_by('pa.referencia', 'Ref.', 'Refer�ncia', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.discriminacao', 'Discrimina��o', '', $order_by, '../../../../');?> &nbsp;
        </td>
        <td>
            �ltimo Invent�rio
        </td>
        <td>
            Racionado
        </td>
        <td>
            E.R.
        </td>
        <td>
            E.D.
        </td>
        <td>
            Vale
        </td>
        <td>
            NFs Faturadas
        </td>
        <td>
            <font title='Estoque Excedente' style='cursor:help'>
                Est. Excedente
            </font>
        </td>
        <td>
            Separado
        </td>
        <td>
            <font title='Estoque na Prateleira' style='cursor:help'>
                Est. Prateleira
            </font>
        </td>
        <td>
            <font title='Entrada Antecipada' style='cursor:help'>
                Ent. Antecipada
            </font>
        </td>
        <td>
            Novo E.R.
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            &nbsp;
            <a href="javascript:nova_janela('../../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS_ULTIMOS_6_MESES', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src = '../../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - �ltimos 6 meses' alt='Visualizar Pedidos - �ltimos 6 meses' border='0'>
            </a>
            <?
                $url = $niveis.'../manipular_estoque/consultar.php?passo=1';
                /*Mudan�a feita em 17/05/2016 - Antigamente os detalhes da consulta s� eram feitos pela 
                refer�ncia independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que s�o similares em seu cadastro na parte de refer�ncia, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica invi�vel 
                vindo todos os ESP�s do Sistema e trazendo informa��es que n�o tinham nada haver ...*/
                if($campos[$i]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em espec�fico ...
                    $url.= '&id_produto_acabado='.$campos[$i]['id_produto_acabado'].'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Refer�ncia ...
                    $url.= '&txt_referencia='.$campos[$i]['referencia'].'&pop_up=1';
                }
            ?>
        &nbsp;
            <img src = '../../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipula��es' alt='Baixas / Manipula��es' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
        </td>
        <td>
        <?
            $sql = "SELECT DATE_FORMAT(SUBSTRING(`data_sys`, 1, 10), '%d/%m/%Y') AS data_lancamento 
                    FROM `baixas_manipulacoes_pas` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `acao` = 'I' ORDER BY id_baixa_manipulacao_pa DESC LIMIT 1 ";
            $campos_baixa_manipulacao = bancos::sql($sql);
            if(count($campos_baixa_manipulacao) == 1) {
                echo $campos_baixa_manipulacao[0]['data_lancamento'];
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
//Aqui eu verifico se o Item est� racionado ...
            $sql = "SELECT racionado 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_estoque = bancos::sql($sql);
            /************Macete para Tratar com o PA************/
            $racionado_future_param = $campos_estoque[0]['racionado'];//Primeiro iguala a Vari�vel com o Valor do PA ...
            //Se Racionado ser� N�o ..., //Se n�o ser� Sim, ser� passado por par�metro quando clicar no Link (R) ...
            $racionado_future_param = ($racionado_future_param == 1) ? 0 : 1;
            /***************************************************/
            if($campos_estoque[0]['racionado'] == 1) {//Est� Racionado ...
                $class              = 'caixadetexto';
                $disabled           = '';
                $id_pas_racionados.= $campos[$i]['id_produto_acabado'].', ';
        ?>
                <font color='red' title='Racionado' onclick="atualizar_racionado('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$racionado_future_param;?>')" style='cursor:help'>
                    <b>(R)</b>
                </font>
        <?
            }else {//N�o est� racionado ...
                $class      = 'textdisabled';
                $disabled   = 'disabled';
        ?>
                <font color='red' title='N�o Racionado' onclick="atualizar_racionado('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$racionado_future_param;?>')" style='cursor:help'>
                    <b>(�R)</b>
                </font>
        <?
            }
        ?>
        </td>
        <td>
            <a href = '../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' class='html5lightbox'>
                <?=number_format($vetor[0], 2, ',', '.');?>
            </a>
        </td>
        <td>
            <?=number_format($vetor[3], 2, ',', '.');?>
        </td>
        <td>
        <?
            //Aki pego o Total de Vale ...
            $sql = "SELECT SUM(`vale`) AS total_vale 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_vale = bancos::sql($sql);
            echo number_format($campos_vale[0]['total_vale'], 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Aki pego o Total Faturado, somente das NFs que est�o com Status menor que Empacotadas ...
            $sql = "SELECT SUM(nfsi.`qtde`) AS total_faturado 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` AND nfs.`status` <= '2' 
                    WHERE nfsi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_faturado = bancos::sql($sql);
            if($campos_faturado[0]['total_faturado'] > 0) {
        ?>
                <!--Passo por Par�metro o PA que quero consultar, o Status da NF como sendo Apenas "Liberada p/ Faturar" 
                e "Faturada", o par�metro pop_up = 1 � p/ que n�o exiba bot�es de voltar ou fechar na Tela-->
                <a href = '../../../faturamento/nfs_consultar/consultar.php?passo=1&txt_referencia=<?=$campos[$i]['referencia'];?>&cmb_status_nf=1,2&pop_up=1' class='html5lightbox'>
        <?
            }
            echo number_format($campos_faturado[0]['total_faturado'], 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Verifico se o Item possui Estoque Excedente, mas somente do que est� "Em aberto" ...
            $sql = "SELECT SUM(`qtde`) AS estoque_excedente 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `status` = '0' ";
            $campos_estoque_excedente   = bancos::sql($sql);
            $estoque_excedente          = (count($campos_estoque_excedente[0]['estoque_excedente']) == 1) ? $campos_estoque_excedente[0]['estoque_excedente'] : 0;
            if($estoque_excedente > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
                <a href = '../excedente/alterar.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&pop_up=1' class='html5lightbox'>
        <?
            }
            echo '<b>'.number_format($estoque_excedente, 2, ',', '.');
        ?>
            </a>
            <input type='hidden' name='txt_estoque_excedente[]' id='txt_estoque_excedente<?=$i;?>' value='<?=$estoque_excedente;?>' disabled>
        </td>
        <td>
            <?=number_format($vetor[4], 2, ',', '.');?>
            <input type='hidden' name='txt_separado[]' id='txt_separado<?=$i;?>' value='<?=number_format($vetor[4], 2, ',', '.');?>' disabled>
        </td>
        <td>
            <?
                //S� existe casas decimais quando a Unidade do PA = Kilo ...
                $onkeyup = ($campos[$i]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '0', event)" : "verifica(this, 'aceita', 'numeros', '', event)";
            ?>
            <input type='text' name='txt_estoque_prateleira[]' id='txt_estoque_prateleira<?=$i;?>' title='Digite o Estoque da Prateleira' onkeyup="<?=$onkeyup;?>;calcular_novo_er('<?=$i;?>', '<?=$estoque_excedente;?>')" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_entrada_antecipada[]' id='txt_entrada_antecipada<?=$i;?>' title='Digite a Entrada Antecipada' onkeyup="<?=$onkeyup;?>;calcular_novo_er('<?=$i;?>', '<?=$estoque_excedente;?>')" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_novo_er[]' id='txt_novo_er<?=$i;?>' title='Novo ER' size='12' class='textdisabled' disabled>
            <input type='hidden' name='txt_separado[]' id='txt_separado<?=$i;?>' value='<?=$vetor[4];?>'>
            <input type='hidden' name='hdd_produto_acabado[]' id='hdd_produto_acabado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <?
                if(empty($pop_up)) {//Significa que essa Tela foi aberta de modo Normal, ent�o exibo os Bot�o Consultar Novamente ...
            ?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
            <?
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?
                if($pop_up == 1) {//Significa que essa tela � um Pop-UP, sendo assim exibir o bot�o de Fechar ...
            ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='return fechar(window)' style='color:red' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<?
    $id_pas_racionados = substr($id_pas_racionados, 0, strlen($id_pas_racionados) - 2);
?>
<input type='hidden' name='id_pas_racionados' value='<?=$id_pas_racionados;?>'>
<!--*************************************************************************-->
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