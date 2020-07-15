<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OP EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTÁ OP NÃO PODE SER EXCLUÍDA.</font>";

if($passo == 1) {
    $contador = 0;//Variável de Controle de Exclusão ...
    $explicao = 'ESTÁ OP NÃO PODE SER EXCLUÍDA !!! \n\n';
//Disparo de Loop das OP(s) Selecionada(s) ...
    foreach($_POST['chkt_op'] as $id_op) {
//Antes de eu excluir a OP, eu verifico se está consta em algum outro lugar ...
//1)Verifico se está OP possui entradas ...
        $sql = "SELECT `id_baixa_op_vs_pa` 
                FROM `baixas_ops_vs_pas` 
                WHERE `id_op` = '$id_op' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $contador++;
            $explicao.= 'ESTÁ OP POSSUI ENTRADA(S) !\n';
        }
//2)Verifico se está OP possui Aço Baixado ...
        $sql = "SELECT SUM(`qtde_baixa`) AS total_qtde_baixada 
                FROM `baixas_ops_vs_pis` 
                WHERE `id_op` = '$id_op' ";
        $campos = bancos::sql($sql);
        if($campos[0]['total_qtde_baixada'] > 0) {//Representa que tivemos um total de baixa maior que o total de estorno p/ essa OP e sendo assim NÃO podemos excluir esta ...
            $contador++;
            $explicao.= 'ESTÁ OP POSSUI AÇO(S) BAIXADO(S) !\n';
        }
//3)Verifico se está OP está atrelada a alguma OS ...
        $sql = "SELECT `id_os_item` 
                FROM `oss_itens` 
                WHERE `id_op` = '$id_op' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $contador++;
            $explicao.= 'ESTÁ OP ESTÁ ATRELADA A UMA OS !\n';
        }
//Significa que eu posso estar excluíndo normalmente essa OP ...
        if($contador == 0) {
//Não posso excluir essa OP, devido algum motivo ...
            $sql = "UPDATE `ops` SET `ativo` = '0' WHERE `id_op` = '$id_op' LIMIT 1 ";
            bancos::sql($sql);
            
            $sql = "SELECT `id_produto_acabado` 
                    FROM `ops` 
                    WHERE `id_op`= '$id_op' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) estoque_acabado::atualizar_producao($campos[0]['id_produto_acabado']);
            $valor = 2;
        }else {
//Retorno um alert, informando ao usuário os motivos pelo qual que ele não pode excluir a OP ...
?>
        <Script Language= 'Javascript'>
            alert('<?=$explicao;?>')
        </Script>
<?
            $valor = 3;
        }
    }
?>
    <Script Language= 'Javascript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
    //Aqui eu puxo o único Filtro de OP(s) que serve para toda parte de OP(s) ...
    require('tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Excluir OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'controlar_checkbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Excluir OP(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º OP
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde a Produzir
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Prazo de Entrega
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        $indice = 0;
        for ($i = 0;  $i < $linhas; $i++) {
            $link_baixa = 'N';//Sempre limpo essa variavel p/ nao dar conflito com as do loops anteriores ...
            
            /*Aki verifica se a OP está sendo utilizado em lugares comprometedores ou se existe Desenho p/ OP ...
            if(cascate::consultar('id_op', 'oss_itens, baixas_ops_vs_pas', $campos[$i]['id_op']) == 1 || $campos[$i]['desenho_para_op'] != '') {*/
            
            /*********************Modificado em 03/10/2018*********************/
            //Verifica se a OP está sendo utilizado em lugares comprometedores ...
            if(cascate::consultar('id_op', 'oss_itens, baixas_ops_vs_pas', $campos[$i]['id_op']) == 1) {
                $sql = "SELECT `status` 
                        FROM `baixas_ops_vs_pis` 
                        WHERE `id_op` = '".$campos[$i]['id_op']."' ORDER BY `id_baixa_op_vs_pi` DESC LIMIT 1 ";
                $campos_baixa_op = bancos::sql($sql);
                if(count($campos_baixa_op) == 1) {//Estornando Baixa ...
                    if($campos_baixa_op[0]['status'] == 2) {//Se o último status for baixa irá exibir um link mais abaixo ...
                        $link_baixa         = 'S';
                        $exibir_checkbox    = 'N';
                    }else if($campos_baixa_op[0]['status'] == 3) {//Se o último status for Estorno posso exibir o checkbox ...
                        $exibir_checkbox    = 'S';
                    }
                }
            }else {
                $exibir_checkbox = 'S';
            }
            
            $vetor_dados_op = intermodular::dados_op($campos[$i]['id_op']);
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '<?=$indice;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_op'].$vetor_dados_op['posicao_op'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_produzir'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
        <td>
        <?
            //Signica que a OP esta sendo usada em lugares comprometedores ...
            if($exibir_checkbox == 'N') {
/*Como o último Status for baixa, então eu exibo essa ferramenta p/ poder estornar a 
Baixa dessa OP e desse PI no Banco de Dados ...*/
                if($link_baixa == 'S') {

        ?>
            <a href='locais_atrelados.php?id_op=<?=$campos[$i]['id_op'];?>' class='html5lightbox'>?</a>
        <?
                }
            }else {//Esta OP não está sendo utilizada em lugares comprometedores, então posso excluí-la caso desejar ...
        ?>
            <input type='checkbox' name='chkt_op[]' value="<?=$campos[$i]['id_op'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '<?=$indice;?>', '#E8E8E8')" class='checkbox'>
        <?
                $indice++;
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}
?>