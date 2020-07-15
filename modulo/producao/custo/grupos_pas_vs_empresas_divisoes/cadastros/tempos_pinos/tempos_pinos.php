<?
require('../../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/grupos_pas_vs_empresas_divisoes/cadastros/cadastros.php', '../../../../../../');

$mensagem[1] = "<font class='confirmacao'>TEMPO(S) PINO(S) EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_GET['id_custo_tempo_pino_conico'])) {
    $sql = "DELETE FROM `custos_tempos_pinos` WHERE `id_custo_tempo_pino` = '$_GET[id_custo_tempo_pino]' LIMIT 1 ";
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
function excluir_item(id_custo_tempo_pino) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE ITEM ?')
    if(resposta == true) window.location = 'tempos_pinos.php?id_custo_tempo_pino='+id_custo_tempo_pino
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Tempo(s) Pino(s) - Usinagem Conicidade CNC
        </td>
    </tr>
<?
        //Aqui vasculha todas as Máquinas atreladas para este Tempo Pino ...
	$sql = "SELECT ctp.id_custo_tempo_pino, ctp.variacao_diametro_pino_conico, 
                ctp.perc_tempo_a_mais, m.nome 
                FROM `custos_tempos_pinos` ctp 
                INNER JOIN `maquinas` m ON m.`id_maquina` = ctp.`id_maquina` 
                ORDER BY m.nome, ctp.variacao_diametro_pino_conico ";
        $campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            NÃO HÁ TEMPO(S) PINO(S) CADASTRADO(S).
        </td>
    </tr>
<?
	}else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Máquina
        </td>
        <td>
            Variação do Diâmetro Pino Cônico
        </td>
        <td>
            Diâmetro Menor Din 7977
        </td>
        <td>
            Comprimento Pino Paralelo
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
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['variacao_diametro_pino_conico'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['diametro_menor_din7977'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['comprimento_pino_paralelo'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['perc_tempo_a_mais'], 2, ',', '.');?>
        </td>
        <td>
            <img src='../../../../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('alterar.php?id_custo_tempo_pino=<?=$campos[$i]['id_custo_tempo_pino'];?>', 'POP', '', '', '', '', 220, 850, 'c', 'c')" title='Alterar Tempo(s) Pino(s)' alt='Alterar Tempo(s) Pino(s)'>
        </td>
        <td>
            <img src='../../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_custo_tempo_pino'];?>')" alt='Excluir Tempo(s) Pino(s)' title='Excluir Tempo(s) Pino(s)'>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <a href="javascript:nova_janela('incluir.php', 'POP', '', '', '', '', 220, 850, 'c', 'c')" title='Incluir Tempo(s) Pino(s)'>
                <font color='#FFFF00'>
                    Incluir Tempo(s) Pino(s)
                </font>
            </a>
        </td>
    </tr>
</body>
</html>