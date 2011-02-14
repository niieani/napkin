<?php

namespace Tools;

class System
{
    public static function getCPUs()
    {
    //  echo "Detecting number of CPU cores: ";
        return system("cat /proc/cpuinfo | grep \"core id\" | sort | uniq | wc -l");
    }
}