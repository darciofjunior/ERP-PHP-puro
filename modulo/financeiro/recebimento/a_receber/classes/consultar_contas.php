<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');

if($itens != 1) segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que o usu�rio j� passou pela tela de contas � receber antes
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
<title>.:: Consultar Conta(s) � Receber ::.</title>
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
//Se a Data de Emiss�o estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
    if(document.form.txt_data_emissao_inicial.value != '' || document.form.txt_data_emissao_final.value != '') {
//Data de Emiss�o Inicial
        if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISS�O INICIAL')) {
            return false
        }
//Data de Emiss�o Final
        if(!data('form', 'txt_data_emissao_final', '4000', 'EMISS�O FINAL')) {
            return false
        }
//Compara��o com as Datas ...
        var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
        var data_emissao_final = document.form.txt_data_emissao_final.value
        data_emissao_inicial = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
        data_emissao_final = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
        data_emissao_inicial = eval(data_emissao_inicial)
        data_emissao_final = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA DE EMISS�O FINAL INV�LIDA !!!\n DATA DE EMISS�O FINAL MENOR DO QUE A DATA DE EMISS�O INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas � > do que 3 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_emissao_inicial, document.form.txt_data_emissao_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
    }
//Se a Data de Vencimento estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
    if(document.form.txt_data_vencimento_inicial.value != '' || document.form.txt_data_vencimento_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
            return false
        }
//Compara��o com as Datas ...
        var data_vencimento_inicial = document.form.txt_data_vencimento_inicial.value
        var data_vencimento_final = document.form.txt_data_vencimento_final.value
        data_vencimento_inicial = data_vencimento_inicial.substr(6,4) + data_vencimento_inicial.substr(3,2) + data_vencimento_inicial.substr(0,2)
        data_vencimento_final = data_vencimento_final.substr(6,4) + data_vencimento_final.substr(3,2) + data_vencimento_final.substr(0,2)
        data_vencimento_inicial = eval(data_vencimento_inicial)
        data_vencimento_final = eval(data_vencimento_final)

        if(data_vencimento_final < data_vencimento_inicial) {
            alert('DATA DE VENCIMENTO FINAL INV�LIDA !!!\n DATA DE VENCIMENTO FINAL MENOR DO QUE A DATA DE VENCIMENTO INICIAL !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas � > do que 3 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_vencimento_inicial, document.form.txt_data_vencimento_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
    }
//Se a Data de Recebimento estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
    if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_inicial', '4000', 'RECEBIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_final', '4000', 'RECEBIMENTO FINAL')) {
            return false
        }
//Compara��o com as Datas ...
        var data_inicial = document.form.txt_data_inicial.value
        var data_final = document.form.txt_data_final.value
        data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
        data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
        data_inicial = eval(data_inicial)
        data_final = eval(data_final)

        if(data_final < data_inicial) {
            alert('DATA DE RECEBIMENTO FINAL INV�LIDA !!!\n DATA DE RECEBIMENTO FINAL MENOR DO QUE A DATA DE RECEBIMENTO INICIAL !')
            document.form.txt_data_final.focus()
            document.form.txt_data_final.select()
            return false
        }
/**Verifico se o intervalo entre Datas � > do que 3 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
        var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
        if(dias > 1095) {
            alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A 3 ANOS !')
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
            Consultar Conta(s) � Receber 
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
            Descri��o da Conta
        </td>
        <td>
            <input type='text' name="txt_descricao_conta" title="Digite a Descri��o da Conta" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N�mero da Conta
        </td>
        <td>
            <input type='text' name="txt_numero_conta" title="Digite o N�mero da Conta" size="12" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emiss�o
        </td>
        <td>
            <input type='text' name="txt_data_emissao_inicial" title="Digite a Data de Emiss�o Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> at�&nbsp;
            <input type='text' name="txt_data_emissao_final" title="Digite a Data de Emiss�o Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name="txt_data_vencimento_inicial" title="Digite a Data de Vencimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> at�&nbsp;
            <input type='text' name="txt_data_vencimento_final" title="Digite a Data de Vencimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Recebimento
        </td>
        <td>
            <input type='text' name="txt_data_inicial" title="Digite a Data de Recebimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">  at�&nbsp; 
            <input type='text' name="txt_data_final" title="Digite a Data de Recebimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>        <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data do Cadastro
        </td>
        <td>
            <input type='text' name="txt_data_cadastro" title="Digite a Data de Cadastro" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_cadastro&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
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
            <input type='checkbox' name='chkt_mostrar' value="1" title='N�o mostrar atrasados > 60 dias' id='label1' class="checkbox" checked>
            <label for="label1">N�o mostrar atrasados > 60 dias</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_exportacao' value="1" title='Somente Exporta��o' id='label2' class="checkbox">
            <label for="label2">Somente Exporta��o</label>
        </td>
    </tr>
    <?
            $data_atual = date('Y-m-d');
/***************Listagem de Clientes com Cr�dito A e B***************/
/*Aqui eu s� listo os Clientes que possuem cr�dito A e B, q possuem D�bitos em Atraso, que j� est�o vencidas 
e que a Data de Atualiza��o de Cr�dito esteje alterada a + que 5 dias*/
            $sql = "SELECT c.id_cliente 
                    FROM clientes c 
                    INNER JOIN contas_receberes cr ON cr.id_conta_receber = cr.id_conta_receber AND cr.status < '2' AND cr.`data_vencimento_alterada` < '$data_atual' 
                    WHERE c.credito in ('A', 'B') 
                    AND c.lembrete_credito = 'S' 
                    AND c.ativo = '1' 
                    AND (DATEDIFF('$data_atual', SUBSTRING(credito_data, 1, 10)) > 5 OR SUBSTRING(credito_data, 1, 10) = '0000-00-00') 
                    GROUP BY c.id_cliente ORDER BY cr.`data_vencimento_alterada` LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Se encontrou algum j� exibe o bot�o ...
                $achou_conta = 1;
            }else {
//Caso n�o encontre nenhum Cliente no caso acima, ent�o verifico se existe algum nesse outro caso ...
/***************Listagem de Clientes com Cr�dito C***************/
/*Aqui eu s� listo os Clientes que possuem cr�dito C, q n�o possuem D�bitos em Atraso, que j� est�o vencidas 
e que a Data de Atualiza��o de Cr�dito esteje alterada a + que 5 dias*/
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
                //Se encontrou algum j� exibe o bot�o ...
                if(count($campos) == 1) $achou_conta = 1;
            }
            if($achou_conta == 1) {
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_listagem_analise_credito" value="Listagem de An�lise de Cr�dito" title="Listagem de An�lise de Cr�dito" style="color:brown" onclick="window.location = '../classes/listagem_clientes.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
    <?
            }
    ?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_cliente.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
<!--Eu passo como par�metro a vari�vel $id_emp2 porque � exatamente o id de empresa dos menus p/ as fun��es de bordero e de devolu��o, 
porque j� existe uma vari�vel na sess�o que eu chamo de $id_emp, e da� daria conflito com essa vari�vel-->
            <?
//S� n�o ir� exibir esses bot�es quando o usu�rio entrar pelo menu de Todas as Empresas ...
                if($id_emp2 != 0) {
            ?>
            <input type='button' name="cmd_opcoes_bordero" value="Op��es de Bordero" title="Op��es de Bordero" style="color:black" onclick="html5Lightbox.showLightbox(7, '../classes/bordero/opcoes_bordero.php?id_emp2=<?=$id_emp2;?>')" class='botao'>
            <input type='button' name="cmd_ajuste_comissoes" value="Ajuste de Comiss�es" title="Ajuste de Comiss�es" onclick="nova_janela('../classes/devolucao/opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
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
                <br>EXISTE(M) AN�LISE(S) DE CR�DITO � SER(EM) RESOLVIDA(S).
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