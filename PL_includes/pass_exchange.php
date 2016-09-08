<center>
<?php
	//Set pass exchange variables
	$pass_submit = $_POST["pass_submit"];

	//Verify that the question was submitted
	if ($pass_submit == "submit")
	{
		$subject = $_POST["subject"];
		$date = $_POST["date"];
		$quantity = $_POST["quantity"];
		$type = $_POST["type"];

		add_pass($subject, $date, $quantity, $type);
		$pass_result = "Request posted successfully!";
	}
?>
<form method="post" id="greeting_block">  
	<h3 class="greeting_step"></h1><input type="text" name="subject" placeholder="Which topic(s)?" class="greeting_desc"></input>
	<h3 class="greeting_step"></h1><input type="text" name="date" placeholder="When? (YEAR-MO-DY HR:MN)" class="greeting_desc"></input>
	<h3 class="greeting_step"></h1><input type="text" name="quantity" placeholder="How many students?" class="greeting_desc"></input>
	<h3 class="greeting_step"></h1><select name="type" class="greeting_desc"><option>Mentor Offer or Request?</option><option value="1">I request to mentor me</option><option value="2">I offer to mentor someone</option></select>
	<h3 class="greeting_step"></h1><input type="submit" name="pass_submit" value="submit" class="home_buttons"></input>
</form>
</center>
<h3>Requests</h3>
<ul class="unstyled">
<?php display_requests(); ?>
</ul>
<h3>Offers</h3>
<ul class="unstyled">
<?php display_offers(); ?>
</ul>
<hr />