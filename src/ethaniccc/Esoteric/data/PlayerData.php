<?php

namespace ethaniccc\Esoteric\data;

use ethaniccc\Esoteric\check\Check;
use ethaniccc\Esoteric\check\combat\autoclicker\AutoClickerA;
use ethaniccc\Esoteric\check\combat\killaura\KillAuraA;
use ethaniccc\Esoteric\check\movement\fly\FlyA;
use ethaniccc\Esoteric\check\movement\fly\FlyB;
use ethaniccc\Esoteric\check\movement\fly\FlyC;
use ethaniccc\Esoteric\check\movement\groundspoof\GroundSpoofA;
use ethaniccc\Esoteric\check\movement\motion\MotionA;
use ethaniccc\Esoteric\check\movement\motion\MotionB;
use ethaniccc\Esoteric\check\movement\motion\MotionC;
use ethaniccc\Esoteric\check\movement\velocity\VelocityA;
use ethaniccc\Esoteric\data\process\ProcessInbound;
use ethaniccc\Esoteric\data\process\ProcessOutbound;
use ethaniccc\Esoteric\data\process\ProcessTick;
use ethaniccc\Esoteric\data\sub\effect\EffectData;
use ethaniccc\Esoteric\data\sub\movement\MovementConstants;
use ethaniccc\Esoteric\utils\AABB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

final class PlayerData{

    /** @var Vector3 - A zero vector, duh. */
    public static $ZERO_VECTOR;

    /** @var Player */
    public $player;
    /** @var string - The spl_object_hash identifier of the player. */
    public $hash;
    /** @var int - The current protocol of the player. */
    public $protocol = ProtocolInfo::CURRENT_PROTOCOL;
    /** @var bool - Boolean value for if the player is logged in. */
    public $loggedIn = false;
    /** @var bool - The boolean value for if the player has alerts enabled. This will always be false for players without alert permissions. */
    public $hasAlerts = false;
    /** @var Check[] - An array of checks */
    public $checks = [];

    public function __construct(Player $player){
        if(self::$ZERO_VECTOR === null){
            self::$ZERO_VECTOR = new Vector3(0, 0, 0);
        }
        $this->player = $player;
        $this->hash = spl_object_hash($player);
        $zeroVec = clone self::$ZERO_VECTOR;

        // AIDS START
        $this->currentLocation = $this->lastLocation = $this->currentMoveDelta = $this->lastMoveDelta
        = $this->lastOnGroundLocation = $this->directionVector = $this->motion = $zeroVec;
        // AIDS END

        $this->inboundProcessor = new ProcessInbound();
        $this->outboundProcessor = new ProcessOutbound();
        $this->tickProcessor = new ProcessTick();

        $this->checks = [
            # Autoclicker checks
            new AutoClickerA(),

            # Killaura checks
            new KillAuraA(),

            # Fly checks
            new FlyA(),
            new FlyB(),
            new FlyC(),

            # Ground spoof checks
            new GroundSpoofA(),

            # Motion checks
            new MotionA(),
            new MotionB(),
            new MotionC(),

            # Velocity checks
            new VelocityA(),
        ];
    }

    /** @var ProcessInbound - A class to process packet data sent by the client. */
    public $inboundProcessor;
    /** @var ProcessOutbound - A class to process packet data sent by the server. */
    public $outboundProcessor;
    /** @var ProcessTick - A class to execute every tick. Mainly will be used for NetworkStackLatency timeouts, and  */
    public $tickProcessor;

    /** @var EffectData[] */
    public $effects = [];

    /** @var int */
    public $currentTick = 0;

    /** @var Vector3 - The current and previous locations of the player */
    public $currentLocation, $lastLocation, $lastOnGroundLocation;
    /** @var Vector3 - Movement deltas of the player */
    public $currentMoveDelta, $lastMoveDelta;
    /** @var float - Rotation values of the player */
    public $currentYaw = 0.0, $previousYaw = 0.0, $currentPitch = 0.0, $previousPitch = 0.0;
    /** @var float - Rotation deltas of the player */
    public $currentYawDelta = 0.0, $lastYawDelta = 0.0, $currentPitchDelta = 0.0, $lastPitchDelta = 0.0;
    /** @var bool - The boolean value for if the player is on the ground. The client on-ground value is used for this. */
    public $onGround = true;
    /** @var bool - An expected value for the client's on ground. */
    public $expectedOnGround = true;
    /** @var int */
    public $onGroundTicks = 0, $offGroundTicks = 0;
    /** @var AABB */
    public $boundingBox;
    /** @var Vector3 */
    public $directionVector;
    /** @var int - Ticks since the player has taken motion. */
    public $ticksSinceMotion = 0;
    /** @var Vector3 */
    public $motion;
    /** @var bool */
    public $isCollidedVertically = false, $isCollidedHorizontally = false, $hasBlockAbove = false;
    /** @var int */
    public $ticksSinceInLiquid = 0, $ticksSinceInCobweb = 0, $ticksSinceInClimbable = 0;
    /** @var int - Movements passed since the user teleported. */
    public $ticksSinceTeleport = 0;
    /** @var bool */
    public $teleported = false;
    /** @var int - The amount of movements that have passed since the player has disabled flight. */
    public $ticksSinceFlight = 0;
    /** @var bool - Boolean value for if the player is flying. */
    public $isFlying = false;
    /** @var bool */
    public $hasFlyFlag = false;
    /** @var int - Movements that have passed since the user has jumped. */
    public $ticksSinceJump = 0;
    /** @var bool */
    public $hasMovementSuppressed = false;

    public $isSprinting = false;
    public $movementSpeed = 0.1;
    public $jumpVelocity = MovementConstants::DEFAULT_JUMP_MOTION;
    public $jumpMovementFactor = MovementConstants::JUMP_MOVE_NORMAL;

    /** @var int[] */
    public $clickSamples = [];
    /** @var bool - Boolean value for if autoclicker checks should run. */
    public $runClickChecks = false;
    /** @var float - Statistical data for autoclicker checks. */
    public $cps = 0.0, $kurtosis = 0.0, $skewness = 0.0, $deviation = 0.0, $outliers = 0.0, $variance = 0.0;
    /** @var int - Last tick the client clicked. */
    public $lastClickTick = 0;
    /** @var bool */
    public $isClickDataIsValid = true;


    public function tick() : void{
    }

}