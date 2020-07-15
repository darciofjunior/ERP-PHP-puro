<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia                     = $_POST['txt_referencia'];
        $txt_discriminacao                  = $_POST['txt_discriminacao'];
        $txt_prateleira                     = $_POST['txt_prateleira'];
        $txt_bandeja                        = $_POST['txt_bandeja'];
        $txt_observacao                     = $_POST['txt_observacao'];
        $cmb_embalado                       = $_POST['cmb_embalado'];
        $chkt_somente_em_aberto             = $_POST['chkt_somente_em_aberto'];
        $chkt_tem_item_faltante_atrelado    = $_POST['chkt_tem_item_faltante_atrelado'];
    }else {
        $txt_referencia                     = $_GET['txt_referencia'];
        $txt_discriminacao                  = $_GET['txt_discriminacao'];
        $txt_prateleira                     = $_GET['txt_prateleira'];
        $txt_bandeja                        = $_GET['txt_bandeja'];
        $txt_observacao                     = $_GET['txt_observacao'];
        $cmb_embalado                       = $_GET['cmb_embalado'];
        $chkt_somente_em_aberto             = $_GET['chkt_somente_em_aberto'];
        $chkt_tem_item_faltante_atrelado    = $_GET['chkt_tem_item_faltante_atrelado'];
    }
    
    if(!empty($cmb_embalado))                       $condicao_embalado = " AND ee.`embalado` = '$cmb_embalado' ";
    if(!empty($chkt_somente_em_aberto))             $condicao_somente_em_aberto = " AND ee.`status` = '0' ";
    if(!empty($chkt_tem_item_faltante_atrelado))    $condicao_tem_item_faltante_atrelado = " AND ee.`id_produto_acabado_faltante` <> '0' ";
    if(empty($txt_prateleira))                      $txt_prateleira = '%';
//Aqui eu busco todos os Registros de Estoque Excedentes registrados do PA passado por parâmetro ...
    $sql = "SELECT ee.*, pa.`referencia`, pa.`discriminacao`, pa.`peso_unitario` 
            FROM `estoques_excedentes` ee 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ee.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            WHERE ee.`prateleira` LIKE '$txt_prateleira' 
            AND ee.`bandeja` LIKE '%$txt_bandeja%' 
            AND ee.`observacao` LIKE '%$txt_observacao%' 
            $condicao_embalado 
            $condicao_somente_em_aberto 
            $condicao_tem_item_faltante_atrelado 
            ORDER BY ee.`prateleira`, ee.`bandeja`, pa.`referencia`, ee.`id_estoque_excedente` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'relatorio.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Relatório de Estoque Excedente do PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório de Estoque Excedente do PA
            &nbsp;
            <input type='button' name='relatorio_por_data' value='Relatório por Data' title='Relatório por Data' onclick="html5Lightbox.showLightbox(7, 'relatorio_por_data.php?txt_referencia=<?=$txt_referencia;?>&txt_discriminacao=<?=$txt_discriminacao;?>&txt_prateleira=<?=$txt_prateleira;?>&txt_bandeja=<?=$txt_bandeja;?>&txt_observacao=<?=$txt_observacao;?>&cmb_embalado=<?=$cmb_embalado;?>&chkt_somente_em_aberto=<?=$chkt_somente_em_aberto;?>&chkt_tem_item_faltante_atrelado=<?=$chkt_tem_item_faltante_atrelado;?>')" class='botao'>
        </td>
    </tr>
<?
    $prateleira_atual 	= '';
    $bandeja_atual 		= '';
    for ($i = 0; $i < $linhas; $i++) {//For ...
/*Aqui eu verifico se o Departamento Anterior é Diferente do Departamento Atual que está sendo listado
no loop, se for então eu atribuo o Departamento Atual p/ o Departamento Anterior ...*/
        if($prateleira_atual != $campos[$i]['prateleira']) {//1) Organização por Pratileira ...
            $prateleira_atual   = $campos[$i]['prateleira'];
            $bandeja_atual      = '';//Se modificou a prateleira, eu zero o histórico de Bandejas ...
            $total_prateleira   = 0;
?>
    <tr class='linhadestaque'>
        <td colspan='9'>
            <font color='yellow'>
                Prateleira:
            </font>
            <?
                echo $campos[$i]['prateleira'];
                
                //Aqui eu verifico o Peso Total da Prateleira mas organizo por Bandeja ...
                $sql = "SELECT ea.`bandeja`, SUM(ea.`qtde` * pa.`peso_unitario`) AS total_bandeja 
                        FROM `estoques_excedentes` ea 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` 
                        WHERE ea.`prateleira` = '".$campos[$i]['prateleira']."' 
                        GROUP BY ea.`bandeja` ";
                $campos_prateleira = bancos::sql($sql);
                $linhas_prateleira = count($campos_prateleira);
                for($j = 0; $j < $linhas_prateleira; $j++) {
                    $total_prateleira+= $campos_prateleira[$j]['total_bandeja'];
                    $bandeja[$campos_prateleira[$j]['bandeja']] = $campos_prateleira[$j]['total_bandeja'];
                }
            ?>
            -
            <font color='yellow'>
                Total da Prateleira:
            </font>	
            <?=number_format($total_prateleira, 2, ',', '.');?> Kgs
        </td>
    </tr>
<?
        }
				
        if($bandeja_atual != $campos[$i]['bandeja']) {//2) Organização por Bandeja ...
            $bandeja_atual = $campos[$i]['bandeja'];
?>
    <tr class='linhacabecalho'>
        <td colspan='9'>
            <font color='yellow'>
                Bandeja:
            </font>
            <?=$campos[$i]['prateleira'].'-'.$campos[$i]['bandeja'];?>
            -
            <font color='yellow'>
                Total da Bandeja:
            </font>
            <?
                echo number_format($bandeja[$campos[$i]['bandeja']], 2, ',', '.').' Kgs';
                if($bandeja[$campos[$i]['bandeja']] > 200) echo ' - <blink><font color="red" size="3">SOBRECARGA - ALIVIAR BANDEJA</font></blink>';
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            Embalado
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Peso<br>Unitário
        </td>
        <td>
            Peso<br>Total
        </td>
        <td>
            Item<br>Faltante
        </td>
        <td>
            Observação
        </td>
        <td>
            Status
        </td>
    </tr>
<?
            //Destrói o Índice e Valor armazenado no Array, p/ não dar conflito com outras Bandejas Futuras ...
            unset($bandeja[$campos[$i]['bandeja']]);
        }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['qtde'];?>
        </td>
        <td>
        <?
            if($campos[$i]['embalado'] == 'S') {
                echo 'SIM';
            }else if($campos[$i]['embalado'] == 'N') {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'] * $campos[$i]['peso_unitario'], 1, ',', '.');?>
        </td>
        <td>
        <?
            $sql = "SELECT referencia, CONCAT(referencia, ' * ', discriminacao) AS dados 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado_faltante']."' LIMIT 1 ";
            $campos_pas = bancos::sql($sql);
        ?>
            <font title="<?=$campos_pas[0]['dados'];?>" style="cursor:help" class="link">
                <a href='../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado_faltante'];?>' class='html5lightbox'>
                    <?=$campos_pas[0]['referencia'];?>
                </a>
            </font>
        </td>		
        <td align='left'>
        <?
            $observacao     = strstr($campos[$i]['observacao'], 'Observação:');
            echo $parte_inicial  = str_replace(strstr($campos[$i]['observacao'], 'Observação:'), '', $campos[$i]['observacao']).'<br><MARQUEE behavior="alternate" width="55%">'.$observacao.'</MARQUEE>';                                                    
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['status'] == 0) {
                echo '<font color="red"><b>(Em Aberto)</b></font>';
            }else {
                echo '<font color="darkblue"><b>(Concluído)</b></font>';
            }
        ?>
        </td>
    </tr>
<?
        }//Fim do For ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'relatorio.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Relatório de Estoque Excedente do PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Estoque Excedente do PA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Referência" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prateleira
        </td>
        <td>
            <input type='text' name="txt_prateleira" title="Digite a Prateleira" size="6" maxlength="3" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bandeja
        </td>
        <td>
            <input type='text' name="txt_bandeja" title="Digite a Bandeja" size="3" maxlength="1" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name="txt_observacao" title="Digite a Observação" size="55" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Embalado
        </td>
        <td>
            <select name='cmb_embalado' title='Selecione o Embalado' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S'>SIM</option>
                <option value='N'>NÃO</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_em_aberto' value='1' title="Somente em Aberto" class="checkbox" id='label1' checked>
            <label for="label1">Somente em Aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_tem_item_faltante_atrelado' value='1' title="Tem Item faltante Atrelado" class="checkbox" id='label2'>
            <label for="label2">Tem Item faltante Atrelado</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_cliente.focus()" style="color:#ff9900" class='botao'>
            <input type='submit' name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>