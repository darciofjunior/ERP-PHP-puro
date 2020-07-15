<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../');
$mensagem[1] = 'DATA(S) DE HOLERITH INCLUIDA COM SUCESSO !';

if(!empty($_POST['txt_data_holerith'])) {
//Em cima dessa Data última que foi gerada, eu gero mais 12 novas datas ...
    $ultimo_mes = substr($_POST['txt_data_holerith'], 3, 2);
    $ultimo_ano = substr($_POST['txt_data_holerith'], 6, 4);
    $novo_mes   = ($ultimo_mes + 1);
    
    for($i = 0; $i < 12; $i++) {
        if($novo_mes < 10) {
            $novo_mes = '0'.$novo_mes;
        }else if($novo_mes == 13) {//Não existe 13 meses por ano ...
            $novo_mes = '01';//Volta-se para o mês de Janeiro ...
            $ultimo_ano++;//Acrescenta-se mais Hum no Ano ...
        }
        $nova_data_holerith = $ultimo_ano.'-'.$novo_mes.'-05';
        $sql = "INSERT INTO `vales_datas` (`id_vale_data`, `data`) VALUES (NULL, '$nova_data_holerith') ";
        bancos::sql($sql);
        
        $novo_mes++;//Acrescenta-mais Hum no novo mês ...
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[1];?>')
        parent.document.form.passo.onclick()
        parent.fechar_pop_up_div()
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Incluir Data de Holerith ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var resposta = confirm('SERÃO GERADAS MAIS 12 DATAS DE HOLERITH !!!\n\nTEM CERTEZA DE QUE DESEJA CONTINUAR ?')
    if(resposta == true) {
        document.form.txt_data_holerith.disabled = false
    }else {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Data de Holerith
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            Última Data de Holerith Gerada:
        </td>
        <td>
            <?
                //SQL que busca a última data de Holerith que foi gerada no Sistema ...
                $sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y') AS data_holerith 
                        FROM `vales_datas` 
                        ORDER BY data DESC LIMIT 1 ";
                $campos_holerith = bancos::sql($sql);
            ?>
            <input type='text' name='txt_data_holerith' value='<?=$campos_holerith[0]['data_holerith'];?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            *** Ao clicar no botão salvar serão geradas mais 12 Datas de Holerith.
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>