<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor, $veio_incluir_orcamento, $id_cliente, $trazer_clientes_matrizes) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/validar.js'></Script>
</head>
<body onload='document.form.txt_nome_fantasia.focus()'>
<form name='form' method='post' action=''>
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='trazer_clientes_matrizes' value='<?=$trazer_clientes_matrizes;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name="txt_nome_fantasia" title="Digite a Nome Fantasia" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name="txt_razao_social" title="Digite a Razão Social" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ / CPF
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ ou CPF' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name="txt_cidade" title="Digite a Cidade" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Telefone
        </td>
        <td>
            <input type='text' name='txt_telefone' title='Digite o Telefone' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            E-mail
        </td>
        <td>
            <input type='text' name='txt_email' title='Digite o E-mail' size='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Novo Tipo de Cliente
        </td>
        <td>
            <select name='cmb_novo_tipo_cliente' title='Selecione o Novo Tipo de Cliente' class='combo'>
            <?
                $sql = "SELECT `id_cliente_tipo`, `tipo` 
                        FROM `clientes_tipos` 
                        ORDER BY `tipo` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Perfil de Cliente
        </td>
        <td>
            <select name='cmb_perfil_cliente' title='Selecione o Perfil de Cliente' class='combo'>
            <?
                $sql = "SELECT `id_cliente_perfil`, `perfil` 
                        FROM `clientes_perfils` 
                        ORDER BY `perfil` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Volume de Compras
        </td>
        <td>
            <select name="cmb_volume_compras" title="Selecione o Volume de Compras" class='combo'>
                <option value="" style="color:red">SELECIONE</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="E">E</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Condições Especiais
        </td>
        <td>
            <select name='cmb_condicoes_especiais' title='Condições Especiais' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1'>É COMERCIAL EXPORTADOR (TRADING)</option>
                <option value='2'>PAGAR COMISSÃO COMO CIDADE DE SP.</option>
                <option value='3'>SUSPENSO IPI</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Crédito
        </td>
        <td>
            <select name='cmb_credito' title='Selecione o Crédito' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='A'>A</option>
                <option value='B'>B</option>
                <option value='C'>C</option>
                <option value='D'>D</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' id="clientes_com_email" name='chkt_clientes_com_email' value='1' title="Clientes c/ Email" class="checkbox">
            <label for="clientes_com_email">Clientes c/ Email</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' id="ceps_invalidos" name='chkt_ceps_invalidos' value='1' title="Ceps Inválidos" class="checkbox">
            <label for="ceps_invalidos">Ceps Inválidos</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' id="clientes_sem_visita" name='chkt_clientes_sem_visita' value='1' title="Clientes s/ Visita" class="checkbox">
            <label for="clientes_sem_visita">Clientes s/ Visita</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' id="clientes_com_codigo_0" name='chkt_clientes_com_codigo_0' value='1' title="Clientes c/ Código 0" class="checkbox">
            <label for="clientes_com_codigo_0">Clientes c/ Código 0</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style='color:#ff9900' onclick="document.form.txt_nome_fantasia.focus()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    //Irá listar somente o Cliente Matriz ...
    if(!empty($trazer_clientes_matrizes))   $condicao_matriz = " AND c.`matriz` = 'S' ";
    if(empty($order_by))                    $order_by = 'c.`razaosocial`';
    if(empty($qtde_por_pagina))             $qtde_por_pagina = 100;

//Tratamento com os Campos de "CNPJ ou CPF" ...
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('/', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('-', '', $txt_cnpj_cpf);

//Tratamento com as Combos agora ...
    if(!empty($cmb_uf)) $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";
//Significa que foi selecionado essa combo
    if($cmb_condicoes_especiais == '') {
        $cmb_condicoes_especiais = '%';
    }else {
        if($cmb_condicoes_especiais == 1) {
            $condicao = " AND `trading` = '1' ";
        }else if($cmb_condicoes_especiais == 2) {
            $condicao = " AND `base_pag_comissao` = '1' ";
        }else if($cmb_condicoes_especiais == 3) {
            $condicao = " AND `artigo_isencao` = '1' ";
        }
    }
    if(!empty($cmb_novo_tipo_cliente))  $condicao_novo_tipo_cliente = " AND c.`id_cliente_tipo` LIKE '$cmb_novo_tipo_cliente' ";
    if(!empty($cmb_perfil_cliente))     $condicao_perfil_cliente = " AND c.`id_cliente_perfil` LIKE '$cmb_perfil_cliente' ";
    if($cmb_volume_compras == '')       $cmb_volume_compras = '%';
    if($cmb_credito == '')              $cmb_credito = '%';
//Significa que foi selecionado para mostrar somente os Clientes com EMAIL ...
    if($chkt_clientes_com_email == 1)   $condicao_clientes_com_email = " AND `email` <> '' ";
//Significa que foi selecionado para mostrar somente os ceps inválidos ...
    if($chkt_ceps_invalidos == 1)       $condicao_ceps_invalidos = " AND LENGTH(`cep`) <> '9' ";
//Significa que é para Mostrar somente os Clientes que ainda não tiveram nenhuma visita
    if($chkt_clientes_sem_visita == 1)  $condicao_clientes_sem_visita = " AND `data_ultima_visita` = '0000-00-00' ";
//Significa que é p/ Mostrar somente os Clientes com Código 0
    if($chkt_clientes_com_codigo_0 == 1) $condicao_clientes_com_codigo_0 = " AND `cod_cliente` = '0' ";

    $sql = "SELECT DISTINCT(c.`id_cliente`), c.`id_pais`, c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, 
            c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, c.`cep`, c.`cidade`, c.`ddi_com`, c.`ddd_com`, 
            c.`telcom`, c.`ddi_fax`, c.`ddd_fax`, c.`telfax`, c.`email`, ct.`tipo`, ufs.`sigla` 
            FROM `clientes` c 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` ";
    
    if($cmb_representante != '') {
        $sql.= "INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$cmb_representante' ";
        $order_by = 'c.`cidade`, ufs.`sigla` ';
    }

    $sql.= "WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
            AND c.`credito` LIKE '$cmb_credito' 
            AND c.`cidade` LIKE '%$txt_cidade%' 
            $condicao_uf 
            $condicao_novo_tipo_cliente 
            $condicao_perfil_cliente 
            AND c.`volume_compras` LIKE '$cmb_volume_compras' 
            AND (c.`telcom` LIKE '%$txt_telefone%' OR c.`telfax` LIKE '%$txt_telefone%') 
            AND c.`email` LIKE '%$txt_email%' 
            AND c.`ativo` = '1' 
            $condicao $condicao_clientes_com_email $condicao_ceps_invalidos $condicao_clientes_sem_visita $condicao_clientes_com_codigo_0 $condicao_matriz 
            ORDER BY $order_by ";

    $sql_extra = "SELECT COUNT(DISTINCT(c.id_cliente)) AS total_registro 
                    FROM `clientes` c 
                    LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` ";
    
    if($cmb_representante != '') $sql_extra.= "INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$cmb_representante' ";
    
    $sql_extra.= "WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
                AND c.`razaosocial` LIKE '%$txt_razao_social%' 
                AND c.`cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
                AND c.`credito` LIKE '$cmb_credito' 
                AND c.`cidade` LIKE '%$txt_cidade%' 
                $condicao_uf 
                $condicao_novo_tipo_cliente 
                $condicao_perfil_cliente 
                AND c.`volume_compras` LIKE '$cmb_volume_compras' 
                AND (c.`telcom` LIKE '$txt_telefone%' OR c.`telfax` LIKE '$txt_telefone%') 
                AND c.`email` LIKE '%$txt_email%' 
                AND c.`ativo` = '1' 
                $condicao $condicao_clientes_com_email $condicao_ceps_invalidos $condicao_clientes_sem_visita $condicao_clientes_com_codigo_0 $condicao_matriz 
                ORDER BY $order_by "; 
        $campos = bancos::sql($sql, $inicio, $qtde_por_pagina, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) {
//Aqui eu chamo a Tela Principal de Filtro ...
        filtro($nivel_arquivo_principal, 1, $veio_incluir_orcamento, $id_cliente, $trazer_clientes_matrizes);
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, '', $veio_incluir_orcamento, $id_cliente, $trazer_clientes_matrizes);
}
?>