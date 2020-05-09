<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\math;

class Vector3 extends \pocketmine\math\Vector3 {  

   /**
    * @param string $string
    * @return Vector3
    */
    public static function fromString(string $string) {
        return new Vector3(
		    (int)explode(",", $string)[0],
			(int)explode(",", $string)[1],
			(int)explode(",", $string)[2]
		);
    }
	
   /**
    * @return string
    */
    public function __toString() {
        return "$this->x,$this->y,$this->z";
    }
}