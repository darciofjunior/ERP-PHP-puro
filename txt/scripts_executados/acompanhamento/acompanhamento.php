<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial       = $_POST['txt_data_inicial'];
    $txt_data_final         = $_POST['txt_data_final'];
    $cmb_condicao_filtro    = $_POST['cmb_condicao_filtro'];
    $cmb_representante      = $_POST['cmb_representante'];
    $hdd_ja_submeteu        = $_POST['hdd_ja_submeteu'];
}else {
    $txt_data_inicial       = $_GET['txt_data_inicial'];
    $txt_data_final         = $_GET['txt_data_final'];
    $cmb_condicao_filtro    = $_GET['cmb_condicao_filtro'];
    $cmb_representante      = $_GET['cmb_representante'];
    $hdd_ja_submeteu        = $_GET['hdd_ja_submeteu'];
}
?>
<html>
<head>
<title>.:: Acompanhamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Emissão Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'DE INÍCIO')) {
        return false
    }
//Data de Emissão Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    data_inicial = document.form.txt_data_inicial.value
    data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.replace('/', '')
    data_inicial_sem_formatacao = data_inicial.replace('/', '')

    data_final = data_final.replace('/', '')
    data_final_sem_formatacao = data_final.replace('/', '')

    data_inicial_invertida = data_inicial_sem_formatacao.substr(4, 4)+data_inicial_sem_formatacao.substr(2, 2)+data_inicial_sem_formatacao.substr(0, 2)
    data_final_invertida = data_final_sem_formatacao.substr(4, 4)+data_final_sem_formatacao.substr(2, 2)+data_final_sem_formatacao.substr(0, 2)

    data_inicial_invertida = eval(data_inicial_invertida)
    data_final_invertida = eval(data_final_invertida)

    if(data_inicial_invertida > data_final_invertida) {
        alert('DATAS INVÁLIDAS !')
        document.form.txt_data_inicial.focus()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 2 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 730) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.hdd_ja_submeteu.value = 1
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela - Significa que já submeteu pelo menos 1 vez-->
<input type='hidden' name='hdd_ja_submeteu' value='<?=$hdd_ja_submeteu;?>'>
<!--*************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Acompanhamento
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='13'>
            Condição: 
            <select name='cmb_condicao_filtro' title='Selecione uma Condição' class='combo'>
                <option value='' style="color:red">SELECIONE</option>
                <?
                    if($cmb_condicao_filtro == 'C') {
                        $filtro_c = 'selected';
                    }else if($cmb_condicao_filtro == 'P') {
                        $filtro_p = 'selected';
                    }
                ?>
                <option value='C' <?=$filtro_c;?>>Concluído</option>
                <option value='P' <?=$filtro_p;?>>Pendente</option>
            </select>
            &nbsp;Representante:
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select> 
            <br>
            <?
//Ainda não fez o Filtro ...
                if(empty($hdd_ja_submeteu)) {//Sugestão na hora em que acaba de carregar a Tela ...
                    $txt_data_inicial   = date('d/m/Y');
                    $txt_data_final     = date('d/m/Y');
                }else {
//Busca das Pendências no Período especificado ...
                    if(!empty($txt_data_inicial)) {
                        $txt_data_inicial_usa   = data::datatodate($txt_data_inicial, '-');
                        $txt_data_final_usa     = data::datatodate($txt_data_final, '-');
                        $condicao = " AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' ";
                    }
                }
            ?>
            &nbsp;Data Inicial: 
            <input type='text' name='txt_data_inicial' value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final: 
            <input type = "text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<? 
//Ainda não fez o Filtro ...
    if(empty($hdd_ja_submeteu)) {
?>
    <tr>
        <td></td>
    </tr>
<? 
    }else {
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
        $vetor = array_sistema::follow_up_cliente();
/********************************Follow-UP******************************/
//Tratamento com os objetos abaixo, p/ não furar o SQL ...
        if(empty($cmb_condicao_filtro)) $cmb_condicao_filtro = '%';
        if(empty($cmb_representante))   $cmb_representante = '%';

        $sql = "SELECT * 
                FROM `clientes_follow_ups` 
                WHERE `status_ocorrencia` LIKE '$cmb_condicao_filtro' 
                AND `id_representante` LIKE '$cmb_representante' 
                $condicao 
                GROUP BY `id_cliente_follow_up` ORDER BY `data_ocorrencia` DESC ";
        $campos_follow_up   = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
        $linhas_follow_up   = count($campos_follow_up);
        if($linhas_follow_up == 0) {//Caso não encontre algum Cliente nesse Tipo de Filtro ...
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
            exit;
        }
?>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            FOLLOW(S) UP(S) REGISTRADO(S)
        </td>
    </tr>
    <tr class='linhanormaldestaque' align='center'>
        <td>
            CLIENTE
        </td>
        <td>
            CIDADE
        </td>
        <td>
            UF
        </td>
        <td bgcolor='#CECECE'>
            <font title='Representante' style='cursor:help'>
                REP
            </font>
        </td>
        <td>
            ORIGEM
        </td>
        <td>
            N.º
        </td>
        <td>
            LOGIN
        </td>
        <td>
            OCORRÊNCIA
        </td>
        <td>
            DATA DE RETORNO
        </td>
        <td>
            CONTATO
        </td>
        <td bgcolor='#CECECE'>
            <font title="Tipo" style='cursor:help'>
                T
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title="Situação" style='cursor:help'>
                S
            </font>
        </td>
        <td>OBSERVAÇÃO</td>
    </tr>
<?
//Listagem de Follow-Up(s) ...
        for($i = 0; $i < $linhas_follow_up; $i++) {
            //Busca de + alguns campos complementares p/ serem exibidos dentro do Loop ...
            $sql = "SELECT cc.`id_cliente`, 
                    IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, 
                    c.`cidade`, cc.`nome`, ufs.`sigla` 
                    FROM `clientes_contatos` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
                    WHERE cc.id_cliente_contato = '".$campos_follow_up[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_dados_gerais = bancos::sql($sql);
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href = '../apv/informacoes_apv.php?id_clientes=<?=$campos_dados_gerais[0]['id_cliente'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_dados_gerais[0]['cliente'];?>
            </a>
            &nbsp;
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Registrar Follow-UP' alt='Registrar Follow-UP' onclick="html5Lightbox.showLightbox(7, '../../classes/follow_ups/detalhes.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&origem=11')">
        </td>
        <td>
            <?=$campos_dados_gerais[0]['cidade'];?>
        </td>
        <td>
            <?=$campos_dados_gerais[0]['sigla'];?>
        </td>
        <td>
        <?
            //Aqui eu busco o Nome do Representante do Cliente ...
            $sql = "SELECT nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos_follow_up[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo "<font title='".$campos_representante[0]['nome_fantasia']."' style='cursor:help'>".$campos_representante[0]['nome_fantasia']."</font>";
        ?>
        </td>
        <td>
            <?=$vetor[$campos_follow_up[$i]['origem']];?>
        </td>
<?
                if($campos_follow_up[$i]['origem'] == 3) {//Tela de Gerenciar Estoque
?>
        <td><?//='Cliente';?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 4) {//Contas à Receber
                    $sql = "SELECT num_conta 
                            FROM `contas_receberes` 
                            WHERE `id_conta_receber` = '".$campos_follow_up[$i]['identificacao']."' LIMIT 1 ";
                    $campos_numero_conta = bancos::sql($sql);
?>
        <td><?=$campos_numero_conta[0]['num_conta'];?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 5) {//Nota Fiscal
?>
        <td><?=faturamentos::buscar_numero_nf($campos_follow_up[$i]['identificacao']);?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 6) {//APV
//Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
                    if($campos_follow_up[$i]['modo_venda'] == 1) {
?>
        <td>FONE</td>
<?
                    }else {
?>
        <td>VISITA</td>
<?
                    }
                }else if($campos_follow_up[$i]['origem'] == 7) {//Atend. Interno
?>
        <td><?//='Atend. Interno:';?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 8) {//Depto. Técnico
?>
        <td><?//='Depto. Técnico:';?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 9) {//Pendências
?>
        <td><?//='Pendências:';?></td>
<?
                }else if($campos_follow_up[$i]['origem'] == 10) {//TeleMarketing
?>
        <td><?//='TeleMkt:';?></td>
<?
//Quando for 1) Orçamento ou 2) Pedido, por coincidência é o próprio id
                }else {
?>
        <td><?=$campos_follow_up[$i]['identificacao'];?></td>
<?
                }
//Aqui busca o Login na Tabela Relacional
                $sql = "SELECT login 
                        FROM `logins` 
                        WHERE `id_funcionario` = ".$campos_follow_up[$i]['id_funcionario']." LIMIT 1 ";
                $campos_login = bancos::sql($sql);
?>
        <td>
            <?=$campos_login[0]['login'];?>
        </td>
        <td>
            <?=data::datetodata($campos_follow_up[$i]['data_ocorrencia'], '/').' - '.substr($campos_follow_up[$i]['data_ocorrencia'], 11, 8);?>
        </td>
        <td>
        <?
            if($campos_follow_up[$i]['tipo_ocorrencia'] == 'F') {//Follow-UP ...
                echo ' - ';
            }else {//Agendamento ...
//Se a Data de Retorno for maior do que a Data Atual, então printa em Verde ... 
                if(substr($campos_follow_up[$i]['data_retorno'], 0, 10) >= date('Y-m-d')) {
                    $font = "<font color='green'>";
                }else {
                    $font = "<font color='black'>";
                }
                echo $font.data::datetodata(substr($campos_follow_up[$i]['data_retorno'], 0, 10), '/').' - '.substr($campos_follow_up[$i]['data_retorno'], 11, 8);
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos_dados_gerais[0]['nome'];?>
        </td>
        <td>
        <?
            if($campos_follow_up[$i]['tipo_ocorrencia'] == 'F') {
                echo "<font title='Follow-UP' style='cursor:help'>F</font>";
            }else {
                echo "<font title='Agendamento' style='cursor:help'>A</font>";
            }
        ?>
        </td>
        <td>
        <?
            if($campos_follow_up[$i]['status_ocorrencia'] == 'P') {//Pendente ...
                $font = "<font color='green' title='Pendente' style='cursor:help'>";
            }else {
                $font = "<font color='black' title='Concluída' style='cursor:help'>";
            }
            echo $font.$campos_follow_up[$i]['status_ocorrencia'];
        ?>
        </td>
        <td align='left'>
            <?=$campos_follow_up[$i]['observacao'];?>
        </td>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            &nbsp;
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
?>