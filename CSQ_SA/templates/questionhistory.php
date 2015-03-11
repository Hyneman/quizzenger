<?php
	if(isset($this->_ ['questionhistory'])){
		if(empty($this->_ ['questionhistory'])){
			echo("Noch keine Daten vorhanden");
		}
		foreach ( $this->_ ['questionhistory'] as $history ){
			echo($history['timestamp']." - ".$history['action']." durch ".htmlspecialchars($history['username'])."<br>");
		}
	}elseif(isset($this->_['questionhistoryByUser'])){
		foreach ( $this->_ ['questionhistoryByUser'] as $history ){
			$qtxt = $history['questiontext'];
			echo($history['timestamp'].' - '.$history['action']." durch ".htmlspecialchars($history['username']).'<br><a href="index.php?view=question&id='.$history['question_id'].'">'.((strlen($qtxt) > 40) ? htmlspecialchars(substr($qtxt,0,40))."..." : htmlspecialchars($qtxt))	.'</a><br><br>');
		}
	}else{
		echo("Fehlende Daten");
	}
?>