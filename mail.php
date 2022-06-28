<?php
// the message
$msg = "First line of text\nSecond line of text";

// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);
$headers = "From: support@jogo.ai" . "\r\n";
// send email
echo mail("shahzaib0791@gmail.com","My subject",$msg, $headers);
?>