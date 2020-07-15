<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/relatorios/retirada_epis/retirada_epis.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Imprimir Relatório de EPI´s ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='100%' border='1' cellspacing='0' cellpadding='1' align='center'>
<?
    if(!empty($_POST['cmd_consultar'])) {
        $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
        $data_final     = data::datatodate($_POST['txt_data_final'], '-');

        $sql = "SELECT f.`nome`, bm.`qtde`, DATE_FORMAT(bm.`data_sys`, '%d/%m/%Y') AS data, pi.`discriminacao`, bm.`observacao`, c.`cargo`, d.`departamento`
                FROM `baixas_manipulacoes` bm
                INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = bm.id_produto_insumo
                INNER JOIN `funcionarios` f ON f.id_funcionario = bm.id_funcionario_retirado AND f.id_funcionario = '$cmb_funcionario'
                INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo
                INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento`
                INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.referencia = 'EPI'
                WHERE bm.`data_sys` BETWEEN '$data_inicial' AND '$data_final'
                ORDER BY bm.data_sys ASC";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
    }

    if($linhas > 0) {//Encontrou pelo menos 1 Registro ...
?>
    <tr>
        <td colspan='5'>
            <img src='../../../../imagem/cabecalho_marcas.jpg' width='100%' height='225'>
        </td>
    </tr>
    <tr class="linhadestaque">
        <td colspan="5" align="center">
            CONTROLE DE ENTREGA DE E.P.I (EQUIPAMENTO DE PROTEÇAO INDIVIDUAL)
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan="2" height="50">            
            <font size="2">Nome do Funcionário:&nbsp;<?=$campos[0]['nome'];?></font>
        </td>     
        <td height="50">            
           <font size="2">Setor:&nbsp; <?=$campos[0]['departamento'];?></font>
        </td>
        <td height="50">
            <font size="2">Função:&nbsp; <?=$campos[0]['cargo'];?></font>
        </td>        
    </tr>    
    <tr class='linhanormal'>    
        <td colspan="5"><font size="1">Declaro para os devidos fins e efeitos, ter recebido gratuitamente os Equipamentos de Proteção Individual Registrados neste documento,
        bem como a devida orientação quanto à sua utilização, zelo e conservação e finalidade conforme determina a legislação vigente, estando ciente de
        eles visam a preservação de minha integridade física no desempenho de minhas funções. Estou também ciente da obrigatoriedade do seu uso e que
        a recusa em usá-los constitui-se em motivo de DISPENSA POR JUSTA CAUSA para recisão de contrato de trabalho. Declaro também, ter sido Cientificado
        de que sou responsável por sua conservação, devolução por desgaste normal ou por ocasião de minha demissão, e autorizo o desconto em minha
        folha de pagamento ou verbas recisórias em caso de dano proposital, má utilização, extravio ou não devolução.</font></td>
    </tr>    
    <tr class='linhadestaque' align='center'>
        <td width='2%'>Qtde</td>
        <td width='3%'>Retirado Em</td>
        <td width='40%'>Produto (PI)</td>
        <td width='30%'>Assinatura</td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'> 
        <td>
            <?=(-1) * $campos[$i]['qtde'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['data'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='left'>
            &nbsp;
        </td>        
    </tr>    
<?
        }
?>       
    <tr align='center'>
        <td colspan="5">
            <?
                for($i = 0; $i < 2; $i++) echo '<br/>';
            ?>
            <hr noshade="noshade" width="350">   
            Assinatura do funcionario    
        </td>
    </tr>
    <!--Apresento o Botão de Imprimir p/ que o Usuário Imprima a Listagem caso desejar ...-->
    <Script Language = 'JavaScript'>
        parent.document.getElementById('linha_imprimir').style.visibility = 'visible'
    </Script>
<?
    }else {//Não encontrou nenhum Registro ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <!--Por não ter nenhum Registro, não tem sentido apresentar o Botão de Imprimir ...-->
    <Script Language = 'JavaScript'>
        parent.document.getElementById('linha_imprimir').style.visibility = 'hidden'
    </Script>
<?
    }
?>    
</table>
</form>
</body>
</html>