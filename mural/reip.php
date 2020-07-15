<?
require('../lib/segurancas.php');

$conteudo_site  = file_get_contents('http://www.grupoalbafer.com.br/interno/reip.php');
$ip_externo     = strip_tags(strrchr($conteudo_site, ': '));
$ip_externo     = str_replace(': ', '', $ip_externo).':5000';
//Aqui eu atualizo o IP Externo na tabela de "Empresas" no caso da Albafer apenas ...
$sql = "UPDATE `empresas` SET `ip_externo` = '$ip_externo' WHERE `id_empresa` = '1' LIMIT 1 ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('REIP ALTERADO COM SUCESSO !!!')
    parent.html5Lightbox.finish()
</Script>