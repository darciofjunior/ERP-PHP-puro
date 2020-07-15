<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');

if($itens != 1) segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que o usuário já passou pela tela de contas à receber antes
    if($itens == 1) {
        session_start('funcionarios');
        $id_emp2 = $id_emp;
        session_unregister('id_emp');
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '../classes/index.php?id_emp2=<?=$id_emp2;?>&txt_cliente=<?=$txt_cliente;?>&txt_descricao_conta=<?=$txt_descricao_conta;?>&cmb_representante=<?=$cmb_representante;?>&txt_numero_conta=<?=$txt_numero_conta;?>&txt_data_emissao_inicial=<?=$txt_data_emissao_inicial;?>&txt_data_emissao_final=<?=$txt_data_emissao_final;?>&txt_data_vencimento_inicial=<?=$txt_data_vencimento_inicial;?>&txt_data_vencimento_final=<?=$txt_data_vencimento_final;?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>&cmb_ano=<?=$cmb_ano;?>&txt_semana=<?=$txt_semana;?>&txt_data_cadastro=<?=$txt_data_cadastro;?>&cmb_banco=<?=$cmb_banco;?>&cmb_tipo_recebimento=<?=$cmb_tipo_recebimento;?>&chkt_mostrar=<?=$chkt_mostrar;?>&chkt_somente_exportacao=<?=$chkt_somente_exportacao;?>&txt_bairro=<?=$txt_bairro;?>&txt_cidade=<?=$txt_cidade;?>&cmb_uf=<?=$cmb_uf;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Conta(s) à Receber ::.</title>
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
        var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
        var data_emissao_final = document.form.txt_data_emissao_final.value
        data_emissao_inicial = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
        data_emissao_final = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
        data_emissao_inicial = eval(data_emissao_inicial)
        data_emissao_final = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA DE EMISSÃO FINAL INVÁLIDA !!!\n DATA DE EMISSÃO FINAL MENOR DO QUE A DATA DE EMISSÃO INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas é > do que 3 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_emissao_inicial, document.form.txt_data_emissao_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
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
        var data_vencimento_final = document.form.txt_data_vencimento_final.value
        data_vencimento_inicial = data_vencimento_inicial.substr(6,4) + data_vencimento_inicial.substr(3,2) + data_vencimento_inicial.substr(0,2)
        data_vencimento_final = data_vencimento_final.substr(6,4) + data_vencimento_final.substr(3,2) + data_vencimento_final.substr(0,2)
        data_vencimento_inicial = eval(data_vencimento_inicial)
        data_vencimento_final = eval(data_vencimento_final)

        if(data_vencimento_final < data_vencimento_inicial) {
            alert('DATA DE VENCIMENTO FINAL INVÁLIDA !!!\n DATA DE VENCIMENTO FINAL MENOR DO QUE A DATA DE VENCIMENTO INICIAL !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas é > do que 3 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_vencimento_inicial, document.form.txt_data_vencimento_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
    }
//Se a Data de Recebimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_inicial', '4000', 'RECEBIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_final', '4000', 'RECEBIMENTO FINAL')) {
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
            alert('DATA DE RECEBIMENTO FINAL INVÁLIDA !!!\n DATA DE RECEBIMENTO FINAL MENOR DO QUE A DATA DE RECEBIMENTO INICIAL !')
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
</Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Conta(s) à Receber 
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
            Cliente
        </td>
        <td>
            <input type='text' name="txt_cliente" title="Digite o Cliente" size="40" maxlength="45" class='caixadetexto'> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descrição da Conta
        </td>
        <td>
            <input type='text' name="txt_descricao_conta" title="Digite a Descrição da Conta" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name="txt_numero_conta" title="Digite o Número da Conta" size="12" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name="txt_data_emissao_inicial" title="Digite a Data de Emissão Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name="txt_data_emissao_final" title="Digite a Data de Emissão Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name="txt_data_vencimento_inicial" title="Digite a Data de Vencimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name="txt_data_vencimento_final" title="Digite a Data de Vencimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Recebimento
        </td>
        <td>
            <input type='text' name="txt_data_inicial" title="Digite a Data de Recebimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">  até&nbsp; 
            <input type='text' name="txt_data_final" title="Digite a Data de Recebimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>        <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data do Cadastro
        </td>
        <td>
            <input type='text' name="txt_data_cadastro" title="Digite a Data de Cadastro" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_cadastro&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name="txt_bairro" title="Digite o Bairro" class='caixadetexto'>
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
            <select name="cmb_uf" title="Selecione o Estado" class='combo'>
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
            <select name="cmb_ano" title="Selecione o Ano" class='combo'>
                    <option value="" style="color:red">SELECIONE</option>
            <?
                    for($i = 2004; $i <= date('Y') + 6; $i++) {
            ?>
                    <option value="<?=$i;?>"><?=$i;?></option>
            <?
                    }
            ?>
            </select>
            Semana 
            <input type='text' name="txt_semana" title="Digite a Semana" size="10" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name="cmb_representante" title="Selecione o Representante" class='combo'>
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
            <select name="cmb_banco" title="Selecione o Banco" class='combo'>
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
            <select name="cmb_tipo_recebimento" title="Selecione o Tipo de Recebimento" class='combo'>
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
            <input type='checkbox' name='chkt_mostrar' value="1" title='Não mostrar atrasados > 60 dias' id='label1' class="checkbox" checked>
            <label for="label1">Não mostrar atrasados > 60 dias</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_exportacao' value="1" title='Somente Exportação' id='label2' class="checkbox">
            <label for="label2">Somente Exportação</label>
        </td>
    </tr>
    <?
            $data_atual = date('Y-m-d');
/***************Listagem de Clientes com Crédito A e B***************/
/*Aqui eu só listo os Clientes que possuem crédito A e B, q possuem Débitos em Atraso, que já estão vencidas 
e que a Data de Atualização de Crédito esteje alterada a + que 5 dias*/
            $sql = "SELECT c.id_cliente 
                    FROM clientes c 
                    INNER JOIN contas_receberes cr ON cr.id_conta_receber = cr.id_conta_receber AND cr.status < '2' AND cr.`data_vencimento_alterada` < '$data_atual' 
                    WHERE c.credito in ('A', 'B') 
                    AND c.lembrete_credito = 'S' 
                    AND c.ativo = '1' 
                    AND (DATEDIFF('$data_atual', SUBSTRING(credito_data, 1, 10)) > 5 OR SUBSTRING(credito_data, 1, 10) = '0000-00-00') 
                    GROUP BY c.id_cliente ORDER BY cr.`data_vencimento_alterada` LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Se encontrou algum já exibe o botão ...
                $achou_conta = 1;
            }else {
//Caso não encontre nenhum Cliente no caso acima, então verifico se existe algum nesse outro caso ...
/***************Listagem de Clientes com Crédito C***************/
/*Aqui eu só listo os Clientes que possuem crédito C, q não possuem Débitos em Atraso, que já estão vencidas 
e que a Data de Atualização de Crédito esteje alterada a + que 5 dias*/
                $sql = "SELECT c.id_cliente 
                        FROM clientes c 
                        INNER JOIN contas_receberes cr ON cr.id_conta_receber = cr.id_conta_receber 
                        WHERE c.credito = 'C' 
                        AND c.lembrete_credito = 'S' 
                        AND c.ativo = 1 
                        AND (DATEDIFF('$data_atual', SUBSTRING(credito_data, 1, 10)) > 5 OR SUBSTRING(`credito_data`, 1, 10) = '0000-00-00') 
                        AND cr.id_conta_receber NOT IN (
                                SELECT id_conta_receber 
                                FROM contas_receberes 
                                WHERE `status` < '2' 
                                AND `data_vencimento_alterada` > '$data_atual') 
                        GROUP BY c.`id_cliente` ORDER BY cr.`data_vencimento_alterada` DESC LIMIT 1 ";
                $campos = bancos::sql($sql);
                //Se encontrou algum já exibe o botão ...
                if(count($campos) == 1) $achou_conta = 1;
            }
            if($achou_conta == 1) {
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_listagem_analise_credito" value="Listagem de Análise de Crédito" title="Listagem de Análise de Crédito" style="color:brown" onclick="window.location = '../classes/listagem_clientes.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
    <?
            }
    ?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_cliente.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
<!--Eu passo como parâmetro a variável $id_emp2 porque é exatamente o id de empresa dos menus p/ as funções de bordero e de devolução, 
porque já existe uma variável na sessão que eu chamo de $id_emp, e daí daria conflito com essa variável-->
            <?
//Só não irá exibir esses botões quando o usuário entrar pelo menu de Todas as Empresas ...
                if($id_emp2 != 0) {
            ?>
            <input type='button' name="cmd_opcoes_bordero" value="Opções de Bordero" title="Opções de Bordero" style="color:black" onclick="html5Lightbox.showLightbox(7, '../classes/bordero/opcoes_bordero.php?id_emp2=<?=$id_emp2;?>')" class='botao'>
            <input type='button' name="cmd_ajuste_comissoes" value="Ajuste de Comissões" title="Ajuste de Comissões" onclick="nova_janela('../classes/devolucao/opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
    <?
        if($achou_conta == 1) {
    ?>
    <tr class="erro" align='center'>
        <td colspan="2">
            <marquee scrolldelay="60" loop="100" scrollamount="5">
                <br>EXISTE(M) ANÁLISE(S) DE CRÉDITO À SER(EM) RESOLVIDA(S).
            </marquee>
        </td>
    </tr>
    <?	
        }
    ?>
</table>
<input type="hidden" name="itens" value="<?=$itens;?>">
</form>
</body>
</html>
<?}?>