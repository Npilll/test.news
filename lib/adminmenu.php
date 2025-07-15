<?
namespace Test\News;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class AdminMenu
{
    public static function onBuildGlobalMenu(array &$aGlobalMenu, array &$aModuleMenu) :void
    {
        // Проверяем, что модуль установлен и подключен
        if (!Loader::includeModule('test.news')) {
            return;
        }

        // Проверяем права доступа
        if ($GLOBALS['APPLICATION']->GetGroupRight('test.news') < 'R') {
            return;
        }


        // Добавляем пункты меню
        $aModuleMenu[] = [
            'parent_menu' => 'global_menu_content',
            'section' => 'test_news',
            'sort' => 100,
            'text' => Loc::getMessage('NEWS_MENU_TITLE'),
            'title' => Loc::getMessage('NEWS_MENU_TITLE'),
            'items_id' => 'test_news_main',
            'items' => [
                [
                    'text' => Loc::getMessage('NEWS_MENU_LIST'),
                    'title' => Loc::getMessage('NEWS_MENU_LIST'),
                    'url' => 'test_news_list.php?lang='.LANGUAGE_ID,
                    'more_url' => ['test_news_list.php']
                ],
                [
                    'text' => Loc::getMessage('NEWS_MENU_STATUS'),
                    'title' => Loc::getMessage('NEWS_MENU_STATUS'),
                    'url' => 'test_status_list.php?lang='.LANGUAGE_ID,
                    'more_url' => ['test_news_poll_edit.php']
                ],
            ]
        ];
    }
}
?>