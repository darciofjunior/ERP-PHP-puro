<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $vetor_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');//Esse vetor será utilizado mais embaixo ...
    
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_nome   = $_POST['txt_nome'];
        $cmb_chefe  = $_POST['cmb_chefe'];
    }else {
        $txt_nome   = $_GET['txt_nome'];
        $cmb_chefe  = $_GET['cmb_chefe'];
    }
    
    if(!empty($cmb_chefe)) $condicao_chefes = ($cmb_chefe[0] > 0) ? " AND f.`id_funcionario_superior` IN (".implode(',', $cmb_chefe).") " : '';
    
    //Aqui eu só listo os funcionários que possuem Banco de Horas e que ainda estejam trabalhando na Empresa ...
    $sql = "SELECT TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(bh.`qtde_horas`))), '%H:%i') AS qtde_horas, 
            f.`id_funcionario`, f.`nome`, f.`tipo_salario`, f.`salario_pd`, f.`salario_pf` 
            FROM `bancos_horas` bh 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = bh.`id_funcionario` AND f.`nome` LIKE '%$txt_nome%' AND f.`status` < '3' $condicao_chefes 
            GROUP BY f.`id_funcionario` ORDER BY f.`nome` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhuma Ocorrência ...
?>
    <Script Language = 'Javascript'>
        window.location = 'relatorio.php?valor=1'
    </Script>
<?
    }else {//Encontrou pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Relatório de Banco de Hora(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    document.form.action = '<?=$PHP_SELF.'?passo=1';?>'
    document.form.target = '_self'
    document.form.submit()
}

function imprimir_pdf() {
    document.form.action = 'relatorio/imprimir_pdf.php'
    document.form.target = 'IMPRIMIR_PDF'
    nova_janela('relatorio/imprimir_pdf.php', 'IMPRIMIR_PDF', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post'>
<!--***************Controle de Tela***************-->
<input type='hidden' name='txt_nome' value='<?=$txt_nome;?>'>
<!--**********************************************-->
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relatório de Banco de Hora(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            Chefe: 
            <select name='cmb_chefe[]' id='cmb_chefe' title='Selecione um Chefe' class='combo' size='5' multiple>
            <?
                /*Nessa relação, eu só listo os Funcionários que são Chefes ou Superiores conforme BD e 
                que ainda trabalham aqui na Empresa "Férias 0, Ativo 1 ou Afastado 2" ...*/
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `status_superior` = '1' 
                        AND `status` <= '2' 
                        ORDER BY `nome` ";
                echo combos::combo($sql, $_POST['cmb_chefe']);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='return validar()' class='botao'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Nome
        </td>
        <td>
            Qtde Hora(s)
        </td>
        <td>
            Saldo em Dia(s)
        </td>
        <td>
            Valor Hora R$
        </td>
        <td>
            Total R$
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <font size='2'>
                <?=$campos[$i]['nome'];?>
            </font>
        </td>
        <td>
            <?
                //Se a Quantidade de Horas do Banco do Funcionário for Negativa, mostro na Cor vermelha ...
                $color = ($campos[$i]['qtde_horas'] < 0) ? 'red' : '';
            ?>
            <a href='detalhes.php?id_funcionario=<?=$campos[$i]['id_funcionario'];?>' class='html5lightbox'>
                <font size='2' color='<?=$color;?>'>
                    <?=$campos[$i]['qtde_horas'];?>
                </font>
            </a>
        </td>
        <td>
            <font color='<?=$color;?>'>
            <?
                //Um funcionário trabalha 9 horas por dia por isso dessa divisão ...
                $qtde_dias = round($campos[$i]['qtde_horas'] / 9, 1);
                echo number_format($qtde_dias, 1, ',', '');
            ?>
            </font>
        </td>
        <td align='right'>
        <?
            $salario_mensal = $campos[$i]['salario_pd'] + $campos[$i]['salario_pf'];
            //Se o Salário do Funcionário for Mensalista, divido por 220 p/ transformar em Horas ...
            if($campos[$i]['tipo_salario'] == 2) $salario_mensal/= 220;
            echo number_format($salario_mensal, 2, ',', '.');
        ?>    
        </td>
        <td align='right'>
            <font color='<?=$color;?>'>
                <?=number_format($qtde_dias * 9 * $salario_mensal, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'relatorio.php'" class='botao'>
            <input type='submit' name='cmd_imprimir_pdf' value='Imprimir PDF' title='Imprimir PDF' onclick='return imprimir_pdf()' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Relatório de Banco de Hora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action=''>
<input type='hidden' name='passo' value='1'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Banco de Hora(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' size='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.txt_nome.focus()" style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>