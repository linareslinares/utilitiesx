<?php
namespace linareslinares;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\math\Vector3;
use pocketmine\world\particle\{DustParticle, CriticalParticle};
use pocketmine\level\Level;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\SnowballPoofParticle;
use linareslinares\Utilities;
class Particles extends Task {
	private Utilities $main;
    public function __construct(Utilities $main) {
        $this->main = $main;
    }
	public function onRun(): void{
		foreach($this->main->getServer()->getWorldManager()->getWorlds() as $world){
            foreach($world->getEntities() as $pl){
                if($pl instanceof Player){
		//foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
			
			$x = $pl->getLocation()->getX();
			$y = $pl->getLocation()->getY() + 2;
			$z = $pl->getLocation()->getZ();
			$player = $pl->getWorld();
			$c = $pl->getWorld();
			if (in_array ($pl->getName(), $this->main->red)) {
				$r = 255;
				$b = 0;
				$g = 0;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(255,0,0));
				
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$ce = $x.$y.$z;
					
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->blue)) {
				$r = 0;
				$b = 255;
				$g = 0;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(0,255,0));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->green)) {
				$r = 0;
				$b = 0;
				$g = 255;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(0,0,255));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->yellow)) {
				$r = 255;
				$b = 0;
				$g = 255;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(255,0,255));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->aqua)) {
				$r = 0;
				$b = 255;
				$g = 220;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(0,255,220));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->pink)) {
				$r = 255;
				$b = 255;
				$g = 35;
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(255,255,35));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->rainbow)) {
				$r = rand(1, 200);
				$b = rand(1, 200);
				$g = rand(1, 200);
				$center = new Vector3($x, $y, $z, $c);
				$rgb = $r.$b.$g;
				$particle = new DustParticle(new \pocketmine\color\Color(rand(1,200),rand(1,200),rand(1,200)));
				for ($yaw = 0;  $yaw <= 10; $yaw += (M_PI * 2) / 20) {
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					$ce = $x.$y.$z;
					$player->addParticle($center, $particle);
				}
			}
			if (in_array ($pl->getName(), $this->main->hearts)) {
				$x = $pl->getLocation()->getX() + 0.1;
			$y = $pl->getLocation()->getY() + -0.1;
			$z = $pl->getLocation()->getZ() + 0.1;
			$center = new Vector3($x, $y, $z, $c);
			
				$a = $pl->getLocation()->getX() + -0.1;
			$b = $pl->getLocation()->getY() + -0.1;
			$d = $pl->getLocation()->getZ() + -0.1;
			$vect = new Vector3($a, $b, $d, $c);
				$pl->getWorld()->addParticle($vect, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($center, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($vect, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($center, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($vect, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($center, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($vect, new HeartParticle(10, $pl)); 
				$pl->getWorld()->addParticle($center, new HeartParticle(10, $pl)); 
				
			}
			if (in_array ($pl->getName(), $this->main->flame)) {
				$x = $pl->getLocation()->getX() + 0.1;
			$y = $pl->getLocation()->getY() + -0.1;
			$z = $pl->getLocation()->getZ() + 0.1;
			$center = new Vector3($x, $y, $z, $c);
			
				$a = $pl->getLocation()->getX() + -0.1;
			$b = $pl->getLocation()->getY() + -0.1;
			$d = $pl->getLocation()->getZ() + -0.1;
			$vect = new Vector3($a, $b, $d, $c);
				$pl->getWorld()->addParticle($vect, new FlameParticle($pl));
				$pl->getWorld()->addParticle($center, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($center, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($center, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new FlameParticle($pl)); 
				$pl->getWorld()->addParticle($center, new FlameParticle($pl)); 
			}
			if (in_array ($pl->getName(), $this->main->happy)) {
				$x = $pl->getLocation()->getX() + 0.1;
			$y = $pl->getLocation()->getY() + -0.1;
			$z = $pl->getLocation()->getZ() + 0.1;
			$center = new Vector3($x, $y, $z, $c);
			
				$a = $pl->getLocation()->getX() + -0.1;
			$b = $pl->getLocation()->getY() + -0.1;
			$d = $pl->getLocation()->getZ() + -0.1;
			$vect = new Vector3($a, $b, $d, $c);
				$pl->getWorld()->addParticle($vect, new HappyVillagerParticle($pl));
				$pl->getWorld()->addParticle($center, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($center, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($center, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new HappyVillagerParticle($pl)); 
				$pl->getWorld()->addParticle($center, new HappyVillagerParticle($pl)); 
			}
			if (in_array ($pl->getName(), $this->main->smoke)) {
				$x = $pl->getLocation()->getX() + 0.1;
			$y = $pl->getLocation()->getY() + -0.1;
			$z = $pl->getLocation()->getZ() + 0.1;
			$center = new Vector3($x, $y, $z, $c);
			
				$a = $pl->getLocation()->getX() + -0.1;
			$b = $pl->getLocation()->getY() + -0.1;
			$d = $pl->getLocation()->getZ() + -0.1;
			$vect = new Vector3($a, $b, $d, $c);
			$pl->getWorld()->addParticle($vect, new SmokeParticle()); 
			$pl->getWorld()->addParticle($center, new SmokeParticle()); 
			$pl->getWorld()->addParticle($vect, new SmokeParticle()); 
			$pl->getWorld()->addParticle($center, new SmokeParticle()); 
			$pl->getWorld()->addParticle($vect, new SmokeParticle()); 
			$pl->getWorld()->addParticle($center, new SmokeParticle()); 
			$pl->getWorld()->addParticle($vect, new SmokeParticle()); 
			$pl->getWorld()->addParticle($center, new SmokeParticle()); 
			}
			if (in_array ($pl->getName(), $this->main->snowball)) {
				$x = $pl->getLocation()->getX() + 0.1;
			$y = $pl->getLocation()->getY() + -0.1;
			$z = $pl->getLocation()->getZ() + 0.1;
			$center = new Vector3($x, $y, $z, $c);
			
				$a = $pl->getLocation()->getX() + -0.1;
			$b = $pl->getLocation()->getY() + -0.1;
			$d = $pl->getLocation()->getZ() + -0.1;
			$vect = new Vector3($a, $b, $d, $c);
				$pl->getWorld()->addParticle($vect, new SnowballPoofParticle($pl));
				$pl->getWorld()->addParticle($center, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($center, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($center, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($vect, new SnowballPoofParticle($pl)); 
				$pl->getWorld()->addParticle($center, new SnowballPoofParticle($pl)); 
			}
		}
		} 
		} 
	}
}