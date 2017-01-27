<?php
class Settings {

	static $grrSettings;
	static $grrSettingsEtab;

	public function __construct()
	{
		return self::load();
	}


	static function load()
	{
		// MULTI ETAb : Charge les données des établissements
		$sql = "SELECT code_etab,`NAME`, `VALUE` FROM ".TABLE_PREFIX."_setting_etablissement";
		$res = grr_sql_query($sql);
		if ($res && grr_sql_count($res) != 0){
			for ($i = 0; ($row= grr_sql_row($res, $i)); $i++) {
				self::$grrSettingsEtab[$row[0]][$row[1]] = $row[2];
			}
		} // fin MULTI-ETAB
	
		$test = grr_sql_query1("SELECT NAME FROM ".TABLE_PREFIX."_setting WHERE NAME='version'");
		if ($test != -1)
			$sql = "SELECT `NAME`, `VALUE` FROM ".TABLE_PREFIX."_setting";
		else
			$sql = "SELECT `NAME`, `VALUE` FROM setting";
		$res = grr_sql_query($sql);
		if (!$res)
			return false;
		if (grr_sql_count($res) == 0)
			return false;
		else
		{
			for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
				self::$grrSettings[$row[0]] = $row[1];
			return true;
		}
	}

	static function get($_name, $global = false)
	{
		if (! $global && 
			isset($_SESSION['current_etablisement']) &&
			isset(self::$grrSettingsEtab[$_SESSION['current_etablisement']]) &&
			isset(self::$grrSettingsEtab[$_SESSION['current_etablisement']][$_name]) &&
			self::$grrSettingsEtab[$_SESSION['current_etablisement']][$_name] != ''){

			return self::$grrSettingsEtab[$_SESSION['current_etablisement']][$_name];
		}
	
		if (isset(self::$grrSettings[$_name]))
			return self::$grrSettings[$_name];
	}

	static function set($_name, $_value)
	{
		if (isset(self::$grrSettings[$_name]))
		{
			$sql = "UPDATE ".TABLE_PREFIX."_setting set VALUE = '" . protect_data_sql($_value) . "' where NAME = '" . protect_data_sql($_name) . "'";
			$res = grr_sql_query($sql);
			if (!$res)
				return false;
		}
		else
		{
			$sql = "INSERT INTO ".TABLE_PREFIX."_setting set NAME = '" . protect_data_sql($_name) . "', VALUE = '" . protect_data_sql($_value) . "'";
			$res = grr_sql_query($sql);
			if (!$res)
				return (false);
		}
		self::$grrSettings[$_name] = $_value;
		return true;
	}
	
	// MULTI ETAB
	static function setEtab($_name, $_value)
	{
		
		$codeEtab = getCodeEtablissementFromId(getIdEtablissementCourant());

		if (isset(self::$grrSettingsEtab[$codeEtab][$_name])){
			
			$sql = "UPDATE ".TABLE_PREFIX."_setting_etablissement set VALUE = '" . protect_data_sql($_value) . "' where NAME = '" . protect_data_sql($_name) . "' AND code_etab = '" . protect_data_sql($codeEtab) . "'";
			$res = grr_sql_query($sql);
			if (!$res)
				return false;
		}
		else
		{
			$sql = "INSERT into ".TABLE_PREFIX."_setting_etablissement set NAME = '" . protect_data_sql($_name) . "', VALUE = '" . protect_data_sql($_value) . "', code_etab = '" . protect_data_sql($codeEtab) . "'";
			$res = grr_sql_query($sql);
			if (!$res)
				return (false);
		}
		self::$grrSettingsEtab[$codeEtab][$_name] = $_value;
		return true;
	}
	
}
?>
