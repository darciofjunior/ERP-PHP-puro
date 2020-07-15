<?
class gerar_arquivo {
    function arquivo($nome_arquivo, $extensao_file) {
        switch($extensao_file) {
            case 'doc': //Word ...
                $ctype = 'application/msword';
            break;
            case 'xls'://Excel ...
            case 'xlsx':
                $ctype = 'application/vnd.ms-excel';
            break;
            case 'ppt'://Power Point ...
                $ctype = 'application/vnd.ms-powerpoint';
            break;
            /*
             * As demais opções por enquanto não estão funcionando ...
             * 
            case 'pdf': $ctype = 'application/pdf'; break;
            case 'exe': $ctype = 'application/octet-stream'; break;
            case 'zip': $ctype = 'application/zip'; break;
            case 'gif': $ctype = 'image/gif';break;
            case 'png': $ctype = 'image/png';break;
            case 'jpeg':
            case 'jpg': 
                    $ctype = 'image/jpg';
            break;
            case 'mp3': $ctype = 'audio/mpeg'; break;
            case 'wav': $ctype = 'audio/x-wav'; break;
            case 'mpeg':
            case 'mpg':
            case 'mpe': 
                    $ctype = 'video/mpeg'; 
            break;
            case 'mov': $ctype = 'video/quicktime'; break;
            case 'avi': $ctype = 'video/x-msvideo'; break;*/
            default:
                exit('TIPO DE ARQUIVO INVÁLIDO !');
            break;
        }
        header('Content-type: "'.$ctype.'"');
        header('Content-type: application/force-download');
        $filename = $nome_arquivo.'.'.$extensao_file;
        header('Content-Disposition: attachment; filename = "'.$filename.'"');
        header('Pragma: no-cache');
    }
}
?>