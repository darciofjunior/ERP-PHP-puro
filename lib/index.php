<?
	$mensagem[1]   = 'Acesso restrito, usuário não logado';
	$mensagem[2]   = 'Não há registros a serem consultados';
	$mensagem[3]   = 'Não há registros a serem alterados';
	$mensagem[4]   = 'Não há registros a serem excluídos';
	$mensagem[5]   = 'Usuário, sem permissão ao endereço da página';
	$mensagem[6]   = 'Administrador do sistema, já existente';
	$mensagem[7]   = 'Administrador do sistema, incluso com sucesso';
	$mensagem[8]   = 'Registro danificado ou excluído';
	$mensagem[9]   = 'Login ou Instituição Inválidos!';
	$mensagem[10]  = 'Senha Inválida !';
	$mensagem[11]  = 'Acesso restrito, aluno não logado';
	$mensagem[12]  = 'Não, há contéudo nesta matéria';
	$mensagem[13]  = 'Não, há provas nesta matéria';
	$mensagem[14]   = 'Empresa já existente';
	$mensagem[15]   = 'Não há mapas cadastrados';
	$mensagem[16]   = 'Não há produtos acabados cadastrados';
	$mensagem[17]   = 'Não há prestadores de serviços cadastrados';
	$mensagem[18]   = 'Não pode ser completado devido a dependência de outros cadastros';
	$mensagem[19]   = 'Todos funcionários já cadastrados';
	$mensagem[20]   = 'Não há funcionários cadastrados';
	$mensagem[21]   = 'Não há eventos cadastrados';
?>
<html>
<head>
<title>.::<?echo $mensagem[$valor];?>::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Cache-Control" content="no-store">
<meta http-equiv="Pragma" content="no-cache">
<link rel="STYLESHEET" type="text/css" href="../css/layout.css">
</head>
<body bgcolor="#FFFFFF" text="#000000" topmargin="100">
<table width="740" border="0" cellspacing="1" cellpadding="1" align="center">
  <tr class="linhacabecalho">
    <td width="369"> 
      <div align="center">ALBAFER - <?echo date("d/m/Y H:i:s");?></div>
    </td>
    <td width="370">
      <div align="center">ALBAFER</div>
    </td>
  </tr>
  <tr>
    <td width="369">
      <div align="center" class="atencao"><?echo $mensagem[$valor];?>.</div>
    </td>
    <td width="370">
      <div align="center"><img src="../imagem/logosistema.jpg" width="138" height="145"></div>
    </td>
  </tr>
</table>
</body>
</html>
