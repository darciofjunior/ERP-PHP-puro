<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
?>
<html>
<head>
<title>.:: Lista de Aniversariante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='2' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Lista de Aniversariante(s)
            &nbsp;-&nbsp;
            <?
                //Verifico se id_funcionario logado trabalha no Departamento de RH ...
                $sql = "SELECT id_departamento 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
                $campos_depto = bancos::sql($sql);
                /*Esse botão só aparecerá p/ os usuários Roberto 62, Sandra 66, Dárcio 98 "porque programa" e p/ a pessoa 
                que trabalha do Departamento "Recursos Humanos" ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98 || $campos_depto[0]['id_departamento'] == 24) {
            ?>
            <input type='button' name='cmd_gerar_listagem' value='Gerar Listagem Impressa' title='Gerar Listagem Impressa' onclick="html5Lightbox.showLightbox(7, 'gerar_listagem_impressa.php')" class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
<?
$meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

for($i = 1; $i <= 12; $i++) {
?>
    <tr class='linhadestaque'>
        <td>
        <?
            $mes = ($i < 10) ? '0'.$i : $i;
            echo $meses[$i];
        ?>
        </td>
    </tr>
<?
//Trago somente os funcionarios que ainda trabalham aqui na Empresa ...
    $sql = "SELECT c.cargo, e.nomefantasia, f.nome, f.data_nascimento 
            FROM `funcionarios` f 
            INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
            WHERE SUBSTRING(f.`data_nascimento`, 6, 2) = '$mes' 
            AND `status` < '3' ORDER BY SUBSTRING(f.data_nascimento, 7, 4) ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
    <tr class='linhanormal'>
        <td>
            <table width='100%' border='1'>
<?
    $cabecalho = 0;
    if($linhas == 0) {
?>
        <tr class='linhanormal'>
            <td colspan='4'>
                <font color='#FF0000'>
                    Não há aniversariantes neste mês
                </font>
            </td>
        </tr>
<?
    }else {
        for($j = 0; $j < $linhas; $j++) {
            $empresa    = $campos[$j]['nomefantasia'];
            $dia        = substr($campos[$j]['data_nascimento'], 8, 2);
            if($cabecalho == 0) {
?>
                <tr class='linhanormaldestaque' align='center'>
                    <td width='25%'>
                        <font color='#FF0000'>
                            <b>Nome</b>
                        </font>
                    </td>
                    <td width='25%'>
                        <font color='#FF0000'>
                            <b>Cargo</b>
                        </font>
                    </td>
                    <td width='25%'>
                        <font color='#FF0000'>
                            <b>Dia</b>
                        </font>
                    </td>
                    <td width='25%'>
                        <font color='#FF0000'>
                            <b>Empresa</b>
                        </font>
                    </td>
                </tr>
<?
                $cabecalho = 1;
            }
?>
                <tr class='linhanormal'>
                    <td>
                        <?=$campos[$j]['nome'];?>
                    </td>
                    <td>
                        <?=$campos[$j]['cargo'];?>
                    </td>
                    <td align='center'>
                        <b><?=$dia;?></b>
                    </td>
                    <td align='center'>
                        <?=$empresa;?>
                    </td>
                </tr>
<?
        }
    }
?>
            </table>
        </td>
    </tr>
<?}?>
</table>
</html>