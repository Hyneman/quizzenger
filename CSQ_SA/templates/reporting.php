<?php
	$user = $this->_['user'];
	$userList = $this->_['userlist'];
	$questionList = $this->_['questionlist'];
	$authorList = $this->_['authorlist'];

	if (isset($this->_['message'])){
		echo '<div class="alert alert-info" role="alert"><a href="#" class="close" data-dismiss="alert">&times;</a>'.htmlspecialchars($this->_['message']).'</div>';
	}

	$outputRow = function($text) {
		echo '<td>' . htmlspecialchars($text) . '</td>';
	};
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<strong>Reporting</strong>
	</div>
	<div role="tabpanel">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#tab-user-report" role="tab" data-toggle="tab"><b>Benutzer</b></a></li>
			<li role="presentation"><a href="#tab-question-report" role="tab" data-toggle="tab"><b>Fragen</b></a></li>
			<li role="presentation"><a href="#tab-author-report" role="tab" data-toggle="tab"><b>Autoren</b></a></li>
			<li role="presentation"><a href="#tab-system-report" role="tab" data-toggle="tab"><b>System</b></a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="tab-user-report">
				<div class="panel-body">
					<table class="table quizzenger-report-table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Name</th>
								<th>Erstellt</th>
								<th>Rang</th>
								<th>Producer</th>
								<th>Consumer</th>
							</tr>
						</thead>
						<tbody>
							<?php
								while($current = $userList->fetch_object()) {
									echo "<tr>";
									$outputRow($current->id);
									$outputRow($current->username);
									$outputRow($current->created_on);
									$outputRow($current->rank);
									$outputRow($current->producer_score);
									$outputRow($current->consumer_score);
									echo "</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="tab-author-report">
				<div class="panel-body">
					<table class="table quizzenger-report-table">
						<thead>
							<tr>
								<th>Frage</th>
								<th>Bewertung</th>
								<th>Schwierigkeit</th>
								<th>Durchführungen</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>ddd</td><td>ddd</td><td>ddd</td><td>ddd</td>
							</tr>
							<tr>
								<td>eee</td><td>eee</td><td>eee</td><td>eee</td>
							</tr>
							<tr>
								<td>fff</td><td>fff</td><td>fff</td><td>fff</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="tab-question-report">
				<div class="panel-body">
					<table class="table quizzenger-report-table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Frage</th>
								<th>Erstellt</th>
								<th>Modifiziert</th>
								<th>Bewertung</th>
								<th>Schwierigkeit</th>
								<th>Gelöst</th>
							</tr>
						</thead>
						<tbody>
							<?php
								while($current = $questionList->fetch_object()) {
									echo '<tr>';
									$outputRow($current->id);
									$outputRow($current->questiontext);
									$outputRow($current->created);
									$outputRow($current->last_modified);
									$outputRow($current->rating);
									$outputRow($current->difficulty);
									$outputRow($current->solved_count);
									echo '</tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="tab-system-report">
				<div class="panel-body">
					<table class="table quizzenger-report-table">
						<thead>
							<tr>
								<th>Frage</th>
								<th>Bewertung</th>
								<th>Schwierigkeit</th>
								<th>Durchführungen</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>jjj</td><td>jjj</td><td>jjj</td><td>jjj</td>
							</tr>
							<tr>
								<td>kkk</td><td>kkk</td><td>kkk</td><td>kkk</td>
							</tr>
							<tr>
								<td>lll</td><td>lll</td><td>lll</td><td>lll</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
