<?php

namespace ethaniccc\Esoteric;

use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

    public function onEnable(){
        try{Esoteric::init($this, true);}catch(\Exception $e){
            $this->getLogger()->error("Unable to start Esoteric (already started?) [{$e->getMessage()}]");
        }
        /*$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) : void{
            $plugin = $this->getServer()->getPluginManager()->getPlugin("Mockingbird");
            if($plugin !== null)
                $this->getServer()->getPluginManager()->disablePlugin($plugin);
        }), 1);*/
    }

    public function onDisable(){
        try{Esoteric::getInstance()->stop();}catch(\Exception $e){
            $this->getLogger()->error("Unable to stop esoteric (???) [{$e->getMessage()}]");
        }
    }

}
