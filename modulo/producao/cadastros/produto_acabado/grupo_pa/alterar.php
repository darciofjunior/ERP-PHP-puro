<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/vendas.php');
if(empty($_GET['pop_up'])) {//Significa que essa Tela abriu de forma normal, n„o como sendo Pop-UP ...
    require '../../../../../lib/menu/menu.php';
    segurancas::geral($PHP_SELF, '../../../../../');
}
require('../../../../../lib/data.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>GRUPO P.A. ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>GRUPO P.A. J¡ EXISTENTE.</font>";
$mensagem[4] = "<font class='confirmacao'>EMPRESA DIVIS√O DO GRUPO P.A EXCLUÕDA COM SUCESSO.</font>";
$mensagem[5] = "<font class='erro'>EMPRESA DIVIS√O N√O PODE SER EXCLUÕDA DO GRUPO P.A, POIS CONSTA EM USO.</font>";

if($passo == 1) {
//Aqui eu Listo todos os Grupos que possuem Comiss„o Extra preenchido ...
    if(!empty($opcao_itens_comissao)) $condicao_itens_comissao = 'INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_grupo_pa = gpa.id_grupo_pa AND ged.comissao_extra > 0 ';
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT gpa.*, cf.`classific_fiscal`, f.`id_familia`, f.`nome` AS familia 
                    FROM `grupos_pas` gpa 
                    $condicao_itens_comissao 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE gpa.`nome` LIKE '%$txt_consultar%' 
                    AND gpa.`ativo` = '1' 
                    GROUP BY gpa.`id_grupo_pa` ORDER BY gpa.`nome` ";
        break;
        default:
            $sql = "SELECT gpa.*, cf.`classific_fiscal`, f.`id_familia`, f.`nome` AS familia 
                    FROM `grupos_pas` gpa 
                    $condicao_itens_comissao 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE gpa.`ativo` = '1' 
                    GROUP BY gpa.`id_grupo_pa` ORDER BY gpa.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
	<Script Language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
	</Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function copiar_valores(indice_coluna) {
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_gpa_vs_emp_div[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 ˙nico elemento ...
    }else {
        var linhas = (elementos['hdd_gpa_vs_emp_div[]'].length)
    }
    if(indice_coluna == 0) {//Õndice de Coluna que Equivale a Coluna Desconto A
        for(var i = 1; i < linhas; i++) elementos['txt_desc_base_a_nac[]'][i].value = elementos['txt_desc_base_a_nac[]'][0].value
    }else if(indice_coluna == 1) {//Õndice de Coluna que Equivale a Coluna Desconto B
        for(var i = 1; i < linhas; i++) elementos['txt_desc_base_b_nac[]'][i].value = elementos['txt_desc_base_b_nac[]'][0].value
    }else if(indice_coluna == 2) {//Õndice de Coluna que Equivale a Coluna AcrÈscimo
        for(var i = 1; i < linhas; i++) elementos['txt_acrescimo_base_nac[]'][i].value = elementos['txt_acrescimo_base_nac[]'][0].value
    }else if(indice_coluna == 3) {//Õndice de Coluna que Equivale a Coluna Margem de Lucro Exp
        for(var i = 1; i < linhas; i++) elementos['txt_margem_lucro_exp[]'][i].value = elementos['txt_margem_lucro_exp[]'][0].value
    }else if(indice_coluna == 4) {//Õndice de Coluna que Equivale a Coluna Margem de Lucro MÌnima
        for(var i = 1; i < linhas; i++) elementos['txt_margem_lucro_minima[]'][i].value = elementos['txt_margem_lucro_minima[]'][0].value
    }else if(indice_coluna == 5) {//Õndice de Coluna que Equivale a Coluna Comiss„o Extra
        for(var i = 1; i < linhas; i++) elementos['txt_comissao_extra[]'][i].value = elementos['txt_comissao_extra[]'][0].value
    }else if(indice_coluna == 6) {//Õndice de Coluna que Equivale a Coluna Data Limite
        for(var i = 1; i < linhas; i++) elementos['txt_data_limite[]'][i].value = elementos['txt_data_limite[]'][0].value
    }
}

function validar() {
    var data_hoje = eval('<?=date('Ymd');?>')
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_gpa_vs_emp_div[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 ˙nico elemento ...
    }else {
        var linhas = (elementos['hdd_gpa_vs_emp_div[]'].length)
    }
//Chama a funÁ„o de acordo com a qtde de descontos ...
    for(var j = 0; j < linhas; j++) {
        if(elementos['txt_comissao_extra[]'][j].value != '') {
            var data_limite = elementos['txt_data_limite[]'][j].value
            data_limite = data_limite.substr(6, 4) + data_limite.substr(3, 2) + data_limite.substr(0, 2)
            if(data_limite < data_hoje) {
                alert('DATA LIMITE INV¡LIDA !\nDATA LIMITE MENOR DO QUE A DATA ATUAL !')
                elementos['txt_data_limite[]'][j].focus()
                elementos['txt_data_limite[]'][j].select()
                return false
            }
        }
        elementos['txt_comissao_extra[]'][j].value 		= strtofloat(elementos['txt_comissao_extra[]'][j].value)
    }
}
</Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=5';?>" method='post' onsubmit='return validar()'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar Grupo P.A.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo
        </td>
        <td colspan='2'>
            FamÌlia
        </td>
        <td>
            Lote MÌn.<br>Produto R$
        </td>
        <td>
            Prazo de<br>Entrega 
        </td>
        <td>
            ClassificaÁ„o<br>Fiscal
        </td>
        <td colspan='3'>
            ObservaÁ„o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="window.location = 'alterar.php?passo=2&id_grupo_pa=<?=$campos[$i]['id_grupo_pa'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td bgcolor='#D8D8D8' align='left'>
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0' title='Alterar Grupo' alt='Alterar Grupo'>
            <?
                echo $campos[$i]['nome'].' (id_grupo_pa='.$campos[$i]['id_grupo_pa'].')';
                if($campos[$i]['desenho_para_conferencia'] != '') {
            ?>
                <img src="<?='../../../../../imagem/desenhos_grupos_pas/'.$campos[$i]['desenho_para_conferencia'];?>" width='40' height='12'>
            <?
                }
            ?>				
        </td>
        <td colspan='2' align='left' bgcolor='#D8D8D8'>
            <?=$campos[$i]['familia'].' (id_familia='.$campos[$i]['id_familia'].')';?>
        </td>
        <td bgcolor="#D8D8D8" align="right">
            <?=segurancas::number_format($campos[$i]['lote_min_producao_reais'], 2, '.');?>
        </td>
        <td bgcolor="#D8D8D8" align='center'>
        <?
            $vetor_prazos_entrega = vendas::prazos_entrega();
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                //Compara o valor do Banco com o valor do Vetor
                if($campos[$i]['prazo_entrega'] == $indice) {//Se igual seleciona esse valor
                    echo $prazo_entrega;
                }
            }
        ?>
        </td>
        <td bgcolor="#D8D8D8">
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td colspan="3" bgcolor="#D8D8D8" align="left">
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
//Aqui traz todos as empresas divisıes e descontos que est„o relacionados a este grupo aqui do loop
            $sql = "SELECT ged.*, ed.`id_empresa_divisao`, ed.`razaosocial` 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE ged.`id_grupo_pa` = '".$campos[$i]['id_grupo_pa']."' ORDER BY ed.`razaosocial` ";
            $campos2 = bancos::sql($sql);
            $linhas2 = count($campos2);
            if($linhas2 > 0) {
                for($j = 0; $j < $linhas2; $j++) {
?>
    <tr class='linhanormal'>
        <td>
            <b>Divis„o:</b>
            <?=$campos2[$j]['razaosocial'].' (id_gpa_vs_emp_div='.$campos2[$j]['id_gpa_vs_emp_div'].')';?>
        </td>
        <td>
            <b>Desc. A Nac.:</b>
            <input type='text' name='txt_desc_base_a_nac[]' value='<?=$campos2[$j]['desc_base_a_nac'];?>' title='Digite a Desconto A' size='8' maxlenght='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(0)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>Desc. B Nac.:</b>
            <input type='text' name='txt_desc_base_b_nac[]' value='<?=$campos2[$j]['desc_base_b_nac'];?>' title='Digite a Desconto B' size='8' maxlenght='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(1)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>Ac. Nac.:</b>
            <input type='text' name='txt_acrescimo_base_nac[]' value='<?=$campos2[$j]['acrescimo_base_nac'];?>' title='Digite o AcrÈscimo Nacional' size='8' maxlenght='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(2)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>M. L. Exp.:</b>
            <input type='text' name='txt_margem_lucro_exp[]' value='<?=$campos2[$j]['margem_lucro_exp'];?>' title='Digite a Margem de Lucro de ExportaÁ„o' size='8' maxlenght='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(3)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>M.L. MÌnima.:</b>
            <input type='text' name='txt_margem_lucro_minima[]' value='<?=$campos2[$j]['margem_lucro_minima'];?>' title='Digite a Margem de Lucro MÌnima' size='8' maxlenght='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(4)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>Com. Extra.:</b>
            <?
                    $comissao_extra = ($campos2[$j]['comissao_extra'] == '0.00') ? '' : number_format($campos2[$j]['comissao_extra'], 1, ',', '.');
            ?>
            <input type='text' name='txt_comissao_extra[]' value='<?=$comissao_extra;?>' title='Digite a Comiss„o Extra' size='8' maxlenght='6' onkeyup="verifica(this, 'moeda_especial', 1, '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(5)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>Data Limite:</b>
            <input type='text' name='txt_data_limite[]' value='<?=data::datetodata($campos2[$j]['data_limite'], '/');?>' title='Digite a Data Limite' size='12' maxlenght='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//SÛ ir· mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src = '../../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_valores(6)'>
            <?
                }
            ?>
        </td>
        <td>
            <b>PDF.:</b>
            <?=$campos2[$j]['path_pdf'];?>
            <input type='hidden' name='hdd_gpa_vs_emp_div[]' value="<?=$campos2[$j]['id_gpa_vs_emp_div'];?>">
        </td>
    </tr>
<?
                }
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
        <?
            //SÛ exibo esses botıes, quando essa Tela n„o foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//N„o È Pop-UP ...
        ?>
            <input type='button' name='cmd_consultar_Novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $sql = "SELECT * 
            FROM `grupos_pas` 
            WHERE `id_grupo_pa` = '$_GET[id_grupo_pa]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(valor) {
//FamÌlia
    if(!combo('form', 'cmb_familia', '', 'SELECIONE A FAMÕLIA !')) {
        return false
    }
//Grupo P.A.
    if(!texto('form', 'txt_grupo', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/.-_∫™():', 'GRUPO P.A.', '2')) {
        return false
    }
//Grupo P.A. InglÍs
    if(document.form.txt_grupo_ingles.value != '') {
        if(!texto('form', 'txt_grupo_ingles', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'GRUPO P.A. INGL S', '2')) {
            return false
        }
    }
//Grupo P.A. Espanhol
    if(document.form.txt_grupo_espanhol.value != '') {
        if(!texto('form', 'txt_grupo_espanhol', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'GRUPO P.A. ESPANHOL', '2')) {
            return false
        }
    }
//Lote MÌn. ProduÁ„o R$
    if(!texto('form', 'txt_lote_min_prod_reais', '1', '0123456789,.', 'LOTE MÕNIMO PRODU«√O R$', '2')) {
        return false
    }
//Prazo de Entrega
    if(!combo('form', 'cmb_prazo_entrega', '', 'SELECIONE O PRAZO DE ENTREGA !')) {
        return false
    }
//Tolerancia
    if(document.form.txt_tolerancia.value != '') {
        if(!texto('form', 'txt_tolerancia', '1', "abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ '1234567890", 'TOLER¬NCIA', '1')) {
            return false
        }
    }
    document.form.passo.value = 3
    limpeza_moeda('form', 'txt_lote_min_prod_reais, ')

//Significa q est· tentando submeter pelo link de cadastro de embalagens
    if(valor == 1) document.form.submit()
}

//AlteraÁ„o de Empresa(s) Divis„o(ıes)
function alterar_item(valor) {
//Somente na FamÌlia Componente que n„o permite a alteraÁ„o dos Valores de Descontos e AcrÈscimos ...
    if(document.form.cmb_familia.value == 23) {
        alert('N√O … PERMITIDO A ALTERA«√O DE DADOS PARA ESTA FAMÕLIA !!!\n\nO DESCONTO DA FAMÕLIA COMPONENTE TEM DE SER 100% PARA N√O DAR ERRO NOS RELAT”RIOS DE PRODU«√O E ESTOQUE !')
    }else {
        html5Lightbox.showLightbox(7, 'alterar_empresa_divisao.php?permissao=alter&id_gpa_vs_emp_div='+valor)
    }
}

//Exclus„o de Empresa(s) Divis„o(ıes)
function excluir_item(valor) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        limpeza_moeda('form', 'txt_lote_min_prod_reais, ')
        document.form.passo.value = 4
        document.form.id_gpa_vs_emp_div.value = valor
        document.form.submit()
    }
}

//Controle do Pop-Up
function submeter() {
    limpeza_moeda('form', 'txt_lote_min_prod_reais, ')
    document.form.passo.value = 2
    document.form.submit()
}
</Script>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' enctype='multipart/form-data' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='id_grupo_pa' value='<?=$_GET['id_grupo_pa'];?>'>
<input type='hidden' name='id_gpa_vs_emp_div'>
<input type='hidden' name='passo' onclick='submeter()'>
<!--****************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>FamÌlia:</b>
        </td>
        <td>
            <select name='cmb_familia' title='Selecione uma FamÌlia' class='combo'>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[0]['id_familia']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A.:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo' value='<?=$campos[0]['nome'];?>' title='Digite o Grupo P.A.' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A. InglÍs:
        </td>
        <td>
            <input type='text' name='txt_grupo_ingles' value='<?=$campos[0]['nome_ing'];?>' title='Digite o Grupo P.A. InglÍs' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A. Espanhol:
        </td>
        <td>
            <input type='text' name='txt_grupo_espanhol' value='<?=$campos[0]['nome_esp'];?>' title='Digite o Grupo P.A. Espanhol' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lote MÌnimo ProduÁ„o R$:</b>
        </td>
        <td>
            <input type='text' name='txt_lote_min_prod_reais' value='<?=segurancas::number_format($campos[0]['lote_min_producao_reais'], 2, '.');?>' title='Digite o Lote MÌnimo ProduÁ„o R$' size='14' maxlength='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>		
        <?
            $vetor_prazos_entrega = vendas::prazos_entrega();
        ?>
            <select name='cmb_prazo_entrega' title='Selecione o Prazo de Entrega' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                        if($campos[0]['prazo_entrega'] == $indice) {//Se igual seleciona esse valor
                ?>
                <option value='<?=$indice;?>' selected><?=$prazo_entrega;?></option>
                <?
                        }else {
                ?>
                <option value='<?=$indice;?>'><?=$prazo_entrega;?></option>
                <?
                        }
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Toler‚ncia:
        </td>
        <td>
            <input type='text' name='txt_tolerancia' value='<?=$campos[0]['tolerancia'];?>' title='Digite a Toler‚ncia' size='40' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desenho p/ ConferÍncia:
        </td>
        <td>
            <input type='file' name='txt_desenho_para_conferencia' title='Digite ou selecione o Caminho do Desenho para ConferÍncia' size='80' class='caixadetexto'>
            <!--Este hidden ser· utilizado mais abaixo no passo 3 ...-->
            <input type='hidden' name='hdd_desenho_para_conferencia' value='<?=$campos[0]['desenho_para_conferencia'];?>'>
        </td>
    </tr>
    <?
        if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo ent„o ...
    ?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ ConferÍncia Atual:
        </td>
        <td>
            <img src = '../../../../../imagem/desenhos_grupos_pas/<?=$campos[0]['desenho_para_conferencia'];?>' width='400' height='100'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ ConferÍncia Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ ConferÍncia Atual
            </label>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhadestaque'>
        <td colspan='3'>
        <?
//Aqui significa que o grupo_pa j· foi incluido
            if(!empty($id_grupo_pa)) {
        ?>
            <a href = 'incluir_empresa_divisao.php?permissao=inc&id_grupo_pa=<?=$id_grupo_pa;?>' class='html5lightbox'>
        <?	
            }else {
        ?>
            <a href="javascript:validar(1)" title='Atrelar Empresa Divis„o'>
        <?
            }
        ?>
                <font color='#FFFF00'>
                    <b><i>Atrelar Empresa(s) Divis„o(ıes)</i></b>
                </font>
            </a>
        </td>
    </tr>
<?
	if(!empty($id_grupo_pa)) {
            //Aqui traz todas as empresas divisıes que est„o relacionado ao grupo pa ...
            $sql = "SELECT ged.*, ed.`id_empresa_divisao`, ed.`razaosocial` 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE ged.`id_grupo_pa` = '$id_grupo_pa' ORDER BY ed.razaosocial ";
            $campos_empresa_divisao = bancos::sql($sql);
            $linhas_empresa_divisao = count($campos_empresa_divisao);
            if($linhas_empresa_divisao > 0) {
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Empresa Divis„o</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Desc. Base A Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Desc. Base B Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>AcrÈsc. Base Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>ML Min Exp.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>ML Min Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Comiss„o Extra</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Data Limite</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Caminho do PDF do Site</i></b>
        </td>
        <td bgcolor='#CCCCCC'>&nbsp;</td>
        <td bgcolor='#CCCCCC'>&nbsp;</td>
    </tr>
<?
                for($i = 0; $i < $linhas_empresa_divisao; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <?=$campos_empresa_divisao[$i]['razaosocial'];?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['desc_base_a_nac'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['desc_base_b_nac'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['acrescimo_base_nac'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['margem_lucro_exp'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['margem_lucro_minima'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_empresa_divisao[$i]['comissao_extra'], 1, '.');?>
        </td>
        <td align='center'>
        <?
            if($campos2[$i]['data_limite'] != '0000-00-00') echo data::datetodata($campos_empresa_divisao[$i]['data_limite'], '/');
        ?>
        </td>
        <td align='left'>
            <?=$campos_empresa_divisao[$i]['path_pdf'];?>
        </td>
        <td align='center'>
            <img src = '../../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item('<?=$campos_empresa_divisao[$i]['id_gpa_vs_emp_div'];?>')" title='Alterar' alt='Alterar'>
        </td>
        <td align='center'>
            <img src = '../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos_empresa_divisao[$i]['id_gpa_vs_emp_div'];?>')" title='Excluir' alt='Excluir'>
        </td>
    </tr>
<?
                }
            }
	}
?>
</table>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='50' rows='5' title="Digite a ObservaÁ„o" maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_grupo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Verifico se existe algum outro "Grupo de PI" com esse nome alÈm do atual ...
    $sql = "SELECT id_grupo_pa 
            FROM `grupos_pas` 
            WHERE `nome` = '$_POST[txt_grupo]' 
            AND `id_grupo_pa` <> '$_POST[id_grupo_pa]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
/*************************************************************/
/*Se o Usu·rio habilitou a opÁ„o de excluir o Desenho para ConferÍncia ou ent„o ele est· fazendo 
a substituiÁ„o de uma Imagem por outra, ent„o eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_conferencia'])) {
            $endereco_desenho_para_conferencia = '../../../../../imagem/desenhos_grupos_pas/'.$_POST['hdd_desenho_para_conferencia'];
            unlink($endereco_desenho_para_conferencia);//Exclui a Imagem do Servidor ...
            $campo_desenho_para_conferencia = " , `desenho_para_conferencia` = '' ";
        }
        if(!empty($_FILES['txt_desenho_para_conferencia']['name'])) {
            if(!empty($_POST['hdd_desenho_para_conferencia'])) {//Se existir algum desenho antigo, aÌ sim eu posso removo este p/ substituir pelo novo ...
                if(file_exists('../../../../../imagem/desenhos_grupos_pas/'.$_POST['hdd_desenho_para_conferencia'])) {
                    unlink('../../../../../imagem/desenhos_grupos_pas/'.$_POST['hdd_desenho_para_conferencia']);
                }
            }
            require('../../../../../lib/mda.php');
            switch ($_FILES['txt_desenho_para_conferencia']['type']) {
                case 'image/gif':
                case 'image/pjpeg':
                case 'image/jpeg':
                case 'image/png':
                case 'image/x-png':
                case 'image/bmp':
                    $desenho_para_conferencia = copiar::copiar_arquivo('../../../../../imagem/desenhos_grupos_pas/', $_FILES['txt_desenho_para_conferencia']['tmp_name'], $_FILES['txt_desenho_para_conferencia']['name'], $_FILES['txt_desenho_para_conferencia']['size'], $_FILES['txt_desenho_para_conferencia']['type'], '2');
                break;
                default:
                    //echo "N„o È possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_conferencia = " , `desenho_para_conferencia` = '$desenho_para_conferencia' ";
        }
        $sql = "UPDATE `grupos_pas` SET `id_familia` = '$_POST[cmb_familia]', `nome` = '$_POST[txt_grupo]', `nome_ing` = '$_POST[txt_grupo_ingles]', `nome_esp` = '$_POST[txt_grupo_espanhol]', `lote_min_producao_reais` = '$_POST[txt_lote_min_prod_reais]', `prazo_entrega` = '$_POST[cmb_prazo_entrega]', `tolerancia` = '$_POST[txt_tolerancia]' $campo_desenho_para_conferencia , `observacao` = '$_POST[txt_observacao]' WHERE `id_grupo_pa` = '$_POST[id_grupo_pa]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else if($passo == 4) {
    if(!empty($id_gpa_vs_emp_div)) {
/*Verifico se a Empresa(s) Divis„o(ıes) do Grupo(s) Pa(s) est· sendo utilizada por algum P.A. 
independente de j· ter sido excluÌdo ou n„o ...*/
        $sql = "SELECT id_produto_acabado 
                FROM `produtos_acabados` 
                WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Em uso
            $valor = 5;
        }else {//N„o est· em uso, pode excluir a Empresa(s) Divis„o(ıes) do Grupo(s) Pa(s)
            $sql = "DELETE FROM `gpas_vs_emps_divs` WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 4;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?passo=2&id_grupo_pa=<?=$id_grupo_pa;?>&valor=<?=$valor;?>'
    </Script>
<?
//
}else if($passo == 5) {
//Nesse passo eu faÁo a alteraÁ„o dos valores de AcrÈscimo e Desconto em Lote ...
    for($i = 0; $i < count($_POST['hdd_gpa_vs_emp_div']); $i++) {
        $data_limite = data::datatodate($_POST['txt_data_limite'][$i], '-');
//AlteraÁ„o dos dados na Empresa Divis„o vs Grupo P.A.
        $sql = "UPDATE `gpas_vs_emps_divs` SET `desc_base_a_nac` = '".$_POST['txt_desc_base_a_nac'][$i]."', `desc_base_b_nac` = '".$_POST['txt_desc_base_b_nac'][$i]."', `acrescimo_base_nac` = '".$_POST['txt_acrescimo_base_nac'][$i]."', `margem_lucro_exp` = '".$_POST['txt_margem_lucro_exp'][$i]."', `margem_lucro_minima` = '".$_POST['txt_margem_lucro_minima'][$i]."', `comissao_extra` = '".$_POST['txt_comissao_extra'][$i]."', `data_limite` = '$data_limite', `path_pdf` = '$_POST[txt_caminho_pdf_site]'  where `id_gpa_vs_emp_div` = '".$_POST['hdd_gpa_vs_emp_div'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled = true
        document.form.txt_consultar.disabled = true
        document.form.txt_consultar.value = ''
    }else {
        document.form.opt_opcao.disabled = false
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan="2" align='center'>
            Alterar Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name="txt_consultar" size=45 maxlength=45 class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Grupos P.A. por: Grupo P.A." id='label' checked>
            <label for='label'>Grupo P.A.</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os Grupos P.A." class="checkbox" id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="2">
            <input type='checkbox' name='opcao_itens_comissao' value='1' title="Somente Itens c/ Comiss„o Extra > 0 " class="checkbox" id='label3'>
            <label for='label3'>Somente Itens c/ Comiss„o Extra > 0 </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>