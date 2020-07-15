<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/depto_pessoal.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../../../');

$vetor_tipos_vale = depto_pessoal::tipos_vale();
?>
<html>
<head>
<title>.:: Imprimir Folha de Pagamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<form name='form'>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    $travar_botao = 'N';//A princ�pio o Bot�o "Avan�ar" esta Liberado p/ Impress�o ...
    
    if($_POST['opt_descontar'] == 'PD') {//Para esse Tipo de Desconto, os Tipos de Vales s�o Menor ...
        /*Abaixo est�o os "tipos de Vales" que s�o obrigat�rios a ter pelo menos 1 registro na respectiva 
        "Data de Holerith" que foi selecionada e passada por par�metro pelo usu�rio ...

        '1' => 'Dia 20', 
               '5' => 'Conv�nio M�dico', 
               '6' =>  'Conv�nio Odontol�gico', 
               '7' =>  'Transporte', 
               '10' =>  'Mensalidade Sindical', 
               '15' =>  'Mensalidade MetalCred'
        * 
        * 
        *      */
        //$obrigatorios = array(1, 5, 6, 7, 10, 15);
    }else if($_POST['opt_descontar'] == 'PF') {//PF ...
        /*Abaixo est�o os "tipos de Vales" que s�o obrigat�rios a ter pelo menos 1 registro na respectiva 
        "Data de Holerith" que foi selecionada e passada por par�metro pelo usu�rio ...

        '1' => 'Dia 20', 
               '3' => 'Combust�vel', 
               '5' => 'Conv�nio M�dico', 
               '6' =>  'Conv�nio Odontol�gico', 
               '7' =>  'Transporte', 
               '9' =>  'Celular', 
               '10' =>  'Mensalidade Sindical', 
               '15' =>  'Mensalidade MetalCred'
        * 
        * 
        *      */
        //$obrigatorios = array(1, 3, 5, 6, 7, 9, 10, 15);
    }else {
?>
    <tr class='atencao' align='center'>
        <td>
            SELECIONE UM TIPO DE DESCONTAR.
        </td>
    </tr>
<?
        exit;
    }

    for($i = 1; $i <= count($vetor_tipos_vale); $i++) {
?>
    <tr class='linhanormal'>
        <td>
        <?
            echo utf8_encode($vetor_tipos_vale[$i]);
            /*Todo tipo de Vale que � obrigat�rio, precisa ter pelo menos 1 Vale gerado na respectiva Data 
            de Holerith selecionada pelo Usu�rio ...*/
            if(in_array($i, $obrigatorios)) {
                echo '<font color="red"><b> (Obrigat&oacute;rio)</b></font>';
                
                /*Aqui eu verifico se o Tipo de Vale do Loop que � obrigat�rio tem pelo menos 1 registro 
                na respectiva Data de Holerith selecionada pelo Usu�rio ...*/
                $sql = "SELECT `id_vale_dp` 
                        FROM `vales_dps` 
                        WHERE `tipo_vale` = '$i' 
                        AND `data_debito` = '$_POST[cmb_data_holerith]' LIMIT 1 ";
                $campos_vale_dp = bancos::sql($sql);
                if(count($campos_vale_dp) == 1) {//Tem pelo menos 1 Registro ...
                    echo '<font color="darkblue"><b> OK</b></font>';
                }else {//N�o tem Registro ...
                    echo '<font color="darkred"><b> S/ REGISTRO</b></font>';
                    $travar_botao = 'S';
                }
            }
        ?>
        </td>
    </tr>
<?
    }
?>
</table>
<!--**************************Controle de Tela**************************-->
<input type='hidden' name='hdd_travar_botao' value='<?=$travar_botao;?>'>
<!--********************************************************************-->
</form>
</body>
</html>