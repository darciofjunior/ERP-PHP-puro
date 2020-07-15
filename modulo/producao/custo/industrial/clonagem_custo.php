<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/custos.php');
session_start('funcionarios');

//Esse parâmetro é porque essa tela também é puxada de lá da tela de Orçamentos, e daí tem conflito de sessão
if(empty($ignorar_sessao)) {
    if($tela == 1) {//Veio da tela de Todos os P.A.
        segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../../');
    }else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
        segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../../');
    }
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $condicao = (!empty($chkt_so_custos_nao_liberados)) ? "AND pa.`status_custo` = '0' ": '';
    $tela = 1;
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`status_custo`, ed.`razaosocial`, gpa.`nome`, u.`unidade` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE pa.`referencia` LIKE '%$txt_consultar%' 
                    AND pa.`operacao_custo` = '0' 
                    AND pa.`ativo` = '1' 
                    $condicao ORDER BY pa.`referencia` ";
        break;
        case 2:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`status_custo`, ed.`razaosocial`, gpa.`nome`, u.`unidade` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE pa.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pa.`operacao_custo` = '0' 
                    AND pa.`ativo` = '1' 
                    $condicao ORDER BY pa.`referencia` ";
        break;
        case 3:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`status_custo`, ed.`razaosocial`, gpa.`nome`, u.`unidade` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`razaosocial` LIKE '%$txt_consultar%' 
                    WHERE pa.`operacao_custo` = '0' 
                    AND pa.`ativo` = '1' 
                    $condicao ORDER BY pa.`referencia` ";
        break;
        default:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`status_custo`, ed.`razaosocial`, gpa.`nome`, u.`unidade` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE pa.`operacao_custo` = '0' 
                    AND pa.`ativo` = '1' 
                    $condicao ORDER BY pa.`referencia` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'clonagem_custo.php?id_produto_acabado_custo=<?=$_POST['id_produto_acabado_custo'];?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) p/ Clonar Custo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_produto_acabado, id_produto_acabado_custo) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA CLONAR ESSE PRODUTO ACABADO ?')
    if(resposta == false) {
        return false
    }else {
        window.location = 'clonagem_custo.php?passo=2&id_produto_acabado='+id_produto_acabado+'&id_produto_acabado_custo='+id_produto_acabado_custo
    }
}
</Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr>
        <td colspan='5'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Produto(s) Acabado(s) p/ Clonar Custo
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font title='Grupo P.A. (Empresa Divisão)' style='cursor:help'>
                Grupo P.A. (E.D.)
            </font>
        </td>
        <td>
            Ref.
        </td>
        <td>
            Discriminação
        </td>
        <td>
            <font title='Lote do Custo por Unidade' style='cursor:help'>
                Lote / U
            </font>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:prosseguir(".$campos[$i]['id_produto_acabado'].", $id_produto_acabado_custo)";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href="#" class='link'>
                <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='center'>
        <?
//Aki eu pego a qtde do lote do custo do P.A. Corrente
            $sql = "SELECT `qtde_lote` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
            $campos_qtde_lote = bancos::sql($sql);
            echo $campos_qtde_lote[0]['qtde_lote'].' / '.substr($campos[$i]['unidade'], 0, 1);
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'clonagem_custo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>
<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?
    }
}else if($passo == 2) {
    //Verifico se esse PA já possui Custo Industrial ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
            AND `operacao_custo` = '0' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Existe Custo Industrial p/ o PA ...
	//Verifico se o Custo Industrial que o usuário está dentro não é o mesmo do Custo do PA que foi selecionado no Link Tela Anterior ...
        $id_produto_acabado_custo_clonar = $campos[0]['id_produto_acabado_custo'];
	if($_GET['id_produto_acabado_custo'] == $id_produto_acabado_custo_clonar) {
            exit('NÃO É POSSÍVEL CLONAR O PRÓPRIO CUSTO !');
	}
    }else {//Não existe Custo Industrial p/ esse PA ...
	exit('NÃO FOI POSSÍVEL ENCONTRAR O CUSTO DESTE PRODUTO !');
    }
    /***********Apago dados do Custo Industrial atual que o Usuário está dentro, acessando pelo Menu do ERP***********/
    //Apagando Etapa 3 ...
    $sql = "DELETE FROM `pacs_vs_pis` WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    bancos::sql($sql);
    
    //Apagando Etapa 4 ...
    $sql = "DELETE FROM `pacs_vs_maquinas` WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    bancos::sql($sql);
    
    //Apagando Etapa 5 ...
    $sql = "DELETE FROM `pacs_vs_pis_trat` WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    bancos::sql($sql);
    
    //Apagando Etapa 6 ...
    $sql = "DELETE FROM `pacs_vs_pis_usis` WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    bancos::sql($sql);
    
    //Apagando Etapa 7 ...
    $sql = "DELETE FROM `pacs_vs_pas` WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    bancos::sql($sql);
    
    //Clonando a Etapa 2 ...
    $sql = "SELECT * 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_clonar' ";
    $campos = bancos::sql($sql);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $id_produto_insumo = (!empty($campos[0]['id_produto_insumo'])) ? "'".$campos[0]['id_produto_insumo']."'" : 'NULL';
    
    $sql = "UPDATE `produtos_acabados_custos` SET `qtde_lote` = '".$campos[0]['qtde_lote']."', `id_produto_insumo` = $id_produto_insumo, `peso_kg` = '".$campos[0]['peso_kg']."', `peca_corte` = '".$campos[0]['peca_corte']."', `comprimento_1` = '".$campos[0]['comprimento_1']."', `comprimento_2` = '".$campos[0]['comprimento_2']."' WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    
    //Clonando a Etapa 3 ...
    $sql = "SELECT `id_produto_insumo`, `qtde` 
            FROM `pacs_vs_pis` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_clonar' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $sql = "INSERT INTO `pacs_vs_pis` (`id_pac_pi`, `id_produto_acabado_custo`, `id_produto_insumo`, `qtde`) VALUES (NULL, '$_GET[id_produto_acabado_custo]', '".$campos[$i]['id_produto_insumo']."', '".$campos[$i]['qtde']."') ";
        bancos::sql($sql);
    }
    
    //Clonando a Etapa 4 ...
    $sql = "SELECT `id_maquina`, `tempo_hs` 
            FROM `pacs_vs_maquinas` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_clonar' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $sql = "INSERT INTO `pacs_vs_maquinas` (`id_pac_maquina`, `id_produto_acabado_custo`, `id_maquina`, `tempo_hs`) VALUES (NULL, '$_GET[id_produto_acabado_custo]', '".$campos[$i]['id_maquina']."', '".$campos[$i]['tempo_hs']."') ";
        bancos::sql($sql);
    }

    //Clonando a Etapa 5 ...
    $sql = "SELECT `id_produto_insumo`, `fator`, `peso_aco`, `peso_aco_manual` 
            FROM `pacs_vs_pis_trat` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_clonar' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $sql = "INSERT INTO `pacs_vs_pis_trat` (`id_pac_pi_trat`, `id_produto_acabado_custo`, `id_produto_insumo`, `fator`, `peso_aco`, `peso_aco_manual`) VALUES (NULL, '$_GET[id_produto_acabado_custo]', '".$campos[$i]['id_produto_insumo']."', '".$campos[$i]['fator']."', '".$campos[$i]['peso_aco']."', '".$campos[$i]['peso_aco_manual']."') ";
        bancos::sql($sql);
    }
    
    //Clonando a Etapa 6 ...
    $sql = "SELECT `id_produto_insumo`, `qtde` 
            FROM `pacs_vs_pis_usis` 
            WHERE `id_produto_acabado_custo` = $id_produto_acabado_custo_clonar ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $sql = "INSERT INTO `pacs_vs_pis_usis` (`id_pac_pi_usi`, `id_produto_acabado_custo`, `id_produto_insumo`, `qtde`) VALUES (NULL, '$_GET[id_produto_acabado_custo]', '".$campos[$i]['id_produto_insumo']."', '".$campos[$i]['qtde']."') ";
        bancos::sql($sql);
    }
    
    //Clonando a Etapa 7 ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' ";
    $campos_pa                      = bancos::sql($sql);
    $id_produto_acabado_principal   = $campos_pa[0]['id_produto_acabado'];
    
    $sql = "SELECT `id_produto_acabado`, `qtde` 
            FROM `pacs_vs_pas` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo_clonar' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        if(custos::vasculhar_pa($id_produto_acabado_principal, $campos[$i]['id_produto_acabado'])) {
            $alert = 1;
        }else {
            $sql = "INSERT INTO `pacs_vs_pas` (`id_pac_pa`, `id_produto_acabado_custo`, `id_produto_acabado`, `qtde`) VALUES (NULL, '$_GET[id_produto_acabado_custo]', '".$campos[$i]['id_produto_acabado']."', '".$campos[$i]['qtde']."') ";
            bancos::sql($sql);
        }
    }
?>
<Script Language = 'JavaScript'>
    if('<?=$alert;?>' == '1') alert('NEM TODOS OS P.A. DA 7ª ETAPA PODERAM SER CLONADOS !')
    alert('CLONAGEM REALIZADA COM SUCESSO !')
    parent.document.form.submit()
</Script>
<?                
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Clonar Custo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_produto_acabado_custo' value='<?=$_GET['id_produto_acabado_custo'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Clonar Custo
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Produtos Acabados por: Referência' onclick='document.form.txt_consultar.focus()' id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Produtos Acabados por: Discriminação' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Produtos Acabados por: Empresa Divisão' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>
                Empresa Divisão
            </label>
        </td>
        <td>
            <input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title='Só Custos não Liberados' id='label4' class='checkbox'>
            <label for='label4'>
                Só Custos não Liberados
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos os Produtos Acabados' class='checkbox' id='label5'>
            <label for='label5'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

* Traz somente P.A(s) do:
<b>* Tipo de O.C. = Industrializado.</b>
</pre>
<?}?>