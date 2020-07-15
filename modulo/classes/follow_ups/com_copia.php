<?
require('../../../lib/segurancas.php');
require('../array_sistema/array_sistema.php');
?>
<html>
<title>.:: Com Cópia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<body>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <?
        for($i = 0; $i < $_POST['qtde_com_copia']; $i++) {
            /*******************************Controle*******************************/
            /*Só a partir do Segundo registro do Loop que essa variável $com_copias_selecionadas 
            estará abastecida, porque a minha real intenção aqui é de não apresentar no próximo Loop 
            o Funcionário / Representante que foi apresentado no Loop Anterior ...*/
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
                //SQL 1) Listagem de todos os Funcionários que possuem E-mail Interno e que trabalham na Empresa
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
                if(($i + 1) == $_POST['qtde_com_copia']) {//Só mostro estes ícones no último Registro do Loop ...
            ?>
            &nbsp;
            <img src = '../../../imagem/menu/adicao.jpeg' border='0' title='Incluir Com c&oacute;pia' alt='Incluir Com c&oacute;pia' width='16' height='16' onclick='incluir_com_copia()'>
            <?
                    //Se tivermos no mínimo 2 usuários "Com cópia para" é que será possível então excluir o E-mail ...
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