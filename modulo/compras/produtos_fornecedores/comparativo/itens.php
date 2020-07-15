<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/comparativo/index.php', '../../../../');

$taxa_financeira_compras = genericas::variavel(4);
?>
<html>
<head>
<title>.:: Consultar Produtos Insumos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Funções p/ Controlar a Cor da Tabela ...
function sobre_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    if((cor_atual == 'rgb(198,226,255)')||(cor_atual == '#c6e2ff')) {
        if(navigator.appName == 'Netscape') {
            cel_atual.style.backgroundColor = 'rgb(198,226,255)'
        }else {
            cel_atual.style.backgroundColor = '#c6e2ff'
        }
    }else {
        cel_atual.style.backgroundColor = nova_cor
    }
    cel_atual.style.cursor='hand'
}

function fora_celula(cel_atual, backgroundColor) {
    var cor_atual = cel_atual.style.backgroundColor
    var nova_cor = backgroundColor
    if((cor_atual == 'rgb(198,226,255)') || (cor_atual == '#c6e2ff')){
        if(navigator.appName == 'Netscape') {
            cel_atual.style.backgroundColor = 'rgb(198,226,255)'
        }else {
            cel_atual.style.backgroundColor = '#c6e2ff'
        }
    }else {
        cel_atual.style.backgroundColor = nova_cor
    }
}

function retornar(objeto) {
    if(objeto.checked == true) {
        document.form.hdd_fornecedores.value+= objeto.value + ','
    }else {
        var numero = '', id_fornecedores = '', flag = 0
        vetor = document.form.hdd_fornecedores.value
        for(i = 0; i < vetor.length; i++) {
            if(vetor.charAt(i) == ',') {
                numero = eval(numero)
                if(numero != objeto.value) id_fornecedores+= numero + ','
                numero = ''
            }else {
                numero+= vetor.charAt(i)
            }
        }
        document.form.hdd_fornecedores.value = id_fornecedores
    }
}

function checar(qtde_checkbox, posicao_todos) {
    posicao_todos = eval(posicao_todos)
    qtde_checkbox = eval(qtde_checkbox)
    if(document.form.elements[posicao_todos].checked == true) {
        document.form.elements[posicao_todos].checked = false
    }else {
        contador = 0
        for(i = 1; i <= qtde_checkbox; i++) {
            if(document.form.elements[posicao_todos + i].checked == true) contador++
        }
        if(contador == qtde_checkbox) {
            document.form.elements[posicao_todos].checked = true
        }
    }
}

function selecionar_grupo(grupo, qtde_itens_grupo) {
    var id_fornecedores = ''
    for(var i = 0; i < qtde_itens_grupo; i++) {
        if(document.getElementById(grupo.id).checked) {
            document.getElementById('itens_'+grupo.id+'|'+i).checked = true
            //Verifico se o id_fornecedor já existe no hidden de Fornecedores, senão existir então eu adiciono o mesmo ...
            if(document.form.hdd_fornecedores.value.indexOf(document.getElementById('itens_'+grupo.id+'|'+i).value + ',') == -1) {
                id_fornecedores+= document.getElementById('itens_'+grupo.id+'|'+i).value + ','
            }
        }else {
            document.getElementById('itens_'+grupo.id+'|'+i).checked = false
            //Verifico se o id_fornecedor já existe no hidden de Fornecedores, se existir então eu retiro o mesmo ...
            if(document.form.hdd_fornecedores.value.indexOf(document.getElementById('itens_'+grupo.id+'|'+i).value + ',') != -1) {
                document.form.hdd_fornecedores.value = document.form.hdd_fornecedores.value.replace(document.getElementById('itens_'+grupo.id+'|'+i).value + ',', '')
            }
        }
    }
    document.form.hdd_fornecedores.value = document.form.hdd_fornecedores.value + id_fornecedores
}

function excluir_fornecedor(id_fornecedor_prod_insumo, pode_excluir_fornec) {
/*Significa que este Fornecedor Corrente é o Fornecedor Default, sendo assim, não posso estar excluindo
esse fornecedor desse PI*/
    if(pode_excluir_fornec == 1) {
        alert('ESSE FORNECEDOR NÃO PODE SER DESATRELADO !\nDEVIDO ESTE SER O FORNECEDOR DEFAULT DESTE PRODUTO INSUMO !!!')
//Não é o Fornecedor Default, então posso estar excluindo esse fornecedor normalmente desse PI
    }else {
        var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE FORNECEDOR DESSE PRODUTO INSUMO ?')
        if(resposta == true) {
//Significa que essa Tela de Comparativo, foi acessada de forma normal pelo menu em Compras ..
            if(typeof(window.parent.itens) == 'object') {
                parent.itens.document.location = 'itens.php?id_prods_insumos=<?=$id_prods_insumos;?>&id_fornecedor_prod_insumo='+id_fornecedor_prod_insumo
//Significa que a Tela do Comparativo foi acessado pela ferramento de Detalhes do PI ... 
            }else {
                document.location = 'itens.php?id_prods_insumos=<?=$id_prods_insumos;?>&id_fornecedor_prod_insumo='+id_fornecedor_prod_insumo
            }
        }
    }
}
</Script>
</head>
<body topmargin='30'>
<form name='form' method='post' action="pdf/relatorio.php" onsubmit="return validar()">
<input type='hidden' name='hdd_fornecedores'>
<!--Armazena todos os Produtos Insumos q foram atrelados p/ a comparação de Preço ...-->
<input type='hidden' name='id_prods_insumos' value='<?=$id_prods_insumos;?>'>
<!--Fornecedores com a qual se deseja a apresentação no Relatório-->
<?
//Aqui Desatrela o Fornecedor de um Produto Insumo específico selecionado pelo Usuário ...
if(!empty($id_fornecedor_prod_insumo)) {
//Aqui é para não furar o SQL
    if($id_fornecedor_prod_insumo == '') $id_fornecedor_prod_insumo = 0;
//Além de eu desatrelar o Fornecedor do PI, eu também já zero os preços deste Fornec na lista de Preço ...
    $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '0.00', `preco_faturado_export` = '0.00', `ativo` = '0' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
    bancos::sql($sql);
}

//Se essa tela foi submetida pelo menos 1 vez ...
if(!empty($id_prods_insumos)) {
    $vetor_produtos_insumos = explode(',', $id_prods_insumos);//Transforma em Vetor ...
}else {//Quando acaba de carregar a Tela ...
    $vetor_produtos_insumos = array('0');
}

//Começo o índice como 2, porque antes do 1º Checkbox Principal, eu tenho 2 caixas hiddens ...
$indice_elemento = 2;
//Disparo nesse Loop, todos os PI(s) que foram atrelados p/ a comparação de Preço ...
for($y = 0; $y < count($vetor_produtos_insumos); $y++) {
    $id_produto_insumo_loop = $vetor_produtos_insumos[$y];

    $sql = "SELECT fpi.*, g.`referencia`, pi.`discriminacao`, pi.`credito_icms`, 
            f.`id_fornecedor`, f.`razaosocial` 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`ativo` = '1' AND f.`razaosocial` <> '' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE fpi.`id_produto_insumo` = '$id_produto_insumo_loop' 
            AND fpi.`ativo` = '1' ORDER BY fpi.`preco_faturado` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='1700' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
        if($y == 0) {//Esse rótulo só será exibido na 1ª linha ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='20'>
            Comparativo de Preço(s)
        </td>    
    </tr>

<?
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='20'>
            <label for="grupo.<?=$y?>">Excluir Grupo: <input type="checkbox" name="chkt_produto_insumo" value="<?=$id_produto_insumo_loop;?>" class="checkbox" id="grupo.<?=$y?>">
<?
            $indice_elemento++;//Soma + 1 por causa do checkbox anterior que carregou no loop ...
            $indice_elemento_todos = $indice_elemento;
?>
            <font color='yellow'>
                Referência: 
            </font>
            <?=$campos[0]['referencia'];?>
            - 
            <font color='yellow'>
                Discriminação:
            </font> 
            <?
                echo $campos[0]['discriminacao'];
//Verifico o PI tem a Marcação de Sem Crédito de ICMS no cadastro ...
                if($campos[0]['credito_icms'] == 0) {//Nesse caso o Valor '0' é true ...
                    echo ' <font color="yellow">(SEM CRÉDITO ICMS)</font>';
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td width='80'>
            <label for="todos<?=$y;?>">Todos</label>
            <input type="checkbox" onclick="selecionar_grupo(this, '<?=$linhas;?>')" id="todos<?=$y;?>" class="checkbox">
        </td>
        <td width='350'>
            Fornecedor&nbsp;
            <input type='button' name="cmd_default" value="Default" title="Default" onclick="nova_janela('../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$id_produto_insumo_loop;?>', 'CONSULTAR', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" style="color:red" class='botao'>
            <input type='button' name="cmd_atrelar_fornecedor" value="Atrelar Fornecedor" title="Atrelar Fornecedor" onclick="nova_janela('../atrelar_fornecedor_em_pi.php?id_produto_insumo=<?=$id_produto_insumo_loop;?>', 'CONSULTAR', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
        <?
            //Soma + 2 por causa do outro checkbox e botão anteriores que carregou no loop ...
            $indice_elemento+=2;
        ?>
        <td>
            Preço Fat. <br>Nac. R$
        </td>
        <td>
            Prazo Pgto <br>Dias
        </td>
        <td>
            Desc. A/V %
        </td>
        <td>
            Desc. SGD %
        </td>
        <td>
            IPI %
        </td>
        <td>
            IPI<br/>Incl
        </td>
        <td>
            ICMS %
        </td>
        <td>
            Redução %
        </td>
        <td>
            IVA %
        </td>
        <td>
            Preço A/V Nac. <br>R$ s/ ICMS
        </td>
        <td>
            Forma <br>de Compra
        </td>
        <td>
            Preço <br>de Compra Nac.
        </td>
        <td>
            Tipo <br>de Moeda
        </td>
        <td>
            Preço <br>Fat. Moeda
        </td>
        <td>
            Valor Moeda <br>p/ Compra
        </td>
        <td>
            Preço de <br>Compra (Inter)
        </td>
        <td>
            Valor Moeda <br>p/ Custo
        </td>
        <td>
            Preço <br>de Custo
        </td>
    </tr>
<?
/*Aqui eu verifico quem é o Fornecedor da Última Compra deste Produto Insumo ...
Vou utilizar esse id + abaixo p/ fazer algumas comparações ...*/
        $sql = "SELECT `id_fornecedor_default` 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo_loop' 
                AND `id_fornecedor_default` > '0' 
                AND `ativo` = '1' ";
        $campos_default         = bancos::sql($sql);
        $id_fornecedor_default  = $campos_default[0]['id_fornecedor_default'];

        for($i = 0; $i < $linhas; $i++) {
            $preco_faturado = $campos[$i]['preco_faturado'];
            if($preco_faturado == '') $preco_faturado = '&nbsp;';

            $prazo_pgto_dias = $campos[$i]['prazo_pgto_ddl'];
            if($prazo_pgto_dias == '') $prazo_pgto_dias = '&nbsp;';

            $desc_avista = $campos[$i]['desc_vista'];
            if($desc_avista == '') $desc_avista = '&nbsp;';

            $desc_sgd = $campos[$i]['desc_sgd'];
            if($desc_sgd == '') $desc_sgd = '&nbsp;';

            $ipi = $campos[$i]['ipi'];
            if($ipi == '') $ipi = '&nbsp;';

            $icms = $campos[$i]['icms'];
            if($icms == '') $icms = '&nbsp;';

            $reducao = $campos[$i]['reducao'];
            if($reducao == '') $reducao = '&nbsp;';

            $iva = $campos[$i]['iva'];
            if($iva == '') $iva = '&nbsp;';

            $forma_compra = $campos[$i]['forma_compra'];
            if($forma_compra == 0) {
                $forma_compra = '&nbsp;';
            }else if($forma_compra == 1) {
                $forma_compra = 'FAT/NF';
            }else if($forma_compra == 2) {
                $forma_compra = 'FAT/SGD';
            }else if($forma_compra == 3) {
                $forma_compra = 'AV/NF';
            }else if($forma_compra == 4) {
                $forma_compra = 'AV/SGD';
            }

            $preco_nacional = $campos[$i]['preco'];
            if($preco_nacional == '') $preco_nacional = '&nbsp;';

            $tipo_moeda = $campos[$i]['tp_moeda'];
            if($tipo_moeda == '') $tipo_moeda = '&nbsp;';

            if($tipo_moeda == 1) {
                $tipo_moeda = 'U$';
            }else if($tipo_moeda == 2) {
                $tipo_moeda = '&euro;';
            }else {
                $tipo_moeda = 'R$';
            }

            $preco_compra_internac = $campos[$i]['preco_exportacao'];
            if($preco_compra_internac == '') $preco_compra_internac = '&nbsp;';

            $valor_moeda_compra = $campos[$i]['valor_moeda_compra'];
            if($valor_moeda_compra == '') $valor_moeda_compra = '&nbsp;';

            $preco_internacional = $campos[$i]['preco_faturado_export'];
            if($preco_internacional == '0.00' or $preco_internacional == '') $preco_internacional = '&nbsp;';

            $valor_moeda_custo = $campos[$i]['valor_moeda_custo'];
            if($valor_moeda_custo == '') $valor_moeda_custo = '&nbsp;';

            $preco_custo = $campos[$i]['preco_custo'];
            if($preco_custo == '') $preco_custo = '&nbsp;';
?>
<label for="itens_todos<?=$y.$i;?>">
    <tr class="linhanormal" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type="checkbox" name="chkt_opcao[]" value="<?=$campos[$i]['id_fornecedor'];?>" onclick="retornar(this);checar('<?=$linhas;?>', '<?=$indice_elemento_todos;?>')" id="itens_todos<?=$y.'|'.$i;?>" class="checkbox">
            <?$indice_elemento++;//Soma + 1 por causa do checkbox anterior que carregou no loop ...?>
        </td>
        <td>
            <label for="<?=$y.'|'.$i;?>">
                <?
/*Significa que este Fornecedor do Loop é o Fornecedor Default, e sendo assim eu não posso estar excluindo
este fornecedor*/
                    if($id_fornecedor_default == $campos[$i]['id_fornecedor']) {
//Aqui eu verifico se esse P.I. é do Tipo P.A e se neste a OC = 'Industrial' ...
                        $sql = "SELECT `operacao_custo` 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_insumo` = '$id_produto_insumo_loop' LIMIT 1 ";
                        $campos_pipa = bancos::sql($sql);
                        if(count($campos_pipa) == 1) {//Significa que esse PI é um PA ...
                            if($campos_pipa[0]['operacao_custo'] == 0) {//Ind, pode desat. normalm
                                $pode_excluir_fornec = 0;
                            }else {//Revenda não pode desatrelar ...
                                $pode_excluir_fornec = 1;
                            }
                        }else {//É simplesmente um PI, então não posso desatrelar esse Fornec ...
                            $pode_excluir_fornec = 1;
                        }
                    }else {
                        $pode_excluir_fornec = 0;
                    }
                ?>
                <img src = "../../../../imagem/menu/excluir.png" border='0' title="Excluir Fornecedor" alt="Excluir Fornecedor" onClick="excluir_fornecedor('<?=$campos[$i]['id_fornecedor_prod_insumo'];?>', '<?=$pode_excluir_fornec;?>')">
                <?
/*Significa que este Fornecedor do Loop é o Fornecedor Default, e sendo assim eu exibo todos os Detalhes 
da Última Compra*/
                    if($id_fornecedor_default == $campos[$i]['id_fornecedor']) {
                ?>
                        <a href="javascript:nova_janela('../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$id_produto_insumo_loop;?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da Última Compra' class='link'>
                            <font color='red'>
                <?
                    }
                    echo $campos[$i]['razaosocial'];
                ?>
                &nbsp;
                <font color='black' size='-2' title='Data da Última Atualização' style='cursor:help'>
                    <b>
                        (<?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/');
                
                            //Aqui eu busco o nome do Funcionário que fez a última modificação na Lista de Preço ...
                            if(!empty($campos[$i]['id_funcionario'])) {
                                $sql = "SELECT SUBSTRING_INDEX(`nome`, ' ', 1) AS nome 
                                        FROM `funcionarios` 
                                        WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                                $campos_funcionario = bancos::sql($sql);
                                echo ' - '.$campos_funcionario[0]['nome'];
                            }
                        ?>)
                    </b>
                </font>
            </label>
        </td>
        <td align='right'>
            <a href="javascript:nova_janela('../alterar_lista_preco.php?id_prods_insumos=<?=$id_prods_insumos;?>&id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>', 'LISTA', '', '', '', '', 530, 650, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Lista de Preço' class='link'>
                <?=number_format($preco_faturado, 2, ',', '.');?>
            </a>
        </td>
        <td align='center'>
            <label for="<?=$y.$i;?>">
                <?=number_format($prazo_pgto_dias, 2, ',', '.');?>
            </label>
        </td>
        <td align='right'>
            <label for="<?=$y.$i;?>">
                <?=number_format($desc_avista, 2, ',', '.');?>
            <label>
        </td>
        <td align='right'>
            <label for="<?=$y.$i;?>">
                <?=number_format($desc_sgd, 2, ',', '.');?>
            </label>
        </td>
        <td align='right'>
            <?=number_format($ipi, 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[$i]['ipi_incluso'] == 'S') {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($icms, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($reducao, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($iva, 2, ',', '.');?>
        </td>
        <td align='right'>
            <b>
            <?
                if($preco_faturado > 0) {
                    $preco_a_utilizar = $preco_faturado;
                }else if($preco_compra_internac > 0) {
                    $preco_a_utilizar = $preco_compra_internac;
                }
                
                //Essa taxa serve p/ calcularmos o Preco de Compra a Vista ...
                $tx_financeira_pzo_medio_fornec = $taxa_financeira_compras / 30 * $prazo_pgto_dias;
                $preco_a_utilizar               = $preco_a_utilizar / (1 + $tx_financeira_pzo_medio_fornec / 100);
                $icms_com_reducao               =  $icms * (1 - $reducao / 100);
/*Verifico o PI tem a Marcação de Sem Crédito de ICMS no cadastro e se existir essa 
marcação eu não posso conceder esse Desconto em cima do Preço Faturado ...*/
                //No IF abaixo, significa que existe Crédito de ICMS, então possui Desconto um desconto a mais ...
                if($campos[0]['credito_icms'] > 0) $preco_a_utilizar*= (100 - $icms_com_reducao) / 100;
                echo number_format($preco_a_utilizar, 2, ',', '.');
/********************************************************************************************************************/
                //Aqui eu verifico qual é o menor Preço p/ apresentar p/ o usuário no fim do histórico ...
                if(!isset($menor_preco)) {//No 1º Loop, ainda não foi criada essa variável ...
                    $menor_preco        = $preco_a_utilizar;
                    $melhor_fornecedor  = $campos[$i]['razaosocial'];
                }else {
                    //Enquanto o Menor Preço for maior que o Preço Faturado, eu faço uma nova atribuição na variável ...
                    if($menor_preco > $preco_a_utilizar) {
                        $menor_preco        = $preco_a_utilizar;
                        $melhor_fornecedor  = $campos[$i]['razaosocial'];
                    }
                }
/********************************************************************************************************************/
            ?>
            </b>
        </td>
        <td align='center'>
            <?=$forma_compra;?>
        </td>
        <td align='right'>
            <?=number_format($preco_nacional, 2, ',', '.');?>
        </td>
        <td align='center'>
            <?=$tipo_moeda;?>
        </td>
        <td align='right'>
            <?=number_format($preco_internacional, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($valor_moeda_compra, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($preco_compra_internac, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($valor_moeda_custo, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($preco_custo, 2, ',', '.');?>
        </td>
    </tr>
</label>
<?
        }
?>
    <tr class='linhadestaque'>
        <td colspan='20'>
            <font color='yellow'>
                Melhor Fornecedor => 
            </font>
            <?=$melhor_fornecedor?>
            <font color='yellow'>
                Menor Preço => 
            </font>
            R$ <?=number_format($menor_preco, 2, ',', '.');?>
        </td>
    </tr>
    <tr></tr>
</table>
<?
    }else {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO EXISTE(M) FORNECEDOR(ES) ATRELADO(S) PARA ESTE GRUPO !')
    </Script>
<?
    }
    unset($menor_preco);//Destruo a variável p/ não dar conflito com o Menor Preço do Próximo Produto no Próximo Loop ...
}
?>
</form>
</body>
</html>