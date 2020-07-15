<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
session_start('funcionarios');

$mes_atual = date('m') - 1;
$ano_atual = date('Y');
$vetor_meses = array('1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');

//Busca de todos os Logins que estão cadastrados no Sistema ...
$sql = "SELECT `id_login`, `id_funcionario`, `login`, `tipo_login`, `ativo` 
        FROM `logins` 
        WHERE `ativo` = '1' 
        ORDER BY `login` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Alterar Login(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar Login(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Login
        </td>
        <td>
            Módulo
        </td>
        <td>
            Data e Hora
        </td>
        <td>
            Status do Funcionário
        </td>
        <td>
            Status
        </td>
        <td>
            Tipo de Login
        </td>
        <td>
            Tipo de Acesso
        </td>
    </tr>
<?
//Variáveis de Controle
	$logado = 0;
	$deslogado = 0;

        for($i = 0; $i < $linhas; $i++) {
            //Busco dados de Funcionário e Empresa desse Login se é que este é um Funcionário, ou seja 99% dos casos com exceção dos Representantes ...
            $sql = "SELECT e.`nomefantasia`, f.`nome`, f.`status` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    WHERE f.`id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            
            //Busca do último acesso de cada usuário na Tabela de Logs ...
            $sql = "SELECT * 
                    FROM logs_".date('Y').".logs_logins_logout_".$vetor_meses[(int)$mes_atual]." 
                    WHERE `id_login` = ".$campos[$i]['id_login']." ORDER BY `id_log_login` DESC LIMIT 1 ";
            $campos_logs = bancos::sql($sql);
            //Controle com os usuários logados e deslogados no Sistema ...
            if($campos_logs[0]['status'] == 1) {//Se o usuário estiver logado ...
                $status = 'Logado';
                $color = '#6600FF';
                $logado++;
            }else {
                $status = 'Deslogado';
                $color = '#FF3333';
                $deslogado++;
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <font color="<?=$color;?>">
                <?=$campos_funcionario[0]['nomefantasia'];?>
            </font>
        </td>
        <td align='left'>
            <font color="<?=$color;?>">
                <?=$campos_funcionario[0]['nome'];?>
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
                <?=$campos[$i]['login'];?>
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
            <?
//Controle com os usuários logados e deslogados no Sistema ...
                if($campos_logs[0]['status'] == 1) {//Se o usuário estiver logado ...
//Verifico qual o módulo acessado pelo usuário ...
                    if($campos_logs[0]['id_modulo'] == 1) {
                        echo 'Sistema';
                    }else if($campos_logs[0]['id_modulo'] == 2) {
                        echo 'Depto Pessoal';
                    }else if($campos_logs[0]['id_modulo'] == 3) {
                        echo 'Compras';
                    }else if($campos_logs[0]['id_modulo'] == 12) {
                        echo 'Faturamento';
                    }else if($campos_logs[0]['id_modulo'] == 14) {
                        echo 'Producao / Custo';
                    }else if($campos_logs[0]['id_modulo'] == 15) {
                        echo 'Financeiro';
                    }else if($campos_logs[0]['id_modulo'] == 17) {
                        echo 'Vendas';
                    }else if($campos_logs[0]['id_modulo'] == 18) {
                        echo 'Principal';
                    }
                }else {//Caso deslogado
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
            <?
                if($campos_logs[0]['data'] == '0000-00-00 00:00:00') {
                    echo '-';
                }else {
                    if(empty($campos_logs[0]['data'])) {
                        echo '-';
                    }else {
                        echo data::datetodata(substr($campos_logs[0]['data'], 0, 10), '/').' - '.substr($campos_logs[0]['data'], 11, 8);
                    }
                }
            ?>
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
            <?
                //Criei esse vetor aqui porque achei + facil, pra na hora de comparar com o valor q retorna do Banco
                $vetor_status = array('Férias', 'Ativo', 'Afastado', 'Demitido');
                echo $vetor_status[$campos_funcionario[0]['status']];
            ?>    
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
                <?=$status;?>
            </font>
        </td>
        <td>
            <font color="<?=$color;?>">
                <?=$campos[$i]['tipo_login'];?>
            </font>
        </td>
        <td>
            <!--Tenho que renomear esse variável de "id_login" para "id_login_loop" porque temos um id_login na Sessão 
            e daí pode dar conflito-->
            <a href='alterar_dados_login.php?id_login_loop=<?=$campos[$i]['id_login'];?>' class='html5lightbox'>
                <font color="<?=$color;?>">
                <?
                    if($campos[$i]['ativo'] == 0) {
                        echo 'SEM ACESSO';
                    }else if($campos[$i]['ativo'] == 1) {
                        echo 'ACESSO INTERNO';
                    }else if($campos[$i]['ativo'] == 2) {
                        echo 'ACESSO INTERNO E EXTERNO';
                    }
                ?>
                </font>
            </a>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
            <font color='#6600FF'>
                <b>Total de Usuário(s) Logado(s)</b>
            </font>
        </td>
        <td colspan='5' align='center'>
            <font color='#FF3333'>
                <b>Total de Usuário(s) Deslogado(s)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
            <font color='#6600FF'>
                <?=$logado;?>
            </font>
        </td>
        <td colspan='5'>
            <font color='#FF3333'>
                <?=$deslogado;?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>