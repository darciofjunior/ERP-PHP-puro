<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/calculos.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/intermodular.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
require('../../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) NOTA(S) FISCAL(IS) NESSA CONDIÇÃO.</font>";
$mensagem[3] = "<font class='confirmacao'>CONTA À RECEBER INCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
//Aqui traz os dados da Nota Fiscal passada por parâmetro ...
    $sql = "SELECT nfs.*, c.`id_pais`, c.`razaosocial` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    $campos = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
    $id_empresa_nota            = $campos[0]['id_empresa'];
    $suframa			= $campos[0]['suframa'];
    $id_pais 			= $campos[0]['id_pais'];
    $id_cliente 		= $campos[0]['id_cliente'];
    $razaosocial		= $campos[0]['razaosocial'];
    $numero_nf 			= faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');
    $ano_emissao		= substr($campos[0]['data_emissao'], 0, 4);

//Aqui verifica o Tipo de Nota
    if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
        $nota_sgd   = 'N';//var surti efeito lá embaixo
        $tipo_nota  = ' (NF)';
    }else {
        $nota_sgd   = 'S'; //var surti efeito lá embaixo
        $tipo_nota  = ' (SGD)';
    }
    
    $forma_pagamento        = $campos[0]['forma_pagamento'];

    if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');
//Prazos
    $valor1                 = $campos[0]['valor1'];
    $vencimento1            = $campos[0]['vencimento1'];
    $valor2                 = $campos[0]['valor2'];
    $vencimento2            = ($campos[0]['vencimento2'] == 0) ? '' : $campos[0]['vencimento2'];
    $valor3                 = $campos[0]['valor3'];
    $vencimento3            = ($campos[0]['vencimento3'] == 0) ? '' : $campos[0]['vencimento3'];
    $valor4                 = $campos[0]['valor4'];
    $vencimento4            = ($campos[0]['vencimento4'] == 0) ? '' : $campos[0]['vencimento4'];
    $valor_dolar_nota       = $campos[0]['valor_dolar_dia'];
    $observacao             = $campos[0]['observacao'];
//Aqui eu já tenho o cálculo para o valor das duplicatas
    $valor_duplicata        = faturamentos::valor_duplicata($_GET['id_nf'], $suframa, $nota_sgd, $id_pais);
/***************Segurança de Notas Fiscais e Duplicatas***************/
//Aqui eu verifico se o Somatório das Duplicatas da NF estão diferente do Valor Total da NF ...
    $calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
    $valor_total_nota       = $calculo_total_impostos['valor_total_nota'];
    $total_duplicatas       = round($valor1 + $valor2 + $valor3 + $valor4, 2);
/*********************************************************************/    
?>
<html>
<head>
<title>.:: Liberar NF de Vendas (Automático) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de Recebimento
    if(!combo('form', 'cmb_tipo_recebimento', '', 'SELECIONE UM TIPO DE RECEBIMENTO !')) {
        return false
    }
<?
/*P/ as empresas Albafer e Tool Master, traz a caixa de texto de descrição
da conta*/
    if($id_emp != 4) {
?>
        if(document.form.txt_descricao_conta.value != '') {
            if(!texto('form','txt_descricao_conta', '2', '-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ','DESCRIÇÃO DA CONTA','1')) {
                return false
            }
        }
<?
    }else {
?>
        if(!combo('form', 'cmb_descricao_conta', '', 'SELECIONE A DESCRIÇÃO DA CONTA !')) {
            return false
        }
<?
    }
?>
//Taxa de Juros
    if(!texto('form', 'txt_taxa_juros', '2', '0123456789,.', 'TAXA DE JUROS', '1')) {
        return false
    }
/***************Segurança de Notas Fiscais e Duplicatas***************/
//Aqui eu verifico se o Somatório das Duplicatas da NF estão diferente do Valor Total da NF ...
    var id_pais             = eval('<?=$id_pais;?>')
    var valor_total_nota    = eval('<?=$valor_total_nota;?>')
    var total_duplicatas    = eval('<?=$total_duplicatas;?>')
    
    //Nunca poderemos ter um Valor Total de NF diferente do Valor Total das Duplicatas ...
    if(id_pais == 31) {//A princípio estamos fazendo esse tratamento apenas em cima dos Clientes daqui do Brasil ...
        if(valor_total_nota != total_duplicatas) {
            alert('O TOTAL DA(S) DUPLICATA(S) ESTA INCOERENTE COM O VALOR TOTAL DA NOTA FISCAL !!!\n\nFAVOR CONTATAR O DEPTO. DE FATURAMENTO !')
            return false
        }
    }
/*********************************************************************/
//Aqui desabilita os campos travados para poder gravar no BD
    for(i = 0; i < document.form.elements.length; i++) document.form.elements[i].disabled = false
//Desabilito o Botão para o usuário não ficar incluindo várias vezes a mesma Nota no BD
    document.form.cmd_salvar.disabled = true
    limpeza_moeda('form', 'txt_taxa_juros, ')
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='id_emp' value='<?=$id_emp;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$verificacao_letras;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Liberar NF de Vendas (Automático)
            <?=genericas::nome_empresa($id_emp);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2' width='50%'>
            <b>Cliente:</b>
        </td>
        <td colspan='2' width='50%'>
            <b>N.º da Conta / Nota:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font darkblue='darkblue' size='-2'>
                <b><?=$razaosocial;?></b>
            </font>
        </td>
        <td colspan='2'>
        <?
            //Aki eu busco o id_pedido_venda_item com o id_nf da Nota Fiscal para poder ver os detalhes da NF
            $sql = "SELECT nfsi.`id_pedido_venda_item` 
                    FROM `nfs` 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                    WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
            $campos_pedido_venda_item = bancos::sql($sql);
            if(count($campos_pedido_venda_item) == 1) {//Quando tiver pelo menos 1 item de pedido na NF, tem link
        ?>
                <a href="javascript:nova_janela('../../../../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$campos_pedido_venda_item[0]['id_pedido_venda_item'];?>', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class="link">
                    <?=$numero_nf;?>
                </a>
        <?
            }else {//Não tem nenhum item, então não tem como ter link para ver os detalhes
                echo $numero_nf. '<p/><marquee behavior="alternate" scrollamount="3"><font color="red"><b>ESSA NF JÁ NÃO É MAIS VÁLIDA, NÃO POSSUI ITENS E <br/>PRECISA SER CANCELADA !!!</b></font></marquee><p/>';
            }
        ?>
            <input type='hidden' name='txt_conta' value='<?=$numero_nf;?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Tipo de Recebimento:</b>
        </td>
        <td colspan='2'>
            <b>Banco:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_tipo_recebimento' title='Tipo de Recebimento' class='textdisabled' disabled>
            <?
                //Definição da variável $id_tipo_recebimento que será utilizada mais abaixo ...
                $sql = "SELECT `id_tipo_recebimento`, `recebimento` 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY `recebimento` ";
                if($forma_pagamento == 1 || $forma_pagamento == 4) {//Dupl. em Carteira ou Dupl. à Definir ...
                    $id_tipo_recebimento = 2;//Carteira ...
                }else if($forma_pagamento == 2) {//Dupl. apenas Banco ...
                    if($id_empresa_nota == 4) {//Título da Empresa Grupo "nesse caso Boleto" ...
                        $id_tipo_recebimento    = 3;//Cobrança Simples ...
                        $id_banco               = 2;//Banco Bradesco ...
                    }else {//Título das Empresas com Nota "Albafer / Tool Master" ...
                        $id_tipo_recebimento    = 11;//Cobrança Caucionada ...
                        $id_banco               = 2;//Banco Bradesco ...
                    }
                }else if($forma_pagamento == 3) {//Dupl. FDIC / Banco ...
                    $id_tipo_recebimento    = 12;//Desconto ...
                    $id_banco               = 10;//CMF Securitizadora ...
                }else if($forma_pagamento == 5) {//Pagto Adiantado ...
                    $id_tipo_recebimento    = 1;//Dinheiro ...
                }else if($forma_pagamento == 6) {//Depósito cheque em cc ...
                    $id_tipo_recebimento    = 4;//Depósito em C/C Cheque ...
                }else if($forma_pagamento == 7) {//Depósito dinheiro em cc ...
                    $id_tipo_recebimento    = 6;//Depósito em C/C Dinheiro ...
                }
                echo combos::combo($sql, $id_tipo_recebimento);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <select name='cmb_banco' title='Banco' class='textdisabled' disabled>
            <?
                $sql = "SELECT `id_banco`, `banco` 
                        FROM `bancos` 
                        WHERE `ativo` = '1' ORDER BY `banco` ";
                echo combos::combo($sql, $id_banco);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Representante:</b>
        </td>
        <td colspan='2'>
        <?
            if($id_emp != 4) {
                echo 'Descrição da Conta:';
            }else {
                echo '<b>Descrição da Conta:<b>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_representante' title='Representante' class='textdisabled' disabled>
            <?
//Verifico qual foi o Representante que teve a maior venda em Nota Fiscal 
//Aqui eu coloco esse comando (sum) para me retornar o representante que teve a maior venda na NF ...
                $sql = "SELECT SUM(nfsi.`valor_unitario`) AS valor_unitario, 
                        nfsi.`id_representante` AS id_rep_melhor_desempenho 
                        FROM `nfs_itens` nfsi 
                        WHERE nfsi.`id_nf` = '$_GET[id_nf]' GROUP BY nfsi.`id_representante` ORDER BY `valor_unitario` DESC LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                $id_rep_melhor_desempenho = $campos_representante[0]['id_rep_melhor_desempenho'];
                
                /*Listagem de Todos os Representantes cadastrados no Sistema independente de trabalhar conosco ou não porque o Financeiro 
                pode importar esse título numa época em que o Representante já não faça mais parte do nosso quadro de funcionários ...*/
                $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                        FROM `representantes` 
                        ORDER BY `nome_fantasia` ";
                echo combos::combo($sql, $id_rep_melhor_desempenho);
            ?>
            </select>
        </td>
        <td colspan='2'>
        <?
            if($id_emp == 4) {//Quando a empresa da Duplicata for Grupo, trago a Combo ...
        ?>
            <select name='cmb_descricao_conta' title='Descrição da Conta' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    //Quando for "Carteira" ou "Depósito em C/C Dinheiro" traz está opção marcada ...
                    if($id_tipo_recebimento == 2 || $id_tipo_recebimento == 6) {
                ?>
                        <option value='PED S/ BOLETO' selected>PED S/ BOLETO</option>
                <?
                    }else if($id_tipo_recebimento == 3) {//Quando for "Cobrança Simples", traz está opção marcada ...
                ?>
                <option value='PED C/ BOLETO' selected>PED C/ BOLETO</option>
                <?
                    }
                ?>
                <option value='CHEQUE DEVOLVIDO'>CHEQUE DEVOLVIDO</option>
            </select>
        <?
            }else {//Quando a empresa da Duplicata for diferente de Grupo, "Alba / Tool", trago a caixa de texto de Descrição da Conta ...
        ?>
            <input type='text' name='txt_descricao_conta' value='<?=$txt_descricao_conta;?>' size='20' title='Digite a Descrição da Conta' class='caixadetexto'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Taxa Juros:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <?
                //Retorna a Taxa de Juros cadastrada pelo pessoal do Financeiro ...
                $taxa_juros_financeiro = number_format(genericas::variavel(39), 2, ',', '.');
            ?>
            <input type='text' name='txt_taxa_juros' value='<?=$taxa_juros_financeiro;?>' title='Taxa Juros' size='8' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Data de Emissão:</b>
            <?=$data_emissao;?>
        </td>
        <td colspan='2'>
            <font color='blue'>Valor Dólar da Nota: </font>
            <?='R$ '.number_format($valor_dolar_nota, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>N.&ordm; da Duplicata</b>
        </td>
        <td>
            <b> Valor em 
            <?
                $sql = "SELECT `simbolo` 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                $campos_moeda = bancos::sql($sql);
                if($id_pais == 31) {//Quando for brasil, é R$
                    $simbolo_moeda = $campos_moeda[0]['simbolo'];
                }else {//Quando for país Internacional, U$
                    $simbolo_moeda = $campos_moeda[1]['simbolo'];
                }
                echo $simbolo_moeda;
            ?>
            </b>
        </td>
        <td>
            <b>Dias</b>
        </td>
        <td>
            <b>Data de Vencimento</b>
        </td>
    </tr>
<?
//Aki eu verifico se a Duplicatas do Número, Cliente, Empresa que Comprou e Ano já foram importadas do Faturamento para o Financeiro ...
	$sql = "SELECT `num_conta` 
                FROM `contas_receberes` 
                WHERE `id_cliente` = '$id_cliente' 
                AND `id_empresa` = '$id_empresa_nota' 
                AND (`num_conta` LIKE '".$numero_nf."_' OR `num_conta` LIKE '$numero_nf') 
                AND SUBSTRING(`data_emissao`, 1, 4) = '$ano_emissao' ORDER BY num_conta LIMIT 4 ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
            for($i =0; $i < $linhas; $i++) {
                if($campos[$i]['num_conta'] == $numero_nf.'A' || $campos[$i]['num_conta'] == $numero_nf) {
                    $a = 1;//Não mostra a duplicata A
                }else if($campos[$i]['num_conta'] == $numero_nf.'B') {
                    $b = 1;//Não mostra a duplicata B
                }else if($campos[$i]['num_conta'] == $numero_nf.'C') {
                    $c = 1;//Não mostra a duplicata C
                }else if($campos[$i]['num_conta'] == $numero_nf.'D') {
                    $d = 1;//Não mostra a duplicata D
                }
            }
	}
	if(!isset($a)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
            if($vencimento2 != 0) {// pois se só tiver uma duplicata, nao precisa de letra
                echo $numero_nf.'A';
            }else {
                echo $numero_nf;
            }
?>
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[0], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento1;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento1);?>
        </td>
    </tr>
<?
	}
        
	if($vencimento2 != 0 && !isset($b)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>B
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[1], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento2;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento2);?>
        </td>
    </tr>
<?
	}
        
	if($vencimento3 != 0 && !isset($c)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>C
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[2], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento3;?>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento3);?>
        </td>
    </tr>
<?
	}
        
	if($vencimento4 != 0 && !isset($d)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>D
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[3], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento4;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento4);?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Observação da NF:</b> <?=$observacao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='5' cols='100' maxlength='500' class='caixadetexto'><?=$txt_observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_nfs_saida.php?id_emp=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
/*Aqui é só quando a empresa for do tipo grupo, eu faço esse macete porque não existe caixa de texto para 
essa empresa, e sim o que existe é uma combo no lugar*/
    $descricao_conta    = ($id_emp == 4) ? $_POST['cmb_descricao_conta'] : $_POST['txt_descricao_conta'];
    $data_sys           = date('Y-m-d H:i:s');
    $data_atual         = date('Y-m-d');

    $sql = "SELECT c.id_cliente, c.id_pais, nfs.numero_nf, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.valor1, nfs.valor2, nfs.valor3, nfs.valor4, nfs.data_emissao 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.`id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $id_pais        = $campos[0]['id_pais'];
//Quando for do Brasil será em Reais ...
    $id_tipo_moeda  = ($id_pais == 31) ? 1 : 2;
    $numero_nf      = $campos[0]['numero_nf'];
    $vencimento1    = $campos[0]['vencimento1'];
    $vencimento2    = $campos[0]['vencimento2'];
    $vencimento3    = $campos[0]['vencimento3'];
    $vencimento4    = $campos[0]['vencimento4'];
    $valor1         = $campos[0]['valor1'];
    $valor2         = $campos[0]['valor2'];
    $valor3         = $campos[0]['valor3'];
    $valor4         = $campos[0]['valor4'];
    $data_emissao   = $campos[0]['data_emissao'];
    //Aki verifica se a Duplicatas, já foram importadas do Faturamento para o Financeiro
    $sql = "SELECT `num_conta` 
            FROM `contas_receberes` 
            WHERE (`num_conta` LIKE '".$numero_nf."_' OR `num_conta` LIKE '$numero_nf') ORDER BY `num_conta` LIMIT 4 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
        for($i = 0; $i < $linhas; $i++) {
            if($campos[$i]['num_conta'] == $numero_nf.'A' || $campos[$i]['num_conta'] == $numero_nf) {
                $a = 1;//Não mostra a duplicata A
            }else if($campos[$i]['num_conta'] == $numero_nf.'B') {
                $b = 1;//Não mostra a duplicata B
            }else if($campos[$i]['num_conta'] == $numero_nf.'C') {
                $c = 1;//Não mostra a duplicata C
            }else if($campos[$i]['num_conta'] == $numero_nf.'D') {
                $d = 1;//Não mostra a duplicata D
            }
        }
    }

    function calc_semana($data_emissao, $prazo) {
        $data_emissao_br    = data::datetodata($data_emissao, '-');
        $data_vencimento    = data::adicionar_data_hora($data_emissao_br, $prazo);
        $dia                = substr($data_vencimento,0,2);
        $mes                = substr($data_vencimento,3,2);
        $ano                = substr($data_vencimento,6,4);
        $retorno[]          = $data_vencimento;
        $retorno[]          = data::numero_semana($dia, $mes, $ano);
        return $retorno;
    }

    if(!isset($a)) {
        if($vencimento2 != 0) {//Pois se só tiver uma duplicata, nao precisa de letra
            $num_conta = $txt_conta.'A';
        }else {
            $num_conta = $txt_conta;
        }
        $retorno        = calc_semana($data_emissao, $vencimento1);
        $vencimento1    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber` , `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `id_nf`, `num_conta`, `descricao_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status` , `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_funcionario]', '$id_cliente', '$id_tipo_moeda', '$_POST[id_nf]', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento1', '$vencimento1', '$vencimento1', '$valor1', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, então vinculo o seu id na tabela de Contas à Receber ...
        if(!empty($_POST['cmb_representante'])) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            $id_representante = (!empty($_POST['cmb_representante'])) ? $_POST['cmb_representante'] : 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        if(!empty($_POST['cmb_banco'])) {//Se existir banco, então vinculo o seu id na tabela de Contas à Receber ...
            $sql = "UPDATE `contas_receberes` SET `id_banco` = '$_POST[cmb_banco]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }

    if($vencimento2 != 0 && !isset($b)) {
        $num_conta      = $txt_conta.'B';
        $retorno        = calc_semana($data_emissao, $vencimento2);
        $vencimento2    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber` , `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `id_nf`, `num_conta`, `descricao_conta`, `semana` , `data_emissao` , `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status` , `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$_POST[id_nf]', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento2', '$vencimento2', '$vencimento2', '$valor2', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, então vinculo o seu id na tabela de Contas à Receber ...
        if(!empty($_POST['cmb_representante'])) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            $id_representante = (!empty($_POST['cmb_representante'])) ? $_POST['cmb_representante'] : 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        if(!empty($_POST['cmb_banco'])) {//Se existir banco, então vinculo o seu id na tabela de Contas à Receber ...
            $sql = "UPDATE `contas_receberes` SET `id_banco` = '$_POST[cmb_banco]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }

    if($vencimento3 != 0 && !isset($c)) {
        $num_conta      = $txt_conta.'C';
        $retorno        = calc_semana($data_emissao, $vencimento3);
        $vencimento3    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber` , `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `id_nf`, `num_conta`, `descricao_conta`, `semana` , `data_emissao` , `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status` , `ativo`) VALUES ('', '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$_POST[id_nf]', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento3', '$vencimento3', '$vencimento3', '$valor3', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, então vinculo o seu id na tabela de Contas à Receber ...
        if(!empty($_POST['cmb_representante'])) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            $id_representante = (!empty($_POST['cmb_representante'])) ? $_POST['cmb_representante'] : 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        if(!empty($_POST['cmb_banco'])) {//Se existir banco, então vinculo o seu id na tabela de Contas à Receber ...
            $sql = "UPDATE `contas_receberes` SET `id_banco` = '$_POST[cmb_banco]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }

    if($vencimento4 != 0 && !isset($d)) {
        $num_conta      = $txt_conta.'D';
        $retorno        = calc_semana($data_emissao, $vencimento4);
        $vencimento4    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber` , `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `id_nf`, `num_conta`, `descricao_conta`, `semana` , `data_emissao` , `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status` , `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$_POST[id_nf]', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento4', '$vencimento4', '$vencimento4', '$valor4', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, então vinculo o seu id na tabela de Contas à Receber ...
        if(!empty($_POST['cmb_representante'])) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            $id_representante = (!empty($_POST['cmb_representante'])) ? $_POST['cmb_representante'] : 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        if(!empty($_POST['cmb_banco'])) {//Se existir banco, então vinculo o seu id na tabela de Contas à Receber ...
            $sql = "UPDATE `contas_receberes` SET `id_banco` = '$_POST[cmb_banco]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }
//****************************** Código Antigo ******************************//
//Função que muda a Situação da NF quando está for importada no sistema do Faturamento para o Financ.
//controla o status de importação de nfs para financeiro -> intermodular::verifica_status_importar_financeiro($id_nf);
//****************************** ************* ******************************//
    $sql = "UPDATE `nfs` SET `importado_financeiro` = 'S' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        //Faço esse tratamento porque as vezes essa Tela é acessada pelo Botão de Alterar Vencimentos ...
        if(typeof(opener.parent.itens) == 'object') {//Tela acessada pelo Botão de Incluir da Tela de Itens ...
            opener.parent.itens.document.form.recarregar.value = 1
            window.location = 'incluir_nfs_saida.php?id_emp=<?=$_POST['id_emp'];?>&valor=3'
        }else {//Tela acessada pelo Botão de Alterar Vencimentos da Tela de Itens ...
            opener.opener.parent.location = opener.opener.parent.location.href
            alert('CONTA À RECEBER INCLUIDA COM SUCESSO !')
            window.close()//Fecha a Tela Atual ...
        }
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Liberar NF de Vendas (Automático) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
//Variável referente ao Frame de Baixo
    var recarregar = opener.parent.itens.document.form.recarregar.value
    if(recarregar == 1 && document.form.ignorar.value == 0) {
        if(typeof(opener.parent.itens.document.form) == 'object') opener.parent.itens.recarregar_tela()
    }
}
</Script>
</head>
<body onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<!--Controle de Tela-->
<input type='hidden' name='ignorar'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?
/*Aqui exibo todas as NFs que já podem ser liberadas da mesma empresa do Menu, nas condições de "Liberada / Empacotada ou Despachada", 
à partir de 01/05/2006, que tenham valor de Faturamento > R$ 0,00 e que estejam com a Marcação de Gerar Duplicatas ...*/
        $sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`data_emissao`, nfs.`vencimento1`, nfs.`vencimento2`, 
                nfs.`vencimento3`, nfs.`vencimento4`, nfs.`status`, nfs.`livre_debito`, nfs.`tipo_despacho`, c.`razaosocial`, 
                c.`credito`, t.`nome` AS transportadora 
                FROM `nfs` 
                INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`id_empresa` = '$id_emp' 
                AND nfs.`data_emissao` >= '2006-05-01' 
                AND nfs.`valor1` <> '0' 
                AND nfs.`status` IN (2, 3, 4) 
                AND nfs.`importado_financeiro` = 'N' 
                AND nfs.`gerar_duplicatas` = 'S' 
                ORDER BY nnn.`numero_nf` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {
?>    
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>         
<?
        }else {
?>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Liberar NF de Vendas (Automático) - <?=genericas::nome_empresa($id_emp);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; NF
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
	$vetor                  = array_sistema::nota_fiscal();
	$vetor_tipo_despacho    = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'FINANCEIRO');
        
	for($i = 0;  $i < $linhas; $i++) {
            $url = "javascript:document.form.ignorar.value = 1;window.location = 'incluir_nfs_saida.php?passo=1&id_nf=".$campos[$i]['id_nf']."&id_emp=".$campos[$i]['id_empresa']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Liberar NF de Saída' class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
            <?
                //Se existir a marcação de Livre de Débito ...
                if($campos[$i]['livre_debito'] == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
            ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
        <td>
        <?
            echo $vetor[$campos[$i]['status']];
            if($campos[$i]['status'] == 4) echo ' ('.$vetor_tipo_despacho[$campos[$i]['tipo_despacho']].')';
        ?>
        </td>
        <td align='left'>
        <?
            //Busca da Empresa da NF ...
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $apresentar = $campos_empresa[0]['nomefantasia'];
            $apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
            $prazo_faturamento = '';//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
//Aki é simplesmente o contador, não tem paginação para não dar conflito com a da Tela de Itens que está no Frame Debaixo ...
?>
    <tr>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='6'>
            Total de Registro(s): <?=$linhas;?>
        </td>
    </tr>
</table>
</form>
</body>
<pre>
O sistema não exibe Notas Fiscais em que:
<b>
- A data de Emissão menor que 01/05/2006;
- Os valores de seus vencimentos estejam zerados;
- Estejam com Status "berto / Liberada / Canceladas";
- Estejam com a opção de Gerar Duplicatas desmarcada <font color='red'>(VENDA ORIGINADA DE ENCOMENDA PARA ENTREGA FUTURA).</font>
</b>
</pre>
</html>
<?  
    }
}
?>