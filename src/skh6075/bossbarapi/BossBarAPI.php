<?php

namespace skh6075\bossbarapi;

use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\Player;

class BossBarAPI{

    private static int $entityId;

    private static AttributeMap $attributeMap;

    private static DataPropertyManager $propertyManager;

    /** @var array */
    protected array $players = [];


    public function __construct() {
        self::$entityId = Entity::$entityCount ++;

        self::$attributeMap = new AttributeMap();
        self::$attributeMap->addAttribute(Attribute::getAttribute(Attribute::HEALTH)
            ->setMaxValue(100.0)
            ->setMinValue(0.0)
            ->setDefaultValue(100.0)
        );

        self::$propertyManager = new DataPropertyManager();
        self::$propertyManager->setLong(Entity::DATA_FLAGS, 0
            ^ 1 << Entity::DATA_FLAG_SILENT
            ^ 1 << Entity::DATA_FLAG_INVISIBLE
            ^ 1 << Entity::DATA_FLAG_NO_AI
            ^ 1 << Entity::DATA_FLAG_FIRE_IMMUNE
        );
        self::$propertyManager->setShort(Entity::DATA_MAX_AIR, 400);
        self::$propertyManager->setString(Entity::DATA_NAMETAG, "");
        self::$propertyManager->setLong(Entity::DATA_LEAD_HOLDER_EID, -1);
        self::$propertyManager->setFloat(Entity::DATA_SCALE, 0);
        self::$propertyManager->setFloat(Entity::DATA_BOUNDING_BOX_WIDTH, 0.0);
        self::$propertyManager->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 0.0);
    }

    public function getAttributeMap(): AttributeMap{
        return self::$attributeMap;
    }

    public function getPropertyManager(): DataPropertyManager{
        return self::$propertyManager;
    }

    public function sendSpawnPacket(Player $player): void{
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = self::$entityId;
        $pk->type = "minecraft:slime";
        $pk->attributes = self::$attributeMap->getAll();
        $pk->metadata = self::$propertyManager->getAll();
        $pk->position = $player->subtract(0, 28);
        $player->sendDataPacket($pk);
    }

    public function removeBossBar(Player $player): void{
        if (isset($this->players[$player->getLowerCaseName()])) {
            $pk = new BossEventPacket();
            $pk->bossEid = self::$entityId;
            $pk->eventType = BossEventPacket::TYPE_HIDE;
            $player->sendDataPacket($pk);
        }
    }

    public function setBossText(Player $player, string $text): void{
        if (isset($this->players[$player->getLowerCaseName()])) {
            $pk = new BossEventPacket();
            $pk->bossEid = self::$entityId;
            $pk->bossEid = BossEventPacket::TYPE_TITLE;
            $pk->title = $text;
            $player->sendDataPacket($pk);
        }
    }

    public function setBossHealth(Player $player, float $percentage): void{
        if (isset($this->players[$player->getLowerCaseName()])) {
            $pk = new BossEventPacket();
            $pk->bossEid = self::$entityId;
            $pk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
            $pk->healthPercent = $percentage;
            $player->sendDataPacket($pk);
        }
    }

    public function sendBossBar(Player $player, string $text, float $percentage): void{
        $this->sendSpawnPacket($player);
        $this->removeBossBar($player);

        $pk = new BossEventPacket();
        $pk->bossEid = self::$entityId;
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->title = $text;
        $pk->healthPercent = $percentage;
        $pk->color = 1;
        $pk->overlay = 1;
        $pk->unknownShort = 0;
        $player->sendDataPacket($pk);
        $this->players[$player->getLowerCaseName()] = clone $pk;
    }
}
