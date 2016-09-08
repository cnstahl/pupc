<?php
//include prerequisite file
include("./PL_includes/functions.php");

//Set registration variables
$submit = $_POST["submit"];
$pass = $_POST["pass"];
$email = safe($_POST["email"], "sql");
$usernm = safe($_POST["usernm"], "sql");

//Verify that the registration form was submitted and that the email & password are correct 
if ($submit && valid_registration($pass, $email))
{
	register($pass, $email);
	$register_result = "Registered successfully!";
}
else
{
	$register_result = registration_error($pass, $email);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Princeton Society of Physics Students - Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <!-- CSS -->
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/styles.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Pacifico' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Arvo:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
        <!--[if IE 7]>
          <link href="css/bootstrap_ie7.css" rel="stylesheet">
        <![endif]-->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Fav and touch icons -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
        <link rel="shortcut icon" href="ico/favicon.ico">
    </head>
    <body>
        <div id="wrap">
            <div class="container">
                <header class="main-header row">
                    <section class="span4 logo">
                        <a href="/index.php"><img src="img/logo.png" alt="Princelink" /></a>
                    </section>
                    <section class="span8">
                        <div class="navbar">
                            <ul class="nav">
                                <li><a href="#">Profile - $email</a></li>
                                <li><a href="about.htm">What is the PSPS Portal?</a></li>
                                <li><a href="?logout=true">Logout</a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </section>
                </header>
                <section class="main-section row">
 			<div id="greeting_block" style="border-color: block; border-width: 2px;">
	<h1 style="font-family: pacifico; text-decoration: underline; color: black;"><u>Registration</u></h1>
	<br />
	<form method="post" id="greeting_block">  
	<input type="text" name="usernm" placeholder="username" class="greeting_desc"></input><br /><br />
	<input type="text" name="email" placeholder="email" class="greeting_desc"></input><br /><br />
	<input type="password" name="pass" placeholder="password" class="greeting_desc"></input><br /><br />
	<input type="submit" name="submit" value="Register" class="home_buttons"></input></h1>
	</form>
	<?php
if ($submit)
	echo $register_result;
?>
			</div>
        </div>
        <div id="footer">
            <div class="container">
                <div class="row">
                    <div class="span8">
                        <p><a href="?page=ToU" id="ToU">Privacy Policy and Terms of Use</a></p>
                    </div>
                    <div class="span2 offset2">
                        <p><a href="?page=feedback" id="footer_feedback">Feedback</a>
                            <a href="?page=about" id="about">About</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap-transition.js"></script>
        <script src="js/bootstrap-alert.js"></script>
        <script src="js/bootstrap-modal.js"></script>
        <script src="js/bootstrap-dropdown.js"></script>
        <script src="js/bootstrap-scrollspy.js"></script>
        <script src="js/bootstrap-tab.js"></script>
        <script src="js/bootstrap-tooltip.js"></script>
        <script src="js/bootstrap-popover.js"></script>
        <script src="js/bootstrap-button.js"></script>
        <script src="js/bootstrap-collapse.js"></script>
        <script src="js/bootstrap-carousel.js"></script>
        <script src="js/bootstrap-typeahead.js"></script>
        <script>
            $(document).ready(function(){
                $('#maintab a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                })
            })
        </script>
    </body>
</html>