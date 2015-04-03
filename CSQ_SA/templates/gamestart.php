<script language="JavaScript"><!--
javascript:window.history.forward(1);
//--></script>
<div class="jumbotron">
	<?php /*if(is_null($this->_ ['gameinfo']['gameid'])){
		header('Location: index.php');
		die();
	} */ ?>
	<h1>Willkommen zum Game '<?php echo htmlspecialchars($this->_ ['gameinfo']['gamename']); ?>'</h1>

		<br>
		<p>
			Warten, bis das Game gestartet wird...
		</p> <br>
		<a data-toggle="collapse" data-target="#participants" href="#participants">
			<h4 class="panel-title"><span id="participantCount"><?php echo count($this->_ ['members']); ?></span> Teilnehmer</h4>
		</a>
		<div id="participants" class="panel-collapse collapse in">
			<ul id="participantList">
				<?php  foreach ($this->_ ['members'] as $member ) {
					echo '<li>'. htmlspecialchars($member['member']) .'</li>';
				} ?>
			</ul>
		</div>
		<br>
  		<span id="gameId" hidden="true"><?php echo $this->_ ['gameinfo']['game_id']; ?></span>
  		<div <?= ($this->_ ['isMember'] ?'':'hidden="true"') ?>>
  			<input id="leaveGame" class="btn btn-primary btn-lg" role="button" value="Austreten"></input>
  		</div>
  		<div <?= ($this->_ ['isMember']?'hidden="true"':'') ?>>
			<input id="joinGame" class="btn btn-primary btn-lg" role="button" value="Teilnehmen"></input>
		</div>
		<?php if($this->_['isOwner']){ ?>
		<br>
		<div class="btn-group">
				<?php $hasStarted = isset($this->_['gameinfo']['has_started']) ?>
				<div <?php echo ($hasStarted ? 'hidden="true"' : '' ); ?>>
					<input id="startGame" class="btn btn-primary pull-left" type="button" value="Game starten" />
				</div>
				<div <?php echo (! $hasStarted ? 'hidden="true"' : '' ); ?>>
					<input id="stopGame" class="btn btn-primary pull-left" type="button" value="Game beenden" />
				</div>
			</div>
		<?php } ?>

</div>
<?php echo $this->_ ['adminView']; ?>