<?php

namespace ethaniccc\Loader;

use ethaniccc\Esoteric\Esoteric;
use Exception;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

	protected function onEnable() : void{
		try{
			Esoteric::init($this, $this->getConfig(), true);
		}catch(Exception $e){
			$this->getLogger()->error("Unable to start Esoteric [{$e->getMessage()}]");
		}
	}

	protected function onDisable() : void{
		try{
			Esoteric::getInstance()->stop();
		}catch(Exception $e){
			$this->getLogger()->error("Unable to stop esoteric [{$e->getMessage()}]");
		}
	}
}
