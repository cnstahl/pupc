<?php
include("./PL_includes/functions.php");

if ($_GET["code"] && $_GET["email"])
{
	$code = $_GET["code"];
	$email = $_GET["email"];
	verify($code, $email);
}

if (!logged_in())
	header("Location: welcome.php");

if (!validated() && logged_in())
	header("Location: sorry.htm");
	
if ($_GET["logout"])
{
	logout();
	header("Location: welcome.php");
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
			<section class="span4 logo" style="margin-top: 0px;">
                        <!-- <a href="http://mindfulprinceton.com/"><img src="http://mindfulprinceton.com/wp-content/uploads/2013/02/mh_logo.png" alt="PSPS" /></a> -->
                    </section>
                    <section class="span8">
                        <div class="navbar">
                            <ul class="nav">
                            	<li><a href="#">Hello, <?php echo htmlentities(get_username($_COOKIE["Plink_uid"]), ENT_QUOTES, 'UTF-8'); ?></a></li>
                                <li><a href="about.htm">What is PSPS?</a></li>
                                <li><a href="?logout=true">Logout</a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </section>
                </header>
                <section class="main-section row"  style="margin-top: 30px;">
                    <div class="span11 offset1" id="maintab">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#profile" data-toggle="tab">Profile</a></li>
                            <li><a href="#learn" data-toggle="tab">Learning Corner</a></li>
                            <li><a href="#connect" data-toggle="tab">Physics Connect</a></li>
                            <li><a href="#mentor" data-toggle="tab">Mentoring</a></li>
			    <li><a href="#messenger" data-toggle="tab">Ask</a></li>
                        </ul>
                        <div class="tab-content white-back">
                            <div class="tab-pane content-section active" id="profile">
                                <headings>
                                    <?php $text = isset($_GET["profile"]) ? "<a href=\"index.php#profile\"><h3 style=\"font-family: pacifico; text-decoration: underline; color: black;\">back to profile (view mode)</h3></a>" : '';
						echo $text;
					 ?>
                                </headings>
				    <?php include("./PL_includes/profile.php"); ?>
                            <div class="tab-pane content-section" id="learn">
                                <headings>
                                    <?php $text = isset($_GET["debate"]) ? "<a href=\"index.php#debates\"><h3 style=\"font-family: pacifico; text-decoration: underline; color: black;\">back to learning corner</h3></a>" : '';
						echo $text;
					 ?>
                                </headings>
				    <?php include("./PL_includes/learn.php"); ?>
                                <!--<div class="pagination pagination-centered">
                                    <ul>
                                        <li class="disabled"><a href="#">Prev</a></li>
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li><a href="#">4</a></li>
                                        <li><a href="#">5</a></li>
                                        <li><a href="#">Next</a></li>
                                    </ul>
                                </div>-->
                            </div>
                            <div class="tab-pane content-section" id="connect">
                                <headings>
                                    <?php $text = isset($_GET["question"]) ? "<a href=\"index.php#connect\"><h3 style=\"font-family: pacifico; text-decoration: underline; color: black;\">back to questions</h3></a>" : '';
						echo $text;
					 ?>
                                </headings>
								<?php include("./PL_includes/the_connection.php"); ?>
                                <!--<div class="pagination pagination-centered">
                                    <ul>
                                        <li class="disabled"><a href="#">Prev</a></li>
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li><a href="#">4</a></li>
                                        <li><a href="#">5</a></li>
                                        <li><a href="#">Next</a></li>
                                    </ul>
                                </div>-->
                            </div>
                            <div class="tab-pane content-section" id="mentor">
					<?php include("./PL_includes/pass_exchange.php"); ?>
                            </div>
                            <div class="tab-pane content-section" id="messenger">
                                <headings>
                                    <?php $text = isset($_GET["message"]) ? "<a href=\"index.php#messenger\"><h3 style=\"font-family: pacifico; text-decoration: underline; color: black;\">back to messages</h3></a>" : '';
						echo $text;
					 ?>
                                </headings>
								<?php include("./PL_includes/messenger.php"); ?>
                            </div>
                        </div>
                </section>
            </div>
            <div id="push"></div>
        </div>
        <div id="footer">
            <div class="container">
                <div class="row">
                    <div class="span8">
                        <p><a href="ToU.htm" id="ToU" style="margin-left:80px;">Privacy Policy and Terms of Use</a></p>
<!-- begin hit counter code <a href="http://hit-counter.info"><img style="border: 0px solid ; display: inline;" alt="tumblr hit counter" 
	src="http://hit-counter.info/hit.php?id=666083&counter=23"></a><br /><a href="http://hit-counter.info">hit counter</a>end hit counter code -->
</div>
                    <div class="span2 offset2">
                            <a href="about.htm" id="about" style="margin-left:100px;">About</a></p>
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
	<script type="text/javascript">
// Javascript to enable link to tab
var hash = document.location.hash;
var prefix = "tab_";
if (hash) {
    $('.nav-tabs a[href='+hash.replace(prefix,"")+']').tab('show');
} 

// Change hash for page-reload
$('.nav-tabs a').on('shown', function (e) {
    window.location.hash = e.target.hash.replace("#", "#" + prefix);
});
</script>
        <!--<script>
            $(document).ready(function(){
                $('#maintab a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                })
            })
        </script>-->
    </body>
</html>