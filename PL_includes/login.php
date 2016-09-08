<?php
	//Set login variables
	$login_submit = $_POST["submit"];
	$login_name = safe($_POST["email"], "sql");
	$login_pass = $_POST["pass"];

	//Verify that the login was submitted and that the username & password are correct 
	if ($login_submit && valid_login($login_name, $login_pass))
	{
		login($login_name, $login_pass);
		$login_result = "Logged in successfully!";
		header("Location: index.php");
	}
	else
	{
		$login_result = "Invalid e-mail/password combination!";
	}
?>
                        <form method="post" class="form-inline">
                            <input type="text" name="email" placeholder="Email" class="input-small" />
                            <input type="password" name="pass" placeholder="Password" class="input-small" />
                            <input type="submit" name="submit" class="btn btn-inverse" value="Sign in" />
                        </form>
			   <a href="reset.php"><h6 style="margin-left:2px;">Forgot your password?</h6></a>
<?php
if ($login_submit)
	echo $login_result;
?>