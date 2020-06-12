<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\language;

use hachkingtohach1\pvpteam\Main;

class Language { 

    public $plugin;

    public function __construct(Main $plugin) 
	{
        $this->plugin = $plugin;       
    } 

    public function translateLang(string $name) 
    {
		$lang = $this->plugin->language->get($name);
		return $lang;
    }
}