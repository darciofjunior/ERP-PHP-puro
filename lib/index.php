<?
	$mensagem[1]   = 'Acesso restrito, usu�rio n�o logado';
	$mensagem[2]   = 'N�o h� registros a serem consultados';
	$mensagem[3]   = 'N�o h� registros a serem alterados';
	$mensagem[4]   = 'N�o h� registros a serem exclu�dos';
	$mensagem[5]   = 'Usu�rio, sem permiss�o ao endere�o da p�gina';
	$mensagem[6]   = 'Administrador do sistema, j� existente';
	$mensagem[7]   = 'Administrador do sistema, incluso com sucesso';
	$mensagem[8]   = 'Registro danificado ou exclu�do';
	$mensagem[9]   = 'Login ou Institui��o Inv�lidos!';
	$mensagem[10]  = 'Senha Inv�lida !';
	$mensagem[11]  = 'Acesso restrito, aluno n�o logado';
	$mensagem[12]  = 'N�o, h� cont�udo nesta mat�ria';
	$mensagem[13]  = 'N�o, h� provas nesta mat�ria';
	$mensagem[14]   = 'Empresa j� existente';
	$mensagem[15]   = 'N�o h� mapas cadastrados';
	$mensagem[16]   = 'N�o h� produtos acabados cadastrados';
	$mensagem[17]   = 'N�o h� prestadores de servi�os cadastrados';
	$mensagem[18]   = 'N�o pode ser completado devido a depend�ncia de outros cadastros';
	$mensagem[19]   = 'Todos funcion�rios j� cadastrados';
	$mensagem[20]   = 'N�o h� funcion�rios cadastrados';
	$mensagem[21]   = 'N�o h� eventos cadastrados';
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
