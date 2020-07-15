<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PEDIDO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTE PEDIDO NÃO PODE SER EXCLUÍDO, ESTE É UM NUMERÁRIO IMPORTADO PELO FINANCEIRO.</font>";
$mensagem[4] = "<font class='erro'>ESTE PEDIDO NÃO PODE SER EXCLUÍDO, DEVIDO TER SIDO IMPORTADO PARA NOTA FISCAL.</font>";
$mensagem[5] = "<font class='erro'>ESTE PEDIDO NÃO PODE SER EXCLUÍDO, DEVIDO ESTAR VINCULADO A UMA OS.</font>";

if($passo == 1) {
/**********************************************************************************/
//Variáveis que eu vou estar utilizando p/ cadastrar o Follow-Up do Pedido ...
    $data_ocorrencia 	= date('Y-m-d H:i:s');
    $data_atual         = date('Y-m-d');
/**********************************************************************************/
//Disparo de Loop para exclusão dos Pedido(s) selecionado(s) ...
    foreach ($_POST['chkt_pedido'] as $id_pedido) {
        //Verifico se esse Pedido possui uma OS Atrelada ...
        $sql = "SELECT id_os 
                FROM `oss` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_os = bancos::sql($sql);
        if(count($campos_os) == 0) {//Não possui OS, posso excluir ...
            //Verifica se esse Pedido já contém algum item que foi importado em Nota Fiscal ...
            $sql = "SELECT ip.id_item_pedido 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido AND p.status > '0' 
                    WHERE ip.`id_pedido` = '$id_pedido' 
                    AND ip.`status` > '0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Ainda não existem itens em Nota Fiscal ...
/***********************************************************************/
/**************************Controle com Pedido**************************/
/***********************************************************************/
                //Vejo esse Pedido está importado no Financeiro ...
                $sql = "SELECT id_conta_apagar 
                        FROM `contas_apagares` 
                        WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                $campos_contas_apagares = bancos::sql($sql);
                if(count($campos_contas_apagares) == 0) {//Significa que não foi importado p/ o Financeiro ...
//1)
/************************Busca de Dados************************/
//Aqui eu trago alguns dados de Pedido p/ passar por e-mail via parâmetro ...
                    $sql = "SELECT p.id_empresa, p.tipo_nota, f.razaosocial 
                            FROM `pedidos` p 
                            INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                            WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
                    $campos_pedido = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
                    $id_empresa_pedido = $campos_pedido[0]['id_empresa'];
                    $empresa = genericas::nome_empresa($id_empresa_pedido);
                    $tipo = $campos_pedido[0]['tipo_nota'];
                    $tipo_pedido = ($tipo == 1) ? 'NF' : 'SGD';//Verifica o Tipo de Pedido ...
                    $fornecedor = $campos_pedido[0]['razaosocial'];
//Dados p/ enviar por e-mail ...
                    $complemento_justificativa.= '<br><b>Empresa: </b>'.$empresa.' ('.$tipo_pedido.') <br><b>Fornecedor: </b>'.$fornecedor.' <br><b>N.º do Pedido: </b>'.$id_pedido;
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver excluindo o Pedido de Compras, então o Sistema dispara um e-mail informando 
qual o Pedido que está sendo excluído ...
//-Aqui eu trago alguns dados de Pedido p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está excluindo o Pedido ...*/
                    $sql = "SELECT login 
                            FROM `logins` 
                            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
                    $campos_login 		= bancos::sql($sql);
                    $login_excluindo 	= $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
                    $txt_justificativa.= $complemento_justificativa.'<br><b>Login: </b>'.$login_excluindo.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$txt_observacao_justificativa;
//Aqui eu mando um e-mail informando quem e porque que exclui o Pedido ...
                    $destino    = $excluir_pedido_compras;
                    $mensagem   = $txt_justificativa;
                    comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Exclusão de Pedido de Compras', $mensagem);
//3)
/************************Exclusão************************/
//Excluindo os Itens do Pedido ...
                    $sql = "DELETE FROM `itens_pedidos` WHERE `id_pedido` = '$id_pedido' ";
                    bancos::sql($sql);
//Excluindo o Finciamento do Pedido ...
                    $sql = "DELETE FROM `pedidos_financiamentos` WHERE `id_pedido` = '$id_pedido' ";
                    bancos::sql($sql);
//Excluindo os Follow-UPs do Pedido ...
                    $sql = "DELETE FROM `follow_ups` WHERE `identificacao` = '$id_pedido' AND `origem` = '16' ";
                    bancos::sql($sql);
//Excluindo as Antecipações do Pedido ...
                    $sql = "DELETE FROM `antecipacoes` WHERE `id_pedido` = '$id_pedido' ";
                    bancos::sql($sql);
//Excluindo o Pedido ...
                    $sql = "DELETE FROM `pedidos` WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                    bancos::sql($sql);
                    $valor = 2;
                }else {//Significa que já foi importado p/ o Financeiro ... 
                    $valor = 3;
                }
            }else {//Pedido não pode ser excluído, já existem itens na Nota Fiscal ...
                $valor = 4;
            }
/********************************************************/
        }else {//Pedido não pode ser excluído, devido estar vinculado a uma OS ...
            $valor = 5;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php<?=$parametro?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var mensagem = '', valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
//Observação / Justificativa ...
        if(document.form.txt_observacao_justificativa.value == '') {
            alert('DIGITE A OBSERVAÇÃO / JUSTIFICATIVA !')
            document.form.txt_observacao_justificativa.focus()
            document.form.txt_observacao_justificativa.select()
            return false
        }
//Mensagem verificando se o Pedido realmente pode ser excluído ...
        var mensagem = confirm('CONFIRMA A EXCLUSÃO ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onSubmit="return validar()">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Excluir Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Pedido
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Empresa
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        $tp_nota = array('', 'NF', 'SGD');

        for($i = 0;  $i < $linhas; $i++) {
            $url = 'itens/itens.php?id_pedido='.$campos[$i]['id_pedido'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href='<?=$url?>' class='html5lightbox'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10),'/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            echo $campos[$i]['nomefantasia'].' ('.$tp_nota[$campos[$i]['tipo_nota']].')';
            if($campos[$i]['tipo_export'] == 'E') {
                echo '<font color="red"><b> (Exp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'I') {
                echo '<font color="red"><b> (Imp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'N') {
                echo '<font color="red"><b> (Nac)</b></font>';
            }
        ?>
        </td>
        <td>
            <input type='checkbox' name='chkt_pedido[]' value="<?=$campos[$i]['id_pedido'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhanormal">
        <td colspan='2'>
            <b>Observação / Justificativa:</b>
        </td>
        <td colspan='3'>
            <textarea name='txt_observacao_justificativa' cols='60' rows='2' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>