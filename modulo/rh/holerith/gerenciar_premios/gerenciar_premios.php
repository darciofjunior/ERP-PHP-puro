<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>PR�MIO(S) VS REPRESENTANTE(S) INCLU�DO / ALTERADO COM SUCESSO.</font>";

//Procedimento normal de quando se carrega a Tela ...
$cmb_data_holerith = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_data_holerith'] : $_GET['cmb_data_holerith'];

if($_POST['hdd_salvar'] == 1) {
    /*Aqui eu renomeio essa vari�vel de $id_funcionario p/ $id_funcionario_rep porque j� existe uma vari�vel
    com esse nome na sess�o do Sistema, ent�o assim iria dar conflito*/
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_rep) {
        //Somente se um dos 4 pr�mios estiverem preenchidos que farei esse controle ...
        if(!empty($_POST['txt_premio_produtividade_trabalho'][$i]) || !empty($_POST['txt_premio_tdc'][$i]) || !empty($_POST['txt_premio_industrializados'][$i])) {
            //Primeiro eu verifico se j� existe esse $id_representante na Tabela ...
            $sql = "SELECT `id_funcionario_vs_holerith` 
                    FROM `funcionarios_vs_holeriths` 
                    WHERE `id_funcionario` = '$id_funcionario_rep' 
                    AND `id_vale_data` = '$_POST[cmb_data_holerith]' ";
            $campos_funcionario_holerith = bancos::sql($sql);
            if(count($campos_funcionario_holerith) == 0) {//Ainda n�o existe, ent�o eu gravo na Base de Dados ...
                $sql = "INSERT INTO `funcionarios_vs_holeriths` (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `premio_produtividade_trabalho`, `premio_tdc`, `premio_industrializados`) VALUES (NULL, '$id_funcionario_rep', '$_POST[cmb_data_holerith]', '".$_POST['txt_premio_produtividade_trabalho'][$i]."', '".$_POST['txt_premio_tdc'][$i]."', '".$_POST['txt_premio_industrializados'][$i]."') ";
            }else {//J� existe, sendo assim eu s� altero na Base de Dados ...
                $sql = "UPDATE `funcionarios_vs_holeriths` SET `premio_produtividade_trabalho` = '".$_POST['txt_premio_produtividade_trabalho'][$i]."', `premio_tdc` = '".$_POST['txt_premio_tdc'][$i]."', `premio_industrializados` = '".$_POST['txt_premio_industrializados'][$i]."' WHERE `id_funcionario_vs_holerith` = '".$campos_funcionario_holerith[0]['id_funcionario_vs_holerith']."' LIMIT 1 ";
            }
            bancos::sql($sql);
        }
    }
    $valor = 1;
}

//Busca de todos os Representantes ativos que est�o cadastrados no Sistema, mais que sejam Funcion�rios somente ...
$sql = "SELECT rf.`id_funcionario`, r.`nome_fantasia` 
        FROM `representantes` r 
        INNER JOIN `representantes_vs_funcionarios` rf ON rf.`id_representante` = r.`id_representante` 
        WHERE r.`ativo` = '1' ORDER BY r.`nome_fantasia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//N�o encontrou nenhum Representante cadastrado ...
    echo '<font face="Verdana, Geneva, Arial, Helvetica, sans-serif" color="red"><center><b>N�O EXISTE(M) REPRESENTANTE(S) CADASTRADO(S) NO SISTEMA !!!</b></center></font>';
    exit;
}
?>
<html>
<head>
<title>.:: Gerenciar Pr�mio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos   = document.form.elements
    var indice      = 0//Essa vari�vel me servir� p/ controlar somente as Caixas de Texto ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text' && elementos[i].name == 'txt_premio_produtividade_trabalho[]') {
            //Preparo as caixas ...
            document.getElementById('txt_premio_produtividade_trabalho'+indice).value   = strtofloat(document.getElementById('txt_premio_produtividade_trabalho'+indice).value)
            document.getElementById('txt_premio_tdc'+indice).value                      = strtofloat(document.getElementById('txt_premio_tdc'+indice).value)
            document.getElementById('txt_premio_industrializados'+indice).value         = strtofloat(document.getElementById('txt_premio_industrializados'+indice).value)
            indice++
        }
    }
    document.form.hdd_salvar.value = 1//Aqui � p/ gravar no BD os Pr�mios, isso se o usu�rio realmente quiser gerar ...
}

function incluir_data_holerith() {
    html5Lightbox.showLightbox(7, '../../class_data_holerith/incluir.php')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        var indice          = document.form.cmb_data_holerith.selectedIndex
        var data_holerith   = document.form.cmb_data_holerith.options[indice].text
        data_holerith       = data_holerith.substr(6, 4) + data_holerith.substr(3, 2) + data_holerith.substr(0, 2)
        
        html5Lightbox.showLightbox(7, '../../class_data_holerith/alterar.php?data='+data_holerith)
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='hdd_salvar' value='0'>
<!--*************************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Gerenciar Pr�mio(s) - 
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' onchange='document.form.submit()' class='combo'>
            <?
                if(empty($cmb_data_holerith)) {//Sugest�o apenas p/ a 1� vez, quando acaba de carregar a Tela ...
                    $sql = "SELECT `id_vale_data` 
                            FROM `vales_datas` 
                            WHERE `data` >= '".date('Y-m-d')."' LIMIT 1 ";
                    $campos_vale_data   = bancos::sql($sql);
                    $cmb_data_holerith  = $campos_vale_data[0]['id_vale_data'];
                }
                //Procedimento normal p/ abastecimento da Combo com os v�rios Registros ...
                $data_atual_menos_30 = data::adicionar_data_hora(date('d/m/Y'), -30);
                $data_atual_menos_30 = data::datatodate($data_atual_menos_30, '-');

                $sql = "SELECT `id_vale_data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_atual_menos_30' ORDER BY `data` ";
                echo combos::combo($sql, $cmb_data_holerith);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick='alterar_data_holerith()'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Representante
        </td>
        <td>
            Pr�mio Produtividade Trabalho (Pontos)
        </td>
        <td>
            Pr�mio TDC
        </td>
        <td>
            Pr�mio Industrializados
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
        <?
            /*Verifico se existe algum "Pr�mio desses 4 campos" que estejam cadastrados nessa tabela 
            "representantes_vs_comissoes" para o Representante do Loop na respectiva Data de Holerith ...*/
            $sql = "SELECT `premio_produtividade_trabalho`, `premio_tdc`, `premio_industrializados` 
                    FROM `funcionarios_vs_holeriths` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `id_vale_data` = '$cmb_data_holerith' LIMIT 1 ";
            $campos_representante_comissao = bancos::sql($sql);
            if(count($campos_representante_comissao) == 1) {
                $premio_produtividade_trabalho  = number_format($campos_representante_comissao[0]['premio_produtividade_trabalho'], 2, ',', '.');
                $premio_tdc                     = number_format($campos_representante_comissao[0]['premio_tdc'], 2, ',', '.');
                $premio_industrializados        = number_format($campos_representante_comissao[0]['premio_industrializados'], 2, ',', '.');
            }else {
                $premio_produtividade_trabalho  = '';
                $premio_tdc                     = '';
                $premio_industrializados        = '';
            }
        ?>
            <input type='text' name='txt_premio_produtividade_trabalho[]' id='txt_premio_produtividade_trabalho<?=$i;?>' value='<?=$premio_produtividade_trabalho;?>' title='Digite o Pr�mio Produtividade Trabalho' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='10' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_premio_tdc[]' id='txt_premio_tdc<?=$i;?>' value='<?=$premio_tdc;?>' title='Digite o Pr�mio TDC' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_premio_industrializados[]' id='txt_premio_industrializados<?=$i;?>' value='<?=$premio_industrializados;?>' title='Digite o Pr�mio Industrializados' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
    <pre>
    * S� exibe para inclus�o os Representantes que sejam Funcion�rios.
    </pre>
</pre>