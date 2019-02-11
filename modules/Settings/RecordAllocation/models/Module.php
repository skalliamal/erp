<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Module_Model extends Settings_Vtiger_Module_Model
{
	private static $data = [];
	private static $types = [
		'owner' => 'user_privileges/module_record_allocation.php',
		'sharedOwner' => 'user_privileges/sharedOwner.php',
	];

	public function save($data)
	{
		$moduleName = $data['module'];
		$userId = $data['userid'];
		$userData = $data['ids'] ?? [];
		$this->putToFile($moduleName, $userId, $userData);
	}

	public function remove($moduleName)
	{
		$data = self::loadFile($this->get('type'));
		unset($data[$moduleName]);
		$content = '<?php' . PHP_EOL . '$map=' . var_export($data, true) . ';';

		$file = self::$types[$this->get('type')];
		file_put_contents($file, $content);
	}

	public function putToFile($moduleName, $userId, $userData)
	{
		$data = self::loadFile($this->get('type'));
		if (!isset($data[$moduleName])) {
			$data[$moduleName] = [];
		}
		$data[$moduleName][$userId] = $userData;
		$content = '<?php' . PHP_EOL . '$map=' . var_export($data, true) . ';';

		$file = self::$types[$this->get('type')];
		file_put_contents($file, $content);
	}

	public static function getRecordAllocationByModule($type, $moduleName)
	{
		$data = self::loadFile($type);

		return $data[$moduleName] ?? [];
	}

	public static function loadFile($type)
	{
		if (empty(self::$types[$type])) {
			return [];
		}
		if (!isset(self::$data[$type])) {
			$file = self::$types[$type];
			require $file;
			self::$data[$type] = $map ?? [];
		}
		return self::$data[$type];
	}

	public static function resetDataVariable()
	{
		self::$data = [];
	}
}
