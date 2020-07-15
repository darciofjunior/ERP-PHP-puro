<?
require('../../../lib/segurancas.php');
require('../../../lib/cascates.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../lib/intermodular.php');

/****************************************************/
//Texto da NF - Caminho NF de Saída e de Devolução ...
/****************************************************/
if($_GET['id_nf'] > 0) {
    //Significa que veio do Menu Abertas / Liberadas
    if($seguranca == 1) {
        $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/abertas_liberadas.php';
    //Significa que veio do Menu de Liberadas / Faturadas
    }else if($seguranca == 2) {
        $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/liberadas_faturadas.php';
    }else if($seguranca == 3) {
        $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/fat_empac_despachada.php';
    }
/****************************************************/
//Texto da NF - Caminho NF Outras / Complementar ...
/****************************************************/
}else if($_GET['id_nf_outra'] > 0) {
    $endereco = '/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php';
}
segurancas::geral($endereco, '../../../');

/********************************Salvando o Texto da Nota Fiscal********************************/
if(!empty($_POST['txt_texto_nf'])) {
    if(!empty($_POST['id_nf'])) {//Texto da NF - Caminho NF de Saída e de Devolução ...
        $sql = "UPDATE `nfs` SET `texto_nf` = '$_POST[txt_texto_nf]' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else if(!empty($_POST['id_nf_outra'])) {//Texto da NF - Caminho NF Outras / Complementar ...
        $sql = "UPDATE `nfs_outras` SET `texto_nf` = '$_POST[txt_texto_nf]' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = ''>
        alert('TEXTO DA NF ALTERADO COM SUCESSO !!!')
        window.opener.location = window.opener.location.href
        window.close()
    </Script>
<?
}
/***********************************************************************************************/


/****************************************************/
//Texto da NF - Caminho NF de Saída e de Devolução ...
/****************************************************/
if($_GET['id_nf'] > 0) {
    //Busca o campo "Texto de NF" que será impresso no corpo ou Dados Adicionais da própria NF ...
    $sql = "SELECT `texto_nf` 
            FROM `nfs` 
            WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(!empty($campos[0]['texto_nf'])) {//Significa que esse Texto já foi preenchido anteriormente nessa NF ...
        $texto_nf = $campos[0]['texto_nf'];
    }else {//Significa que esse Texto nunca foi preenchido e daí eu busco a mascará de Texto diretamente da CFOP da NF
        $sql = "SELECT c.`id_cliente`, c.`id_pais`, c.`id_uf`, c.`isento_st`, nfs.`natureza_operacao`, 
                nfs.`snf_devolvida`, nfs.`data_emissao_snf`, nfs.`status` 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
        $campos_geral           = bancos::sql($sql);
        $id_cliente             = $campos_geral[0]['id_cliente'];
        $id_pais                = $campos_geral[0]['id_pais'];
        $id_uf                  = $campos_geral[0]['id_uf'];
        $isento_cliente_st      = $campos_geral[0]['isento_st'];
        $natureza_operacao      = $campos_geral[0]['natureza_operacao'];
        $snf_devolvida          = $campos_geral[0]['snf_devolvida'];
        $data_emissao_snf       = data::datetodata($campos_geral[0]['data_emissao_snf'], '/');
        $status                 = $campos_geral[0]['status'];

        /**********************************************************************/
        /*****************Macete para vir Texto da Nota Fiscal*****************/
        /**********************************************************************/
        if($status == 6) {//Nota Fiscal de Devolução ...
            if($id_uf == 1) {//Estado de São Paulo ...
                $id_cfop = 139;
            }else {//Fora do Estado de São Paulo ...
                $id_cfop = 147;
            }
        }
        /**********************************************************************/
        
        if($id_cfop > 0) {
            //Impressão do Texto da Nota Fiscal ...
            $sql = "SELECT `descricao` 
                    FROM `cfops` 
                    WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
            $campos_texto = bancos::sql($sql);
            if(!empty($snf_devolvida) && $data_emissao_snf != '00/00/0000') {
                $numero_nf_subst_texto      = $snf_devolvida;
                $data_emissao_subst_texto   = $data_emissao_snf;
                //Tratamento com o Texto gravado atribuido a CFOP ...
                $descricao = str_replace('xx.xxx', $numero_nf_subst_texto, $campos_texto[0]['descricao']);
                $descricao = str_replace('??/??/????', $data_emissao_subst_texto, $descricao);
                $texto_nf = $descricao;
            }else {
                $texto_nf = $campos_texto[0]['descricao'];
            }
        }else {
            if($natureza_operacao == 'BON') {//Nota Fiscal de Bonificação ...
                $texto_nf = 'Nota Fiscal de Remessa de Bonificação.';
            }else {
                $texto_nf = faturamentos::texto_dados_adicionais($_GET[id_nf]);
            }
        }
    }
/****************************************************/
//Texto da NF - Caminho NF Outras / Complementar ...
/****************************************************/
}else if($_GET['id_nf_outra'] > 0) {
    //Busca o campo "Texto de NF" que será impresso no corpo ou Dados Adicionais da própria NF ...
    $sql = "SELECT id_cfop, id_nf_comp, id_nf_outra_comp, texto_nf, base_calculo_icms_comp, 
            valor_icms_comp, base_calculo_icms_st_comp, valor_icms_st_comp, valor_total_produtos_comp, 
            valor_frete_comp, valor_seguro_comp, outras_despesas_acessorias_comp, valor_ipi_comp, 
            valor_total_nota_comp 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos = bancos::sql($sql);
/***************************************************************************/
/******************************NF Complementar******************************/
/***************************************************************************/
//Primeiro verifico se essa NF é Complementar ...
    if($campos[0]['id_nf_comp'] > 0 || $campos[0]['id_nf_outra_comp'] > 0) {//Se sim, então esse Texto é montado de acordo com a NF atual e de Saída complementada.
        if($campos[0]['id_nf_comp'] > 0) {
            //Aqui eu busco alguns dados da NF de Saída ...
            $sql = "SELECT c.id_pais, nfs.id_empresa AS id_empresa_nota, nfs.suframa, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, nnn.numero_nf 
                    FROM `nfs` 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                    WHERE nfs.`id_nf` = '".$campos[0]['id_nf_comp']."' LIMIT 1 ";
            $campos_nf  = bancos::sql($sql);
            //Aqui verifica o Tipo de Nota, var surti efeito na função abaixo ...
            $nota_sgd               = ($campos_nf[0]['id_empresa_nota'] == 1 || $campos_nf[0]['id_empresa_nota'] == 2) ? 'N' : 'S';
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_nf_comp'], 'NF');
        }else if($campos[0]['id_nf_outra_comp'] > 0) {
            //Aqui eu busco alguns dados da NF Outra ...
            $sql = "SELECT c.id_pais, nfso.id_empresa AS id_empresa_nota, DATE_FORMAT(nfso.data_emissao, '%d/%m/%Y') AS data_emissao, nnn.numero_nf 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota 
                    INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente 
                    WHERE nfso.`id_nf_outra` = '".$campos[0]['id_nf_outra_comp']."' LIMIT 1 ";
            $campos_nf  = bancos::sql($sql);
            //Aqui verifica o Tipo de Nota, var surti efeito na função abaixo ...
            $nota_sgd               = ($campos_nf[0]['id_empresa_nota'] == 1 || $campos_nf[0]['id_empresa_nota'] == 2) ? 'N' : 'S';
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_nf_outra_comp'], 'NFO');
        }
        if(!empty($campos[0]['texto_nf'])) {//Significa que esse Texto já foi preenchido anteriormente nessa NF ...
            $texto_nf = $campos[0]['texto_nf'];
        }else {//Significa que esse Texto nunca foi preenchido e daí eu busco a mascará de Texto diretamente da CFOP da NF
//Antes eu verifico se foi preenchido algum Campo Complementar ...
            if($campos[0]['base_calculo_icms_comp'] == 0 && $campos[0]['valor_icms_comp'] == 0 && $campos[0]['base_calculo_icms_st_comp'] == 0 && $campos[0]['valor_icms_st_comp'] == 0 && $campos[0]['valor_total_produtos_comp'] == 0 && $campos[0]['valor_frete_comp'] == 0 && $campos[0]['outras_despesas_acessorias_comp'] == 0 && $campos[0]['valor_ipi_comp'] == 0 && $campos[0]['valor_total_nota_comp'] == 0) {
?>
            <Script Language = 'JavaScript'>
                alert('PREENCHA UM CAMPO A SER COMPLEMENTADO NA NF !')
                window.close()
            </Script>
<?
                exit;
            }
/******************************Montagem do Texto******************************/
            $cabecalho_texto = 'NF. EMITIDA EM VIRTUDE DO DESTAQUE A MENOR NO(A) ';
            if($campos[0]['base_calculo_icms_comp'] > 0) {
                $cabecalho_texto.= 'BASE CÁLC. ICMS, ';
                $corpo_texto.= '<p>TOTAL DA (BASE CÁLC. ICMS) CORRETA: '.number_format(($calculo_total_impostos['base_calculo_icms'] + $campos[0]['base_calculo_icms_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DA (BASE CÁLC. ICMS) DESTACADA: '.number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DA (BASE CÁLC. ICMS) COMPLEMENTAR: '.number_format($campos[0]['base_calculo_icms_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_icms_comp'] > 0) {
                $cabecalho_texto.= 'VALOR DO ICMS, ';
                $corpo_texto.= '<p>TOTAL DO (VALOR DO ICMS) CORRETO: '.number_format(($calculo_total_impostos['valor_icms'] + $campos[0]['valor_icms_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR DO ICMS) DESTACADO: '.number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR DO ICMS) COMPLEMENTAR: '.number_format($campos[0]['valor_icms_comp'], 2, ',', '.');
            }
            if($campos[0]['base_calculo_icms_st_comp'] > 0) {
                $cabecalho_texto.= 'BASE CÁLC. ICMS SUBST., ';
                $corpo_texto.= '<p>TOTAL DA (BASE CÁLC. ICMS SUBST.) CORRETA: '.number_format(($calculo_total_impostos['base_calculo_icms_st'] + $campos[0]['base_calculo_icms_st_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DA (BASE CÁLC. ICMS SUBST.) DESTACADA: '.number_format($calculo_total_impostos['base_calculo_icms_st'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DA (BASE CÁLC. ICMS SUBST.) COMPLEMENTAR: '.number_format($campos[0]['base_calculo_icms_st_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_icms_st_comp'] > 0) {
                $cabecalho_texto.= 'VALOR ICMS SUBST., ';
                $corpo_texto.= '<p>TOTAL DO (VALOR ICMS SUBST.) CORRETO: '.number_format(($calculo_total_impostos['valor_icms_st_todos_itens'] + $campos[0]['valor_icms_st_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR ICMS SUBST.) DESTACADO: '.number_format($calculo_total_impostos['valor_icms_st_todos_itens'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR ICMS SUBST.) COMPLEMENTAR: '.number_format($campos[0]['valor_icms_st_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_total_produtos_comp'] > 0) {
                $cabecalho_texto.= 'VALOR TOTAL DOS PRODUTOS, ';
                $corpo_texto.= '<p>TOTAL DO (VALOR TOTAL DOS PRODUTOS) CORRETO: '.number_format(($calculo_total_impostos['valor_total_produtos'] + $campos[0]['valor_total_produtos_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DOS PRODUTOS) DESTACADO: '.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DOS PRODUTOS) COMPLEMENTAR: '.number_format($campos[0]['valor_total_produtos_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_frete_comp'] > 0) {
                $cabecalho_texto.= 'VALOR DO FRETE, ';
                $corpo_texto.= '<p>TOTAL DO (VALOR DO FRETE) CORRETO: '.number_format(($calculo_total_impostos['valor_frete'] + $campos[0]['valor_frete_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR DO FRETE) DESTACADO: '.number_format($calculo_total_impostos['valor_frete'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR DO FRETE) COMPLEMENTAR: '.number_format($campos[0]['valor_frete_comp'], 2, ',', '.');
            }
            if($campos[0]['outras_despesas_acessorias_comp'] > 0) {
                $cabecalho_texto.= 'OUTRAS DESPESAS ACESSÓRIAS, ';
                $corpo_texto.= '<p>TOTAL DAS (OUTRAS DESPESAS ACESSÓRIAS) CORRETA: '.number_format(($calculo_total_impostos['outras_despesas_acessorias'] + $campos[0]['outras_despesas_acessorias_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DAS (OUTRAS DESPESAS ACESSÓRIAS) DESTACADA: '.number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DAS (OUTRAS DESPESAS ACESSÓRIAS) COMPLEMENTAR: '.number_format($campos[0]['outras_despesas_acessorias_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_ipi_comp'] > 0) {
                $cabecalho_texto.= 'VALOR TOTAL DO IPI, ';
                $corpo_texto.= '<p>TOTAL DO (VALOR TOTAL DO IPI) CORRETO: '.number_format(($calculo_total_impostos['valor_ipi'] + $campos[0]['valor_ipi_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DO IPI) DESTACADO: '.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DO IPI) COMPLEMENTAR: '.number_format($campos[0]['valor_ipi_comp'], 2, ',', '.');
            }
            if($campos[0]['valor_total_nota_comp'] > 0) {
                $cabecalho_texto.= 'VALOR TOTAL DA NOTA, ';
                $corpo_texto.= '<p>TOTAL DO (VALOR TOTAL DA NOTA) CORRETO: '.number_format(($calculo_total_impostos['valor_total_nota'] + $campos[0]['valor_total_nota_comp']), 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DA NOTA) DESTACADO: '.number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
                $corpo_texto.= '<br>TOTAL DO (VALOR TOTAL DA NOTA) COMPLEMENTAR: '.number_format($campos[0]['valor_total_nota_comp'], 2, ',', '.');
            }
            $cabecalho_texto = substr($cabecalho_texto, 0, strlen($cabecalho_texto) - 2);
            $cabecalho_texto.= ' EM NOSSA NF. DE N.º '.$campos_nf[0]['numero_nf'].' EMITIDA EM '.$campos_nf[0]['data_emissao'].', CONFORME SEGUE DEMONSTRADO: ';
            $texto_nf = $cabecalho_texto.$corpo_texto;
        }
    }else {
        if(!empty($campos[0]['texto_nf'])) {//Significa que esse Texto já foi preenchido anteriormente nessa NF ...
            $texto_nf = $campos[0]['texto_nf'];
        }else {//Significa que esse Texto nunca foi preenchido e daí eu busco a mascará de Texto diretamente da CFOP da NF
            if($campos[0]['id_cfop'] == 161) {//Aqui eu verifico se a NF é de Importação ...
                //Aqui eu pego o Total de Pis e Total de Cofins dos Itens cadastrados na NF de Importação ...
                $sql = "SELECT SUM(`pis`) AS total_pis, SUM(`cofins`) AS total_cofins, SUM(`despesas_aduaneiras`) AS total_despesas_aduaneiras 
                        FROM `nfs_outras_itens` 
                        WHERE `id_nf_outra` = '$_GET[id_nf_outra]' ";
                $campos_impostos = bancos::sql($sql);
                if($campos_impostos[0]['total_pis'] == 0 || $campos_impostos[0]['total_cofins'] == 0 || $campos_impostos[0]['total_despesas_aduaneiras'] == 0) {
?>
                    <Script Language = 'JavaScript'>
                        alert('DIGITE O(S) VALOR(ES) DE PIS, COFINS E DESPESAS ADUANEIRAS DA NOTA FISCAL !')
                        window.close()
                    </Script>
<?
                    exit;
                }
            }
            $sql = "SELECT `descricao` 
                    FROM `cfops` 
                    WHERE `id_cfop` = '".$campos[0]['id_cfop']."' LIMIT 1 ";
            $campos_texto = bancos::sql($sql);
            $texto_nf = str_replace('PIS = R$ ???,??', 'PIS = R$ '.number_format($campos_impostos[0]['total_pis'], '2', ',', '.'), $campos_texto[0]['descricao']);
            $texto_nf = str_replace('Cofins = R$ ???,??', 'Cofins = R$ '.number_format($campos_impostos[0]['total_cofins'], '2', ',', '.'), $texto_nf);
            $texto_nf = str_replace('Despesas Aduaneiras = R$ ???,??', 'Despesas Aduaneiras = R$ '.number_format($campos_impostos[0]['total_despesas_aduaneiras'], '2', ',', '.'), $texto_nf);
        }
    }
}
?>
<html>
<head>
<title>.:: Preencher Texto da NF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Texto da NF ...
    if(document.form.txt_texto_nf.value == '') {
        alert('DIGITE O TEXTO DA NF !')
        document.form.txt_texto_nf.focus()
        document.form.txt_texto_nf.select()
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_texto_nf.focus()' topmargin='20'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
<input type='hidden' name='seguranca' value='<?=$_GET['seguranca'];?>'>
<!--******************************************************-->
<table width='80%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Preencher Texto da NF
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Texto da NF:</b>
        </td>
        <td>
            <textarea name='txt_texto_nf' title='Digite o Texto da NF' cols='80' rows='8' maxlength='1000' class='caixadetexto'><?=$texto_nf;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_texto_nf.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<font color='darkblue'><b>
* Lembre-se de verificar o Texto da Nota.
<font color='brown'>
*** Quando não valer a NF do Cliente <b>"SNF"</b>, então tem que aparecer os dizeres da Natureza de Operação 
no Campo Texto da NF.
</font>
</pre>