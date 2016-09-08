<html>
<head>
<title>Statistics</title>
</head>
<body>
<?php
include("./PL_includes/functions.php");
/*
Stats by Andrew
*/

//authenticate
if (administrator())
{
  //Create a connection with the MySQL server

  //Select the wordpress database
  mysql_select_db('db437533910', $con) or die(mysql_error());

  //Set the date
  date_default_timezone_set('America/New_York');
  $date = date("M d, g:ia");
  
  //Reset
  if ($_POST["submit"] == "submit")
  {
    if ($_POST["reset"] == "true")
    {
      mysql_query("UPDATE statistics SET site_views=0");
      mysql_query("UPDATE statistics SET indiv_views=0");
      mysql_query("UPDATE statistics SET date='$date'");
      mysql_query("TRUNCATE TABLE visitors");
    }
  }

  //Get total site views
  $site_views = mysql_fetch_array(mysql_query("SELECT site_views FROM `statistics`"));
  $indiv_views = mysql_fetch_array(mysql_query("SELECT indiv_views FROM `statistics`"));
  $as_of = mysql_fetch_array(mysql_query("SELECT date FROM `statistics`"));

  //Print header
  echo "<h1>Site Statistics</h1>";

  //Print total site views
  echo "Total Site Views as of $as_of[0]: $site_views[0]<br>";

  //Print unique site views
  echo "Total Unique Views as of $as_of[0]: $indiv_views[0]";
?>
<br><br>
<center style="margin-right:100px"><u>Visitors</u></center>
<table border="1">
<tr><th>IP</th><th>User Agent</th><th>Referer</th><th>Location</th><th>URI</th><th>Date</th></tr>
<?php
  //Display individual visitors
  $get_visitors = mysql_query("SELECT * FROM visitors");
  while ($visitor = mysql_fetch_array($get_visitors, MYSQL_NUM))
  {
    echo "<tr><td>$visitor[0]</td><td>$visitor[1]</td><td>$visitor[2]</td><td>$visitor[3]</td><td>$visitor[5]</td><td>$visitor[4]</td></tr>";
  }
?>
</table>
<form method="post">
Reset: <input type="checkbox" name="reset" value="true"></input><br>
<input type="submit" name="submit" value="submit"></input>
</form>
<hr>
<?php
}

else
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Error 404 - Not found</title>
	<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
	<script type="text/javascript" language="JavaScript" src="http://img.sedoparking.com/registrar/dopark.js"></script>
</head>
	<body>
		<script type="text/javascript">
			var reg = '1und1parking4';
			document.write( '<scr'+'ipt type="text/javascript" language="JavaScript"' + 'src="http://sedoparking.com/' + window.location.host + '/' + reg + '/park.js">' + '<\/scr' + 'ipt>' );
		</script>
	</body>
</html>
<?php
}
?>
	</body>
</html>