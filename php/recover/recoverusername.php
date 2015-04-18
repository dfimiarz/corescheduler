<?php

include_once '../includes/recaptchalib.php';

session_start();

$err_msg = "" ;
$err_msg_html = "";



if( isset($_SESSION['err_msg']))
{
	$err_msg = $_SESSION['err_msg'];
	$err_msg_html = '<div id="password_rec_error" class="alert alert-danger">' . $err_msg . '</div>';
}


?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Password recovery page</title>

<link href="../../css/recoverpassword.css" rel="stylesheet" type="text/css" />
<link href="../../css/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css" />

<script language="javascript" type="text/javascript" src="../../jquery/jquery-1.10.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../../jquery/jquery-ui-1.10.3.recovery_page.min.js" /></script>
<script language="javascript" type="text/javascript" src="../../js/bootstrap/bootstrap.min.js"></script>
<script type="text/javascript">
	var RecaptchaOptions = {
	   theme : 'white'
	};
</script>
</head>
<body>
<div class="page_element" id="view_port">
    <div id="calendar_view">
		<div class="calendar_view_element">
			<table id="top_panel_container">
				<tr>
					<td>
						<div id="info" class="corecal_gen_container navigation_panel">
							<div id="logo_container">
								<img src="../../images/logo.png"/>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
        <div class="calendar_view_element" id="account_recovery_pannel">
        	<div id="page_intro_lbl">Recover your username</div>
        	<form method="POST" action="doLoginReminder.php" autocomplete="off">
        		<div class="pass_recovery_msg">
					<?php
						if( ! empty($err_msg_html) )
							echo $err_msg_html;
					?>
				</div>
				<div class="pass_rec_cont">
					<label for="emailinput">E-mail address associated with your account:</label>
					<input type='text' name="email" value="" id="emailinput" maxlength="64" class="form-control" placeholder="Your e-mail:"/>
				</div>
				<div class="pass_rec_cont">
					<label for="unameinput">Please type the text displayed below:</label>
					<?php

	          			$publickey = "6LdXAgMTAAAAAKTWhD-wTOND6tOeiAfyzjx6LN5i";
	         			echo recaptcha_get_html($publickey);
	         		?>
				</div>
				<div class="pass_rec_cont">
					<button type="submit" class="btn btn-primary">Recover my username</button>
				</div>
			</form>
			<div id="credits">
	         	Developed and maintained by: <a href="http://forum.sci.ccny.cuny.edu/people/science-division-directory/danielf">Daniel Fimiarz</a>, The City College of New York, 160 Convent Ave, MR 1328, New York, NY 10031.
	        </div>
        </div>

    </div>
</div>
</body>
</html>
