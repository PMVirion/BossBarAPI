<?php

namespace skh6075\bossbarapi;

use pocketmine\plugin\Plugin;

final class BossBarHandler{

    private static ?Plugin $registrant = null;


    public static function isRegistrant(): bool{
        return self::$registrant instanceof Plugin;
    }

    public static function getRegistrant(): ?Plugin{
        return self::$registrant;
    }

    public static function register(Plugin $plugin): void{
        if (self::isRegistrant()) {
            throw new \InvalidArgumentException("{$plugin->getName()} attempted to register " . self::class . " twice.");
        }
        self::$registrant = $plugin;
    }
}