<?php
namespace Test\News;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class AgentControl
{
    public static function AgentAddNew() :string
    {
        NewsTable::add([
            'STATUS_ID' => 3,
            'NAME' => Helper::generateRandomString(),
            'DESCRIPTION' => Helper::generateRandomText(),
        ]);
        return '\\'.__CLASS__ .'::'.__FUNCTION__.'();';
    }
}
?>