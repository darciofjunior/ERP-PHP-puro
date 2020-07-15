<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

if($passo == 1) {
/*Aqui eu gero um Vale Avulso, porque esse Valor tem de ser descontado do Funcionário na Data de Holerith 
que foi selecionada ...*/
    foreach($_POST['hdd_funcionario_vs_holerith'] as $i => $id_funcionario_vs_holerith) {
        //Busco o id_funcionario p/ quem foi gerado o Vale Negativo ...
        $sql = "SELECT `id_funcionario` 
                FROM `funcionarios_vs_holeriths` 
                WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
        $campos = bancos::sql($sql);
        //Atualizo o campo "gerado_vale_negativo" p/ "SIM", p/ que não apareça mais esse Registro na próxima vez ...
        $sql = "UPDATE `funcionarios_vs_holeriths` SET `gerado_vale_negativo` = 'S' 
                WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
        bancos::sql($sql);
        //Gerando o Vale Avulso ...
        $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `observacao`, `data_sys`) VALUES (NULL, '".$campos[0]['id_funcionario']."', '2', '".$_POST['txt_valor_vale'][$i]."', '$_POST[cmb_data_holerith]', '".date('Y-m-d')."', 'PF', 'Saldo negativo Holerith com vencimento em ".data::datetodata($_POST['cmb_data_holerith'], '/')."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        alert('SALDO NEGATIVO HOLERITH PF INCLUÍDO COM SUCESSO !')
        window.location = 'incluir.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>'
    </Script>
<?
}else {
//Busco um período anterior ao que foi passado por parâmetro da "Data de Holerith" ...
    $sql = "SELECT `id_vale_data` 
            FROM `vales_datas` 
            WHERE `data` < '$cmb_data_holerith' ORDER BY `data` DESC LIMIT 1 ";
    $campos_data_holerith   = bancos::sql($sql);
/*Busco todos os Funcionários que tiveram Vale Negativo no mês anterior e que ainda não tiveram o seu Vale 
Avulso gerado p/ fazer o desconto ...*/
    $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`id_empresa`, 
            fh.`id_funcionario_vs_holerith`, fh.`valor_total_receber` 
            FROM `funcionarios_vs_holeriths` fh 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = fh.`id_funcionario` 
            WHERE fh.`id_vale_data` = '".$campos_data_holerith[0]['id_vale_data']."' 
            AND fh.`valor_total_receber` < '0' 
            AND fh.`gerado_vale_negativo` = 'N' ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhum funcionário com essa marcação ...
?>
        <Script Language = 'Javascript'>
            window.location = '../itens/incluir.php?valor=1'
        </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Incluir / Alterar Vale(s) - Saldo Negativo Holerith PF ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_funcionario_vs_holerith[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario_vs_holerith[]'].length)
    }
//Preparo e desabilito as caixas p/ poder gravar no BD ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_valor_vale'+i).value       = strtofloat(document.getElementById('txt_valor_vale'+i).value)
        document.getElementById('txt_valor_vale'+i).disabled    = false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir Saldo Negativo Holerith PF - 
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
        <td>
            Valor do Vale
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        /*Como será gerado um "Vale Avulso" e esse terá que ser descontado do Funcionário, então faço a 
        inversão de Sinal para que o mesmo saia com Valor Positivo ...*/
        $valor_vale = (-1) * $campos[$i]['valor_total_receber'];
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href='<?=$url;?>' title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
        <td>
            R$ <input type='text' name='txt_valor_vale[]' id='txt_valor_vale<?=$i;?>' value="<?=number_format($valor_vale, 2, ',', '.');?>" title='Valor do Vale' size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario_vs_holerith[]' value='<?=$campos[$i]['id_funcionario_vs_holerith'];?>'>
        </td>
    </tr>
<?
        //Essa variável aqui eu apresento mais abaixo no fim do loop ...
        $total_vlr_vale+= $valor_vale;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='right'>
            Total Valor do Vale R$
        </td>
        <td>
            <input type='text' name='txt_total_vlr_vale' value="<?=number_format($total_vlr_vale, 2, ',', '.');?>" title='Total Valor do Vale' size="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>