<?php

namespace tedo0627\inventoryui;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\plugin\PluginBase;

class InventoryUI extends PluginBase implements Listener {

    private CustomInventory $inv;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("inventoryui", new InventoryCommand());

        $this->inv = new CustomInventory(20);
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void {
        foreach ($event->getPackets() as $packet) {
            if (!$packet instanceof AvailableActorIdentifiersPacket) continue;

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
            if (!$inventory->click($player, $slot)) continue;

            $event->cancel();
            return;
        }
    }

    public function onPlayerSneak(PlayerToggleSneakEvent $event): void {
        if ($event->isSneaking()) return;

        $player = $event->getPlayer();
        $player->setCurrentWindow($this->inv);
    }
}