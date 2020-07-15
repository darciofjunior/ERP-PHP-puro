<?
require('segurancas.php');
require('comunicacao.php');

if(!empty($_GET['id_orcamentos_para_enviar_email'])) {
    /*Busco o e-mail de cada cliente p/ enviar em seu e-mail o PDF que foi gerado acima desse 
    id_orcamento_venda do Loop ...*/
    $sql = "SELECT c.`id_cliente`, IF(c.`id_pais` = 31, 'R$ ', 'U$ ') AS tipo_moeda, 
            IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, 
            c.`email`, cc.`nome`, ov.`id_orcamento_venda`, 
            DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, ov.`valor_orc`, ov.`mala` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = ov.`id_cliente_contato` 
            WHERE ov.`id_orcamento_venda` IN ($_GET[id_orcamentos_para_enviar_email]) ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        /*Aqui eu busco o "Representante" e o email do "Representante" deste Cliente 
        do "Orçamento de Vendas" do Loop ...*/
        $sql = "SELECT DISTINCT(ovi.`id_representante`) AS id_representante, r.`nome_fantasia`, r.`email` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `representantes` r ON r.`id_representante` = ovi.`id_representante` 
                WHERE ovi.`id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' ";
        $campos_representante = bancos::sql($sql);
        $linhas_representante = count($campos_representante);
        if($linhas_representante > 0) {//Encontrou, então envio um e-mail como Lembrete de Vendas para este ...
            for($j = 0; $j < $linhas_representante; $j++) {
                //Verifico se esse Representante do Loop é um Funcionário ...
                $sql = "SELECT f.`email_externo` 
                        FROM `representantes_vs_funcionarios` rf 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                        WHERE rf.`id_representante` = '".$campos_representante[$j]['id_representante']."' LIMIT 1 ";
                $campos_funcionario = bancos::sql($sql);
                if(count($campos_funcionario) == 1) {//Funcionário ...
                    $copia.=            $campos_funcionario[0]['email_externo'].'; ';
                    $representante.=    $campos_representante[$j]['nome_fantasia'].'; ';
                }else {//Autônomo ...
                    $copia.=            $campos_representante[$j]['email'].'; ';
                    $representante.=    $campos_representante[$j]['nome_fantasia'].'; ';
                }
            }
            //Verifico se esse 1º Repres. que foi encontrado acima, tem um Supervisor e se este possui email ...
            $sql = "SELECT r.`email` 
                    FROM `representantes_vs_supervisores` rs 
                    INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` AND r.`email` <> '' 
                    WHERE rs.`id_representante` = '".$campos_representante[0]['id_representante']."' LIMIT 1 ";
            $campos_supervisor = bancos::sql($sql);
            if(count($campos_supervisor) == 1) {//Se sim, envio um e-mail de Lembrete de Vendas para este também ...
                $copia.= $campos_supervisor[0]['email'].'; ';
            }
        }
        $representante  = substr($representante, 0, strlen($representante) - 2);
        
        $assunto    = 'ORÇAMENTO N.º '.$campos[$i]['id_orcamento_venda'].' / '.$campos[$i]['tipo_moeda'].number_format($campos[$i]['valor_orc'], 2, ',', '.').' / '.$representante.' ('.$campos[$i]['cliente'].') EM ABERTO - GRUPO ALBAFER';
        $mensagem   = '<font size="4"><b>Olá '.strtoupper($campos[$i]['nome']).' !</b></font><p/>';
        
        if($campos[$i]['mala'] == 1) {//1º Email de Mala Direta sendo Enviado ...
            $mensagem.= "<img src='http://www.grupoalbafer.com.br/imagens/mala1.jpg'>";
        }else if($campos[$i]['mala'] == 2) {//2º Email de Mala Direta sendo Enviado ...
            $mensagem.= "<img src='http://www.grupoalbafer.com.br/imagens/mala2.jpg'>";
        }else if($campos[$i]['mala'] == 3) {//3º Email de Mala Direta sendo Enviado ...
            $mensagem.= "<img src='http://www.grupoalbafer.com.br/imagens/mala3.jpg'>";
        }
        $copia.= 'rivaldo@grupoalbafer.com.br; ';//P/ que o Rivaldo possa estar interagindo com o Cliente de modo a tentar fechar negociação ...
        $copia = substr($copia, 0, strlen($copia) - 2);
        
        $mensagem.= '<font size="5" color="red"><b>DISPONIBILIDADE SUJEITA A CONSULTA DE ESTOQUE.</b></font>';
        comunicacao::email('wilson.nishimura@grupoalbafer.com.br', $campos[$i]['email'], $copia, $assunto, $mensagem, 'darcio@grupoalbafer.com.br; wilson@grupoalbafer.com.br', '../pdf/Orcamento_Grupo_Albafer_'.$campos[$i]['id_orcamento_venda'].'.pdf', 'Orcamento_Grupo_Albafer_'.$campos[$i]['id_orcamento_venda'].'.pdf');
        //Deleto essas variáveis p/ não acumular valores do Loop Anterior ...
        unset($copia);
        unset($representante);
    }
?>
<Script Language = 'JavaScript'>
    window.close()
</Script>
<?}?>