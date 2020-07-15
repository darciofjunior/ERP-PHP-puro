<?
require('../../../lib/segurancas.php');
require('../../../lib/faturamentos.php');
require('../../../lib/data.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if($representante == '') $representante = '%';
$retorno    = pdt::funcao_geral_follow_ups($dias, $representante, $inicio, $pagina, $paginacao = 'sim');
$campos     = $retorno['campos'];
$linhas_follow_ups = count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) Follow-UP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas_follow_ups == 0) {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) FOLLOW-UP(S) PENDENTE(S) NESTA CONDIÇÃO
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Detalhe(s) Follow-UP(s)
            <?
                if(!empty($dias)) echo ' nos últimos <font color="yellow">'.$dias.'</font> dias';
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            <font title='Representante' style='cursor:help'>
                R
            </font>
        </td>
        <td>
            N.º
        </td>
        <td>
            Login
        </td>
        <td>
            Ocorrência
        </td>
        <td>
            Contato
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas_follow_ups; $i++) {
//Aqui eu trago dados do Follow-UP do Loop ...
            $sql = "SELECT * 
                    FROM `follow_ups` 
                    WHERE `id_follow_up` = '".$campos[$i]['id_follow_up']."' LIMIT 1 ";
            $campos_follow_up = bancos::sql($sql);
            if($campos_folow_up[0]['id_cliente'] > 0) {
/*Aqui eu sempre passo o parâmetro como "id_clientes", porque essa tela as vezes é acessada 
por vários clientes ao mesmo tempo ...*/
                $url = "../apv/informacoes_apv.php?id_clientes=".$campos_folow_up[0]['id_cliente'].'&pop_up=1';
            }
?>
    <tr class='linhanormal' align='center'>
        <td title='Acesso ao APV' style='cursor:help' width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
        <?
            if($campos_follow_up[0]['id_cliente'] > 0) {
                //Aqui busco o Cliente na Tabela Relacional ...
                $sql = "SELECT IF(`nomefantasia` = '', `razaosocial`, `nomefantasia`) AS cliente 
                        FROM `clientes` 
                        WHERE `id_cliente` = '".$campos_follow_up[0]['id_cliente']."' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
        ?>
            <a href="<?=$url;?>" title='Acesso ao APV' style='cursor:help' class='link'>
                <?=$campos_cliente[0]['cliente'];?>
            </a>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($campos_follow_up[0]['id_representante'] > 0) {
                //Aqui busco o Representante na Tabela Relacional ...
                $sql = "SELECT `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos_follow_up[0]['id_representante']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo "<font title='".$campos_representante[0]['nome_fantasia']."' style='cursor:help'>?</font>";
            }
        ?>
        </td>
        <?
            if($campos_follow_up[0]['origem'] == 3) {//Tela de Gerenciar Estoque
?>
        <td><?//='Cliente';?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 4) {//Contas à Receber
                $sql = "SELECT `num_conta` 
                        FROM `contas_receberes` 
                        WHERE `id_conta_receber` = '".$campos_follow_up[0]['identificacao']."' LIMIT 1 ";
                $campos_conta_receber = bancos::sql($sql);
?>
        <td><?=$campos_conta_receber[0]['num_conta'];?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 5) {//Nota Fiscal
?>
        <td><?=faturamentos::buscar_numero_nf($campos_follow_up[0]['identificacao'], 'S');?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 6) {//APV
//Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
                if($campos_follow_up[0]['modo_venda'] == 1) {
?>
        <td>FONE</td>
<?
                }else {
?>
        <td>VISITA</td>
<?
                }
            }else if($campos_follow_up[0]['origem'] == 7) {//Atend. Interno
?>
        <td><?//='Atend. Interno:';?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 8) {//Depto. Técnico
?>
        <td><?//='Depto. Técnico:';?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 9) {//Pendências
?>
        <td><?//='Pendências:';?></td>
<?
            }else if($campos_follow_up[0]['origem'] == 10) {//TeleMarketing
?>
        <td><?//='TeleMkt:';?></td>
<?
//Quando for 1) Orçamento ou 2) Pedido, por coincidência é o próprio id
            }else {
?>
        <td><?=$campos_follow_up[0]['identificacao'];?></td>
<?
            }
            //Aqui busco o Login na Tabela Relacional ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_funcionario` = ".$campos_follow_up[0]['id_funcionario']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
?>
        <td>
            <?=$campos_login[0]['login'];?>
        </td>
        <td>
            <?=data::datetodata($campos_follow_up[0]['data_sys'], '/').' - '.substr($campos_follow_up[0]['data_sys'], 11, 8);?>
        </td>
<?
            if($campos_follow_up[0]['id_cliente_contato'] > 0) {
                //Aqui busca o Contato na Tabela Relacional ...
                $sql = "SELECT `nome` 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = ".$campos_follow_up[0]['id_cliente_contato']." LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
                $contato        = (count($campos_contato[0]['nome']) == 1) ? $campos_contato[0]['nome'] : '';
            }
?>
        <td align='left'>
            <?=$contato;?>
        </td>
        <td align='left'>
            <?=$campos_follow_up[0]['observacao'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>