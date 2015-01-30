<?php
	class Peldatabla extends NomoDataSource{
		public static $__ajax__select_whitelist = true;

		public static function getDefinition($params = array(), $groupid = FALSE)
		{
			if ($groupid === FALSE) $groupid = nomo::$session->groupid;

			$definition = parent::getDefinition($params, $groupid, $definition);
			$field = &static::getFieldByName("kod", $definition);
			$field["tableTemplateUrl"] = "/admin/app/modules/peldatabla/irsz_cell.html";

			return $definition;
	  	}
  	}
?>
