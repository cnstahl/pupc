<?php
$email = "atsesis@gmail.com";
$to      = $email;
$subject = 'Princelink registration confirmation';
$message = "Hi bob! :D,<br /><br />Thank you for signing up at princelink. This week's theme is mental health aWith all the dogs in this morning world it is quite fascinating how the world works and I'm rather fond of it as this should be true for all the childtren in the world.nd we're bringing you personal stories from Princeton students in collaboration with the Princeton Mental Health Initiative. Also, don't forget to anonymously seek for help (especially in the area of this week's theme) at the fail wall; express anything. Hit the link below to begin debating, asking, and answering!<br /><br /><br />Thank you,<br />The Princelink team<br /><br />If you'd like to unsubscribe, go here:";
$headers = 'From: The Princelink Team <do-not-reply@princelink.com>' . "\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "List-Unsubscribe: http://princelink.com?action=unsubscribe&email=$email";
mail($to, $subject, $message, $headers);
?>