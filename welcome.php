<?php include("./PL_includes/functions.php"); ?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
    <head>
        <meta charset="utf-8">
        <title>Princeton Society of Physics Students - Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
	<meta name="google-site-verification" content="mSDgra6HCSqvAWHdSC6QHX2R-pg7RC8ToL8G4Zw2a3Y" />
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
                        <!--<a href="/index.php" ><img src="img/logo.png" alt="PSPS" /></a>-->

<table border="0" cellpadding="0" cellspacing="0" width="130">
      <tbody>
        <tr>
          <td width="200">
            <div class="pspslogo"> <a href="/index.php"><img src="img/logo.png"
                height="125" hspace="0" vspace="0" width="170"> </div>
          </td>
        </tr>
      </tbody>
</table>
            


                    </section>
			<section class="span4 logo" style="margin-top: 35px;">
                    </section>
			<section class="span4 logo" style="margin-top: 20px;">
                    <?php include("./PL_includes/login.php"); ?>
			</section>
                </header>
                <section class="welcome-section row">
                    <article class="span5 offset1" id="welcome">
                        <p>&nbsp;</p>
                        <p><span class="pacifico"><font color="dark-orange">Learning</font> Corner: </span>
                        watch lectures or read content posted by the PSPS officers and respond to it.</p>
                        <p><span class="pacifico">Physics <font color="dark-orange">Connect</font>: </span>
                        interact with other Princeton physics-minded students by discussing the PSPS, the physics department, or physics in general.</p>
                        <p><span class="pacifico"><font color="dark-orange">Mentoring</font> Set-up: </span>
                        offer or request from your peers free mentoring sessions, in accordance with University regulations.</p>
                        <p><span class="pacifico">Register to gain access: </span> <a href="./register.php" class="btn btn-inverse">Register</a></p>
                    </article>
                    <div class="span5 offset1">
                        <img src="img/slides/jadwin.jpg" alt="PSPS" />
                    </div>
                </section>
            </div>
            <div id="push"></div>
        </div>
        <div id="footer">
            <div class="container">
                <div class="row">
                <div class="span8">
                    <p><a href="ToU.htm" id="ToU">Privacy Policy and Terms of Use</a></p>
                </div>
                <div class="span2 offset2">
                <a href="about.htm" id="about">About</a></p>
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