<?php

namespace AmitxD\PrivateChat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\Server;

class PrivateChat extends PluginBase implements Listener{
  
  private $privateChatEnabled = [];
  private $privateChatMembers = [];
  
  public function onEnable() : void {
    $this->getLogger()->info("PrivateChat has been enabled!");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function onDisable(): void {
    $this->getLogger()->info("PrivateChat has been disabled!");
  }
  
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    if($command->getName() === "pc"){
        if (!$sender instanceof Player) {
                $sender->sendMessage("§cThis command can only be used in-game.");
                return true;
        }
      if(count($args) < 1){
        $sender->sendMessage("§cUsage:§a /pc <add|remove|list|chat>");
        return true;
      }
      switch(strtolower($args[0])){
        case "add":
          if(count($args) < 2){
            $sender->sendMessage("§cUsage: §a/pc add <playername>");
            return true;
          }
          $player = $this->getServer()->getPlayerExact($args[1]);
          if(!$player instanceof Player){
            $sender->sendMessage("§d[PrivateChat §cPlayer not found or not online!");
            return true;
          }
          if(isset($this->privateChatMembers[$player->getName()])){
            $sender->sendMessage("§6" . $player->getName()."§c is already in another private chat group!");
            return true;
          }
          $senderName = $sender->getName();
          $this->privateChatMembers[$senderName][] = strtolower($player->getName());
          $sender->sendMessage("§d[PrivateChat §aAdded §6".$player->getName()."§a to your private chat group.");
          return true;
          break;
        case "remove":
    if (count($args) < 2) {
        $sender->sendMessage("§cUsage: §a/pc remove <playername>");
        return true;
    }
    $player = $this->getServer()->getPlayerExact($args[1]);
    if (!$player instanceof Player) {
        $sender->sendMessage("§d[PrivateChat §cPlayer not found or not online!");
        return true;
    }
    $senderName = $sender->getName();
    $playerName = strtolower($player->getName());
    if (isset($this->privateChatMembers[$senderName]) && in_array($playerName, $this->privateChatMembers[$senderName])) {
        $rmplyer = array_search($playerName, $this->privateChatMembers[$senderName]);
        unset($this->privateChatMembers[$senderName][$rmplyer]);
        $sender->sendMessage("§d[PrivateChat §6Removed §c".$player->getName()." §6from your private chat group.");
        return true;
    }
    $sender->sendMessage("§d[PrivateChat §c".$player->getName()."§6 is not in your private chat group.");
    return true;
          break;
        case "list":
          $senderName = $sender->getName();
          if(isset($this->privateChatMembers[$senderName])){
            $sender->sendMessage("§d[PrivateChat] §aPrivate chat members:\n§c");
            foreach($this->privateChatMembers[$senderName] as $playerName){
              $sender->sendMessage(" - ".$playerName);
            }
          }else{
            $sender->sendMessage("§d[PrivateChat§c You are not in a private chat group.");
          }
          return true;
          break;
        case "chat":
    if (!isset($args[1])) {
        $sender->sendMessage("§cUsage: §a/pc chat <on|off>");
        return true;
    }

    $status = strtolower($args[1]);
    if ($status === "on") {
        if (isset($this->privateChatEnabled[$sender->getName()])) {
            $sender->sendMessage("§l§d[PrivateChat] §cYou have already enabled Privatechat!");
            return true;
        }
        $this->privateChatMembers[$sender->getName()] = [$sender->getName()];
        $this->privateChatEnabled[$sender->getName()] = true;
        $sender->sendMessage("§l§d[PrivateChat] §aPrivate chat enabled.");
    } elseif ($status === "off") {
        if (!isset($this->privateChatEnabled[$sender->getName()])) {
            $sender->sendMessage("§l§d[PrivateChat] §cYou have already disabled Privatechat!");
            return true;
        }
        unset($this->privateChatEnabled[$sender->getName()]);
        unset($this->privateChatMembers[$senderName][$senderName]);
        $sender->sendMessage("§l§d[PrivateChat] §cPrivate chat disabled.");
    } else {
        $sender->sendMessage("§cUsage: §a/pc chat <on|off>");
        return true;
        break;
    }
    case "help":
    $sender->sendMessage("§6PrivateChat Commands:");
    $sender->sendMessage("§e/pc add <player> §f- Add a player to your private chat group.");
    $sender->sendMessage("§e/pc remove <player> §f- Remove a player from your private chat group.");
    $sender->sendMessage("§e/pc list §f- List the members of your private chat group.");
    $sender->sendMessage("§e/pc chat on §f- Turn on private chat mode.");
    $sender->sendMessage("§e/pc chat off §f- Turn off private chat mode.");
    return true;
          break;

        default:
          $sender->sendMessage("§cUnknown command. Use §a/pc help §cfor a list of commands.");
          return true;
      }
    } else {
      $sender->sendMessage("§cUsage: /pc <add/remove/list/chat/help>");
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
                $onlinePlayer->sendMessage("§b[PrivateChat] §a" . $player->getDisplayName() . "§r> " . $event->getMessage());
            }
        }
    }
}
}
