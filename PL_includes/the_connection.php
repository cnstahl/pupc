<?php
if (!$_GET['question'])
{
	echo "<center>";
	//Set question variables
	$question_submit = $_POST["submit"];
	$question = safe($_POST["question"], "sql");
	$content = safe($_POST["content"], "sql");

	//Verify that the question was submitted
	if ($question_submit == "Ask")
	{
		add_question($question, $content);
		$ask_result = "Question added successfully!";
	}
	else
	{
		$ask_result = "Your question title or content is too long!";
	}
?>
Please use the <a href="#Comments">Comments</a> section below to post comments with regard to the interaction among physics-minded undergraduates at Princeton or anything related to your experience as a physics concentrator, or, for those still deciding on a major, anything you would like to know firsthand. Remember, only users with a valid princeton.edu account can view this page, so you should not worry about random strangers on the web seeing this conversation. We would like to thank you in advance for your participation!
<br>
<a name="Comments"></a><!-- begin htmlcommentbox.com -->
 <div id="HCB_comment_box"><a href="http://www.htmlcommentbox.com">HTML Comment Box</a> is loading comments...</div>
 <script type="text/javascript" language="javascript" id="hcb"> /*<!--*/ if(!window.hcb_user){hcb_user={};} hcb_user.PAGE="http://psps.mycpanel.princeton.edu/index.php#tab_connect";(function(){s=document.createElement("script");s.setAttribute("type","text/javascript");s.setAttribute("src", "http://www.htmlcommentbox.com/jread?page="+escape((window.hcb_user && hcb_user.PAGE)||(""+window.location)).replace("+","%2B")+"&mod=%241%24wq1rdBcg%24gINTT/y8rneQopZRgNDdN/"+"&opts=478&num=10");if (typeof s!="undefined") document.getElementsByTagName("head")[0].appendChild(s);})(); /*-->*/ </script>
<!-- end htmlcommentbox.com -->
<!--
<form method="post" id="greeting_block">  
	<h3 class="greeting_step"></h1><input type="text" name="question" placeholder="Question Title" class="greeting_desc"></input>
	<h3 class="greeting_step"></h1><textarea name="content" placeholder="Question" class="greeting_desc"></textarea>
	<h3 class="greeting_step"></h1><input type="submit" name="submit" value="Ask" class="home_buttons"></input>
</form>    
<?php
	if ($submit)
		echo $ask_result;
	//echo "<br />Questions:<br />";
	display_recent_answers();
	echo "<hr />";
	display_questions();
	echo "</center>";
}
else
{
	//Set answer variables
	$answer_submit = $_POST["submit"];
	$answer = safe($_POST["content"], "sql");

	//Verify that the answer was submitted
	if ($answer_submit)
	{
		add_answer($answer, $content);
	}
	
	//Get question content
	$question_request = safe($_GET["question"], "sql");
	$question = get_question($question_request);
	$title = $question[0];
	$content = $question[1];

	//Display question content
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
						//Display answers
						display_answers($_GET["question"]);
					?>
<h3 id="greeting">Leave an answer:</h3>
<br />
<form method="post" id="greeting_block">  
	<textarea name="content" placeholder="Answer" class="greeting_desc"></textarea><br /><br />
	<input type="submit" name="submit" value="Answer" class="home_buttons"></input>
</form>
                                    </div>
                                </li>
                            </ul>
                        </div>-->
					<?php
						}
					?>       
