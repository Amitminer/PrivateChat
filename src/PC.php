<?php

namespace AmitxD\PrivateChat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\Server;

class PC extends PluginBase implements Listener {

    private $privateChatEnabled = [];
    private $privateChatMembers = [];

    public function onEnable() : void {
        $this->getLogger()->info("PrivateMine is enabled");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "pc") {
            if (!isset($args[0])) {
                return false;
            }
            switch ($args[0]) {
    case "chat":
        if (!isset($args[1])) {
            return false;
        }
        if ($args[1] === "on") {
            $this->privateChatEnabled[$sender->getName()] = true;
            $this->privateChatMembers[$sender->getName()][] = strtolower($sender->getName());
            $sender->sendMessage("§l§d[PrivateChat] §aPrivate chat enabled.");
        } elseif ($args[1] === "off") {
            unset($this->privateChatEnabled[$sender->getName()]);
            $sender->sendMessage("§l§d[PrivateChat] §cPrivate chat disabled.");
        } else {
            return false;
        }
        break;
        # Add Command 
                case "add":
    if (!isset($args[1])) {
        return false;
    }
    $senderName = strtolower($sender->getName());
    $playerToAdd = strtolower($args[1]);

    if (!isset($this->privateChatMembers[$senderName])) {
        $this->privateChatMembers[$senderName] = [];
    }

    if (in_array($playerToAdd, $this->privateChatMembers[$senderName])) {
        $sender->sendMessage("§d[PrivateChat] §c$playerToAdd is already in your private chat group.");
    } elseif (!Server::getInstance()->getPlayerExact($playerToAdd)) {
        $sender->sendMessage("§d[PrivateChat] §c$playerToAdd is not currently online.");
    } elseif ($playerToAdd === $senderName) {
        $sender->sendMessage("§d[PrivateChat] §cYou cannot add yourself to your private chat group.");
    } elseif (count($this->privateChatMembers[$senderName]) >= 6) {
        $sender->sendMessage("§d[PrivateChat] §cYou can only add 6 players to your private chat group.");
    } else {
        $this->privateChatMembers[$senderName][] = $playerToAdd;
        $sender->sendMessage("§d[PrivateChat] §aAdded $playerToAdd to your private chat group.");
    }
    break;
                case "remove":
    if (!isset($args[1])) {
        return false;
    }
    if (isset($this->privateChatMembers[$sender->getName()])) {
        $index = array_search(strtolower($args[1]), $this->privateChatMembers[$sender->getName()]);
        if ($index !== false) {
            $removedPlayer = $this->privateChatMembers[$sender->getName()][$index];
            unset($this->privateChatMembers[$sender->getName()][$index]);
            $sender->sendMessage("§d[PrivateChat] §r §e{$removedPlayer}§r has been removed from your private chat group.");
        } else {
            $sender->sendMessage("§d[PrivateChat] §e{$args[1]}§r is not in your private chat group.");
        }
    } else {
        $sender->sendMessage("§d[PrivateChat] §c You have not added any members to your private chat.");
    }
    break;
                case "help":
    $sender->sendMessage("§aPrivateChat commands:\n§b/pc chat on/off§r - Enable/disable private chat\n§b/pc add playername§r - Add a player to your private chat group\n§b/pc remove playername§r - Remove a player from your private chat group\n§b/pc list§r - List members in your private chat group");
    break;
                case "list":
    if (isset($this->privateChatMembers[$sender->getName()])) {
        $sender->sendMessage("§d[PrivateChat] §aPrivate chat members:\n§c " . implode(", ", $this->privateChatMembers[$sender->getName()]));
    } else {
        $sender->sendMessage("§d[PrivateChat] §cYou have not added any members to your private chat.");
    }
    break;
                default:
                    return false;
                }
                return true;
            }
            return false;
        }

        public function onPlayerChat(PlayerChatEvent $event) {
            $player = $event->getPlayer();
            if (isset($this->privateChatEnabled[$player->getName()])) {
                $event->cancel();
                foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $senderName = $player->getName();
                    if (isset($this->privateChatMembers[$senderName]) && in_array(strtolower($onlinePlayer->getName()), $this->privateChatMembers[$senderName])) {
                        $onlinePlayer->sendMessage("§b[PrivateChat] §a" . $player->getDisplayName() . " §r> " . $event->getMessage());
                    }
                }
            }
        }
    }
