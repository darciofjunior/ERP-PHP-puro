<html>
<head>
<title>.:: Fixando Linha e Coluna que nem no Excel ::.</title>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../js/fixar_tabela.js'></Script>
</head>
<body topmargin='60'>
<table id='TabelaPrincipal' width='700' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <th>
            &nbsp;Vencidos até 30 dias &nbsp;
        </th>
        <th>
            &nbsp;01/12/2007&nbsp;
        </th>
        <th>
            &nbsp;02/12/2007&nbsp;
        </th>
        <th>
            &nbsp;03/12/2007&nbsp;
        </th>
    </tr>
    <?
        for($i = 0; $i < 100; $i++) {
    ?>
    <tr class='linhanormal'  align='right'>
        <td>LInha N.º <?=$i;?></td>
        <td>2</td>
        <td>3</td>
        <td>4</td>
    </tr>
    <?
        }
    ?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<Script Language = 'JavaScript'>
    if(typeof tableScroll == 'function') {
        tableScroll('TabelaPrincipal');
    }
</Script>
</body>
</html>