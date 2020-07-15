<?
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>PA(S) INCLUÍDO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>PA(S) ALTERADO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>PA(S) DESATRELADO(S) COM SUCESSO.</font>";

$data_hoje = date('Y-m-d');

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_cliente         = $_POST['id_cliente'];
        $id_orcamento_venda = $_POST['id_orcamento_venda'];
        $hdd_pa_cod_cliente = $_POST['hdd_pa_cod_cliente'];
    }else {
        $id_cliente         = $_GET['id_cliente'];
        $id_orcamento_venda = $_GET['id_orcamento_venda'];
        $hdd_pa_cod_cliente = $_GET['hdd_pa_cod_cliente'];
    }
/****************************Controles****************************/
//1) Alterar PA(s) ...
    if($_POST['hdd_pa_cod_cliente_salvar'] == 1) {
        /**************************************************************************/
        /****************************Orçamento de Vendas***************************/
        /**************************************************************************/
        if(!empty($id_orcamento_venda)) {//Significa que esta tela foi acessada de dentro do Orçamento de Vendas ...
            foreach($_POST['hdd_produto_acabado'] as $i => $id_produto_acabado) {
                if(!empty($_POST['txt_cod_cliente'][$i])) {
                    //Verifico se existe código cadastrado p/ esse Produto Acabado e p/ esse Cliente ...
                    $sql = "SELECT `id_pa_cod_cliente` 
                            FROM `pas_cod_clientes` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' 
                            AND `id_cliente` = '$id_cliente' LIMIT 1 ";
                    $campos_pas_cod_cliente = bancos::sql($sql);
                    if(count($campos_pas_cod_cliente) == 0) {//Não existe um código cadastrado p/ esse Produto Acabado e p/ esse Cliente, então realizo um cadastro ...
                        $sql = "INSERT INTO `pas_cod_clientes` (`id_pa_cod_cliente`, `id_produto_acabado`, `id_cliente`, `cod_cliente`) VALUES (NULL, '$id_produto_acabado', '$id_cliente', '".$_POST['txt_cod_cliente'][$i]."') ";
                        bancos::sql($sql);
                        $valor = 1;
                    }else {//Já existe um código cadastrado p/ esse Produto Acabado e p/ esse Cliente, então só atualizo seu cadastro ...
                        //Verifico se já não existe um outro PA desse Cliente com esse Código que o usuário está tentando atualizar ...
                        $sql = "SELECT `id_pa_cod_cliente` 
                                FROM `pas_cod_clientes` 
                                WHERE `cod_cliente` = '".$_POST['txt_cod_cliente'][$i]."' 
                                AND `id_cliente` = '$id_cliente' 
                                AND `id_produto_acabado` <> '$id_produto_acabado' LIMIT 1 ";
                        $campos = bancos::sql($sql);
                        if(count($campos) == 0) {//Não temos Código repetido desse PA p/ o mesmo Cliente ...
                            $sql = "UPDATE `pas_cod_clientes` SET `cod_cliente` = '".$_POST['txt_cod_cliente'][$i]."' WHERE `id_pa_cod_cliente` = '".$campos_pas_cod_cliente[0]['id_pa_cod_cliente']."' LIMIT 1 ";
                            bancos::sql($sql);
                            $valor = 2;
                        }else {//Já existe Código repetido desse PA p/ o mesmo Cliente ...
                ?>
                    <Script Language = 'JavaScript'>
                        alert('EXISTE(M) PA(S) QUE NÃO FOI(RAM) ALTERADO(S) !!!\n\nJÁ EXISTE(M) PA(S) COM ESSE MESMO CÓDIGO P/ ESSE MESMO CLIENTE !')
                    </Script>
                <?
                        }
                    }
                }
            }
        /**************************************************************************/
        /**********************************Cliente*********************************/
        /**************************************************************************/
        }else {//Significa que esta tela foi acessada pela Permissão "Clientes vs Produtos Acabados" ... 
            foreach($_POST['hdd_pa_cod_cliente'] as $i => $id_pa_cod_cliente) {
                //Verifico se já não existe um outro PA desse Cliente com esse Código que o usuário está tentando atualizar ...
                $sql = "SELECT `id_pa_cod_cliente` 
                        FROM `pas_cod_clientes` 
                        WHERE `cod_cliente` = '".$_POST['txt_cod_cliente'][$i]."' 
                        AND `id_pa_cod_cliente` <> '$id_pa_cod_cliente' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//Não temos Código repetido desse PA p/ o mesmo Cliente ...
                    $sql = "UPDATE `pas_cod_clientes` SET `cod_cliente` = '".$_POST['txt_cod_cliente'][$i]."' WHERE `id_pa_cod_cliente` = '".$_POST['hdd_pa_cod_cliente'][$i]."' LIMIT 1 ";
                    bancos::sql($sql);
                    $valor = 2;
                }else {//Já existe Código repetido desse PA p/ o mesmo Cliente ...
            ?>
                <Script Language = 'JavaScript'>
                    alert('EXISTE(M) PA(S) QUE NÃO FOI(RAM) ALTERADO(S) !!!\n\nJÁ EXISTE(M) PA(S) COM ESSE MESMO CÓDIGO P/ ESSE MESMO CLIENTE !')
                </Script>
            <?
                }
            }
        }
    }
//2) Apagar PA(s) ...
    if(!empty($_POST['hdd_pa_cod_cliente_excluir'])) {//Exclusão do Concorrente ...
        $sql = "DELETE FROM `pas_cod_clientes` WHERE `id_pa_cod_cliente` = '$_POST[hdd_pa_cod_cliente_excluir]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 3;
    }
/******************************************************************************/
    
    /**************************************************************************/
    /****************************Orçamento de Vendas***************************/
    /**************************************************************************/
    if(!empty($id_orcamento_venda)) {//Significa que esta tela foi acessada de dentro do Orçamento de Vendas ...
        //Busco o id_cliente através do $id_orcamento_venda passado por parâmetro ...
        $sql = "SELECT `id_cliente` 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        $id_cliente     = $campos_cliente[0]['id_cliente'];
    }
?>
<html>
<head>
<title>.:: Incluir Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function incluir_produto_acabado() {
    html5Lightbox.showLightbox(7, 'incluir_produtos_acabados.php?id_cliente=<?=$id_cliente;?>')
}

function clonar_para_filial() {
    html5Lightbox.showLightbox(7, 'clonar_para_filial.php?id_cliente=<?=$id_cliente;?>')
}

function excluir_item(id_pa_cod_cliente) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESSE PRODUTO ACABADO DESSE CLIENTE ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.hdd_pa_cod_cliente_excluir.value  = id_pa_cod_cliente
        //Aqui é para não atualizar a Tela abaixo desse Pop-UP Div ...
        document.form.nao_atualizar.value               = 1
        document.form.submit()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        parent.ativar_loading()
        parent.html5Lightbox.finish()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action=''>
<!--********Controle de Tela********-->
<input type='hidden' name='hdd_pa_cod_cliente_salvar'>
<input type='hidden' name='hdd_pa_cod_cliente_excluir'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='nao_atualizar'>
<!--********************************-->
<?
//Esse parâmetro só será nulo quando entrar nessa Tela 
if(empty($parametro_cliente)) $parametro_cliente = $parametro;
?>
<input type='hidden' name='parametro_cliente' value='<?=$parametro_cliente;?>'>
<!--****************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Incluir Produto(s) Acabado(s)
            <?
                /**************************************************************************/
                /****************************Orçamento de Vendas***************************/
                /**************************************************************************/
                if(empty($_GET['id_orcamento_venda'])) {//Se essa tela foi acessada pela Permissão "Clientes vs Produtos Acabados", então exibo ...
            ?>
                p/ o Cliente -> 
                <font color='yellow'>
                <?
                    //Seleção da Razão Social do Cliente ...
                    $sql = "SELECT IF(nomefantasia = '', razaosocial, nomefantasia) AS cliente 
                            FROM `clientes` 
                            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                    $campos = bancos::sql($sql);
                    echo $campos[0]['cliente'];
                ?>
                </font>
            <?
                }
            ?>
        </td>
    </tr>
<?
    /**************************************************************************/
    /****************************Orçamento de Vendas***************************/
    /**************************************************************************/
    if(!empty($id_orcamento_venda)) {//Significa que esta tela foi acessada de dentro do Orçamento de Vendas ...
        //Busca de todos PA(s) do $id_orcamento_venda passado por parâmetro ...
        $sql = "SELECT `id_produto_acabado` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
        $campos_orcamentos_itens = bancos::sql($sql);
        $linhas_orcamentos_itens = count($campos_orcamentos_itens);
        if($linhas_orcamentos_itens == 0) {//Não existe nenhum item no Orçamento ...
            $id_produtos_acabados = 0;
        }else {//Existe pelo menos 1 item ...
            for($i = 0; $i < $linhas_orcamentos_itens; $i++) $id_produtos_acabados.= $campos_orcamentos_itens[$i]['id_produto_acabado'].', ';
            $id_produtos_acabados = substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2);
        }
        
        $sql = "SELECT DISTINCT(`id_produto_acabado`), `referencia` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` IN ($id_produtos_acabados) ORDER BY `referencia`, `discriminacao` ";
    /**************************************************************************/
    /**********************************Cliente*********************************/
    /**************************************************************************/
    }else {//Significa que esta tela foi acessada pela Permissão "Clientes vs Produtos Acabados" ... 
        //Busca de todos PA(s) atrelados ao Cliente que foi passado por parâmetro ...
        $sql = "SELECT pcc.`id_pa_cod_cliente`, pcc.`id_produto_acabado`, pcc.`cod_cliente`, pa.`referencia` 
                FROM `pas_cod_clientes` pcc 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pcc.`id_produto_acabado` 
                WHERE pcc.`id_cliente` = '$id_cliente' ORDER BY pa.`referencia`, pa.`discriminacao` ";
    }
    /**************************************************************************/
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhum Produto Acabado ...
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO HÁ PRODUTO(S) ACABADO(S) ATRELADO(S).
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
        <?
            /**************************************************************************/
            /**********************************Cliente*********************************/
            /**************************************************************************/
            if(empty($id_orcamento_venda)) {//Se essa tela foi acessada pela Permissão "Clientes vs Produtos Acabados", então exibo ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'vs_produtos_acabados.php'" class='botao'>
            <input type='button' name='cmd_incluir_produto_acabado' value='Incluir PA' title='Incluir PA' onclick='incluir_produto_acabado()' style='color:black' class='botao'>
        <?
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
<?
    }else {//Se existir pelo Menos 1 Produto Acabado, então eu exibo os dados abaixo ...
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência p/ Colagem
        </td>
        <td>
            Produto Acabado
        </td>
        <td>
            Código p/ Colagem
        </td>
        <td>
            Código do Cliente
        </td>
        <td> 
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '');?>
        </td>
        <td>
            <?
            /**************************************************************************/
            /**********************************Cliente*********************************/
            /**************************************************************************/
            if(empty($id_orcamento_venda)) {//Se essa tela foi acessada pela Permissão "Clientes vs Produtos Acabados", então exibo ...   
                $id_pa_cod_cliente  = $campos[$i]['id_pa_cod_cliente'];
                $cod_cliente        = $campos[$i]['cod_cliente'];
            /**************************************************************************/
            /****************************Orçamento de Vendas***************************/
            /**************************************************************************/
            }else {
                //Verifico se existe código cadastrado p/ esse Produto Acabado e p/ esse Cliente ...
                $sql = "SELECT `id_pa_cod_cliente`, `cod_cliente` 
                        FROM `pas_cod_clientes` 
                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_pas_cod_cliente = bancos::sql($sql);
                $id_pa_cod_cliente      = $campos_pas_cod_cliente[0]['id_pa_cod_cliente'];
                $cod_cliente            = $campos_pas_cod_cliente[0]['cod_cliente'];
            }
            echo $cod_cliente;
        ?>
        </td>
        <td>
            <input type='text' name='txt_cod_cliente[]' value='<?=$cod_cliente;?>' maxlength='25' size='20' class='caixadetexto'>
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$id_pa_cod_cliente;?>')" alt='Excluir Código de Cliente' title='Excluir Código de Cliente'>
            <input type='hidden' name='hdd_pa_cod_cliente[]' value='<?=$id_pa_cod_cliente;?>'>
            <input type='hidden' name='hdd_produto_acabado[]' value='<?=$campos[$i]['id_produto_acabado'];?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <?
                /**************************************************************************/
                /****************************Orçamento de Vendas***************************/
                /**************************************************************************/
                if(!empty($id_orcamento_venda)) {//Significa que esta tela foi acessada de dentro do Orçamento de Vendas ...
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = '../../vendas/orcamentos/itens/outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <?
                /**************************************************************************/
                /**********************************Cliente*********************************/
                /**************************************************************************/
                }else {//Significa que esta tela foi acessada pela Permissão "Clientes vs Produtos Acabados" ... 
            ?>
            <input type='button' name='cmd_atualizar_pas' value='Atualizar Pas do Cliente' title='Atualizar Pas do Cliente' onclick="html5Lightbox.showLightbox(7, '../../classes/cliente/atualizar_pas_do_cliente.php?id_cliente=<?=$id_cliente;?>')" class='botao'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'vs_produtos_acabados.php'" class='botao'>
            <?
                }
            
                if($linhas > 0) {//Esse botão só será exibido, quando tivermos pelo menos 1 PA ...
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='document.form.hdd_pa_cod_cliente_salvar.value = 1' class='botao'>
            <?
                }
                
                /**************************************************************************/
                /**********************************Cliente*********************************/
                /**************************************************************************/
                if(empty($id_orcamento_venda)) {//Se essa tela foi acessada pela Permissão "Clientes vs Produtos Acabados", então exibo ...
            ?>
            <input type='button' name='cmd_incluir_produto_acabado' value='Incluir PA' title='Incluir PA' onclick='incluir_produto_acabado()' style='color:black' class='botao'>
            <input type='button' name='cmd_clonar_para_filial' value='Clonar p/ Filial' title='Clonar p/ Filial' onclick='clonar_para_filial()' style='color:darkblue' class='botao'>
            <?
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
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    //$somente_matriz = 1;
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Incluir Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='15'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Consultar Cliente(s) p/ Incluir Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan="2">
            <?=genericas::order_by('c.razaosocial', 'Razão Social', 'Razão Social', $order_by, '../../../');?>
        </td>
        <td>
            <?=genericas::order_by('c.nomefantasia', 'Nome Fantasia', 'Nome Fantasia', $order_by, '../../../');?>
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Tel Fax
        </td>
        <td>
            Cr
        </td>
        <td>
            E-mail
        </td>
        <td>
            Últ Visita
        </td>
        <td>
            Endereço
        </td>
        <td>
            Cidade
        </td>
        <td>
            Cep
        </td>
        <td>
            País
        </td>
        <td>
            UF
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='vs_produtos_acabados.php?passo=1&id_cliente=<?=$campos[$i]['id_cliente'];?>' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='vs_produtos_acabados.php?passo=1&id_cliente=<?=$campos[$i]['id_cliente'];?>' class='link'>
                <?=$campos[$i]['cod_cliente'].' - '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))    echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))      echo $campos[$i]['telfax'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['data_ultima_visita'] != '0000-00-00') echo data::datetodata($campos[$i]['data_ultima_visita'], '/');
        ?>
        </td>
        <?
            $endereco   = $campos[$i]['endereco'];
            $cidade     = $campos[$i]['cidade'];
            $id_estado  = $campos[$i]['id_uf'];

            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '$id_estado' LIMIT 1 ";
            $campos_uf  = bancos::sql($sql);
            $estado     = $campos_uf[0]['sigla'];
        ?>
        <td>
        <?
            echo $endereco;
            //Daí sim printa o complemento
            if(!empty($endereco)) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$cidade;?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cep'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `pais` 
                    FROM `paises` 
                    WHERE `id_pais` = '".$campos[$i]['id_pais']."' LIMIT 1 ";
            $campos_pais = bancos::sql($sql);
            echo $campos_pais[0]['pais'];
        ?>
        </td>
        <td align='center'>
            <?=$estado;?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'vs_produtos_acabados.php'" class='botao'>
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
}
?>