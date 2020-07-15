<?
require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/vendas.php');
session_start('funcionarios');

if($passo == 1) {
    /*Aqui eu verifico se existe pelo menos 1 Orçamento do Cliente que esteja em aberto sem Itens em que o campo 
    "Incluir Novos Itens" esteja vazio, pois se esse campo estiver preenchido significa que logo + será incluido algum item 
    no Orçamento pelo Departamento Técnico ...*/
    $sql = "SELECT `id_orcamento_venda` 
            FROM `orcamentos_vendas` 
            WHERE `id_cliente` = '$_GET[id_cliente]' 
            AND `incluir_novos_pas` = '' 
            AND `id_orcamento_venda` NOT IN 
            (SELECT DISTINCT(ov.`id_orcamento_venda`) 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
            WHERE ov.`id_cliente` = '$_GET[id_cliente]') 
            ORDER BY `id_orcamento_venda` LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe um Orçamento em aberto sem Itens, sendo assim eu só reaproveita esta ...
        $id_orcamento_venda = $campos[0]['id_orcamento_venda'];
        //Atualiza a Data de Emissão do Orçamento que foi reaproveitado com a Data Atual ...
        $sql = "UPDATE `orcamentos_vendas` SET `data_emissao` = '".date('Y-m-d')."' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        bancos::sql($sql);
    }else {//Ainda não existe nenhum Orçamento sem Itens, sendo assim eu vou gerar uma Orçamento ...
        $sql = "SELECT `id_cliente_tipo`, `artigo_isencao`, `tipo_suframa`, `suframa_ativo` 
                FROM `clientes` 
                WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if($campos_cliente[0]['artigo_isencao'] == 1) $artigo_isencao = 1;
        
        $finalidade = ($campos_cliente[0]['id_tipo_cliente'] == 4) ? 'C' : 'R';//Se Tipo de Cliente = 'Industria' - CONSUMO ...
        
        if(($campos_cliente[0]['tipo_suframa'] == 1 || $campos_cliente[0]['tipo_suframa'] == 2) && $campos_cliente[0]['suframa_ativo'] == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
            $conceder_pis_cofins = 'S';
        }
        
        //Busco o primeiro Contato do Cliente que estiver cadastrado ...
        $sql = "SELECT `id_cliente_contato` 
                FROM `clientes_contatos` 
                WHERE `id_cliente` = '$_GET[id_cliente]' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_contato     = bancos::sql($sql);
        if(count($campos_contato) == 0) {//Não existe nenhum Contato cadastrado p/ este Cliente, sendo assim gero um automático ...
            $sql = "INSERT INTO `clientes_contatos` (`id_cliente_contato`, `id_cliente`, `id_departamento`, `nome`, `opcao_phone`, `ddi`, `ddd`, `telefone`, `ramal`, `email`, `observacao`, `ativo`) VALUES (NULL, '$_GET[id_cliente]', '4', 'Contato Automático', '0', '0', '0', '0', '0', '', 'Esse contato precisa ser alterado. Foi gerado sozinho pelo Sistema.', '1') ";
            bancos::sql($sql);
            $id_cliente_contato = bancos::id_registro();
        }else {//Existia pelo menos 1 contato cadastrado ...
            $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];
        }
        
        if(!empty($_SESSION[id_funcionario])) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
            $campo = " `id_funcionario` ";
            $valor = " '$_SESSION[id_funcionario]' ";
        }else {//No demais representantes ...
            $campo = " `id_login` ";
            $valor = " '$_SESSION[id_login]' ";
        }
        
        $sql = "INSERT INTO `orcamentos_vendas` (`id_orcamento_venda`, `id_cliente`, `id_cliente_contato`, $campo, `finalidade`, `artigo_isencao`, `nota_sgd`, `conceder_pis_cofins`, `data_emissao`, `prazo_a`, `prazo_medio`, `data_sys`, `status`) VALUES (NULL, '$_GET[id_cliente]', '$id_cliente_contato', $valor, '$finalidade', '$artigo_isencao', 'N', '$conceder_pis_cofins', '".date('Y-m-d')."', '28', '28', '".date('Y-m-d H:i:s')."', '1') ";
        bancos::sql($sql);
        $id_orcamento_venda = bancos::id_registro();
    }
?>
    <Script Language = 'JavaScript'>
        //Estou vendo se esse procedimento foi aberto como sendo Pop-UP se sim, então ...
        if(opener != null) {
            opener.window.location = 'itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
            window.close()
        }else {
            parent.window.location = 'itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
        }
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal 	= '../../..';
    $qtde_por_pagina            = 50;
    $veio_incluir_orcamento 	= 1;
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../classes/cliente/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Incluir Novo Orçamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente, credito) {
    if(credito == 'C' || credito == 'D') {
        alert('CLIENTE COM CRÉDITO '+credito+' !\n POR FAVOR CONTATAR O DEPTO. FINANCEIRO !')
        window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
    }else {
/*Se o Cliente em que irá ser feito o Orçamento for Albafer = 2276 ou Tool Master = 2688, então os únicos que 
poderão orçar para estes Clientes são o Roberto 62, Fábio 64, Dárcio 98 "porque programa" ou Nishimura 136 ...*/
        if(id_cliente == 2276 || id_cliente == 2688) {
            var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>')
            if(id_funcionario != 62 && id_funcionario != 64 && id_funcionario != 98 && id_funcionario != 136) {
                alert('SOMENTE ALGUM(NS) USUÁRIO(S) ESPECÍFICO(S) QUE PODEM CRIAR ORÇAMENTO COM ESTE CLIENTE !')
                return false
            }
        }
        window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
    }
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Incluir Novo Orçamento
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp Cliente
        </td>
        <td>
            Duplicatas
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>', '<?=$campos[$i]['credito'];?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>', '<?=$campos[$i]['credito'];?>')" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT id_conta_receber 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_contas_receberes = bancos::sql($sql);
            if(count($campos_contas_receberes) == 1) {
                    echo 'SIM';
            }else {
                    echo 'NÃO';
            }
        ?>
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
    <tr class="linhacabecalho" align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
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