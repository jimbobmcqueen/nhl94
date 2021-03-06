<?php

		session_start();
		$ADMIN_PAGE = true;
		include_once './_INCLUDES/00_SETUP.php';
		include_once './_INCLUDES/dbconnect.php';

		if ($LOGGED_IN == true) {	

			$msg = "";
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

			$creatednew = true;
			$seriesid = 0;

			if(isset($_GET['seriesId'])){

				$seriesid = $_GET['seriesId'];
				$creatednew = false;
			}

			if(!$creatednew){

				$series = GetSeriesById($seriesid);
				$gamesplayed = GetGamesBySeriesId($seriesid);

				$hometeam = GetTeamById($series["HomeTeamId"]);
				$awayteam = GetTeamById($series["AwayTeamId"]);

				$homeUserAlias = GetUserAlias($series["HomeUserID"]);
				$awayUserAlias = GetUserAlias($series["AwayUserID"]);

				$submitBtn ="<input type='hidden' name='MAX_FILE_SIZE' value='400000' />";
				$submitBtn .="<input type='hidden' name='userid' value='" . $_SESSION['userId'] . "' />"; 
				$submitBtn .="<input type='hidden' name='seriesid' value='" . $seriesid ."' />";	


			}else{
				
				// User wants to update an existing series so gab all the series games and show in drop down box
				$allseries = GetSeries();
				$seriesSelectBox = "<select id='Series' name='Series' onchange='LoadSeries(this.value)'>";
				$seriesSelectBox .= "<option value='0'>Select Series</option>";

				while($row = mysqli_fetch_array($allseries)){
					$seriesSelectBox .= "<option value='" . $row['ID'] . "'>" . $row['Name'] . "  |  " . $row['DateCreated']. "</option>";					
				}	

				$seriesSelectBox .= "</select>";
				
			}

			$fileInput = "Choose file: <input type='file' name='uploadfile' />";			
			$fileInput .= "<input type='submit' name='submit' value='Upload' />";
			
?><!DOCTYPE HTML>
<html>
<head>
<title>Update Series</title>
<?php include_once './_INCLUDES/01_HEAD.php'; ?>

			<script>			

			function UploadFile(scheduleNum){

				var fileInputBox = "<?= $fileInput ?><input type='hidden' name='scheduleid' value='" + scheduleNum + "' />";
				var fileInputDiv = $("#fileInput" + scheduleNum);

				fileInputDiv.html(fileInputBox);
				fileInputDiv.show();					

			}

			function LoadSeries(seriesId){

				if(seriesId != 0)
					document.location.href = "update.php?seriesId=" + seriesId;

			}
			</script>

</head>

<body>

		<div id="page">
		
				<?php include_once './_INCLUDES/02_NAV.php'; ?>
				
				<div id="main">
					<?php include_once './_INCLUDES/03_LOGIN_INFO.php'; ?>

					<div style="color:red;"><?= $msg ?></div><br/><br/>
					<h2>Update Series</h2> 
					<?php
					if(!$creatednew){
					?>
					<form method="post" action="processUpdate.php" enctype="multipart/form-data">	
					<?= $submitBtn?>
					<table class="standard">
						<tr class="heading rowSpacer">
							<td class="seriesNum mainTD"></td>
							<td class="seriesName mainTD"><?=$series["Name"]?></td>
							<td class="seriesDate mainTD">Created <?=$series["DateCreated"]?></td>
						</tr>
						<tr class="heading">
							<td>&nbsp;</td>
							<td class="seriesInfo mainTD" colspan="2"><b><?=$hometeam["Name"]?> ( <?= $homeUserAlias ?> )</b> vs <?=$awayteam["Name"]?> ( <?= $awayUserAlias ?> ), starting in <?=$hometeam["ABV"]?> (3-3-1)</td>
						</tr>						

						<?php 

						$i=1;
						$homeWinnerCount = 0;
						$awayWinnerCount = 0;

						while($row = mysqli_fetch_array($gamesplayed, MYSQL_ASSOC)){							
								
							if($row["GameID"] != 0){								
							
								if($row["WinnerTeamID"] == $series["HomeTeamId"]){
									$homeWinnerCount++;
								}

								if($row["WinnerTeamID"] == $series["AwayTeamId"]){
									$awayWinnerCount++;
								}							
						
						?>
						<tr>
							<td>&nbsp;</td>
							<td>Gm <?=$i?>. <b><?= GetTeamNameById($row["HomeTeamID"]) ?> <?=$row["HomeScore"]?></b> / <?= GetTeamNameById($row["AwayTeamID"]) ?> <?=$row["AwayScore"]?></td>
							<td><button type="button" class="square" onclick="location.href='gamestats.php?gameId=<?= $row['GameID']?>'">Game Stats</button></td>
						</tr>
						<?php
							}else{

								if($homeWinnerCount < 4 && $awayWinnerCount < 4){
						?>
						<tr class="normal">
							<td>&nbsp;</td>
							<td>Gm <?=$i?>. <?= GetTeamNameById($row["HomeTeamID"]) ?> at <?= GetTeamNameById($row["AwayTeamID"]) ?></td>
							<td><button type="button" class='square' id='submit<?= $row["ID"]?>' onclick="UploadFile('<?= $row["ID"]?>')">Upload File</button></td>
						</tr>		
						<tr>
							<td colspan="3" id="fileInput<?= $row["ID"]?>" style="display:none;"></td>						
						</tr>				
						<?php
								//End Winner Count If
								}
							}	
							$i++;					
							// End While
						 }			
						 
						 $winnerText = "";

						 if($homeWinnerCount >= 4 || $awayWinnerCount >= 4){

							$winnerText = "Series Won By: ";

							if($homeWinnerCount >= 4){

								$winnerText .= $hometeam["Name"] ."<br/>" . $homeUserAlias; 
							}

							if($awayWinnerCount >= 4){

								$winnerText .= $awayteam["Name"] ."<br/>" . $awayUserAlias;

							}

							$winnerText .= " Wins the fucking Stanley Cup!!!";
						 }

						 ?>
						 <tr>
							<td colspan="3"><?= $winnerText ?></td>						
						</tr>
						 
						<?php
							
							$seriesText = "<h2>";

							if($homeWinnerCount > $awayWinnerCount && $homeWinnerCount < 4){
								$seriesText .= $hometeam["Name"] . " leads series " . $homeWinnerCount . " to " . $awayWinnerCount;
							}

							if($awayWinnerCount > $homeWinnerCount && $awayWinnerCount < 4) {
								$seriesText .= $awayteam["Name"] . " leads series " . $awayWinnerCount . " to " . $homeWinnerCount;
							}

							if($awayWinnerCount == $homeWinnerCount && (!$awayWinnerCount == 0 && !$homeWinnerCount == 0)  ){
								$seriesText .= "Series tied " .  $awayWinnerCount . " to " . $homeWinnerCount;
							}

							if($awayWinnerCount == 0 && $homeWinnerCount == 0 ){

								$seriesText .= "Series not yet started.";
							}

							if($homeWinnerCount >= 4){

								$seriesText .= $hometeam["Name"] . " Win The Stanley!"; 
							}

							if($awayWinnerCount >= 4){

								$seriesText .= $awayteam["Name"] . " Win The Stanley!";

							}
							
							$seriesText .= "</h2>";

							echo $seriesText;	 
							//logMsg("hw:" . $homeWinnerCount);
							//logMsg("aw:" . $awayWinnerCount);	
					
						?>
											
					</table>
					</form>	
					<?php						 
					 }else{
						 echo $seriesSelectBox;
					 }
					  ?>
				</div>	
		
		</div><!-- end: #page -->	
		
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<script src="./js/default.js"></script>

</body>
</html>
<?php
		}
		else {
				header('Location: index.php');
		}	
?>	