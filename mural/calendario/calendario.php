<?
require('../../lib/segurancas.php');
require('../../lib/menu/menu.php');
require('../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>SEMANA INCLUÍDA COM SUCESSO.</font>";

if(!empty($_POST['cmb_ano'])) {
    $cont       = 0;
    $desvio     = 0;
    $semana     = 0;
    $verifica   = 0;
    $mes_atual  = 0;

    for($i = 1; $i <= 12; $i++) {
        $qtde_dia = date('t', mktime(0,0,0, $i, 1, $_POST['cmb_ano']));
        for($j = 1; $j <= $qtde_dia; $j++) {
            if(date('w', mktime(0,0,0, $i, $j, $_POST['cmb_ano'])) == 6) {
                $fim = $j;
                $semana ++;
                $cont = 0;
                $desvio = 0;
                if($verifica == 0) {
                    //Verifica se já existe alguma cadastrada no Ano Selecionado pelo Usuário ...
                    $sql = "SELECT id_semana 
                            FROM `semanas` 
                            WHERE SUBSTRING(`dia_inicio`, 1, 4) = '$_POST[cmb_ano]' LIMIT 1 ";
                    $campos = bancos::sql($sql);
                    if(count($campos) == 1) {//Semana já existe p/ o Ano Selecionado pelo Usuário ...
                        $i = 13;//Já faço isso p/ sair fora do Loop e não cadastrar mais nada ...
                    }else {//Semana não existe p/ o Ano Selecionado pelo Usuário, sendo assim vou cadastrá-la ...
                        if($inicio == 0) $inicio = 1;
                        $dia_inicio = $_POST['cmb_ano'].'-'.$i.'-'.$inicio;
                        $dia_fim    = $_POST['cmb_ano'].'-'.$i.'-'.$fim;
                        $verifica   = 1;
                        $mes_atual  = $i;
                        $sql = "INSERT INTO `semanas` (`id_semana`, `semana`, `dia_inicio`, `dia_fim`, `data_sys`) VALUES (NULL, '$semana', '$dia_inicio', '$dia_fim', '".date('Y-m-d H:i:s')."') ";
                        bancos::sql($sql);
                        $valor = 1;
                    }
                }else {
                    if($mes_atual != $i) {
                        if($inicio == 1) {
                            $dia_inicio = $_POST['cmb_ano'].'-'.$i.'-'.$inicio;
                        }else {
                            $dia_inicio = $_POST['cmb_ano'].'-'.$mes_atual.'-'.$inicio;
                        }
                        $mes_atual = $i;
                    }else {
                        $dia_inicio = $_POST['cmb_ano'].'-'.$i.'-'.$inicio;
                    }
                    $dia_fim = $_POST['cmb_ano'].'-'.$mes_atual.'-'.$fim;
                    $sql = "INSERT INTO `semanas` (`id_semana`, `semana`, `dia_inicio`, `dia_fim`, `data_sys`) VALUES (NULL, '$semana', '$dia_inicio', '$dia_fim', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                    $valor = 1;
                }
            }else {
                if($desvio == 0) {
                    $inicio = $j;
                    $desvio = 1;
                }
                if(($i == 12) && ($j == $qtde_dia)) {
                    $fim = $j;
                    $semana++;
                    $dia_inicio = $_POST['cmb_ano'].'-'.$i.'-'.$inicio;
                    $dia_fim    = $_POST['cmb_ano'].'-'.$i.'-'.$fim;
                    $sql = "INSERT INTO `semanas` (`id_semana`, `semana`, `dia_inicio`, `dia_fim`, `data_sys`) VALUES (NULL, '$semana', '$dia_inicio', '$dia_fim', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                    $valor = 1;
                }
            }
        }
    }
}
?>
<html>
<title>.:: Calend&aacute;rio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!combo('form', 'cmb_ano', '', 'SELECIONE O ANO !')) {
        return false
    }
}
</Script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='40%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            CALENDÁRIO DO ANO DE
            <select name='cmb_ano' onchange='document.form.submit()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
<?
                $ano = 2100;
                for($i = 2000; $i <= $ano; $i++) {
                    if(!empty($_POST['cmb_ano'])) {
                        if($i == $_POST['cmb_ano']) {
                            $ano_selecionado = $_POST['cmb_ano'];
?>
                <option value='<?=$i;?>' selected><?=$i;?></option>
                <?
                        }else {
                ?>
                <option value='<?=$i;?>'><?=$i;?></option>
<?
                        }
                    }else {
                        if($i == date('Y')) {
                            $ano_selecionado = $i;
?>
                <option value='<?=$i;?>' selected><?=$i;?></option>
<?
                        }else {
?>
                <option value='<?=$i;?>'><?=$i;?></option>
<?
                        }
                    }
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            DIA INICIAL
        </td>
        <td>
            DIA FINAL
        </td>
        <td>
            SEMANA
        </td>
    </tr>
<?
        //Busca todas as Semanas cadastradas de acordo com o Ano cadastrado ...
	$sql = "SELECT * 
                FROM `semanas` 
                WHERE SUBSTRING(`dia_inicio`, 1, 4) = '$ano_selecionado' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i ++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=data::datetodata($campos[$i]['dia_inicio'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['dia_fim'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>