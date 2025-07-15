<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use \Test\News\StatusTable;

Loader::includeModule('test.news');

$statuses = StatusTable::getList()->fetchAll();
$statusesArray = [0 => 'По умолчанию'];
$statusesArray += array_column($statuses, 'TITLE', 'ID');

// Формирование массива параметров
$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "DEFAULT_STATUS"   =>  array(
            "PARENT"    =>  "BASE",
            "NAME"      =>  GetMessage("NEWS_DEFAULT_STATUS"),
            "TYPE"      =>  "LIST",
            "VALUES"  =>  $statusesArray,
        ),
    ),
);
