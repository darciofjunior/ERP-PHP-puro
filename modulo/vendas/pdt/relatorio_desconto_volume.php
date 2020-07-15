<?
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
/********************************************************************************************************/
/***********Aqui eu ignoro o Desconto do Scan ERP - automático, e faço com que o sistema passe a 
assumir o Desconto Manual preenchido de forma Manual pelo Nishimura ***/
/********************************************************************************************************/
if(!empty($_POST['cmb_desconto_desejado'])) {
    foreach($_POST['cmb_desconto_desejado'] as $i => $desconto_desejado) {
        if($desconto_desejado != '') {
            $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente_old` = `desconto_cliente`, `desconto_cliente` = '$desconto_desejado' WHERE `id_cliente` = '".$_POST['hdd_cliente'][$i]."' ";
            bancos::sql($sql);
        }
    }
}
/********************************************************************************************************/
?>
<html>
<head>
<title>.:: Relatório de Desc vs Volume ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.cmb_representante.value == '') {
        alert('SELECIONE UM REPRESENTANTE !')
        document.form.cmb_representante.focus()
        return false
    }else {
        document.form.submit()
    }
}

function calcular_desconto_desejado(volume_compras_do_cliente, indice) {
    var desconto_cliente 	= new Array()
    var valor_semestral 	= new Array()
<?
    /********************************************************************************************/
    //Aqui eu trago todos os descontos do Cliente do Grupo (Semestral) para armazenar no array ...
    $sql = "SELECT desconto_cliente, valor_semestral 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '0' ";
    $campos_descontos = bancos::sql($sql);
    $linhas_descontos = count($campos_descontos);
    for($i = 0; $i < $linhas_descontos; $i++) {
?>
        desconto_cliente['<?=$i;?>'] 	= '<?=intval($campos_descontos[$i]['desconto_cliente']);?>'
        valor_semestral['<?=$i;?>'] 	= '<?=intval($campos_descontos[$i]['valor_semestral']);?>'
<?
    }
    /********************************************************************************************/
?>
    //Aqui eu só leio os arrays em JavaScript ...
    for(i = 0; i < desconto_cliente.length; i++) {
        if(document.getElementById('cmb_desconto_desejado'+indice).value == desconto_cliente[i]) {
            var valor_minimo = valor_semestral[i]
            break
        }
    }
    document.getElementById('txt_valor_calculado'+indice).value = (valor_minimo - volume_compras_do_cliente < 0) ? 0 : (valor_minimo - volume_compras_do_cliente)
    document.getElementById('txt_valor_calculado'+indice).value = arred(document.getElementById('txt_valor_calculado'+indice).value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <font color='yellow'>
                <b>Vendedor: </b>
            </font>
        <?
//Verifico se o Vendedor foi passado por Parâmetro ...
                if(!empty($representante)) {
                    $sql = "SELECT nome_fantasia 
                            FROM `representantes` 
                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    echo $campos_representante[0]['nome_fantasia'];
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
                }else {
        ?>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
        <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
        ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        <?
                }
        ?>
        </td>
    </tr>
<?
/*Se foi passado um parâmetro de Representante ou foi selecionado algum na Combo, então 
eu realizo o SQL abaixo ...*/
    if(!empty($representante) || !empty($cmb_representante)) {
        if(!empty($representante)) {
            $condicao_representante = " AND nfsi.`id_representante` LIKE '$representante' ";
        }else {
            $condicao_representante = " AND nfsi.`id_representante` LIKE '$cmb_representante' ";
        }
        
        /***************************************************************************************************/
        //Busco todas as NFs agrupando por Ano dentro dos últimos 5 anos, que serão apresentados mais abaixo ...
        $sql = "SELECT nfs.`id_cliente`, SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS total_nfs_emitidas_ano, 
                YEAR(nfs.`data_emissao`) AS ano_corrente 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                WHERE YEAR(nfs.`data_emissao`) >= '".(date('Y') - 4)."' 
                GROUP BY nfs.`id_cliente`, YEAR(nfs.`data_emissao`) ORDER BY nfs.`id_cliente` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            $total_emitido_por_cliente_no_ano[$campos[$i]['ano_corrente']][$campos[$i]['id_cliente']] = $campos[$i]['total_nfs_emitidas_ano'];
            $total_emitido_por_ano[$campos[$i]['ano_corrente']]+= $campos[$i]['total_nfs_emitidas_ano'];
        }
        /***************************************************************************************************/
        //Busco a Carteira de Cliente do Representante que foi selecionado ou passado por parâmetro ...
        $sql = "SELECT DISTINCT(cr.`id_cliente`) AS id_cliente, 
                IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
                c.`cidade`, c.`credito`, ufs.`sigla` 
                FROM `clientes_vs_representantes` cr 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND c.ativo = '1' 
                LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                WHERE cr.`id_representante` = '$cmb_representante' ORDER BY cliente ";
        
        $sql_extra = "SELECT DISTINCT(cr.`id_cliente`) AS total_registro 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND c.ativo = '1' 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE cr.`id_representante` = '$cmb_representante' ";
        $campos_clientes = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
        $linhas_clientes = count($campos_clientes);
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Relatório de Desconto vs Volume
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2'>
            Cidade
        </td>
        <td rowspan='2'>
            UF
        </td>
        <td rowspan='2'>
            <font title='Crédito' style='cursor:help'>
                Cr
            </font>
        </td>
        <td colspan='5'>
            Compras em R$
        </td>
        <td colspan='3'>
            Desconto
        </td>
        <td rowspan='2'>
            Desconto Desejado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <?=(date('Y') - 4);?>
        </td>
        <td>
            <?=(date('Y') - 3);?>
        </td>
        <td>
            <?=(date('Y') - 2);?>
        </td>
        <td>
            <?=(date('Y') - 1);?>
        </td>
        <td>
            <?=date('Y');?>
        </td>
        <td>
            Anterior
        </td>
        <td>
            Atual
        </td>
        <td>
            Futuro
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_clientes; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href = '../sac/atendimento_cliente/relatorio.php?id_clientes=<?=$campos_clientes[$i]['id_cliente'];?>' title='APV do Cliente' style='cursor:help' class='link'>
                <?=$campos_clientes[$i]['cliente'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos_clientes[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos_clientes[$i]['sigla'];?>
        </td>
        <td>
        <?
//Tratamento com as cores de Crédito
            if($campos_clientes[$i]['credito'] == 'D') {//Se for D, Cliente caloteiro, vermelho ...
                $font = "<font color='red'>";
            }else {//Se não está ok, azul ...
                $font = "<font color='blue'>";
            }
            echo $font.$campos_clientes[$i]['credito'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($total_emitido_por_cliente_no_ano[(date('Y') - 4)][$campos_clientes[$i]['id_cliente']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_emitido_por_cliente_no_ano[(date('Y') - 3)][$campos_clientes[$i]['id_cliente']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_emitido_por_cliente_no_ano[(date('Y') - 2)][$campos_clientes[$i]['id_cliente']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_emitido_por_cliente_no_ano[(date('Y') - 1)][$campos_clientes[$i]['id_cliente']], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_emitido_por_cliente_no_ano[date('Y')][$campos_clientes[$i]['id_cliente']], 2, ',', '.');?>
        </td>
        <?
            //Busca o Maior Valor dos Descontos Antigos Novos do Cliente e do Representante ...
            $sql = "SELECT MAX(cr.desconto_cliente_old) AS desconto_cliente_old, MAX(cr.desconto_cliente) AS `desconto_cliente` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
                    WHERE cr.`id_cliente` = '".$campos_clientes[$i]['id_cliente']."' LIMIT 1 ";
            $campos_descontos_cliente = bancos::sql($sql);

/*Aqui eu verifico em qual Faixa de Desconto que o Cliente irá se adequar de acordo com o seu Volume de Compras do Ano Atual - 
Busca feito por Grupo ...*/
            $sql = "SELECT desconto_cliente 
                    FROM `descontos_clientes` 
                    WHERE `tabela_analise` = '0' 
                    AND `valor_semestral` >= '".round($volume_compras_ano_atual[$campos_clientes[$i]['id_cliente']], 2)."' LIMIT 1 ";
            $campos_descontos_futuro = bancos::sql($sql);

//Aqui eu verifico se o Futuro desconto do Cliente é menor de que seu Desconto Atual ...
            if($campos_descontos_futuro[0]['desconto_cliente'] < $campos_descontos_cliente[0]['desconto_cliente']) {
                $sql = "SELECT desconto_cliente 
                        FROM `descontos_clientes` 
                        WHERE `tabela_analise` = '0' 
                        AND `desconto_cliente` = '".$campos_descontos_futuro[0]['desconto_cliente']."' ORDER BY valor_semestral LIMIT 1 ";
                $campos_desconto_categoria_manter = bancos::sql($sql);
            }else {
/*Se o Desconto Futuro é menor do que 20% "Máximo", verifico a Diferença em R$ que p/ saber o qto que o Vendedor necessita pra subir uma 
Categoria acima em comparado com a Atual que o mesmo se encontra.

Obs: No SQL abaixo eu trago LIMIT 2, porque para subir o desconto do Cliente, estamos sempre nos baseando no valor cheio de Venda ...

Exemplo: R$ 1001 à R$ 5000 o Cliente terá os mesmos 5%, então com o valor cheio, forçamos aí para ganhar algo mais "lembrando que 
isso tudo é por semestre" ...
...*/
                if($campos_descontos_futuro[0]['desconto_cliente'] < 20) {
                    $sql = "SELECT desconto_cliente 
                            FROM `descontos_clientes` 
                            WHERE `tabela_analise` = '0' 
                            AND `valor_semestral` >= '".round($volume_compras_ano_atual[$campos_clientes[$i]['id_cliente']], 2)."' LIMIT 2 ";
                    $campos_desconto_categoria_acima = bancos::sql($sql);
                }
            }
        ?>
        <td>
            <?=number_format($campos_descontos_cliente[0]['desconto_cliente_old'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_descontos_cliente[0]['desconto_cliente'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_descontos_futuro[0]['desconto_cliente'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <select name='cmb_desconto_desejado[]' id='cmb_desconto_desejado<?=$i;?>' title='Selecione o Desconto Desejado' onchange="calcular_desconto_desejado('<?=$volume_compras_ano_atual[$campos_clientes[$i]['id_cliente']];?>', '<?=$i;?>')" class='combo'>
                <option value='' style='color:red'>-</option>
                <option value='0'>0</option>
                <option value='5'>5</option>
                <option value='10'>10</option>
                <option value='15'>15</option>
                <option value='20'>20</option>
            </select>
            <br>
            R$ <input type='text' id='txt_valor_calculado<?=$i;?>' size='12' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_cliente[]' value='<?=$campos_clientes[$i]['id_cliente'];?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'">
            <?
                //Esse botão só é exibido para Roberto, Dárcio e Nishimura
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }else {//Se não foi passado nenhum representante por parâmetro ...
?>
    <tr></tr>
    <tr align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>