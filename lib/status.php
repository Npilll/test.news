<?php
namespace Test\News;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

class StatusTable extends DataManager
{
    public static function getTableName() :string
    {
        return 'test_news_status';
    }

    public static function getMap() :array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new StringField('TITLE'))
                ->configureRequired(true)
                ->configureTitle(Loc::getMessage('TEST_STATUS_TITLE'))
                ->configureSize(255),
        ];
    }
}
?>