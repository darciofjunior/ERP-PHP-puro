<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">PROGRAMAÇÃO SEMANAL DE VISITA EXCLUÍDA COM SUCESSO.</font>';

/******************************Código de Exclusão******************************/
if(!empty($_GET['id_programacao_semanal_visita'])) {
    $sql = "DELETE FROM `programacoes_semanais_visitas` WHERE id_programacao_semanal_visita = '".$_GET['id_programacao_semanal_visita']."'";
    bancos::sql($sql);
    $valor = 2;
}
/******************************************************************************/

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmb_representante  = $_POST['cmb_representante'];
    $txt_cliente        = $_POST['txt_cliente'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final 	= $_GET['txt_data_final'];
    $cmb_representante 	= $_GET['cmb_representante'];
    $txt_cliente        = $_GET['txt_cliente'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}

$data_atual                 = date('d/m/Y');
$data_atual_menos_15dias    = data::datatodate(data::adicionar_data_hora($data_atual, -15), '-');
?>
<html>
<head>
<title>.:: Alterar / Excluir Programação(ões) Semanal(is) de Visita(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_programacao_semanal_visita) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == true) window.location = 'alterar_excluir.php?id_programacao_semanal_visita='+id_programacao_semanal_visita
}

function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial 	= document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Representante ...
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar / Excluir Programação(ões) Semanal(is) de Visita(s)
            <p/>
            Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {//Sugestão p/ a primeira vez que se carrega a Tela ...
                    $txt_data_inicial 	= date('01/m/Y');
                    $txt_data_final 	= date('t/m/Y');
                }
                $data_inicial   = data::datatodate($txt_data_inicial, '-');
                $data_final     = data::datatodate($txt_data_final, '-');
            ?>
            <input type = 'text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type = 'text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;-&nbsp;
            Representante:
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                /*Observação: O Nishimura é o único Representante que pode ter acesso a todos Representantes 
                porque ele é Gerente de Vendas ...*/
            
                //Aqui eu verifico se o Funcionário que está Logado no Sistema é um Representante ...
                $sql = "SELECT id_representante 
                        FROM `representantes_vs_funcionarios` 
                        WHERE id_funcionario = '$_SESSION[id_funcionario]' LIMIT 1 ";
                $campos_representante   = bancos::sql($sql);
                if(count($campos_representante) == 1 && $_SESSION['id_funcionario'] != 136) {//Sim é um Representante e diferente do Nishimura ...
                    //Aqui eu busco o próprio Representante logado + os seus subordinados no 2º SQL ...
                    $sql = "(SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                            FROM `representantes` 
                            WHERE `id_representante` = '".$campos_representante[0]['id_representante']."') 
                            UNION 
                            (SELECT r.`id_representante`, CONCAT(r.`nome_fantasia`, ' / ', r.`zona_atuacao`) AS dados 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                            WHERE rs.`id_representante_supervisor` = '".$campos_representante[0]['id_representante']."') 
                            ORDER BY dados ";
                }else {//Não é Representante, então trago todos os Representantes que estão cadastrados no Sistema ...
                    $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' ORDER BY dados ";
                }
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
/*****************Se já submeteu então*****************/
if(!empty($cmd_consultar)) {
    $where = "WHERE psv.`id_representante` LIKE '$cmb_representante' AND psv.`data_registro` BETWEEN '$data_inicial' AND '$data_final' ";   

    //SQL principal
    $sql = "SELECT cc.`nome`, psv.`id_programacao_semanal_visita`, DATE_FORMAT(psv.`data_registro`, '%d/%m/%Y') AS data_registro, 
            IF(psv.`periodo` = 'M', 'MANHÃ', 'TARDE') AS periodo, psv.`perspectiva_periodo`, psv.`comentario`, 
            IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente  
            FROM `programacoes_semanais_visitas` psv 
            LEFT JOIN `clientes` c ON c.`id_cliente` = psv.`id_cliente` 
            LEFT JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = psv.`id_cliente_contato` 
            $where 
            ORDER BY psv.`data_registro`, psv.`periodo` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[1];?>
        <td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Data do Registro
        </td>
        <td>
            Dia da Semana
        </td>
        <td>
            Período
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cliente Contato
        </td>
        <td>
            Perspectivade <br/>de Período R$
        </td>
        <td>
            Comentário
        </td>
        <td width='30'>
            &nbsp;
        </td>
        <td width='30'>
            &nbsp;
        </td>
    </tr>
<?
        //Esse vetor irá me auxiliar mais abaixo ...
        $vetor_semana = array('DOMINGO', 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO');

        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['data_registro'];?>
        </td>
        <td>
            <?=$vetor_semana[data::dia_semana($campos[$i]['data_registro'])];?>
        </td>
        <td>
            <?=$campos[$i]['periodo'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['perspectiva_periodo'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['comentario'];?>
        </td>
        <td>
        <?
            //Só posso alterar registros que foram lançados dentro dos últimos 15 dias ...
            if(data::datatodate($campos[$i]['data_registro'], '-') >= $data_atual_menos_15dias) {
        ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/programacao_semanal_visita/alterar.php?id_programacao_semanal_visita=<?=$campos[$i]['id_programacao_semanal_visita'];?>')" alt='Alterar Programação Semanal de Visita' title='Alterar Programação Semanal de Visita'>
        <?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            //Só posso excluir registros que foram lançados dentro dos últimos 15 dias ...
            if(data::datatodate($campos[$i]['data_registro'], '-') >= $data_atual_menos_15dias) {
        ?>
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_programacao_semanal_visita'];?>')" alt='Excluir Programação Semanal de Visita' title='Excluir Programação Semanal de Visita'>
        <?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='9'>
            &nbsp;
        </td>
    </tr>
<?
    }
}
?>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só é possível fazer "Alteração" ou "Exclusão" do(s) Registro(s) que foi(ram) lançado(s) até nos últimos 15 dias, ou seja à partir de <b><?=data::datetodata($data_atual_menos_15dias, '/');?></b>.
</pre>