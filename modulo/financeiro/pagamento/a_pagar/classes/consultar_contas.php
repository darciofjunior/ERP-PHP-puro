<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');

if($itens != 1) segurancas::geral($PHP_SELF, '../../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que o usuário já passou pela tela de contas à pagar antes
    if($itens == 1) {
        session_start('funcionarios');
        $id_emp2 = $id_emp;
        session_unregister('id_emp');
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '../classes/index.php?id_emp2=<?=$id_emp2;?>&txt_fornecedor=<?=$_POST[txt_fornecedor];?>&txt_numero_conta=<?=$_POST[txt_numero_conta];?>&txt_data_emissao_inicial=<?=$_POST[txt_data_emissao_inicial];?>&txt_data_emissao_final=<?=$_POST[txt_data_emissao_final];?>&txt_data_vencimento_alterada_inicial=<?=$_POST[txt_data_vencimento_alterada_inicial];?>&txt_data_vencimento_alterada_final=<?=$_POST[txt_data_vencimento_alterada_final];?>&txt_data_inicial=<?=$_POST[txt_data_inicial];?>&txt_data_final=<?=$_POST[txt_data_final];?>&txt_semana=<?=$_POST[txt_semana];?>&txt_valor=<?=$_POST[txt_valor];?>&txt_bairro=<?=$_POST[txt_bairro];?>&txt_cidade=<?=$_POST[txt_cidade];?>&cmb_uf=<?=$_POST[cmb_uf];?>&cmb_conta_caixa=<?=$_POST[cmb_conta_caixa];?>&cmb_tipo_pagamento=<?=$_POST[cmb_tipo_pagamento];?>&cmb_importacao=<?=$_POST[cmb_importacao];?>&cmb_contas_vencidas=<?=$_POST[cmb_contas_vencidas];?>&chkt_mostrar=<?=$_POST[chkt_mostrar];?>&chkt_pago_pelo_caixa_compras=<?=$_POST[chkt_pago_pelo_caixa_compras];?>&chkt_somente_importacao=<?=$_POST[chkt_somente_importacao];?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Conta(s) à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
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
/**Verifico se o intervalo entre Datas é > do que 3 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        /*var dias = diferenca_datas(document.form.txt_data_emissao_inicial, document.form.txt_data_emissao_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
	*************TIREI A LIMITACAO DE 2 ANOS PQ O ROBERTO PEDIU****************/ 
//Se a Data de Vencimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
        if(document.form.txt_data_vencimento_alterada_inicial.value != '' || document.form.txt_data_vencimento_alterada_final.value != '') {
//Data de Vencimento Inicial
            if(!data('form', 'txt_data_vencimento_alterada_inicial', '4000', 'VENCIMENTO ALTERADA INICIAL')) {
                return false
            }
//Data de Vencimento Final
            if(!data('form', 'txt_data_vencimento_alterada_final', '4000', 'VENCIMENTO ALTERADA FINAL')) {
                return false
            }
//Comparação com as Datas ...
            var data_vencimento_alterada_inicial    = document.form.txt_data_vencimento_alterada_inicial.value
            var data_vencimento_alterada_final      = document.form.txt_data_vencimento_alterada_final.value
            data_vencimento_alterada_inicial        = data_vencimento_alterada_inicial.substr(6, 4) + data_vencimento_alterada_inicial.substr(3, 2) + data_vencimento_alterada_inicial.substr(0, 2)
            data_vencimento_alterada_final          = data_vencimento_alterada_final.substr(6, 4) + data_vencimento_alterada_final.substr(3, 2) + data_vencimento_alterada_final.substr(0, 2)
            data_vencimento_alterada_inicial        = eval(data_vencimento_alterada_inicial)
            data_vencimento_alterada_final          = eval(data_vencimento_alterada_final)
	
            if(data_vencimento_alterada_final < data_vencimento_alterada_inicial) {
                alert('DATA DE VENCIMENTO ALTERADA FINAL INVÁLIDA !!!\nDATA DE VENCIMENTO ALTERADA FINAL MENOR DO QUE A DATA DE VENCIMENTO ALTERADA INICIAL !')
                document.form.txt_data_vencimento_alterada_final.focus()
                document.form.txt_data_vencimento_alterada_final.select()
                return false
            }
/**Verifico se o intervalo entre Datas é > do que 3 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
            var dias = diferenca_datas(document.form.txt_data_vencimento_alterada_inicial, document.form.txt_data_vencimento_alterada_final)
            if(dias > 1095) {
                alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
                document.form.txt_data_vencimento_alterada_final.focus()
                document.form.txt_data_vencimento_alterada_final.select()
                return false
            }
	}
//Se a Data de Pagamento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
	if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
//Data de Vencimento Inicial
            if(!data('form', 'txt_data_inicial', '4000', 'PAGAMENTO INICIAL')) {
                return false
            }
//Data de Vencimento Final
            if(!data('form', 'txt_data_final', '4000', 'PAGAMENTO FINAL')) {
                return false
            }
//Comparação com as Datas ...
            var data_inicial = document.form.txt_data_inicial.value
            var data_final = document.form.txt_data_final.value
            data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
            data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
            data_inicial = eval(data_inicial)
            data_final = eval(data_final)

            if(data_final < data_inicial) {
                alert('DATA DE PAGAMENTO FINAL INVÁLIDA !!!\n DATA DE PAGAMENTO FINAL MENOR DO QUE A DATA DE PAGAMENTO INICIAL !')
                document.form.txt_data_final.focus()
                document.form.txt_data_final.select()
                return false
            }
/**Verifico se o intervalo entre Datas é > do que 3 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
            var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
            if(dias > 1095) {
                alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
                document.form.txt_data_final.focus()
                document.form.txt_data_final.select()
                return false
            }
        }
    }
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload='document.form.txt_fornecedor.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        Consultar Conta(s) à Pagar
        <font color='yellow'>
            <?
                if($id_emp2 != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp2);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
        </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name="txt_fornecedor" title="Digite o Fornecedor" size="40" maxlength="45" class='caixadetexto'> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name="txt_numero_conta" title="Digite o Número da Conta" size="20" maxlength="18" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name="txt_data_emissao_inicial" title="Digite a Data de Emissão Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name="txt_data_emissao_final" title="Digite a Data de Emissão Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src="../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento alterada
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_alterada_inicial' title='Digite a Data de Vencimento alterada Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_alterada_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_vencimento_alterada_final' title='Digite a Data de Vencimento alterada Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_alterada_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Pagamento
        </td>
        <td>
            <input type='text' name="txt_data_inicial" title="Digite a Data de Pagamento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">  até&nbsp; 
            <input type='text' name="txt_data_final" title="Digite a Data de Pagamento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>        <img src="../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='16' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Semana
        </td>
        <td>
            <input type='text' name='txt_semana' title='Digite a Semana' size='12' maxlength='10' class='caixadetexto'>
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
            <input type='text' name="txt_cidade" title="Digite a Cidade" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Caixa
        </td>
        <td>
            <select name='cmb_conta_caixa' title='Selecione a Conta Caixa' class='combo'>
            <?
//Traz somente as contas caixas do Módulo Financeiro
                $sql = "SELECT `id_conta_caixa_pagar`, `conta_caixa` 
                        FROM `contas_caixas_pagares` 
                        WHERE `ativo` = '1' ORDER BY `conta_caixa` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importação
        </td>
        <td>
            <select name='cmb_importacao' title='Selecione a Importação' class='combo'>
            <?
                $sql = "SELECT `id_importacao`, `nome` 
                        FROM `importacoes` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Pagamento
        </td>
        <td>
            <select name='cmb_tipo_pagamento' title='Tipo de Pagamento' class='combo'>
            <?
                $sql = "SELECT `id_tipo_pagamento`, `pagamento` 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY `pagamento` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>OPÇÕES DE CONTAS VENCIDAS</b>
            </font>
        </td>
        <td>
            <select name='cmb_contas_vencidas' title='Selecione uma Opção de Contas Vencidas' class='combo'>
                <option value='S'>MOSTRAR "APENAS URGENTES"</option>
                <option value='%'>MOSTRAR "TUDO"</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_mostrar' value='1' title='Não mostrar atrasados > 60 dias' id='label1' class='checkbox' checked>
            <label for='label1'>Não mostrar atrasados > 60 dias 
                <font color='darkblue'>
                    <b>(em relação a Data de Vencimento alterada)</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pago_pelo_caixa_compras' value='1' title='Pago pelo Caixa de Compras' id='label2' class='checkbox'>
            <label for='label2'>Pago pelo Caixa de Compras</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_importacao' value='1' title='Somente Importação' id='label3' class='checkbox'>
            <label for='label3'>Somente Importação</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.txt_fornecedor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_listagem_representantes' value='Listagem de Representantes' title='Listagem de Representantes' style='color:brown' onclick="html5Lightbox.showLightbox(7, '../../../../vendas/representante/utilitarios/lista_representantes/lista_representantes.php?pop_up=1')" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='itens' value='<?=$itens;?>'>
</form>
</body>
</html>
<?}?>