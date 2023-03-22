<?php

namespace AmitxD\PrivateChat;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

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

                        $sender->sendMessage("Private chat enabled.");

                    } elseif ($args[1] === "off") {

                        unset($this->privateChatEnabled[$sender->getName()]);

                        $sender->sendMessage("Private chat disabled.");

                    } else {

                        return false;

                    }

                    break;

                case "add":

                    if (!isset($args[1])) {

                        return false;

                    }

                    if (!isset($this->privateChatMembers[$sender->getName()])) {

                        $this->privateChatMembers[$sender->getName()] = [];

                    }

                    array_push($this->privateChatMembers[$sender->getName()], strtolower($args[1]));

                    break;

                case "remove":

                    if (!isset($args[1])) {

                        return false;

                    }

                    if (isset($this->privateChatMembers[$sender->getName()])) {

                        $index = array_search(strtolower($args[1]), $this->privateChatMembers[$sender->getName()]);

                        if ($index !== false) {

                            unset($this->privateChatMembers[$sender->getName()][$index]);

                        }

                    }

                    break;

                case "help":

                    $sender->sendMessage("PrivateChat commands:\n/pc chat on/off - Enable/disable private chat\n/pc add playername - Add a player to your private chat group\n/pc remove playername - Remove a player from your private chat group\n/pc list - List members in your private chat group");

                    break;

                case "list":

                    if (isset($this->privateChatMembers[$sender->getName()])) {

                        $sender->sendMessage("Private chat members: " . implode(", ", $this->privateChatMembers[$sender->getName()]));

                    } else {

                        $sender->sendMessage("You have not added any members to your private chat.");

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

                $event->setCancelled();

                foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {

                    if (in_array(strtolower($onlinePlayer->getName()), $this->privateChatMembers[$player->getName()])) {

                        $onlinePlayer->sendMessage($player->getDisplayName() . " > " . $event->getMessage());

                    }

                }

            }

        }

}
