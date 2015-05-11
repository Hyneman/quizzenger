<?php
	use \quizzenger\controlling\EventController as EventController;

	if (isset ( $this->request ['id'] ) && isset ( $this->request ['answer'] )) {
		$viewInner->setTemplate ( 'solution' );

		$question = $questionModel->getQuestion ( $this->request ['id'] );

		$author = $userModel->getUsernameByID ( $question ['user_id'] );

		$categoryName = $categoryModel->getNameByID ( $question ['category_id'] );

		$answers = $answerModel->getAnswersByQuestionID ( $this->request ['id'] );
		$order = $_SESSION['questionorder'][$this->request ['id']];
		array_multisort($order, $answers);
		$selectedAnswer = $this->request ['answer'];
		$correctAnswer = $answerModel->getCorrectAnswer ( $this->request ['id'] );

		$alreadyReported= $reportModel->checkIfUserAlreadyDoneReport("question", $this->request ['id'] , $_SESSION ['user_id']);
		$viewInner->assign ('alreadyreported',$alreadyReported);

		include("rating.php");


		if($GLOBALS['loggedin'] && $correctAnswer == $selectedAnswer){
			if(!$userscoreModel->hasUserScoredQuestion($this->request ['id'],$_SESSION['user_id'])){ // no multiple scoring for question.
				EventController::fire('question-answered-correct', $_SESSION['user_id'], [
					'category' => $question['category_id']
				]);
				$viewInner->assign ('pointsearned', QUESTION_ANSWERED_SCORE);
			}
		}

		include("helper/solution_report.php");


		$viewInner->assign ( 'answers', $answers );
		$viewInner->assign ( 'category', $categoryName );
		$viewInner->assign ( 'author', $author );

		$viewInner->assign ( 'questionID', $this->request ['id'] );
		$viewInner->assign ( 'selectedAnswer', $selectedAnswer );
		$viewInner->assign ( 'userismodhere', $userIsModHere );
		$viewInner->assign ( 'question', $question );

		// Implement other Strategies if other question types are desired
		$correct = ($correctAnswer == $selectedAnswer ? 100 : 0);

		$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';


		// Only relevant if question was answered in quiz context
		if (isset ( $this->request ['session_id'] )  ) {
			$session_id = $this->request ['session_id'];
			$inc_counter=0;
			if ($questionModel->answerExists ( $session_id, $this->request ['id'], $_SESSION['user_id'] ) == 0) { // Normal Quiz
				$questionModel->InsertQuestionPerformance ( $this->request ['id'], $_SESSION ['user_id'], $correct, $session_id, NULL);
				$inc_counter=1;
			}
			$_SESSION ['counter'. $session_id] += $inc_counter;
			$questionCount= count ( $_SESSION ['questions'. $session_id] );
			$currentCounter= $_SESSION ['counter'. $session_id];
			$progress = round ( 100 * ($currentCounter / $questionCount) );
			$viewInner->assign ( 'progress', $progress );
			$viewInner->assign ( 'questioncount', $questionCount );
			$viewInner->assign ( 'currentcounter', $currentCounter );
			$viewInner->assign ( 'progress', $progress );

			if (count ( $_SESSION ['questions'. $session_id] ) > $_SESSION ['counter'. $session_id]) {
				$viewInner->assign ( 'nextQuestion', "?view=question&id=" . $_SESSION ['questions'. $session_id] [$_SESSION ['counter'. $session_id]] . "&amp;session_id=" . $session_id);
			} else {
				$viewInner->assign ( 'nextQuestion', "?view=quizend&session_id=". $session_id);
			}
		}
		else { // not in quiz context
			if(!$pageWasRefreshed){
				$questionModel->InsertQuestionPerformance ( $this->request ['id'], $_SESSION ['user_id'], $correct, NULL, NULL);
			}
		}
	}
?>