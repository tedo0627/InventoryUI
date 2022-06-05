<?php

namespace tedo0627\inventoryui;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\plugin\PluginBase;

class InventoryUI extends PluginBase implements Listener {

    private const uuid = "21f0427f-572a-416d-a90e-c5d9becb0fa3";
    private const version = "1.0.0";

    private CustomInventory $inv;

    public function onEnable(): void {
        $manager = $this->getServer()->getResourcePackManager();
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

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("inventoryui", new InventoryCommand());

        $this->inv = new CustomInventory(70, "Backpack");

        $this->getScheduler()->scheduleRepeatingTask(new InventoryTickTask($this->getServer()), 1);

        $packet = StaticPacketCache::getInstance()->getAvailableActorIdentifiers();
        $nbt = new CompoundTag();
        $nbt->setString("bid", "");
        $nbt->setByte("hasspawnegg", false);
        $nbt->setString("id", "inventoryui:inventoryui");
        $nbt->setByte("summonable", false);

        $tag = $packet->identifiers->getRoot();
        if ($tag instanceof CompoundTag) {
            $tag->getListTag("idlist")->push($nbt);
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $player->getNetworkSession()
            ->getInvManager()
            ?->getContainerOpenCallbacks()
            ->add(function(int $id, Inventory $inventory) use($player): ?array {
                if (!$inventory instanceof CustomInventory) return null;

                return $inventory->getPackets($player, $id);
            });
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        foreach ($transaction->getActions() as $action) {
            if (!$action instanceof SlotChangeAction) continue;

            $inventory = $action->getInventory();
            if (!$inventory instanceof CustomInventory) continue;

            $player = $transaction->getSource();
            $slot = $action->getSlot();
            if (!$inventory->click($player, $slot, $action->getSourceItem(), $action->getSourceItem())) continue;

            $event->cancel();
            return;
        }
    }

    public function onPlayerSneak(PlayerToggleSneakEvent $event): void {
        if ($event->isSneaking()) return;

        $player = $event->getPlayer();
        //$player->setCurrentWindow($this->inv);
        $type = $player->getInventory()->getHeldItemIndex();
        if ($type == 0) $type = 1;

        $player->setCurrentWindow(new CustomInventory($type * 9, "inventory", $type));
    }
}