<?
require('../../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/grupos_pas_vs_empresas_divisoes/cadastros/cadastros.php', '../../../../../../');

$mensagem[1] = "<font class='confirmacao'>QUALIDADE(S) AÇO(S) vs TEMPO(S) USINAGEM(NS) EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_GET['id_custo_qualidade_aco_vs_tempo_usinagem'])) {
    $sql = "DELETE FROM `custos_qualidades_acos_vs_tempos_usinagens` WHERE `id_custo_qualidade_aco_vs_tempo_usinagem` = '$_GET[id_custo_qualidade_aco_vs_tempo_usinagem]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Cadastro(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_custo_qualidade_aco_vs_tempo_usinagem) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE ITEM ?')
    if(resposta == true) window.location = 'qualidades_acos_vs_tempos_usinagens.php?id_custo_qualidade_aco_vs_tempo_usinagem='+id_custo_qualidade_aco_vs_tempo_usinagem
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)
        </td>
    </tr>
<?
        //Aqui vasculha todas os Grupos vs Empresas Divisões, Qualidades de Aços atrelados para este Tempo Usinagem ...
	$sql = "SELECT cqatu.id_custo_qualidade_aco_vs_tempo_usinagem, cqatu.perc_tempo_a_mais, 
                CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS grupo_vs_empresa_divisao, qa.nome 
                FROM `custos_qualidades_acos_vs_tempos_usinagens` cqatu 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = cqatu.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.`id_empresa_divisao` 
                INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = cqatu.`id_qualidade_aco` 
                ORDER BY grupo_vs_empresa_divisao, qa.nome ";
        $campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            NÃO HÁ QUALIDADE(S) AÇO(S) vs TEMPO(S) USINAGEM(NS) CADASTRADO(S).
        </td>
    </tr>
<?
	}else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo PA vs Empresa Divisão
        </td>
        <td>
            Qualidade do Aço
        </td>
        <td>
            % Tempo a mais
        </td>
        <td width='30'>
            &nbsp;
        </td>
        <td width='30'>
            &nbsp;
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['grupo_vs_empresa_divisao'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['perc_tempo_a_mais'], 2, ',', '.');?>
        </td>
        <td>
            <img src='../../../../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('alterar.php?id_custo_qualidade_aco_vs_tempo_usinagem=<?=$campos[$i]['id_custo_qualidade_aco_vs_tempo_usinagem'];?>', 'POP', '', '', '', '', 220, 850, 'c', 'c')" title='Alterar Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)' alt='Alterar Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)'>
        </td>
        <td>
            <img src='../../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_custo_qualidade_aco_vs_tempo_usinagem'];?>')" alt='Excluir Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)' title='Excluir Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)'>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='6'>
            <a href="javascript:nova_janela('incluir.php', 'POP', '', '', '', '', 220, 850, 'c', 'c')" title='Incluir Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)'>
                <font color='#FFFF00'>
                    Incluir Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)
                </font>
            </a>
        </td>
    </tr>
</body>
</html>