<?php

namespace tedo0627\inventoryui;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class InventoryCommand extends Command {

    public function __construct() {
        parent::__construct("inv", "", "", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) return;

        if (count($args) <= 0) return;

        $slot = intval($args[0]);
        $sender->setCurrentWindow(new CustomInventory($slot));
    }
}