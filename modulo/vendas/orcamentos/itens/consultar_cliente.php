<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`tipo_cliente`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, 
                    c.`bairro`, c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE c.`nomefantasia` LIKE '%$txt_consultar%' 
                    AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
        break;
        case 2:
            $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`tipo_cliente`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, 
                    c.`bairro`, c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE c.`razaosocial` LIKE '%$txt_consultar%' 
                    AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
        break;
        case 3:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            
            $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`tipo_cliente`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, 
                    c.`bairro`, c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE c.`cnpj_cpf` = '$txt_consultar%' 
                    AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
        break;
        case 5:
            $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`tipo_cliente`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, 
                    c.`bairro`, c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE c.`tipo_cliente` = '$cmb_tipo_cliente' 
                    AND c.`ativo` = '1' ORDER BY c.razaosocial ";
        break;
        default:
            $sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`tipo_cliente`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, 
                    c.`bairro`, c.`cidade`, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE c.`ativo` = '1' ORDER BY c.`razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar_cliente.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&chkt_orcamento_venda_item=<?=$chkt_orcamento_venda_item?>&acao=<?=$acao;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cliente(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente) {
    document.form.id_cliente.value = id_cliente
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cliente(s) p/ Clonar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Tp Cliente
        </td>
        <td>
            Endereço
        </td>
        <td>
            Bairro
        </td>
        <td>
            Cidade / Estado
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" width='10'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')">
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" class="link">
            <?
                echo $campos[$i]['razaosocial'];
                if(!empty($campos[$i]['nomefantasia'])) echo ' ('.$campos[$i]['nomefantasia'].')';
            ?>
            </a>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_cliente'] == 0) {
                echo 'Revenda Ativa';
            }else if($campos[$i]['tipo_cliente'] == 1) {
                echo 'Revenda Inativa';
            }else if($campos[$i]['tipo_cliente'] == 2) {
                echo 'Cooperado';
            }else if($campos[$i]['tipo_cliente'] == 3) {
                echo 'Indústria';
            }else if($campos[$i]['tipo_cliente'] == 4) {
                echo 'Atacadista';
            }else if($campos[$i]['tipo_cliente'] == 5) {
                echo 'Distribuidor';
            }else if($campos[$i]['tipo_cliente'] == 6) {
                echo 'Internacional';
            }else if($campos[$i]['tipo_cliente'] == 7) {
                echo 'Fornecedor';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['endereco'].', '.$campos[$i]['num_complemento'];?>
        </td>
        <td>
            <?=$campos[$i]['bairro'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cidade'].' / '.$campos[$i]['sigla'];?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_cliente.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&chkt_orcamento_venda_item=<?=$chkt_orcamento_venda_item?>&acao=<?=$acao;?>'" class='botao'>
        </td>
    </tr>
</table>
<?//Quando o parâmetro acao = 0, significa q deseja transportar os itens para o mesmo cliente; ou
//Quando o parâmetro acao = 1, significa q deseja transportar os itens para outro cliente
?>
<input type='hidden' name='acao' value="<?=$acao;?>">
<input type='hidden' name='id_orcamento_venda' value="<?=$id_orcamento_venda;?>">
<input type='hidden' name='chkt_orcamento_venda_item' value="<?=$chkt_orcamento_venda_item;?>">
<input type='hidden' name='id_cliente'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Exclusão de contatos
    if(!empty($id_cliente_contato)) {
        $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function atualizar() {
    document.form.passo.value = 2
    document.form.id_cliente_contato.value = ''
    document.form.submit()
}

function validar() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }
    document.form.passo.value = 3
    //Travo o Botão p/ que o usuário não fique clicando, submetendo a mesma informação de clonagem várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function alterar_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        nova_janela('../../../classes/cliente/alterar_contatos.php?id_cliente_contato='+document.form.cmb_cliente_contato.value, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Exclusão de Contatos
function excluir_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value = 2
            document.form.id_cliente_contato.value = document.form.cmb_cliente_contato.value
            document.form.submit()
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controles de Contato -->
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='id_cliente_contato' value='<?=$id_cliente_contato;?>'>
<!--Caixa que faz o controle de contatos inclusos deste Cliente nesse Orcamento-->
<input type='hidden' name='controle'>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='chkt_orcamento_venda_item' value='<?=$chkt_orcamento_venda_item;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) p/ Clonar
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
        <?
            $sql = "SELECT razaosocial 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['razaosocial'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contato(s) do Cliente:</b>
        </td>
        <td>
            <select name="cmb_cliente_contato" title="Selecione os Contatos do Cliente" class='combo'>
            <?
/*Significa que foi incluido algum contato no Pop-Up de contatos, sendo assim, o sistema sugere esse contato na combo
assim que acaba de ser incluso*/
                    if($controle == 1) {
//Aqui eu pego o ultimo contato que acabou de ser incluido ou alterado
                        $sql = "SELECT id_cliente_contato, nome 
                                FROM `clientes_contatos` 
                                WHERE `id_cliente` = '$id_cliente' 
                                AND `ativo` = '1' ORDER BY id_cliente_contato DESC LIMIT 1 ";
                        $campos_contato     = bancos::sql($sql);
                        $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];
                    }
                    $sql = "SELECT id_cliente_contato, nome 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente` = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY nome ";
                    echo combos::combo($sql, $id_cliente_contato);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/incluir.png' border='0' title='Incluir Contato' alt='Incluir Contato' onclick="nova_janela('../../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Contato' alt='Alterar Contato' onclick='alterar_contato()'>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Contato' alt='Excluir Contato' onclick='excluir_contato()'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'consultar_cliente.php<?=$parametro;?>&passo=1'" class='botao'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR')" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if ($passo == 3) {
    /*Se existirem muitos itens de Orçamento a serem transportados ou clonados, isso faz com que o sistema 
    fique muito pesado e trave a tela não concluindo toda a Rotina, então sendo assim aumentei o timer 
    em específico p/ essa Rotina = 1200 segundos = 20 minutos ...*/
    set_time_limit(1200);
    
    $data_sys                   = date('Y-m-d H:i:s');
//Aqui transforma em um vetor para poder disparar o loop com os itens
    $vetor_orcamento_venda_item = explode(',', $chkt_orcamento_venda_item);
//Aqui busca os dados de cabeçalho do orç. atual p/ poder gerar um novo com o do cliente escolhido
    $sql = "SELECT `finalidade`, `nota_sgd`, `conceder_pis_cofins`, `data_emissao`, 
            `prazo_a`, `prazo_b`, `prazo_c`, `prazo_d`, `prazo_medio` 
            FROM `orcamentos_vendas` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $finalidade             = $campos[0]['finalidade'];
    $nota_sgd               = $campos[0]['nota_sgd'];
    $conceder_pis_cofins    = $campos[0]['conceder_pis_cofins'];
    $prazo_a                = $campos[0]['prazo_a'];
    $prazo_b                = $campos[0]['prazo_b'];
    $prazo_c                = $campos[0]['prazo_c'];
    $prazo_d                = $campos[0]['prazo_d'];
    $prazo_medio            = $campos[0]['prazo_medio'];
//Busca o id_cliente do Cliente que receberá o Orçamento Clonado ... 
    $sql = "SELECT `id_cliente` 
            FROM `clientes_contatos` 
            WHERE `id_cliente_contato` = '$_POST[cmb_cliente_contato]' LIMIT 1 ";
    $campos_cliente_clonado = bancos::sql($sql);
    $id_cliente_clonado     = $campos_cliente_clonado[0]['id_cliente'];
/*Verifico se existe algum Orçamento q esteja em aberto desse Cliente, q não esteja congelado, q não possui nenhuma 
relação de novos PA(s) ESP(s) que precisam ser incluídos pelo Depto. Técnico e que não possua nenhum Item, com exceção 
do ORC Corrente que está sendo clonado ...*/
    $sql = "SELECT id_orcamento_venda 
            FROM `orcamentos_vendas` 
            WHERE `id_cliente` = '$id_cliente_clonado' 
            AND `id_orcamento_venda` <> '$id_orcamento_venda' 
            AND `incluir_novos_pas` = '' 
            AND `congelar` = 'N' 
            AND `status` < '2' 
            AND `id_orcamento_venda` NOT IN 
            (SELECT id_orcamento_venda
            FROM `orcamentos_vendas_itens`) LIMIT 1 ";
    $campos_orcamento = bancos::sql($sql);
    if(count($campos_orcamento) == 1) {//Se encontrou um Orçamento Vazio, posso reaproveitá-lo ...
        $sql = "UPDATE `orcamentos_vendas` SET `id_cliente_contato` = '$_POST[cmb_cliente_contato]', `id_cliente` = '$id_cliente_clonado', `id_funcionario` = '$_SESSION[id_funcionario]', `finalidade` = '$finalidade', `nota_sgd` = '$nota_sgd', `conceder_pis_cofins` ='$conceder_pis_cofins', `data_emissao` = '".date('Y-m-d')."', `prazo_a` = '$prazo_a', `prazo_b` = '$prazo_b', `prazo_c` = '$prazo_c', `prazo_d` = '$prazo_d', `prazo_medio` = '$prazo_medio', `data_sys` = '$data_sys' WHERE `id_orcamento_venda` = '".$campos_orcamento[0]['id_orcamento_venda']."' LIMIT 1 ";
        bancos::sql($sql);
        //De novo não tem nada, só coloquei esse Nome só p/ não ferrar com a lógica + abaixo ... rs
        $id_orcamento_venda_novo = $campos_orcamento[0]['id_orcamento_venda'];
    }else {//Não existe nenhum Orçamento, então sou obrigado a criar um Novo ...
        //O id_cliente_contato q está sendo inserido aki, foi o selecionado pelo usuário na tela anterior ...
        //Aqui é a inserção dos dados de cabeçalho no novo orçamento
        $sql = "INSERT INTO `orcamentos_vendas` (`id_orcamento_venda`, `id_cliente_contato`, `id_cliente`, `id_funcionario`, `finalidade`, `nota_sgd`, `conceder_pis_cofins`, `data_emissao`, `prazo_a`, `prazo_b`, `prazo_c`, `prazo_d`, `prazo_medio`, `data_sys`) VALUES (NULL, '$_POST[cmb_cliente_contato]', '$id_cliente_clonado', '$_SESSION[id_funcionario]', '$finalidade', '$nota_sgd', '$conceder_pis_cofins', '".date('Y-m-d')."', '$prazo_a', '$prazo_b', '$prazo_c', '$prazo_d', '$prazo_medio', '$data_sys') ";
        bancos::sql($sql);
        $id_orcamento_venda_novo = bancos::id_registro();
    }
    //Aqui é a parte da inserção dos itens no novo Orçamento ...
    foreach($vetor_orcamento_venda_item as $i => $id_orcamento_venda_item) {
        //Busca de alguns dados do PA que serão utilizados + abaixo, independente do caso ...
        $sql = "SELECT ged.`id_empresa_divisao` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos_dados_gerais = bancos::sql($sql);

        //Aqui eu busco o Representante do Cliente no Orçamento na Respectiva Empresa Divisão do PA ...
        $sql = "SELECT `id_representante` 
                FROM `clientes_vs_representantes` 
                WHERE `id_cliente` = '$id_cliente_clonado' 
                AND `id_empresa_divisao` = '".$campos_dados_gerais[0]['id_empresa_divisao']."' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        if(count($campos_representante) == 0) {//Não encontrou nenhum Representante na Query acima ...
            exit('REPRESENTANTE NÃO ENCONTRADO, VERIFIQUE SE O REPRESENTANTE ESTE CLIENTE PARA ESTA DIVISÃO !');
        }

        //Aqui busca os dados do item do Orçamento Antigo ...
        $sql = "SELECT `id_produto_acabado`, `qtde`, `desc_cliente`, `promocao`, `desc_extra`, 
                `acrescimo_extra`, `prazo_entrega`, `data_sys` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos			= bancos::sql($sql);
        $id_produto_acabado	= $campos[0]['id_produto_acabado'];
        $qtde                   = $campos[0]['qtde'];
        $desc_cliente           = $campos[0]['desc_cliente'];
        /*Aqui eu zero a Promoção porque em alguns casos o Sistema buscava Promoção até 2, 3 promoções a atual 
        o que gerava um grande Erro ...*/
        if($promocao == 'A' || $promocao == 'B') {//Caso exista Promoção ...
            $promocao           = 'N';
            $desc_extra         = 0;
            $acrescimo_extra	= 0;
        }else { 
            $promocao           = $campos[0]['promocao'];
            $desc_extra         = $campos[0]['desc_extra'];
            $acrescimo_extra	= $campos[0]['acrescimo_extra'];
        }
        $prazo_entrega		= $campos[0]['prazo_entrega'];
        //Insere os Itens do Orçamento Antigo no novo Orçamento que foi Clonado ...
        $sql = "INSERT INTO `orcamentos_vendas_itens` (`id_orcamento_venda_item`, `id_orcamento_venda`, `id_produto_acabado`, `id_representante`, `qtde`, `desc_cliente`, `promocao`, `desc_extra`, `acrescimo_extra`, `prazo_entrega`, `data_sys`) VALUES (NULL, '$id_orcamento_venda_novo', '$id_produto_acabado', '".$campos_representante[0]['id_representante']."', '$qtde', '$desc_cliente', '$promocao', '$desc_extra', '$acrescimo_extra', '$prazo_entrega', '$data_sys') ";
        bancos::sql($sql);
        $id_orcamento_venda_item = bancos::id_registro();
/*******************************************************************************************************/
        vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item, 'S');
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
        custos::margem_lucro_estimada($id_orcamento_venda_item);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
        vendas::calculo_ml_comissao_item_orc($id_orcamento_venda_novo, $id_orcamento_venda_item);
    }
?>
    <Script Language = 'JavaScript'>
        alert('ORÇAMENTO N.º '+<?=$id_orcamento_venda_novo;?>+' GERADO / REAPROVEITADO COM SUCESSO !')
        window.parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda_novo;?>'
    </Script>
<?
}else {
//Aqui disparo o loop de Itens de Orçamento p/ poder acumular no vetor de Itens de Orçamento
    if(is_array($_POST['chkt_orcamento_venda_item'])) {
        foreach($_POST['chkt_orcamento_venda_item'] as $id_orcamento_venda_item) $vetor_orcamento_venda_item.= $id_orcamento_venda_item.', ';
        $chkt_orcamento_venda_item = substr($vetor_orcamento_venda_item, 0, strlen($vetor_orcamento_venda_item) - 2);
    }
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = true
        document.form.cmb_tipo_cliente.disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = false
        document.form.opt_opcao[1].checked      = true
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }else {
        if(document.form.opt_opcao[4].disabled == false) {
            if(document.form.cmb_tipo_cliente.value == '') {
                alert('SELECIONE UM TIPO DE CLIENTE !')
                document.form.cmb_tipo_cliente.focus()
                return false
            }
        }
    }
}

function desabilitar() {
    if(document.form.opt_opcao[3].checked == true) {
        document.form.cmb_tipo_cliente.disabled = false
        document.form.cmb_tipo_cliente.value    = ''
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.cmb_tipo_cliente.disabled = true
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}
</script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()";>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) p/ Clonar 
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' id="opt1" name='opt_opcao' value="1" onclick="desabilitar()" title="Consultar cliente por: Nome Fantasia">
            <label for="opt1">Nome Fantasia</label>
        </td>
        <td width='20%'>
            <input type='radio' id="opt2" name='opt_opcao' value="2" checked onclick="desabilitar()" title="Consultar cliente por: Razão Social">
            <label for="opt2">Razão Social</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' id='opt3' name='opt_opcao' value='3' onclick="desabilitar()" title="Consultar cliente por: CNPJ / CPF">
            <label for='opt3'>CNPJ / CPF</label>
        </td>
        <td>
            <input type='radio' id="opt5" name='opt_opcao' value="5" onclick="desabilitar()" title="Consultar cliente por: Tipo de Cliente"><label for="opt5">Tipo de Cliente</label>
            <select name="cmb_tipo_cliente" title="Selecione o Tipo de Cliente" class='combo' disabled>
                <option value="" style="color:red" selected>SELECIONE</option>
                <option value="0">Revenda Ativa</option>
                <option value="1">Revenda Inativa</option>
                <option value="2">Cooperado</option>
                <option value="3">Indústria</option>
                <option value="4">Atacadista</option>
                <option value="5">Distribuidor</option>
                <option value="6">Internacional</option>
                <option value="7">Fornecedor</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' id="todos" name='opcao' onclick='limpar()' value='1' title="Consultar todos os clientes" class="checkbox">
            <label for='todos'>Todos os registros
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'transportar_outro_orcamento.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&acao=<?=$acao;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
<?//Quando o parâmetro acao = 0, significa q deseja transportar os itens para o mesmo cliente; ou
//Quando o parâmetro acao = 1, significa q deseja transportar os itens para outro cliente
?>
<input type='hidden' name='acao' value="<?=$acao;?>">
<input type='hidden' name='id_orcamento_venda' value="<?=$id_orcamento_venda;?>">
<input type='hidden' name='chkt_orcamento_venda_item' value="<?=$chkt_orcamento_venda_item;?>">
</form>
</body>
</html>
<?}?>