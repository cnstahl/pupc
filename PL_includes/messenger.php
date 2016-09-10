<?php
if (!$_GET['message'])
{
	echo "<center>";
	//Set question variables
	$to = $_GET["to"];
	$sub = $_GET["subject"];
	$message_submit = $_POST["submit"];
	$message = safe($_POST["message"], "sql");
	$subject = safe($_POST["subject"], "sql");
	$recipient = safe($_POST["recipient"], "sql");

	//Verify that the message was submitted
	if ($message_submit == "Send")
	{
		send_message($recipient, $subject, $message);
		$message_result = "message sent successfully!";
	}
	else
	{
		$ask_result = "Your message title or content is too long!";
	}
?>
<form method="post" id="greeting_block">
	<h1 class="greeting_step"></h1><input type="hidden" name="recipient" value="<?php echo $to; ?>" placeholder="Recipient" class="greeting_desc"></input>
	<h1 class="greeting_step"></h1><input type="text" name="subject" value="<?php echo $sub; ?>" placeholder="Subject" class="greeting_desc"></input>
	<h1 class="greeting_step"></h1><textarea name="message" placeholder="Message" class="greeting_desc"></textarea>
	<h1 class="greeting_step"></h1><input type="submit" name="submit" value="Send" class="home_buttons"></input>
</form>    
<?php
	if ($submit)
		echo $ask_result;
	//echo "<br />messages:<br />";
	display_inbox();
	echo "</center>";
}
else
{
	//Set answer variables
	$reply_submit = $_POST["reply_submit"];
	$reply = safe($_POST["content"], "sql");

	//Verify that the answer was submitted
	if ($reply_submit)
	{
		add_reply($_GET["message"], $content);
	}
	
	//Get message content
	$message_request = (int)$_GET["message"];
	$message = get_message($message_request);
	$title = $message[0];
	$content = $message[1];

	//Display message content
?>
                        <div class="content-section white-back">
                            <h4><?php echo $title; ?></h4>
                            <div class="well well-small">
                                <p><?php echo $content; ?></p>
                            </div>
                            <ul>
                                <li class="media">
                                    <div class="media-body">
					<?php
						//Display replies
						display_replies($_GET["message"]);
					?>
<h3 id="greeting">Reply:</h3>
<br />
<form method="post" id="greeting_block">  
	<textarea name="content" placeholder="Reply" class="greeting_desc"></textarea><br /><br />
	<input type="submit" name="reply_submit" value="Reply" class="home_buttons"></input>
</form>
                                    </div>
                                </li>
                            </ul>
                        </div>
					<?php
						}
					?>       
