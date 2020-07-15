<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Relatório de Cliente(s) não Visitado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
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
//Representante
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
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
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relatório de Cliente(s) não Visitado(s)
            <br/>
            <font color='yellow'>
                (À direita está o Faturamento desse(s) cliente(s) que estão s/ Visita - mesmo Período de Datas do Relatório)
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='7'>
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial))    $txt_data_inicial = data::adicionar_data_hora(date('d/m/Y'), -365);
                if(empty($txt_data_final))      $txt_data_final = date('d/m/Y');
            ?>
            <input type='text' name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
            &nbsp; <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;&nbsp;&nbsp;&nbsp;
            Relatório por: 
            <select name="cmb_representante" title="Selecione o Representante" class="combo">
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
<?
if(!empty($cmd_consultar)) {//Só processará dados de Relatório, quando o usuário clicar no botão Consultar ...
    $data_inicial   = data::datatodate($txt_data_inicial, '-');
    $data_final     = data::datatodate($txt_data_final, '-');
    
    //Verifico os Clientes que possuem visita no período especificado do APV, independente do Representante ...
    $sql = "SELECT DISTINCT(la.id_cliente) 
            FROM `logs_apvs` la 
            WHERE SUBSTRING(la.`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhum Cliente com visita agendada nesse período ...
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.
        </td>
    </tr>
<?
    }else {//Existe pelo menos 1 cliente com Visita agendada ...
        //Guardo na variável todos os Clientes que possuem agendamento no período ...
        for($i = 0; $i < $linhas; $i++) $id_cliente_com_visita.= $campos[$i]['id_cliente']. ', ';
        $id_cliente_com_visita  = substr($id_cliente_com_visita, 0, (strlen($id_cliente_com_visita) - 2));

        //Aqui eu trago todos os Clientes do Representante selecionado na Combo que "NÃO" possuem visita ...
        $sql = "SELECT c.id_cliente, c.razaosocial AS cliente 
                FROM `clientes` c 
                INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` = '$cmb_representante' 
                WHERE c.`id_cliente` NOT IN ($id_cliente_com_visita) GROUP BY c.id_cliente order by c.razaosocial";
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas = count($campos);
        
        //Aqui nessa variável guardamos todos os clientes que não possuem visita ...
        for($i = 0; $i < $linhas; $i++) $id_cliente_sem_visita.= $campos[$i]['id_cliente'].', ';
        $id_cliente_sem_visita  = substr($id_cliente_sem_visita, 0, (strlen($id_cliente_sem_visita) - 2));
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Cliente(s)
        </td>
        <td>
            Cabri R$
        </td>
        <td>
            Heinz R$
        </td>
        <td>
            Warrior R$
        </td>
        <td>
            NVO R$
        </td>
        <td>
            Tool Master R$
        </td>
        <td>
            Total R$
        </td>
    </tr>
<?
        /*Aqui busco o Total Faturado no mesmo período especificado do APV dos clientes que estão sem Visita 
        do representante selecionado na combo ...*/
        $sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS total, ged.id_empresa_divisao, nfs.id_cliente 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND nfs.id_cliente IN ($id_cliente_sem_visita) 
                GROUP BY ged.id_empresa_divisao, nfs.id_cliente ";
        $campos_nfs = bancos::sql($sql);
        $linhas_nfs = count($campos_nfs);
        for($i = 0; $i < $linhas_nfs; $i++) $clientes_divisoes_array[$campos_nfs[$i]['id_cliente']][$campos_nfs[$i]['id_empresa_divisao']] = $campos_nfs[$i]['total'];

        //Listo os clientes que NÃO possuem Visitas dentro do Período de 1 ano ...
        for($i = 0; $i < $linhas; $i++) {
//Aqui eu sempre zero essa variável p/ não continuar armazenando os valores antigos do Loop Anterior ...
            $total_todas_divisoes = 0;
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
        <?
            echo segurancas::number_format($clientes_divisoes_array[$campos[$i]['id_cliente']][1], 2, '.');
            $total_todas_divisoes+= $clientes_divisoes_array[$campos[$i]['id_cliente']][1];
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($clientes_divisoes_array[$campos[$i]['id_cliente']][2], 2, '.');
            $total_todas_divisoes+= $clientes_divisoes_array[$campos[$i]['id_cliente']][2];
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($clientes_divisoes_array[$campos[$i]['id_cliente']][3], 2, '.');
            $total_todas_divisoes+=$clientes_divisoes_array[$campos[$i]['id_cliente']][3];
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($clientes_divisoes_array[$campos[$i]['id_cliente']][5], 2, '.');
            $total_todas_divisoes+=$clientes_divisoes_array[$campos[$i]['id_cliente']][5];
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($clientes_divisoes_array[$campos[$i]['id_cliente']][4], 2, '.');
            $total_todas_divisoes+=$clientes_divisoes_array[$campos[$i]['id_cliente']][4];
        ?>
        </td>
        <td>
            <?=number_format($total_todas_divisoes, 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
}
?>
</body>
</html>