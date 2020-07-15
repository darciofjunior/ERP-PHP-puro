<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">CAIXA DE COMPRA EXCLUÍDO COM SUCESSO.</font>';

/*********************************************************************************************/
if(!empty($_GET['id_caixa_compra'])) {//Exclusão das Faixa(s) de Desconto(s) do Cliente
    $sql = "DELETE FROM `caixas_compras` WHERE `id_caixa_compra` = '$_GET[id_caixa_compra]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
/*********************************************************************************************/

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}

//Essas variáveis serão utilizadas mais abaixo ...
$total_debito   = 0;
$total_credito  = 0;
?>
<html>
<head>
<title>.:: Caixa de Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}

function excluir(id_caixa_compra) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE REGISTRO ?')
    if(resposta == true) window.location = 'caixa_compras.php?id_caixa_compra='+id_caixa_compra
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Caixa de Compra(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Data Inicial:
            <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial   = data::adicionar_data_hora(date('d/m/Y'), -30);
                    $txt_data_final     = date('d/m/Y');
                }
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
    if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
        //Campos de Data ...
        $data_inicial = data::datatodate($txt_data_inicial, '-');
        $data_final = data::datatodate($txt_data_final, '-');
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
            <font color='yellow'>
                SALDO => 
            </font>
        </td>
        <td>
            <font color='yellow'>
            <?
                //Busco Todas as "Entradas e Saídas" que foram lançadas na "Tabela de Caixa de Compras" no Sistema ...
                $sql = "SELECT (SUM(`valor_credito`) - SUM(`valor_debito`)) AS total_entrada_saida 
                        FROM `caixas_compras` ";
                $campos_entrada_saida = bancos::sql($sql);
                $total_entrada_saida  = $campos_entrada_saida[0]['total_entrada_saida'];

                //Busco Todas as NFs de Compra que foram lançadas no Sistema ...
                $sql = "SELECT id_nfe 
                        FROM `nfe` 
                        WHERE `pago_pelo_caixa_compras` = 'S' ";
                $campos_nfe = bancos::sql($sql);
                $linhas_nfe = count($campos_nfe);
                for($i = 0; $i < $linhas_nfe; $i++) {
                    $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nfe[$i]['id_nfe'], 'NFC');
                    $total_nfe+= $calculo_total_impostos['valor_total_nota'];
                }
                //O cálculo do Saldo é: $total_entrada_saida da "Tabela de Caixa de Compras" - $total_nfe de Compras ...
                echo 'R$ '.number_format($total_entrada_saida - $total_nfe, 2, ',', '.');
            ?>
            </font>
        </td>
    </tr>
<?
        /******************************************************************************************/
        /********************************Parte de Entradas e Saídas********************************/
        /******************************************************************************************/
        /*Aqui eu listo as Entradas e Saídas que estão gravadas na tabela de "Caixa de Compras" no Período filtrado 
        pelo Usuário c/ a Marcação que foi feita pelos Compradores no Cabeçalho de NF "Pago pelo Caixa Compras" ...*/
        $sql = "SELECT cc.`id_caixa_compra`, cc.`id_funcionario`, cc.`valor_debito`, cc.`valor_credito`, 
                DATE_FORMAT(cc.`data_emissao`, '%d/%m/%Y') AS data_emissao, cc.observacao, f.`nome` 
                FROM `caixas_compras` cc 
                INNER JOIN `funcionarios` f ON f.`id_funcionario` = cc.`id_funcionario` 
                WHERE cc.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' ORDER BY cc.`data_emissao` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Entradas e Saídas
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            &nbsp;
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Observação
        </td>
        <td>
            Débito
        </td>
        <td>
            Crédito
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?
                /*Essas imanges de Alterar e Excluir só aparecem p/ o autor do Registro e ... 
                p/ o Roberto '62', Sandra '66' que são Diretores e Dárcio 98 porque programa ...*/
                if(($campos[$i]['id_funcionario'] == $_SESSION['id_funcionario']) || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98) {
            ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="html5Lightbox.showLightbox(7, 'alterar.php?id_caixa_compra=<?=$campos[$i]['id_caixa_compra'];?>')" alt='Alterar' title='Alterar'>
            &nbsp;
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir('<?=$campos[$i]['id_caixa_compra'];?>')" alt='Excluir' title='Excluir'>
            <?
                }
            ?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_debito'] != 0) echo 'R$ '.number_format($campos[$i]['valor_debito'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_credito'] != 0) echo 'R$ '.number_format($campos[$i]['valor_credito'], 2, ',', '.');
        ?>
        </td>
    </tr>
<?
                $total_debito+= $campos[$i]['valor_debito'];
                $total_credito+= $campos[$i]['valor_credito'];
            }
        }
        /******************************************************************************************/
        /**********************************Parte de NFs de Compras*********************************/
        /******************************************************************************************/
        /*Aqui eu listo as Notas Fiscais de Compras no Período filtrado pelo Usuário com a Marcação que foi feita
        pelos Compradores no Cabeçalho de Nota Fiscal "Pago pelo Caixa Compras" ...*/
        $sql = "SELECT e.`nomefantasia`, f.`razaosocial`, nfe.`id_nfe`, nfe.`num_nota`, 
                DATE_FORMAT(nfe.`data_emissao`, '%d/%m/%Y') AS data_emissao, nfe.`situacao`, 
                nfe.`importado_financeiro` 
                FROM `nfe` 
                INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                WHERE nfe.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND nfe.`pago_pelo_caixa_compras` = 'S' ORDER BY nfe.`data_emissao` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            NFs de Compras
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Empresa
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            N.º Nota Fiscal
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Débito
        </td>
        <td>
            Crédito
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <a href='../pedidos/nota_entrada/itens/index.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['num_nota'];?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Nota já liberada ...
            if($campos[$i]['situacao'] == 2) echo '<font color="red"><b> (Liberada)</b></font>';
            //Nota Importada no Financeiro ...
            if($campos[$i]['importado_financeiro'] == 'S') echo '<font color="darkblue" title="Importado no Financeiro" style="cursor:help"><b> (Import. Financ.)</b></font>';
        ?>
        </td>
        <td align='right'>
        <?
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nfe'], 'NFC');
            echo 'R$ '.number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
<?
                $total_debito+= $calculo_total_impostos['valor_total_nota'];
            }
        }
        /******************************************************************************************/
    }
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
            R$ <?=number_format($total_debito, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_credito, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            &nbsp;<!--Coloquei um espaço aqui e outro no fim p/ alinhar os botões ...-->
            <?
                //Esse botão de Incluir Entrada só aparece p/ o Roberto 62, Fábio Petroni 64, Sandra 66 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98) {
                    /*acao=E, representa que o Usuário estará dando uma Entrada no Caixa, isso foi criado p/ termos
                    somente um único arquivo de Inclusão, facilitando futuras manutenções ...*/
            ?>
            <input type='button' name='cmd_incluir_entrada' value='Incluir Entrada' title='Incluir Entrada' onclick="html5Lightbox.showLightbox(7, 'incluir.php?acao=E')" style='color:blue' class='botao'>
            <?
                }
                //Esse botão de Incluir Saída só aparece p/ a Gladys 14, Roberto 62, Fábio Petroni 64 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98) {
                    /*acao=S, representa que o Usuário estará dando uma Saída no Caixa, isso foi criado p/ termos
                    somente um único arquivo de Inclusão, facilitando futuras manutenções ...*/
            ?>
            <input type='button' name='cmd_incluir_saida' value='Incluir Saída' title='Incluir Saída' onclick="html5Lightbox.showLightbox(7, 'incluir.php?acao=S')" style='color:red' class='botao'>
            <?
                }
            ?>
            &nbsp;&nbsp;<!--Coloquei um espaço aqui e outro no fim p/ alinhar os botões ...-->
        </td>
    </tr>
</table>
</form>
</body>
</html>