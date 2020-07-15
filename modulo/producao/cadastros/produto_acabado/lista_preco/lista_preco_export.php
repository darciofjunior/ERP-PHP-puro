<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

set_time_limit(600);//A princípio estou deixando 10 minutos ...
$qtde_por_pagina = 10;

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>LISTA DE PREÇO DE EXPORTAÇÃO ATUALIZADA COM SUCESSO.</font>";

/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmb_empresa_divisao                = $_POST['cmb_empresa_divisao'];
    $cmb_grupo_pa                       = $_POST['cmb_grupo_pa'];
    $chkt_somente_produtos_promocionais = $_POST['chkt_somente_produtos_promocionais'];
    $chkt_todos_produtos_zerados        = $_POST['chkt_todos_produtos_zerados'];
    $txt_referencia                     = $_POST['txt_referencia'];
    $txt_discriminacao                  = $_POST['txt_discriminacao'];
}else {
    $cmb_empresa_divisao                = $_GET['cmb_empresa_divisao'];
    $cmb_grupo_pa                       = $_GET['cmb_grupo_pa'];
    $chkt_somente_produtos_promocionais = $_GET['chkt_somente_produtos_promocionais'];
    $chkt_todos_produtos_zerados        = $_GET['chkt_todos_produtos_zerados'];
    $txt_referencia                     = $_GET['txt_referencia'];
    $txt_discriminacao                  = $_GET['txt_discriminacao'];
}

//Busca do Dólar Atual
$sql = "SELECT `valor_dolar_dia` 
        FROM `cambios` 
        ORDER BY `id_cambio` DESC ";
$campos         = bancos::sql($sql);
$valor_dolar    = $campos[0]['valor_dolar_dia'];

if($passo == 1) {
    //Tratamento com as combos submetidas p/ não furar o SQL abaixo ...
    if($cmb_empresa_divisao == '')  $cmb_empresa_divisao = '%';
    if($cmb_grupo_pa == '')         $cmb_grupo_pa = '%';

    if(!empty($chkt_somente_produtos_promocionais)) $condicao_produtos_promocionais = " AND pa.`promocao_export` = 'S' ";
    if(!empty($chkt_todos_produtos_zerados)) $condicao = " AND pa.`preco_export` = '0.00' ";

    $sql = "SELECT ed.razaosocial, ged.*, gpa.nome, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, 
            pa.discriminacao, pa.promocao_export, pa.preco_export, pa.status_custo 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pa.`ativo` = '1' 
            AND pa.`status_nao_produzir` = '0' 
            AND pa.`referencia` <> 'ESP' 
            AND gpa.`id_familia` <> '23' $condicao $condicao_produtos_promocionais 
            ORDER BY pa.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, $qtde_por_pagina, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'lista_preco_export.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Lista de Preço de Exportação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) document.getElementById('txt_preco_bruto_fat_us'+i).value = strtofloat(document.getElementById('txt_preco_bruto_fat_us'+i).value)
} 
    
//Controle do Pop-Up
function submeter() {
    window.location = '../lista_preco/lista_preco_export.php<?=$parametro;?>'
}
</Script>
</head>
<body>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Lista de Preço de Exportação -
            <font color='yellow'>
                Valor U$ Dia: <?=number_format($valor_dolar, 4, ',', '.');?> Reais
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Promoção
        </td>
        <td>
            Pço Máx. <br>Custo Fat. U$
        </td>
        <td>
            M. L. Zero
        </td>
        <td>
            ML Min
        </td>
        <td>
            Pço Bruto. <br>Fat. U$
        </td>
        <td>
            Pço Bruto. <br>Fat. U$ Atual
        </td>
    </tr>
<?
//Aqui instância as sub-funções
        for ($i = 0;  $i < $linhas; $i++) {
            $retornar_valores = vendas::pas_precos_export($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align="left">
            <font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>">
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </font>
            <?
                if($campos[$i]['operacao_custo'] == 0) {//Industrial
            ?>
                    <a href = '../../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela=2&pop_up=1' class='html5lightbox'>
            <?
                }else {
            ?>
                    <a href = '../../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' class='html5lightbox'>
            <?
                }
            ?>
                &nbsp;<img src ='../../../../../imagem/menu/alterar.png' border='0' title='Alterar Custo' alt='Alterar Custo'>
        </td>
        <td>
            <?$checked_promocao = ($campos[$i]['promocao_export'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_promocao[]' id='chkt_promocao<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' title='Promoção' class='checkbox' <?=$checked_promocao;?>>
        </td>
        <td>
            <?=number_format($retornar_valores['preco_maximo_custo_fat_us'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($retornar_valores['margem_lucro_zero'], 2, ',', '.');?>
        </td>
        <td>
            <font color='green'>
                <a href = '../grupo_pa/alterar_empresa_divisao.php?permissao=alter&id_gpa_vs_emp_div=<?=$campos[$i]['id_gpa_vs_emp_div'];?>' class='html5lightbox'>
                    <?=number_format($campos[$i]['margem_lucro_exp'], 2, ',', '.');?>
                </a>
            </font>
            <input type='hidden' name='txt_margem_lucro_exp[]' value='<?=number_format($campos[$i]['margem_lucro_exp'], 2, ',', '.');?>' maxlength='8' size="10" class='caixadetexto' disabled>
        </td>
        <td>
            <input type='text' name='txt_preco_bruto_fat_us[]' id='txt_preco_bruto_fat_us<?=$i;?>' value='<?=number_format($retornar_valores['preco_bruto_fat_us'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='8' size='10' class='caixadetexto'>
            <input type='hidden' name='hdd_produto_acabado[]' value='<?=$campos[$i]['id_produto_acabado'];?>'>
        </td>
        <td>
            <?=number_format($campos[$i]['preco_export'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'lista_preco_export.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='document.form.passo.value = 2' class='botao'>
            <input type='submit' name='cmd_salvar_avancar' value='Salvar e Avançar' title='Salvar e Avançar' style='color:darkgreen' onclick="document.form.passo.value = 2; document.form.hdd_salvar_avancar.value = 'S'" class='botao'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
</table>
<!--*********************Controles de Tela*********************-->
<input type='hidden' name='passo' value='1' onclick='submeter()'>
<input type='hidden' name='hdd_salvar_avancar'>
<!--***********************************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Aqui é a parte de atualização dos Produtos Acabados
    foreach($_POST['hdd_produto_acabado'] as $i => $id_produto_acabado) {
        $promocao = (in_array($_POST['hdd_produto_acabado'][$i], $_POST['chkt_promocao'])) ? 'S' : 'N';
        $sql = "UPDATE `produtos_acabados` SET `promocao_export` = '$promocao', `preco_export` = '".$_POST['txt_preco_bruto_fat_us'][$i]."' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
    
    if($_POST['hdd_salvar_avancar'] == 'S') {
        $pagina         = '';
        $inicio         = '';
        $e_comercial    = 0;

        for($i = 0; $i < strlen($parametro); $i++) {
            if(substr($parametro, $i, 1) != '&') {//Enquanto não chega esse caracter que faz separação de parâmetros ...
                if($e_comercial == 0) {
                    $pagina.= substr($parametro, $i, 1);
                }else if($e_comercial == 1) {
                    $inicio.= substr($parametro, $i, 1);
                }
            }else {//Chegou o & ...
                $e_comercial++;
                if($e_comercial == 2) break;//P/ Sair do Loop ...
            }
        }
        
        $pagina_atual   = str_replace('=', '', strchr($pagina, '='));
        $proxima_pagina = ($pagina_atual + 1);
        $nova_pagina    = '?pagina='.$proxima_pagina;
        
        $inicio_atual   = str_replace('=', '', strchr($inicio, '='));
        $proximo_inicio = ($inicio_atual + $qtde_por_pagina);
        $novo_inicio    = 'inicio='.$proximo_inicio;
        
        $parametro      = str_replace($pagina, $nova_pagina, $parametro);
        $parametro      = str_replace($inicio, $novo_inicio, $parametro);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'lista_preco_export.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Lista de Preço de Exportação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv ='cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action = '<?=$PHP_SELF."?passo=1";?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto Acabado - Lista de Preço de Exportação
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Referência" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="45" maxlength="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divisão
        </td>
        <td>
            <select name="cmb_empresa_divisao" title="Consultar Produto Acabado por: Empresa Divisão" class="combo">
            <?
                $sql = "SELECT id_empresa_divisao, razaosocial 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A.
        </td>
        <td>
            <select name="cmb_grupo_pa" title="Consultar Produto Acabado por: Grupo P.A." class="combo">
            <?
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
                $sql = "SELECT id_grupo_pa, nome 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' 
                        AND `id_familia` <> '23' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_todos_produtos_zerados' value='1' title='Todos os Produtos Zerados' id='todos' class='checkbox'>
            <label for='todos'>Todos os Produtos Zerados</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_produtos_promocionais' value='1' title='Somente Produtos Promocionais' id='promocionais' class='checkbox'>
            <label for='promocionais'>Somente Produtos Promocionais</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_relatorio_pdf' value='Relatorio PDF' title='Relatorio PDF' onclick="html5Lightbox.showLightbox(7, 'relatorio_pdf.php')" style='color:black' class='botao'>
            <input type='button' name='cmd_promocoes' value='Promoções' title='Promoções' onclick="html5Lightbox.showLightbox(7, '../../../../vendas/pdt/promocoes/promocoes.php')" class='botao'>
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