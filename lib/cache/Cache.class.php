<?php
class Cache {
    const PASTA_CACHE   = '/var/www/erp/albafer/lib/cache/cache_files/';//Pasta onde o cache irá salvar, é bom colocar o caminho todo "/var/www/erp" ...
    const MODE          = 777;//Permissão pra Salvar ...
    public $file, $time;

    public function  __construct($file, $time = 84600) {//84600 segundos = 1 dia ...
        if($file && $time) {
            /*if(chmod(self::PASTA_CACHE, 0755)) {
                echo 'permisao dada com sucesso';
                exit;
            }*/
            $this->file = self::PASTA_CACHE.$file.'.html';
            $this->time = $time;
            if($time == 'infinito') {
                if(file_exists($this->file)) {
                    $this->Check_cache = true;
                }else {
                    $this->Check_cache = false;
                }
            }else {
                if(file_exists($this->file) && (time() - $this->time) < filemtime($this->file)) {
                    $this->Check_cache = true;
                }else {
                    $this->Check_cache = false;
                }
            }
        }else {
            exit('PARÂMETROS INVÁLIDOS !');
        }
    }

    public function Start_cache() {
        if($this->Check_cache) {
            include_once $this->file;
            return false;
        }else {
            ob_start();
            echo "<!-- CACHE-FILE: ".date('d-m-Y H:i:s')." ({$this->time})  -->";
            return true;
        }
    }

    public function End_cache() {
        if($this->Check_cache) {
            return false;
        }else {            
            echo "<!-- CACHE-FILE: ".date('d-m-Y H:i:s')." ({$this->time})  -->";                        
            self::Salve_cache();
        }
    }

    private function Salve_cache() {
        $fp = fopen($this->file, 'w');  
        if(is_writable($this->file)) {
            fwrite($fp, ob_get_contents());
            fclose($fp);
        }else {
            chmod($this->file, self::MODE);
            if(is_writable($this->file)) {
                fwrite($fp, ob_get_contents());
                fclose($fp);
            }else {
                exit('ERRO AO ESCREVER O ARQUIVO ...');
            }
        }
    }

    public function Limpa_cache() {
        if(is_file($this->file)) unlink($this->file);
    }
}