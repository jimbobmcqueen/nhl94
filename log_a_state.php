<?php

require_once("comfunc.php");

/*********************************************************************/

function printLogo20($lg, $city, $nick) {
	
	$dir = ChkLogos($lg);	
	$imgnick = strtolower(substr($city,0,3)). '_'. strtolower(str_replace(" ", "_", $nick));
	return ('<img src="/images/'. $dir. '/logos20/'. $imgnick. 
		'" alt="'. $nick. '" width=20 >');
		
} // end of function

function printLogo100($lg, $city, $nick) {
	
	$dir = ChkLogos($lg);	
	$imgnick = strtolower(substr($city,0,3)). '_'. strtolower(str_replace(" ", "_", $nick));
	return ('<img src="/images/'. $dir. '/logos100/'. $imgnick. 
		'" alt="'. $nick. '" width=100 >');
		
} // end of function

function LgActive($lg){  // is this league still going on?

	$lgq = "SELECT Seasonend, Active FROM Leagues WHERE League_ID = '$lg'";
	$lgr = @mysql_query($lgq);
	
	while($row = mysql_fetch_array($lgr, MYSQL_NUM)){
		if($row[0] == 0 && $row[1] == 1){
			return 1;
		}
		else if($row[0] == 1 && $row[1] == 1){
			echo '<p align="center"><span class="heading_white">The regular season has ended.  '.
				'Please check the playoffs page for the playoff schedule.</span></p>';
		}
		else 	 
			echo '<p align="center"><span class="heading_white">This league is no longer active.'.
				'</span></p>';
	}

	return 0;
	
} // end of function

/*********************************************************************/


	if(isset($_GET['gmid']) && isset($_GET['tmid']) && isset($_GET['gn']) && is_numeric($_GET['gmid']) && is_numeric($_GET['tmid'])
	  && is_numeric($_GET['gn'])){	
		$game_id = $_GET['gmid'];
		$team_id = $_GET['tmid'];
		$gn = $_GET['gn'];
	}
	else
		die("ERROR 2001: Data not sent to page.");

	if(isset($_GET['err'])){
		switch ($_GET['err']){
			
			case 0:
				$msg = "Game has been uploaded and submitted.";
			break;
			case 1:
				$msg = "Teams in the save state file do not match the game on the schedule.  Please try a different file.";
			break;
			case 2:
				$msg = "Password is incorrect.  Please try again.";
			break;
			case 3:
				$msg = "File is not valid.  Please choose a file that ends in .gs (Genesis) or .zs (SNES).";
			break; 
			case 4:
				$msg = "Error submitting game.  Please contact the administrator.";
			break;			
			case 5:
				$msg = "Game could not be uploaded.  Please contact the administrator.";
			break;
			default:
				$msg = "";
			break;
	
		}
	}
	
	else 
		$msg = "";
	

	$gameq = "SELECT UH.ForumName HCoach, UA.ForumName ACoach, H.City HCity, H.Nickname HN, A.City ACity, A.Nickname AN, 
			S.Home, S.Away, S.H_Confirm HC, S.A_Confirm AC 
			FROM Schedule S 
			JOIN Teams H ON H.Team_ID = S.Home
			JOIN Teams A ON A.Team_ID = S.Away
			JOIN User UH ON UH.User_ID = S.H_User_ID
			JOIN User UA ON UA.User_ID = S.A_User_ID
			WHERE Game_ID = '$game_id' LIMIT 1";

	$gamer = mysql_query($gameq) or die("ERROR 2002: Could not retrieve game information.");

	if(mysql_num_rows($gamer)){
		$tmpglink = "coachpage.php?lg=". $lg. "&sublg=". $sublg. "&team_ID=". $team_id;
		$boxscore = "";
		$row = mysql_fetch_array($gamer, MYSQL_ASSOC);
		$homelogo = printLogo20($lg, $row['HCity'], $row['HN']);
		$awaylogo = printLogo20($lg, $row['ACity'], $row['AN']);
		$hometeam = ChkTeam($row['HCity'], $row['HN']);
		$awayteam = ChkTeam($row['ACity'], $row['AN']);
		
		$bigaway = printLogo100($lg, $row['ACity'], $row['AN']);
		$bighome = printLogo100($lg, $row['HCity'], $row['HN']);
		$hiddenfields = '<input name="gameid" type="hidden" value="'. $game_id. '">'.
				'<input name="teamid" type="hidden" value="'. $team_id. '">'.
				'<input name="gn" type="hidden" value="'. $gn. '">';			
		
		// Check who is logging the game, for logo display purposes
		
		if($team_id == $row['Home']){  // home team logging
			$logo = $homelogo;
			$team = $hometeam;
			$user = $row['HCoach'];
		}
		else if($team_id == $row['Away']){  // away team logging
			$logo = $awaylogo;
			$team = $awayteam;
			$user = $row['ACoach'];
		}
		else
			die("Team ID incorrect.");
			
		// Check if game is already uploaded
			
		if($row['HC'] == '0' || $row['AC'] =='0'){  // game needs uploading	
			$upl = 'Choose file: <input type="file" name="uploadfile" />';		
			$pwd = 'Enter Password: <input name="pwd" type="password" size="20" maxlength="20">';
			$loggame = '<input type="submit" name="submit" value="Upload" />';
		}
		else {
			$upl = 'This game has been uploaded and submitted correctly.';
			$pwd = "";
			$loggame = "";
			$boxlink = "box_score.php?gameid=". $game_id;
			$boxscore = '<p align="center" class="small_white"><a href="'. $boxlink. '" class="link6">View Box Score</a> 
                <p align="center">&nbsp;</p>';		
		}
		
		
	}
	else
		die("Error with data submitted to page.  Please contact the administrator.");				
				
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
echo '<script type="text/javascript" language="JavaScript">';
echo ("var lg = '$lg';\n");
echo ("var sublg = '$sublg';\n");
echo '</script>';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>NHL94 Online.com | Log A State</title>
<link href="/css/main.css" rel="stylesheet" type="text/css" />
<!-- <script language="JavaScript">dqm__codebase = "/jslib/nav_bar/"</script>
<script language="JavaScript" src="/jslib/nav_bar/nav_bar.js"></script>
<script language="JavaScript" src="/jslib/nav_bar/tdqm_loader.js"></script> -->
</head>

<body>
<?php include($_SERVER['DOCUMENT_ROOT']. "/html/header.php"); ?>
<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
  <tr>
    <td width="250" valign="top"><?php include($_SERVER['DOCUMENT_ROOT']. "/html/menu-leftnav.php"); ?></td>
    <td valign="top"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="border_table1">
      <tr>
        <td height="45" valign="middle"><div align="center"><img src="/images/2007/log-a-game.gif" alt="Log A Game" width="220" height="26" /></div></td>
      </tr>
      <tr>
        <td valign="top" background="/images/2007/ea-sports-bg.gif"><div align="left">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            
            <tr>
              <td height="50" valign="top"><div align="center">                
                <p class="heading_white">Log A Save State -
                  <?php echo lgname($lg); ?>
                  League</p>

		  <?php if(LgActive($lg)){ ?>
     
	        <p class="small_bright_blue_bold">This is to be filled out after you have completed a league game.  Please choose a save state to upload.  Save states will have either end in a .gs  (Genesis), or a .zs  (SNES), followed by the save state number slot (NOTE: ZSNES save state slot 0 will end in .zst).  After choosing the save state, enter your password and hit the "Upload" button.  This will upload the file and extract the stats used for the league.</p>
                <p class="small_white">&nbsp;</p>
              </div></td>
            </tr>
          </table>
          <table width="700" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td valign="top"><div align="center">
                  <p style="font-size:125%" class="bold_bright_orange">
                    <?= $sublg ?>
                    Level</p>
              </div></td>
            </tr>
            <tr>
              <td align="center"><p style="font-size:125%" class="bold_white"><?= $msg ?></p>
                  <form method="post" action="save_state.php" enctype="multipart/form-data">
                    <?= $hiddenfields ?>
                    <table width="300" border="0" align="center" cellpadding="3" cellspacing="0" class="border_table2">
                      <tr>
                        <td align="center" colspan="3" class="small_black_bold" background="/images/2007/menu-bg-fade.gif" style="font-size:125%">Game #: <?= $_GET['gn'] ?></td>
                      </tr>
                      <tr>
                        <td align="center"><?= $bigaway ?></td>
                        <td align="center" width="20"><p style="font-size:125%" class="heading_black"> vs. </p></td>
                        <td align="center"><?= $bighome ?></td>
                      </tr>
                      <tr>
                      	<td align="center"><span style="font-size:125%" class="heading_black"><?=$awayteam ?>
                        </span></td>
                        <td width="20"></td>
                        <td align="center"><span style="font-size:125%" class="heading_black"><?=$hometeam ?>
                        </span></td>
                      </tr>  
                    </table><br />
                    <table width="580" border="0" align="center" cellpadding="3" cellspacing="0" bordercolor="#000000" class="border_table2">
                      <tr>
                      	<input type="hidden" name="MAX_FILE_SIZE" value="400000" />
                      	<td colspan="4" align="center"><?= $upl ?></td>
                      </tr>  
                      <tr>
                      	<td align="center" bgcolor="#FFFFFF"><?= $logo ?></td>
                        <td align="center" bgcolor="#FFFFFF"><?= $user ?></td>
                        <td align="center" bgcolor="#FFFFFF"><?= $pwd ?></td>
                        <td align="center" bgcolor="#FFFFFF"><?= $loggame ?></td>
                      </tr>
                    </table>
                  </form>
		<?php } ?>
                <p align="center" class="small_white"><a href="<?= $tmpglink ?>" class="link6">Back to Coach Page</a> 
                <p align="center">&nbsp;</p>
                <?= $boxscore ?>
              </td>
            </tr>
          </table>
          </div></td>
      </tr>
    </table>
    <table width="100%" border="0" cellpadding="8" cellspacing="0" class="border_table1">
      <tr>
        <td><?php include($_SERVER['DOCUMENT_ROOT']. "/html/feedback.php"); ?></td>
      </tr>
    </table></td>
  </tr>
</table>
<?php include($_SERVER['DOCUMENT_ROOT']. "/html/footer.php"); ?>
</body>
</html>
<?php
    /* Close SQL-connection */
    mysql_close();
?>