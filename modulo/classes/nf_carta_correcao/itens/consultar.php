<?
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='confirmacao'>CARTA DE CORREÇÃO ATUALIZADA COM SUCESSO.</font>";

if($passo == 1) {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../classes/nf_carta_correcao/itens/tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements, selecionados = 0
    for (i = 0; i < elementos.length; i ++) {
        if (elementos[i].checked == true && elementos[i].type == 'checkbox') {
            selecionados = 1
            break;
        }
    }
//Verifica se existe pelo menos 1 Checkbox selecionado ...
    if(selecionados == 0) {
        alert('SELECIONE UMA ESPECIFICAÇÃO !')
        return false
    }
//Se tiver pelo menos 1 Item selecionado, eu verifico se foi preenchido o Campo Retificação ...
    for (i = 0; i < elementos.length; i ++) {
        if (elementos[i].checked == true && elementos[i].type == 'checkbox') {
            if(elementos[i + 1].value == '') {//Se a Retificação estiver em branco ...
                alert('DIGITE A RETIFICAÇÃO !')
                elementos[i + 1].focus()
                return false
            }
        }
    }
}

function imprimir() {
    var numero_vias = prompt('DIGITE O NÚMERO DE VIAS QUE DESEJA IMPRIMIR !')
    if(numero_vias == null) {
        return false
    }else if(numero_vias == '' || numero_vias > 3) {
        alert('NÚMERO DE VIAS INVÁLIDO !')
        return false
    }
    nova_janela('../../../classes/nf_carta_correcao/itens/relatorio/imprimir.php?id_carta_correcao=<?=$id_carta_correcao;?>&numero_vias='+numero_vias, 'POP', 'F')
}

function comunicado() {
    var elementos = document.form.elements, especific_selecionadas = ''
//Se tiver pelo menos 1 Item selecionado, eu verifico se foi preenchido o Campo Retificação ...
    for(i = 0; i < elementos.length; i ++) {
        if (elementos[i].checked == true && elementos[i].type == 'checkbox') {
            if(elementos[i].value == 18 || elementos[i].value == 23 || elementos[i].value == 43) especific_selecionadas+= elementos[i].value + ', '
        }
    }
    selecionados = especific_selecionadas.substr(0, especific_selecionadas.length - 2)
    html5Lightbox.showLightbox(7, '../../../classes/nf_carta_correcao/itens/relatorio/comunicado.php?id_carta_correcao=<?=$id_carta_correcao;?>&especific_selecionadas='+especific_selecionadas)
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=2';?>' method='post' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Carta de Correção N.º
            <font color='yellow'> 
                <?=$id_carta_correcao;?> 
            </font>	
            &nbsp;- NF N.º&nbsp;
            <?
                require('../../../classes/nf_carta_correcao/class_carta_correcao.php');
                $dados = carta_correcao::dados_nfs($id_carta_correcao);

                if($dados['tipo_nota'] == 'NFE') {
                    $caminho = '/erp/albafer/modulo/compras/pedidos/nota_entrada/consultar_itens_nota.php?id_nfe='.$dados['id_nota'].'&pop_up=1';
                }else if($dados['tipo_nota'] == 'NFS') {
                    $caminho = '/erp/albafer/modulo/faturamento/nota_saida/itens/itens.php?id_nf='.$dados['id_nota'].'&pop_up=1';
                }else if($dados['tipo_nota'] == 'NFSO') {
                    $caminho = '/erp/albafer/modulo/faturamento/outras_nfs/itens/itens.php?id_nf_outra='.$dados['id_nota'].'&pop_up=1';
                }
            ?>
            <a href='<?=$caminho;?>' class='html5lightbox'>
                <font color='#5DECFF' size='-1'>
                    <?=$dados['numero_nf'];?>
                </font>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes da Nota Fiscal' alt='Detalhes da Nota Fiscal' border='0'>
            </a>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Itens</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Código</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Especificação</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Retificação</b>
        </td>
    </tr>
<?
//Aqui eu listo todas as especificações cadastradas no Sistema ...
    $sql = "SELECT `id_especificacao`, `especificacao` 
            FROM `especificacoes` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu verifico se a Especificação Corrente se encontra nessa Carta de Correção ...
        $sql = "SELECT `retificacao` 
                FROM `cartas_correcoes_itens` 
                WHERE `id_carta_correcao` = '$id_carta_correcao' 
                AND `id_especificacao` = '".$campos[$i]['id_especificacao']."' LIMIT 1 ";
        $campos_itens = bancos::sql($sql);
//Se já existe então seleciona o Checkbox e habilita a Caixa Retificação ...
        if(count($campos_itens) == 1) {
            if($campos[$i]['id_especificacao'] == 18 || $campos[$i]['id_especificacao'] == 23 || $campos[$i]['id_especificacao'] == 43) {
                $class_botao_comunicado = 'botao';
                $disabled_comunicado    = '';
            }else {
                $class_botao_comunicado = 'textdisabled';
                $disabled_comunicado    = 'disabled';
            }
            $checked    = 'checked';
            $disabled   = '';
            $class      = 'caixadetexto';
        }else {
            $checked    = '';
            $disabled   = 'disabled';
            $class      = 'textdisabled';
        }
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_especificacao[]' value='<?=$campos[$i]['id_especificacao'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
            <?=$campos[$i]['id_especificacao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['especificacao'];?>
        </td>
        <td>
            <input type='text' name='txt_retificacao[]' value='<?=$campos_itens[0]['retificacao'];?>' title='Digite a Retificação' maxlength='255' size='70' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
<?
    }
//Tratamento com essa variável parâmetro, apenas quando se acaba de gerar uma carta de correção ...
    if(strpos($parametro, 'opt_opcao') > 0) $parametro = '?pagina=1&inicio=0&cmd_consultar=Consultar&funcao_gomes=funcao_gomes&funcao_gomes=funcao_gomes'; 
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='botao'>
            <input type='button' name='cmd_comunicado' value='Comunicado de não apropriação de Imposto' title='Comunicado de não apropriação de Imposto' onclick='comunicado()' style='color:red' class='<?=$class_botao_comunicado;?>' <?=$disabled_comunicado;?>>
        </td>
    </tr>
</table>
<input type='hidden' name='id_carta_correcao' value='<?=$id_carta_correcao?>'>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    foreach($_POST['chkt_especificacao'] as $i => $id_especificacao) {
//Aqui eu guardo os itens que ainda se mantiveram selecionados em checkbox ...
        $vetor_especific_continuar[] = $id_especificacao;
//Aqui eu verifico se esse Item existe ...
        $sql = "SELECT `id_carta_correcao_item` 
                FROM `cartas_correcoes_itens` 
                WHERE `id_carta_correcao` = '$id_carta_correcao' 
                AND `id_especificacao` = '$id_especificacao' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Esse Item ainda não existe 
            $sql = "INSERT INTO `cartas_correcoes_itens` (`id_carta_correcao_item`, `id_carta_correcao`, `id_especificacao`, `retificacao`) VALUES (NULL, '$id_carta_correcao', '$id_especificacao', '".$_POST['txt_retificacao'][$i]."') ";
        }else {
            $sql = "UPDATE `cartas_correcoes_itens` SET `retificacao` = '".$_POST['txt_retificacao'][$i]."' WHERE `id_carta_correcao_item` = '".$campos[0]['id_carta_correcao_item']."' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
/**********************************************************************************************************/
/***********************************Deleta os Itens que foram desmarcados**********************************/
/**********************************************************************************************************/
//Aqui eu busco todos os Itens que estão gravados p/ aquela Carta de Correção antes de existir alguma alteração ...
    $sql = "SELECT `id_especificacao` 
            FROM `cartas_correcoes_itens` 
            WHERE `id_carta_correcao` = '$id_carta_correcao' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {
/*Agora eu verifico qual item da Carta de Correção que terá de ser deletada, caso o usuário desmarcou algum 
o checkbox ...*/
        if(!in_array($campos_itens[$i]['id_especificacao'], $vetor_especific_continuar)) {
            $sql = "DELETE FROM `cartas_correcoes_itens` WHERE `id_carta_correcao` = '$id_carta_correcao' AND `id_especificacao` = '".$campos_itens[$i]['id_especificacao']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/**********************************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar.php?passo=1&id_carta_correcao=<?=$id_carta_correcao;?>&valor=1'
    </Script>
<?
}else {
    $nivel_path = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Cartas de Correção que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Cartas de Correção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Carta(s) de Correção(ões)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Carta
        </td>
        <td>
            Data
        </td>
        <td>
            N.&ordm; da NF
        </td>
        <td>
            Cliente / Fornecedor
        </td>
    </tr>
<?
        require('../../../classes/nf_carta_correcao/class_carta_correcao.php');
        for($i = 0;  $i < $linhas; $i++) {
            $url    = 'consultar.php?passo=1&id_carta_correcao='.$campos[$i]['id_carta_correcao'];
            $dados  = carta_correcao::dados_nfs($campos[$i]['id_carta_correcao']);
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['id_carta_correcao'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_sys'];?>
        </td>
        <td>
            <?=$dados['numero_nf'];?>
        </td>
        <td align='left'>
            <?=$dados['negociador'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
}
?>