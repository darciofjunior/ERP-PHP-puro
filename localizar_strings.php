<?
function localizar($str, $dir, $subpasta = 'N') {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($files = readdir($dh)) !== false) {
                if(filetype($dir.$files)!="dir") {
                    $ab = fopen($dir.$files,"r");
                    $le = fread($ab,filesize($dir.$files));
                    fclose($ab);
                    $name = explode(".",$files);
                        if(preg_match("/\b$str\b/i",$le)) {
                            echo "<br><font color='black' size=3>".$dir.$name[0].".".$name[1]."</font>";
                            $GLOBALS['listado']+=1;
                        }
                }else {//somente diretorios
                    if($subpasta == 'S') {
                        if($files != '.' && $files != '..') {
                            $diretorios[]=$dir.$files."/";//apesar do nome ele também exibi o nome do diretorio current
                        }
                    }
                }
            }
            for($i = 0; $i < count($diretorios); $i++) {
                localizar($str, $diretorios[$i], 'S');
            }
            closedir($dh);
        }
    }else {
        echo "Não é diretório válido";
    }
}

$id_funcionario="$id_funcionario"."=";
localizar('estornar_baixa_pi_estoque', "/var/www/erp/albafer/", 'S');
exit("<br><hr><br>Arquivo(s) Listado(s)=".$GLOBALS['listado']."<br>Fim do Script");
?>