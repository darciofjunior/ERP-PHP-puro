<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/vendas.php');

session_start('funcionarios');
error_reporting(1);

//Vari�veis que seram utilizadas mais abaixo ...
$aliquota_imposto_renda = 1.5;
$valor_minimo_gare      = 10;//O Valor M�nimo p/ se Emitir uma Gare � a partir de R$ 10,00 ...
$vetor_empresas         = array(1, 2, 4);
$linhas_empresas        = count($vetor_empresas);

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicial               = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final                 = data::datatodate($_POST['txt_data_final'], '-');
    $txt_projecao_faturamento   = str_replace('.', '', $_POST['txt_projecao_faturamento']);
    $txt_projecao_faturamento   = str_replace(',', '.', $txt_projecao_faturamento);
}else {
    $data_inicial               = data::datatodate($_GET['txt_data_inicial'], '-');
    $data_final                 = data::datatodate($_GET['txt_data_final'], '-');
    $txt_projecao_faturamento   = str_replace('.', '', $_GET['txt_projecao_faturamento']);
    $txt_projecao_faturamento   = str_replace(',', '.', $txt_projecao_faturamento);
}

//Vari�veis de controle para que o Sistema n�o d� Insert ou Update em per�odos maiores fora do per�odo normal do Pagamento de comiss�o ...
$dia_inicial                    = substr($data_inicial, 8, 2);
$dia_final                      = substr($data_final, 8, 2);
$vetor_data                     = data::diferenca_data($data_inicial, $data_final);
$qtde_dias                      = $vetor_data[0];

//Referente ao pr�mio que os Vendedores conquistam com a Meta ...
$mes_ref_sg     = (string)data::mes((int)date('m'));
$mes_ref_sg     = substr($mes_ref_sg, 0, 3).date('/Y');
$mes_ref 	= date('m');
$ano_ref 	= date('Y');

//Macete por causa do Loop de Todas as Comiss�es ...
if(empty($cmb_representante)) {
    $condicao_representante = " AND `id_representante` LIKE '%' ";
}else {
    if(strpos($cmb_representante, ',') !== false) {//Verifico se existe ',' ...
        $condicao_representante = " AND `id_representante` IN ($cmb_representante) ";
    }else {
        $condicao_representante = " AND `id_representante` = '$cmb_representante' ";
    }
}

$sql = "SELECT `id_representante`, `comissao_meta_atingida`, `comissao_meta_atingida_sup` 
        FROM `comissoes_extras` 
        WHERE MONTH(`data_periodo_fat`) = '$mes_ref' 
        AND YEAR(`data_periodo_fat`) = '$ano_ref' 
        $condicao_representante ";
$campos_perc_extra = bancos::sql($sql);
$linhas_perc_extra = count($campos_perc_extra);
if($linhas_perc_extra == 0) {
    $comissao_meta_atingida_perc[$cmb_representante]        = 0;
    $comissao_meta_atingida_sup_perc[$cmb_representante]    = 0;
}else {
    for($i = 0; $i < $linhas_perc_extra; $i++) {
        $comissao_meta_atingida_perc[$campos_perc_extra[$i]['id_representante']]        = $campos_perc_extra[$i]['comissao_meta_atingida'];
        $comissao_meta_atingida_sup_perc[$campos_perc_extra[$i]['id_representante']]	= $campos_perc_extra[$i]['comissao_meta_atingida_sup'];
    }
}

/*Busca a pr�xima Data do Holerith, maior do que a Data Final digitada pelo usu�rio 
no Filtro e que tenha o campo 'qtde_dias_uteis_mes' preenchida ...*/
$sql = "SELECT `id_vale_data`, `data`, `qtde_dias_uteis_mes`, `qtde_dias_inuteis_mes`, `total_faturamento` 
        FROM `vales_datas` 
        WHERE `data` > '$data_final' 
        AND `qtde_dias_uteis_mes` > '0' LIMIT 1 ";
$campos_data = bancos::sql($sql);
if(count($campos_data) == 1) {//Se encontrar na Base de Dados ...
    $id_vale_data           = $campos_data[0]['id_vale_data'];
    $data_holerith          = $campos_data[0]['data'];
    $qtde_dias_uteis_mes    = $campos_data[0]['qtde_dias_uteis_mes'];
    $qtde_dias_inuteis_mes  = $campos_data[0]['qtde_dias_inuteis_mes'];
    $total_faturamento      = $campos_data[0]['total_faturamento'];
}else {//Se n�o encontrar, ent�o ...
    $qtde_dias_uteis_mes    = 0;
    $qtde_dias_inuteis_mes  = 0;
    $total_faturamento      = 0;
}

//Essas vari�veis v�o servir p/ o controle de Impress�o do Relat�rio de Comiss�o em PDF ...
if($qtde_dias_uteis_mes == 0 && $qtde_dias_inuteis_mes == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('O "CAMPO DIAS �TEIS" E O "CAMPO DOMINGOS E FERIADOS" S�O = 0 !\n\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
        window.close()
    </Script>
<?
    exit;
}else if($qtde_dias_uteis_mes > 0 && $qtde_dias_inuteis_mes == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('O "CAMPO DIAS �TEIS" = 0 !\n\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
        window.close()
    </Script>
<?
    exit;
}else if($qtde_dias_uteis_mes == 0 && $qtde_dias_inuteis_mes > 0) {
?>
    <Script Language = 'JavaScript'>
        alert('O "CAMPO DOMINGOS E FERIADOS" = 0 !\n\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
        window.close()
    </Script>
<?
    exit;
}else if($total_faturamento == 0 && $txt_projecao_faturamento == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('O "CAMPO TOTAL DE FATURAMENTO" = 0 !\n\nENTRE EM CONTATO COM O ROBERTO !')
        window.close()
    </Script>
<?
    exit;
}

/*Funcion�rios que podem rodar "simular" o Relat�rio de Comiss�o mesmo sem o Faturamento ter sido 
fechado por Completo, n�o tendo fechado o Per�odo do m�s - 62 Roberto, 98 Darcio, 136 Nishimura ...*/
$id_funcionarios_com_permissao   = array(62, 98, 136);

if(!in_array($_SESSION['id_funcionario'], $id_funcionarios_com_permissao)) {
    /*Verifico se j� foi preenchido pelo Depto. Pessoal a Pontua��o dos Vendedores Internos na parte de 
    "Pr�mio(s) vs Representante(s)" na respectiva Data de Holerith do filtro feito pelo Usu�rio ...*/
    $sql = "SELECT `id_funcionario_vs_holerith` 
            FROM `funcionarios_vs_holeriths` 
            WHERE `id_vale_data` = '$id_vale_data' 
            AND `premio_produtividade_trabalho` > '0' LIMIT 1 ";
    $campos_funcionario_holerith = bancos::sql($sql);
/*A partir dessa Data de 26/10/2015 que foi criada essa log�stica de Pontua��o p/ os vendedores por isso 
que fixei a mesma no C�digo abaixo ...*/
    if(count($campos_funcionario_holerith) == 0 && $data_final >= '2015-10-26') {//N�o foi preenchida a Pontua��o dos Vendedores ...
?>
        <Script Language = 'JavaScript'>
            alert('N�O FOI PREENCHIDA A PONTUA��O DOS VENDEDORES INTERNOS P/ ESSA DATA DE HOLERITH !\n\nENTRE EM CONTATO COM O DEPTO. PESSOAL OU ROBERTO !')
            window.close()
        </Script>
<?
        exit;
    }
}

function rotulo($moeda) { // porq chama mais de uma vez por causa da paginacao
    global $pdf;
    $pdf->SetLeftMargin(1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($GLOBALS['ph'] * 15, 5, 'Data de Emiss�o (NF)', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 10, 5, 'N� da NF', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 37, 5, 'Cliente', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 14, 5, 'Vendas '.$moeda, 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 13, 5, 'Comis. M�dia %', 1, 0, 'C');
    $pdf->Cell($GLOBALS['ph'] * 11, 5, 'Comis. R$ '.$moeda, 1, 1, 'C');
}

function Heade($rotulo = 'COMISSAO', $data_inicial, $data_final, $data_holerith, $cmb_representante, $cmb_empresa) {
    global $pdf;
    $pdf->SetFont('Arial', 'B', 12);
    //Empresa
    if($cmb_empresa == 1) {
        $empresa = 'ALBAFER';
    }else if($cmb_empresa == 2) {
        $empresa = 'TOOL MASTER';
    }else if($cmb_empresa == 4) {
        $empresa = 'GRUPO';
    }else {
        $empresa = 'OUTROS';
    }
    
    if($rotulo == 'COMISSAO') {
        $pdf->Cell(120, 5, 'RELAT�RIO DE COMISS�ES - '.$empresa, 'LBT', 0, 'R');
        //Aqui � Padr�o para todas as Empresas
        $pdf->SetFont('Arial', 'BI', 9);
        $pdf->Cell(85, 5, ' -   Impress�o: '.date('d/m/Y').' - '.date('H:i:s'), 'RBT', 1, 'L');
    }else {
        $pdf->Cell(85, 5, 'PR�MIOS', 'LBT', 0, 'R');
        //Aqui � Padr�o para todas as Empresas
        $pdf->SetFont('Arial', 'BI', 9);
        $pdf->Cell(120, 5, ' -   Impress�o: '.date('d/m/Y').' - '.date('H:i:s'), 'RBT', 1, 'L');
    }
    
    //Continuando ...
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, 'Data Inicial: '.data::datetodata($data_inicial, '/'), 1, 0, 'C');
    $pdf->Cell(40, 5, 'Data Final: '.data::datetodata($data_final, '/'), 1, 0, 'C');
    $pdf->Cell(49, 5, 'Data de Holerith: '.data::datetodata($data_holerith, '/'), 1, 0, 'C');
    
    //Busca do Nome do Representante
    $sql = "SELECT `nome_fantasia`, `tipo_pessoa` 
            FROM `representantes` 
            WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $nome_fantasia  = $campos[0]['nome_fantasia'];
    $tipo_pessoa    = ($campos[0]['tipo_pessoa'] == 'F') ? ' (PF) ' : ' (PJ) ';
    
    //Verifico se esse Representante � um Funcion�rio ...
    $sql = "SELECT SUBSTRING(e.`nomefantasia`, 1, 1) AS inicial_empresa_registro 
            FROM `representantes_vs_funcionarios` rf 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE rf.`id_representante` = '$cmb_representante' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    //Significa que esse Representante � um Funcion�rio ...
    $empresa_registro   = (count($campos_funcionario) == 1) ? ' ('.$campos_funcionario[0]['inicial_empresa_registro'].') ' : $tipo_pessoa;
    
    /*****************************************************/
    $pdf->Cell(60, 5, 'Relat�rio por: '.$nome_fantasia.$empresa_registro, 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(16, 5, 'P�g: '.$GLOBALS['num_pagina'], 1, 1, 'C');
    $pdf->Ln(1);
    $pdf->Line(1 * $GLOBALS['ph'], 23, 101.5 * $GLOBALS['ph'], 23);
}

/////////////////////////////////////// IN�CIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel     = 'P';  // P=> Retrato L=>Paisagem
$unidade        = 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= 'A4'; // A3, A4, A5, Letter, Legal
$pdf            = new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(1);
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

$pdf->SetFont('Arial', '', 10);

//Aqui eu trago o Representante ou Representantes de acordo com o Par�metro que foi passado ...
$sql = "SELECT `id_representante` 
        FROM `representantes` 
        WHERE `ativo` = '1' 
        $condicao_representante ORDER BY `nome_fantasia` ";
$campos_representante = bancos::sql($sql);//traz todos representante
$total_representantes = count($campos_representante);
if($total_representantes > 0) {
    //Se for maior que 5 representante de uma s� vez executo o set time para ampliar o tempo de execu��o ...
    if($total_representantes > 5) ini_set('max_execution_time', '1000');
/**************Disparo de Loop dos Representantes**************/
    for($i = 0; $i < $total_representantes; $i++) {//Loop dos Representantes ...
        $cmb_representante 	= $campos_representante[$i]['id_representante'];
        /************Zero essas vari�veis para n�o herdar valores do Loop Anterior************/
        $total_vendas_diretas_todas_empresas            = 0;
        $total_devolucoes_todas_empresas                = 0;
        $total_devolucoes_reembolsos_todas_empresas     = 0;
        $total_comissoes_vendas_diretas_todas_empresas  = 0;
        $total_supervisor_todas_empresas                = 0;
        /*************************************************************************************/
//Aqui eu verifico se o Representante � um Funcion�rio ...		
        $sql = "SELECT f.id_cargo, f.id_empresa, f.id_pais, r.porc_comissao_sob_fat, r.cnpj_cpf, r.banco, r.agencia, r.conta_corrente, r.correntista 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                WHERE r.id_representante = '$cmb_representante' LIMIT 1 ";
        $campos_rep_func = bancos::sql($sql);//certifico que o rep � funcionario
        if(count($campos_rep_func) > 0) {//Se o Representante for Funcion�rio tem DSR ...
            $id_cargo_func   		= $campos_rep_func[0]['id_cargo'];//representante externo id_cargo=>27 ou id_cargo=>25 => supervisor � para tratar como vend. externo nova l�gica 109=> super interno de vendas
            $id_empresa_func 		= $campos_rep_func[0]['id_empresa'];
            $id_pais                    = $campos_rep_func[0]['id_pais'];
            $porc_comissao_sob_fat      = $campos_rep_func[0]['porc_comissao_sob_fat'];
            if(strlen($campos_rep_func[0]['cnpj_cpf']) == 14) {//Significa que � CNPJ ...
                $rotulo_cnpj_cpf        = 'CNPJ: ';
                $cnpj_cpf               = substr($campos_rep_func[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_rep_func[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_rep_func[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_rep_func[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_rep_func[0]['cnpj_cpf'], 12, 2);
            }else {
                $rotulo_cnpj_cpf        = 'CPF: ';
                $cnpj_cpf               = substr($campos_rep_func[0]['cnpj_cpf'], 0, 3).'.'.substr($campos_rep_func[0]['cnpj_cpf'], 3, 3).'.'.substr($campos_rep_func[0]['cnpj_cpf'], 6, 3).'-'.substr($campos_rep_func[0]['cnpj_cpf'], 9, 2);
            }
            $banco                      = $campos_rep_func[0]['banco'];
            $agencia                    = $campos_rep_func[0]['agencia'];
            $conta_corrente             = $campos_rep_func[0]['conta_corrente'];
            $correntista                = $campos_rep_func[0]['correntista'];
        }else {//Se o Representante n�o for Funcion�rio tem DSR e busca dados diretamente da Tabela de Representantes ...
            $sql = "SELECT id_pais, porc_comissao_sob_fat, cnpj_cpf, banco, agencia, conta_corrente, correntista 
                    FROM `representantes` 
                    WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
            $campos                 = bancos::sql($sql);
            $id_pais                = $campos[0]['id_pais'];
            $porc_comissao_sob_fat  = $campos[0]['porc_comissao_sob_fat'];
            if(strlen($campos[0]['cnpj_cpf']) == 14) {//Significa que � CNPJ ...
                $rotulo_cnpj_cpf    = 'CNPJ: ';
                $cnpj_cpf           = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
            }else {
                $rotulo_cnpj_cpf    = 'CPF: ';
                $cnpj_cpf           = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
            }
            $banco                  = $campos[0]['banco'];
            $agencia                = $campos[0]['agencia'];
            $conta_corrente         = $campos[0]['conta_corrente'];
            $correntista            = $campos[0]['correntista'];
            $id_cargo_func          = 0;
            $id_empresa_func        = 0;
        }
        /**************************Exce��o**************************/
        if($cmb_representante == 14) {//Caso for a Mercedes, essa ser� a �nica que ter� 1,5% em cima das Supervis�es ...
            $comissao_supervisao = 1.5;
            /***********************************************************/
        }else {//Os demais ter�o 1% como sempre foi ...
            $comissao_supervisao = 1;
        }
        //Se o Representante for do Brasil ent�o ...
        $campo_valor 	= ($id_pais == 31) ? ' nfsi.valor_unitario ' : ' nfsi.valor_unitario_exp ';
        $moeda          = ($id_pais == 31) ? 'R$ ' : 'U$ ';
        for($j = 0; $j < $linhas_empresas; $j++) {//Caso seja mais de uma empresa criar� um for para disparar ...
            $id_empresa_loop = $vetor_empresas[$j];
/*Trago todas as Notas Fiscais do Representante selecionado, da Empresa do Loop, 
nas respectivas Datas Digitadas 

Somente Notas Fiscais com status -> Faturada, Empacotada, Despachada e Devolu��o ...*/
            $sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`data_emissao`, nfs.`suframa`, nfs.`status`, nfs.`snf_devolvida`, 
                    IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, c.`id_pais`, 
                    IF(nfs.`status` = '6', (SUM(ROUND((nfsi.`qtde_devolvida` * $campo_valor), 2)) * (-1)), SUM(ROUND((nfsi.`qtde` * $campo_valor), 2))) AS tot_mercadoria, 
                    IF(nfs.`status` = '6', (SUM(ROUND((((nfsi.`qtde_devolvida` * $campo_valor) * nfsi.`comissao_new`) / 100), 2)) * (-1)), SUM(ROUND((((nfsi.`qtde` * $campo_valor) * (nfsi.`comissao_new` + nfsi.`comissao_extra`)) / 100), 2))) AS valor_comissao 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`status` IN (2, 3, 4, 6) 
                    AND nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                    AND nfsi.`id_representante` = '$cmb_representante' 
                    AND nfs.`id_empresa` = '$id_empresa_loop' 
                    GROUP BY nfsi.`id_nf` ORDER BY nfs.`data_emissao` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            /***************************************************************************************/
            /*********************************Estorno de Comiss�es**********************************/
            /***************************************************************************************/
            /*Estorno de Comiss�es, n�o fa�o JOIN com as Contas �s Receberes "Duplicatas", pq essa nova log�stica 
            foi criada recentemente ...*/
            $sql = "SELECT ce.`id_conta_receber`, ce.`num_nf_devolvida`, DATE_FORMAT(ce.`data_lancamento`, '%d/%m/%Y') AS data_lancamento, 
                    ce.`tipo_lancamento`, ce.`porc_devolucao`, ce.`valor_duplicata`, 
                    if(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, nfs.`id_nf`, nfs.`id_empresa` 
                    FROM `comissoes_estornos` ce 
                    INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE SUBSTRING(ce.`data_lancamento`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' AND ce.`id_representante` = '$cmb_representante' 
                    AND nfs.`id_empresa` = '$id_empresa_loop' ORDER BY ce.`data_lancamento` ";
            $campos_devolucao	= bancos::sql($sql);
            $linhas_devolucao 	= count($campos_devolucao);

            /*Por mais que na Empresa Grupo "PF" n�o tenha tido nenhum Tipo de Venda, � muito comum se 
            dar alguns benef�cios / pr�mios para alguns determinados vendedores sobre Faturamento ou se 
            este talvez seja um Supervisor ...*/

            //Se tem Venda pela Empresa do Loop ou tem Devolu��o pela Empresa do Loop ou n�o tem mais � Grupo com Comiss�o ou Grupo e � Supervisor ...
            if($linhas > 0 || $linhas_devolucao > 0 || ($id_empresa_loop == 4 && $porc_comissao_sob_fat > 0) || ($id_empresa_loop == 4 && $id_cargo_func == 25 || $id_cargo_func == 109)) {
                $pdf->AddPage();
                /*****************Procedimento Normal para calcular as Comiss�es*****************/
                Heade('COMISSAO', $data_inicial, $data_final, $data_holerith, $cmb_representante, $id_empresa_loop);
                rotulo($moeda);
                /************Zero essas vari�veis para n�o herdar valores do Loop Anterior************/
                $total_vendas_diretas_por_empresa           = 0;
                $total_devolucoes_por_empresa               = 0;
                $total_devolucoes_reembolsos_por_empresa    = 0;
                $total_comissoes_vendas_diretas_por_empresa = 0;
                $total_supervisor_por_empresa               = 0;
                $ir_por_empresa                             = 0;
                $total_sobre_total_faturamento_por_empresa  = 0;
                $desconto_devolucao_superviso_por_empresa   = 0;
                /*************************************************************************************/
                for($k = 0; $k < $linhas; $k++) {//Disparo dos Itens ...
                    $pdf->SetFont('Arial', '', 10);
                    //Data de Emiss�o
                    $pdf->Cell($GLOBALS['ph'] * 15, 5, data::datetodata($campos[$k]['data_emissao'], '/'), 1, 0, 'C');
                    //N� DA NF
                    $pdf->Cell($GLOBALS['ph'] * 10, 5, faturamentos::buscar_numero_nf($campos[$k]['id_nf'], 'S'), 1, 0, 'C');
                    //Se a NF for do Tipo Devolu��o
                    $status = ($campos[$k]['status'] == 6) ? ' (DEVOLU��O)' : '';
                    //Cliente
                    $pdf->Cell($GLOBALS['ph'] * 37, 5, $campos[$k]['cliente'].$status, 1, 0, 'L');
                    //Aqui verifica o Tipo de Nota
                    if($campos[$k]['id_empresa'] == 1 || $campos[$k]['id_empresa'] == 2) {
                        $nota_sgd = 'N';//var surti efeito l� embaixo
                    }else {
                        $nota_sgd = 'S'; //var surti efeito l� embaixo
                    }
                    //Valor da Mercadoria na Moeda da NF ...
                    $total_vendas_diretas_por_empresa+= $campos[$k]['tot_mercadoria'];
                    $total_vendas_diretas_todas_empresas+= $campos[$k]['tot_mercadoria'];
                    
                    $pdf->Cell($GLOBALS['ph'] * 14, 5, number_format($campos[$k]['tot_mercadoria'], 2, ',', '.'), 1, 0, 'R');

                    //Comiss�o M�dia %
                    $comissao_media = ($campos[$k]['tot_mercadoria'] == 0) ? 0 : ($campos[$k]['valor_comissao'] / $campos[$k]['tot_mercadoria']) * 100;
                    $pdf->Cell($GLOBALS['ph'] * 13, 5, number_format($comissao_media, 2, ',', '.'), 1, 0, 'R');

                    //Comiss�o na Moeda da NF ...
                    $pdf->Cell($GLOBALS['ph'] * 11, 5, number_format($campos[$k]['valor_comissao'], 2, ',', '.'), 1, 1, 'R');

                    //Aqui eu atualizo o campo de Comiss�o da NF de Sa�da p/ pago ...
                    $sql = "UPDATE `nfs` SET `status_comissao_pg` = 'S' WHERE `id_nf` = '".$campos[$k]['id_nf']."' LIMIT 1 ";
                    bancos::sql($sql);
                    
                    $total_comissoes_vendas_diretas_por_empresa+= round($campos[$k]['valor_comissao'], 2);
                    $total_comissoes_vendas_diretas_todas_empresas+= round($campos[$k]['valor_comissao'], 2);
                }//Fim do Loop dos Itens ...
                $total_geral_premio+= $total_comissoes_vendas_diretas_por_empresa;

                $pdf->Cell(161.9, 5, 'Vendas '.$moeda.number_format($total_vendas_diretas_por_empresa, 2, ',', '.'), 1, 0, 'R');
                $pdf->Cell(43.1, 5, 'Sub-Total '.$moeda.number_format($total_comissoes_vendas_diretas_por_empresa, 2, ',', '.'), 1, 1, 'R');
                
                if($linhas_devolucao > 0) {//Se encontrar uma NF de Devolu��o ...
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(205, 5, 'DEVOLU��ES', 1, 1, 'C');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, 'Data de Lan�', 1, 0, 'C');
                    $pdf->Cell(30, 5, 'Tipo de Lan�', 1, 0, 'C');
                    $pdf->Cell(15, 5, 'SNF', 1, 0, 'C');
                    $pdf->Cell(22, 5, 'NNF / Dupl', 1, 0, 'C');
                    $pdf->Cell(52, 5, 'Cliente', 1, 0, 'C');
                    $pdf->Cell(21, 5, 'Valor Dupl '.$moeda, 1, 0, 'C');
                    $pdf->Cell(25, 5, 'Comis. M�dia %', 1, 0, 'C');
                    $pdf->Cell(20, 5, 'Comis. '.$moeda, 1, 1, 'C');
                    for($k = 0; $k < $linhas_devolucao; $k++) {//Disparo dos Itens de Devolu��o ...
                        $pdf->SetFont('Arial', '', 7);
                        $pdf->Cell(20, 5, $campos_devolucao[$k]['data_lancamento'], 1, 0, 'C');

                        $pdf->SetFont('Arial', '', 8);
                        if($campos_devolucao[$k]['tipo_lancamento'] == 0) {
                            $tipo_lancamento = 'DEV. CANCELAMENTO';
                        }else if($campos_devolucao[$k]['tipo_lancamento'] == 1) {
                            $tipo_lancamento = 'ATRASO DE PGTO.';
                        }else if($campos_devolucao[$k]['tipo_lancamento'] == 2) {
                            $tipo_lancamento = 'ABAT./DIF. PRE�OS';
                        }else if($campos_devolucao[$k]['tipo_lancamento'] == 3) {
                            $tipo_lancamento = 'REEMBOLSO';
                        }
                        $pdf->Cell(30, 5, $tipo_lancamento, 1, 0, 'L');
                        $pdf->Cell(15, 5, $campos_devolucao[$k]['num_nf_devolvida'], 1, 0, 'C');

                        if($campos_devolucao[$k]['id_conta_receber'] > 0) {
                            //Busca o N� da Duplicata ...
                            $sql = "SELECT `num_conta` 
                                    FROM `contas_receberes` 
                                    WHERE `id_conta_receber` = '".$campos_devolucao[$k]['id_conta_receber']."' LIMIT 1 ";
                            $campos_contas_receber = bancos::sql($sql);
                            $duplicata = ' / '.$campos_contas_receber[0]['num_conta'];
                        }

                        $pdf->Cell(22, 5, faturamentos::buscar_numero_nf($campos_devolucao[$k]['id_nf'], 'D').$duplicata, 1, 0, 'C');
                        $pdf->Cell(52, 5, $campos_devolucao[$k]['cliente'], 1, 0, 'L');
                        $pdf->Cell(21, 5, number_format($campos_devolucao[$k]['valor_duplicata'], 2, ',', '.'), 1, 0, 'R');

                        $total_devolucoes_por_empresa+= $campos_devolucao[$k]['valor_duplicata'];
                        $total_devolucoes_todas_empresas+= $campos_devolucao[$k]['valor_duplicata'];
                        
                        $pdf->Cell(25, 5, number_format($campos_devolucao[$k]['porc_devolucao'], 1, ',', '.'), 1, 0, 'R');

                        $comissao = ($campos_devolucao[$k]['valor_duplicata'] * $campos_devolucao[$k]['porc_devolucao']) / 100;

                        if($campos_devolucao[$k]['tipo_lancamento'] == 3) {//REEMBOLSO
                            $total_devolucoes_reembolsos_por_empresa+=   $comissao;
                            $total_devolucoes_reembolsos_todas_empresas+= $comissao;
                            $pdf->Cell(20, 5, number_format($comissao, 2, ',', '.'), 1, 1, 'R');
                        }else {//DEVOLU��O, ATRASO DE PAGAMENTO, ABATIMENTO / DIF. PRE�OS
                            $total_devolucoes_reembolsos_por_empresa-=      $comissao;
                            $total_devolucoes_reembolsos_todas_empresas-=   $comissao;
                            $pdf->Cell(20, 5, number_format($comissao * (-1), 2, ',', '.'), 1, 1, 'R');
                        }
                    }//Fim do Loop dos Itens de Devolu��o ...
                }//Fim da Parte de Devolu��o ...
                /***************************************************************************************/
                if($total_devolucoes_reembolsos_por_empresa != 0) {
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(161.9, 5, 'Devolu��es '.$moeda.number_format($total_devolucoes_por_empresa, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell(43.1, 5, 'Sub-Total '.$moeda.number_format($total_devolucoes_reembolsos_por_empresa, 2, ',', '.'), 1, 1, 'R');
                }
                /***************************************************************************************/
                /**************************************Supervis�o***************************************/
                /***************************************************************************************/
                if(($id_cargo_func == 25 || $id_cargo_func == 109) && $id_empresa_loop == 4) {//Supervisor apenas na Empresa "Grupo" ...
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(205, 5, 'SUPERVIS�O', 1, 1, 'C');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(75, 5, 'REPRESENTANTE', 1, 0, 'C');
                    $pdf->Cell(40, 5, 'EMPRESA', 1, 0, 'C');
                    $pdf->Cell(40, 5, 'VENDAS '.$moeda, 1, 0, 'C');
                    $pdf->Cell(50, 5, 'COMISS�O SUP. 1% EM '.$moeda, 1, 1, 'C');
                    //Busca de todos os Representantes Subordinados ao Supervisor ...
                    $sql = "SELECT rs.id_representante, 
                            IF(r.nome_fantasia = '', r.nome_representante, r.nome_fantasia) AS representante 
                            FROM `representantes` r 
                            INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante = r.id_representante 
                            WHERE rs.`id_representante_supervisor` = '$cmb_representante' ORDER BY representante ";
                    $campos_sub = bancos::sql($sql);
                    $linhas_sub = count($campos_sub);
                    for($r = 0; $r < $linhas_sub; $r++) {
                        //Busca das Vendas dos Subordinados ao Supervisor ...
                        $sql = "SELECT nfs.`id_nf`, nfs.`data_emissao`, nfs.`id_empresa`, 
                                (SUM(ROUND(((nfsi.`qtde` * $campo_valor)), 2)) - SUM(ROUND((nfsi.`qtde_devolvida` * $campo_valor),2))) AS valor_nota 
                                FROM `nfs_itens` nfsi 
                                INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                                WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' AND nfsi.`id_representante` = '".$campos_sub[$r]['id_representante']."' 
                                GROUP BY nfsi.`id_representante`, nfs.`id_empresa` ";
                        $campos_vendas_subordinados_supervisor = bancos::sql($sql);
                        $linhas_vendas_subordinados_supervisor = count($campos_vendas_subordinados_supervisor);
                        for($k = 0; $k < $linhas_vendas_subordinados_supervisor; $k++) {//Disparo do Loop dos Itens dos Subordinados ...
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->Cell(75, 5, $campos_sub[$r]['representante'], 1, 0, 'L');
                            if($campos_vendas_subordinados_supervisor[$k]['id_empresa'] == 1) {
                                $empresa = 'ALBAFER';
                            }else if($campos_vendas_subordinados_supervisor[$k]['id_empresa'] == 2) {
                                $empresa = 'TOOL MASTER';
                            }else if($campos_vendas_subordinados_supervisor[$k]['id_empresa'] == 4) {
                                $empresa = 'GRUPO';
                            }else {
                                $empresa = 'OUTROS';
                            }
                            $pdf->Cell(40, 5, $empresa, 1, 0, 'C');
                            $pdf->Cell(40, 5, number_format($campos_vendas_subordinados_supervisor[$k]['valor_nota'], 2, ',', '.'), 1, 0, 'R');
                            $pdf->Cell(50, 5, number_format($campos_vendas_subordinados_supervisor[$k]['valor_nota'] * $comissao_supervisao / 100, 2, ',', '.'), 1, 1, 'R');

                            $total_supervisor_por_empresa+= $campos_vendas_subordinados_supervisor[$k]['valor_nota'];
                            $total_supervisor_todas_empresas+= $campos_vendas_subordinados_supervisor[$k]['valor_nota'];
                        }//Fim do Loop dos Itens dos Subordinados ...
                        /***************************************************************************************/
                        /**********************************Estorno de Comiss�es*********************************/
                        /***************************************************************************************/
                        $sql = "SELECT IF(ce.tipo_lancamento = '3', ce.valor_duplicata, ce.valor_duplicata*(-1)) AS valor_descontar, nfs.id_nf 
                                FROM `comissoes_estornos` ce 
                                INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf 
                                WHERE SUBSTRING(ce.data_lancamento, 1, 10) BETWEEN '$data_inicial' AND '$data_final' AND ce.`id_representante` = '".$campos_sub[$r]['id_representante']."' 
                                ORDER BY ce.data_lancamento ";
                        $campos_dev_super = bancos::sql($sql);
                        $linhas_dev_super = count($campos_dev_super);
                        for($k = 0; $k < $linhas_dev_super; $k++) $desconto_devolucao_superviso_por_empresa+= $campos_dev_super[$k]['valor_descontar'];
                        /***************************************************************************************/
                    }
                }
                $total_supervisor_por_empresa+= $desconto_devolucao_superviso_por_empresa;
                $total_supervisor_todas_empresas+= $desconto_devolucao_superviso_por_empresa;
                
                if($total_supervisor_por_empresa > 0) {
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(115, 5, 'TOTAL DE REEMBOLSOS, ATRASOS DE PGTO E ABATIMENTOS DE PRE�OS', 1, 0, 'L');
                    $pdf->Cell(40, 5, number_format($desconto_devolucao_superviso_por_empresa, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell(50, 5, number_format($desconto_devolucao_superviso_por_empresa * $comissao_supervisao / 100, 2, ',', '.'), 1, 1, 'R');

                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(155, 5, 'Total '.$moeda.number_format($total_supervisor_por_empresa, 2, ',', '.'), 1, 0, 'R');
                    $pdf->Cell(50, 5, 'Total de 1% '.$moeda.number_format($total_supervisor_por_empresa * $comissao_supervisao / 100, 2, ',', '.'), 1, 1, 'R');
                }
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 10);
                //Sub Total Sobre Vendas Diretas na Moeda da NF
                $pdf->Cell(155, 5, 'SUB TOTAL SOBRE VENDAS DIRETAS '.$moeda, 1, 0, 'R');
                $pdf->Cell(50, 5, number_format($total_comissoes_vendas_diretas_por_empresa, 2, ',', '.'), 1, 1, 'R');
                
                /***************************************************************************************/
                if($id_empresa_loop == 4) {//Somente se a Empresa = '4', ou seja Grupo que eu mostro a linha abaixo ...
                    //Sub-Total de Supervis�o 1% na Moeda da NF
                    $pdf->Cell(155, 5, 'SUB TOTAL SOBRE SUPERVIS�O 1% '.$moeda, 1, 0, 'R');
                    $sub_total_sobre_supervisao = $total_supervisor_por_empresa * $comissao_supervisao / 100;
                    $pdf->Cell(50, 5, number_format($sub_total_sobre_supervisao, 2, ',', '.'), 1, 1, 'R');
                    
                    //Essa op��o existe somente p/ aqueles vendedores que est�o ligados as vendas de modo geral da Empresa ...
                    if($porc_comissao_sob_fat > 0) {
                        $pdf->Cell(155, 5, 'SUB-TOTAL S/ O TOTAL DO FATURAMENTO '.number_format($porc_comissao_sob_fat, 3, ',', '.').'%) '.$moeda, 1, 0, 'R');
                        
                        /*Campo preenchido de forma manual como um Valor de Faturamento estimativo, 
                        ainda n�o foi fechado o m�s e por isso esse campo tem de ser preenchido p/ que tenhamos 
                        um calculo da Comiss�o do Representante, caso o mesmo venha solicitar isso antes do 
                        fechamento do m�s "onde se tem o Valor Real de Faturamento" ...*/
                        if($txt_projecao_faturamento > 0) {//M�s em andamento, n�o temos ainda o Valor Real de Faturamento ...
                            $total_sobre_total_faturamento_por_empresa = round($txt_projecao_faturamento * $porc_comissao_sob_fat / 100, 2);
                        }else {//M�s fechado, conseq�entemente j� temos o Valor Real de Faturamento ...
                            /*Aqui eu Busco o Valor de Faturamento que � preenchido manual pelo RH na parte de Holeriths, lembrando que apenas
                            do dia 05 do pagamento pois existem Horeliths com Data do dia 20 que � do Vale e da� j� n�o me servem 
                            que tenha o campo 'qtde_dias_uteis_mes' preenchida ...*/
                            $sql = "SELECT `total_faturamento` 
                                    FROM `vales_datas` 
                                    WHERE `data` >= '$data_final' 
                                    AND SUBSTRING(`data`, 9, 2) <= '05' 
                                    AND `qtde_dias_uteis_mes` > '0' LIMIT 1 ";
                            $campos_faturamento = bancos::sql($sql);
                            $total_sobre_total_faturamento_por_empresa = round($campos_faturamento[0]['total_faturamento'] * $porc_comissao_sob_fat / 100, 2);
                        }
                        //N�o estamos pagando DSR sobre esse valor ...
                        $pdf->Cell(50, 5, number_format($total_sobre_total_faturamento_por_empresa, 2, ',', '.'), 1, 1, 'R');
                    }
                }
                /***************************************************************************************/
                /*****************Sub-Total das Devolu��es / Reembolsos na Moeda da NF******************/
                /***************************************************************************************/
                $pdf->Cell(155, 5, 'SUB TOTAL SOBRE DEVOLU��ES / REEMBOLSOS '.$moeda, 1, 0, 'R');
                $pdf->Cell(50, 5, number_format($total_devolucoes_reembolsos_por_empresa, 2, ',', '.'), 1, 1, 'R');
                
                //DSR na Moeda da NF ...
                $pdf->Cell(155, 5, 'DSR '.$moeda, 1, 0, 'R');
                //S� ir� existir esse valor se existir valor de Comiss�o ...
                $sub_total_global = $total_comissoes_vendas_diretas_por_empresa + ($total_supervisor_por_empresa * $comissao_supervisao / 100) + $total_devolucoes_reembolsos_por_empresa + $total_sobre_total_faturamento_por_empresa;
                
                /*Se a Qtde de Dias �teis ou Qtde de Dias In�teis = 0 ou o Representante for a Mercedes, ent�o n�o existe 
                c�lculo p/ o DSR ...*/
                if($qtde_dias_uteis_mes == 0 || $qtde_dias_inuteis_mes == 0 || empty($id_cargo_func) || $cmb_representante == 14) {
                    $dsr = 0;
                }else {
                    $dsr = $sub_total_global / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
                }
                $pdf->Cell(50, 5, number_format($dsr, 2, ',', '.'), 1, 1, 'R');
                
                /***************************************************************************************/
                /************************************Imposto de Renda***********************************/
                /***************************************************************************************/
                if($id_empresa_loop == 1 || $id_empresa_loop == 2) {//Somente se a Empresa = '1' ou 2, ou seja Albafer ou Tool Master que eu mostro a linha abaixo ...
                    $pdf->Cell(155, 5, 'IMPOSTO DE RENDA ('.number_format($aliquota_imposto_renda, 1, ',', '.').'%) '.$moeda, 1, 0, 'R');
                    
                    if(empty($id_cargo_func)) {
                        $sql = "SELECT `tipo_pessoa`, `id_pais`, `descontar_ir` 
                                FROM `representantes` 
                                WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
                        $campos_rep = bancos::sql($sql);
                        if(strtoupper($campos_rep[0]['descontar_ir'] == 'S')) {//Est� marcado no Cadastro p/ descontar IR ...
                            if($campos_rep[0]['id_pais'] == 31) {//Representante do Brasil ...
                                if($campos_rep[0]['tipo_pessoa'] == 'J') {//Pessoa Jur�dica ...
                                    
                                    /*O Roberto mudou esse IF em 14/07/2017, se ningu�m falar mais nada, mais pra frente irei arrancar o c�digo 
                                    comentado ...*/
                                    /*Nesse caso por ser positivo o "$total_devolucoes_reembolsos_por_empresa", ent�o esse tem que compor a f�rmula 
                                    o IR j� foi descontado quando houve alguma Devolu��o anterior e agora precisa ser recomposto por haver um 
                                    Reembolso ...*/
                                    //if($total_devolucoes_reembolsos_por_empresa > 0) {
                                        $ir_por_empresa=- round((round($total_comissoes_vendas_diretas_por_empresa, 2) + round($total_devolucoes_reembolsos_por_empresa, 2)) * $aliquota_imposto_renda / 100, 2);
                                    /*}else {
                                        $ir_por_empresa=- round(round($total_comissoes_vendas_diretas_por_empresa, 2) * $aliquota_imposto_renda / 100, 2);
                                    }*/
                                    if(abs($ir_por_empresa) > $valor_minimo_gare) {
                                        $pdf->Cell(50, 5, number_format($ir_por_empresa, 2, ',', '.'), 1, 1, 'R');
                                    }else {
                                        $ir_por_empresa = 0;//Ignoro o Valor M�nimo pois ele � muito baixo ...
                                        $pdf->Cell(50, 5, 'N�o atinge Vl.Min GARE', 1, 1, 'R');
                                    }
                                }else {//Pessoa F�sica ...
                                    $ir_por_empresa = 0;
                                    $pdf->Cell(50, 5, 'Isento', 1, 1, 'R');//Pessoa F�sica n�o tem Empresa Aberta ...
                                }
                            }else {//Representante Internacional ...
                                $ir_por_empresa = 0;
                                $pdf->Cell(50, 5, 'Isento', 1, 1, 'R');//Representante n�o � do Brasil ...
                            }
                        }else {//N�o est� marcado no Cadastro p/ descontar IR ...
                            $ir_por_empresa = 0;//Se n�o est� marcado no cadastro, ent�o este n�o declara este Imposto ...
                            $pdf->Cell(50, 5, 'Isento', 1, 1, 'R');
                        }
                    }else {//Representa que o representante � Funcion�rio ...
                        $ir_por_empresa = 0;
                        $pdf->Cell(50, 5, 'Isento', 1, 1, 'R');
                    }
                }
                //Total Geral na Moeda da NF ...
                $pdf->Cell(155, 5, 'TOTAL GERAL '.$moeda, 1, 0, 'R');
                $total_global = $sub_total_global + $dsr + $ir_por_empresa;
                
                $total_geral_global+= $total_global; //Guardo o Total Geral de Comiss�o que o Representante ganhou ...
                
                $pdf->Cell(50, 5, number_format($total_global, 2, ',', '.'), 1, 1, 'R');
                $total_global = number_format($total_global, 2, '.', '');//Macete (rs)
                
                /******************************************************************************************************/
                /**********************Script p/ Grava��o das Comiss�es e Pr�mios do Representante*********************/
                /******************************************************************************************************/
                /*Para evitar que quando algum funcion�rio consulte per�odos maiores, acabe dando Inserts ou Updates fora do per�odo normal 
                do Pagamento de comiss�o ... 

                Obs: Sempre tendo a Data do dia maior do que a Data Final - garantindo que a Comiss�o foi rodada dentro de um per�odo inteiro 
                e n�o dentro de um per�odo parcial ...*/
                if($dia_inicial == 26 && $dia_final == 25 && $qtde_dias <= 31 && (date('Y-m-d') > $data_final || $_POST['chkt_gerar_comissao_antes_prazo'] == 'S')) {
                    //1)Busca do Funcion�rio atrav�s do id_representante ...
                    $sql = "SELECT `id_funcionario` 
                            FROM `representantes_vs_funcionarios` 
                            WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
                    $campos_representante_funcionario = bancos::sql($sql);
                    if(count($campos_representante_funcionario) == 1) {/**********************� funcion�rio*********************/
//Verifico se retornou algum valor no SQL de busca da pr�xima Data do Holerith, feito l� no in�cio ...
                        if(count($campos_data) == 1) {
/*Primeiro eu verifico se j� existe esse "id_funcionario" na respectiva "Data de Holerith" da data que foi 
filtrada pelo usu�rio nesse relat�rio de Comiss�es nessa respectiva tabela de "representantes_vs_comissoes" ...*/
                            $sql = "SELECT `id_funcionario_vs_holerith` 
                                    FROM `funcionarios_vs_holeriths` 
                                    WHERE `id_funcionario` = '".$campos_representante_funcionario[0]['id_funcionario']."' 
                                    AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
                            $campos_funcionario_holerith = bancos::sql($sql);
                            if(count($campos_funcionario_holerith) == 0) {
                                $sql = "INSERT INTO `funcionarios_vs_holeriths` (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`) VALUES (NULL, '".$campos_representante_funcionario[0]['id_funcionario']."', '$id_vale_data') ";
                                bancos::sql($sql);
                                $id_funcionario_vs_holerith = bancos::id_registro();
                            }else {
                                $id_funcionario_vs_holerith = $campos_funcionario_holerith[0]['id_funcionario_vs_holerith'];
                            }
                        
                            if($id_empresa_loop == 1) {//Se a Empresa for Albafer ...
                                $sql = "UPDATE `funcionarios_vs_holeriths` SET `comissao_alba` = '$total_global', `dsr_alba` = '$dsr' WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
                            }else if($id_empresa_loop == 2) {//Se a Empresa for Tool Master ...
                                $sql = "UPDATE `funcionarios_vs_holeriths` SET `comissao_tool` = '$total_global', `dsr_tool` = '$dsr' WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
                            }else if($id_empresa_loop == 4) {//Se a Empresa for Grupo ...
                                $sql = "UPDATE `funcionarios_vs_holeriths` SET `comissao_grupo` = '$total_global', `dsr_grupo` = '$dsr' WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
                            }
                            bancos::sql($sql);
                        }
                    }else {/**********************� representante*********************/
//Verifico se retornou algum valor no SQL de busca da pr�xima Data do Holerith, feito l� no in�cio ...
                        if(count($campos_data) == 1) {
/*Primeiro eu verifico se j� existe esse "id_representante" na respectiva "Data de Holerith" da data que foi 
filtrada pelo usu�rio nesse relat�rio de Comiss�es nessa respectiva tabela de "representantes_vs_comissoes" ...*/
                            $sql = "SELECT `id_representante_vs_comissao` 
                                    FROM `representantes_vs_comissoes` 
                                    WHERE `id_representante` = '$cmb_representante' 
                                    AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
                            $campos_representante_comissao = bancos::sql($sql);
                            if(count($campos_representante_comissao) == 0) {
                                $sql = "INSERT INTO `representantes_vs_comissoes` (`id_representante_vs_comissao`, `id_representante`, `id_vale_data`) VALUES (NULL, '$cmb_representante', '$id_vale_data') ";
                                bancos::sql($sql);
                                $id_representante_vs_comissao = bancos::id_registro();
                            }else {
                                $id_representante_vs_comissao = $campos_representante_comissao[0]['id_representante_vs_comissao'];
                            }
                        
                            if($id_empresa_loop == 1) {//Se a Empresa for Albafer ...
                                $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_alba` = '$total_global' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                            }else if($id_empresa_loop == 2) {//Se a Empresa for Tool Master ...
                                $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_tool` = '$total_global' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                            }else if($id_empresa_loop == 4) {//Se a Empresa for Grupo ...
                                $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_grupo` = '$total_global' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                            }
                            bancos::sql($sql);
                        }
                    }//Fim do Representante ...
                }
                /******************************************************************************************************/
                $pdf->Ln(5);
                $pdf->Cell(100, 5, 'Banco: '.$banco, 1, 0, 'L');
                $pdf->Cell(40, 5, 'Ag: '.$agencia, 1, 0, 'L');
                $pdf->Cell(65, 5, 'C/C: '.$conta_corrente, 1, 1, 'L');
                $pdf->Cell(140, 5, 'Correntista: '.$correntista, 1, 0, 'L');
                $pdf->Cell(65, 5, $rotulo_cnpj_cpf.$cnpj_cpf, 1, 1, 'L');
            }//Fim da Garantia que n�o ser�o impressos comiss�es com valores Zerados ...
        }
        faturamentos::comissao_ultimos3meses($cmb_representante);
        
        /**********************************************************************/
        /********************************PR�MIOS*******************************/
        /**********************************************************************/
        //Essa parte de Pr�mio alcance de Cota(s) / Meta(s) s� existe p/ Representante que � Funcion�rio ...
        $sql = "SELECT `id_funcionario` 
                FROM `representantes_vs_funcionarios` 
                WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
        $campos_representante_funcionario = bancos::sql($sql);
        if(count($campos_representante_funcionario) == 1) {/**********************� funcion�rio*********************/
            //Pr�mio alcance de Cota(s) / Meta(s) ...
            $pdf->AddPage();
            $pdf->SetLeftMargin(6);

            $pdf->SetFont('Arial', 'B', 12);
            Heade('PREMIOS', $data_inicial, $data_final, $data_holerith, $cmb_representante, $id_empresa_loop);
            $pdf->Ln(5);

            $pdf->Cell(205, 5, 'PR�MIO ALCANCE DE COTA(S) / META(S)', 1, 1, 'C');
            $pdf->Ln(5);


            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(205, 5, 'TABELA ( % ATINGIDA SOBRE COTA(S) x % INCREMENTO )', 1, 1, 'C');

            $pdf->Cell(130, 5, '<=60%', 1, 0, 'C');
            $pdf->Cell(75, 5, '0%', 1, 1, 'C');

            $pdf->Cell(130, 5, '> 60% e <=80%', 1, 0, 'C');
            $pdf->Cell(75, 5, '10%', 1, 1, 'C');

            $pdf->Cell(130, 5, '> 80% e <=100%', 1, 0, 'C');
            $pdf->Cell(75, 5, '15%', 1, 1, 'C');

            $pdf->Cell(130, 5, '> 100%', 1, 0, 'C');
            $pdf->Cell(75, 5, '20%', 1, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(130, 5, 'Total de Vendas Diretas '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_vendas_diretas_todas_empresas, 2, ',', '.'), 1, 1, 'R');

            $pdf->Cell(130, 5, 'Total de Devolu��es '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_devolucoes_todas_empresas, 2, ',', '.'), 1, 1, 'R');

            $total_vendas_menos_devolucoes = $total_vendas_diretas_todas_empresas - $total_devolucoes_todas_empresas;

            $pdf->Cell(130, 5, 'Total de Vendas Diretas - Devolu��es '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_vendas_menos_devolucoes, 2, ',', '.'), 1, 1, 'R');

            $cota_total_do_periodo = vendas::cota_total_do_representante($cmb_representante, $data_inicial, $data_final);

            $pdf->Cell(130, 5, 'Total de Cotas em Vig�ncia Vendas Diretas '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($cota_total_do_periodo, 2, ',', '.'), 1, 1, 'R');

            $pdf->SetFillColor(220, 220, 220);//Cor Cinza
            $perc_atingida_sobre_cotas_vendas_diretas = ($total_vendas_diretas_todas_empresas - $total_devolucoes_todas_empresas) / $cota_total_do_periodo * 100;

            $pdf->Cell(130, 5, '% atingida sobre Cotas Vendas Diretas', 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($perc_atingida_sobre_cotas_vendas_diretas, 1, ',', '.'), 1, 1, 'R');

            if($perc_atingida_sobre_cotas_vendas_diretas < 60) {
                $incremento_premio_cota = 0;
            }else if($perc_atingida_sobre_cotas_vendas_diretas < 80) {
                $incremento_premio_cota = 10;
            }else if($perc_atingida_sobre_cotas_vendas_diretas < 100) {
                $incremento_premio_cota = 15;
            }else {
                $incremento_premio_cota = 20;
            }

            $pdf->Cell(130, 5, '% incremento Vendas Diretas', 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($incremento_premio_cota, 1, ',', '.'), 1, 1, 'R');

            $pdf->Cell(130, 5, 'Total sobre Vendas Diretas '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_comissoes_vendas_diretas_todas_empresas, 2, ',', '.'), 1, 1, 'R');

            $pdf->Cell(130, 5, 'Total sobre Devolu��es / Reembolsos '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_devolucoes_reembolsos_todas_empresas, 2, ',', '.'), 1, 1, 'R');

            $total_sobre_vendas_dir_menos_dev_reemb = ($total_comissoes_vendas_diretas_todas_empresas + $total_devolucoes_reembolsos_todas_empresas);

            $pdf->Cell(130, 5, 'Total sobre Vendas Diretas - Dev. / Reemb. '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_sobre_vendas_dir_menos_dev_reemb, 2, ',', '.'), 1, 1, 'R');

            $premio_alcance_cotas_metas_vendas_diretas = $incremento_premio_cota / 100 * $total_sobre_vendas_dir_menos_dev_reemb;

            $pdf->Cell(130, 5, 'Pr�mio alcance de Cota(s) / Meta(s) Vendas Diretas '.$moeda, 1, 0, 'R', 1);
            $pdf->Cell(75, 5, number_format($premio_alcance_cotas_metas_vendas_diretas, 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);


            $pdf->Cell(130, 5, 'Total de Supervis�o - Dev. / Reemb. '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($total_supervisor_todas_empresas, 2, ',', '.'), 1, 1, 'R');

            $cota_total_do_periodo_supervisao = vendas::cota_total_do_representante($cmb_representante, $data_inicial, $data_final, 'S');

            $pdf->Cell(130, 5, 'Total de Cotas em Vig�ncia Supervis�o '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($cota_total_do_periodo_supervisao, 2, ',', '.'), 1, 1, 'R');

            $perc_atingida_sobre_cotas_supervisao = $total_supervisor_todas_empresas / $cota_total_do_periodo_supervisao * 100;

            $pdf->Cell(130, 5, '% atingida sobre Cotas Supervis�o', 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($perc_atingida_sobre_cotas_supervisao, 1, ',', '.'), 1, 1, 'R');

            if($perc_atingida_sobre_cotas_supervisao < 60) {
                $incremento_premio_cota_supervisao = 0;
            }else if($perc_atingida_sobre_cotas_supervisao < 80) {
                $incremento_premio_cota_supervisao = 10;
            }else if($perc_atingida_sobre_cotas_supervisao < 100) {
                $incremento_premio_cota_supervisao = 15;
            }else {
                $incremento_premio_cota_supervisao = 20;
            }

            $pdf->Cell(130, 5, '% incremento Supervis�o', 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($incremento_premio_cota_supervisao, 1, ',', '.'), 1, 1, 'R');

            $pdf->Cell(130, 5, 'Total sobre Supervis�o - Dev. / Reemb. '.$moeda, 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($sub_total_sobre_supervisao, 2, ',', '.'), 1, 1, 'R');

            $premio_alcance_cotas_metas_supervisao = $incremento_premio_cota_supervisao / 100 * $sub_total_sobre_supervisao;

            $pdf->Cell(130, 5, 'Pr�mio alcance de Cota(s) / Meta(s) Supervis�o '.$moeda, 1, 0, 'R', 1);
            $pdf->Cell(75, 5, number_format($premio_alcance_cotas_metas_supervisao, 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);


            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(205, 5, 'PR�MIO COMISS�ES EXTRA (ESTE M�S FOI INCORPORANDO AS COMISS�ES)', 1, 1, 'C');
            $premio_comissoes_extra = 0;
            $pdf->Ln(5);


            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(205, 5, 'PR�MIO PRODUTIVIDADE DE TRABALHO', 1, 1, 'C');
            $pdf->Ln(5);


            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(205, 5, 'TABELA PONTOS x PR�MIO', 1, 1, 'C');

            $pdf->Cell(130, 5, 'De 0 a 150 pontos', 1, 0, 'C');
            $pdf->Cell(75, 5, 'R$ 0,00', 1, 1, 'C');

            $pdf->Cell(130, 5, 'De 151 a 250 pontos', 1, 0, 'C');
            $pdf->Cell(75, 5, 'R$ 50,00', 1, 1, 'C');

            $pdf->Cell(130, 5, 'De 251 a 350 pontos', 1, 0, 'C');
            $pdf->Cell(75, 5, 'R$ 100,00', 1, 1, 'C');

            $pdf->Cell(130, 5, 'De 351 a 500 pontos', 1, 0, 'C');
            $pdf->Cell(75, 5, 'R$ 200,00', 1, 1, 'C');

            $pdf->Cell(130, 5, 'Acima de 500 pontos', 1, 0, 'C');
            $pdf->Cell(75, 5, 'R$ 400,00', 1, 1, 'C');
            $pdf->Ln(5);

/*Verifico se na tabela "funcionarios_vs_holeriths" j� foram cadastrados os pr�mios para o Funcion�rio 
na respectiva "Data de Holerith", hoje em dia quem faz isso � a pessoa respons�vel pelo RH ...*/
            $sql = "SELECT `premio_produtividade_trabalho`, `premio_tdc`, `premio_industrializados` 
                    FROM `funcionarios_vs_holeriths` 
                    WHERE `id_funcionario` = '".$campos_representante_funcionario[0]['id_funcionario']."' 
                    AND `id_vale_data` = '$id_vale_data' LIMIT 1 ";
            $campos_funcionario_holerith = bancos::sql($sql);

            if($campos_funcionario_holerith[0]['premio_produtividade_trabalho'] <= 150) {
                $premio_produtividade_trabalho_rs = 0;
            }else if($campos_funcionario_holerith[0]['premio_produtividade_trabalho'] <= 250) {
                $premio_produtividade_trabalho_rs = 50;
            }else if($campos_funcionario_holerith[0]['premio_produtividade_trabalho'] <= 350) {
                $premio_produtividade_trabalho_rs = 100;
            }else if($campos_funcionario_holerith[0]['premio_produtividade_trabalho'] <= 500) {
                $premio_produtividade_trabalho_rs = 200;
            }else {
                $premio_produtividade_trabalho_rs = 400;
            }

            $pdf->SetFont('Arial', '', 12);

            $pdf->Cell(130, 5, 'Pontos ', 1, 0, 'R');
            $pdf->Cell(75, 5, number_format($campos_funcionario_holerith[0]['premio_produtividade_trabalho'], 1, ',', '.'), 1, 1, 'R');
            $pdf->Cell(130, 5, 'Pr�mio Produtividade de Trabalho '.$moeda, 1, 0, 'R', 1);
            $pdf->Cell(75, 5, number_format($premio_produtividade_trabalho_rs, 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);

            $pdf->Cell(130, 5, 'Pr�mio TDC '.$moeda, 1, 0, 'R', 1);
            $pdf->Cell(75, 5, number_format($campos_funcionario_holerith[0]['premio_tdc'], 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);

            $pdf->Cell(130, 5, 'Pr�mio Vendas Produtos Industrializados Internamente '.$moeda, 1, 0, 'R', 1);
            $pdf->Cell(75, 5, number_format($campos_funcionario_holerith[0]['premio_industrializados'], 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);

            $pdf->Cell(130, 5, 'SubTotal dos Pr�mios '.$moeda, 1, 0, 'R', 1);

            $subtotal_premios = $premio_alcance_cotas_metas_vendas_diretas + $premio_alcance_cotas_metas_supervisao + $premio_comissoes_extra + $premio_produtividade_trabalho_rs + $campos_funcionario_holerith[0]['premio_tdc'] + $campos_funcionario_holerith[0]['premio_industrializados'];

            $pdf->Cell(75, 5, number_format($subtotal_premios, 2, ',', '.'), 1, 1, 'R', 1);
            $pdf->Ln(5);

            $pdf->MultiCell(205, 5, 'O r�tulo acima est� como SubTotal dos Pr�mios, porque o Pr�mio "Os Melhores" � calculado somente dentro da Folha de Pagamento.', 0, 'L');

            $sql = "UPDATE `funcionarios_vs_holeriths` SET `perc_atingida_sobre_cotas_vendas_diretas` = '$perc_atingida_sobre_cotas_vendas_diretas', `perc_atingida_sobre_cotas_supervisao` = '$perc_atingida_sobre_cotas_supervisao', `subtotal_premios` = '$subtotal_premios' WHERE `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' LIMIT 1 ";
            bancos::sql($sql);

            //Perguntar para Sandra se o IR devia ser sobre o Total de Vendas - Devolu��es
            //Perguntar para Sandra se o IR devia ser sobre o Total de Vendas ...
        }
    }
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(getcwd()), '').'comissao_de_representante.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>