<html>
<head>
<title>.:::: Calendário ::::.</title>
<link rel="STYLESHEET" href="../css/calendario.css" type="text/css">
<link rel="STYLESHEET" href="../css/layout.css" type="text/css">
<script language="JavaScript" src="../js/data.js"></script>
<script language="JavaScript">
/*
Dynamic Calendar II (By Jason Moon at http://www.jasonmoon.net)
Permission granted to Dynamicdrive.com to include script in archive
For this and 100's more DHTML scripts, visit http://dynamicdrive.com
*/

var ns6=document.getElementById&&!document.all
var ie4=document.all

var Selected_Month;
var Selected_Year;
//var Current_Date = new Date();
//alert(Current_Date)
//var Current_Month = Current_Date.getMonth();
var Current_Month = "<?=date('m') - 1;?>"
var Days_in_Month = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
var Month_Label = new Array('Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

//var Current_Year = Current_Date.getYear();
var Current_Year = "<?=date('Y');?>"

if (Current_Year < 1000)
Current_Year+=1900

//var Today = Current_Date.getDate();
var Today = "<?=date('d');?>"

function Header(Year, Month) {

   if (Month == 1) {
   Days_in_Month[1] = ((Year % 400 == 0) || ((Year % 4 == 0) && (Year % 100 !=0))) ? 29 : 28;
   }
   var Header_String = Month_Label[Month] + ' ' + Year;
   return Header_String;
}

function Make_Calendar(Year, Month) {
   var First_Date = new Date(Year, Month, 1);
   var Heading = Header(Year, Month);
   var First_Day = First_Date.getDay() + 1;
   if (((Days_in_Month[Month] == 31) && (First_Day >= 6)) ||
       ((Days_in_Month[Month] == 30) && (First_Day == 7))) {
      var Rows = 6;
   }else if ((Days_in_Month[Month] == 28) && (First_Day == 1)) {
      var Rows = 4;
   }else {
      var Rows = 5;
   }

   var HTML_String = '<table align=center><tr><td valign="top"><table BORDER=4 CELLSPACING=1 cellpadding=2 FRAME="box" BGCOLOR="C0C0C0" BORDERCOLORLIGHT="808080" align=center>';

   HTML_String += '<tr><th colspan=7 BGCOLOR="FFFFFF" BORDERCOLOR="000000">' + Heading + '</font></th></tr>';

   HTML_String += '<tr><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Dom</th><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Seg</th><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Ter</th><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Qua</th>';

   HTML_String += '<th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Qui</th><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Sex</th><th ALIGN="CENTER" BGCOLOR="FFFFFF" BORDERCOLOR="000000">Sab</th></tr>';

var Day_Counter = 1;
   var Loop_Counter = 1;
   for (var j = 1; j <= Rows; j++) {
      HTML_String += '<tr ALIGN="left" VALIGN="top">';
      for (var i = 1; i < 8; i++) {
         if ((Loop_Counter >= First_Day) && (Day_Counter <= Days_in_Month[Month])) {
            if ((Day_Counter == Today) && (Year == Current_Year) && (Month == Current_Month)) {
			<?
				if(!empty($chamar_funcao) && !empty($caixa_a)) {
			?>
				HTML_String += '<td BGCOLOR="FFFFFF" BORDERCOLOR="000000" style="cursor:hand" onclick="return retornar_data('+ Day_Counter +','+ Month +','+ Year +','+'<?=$tipo_retorno;?>'+','+'<?=$chamar_funcao;?>'+')"><strong><font color="red">' + Day_Counter + '</font></strong></td>';
			<?
				}else if(!empty($chamar_funcao) && empty($caixa_a)) {
			?>
				HTML_String += '<td BGCOLOR="FFFFFF" BORDERCOLOR="000000" style="cursor:hand" onclick="return retornar_data('+ Day_Counter +','+ Month +','+ Year +','+'<?=$tipo_retorno;?>'+','+'<?=$chamar_funcao;?>'+')"><strong><font color="red">' + Day_Counter + '</font></strong></td>';
			<?
				}else {
			?>
				HTML_String += '<td BGCOLOR="FFFFFF" BORDERCOLOR="000000" style="cursor:hand" onclick="return retornar_data('+ Day_Counter +','+ Month +','+ Year +','+'<?=$tipo_retorno;?>'+')"><strong><font color="red">' + Day_Counter + '</font></strong></td>';
			<?
				}
			?>
            }else {
			<?
				if(!empty($chamar_funcao)) {
			?>
				HTML_String += '<td BGCOLOR="FFFFFF" BORDERCOLOR="000000" style="cursor:hand" onclick="return retornar_data('+ Day_Counter +','+ Month +','+ Year +','+'<?=$tipo_retorno;?>'+','+'<?=$chamar_funcao;?>'+')">' + Day_Counter + '</td>';
			<?
				}else {
			?>
				HTML_String += '<td BGCOLOR="FFFFFF" BORDERCOLOR="000000" style="cursor:hand" onclick="return retornar_data('+ Day_Counter +','+ Month +','+ Year +','+'<?=$tipo_retorno;?>'+')">' + Day_Counter + '</td>';
			<?
				}
			?>
            }
            Day_Counter++;
         }else {
            HTML_String += '<td BORDERCOLOR="C0C0C0"> </td>';
         }
         Loop_Counter++;
      }
      HTML_String += '</tr>';
}
   HTML_String += '</table></td></tr></table>';
   cross_el=ns6? document.getElementById("Calendar") : document.all.Calendar
   cross_el.innerHTML = HTML_String;
}

function Check_Nums(event) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		if ((event.keyCode != 0) && (event.keyCode != 8) && !(event.keyCode >= 48 && event.keyCode <= 57)) {
			return false;
		}
	}else {
		if ((event.which != 0) && (event.which != 8) && !(event.which >= 48 && event.which <= 57)) {
			return false;
		}
    }

}

function On_Year() {
   var Year = document.when.year.value;
   if (Year.length == 4) {
      Selected_Month = document.when.month.selectedIndex;
      Selected_Year = Year;
      Make_Calendar(Selected_Year, Selected_Month);
   }
}

function On_Month() {
   var Year = document.when.year.value;
   if (Year.length == 4) {
      Selected_Month = document.when.month.selectedIndex;
      Selected_Year = Year;
      Make_Calendar(Selected_Year, Selected_Month);
   }else {
      alert('DIGITE UM ANO VÁLIDO !');
      document.when.year.focus();
   }
}

function Defaults() {
   if (!ie4&&!ns6)
   return
   var Mid_Screen = Math.round(document.body.clientWidth / 2);
   document.when.month.selectedIndex = Current_Month;
   document.when.year.value = Current_Year;
   Selected_Month = Current_Month;
   Selected_Year = Current_Year;
   Make_Calendar(Current_Year, Current_Month);
}

function Skip(Direction) {
   if (Direction == '+') {
      if (Selected_Month == 11) {
         Selected_Month = 0;
         Selected_Year++;
      }else {
         Selected_Month++;
      }
   }else {
      if (Selected_Month == 0) {
         Selected_Month = 11;
         Selected_Year--;
      }else {
         Selected_Month--;
      }
   }
   Make_Calendar(Selected_Year, Selected_Month);
   document.when.month.selectedIndex = Selected_Month;
   document.when.year.value = Selected_Year;
}

function retornar_data(dia, mes, ano, tipo_retorno, chamar_funcao) {
    if(dia < 10) dia = '0' + dia
    mes = mes + 1

    if(mes < 10) mes = '0' + mes

    var campo   = '<?=$campo;?>'
    elemento    = eval('opener.document.form.'+campo)
//Significa que o usuário quer apenas retornar a data em que clicou
    if(tipo_retorno == 1) {
        formar_data = dia + '/' + mes + '/' + ano
        elemento.value = formar_data
/*Retorna a quantidade de dias a partir do dia de hoje até a data
em que o usuário clicou*/
    }else {
        document.when.txt_data_formada.value = dia + '/' + mes + '/' + ano
        dias = diferenca_datas('document.when.txt_data_hoje','document.when.txt_data_formada')
        if(dias == false) dias = 0
        elemento.value = dias
    }

    if(chamar_funcao == 1) elemento.onclick()

/*Caixa auxiliar é uma variável que eu utilizo para executar uma determinada
função, supondo que eu tenho uma caixa por exemplo um hidden no formulário abaixo
do pop-up calendário para executar uma determinada função que está dentro desse
hidden*/
    var caixa_auxiliar = '<?=$caixa_auxiliar;?>'
    if(caixa_auxiliar != '') {
        elemento = eval('opener.document.form.'+caixa_auxiliar)
        elemento.onclick()
    }
    window.close()
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"></head>
<body bgcolor="#CCCCCC" onload="Defaults()">
<br>
<table align='center' width="80">
	<tr class="atencao">
		<td>
			<font size='-1' color="#000000">
				<b>CALENDÁRIO</b>
			</font>
		</td>
	</tr>
</table>
<table align='center'>
	<tr>
		<td>
			<div id=NavBar style="position:relative;width:175px;top:5px;">
				<form name="when">
					<table>
						<tr>
							<td width='16'>&nbsp;
								
							</td>
							<td>
								<input type="button" value=" &lt;&lt; " title="Voltar" onClick="Skip('-')" class="botao">
							</td>
							<td>
							</td>
							<td>
								<select name="month" onChange="On_Month()" class="combo">
									<script language="JavaScript1.2">
										if (ie4||ns6) {
											for (j=0;j<Month_Label.length;j++) {
												document.writeln('<option value=' + j + '>' + Month_Label[j]);
											}
										}
									</script>
								</select>
							</td>
							<td>
								<input type="text" name="year" size='5' maxlength='4' onKeyPress="return Check_Nums(event)" onKeyUp="On_Year()" class="caixadetexto" style="text-align:center">
							</td>
							<td>
							</td>
							<td>
								<input type="button" value=" &gt;&gt; " title="Avançar" onClick="Skip('+')" class="botao">
							</td>
						</tr>
					</table>
					<input type="hidden" name="txt_data_hoje" value="<?echo date('d/m/Y');?>">
					<input type="hidden" name="txt_data_formada">
				</form><p></p>
			</div>
			<div id=Calendar style="position:relative;width:238px;top:-2px;"></div>
		</td>
	</tr>
</table>
<table align='center' width="60">
	<tr class="atencao">
		<td>
            <input type="button" style="color:red" name="cmd_Fechar" value="Fechar" title="Fechar" class='botao' onclick="window.close()">
		</td>
	</tr>
</table>
</body>
</html>
