<?php
namespace Test\News;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM;

Loc::loadMessages(__FILE__);

class NewsTable extends DataManager
{
    public static function getTableName() :string
    {
        return 'test_news_news';
    }

    public static function getMap() :array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new DatetimeField('CREATED_AT'))
                ->configureDefaultValueNow()
                ->configureTitle(Loc::getMessage('TEST_NEWS_CREATED_AT')),

            (new DatetimeField('UPDATED_AT'))
                ->configureDefaultValueNow()
                ->configureRequired(true)
                ->configureTitle(Loc::getMessage('TEST_NEWS_UPDATED_AT')),

            (new IntegerField('SORT'))
                ->configureDefaultValue(500)
                ->configureTitle(Loc::getMessage('TEST_NEWS_SORT')),

            (new IntegerField('STATUS_ID'))
                ->configureTitle(Loc::getMessage('TEST_NEWS_STATUS_ID')),

            (new Reference(
                'STATUS',
                StatusTable::class,
                Join::on('this.STATUS_ID', 'ref.ID')
            ))
                ->configureTitle(Loc::getMessage('TEST_NEWS_STATUS')),

            (new StringField('NAME'))
                ->configureRequired(true)
                ->configureTitle(Loc::getMessage('TEST_NEWS_NAME'))
                ->configureSize(255),

            (new TextField('DESCRIPTION'))
                ->configureTitle(Loc::getMessage('TEST_NEWS_DESCRIPTION'))
                ->configureRequired(true),

        ];
    }

    public static function onBeforeUpdate(ORM\Event $event) :ORM\EventResult
    {
        $result = new ORM\EventResult;
        $result->modifyFields(['UPDATED_AT' => new DateTime()]);
        return $result;
    }
}
?>