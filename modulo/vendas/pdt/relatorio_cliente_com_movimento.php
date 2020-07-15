<?
require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up                 = $_POST['pop_up'];
    $representante          = $_POST['representante'];
    $cmb_representante      = $_POST['cmb_representante'];
    $cmb_novo_tipo_cliente  = $_POST['cmb_novo_tipo_cliente'];
    $cmb_dias               = $_POST['cmb_dias'];
}else {
    $pop_up                 = $_GET['pop_up'];
    $representante          = $_GET['representante'];
    $cmb_representante      = $_GET['cmb_representante'];
    $cmb_novo_tipo_cliente  = $_GET['cmb_novo_tipo_cliente'];
    $cmb_dias               = $_GET['cmb_dias'];
}

$ano_atual      = date('Y');
$ano_anterior   = date('Y') - 1;
?>
<html>
<head>
<title>.:: Relatório de Clientes com Movimento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(typeof(document.form.cmb_representante) == 'object') {
        if(document.form.cmb_representante.value == '') {
            alert('SELECIONE UM REPRESENTANTE !')
            document.form.cmb_representante.focus()
            return false
        }
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='POST' action='' onsubmit="return validar()">
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Relatório de Clientes com Movimento <br/>
            <font color='yellow'>
                <b>Vendedor: </b>
            </font>
<?
//Verifico se o Vendedor foi passado por Parâmetro ...
    if(!empty($representante)) {
        $sql = "SELECT `nome_fantasia` 
                FROM `representantes` 
                WHERE `id_representante` = '$representante' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        echo $campos_representante[0]['nome_fantasia'];
?>
            <input type='hidden' name='representante' value='<?=$representante;?>'>
<?
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
    }else {
?>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
<?
                $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql, $cmb_representante);
?>
            </select>
<?
    }
?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                <b>Novo Tipo de Cliente: </b>
            </font>
            <select name='cmb_novo_tipo_cliente' title='Selecione o Novo Tipo de Cliente' class='combo'>
            <?
                $sql = "SELECT `id_cliente_tipo`, `tipo` 
                        FROM `clientes_tipos` ";
                echo combos::combo($sql, $cmb_novo_tipo_cliente);
            ?>
            </select>
            &nbsp;-&nbsp;
            <font color='yellow'>
                <b>Últimos: </b>
            </font>
            <select name="cmb_dias" title="Selecione a Qtde de Dias" class='combo'>
                <?
                    if(empty($cmb_dias) || $cmb_dias == 30) {
                        $selected30 = 'selected';
                    }else if($cmb_dias == 60) {
                        $selected60 = 'selected';
                    }else if($cmb_dias == 90) {
                        $selected90 = 'selected';
                    }else if($cmb_dias == 180) {
                        $selected180 = 'selected';
                    }else if($cmb_dias == 270) {
                        $selected270 = 'selected';
                    }else if($cmb_dias == 365) {
                        $selected365 = 'selected';
                    }else if($cmb_dias == 730) {
                        $selected730 = 'selected';
                    }else if($cmb_dias == 1460) {
                        $selected1460 = 'selected';
                    }
                ?>
                <option value='30' <?=$selected30;?>>30 dias</option>
                <option value='60' <?=$selected60;?>>60 dias</option>
                <option value='90' <?=$selected90;?>>90 dias</option>
                <option value='180' <?=$selected180;?>>180 dias</option>
                <option value='270' <?=$selected270;?>>270 dias</option>
                <option value='365' <?=$selected365;?>>365 dias (1 ano)</option>
                <option value='730' <?=$selected730;?>>730 dias (2 anos)</option>
                <option value='1460' <?=$selected1460;?>>1460 dias (4 anos)</option>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
            </font>
            &nbsp;
            <font color='darkblue' size='-1'>
                FATURAMENTO
            </font>
        </td>
    </tr>
<?
    $data_atual = date('Y-m-d');
//Se foi passado um parâmetro de Representante ou foi selecionado algum na Combo, então eu realizo o SQL abaixo ...
    if(!empty($representante) || !empty($cmb_representante)) {
        $valor_dolar_dia                    = genericas::moeda_dia('dolar');
        $condicao_representante             = (!empty($representante)) ? " id_representante LIKE '$representante' " : " id_representante LIKE '$cmb_representante' ";
        if(!empty($cmb_novo_tipo_cliente))  $condicao_novo_tipo_cliente = " AND c.id_cliente_tipo = '$cmb_novo_tipo_cliente' ";
        if(empty($cmb_dias)) $cmb_dias      = 30;//Caso a combo esteja vazia, então eu sugiro 30 dias que é o Menor Período ...

        /************************************************************************/
        //1) Busco todos os Clientes do Representante ...
        $sql = "SELECT DISTINCT(id_cliente) 
                FROM `clientes_vs_representantes` 
                WHERE $condicao_representante ";
        $campos_clientes = bancos::sql($sql);
        $linhas_clientes = count($campos_clientes);
        if($linhas_clientes > 0) {
            for($i = 0; $i < $linhas_clientes; $i++) $id_clientes[] = $campos_clientes[$i]['id_cliente'];
        }else {
            $id_clientes[] = 0;
        }
        /************************************************************************/

        //2) Busco o Valor Total Faturado "Cheio" de cada Cliente encontrado acima nos últimos 3 anos / agrupo os valores por ano ...
        $sql = "SELECT nfs.`id_cliente`, SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS total, 
                YEAR(nfs.`data_emissao`) AS ano 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                WHERE nfs.`id_cliente` IN (".implode(',', $id_clientes).") 
                AND YEAR(nfs.`data_emissao`) >= '".(date('Y') - 3)."' 
                GROUP BY nfs.`id_cliente`, YEAR(nfs.`data_emissao`) ORDER BY YEAR(nfs.`data_emissao`) ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $vetor_faturamento[$campos[$i]['ano']][$campos[$i]['id_cliente']]+= $campos[$i]['total'];

        /*3) Busco todos os Clientes do Representante que foram encontrados no SQL anterior e que tiveram Faturamento nos 
        últimos X dias selecionados pelo usuário ...*/
        $sql = "SELECT DISTINCT(c.id_cliente), c.id_pais, c.id_uf, c.id_cliente_tipo, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) as cliente, c.cidade, c.credito, ct.tipo 
                FROM `clientes` c 
                INNER JOIN `clientes_tipos` ct ON ct.id_cliente_tipo = c.id_cliente_tipo 
                INNER JOIN `nfs` ON nfs.id_cliente = c.id_cliente AND (nfs.data_emissao >= DATE_ADD('$data_atual', INTERVAL -$cmb_dias DAY)) 
                WHERE c.ativo = '1' 
                AND c.`id_cliente` IN (".implode(',', $id_clientes).") 
                $condicao_novo_tipo_cliente GROUP BY nfs.id_cliente ORDER BY c.razaosocial ";
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            <font title='Tipo de Cliente' style='cursor:help'>
                TC
            </font>
        </td>
        <td>
            UF
        </td>
        <td>
            <font title='Crédito' style='cursor:help'>
                Cr
            </font>
        </td>	
        <td>
            <?=(date('Y') - 3);?> R$
        </td>
        <td>
            <?=(date('Y') - 2);?> R$
        </td>
        <td>
            <?=(date('Y') - 1);?> R$
        </td>
        <td>
            <?=date('Y');?> R$
        </td>
    </tr>
<?
//Esse vetor será utilizado mais abaixo ...
        $vetor_ufs = array('', 'SP', 'RJ', 'MG', 'ES', 'PR', 'RS', 'SC', 'MS', 'DF', 'GO', 'A2', 'A3', 'A4', 'AL', 'BA', 'CE', 'A1', 'MA', 'RN', 'SE', 'PE', 'PI', 'PB', 'AC', 'AM', 'AP', 'RR', 'RO', 'TO', 'PA');
        $vetor_tipos = array('', 'RA', 'RI', 'C', 'I', 'A', 'D', 'I', 'F', 'UC', 'NC', 'TM', 'TMI');
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href="javascript:nova_janela('../apv/informacoes_apv.php?id_clientes=<?=$campos[$i]['id_cliente'];?>&pop_up=1', 'RELATORIO_CLIENTES', '', '', '', '', 550, 975, 'c', 'c', '', '', 's', 's', '', '', '')" title='APV do Cliente' style='cursor:help' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?='<font title="'.$campos[$i]['tipo'].'" style="cursor:help">'.$vetor_tipos[$campos[$i]['id_cliente_tipo']].'</font>';?>
        </td>
        <td>
            <?=$vetor_ufs[$campos[$i]['id_uf']];?>
        </td>
        <td>
        <?
//Tratamento com as cores de Crédito
            if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {//Se for D, Cliente caloteiro, vermelho ...
                $font = "<font color='red'>";
            }else {//Se não está ok, azul ...
                $font = "<font color='blue'>";
            }
            echo $font.$campos[$i]['credito'];
        ?>
        </td>
        <td>
        <?
            $total_parcial_ano_atual_menos3 = $vetor_faturamento[(date('Y') - 3)][$campos[$i]['id_cliente']];
            echo segurancas::number_format($total_parcial_ano_atual_menos3, 2, '.');
        ?>
        </td>
        <td>
        <?
            $total_parcial_ano_atual_menos2 = $vetor_faturamento[(date('Y') - 2)][$campos[$i]['id_cliente']];
            echo segurancas::number_format($total_parcial_ano_atual_menos2, 2, '.');
        ?>
        </td>
        <td>
        <?
            $total_parcial_ano_atual_menos1 = $vetor_faturamento[(date('Y') - 1)][$campos[$i]['id_cliente']];
            echo segurancas::number_format($total_parcial_ano_atual_menos1, 2, '.');
        ?>
        </td>
        <td>
        <?
            $total_parcial_ano_atual = $vetor_faturamento[date('Y')][$campos[$i]['id_cliente']];
            echo segurancas::number_format($total_parcial_ano_atual, 2, '.');
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
//Se não foi passado nenhum representante por parâmetro ...
    }else {
?>
    <tr></tr>
    <tr class='atencao' align='center'>
        <td colspan='5'>
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