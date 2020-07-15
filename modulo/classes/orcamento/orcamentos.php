<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/alterar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>N�O H� OR�AMENTO(S) QUE CONT�M ESSE PA ATRELADO.</font>";

/*******************************************************************************/
//Controle para ver se o usu�rio tem permiss�o no menu de "Follow-Up do Cliente"
$endereco = '/modulo/producao/custo/follow_up_cliente/follow_up_cliente.php';

$sql = "SELECT id_menu_item 
        FROM `menus_itens` 
        WHERE `endereco` LIKE '%$endereco%'";
$campos         = bancos::sql($sql);
$id_menu_item   = $campos[0]['id_menu_item'];

/*Aqui eu verifico se o usu�rio tem permiss�o no menu p/ disponibilizar um link p/ ele poder registrar
o follow_Up dessa tela mesmo*/
$sql = "SELECT id_tipo_acesso 
        FROM `tipos_acessos` 
        WHERE `id_login` = '$_SESSION[id_login]' 
        AND `id_menu_item` = '$id_menu_item' ";
$campos = bancos::sql($sql);
if(count($campos) == 1) $exibir_link_follow_up = 1;
/*******************************************************************************/

//Busco a Refer�ncia e a Opera��o de Custo do P.A. ...
$sql = "SELECT referencia, operacao_custo 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$referencia     = $campos[0]['referencia'];
$operacao_custo = $campos[0]['operacao_custo'];
/*Se o P.A. � a 'ESP' e a Opera��o de Custo = 'Revenda' eu preciso buscar qual � o id_fornecedor_setado 
porque vou utilizar mais abaixo p/ gravar no Banco*/
if($referencia == 'ESP' && $operacao_custo == 1) {
    $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($_GET['id_produto_acabado'], '', 1);
}

//Listagem de Todos os Or�amento(s) que cont�m esse PA atrelado
$sql = "SELECT c.id_cliente, c.nomefantasia, c.razaosocial, c.ddi_com, c.ddd_com, c.telcom, c.ddi_fax, c.ddd_fax, c.telfax, ov.id_orcamento_venda, ov.id_funcionario, ov.data_sys, ov.congelar, DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda_item, ovi.qtde, ovi.prazo_entrega, ovi.prazo_entrega_tecnico, ovi.status 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        WHERE ovi.`id_produto_acabado` = '$_GET[id_produto_acabado]' ORDER BY ov.id_orcamento_venda DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Or�amento(s) que cont�m esse PA atrelado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form'>
<!--
Esse hidden � um controle dessa tela porque ... essa tela � chamada de dentro de um Pop-Up (Lista de Pre�o)
no M�dulo de Produ��o, no Menu de Custo, Op��o de Revenda (ESP).
E da� tem um outro iframe, que quando eu acabo de Salvar altera��es tem que estar atualizando esse arquivo
aqui, ent�o por isso que eu coloquei essa vari�vel de $id_produto_acabado num hidden, p/ que pudesse
estar submetendo esse arquivo.
-->
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    if($linhas == 0) {//
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Or�amento(s) que cont�m esse PA atrelado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <font title='N.� do Or�amento' style='cursor:help'>
                N.� Orc.
            </font>
        </td>
        <td rowspan='2'>
            Data<br/>Emiss�o
        </td>
        <td rowspan='2'>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td colspan='2'>
            Prazo de Entrega
        </td>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2'>
            Vendedor
        </td>
        <td rowspan='2'>
            <font title='�ltima Atualiza��o' style='cursor:help'>
                Atualizado
            </font>
        </td>
        <td rowspan='2'>
            Status
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            T&eacute;cnico
        </td>
        <td>
            Vendedor
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
//1) Verifico se na discrimina��o dessa String existe a express�o XXX, p/ exibir o Link ...
            $discriminacao = intermodular::pa_discriminacao($_GET['id_produto_acabado']);
            if(strstr($discriminacao, 'xxx') || strstr($discriminacao, 'XXX')) {
//2) S� ir� exibir o link quando o Item tiver com pend�ncia Total ...
                if($campos[$i]['status'] == 0) {//Pend�ncia Total ...
//3) S� ir� exibir o link p/ Roberto 62, D�rcio 98 porque programa ...
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
//Passo o id_produto_acabado tamb�m p/ facilitar a vida na outra tela e n�o ter q fazer SQL ...
    ?>
            <a href="substituir_pa_orcamento.php?id_orcamento_venda_item=<?=$campos[$i]['id_orcamento_venda_item'];?>&id_produto_acabado=<?=$_GET['id_produto_acabado'];?>" title='Substituir P.A do Or�amento' class='link'>
                Substituir PA&nbsp;
            </a>
    <?
                    }
                }
            }
        ?>
            <a href="javascript:nova_janela('../../vendas/pedidos/itens/detalhes_orcamento.php?id_orcamento_venda=<?=$campos[$i]['id_orcamento_venda'];?>', 'ORC', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Or�amento' class='link'>
                <?=$campos[$i]['id_orcamento_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
/************************Tratamento Novo com Rela��o ao Prazo de Entrega************************/
/*Se o P.A. � do Tipo = 'ESP' e a O.C. = 'Revenda', ent�o eu ignoro o prazo_entrega_tecnico da Tabela
de Or�amento e leio o "Prazo de Entrega" da tabela relacional de 'prazos_revendas_esps' ...*/
            if($referencia == 'ESP' && $operacao_custo == 1) {
                $sql = "SELECT prazo 
                        FROM `prazos_revendas_esps` 
                        WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                        AND `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' 
                        AND `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
                $campos_prazo_entrega = bancos::sql($sql);
//Se encontrar algum Prazo de Entrega p/ esta condi��o ...
                if(count($campos_prazo_entrega) == 1) {
                    $prazo_entrega_tecnico = $campos_prazo_entrega[0]['prazo'];
                    if($prazo_entrega_tecnico == 0) {
                        echo 'IMEDIATO';
                    }else {
                        echo $prazo_entrega_tecnico;
                    }
                }else {//Se n�o encontrar ...
                    echo '<font color="red"><b>SEM PRAZO</b></font>';
                }
            }else {
                if($campos[$i]['prazo_entrega_tecnico'] == '0.0') {
                    echo '<font color="red"><b>SEM PRAZO</b></font>';
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' � do Tipo Float, foi feito
esse esquema para n�o dar problema na hora de Atualizar o Custo*/
                }else if((int)$campos[$i]['prazo_entrega_tecnico'] == 0) {
                    echo 'IMEDIATO';
                }else {
                    echo (int)$campos[$i]['prazo_entrega_tecnico'];
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['prazo_entrega'] == 0) {
                echo 'IMEDIATO';
            }else {
                echo $campos[$i]['prazo_entrega'];
            }
        ?>
        </td>
        <td align='left' style='cursor:help'>
        <?
/**********************Telefone Fone**********************/
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    $title = "Tel Com: ".$campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     $title = "Tel Com: ".$campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     $title = "Tel Com: ".$campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      $title = "Tel Com: ".$campos[$i]['telcom'];
/**********************Telefone Fax**********************/
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))    $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))     $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))     $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))      $title.= " - Tel Fax: ".$campos[$i]['telfax'];
/********************************************************/
//Controle de Cores
            if($campos[$i]['congelar'] == 'N') {
                $color = 'red';
                $title = 'Or�amento n�o Congelado';
            }else {
                $color = 'blue';
                $title = 'Or�amento Congelado';
            }
//Exibe o link do Follow-UP
            if($exibir_link_follow_up == 1) {
?>
                <a href="javascript:nova_janela('../cliente/follow_up.php?identificacao=<?=$campos[$i]['id_cliente'];?>&origem=8', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
<?
                $title.= " - Registrar Follow-UP";
            }
?>
            <font color="<?=$color;?>" title="<?=$title;?>">
<?
            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
?>
            </font>
<?
/********************************************************/
        ?>
        </td>
        <td>
        <?
            //Busca o respons�vel que fez esse Or�amento ...
            $sql = "SELECT l.login 
                    FROM `logins` l 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').'<br>'.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
        <?
            if($campos[$i]['status'] == 0) {//Pend�ncia Total
                echo '<b>PEND�NCIA TOTAL</b>';
            }else if($campos[$i]['status'] == 1) {//Pend�ncia Parcial
                echo '<b>PEND�NCIA PARCIAL</b>';
            }else if($campos[$i]['status'] == 2) {//Item Conclu�do
                echo '<b>ITEM CONCLU�DO</b>';
            }
        ?>
        </td>
    </tr>
<?
/*Vou utilizar essa vari�vel um pouco + abaixo p/ calcular a m�dia ponderada q vai auxiliar
o Rodrigo p/ a qtde do lote*/
            $qtde_total_itens+= $campos[$i]['qtde'];
        }
?>
    <tr class='linhacabecalho'> 
        <td colspan='9'> 
            <font color='yellow'>
                M�dia de Qtde(s) => 
            </font>
            <?=segurancas::number_format(($qtde_total_itens / $linhas), 2, '.');?>
        </td>
    </tr>
    <tr align='center'> 
        <td colspan='9'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>