<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');

echo 'EM MANUTENÇÃO !';
exit;

require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OE EXCLUÍDA COM SUCESSO.</font>";

if($passo == 1) {
    foreach($_POST['chkt_oe'] as $id_oe) {
        //Aqui eu busco a Quantidade de Saída do $id_oe do Loop que foi selecionado na Tela anterior ...
        $sql = "SELECT oes.`id_produto_acabado_s`, oes.`id_produto_acabado_e`, oes.`qtde_s`, u.`sigla` 
                FROM `oes` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oes.`id_produto_acabado_s` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE oes.`id_oe` = '$id_oe' LIMIT 1 ";
        $campos = bancos::sql($sql);
        
        /****************************PA(s) de Saída****************************/
        //Faço esse Tratamento porque podemos ter várias Qtde(s) de Saída ...
        $vetor_qtde_s           = explode(',', $campos[0]['qtde_s']);
        //Faço esse Tratamento porque podemos ter vários PA(s) de Saída ...
        $vetor_produto_acabado  = explode(',', $campos[0]['id_produto_acabado_s']);
        
        foreach($vetor_produto_acabado as $j => $id_produto_acabado_loop) {
            //1) Atualizando o P.A. Enviado
            $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_oe`, `retirado_por`, `qtde`, `observacao`, `acao`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$id_produto_acabado_loop', '$_SESSION[id_funcionario]', '', '$id_oe', '', '$vetor_qtde_s[$j]', 'OE cancelada.', 'M', '2', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        
            //Aqui eu preciso recalcular a Qtde de Estoque do PA que eu exclui a OE, que é o de Saída ...
            estoque_acabado::atualizar($id_produto_acabado_loop);
            estoque_acabado::controle_estoque_pa($id_produto_acabado_loop);
        }
        /***************************PA(s) de Entrada***************************/
        //Atualizações Finais na própria OE ...
        $sql = "UPDATE `oes` SET `qtde_s` = '0', `data_e` = '".date('Y-m-d H:i:s')."', `observacao_e` = 'Cancelada a OE e saída de ".$campos[0]['qtde_s'].' '.$campos[0]['sigla'].".', `status_finalizar` = '1' WHERE `id_oe` = '$id_oe' LIMIT 1 ";
        bancos::sql($sql);
        
        //Aqui eu preciso recalcular a Qtde de OEs do PA que eu exclui a OE, que é o de Entrada ...
        estoque_acabado::atualizar_producao($campos[0]['id_produto_acabado_e']);
    }
?>
    <Script Language= 'Javascript'>
        window.location = 'excluir.php?valor=4'
    </Script>
<?
}else {
    $sem_entrada    = " AND o.`qtde_e` = '0' ";

    //Aqui eu puxo o único Filtro de OE(s) que serve para toda parte de OE(s) ...
    require('tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Excluir OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr>
        <td colspan='12'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Excluir OE(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            N.º O.E.
        </td>
        <td>
            PA Saída
        </td>
        <td>
            PA Entrada
        </td>
        <td>
            Qtde Saída
        </td>
        <td>
            Qtde Entrada
        </td>
        <td>
            Data de Emissão Saída
        </td>
        <td>
            Data de Emissão Entrada
        </td>
        <td>
            Observação Saída
        </td>
        <td>
            Observação Entrada
        </td>
        <td>
            Login Entrada
        </td>
        <td>
            Login Saída
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=1&pop_up=1&id_oe='.$campos[$i]['id_oe'];
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_oe[]' value="<?=$campos[$i]['id_oe'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_oe'];?>
            </a>
        </td>
        <td align='left'>
        <?
            //Faço esse Tratamento porque podemos ter vários PA(s) de Saída ...
            $vetor_produto_acabado = explode(',', $campos[$i]['id_produto_acabado_s']);
            foreach($vetor_produto_acabado as $j => $id_produto_acabado_loop) {
                echo '* '.intermodular::pa_discriminacao($id_produto_acabado_loop).'<br/>';
            }
        ?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado_e']);?>
        </td>
        <td>
            <?=$campos[$i]['qtde_s'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_e'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_s'], '/').' - '.substr($campos[$i]['data_s'], 11, 8);?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_e'], '/').' - '.substr($campos[$i]['data_e'], 11, 8);?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao_s'])) echo "<img width='28' height='23' title='".$campos[$i]['observacao_s']."' src = '../../../imagem/olho.jpg'>"."<br>";
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao_e'])) echo "<img width='28' height='23' title='".$campos[$i]['observacao_e']."' src = '../../../imagem/olho.jpg'>"."<br>";
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario_resp_s']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario_resp_e']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
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