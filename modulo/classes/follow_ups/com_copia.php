<?
require('../../../lib/segurancas.php');
require('../array_sistema/array_sistema.php');
?>
<html>
<title>.:: Com C�pia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<body>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <?
        for($i = 0; $i < $_POST['qtde_com_copia']; $i++) {
            /*******************************Controle*******************************/
            /*S� a partir do Segundo registro do Loop que essa vari�vel $com_copias_selecionadas 
            estar� abastecida, porque a minha real inten��o aqui � de n�o apresentar no pr�ximo Loop 
            o Funcion�rio / Representante que foi apresentado no Loop Anterior ...*/
            if(!empty($com_copias_selecionadas)) {
                $condicao_com_copias_selecionadas_funcionarios      = " AND `email_externo` NOT IN (".substr($com_copias_selecionadas, 0, strlen($com_copias_selecionadas) - 2).") ";
                $condicao_com_copias_selecionadas_representantes    = " AND `email` NOT IN (".substr($com_copias_selecionadas, 0, strlen($com_copias_selecionadas) - 2).") ";
            }
    ?>
    <tr class='linhanormal'>
        <td width='15%'>
            Com c&oacute;pia para:
        </td>
        <td width='84%'>
            <select name='cmb_com_copia_para[]' id='cmb_com_copia_para<?=$i;?>' title='Selecione o Com c&oacute;pia para' class='combo'>
            <?
                //SQL 1) Listagem de todos os Funcion�rios que possuem E-mail Interno e que trabalham na Empresa
                //SQL 2) Listagem de todos os Representantes ...
                $sql = "(SELECT `email_externo`, `nome` 
                        FROM `funcionarios` 
                        WHERE `email_externo` <> '' 
                        $condicao_com_copias_selecionadas_funcionarios 
                        AND `status` < '3') 
                        UNION 
                        (SELECT `email`, CONCAT(UPPER(SUBSTR(`nome_fantasia`, 1, 1)), LOWER(SUBSTR(`nome_fantasia`, 2, LENGTH(`nome_fantasia`)))) AS nome 
                        FROM `representantes` 
                        WHERE `email` <> '' 
                        $condicao_com_copias_selecionadas_representantes 
                        AND `ativo` = '1') 
                        ORDER BY `nome` ";
                echo utf8_encode(combos::combo($sql, $_POST['cmb_com_copia_para'][$i], 'S'));
            ?>
            </select>
            <?
                if(($i + 1) == $_POST['qtde_com_copia']) {//S� mostro estes �cones no �ltimo Registro do Loop ...
            ?>
            &nbsp;
            <img src = '../../../imagem/menu/adicao.jpeg' border='0' title='Incluir Com c&oacute;pia' alt='Incluir Com c&oacute;pia' width='16' height='16' onclick='incluir_com_copia()'>
            <?
                    //Se tivermos no m�nimo 2 usu�rios "Com c�pia para" � que ser� poss�vel ent�o excluir o E-mail ...
                    if($_POST['qtde_com_copia'] >= 2) {
            ?>
            &nbsp; 
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Com c&oacute;pia' alt='Excluir Com c&oacute;pia' onclick='excluir_com_copia()'>
            <?
                    }
                }
            ?>
        </td>
    </tr>
    <?
            $com_copias_selecionadas.= "'".$_POST['cmb_com_copia_para'][$i]."', ";
        }
    ?>
</table>
</body>
</html>