<?php

namespace HydraNetwork\SkyBlockMenu;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\island\IslandFactory;
use room17\SkyBlock\session\Session;
use room17\SkyBlock\session\SessionLocator;
use room17\SkyBlock\utils\Invitation;
use room17\SkyBlock\utils\message\MessageContainer;

class Main extends PluginBase implements Listener
{

    public function onEnable()
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $cmd = $command->getName();
        $session = SkyBlock::getInstance()->getSessionManager()->getSession($sender);
        if ($cmd === "is"){
            if (!$session->hasIsland()){
                $this->onCreateMenu($sender);
            }else{
                $this->onManage($sender);
            }
        }
        return true;
    }
    
    public function onCreateMenu(Player $sender)
    {
        $form = new SimpleForm(function (Player $sender, $data){
            if ($data === null) {
                return true;
            }
            $session = SkyBlock::getInstance()->getSessionManager()->getSession($sender);
            switch ($data){
                case 0:
                    $this->onCreate($sender);
                    break;
                case 1:
                    $this->getServer()->dispatchCommand($sender, "sb accept");
                    break;
            }
        });
        $form->setTitle("Skyblock");
        $form->addButton("§8Create your Island\n§o§7Tap to next page");
        $form->addButton("§8Accept Invite\n§o§7Tap to accept");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function onCreate(Player $sender)
    {
        $form = new SimpleForm(function (Player $sender, $data){
            if ($data === null) {
                return true;
            }
            $session = SkyBlock::getInstance()->getSessionManager()->getSession($sender);
            switch ($data){
                case 0:
                    $this->getServer()->dispatchCommand($sender, "sb create Basic");
                    break;
                case 1:
                    $this->getServer()->dispatchCommand($sender, "sb create OP");
                    break;
            }
        });
        $form->setTitle("Choose Your Island");
        $form->addButton("§8Basic Island\n§o§7Tap to create");
        $form->addButton("§8Wide Island\n§o§7Tap to create");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function onManage(Player $sender)
    {
        $form = new SimpleForm(function (Player $sender, $data){
            if ($data === null) {
                return true;
            }
            $session = SkyBlock::getInstance()->getSessionManager()->getSession($sender);
            switch ($data){
                case 0:
                    $session->getPlayer()->teleport($session->getIsland()->getLevel()->getSpawnLocation());
                    $sender->sendMessage(TextFormat::YELLOW . "Welcome to Your island");
                    break;
                case 1:
                    $this->getServer()->dispatchCommand($sender, "sb lock");
                    break;
                case 2:
                    $this->getServer()->dispatchCommand($sender, "sb setspawn");
                    break;
                case 3:
                    $this->onTransferIs($sender);
                    break;
                case 4:
                    $this->onDisbandIs($sender);
                    break;
                case 5:
                    $this->onVisitIs($sender);
                    break;
                case 6:
                    $this->onManageF($sender);
                    break;
            }
        });
        $form->setTitle("Skyblock");
        $form->setContent("Quick access to Skyblock commands");
        $form->addButton("§8Go to Island\n§7Click to select!");
        $form->addButton("§8Lock/Unlock Island\n§7Click to select!");
        $form->addButton("§8Setspawn Island\n§7Click to select!");
        $form->addButton("§8Transfer your Island\n§7Click to select!");
        $form->addButton("§8Delete your Island\n§7Click to select!");
        $form->addButton("§8Visit Island Public\n§7Click to select!");
        $form->addButton("§8Manage Players\n§7Click to select!");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function onManageF(Player $sender)
    {
        $form = new SimpleForm(function (Player $sender, $data){
            if ($data === null) {
                return true;
            }
            switch ($data){
                case 0:
                    $this->onManageAF($sender);
                    break;
                case 1:
                    $this->onManageRF($sender);
                    break;
                case 2:
                    $this->onManage($sender);
                    break;
                case 3:
                    $this->onManageCo($sender);
                    break;
                case 4:
                    $this->onManage($sender);
                    break;
            }
        });
        $form->setTitle("Manage Players");
        $form->setContent("§eTIPS: §7If you want to invite your friends, your friends must not have an island first and after you have invited your friends tell your friends to type §f/sb accept.");
        $form->addButton("§8Invite a Player\n§7Click to select!");
        $form->addButton("§8Kick a Player\n§7Click to select!");
        $form->addButton("§8Leave an Island\n§7Click to select!");
        $form->addButton("§8Cooperate with a Player\n§7Click to select!");
        $form->addButton("§8Back\n§7Click to select!");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function onManageCo($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
           $form = $api->createCustomForm(function(Player $player, $result){
           if($result === null){
              return;
           }
           if(trim($result[0]) === ""){
              $player->sendActionbarMessage("§cPlease input name");
              return;
           }
           $this->getServer()->getCommandMap()->dispatch($player, "sb cooperate ".$result[0]);
        });
        $form->setTitle("Cooperate Player");
        $form->addInput("Input the name");
        $player->sendForm($form);
        }

    public function onManageAF($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "sb invite ".$result[0]);
      });
      $form->setTitle("Invite Player");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }

    public function onManageRF($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "sb fire ".$result[0]);
      });
      $form->setTitle("Kick Player");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }
    
    public function onVisitIs($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "sb visit ".$result[0]);
      });
      $form->setTitle("Visit Island Public");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }
      
    public function onTransferIs($player){ 
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
         $form = $api->createCustomForm(function(Player $player, $result){
         if($result === null){
            return;
         }
         if(trim($result[0]) === ""){
            $player->sendActionbarMessage("§cPlease input name");
            return;
         }
         $this->getServer()->getCommandMap()->dispatch($player, "sb transfer ".$result[0]);
      });
      $form->setTitle("Transfer Island");
      $form->addInput("Input the name");
      $player->sendForm($form);
      }
    
    public function onDisbandIs(Player $sender)
    {
        $form = new SimpleForm(function (Player $sender, $data){
            if ($data === null) {
                return true;
            }
            switch ($data){
                case 0:
                    $this->getServer()->dispatchCommand($sender, "sb disband");
                    break;
                case 1:
                    $this->onManage($sender);
                    break;
            }
        });
        $form->setTitle("Are You Sure?");
        $form->addButton("§8Yes!\n§7Click to select!");
        $form->addButton("§8No!\n§7Click to select!");
        $form->sendToPlayer($sender);
        return $form;
    }

}
