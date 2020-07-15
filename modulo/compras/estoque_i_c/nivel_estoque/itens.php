<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_new.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../../');

$mensagem[1] = 'NENHUM PRODUTO ESTÁ COM NÍVEL DE ESTOQUE BAIXO.';
$mensagem[2] = 'NENHUM PRODUTO ESTÁ COM NÍVEL DE ESTOQUE MÉDIO.';
$mensagem[3] = 'NENHUM PRODUTO ESTÁ COM NÍVEL DE ESTOQUE ALTO.';
$mensagem[4] = 'NENHUM PRODUTO NO ESTOQUE.';

/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia                             = $_POST['txt_referencia'];
        $txt_discriminacao                          = $_POST['txt_discriminacao'];
        $txt_numero_cotacao                         = $_POST['txt_numero_cotacao'];
        $chkt_todos_baixos_medios                   = $_POST['chkt_todos_baixos_medios'];
        $chkt_somente_itens_com_estoque_maior_zero  = $_POST['chkt_somente_itens_com_estoque_maior_zero'];
    }else {
        $txt_referencia                             = $_GET['txt_referencia'];
        $txt_discriminacao                          = $_GET['txt_discriminacao'];
        $txt_numero_cotacao                         = $_GET['txt_numero_cotacao'];
        $chkt_todos_baixos_medios                   = $_GET['chkt_todos_baixos_medios'];
        $chkt_somente_itens_com_estoque_maior_zero  = $_GET['chkt_somente_itens_com_estoque_maior_zero'];
    }
?>
<html>
<head>
<title>.:: Nível de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' href = '../../../../css/layout.css' type = 'text/css'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar(valor) {
    if(valor == 0) {
        parent.rodape.document.form.hdd_insert.value = ''
    }else {
        parent.rodape.document.form.hdd_insert.value = 1
    }
}

function checar() {
    document.form_cabecalho.chkt_mostrar_consumo_mensal_zero.value = (document.form_cabecalho.chkt_mostrar_consumo_mensal_zero.checked == true) ? 1 : 0
}
</Script>
</head>
<body>
<form name='form_cabecalho' action='' method='post'>
<!--*********************Controle de Tela********************-->
<!--Guardo essas variáveis aqui p/ não perder a paginação ...-->
<input type='hidden' name='txt_referencia' value='<?=$txt_referencia;?>'>
<input type='hidden' name='txt_discriminacao' value='<?=$txt_discriminacao;?>'>
<input type='hidden' name='txt_numero_cotacao' value='<?=$txt_numero_cotacao;?>'>
<input type='hidden' name='chkt_todos_baixos_medios' value='<?=$chkt_todos_baixos_medios;?>'>
<input type='hidden' name='chkt_somente_itens_com_estoque_maior_zero' value='<?=$chkt_somente_itens_com_estoque_maior_zero;?>'>
<!--*********************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Nível de Estoque 
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
<?
            if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($txt_numero_cotacao)) {//Esses parâmetros vem de Outra Tela ...
?>
            <font size='-1'>
                <b>Nível:</b>
            </font>
            <select name='cmb_nivel' title='Selecione o Nível' onchange='controlar(0)' class='combo'>
                <option value='1'>Baixo</option>
                <option value='2'>Médio</option>
                <option value='3'>Alto</option>
                <option value='' selected>Todos</option>
            </select>
<?
            }
?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font size='-1'>
                <b>Mês:</b>
            </font>
            <select name='cmb_mes' title='Selecione o Mês' onchange='controlar(0)' class='combo'>
<?
            $dias = 30;

            for($i = 1; $i <= 12; $i++) {
                $separador 	= ($i < 10) ? '  | ' : ' |';
                $selected 	= ($i == $cmb_mes) ? 'selected' : ''; 
?>
                <option value="<?=$i;?>" <?=$selected;?>><?=$i;?> Mês<?=$separador;?> <?=$dias;?> Dias</option>
<?
                $dias+= 30;
            }
?>
            </select>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?
            if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($txt_numero_cotacao)) {//Esses parâmetros vem de Outra Tela ...
                /*Se foi preenchido o número da Cotação na tela anterior de filtro, como sugestão na primeira vez em que se carregar esta, o 
                sistema nos marca o "checkbox" -> $chkt_mostrar_consumo_mensal_zero ou marca este, se o mesmo já estava marcado mesmo ...*/
                //$checked = ((!empty($txt_numero_cotacao) && empty($cmd_pesquisar)) || $chkt_mostrar_consumo_mensal_zero == 1) ? 'checked' : '';
                $checked = ($chkt_mostrar_consumo_mensal_zero == 1) ? 'checked' : '';
?>
            <input type='checkbox' name='chkt_mostrar_consumo_mensal_zero' id='chkt_mostrar_consumo_mensal_zero' value='1' onclick='checar();controlar(0)' class='checkbox' <?=$checked;?>>
            <font size='-1'>
                <label for='chkt_mostrar_consumo_mensal_zero'>
                    <b>Mostrar Consumo Mensal Zero</b>
                </label>
            </font>
<?
            }
?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' name='cmd_pesquisar' value='Pesquisar' title='Pesquisar' onclick='controlar(1)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
/***************************************Filtro normal que já é executada da tela anterior***************************************/
//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$nivel_baixo    = genericas::variavel(2);
$nivel_alto     = genericas::variavel(3);

if(!empty($chkt_todos_baixos_medios)) {//Todos os Baixos e Todos os Médios ...
    $cmb_nivel      = 4;
    $qtde_analisar  = 350;
    //Não mostrará Produtos com referências igual a FER (Ferramentas e Acessórios), MED (Instrumento de Medição) e ROL (Rolamento) ...
    $condicao_grupo = " AND g.`id_grupo` NOT IN (16, 20, 28) ";
}else {
    $qtde_analisar  = 100;
}

//Esta parte serve para se selecionar o consumo mensal com zero ele pega os com consumo = 0 e consumo <> 0 tb
if(empty($chkt_mostrar_consumo_mensal_zero))            $condicao_estoque_mensal = " AND pi.`estoque_mensal` <> '0.00' ";

if(!empty($chkt_somente_itens_com_estoque_maior_zero))  $inner_join_estoques_insumos = " INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` AND ei.`qtde` > '0' ";

//Não exibe PI's que são do Tipo do PRAC
$nao_prac = " AND g.`id_grupo` <> '9' ";

if(!empty($txt_numero_cotacao)) {//N.º da Cotação ...
    $sql = "SELECT u.`sigla`, pi.`id_produto_insumo`, pi.`observacao`, pi.`qtde_estoque_pi`, g.`referencia`, pi.`unidade_conversao`, 
            pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, (((pi.`prazo_entrega` / 30) + $nivel_baixo) * pi.`estoque_mensal`) AS estoque_critico_baixo, 
            (((pi.`prazo_entrega` / 30) + $nivel_alto) * pi.`estoque_mensal`) AS estoque_critico_alto 
            FROM `cotacoes` c 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = c.`id_funcionario` 
            INNER JOIN `cotacoes_itens` ci ON ci.`id_cotacao` = c.`id_cotacao` AND c.`id_cotacao` = '$txt_numero_cotacao' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ci.`id_produto_insumo` AND pi.`discriminacao` LIKE '%$txt_discriminacao%' AND pi.`ativo` = '1' $condicao_estoque_mensal 
            $inner_join_estoques_insumos 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '$txt_referencia%' $condicao_grupo $nao_prac 
            ORDER BY pi.`discriminacao`, g.`referencia` ";
}else {//Demais Opções
    $sql = "SELECT u.`sigla`, pi.`id_produto_insumo`, pi.`observacao`, pi.`qtde_estoque_pi`, g.`referencia`, pi.`unidade_conversao`, 
            pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, (((pi.`prazo_entrega` / 30) + $nivel_baixo) * pi.`estoque_mensal`) AS estoque_critico_baixo, 
            (((pi.`prazo_entrega` / 30) + $nivel_alto) * pi.`estoque_mensal`) AS estoque_critico_alto 
            FROM `produtos_insumos` pi 
            $inner_join_estoques_insumos 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '$txt_referencia%' $condicao_grupo $nao_prac 
            WHERE pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pi.`ativo` = '1' 
            $condicao_estoque_mensal 
            ORDER BY pi.`discriminacao`, g.`referencia` ";
}

if(empty($cmb_nivel) || $cmb_nivel == 4) {//Mostro a paginação normal ...
    $campos = bancos::sql($sql, $inicio, $qtde_analisar, 'sim', $pagina);
}else {//não posso paginar pois pode ser q nao venha nenhum resultado mas tem resultado na pagina 2
    $campos = bancos::sql($sql);
}
$linhas = count($campos);
if($linhas == 0) {
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NENHUM PRODUTO FOI ENCONTRADO NESTA CONDIÇÃO
        </td>
    </tr>
</table>
<Script Language = 'JavaScript'>
    //Se a opção "Mostrar Consumo Mensal Zero" estiver desmarcada, faço a sugestão ...
    if(document.form_cabecalho.chkt_mostrar_consumo_mensal_zero.checked == false) {
        var resposta = confirm('DESEJA MOSTRAR CONSUMO MENSAL ZERO ?')
        if(resposta == true) {
            document.form_cabecalho.chkt_mostrar_consumo_mensal_zero.checked = true
            document.form_cabecalho.cmd_pesquisar.click()
        }else {
            parent.location = 'index.php'
        }
    }else {//Opção "Mostrar Consumo Mensal Zero" marcada, mas não encontrou nada, volto p/ a Tela de Filtro ...
        parent.location = 'index.php?valor=1'
    }
</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Nível de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' href = '../../../../css/layout.css' type = 'text/css'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
//Função que controlar o hidden do frame rodapé para poder dar novo insert
function controlar_insert() {
    parent.rodape.document.form.hdd_insert.value = 0
}

function mostrar_erro(linha) {
//Aqui conta o checkbox principal Tudo
    var posicao = 1
    if(linha == 0) 	posicao = eval(posicao) + 1
    if(linha == 1) 	posicao = eval(posicao) + 3
    if(linha > 1) 	posicao = (linha * 2) + 2
    var elementos = document.form.elements
    var valor = elementos[posicao].value
    if(valor != '-') {//Se tracinho, diferente
        valor = eval(strtofloat(valor))
        if(isNaN(valor)) {//Caso o campo esteja vazio
            valor = 0
        }
        if(valor > 0) {//Valor Positivo
            elementos[posicao].style.background = 'red'
            elementos[posicao].style.color      = 'white'
        }else {//Valor Negativo
            elementos[posicao].style.background = 'white'
            elementos[posicao].style.color      = 'brown'
        }
    }else {//Igual
        elementos[posicao].value = ''
        elementos[posicao].style.background = 'white'
    }
}

function calcular_qtde_kilos(linha, densidade_aco) {
    var elementos = document.form.elements
    var objetos_inicio = 1
    var objetos_linha = 4
    var objetos_fim = 8
/*Significa que o usuário está clicando da segunda linha em diante, aqui se realiza
esse macete porque se tem 2 objetos por linha contando o checkbox também*/
    if(linha != 0) {
            var cont = linha * objetos_linha
    }else {
            var cont = linha
    }
//Aqui se adiciona mais um para o cont para pular o primeiro checkbox principal
    cont ++
    if (elementos[cont].checked == true) {
//Habilita os objetos
        if(elementos[cont + 2].value != '') {//Se tiver digitada a qtde_em_metros daí calcula
            qtde_metros = eval(strtofloat(elementos[cont + 2].value))
            elementos[cont + 3].value = qtde_metros * densidade_aco
            elementos[cont + 3].value = arred(elementos[cont + 3].value, 2, 1)
        }else {
            elementos[cont + 2].value = ''
            elementos[cont + 3].value = ''
        }
    }
}

function detalhes(id_nfe) {
    nova_janela('../../pedidos/nota_entrada/itens/itens.php?id_nfe='+id_nfe+'&pop_up=1', 'ITENS', 'F')
}
</Script>
</head>
<body>
<form name='form' method='post' action='../../../classes/cotacao/gerar_cotacao.php?compras=1' target='COTACAO'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr> <!-- este tr é para controlar as cores da linha nao retirar -->
    <tr></tr> <!-- este tr é para controlar as cores da linha nao retirar -->
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Ref.
        </td>
        <td>
            Un.
        </td>
        <td>
            Discriminação
        </td>
        <td>
            CMM/<br>CMMV
        </td>
        <td>
            Nec.<br>Comp
        </td>
        <td>
            Comp<br>Prod.
        </td>
        <td>
            Qtd<br>Est.
        </td>
        <td>
            Prazo<br>Ent.
        </td>
        <td>
            Qtd p/<br>Compra
        </td>
        <td>
            Nível<br>Est.
        </td>
        <td>
            Qtd PI<br>Usado
        </td>
        <td>
            Qtd<br>Entr
        </td>
        <td>
            Data<br>Entregue
        </td>
        <td>
            Qtd Mts
        </td>
        <td>
            Qtd Kg
        </td>
    </tr>
<?
    $indice = 0;
    //Esse tratamento é porque na 1ª vez em que se carrega tela, essa variável vem nula, daí dá erro nas fórmulas do Nível ...
    if(empty($cmb_mes)) $cmb_mes = 1;
	
    for($i = 0; $i < $linhas;$i++) {
        $id_produto_insumo = $campos[$i]['id_produto_insumo'];
        //Busca da qtde em Estoque do PI ...
        $sql = "SELECT `qtde` 
                FROM `estoques_insumos` 
                WHERE id_produto_insumo = '$id_produto_insumo' LIMIT 1 ";
        $campos_estoque 	= bancos::sql($sql);
        $quantidade_estoque = (count($campos_estoque) > 0) ? $campos_estoque[0]['qtde'] : 0;
        //Aqui eu busco a densidade do aço, p/ poder fazer o cálculo de qtde_metros p/ qtde_kilos ...
        $sql = "SELECT densidade_aco 
                FROM `produtos_insumos_vs_acos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_densidade       = bancos::sql($sql);
        $densidade_aco          = $campos_densidade[0]['densidade_aco'];
        $title_qtde_kilos       = ($densidade_aco > 0) ? $campos[$i]['discriminacao'].' => '.number_format($quantidade_estoque / $densidade_aco, 2, ',', '.').' Mts' : '';
        $estoque_critico_baixo  = $campos[$i]['estoque_critico_baixo'];
        $estoque_critico_alto   = $campos[$i]['estoque_critico_alto'];
        $qtde_estoque_pi        = $campos[$i]['qtde_estoque_pi'];
        /*************Calculo de Nivel de Estoque*************/
        $qtde_producao          = estoque_ic::compra_producao($id_produto_insumo); //a parte lenta esta aqui
        $qtde_estoque_total     = $quantidade_estoque + $qtde_producao + $qtde_estoque_pi;
        $lista = 0;//n~ listar o registro na tela
        switch($cmb_nivel) {
            case 1://Nível Baixo
                if(($qtde_estoque_total <= $estoque_critico_baixo)) {
                    $lista = 1;//sim listar o registro na tela
                    $nivel_de_estoque = "<font color='red'>Baixo</font>";
                }
            break;
            case 2://Nível Medio
                if(($qtde_estoque_total > $estoque_critico_baixo) && ($qtde_estoque_total <= $estoque_critico_alto)) {
                    $lista = 1;//sim
                    $nivel_de_estoque = "<font color='red'>Médio</font>";
                }
            break;
            case 3://Nível Alto
                if(($qtde_estoque_total > $estoque_critico_alto)) {
                    $lista = 1;//sim
                    $nivel_de_estoque = "<font color='green'>Alto</font>";
                }
            break;
            case 4://Nível medio e alto
                if(($qtde_estoque_total <= $estoque_critico_baixo)) {
                    $lista = 1;//sim listar o registro na tela
                    $nivel_de_estoque = "<font color='red'>Baixo</font>";
                }else if(($qtde_estoque_total > $estoque_critico_baixo) && ($qtde_estoque_total <= $estoque_critico_alto)) {
                    $lista = 1;//sim
                    $nivel_de_estoque = "<font color='red'>Médio</font>";
                }
            break;
            default://Todos os Níveis
                if(($qtde_estoque_total <= $estoque_critico_baixo)) {
                    $lista = 1;//sim listar o registro na tela
                    $nivel_de_estoque = "<font color='red'>Baixo</font>";
                }else if(($qtde_estoque_total > $estoque_critico_baixo)&&($qtde_estoque_total<=$estoque_critico_alto)) {
                    $lista = 1;//sim
                    $nivel_de_estoque = "<font color='red'>Médio</font>";
                }else if(($qtde_estoque_total > $estoque_critico_alto)) {
                    $lista = 1;//sim
                    $nivel_de_estoque = "<font color='green'>Alto</font>";
                }
            break;
        }

        if($lista == 1) {
            $cmm            = $campos[$i]['estoque_mensal'];
            $prazo_entrega  = $campos[$i]['prazo_entrega'];
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$indice;?>' value='<?=$id_produto_insumo;?>' onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'POP_UP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Consultar Inventário' class='link'>
                <?=$campos[$i]['discriminacao'];?>
            </a>
            <?
                if(!empty($campos[$i]['observacao'])) echo "<img width='22'  height='18' title='".$campos[$i]['observacao']."' src = '../../../../imagem/olho.jpg'>";
            ?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='right'>
        <? 
            $retorno        = estoque_ic::consumo_mensal($id_produto_insumo, $campos[$i]['unidade_conversao']);//pego a qtde de cmmv do custo
            $mostrar_cmmv   = $retorno['mostrar_cmmv'];
            $cmmv           = $retorno['cmmv'];
        ?>
<!--Eu também levo o parâmetro de pop_up igual a 1, p/ q o Sistema não abra esse arquivo como sendo uma 
Tela Normal, evitando erro de redirecionamento da Tela, após a atualização dos dados do Produto Insumo-->
            <a href="javascript:nova_janela('../../produtos/alterar.php?passo=1&id_produto_insumo=<?=$id_produto_insumo;?>&pop_up=1', 'pop', '', '', '', '', '620', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=number_format($cmm, 2, ',', '.');?>
            </a>
        <?
            //Caso tenha algum PA atrelado ele segue este caminho aqui ...
            if($mostrar_cmmv == 1) echo '<font color="green"> / '.number_format($cmmv, 2, ',', '.').'</font>';
            $consumo_mensal = ($cmm > $cmmv) ? $cmm : $cmmv;
        ?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='right'>
        <?
            $necessidade_compra = estoque_ic::necessidade_compras($id_produto_insumo);
            if($necessidade_compra > 0) {
        ?>
                <a href="javascript:nova_janela('../../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'NECESSIDADE_COMPRA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Necessidade de Compra' class='link'>
                    <?=segurancas::number_format($necessidade_compra, 2, '.');?>
                </a>
        <?
            }else if($mostrar_cmmv == 1) { //caso tenha algum PA atrelado ele segue este caminho aqui 
        ?>
                <a href="javascript:nova_janela('../../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'NECESSIDADE_COMPRA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Necessidade de Compra' class='link'>
                    <img src = '../../../../imagem/mao.jpg' title="Necessidade de Compra" alt="Necessidade de Compra" border="0">
                </a>
        <?
                }else {
                    echo '&nbsp;';
                }
        ?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='right'>
        <?
            if($qtde_producao > 0) {
        ?>
                <a href="javascript:nova_janela('pendencias_item.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'pop', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                    <?=$qtde_producao;?>
                </a>
        <?
            }else {
                echo $qtde_producao;
            }
        ?>
        </td>
        <td title='<?=$title_qtde_kilos;?>' onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='right'>
            <?=number_format($quantidade_estoque, 2, ',', '.');?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()" align='right'>
            <?=number_format($prazo_entrega, 2, ',', '.');?>
        </td>
        <?
            /*Cálculo de Estoque p/ comprar =
            (Qtde de Meses do Combo + Qtde de Meses da Coluna Prazo de Entrega) * CMM or CMMV usando o Maior ...
            + O Estoque Disponivel = Compra + Estoque - Necessidade ...*/
            $ideal_comprar = $consumo_mensal * ($cmb_mes + $prazo_entrega / 30) - ($quantidade_estoque + $qtde_producao - $necessidade_compra);
            if($ideal_comprar > 0) {//Valor Positivo
                $backcolor  = 'background:red';
                $color      = 'color:white';
            }else {//Valor Negativo
                $backcolor  = 'background:white';
                $color      = 'color:Brown';
            }
        ?>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <input type='text' name='txt_qtde_compra[]' id='txt_qtde_compra<?=$indice;?>' value='<?=number_format($ideal_comprar, 2, ',', '.');?>' title="Valor Inicial => <?=number_format($ideal_comprar, 2, ',', '.');?>" size="9" onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');return focos(this);" onkeyup="controlar_insert();verifica(this,'moeda_especial', '2', '1', event);mostrar_erro('<?=$i;?>')" style='cursor:help;<?=$backcolor.';'.$color;?>' class='textdisabled' disabled>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <?=$nivel_de_estoque;?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
        <?
            if($qtde_estoque_pi != '0.00') echo number_format($qtde_estoque_pi, 2, ',', '.');
        ?>
        </td>
        <td>
            <?
                //Aqui eu busco a Qtde Entregue deste item na última Compra da Nota Fiscal ...
                $sql = "SELECT nfe.id_nfe, nfe.data_emissao, nfeh.qtde_entregue 
                        FROM `nfe_historicos` nfeh 
                        INNER JOIN `nfe` ON nfe.id_nfe = nfeh.id_nfe 
                        WHERE nfeh.id_produto_insumo = '$id_produto_insumo' ORDER BY nfe.id_nfe DESC LIMIT 1 ";
                $campos_nf = bancos::sql($sql);
                if(count($campos_nf) == 1) {//Se encontrar a NF ...
                    $id_nfe 		= $campos_nf[0]['id_nfe'];
                    $data_emissao 	= data::datetodata($campos_nf[0]['data_emissao'], '/');
                    $qtde_entregue 	= number_format($campos_nf[0]['qtde_entregue'], 2, ',', '.');
                }else {//Caso não encontre, então ...
                    $id_nfe 		= '';
                    $data_emissao 	= '';
                    $qtde_entregue 	= '';
                }
            ?>
            <a href="javascript:detalhes('<?=$id_nfe;?>')" title='Detalhes de NF' style='cursor:help' class='link'>
                <?=$qtde_entregue;?>
            </a>
        </td>
        <td>
            <?=$data_emissao;?>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <input type='text' name='txt_qtde_metros[]' id='txt_qtde_metros<?=$indice;?>' size='8' onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');return focos(this);" onkeyup="controlar_insert();verifica(this, 'moeda_especial', '2', '1', event);calcular_qtde_kilos('<?=$indice;?>', '<?=$densidade_aco;?>')" class='textdisabled' disabled>
        </td>
        <td onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');controlar_insert()">
            <input type='text' name='txt_qtde_kg[]' id='txt_qtde_kg<?=$indice;?>' size='8' onclick="checkbox_habilita('<?=$indice;?>', '#E8E8E8');return focos(this);" onkeyup="controlar_insert();verifica(this, 'moeda_especial', '2', '1', event)" class='textdisabled' disabled>
        </td>
    </tr>
<?
            $indice++;
        }
    }
?>
    <tr></tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<pre>
* Comparamos o estoque necessário para a qtde de dias do prazo de entrega => ((Prazo_entrega / 30 + Fator Nível) * CMM) 
com (Qtde Estoque + Compra/Produção).
<b>Nível Baixo</b> => <?=$nivel_baixo;?>, <b>Nível Alto</b> =>  <?=$nivel_alto;?>

<font color='blue'>
* A coluna Nível de Estoque se baseia apenas no CMM, pois tem casos como "Caixa de Papelão" que tem 
CMMV c/ distorção e também sobrecarregaria o servidor se fizesse a busca de todos baixo e médio pelo CMMV.</font>

* <font color="red">O sistema mostra em vermelho o campo Qtde p/ Compra</font> quando o (CMM p/a qtd de meses estipulada na combo + CMM p/o prazo de entrega do PI) 
forem menores que a (Qtde Estoque + Compra/Produção - Necessidade de Compra).

* <b>Qtde p/ Compra </b> = (Qtde de Meses do Combo + Qtde de Meses Prazo de Entrega) * CMM or CMMV usando o Maior ...
- o (Estoque Disponivel = Compra + Estoque - Necessidade)
</pre>
<?
	if(empty($txt_referencia) && empty($txt_discriminacao) && !empty($txt_numero_cotacao)) {
?>
<pre>
<font color='blue'><b>Grupos não apresentados na consulta de todos os Baixos / Médios</b></font>
	
	<b>AÇOS</b>=> AÇOS
	<b>FER</b>=> FERRAMENTAS E ACESSORIOS
	<b>MED</b>=> INSTRUMENTO DE MEDIÇÃO
	<b>ROL</b>=> ROLAMENTO
</pre>
<?
	}
//Se não achar nenhum Item então redireciono p/ essa Tela ...
    if($indice == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'itens.php?achou=1'
        </Script>
<?
    }
?>
<!--Pega o Combo Mês-->
<input type='hidden' name='cmb_mes' value='<?=$cmb_mes;?>'>
</form>
</body>
</html>
<?}?>