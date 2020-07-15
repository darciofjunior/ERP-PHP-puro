<?
/*Eu tenho esse desvio aki para não redeclarar as bibliotecas novamente, isso porque tem alguns arquivos 
q essa parte de estoque embutida e sendo assim já tem as bibliotecas declaradas logo no início*/
if($nao_chamar_biblioteca != 1) {
    require('../../../lib/segurancas.php');
    require('../../../lib/biblioteca.php');
    require('../../../lib/custos.php');
    require('../../../lib/vendas.php');
    require('../../../lib/data.php');
    require('../../../lib/intermodular.php');
    require('../../../lib/estoque_acabado.php');
}
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) COMPRA(S) PRODUÇÃO(ÕES).</font>";

//Busca de Dados do PA com o id_produto_acabado passado por parâmetro ...
$sql = "SELECT referencia, discriminacao, operacao_custo 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos         = bancos::sql($sql);
$referencia 	= $campos[0]['referencia'];
$discriminacao 	= $campos[0]['discriminacao'];
$operacao_custo = $campos[0]['operacao_custo'];

if($operacao_custo == 0) {//Se o Custo do PA = 'Indutrial'
    //Aqui eu busco o Custo desse PA ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' ";
    $campos_pac                     = bancos::sql($sql);
    $id_produto_acabado_custo 	= $campos_pac[0]['id_produto_acabado_custo'];
    //Aqui eu verifico se esse PA tem itens de Blank na 3ª Etapa ...
    $sql = "SELECT pp.`id_produto_insumo` 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` AND pi.`id_grupo` = '22' 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
    $campos_etapa3 = bancos::sql($sql);
    if(count($campos_etapa3) == 1) {
        $id_produto_insumo = $campos_etapa3[0]['id_produto_insumo'];
    }else if(count($campos_etapa3) > 1) {
?>
	<Script Language = 'JavaScript'>
            alert('EXISTE(M) MAIS DE HUM BLANK NA 3ª ETAPA DESSE CUSTO !')
            window.close()
	</Script>
<?	
        exit;
    }
}else {//Se for Revenda ...
    //Aqui eu verifico se esse PA tem itens de Blank na 3ª Etapa ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_pi = bancos::sql($sql);
    $id_produto_insumo = $campos_pi[0]['id_produto_insumo'];
}

//Aqui eu busco a qtde do PI que ainda está pendente ...
$sql = "(SELECT SUM(ip.`qtde`) AS qtde_item, ip.`id_item_pedido`, ip.`marca`, f.`razaosocial`, f.`id_pais`, 
        p.`id_pedido`, p.`prazo_entrega`, p.`prazo_navio`, p.`data_retirada_porto`, u.`sigla` 
        FROM `produtos_insumos` pi 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        INNER JOIN `itens_pedidos` ip ON ip.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND (p.`status` = '1' OR p.`status` = '2') 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
        AND p.`ativo` = '1' AND ip.`status` < '2' GROUP BY p.`id_pedido`) 
        UNION ALL 
        (SELECT SUM(ip.`qtde`) AS qtde_item, ip.`id_item_pedido`, ip.`marca`, f.`razaosocial`, f.`id_pais`, 
        /*Esse Pipe é um Macete ...*/ CONCAT('|', p.`id_pedido`), 
        p.`prazo_entrega`, p.`prazo_navio`, p.`data_retirada_porto`, u.`sigla` 
        FROM `produtos_insumos` pi 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        INNER JOIN `itens_pedidos` ip ON ip.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` AND nfeh.`status` = '0' 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND (p.`status` = '1' OR p.`status` = '2') 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
        GROUP BY p.`id_pedido`) ORDER BY `prazo_entrega` ";
$campos = bancos::sql($sql);
$linhas = count($campos);

if($linhas == 0) {//Não existe nenhum PI que está pendente ...
?>
<html>
<head>
<title>.:: Compra Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript'>
//Eu limpo o Prazo de Entrega na Tela de Cima ...
function retirar_prazo_fechar_atualizar() {
    if(typeof(top.document.form.txt_prazo_entrega) == 'object') {
        top.document.form.txt_prazo_entrega.value = ''
        top.document.form.submit()
    }
/*Aqui eu já chamo a função de "fechar_atualizar" da Tela Principal que fez requisição dessa tela
como Iframe, nela eu já tenho todos os desvios prontinhos ...*/
    top.fecha_e_atualiza()
    top.fecha_e_atualiza()
}

//Quando o Prazo de Entrega de Rev. não tiver importações, o Sistema irá gravar esse Prazo em branco ...
function salvar() {
    top.document.form.submit()
}

/*Aqui eu já chamo a função de "fechar_atualizar" da Tela Principal que fez requisição dessa tela
como Iframe, nela eu já tenho todos os desvios prontinhos ...*/
function fechar_atualizar() {
    top.fecha_e_atualiza()
    top.fecha_e_atualiza()
}
</Script>
<body>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_retirar_fechar_atualizar' value='Retirar Prazo, Fechar e Atualizar' title='Retirar Prazo, Fechar e Atualizar' onclick='retirar_prazo_fechar_atualizar()' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
//Se tiver pelo menos 1 item de Compra Produção para exibir ...
}else {
//Ordena o vetor na ordem certa crescente
    //if(count($vetor_data_chegada) > 0) sort($vetor_data_chegada);
?>
<html>
<head>
<title>.:: Compra Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Atualizo o Prazo de Entrega na Tela de Cima ...
function salvar_fechar_atualizar() {
    if(top.document.form.chkt_enviar_email.checked == true) {//Se tiver checado ...
//Forço o Preenchimento da Justificativa
        if(top.document.form.txt_justificativa.value == '') {
            alert('DIGITE A JUSTIFICATIVA !')
            top.document.form.txt_justificativa.focus()
            top.document.form.txt_justificativa.select()
            return false
        }
    }
    top.document.form.txt_prazo_entrega.value = document.form.new_prazo_entrega.value
    top.document.form.submit()
}

//Quando o Prazo de Entrega de Rev. não tiver importações, o Sistema irá gravar esse Prazo em branco ...
function salvar() {
    if(top.document.form.chkt_enviar_email.checked == true) {//Se tiver checado ...
//Forço o Preenchimento da Justificativa
        if(top.document.form.txt_justificativa.value == '') {
            alert('DIGITE A JUSTIFICATIVA !')
            top.document.form.txt_justificativa.focus()
            top.document.form.txt_justificativa.select()
            return false
        }
//Verifica se a Justificatica tem algum caracter Inválido ...
        elemento = eval('top.document.form.txt_justificativa.value')
        for(var i = 0; i < elemento.length; i++) {
            caracter_atual = elemento.charAt(i)
            if(caracter_atual == "'") {
                alert('JUSTIFICATIVA INVÁLIDA !')
                top.document.form.txt_justificativa.focus()
                top.document.form.txt_justificativa.select()
                return false
            }
        }
    }
    top.document.form.submit()
}

/*Aqui eu já chamo a função de "fechar_atualizar" da Tela Principal que fez requisição dessa tela
como Iframe, nela eu já tenho todos os desvios prontinhos ...*/
function fechar_atualizar() {
    top.fecha_e_atualiza()
    top.fecha_e_atualiza()
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Compra Produção
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$referencia;?>
        </td>
        <td colspan='8'>
            <font color='yellow'>
                <b>Discriminação: </b>
            </font>
            <?=$discriminacao;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font title='Quantidade Pendente' style='cursor:help'>
                Qtde Pend
            </font>
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            <font title='Nome da Importação' style='cursor:help'>
                Importação
            </font>
        </td>
        <td>
            <font title='Prazo de Entrega' style='cursor:help'>
                Prazo <br/>Entrega
            </font>
        </td>
        <td>
            <font title='Data de Chegada' style='cursor:help'>
                Data <br/>Chegada
            </font>
        </td>
        <td>
            <font title='Previsão Data de Retirada no Porto' style='cursor:help'>
                Prev. Data <br/>Retir. Porto
            </font>
        </td>
        <td>
            <font title='Previsão de Estoque' style='cursor:help'>
                Prev <br/>Estoque
            </font>
        </td>
        <td>
            <font title='Marca / Observação' style='cursor:help'>
                Marca / <br/>OBS
            </font>
        </td>                
        <td>
            <font title='N.º do Pedido' style='cursor:help' size='-1'>
                N.º Ped
            </font>
        </td>
    </tr>
<?
        /*Destruo essa variável p/ não herdar valores do Frame "alterar_prazo_entrega_industrial", 
        se é que esse Frame chamou esse arquivo ...*/
        if(isset($total_nao_entregue)) {
            unset($total_nao_entregue);//Destruindo variável ...
            $total_nao_entregue = 0;
        }

	for($i = 0; $i < $linhas; $i++) {
/*Obs: O Union retorna o "id_pedido", daí para distinguir um Pedido puro de um Pedido que já está em NF, 
uma tabela com outra, eu joguei um "|" na Frente desse campo ...*/
            if(substr($campos[$i]['id_pedido'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                $id_pedido          = substr($campos[$i]['id_pedido'], 1, strlen($campos[$i]['id_pedido']));
                $tabela_pedido_nfe  = 'S';
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
                $id_pedido          = $campos[$i]['id_pedido'];
                $tabela_pedido_nfe  = 'N';
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($tabela_pedido_nfe == 'N') {//Significa que a consulta foi feita somente em cima da tabela de pedido ...
                //Então busco o total que foi recebido do Item na NF e que foi liberado em Estoque ...
                $sql = "SELECT SUM(nfeh.`qtde_entregue`) AS total_entregue 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` AND nfeh.`status` = '1' 
                        WHERE ip.`id_item_pedido`  = '".$campos[$i]['id_item_pedido']."' GROUP BY ip.`id_pedido` ";
                $campos_nfe     = bancos::sql($sql);
                $nao_entregue   = $campos[$i]['qtde_item'] - $campos_nfe[0]['total_entregue'];
            }else {//Consulta feita em cima da tabela de Pedido + tabela de Nota Fiscal ...
                $nao_entregue   = $campos[$i]['qtde_item'];
            }
            echo number_format($nao_entregue, 2, ',', '.');
            
            //Consulta feita em cima da tabela de Pedido + tabela de Nota Fiscal ...
            if($tabela_pedido_nfe == 'S') echo '<font color="red" title="Item não Liberado na Nota Fiscal de Compras" style="cursor:help"><b> (Ñ LIB)</b></font>';
            
            $total_nao_entregue+= $nao_entregue;
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            //Verifico se o Pedido está atrelado a alguma Importação ...
            $sql = "SELECT i.`nome` 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.`id_importacao` = i.`id_importacao` 
                    WHERE p.`id_pedido` = '$id_pedido' ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) == 1) {//Se existir Importação ...
                $existe_importacao = 1;//Vou utilizar p/ controle ...
                echo $campos_importacao[0]['nome'];
            }else {//Se não existir Importação ...
                $existe_importacao = 0;//Vou utilizar p/ controle ...
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
        <td>
        <?
            $data_entrega = data::datetodata($campos[$i]['prazo_entrega'], '/');
//Verifica se o fornecedor é internacional
            if($campos[$i]['id_pais'] == 31) {
                echo $data_entrega;
            }else {
                echo data::adicionar_data_hora($data_entrega, $campos[$i]['prazo_navio']);
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_retirada_porto'] != '0000-00-00') echo data::datetodata($campos[$i]['data_retirada_porto'], '/');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['id_pais'] == 31) {//Brasil
                $previsao_estoque = data::adicionar_data_hora($data_entrega, 12);
//Verifico se o PI é um PIPA ...
                $sql = "SELECT ged.`id_grupo_pa` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        WHERE pa.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_gpa = bancos::sql($sql);
                if(count($campos_gpa) == 1) {//Se encontrou algum Registro ...
                    if($campos_gpa[0]['id_grupo_pa'] == 1) {//Bedames M2
                        $previsao_estoque = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), 5);
                    }else if($campos_gpa[0]['id_grupo_pa'] == 8) {//Chave de Pino
                        $previsao_estoque = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), 15);
                    }else if($campos_gpa[0]['id_grupo_pa'] == 37) {//Chave de Garra
                        $previsao_estoque = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), 10);
                    }else if($campos_gpa[0]['id_grupo_pa'] == 43) {//Pinos DIN 7978
                        $previsao_estoque = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), 5);
                    }else if($campos_gpa[0]['id_grupo_pa'] == 44) {//Pinos DIN 7979
                        $previsao_estoque = data::adicionar_data_hora(data::datetodata($data_entrega, '/'), 10);
                    }else {
                        $previsao_estoque = data::datetodata($campos[$i]['prazo_entrega'], '/');
                    }
                }
            }else {//Fora do Brasil ...
//Aqui virou a nova lógica ...
//Se esse campo estiver preenchido ...
                if($campos[$i]['data_retirada_porto'] != '0000-00-00') {
                    $previsao_estoque = data::adicionar_data_hora(data::datetodata($campos[$i]['data_retirada_porto'], '/'), 2);
                }else {
                    $previsao_estoque = '';
                }
            }
            echo $previsao_estoque;
        ?>
        </td>
        <td>
            <?=$campos[$i]['marca']?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../compras/pedidos/itens/itens.php?id_pedido=<?=$id_pedido;?>&pop_up=1', 'DETALHES', 'F')" alt='Detalhes do Pedido' title='Detalhes do Pedido' class='link'>
                <?=$id_pedido;?>
            </a>
        </td>
    </tr>
<?
/*Esse controle é uma pequena automaçãozinha que o Roberto pediu p/ fazer p/ facilitar quando 
fosse importação p/ ajudar o pessoal do Depto. Técnico no preenchimento do Prazo de Entrega ...*/
            if($existe_importacao == 1) {
//Só irei pegar as 2 primeiras linhas, quando disparar o loop ...
                if($i < 2) {
                    $previsao_estoque = substr($previsao_estoque, 0, 5).'/'.substr($previsao_estoque, 8, 2);
                    $new_prazo_entrega.= $nao_entregue.' p/ '.$previsao_estoque.'<br/>';
                }
            }
	}
//Caso exista essa variável, eu faço um Tratamento nesta para que não dê nenhum problema ...
        if(!empty($new_prazo_entrega)) $new_prazo_entrega = substr($new_prazo_entrega, 0, strlen($new_prazo_entrega) - 4);
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <?=number_format($total_nao_entregue, 2, ',', '.');?>
        </td>
        <td colspan='8'>
<?
//Significa que esse arquivo está sendo acessado como um Iframe dentro de uma outra tela Principal
        if($atualizar_iframe == 1) {
/*Significa que existe Importação, então eu exibo esse botão p/ atualizar os Prazos de Entrega 
dessa tela na tela de cima desse iframe*/
            if(!empty($new_prazo_entrega)) {
?>
                <input type='button' name='cmd_salvar_fechar_atualizar' value='Salvar, Fechar e Atualizar' title='Salvar, Fechar e Atualizar' onclick='salvar_fechar_atualizar()' style='color:green' class='botao'>
<?
//Significa que não existe Importação
            }else {
?>
                <input type="button" name="cmd_salvar" value="Salvar" title="Salvar" onclick="salvar()" style="color:green" class="botao">
                <input type="button" name="cmd_fechar_atualizar" value="Fechar e Atualizar" title="Fechar e Atualizar" onclick="fechar_atualizar()" style="color:black" class="botao">
<?
            }
//Significa que esse arquivo está sendo acessado como uma Tela Principal mesmo que é exatamente o que ele é 
        }else {
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.close()' style='color:red' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<input type='hidden' name='new_prazo_entrega' value='<?=$new_prazo_entrega;?>'>
</form>
</body>
</html>
<?
/******************************************************************************/
/******************Controle p/ exibir as OEs no fim da página******************/
/******************************************************************************/
    if(!empty($id_produto_acabado)) {//Esse parâmetro só existe em alguns casos ...
?>
<hr/>
<!--Esse parâmetro cmd_consultar=Consultar é um macete significando que o usuário já clicou no Botão 
cmd_consultar da Tela de Filtro e ir diretamente p/ a Tela Pós-Filtro, o parâmetro pop_up=1 já é outro macete 
porque apesar dessa tela não ter sido aberta como Pop-UP eu não quero que a mesma me apresente o Menu ...-->
<iframe name='iframe_oes' src = '../../producao/oes/alterar.php?id_produto_acabado=<?=$id_produto_acabado;?>&cmd_consultar=Consultar&pop_up=1' width='95%' height='250'>
</body>
</html>
<?
    }
/******************************************************************************/
}
?>