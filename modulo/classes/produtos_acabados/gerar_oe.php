<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
session_start('funcionarios');

if($passo == 1) {
/*********************Controle com Toda a Parte de Substituir*********************/
//Aki registra a Data e Hora em q foi feita a alteração
    $data_sys = date('Y-m-d H:i:s');
//Controle com o Estoque dos P.A(s)
//1) P.A. em que eu estou retirando algo do Estoque
    $resultado1 = estoque_acabado::verificar_manipulacao_estoque($_POST['cmb_pa_enviado'], -$_POST['txt_qtde_enviada_a_retornar']);
/*********************************************************************************/
/*Comentado a pedido do Rivaldo e do Roberto na Data -> 30/03/2010 ...
//2) P.A. em que eu estou acrescentando algo do Estoque
    $resultado2 = estoque_acabado::verificar_manipulacao_estoque($id_produto_acabado, $_POST[txt_qtde_enviada_a_retornar]);*/
/*********************************************************************************/
    //if($resultado1['retorno'] == 'executar' && $resultado2['retorno'] == 'executar') {
    if($resultado1['retorno'] == 'executar') {
        //Gera OE ...
        $sql = "INSERT INTO `oes` (`id_oe`, `id_produto_acabado_s`, `id_produto_acabado_e`, `id_funcionario_resp_s`, `qtde_s`, `qtde_a_retornar`, `data_s`, `observacao_s`) VALUES (NULL, '$_POST[cmb_pa_enviado]', '$id_produto_acabado', '$_SESSION[id_funcionario]', '$_POST[txt_qtde_enviada_a_retornar]', '$_POST[txt_qtde_enviada_a_retornar]', '$data_sys', '".ucfirst(strtolower($_POST['txt_observacao']))."') ";
        bancos::sql($sql);
        $id_oe = bancos::id_registro();
        
        //1) Atualizando o P.A. Enviado
        $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_oe`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$_POST[cmb_pa_enviado]', '$_SESSION[id_funcionario]', '$id_oe', '-$_POST[txt_qtde_enviada_a_retornar]', '<b>PA ENVIADO. </b>".ucfirst(strtolower($_POST['txt_observacao']))."', 'M', '2', '$data_sys') ";
        bancos::sql($sql);
        
        //Se foi selecionada a opção "ESTORNAR ENTRADA ANTECIPADA NF DE COMPRAS", então preciso abater desse Estoque a "Qtde Enviada / À Retornar" ...
        if($_POST['chkt_estornar_entrada_antecipada_nf_compras'] == 'S') {
            $sql = "UPDATE `estoques_acabados` SET `entrada_antecipada` = `entrada_antecipada` - $_POST[txt_qtde_enviada_a_retornar] WHERE `id_produto_acabado` = '$_POST[cmb_pa_enviado]' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        estoque_acabado::atualizar($_POST['cmb_pa_enviado']);
        estoque_acabado::controle_estoque_pa($_POST['cmb_pa_enviado']);
        estoque_acabado::atualizar_producao($id_produto_acabado);//Atualizo apenas a Produção do Retornado ...
?>
        <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
        <Script Language = 'JavaScript'>
            alert('O.E. N.º <?=$id_oe;?> INCLUIDA COM SUCESSO !')
            var resposta = confirm('DESEJA IMPRIMIR ESTA OE ?')
            if(resposta == true) nova_janela('../../producao/oes/relatorio/relatorio.php?chkt_oe[]=<?=$id_oe;?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        </Script>
<?
    }else {
?>
        <Script Language = 'JavaScript'>
            alert('ESTE P.A. NÃO PODE SER MANIPULADO !!!\nO ESTOQUE DISPONÍVEL PODE ESTAR RACIONADO, MANIPULADO OU A QTDE DISPONÍVEL ESTÁ INCOMPATÍVEL !')
        </Script>
<?
    }
?>
    <Script Language = 'JavaScript'>
        if(opener != null) {//Significa que essa Tela foi aberta em um Pop-UP ...
            window.opener.document.form.submit()
            window.close()
        }else {//Significa que essa Tela foi aberta em um LightBox ...
            parent.location = parent.location.href+'<?=$parametro;?>'
        }
    </Script>
<?
/***************************************************************************/
}else {
    $sql = "SELECT `referencia`, `mmv` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_pa              = bancos::sql($sql);
    //Aqui eu verifico a qtde_disponível q eu tenho em estoque do PA passado por parâmetro ...
    $vetor_estoque_pa       = estoque_acabado::qtde_estoque($id_produto_acabado);
    $estoque_disponivel     = $vetor_estoque_pa[3];
    $estoque_comprometido   = $vetor_estoque_pa[8];
    
    /*****************************Compra Produção******************************/
    //Verifico primeiro a Produção porque este é mais leve do que a Compra ...
    $vetor_estoque_pa       = estoque_acabado::qtde_estoque($id_produto_acabado, 0);
    $producao               = $vetor_estoque_pa[2];
    $compra                 = estoque_acabado::compra_producao($id_produto_acabado);
    /**************************************************************************/
?>
<html>
<title>.:: Gerar O.E ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//P.A. Enviado / Produto de Retorno
    if(!combo('form', 'cmb_pa_enviado', '', 'SELECIONE O P.A. ENVIADO / PRODUTO DE RETORNO !')) {
        return false
    }
/**************************************************************************************************************/
/****************Controle p/ saber se o Usuário não escolheu um PA Enviado que esteja Bloqueado****************/
/**************************************************************************************************************/
    var options_combo   = document.form.cmb_pa_enviado.options
    var indice_combo    = document.form.cmb_pa_enviado.selectedIndex

    if(options_combo[indice_combo].text.indexOf(' (BLOQUEADO)') != -1) {
        alert('ESTE P.A. ENVIADO / PRODUTO DE RETORNO ESTÁ BLOQUEADO !!!\n\nESCOLHA OUTRO P.A. ENVIADO / PRODUTO DE RETORNO !')
        document.form.cmb_pa_enviado.focus()
        return false
    }
    
    //Estoque Disponível do P.A. Enviado -> "Combo" ...
    var estoque_disponivel      = eval(strtofloat(document.form.txt_estoque_disponivel.value))
    var entrada_antecipada      = eval(strtofloat(document.form.txt_entrada_antecipada.value))
    var estoque_comprometido    = eval('<?=$estoque_comprometido;?>')
    var mmv_pa                  = eval('<?=$campos_pa[0]['mmv'];?>')
/**************************************************************************************************************/
//Quantidade à Substituir / Embalar
    if(!texto('form', 'txt_qtde_enviada_a_retornar', '1', '0123456789', 'QUANTIDADE À SUBSTITUIR / EMBALAR', '1')) {
        return false
    }
//Verifica se o usuário digitou uma qtde = 0
    if(document.form.txt_qtde_enviada_a_retornar.value == '' || document.form.txt_qtde_enviada_a_retornar.value == 0) {
        alert('QUANTIDADE À SUBSTITUIR / EMBALAR INVÁLIDA !\nQUANTIDADE À SUBSTITUIR / EMBALAR = 0 !!!')
        document.form.txt_qtde_enviada_a_retornar.focus()
        document.form.txt_qtde_enviada_a_retornar.select()
        return false
    }
//Verifica se o usuário digitou uma qtde à substituir / embalar > do que a qtde disponível do P.A. Enviado ...
    if(document.form.txt_qtde_enviada_a_retornar.value > estoque_disponivel) {
        alert('QUANTIDADE À SUBSTITUIR / EMBALAR INVÁLIDA !\n\nQUANTIDADE À SUBSTITUIR / EMBALAR MAIOR DO QUE A QTDE DISPONÍVEL DO P.A. ENVIADO !!!')
        document.form.txt_qtde_enviada_a_retornar.focus()
        document.form.txt_qtde_enviada_a_retornar.select()
        return false
    }
/*Só se o usuário selecionar o checkbox "ESTORNAR ENTRADA ANTECIPADA NF DE COMPRAS" que irá fazer essa verificação porque ao submeter, aí sim irá 
interferir na Tabela de Estoque do PA ...*/
    if(document.form.chkt_estornar_entrada_antecipada_nf_compras.checked == true) {
        //Verifica se o usuário digitou uma qtde à substituir / embalar > do que a Entrada Antecipada do P.A. Enviado ...
        if(document.form.txt_qtde_enviada_a_retornar.value > entrada_antecipada) {
            alert('QUANTIDADE À SUBSTITUIR / EMBALAR INVÁLIDA !\n\nQUANTIDADE À SUBSTITUIR / EMBALAR MAIOR DO QUE A ENTRADA ANTECIPADA DO P.A. ENVIADO !!!')
            document.form.txt_qtde_enviada_a_retornar.focus()
            document.form.txt_qtde_enviada_a_retornar.select()
            return false
        }
    }
//Esse controle abaixo é p/ mantermos estoque de Itens mais Vendáveis ...
    if(document.form.txt_qtde_enviada_a_retornar.value < parseInt(mmv_pa - estoque_comprometido)) {
        var resposta = confirm('A QUANTIDADE À SUBSTITUIR / EMBALAR ESTA INFERIOR AO "MMV - EC" = '+parseInt(mmv_pa - estoque_comprometido)+' !!!\n\nQUER POR MAIS PEÇAS EM ESTOQUE ?')
        if(resposta == true) return false
    }
//Desabilito o botão de Salvar, p/ evitar de o usuário enviar as informações + de 1 vez p/ o Servidor
    document.form.cmd_salvar_fechar.disabled = true
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
}

function verificar_compra_producao() {
    var referencia  = '<?=$campos_pa[0]['referencia'];?>'
    var compra      = '<?=$compra;?>'
    var producao    = '<?=$producao;?>'
    
    if(referencia == 'ESP') {//Somente em itens Especiais "ESP" ...
        if(compra > 0 || producao > 0) alert('JÁ EXISTE "COMPRA / PRODUÇÃO" DESTE PA A RETORNAR !!!')
    }
}

function desatrelar_pa() {
//PA Enviado ...
    if(!combo('form', 'cmb_pa_enviado', '', 'SELECIONE O P.A. ENVIADO !')) {
        return false
    }
    var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE P.A. DO PA PRINCIPAL ?')
    if(resposta == true) {
        var id_pa_enviado = document.form.cmb_pa_enviado.value
        nova_janela('../../classes/produtos_acabados/desatrelar_pa.php?id_pa_a_ser_desatrelado='+id_pa_enviado+'&id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        var tela1 = eval(document.form.tela1.value)//Referente aos frames da Tela da parte de baixo
        var tela2 = eval(document.form.tela2.value)//Referente aos frames da Tela da parte de baixo
//Atualiza a parte de Itens se existir
        if(typeof(tela1) == 'object') tela1.document.form.submit()
//Atualiza a parte de Rodapé se existir
        if(typeof(tela2) == 'object') tela2.document.form.submit()
    }
}

function consultar_estoques_pa(id_produto_acabado) {
    ajax('estoques_pa.php?id_produto_acabado='+id_produto_acabado, 'div_estoques_pa')
}
</Script>
</head>
<body onload='verificar_compra_producao();document.form.txt_qtde_enviada_a_retornar.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<input type='hidden' name='tela1' value='<?=$tela1;?>'>
<input type='hidden' name='tela2' value='<?=$tela2;?>'>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar O.E
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='240'>
            <b>P.A. Enviado:</b>
        </td>
        <td>
            <select name='cmb_pa_enviado' title='Selecione o P.A. Enviado / Produto de Retorno' onchange='consultar_estoques_pa(this.value)' class='combo'>
            <?
                /*Na 1ª Query eu trago todos os PA(s) que foram atrelados ao $id_produto_acabado que foi 
                passado por parâmetro e no outro SQL trago ele próprio ...*/
                $sql = "SELECT 
                        IF(ps.`id_produto_acabado_1` = '$id_produto_acabado', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_produto_acabado 
                        FROM `pas_substituires` ps 
                        WHERE 
                        (ps.`id_produto_acabado_1` = '$id_produto_acabado') 
                        OR (ps.`id_produto_acabado_2` = '$id_produto_acabado') 
                        UNION 
                        SELECT `id_produto_acabado` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                if($linhas > 0) {//Se encontrar pelo menos 1 PA, então ...
                    for($i = 0; $i < $linhas; $i++) $id_pas_exibir.= $campos[$i]['id_produto_acabado'].', ';
                    $id_pas_exibir = substr($id_pas_exibir, 0, strlen($id_pas_exibir) - 2);
                }
//Trago todos os PA(s) que estão atrelados na tab. relacional + o próprio PA que veio por parâmetro ...
                $sql = "SELECT pa.`id_produto_acabado`, CONCAT(ROUND(ea.`qtde_disponivel`, 0), ' * ', pa.`referencia`, ' * ', pa.`discriminacao`, IF(ea.`status` = '0', '', ' (BLOQUEADO)')) AS dados 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                        WHERE pa.`id_produto_acabado` IN ($id_pas_exibir) ORDER BY pa.`referencia` ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_atrelar_pa' value='Atrelar PA' title='Atrelar PA' onclick="nova_janela('atrelar_pa.php?id_pa_a_ser_atrelado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            &nbsp;
            <input type='button' name='cmd_desatrelar_pa' value='Desatrelar PA' title='Desatrelar PA' onclick='desatrelar_pa()' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <div id='div_estoques_pa'></div>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <?=date('d/m/Y');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Qtde Enviada / À Retornar:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_qtde_enviada_a_retornar' value='<?=$txt_qtde_enviada_a_retornar;?>' title='Digite a Substituir / Embalar' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto a Retornar:</b>
        </td>
        <td>
            <font color='blue'>
                <?=intermodular::pa_discriminacao($id_produto_acabado, 0);?>
            </font>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src = '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Orçamentos - Últimos 6 meses' class='link'>
                <img src = '../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Compra Produção a Retornar:</b>
        </td>
        <td>
            <?=number_format($compra, 2, ',', '.').' / '.number_format($producao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estoque Comprometido do P.A. a Retornar:</b>
        </td>
        <td>
            <?=number_format($estoque_comprometido, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estoque Disponível do P.A. a Retornar:</b>
        </td>
        <td>
            <?=number_format($estoque_disponivel, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>MMV do P.A. a Retornar:</b>
        </td>
        <td>
            <?=number_format($campos_pa[0]['mmv'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='100' rows='5' maxlength='500' class='caixadetexto'><?=$txt_observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'substituir_estoque_pa.php?id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_enviada_a_retornar.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar_fechar' value='Salvar e Fechar' title='Salvar e Fechar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<Script Language = 'JavaScript'>
//Essa função tem efeito de Onload, eu executo está automática quando carrego a tela ...
/* 1) Tenho que colocar ela aqui em baixo porque daí já carregou todo o Body, Form, ... 
resumindo p/ não dar problema com os objetos
2) Esta função tem um papel importante porque ela também faz controle com o P.A. em que eu adiciono 
pelo botão de Consultar P.A. que vem de outro Pop-UP acima desta tela*/
if(document.form.cmb_pa_enviado.value != '') {
    consultar_estoques_pa(document.form.cmb_pa_enviado.value)
}
</Script>
<?}?>