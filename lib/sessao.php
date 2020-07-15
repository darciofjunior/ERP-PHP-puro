<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class sessao extends bancos {
    public function gerenciar_sessao() {
        /***********************Controle com o Tempo da Sessão***********************/
        session_start('funcionarios');
        if(isset($_SESSION['ultimo_acesso'])) {//Se ainda existe Sessão faço a verificação ...
            $hora_logada = strtotime(substr($_SESSION['ultimo_acesso'], 11, 8));
            $hora_atual = strtotime(date('H:i:s'));
    
            $tempo_logado = mktime(date('H', $hora_atual) - date('H', $hora_logada), date('i', $hora_atual) - date('i', $hora_logada), date('s', $hora_atual) - date('s', $hora_logada));
            $tempo_logado = date('H:i:s', $tempo_logado);

            //Se o usuário ficou + de 10 minutos sem mexer destrói a Sessão por segurança e redireciona para a tela de Login ...
            if($tempo_logado > '00:10:00') {
                /*******************************************Sessão*******************************************/
                /*Aqui eu guardo a última URL que o usuário estava acessando dentro do ERP até cair a Sessão, para que está possa 
                ser restaurada num próximo Login ...*/
                //Essas são as únicas URL´s que não me interessam gravar para fazer a Restauração de Último Acesso ...
                if(strpos($_SERVER['PHP_SELF'], 'mural') === false && strpos($_SERVER['PHP_SELF'], 'relogio_sessao') === false) {
                    $sql = "UPDATE `logins` SET `ultima_url_acessada` = '$_SERVER[PHP_SELF]' WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
                    bancos::getDb()->query($sql);
                }
                /********************************************Logs********************************************/
                if(!class_exists('logs')) require 'logs.php';//CASO EXISTA EU DESVIO A CLASSE
                logs::gerenciar_logs();//Cria toda a Estrutura de Banco de Dados de Logs ...
                $vetor_meses 	= array('', '1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
                $database 	= 'logs_'.date('Y');
                $mes_current 	= $vetor_meses[(int)date('m')];
                $sql		= "INSERT INTO $database.`logs_logins_logout_$mes_current` (`id_log_login`, `id_login`, `id_modulo`, `ip`, `status`, `data`) VALUES (NULL, '$_SESSION[id_login]', '$_SESSION[id_modulo]', '$_SESSION[ip]', '0', '".date('Y-m-d H:i:s')."') ";
                bancos::getDb()->query($sql);
                /********************************************************************************************/
                session_unset('funcionarios');//Exclui todas as variáveis armazenadas da Sessão ...
                session_destroy();//Destrói a Sessão já vazia ...
                echo "<Script Language='JavaScript'>
                            alert('SESSÃO EXPIRADA POR FALTA DE USO !!! FAVOR LOGAR NOVAMENTE !')
                            window.top.parent.location = 'http://".$_SERVER['SERVER_ADDR']."/erp/albafer/default.php?deslogar=s&valor=3'
                      </Script>";
            }else {//Caso mexou em um tempo menor, a Sessão reassumi o tempo atual ...
                $_SESSION['ultimo_acesso'] = date('Y-m-d H:i:s');
                return bancos::getDb();
            }
        }else {//Se a sessão já caiu e o Sistema por algum motivo não deslogou, forço o Sistema a sair na marra ... rs
            if($_SERVER['PHP_SELF'] != '/erp/albafer/default.php') {
                echo "<Script Language='JavaScript'>
                            alert('SESSÃO EXPIRADA !!! O SERVIDOR JÁ HAVIA PERDIDO A AUTENTICAÇÃO E NÃO RETORNOU PARA A TELA DE LOGIN !!\n\nFAVOR LOGAR NOVAMENTE !')
                            window.top.parent.location = 'http://".$_SERVER['SERVER_ADDR']."/erp/albafer/default.php?deslogar=s&valor=3'
                      </Script>";
            }
        }
/****************************************************************************/
    }
    /*Método que atualiza o Tempo de Sessão enquando o usuário estiver digitando ou teclando dentro de uma Pop-Div 
    e não tiver caído o tempo de Sessão ... - Esse método ainda não está sendo utilizado ...*/
    public function renovar_sessao() {
        echo "<Script Language='JavaScript'>
        if(typeof(parent.relogio_sessao) == 'object') {                   
            '<body 
                onclick=\"if(typeof(parent.relogio_sessao) == \'object\') {
                    parent.relogio_sessao.location = \'/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S\'
                }
                onkeyup=\"if(typeof(parent.relogio_sessao) == \'object\') {
                    parent.relogio_sessao.location = \'/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S\'
                }>
            </body>';
        </Script>";
    }
}
?>