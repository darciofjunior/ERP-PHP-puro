<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...

class segurancas extends bancos {
    function number_format($campo, $casas_decimais = 0, $ponto = '', $pdf = 0) {
        if($campo == 0) {
            if($pdf == 1) {
                return ' ';
            }else {
                return '&nbsp;';
            }
        }else {
            $virgula = ($casas_decimais > 0) ? ',' : '';
            return number_format($campo, $casas_decimais, $virgula, $ponto);
        }
    }

    function criptografia($login, $senha) {
        if (strlen($login) >= strlen($senha)) {
            for ($x = 0, $y = 0; $x < strlen($senha); $x ++, $y ++) {
                $processo = ord(substr($login, $x, 1)) + ord(substr($senha, $y, 1));
                if ($y >= strlen($senha) - 1) $y = -1;
                $gerado.= chr($processo);
            }
        }else {
            for ($x = 0, $y = 0; $y < strlen($senha); $x ++, $y ++) {
                $processo = ord(substr($login, $x, 1)) + ord(substr($senha, $y, 1));
                if($x >= strlen($login) - 1) $x = -1;
                $gerado.= chr($processo);
            }
        }
        return ($gerado);
    }

    function descriptografia($login, $senha)  {
        if (strlen($login) >= strlen($senha)) {
            for ($x = 0, $y = 0; $x < strlen($senha); $x ++, $y ++) {
                $processo   = ord(substr($senha, $y, 1)) - ord(substr($login, $x, 1));
                if($y >= strlen($senha) - 1) $y = -1;
                $gerado.= chr($processo);
            }
        }else {
            for ($x = 0, $y = 0; $y < strlen($senha); $x ++, $y ++) {
                $processo   = ord(substr($senha, $y, 1)) - ord(substr($login, $x, 1));
                if ($x >= strlen($login) - 1) $x = -1;
                $gerado.= chr($processo);
            }
        }
        return ($gerado);
    }

    function autentica($login, $senha, $screen_width) {
        //Aqui eu verifico o status do Funcionário que está tentando logar no Sistema ...
        $sql = "SELECT `id_login`, `id_funcionario`, `id_modulo` AS id_modulo_default, `tipo_login`, `senha` 
                FROM `logins` 
                WHERE `login` = '$login' 
                AND `ativo` >= '1' LIMIT 1 ";
        $campos     = bancos::getDb()->query($sql);
        $linhas     = $campos->rowCount();
        if($linhas == 1) {//O sistema encontrou o "Login Ativo" digitado pelo Usuário na Tela Inicial do Sistema ...
            $result_login = $campos->fetch(PDO::FETCH_ASSOC);//Aqui eu leio o resultado da consulta do Banco de Dados do PDO ...
            
            if($result_login['tipo_login'] == 'FUNCIONARIO') {//Significa que esse Login é um Funcionário da Albafer ...
                /*Nesse caso eu busco o "status" desse respectivo Login no seu cadastro de Funcionários 
                => "Situação do mesmo aqui na Empresa" ...*/
                $sql = "SELECT `id_empresa`, `status` 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` >= '".$result_login['id_funcionario']."' LIMIT 1 ";
                $campos             = bancos::getDb()->query($sql);
                $result_funcionario = $campos->fetch(PDO::FETCH_ASSOC);//Aqui eu leio o resultado da consulta do Banco de Dados do PDO ...
                $id_empresa         = $result_funcionario['id_empresa'];
                $status             = $result_funcionario['status'];
            }else {//Significa que esse Login é qq outra coisa menos Funcionário ...
                $id_empresa         = 4;//Interpreto que esse Login está como Grupo, devido não ter registro ...
                $status             = 1;//Se o Login estava ativo então com toda segurança posso ativá-lo p/ acessar o Sistema ...
            }
            
            /*A Solange é a única que está afastada aqui da Empresa devido as cirurgias que tem feito, 
            mas precisa estar logando no sistema devido estar trabalhando de casa - Mudança 
            feita no dia 05/04/2015 ...*/
            if($status == 1 || $login == 'solange') {//Login ativo, pode Logar ...
                //Comparação da Senha digitada pelo Usuário com a Senha do Banco de Dados ...
                $senha_digitada = segurancas::criptografia($login, $senha);
                for($i = 0; $i < strlen($senha_digitada); $i++) {
                    //Não me interessa pegar os caracteres Espaço, porque o Sistema dá pau ...
                    if(ord(substr($senha_digitada, $i, 1)) != 32 && ord(substr($senha_digitada, $i, 1)) != 160) {
                        $senha_comparar1.= substr($senha_digitada, $i, 1);
                    }
                }
                for($i = 0; $i < strlen($result_login['senha']); $i++) {
                    //Não me interessa pegar os caracteres Espaço, porque o Sistema dá pau ...
                    if(ord(substr($result_login['senha'], $i, 1)) != 32 && ord(substr($result_login['senha'], $i, 1)) != 160) {
                        $senha_comparar2.= substr($result_login['senha'], $i, 1);
                    }
                }
                /**********************************************************************************************/
                //Busca o numero de tentativas que o usuário logou errado ...
                $sql = "SELECT `tentativa_errada` 
                        FROM `logins` 
                        WHERE `login` = '$login' 
                        AND `ativo` >= '1' LIMIT 1 ";
                $campo_tentativa    = bancos::getDb()->query($sql);
                $result_tentativa   = $campo_tentativa->fetch(PDO::FETCH_ASSOC);//Aqui eu leio o resultado da consulta do Banco de Dados do PDO ...
                if($result_tentativa['tentativa_errada'] == 3) {//Aqui é para bloquear de propósito ...
                    echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=5'</Script>";
                    exit;
                }
                /**********************************************************************************************/
                if($senha_comparar1 != $senha_comparar2) {//comparar as senhas para ver se está certo ou errada ...
                    //Adiciona + 1 na tentativa errada ...
                    $sql = "UPDATE `logins` SET `tentativa_errada` = `tentativa_errada` + 1 WHERE `login` = '$login' LIMIT 1 ";
                    bancos::getDb()->query($sql);
                    echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=2'</Script>";
                    exit;
                }else {
                    //Zero o número de tentativas erradas, afinal o usuário acertou logar ... rs
                    $sql = "UPDATE `logins` SET `tentativa_errada` = '0' WHERE `login` = '$login' LIMIT 1 ";
                    bancos::getDb()->query($sql);
                    //Verifica se obtem acesso ao módulo ...
                    if(empty($result_login['id_modulo_default']) || $result_login['id_modulo_default'] == 0) $result_login['id_modulo_default'] = 10;
                    //Registrar Sessão ...
                    session_start('funcionarios');
                    $_SESSION['id_login']           = $result_login['id_login'];
                    $_SESSION['id_funcionario']     = $result_login['id_funcionario'];
                    $_SESSION['id_empresa']         = $id_empresa;
                    $_SESSION['id_modulo']          = $result_login['id_modulo_default'];
                    $_SESSION['ip']                 = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['login']              = $login;
                    $_SESSION['ultimo_acesso']      = date('Y-m-d H:i:s');
                    $_SESSION['screen_width']       = $screen_width;
                    /********************************************Logs********************************************/
                    if(!class_exists('logs')) 	require 'logs.php';//CASO EXISTA EU DESVIO A CLASSE
                    logs::gerenciar_logs();//Cria toda a Estrutura de Banco de Dados de Logs ...
                    $vetor_meses    = array('', '1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
                    $database       = 'logs_'.date('Y');
                    $mes_current    = $vetor_meses[(int)date('m')];
                    $sql            = "INSERT INTO $database.`logs_logins_logout_$mes_current` (`id_log_login`, `id_login`, `id_modulo`, `ip` ,`status` ,`data`) VALUES (NULL, '$_SESSION[id_login]', '$_SESSION[id_modulo]', '$_SESSION[ip]', '1', '".date('Y-m-d H:i:s')."') ";
                    bancos::getDb()->query($sql);
                    /********************************************************************************************/
                    /*********************************Estruturação de BD por ano*********************************/
                    /********************************************************************************************/
                    if(!class_exists('bancos_anuais.php')) require 'bancos_anuais.php';//CASO EXISTA EU DESVIO A CLASSE ...
                    $bancos_anuais = new bancos_anuais();
                    /********************************************************************************************/
                    
                    //Verifico se existe alguma URL pendente para ser acessada ...
                    session_start('url_pendente');
                    if(isset($_SESSION['request_uri'])) {
                        header('Location: '.$_SESSION['request_uri']);
                    }else {
                        header('Location:mural/mural.php');
                    }
                }
            }else {//Funcionário em algum outro status e diferente da Solange ...
                echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=1'</Script>";
                exit;
            }
        }else {//O sistema não encontrou o Login digitado pelo Usuário ...
            echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=1'</Script>";
            exit;
        }
    }

    //Função que verifica se o Usuário tem acesso a URL do ERP que ele tentou acessar via ERP ou por de trás do Sistema ...
    function conferencia($endereco) {
        session_start('funcionarios');
        if(strpos($endereco, 'modulo') == true) {//Significa que nessa URL existe a palavra Módulo ...
            //Verifico se o usuário tem acesso aos itens de Menu da URL que ele tentou acessar ...
            $sql = "SELECT ta.id_tipo_acesso 
                    FROM `menus_itens` mi 
                    INNER JOIN tipos_acessos ta ON ta.id_menu_item = mi.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
                    WHERE mi.endereco = '$endereco' LIMIT 1 ";
            $campos_itens_menu = bancos::getDb()->query($sql);
            if($campos_itens_menu->rowCount() == 0) {//Representa que o Usuário / Funcionário não tem acesso nessa URL ...
                //Verifico se o usuário tem acesso ao Menu da URL que ele tentou acessar ...
                $sql = "SELECT ta.id_tipo_acesso 
                        FROM `menus` m 
                        INNER JOIN tipos_acessos ta ON ta.id_menu = m.id_menu AND ta.id_login = '$_SESSION[id_login]' 
                        WHERE m.endereco = '$endereco' LIMIT 1 ";
                $campos_menu = bancos::getDb()->query($sql);
                $valor = ($campos_menu->rowCount() == 0) ? 0 : 1;
            }else {
                $valor = 1;
            }
            return $valor;
        }else {//Irá cair nessa tela quando for um acesso que não tenha a palavra módulo na URL, por exemplo Mural ...
            return 1;
        }
    }

    function geral($endereco, $nivel, $origem = '', $id_identificacao = 0) {
        session_start('funcionarios');
        if (segurancas::conferencia($endereco) == 0) {//Significa que o Usuário não tem acesso a URL do ERP que ele tentou acessar ...
            echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=4'</Script>";
        }else {//Acesso permitido ...
            $sql = "SELECT `id_login`, SUBSTRING(`data_sys`, 1, 10) AS data 
                    FROM `logins` 
                    WHERE `id_login` = '$GLOBALS[id_login]' 
                    AND SUBSTRING(`data_sys`, 1, 10) <> '0000-00-00' 
                    AND `ativo` >= '1' LIMIT 1 ";
            $resultado = bancos::getDb()->query($sql);
            if(count($resultado) == 0) {//Se a data da senha =0000-00-00 significa q ela expirou e precisa ser atualizada ...
                echo "<Script Language='JavaScript'>window.top.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=4'</Script>";
            }
            /********************************************Logs********************************************/
            if(!class_exists('logs')) 	require 'logs.php';//CASO EXISTA EU DESVIO A CLASSE
            logs::gerenciar_logs();//Cria toda a Estrutura de Banco de Dados de Logs ...
                $vetor_meses 	= array('', '1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
                $database 		= 'logs_'.date('Y');
                $mes_current 	= $vetor_meses[(int)date('m')];
                $sql 			= "INSERT INTO $database.`logs_acessos_telas_$mes_current` (`id_log_acesso`, `id_funcionario`, `identificacao`, `origem`, `url`, `ip`, `data_ocorrencia`) 
                                            VALUES (NULL, '$_SESSION[id_funcionario]', '$id_identificacao', '$origem', '$endereco', '$_SESSION[ip]', '".date('Y-m-d H:i:s')."') ";
                bancos::getDb()->query($sql);
            /********************************************************************************************/
        }
    }
    
    //Esses 2 métodos abaixo são utilizados na Criação / Alteração de Menus, Itens, Sub-Itens, etc ...
    function tratar_path($endereco) {
        $caracter           = trim('\ ');
        $caracter           = $caracter.$caracter;
        $endereco           = str_replace($caracter, '/', $endereco);
        $contador_caracter  = strlen($endereco);

        for($i = 0; $i < $contador_caracter; $i++) {
            $letra = substr($endereco, $i, 1);
            if($letra == 'm') {
                if(substr($endereco, $i, 6) == 'modulo') {
                    $endereco = substr($endereco, $i, $contador_caracter);
                    $i = $contador_caracter;
                }
            }
        }
        $endereco = '/erp/albafer/'.$endereco;
        return $endereco;
    }
    
    function controle_casas_decimais($valor) {
        if(strlen($valor) == 1) {
            $valor = '00'.$valor;
        }else if(strlen($valor) == 2) {
            $valor = '0'.$valor;
        }
        return $valor;
    }
}
?>