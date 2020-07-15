<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/cascates.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/producao.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/clonar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = '<font class="confirmacao">PRODUTO ACABADO CLONADO COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">PRODUTO ACABADO J� EXISTENTE.</font>';

if($passo == 1) {
//Aqui traz todos os PA
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT ed.razaosocial, gpa.nome, pa.id_produto_acabado, pa.origem_mercadoria, pa.referencia, pa.discriminacao, pa.operacao, pa.operacao_custo, u.unidade 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    INNER JOIN unidades u ON u.id_unidade = pa.id_unidade 
                    WHERE pa.referencia like '%$txt_consultar%' 
                    AND pa.ativo = '1' ORDER BY pa.discriminacao ";
        break;
        case 2:
            $sql = "SELECT ed.razaosocial, gpa.nome, pa.id_produto_acabado, pa.origem_mercadoria, pa.referencia, pa.discriminacao, pa.operacao, pa.operacao_custo, u.unidade 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    INNER JOIN unidades u ON u.id_unidade = pa.id_unidade 
                    WHERE pa.discriminacao like '%$txt_consultar%' 
                    AND pa.ativo = '1' ORDER BY pa.discriminacao ";
        break;
        default:
            $sql = "SELECT ed.razaosocial, gpa.nome, pa.id_produto_acabado, pa.origem_mercadoria, pa.referencia, pa.discriminacao, pa.operacao, pa.operacao_custo, u.unidade 
                    FROM produtos_acabados pa 
                    INNER JOIN gpas_vs_emps_divs ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN grupos_pas gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN empresas_divisoes ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    INNER JOIN unidades u ON u.id_unidade = pa.id_unidade 
                    WHERE pa.ativo = '1' ORDER BY pa.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'clonar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
//O sistema s� ir� cair aqui quando o sistema voltar de uma Clonagem Revenda e desejar incluir um PA em um Or�amento ...
function incluir_pa_no_orcamento() {
    var incluir_pa_no_orcamento = '<?=$_GET[incluir_pa_no_orcamento];?>'
    if(incluir_pa_no_orcamento == 'S') html5Lightbox.showLightbox(7, '../../custo/industrial/incluir_pa_do_custo_no_orc.php?id_produto_acabado=<?=$_GET[id_produto_acabado];?>')
}
</Script>
</head>
<body onload='incluir_pa_no_orcamento()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'> 
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Produto(s) Acabado(s) p/ Clonar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Refer�ncia
        </td>
        <td>
            Discrimina��o
        </td>
        <td>
            <p title="Grupo P.A. (Empresa Divis�o)">Grupo P.A. (E.D.)</p>
        </td>
        <td>
            <p title="Peso Unit�rio">P. U.</p>
        </td>
        <td>
            <p title="Opera��o de Custo">O. C.</p>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <p title="Opera��o (Fat)">O. F.</p>
        </td>
        <td>
            <p title="Unidade">U.</p>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'clonar.php?passo=2&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="clonar.php?passo=2&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>" class='link'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td align="left">
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
        </td>
        <td align="left">
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
        ?>
                <p title="Industrializa��o">I</p>
        <?
            }else if($campos[$i]['operacao_custo'] == 1) {
        ?>
                <p title="Revenda">R</p>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='center'>
            <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao'] == 0) {
        ?>
                <p title="Industrializa��o (c/ IPI)">I - C</p>
        <?
            }else if($campos[$i]['operacao'] == 1) {
        ?>
                <p title="Revenda (s/ IPI)">R - S</p>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='center'>
            <p title="<?=$campos[$i]['unidade'];?>"><?=substr($campos[$i]['unidade'], 0, 1)?></p>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'clonar.php'" class='botao'>
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
}else if($passo == 2) {//fim do passo
    //Busco dados do Produto Acabado que o usu�rio deseja clonar ...
    $sql = "SELECT gp.`id_familia`, pa.* 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gp ON gp.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_familia         = $campos[0]['id_familia'];
    $id_produto_insumo  = $campos[0]['id_produto_insumo'];
    $id_gpa_vs_emp_div  = $campos[0]['id_gpa_vs_emp_div'];
    $id_unidade         = $campos[0]['id_unidade'];
    $operacao           = $campos[0]['operacao'];
    $operacao_custo     = $campos[0]['operacao_custo'];
    $operacao_custo_sub = $campos[0]['operacao_custo_sub'];
    $origem_mercadoria  = $campos[0]['origem_mercadoria'];
    $codigo_fornecedor  = $campos[0]['codigo_fornecedor'];
    $referencia         = $campos[0]['referencia'];
    $discriminacao      = $campos[0]['discriminacao'];
    
    //Se esse PA tamb�m � um PI, ent�o eu busco o seu fornecedor Default, se � que esse possui ...
    if($id_produto_insumo > 0) {
        $sql = "SELECT f.`razaosocial`, pi.`id_fornecedor_default` 
                FROM `produtos_insumos` pi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pi.`id_fornecedor_default` 
                WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
                AND pi.`id_fornecedor_default` > '0' LIMIT 1 ";
        $campos_fornecedor_default  = bancos::sql($sql);
        $fornecedor_default         = strtr($campos_fornecedor_default[0]['razaosocial'], '()', '[]');
        $id_fornecedor_default      = $campos_fornecedor_default[0]['id_fornecedor_default'];
    }
?>
<html>
<title>.:: Clonar Produto(s) Acabado(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Grupo P.A. vs Empresa Divis�o
    if(!combo('form', 'cmb_gpas_vs_emps_divs', '', 'SELECIONE UM GRUPO P.A. (EMPRESA DIVIS�O) !')) {
        return false
    }
//Opera��o de Custo
    if(!combo('form', 'cmb_operacao_custo', '', 'SELECIONE UMA OPERA��O DE CUSTO !')) {
        return false
    }
//Sub-Opera��o de Custo
/*Se a Opera��o de Custo selecionada foi Industrial, ent�o eu for�o o usu�rio a preencher uma 
Sub-Opera��o de Custo*/
    if(document.form.cmb_operacao_custo.value == 0) {//Industrial
        if(!combo('form', 'cmb_operacao_custo_sub', '', 'SELECIONE UMA SUB-OPERA��O DE CUSTO !')) {
            return false
        }
    }
//Unidade
    if(!combo('form', 'cmb_unidade', '', 'SELECIONE UMA UNIDADE !')) {
        return false
    }
//Origem da Mercadoria ...
    if(!combo('form', 'cmb_origem_mercadoria', '', 'SELECIONE A ORIGEM DA MERCADORIA !')) {
        return false
    }
//Opera��o FAT
    if(!combo('form', 'cmb_operacao', '', 'SELECIONE UMA OPERA��O !')) {
        return false
    }
//C�digo do Fornecedor ...
    if(document.form.txt_codigo_fornecedor.value != '') {
        if(!texto('form', 'txt_codigo_fornecedor', '1', '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._-', 'C�DIGO DO FORNECEDOR', '2')) {
            return false
        }
    }
//Refer�ncia ...
    if(!texto('form', 'txt_referencia', '3', "-=!@������{} 1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM,.'��������������������������.,%&*$()@#<>���:;\/", 'REFER�NCIA', '1')) {
        return false
    }
//Discrimina��o ...
    if(!texto('form', 'txt_discriminacao', '3', "+-=!@������{} 1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM��������������������������.%&*$()@#<>����:;\/", 'DISCRIMINA��O', '1')) {
        return false
    }
//Peso Unit�rio Kg
    if(document.form.txt_peso_unitario.value != '') {
        if(!texto('form', 'txt_peso_unitario', '1', '1234567890,.', 'PESO UNIT�RIO KG', '2')) {
            return false
        }
    }
/*Pe�as por Jogo - sempre ser� obrigat�rio ser preenchido se a Unidade for Jogo ou a Fam�lia 
do Produto = Machos ...*/
    var id_familia = eval('<?=$id_familia;?>')
    if(document.form.cmb_unidade.value == 12 || id_familia == 9) {
        if(!texto('form', 'txt_pecas_por_jogo', '1', '1234567890', 'PE�AS POR JOGO', '1')) {
            return false
        }
        //Nunca este campo "Pe�as por Jogo" pode ser igual = Zero ...
        if(document.form.txt_pecas_por_jogo.value == 0) {
            alert('PE�AS POR JOGO INV�LIDO !!!\n\nPE�AS POR JOGO N�O PODE SER IGUAL A ZERO !')
            document.form.txt_pecas_por_jogo.focus()
            document.form.txt_pecas_por_jogo.select()
            return false
        }
    }
//Altura
    if(document.form.txt_altura.value != '') {
        if(!texto('form', 'txt_altura', '1', '1234567890', 'ALTURA', '1')) {
            return false
        }
    }
//Largura
    if(document.form.txt_largura.value != '') {
        if(!texto('form', 'txt_largura', '1', '1234567890', 'LARGURA', '1')) {
            return false
        }
    }
//Comprimento
    if(document.form.txt_comprimento.value != '') {
        if(!texto('form', 'txt_comprimento', '1', '1234567890', 'COMPRIMENTO', '2')) {
            return false
        }
    }
/*Se for igual significa que o usu�rio n�o fez nenhuma altera��o ainda no c�digo do Fornecedor, fa�o esse 
controle porque da� fica mais f�cil a n�vel de banco e tamb�m porque � normal as vezes o usu�rio 
entrar na tela e acabar esquecendo de alterar o c�digo do Fornecedor ...*/
    if(document.form.txt_codigo_fornecedor_inicial.value != '') {
        if(document.form.txt_codigo_fornecedor_inicial.value == document.form.txt_codigo_fornecedor.value) {
            alert('O C�DIGO DO FORNECEDOR AINDA N�O FOI ALTERADO !')
            document.form.txt_codigo_fornecedor.focus()
            document.form.txt_codigo_fornecedor.select()
            return false
        }
    }
    
/*Se for igual significa que o usu�rio n�o fez nenhuma altera��o ainda na refer�ncia, fa�o esse
controle porque da� fica mais f�cil a n�vel de banco e tamb�m porque � normal as vezes o usu�rio 
entrar na tela e acabar esquecendo de alterar a refer�ncia*/

//S� far� essa verifica��o para produtos que sejam diferentes de ESP
    if(document.form.txt_referencia_inicial.value != 'ESP') {
        if(document.form.txt_referencia_inicial.value == document.form.txt_referencia.value) {
            alert('A REFER�NCIA AINDA N�O FOI ALTERADA !')
            document.form.txt_referencia.focus()
            document.form.txt_referencia.select()
            return false
        }
    }
	
/*Se for igual significa que o usu�rio n�o fez nenhuma altera��o ainda na discriminacao, fa�o esse
controle porque da� fica mais f�cil a n�vel de banco e tamb�m porque � normal as vezes o usu�rio 
entrar na tela e acabar esquecendo de alterar a discrimina��o*/
    if(document.form.txt_discriminacao_inicial.value == document.form.txt_discriminacao.value) {
        alert('A DISCRIMINA��O AINDA N�O FOI ALTERADA !')
        document.form.txt_discriminacao.focus()
        document.form.txt_discriminacao.select()
        return false
    }
/*********************************************************************************************************/
    //Somente quando a OC do PA que est� sendo clonado for = 'Rev' que o Sistema far� a verifica��o abaixo ...
    if(document.form.cmb_operacao_custo.value == 1) {
        /*Se existir Fornecedor Default do PA "PIPA" e o Usu�rio ainda n�o deu nenhuma resposta concernente a 
        pergunta "De Clonagem do Fornecedor", ent�o exibo a mensagem abaixo ...*/
        if(document.form.hdd_fornecedor_default.value > 0 && document.form.hdd_clonar_fornecedor_default.value == '') {
            var resposta = confirm('DESEJA CLONAR ESSE FORNECEDOR DEFAULT "'+'<?=$fornecedor_default;?>'+'" P/ O NOVO PA ?')
            document.form.hdd_clonar_fornecedor_default.value = (resposta == true) ? 'S' : 'N'
        }
    }
/********************************************Fornecedor Default*******************************************/
//Habilita a caixa de texto referencia para submeter valor
    document.form.txt_referencia.disabled = false
    limpeza_moeda('form', 'txt_peso_unitario, ')
}

function check_referencia() {
    if (document.form.chkt_referencia.checked == true ) {
        document.form.txt_referencia.disabled   = true
        document.form.txt_referencia.value      = 'ESP'
    }else {
        document.form.txt_referencia.value      = '<?=$referencia;?>'
        document.form.txt_referencia.disabled   = false
        document.form.txt_referencia.focus()
    }
}

function controle_operacao_custo() {
    var operacao_custo = eval(document.form.cmb_operacao_custo.value)
    if(operacao_custo == 0) {//Quando a Opera��o de Custo = Industrial, eu habilito a Sub-Opera��o de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className = 'caixadetexto'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value = '<?=$operacao_custo_sub;?>'
        document.form.cmb_operacao_custo_sub.disabled = false
//Quando a Opera��o de Custo = Revenda, eu desabilito a Sub-Opera��o de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value = ''
        document.form.cmb_operacao_custo_sub.disabled = true
    }
}
</Script>
<body onload='check_referencia();controle_operacao_custo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()' enctype='multipart/form-data'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'> 
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='3'>
            Clonar Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A. (Empresa Divis�o):</b>
        </td>
        <td>
            <b>Opera��o de Custo / Sub-Opera��o de Custo:</b>
        </td>
        <td>
            <b>Unidade:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name="cmb_gpas_vs_emps_divs" title="Selecione o Grupo P.A. (Empresa Divis�o)" class='combo'>
            <?
                $sql = "SELECT ged.`id_gpa_vs_emp_div`, CONCAT(gpa.`nome`, ' (', ed.razaosocial, ') ') AS rotulo 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                        WHERE (gpa.`ativo` = '1' OR (gpa.`ativo` = '0' AND ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div')) 
                        AND gpa.`ativo` = '1' ORDER BY rotulo ";
                echo combos::combo($sql, $id_gpa_vs_emp_div);
            ?>
            </select>
        </td>
        <td>
            <select name="cmb_operacao_custo" title="Selecione a Opera��o de Custo" onchange="controle_operacao_custo()" class='combo'>
                <?
                    if($operacao_custo == 0) {
                        $selectedi = 'selected';
                    }else if($operacao_custo == 1) {
                        $selectedr = 'selected';
                    }
                ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedi;?>>Industrializa��o</option>
                <option value='1' <?=$selectedr;?>>Revenda</option>
            </select>
            &nbsp;
            <select name="cmb_operacao_custo_sub" title="Selecione a Sub-Opera��o" class='combo'>
            <?
                if($operacao_custo_sub == 0) {
                    $selectedii = 'selected';
                }else if($operacao_custo_sub == 1) {
                    $selectedir = 'selected';
                }
            ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedii;?>>Industrializa��o</option>
                <option value='1' <?=$selectedir;?>>Revenda</option>
            </select>
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `unidade` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `unidade` ";
                echo combos::combo($sql, $id_unidade);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#0000FF'>
                <b>Origem da Mercadoria:</b>
            </font>
        </td>
        <td colspan='2'>
            <font color='#0000FF'>
                <b>Opera��o (Fat):</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_origem_mercadoria' title='Selecione a Origem da Mercadoria' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_origem_mercadoria  = array_sistema::origem_mercadoria();
                    foreach($vetor_origem_mercadoria as $indice => $id_origem_mercadoria) {
                        $selected = ($origem_mercadoria == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$id_origem_mercadoria."</option>";
                    }
                ?>
            </select>
        </td>
        <td colspan='2'>
            <select name='cmb_operacao' title='Selecione a Opera��o' class='combo'>
                <?
                    if($operacao == 0) {
                        $selected_i = 'selected';
                    }else if($operacao == 1) {
                        $selected_r = 'selected';
                    }
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' <?=$selected_i;?>>Industrializa��o (c/ IPI)</option>
                <option value='1' <?=$selected_r;?>>Revenda (s/ IPI)</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            C�digo do Fornecedor:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Refer�ncia:</b>
        </td>
        <td colspan='2'>
            <b>Discrimina��o:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_codigo_fornecedor' value='<?=$codigo_fornecedor;?>' title='Digite o C�digo do Fornecedor' maxlength='15' size='16' class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_referencia' value='<?=$referencia;?>' title='Digite a Refer�ncia' size='20' maxlength='30' class='caixadetexto'>
            <input type='checkbox' name='chkt_referencia' onclick='check_referencia()' id='label' class='checkbox'>
            <label for='label'>ESP</label>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_discriminacao' value='<?=$discriminacao;?>' title='Digite a Discrimina��o' size='60' maxlength='100' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Foto:
        </td>
        <td colspan='2'>
            Foto Atual:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Unit�rio (Kg):
        </td>
        <td colspan='2'>
            Pe�as por Jogo:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_peso_unitario' maxlength='9' size='15' title='Digite o Peso Unit�rio (Kg)' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class='caixadetexto'>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_pecas_por_jogo' title='Digite o Pe�as por Jogo' size='4' maxlenght='2' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura:
        </td>
        <td>
            Largura:
        </td>
        <td>
            Comprimento:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_altura' title='Digite a Altura' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
        <td>
            <input type='text' name='txt_largura' title='Digite a Largura' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
        <td>
            <input type='text' name='txt_comprimento' title='Digite o Comprimento' size='12' maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Observa��o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name='txt_observacao_produto' cols='120' rows='3' title='Digite a Observa&ccedil;&atilde;o' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'clonar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_clonar' value='Clonar' title='Clonar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<!--Esse hidden guarda o c�digo do fornecedor original do BD e j� serve para comparar com a caixa de c�digo do 
fornecedor vis�vel ao usu�rio e verifica se o usu�rio chegou a fazer alguma altera��o nesse c�digo do Fornecedor-->
<input type='hidden' name='txt_codigo_fornecedor_inicial' value='<?=$codigo_fornecedor;?>'>
<!--Esse hidden guarda a refer�ncia original do BD e j� serve para comparar com a caixa de refer�ncia 
est� vis�vel ao usu�rio e verifica se o usu�rio chegou a fazer alguma altera��o nessa refer�ncia-->
<input type='hidden' name='txt_referencia_inicial' value='<?=$referencia;?>'>
<!--Esse hidden guarda a discrimina��o original do BD e j� serve para comparar com a caixa de discrimina��o 
est� vis�vel ao usu�rio e verifica se o usu�rio chegou a fazer alguma altera��o nessa discrimina��o-->
<input type='hidden' name='txt_discriminacao_inicial' value='<?=$discriminacao;?>'>
<!--Guardo esse "id_fornecedor_default" aqui nesse hidden, p/ facilitar a vida se submeter esse valor ...-->
<input type='hidden' name='hdd_fornecedor_default' value='<?=$id_fornecedor_default;?>'>
<!--**************Controle de Tela**************-->
<input type='hidden' name='hdd_clonar_fornecedor_default'>
<input type='hidden' name='hdd_produto_insumo_clonado' value='<?=$id_produto_insumo;?>'>
<!--******Ser� utilizada no pr�ximo passo*******-->
<input type='hidden' name='id_familia' value='<?=$id_familia;?>'>
<!--********************************************-->
</form>
</body>
</html>
<pre>
<font color='red'><b>Observa��o:</b></font>

* Mantenha a ordem das palavras substituindo apenas as medidas na discrimina��o, pois facilitar� 
  mais a busca desse produto no sistema.
</pre>
<?
}else if($passo == 3) {
/*Aqui verifico se o novo P.A. j� existe, lembrando que o controle por refer�ncia s� me interessa se o usu�rio colocar 
uma refer�ncia diferente de ESP, porque sen�o sempre o sistema ir� falar produto existente ...*/
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE ((`referencia` = '$_POST[txt_referencia]' AND `referencia` <> 'ESP') OR (`discriminacao` = '$_POST[txt_discriminacao]')) 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//P.A. n�o existe ...
        //Aqui cria um novo produto acabado que acabou de ser clonado ...
        $data_sys = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `produtos_acabados` (`id_produto_acabado`, `id_gpa_vs_emp_div`, `id_unidade`, `id_funcionario`, `operacao`, `operacao_custo`, `operacao_custo_sub`, `origem_mercadoria`, `id_nivel`, `codigo_fornecedor`, `referencia`, `discriminacao`, `peso_unitario`, `pecas_por_jogo`, `altura`, `largura`, `comprimento`, `preco_export`, `observacao`, `data_sys`, `ativo`) VALUES (NULL, '$_POST[cmb_gpas_vs_emps_divs]', '$_POST[cmb_unidade]', '$_SESSION[id_funcionario]', '$_POST[cmb_operacao]', '$_POST[cmb_operacao_custo]', '$_POST[cmb_operacao_custo_sub]', '$_POST[cmb_origem_mercadoria]', '', '$_POST[txt_codigo_fornecedor]', '$_POST[txt_referencia]', '$_POST[txt_discriminacao]', '$_POST[txt_peso_unitario]', '$_POST[txt_pecas_por_jogo]', '$_POST[txt_altura]', '$_POST[txt_largura]', '$_POST[txt_comprimento]', '', '$_POST[txt_observacao_produto]', '$data_sys', '1') ";
        bancos::sql($sql);
        $id_produto_acabado = bancos::id_registro();
        genericas::atualizar_pas_no_site_area_cliente($id_produto_acabado);
            
        /*Se o PA for: 

        * Normal de Linha;
        * Fam�lias Diferentes de Componente / M�o Obra;
        * SEM Blank na Discrimina��o;
         * 

        Ent�o existir� C�digo de Barras ...*/

        if($_POST['txt_referencia'] != 'ESP' && ($_POST['id_familia'] != 23 && $_POST['id_familia'] != 24 && $_POST['id_familia'] != 25) && strpos($_POST['txt_discriminacao'], 'BLANK') === false) {
            //Busco o Primeiro N.� de C�digo de Barras que esteja dispon�vel na Tabela "codigos_barras" ...
            $sql = "SELECT `codigo_barra` 
                    FROM `codigos_barras` 
                    WHERE `usado` = 'N' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            if(count($campos_codigo_barra) == 1) {//Significa que existe 1 c�digo de Barra dispon�vel ...
                $codigo_barra = $campos_codigo_barra[0]['codigo_barra'];
            }else {//Significa que j� n�o temos mais c�digos dispon�veis ...
                echo 'CHEGAMOS AO LIMITE DE 9.999 C�DIGO DE BARRA(S).';
                exit;
                //Teria que gerar o 10000 ???
                
                /*$codigo_barra = producao::gerador_codigo_barra($id_produto_acabado);
                /*Essa � uma garantia de que estou trabalhando exatamente com 13 d�gitos, �s vezes o sistema pode
                gerar com 14 d�gitos o c�digo devido estourar o limite de c�digos que ainda hoje � de 9999
                o que seria um erro ...
                $codigo_barra = substr($codigo_barra, 0, 13);*/
            }

            /*Verifico se esse C�digo de Barras que acabou de ser gerado acima, j� foi utilizado 
            por algum PA no sistema ...*/
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            if(count($campos_codigo_barra) == 0) {
                //Atualizo os Dados do PA com o Novo C�digo de Barras ...
                $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '$codigo_barra' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                bancos::sql($sql);
                
                /*Atualizo a Tabela "codigos_barras" marcando o campo "usado" como sendo = 'S' p/ que este 
                N.� n�o seja sugerido futuramente ...*/
                $sql = "UPDATE `codigos_barras` SET `usado` = 'S' WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
                bancos::sql($sql);
            }else {
?>
                <Script Language = 'JavaScript'>
                    alert('C�DIGO DE BARRA(S) J� EXISTENTE !')
                </Script>
<?
            }
        }
/*Aqui eu Insiro o Produto no Estoque com a Qtde Zero p/ que este possa ser exibido no Or�amento e n�o seja 
necess�rio fazer uma Manipula��o de Zero do mesmo ...*/
        $sql = "INSERT INTO `estoques_acabados` (`id_estoque_acabado`, `id_produto_acabado`, `qtde`, `data_atualizacao`, `responsavel`) VALUES (NULL, '$id_produto_acabado', '0', '$data_sys', 'Produto Novo') ";
        bancos::sql($sql);

        //Somente se a OC do PA = 'Revenda' que o Sistema cair� nesse Caminho ...
        if($_POST[cmb_operacao_custo] == 1) {
            //1) Gera o PI atrav�s do PA ...
            $id_produto_insumo = intermodular::importar_patopi($id_produto_acabado);//Aqui � a fun��o que importa o PA para PI ...
            //2) Vinculo o Novo PI que foi gerado no Novo PA que foi Clonado ...
            $sql = "UPDATE `produtos_acabados` SET `id_produto_insumo` = '$id_produto_insumo' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
            /*3) Busco dados da Lista de Pre�o do PI que foi clonado no seu Fornecedor Default, esses dados ser�o 
            replicados para o Novo PI no Fornecedor Default que tamb�m foi clonado ...*/
            $sql = "SELECT * 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$_POST[hdd_fornecedor_default]' 
                    AND `id_produto_insumo` = '$_POST[hdd_produto_insumo_clonado]' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            //3.1) Inserindo um Registro na Lista de Pre�o do Novo PI no Fornecedor Default ...
            $sql = "INSERT INTO `fornecedores_x_prod_insumos` (`id_fornecedor_prod_insumo`, `id_fornecedor`, 
                    `id_produto_insumo`, 
                    `prazo_pgto_ddl`, `desc_vista`, `desc_sgd`, `ipi`, `icms`, `reducao`, `iva`, 
                    `lote_minimo_reais`, `forma_compra`, `tp_moeda`, `valor_moeda_compra`, `condicao_padrao`, 
                    `fator_margem_lucro_pa`, `valor_moeda_custo`, `data_sys`, `lote_minimo_pa_rev`) VALUES 
                    (NULL, '$_POST[hdd_fornecedor_default]', '$id_produto_insumo', 
                    '".$campos_lista[0]['prazo_pgto_ddl']."', '".$campos_lista[0]['desc_vista']."', 
                    '".$campos_lista[0]['desc_sgd']."', '".$campos_lista[0]['ipi']."', 
                    '".$campos_lista[0]['icms']."', '".$campos_lista[0]['reducao']."', 
                    '".$campos_lista[0]['iva']."', '".$campos_lista[0]['lote_minimo_reais']."', 
                    '".$campos_lista[0]['forma_compra']."', '".$campos_lista[0]['tp_moeda']."', 
                    '".$campos_lista[0]['valor_moeda_compra']."', '".$campos_lista[0]['condicao_padrao']."', 
                    '".$campos_lista[0]['fator_margem_lucro_pa']."', '".$campos_lista[0]['valor_moeda_custo']."', 
                    '".date('Y-m-d H:i:s')."', '".$campos_lista[0]['lote_minimo_pa_rev']."') ";
            bancos::sql($sql);
            /*
            Comentado em 27/07/2018 � Pedido do Roberto ...

            4) Vinculo o Fornecedor Default no Novo PI que foi gerado ...
            $sql = "UPDATE `produtos_insumos` SET id_fornecedor_default = '$_POST[hdd_fornecedor_default]' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);*/
        }
/*****************************************************************************/
?>
    <Script Language = 'JavaScript'>
        var operacao_custo = eval('<?=$_POST[cmb_operacao_custo];?>')
        if(operacao_custo == 0) {//Custo Industrial ...
            var confirmar = confirm('DESEJA IR P/ O CUSTO ?')
            if(confirmar == true) {//Significa que o usu�rio desejou ir p/ o Custo Industrial ...
                window.location = '../../custo/industrial/custo_industrial.php?id_produto_acabado=<?=$id_produto_acabado;?>'
            }else {//Volta p/ o processo de Clonagem de Custo ...
                window.location = 'clonar.php<?=$parametro;?>&valor=2'
            }
        }else {//Custo Revenda ...
            var referencia = '<?=$_POST[txt_referencia];?>'
            if(referencia == 'ESP') {//Se o PA = 'ESP' sempre inclui este PA em um Or�amento -> Par�metro -> incluir_pa_no_orcamento = 'S' ...
                window.location = 'clonar.php<?=$parametro;?>&id_produto_acabado=<?=$id_produto_acabado;?>&incluir_pa_no_orcamento=S&valor=2'
            }else {//Volta p/ o processo de Clonagem de Custo -> Par�metro -> incluir_pa_no_orcamento = 'N' ...
                window.location = 'clonar.php<?=$parametro;?>&incluir_pa_no_orcamento=N&valor=2'
            }
        }
    </Script>
<?
    }else {//P.A. j� existente
?>
    <Script Language = 'JavaScript'>
        window.location = 'clonar.php<?=$parametro;?>&valor=3'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Clonar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='txt_discriminacao' value='<?=$txt_discriminacao;?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Clonar
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name="txt_consultar" size="45" maxlength="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1"  onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Refer�ncia" id='label1'>
            <label for='label1'>
                Refer�ncia
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2"  onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Discrimina��o" id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onClick='limpar()' value='3' title="Consultar todos os Produtos Acabados" class='checkbox' id='label3'>
            <label for='label3'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php'" class='botao'>
            <input type='reset' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>