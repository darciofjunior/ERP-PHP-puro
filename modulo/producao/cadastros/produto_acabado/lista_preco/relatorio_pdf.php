<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

if($passo == 1) {
    function rotulo() {
        global $pdf;
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->Cell($GLOBALS['ph']*94, 3, 'Pg. '.$GLOBALS['num_pagina'], 0, 1, 'R');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 7.2);
        $pdf->Cell($GLOBALS['ph']*12, 5, 'Cant. p/ Consulta', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*10, 5, 'Referencia', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*48, 5, 'Discriminación', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*9, 5, 'Partes / Emb.', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*6, 5, 'Unidad', 1, 0, 'C');
        $pdf->Cell($GLOBALS['ph']*9, 5, 'P. Unid. U$', 1, 1, 'C');
    }

    function Heade() {
        global $pdf;
        $pdf->Image('../../../../../imagem/marcas/Logo Grupo Albafer.jpg', 8, 5, 28, 30, 'JPG');
        $pdf->SetLeftMargin(10);
    /******************************************Albafér******************************************/
        //Busca de Dados da Empresa Albafer ...
        $sql = "SELECT razaosocial, endereco, numero, bairro, cidade, telefone_comercial, cep, ddd_comercial 
                FROM `empresas` 
                WHERE `id_empresa` = '1' LIMIT 1 ";
        $campos_albafer     = bancos::sql($sql);
        $endereco           = $campos_albafer[0]['endereco'];
        $numero             = $campos_albafer[0]['numero'];
        $bairro             = $campos_albafer[0]['bairro'];
        $cidade             = $campos_albafer[0]['cidade'];
        $telefone_comercial = $campos_albafer[0]['telefone_comercial'];
        $cep                = $campos_albafer[0]['cep'];
        $ddd_comercial      = $campos_albafer[0]['ddd_comercial'];

        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(15*$GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(175, 8, $campos_albafer[0]['razaosocial'], 0, 0, 'L');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Cell(15*$GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(124, 6, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');
        $pdf->Ln(4);
    /******************************************Tool Master******************************************/
        //Busca de Dados da Empresa Tool Master ...
        $sql = "SELECT razaosocial, endereco, numero, bairro, cidade, telefone_comercial, cep, ddd_comercial 
                FROM `empresas` 
                WHERE `id_empresa` = '2' LIMIT 1 ";
        $campos_tool_master = bancos::sql($sql);
        $endereco           = $campos_tool_master[0]['endereco'];
        $numero             = $campos_tool_master[0]['numero'];
        $bairro             = $campos_tool_master[0]['bairro'];
        $cidade             = $campos_tool_master[0]['cidade'];
        $telefone_comercial = $campos_tool_master[0]['telefone_comercial'];
        $cep                = $campos_tool_master[0]['cep'];
        $ddd_comercial      = $campos_tool_master[0]['ddd_comercial'];

        $pdf->SetTextColor(80, 0, 0);
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(15*$GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(175, 8, $campos_tool_master[0]['razaosocial'], 0, 0, 'L');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Cell(15*$GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(124, 6, $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep, 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Cell(15 * $GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(60, 8, 'FONE/FAX: (55) ('.$ddd_comercial.') '.$telefone_comercial.'    #    E-MAIL: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'BIU', 8);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Cell(20, 8, 'mercedes@grupoalbafer.com.br', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Cell(15 * $GLOBALS['ph'], 8, '', 0, 0, 'L');
        $pdf->Cell(10, 8, 'SITIO: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'BIU', 8);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->Cell(110, 8, 'www.grupoalbafer.com.br', 0, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(18);
    }
    /////////////////////////////////////// INÍCIO PDF ///////////////////////////////////////////////////////
    define('FPDF_FONTPATH', 'font/');
    $tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
    $unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
    $formato_papel      = 'A4'; // A3, A4, A5, Letter, Legal
    $pdf                = new FPDF($tipo_papel, $unidade, $formato_papel);
    $pdf->Open();
    $pdf->SetTopMargin(2);
    $pdf->SetLeftMargin(7);
    $pdf->AddPage();
    global $pv,$ph; //valor baseado em mm do A4
    if($formato_papel == 'A4') {
        if($tipo_papel == 'P') {
            $pv = 295/100;
            $ph = 205/100;
        }else {
            $pv = 205/100;
            $ph = 295/100;
        }
    }else {
        echo 'Formato não definido';
    }

    Heade();
    $pdf->SetFont('Arial', '', 10);
    //Trago p/ Impressão de Lista somente os Itens que foram selecionados na Tela anterior ...
    $sql = "SELECT ed.razaosocial, f.nome AS familia, f.nome_ing AS familia_ingles, f.nome_esp AS familia_espanhol, 
            gpa.nome AS grupo, gpa.nome_ing AS grupo_ingles, gpa.nome_esp AS grupo_espanhol, 
            pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.preco_export, u.sigla 
            FROM `produtos_acabados` pa 
            INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_gpa_vs_emp_div` IN (".implode($_POST['chkt_gpa_vs_emp_div'], ',').") 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa NOT IN (29, 38, 61, 66, 73, 74, 75, 76, 77, 79, 80, 82, 89) 
            INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
            WHERE pa.`ativo` = '1' 
            AND pa.`status_nao_produzir` = '0' 
            AND pa.`referencia` <> 'ESP' 
            AND gpa.`id_familia` NOT IN (2, 23, 24) 
            ORDER BY ed.razaosocial, familia, grupo, pa.referencia, pa.discriminacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        if($i % 2 == 0) {
            $pdf->SetFillColor(200, 200, 200);//Cor Cinza
        }else {
            $pdf->SetFillColor(255, 255, 255);//Cor Branca
        }
    //1) Controle p/ Impressão do Cabeçalho na Folha ...
        if($GLOBALS['nova_pagina'] == 'sim') if($i != 0) $pdf->Ln(-5);
    /*2) Comparo a Família com o do Loop anterior caso a Família for diferente, então eu printo qual que é a 
    Família Atual, em qualquer ponto do relatório de Impressão ...*/
        if($familia_atual != $campos[$i]['familia']) {
            //Só não irá adicionar uma nova Página na hora em que carregar o loop ...
            if($i != 0) $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            
            //Se existir em Espanhol ...
            if(!empty($campos[$i]['familia_espanhol'])) $familia_imprimir = strtoupper($campos[$i]['familia_espanhol']);
            //Se existir em Inglês ...
            if(!empty($campos[$i]['familia_ingles'])) $familia_imprimir.= ' - '.strtoupper($campos[$i]['familia_ingles']);
            $familia_imprimir.= ' - '.strtoupper($campos[$i]['familia']);
            
            $pdf->SetTextColor(130, 0, 0);
            $pdf->Cell($GLOBALS['ph']*90, 5, '* División: '.$campos[$i]['razaosocial'].' - Familia: '.$familia_imprimir, 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
        }
    /*3) Comparo o Grupo com o do Loop anterior caso o Grupo for diferente, então eu printo qual que é o 
    Grupo Atual, em qualquer ponto do relatório de Impressão ...*/
        if($grupo_atual != $campos[$i]['grupo']) {
            $pdf->SetFont('Arial', 'B', 9);
            
            //Se existir em Espanhol ...
            if(!empty($campos[$i]['grupo_espanhol'])) $grupo_imprimir = strtoupper($campos[$i]['grupo_espanhol']);
            //Se existir em Inglês ...
            if(!empty($campos[$i]['grupo_ingles'])) $grupo_imprimir.= ' - '.strtoupper($campos[$i]['grupo_ingles']);
            $grupo_imprimir.= ' - '.strtoupper($campos[$i]['grupo']);
            
            $pdf->SetTextColor(230, 0, 0);
            $pdf->Cell($GLOBALS['ph']*90, 5, '* Grupo: '.$grupo_imprimir, 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
        }
    //4) Sempre que for uma nova página eu printo os rótulos referente a cada coluna ...
        if($GLOBALS['nova_pagina'] == 'sim') {
            $GLOBALS['nova_pagina'] = 'nao';
            rotulo();
        }
        $pdf->SetFont('Arial', '', 7);
    //Qtde p/ Cotação
        $pdf->Cell($GLOBALS['ph']*12, 5, '', 1, 0, 'C', 1);
    //Referência
        $pdf->Cell($GLOBALS['ph']*10, 5, $campos[$i]['referencia'], 1, 0, 'C', 1);
    //Discriminação
        $pdf->Cell($GLOBALS['ph']*48, 5, $campos[$i]['discriminacao'], 1, 0, 'L', 1, 'javascript:window.open("'.$campos[$i]['path_pdf'].'", "SITE");alert("SEJA BEM VINDO / WELCOME / BIEN VENIDO - GRUPO ALBAFER !")');
    //Embalagem
        $sql = "SELECT pecas_por_emb 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                AND `embalagem_default` = '1' ORDER BY id_pa_pi_emb LIMIT 1 ";
        $campos_pecas_emb   = bancos::sql($sql);
        $pecas_emb          = (count($campos_pecas_emb) == 1) ? intval($campos_pecas_emb[0]['pecas_por_emb']) : 1;
        $pdf->Cell($GLOBALS['ph']*9, 5, $pecas_emb, 1, 0, 'C', 1);
    //Unidade
        $pdf->Cell($GLOBALS['ph']*6, 5, $campos[$i]['sigla'], 1, 0, 'C', 1);
    //Preço Líquido Fat. US$
        $pdf->Cell($GLOBALS['ph']*9, 5, number_format($campos[$i]['preco_export'], 2, ',', '.'), 1, 1, 'R', 1);

        $familia_atual 	= $campos[$i]['familia'];
        $grupo_atual 	= $campos[$i]['grupo'];
    }
    chdir('../../../../../pdf');
    $file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
    chdir(dirname(__FILE__));
    $pdf->Output($file);//Save PDF to file
    echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
}else {
    //Seleção dos Grupos cadastrados no Sistema com exceção dos que estão discriminados lá embaixo que são p/ não trazer mesmo ...
    $sql = "SELECT ed.razaosocial, gpa.nome, ged.id_gpa_vs_emp_div, ged.id_empresa_divisao 
            FROM `grupos_pas` gpa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_grupo_pa` = gpa.`id_grupo_pa` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` AND pa.`ativo` = '1' AND pa.`status_nao_produzir` = '0' AND pa.`referencia` <> 'ESP' 
            WHERE gpa.id_grupo_pa NOT IN (29, 38, 61, 66, 73, 74, 75, 76, 77, 79, 80, 82, 89) 
            AND gpa.`id_familia` NOT IN (2, 23, 24) 
            AND gpa.ativo = '1' GROUP BY ged.id_gpa_vs_emp_div ORDER BY ed.razaosocial, gpa.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Imprimir Lista de Preço ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') if(elementos[i].checked == true) valor = true
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}

function controlar_empresa_divisao(id_empresa_divisao, situacao_checkbox) {
    var elementos   = document.form.elements
    var linhas      = elementos['hdd_empresa_divisao[]'].length//Quantidade de todos os Grupos ...
    
    for(i = 0; i < linhas; i++) {
        var vetor                   = elementos['hdd_empresa_divisao[]'][i].value.split('|')
        var id_empresa_divisao_loop = vetor[0]
        var id_grupo_emp_div_loop   = vetor[1]
        //Verifico se o Hidden da Empresa Divisão da Linha é igual ao id_empresa_divisao passado por parâmetro ...
        if(id_empresa_divisao_loop == id_empresa_divisao) {
            //Se sim só traz os Grupos de PA que estão relacionados a Empresa Divisão do Loop ...
            if(document.getElementById('chkt_gpa_vs_emp_div'+i).value == id_grupo_emp_div_loop) {
                document.getElementById('chkt_gpa_vs_emp_div'+i).checked = situacao_checkbox
            }
        }
    }
}

function controlar_linha(indice) {
    document.getElementById('chkt_gpa_vs_emp_div'+indice).checked = (document.getElementById('chkt_gpa_vs_emp_div'+indice).checked == true) ? false : true
    //Controle p/ marcar /desmarcar o Checkbox Principal do Grupo do PA ...
    var elementos                       = document.form.elements
    var linhas                          = elementos['hdd_empresa_divisao[]'].length//Quantidade de todos os Grupos ...
    var checkbox_do_grupo               = 0
    var checkbox_do_grupo_selecionados  = 0
    //Aqui eu busco somente os Itens da Empresa Divisão do item marcado / desmarcado ...
    var id_empresa_divisao              = eval(document.getElementById('hdd_empresa_divisao'+indice).value.replace('|'+document.getElementById('chkt_gpa_vs_emp_div'+indice).value, ''))
    
    for(i = 0; i < linhas; i++) {
        var vetor                           = elementos['hdd_empresa_divisao[]'][i].value.split('|')
        var id_empresa_divisao_loop         = vetor[0]
        var id_grupo_emp_div_loop           = vetor[1]
        //Verifico se o Hidden da Empresa Divisão da Linha é igual ao id_empresa_divisao encontrado acima ...
        if(id_empresa_divisao_loop == id_empresa_divisao) {
            //Se sim só traz os Grupos de PA que estão relacionados a Empresa Divisão do Loop ...
            if(document.getElementById('chkt_gpa_vs_emp_div'+i).value == id_grupo_emp_div_loop) {
                if(document.getElementById('chkt_gpa_vs_emp_div'+i).checked) checkbox_do_grupo_selecionados++
                checkbox_do_grupo++
            }
        }
    }
    //Significa que todos os itens do Grupo do PA estão selecionados ...
    if(checkbox_do_grupo_selecionados == checkbox_do_grupo) {
        document.getElementById('chkt_empresa_divisao'+id_empresa_divisao).checked = true//Marco o Checkbox Principal do Grupo do PA ...
    }else {
        document.getElementById('chkt_empresa_divisao'+id_empresa_divisao).checked = false//Desmarco o Checkbox Principal do Grupo do PA ...
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Imprimir Lista de Preço
        </td>
    </tr>
    <tr align='center'>
        <td class='linhadestaque'>
            Grupo P.A. (Empresa Divisão)
        </td>
        <td class='linhadestaque'>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        $id_empresa_divisao_atual   = '';//Valor Inicial ...
	for($i = 0; $i < $linhas; $i++) {
            /******************************************************************/
            //Se trocou a Empresa Divisão, então eu apresento o nome da mesma ...
            if($campos[$i]['id_empresa_divisao'] != $id_empresa_divisao_atual) {
?>
    <tr align='center'>
        <td class='linhacabecalho'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td class='linhacabecalho'>
            <input type='checkbox' name='chkt_empresa_divisao[]' id='chkt_empresa_divisao<?=$campos[$i]['id_empresa_divisao'];?>' onclick="controlar_empresa_divisao('<?=$campos[$i]['id_empresa_divisao'];?>', this.checked)" class='checkbox'>
        </td>
    </tr>
<?
                $id_empresa_divisao_atual = $campos[$i]['id_empresa_divisao'];
            }
            /******************************************************************/
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');controlar_linha('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_gpa_vs_emp_div[]' id='chkt_gpa_vs_emp_div<?=$i;?>' value='<?=$campos[$i]['id_gpa_vs_emp_div'];?>' onclick="cor_clique_celula(this, '#C6E2FF');controlar_linha('<?=$i;?>')" class='checkbox'>
            <!--Esse hidden guarda o id_empresa_divisao e o id_gpa_vs_emp_div, que servirá p/ fazer controles quando 
            o usuário clicar no checkbox de "Empresa Divisão" fazendo com que todos os Grupos dessa Empresa Divisão fiquem 
            selecionados ...-->
            <input type='hidden' name='hdd_empresa_divisao[]' id='hdd_empresa_divisao<?=$i;?>' value='<?=$campos[$i]['id_empresa_divisao'].'|'.$campos[$i]['id_gpa_vs_emp_div'];?>'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_imprimir' value='Imprimir' title='Imprimir' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
No botão Lista de Preço não apresentamos Produtos:
 
* ESP;
* Produtos com marcação não produzidos temporariamente; 
* Família Componentes; 
* Família 2 - Pinos;
* Grupo 29 Sup. Intercambiavel; 
* Grupo 38 Cossinete TOP;
* Grupo 61 - Mão de Obra; 
* Grupo 66 Chaves Mandril Outras; 
* Grupo 73 Conjugados;
* Grupo 74 / 75 Lima Agrícola e Mecânica NL;
* Grupo 76 Bits Quadrado HardSteel;
* Grupo 77 - Bedame HardSteel;
* Grupo 79 - Bits Redondo HardSteel; 
* Grupo 80 / 89 - Pinos / Peças Complexa / Simples;
* Grupo 82 - Conserto.
</pre>
<?}?>