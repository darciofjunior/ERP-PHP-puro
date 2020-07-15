<?
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up             = $_POST['pop_up'];
    $representante      = $_POST['representante'];
    $cmb_representante 	= $_POST['cmb_representante'];
    $txt_data_inicial 	= $_POST['txt_data_inicial'];
    $txt_data_final 	= $_POST['txt_data_final'];
    $cmb_familia        = $_POST['cmb_familia'];
    $cmb_grupo_pa       = $_POST['cmb_grupo_pa'];
    $txt_referencia 	= $_POST['txt_referencia'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $pop_up             = $_GET['pop_up'];
    $representante      = $_GET['representante'];
    $cmb_representante 	= $_GET['cmb_representante'];
    $txt_data_inicial 	= $_GET['txt_data_inicial'];
    $txt_data_final 	= $_GET['txt_data_final'];
    $cmb_familia        = $_GET['cmb_familia'];
    $cmb_grupo_pa       = $_GET['cmb_grupo_pa'];
    $txt_referencia 	= $_GET['txt_referencia'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Estoque vs Compra Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    /*if(typeof(document.form.cmb_representante) == 'object') {
        if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
            return false
        }
    }*/
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 1460) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A QUATRO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Família
    if(!combo('form', 'cmb_familia', '', 'SELECIONE A FAMÍLIA !')) {
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Relatório de Estoque vs Compra Cliente <br>
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
            <input type="hidden" name="representante" value="<?=$representante;?>">
    <?
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
            }else {
    ?>
                <select name="cmb_representante" title="Selecione o Representante" class='combo'>
    <?
            $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                    FROM `representantes` 
                    WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                    echo combos::combo($sql, $cmb_representante);
    ?>
                </select>
    <?
            }
    ?>
                    Data Inicial: 
    <?
            if(empty($txt_data_inicial)) {
                $datas = genericas::retornar_data_relatorio();
                $txt_data_inicial = data::adicionar_data_hora($datas['data_final'], -365);//Período de 1 ano
                $txt_data_final = $datas['data_final'];
            }
        ?>
        <input type='text' name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
        &nbsp; <img src = "../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
        <input type='text' name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
        &nbsp; <img src = "../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        &nbsp;&nbsp;&nbsp;<br>
        Família: 
        <select name='cmb_familia' title='Selecione a Família' onchange='return validar()' class='combo'>
        <?
            $sql = "SELECT id_familia, nome 
                    FROM `familias` 
                    WHERE `ativo` = '1' ORDER BY nome ";
            echo combos::combo($sql, $cmb_familia);
        ?>	
        </select>
<?
        if(!empty($cmb_familia)) {
?>	
        Divisões:	
        <select name='cmb_divisoes' title='Selecione a Divisão' class='combo'>	
        <?
            $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                    FROM `empresas_divisoes` 
                    WHERE `ativo` = '1' ORDER BY razaosocial ";
            echo combos::combo($sql, $cmb_divisoes);
        ?>
        </select>
        Grupo:	
        <select name='cmb_grupo_pa' title='Selecione o Grupo do PA' class='combo'>
        <?
            $sql = "SELECT `id_grupo_pa`, `nome` 
                    FROM `grupos_pas` 
                    WHERE `ativo` = '1' 
                    AND `id_familia` = '$cmb_familia' ORDER BY nome ";
            echo combos::combo($sql, $cmb_grupo_pa);
        ?>	
        </select>
        &nbsp;
        Referência: 
        <input type='text' name='txt_referencia' value='<?=$txt_referencia;?>' size='12' maxlength='10' class='caixadetexto'>
<?
        }
?>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
    $data_atual = date('Y-m-d');
    if(!empty($cmd_consultar)) {//Se foi submetido executa o SQL ...
//Combo de Clientes ...
        $condicao_representante = "AND nfsi.`id_representante` ";
        $condicao_representante.= (!empty($representante)) ? " LIKE '$representante' " : " LIKE '$cmb_representante' ";
        $condicao_representante = str_replace("''", "'%'", $condicao_representante);
//Campos de Data ...
        $data_inicial 	= data::datatodate($txt_data_inicial, '-');
        $data_final 	= data::datatodate($txt_data_final, '-');
//Combo de Grupo PA ...
        if(!empty($cmb_grupo_pa))   $condicao_grupo_pa = " AND gpa.`id_grupo_pa` = '$cmb_grupo_pa' ";
        if(empty($cmb_divisoes))    $cmb_divisoes = '%';

//Busca o Total de Faturamento num determinado período por Representante ...
        //A tabela de Orcs não é usada para nada nas Querys abaixo, mais deixo a mesma porque acaba a Query mais rápida ...
        $sql = "SELECT c.`id_pais`, IF(c.id_pais = '31', SUM((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario), SUM(((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario) * $valor_dolar_dia)) AS total 
                FROM `clientes` c 
                INNER JOIN nfs ON nfs.id_cliente = c.id_cliente AND nfs.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                INNER JOIN nfs_itens nfsi ON nfsi.id_nf = nfs.id_nf $condicao_representante 
                INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = nfsi.id_produto_acabado AND pa.referencia LIKE '%$txt_referencia%' 
                INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.id_empresa_divisao LIKE '$cmb_divisoes' 
                INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia = '$cmb_familia' $condicao_grupo_pa 
                WHERE c.ativo = '1' GROUP BY c.id_pais ";
        $campos_tot = bancos::sql($sql);
        $linhas = count($campos_tot);
        for($i = 0; $i < $linhas; $i++) $total_faturamentos+= $campos_tot[$i]['total'];

        $sql = "SELECT DISTINCT(c.id_cliente), c.id_pais, c.id_uf, c.id_cliente_tipo, if(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.credito, c.cidade, c.`email`, ct.tipo, 
                IF(c.id_pais = '31', SUM((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario), SUM(((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario) * $valor_dolar_dia)) total, 
                r.nome_fantasia 
                FROM clientes c 
                INNER JOIN clientes_tipos ct ON ct.id_cliente_tipo = c.id_cliente_tipo 
                INNER JOIN nfs ON nfs.id_cliente = c.id_cliente AND nfs.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                INNER JOIN nfs_itens nfsi ON nfsi.id_nf = nfs.id_nf $condicao_representante 
                INNER JOIN `representantes` r ON r.id_representante = nfsi.id_representante 
                INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = nfsi.id_produto_acabado and pa.referencia LIKE '%$txt_referencia%' 
                INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.id_empresa_divisao LIKE '$cmb_divisoes' 
                INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia = '$cmb_familia' $condicao_grupo_pa 
                WHERE c.ativo = 1 $condicao_novo_tipo_cliente 
                GROUP BY c.id_cliente ORDER BY total DESC ";

        $sql_extra = "SELECT COUNT(DISTINCT(c.id_cliente)) AS total_registro 
                    FROM clientes c 
                    INNER JOIN clientes_tipos ct ON ct.id_cliente_tipo = c.id_cliente_tipo 
                    INNER JOIN nfs ON nfs.id_cliente = c.id_cliente AND nfs.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                    INNER JOIN nfs_itens nfsi ON nfsi.id_nf = nfs.id_nf $condicao_representante 
                    INNER JOIN `representantes` r ON r.id_representante = nfsi.id_representante 
                    INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = nfsi.id_produto_acabado and pa.referencia LIKE '%$txt_referencia%' 
                    INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.id_empresa_divisao LIKE '$cmb_divisoes' 
                    INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_familia = '$cmb_familia' $condicao_grupo_pa 
                    WHERE c.ativo = 1 $condicao_novo_tipo_cliente 
                    GROUP BY c.id_cliente ORDER BY c.razaosocial ";
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2' title='Representante' style='cursor:help'>
            Repres.
        </td>
        <td rowspan='2'>
            Cidade
        </td>
        <td rowspan='2'>
            TC
        </td>
        <td rowspan='2'>
            UF
        </td>
        <td rowspan='2'>
            Email
        </td>
        <td rowspan='2'>
            CR
        </td>
        <td rowspan='2'>
            <font title='Total em R$ de Ped. Emitidos no Período Específicado' style='cursor:help'>
                Total R$ P.E.
            </font>
        </td>
        <td rowspan='2'>
            Perc(s)
        </td>
        <td colspan='3'>
            Último Pedido
        </td>
    </tr>
    <tr class='linhadestaque' align='center' colspan='3'>
        <td>
            N.º Ped
        </td>
        <td>
            Data
        </td>
        <td>
            Valor R$
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
            <a href = "../apv/informacoes_apv.php?id_clientes=<?=$campos[$i]['id_cliente'];?>" title='APV do Cliente' style='cursor:help' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome_fantasia'];?>
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
        <td align='left'>
            <?=$campos[$i]['email'];?>
        </td>
        <td>
        <?
//Tratamento com as cores de Crédito
            if($campos[$i]['credito'] == 'D') {//Se for D, Cliente caloteiro, vermelho ...
                $font = "<font color='red'>";
            }else {//Se não está ok, azul ...
                $font = "<font color='blue'>";
            }
            echo $font.$campos[$i]['credito'];
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['total'], 2, '.');?>
        </td>
        <td align='right'>
        <?
            $perc_parcial = ($campos[$i]['total'] / $total_faturamentos) * 100;
            $perc_total+= $perc_parcial;
            echo number_format($perc_parcial, 2, ',', '.');
        ?>
        %
        </td>
        <?
//Busco o último Pedido de Compra do Cliente na determinada Família e Grupo que é selecionada na combo ...
            $sql = "SELECT pv.id_pedido_venda, DATE_FORMAT(pv.data_emissao, '%d/%m/%Y') as data_emissao, IF(c.id_pais = '31', SUM(pvi.qtde * pvi.preco_liq_final), SUM(pvi.qtde * pvi.preco_liq_final * $valor_dolar_dia)) AS total_pedido 
                    FROM pedidos_vendas pv 
                    INNER JOIN clientes c ON c.id_cliente = pv.id_cliente 
                    INNER JOIN pedidos_vendas_itens pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                    INNER JOIN produtos_acabados pa ON pa.id_produto_acabado = pvi.id_produto_acabado AND pa.referencia LIKE '%$txt_referencia%' 
                    INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa and gpa.id_familia = '$cmb_familia' $condicao_grupo_pa 
                    WHERE pv.id_cliente = '".$campos[$i]['id_cliente']."' 
                    GROUP BY id_pedido_venda order by pv.id_pedido_venda DESC LIMIT 1 ";
            $campos_ultimo_pedido = bancos::sql($sql);
        ?>
        <td>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_ultimo_pedido[0]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
                <?=$campos_ultimo_pedido[0]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos_ultimo_pedido[0]['data_emissao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos_ultimo_pedido[0]['total_pedido'], 2, ',', '.');?>
        </td>
    </tr>
<?
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $mostrar = '';
        }
?>
    <tr class='linhanormal'>
        <td colspan='11' align='right'>
            <font color='red' size='2'>
                <b>Total Geral:</b> <?=number_format($total_faturamentos, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format($perc_total, 2, ',', '.');?> %
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='12'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
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
    <tr class='atencao' align='center'>
        <td colspan='12'>
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