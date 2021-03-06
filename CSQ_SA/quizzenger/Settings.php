<?php

namespace quizzenger {
	use \mysqli as mysqli;
	use \stdClass as stdClass;
	use \quizzenger\logging\Log as Log;

	/**
	 * Sets and retrieves application-wide settings.
	**/
	class Settings {
		/**
		 * Holds an instance to the database connection.
		 * @var mysqli
		**/
		private $mysqli;

		/**
		 * Creates the object based on an existing database connection.
		**/
		public function __construct(mysqli $mysqli) {
			$this->mysqli = $mysqli;
		}

		/**
		 * Gets the value of the specified setting.
		 * @param string $name Name of the setting.
		 * @return string Value of the setting.
		**/
		public function getSingle($name) {
			$setting = $this->get([$name]);
			if(empty($setting))
				return null;

			return $setting[0]->value;
		}

		/**
		 * Gets the values of the specified settings.
		 * @param array $settings Names of settings to query.
		 * @return array Returns an array holding names and values.
		**/
		public function get(array $settings) {
			if(empty($settings))
				return [];

			$placeholders = str_repeat('?,', count($settings) - 1) . '?';
			$statement = $this->mysqli->prepare("SELECT name, value FROM settings WHERE name IN ($placeholders)");
			if(!$statement) {
				Log::error('Could not prepare statement.');
				return null;
			}

			$arguments = [str_repeat('s', count($settings))];
			foreach($settings as $key => $value) {
				$arguments[] = &$settings[$key];
			}

			call_user_func_array([$statement, 'bind_param'], $arguments);

			if(!$statement->execute() || !($result = $statement->get_result())) {
				Log::error('Could not retrieve settings.');
				return null;
			}
			else {
				$received = [];
				while($current = $result->fetch_object()) {
					$object = new stdClass();
					$object->name = $current->name;
					$object->value = $current->value;

					$received[] = $object;
				}
				return $received;
			}
		}

		/**
		 * Updates the value of the specified setting.
		 * @param string $name Name of the setting to update.
		 * @param string $value New value to be stored.
		 * @return boolean Returns 'true' on success, 'false' otherwise.
		**/
		public function set($name, $value) {
			$statement = $this->mysqli->prepare('INSERT INTO settings (name, value) VALUES (?, ?)'
				. ' ON DUPLICATE KEY UPDATE value=VALUES(value)');

			$statement->bind_param('ss', $name, $value);
			if(!$statement->execute()) {
				Log::error('Could not update settings.');
				return false;
			}

			return true;
		}
	} // class Settings
} // namespace quizzenger

?>
