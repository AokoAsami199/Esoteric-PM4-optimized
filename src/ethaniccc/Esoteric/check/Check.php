<?php

namespace ethaniccc\Esoteric\check;

use ethaniccc\Esoteric\data\PlayerData;
use ethaniccc\Esoteric\Esoteric;
use pocketmine\network\mcpe\protocol\DataPacket;

abstract class Check{

    public $name;
    public $subType;
    public $description;

    public $experimental;
    public $violations = 0;
    public $buffer = 0;
    public $lastWarnedTime = 0;
    public static $settings = [];

    public function __construct(string $name, string $subType, string $description, bool $experimental = false){
        $settings = Esoteric::getInstance()->getSettings()->getCheckSettings($name, $subType);
        if($settings === null){
            $settings = [
                "enabled" => true,
                "punishment_type" => "none",
                "max_vl" => 20
            ];
        }
        $this->name = $name;
        $this->subType = $subType;
        $this->description = $description;
        $this->experimental = $experimental;
        if(!isset(self::$settings["$name:$subType"]))
            self::$settings["$name:$subType"] = $settings;
    }

    public abstract function inbound(DataPacket $packet, PlayerData $data) : void;

    public function outbound(DataPacket $packet, PlayerData $data) : void{}

    public function handleOut() : bool{
        return false;
    }

    public function enabled() : bool{
        return $this->option("enabled");
    }

    protected function flag(PlayerData $data, array $extraData = []) : void{
        if(!$this->experimental)
            ++$this->violations;
        $extraData["ping"] = $data->player->getPing();
        if(microtime(true) - $this->lastWarnedTime >= Esoteric::getInstance()->getSettings()->getWarnCooldown()){
            $this->warn($data, $extraData);
        }
        if($this->violations >= $this->option("max_vl") && $this->canPunish()){
            if($data->player->hasPermission("ac.bypass"))
                $this->violations = 0;
            else
                $this->punish($data);
        }
    }

    protected function warn(PlayerData $data, array $extraData) : void{
        $dataString = "";
        $n = count($extraData); $i = 1;
        foreach($extraData as $name => $value){
            $dataString .= "$name=$value";
            if($i !== $n)
                $dataString .= " ";
            $i++;
        }
        $string = str_replace(["{prefix}", "{player}", "{check_name}", "{check_subtype}", "{violations}", "{data}"], [Esoteric::getInstance()->getSettings()->getPrefix(), $data->player->getName(), $this->name, $this->subType, var_export(round($this->violations, 2), true), $dataString], Esoteric::getInstance()->getSettings()->getWarnMessage());
        foreach(Esoteric::getInstance()->hasAlerts as $other)
            $other->player->sendMessage($string);
        $this->lastWarnedTime = microtime(true);
    }

    protected function punish(PlayerData $data) : void{
        // TODO: Work on punishments
    }

    protected function reward(float $sub = 0.01) : void{
        $this->violations = max($this->violations - $sub, 0);
    }

    protected function option(string $option, $default = null){
        return self::$settings["{$this->name}:{$this->subType}"][$option] ?? $default;
    }

    protected function canPunish() : bool{
        return $this->option("punishment_type") !== "none";
    }

}