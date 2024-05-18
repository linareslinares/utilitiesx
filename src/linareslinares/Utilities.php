<?php

namespace linareslinares;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\NetworkSessionManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\permission\Permission;
use pocketmine\player\GameMode;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\TransactionCancelledEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\entity\Skin;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use linareslinares\Particles;

use linareslinares\utils\FormAPI\SimpleForm;
use linareslinares\utils\FormAPI\CustomForm;
use linareslinares\utils\FormAPI\ModalForm;
use linareslinares\utils\FormAPI\Form;
use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class Utilities extends PluginBase implements Listener{

    public $hearts = [];
    public $torment = [];
    public $flame = [];
    public $happy = [];
    public $smoke = [];
    public $snowball = [];
    public $red = [];
    public $blue = [];
    public $green = [];
    public $yellow = [];
    public $aqua = [];
    public $pink = [];
    public $rainbow = [];
    protected $skin = [];
    private $cooldown = [];
    public $cps = [];
    public $cpsEnabled = [];
    private Config $pdata;

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->pdata = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new Particles($this), 5);
        $this->saveResource("config.yml");

        $serverlistname = $config->get("server-name-list", false);
        if ($serverlistname === true){
            $this->getServer()->getNetwork()->setName($config->get("server-name"));
        }

        if(is_array($config->get("list_capes"))) {
            foreach($config->get("list_capes") as $cape){
                $this->saveResource("$cape.png");
            }
            $config->set("list_capes", "done");
            $config->save();
        }

        if (!empty($config->get("Lobby-name"))) {
            $this->getServer()->getWorldManager()->loadWorld($config->get("Lobby-name"));
        } else {
            $this->getServer()->getLogger()->warning("Aun no esta fijado el Hub, no se cargo ningun mapa..");
        }
    }

    public function join(PlayerJoinEvent $ev){
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $player = $ev->getPlayer();
        $ev->setJoinMessage(TE::GREEN . "[+] " . $player->getName());
        $player->setGamemode(GameMode::SURVIVAL());
        $this->cpsEnabled[$ev->getPlayer()->getName()] = $config->getNested("CPS.Mostrar");

        $refilcomida = $config->get("food-refil", false);
        if ($refilcomida === true){
            $player->getHungerManager()->setFood(20);
        }

        $refilsalud = $config->get("health-refil", false);
        if ($refilsalud === true){
            $player->setHealth(20);
        }

        $soundtrue = $config->get("sound-join", false);
        if ($soundtrue === true){
            $this->PlaySound($player, "random.explode", 1, 1);
        }

        $titletrue = $config->get("show-title", false);
        if ($titletrue === true){
            $player->sendTitle(TE::GREEN . $config->get("Welcome-Title"), TE::YELLOW . $player->getName());
        }

        $clearinv = $config->get("clear-inv", false);
        if ($clearinv === true){
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
        }

        $cosmeticositem = VanillaItems::TOTEM();
        $cosmeticositem->setCustomName($config->get("item-name"));

        $itemshow = $config->get("show-item", false);
        if ($itemshow === true){
            $player->getInventory()->setItem(8, $cosmeticositem);
        }

        $staffalert = $config->get("staff-alert", false);
        if ($staffalert === true){
            if ($player instanceof Player){
                if ($player->hasPermission("join.staff")){
                    $this->getServer()->broadcastMessage("§8[§cSTAFF§8]§7 " . $player->getName() . " entro al servidor. ");
                }
            }
        }

        $this->skin[$player->getName()] = $player->getSkin();
        if(file_exists($this->getDataFolder() . $this->pdata->get($player->getName()) . ".png")) {
            $oldSkin = $player->getSkin();
            $capeData = $this->createCape($this->pdata->get($player->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

            $player->setSkin($setCape);
            $player->sendSkin();
        } else {
            $this->pdata->remove($player->getName());
            $this->pdata->save();
        }
        return true;
    }

    public function onPlayerLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $event->getPlayer()->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }

    public function onQuit(PlayerQuitEvent $ev){
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $player = $ev->getPlayer();
        $ev->setQuitMessage(TE::RED. "[-] " . $player->getName());
        unset($this->cps[$ev->getPlayer()->getName()]);

        $clearinv = $config->get("clear-inv", false);
        if ($clearinv === true){
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
        }
    }

    public function itemuse(PlayerItemUseEvent $ev): void {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $player = $ev->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if($item->getName() === $config->get("item-name")){
            $this->UtilitiesForm($player);
        }
    }

    public function UtilitiesForm($player){
        if ($player->hasPermission("fly.cmd")) {
            $msg3 = "§r§aToca para usar";
        }else{
            $msg3 = "§cBLOQUEADO";
        }
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return true;
            }

            switch ($data){
                case 0:
                    $mapName = $player->getWorld()->getFolderName();
                    $listmaps = $this->getConfig()->getNested("allow-cosmetics", []);
                    if (in_array($mapName, $listmaps)) {
                        if (!$player->hasPermission("fly.cmd")) {
                            $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                            return true;
                        }
                        if ($player->getAllowFlight()) {
                            $player->setAllowFlight(false);
                            $player->setFlying(false);
                            $player->sendTitle("§l§aVuelo §8[§7Desactivado§8]");
                        } else {
                            $player->setAllowFlight(true);
                            $player->sendTitle("§aVuelo §8[§7Activado§8]" , " §cEl uso inadecuado es baneable!! ");
                            $this->PlaySound($player, "random.levelup", 500, 1);
                        }
                    }else{
                        $player->sendMessage(TE::RED. "No puedes usar esto en este lugar");
                    }
                break;
                case 1:
                    $mapName = $player->getWorld()->getFolderName();
                    $listmaps = $this->getConfig()->getNested("allow-cosmetics", []);
                    if (in_array($mapName, $listmaps)) {
                        $this->SizeForm($player);
                    }else{
                        $player->sendMessage(TE::RED. "No puedes usar esto en este lugar");
                    }
                break;
                case 2:
                    $this->openCapeListUI($player);
                break;
                case 3:
                    $this->getTrails($player);
                break;
                case 4:
                    $this->OtherForm($player);
                break;
            }
        });
        $form->setTitle("§l§uUTILS-X");
        $form->setContent(" ");
        $form->addButton("§l§uFLY\n{$msg3}", 0, "textures/items/feather");
        $form->addButton("§l§uSIZE\n§r§aToca para abrir", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§l§uCAPES\n§r§aToca para abrir", 0, "textures/ui/dressing_room_capes");
        $form->addButton("§l§uPARTICLES§r\n§r§aToca para abrir", 0, "textures/ui/icon_best3");
        $form->addButton("§l§uOTHERS\n§r§aToca para abrir", 0, "textures/ui/icon_setting");
        $form->sendToPlayer($player);
        return $form;
    }

    public function OtherForm($player) {
        $config = $this->getConfig();
        $showCPS = $config->get("CPS")["Mostrar"];
        $form = new CustomForm(function(Player $player, $data) use ($config) {
            if ($data === null) {
                return;
            }
            $showCPS = (bool)$data[0];
            $config->setNested("CPS.Mostrar", $showCPS);
            $config->save();
            $this->cpsEnabled[$player->getName()] = $showCPS;
            $message = $showCPS ? "§8[§uPREFERENCIAS§8]§e Encendiste el contador de CPS." : "§8[§uPREFERENCIAS§8]§e Apagaste el contador de CPS.";
            $player->sendMessage(TE::YELLOW . $message);
            $this->PlaySound($player, "random.levelup", 3, 1);
        });
        $form->setTitle(TE::BOLD . "§uPREFERENCIAS");
        $toggleTitle = $showCPS ? "Ocultar contador de CPS" : "Mostrar contador de CPS";
        $form->addToggle("§u" . $toggleTitle, $showCPS);
        $form->sendToPlayer($player);
    }

    public function AjustesForm($player){
        if ($player->hasPermission("remove.hub")) {
            $msg1 = "§aToca para limpiar";
        }else{
            $msg1 = "§cBLOQUEADO";
        }
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return true;
            }

            switch ($data){
                case 0:
                    if($player->hasPermission("remove.hub")){
                        $this->getConfig()->set("Lobby-name", []);
                        $this->PlaySound($player, "random.anvil_use", 500, 1);
                        $player->sendMessage(TE::RED. "Se removio el Hub correctamente.");
                        $this->getConfig()->save();
                        $player->sendMessage(TE::YELLOW. "Puedes fijarlo de nuevo con /sethub set <mapName>");
                    }
                break;
            }
        });
        $form->setTitle("§l§uOWNER CONFIG");
        $form->setContent(TE::AQUA. "En futuras versiones se agregaran mas opciones.");
        $form->addButton("§l§cRemover Hub§r\n{$msg1}", 0, "textures/ui/icon_setting");
        $form->sendToPlayer($player);
        return $form;
    }

    public function SizeForm($player){
        if ($player->hasPermission("diminuto.size")) {
            $msg1 = "§aToca para activar";
        }else{
            $msg1 = "§cBLOQUEADO";
        }
        if ($player->hasPermission("pequeño.size")) {
            $msg2 = "§aToca para activar";
        }else{
            $msg2 = "§cBLOQUEADO";
        }
        if ($player->hasPermission("grande.size")) {
            $msg3 = "§aToca para activar";
        }else{
            $msg3 = "§cBLOQUEADO";
        }
        if ($player->hasPermission("enorme.size")) {
            $msg4 = "§aToca para activar";
        }else{
            $msg4 = "§cBLOQUEADO";
        }
        if ($player->hasPermission("gigante.size")) {
            $msg5 = "§aToca para activar";
        }else{
            $msg5 = "§cBLOQUEADO";
        }
        if ($player->hasPermission("custom.size")) {
            $msg6 = "§aToca para activar";
        }else{
            $msg6 = "§cBLOQUEADO";
        }
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return true;
            }

            switch($data){
                case 0:
                    $player->setScale("1.0");
                    $player->sendTitle("§6Tamaño §8[§7Normal§8]" , " §cVolviste a tu Tamaño normal!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 1:
                    if (!$player->hasPermission("diminuto.size")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $player->setScale("0.2");
                    $player->sendTitle("§6Tamaño §8[§7Diminuto§8]" , " §cEl uso inadecuado es baneable!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 2:
                    if (!$player->hasPermission("pequeño.size")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $player->setScale("0.5");
                    $player->sendTitle("§6Tamaño §8[§7Pequeño§8]" , " §cEl uso inadecuado es baneable!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 3:
                    if (!$player->hasPermission("grande.size")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $player->setScale("1.5");
                    $player->sendTitle("§6Tamaño §8[§7Grande§8]" , " §cEl uso inadecuado es baneable!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 4:
                    if (!$player->hasPermission("enorme.size")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $player->setScale("2.0");
                    $player->sendTitle("§6Tamaño §8[§7Enorme§8]" , " §cEl uso inadecuado es baneable!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 5:
                    if (!$player->hasPermission("gigante.size")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $player->setScale("2.5");
                    $player->sendTitle("§6Tamaño §8[§7Gigante§8]" , " §cEl uso inadecuado es baneable!! ");
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    break;

                case 6:
                    if (!$player->hasPermission("custom.size")){
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    $this->customSize($player);
                    break;
            }
        });
        $form->setTitle("§l§uTAMAÑOS");
        $form->setContent("");
        $form->addButton("§c§lReiniciar§r\n§eToca para reiniciar..", 0, "textures/ui/icon_none");
        $form->addButton("§e§lDiminuto§r\n{$msg1}", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lPequeño§r\n{$msg2}", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lGrande§r\n{$msg3}", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lEnorme§r\n{$msg4}", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lGigante§r\n{$msg5}", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lCustom§r\n{$msg6}", 0, "textures/ui/dressing_room_animation");
        $form->sendToPlayer($player);
        return $form;
    }

    public function customSize($player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return;
            }

            switch($data){
                case 0:
                    $this->customMin($player);
                    break;

                case 1:
                    $this->customMax($player);
                    break;
                case 2:
                    $this->SizeForm($player);
                    break;
            }
        });
        $form->setTitle("§l§uCUSTOM SIZE§r");
        $form->setContent("");
        $form->addButton("§e§lPequeño§r\n§7Toca para abrir", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§e§lGrande§r\n§7Toca para abrir", 0, "textures/ui/dressing_room_animation");
        $form->addButton("§c§lAtras\n§7Toca para regresar", 0, "textures/ui/cancel");
        $form->sendToPlayer($player);
        return $form;
    }

    public function customMin(Player $player){

        $form = new CustomForm(function(Player $player, array $data = null){
            if($data === null) {
                return true;
            }

            $index=$data[0];
            $result = "0.{$index}";
            $player->setScale($result);
            $player->sendTitle("§6Tamaño §8[§70.{$index}§8]" , " §cEl uso inadecuado es baneable!! ");
            $this->PlaySound($player, "random.levelup", 500, 1);

        });
        $form->setTitle("§l§uCUSTOM SIZE");
        $form->addLabel(TE::YELLOW. "Ajusta el tamaño a tu preferencia.");
        $form->addSlider("§l§d0§7", 2, 9);
        $form->sendToPlayer($player);
        return $form;
    }

    public function customMax(Player $player){

        $form = new CustomForm(function(Player $player, array $data = null){
            if($data === null) {
                return true;
            }
            $index=$data[0];
            $result = "1.{$index}";
            $player->setScale($result);
            $player->sendTitle("§6Tamaño §8[§71.{$index}§8]" , " §cEl uso inadecuado es baneable!! ");
            $this->PlaySound($player, "random.levelup", 500, 1);

        });
        $form->setTitle("§l§uCUSTOM SIZE");
        $form->addLabel(TE::YELLOW. "Ajusta el tamaño a tu preferencia.");
        $form->addSlider("§l§d1§r§7", 1, 9);
        $form->sendToPlayer($player);
        return $form;
    }

    public function createCape($capeName) {
        $path = $this->getDataFolder() . "{$capeName}.png";
        $img = @imagecreatefrompng($path);
        $bytes = '';
        $l = (int) @getimagesize($path)[1];

        for($y = 0; $y < $l; $y++) {
            for($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        @imagedestroy($img);

        return $bytes;
    }

    public function onChangeSkin(PlayerChangeSkinEvent $event) {
        $player = $event->getPlayer();

        $this->skin[$player->getName()] = $player->getSkin();
    }

    public function openCapeListUI($player) {
        $form = new SimpleForm(function(Player $player, $data = null) {
            $result = $data;

            if(is_null($result)) {
                return true;
            }

            $cape = $data;
            $noperms = "§cNo tienes permisos... si es un error abre ticket en Discord!";
            if(!file_exists($this->getDataFolder() . $data . ".png")) {
                $player->sendMessage("Tu skin no es compatible!");
            } else {
                if($player->hasPermission("$cape.cape")) {
                    $oldSkin = $player->getSkin();
                    $capeData = $this->createCape($cape);
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                    $player->setSkin($setCape);
                    $player->sendSkin();

                    $msg = "§aTu nueva capa es: {name} !";
                    $msg = str_replace("{name}", $cape, $msg);

                    $player->sendMessage($msg);
                    $this->PlaySound($player, "random.levelup", 500, 1);
                    $this->pdata->set($player->getName(), $cape);
                    $this->pdata->save();
                } else {
                    $player->sendMessage($noperms);
                }
            }
        });

        $form->setTitle("§l§uCAPAS");
        $form->setContent(" ");
        foreach($this->getCapes() as $capes) {
            if ($player->hasPermission("$capes.cape")) {
                $msg2 = "§r§aToca para activar";
            }else{
                $msg2 = "§r§cBLOQUEADO";
            }
            $form->addButton(TE::YELLOW . TE::BOLD . "$capes\n{$msg2}", -1, "", $capes);
        }
        $form->sendToPlayer($player);
    }

    public function getCapes() {
        $list = array();

        foreach(array_diff(scandir($this->getDataFolder()), ["..", "."]) as $data) {
            $dat = explode(".", $data);

            if($dat[1] == "png") {
                array_push($list, $dat[0]);
            }
        }

        return $list;
    }

    public function getTrails($player) {
        if ($player->hasPermission("hearts.cmd")) {
            $msg1 = "§r§aToca para activar";
        }else{
            $msg1 = "§r§cBLOQUEADO";
        }

        if ($player->hasPermission("flame.cmd")) {
            $msg2 = "§r§aToca para activar";
        }else{
            $msg2 = "§r§cBLOQUEADO";
        }

        if ($player->hasPermission("happy.cmd")) {
            $msg3 = "§r§aToca para activar";
        }else{
            $msg3 = "§r§cBLOQUEADO";
        }

        if ($player->hasPermission("smoke.cmd")) {
            $msg4 = "§r§aToca para activar";
        }else{
            $msg4 = "§r§cBLOQUEADO";
        }

        if ($player->hasPermission("snowball.cmd")) {
            $msg5 = "§r§aToca para activar";
        }else{
            $msg5 = "§r§cBLOQUEADO";
        }

        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return;
            }
            switch ($result) {
                case 0:
                    if (!$player->hasPermission("hearts.cmd")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    if (!in_array ($player->getName(), $this->hearts)) {
                        $this->hearts[] = $player->getName();
                        $player->sendMessage(TE::GRAY."has activado las particulas ".TE::AQUA."(corazones)");
                        $this->PlaySound($player, "random.levelup", 500, 1);
                        if (in_array ($player->getName(), $this->flame)) {
                            unset($this->flame[array_search($player->getName(), $this->flame)]);
                        } else if (in_array ($player->getName(), $this->happy)) {
                            unset($this->happy[array_search($player->getName(), $this->happy)]);
                        }  else if (in_array ($player->getName(), $this->torment)) {
                            unset($this->torment[array_search($player->getName(), $this->torment)]);
                        } else if (in_array ($player->getName(), $this->smoke)) {
                            unset($this->smoke[array_search($player->getName(), $this->smoke)]);
                        } else if (in_array ($player->getName(), $this->snowball)) {
                            unset($this->snowball[array_search($player->getName(), $this->snowball)]);
                        }
                    } else {
                        $player->sendMessage(TE::GRAY."ya tienes activadas las particulas");
                    }
                    break;
                case 1:
                    if (!$player->hasPermission("flame.cmd")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    if (!in_array ($player->getName(), $this->flame)) {
                        $this->flame[] = $player->getName();
                        $player->sendMessage(TE::GRAY."has activado las particulas ".TE::AQUA."(llamas)");
                        $this->PlaySound($player, "random.levelup", 500, 1);
                        if (in_array ($player->getName(), $this->hearts)) {
                            unset($this->hearts[array_search($player->getName(), $this->hearts)]);
                        }   else if (in_array ($player->getName(), $this->torment)) {
                            unset($this->torment[array_search($player->getName(), $this->torment)]);
                        } else if (in_array ($player->getName(), $this->happy)) {
                            unset($this->happy[array_search($player->getName(), $this->happy)]);
                        } else if (in_array ($player->getName(), $this->smoke)) {
                            unset($this->smoke[array_search($player->getName(), $this->smoke)]);
                        } else if (in_array ($player->getName(), $this->snowball)) {
                            unset($this->snowball[array_search($player->getName(), $this->snowball)]);
                        }
                    } else {
                        $player->sendMessage(TE::GRAY."ya tienes activadas las particulas");
                    }
                    break;
                case 2:
                    if (!$player->hasPermission("happy.cmd")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    if (!in_array ($player->getName(), $this->happy)) {
                        $this->happy[] = $player->getName();
                        $player->sendMessage(TE::GRAY."has activado las particulas ".TE::AQUA."(aldeano)");
                        $this->PlaySound($player, "random.levelup", 500, 1);
                        if (in_array ($player->getName(), $this->hearts)) {
                            unset($this->hearts[array_search($player->getName(), $this->hearts)]);
                        }   else if (in_array ($player->getName(), $this->torment)) {
                            unset($this->torment[array_search($player->getName(), $this->torment)]);
                        } else if (in_array ($player->getName(), $this->flame)) {
                            unset($this->flame[array_search($player->getName(), $this->flame)]);
                        } else if (in_array ($player->getName(), $this->smoke)) {
                            unset($this->smoke[array_search($player->getName(), $this->smoke)]);
                        } else if (in_array ($player->getName(), $this->snowball)) {
                            unset($this->snowball[array_search($player->getName(), $this->snowball)]);
                        }
                    } else {
                        $player->sendMessage(TE::GRAY."ya tienes activadas las particulas");
                    }
                    break;
                case 3:
                    if (!$player->hasPermission("smoke.cmd")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    if (!in_array ($player->getName(), $this->smoke)) {
                        $this->smoke[] = $player->getName();
                        $player->sendMessage(TE::GRAY."has activado las particulas ".TE::AQUA."(humo)");
                        $this->PlaySound($player, "random.levelup", 500, 1);
                        if (in_array ($player->getName(), $this->hearts)) {
                            unset($this->hearts[array_search($player->getName(), $this->hearts)]);
                        }   else if (in_array ($player->getName(), $this->torment)) {
                            unset($this->torment[array_search($player->getName(), $this->torment)]);
                        } else if (in_array ($player->getName(), $this->flame)) {
                            unset($this->flame[array_search($player->getName(), $this->flame)]);
                        } else if (in_array ($player->getName(), $this->happy)) {
                            unset($this->happy[array_search($player->getName(), $this->happy)]);
                        } else if (in_array ($player->getName(), $this->snowball)) {
                            unset($this->snowball[array_search($player->getName(), $this->snowball)]);
                        }
                    } else {
                        $player->sendMessage(TE::GRAY."ya tienes activadas las particulas");
                    }
                    break;
                case 4:
                    if (!$player->hasPermission("snowball.cmd")) {
                        $player->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return;
                    }
                    if (!in_array ($player->getName(), $this->snowball)) {
                        $this->snowball[] = $player->getName();
                        $player->sendMessage(TE::GRAY."has activado las particulas ".TE::AQUA."(nieve)");
                        $this->PlaySound($player, "random.levelup", 500, 1);
                        if (in_array ($player->getName(), $this->hearts)) {
                            unset($this->hearts[array_search($player->getName(), $this->hearts)]);
                        }   else if (in_array ($player->getName(), $this->torment)) {
                            unset($this->torment[array_search($player->getName(), $this->torment)]);
                        } else if (in_array ($player->getName(), $this->flame)) {
                            unset($this->flame[array_search($player->getName(), $this->flame)]);
                        } else if (in_array ($player->getName(), $this->happy)) {
                            unset($this->happy[array_search($player->getName(), $this->happy)]);
                        } else if (in_array ($player->getName(), $this->smoke)) {
                            unset($this->smoke[array_search($player->getName(), $this->smoke)]);
                        }
                    } else {
                        $player->sendMessage(TE::GRAY."ya tienes activadas las particulas");
                    }
                    break;
                case 5:
                    $player->sendMessage(TE::GRAY."has removido las particulas");
                    if (in_array ($player->getName(), $this->hearts)) {
                        unset($this->hearts[array_search($player->getName(), $this->hearts)]);
                    }   else if (in_array ($player->getName(), $this->torment)) {
                        unset($this->torment[array_search($player->getName(), $this->torment)]);
                    }  else if (in_array ($player->getName(), $this->flame)) {
                        unset($this->flame[array_search($player->getName(), $this->flame)]);
                    } else if (in_array ($player->getName(), $this->happy)) {
                        unset($this->happy[array_search($player->getName(), $this->happy)]);
                    } else if (in_array ($player->getName(), $this->smoke)) {
                        unset($this->smoke[array_search($player->getName(), $this->smoke)]);
                    } else if (in_array ($player->getName(), $this->snowball)) {
                        unset($this->snowball[array_search($player->getName(), $this->snowball)]);
                    }
                    break;
            }
        });
        $form->setTitle("§l§uPARTICULAS");
        $form->addButton(TE::BOLD . TE::YELLOW . "Corazones\n{$msg1}", 0,"textures/ui/health_boost_effect");
        $form->addButton(TE::BOLD . TE::YELLOW . "Llamas\n{$msg2}", 0,"textures/ui/icon_trending");
        $form->addButton(TE::BOLD . TE::YELLOW . "Aldeano\n{$msg3}", 0,"textures/ui/icon_deals");
        $form->addButton(TE::BOLD . TE::YELLOW . "Humo\n{$msg4}", 0,"textures/ui/flame_empty_image");
        $form->addButton(TE::BOLD . TE::YELLOW . "Nieve\n{$msg5}", 0,"textures/ui/icon_winter");
        $form->addButton(TE::RED. "Quitar particulas\n§r§7click", 0,"textures/ui/cancel");
        $form->sendToPlayer($player);
        return $form;
    }

    public function onCommand(CommandSender $s, Command $cmd, string $label, array $args) : bool {

        switch($cmd->getName()){
            case "fly":
                $mapName = $s->getWorld()->getFolderName();
                $listmaps = $this->getConfig()->getNested("allow-cosmetics", []);

                if (in_array($mapName, $listmaps)){
                    if(!$s->hasPermission("fly.cmd")){
                        $s->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                        return false;
                    }

                    if(!isset($args[0])){
                        $s->sendMessage("§7Usa /fly <on> <off>");
                        return false;
                    }

                    if($args[0] == "on"){
                        $s->sendTitle(" §eFly§b Activado " , " §cEl uso inadecuado es baneable!! ");
                        $s->setAllowFlight(true);

                    }

                    if($args[0] == "off"){
                        $s->sendTitle(" §eFly " , " §cDesactivado ");
                        $s->setFlying(false);
                        $s->setAllowFlight(false);
                    }
                }else{
                    $s->sendMessage(TE::RED. "No puedes usar esto en este lugar");
                }
                return true;

                break;

            case "ping":
                if($s->hasPermission("ping.cmd")){
                    if($s instanceof Player){
                        $ping = $s->getNetworkSession()->getPing();
                        $s->sendMessage(" » §aTu conexion esta en§7:§e " . $ping . " §ams");
                        $s->sendTitle(" »§e " . $ping . " §ams" , "§aEs tu conexion ");

                    }
                }
                return true;

                break;

            case "gm":

                if(!$s->hasPermission("gm.cmd")){
                    $s->sendMessage("§cNo tienes permisos... si es un error abre ticket en Discord!");
                    return false;
                }

                if(!isset($args[0])){
                    $s->sendMessage("§7usa /gm <0> <1> <3> ");
                    return false;
                }

                if($args[0] == "0"){
                    if($s instanceof Player){
                        $s->sendMessage("§7Cambiaste a §cSurvival! ");
                        $s->setGamemode(GameMode::SURVIVAL());
                    }
                }

                if($args[0] == "1"){
                    $s->sendMessage("§7Cambiaste a §bCreativo! ");
                    $s->sendTitle(" §bCreativo " , " §cEl uso inadecuado es baneable!! ");
                    $s->setGamemode(GameMode::CREATIVE());
                }

                if($args[0] == "3"){
                    $s->sendMessage("§7Cambiaste a §eSpectador! ");
                    $s->sendTitle(" §eSpectador" , " §cEl uso inadecuado es baneable!! ");
                    $s->setGamemode(GameMode::SPECTATOR());
                }

                return true;

                break;

            case "hub":
                if($s instanceof Player){
                    if ($s->hasPermission("hub.cmd")){
                        if (!empty($this->getConfig()->get("Lobby-name"))) {
                            $world = $this->getServer()->getWorldManager()->getWorldByName($this->getConfig()->get("Lobby-name"));
                            $s->teleport($world->getSafeSpawn());

                            $clearinv = $this->getConfig()->get("clear-inv", false);
                            if ($clearinv === true){
                                $s->getInventory()->clearAll();
                                $s->getArmorInventory()->clearAll();
                            }

                            $cosmeticositem = VanillaItems::TOTEM();
                            $cosmeticositem->setCustomName($this->getConfig()->get("item-name"));
                            $itemshow = $this->getConfig()->get("show-item", false);
                            if ($itemshow === true){
                                $s->getInventory()->setItem(8, $cosmeticositem);
                            }
                        } else {
                            $s->sendMessage(TE::RED. "El Hub no esta colocado. Usa /sethub set <MapName>");
                        }
                    }
                }

                return false;
                break;

            case "utils":
                $mapName = $s->getWorld()->getFolderName();
                $listmaps = $this->getConfig()->getNested("allow-cosmetics", []);
                if (in_array($mapName, $listmaps)) {
                    if($s instanceof Player){
                        if ($s->hasPermission("utils.cmd")){
                            $this->UtilitiesForm($s);
                        }
                    }
                }else{
                    $s->sendMessage(TE::RED. "No puedes usar esto en este lugar");
                }
                return false;
                break;
        }

        if($cmd->getName() === "sethub" && isset($args[0], $args[1])){
            $mapName = $args[1];
            if(!isset($args[0])){
                $s->sendMessage(TE::YELLOW. "Usa /sethub set <map>");
                return false;
            }

            if($s->hasPermission("set.hub")){
                if($args[0] === "set"){
                    $this->getConfig()->set("Lobby-name", $mapName);
                    $s->sendMessage(TE::GREEN. "Se fijo el mapa {$mapName} como el hub del servidor.");

                    $listmaps = $this->getConfig()->getNested("allow-cosmetics", []);
                    $listmaps[] = $mapName;
                    $this->getConfig()->setNested("allow-cosmetics", $listmaps);
                }else{
                    $s->sendMessage(TE::YELLOW. "Usa /sethub set <map>");
                }
                $this->getConfig()->save();
            }
        }
        return false;
    }

    public static function PlaySound(Player $player, string $sound, int $volume, float $pitch){
        $packet = new PlaySoundPacket();
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
        $packet->soundName = $sound;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public static function BroadSound(Player $player, string $soundName, int $volume, float $pitch){
        $packet = new PlaySoundPacket();
        $packet->soundName = $soundName;
        $position = $player->getPosition();
        $packet->x = $position->getX();
        $packet->y = $position->getY();
        $packet->z = $position->getZ();
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $world = $position->getWorld();
        NetworkBroadcastUtils::broadcastPackets($world->getPlayers(), [$packet]);
    }

    public function hasCooldown(Player $player): bool{
        return isset($this->cooldown[$player->getName()]) && $this->cooldown[$player->getName()] > time();
    }

    public function updateCooldown(Player $player): void{
        $this->cooldown[$player->getName()] = time() + 0;
    }

    public function addCPS(Player $player): void{
        $time = microtime(true);
        $this->cps[$player->getName()][] = $time;
    }

    public function getCPS(Player $player): int{
        $time = microtime(true);
        return count(array_filter($this->cps[$player->getName()] ?? [], static function(float $t) use ($time):bool{
            return ($time - $t) <= 1;
        }));
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();

        if($packet instanceof LevelSoundEventPacket){
            if($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE){
                $this->addCPS($player);
                if(isset($this->cpsEnabled[$player->getName()]) && $this->cpsEnabled[$player->getName()] === true){
                    $popup = str_replace("{cps}", $this->getCPS($player), "§e§lCPS: {cps}");
                    $player->sendPopup($popup);
                }
                if($this->getCPS($player) >= $config->getNested("CPS.Alertas")){
                    $players = $this->getServer()->getInstance()->getOnlinePlayers();
                    if(!$this->hasCooldown($player)){
                        $this->updateCooldown($player);
                        foreach($players as $playerName){
                            $offender = $player->getName();
                            if($playerName->hasPermission("cps.staff")){
                                $cpsAlerts = str_replace("{player}", $player->getName(), $config->getNested("CPS.Mensaje"));
                                $cpsAlerts = str_replace("{cps}", $this->getCPS($player), $cpsAlerts);
                                $playerName->sendMessage($cpsAlerts);
                            }
                        }
                    }
                }
            }
        }
        if($packet instanceof InventoryTransactionPacket){
            if($packet->trData instanceof UseItemOnEntityTransactionData){
                $this->addCPS($player);
                if(isset($this->cpsEnabled[$player->getName()]) && $this->cpsEnabled[$player->getName()] === true){
                    $popup = str_replace("{cps}", $this->getCPS($player), "§e§lCPS: {cps}");
                    $player->sendPopup($popup);
                }
                if($this->getCPS($player) >= $config->getNested("CPS.Alertas")){
                    $players = $this->getServer()->getInstance()->getOnlinePlayers();

                    if(!$this->hasCooldown($player)){
                        $this->updateCooldown($player);
                        foreach($players as $playerName){
                            $offender = $player->getName();
                            if($playerName->hasPermission("cps.staff")){
                                $cpsAlerts = str_replace("{player}", $player->getName(), $config->getNested("CPS.Mensaje"));
                                $cpsAlerts = str_replace("{cps}", $this->getCPS($player), $cpsAlerts);
                                $playerName->sendMessage($cpsAlerts);
                            }
                        }
                    }
                }
            }
            if($this->getCPS($player) > 35){
                $event->cancel();
            }
        }
    }
}
