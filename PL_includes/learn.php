<?php
if (administrator() && !$_GET["debate"])
{
	//Set debate variables
	$debate_submit = $_POST["debate_submit"];
	$description = safe($_POST["description"], "sql");
	$title = safe($_POST["title"], "sql");
	$bio = safe($_POST["bio"], "sql");
	$video_id = safe($_POST["video_id"], "sql");

	//Verify that the content for debate was submitted
	if ($debate_submit == "Submit")
	{
		add_debate($title, $description, $video_id, $bio);
		$ask_result = "Content for debate added successfully!";
	}
	else
	{
		$ask_result = "Your title or content is too long!";
	}
?>
<h1 id="greeting">Learning/Debate Corner</h1>
<br />
<form method="post" id="greeting_block">  
	<h1 class="greeting_step">debate title --> </h1><input type="text" name="title" class="greeting_desc"></input><br />
	<h1 class="greeting_step">debate description --> </h1><textarea name="description" class="greeting_desc"></textarea><br />
	<h1 class="greeting_step">debate video-id --> </h1><input type="text" name="video_id" class="greeting_desc" /><br />
	<h1 class="greeting_step">debate bio --> </h1><textarea name="bio" class="greeting_desc"></textarea><br />
	<h1 class="greeting_step">submit --> </h1><input type="submit" name="debate_submit" value="Submit" class="home_buttons"></input>
</form>
<?php
	if ($submit)
	{
		echo $debate_result;
	}
	echo "<br />debates:<br />";
	display_debates();
}
elseif (!administrator() && !$_GET["debate"])
{
	//echo "<br />debates:<br />";
	display_debates();
}
else
{
	//Set answer variables
	$response_submit = $_POST["submit"];
	$response = safe($_POST["content"], "sql");
	$yn = $_POST["yn"] == "agree" ? 1 : 2;

	//Verify that the answer was submitted
	if ($response_submit)
	{
		add_response($response, $yn);
	}
	
	//Get debate content
	$debate_request = safe($_GET["debate"], "sql");
	$debate = get_debate($debate_request);
	$title = $debate[0];
	$content = $debate[1];
	$video_id = $debate[2];
	$bio = $debate[3];

	//Display debate content
	echo "<u><h2>$title</h2></u>\n$content\n<br />\n<div class=\"well well-small\">\n<iframe width=\"560\" height=\"315\" src=\"http://www.youtube.com/embed/$video_id\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe>\n<br />\n$bio\n<br />\n</div>\n";
?>
<h3 id="greeting">What do you think?</h3>
<form method="post" id="greeting_block">  
	<textarea name="content" placeholder="Response" rows="4" cols="150" class="greeting_desc"></textarea><br />
	<!--Agree <input type="checkbox" name="yn" value="agree"> Disagree <input type="checkbox" name="yn" value="disagree"><br/>-->
	<input type="submit" name="submit" value="Respond" class="home_buttons"></input>
</form>
<!--<span class="agree">Agreements</span><span class="disagree">Disagreements</span>-->
<?php
	//Display responses
	display_responses($_GET["debate"]);
}
?>