<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 2 => 'AVALIADO PELO SUPERVISOR', 3 => 'ENVIADO PARA PROCESSO INTERNO', 
4 => 'ENVIADO P/ TÉCNICO - PARA ESCLARECIMENTO DE PROBLEMA', 5 => 'ENVIADO P/ TÉCNICO - PARA ORÇAMENTO', 
6 => 'ORÇAMENTO ENVIADO P/ CLIENTE - AGUARDANDO APROVAÇÃO', 7 => 'ENVIADO P/ ESTOQUE', 8 => 'MANIPULAÇÃO P/ ESTOQUE', 
9 => 'ENVIADO P/ CLIENTE / REPRESENTANTE', 10 => 'DESDOBRAR QUANTIDADE', 11 => 'ACOMPANHAMENTO INTERNO');

if($passo == 1) {
//Busca Somente as OCs em Aberto ...
    if($chkt_ocs_em_aberto == 1) $condicao_em_aberto = " AND ocs.status < '1' ";
    if($chkt_data_emissao_30_dias == 1) {//Busca das OCs em que a Data de Emissão <= 30 dias
        $data_atual_abaixo_30 	= data::adicionar_data_hora(date('d/m/Y'), -30);
        $data_atual_abaixo_30 	= data::datatodate($data_atual_abaixo_30, '-');
        $condicao_data_emissao 	= " AND ocs.data_emissao >= '$data_atual_abaixo_30' ";
    }
//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
    if(!empty($cmb_representante))  $condicao_representante = " AND ocs.`id_representante` LIKE '$cmb_representante' ";
    if(empty($cmb_status))          $cmb_status = '%';
    if(!empty($txt_referencia) || !empty($txt_discriminacao) || $cmb_status > 0) {
        $inner_join_ocs = "
                INNER JOIN `ocs_itens` oi ON oi.`id_oc` = ocs.`id_oc` AND oi.`status` LIKE '$cmb_status' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' ";
    }
    $sql = "SELECT DISTINCT(ocs.`id_oc`), ocs.`id_cliente_contato`, DATE_FORMAT(ocs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
            DATE_FORMAT(ocs.`data_conclusao`, '%d/%m/%Y') AS data_conclusao, ocs.`nf_entrada`, ocs.`observacao`, 
            c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, c.`cidade` 
            FROM `ocs` 
            $inner_join_ocs 
            INNER JOIN `clientes` c ON c.`id_cliente` = ocs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
            WHERE ocs.`id_oc` LIKE '%$txt_numero_oc%' 
            $condicao_representante 
            AND ocs.`observacao` LIKE '%$txt_observacao%' 
            $condicao_em_aberto $condicao_data_emissao ORDER BY ocs.`data_emissao` DESC, ocs.`id_oc` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Se não encontrou nenhuma OC ...
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {//Se encontrou mais do que 1 OC ...
?>
<html>
<head>
<title>.:: Consultar OCs::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_oc) {
    window.location = 'itens.php?id_oc='+id_oc
}
</Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Consultar OC(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; OC
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cidade / Estado
        </td>
        <td>
            Contato
        </td>
        <td>
            <font title='Representante' style='cursor:help'>
                Rep
            </font>
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Data Conclusão
        </td>
        <td>
            NF de Entrada
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormalescura' align='center'>
        <td width='10'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_oc'];?>')" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_oc'];?>')" class='link'>
                <?=$campos[$i]['id_oc'];?>
            </a>
        </td>
        <td align='left'>
            <font title='Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>' style='cursor:help'>
                <?=$campos[$i]['razaosocial'];?>
            </font>
            <?
                //Aqui verifica se a OC contém pelo menos 1 item ...
                $sql = "SELECT `id_oc_item` 
                        FROM `ocs_itens` 
                        WHERE `id_oc` = '".$campos[$i]['id_oc']."' LIMIT 1 ";
                $campos_itens_ocs 	= bancos::sql($sql);
                $qtde_itens_ocs 	= count($campos_itens_ocs);
                if($qtde_itens_ocs == 0) echo ' <font color="red">(S/ ITENS)</font>';
            ?>
        </td>
        <td>
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT `nome` 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            echo $campos_contato[0]['nome'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT `nome_fantasia` 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?if($campos[$i]['data_conclusao'] != '00/00/0000') echo $campos[$i]['data_conclusao'];?>
        </td>
        <td>
            <?=$campos[$i]['nf_entrada'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
            //Busca dos Itens da OC ...
            $sql = "SELECT oi.`id_produto_acabado`, oi.`qtde`, oi.`defeito_alegado`, 
                    oi.`cliente_vai_devolver_peca`, oi.`status`, pa.`referencia`, pa.`discriminacao` 
                    FROM `ocs_itens` oi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` 
                    WHERE oi.`id_oc` = '".$campos[$i]['id_oc']."' ";
            $campos_itens = bancos::sql($sql);
            $linhas_itens = count($campos_itens);
            for($j = 0; $j < $linhas_itens ;$j++) {
?>
    <tr class='linhanormal'>
        <td colspan='2' align='center'>
            <b>Qtde => </b> <?=$campos_itens[$j]['qtde'];?> 
        </td>
        <td colspan='3'>
            <b>Produto:</b> <?=$campos_itens[$j]['referencia'].' - '.$campos_itens[$j]['discriminacao'];?>
        </td>
        <td colspan='3'>
            <b>Defeito Alegado:</b>
            <?
                echo $campos_itens[$j]['defeito_alegado'];
                if($campos_itens[$j]['cliente_vai_devolver_peca'] == 'S') echo '<font color="red" title="Cliente vai Devolver Peça" style="cursor:help"><b> (Cliente Dev)</b></font>';
            ?>
        </td>
        <td colspan='2'>
            <b>Status:</b> <?=$vetor_status[$campos_itens[$j]['status']];?>
        </td>
    </tr>
<?				
            }
?>
    <tr class='linhanormal'><!--Linha Preta, para fazer separação de uma OC e seus Itens com outra-->
        <td colspan='10' bgcolor='#000000'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar OCs ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body onload="document.form.txt_cliente.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar OC(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da OC
        </td>
        <td>
            <input type='text' name="txt_numero_oc" title="Digite o Número da OC" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name="txt_cliente" title="Digite o Cliente" class='caixadetexto'>
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
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Status
        </td>
        <td>
            <select name='cmb_status' title='Selecione o Status do Item' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    for($i = 1; $i <= count($vetor_status); $i++) {
                ?>
                <option value='<?=$i;?>'><?=$vetor_status[$i];?></option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ocs_em_aberto' value='1' title='Só OC(s) em Aberto' id='label1' class='checkbox' checked>
            <label for='label1'>Só OC(s) em Aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_data_emissao_30_dias' value='1' title='Data de Emissão <= 30 dias' id='label2' class='checkbox'>
            <label for='label2'>Data de Emissão <= 30 dias</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>