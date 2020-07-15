<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/comunicacao.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/variaveis/intermodular.php');

session_start('funcionarios');
if($id_emp2 == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/estornos/albafer/index.php';
}else if($id_emp2 == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/estornos/tool_master/index.php';
}else if($id_emp2 == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/estornos/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) CHEQUES(S) PENDENTE(S) PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='confirmacao'>SUA(S) CONTA(S) / QUITAÇÃO(ÕES) À RECEBER FORAM ESTORNADA(S) COM SUCESSO.</font>";

//Busca do Dólar e do Euro, que será utilizado mais abaixo ...
$valor_dolar = genericas::moeda_dia('dolar');
$valor_euro = genericas::moeda_dia('euro');

if($passo == 1) {
//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
    if(!empty($cmb_representante))      $condicao_representante = " AND cr.`id_representante` LIKE '$cmb_representante' ";
    if(empty($cmb_tipo_recebimento))    $cmb_tipo_recebimento = '%';
    if(!empty($cmb_uf))                 $condicao_uf            = " AND c.`id_uf` LIKE '$cmb_uf' ";
    if(empty($cmb_ano))                 $cmb_ano = '%';
    if(!empty($cmb_banco))              $condicao_banco = " AND cr.`id_banco` LIKE '$cmb_banco' ";

    $condicao_emp = " AND cr.`id_empresa` = '$id_emp2' ";
    if(!empty($chkt_somente_exportacao)) $condicao_exportacao = " AND c.`id_pais` <> 31" ;

    $data_retirada_60 = data::adicionar_data_hora(date('d/m/Y'), -60);
    $data_retirada_60 = data::datatodate($data_retirada_60, '-');

    if(!empty($txt_data_emissao_inicial)) {
        //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_emissao_final, 4, 1) != '-') {
            $txt_data_emissao_inicial   = data::datatodate($txt_data_emissao_inicial, '-');
            $txt_data_emissao_final     = data::datatodate($txt_data_emissao_final, '-');
        }
        //Aqui é para não dar erro de SQL
        $condicao1 = " AND cr.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
    }

    if(!empty($txt_data_vencimento_inicial)) {
        //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_vencimento_final, 4, 1) != '-') {
            $txt_data_vencimento_inicial    = data::datatodate($txt_data_vencimento_inicial, '-');
            $txt_data_vencimento_final      = data::datatodate($txt_data_vencimento_final, '-');
        }
        //Aqui é para não dar erro de SQL
        $condicao2 = " AND cr.`data_vencimento_alterada` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
    }

    if(!empty($txt_data_recebimento_inicial)) {
        //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_recebimento_final, 4, 1) != '-') {
            $txt_data_recebimento_inicial = data::datatodate($txt_data_recebimento_inicial, '-');
            $txt_data_recebimento_final = data::datatodate($txt_data_recebimento_final, '-');
        }
        $INNER_JOIN_QUITACOES = " INNER JOIN `contas_receberes_quitacoes` crq ON crq.id_conta_receber = cr.id_conta_receber AND crq.data between '$txt_data_recebimento_inicial' AND '$txt_data_recebimento_final' ";
    }

    if(!empty($txt_data_cadastro)) {
        //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_cadastro, 4, 1) != '-') $txt_data_cadastro = data::datatodate($txt_data_cadastro, '-');
    }
	
    //Essa adaptação só serve para o 1º SQL que não tem o relacional com a Tabela mesmo que não esteja preenchido o campo descrição da Conta ...
    if(empty($txt_descricao_conta) && !empty($txt_cliente)) {
        $txt_descricao_conta_sql1 = $txt_cliente;
    }else {
        $txt_descricao_conta_sql1 = $txt_descricao_conta;
    }
	
    $sql = "SELECT cr.*, c.`razaosocial`, c.`credito`, t.`recebimento`, t.`imagem`, 
            CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
            FROM `contas_receberes` cr 
            $INNER_JOIN_QUITACOES 
            INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%' OR cr.`descricao_conta` LIKE '%$txt_descricao_conta_sql1%') AND c.`ativo` = '1' AND c.`bairro` LIKE '%$txt_bairro%' AND c.`cidade` LIKE '%$txt_cidade%' $condicao_uf $condicao_exportacao 
            INNER JOIN `tipos_recebimentos` t ON t.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
            WHERE cr.`ativo` = '1' 
            AND cr.`status` IN (1, 2) 
            AND cr.`num_conta` LIKE '%$txt_numero_conta%' 
            AND SUBSTRING(cr.`data_vencimento_alterada`, 1, 4) LIKE '$cmb_ano' 
            AND cr.`semana` LIKE '%$txt_semana%' 
            AND SUBSTRING(cr.`data_sys`, 1, 10) LIKE '%$txt_data_cadastro%' 
            AND cr.`id_tipo_recebimento` LIKE '$cmb_tipo_recebimento' 
            $condicao_representante 
            $condicao_banco 
            $condicao_emp 
            $condicao 
            $condicao1 
            $condicao2 ORDER BY cr.`data_vencimento_alterada` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
/*******************************************************************************************/
    if($linhas == 0) {
?>
        <Script Language= 'Javascript'>
            window.location = '../classes/consultar.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Estornar Conta(s) Recebida(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Estornar Conta(s) Recebida(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Sem
        </td>
        <td>
            N.º da Conta
        </td>
        <td>
            Cliente / <br/>Descrição da Conta
        </td>
        <td>
            Cr
        </td>
        <td>
            Representante
        </td>
        <td>
            Data de <br/>Vencimento
        </td>
        <td>
            Tipo Rec.
        </td>
        <td>
            Praça de <br/>Recebimento
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor <br/>Recebido
        </td>
        <td>
            Valores <br/>Extras
        </td>
        <td>
            Valor <br>Reajustado
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            $url = "javascript:window.location = '../classes/consultar.php?passo=2&id_emp2=$id_emp2&id_conta_receber=".$campos[$i]['id_conta_receber']."'";
            //Essa variável iguala o tipo de moeda da conta à receber
            $moeda                      = $campos[$i]['simbolo'];
            $cliente                    = $campos[$i]['razaosocial'];
/***************************************************************************/
            $data_vencimento_alterada   = substr($campos[$i]['data_vencimento_alterada'], 0, 4).substr($campos[$i]['data_vencimento_alterada'], 5, 2).substr($campos[$i]['data_vencimento_alterada'], 8, 2);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url?>" width='12'>
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url?>" class='link'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                    <?=$campos[$i]['semana'];?>
                </font>
            </a>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if($campos[$i]['num_conta']=='') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['num_conta'];
                }
            ?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if(!empty($cliente) && $cliente != '&nbsp;') echo $cliente.' / ';
                if($campos[$i]['descricao_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['descricao_conta'];
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=$campos[$i]['credito'];?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                //Busca o Representante da Duplicata caso exista ...
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                if(count($campos_representante) > 0) {
                    echo $campos_representante[0]['nome_fantasia'];
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <img src = "<?='../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>" title="<?=$campos[$i]['recebimento'];?>" width='33' height='20' border='0'>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                //Busca o Banco da Duplicata caso exista ...
                $sql = "SELECT `banco` 
                        FROM `bancos` 
                        WHERE `id_banco` = '".$campos[$i]['id_banco']."' LIMIT 1 ";
                $campos_bancos = bancos::sql($sql);
                if(count($campos_bancos) > 0) {
                    echo $campos_bancos[0]['banco'];
                }else {
                    if($campos[$i]['id_tipo_recebimento'] == 7) {
                        echo 'PROTESTADO';
                    }else {
                        echo '&nbsp';
                    }
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if($campos[$i]['valor_pago'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
                echo 'R$ '.number_format($calculos_conta_receber['valores_extra'], 2, ',', '.');
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?='R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '../classes/consultar.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    //Aqui eu trago todos os recebimentos da conta à receber passada por parâmetro
    $sql = "SELECT * 
            FROM `contas_receberes_quitacoes` 
            WHERE id_conta_receber = '$_GET[id_conta_receber]' ORDER BY id_conta_receber_quitacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Estornar Conta(s) Recebida(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ ESTORNAR ESSE(S) RECEBIMENTO(S): ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Observação ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ ESTORNAR ESSE RECEBIMENTO !')
            return false
        }
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3'?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Parcela(s) Quitada(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <?
    //Busca do Número e do Tipo de Moeda da conta à Receber ...
        $sql = "SELECT c.razaosocial, cr.id_tipo_moeda, cr.num_conta, cr.valor, tm.simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                WHERE cr.`id_conta_receber` = '$_GET[id_conta_receber]' LIMIT 1 ";
        $campos_contas_receber  = bancos::sql($sql);
        $id_tipo_moeda          = $campos_contas_receber[0]['id_tipo_moeda'];
        $valor_conta            = $campos_contas_receber[0]['valor'];
        $moeda                  = $campos_contas_receber[0]['simbolo'].' ';

        if($id_tipo_moeda == 1) {//Real
            $valor_moeda_dia = '1.0000';
        }else if($id_tipo_moeda == 2) {//Dólar
            $valor_moeda_dia = $valor_dolar;
        }else if($id_tipo_moeda == 3) {//Euro
            $valor_moeda_dia = $valor_euro;
        }
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='11'>
            <font color='yellow' size='2'>
                Cliente:
            </font>
                <?=$campos_contas_receber[0]['razaosocial'];?>
            &nbsp;-&nbsp;
            <font color='yellow' size='2'>
            N.º da Conta:
            </font>
                <?=$campos_contas_receber[0]['num_conta'];?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Tipo de Rec.
        </td>
        <td>
            Conta Corrente /<br/>Agência / Banco
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Extra(s)
        </td>
        <td>
            Valor Recebido <br>da Parcela
        </td>
        <td>
            Valor Total <br>do Recebimento
        </td>
        <td>
            Valor à <br>Receber
        </td>
        <td>
            N.º Cheque
        </td>
        <td>
            Data
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $valor_recebido_parcela = $campos[$i]['valor'];
            $valor_recebido_total+= $campos[$i]['valor'];
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_conta_receber_quitacao[]' value="<?=$campos[$i]['id_conta_receber_quitacao']?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
        <?
            $sql = "SELECT recebimento 
                    FROM `tipos_recebimentos` 
                    WHERE `id_tipo_recebimento` = '".$campos[$i]['id_tipo_recebimento']."' LIMIT 1 ";
            $campos_tipo_recebimento = bancos::sql($sql);
            echo $campos_tipo_recebimento[0]['recebimento'];
        ?>
        </td>
        <td>
        <?
        //Aqui verifica a Conta Corrente, Agência e Banco na tabela relacional de Contas à Receber ...
            $sql = "SELECT cc.conta_corrente, a.nome_agencia, b.banco 
                    FROM `contas_receberes_quitacoes` crc 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = crc.`id_contacorrente` 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE crc.`id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            $campos_dados_gerais    = bancos::sql($sql);
            $dados                  = $campos_dados_gerais[0]['conta_corrente'].' / '.$campos_dados_gerais[0]['nome_agencia'].' / '.$campos_dados_gerais[0]['banco'];
            if($dados == ' /  / ') {
                echo '&nbsp;';
            }else {
                echo $dados;
            }
        ?>
        </td>
        <td>
            <?=$moeda.number_format($valor_conta, '2', ',', '.');?>
        </td>
        <td>
        <?
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
            //Arredondo p/ ficar com o valor mais preciso ...
            $valores_extra = round(round($calculos_conta_receber['valores_extra'] / $valor_moeda_dia, 3), 2);
            echo $moeda.number_format($valores_extra, '2', ',', '.');
        ?>
        </td>
        <td>
            <?=$moeda.number_format($valor_recebido_parcela, '2', ',', '.');?>
        </td>
        <td>
            <?=$moeda.number_format($valor_recebido_total, '2', ',', '.');?>
        </td>
        <td>
            <?=$moeda.number_format((($valor_conta + $valores_extra) - $valor_recebido_total), 2, ',', '.');?>
        </td>
        <td>
        <?
            //Aqui eu busco o número de cheque na tabela relacional de contas à receber ...
            $sql = "SELECT cc.num_cheque 
                    FROM `contas_receberes_quitacoes` crq 
                    INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` 
                    WHERE crq.`id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            $campos_cheque  = bancos::sql($sql);
            $num_cheque     = (count($campos_cheque) == 1) ? $campos_cheque[0]['num_cheque'] : '';
            echo $num_cheque;
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
        <?
            $sql = "SELECT `identificacao`, `observacao` 
                    FROM `follow_ups` 
                    WHERE `origem` = '4' 
                    AND `identificacao` = '$_GET[id_conta_receber]' LIMIT 1 ";
            $campos_follow_ups = bancos::sql($sql);
            echo $campos_follow_ups[0]['observacao'];
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location='../classes/consultar.php?<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_estornar' value='Estornar' title='Estornar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_conta_receber' value='<?=$_GET['id_conta_receber'];?>'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<input type='hidden' name='hdd_justificativa'>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    foreach($_POST['chkt_conta_receber_quitacao'] as $id_conta_receber_quitacao) {
//Talvez eu passe essas informações por e-mail ...
/*************************************Dados do Receb Estornado*************************************/
        $sql = "SELECT * 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber_quitacao` = '$id_conta_receber_quitacao' ORDER BY id_conta_receber_quitacao ";
        $campos_recebimentos = bancos::sql($sql);
        $linhas_recebimentos = count($campos_recebimentos);
        for($i = 0; $i < $linhas_recebimentos; $i++) {
//Zero essas variáveis p/ não dar problema na hora em que voltar do Loop ...
            $num_cheque = '';
            $dados = '';
/*Aqui eu busco o número de cheque na tabela relacional de contas à pagar*/
            $sql = "SELECT cc.num_cheque 
                    FROM `contas_receberes_quitacoes` crq 
                    INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` 
                    WHERE crq.`id_conta_receber_quitacao` = '$id_conta_receber_quitacao' LIMIT 1 ";
            $campos_cheques = bancos::sql($sql);
            if(count($campos_cheques) == 1) $num_cheque = $campos_cheques[0]['num_cheque'];
            //Verifico se essa parcela que foi recebida possui Banco e Conta Corrente atrelados ...
            $sql = "SELECT cc.conta_corrente, a.nome_agencia, b.banco 
                    FROM `contas_receberes_quitacoes` crc 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = crc.`id_contacorrente` 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE crc.`id_conta_receber_quitacao` = '$id_conta_receber_quitacao' LIMIT 1 ";
            $campos_dados_gerais = bancos::sql($sql);
            if(count($campos_dados_gerais) == 1) $dados = $campos_dados_gerais[0]['conta_corrente'].' / '.$campos_dados_gerais[0]['nome_agencia'].' / '.$campos_dados_gerais[0]['banco'];
/***********************************************************************/
            //Tipo de Recebimento da Parcela ...
            $sql = "SELECT recebimento 
                    FROM `tipos_recebimentos` 
                    WHERE `id_tipo_recebimento` = '".$campos_recebimentos[$i]['id_tipo_recebimento']."' LIMIT 1 ";
            $campos_tipo_recebimento    = bancos::sql($sql);
            $recebimento_estornado      = '<br><b>Tipo de Recebimento: </b>'.$campos_tipo_recebimento[0]['recebimento'].' <br><b>Banco / Conta Corrente: </b>'.$dados.' <br><b>Valor do Rec.: </b>R$ '.number_format($campos_recebimentos[$i]['valor'], '2', ',', '.').' <br><b>N.º Cheque: </b>'.$num_cheque.' <br><b>Data do Recebimento: </b>'.data::datetodata($campos_recebimentos[$i]['data'], '/').' <br><b>Observação: </b>'.$campos_recebimentos[$i]['observacao'].'<br>';
        }
        $retorno            = financeiros::estorno_conta_recebida($id_conta_receber_quitacao);//Função que estorna ...
        $status             = $retorno['status'];
        $id_conta_receber   = $retorno['id_conta_receber'];
        if($status == 0) $dados_recebimentos_estornados.= $recebimento_estornado;
/***************************************************************************************/
/********************************Estorno de Reembolso***********************************/
/***************************************************************************************/
/*Aqui eu faco um estorno de Comissao do Vendedor, caso este Recebimento tenha gerado 
um Reembolso e nesse exato momento esta tendo o seu recebimento cancelado ...*/
        $sql = "DELETE FROM `comissoes_estornos` WHERE `id_conta_receber_quitacao` = '$id_conta_receber_quitacao' LIMIT 1 ";
        bancos::sql($sql);
    }
    if($status == 0) {//Se foi possível o Estorno da Conta, então busco alguns dados da Conta à Pagar que foi estornada ...
/*************************************Dados da Conta Estornada*************************************/
        //Busca de Mais alguns Dados ...
        $sql = "SELECT c.razaosocial, cr.id_empresa, cr.num_conta, DATE_FORMAT(cr.data_emissao, '%d/%m/%Y') AS data_emissao, DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada, cr.valor, cr.valor_pago, cr.predatado, tr.recebimento, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
                INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
                WHERE cr.id_conta_receber = '$id_conta_receber' LIMIT 1 ";
        $campos_contas_receber      = bancos::sql($sql);
        $cliente                    = $campos_contas_receber[0]['razaosocial'];
        $num_conta                  = $campos_contas_receber[0]['num_conta'];
        $empresa                    = genericas::nome_empresa($campos_contas_receber[0]['id_empresa']);
        $data_vencimento_alterada   = $campos_contas_receber[0]['data_vencimento_alterada'];
        $moeda                      = $campos_contas_receber[0]['simbolo'];
        $valor                      = $moeda.number_format($campos_contas_receber[0]['valor'], 2, ',', '.');
        $valor_pago                 = $moeda.number_format($campos_contas_receber[0]['valor_pago'], 2, ',', '.');
        $recebimento                = $campos_contas_receber[0]['recebimento'];
        $calculos_conta_receber     = financeiros::calculos_conta_receber($id_conta_receber);
        $valor_receber              = $moeda.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');
        $conta_estornada            = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$num_conta.' <br><b>Data de Vencimento: </b>'.$data_vencimento_alterada.' <br><b>Tipo de Recebimento: </b>'.$recebimento.' <br><b>Valor da Conta: </b>'.$valor.' <br><b>Valor Pago: </b>'.$valor_pago.' <br><b>Valor Reajustado: </b>'.$valor_receber;
/*******************************************************************************************/
            //Busca do Login que está estornando a Conta ...
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_estornando   = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $mensagem_email = '<font color="darkblue"><b>Conta Estornada</b></font>';
        $mensagem_email.= $conta_estornada;
        $mensagem_email.= '<br><br><font color="darkblue"><b>Recebimentos Estornados</b></font>';
        $mensagem_email.= $dados_recebimentos_estornados;
        $mensagem_email.= '<br><b>Login: </b>'.$login_estornando.' - <b>Data e Hora: </b>'.date('d/m/Y H:i:s');
        $mensagem_email.= '<br><b>Justificativa: </b>'.$_POST['hdd_justificativa'];
//Aqui eu mando um e-mail informando quem e porque está estornando as Contas à Pagar ...
        $destino = $estornar_contas_receber;
/*Se foi possível o Estorno, então o Sistema dispara um e-mail informando a Dona Sandra quem foi o 
responsável pelo Estorno da Conta ...*/
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Estorno de Contas à Receber', $mensagem_email);
    }
?>
    <Script Language = 'JavaScript'>
        window.location= 'consultar.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Estornar Conta(s) Recebida(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Se a Data de Emissão estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_emissao_inicial.value != '' || document.form.txt_data_emissao_final.value != '') {
//Data de Emissão Inicial
        if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISSÃO INICIAL')) {
            return false
        }
//Data de Emissão Final
        if(!data('form', 'txt_data_emissao_final', '4000', 'EMISSÃO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_emissao_inicial    = document.form.txt_data_emissao_inicial.value
        var data_emissao_final      = document.form.txt_data_emissao_final.value
        data_emissao_inicial        = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
        data_emissao_final          = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
        data_emissao_inicial        = eval(data_emissao_inicial)
        data_emissao_final          = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA DE EMISSÃO FINAL INVÁLIDA !!!\n DATA DE EMISSÃO FINAL MENOR DO QUE A DATA DE EMISSÃO INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas é > do que 2 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_emissao_inicial, document.form.txt_data_emissao_final)
        if(dias > 730) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
    }
//Se a Data de Vencimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_vencimento_inicial.value != '' || document.form.txt_data_vencimento_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_vencimento_inicial = document.form.txt_data_vencimento_inicial.value
        var data_vencimento_final   = document.form.txt_data_vencimento_final.value
        data_vencimento_inicial     = data_vencimento_inicial.substr(6,4) + data_vencimento_inicial.substr(3,2) + data_vencimento_inicial.substr(0,2)
        data_vencimento_final       = data_vencimento_final.substr(6,4) + data_vencimento_final.substr(3,2) + data_vencimento_final.substr(0,2)
        data_vencimento_inicial     = eval(data_vencimento_inicial)
        data_vencimento_final       = eval(data_vencimento_final)

        if(data_vencimento_final < data_vencimento_inicial) {
            alert('DATA DE VENCIMENTO FINAL INVÁLIDA !!!\n DATA DE VENCIMENTO FINAL MENOR DO QUE A DATA DE VENCIMENTO INICIAL !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas é > do que 2 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_vencimento_inicial, document.form.txt_data_vencimento_final)
        if(dias > 730) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
    }
//Se a Data de Recebimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_recebimento_inicial.value != '' || document.form.txt_data_recebimento_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_recebimento_inicial', '4000', 'RECEBIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_recebimento_final', '4000', 'RECEBIMENTO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_inicial    = document.form.txt_data_recebimento_inicial.value
        var data_final      = document.form.txt_data_recebimento_final.value
        data_inicial        = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
        data_final          = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
        data_inicial        = eval(data_inicial)
        data_final          = eval(data_final)

        if(data_final < data_inicial) {
            alert('DATA DE RECEBIMENTO FINAL INVÁLIDA !!!\n DATA DE RECEBIMENTO FINAL MENOR DO QUE A DATA DE RECEBIMENTO INICIAL !')
            document.form.txt_data_recebimento_final.focus()
            document.form.txt_data_recebimento_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas é > do que 2 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_recebimento_inicial, document.form.txt_data_recebimento_final)
        if(dias > 730) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
            document.form.txt_data_recebimento_final.focus()
            document.form.txt_data_recebimento_final.select()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Estornar Conta(s) Recebida(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' size='40' maxlength='45' class='caixadetexto'> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descrição da Conta
        </td>
        <td>
            <input type='text' name='txt_descricao_conta' title='Digite a Descrição da Conta' size='40' maxlength='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name='txt_numero_conta' title='Digite o Número da Conta' size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name='txt_data_emissao_inicial' value='<?=data::adicionar_data_hora(date('d/m/Y'), -365);?>' title='Digite a Data de Emissão Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_emissao_final' value='<?=date('d/m/Y');?>' title='Digite a Data de Emissão Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
                Data de Vencimento
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_inicial' title='Digite a Data de Vencimento Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_vencimento_final' title='Digite a Data de Vencimento Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Recebimento
        </td>
        <td>
            <input type='text' name='txt_data_recebimento_inicial' title='Digite a Data de Recebimento Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_recebimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">  até&nbsp; 
            <input type='text' name='txt_data_recebimento_final' title='Digite a Data de Recebimento Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_recebimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data do Cadastro
        </td>
        <td>
            <input type='text' name='txt_data_cadastro' title='Digite a Data de Cadastro' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_cadastro&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencido Em
        </td>
        <td>
            <select name='cmb_ano' title='Selecione o Ano' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                for($i = 2004; $i <= date('Y') + 6; $i++) {
            ?>
                    <option value="<?=$i;?>"><?=$i;?></option>
            <?
                }
            ?>
            </select>
            Semana 
            <input type='text' name='txt_semana' title='Digite a Semana' size='5' maxlength='2' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Banco
        </td>
        <td>
            <select name='cmb_banco' title='Selecione o Banco' class='combo'>
            <?
                $sql = "SELECT id_banco, banco 
                        FROM `bancos` 
                        WHERE `ativo` = '1' ORDER BY banco ";
                echo combos::combo($sql);
            ?>
            </select> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Recebimento
        </td>
        <td>
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' class='combo'>
            <?
                $sql = "SELECT id_tipo_recebimento, recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY recebimento ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_exportacao' value='1' title='Somente Exportação' id='label1' class='checkbox'>
            <label for='label1'>Somente Exportação</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?}?>