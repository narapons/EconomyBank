<?php

    namespace EconomyBank;


    use pocketmine\plugin\Plugin;
    use pocketmine\scheduler\Task;

    class saveTask extends Task
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onRun(int $currentTick)
        {
            $this->plugin->money->save();
        }

    }