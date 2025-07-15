<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class TestNews
{
    const MODULE_ID = 'test.news';

    public static function getModulePath()
    {
        return __DIR__;
    }
}
?>