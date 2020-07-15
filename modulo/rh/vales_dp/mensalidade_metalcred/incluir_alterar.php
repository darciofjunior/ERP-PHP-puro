<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>MENSALIDADE METALCRED INCLUÍDA COM SUCESSO.</font>";

if($_POST['hdd_salvar'] == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
//Primeiro apaga-se todos os vales do Tipo Mensalidade MetalCred p/ poder gerar Novos Vales Válidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '15' 
            AND `data_debito` = '$_POST[cmb_data_holerith]' ";
    bancos::sql($sql);
    //Renomeio a Variável p/ o nome de $id_funcionario_loop por causa que id_funcionario já existe na Sessão ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {
//Busca da Empresa do Funcionário porque eu tenho um controle mais abaixo ...
        $sql = "SELECT `id_empresa` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
        $campos_empresa = bancos::sql($sql);
//Aqui eu tenho q/ renomear a Empresa p/ não dar conflito a variável $id_empresa da Sessão ...
        $id_empresa_loop = $campos_empresa[0]['id_empresa'];
//Se a Empresa for Alba ou Tool, eu tenho que descontar do salário PD do Funcionário ...
        if($id_empresa_loop == 1 || $id_empresa_loop == 2) {
            $descontar_pd_pf = 'PD';
        }else {//Descontar do salário PF do Funcionário quando a Empresa for Grupo ...
            $descontar_pd_pf = 'PF';
        }
//Se o Valor do vale <> 0, então eu gero vale para esse funcionário ...
        if($_POST['txt_vlr_metalcred'][$i] != 0) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '15', '".$_POST['txt_vlr_metalcred'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', '$descontar_pd_pf', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>&valor=1'
    </Script>
<?
}else {
/****************************************************************************************************/
/*Listagem de Funcionários que ainda estão trabalhando e que estão com a marcação de Mensalidade MetalCred ...
* Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT id_funcionario, nome, valor_metalcred 
            FROM `funcionarios` 
            WHERE `status` < '3' 
            AND `mensalidade_metalcred` = 'S' 
            AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
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
<title>.:: Incluir / Alterar Vale(s) - Mensalidade MetalCred ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos   = document.form.elements
    var indice      = 0//Essa variável me servirá p/ controlar somente as Caixas de Texto ...
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text' && elementos[i].name == 'txt_vlr_metalcred[]') {
            //Preparo as caixas ...
            document.getElementById('txt_vlr_metalcred'+indice).value    = strtofloat(document.getElementById('txt_vlr_metalcred'+indice).value)
            //Habilito as caixas p/ poder gravar no Banco ...
            document.getElementById('txt_vlr_metalcred'+indice).disabled = false
            indice++
        }
    }
    document.form.hdd_salvar.value = 1//Aqui p/ gravar no Banco de Dados o Vale que o usuário realmente quis gerar ...
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$_GET['cmb_data_holerith'];?>'>
<!--Esse hidden é um controle que criei p/ que o sistema ao fechar a tela de dados do cadastro do funcionário 
não atualiza esse arquivo aqui gerando os vales de Mensalidade MetalCred sem querer ...-->
<input type='hidden' name='hdd_salvar' value='0'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir / Alterar Vale(s) - Mensalidade MetalCred
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($_GET['cmb_data_holerith'], '/');?>
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td>
            Funcionário
        </td>
        <td>
            Valor MetalCred
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Cálculos e controle com o Pop-Up ... 
        $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <input type='text' name='txt_vlr_metalcred[]' id='txt_vlr_metalcred<?=$i;?>' value='<?=number_format($campos[$i]['valor_metalcred'], 2, ',', '.');?>' title='Valor MetalCred' size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
    </tr>
<?
        //Essa variável aqui eu apresento mais abaixo no fim do loop ...
        $total_vlr_metalcred+= $campos[$i]['valor_metalcred'];
    }
?>
    <tr class='linhadestaque' align='center'>
        <td align='right'>
            Total Vlr MetalCred R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_metalcred' value='<?=number_format($total_vlr_metalcred, 2, ',', '.');?>' title='Total Vlr MetalCred R$' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value="Redefinir" title="Redefinir" onclick="document.form.reset()" style="color:#ff9900;" class='botao'>
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