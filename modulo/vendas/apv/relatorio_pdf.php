<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/intermodular.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

//error_reporting(0);
function Heade() {
    global $pdf, $banco, $id_pedido_venda, $paginacao, $pagina, $desvio, $total;
    $pdf->Image('../../../imagem/logo_transparente.jpg',245,5,34,36,'JPG');
    $pdf->Cell(15*$GLOBALS['ph'], 10, '', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(170, 10, 'APV', 0, 0, 'C');
}

//IN�CIO PDF ...
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'L';// P=> Retrato L=>Paisagem
$unidade        = 'mm';// pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4';// A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
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
    echo 'Formato n�o definido';
}

$data_ocorrencia    = date('Y-m-d H:i:s');
$data_impressao     = date('d/m/Y').' - '.date('H:i:s');
//Busca o Login do usu�rio que est� imprimindo o Rel
$sql = "SELECT login 
        FROM `logins` 
        WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
$campos = bancos::sql($sql);
$login  = $campos[0]['login'];
/**********************************************/
//Transforma em vetor para poder disparar o loop
$vetor_clientes = explode(',', $_GET['id_clientes']);
/**********************************************/
//Disparo de loop para os Clientes Selecionados
foreach($vetor_clientes as $id_cliente) {
    $pdf->AddPage();
    Heade();
//Marcador de Impress�o
    $pdf->SetLeftMargin(1);
    $pdf->Ln();
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(100, 5, 'LOGIN: '.$login.' - IMPRESS�O: '.$data_impressao.' - '.$id_cliente, 0, 0, 'L');
    $pdf->Ln(5);
//Aki � a busca dos Dados do Cliente
    $sql = "SELECT * 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos = bancos::sql($sql);
    /*Aqui eu coloco o nome da vari�vel como '$id_func_resp_cliente' p/ n�o dar conflito com 
    a vari�vel $id_funcionario da sess�o ...*/
    $id_func_resp_cliente  = $campos[0]['id_funcionario'];
    $id_cliente_tipo        = $campos[0]['id_cliente_tipo'];
    $nome_fantasia          = $campos[0]['nomefantasia'];
    $razao_social           = $campos[0]['razaosocial'];
    $credito                = $campos[0]['credito'];
    $limite_credito         = $campos[0]['limite_credito'];
    $credito_data           = $campos[0]['credito_data'];
    $observacao_credito     = $campos[0]['credito_observacao'];
    $suframa                = $campos[0]['cod_suframa'];
//Dados de Endere�o
    $id_pais                = $campos[0]['id_pais'];
//Significa que o Cliente � do Tipo Internacional
    $tipo_moeda             = ($id_pais != 31) ? 'U$ ' : 'R$ ';
    $cep                    = $campos[0]['cep'];
    $numero_complemento     = $campos[0]['num_complemento'];
    $endereco               = $campos[0]['endereco'];
    $bairro                 = $campos[0]['bairro'];
    $cidade                 = $campos[0]['cidade'];
    $id_uf_cliente          = $campos[0]['id_uf'];

    $sql = "SELECT sigla 
            FROM `ufs` 
            WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
    $campos_uf  = bancos::sql($sql);
    $estado     = $campos_uf[0]['sigla'];

    $sql = "SELECT pais 
            FROM `paises` 
            WHERE `id_pais` = '$id_pais' LIMIT 1 ";
    $campos_pais        = bancos::sql($sql);
    $pais               = $campos_pais[0]['pais'];

    $ddi_com        = $campos[0]['ddi_com'];
    $ddd_com        = $campos[0]['ddd_com'];
    $telcom         = $campos[0]['telcom'];
    $telefone_com   = '('.$ddi_com.'-'.$ddd_com.') '.$telcom;

    $ddi_fax        = $campos[0]['ddi_fax'];
    $ddd_fax        = $campos[0]['ddd_fax'];
    $telfax         = $campos[0]['telfax'];
    $telefone_fax   = '('.$ddi_fax.'-'.$ddd_fax.') '.$telfax;

    $email          = $campos[0]['email'];
    $pagina_web     = $campos[0]['pagweb'];
    $observacao_vendas = $campos[0]['observacao'];
    
    $inscricao_estadual     = ($campos[0]['insc_estadual'] == 0) ? '' : substr($campos[0]['insc_estadual'], 0, 3).'.'.substr($campos[0]['insc_estadual'], 3, 3).'.'.substr($campos[0]['insc_estadual'], 6, 3).'.'.substr($campos[0]['insc_estadual'], 9, 3);
    $inscricao_municipal    = ($campos[0]['insc_municipal'] == 0) ? '' : $campos[0]['insc_municipal'];
    
    if(!empty($campos[0]['cnpj_cpf'])) {
        if(strlen($campos[0]['cnpj_cpf']) == 14) {//CNPJ ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
        }else if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
        }
    }else {
        $cnpj_cpf = '';
    }
    
    $ccm    = ($campos[0]['ccm'] == 0) ? '' : $campos[0]['ccm'];
    $rg     = $campos[0]['rg'];
    $orgao  = $campos[0]['orgao'];
	
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(29, 5, 'RAZ�O SOCIAL:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(149, 5, $razao_social, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'N. FANTASIA:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(53, 5, $nome_fantasia, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(23, 5, 'ENDERE�O:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(86, 5, $endereco.', '.$numero_complemento, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 5, 'BAIRRO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(53, 5, $bairro, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 5, 'CIDADE:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(93, 5, $cidade, 0, 1, 'L');
	
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 5, 'CEP:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(99, 5, $cep, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(17, 5, 'ESTADO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(52, 5, $estado, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(11, 5, 'PA�S:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(47, 5, $pais, 0, 1, 'L');
	
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 5, 'DDI/ DDD/ TEL. COMERCIAL:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(59, 5, $telefone_com, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(36, 5, 'DDI/ DDD/ TEL. FAX:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(53, 5, $telefone_fax, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(14, 5, 'E-MAIL:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 5, $email, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 5, 'P�GINA WEB:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(53, 5, $pagina_web, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(24, 5, 'TP CLIENTE:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'BI', 12);

    $sql = "SELECT `tipo` 
            FROM `clientes_tipos` 
            WHERE `id_cliente_tipo` = '$id_cliente_tipo' LIMIT 1 ";
    $campos_tipo = bancos::sql($sql);
    $pdf->Cell(31, 5, $campos_tipo[0]['tipo'], 0, 1, 'L');

    $pdf->Ln(1);
//Exibe os Dados de Pessoa Jur�dica
    if(strlen($cnpj_cpf) == 14) {//CNPJ ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(6, 5, 'IE: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(71, 5, $inscricao_estadual, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(6, 5, 'IM:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(52, 5, $inscricao_municipal, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(12, 5, 'CNPJ:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 5, $cnpj_cpf, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 5, 'CCM:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 5, $ccm, 0, 1, 'L');
    }else if(strlen($cnpj_cpf) == 11) {//CPF ...
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 5, 'CPF:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(80, 5, $cnpj_cpf, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(7, 5, 'RG:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 5, $rg, 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(16, 5, '�RG�O:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(100, 5, $orgao, 0, 1, 'L');
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(37, 5, 'LIMITE DE CR�DITO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, number_format($limite_credito, 2, ',', '.'), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(18, 5, 'CR�DITO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 5, $credito, 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(62, 5, '�LTIMO CR�DITO ALTERADO POR: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    //Busca o funcion�rio respons�vel pela altera��o dos dados
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_func_resp_cliente' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    if(count($campos_funcionario) == 1) {
        $pdf->Cell(50, 5, strtok($campos_funcionario[0]['nome'], ' '), 0, 0, 'L');
    }else {
        $pdf->Cell(50, 5, '', 0, 0, 'L');
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(8, 5, 'EM:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    if(substr($credito_data, 0, 10) != '0000-00-00') {
        $pdf->Cell(53, 5, data::datetodata(substr($credito_data, 0, 10), '/').substr($credito_data, 10, 9), 0, 1, 'L');
    }else {
        $pdf->Cell(53, 5, '', 0, 1, 'L');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(34, 5, 'OBS. DE CR�DITO:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(114, 5, $observacao_credito, 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(33, 5, 'OBS. DE VENDAS:', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(114, 5, $observacao_vendas, 0, 1, 'L');
    $pdf->Ln(1);
/********************************Contatos******************************/
//Aqui busca todos os contatos que est�o atrelados a esse Cliente
    $sql = "SELECT cc.*, d.departamento 
            FROM `clientes_contatos` cc 
            INNER JOIN `departamentos` d ON d.`id_departamento` = cc.`id_departamento` 
            WHERE cc.`id_cliente` = '$id_cliente' 
            AND cc.`ativo` = '1' ORDER BY d.departamento, cc.nome LIMIT 8 ";
    $campos_contatos = bancos::sql($sql);
    $linhas_contatos = count($campos_contatos);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 5, 'DEPTO - COMPRAS / PROPAG.', 1, 0, 'C');
    $pdf->Cell(50, 5, 'NOME / CARGO', 1, 0, 'C');
    $pdf->Cell(55, 5, 'DDI - DDD - TELEFONE', 1, 0, 'C');
    $pdf->Cell(30, 5, 'RAMAL', 1, 0, 'C');
    $pdf->Cell(99, 5, 'E-MAIL', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    if($linhas_contatos > 0) {
//Listagem dos Contato(s)
        for($i = 0; $i < $linhas_contatos; $i++) {
            $pdf->Cell(60, 5, $campos_contatos[$i]['departamento'], 1, 0, 'L');
//Tem que truncar o Nome / Cargo, se este for muito grande por causa do Tamanho da Coluna
            if(strlen($campos_contatos[$i]['nome']) > 20) {
                $pdf->Cell(50, 5, substr($campos_contatos[$i]['nome'], 0, 20).' ...', 1, 0, 'L');
            }else {
                $pdf->Cell(50, 5, $campos_contatos[$i]['nome'], 1, 0, 'L');
            }
            $pdf->Cell(55, 5, $campos_contatos[$i]['ddi'].' - '.$campos_contatos[$i]['ddd'].' - '.$campos_contatos[$i]['telefone'], 1, 0, 'C');
            $pdf->Cell(30, 5, $campos_contatos[$i]['ramal'], 1, 0, 'C');
            $pdf->Cell(99, 5, $campos_contatos[$i]['email'], 1, 1, 'L');
        }
    }else {
        $i = 0;//Aqui zera para n�o dar problema com as 8 linhas p/ imprimir abaixo de contato
    }
//Aki sempre ter� que ter 8 linhas 
    for(; $i < 8; $i++) {
        $pdf->Cell(60, 5, '', 1, 0, 'C');
        $pdf->Cell(50, 5, '', 1, 0, 'C');
        $pdf->Cell(55, 5, '', 1, 0, 'C');
        $pdf->Cell(30, 5, '', 1, 0, 'C');
        $pdf->Cell(99, 5, '', 1, 1, 'L');
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(294, 5, 'EXIBE SOMENTE 8 CONTATOS', 1, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Ln(2);
/******************************Cinco Linhas****************************/
    $pdf->Cell(120, 5, 'CONTATO:', 'B', 0, 'L');
    $pdf->Cell(80, 5, 'DATA: ____/ _____ / _____  -  HORA          :     ', 0, 0, 'L');
    $pdf->Cell(20, 5, $pdf->Cell(5, 5, '', 1, 0, 'L').'VISITA', 0, 0, 'L');
    $pdf->Cell(64, 5, $pdf->Cell(5, 5, '', 1, 0, 'L').'FONE', 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(147, 5, 'A��O', 1, 0, 'C');
    $pdf->Cell(147, 5, 'REA��O', 1, 1, 'C');
    for($i = 0; $i < 8; $i++) {
        $pdf->Cell(147, 5, ($i + 1).')', 1, 0, 'L');//Aqui eu en�mero as linhas
        $pdf->Cell(147, 5, '', 1, 1, 'C');
    }

    $pdf->SetFont('Arial', '', 10);
    $pdf->Ln(4);
//Marcador de Impress�o
    $pdf->SetLeftMargin(1);
    $pdf->Ln(1);
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(100, 5, 'LOGIN: '.$login.' - IMPRESS�O: '.$data_impressao.' - '.$id_cliente, 0, 0, 'L');
    $pdf->Ln(5);
//Vetor para Auxiliar as Identifica��es de Follow-UP, que busca de outro arquivo
    $vetor = array_sistema::follow_ups();
/********************************Follow-UP******************************/
    $sql = "SELECT fu.* 
            FROM `clientes_contatos` cc 
            INNER JOIN `follow_ups` fu ON fu.`id_cliente_contato` = cc.`id_cliente_contato` 
            WHERE cc.`id_cliente` = '$id_cliente' ORDER BY fu.`data_sys` DESC LIMIT 10 ";
    $campos_follow_ups = bancos::sql($sql);
    $linhas_follow_ups = count($campos_follow_ups);
    if($linhas_follow_ups > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'FOLLOW(S) UP(S) - EXIBE SOMENTE O(S) �LTIMO(S) 10 REGISTRADO(S)', 1, 1, 'C');
        $pdf->Cell(30, 5, 'ORIGEM', 1, 0, 'C');
        $pdf->Cell(15, 5, 'N.�', 1, 0, 'C');
        $pdf->Cell(20, 5, 'LOGIN', 1, 0, 'C');
        $pdf->Cell(36, 5, 'OCORR�NCIA', 1, 0, 'C');
        $pdf->Cell(38, 5, 'CONTATO', 1, 0, 'C');
        $pdf->Cell(155, 5, 'OBSERVA��O', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
//Listagem de Follow-Up(s)
        $valores_invalidos = array('<font color="blue">', '</font>', '</b>', '<br>', '<b>', 'b>', '<b', '</');
        $qtde_observacao = 72;//Tamanho do Campo Observa��o ...
        for($i = 0; $i < $linhas_follow_ups; $i++) {
//Neste caso vou precisar de v�rias linhas para colocar a Observa��o do Follow-UP
            $qtde_letras_observacao = strlen($campos_follow_ups[$i]['observacao']);
            if($qtde_letras_observacao > $qtde_observacao) {
//Esse vetor vai armazenar a posi��o inicial de cada linha da String
                $vetor_inicial_cada_linha = Array();
                $contador = 0;
                $j = 0;
//Algoritmo que vai gerar a qtde de linhas necess�rias para poder imprimir a Observa��o correta do Follow UP
                while($qtde_letras_observacao >= $qtde_observacao) {
                    $qtde_letras_observacao-= $qtde_observacao;//Vai desconto para poder sair do loop
                    $vetor_inicial_cada_linha[$j] = $contador;
                    $contador+= $qtde_observacao;//�ndice do Vetor
                    $j++;//�ndice do Vetor
                }
//Aqui armazena a �ltima linha, que n�o pegou porque saiu do loop
                $vetor_inicial_cada_linha[$j] = $contador;
//Printagem de Todas as Linhas
                for($k = 0; $k <= $j; $k++) {
//Na primeira linha, eu fa�o a printagem normalmente dos campos
                    if($k == 0) {
                        $pdf->Cell(30, 5, $vetor[$campos_follow_ups[$i]['origem']], 'RLT', 0, 'C');
                        if($campos_follow_ups[$i]['origem'] == 3) {//Tela de Gerenciar Estoque
                            $pdf->Cell(15, 5, '', 'RLT', 0, 'C');
                        }else if($campos_follow_ups[$i]['origem'] == 4) {//Contas � Receber
                            $sql = "SELECT num_conta 
                                    FROM `contas_receberes` 
                                    WHERE `id_conta_receber` = '".$campos_follow_ups[$i]['identificacao']."' LIMIT 1 ";
                            $campos_duplicata = bancos::sql($sql);
                            $pdf->Cell(15, 5, $campos_duplicata[0]['num_conta'], 'RLT', 0, 'C');
                        }else if($campos_follow_ups[$i]['origem'] == 5) {//Nota Fiscal
                            $pdf->Cell(15, 5, faturamentos::buscar_numero_nf($campos_follow_ups[$i]['identificacao'], 'S'), 'RLT', 0, 'C');
                        }else if($campos_follow_ups[$i]['origem'] == 6) {//APV
//Significa que um Follow-Up que est� sendo registrado pela parte de Vendas (Antigo Sac)
                            if($campos_follow_ups[$i]['modo_venda'] == 1) {
                                $pdf->Cell(15, 5, 'FONE', 'RLT', 0, 'C');
                            }else {
                                $pdf->Cell(15, 5, 'VISITA', 'RLT', 0, 'C');
                            }
                        }else if($campos_follow_ups[$i]['origem'] == 7) {//Atend. Interno
                            $pdf->Cell(15, 5, '', 'RLT', 0, 'C');
                        }else if($campos_follow_ups[$i]['origem'] == 8) {//Depto. T�cnico
                            $pdf->Cell(15, 5, '', 'RLT', 0, 'C');
                        }else if($campos_follow_ups[$i]['origem'] == 9) {//Pend�ncias
                            $pdf->Cell(15, 5, '', 'RLT', 0, 'C');
//Quando for 1) Or�amento ou 2) Pedido, por coincid�ncia � o pr�prio id
                        }else {
                            $pdf->Cell(15, 5, $campos_follow_ups[$i]['identificacao'], 'RLT', 0, 'C');
                        }
//Aqui busca o Login na Tabela Relacional
                        $sql = "SELECT `login` 
                                FROM `logins` 
                                WHERE `id_funcionario` = '".$campos_follow_ups[$i]['id_funcionario']."' LIMIT 1 ";
                        $campos_login = bancos::sql($sql);
                        $pdf->Cell(20, 5, $campos_login[0]['login'], 'RLT', 0, 'C');
//Data de Ocorr�ncia ...				
                        $pdf->Cell(36, 5, data::datetodata($campos_follow_ups[$i]['data_sys'], '/').' - '.substr($campos_follow_ups[$i]['data_sys'], 11, 8), 'RLT', 0, 'C');
//Aqui busca o Contato na Tabela Relacional
                        $sql = "SELECT cc.`nome`, d.`departamento` 
                                FROM `clientes_contatos` cc 
                                INNER JOIN `departamentos` d ON d.`id_departamento` = cc.`id_departamento` 
                                WHERE cc.`id_cliente_contato` = '".$campos_follow_ups[$i]['id_cliente_contato']."' LIMIT 1 ";
                        $campos_contato = bancos::sql($sql);
//Tem que truncar o Contato, se este for muito grande por causa do Tamanho da Coluna
                        if(strlen($campos_contato[0]['nome']) > 15) {
                            $pdf->Cell(38, 5, substr($campos_contato[0]['nome'], 0, 15).' ...', 'RLT', 0, 'L');
                        }else {
                            $pdf->Cell(38, 5, $campos_contato[0]['nome'], 'RLT', 0, 'L');
                        }
//Aqui printo de acordo com o Campo de Tamanho de Observa��o ...
                        $pdf->Cell(155, 5, str_replace($valores_invalidos, '', substr($campos_follow_ups[$i]['observacao'], $vetor_inicial_cada_linha[$k], $qtde_observacao)), 'RLT', 1, 'L');
//Na �ltima linha, coloco as bordas de baixo da linha
                    }else if($k == $j) {
                        $pdf->Cell(30, 5, '', 'RLB', 0, 'C');
                        $pdf->Cell(15, 5, '', 'RLB', 0, 'C');
                        $pdf->Cell(20, 5, '', 'RLB', 0, 'C');
                        $pdf->Cell(36, 5, '', 'RLB', 0, 'C');
                        $pdf->Cell(38, 5, '', 'RLB', 0, 'C');
                        $pdf->Cell(155, 5, str_replace($valores_invalidos, '', substr($campos_follow_ups[$i]['observacao'], $vetor_inicial_cada_linha[$k], $qtde_observacao)), 'RLB', 1, 'L');
//Nas demais linhas
                    }else {
                        $pdf->Cell(30, 5, '', 'RL', 0, 'C');
                        $pdf->Cell(15, 5, '', 'RL', 0, 'C');
                        $pdf->Cell(20, 5, '', 'RL', 0, 'C');
                        $pdf->Cell(36, 5, '', 'RL', 0, 'C');
                        $pdf->Cell(38, 5, '', 'RL', 0, 'C');
                        $pdf->Cell(155, 5, str_replace($valores_invalidos, '', substr($campos_follow_ups[$i]['observacao'], $vetor_inicial_cada_linha[$k], $qtde_observacao)), 'RL', 1, 'L');
                    }
                }
/*Se a Observa��o for < do que a qtde do Campo Observa��o, ent�o significa que esta cabe 
em apenas uma �nica linha*/
            }else {
                $pdf->Cell(30, 5, $vetor[$campos_follow_ups[$i]['origem']], 1, 0, 'C');
                if($campos_follow_ups[$i]['origem'] == 3) {//Tela de Gerenciar Estoque
                    $pdf->Cell(15, 5, '', 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 4) {//Contas � Receber
                    $sql = "SELECT num_conta 
                            FROM `contas_receberes` 
                            WHERE `id_conta_receber` = '".$campos_follow_ups[$i]['identificacao']."' LIMIT 1 ";
                    $campos_duplicata = bancos::sql($sql);
                    $pdf->Cell(15, 5, $campos_duplicata[0]['num_conta'], 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 5) {//Nota Fiscal
                    $pdf->Cell(15, 5, faturamentos::buscar_numero_nf($campos_follow_ups[$i]['identificacao'], 'S'), 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 6) {//APV
//Significa que um Follow-Up que est� sendo registrado pela parte de Vendas (Antigo Sac)
                    if($campos_follow_ups[$i]['modo_venda'] == 1) {
                        $pdf->Cell(15, 5, 'FONE', 1, 0, 'C');
                    }else {
                        $pdf->Cell(15, 5, 'VISITA', 1, 0, 'C');
                    }
                }else if($campos_follow_ups[$i]['origem'] == 7) {//Atend. Interno
                    $pdf->Cell(15, 5, '', 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 8) {//Depto. T�cnico
                    $pdf->Cell(15, 5, '', 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 9) {//Pend�ncias
                    $pdf->Cell(15, 5, '', 1, 0, 'C');
                }else if($campos_follow_ups[$i]['origem'] == 10) {//TeleMarketing
                    $pdf->Cell(15, 5, '', 1, 0, 'C');
//Quando for 1) Or�amento ou 2) Pedido, por coincid�ncia � o pr�prio id
                }else {
                    $pdf->Cell(15, 5, $campos_follow_ups[$i]['identificacao'], 1, 0, 'C');
                }
//Aqui busca o Login na Tabela Relacional
                $sql = "SELECT login 
                        FROM `logins` 
                        WHERE `id_funcionario` = '".$campos_follow_ups[$i]['id_funcionario']."' LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                $pdf->Cell(20, 5, $campos_login[0]['login'], 1, 0, 'C');
//Data de Ocorr�ncia ...
                $pdf->Cell(36, 5, data::datetodata(substr($campos_follow_ups[$i]['data_sys'], 0, 10), '/').' - '.substr($campos_follow_ups[$i]['data_ocorrencia'], 11, 8), 1, 0, 'C');
//Aqui busca o Contato na Tabela Relacional
                $sql = "SELECT nome, departamento 
                        FROM `clientes_contatos` cc
                        INNER JOIN `departamentos` d ON d.`id_departamento` = cc.`id_departamento` 
                        WHERE cc.`id_cliente_contato` = '".$campos_follow_ups[$i]['id_cliente_contato']."' LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
//Tem que truncar o Contato, se este for muito grande por causa do Tamanho da Coluna
                if(strlen($campos_contato[0]['nome']) > 15) {
                    $pdf->Cell(38, 5, substr($campos_contato[0]['nome'], 0, 15).' ...', 1, 0, 'L');
                }else {
                    $pdf->Cell(38, 5, $campos_contato[0]['nome'], 1, 0, 'L');
                }
                $pdf->Cell(155, 5, str_replace($valores_invalidos, '', $campos_follow_ups[$i]['observacao']), 1, 1, 'L');
            }
        }
/******************************Cinco Linhas****************************/
        for($i = 0; $i < 5; $i++) $pdf->Cell(294, 5, '', 'B', 1, 'L');
        $pdf->Ln(8);
    }
/********************************D�bitos do Cliente******************************/
    $data_atual_americano = date('Y-m-d');

    $sql = "SELECT * 
            FROM `contas_receberes` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `ativo` = '1' 
            AND `status` < '2' ORDER BY data_vencimento ";
    $campos_contas_receberes = bancos::sql($sql);
    $linhas_contas_receberes = count($campos_contas_receberes);
    if($linhas_contas_receberes > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'QTDE DE D�BITO(S)', 1, 1, 'C');
        $pdf->Cell(58, 5, 'N.� CONTA', 1, 0, 'C');
        $pdf->Cell(58, 5, 'EMPRESA', 1, 0, 'C');
        $pdf->Cell(58, 5, 'DATA DE VENCIMENTO', 1, 0, 'C');
        $pdf->Cell(60, 5, 'VALOR RECEBIDO '.$tipo_moeda, 1, 0, 'C');
        $pdf->Cell(60, 5, 'VALOR � RECEBER '.$tipo_moeda, 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
//Listagem de D�bitos do Cliente
        for($i = 0; $i < $linhas_contas_receberes; $i++) {
            $data_vencimento = $campos_contas_receberes[$i]['data_vencimento'];
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos_contas_receberes[$i]['id_conta_receber']);
//Contas vencidas
            if($data_vencimento < $data_atual_americano) {
                $total_vencidas+= $calculos_conta_receber['valor_reajustado'];
                $color = 'red';
//Contas � vencer
            }else {
                $total_vencer+= $calculos_conta_receber['valor_reajustado'];
                $color = '';
            }
            $pdf->Cell(58, 5, $campos_contas_receberes[$i]['num_conta'], 1, 0, 'C');
            $pdf->Cell(58, 5, genericas::nome_empresa($campos_contas_receberes[$i]['id_empresa']), 1, 0, 'C');
            $pdf->Cell(58, 5, data::datetodata($data_vencimento, '/'), 1, 0, 'C');
            if($campos_contas_receberes[$i]['valor_pago'] > 0) {
                $pdf->Cell(60, 5, number_format($campos_contas_receberes[$i]['valor_pago'], 2, ',', '.'), 1, 0, 'R');
            }else {
                $pdf->Cell(60, 5, '', 1, 0, 'R');
            }
            $pdf->Cell(60, 5, number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.'), 1, 1, 'R');
            $valor_total+= $calculos_conta_receber['valor_reajustado'];
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(58, 5, 'TOTAL VENCIDO:', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 10);
        //Se for Negativa ent�o ...
        $escrever = ($total_vencidas < 0) ? ' (CR�DITO A FAVOR)' : '';
        $pdf->Cell(58, 5, $tipo_moeda.segurancas::number_format($total_vencidas, 2, '.', 1).$escrever, 1, 0, 'R');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(58, 5, 'TOTAL � VENCER:', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 5, $tipo_moeda.segurancas::number_format($total_vencer, 2, '.', 1), 1, 0, 'R');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 5, 'VALOR TOTAL:', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 5, $tipo_moeda.segurancas::number_format($valor_total, 2, '.', 1), 1, 1, 'R');
        $pdf->Ln(4);
    }
//Marcador de Impress�o
    $pdf->SetLeftMargin(1);
    $pdf->Ln();
    $pdf->SetFont('Arial', 'BI', 9);
    $pdf->Cell(100, 5, 'LOGIN: '.$login.' - IMPRESS�O: '.$data_impressao.' - '.$id_cliente, 0, 0, 'L');
    $pdf->Ln(5);
/********************************Pend�ncias******************************/
    $sql = "SELECT c.`id_uf`, pv.`id_pedido_venda`, pv.`id_cliente`, pv.`id_empresa`, pv.`finalidade`, 
            IF(pv.`id_empresa` = '4', 'S', 'N') AS nota_sgd, pv.`vencimento1`, pv.`vencimento2`, 
            pv.`vencimento3`, pv.`vencimento4`, pv.`faturar_em`, pvi.`id_pedido_venda_item`, 
            pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`qtde_faturada`, pvi.`preco_liq_final`, 
            pvi.`status` AS status_item, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao_custo`, pa.`operacao`, pa.`peso_unitario`, pa.`observacao` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            WHERE pv.`id_cliente` = '$id_cliente' 
            ORDER BY pv.`id_empresa`, pv.`id_pedido_venda` ";
    $campos_pedido_vendas = bancos::sql($sql);
    $linhas_pedido_vendas = count($campos_pedido_vendas);
    if($linhas_pedido_vendas > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'PEND�NCIA(S)', 1, 1, 'C');
        $pdf->Cell(17, 5, 'INI', 1, 0, 'C');
        $pdf->Cell(17, 5, 'FAT', 1, 0, 'C');
        $pdf->Cell(12, 5, 'SEP', 1, 0, 'C');
        $pdf->Cell(12, 5, 'PEND', 1, 0, 'C');
        $pdf->Cell(12, 5, 'VALE', 1, 0, 'C');
        $pdf->Cell(10, 5, 'E.D.', 1, 0, 'C');
        $pdf->Cell(80, 5, 'REF * DISCRIMINA��O', 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(22, 5, 'P. L. FINAL '.$tipo_moeda, 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(12, 5, 'IPI %', 1, 0, 'C');
        $pdf->Cell(20, 5, 'TOTAL '.$tipo_moeda, 1, 0, 'C');
        $pdf->Cell(35, 5, 'EMP/ TP/ PZO PGTO', 1, 0, 'C');
        $pdf->Cell(25, 5, 'FATURAR EM', 1, 0, 'C');
        $pdf->Cell(20, 5, 'N.� PED', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
//Listagem de Pend�ncia
        for($i = 0; $i < $linhas_pedido_vendas; $i++) {
            $pdf->Cell(17, 5, number_format($campos_pedido_vendas[$i]['qtde'], 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell(17, 5, segurancas::number_format($campos_pedido_vendas[$i]['qtde_faturada'], 2, '.', 1), 1, 0, 'C');
            
            $separado = $campos_pedido_vendas[$i]['qtde'] - $campos_pedido_vendas[$i]['qtde_pendente'] - $campos_pedido_vendas[$i]['vale'] - $campos_pedido_vendas[$i]['qtde_faturada'];
            
            $pdf->Cell(12, 5, segurancas::number_format($separado, 0, '.', 1), 1, 0, 'C');
            $pdf->Cell(12, 5, segurancas::number_format($campos_pedido_vendas[$i]['qtde_pendente'], 0, '.', 1), 1, 0, 'C');
            $pdf->Cell(12, 5, segurancas::number_format($campos[$i]['vale'], 0, '.', 1), 1, 0, 'C');
            $pdf->Cell(10, 5, segurancas::number_format($vetor[3], 0, '.', 1), 1, 0, 'C');
            if(strlen($campos_pedido_vendas[$i]['referencia'].' * '.$campos_pedido_vendas[$i]['discriminacao']) > 38) {
                $pdf->Cell(80, 5, substr($campos_pedido_vendas[$i]['referencia'].' * '.$campos_pedido_vendas[$i]['discriminacao'], 0, 38).' ...', 1, 0, 'L');
            }else {
                $pdf->Cell(80, 5, $campos_pedido_vendas[$i]['referencia'].' * '.$campos_pedido_vendas[$i]['discriminacao'], 1, 0, 'L');
            }
            $preco_liq_final = $campos_pedido_vendas[$i]['preco_liq_final'];
            $pdf->Cell(22, 5, segurancas::number_format($preco_liq_final, 2, '.', 1), 1, 0, 'R');
            
            //Essas variaveis serao utilizadas mais abaixo ...
            $dados_produto          = intermodular::dados_impostos_pa($campos_pedido_vendas[$i]['id_produto_acabado'], $id_uf_cliente, $id_cliente, $campos_pedido_vendas[$i]['id_empresa'], $campos_pedido_vendas[$i]['finalidade']);
            $ipi                    = $dados_produto['ipi'];
            
            $pdf->Cell(12, 5, $ipi, 1, 0, 'C');
            $preco_total_lote = $preco_liq_final * ($campos_pedido_vendas[$i]['qtde'] - $campos_pedido_vendas[$i]['qtde_faturada']);
            $total_geral+= $preco_total_lote;
            $pdf->Cell(20, 5, segurancas::number_format($preco_total_lote, 2, '.', 1), 1, 0, 'R');

            if($campos_pedido_vendas[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_pedido_vendas[$i]['vencimento4'];
            if($campos_pedido_vendas[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos_pedido_vendas[$i]['vencimento3'].$prazo_faturamento;
            if($campos_pedido_vendas[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos_pedido_vendas[$i]['vencimento1'].'/'.$campos_pedido_vendas[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_pedido_vendas[$i]['vencimento1'] == 0) ? '� vista' : $campos_pedido_vendas[$i]['vencimento1'];
            }
            if($campos_pedido_vendas[$i]['id_empresa'] == 1) {
                $nomefantasia = 'ALBA - NF';
                $total_empresa+= $preco_total_lote;
                $pdf->Cell(35, 5, '(A - NF) / '.$prazo_faturamento, 1, 0, 'C');
            }else if($campos_pedido_vendas[$i]['id_empresa'] == 2) {
                $nomefantasia = 'TOOL - NF';
                $total_empresa+= $preco_total_lote;
                $pdf->Cell(35, 5, '(T - NF) / '.$prazo_faturamento, 1, 0, 'C');
            }else if($campos_pedido_vendas[$i]['id_empresa'] == 4) {
                $nomefantasia = 'GRUPO - SGD';
                $total_empresa+= $preco_total_lote;
                $pdf->Cell(35, 5, '(G - SGD) / '.$prazo_faturamento, 1, 0, 'C');
            }else {
                $pdf->Cell(35, 5, 'Erro', 1, 0, 'C');
            }
//Aki eu limpo essa vari�vel para n�o dar problema quando voltar no pr�ximo loop
            $prazo_faturamento = '';

            $pdf->Cell(25, 5, data::datetodata($campos_pedido_vendas[$i]['faturar_em'], '/'), 1, 0, 'C');
            $pdf->Cell(20, 5, $campos_pedido_vendas[$i]['id_pedido_venda'], 1, 1, 'C');
        }
//Apresenta��o do Total de Pend�ncias ...
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(194, 5, 'TOTAL PEND�NCIA(S):', 1, 0, 'R');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(20, 5, 'R$ '.segurancas::number_format($total_geral, 2, '.', 1), 1, 0, 'R');
        $pdf->Cell(80, 5, '', 1, 1, 'R');
/******************************Cinco Linhas****************************/
        for($i = 0; $i < 5; $i++) $pdf->Cell(294, 5, '', 'B', 1, 'L');
        $pdf->Ln(8);
    }
    unset($vetor_faturamento);//Aqui eu Destruo a vari�vel para n�o herdar valores do Loop Anterior ...
/****************************Volume de Compra**************************/
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos_empresas_divisoes = bancos::sql($sql);
    $linhas_empresas_divisoes = count($campos_empresas_divisoes);
	
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(296, 5, 'VOLUME DE COMPRA', 1, 1, 'C');
    $pdf->Cell(42, 5, 'ANO', 1, 0, 'C');
	
    $sql = "SELECT ged.id_empresa_divisao, YEAR(nfs.data_emissao) AS ano, SUM((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario) AS total 
            FROM `clientes` c 
            INNER JOIN `nfs` ON nfs.`id_cliente` = c.`id_cliente` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE nfs.id_cliente = '$id_cliente' GROUP BY YEAR(nfs.data_emissao), ged.id_empresa_divisao ";
    $campos_faturamentouramento = bancos::sql($sql);
    $linhas_faturamento = count($campos_faturamentouramento);
    for($i = 0; $i < $linhas_faturamento; $i++) $vetor_faturamento[$campos_faturamentouramento[$i]['ano']][$campos_faturamentouramento[$i]['id_empresa_divisao']] = $campos_faturamentouramento[$i]['total'];
    //Aqui busca todos os representantes que est�o atrelados a esse Cliente ...
    $largura_coluna_divisao = (294 - 70) / $linhas_empresas_divisoes;

    for($i = 0; $i < $linhas_empresas_divisoes; $i++) $pdf->Cell($largura_coluna_divisao, 5, $campos_empresas_divisoes[$i]['razaosocial'], 1, 0, 'C');
    $pdf->Cell(30, 5, 'TOTAL R$', 1, 1, 'C');

    for($ano = 2006; $ano <= date('Y'); $ano++) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(42, 5, $ano.' NOVO (FAT)', 1, 0, 'C');
        $total_por_ano = 0;
        for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($largura_coluna_divisao, 5, number_format($vetor_faturamento[$ano][$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.'), 1, 0, 'C');
            $total_por_ano+= $vetor_faturamento[$ano][$campos_empresas_divisoes[$i]['id_empresa_divisao']];
        }
        $pdf->Cell(30, 5, number_format($total_por_ano, 2, ',', '.'), 1, 1, 'R');
    }
    $pdf->Ln(4);
/********************************Representantes******************************/
//Aqui busca todos os representantes que est�o atrelados a esse Cliente
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ORDER BY razaosocial ";
    $campos_ed = bancos::sql($sql);
    $linhas_ed = count($campos_ed);
    if($linhas_ed > 0) {
/*Aqui eu n�o preciso calcular nada com rela��o a largura de coluna, porque vai seguir da mesma l�gica
anterior*/
//Essa primeira linha e primeira coluna � fixa
        $pdf->Cell(42, 5, '', 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 10);
//Printagem das outras colunas de Divis�o
        for($i = 0; $i < $linhas_ed; $i++) $pdf->Cell($largura_coluna_divisao, 5, $campos_ed[$i]['razaosocial'], 1, 0, 'C');
//Essa coluna aqui � p/ as divis�es alinhadas com as Divis�es de Cima
        $pdf->Cell(42, 5, '', 1, 1, 'C');
//Essa segunda linha e primeira coluna � fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(42, 5, 'REPRESENTANTE(S)', 1, 0, 'L');
//Printagem das outras colunas de Divis�o
        for($i = 0; $i < $linhas_ed; $i++) {
            $pdf->SetFont('Arial', '', 10);
//Verifica se a empresa divis�o atual do loop est� atrelada ao cliente
            $sql = "SELECT r.nome_fantasia 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '$id_cliente' 
                    AND cr.`id_empresa_divisao` = '".$campos_ed[$i]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            $pdf->Cell($largura_coluna_divisao, 5, $campos_representante[0]['nome_fantasia'], 1, 0, 'C');
        }
//Essa coluna aqui � p/ as divis�es alinhadas com as Divis�es de Cima
        $pdf->Cell(42, 5, '', 1, 1, 'L');
//Essa terceira linha e primeira coluna � fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(42, 5, 'DESC.(S) ATUAL', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_ed; $i++) {
//Verifica se a empresa divis�o atual do loop est� atrelada ao cliente
            $sql = "SELECT cr.desconto_cliente 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '$id_cliente' 
                    AND cr.`id_empresa_divisao` = '".$campos_ed[$i]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_desconto = bancos::sql($sql);
            $pdf->Cell($largura_coluna_divisao, 5, number_format($campos_desconto[0]['desconto_cliente'], 2, ',', '.').' %', 1, 0, 'C');
        }
//Essa coluna aqui � p/ as divis�es alinhadas com as Divis�es de Cima
        $pdf->Cell(42, 5, '', 1, 1, 'L');
//Essa quarta linha e primeira coluna � fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(42, 5, 'DESC.(S) ANTERIOR', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_ed; $i++) {
//Verifica se a empresa divis�o atual do loop est� atrelada ao cliente
            $sql = "SELECT cr.desconto_cliente_old 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '$id_cliente' 
                    AND cr.`id_empresa_divisao` = '".$campos_ed[$i]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_desconto_antigo = bancos::sql($sql);
            $pdf->Cell($largura_coluna_divisao, 5, number_format($campos_desconto_antigo[0]['desconto_cliente_old'], 2, ',', '.').' %', 1, 0, 'C');
        }
//Essa coluna aqui � p/ as divis�es alinhadas com as Divis�es de Cima
        $pdf->Cell(42, 5, '', 1, 1, 'L');
    }
    $pdf->Ln(2);
/********************************An�lises*******************************/
/********************************An�lise 1******************************/
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '0' 
            ORDER BY valor_semestral ";
    $campos_desconto_cliente = bancos::sql($sql);
    $linhas_desconto_cliente = count($campos_desconto_cliente);
    if($linhas_desconto_cliente > 0) {
        $largura_linha = 294;
        $largura_coluna = $largura_linha / ($linhas_desconto_cliente + 1);//Somo + 1, por causa da coluna fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'AN�LISE 1 - (VOLUME VENDAS DO GRUPO)', 1, 1, 'C');
//Essa segunda linha e primeira coluna � fixa
        $pdf->Cell($largura_coluna, 5, 'PORCENTAGEM(NS)', 1, 0, 'L');
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_desconto_cliente; $i++) {
            $pdf->SetFont('Arial', '', 10);
            if(($i + 1) == $linhas_desconto_cliente) {//Se j� for o �ltimo registro do loop, faz quebra de linha
                $pdf->Cell($largura_coluna, 5, number_format($campos_desconto_cliente[$i]['desconto_cliente'], 2, ',', '.').' %', 1, 1, 'R');
            }else {//Ainda n�o est� no �ltimo registro
                $pdf->Cell($largura_coluna, 5, number_format($campos_desconto_cliente[$i]['desconto_cliente'], 2, ',', '.').' %', 1, 0, 'R');
            }
        }
//Essa terceira linha e primeira coluna � fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($largura_coluna, 5, 'VALOR(ES)', 1, 0, 'L');
//Printagem das outras colunas de Divis�o
        for($i = 0; $i < $linhas_desconto_cliente; $i++) {
            $pdf->SetFont('Arial', '', 10);
            if(($i + 1) == $linhas_desconto_cliente) {//Se j� for o �ltimo registro do loop, faz quebra de linha
                $pdf->Cell($largura_coluna, 5, '< '.number_format($campos_desconto_cliente[$i]['valor_semestral'], 2, ',', '.'), 1, 1, 'R');
            }else {//Ainda n�o est� no �ltimo registro
                $pdf->Cell($largura_coluna, 5, '< '.number_format($campos_desconto_cliente[$i]['valor_semestral'], 2, ',', '.'), 1, 0, 'R');
            }
        }
        $pdf->SetFont('Arial', '', 10);
    }
    $pdf->Ln(2);
/********************************An�lise 2******************************/
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '1' 
            ORDER BY valor_semestral ";
    $campos_desconto_cliente = bancos::sql($sql);
    $linhas_desconto_cliente = count($campos_desconto_cliente);
    if($linhas_desconto_cliente > 0) {
        $largura_linha = 294;
        $largura_coluna = $largura_linha / ($linhas_desconto_cliente + 1);//Somo + 1, por causa da coluna fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'AN�LISE 2 - (VOLUME VENDAS POR LINHA)', 1, 1, 'C');
//Essa segunda linha e primeira coluna � fixa
        $pdf->Cell($largura_coluna, 5, 'PORCENTAGEM(NS)', 1, 0, 'L');
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_desconto_cliente; $i++) {
            $pdf->SetFont('Arial', '', 10);
            if(($i + 1) == $linhas_desconto_cliente) {//Se j� for o �ltimo registro do loop, faz quebra de linha
                $pdf->Cell($largura_coluna, 5, number_format($campos_desconto_cliente[$i]['desconto_cliente'], 2, ',', '.').' %', 1, 1, 'R');
            }else {//Ainda n�o est� no �ltimo registro
                $pdf->Cell($largura_coluna, 5, number_format($campos_desconto_cliente[$i]['desconto_cliente'], 2, ',', '.').' %', 1, 0, 'R');
            }
        }
//Essa terceira linha e primeira coluna � fixa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($largura_coluna, 5, 'VALOR(ES)', 1, 0, 'L');
//Printagem das outras colunas de Divis�o
        for($i = 0; $i < $linhas_desconto_cliente; $i++) {
            $pdf->SetFont('Arial', '', 10);
            if(($i + 1) == $linhas_desconto_cliente) {//Se j� for o �ltimo registro do loop, faz quebra de linha
                $pdf->Cell($largura_coluna, 5, '< '.number_format($campos_desconto_cliente[$i]['valor_semestral'], 2, ',', '.'), 1, 1, 'R');
            }else {//Ainda n�o est� no �ltimo registro
                $pdf->Cell($largura_coluna, 5, '< '.number_format($campos_desconto_cliente[$i]['valor_semestral'], 2, ',', '.'), 1, 0, 'R');
            }
        }
        $pdf->SetFont('Arial', '', 10);
    }
    $pdf->Ln(2);
/********************************�ltima Compra******************************/		
    $sql = "SELECT nfs.*, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, IF(c.id_pais = 31, SUM(nfsi.qtde * nfsi.valor_unitario), SUM(nfsi.qtde * nfsi.valor_unitario / nfs.valor_dolar_dia)) AS valor_ultima_compra 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.id_cliente = '$id_cliente' 
            GROUP BY nfs.id_nf ORDER BY nfs.data_emissao DESC , nfs.id_nf DESC ";
    $campos_faturamento = bancos::sql($sql);
    $linhas_faturamento = count($campos_faturamento);
    if($linhas_faturamento > 0) {
        $valor_ultima_compra    = $campos_faturamento[0]['valor_ultima_compra'];
        $data_ultima_compra     = $campos_faturamento[0]['data_emissao'];
        $numero_nf              = faturamentos::buscar_numero_nf($campos_faturamento[0]['id_nf'], 'S');
        if($campos_faturamento[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_faturamento[0]['vencimento4'];
        if($campos_faturamento[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos_faturamento[0]['vencimento3'].$prazo_faturamento;
        if($campos_faturamento[0]['vencimento2'] > 0) {
            $prazo_faturamento = $campos_faturamento[0]['vencimento1'].'/'.$campos_faturamento[0]['vencimento2'].$prazo_faturamento;
        }else {
            $prazo_faturamento = ($campos_faturamento[0]['vencimento1'] == 0) ? '� vista' : $campos_faturamento[0]['vencimento1'];
        }
//Aqui verifica o Tipo de Nota
        if($campos_faturamento[0]['id_empresa'] == 1 || $campos_faturamento[0]['id_empresa'] == 2) {
            $nota_sgd   = 'N';//var surti efeito l� embaixo
            $tipo_nota  = ' (NF)';
        }else {
            $nota_sgd   = 'S'; //var surti efeito l� embaixo
            $tipo_nota  = ' (SGD)';
        }
//Aqui � a verifica se esta Nota � de Sa�da ou Entrada
        $tipo_nfe_nfs = ($campos_faturamento[0]['tipo_nfe_nfs'] == 'S') ? ' - Sa�da' : ' - Entrada';
        $prazo_faturamento.= $tipo_nota.$tipo_nfe_nfs;
//Se o Cliente for Estrangeiro "Internacional", ent�o a Moeda � apresentada em D�lar ...
        $tipo_moeda = ($id_pais != 31) ? 'U$ ' : 'R$ ';
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, '�LTIMA COMPRA (FAT)', 1, 1, 'C');
        $pdf->Cell(56, 5, 'NF: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(65, 5, 'N.� '.$numero_nf.' - '.$prazo_faturamento, 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(64, 5, 'VALOR: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 5, $tipo_moeda.number_format($valor_ultima_compra, 2, ',', '.'), 1, 0, 'R');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(56, 5, 'DATA DE EMISS�O: ', 1, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(23, 5, $data_ultima_compra, 1, 1, 'R');
        $pdf->Ln(4);
    }
/********************************Produtos Vendidos para o Cliente******************************/
    $mes_atual = (int)date('m');//M�s atual
    $ano_atual = (int)date('Y');//Ano atual
    if($mes_atual > 6) {//Significa est� na segunda parte do Semestre
//Ent�o ele mostra dados do �ltimo Semestre Passado - Primeiro
        $mes_atual-= 6;
    }else {//Significa que est� na primeira parte do Semestre
//Ent�o ele mostra dados do �ltimo Semestre Passado - Segundo
        $mes_atual = 12 + ($mes_atual - 6);
        $ano_atual--; 
    }
//Listagem de Todos os Produtos Vendidos para o Cliente
    $sql = "SELECT SUM(nfsi.qtde * nfsi.valor_unitario) AS total, ed.id_empresa_divisao, ed.razaosocial, CONCAT(ed.razaosocial, ' - ', f.nome) AS vendas 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
            WHERE MONTH(nfs.`data_emissao`) >= '$mes_atual' 
            AND SUBSTRING(nfs.data_emissao, 1, 4) >= '$ano_atual' 
            AND nfs.id_cliente = '$id_cliente' GROUP BY f.id_familia, ed.id_empresa_divisao ORDER BY ed.id_empresa_divisao, f.nome ";
    $campos_faturamento = bancos::sql($sql);
    $linhas_faturamento = count($campos_faturamento);
    if($linhas_faturamento > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(294, 5, 'COMPRA DO(S) �LTIMO(S) 6 MES(ES)', 1, 1, 'C');
        $pdf->Cell(160, 5, 'DIVIS�O / FAM�LIA', 1, 0, 'C');
        $pdf->Cell(134, 5, 'VALOR R$ ', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
//Listagem de D�bitos do Cliente
        $id_empresa_divisao_current = $campos_faturamento[0]['id_empresa_divisao'];
        for($i = 0; $i < $linhas_faturamento; $i++) {
//Pergunta se a Empresa Divis�o atual ainda � a mesma divis�o em Rela��o a Pr�xima do Loop
            $pdf->Cell(160, 5, $campos_faturamento[$i]['vendas'], 1, 0, 'L');
            $pdf->Cell(134, 5, segurancas::number_format($campos_faturamento[$i]['total'], 2, '.', 1), 1, 1, 'R');
            $sub_total_divisao+= $campos_faturamento[$i]['total'];
            if($id_empresa_divisao_current != $campos_faturamento[$i + 1]['id_empresa_divisao']) {//Trocou a Divis�o
                $pdf->Cell(294, 5, 'TOTAL DA '.$campos_faturamento[$i]['razaosocial'].' => R$ '.number_format($sub_total_divisao, 2, ',', '.'), 1, 1, 'R');
                $sub_total_divisao = 0;
                $id_empresa_divisao_current=$campos_faturamento[$i+1]['id_empresa_divisao'];
            }
            $total_ultimos_6_meses+= $campos_faturamento[$i]['total'];
        }
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 5, 'TOTAL GERAL ', 1, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(134, 5, 'R$ '.number_format($total_ultimos_6_meses, 2, ',', '.'), 1, 1, 'R');
//Aqui eu sempre zero essa vari�vel p/ n�o continuar com o valor antigo armazenado do Loop Anterior ...
    $total_ultimos_6_meses = '';
//Aqui grava os dados de funcion�rio, cliente, ... de quem gerou esse apv na tabela de logs_apvs
    $sql = "INSERT INTO `logs_apvs` (`id_log_apv`, `id_cliente`, `id_funcionario`, `data_ocorrencia`) VALUES (NULL, '$id_cliente', '$_SESSION[id_funcionario]', '$data_ocorrencia') ";
    bancos::sql($sql);
}

chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>