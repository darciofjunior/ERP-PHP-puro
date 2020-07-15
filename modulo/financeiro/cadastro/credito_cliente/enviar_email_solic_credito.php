<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é utilizada dentro da Biblioteca 'faturamentos' ...
require('../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

$aliquota_icms_st_ipi = 20;

if($passo == 1) {
    $retorno_analise_credito = faturamentos::analise_credito_cliente($_POST['id_cliente']);
    /*Sempre que o Sys cair nessa situação, será disparado um e-mail automático 
    p/ o Departamento de Financeiro solicitando a modificação do Crédito do Cliente 
    p/ que se possa faturar a NF ...*/
    $conteudo_email = '<b>Data: </b>'.date('d/m/Y').' / <b>Hora: </b>'.date('H:i:s').'<br><br>';

    $sql = "SELECT `id_pais`, `id_uf`, CONCAT(`razaosocial`, '(', `nomefantasia`, ')') AS cliente, `cidade` 
            FROM `clientes` 
            WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    $conteudo_email.= '<b>Cliente: </b>'.$campos_cliente[0]['cliente'];
    if($campos_cliente[0]['id_pais'] != 0) {
        $sql = "SELECT `pais` 
                FROM `paises` 
                WHERE `id_pais` = '".$campos_cliente[0]['id_pais']."' LIMIT 1 ";
        $campos_pais = bancos::sql($sql);
        $conteudo_email.= ' / <b>País: </b>'.$campos_pais[0]['pais'];
    }
    if($campos_cliente[0]['id_uf'] != 0) {
        $sql = "SELECT `sigla` 
                FROM `ufs` 
                WHERE `id_uf` = '".$campos_cliente[0]['id_uf']."' LIMIT 1 ";
        $campos_uf = bancos::sql($sql);
        $conteudo_email.= ' / <b>UF: </b>'.$campos_uf[0]['sigla'];
    }
    $conteudo_email.= ' / <b>Cidade: </b>'.$campos_cliente[0]['cidade'];
//Aqui eu busco o email de quem está enviando a Solicitação de Limite de Crédito ...
    $sql = "SELECT `email_externo` AS email 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_email       = bancos::sql($sql);
    $email_solicitador  = $campos_email[0]['email'];
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
    $destino = $enviar_email_solic_credito;
//Aqui eu busco todos os representantes do Cliente ...
    $sql = "SELECT DISTINCT(r.`id_representante`), r.`nome_fantasia` 
            FROM `clientes_vs_representantes` cr 
            INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
            WHERE cr.`id_cliente` = '$_POST[id_cliente]' ";
    $campos_representantes = bancos::sql($sql);
    $linhas_representantes = count($campos_representantes);
    for($i = 0; $i < $linhas_representantes; $i++) {
        //Se o Representante for Direto não precisa pq o e-mail já vai para a Dona Sandra e para o Wilson ...
        if($campos_representantes[$i]['id_representante'] != 1) {
//Aqui eu verifico se o Representante é Funcionário ...
            $sql = "SELECT f.`email_externo` 
                    FROM `representantes_vs_funcionarios` rf 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                    WHERE rf.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            if(count($campos_funcionario) == 1) {//Se for funcionário ...
                $vendedores.= $campos_funcionario[0]['email_externo'].', ';
            }else {//Significa que é autônomo, sendo assim eu busco o Supervisor do Representante p/ passar e-mail ...
                $sql = "SELECT r.`id_representante`, r.`nome_fantasia` 
                        FROM `representantes_vs_supervisores` rs 
                        INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                        WHERE rs.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
                $campos_supervisores = bancos::sql($sql);
                //Tratamento com alguns e-mails ...
                if($campos_supervisores[0]['id_representante'] == 42) {//Arnaldo Nogueira ...
                    $vendedores.= 'nogueira@grupoalbafer.com.br'.', ';
                }else if($campos_supervisores[0]['id_representante'] == 69) {//Carlos Junior ...
                    $vendedores.= 'carlos.junior@grupoalbafer.com.br'.', ';
                }else if($campos_supervisores[0]['id_representante'] == 6) {//Edson Gonçalves ...
                    $vendedores.= 'edson.goncalves@grupoalbafer.com.br'.', ';
                }else if($campos_supervisores[0]['id_representante'] == 93) {//Izael Pedreira ...
                    $vendedores.= 'noronha@grupoalbafer.com.br'.', ';
                }else if($campos_supervisores[0]['id_representante'] == 137) {//Wilson Roberto "Diretor" ...
                    $vendedores.= 'wilson@grupoalbafer.com.br'.', ';
                }else {
                    $vendedores.= strtolower($campos_supervisores[0]['nome_fantasia']).'@grupoalbafer.com.br, ';
                }
            }
        }
    }
    $vendedores = substr($vendedores, 0, strlen($vendedores) - 2);
    $copia      = $vendedores;//Aqui eu envio e-mail para os Vendedores do Cliente estarem a par ...
    $assunto = 'LIBERAÇÃO DE CRÉDITO P/ CLIENTE';
	
    $conteudo_email.= '<br><br>H) VALOR À FATURAR + ICMS ST + IPI (+ '.$aliquota_icms_st_ipi.'%): <b> R$ '.$_POST['hdd_valor_total_itens_faturar'].'</b>';
    $conteudo_email.= '<br><br>'.str_replace('\n', '<br>', $retorno_analise_credito['historico_cliente']);
    $conteudo_email.= '<br><br>K) NOVO VALOR DE CRÉDITO SOLICITADO, ITENS FATURANDO: <b> R$ '.$_POST['hdd_novo_valor_credito_itens_faturando'].',00 (CRÉDITO MÍNIMO)</b>';
    $conteudo_email.= '<br><br>M) NOVO VALOR DE CRÉDITO (TODOS PEDIDOS À FATURAR): <b> R$ '.$_POST['hdd_novo_valor_credito_todos_pedidos'].',00 (CRÉDITO MÁXIMO)</b>';
    $conteudo_email.= '<br><br><b>JUSTIFICATIVA: </b>'.$_POST['txt_justificativa'];
    comunicacao::email($email_solicitador, $destino, $copia, $assunto, $conteudo_email);
?>
	<Script Language = 'JavaScript'>
            alert('E-MAIL DE LIBERAÇÃO DE CRÉDITO ENVIADO COM SUCESSO !')
            window.close()
	</Script>
<?
}else {
    $retorno_analise_credito = faturamentos::analise_credito_cliente($_GET['id_cliente']);
?>
<html>
<head>
<title>.:: Enviar E-mail de Liberação de Crédito p/ Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Justificativa ...
    if(document.form.txt_justificativa.value == '') {
        alert('DIGITE A JUSTIFICATIVA !')
        document.form.txt_justificativa.focus()
        document.form.txt_justificativa.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_justificativa.focus()' topmargin='40'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
<table width='98%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Enviar E-mail de Liberação de Crédito - 
            <font color='yellow'>
            <?
                $sql = "SELECT IF(razaosocial = '', nomefantasia, razaosocial) AS cliente 
                        FROM `clientes` 
                        WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                echo $campos_cliente[0]['cliente'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>A) LIMITE DE CRÉDITO:</b>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ <?=number_format($retorno_analise_credito['limite_credito'], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>B) TOLERÂNCIA DO CLIENTE (A + 10%):</b>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ <?=number_format($retorno_analise_credito['tolerancia_cliente'], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>C) CONTAS À RECEBER:</b>
        </td>
        <td>
            <b>R$ <?=number_format($retorno_analise_credito['contas_a_receber'], 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>D) FATURANDO (EXCLUSA ESTA NF):</b>
        </td>
        <td>
            <b>R$ <?=number_format($retorno_analise_credito['faturando'], 2, ',', '.');?></b>
        </td>
    </tr>
     <tr class='linhanormal'>
        <td>
            <b>E) TOTAL DE VALE + ICMS ST + IPI (+ <?=$aliquota_icms_st_ipi;?>%):</b>
        </td>
        <td>
            <b>R$ <?=number_format($retorno_analise_credito['total_vale'], 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>F) CRÉDITO COMPROMETIDO (C + D + E):</b>
        </td>
        <td>
            <b>R$ <?=number_format($retorno_analise_credito['credito_comprometido'], 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>G) PESO TOTAL À FATURAR (APENAS SE TIVER ITENS NA NF):</b>
        </td>
        <td>
            <b><?=number_format($peso_total_faturar, 4, ',', '.');?> KG</b> S/ USO AINDA ...
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>H) VALOR À FATURAR + ICMS ST + IPI (+ <?=$aliquota_icms_st_ipi;?>%):</b>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ <?=number_format($_GET['valor_total_itens_faturar'], 2, ',', '.');?></b>
            </font>
            <!--Esse valor será apresentado no Conteúdo do E-mail-->
            <input type='hidden' name='hdd_valor_total_itens_faturar' value='<?=number_format($_GET['valor_total_itens_faturar'], 2, ',', '.');?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>I) TOTAL DE PEDIDOS EM ABERTO + <u>VALES</u> + ICMS ST + IPI (+ <?=$aliquota_icms_st_ipi;?>%):</b>
        </td>
        <td>
            <b>R$ <?=number_format($retorno_analise_credito['total_pedidos_abertos'], 2, ',', '.');?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>J) NOVA TOLERÂNCIA DE CRÉDITO (APENAS ITENS FATURANDO) (F + H):</b>
            </font>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ <?=number_format($retorno_analise_credito['credito_comprometido'] + $_GET['valor_total_itens_faturar'], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>K) NOVO VALOR DE CRÉDITO (APENAS ITENS FATURANDO) (J / 1,1) :</b>
            </font>
        </td>
        <td>
            <font color='darkblue'>
                <!--Aqui é como se eu tirasse os 10% do Crédito do Cliente porque o próprio Sistema sozinho, já joga 10% pelo Sistema do Financeiro ...-->
                <b>R$ 
                <?
                    $novo_valor_credito_itens_faturando = ($retorno_analise_credito['credito_comprometido'] + $_GET['valor_total_itens_faturar']) / 1.1;
                    //Aqui eu arredondo para centenas de reais ...
                    $novo_valor_credito_itens_faturando/= 100;
                    $novo_valor_credito_itens_faturando = (intval($novo_valor_credito_itens_faturando) + 1) * 100;
                    echo number_format($novo_valor_credito_itens_faturando, 2, ',', '.');
                ?>
                (CRÉDITO MÍNIMO)
                </b>
            </font>
            <input type='hidden' name='hdd_novo_valor_credito_itens_faturando' value='<?=number_format($novo_valor_credito_itens_faturando, 0, ',', '.');?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>L) NOVA TOLERÂNCIA DE CRÉDITO (TODOS PEDIDOS À FATURAR) (F + I):</b>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ <?=number_format($retorno_analise_credito['credito_comprometido'] + $retorno_analise_credito['total_pedidos_abertos'], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>M) NOVO VALOR DE CRÉDITO (TODOS PEDIDOS À FATURAR) (L / 1,1):</b>
        </td>
        <td>
            <font color='darkblue'>
                <b>R$ 
                <?
                    $novo_valor_credito_todos_pedidos = ($retorno_analise_credito['credito_comprometido'] + $retorno_analise_credito['total_pedidos_abertos']) / 1.1;
                    //Aqui eu arredondo para centenas de reais ...
                    $novo_valor_credito_todos_pedidos/= 100;
                    $novo_valor_credito_todos_pedidos = (intval($novo_valor_credito_todos_pedidos) + 1) * 100;
                    echo number_format($novo_valor_credito_todos_pedidos, 2, ',', '.');
                ?>
                (CRÉDITO MÁXIMO)
                </b>
            </font>
            <input type='hidden' name='hdd_novo_valor_credito_todos_pedidos' value='<?=number_format($novo_valor_credito_todos_pedidos, 0, ',', '.');?>'>
        </td>
    </tr>	
    <tr class='linhanormal'>
        <td>
            <b>Justificativa:</b>
        </td>
        <td>
            <textarea name='txt_justificativa' maxlength='300' cols='60' rows='5' class='caixadetexto'></textarea>
        </td>
    </tr>	
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900;' onclick='document.form.txt_justificativa.focus()' class='botao'>
            <input type='submit' name='cmd_enviar_email' value='Enviar E-mail' title='Enviar E-mail' style='color:green' class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'>
<b>Observação:</b>
<pre>
* Caso exista Valor de Frete, não envie o e-mail. Antes de calcular o Frete e digitá-lo no Cabeçalho.
* Total do Peso dos Itens já inclusos + Itens selecionados = <?=number_format($peso_total_faturar, 4, ',', '.');?> kg(s).
<font color='black'>
* Em alguns casos o Valor a Faturar, pode ser também o Valor Total do Orçamento.

<b>Este e-mail é enviado para:

Agueda, Financeiro, Roberto, Wilson e Representante do Cliente;
em caso de o Representante ser Externo, esse e-mail será direcionado para o Supervisor do mesmo.</b>
</font>
</pre>
</font>
<?}?>