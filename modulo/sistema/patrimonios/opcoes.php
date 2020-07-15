<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">PATRIMÔNIO EXCLUÍDO COM SUCESSO.</font>';

if(!empty($_POST['id_patrimonio'])) {//Exclusão de Patrimônio ...
    //Antes de se excluir o Patrimônio da Empresa, é enviado um e-mail ao Roberto p/ que este fique ciente do ocorrido ...
    $sql = "SELECT * 
            FROM `patrimonios` 
            WHERE `id_patrimonio` = '$_POST[id_patrimonio]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $texto_email.=  '<br/><b>Marca / Modelo: </b>'.$campos[0]['marca_modelo'];
    if(!empty($campos[0]['numero_serie']))          $texto_email.= '<br/><b>Número de Série: </b>'.$campos[0]['numero_serie'];
    if(!empty($campos[0]['sistema_operacional']))   $texto_email.= '<br/><b>Sistema Operacional: </b>'.$campos[0]['sistema_operacional'];
    if(!empty($campos[0]['processador']))           $texto_email.= '<br/><b>Processador: </b>'.$campos[0]['processador'];
    if(!empty($campos[0]['memoria']))               $texto_email.= '<br/><b>Memória: </b>'.$campos[0]['memoria'];
    if(!empty($campos[0]['hd']))                    $texto_email.= '<br/><b>HD: </b>'.$campos[0]['hd'];
    if(!empty($campos[0]['valor']))                 $texto_email.= '<br/><b>Valor: </b>'.number_format($campos[0]['valor'], 2, ',', '.');
    if(!empty($campos[0]['observacao']))            $texto_email.= '<br/><b>Observação: </b>'.$campos[0]['observacao'];
    
    //Busco o nome do Funcionário que está excluindo o Patrimônio ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    
    $mensagem_email = 'O Patrimônio <b>"'.$campos[0]['tipo_patrimonio'].'"</b>: <br/>'.$texto_email.'<br/><br/>Foi excluído pelo funcionário <b>'.$campos_funcionario[0]['nome'].'</b> no dia '.date('d/m/Y').' às '.date('H:i:s').'.';
    comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br', '', 'Exclusão de Patrimônio', $mensagem_email);
    
    //Aqui o Patrimônio é deletado ...
    $sql = "DELETE FROM `patrimonios` WHERE `id_patrimonio` = '$_POST[id_patrimonio]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Patrimônio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_patrimonio) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_patrimonio.value = id_patrimonio
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_patrimonio'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Patrimônio(s)

            <p/>Tipo de Patrimônio: 
            <?
                if($_POST['cmb_tipo_patrimonio'] == 'CELULAR') {
                    $selected_celular = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'COMPUTADOR') {
                    $selected_computador = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'IMPRESSORA') {
                    $selected_impressora = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'INSTRUMENTO DE MEDIÇÃO') {
                    $selected_inst_medicao = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'MONITOR') {
                    $selected_monitor = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'TELEFONE') {
                    $selected_telefone = 'selected';
                }else if($_POST['cmb_tipo_patrimonio'] == 'UMIDIFICADOR') {
                    $selected_umidificador = 'selected';
                }
            ?>
            <select name='cmb_tipo_patrimonio' title='Selecione o Tipo de Patrimônio' onchange='document.form.submit()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='CELULAR' <?=$selected_celular;?>>CELULAR</option>
                <option value='COMPUTADOR' <?=$selected_computador;?>>COMPUTADOR</option>
                <option value='IMPRESSORA' <?=$selected_impressora;?>>IMPRESSORA</option>
                <option value='INSTRUMENTO DE MEDIÇÃO' <?=$selected_inst_medicao;?>>INSTRUMENTO DE MEDIÇÃO</option>
                <option value='MONITOR' <?=$selected_monitor;?>>MONITOR</option>
                <option value='TELEFONE' <?=$selected_telefone;?>>TELEFONE</option>
                <option value='UMIDIFICADOR' <?=$selected_umidificador;?>>UMIDIFICADOR</option>
            </select>
        </td>
    </tr>
<?
    if(!empty($_POST['cmb_tipo_patrimonio'])) $condicao_tipo_patrimonio = " WHERE p.`tipo_patrimonio` = '$_POST[cmb_tipo_patrimonio]' ";
//Aqui eu busco todos os Patrimônios cadastrados ...
    $sql = "SELECT p.*, d.`departamento`, f.`nome` 
            FROM `patrimonios` p 
            INNER JOIN `departamentos` d ON d.`id_departamento` = p.`id_departamento` 
            LEFT JOIN `funcionarios` f ON f.`id_funcionario` = p.`id_funcionario` 
            $condicao_tipo_patrimonio 
            ORDER BY p.`tipo_patrimonio`, p.`data_sys` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='14'>
            NÃO HÁ PATRIMÔNIO(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Departamento
        </td>
        <td>
            Para Funcionário
        </td>
        <td>
            Tipo Patrimônio
        </td>
        <td>
            Marca / Modelo
        </td>
        <td>
            Número de Série
        </td>
        <td>
            Sistema Operacional
        </td>
        <td>
            Processador
        </td>
        <td>            
            Memória
        </td>
        <td>
            HD
        </td>
        <td>
            Valor
        </td>
        <td>
            Observação
        </td>
        <td>           
            Login / Data
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
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['tipo_patrimonio'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['marca_modelo'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['numero_serie'];?>
        </td>
        <td>        
            <?=$campos[$i]['sistema_operacional'];?>        
        </td>
        <td>
            <?=$campos[$i]['processador'];?> 
        </td>
        <td>
            <?=$campos[$i]['memoria'];?> 
        </td>
        <td>
            <?=$campos[$i]['hd'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
        <?
            //Busco o Login de quem registrou esse Patrimônio ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_registrou']."' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'].' em '.data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' às '.substr($campos[$i]['data_sys'], 11, 8);
        ?>
        </td>   
        <td>
        <?
            /*Só pode alterar esse Tipo de Registro o próprio autor ou "Rodrigo Soares 54 Depto Técnico, 
            Roberto 62 porque é diretor ou Dárcio 98 porque programa o Sistema" ...*/
            if($campos[$i]['id_funcionario_registrou'] == $_SESSION['id_funcionario'] || ($_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98)) {//Só o autor desse Registro que pode alterar dados ...
        ?>
            <img src='../../../imagem/menu/alterar.png' border='0' onclick="html5Lightbox.showLightbox(7, 'alterar.php?id_patrimonio=<?=$campos[$i]['id_patrimonio'];?>')" alt='Alterar Patrimônio' title='Alterar Patrimônio'>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            /*Só pode excluir esse Tipo de Registro o próprio autor ou "Rodrigo Soares 54 Depto Técnico, 
            Roberto 62 porque é diretor ou Dárcio 98 porque programa o Sistema" ...*/
            if($campos[$i]['id_funcionario_registrou'] == $_SESSION['id_funcionario'] || ($_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98)) {//Só o autor desse Registro que pode excluir dados ...
        ?>
            <img src='../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_patrimonio'];?>')" alt='Excluir Patrimônio' title='Excluir Patrimônio'>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='14'>
            <a href='incluir.php' title='Incluir Patrimônio(s)'>
                <font color='#FFFF00'>
                    Incluir Patrimônio(s)
                </font>
            </a>
        </td>
    </tr>
</table>
</form>
</body>
</html>