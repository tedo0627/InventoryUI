<?php

namespace tedo0627\inventoryui;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\plugin\PluginBase;
use tedo0627\inventoryui\exception\InventoryUIResourcePackException;

class InventoryUI {

    private static bool $setup = false;

    private const uuid = "21f0427f-572a-416d-a90e-c5d9becb0fa3";
    private const version = "1.1.0";

    public static function setup(PluginBase $plugin): void {
        if (self::$setup) return;

        $server = $plugin->getServer();

        $manager = $server->getResourcePackManager();
        if (!$manager->resourcePacksRequired()) {
            throw new InventoryUIResourcePackException("'force_resources' must be set to 'true'");
        }

        $pack = $manager->getPackById(self::uuid);
        if ($pack === null) {
            throw new InventoryUIResourcePackException("Resource pack 'Inventory UI Resource Pack' not found");
        }

        if ($pack->getPackVersion() !== self::version) {
            throw new InventoryUIResourcePackException("'Inventory UI Resource Pack' version did not match");
        }

        $server->getPluginManager()->registerEvents(new EventListener(), $plugin);

        $plugin->getScheduler()->scheduleRepeatingTask(new InventoryTickTask($server), 1);

        $nbt = new CompoundTag();
        $nbt->setString("bid", "");
        $nbt->setByte("hasspawnegg", false);
        $nbt->setString("id", "inventoryui:inventoryui");
        $nbt->setByte("summonable", false);

        $packet = StaticPacketCache::getInstance()->getAvailableActorIdentifiers();
        $tag = $packet->identifiers->getRoot();
        if ($tag instanceof CompoundTag) {
            $tag->getListTag("idlist")->push($nbt);
        }

        self::$setup = true;
    }
}