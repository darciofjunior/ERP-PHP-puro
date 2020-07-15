<?
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up             = $_POST['pop_up'];
    $representante      = $_POST['representante'];
    $cmb_representante  = $_POST['cmb_representante'];
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmb_credito        = $_POST['cmb_credito'];
}else {
    $pop_up             = $_GET['pop_up'];
    $representante      = $_GET['representante'];
    $cmb_representante  = $_GET['cmb_representante'];
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmb_credito        = $_GET['cmb_credito'];
}

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');

segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
?>
<html>
<head>
<title>.:: Relatório de Faturamento por Linha ::.</title>
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
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 10 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 3700) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DEZ ANO(S) !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='POST' action='' onsubmit="return validar()">
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            Relatório de Faturamento por Linha<br>
            <font color='yellow'>
                <b>Representante: </b>
            </font>
            <?
//Verifico se o Vendedor foi passado por Parâmetro ...
            if(!empty($representante)) {
                    $sql = "SELECT nome_fantasia 
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
            $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                    FROM `representantes` 
                    WHERE `ativo` = '1' ORDER BY nome_fantasia ";
            echo combos::combo($sql, $cmb_representante);
    ?>
                </select>
    <?
            }
    ?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                <b>Empresa Divisão: </b>
            </font>
            
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
    <?
            $sql = "SELECT id_empresa_divisao, razaosocial 
                    FROM `empresas_divisoes` 
                    WHERE `ativo` = '1' ORDER BY razaosocial ";
            echo combos::combo($sql, $cmb_empresa_divisao);
    ?>
            </select>           
            <br/>Data Inicial: 
    <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
            if(empty($txt_data_inicial)) {
                $txt_data_inicial = '01/01/'.(date('Y') - 1);
                $txt_data_final = '31/12/'.(date('Y') - 1);
            }
    ?>
            <input type = 'text' name='txt_data_inicial' value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal"  onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type = 'text' name='txt_data_final' value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;-&nbsp;Crédito
            <select name='cmb_credito' title='Selecione o Credito' onchange='return validar()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($cmb_credito == 'A') {
                        $selecteda = 'selected';
                    }else if($cmb_credito == 'B') {
                        $selectedb = 'selected';
                    }else if($cmb_credito == 'C') {
                        $selectedc = 'selected';
                    }else if($cmb_credito == 'D') {
                        $selectedd = 'selected';
                    }
                ?>
                <option value='A' <?=$selecteda;?>>A</option>
                <option value='B' <?=$selectedb;?>>B</option>
                <option value='C' <?=$selectedc;?>>C</option>
                <option value='D' <?=$selectedd;?>>D</option>
            </select>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
    $data_atual = date('Y-m-d');
    //Se foi realizada alguma consulta ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Bairro
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            <font title='Representante' style='cursor:help'>
                Rep
            </font>
        </td>
        <td>
            Crédito
        </td>
        <td>
            Telefone
        </td>
        <?
            $sql = "SELECT id_empresa_divisao, razaosocial 
                    FROM `empresas_divisoes` 
                    WHERE `ativo` = '1' ORDER BY razaosocial ";
            $campos_empresas_divisoes = bancos::sql($sql);
            $linhas_empresas_divisoes = count($campos_empresas_divisoes);
            for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
        ?>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <?
            }
        ?>
        <td>
            Total Faturado R$
        </td>
    </tr>
<?
        //Representantes ...
        if(!empty($representante) || !empty($cmb_representante)) {
            /*Aqui eu busco todos os Clientes "Carteira" do Representante passado por parâmetro 
            ou selecionado na Combo ...*/
            if(!empty($representante)) {
                $sql = "SELECT DISTINCT(`id_cliente`) 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$representante' ";
            }else if(!empty($cmb_representante)) {
                $sql = "SELECT DISTINCT(`id_cliente`) 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$cmb_representante' ";
            }
            $campos_clientes = bancos::sql($sql);
            $linhas_clientes = count($campos_clientes);
            for($i = 0; $i < $linhas_clientes; $i++) $vetor_clientes[] = $campos_clientes[$i]['id_cliente'];
            
            $condicao_clientes = " AND nfs.`id_cliente` IN (".implode(',', $vetor_clientes).") ";
        }
        
        //Empresas Divisões ...
        if(!empty($cmb_empresa_divisao)) $condicao_empresa_divisao = " AND `id_empresa_divisao` LIKE '$cmb_empresa_divisao' ";
        
        //Campos de Data ...
        $data_inicial   = data::datatodate($txt_data_inicial, '-');
        $data_final     = data::datatodate($txt_data_final, '-');

        if($cmb_credito == '') 	$cmb_credito = '%';//Combo de Crédito ...
        
        /*Apesar de eu não utilizar esse campo "valor_total" na Apresentação de dentro do For, eu ainda o 
        mantenho nesse SQL para me ajudar na ordenação do Cliente que mais comprou até o que menos comprou ...*/
        $sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS valor_total, 
                IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, c.id_cliente, c.credito, 
                c.bairro, c.cidade, c.ddi_com, c.ddd_com, c.telcom, ufs.sigla 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`credito` LIKE '$cmb_credito' AND c.`ativo` = '1' 
                LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                $condicao_clientes 
                GROUP BY nfs.`id_cliente` HAVING `valor_total` > '0' ORDER BY valor_total DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href = "javascript:nova_janela('../apv/relatorio_pdf.php?id_clientes=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO_CLIENTES', '', '', '', '', 550, 975, 'c', 'c', '', '', 's', 's', '', '', '')" title='APV do Cliente' style='cursor:help' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td align='center'>
            <?=$campos[$i]['bairro'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='center'>
        <?
            //Busco o nome do Representante ...
            $sql = "SELECT r.`nome_fantasia` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    $condicao_empresa_divisao LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td align='center'>
        <?
            $color = ($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D')? 'red' : 'darkblue';
            echo '<font color="'.$color.'"><b>'.$campos[$i]['credito'].'</b></font>';
        ?>	
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com'])) echo '('.$campos[$i]['ddi_com'].') - ';
            if(!empty($campos[$i]['ddd_com'])) echo '('.$campos[$i]['ddd_com'].') ';
            echo $campos[$i]['telcom'];
        ?>
        </td>    
        <?
            $sql = "SELECT ged.`id_empresa_divisao`, SUM((nfsi.`qtde` - nfsi.`qtde_devolvida`) * nfsi.`valor_unitario`) AS valor_total 
                    FROM `nfs` 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` $condicao_empresa_divisao 
                    WHERE nfs.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    AND nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                    GROUP BY ged.`id_empresa_divisao` HAVING `valor_total` > '0' ";
            $campos_faturamento = bancos::sql($sql);
            $linhas_faturamento = count($campos_faturamento);
            if($linhas_faturamento > 0) for($j = 0; $j < $linhas_faturamento; $j++) $vetor_faturamento[$campos_faturamento[$j]['id_empresa_divisao']] = $campos_faturamento[$j]['valor_total'];
            $total_por_ano = 0;
            for($j = 0; $j < $linhas_empresas_divisoes; $j++) {
        ?>
        <td>
            <?=number_format($vetor_faturamento[$campos_empresas_divisoes[$j]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
        <?
                $total_por_ano+= $vetor_faturamento[$campos_empresas_divisoes[$j]['id_empresa_divisao']];
                //Já limpa o Valor da Empresa Divisão p/ o Próximo Loop ...
                $vetor_faturamento[$campos_empresas_divisoes[$j]['id_empresa_divisao']] = 0;
            }
        ?>
        <td align='right'>
            <?=number_format($total_por_ano, 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='17'>
            <br/><?='Quantidade de Clientes => '.$linhas;?>
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
    <tr align='center'>
        <td colspan='17'>
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