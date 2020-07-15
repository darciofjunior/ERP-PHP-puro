<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../classes/array_sistema/array_sistema.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

/*Usuários que terão acesso a visualização do Representante do Pedido ...
Funcionários: Rivaldo 27, Roberto 62, Wilson Chefe 68, Dárcio 98 porque programa e Wilson Japonês 136*/
$vetor_usuarios_com_acesso = array('27', '62', '68', '98', '136');
$usuario_com_acesso = 0;

for($i = 0; $i < count($vetor_usuarios_com_acesso); $i++) {
//Se o usuário logado for um dos designados acima, então este terá acesso ao combo ...
    if($vetor_usuarios_com_acesso[$i] == $_SESSION['id_funcionario']) $usuario_com_acesso = 1;
}

if($representante == '') $representante = '%';
$retorno        = pdt::funcao_geral_pedidos($tipo_retorno, $dias, $representante, $inicio, $pagina, $paginacao = 'sim');
$campos         = $retorno['campos'];
$linhas_pedidos = count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_pedido_venda) {
    parent.document.location = '../pedidos/itens/index.php?id_pedido_venda='+id_pedido_venda
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas_pedidos == 0) {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) PEDIDO(S) PENDENTE(S) NESTA CONDIÇÃO
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Detalhe(s) Pedido(s)
            <?
                if(!empty($dias)) echo ' nos últimos <font color="yellow">'.$dias.'</font> dias';
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Pedido
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Faturar Em
        </td>
        <td>
            <font title='Condição de Faturamento' style='cursor:help'>
                Condição de<br/>Faturamento
            </font>
        </td>
        <td>
            Vale
        </td>
        <td>
            Cliente / Representante
        </td>
        <td>
            Contato
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento'>
                Emp / Tp Nota<br> / Prazo Pgto
            </font>
        </td>
        <td>
            Valor do Ped.
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas_pedidos; $i++) {
            $url = "javascript:avancar('".$campos[$i]['id_pedido_venda']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                echo data::datetodata($campos[$i]['faturar_em'], '/');
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {
                echo '<font color="red">CRÉDITO '.$campos[$i]['credito'].'</font>';
            }else {
                $condicao_faturamento = array_sistema::condicao_faturamento();
                echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
            }
        ?>
        </td>
        <td>
        <?
//Aqui eu verifico se existe pelo menos 1 item desse que Pedido que contém Vale ...
            $sql = "SELECT `id_pedido_venda_item` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' 
                    AND `vale` > '0' LIMIT 1 ";
            $campos_vale = bancos::sql($sql);
            if(count($campos_vale) == 1) echo '<font color="blue"><b>SIM</b></font>';
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
/***************************************************************************************/
//Essa variável foi tratada acima ...
            if($usuario_com_acesso == 1) {
                //Busca do nome do Representante ...
                $sql = "SELECT `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo ' <b>('.$campos_representante[0]['nome_fantasia'].')</b>';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }

            if($campos[$i]['id_empresa']==1) {
                $nomefantasia = 'ALBA - NF';

                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==2) {
                $nomefantasia = 'TOOL - NF';

                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==4) {
                $nomefantasia = 'GRUPO - SGD';

                echo '(G - SGD) / '.$prazo_faturamento;
            }else {
                echo 'Erro';
            }
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
                $prazo_faturamento = '';
        ?>
        </td>
        <td align='right'>
            <?=$campos[$i]['valor_ped'];?>
        </td>
    </tr>
<?
            $total_peds+= $campos[$i]['valor_ped']; 
        }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='9'>
            Total Ped(s) =>
        </td>
        <td align='right'>
            <?=number_format($total_peds, 2, ',', '.');?>
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