<?php

include($_SERVER['DOCUMENT_ROOT'] . "/PL_includes/functions.php");

  /**
   * Sends an email to note that 
   * Note: this function assumes that the given email is SQL-safe.
   * @param string $email the given email address
   * @param string $year the given year
   */
function email_PUPC_confirmation($email, $site, $year)
{
  $name = get_name(get_id($email));
  $to      = $email;
  $subject = "PUPC $year registration confirmation: Onsite Exam";
  $message = "Hello $name,<br /><br />Thank you for registering for PUPC $year onsite test in $site! This e-mail confirms that you have successfully registered. ";
  //      $message .= "and your ID is 16<continent code><country ISO code><index from within country>."; 
  $message .= "We look forward to your participation!<br /><br />Best wishes,<br />PUPC Organizers";
  $headers = "From: PUPC Administrator <noreply@pupc.princeton.edu>\n";
  $headers .= "Content-type: text/html; charset=iso-8859-1\n";
  $headers .= "X-Mailer: PHP/" . phpversion() . "\n";
  $headers .= "List-Unsubscribe: http://pupc.princeton.edu/?action=unsubscribe&email=$email";
  mail($to, $subject, $message, $headers);
}

email_PUPC_confirmation('cnstahl@princeton.edu', 'Princeton', '2017')

?>