<?
function descriptografia($string) {
	for($i = 0; $i < 5; $i++) $string = base64_decode(strrev($string));
	echo $string;
}
descriptografia('UFDczVlboNlYHpkcOZFZaNGbKVVVB1TP');
?>