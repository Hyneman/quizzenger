<?php

use \quizzenger\Settings as Settings;
class ReportingModel {
	private $mysqli;
	private $logger;

	public function __construct($mysqli, $log) {
		$this->mysqli = $mysqli;
		$this->logger = $log;
	}

	public function isAnyModerator($userId) {
		$statement = $this->mysqli->s_query('SELECT ? IN (SELECT  user_id FROM moderation) AS moderator',
			['i'], [$userId], false);

		if($statement->fetch_object()->moderator)
			return true;

		return false;
	}

	public function getUserList($categoryId) {
		if($categoryId == "" || $categoryId == 0) {
			return $this->mysqli->s_query('SELECT * FROM userscoreview', [], [], false);
		}
		else {
			return $this->mysqli->s_query('SELECT * FROM userscoreview AS v'
				. ' RIGHT JOIN userscore ON (v.id=userscore.user_id)'
				. ' WHERE userscore.category_id=?'
				. ' GROUP BY v.id'
				. ' ORDER BY v.id ASC',
				['i'], [$categoryId], false);
		}
	}

	/**
	 * @param $user_id The user id is used to check the authorization
	 * @return autorized_on_question is > 0 when user is authorized to delete and edit a question.
	 */
	public function getQuestionList($user_id) {
		return $this->mysqli->s_query('SELECT question.id, question.questiontext,'
			. ' DATE(question.created) AS created, DATE(question.lastModified) AS last_modified,'
			. ' question.difficulty, question.rating, question.ratingcount,'
			. ' user.id AS author_id, user.username AS author,'
			. ' category.id AS category_id, category.name AS category,'
			. ' (SELECT COUNT(*) FROM questionperformance WHERE questionperformance.question_id=question.id) AS solved_count,'
			. ' (SELECT (SELECT COUNT(m.id) FROM moderation m JOIN question q on q.category_id = m.category_id WHERE m.user_id=? AND q.id = question.id) + '
			. ' (SELECT COUNT(q.id) FROM question q WHERE q.id=question.id AND q.user_id=?) +'
			. ' (SELECT u.superuser FROM user u WHERE u.id=?)) as autorized_on_question'
			. ' FROM question'
			. ' JOIN user ON (user.id=question.user_id)'
			. ' JOIN category ON (category.id=question.category_id)'
			. ' ORDER BY created ASC, question.id ASC',
			['i','i','i'], [$user_id, $user_id, $user_id], false);
	}

	public function getAuthorList() {
		return $this->mysqli->s_query('SELECT user.id AS author_id, user.username AS author,'
			. ' (SELECT COUNT(*) FROM question WHERE question.user_id=user.id GROUP BY question.user_id) AS question_count,'
			. ' (SELECT AVG(stars) FROM rating WHERE rating.user_id=user.id GROUP BY rating.user_id) AS rating_average,'
			. ' (SELECT AVG(difficulty) FROM question WHERE question.user_id=user.id GROUP BY question.user_id) AS difficulty_average'
			. ' FROM user'
			. ' WHERE user.id IN (SELECT user_id FROM question)'
			. ' ORDER BY user.id ASC',
			[], [], false);
	}

	public function getCategoryList($userId, $isSuperUser) {
		return $this->mysqli->s_query('SELECT DISTINCT userscore.category_id AS id, category.name'
			. ' FROM userscore'
			. ' JOIN category ON (category.id=userscore.category_id)'
			. ' WHERE ? OR category.id IN (SELECT moderation.category_id FROM moderation WHERE moderation.user_id=?)'
			. ' ORDER BY category.name ASC',
			['i', 'i'], [$isSuperUser, $userId], false);
	}

	public function getAttachmentMemoryUsage() {
		$size = 0;
		$iterator = new DirectoryIterator(ATTACHMENT_PATH);
		foreach($iterator as $file) {
			if($file->isDot() || $file->isDir())
				continue;

			$size += $file->getSize();
		}
		return $size;
	}

	public function getDatabaseMemoryUsage() {
		$size = 0;
		$statement = $this->mysqli->s_query('SHOW TABLE STATUS', [], [], false);
		while($row = $statement->fetch_object()) {
			$size += $row->Data_length + $row->Index_length;
		}
		return $size;
	}

	public function getRecentLoginAttempts() {
		$statement = $this->mysqli->s_query('SELECT COUNT(*) AS count FROM login_attempts'
			. '  WHERE time >= NOW() - INTERVAL 1 DAY', [], [], false);
		return $statement->fetch_object()->count;
	}

	public function getLogFiles() {
		$files = [];
		$iterator = new DirectoryIterator(LOGPATH);
		foreach($iterator as $file) {
			if($file->isDot() || $file->isDir() || $file->getExtension() !== 'log')
				continue;

			$files[] = $file->getFilename();
		}

		natsort($files);
		return array_reverse($files);
	}
}
?>
