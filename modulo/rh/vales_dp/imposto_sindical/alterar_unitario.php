<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE IMPOSTO SINDICAL ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    $sql = "UPDATE `vales_dps` SET `valor` = '$txt_vlr_fatura', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_vale_dp` = '$_POST[id_vale_dp]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_unitario.php?id_vale_dp=<?=$_POST['id_vale_dp'];?>&valor=1'
    </Script>
<?
}else {
//Busca dados do vale através do id_vale_dp passado por parâmetro ...
    $sql = "SELECT f.`id_funcionario`, f.`id_empresa`, f.`nome`, f.`tipo_salario`, f.`salario_pd`, vd.`data_debito` 
            FROM `vales_dps` vd 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
            WHERE vd.`id_vale_dp` = '$_GET[id_vale_dp]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale(s) - Imposto Sindical Unitário ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tratamento no objeto Vlr Fatura p/ gravar os objetos no BD ...
    document.form.txt_vlr_fatura.value = strtofloat(document.form.txt_vlr_fatura.value)
//Desabilita este campo p/ poder gravar no BD ...
    document.form.txt_vlr_fatura.disabled = false
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<!--Aqui eu renomeio essa variável $id_funcionario para $id_funcionario_loop para não dar conflito com 
a variável da Sessão "$id_funcionario"-->
<input type='hidden' name='id_funcionario_loop' value='<?=$campos[0]['id_funcionario'];?>'>
<input type='hidden' name='id_vale_dp' value='<?=$_GET['id_vale_dp'];?>'>
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale(s) - Imposto Sindical Unitário
        </td>
    </tr>
    <tr class=linhadestaque">
        <td colspan="6">
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($campos[0]['data_debito'], '/');?>
        </td>
    </tr>
    <tr >
        <td width='20%'>
            <b>Funcionário:</b>
        </td>
        <td width='80%'>
        <?
//Controle com o Pop-Up ... 
                $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[0]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        ?>
            <a href="#" onclick="<?=$url;?>" title="Detalhes Funcionário" class="link">
                <?=$campos[0]['nome'];?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <?=genericas::nome_empresa($campos[0]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Salário:</b>
        </td>
        <td>
        <?
            if($campos[0]['tipo_salario'] == 1) {//Horista
                echo 'HORISTA';
            }else {//Mensalista
                echo 'MENSALISTA';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Salário PD:</b>
        </td>
        <?
            if($campos[0]['tipo_salario'] == 1) {//Horista
                $salario_pd = 220 * $campos[0]['salario_pd'];
            }else {//Mensalista
                $salario_pd = $campos[0]['salario_pd'];
            }
        ?>
        <td>
            <?=number_format($salario_pd, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Fatura:</b>
        </td>
        <?
//Calculando o Imposto Sindical ...
            $imposto_sindical = $salario_pd / 30;//Divide pela qtde de Dias ...
            $vlr_fatura = $imposto_sindical;
        ?>
        <td>
            <input type='text' name='txt_vlr_fatura' value='<?=number_format($vlr_fatura, 2, ',', '.');?>' title='Valor da Fatura' size="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>