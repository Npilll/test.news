<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\AdminPageNavigation;
use \Test\News\NewsTable;
use \Test\News\StatusTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('test.news');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

// Проверка прав
if ($APPLICATION->GetGroupRight('test.news') < 'R') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}
$arFilter = [];

$APPLICATION->SetTitle(Loc::getMessage('NEWS_LIST_TITLE'));

$entity = NewsTable::getEntity();
$fields = $entity->getFields();

$statuses = StatusTable::getList()->fetchAll();
$statuses = array_column($statuses, 'TITLE', 'ID');

// Инициализация сортировки
$sTableID = 'test_news_list';
$oSort = new CAdminUiSorting($sTableID, "ID", "DESC");

$adminList = new CAdminUiList($sTableID, $oSort);

$arHeaders = [];
foreach ($fields as $field) {
    if($field instanceof Bitrix\Main\ORM\Fields\Relations\Reference){
        continue;
    }

    $arHeaders[] = [
        'id' => $field->getName(),
        'content' => $field->getName()=='STATUS_ID' ? Loc::getMessage('NEWS_LIST_STATUS') : $field->getTitle(),
        'sort' => mb_strtolower($field->getName()),
        'default' => ($field->getName() == 'DESCRIPTION'
            || $field->getName() == 'CREATED_AT') ? false : true,
    ];

    if($field->getName()=='STATUS_ID'){
        $filterFields[] = array(
            "id" => $field->getName(),
            "name" => $field->getName()=='STATUS_ID' ? Loc::getMessage('NEWS_LIST_STATUS') : $field->getTitle(),
            "filterable" => "",
            "type" => "list",
            "items" => $statuses,
            'default' => true,
        );
    }
    elseif($field instanceof Bitrix\Main\ORM\Fields\DatetimeField){

        $filterFields[] = array(
            "id" => $field->getName(),
            "name" => $field->getTitle(),
            "filterable" => "",
            "type" => "date",
            'default' => true,
        );
    }
    else{
        $filterFields[] = array(
            "id" => $field->getName(),
            "name" => $field->getTitle(),
            "filterable" => "?",
            'default' => $field->getName() == 'DESCRIPTION' ? false : true,
        );
    }
}

$adminList->AddHeaders($arHeaders);
$visibleFields = $adminList->GetVisibleHeaderColumns();

$connection = \Bitrix\Main\Application::getConnection();

// сохранение отредактированных элементов
if ($adminList->EditAction() && $APPLICATION->GetGroupRight('test.news') >= 'W') {

    try {
        $connection->startTransaction();
    // пройдем по списку переданных элементов
        foreach($adminList->GetEditFields() as $ID => $arFields)
        {
            $ID = IntVal($ID);
            $result = NewsTable::update($ID, $arFields);
            if(!$result->isSuccess())
            {
                $errors = $result->getErrorMessages();
                throw new \Exception(implode(PHP_EOL,$errors));
            }
        }
        $connection->commitTransaction();
    } catch (\Exception $e) {
        $connection->rollbackTransaction();
        $adminList->AddGroupError($e->getMessage(), $ID);
    }
}

// обработка одиночных и групповых действий
if(($arID = $adminList->GroupAction()) && $APPLICATION->GetGroupRight('test.news') >= 'W')
{
    // если выбрано "Для всех элементов"
    if ($adminList->IsGroupActionToAll()){
        $rsData = NewsTable::getList([
            'select' => ['ID'],
            'filter' => $arFilter,
        ]);
        while($arRes = $rsData->fetch())
            $arID[] = $arRes['ID'];
    }

    $action = $adminList->GetAction();

    try {
        $connection->startTransaction();

        foreach ($arID as $ID) {
            switch ($action) {
                // удаление
                case "delete":
                    $result = NewsTable::delete($ID);
                    break;
            }
            if(!$result->isSuccess())
            {
                $errors = $result->getErrorMessages();
                throw new \Exception(implode(PHP_EOL,$errors));
            }
        }
        $connection->commitTransaction();
    }
    catch (\Exception $e){
        $connection->rollbackTransaction();
        $adminList->AddGroupError($e->getMessage(), $ID);
    }
}


// Фильтр
$adminList->AddFilter($filterFields, $arFilter);

// Навигация
$nav = new AdminPageNavigation('nav');
$nav->allowAllRecords(true)
    ->setPageSize($adminList->GetNavSize())
    ->initFromUri();

// Получение данных
$params = [
    'select' => $visibleFields,
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
    'order' => [$oSort->getField() => $oSort->getOrder()],
    'filter' => $arFilter,
];

// Получение данных
$result = \Test\News\NewsTable::getList($params);

//устанавливаем навигацию
$nav->setRecordCount($result->getCount());
$adminList->setNavigation($nav, 'test');

// Отображение данных
while ($row = $result->fetchObject()) {
    $values =$row->collectValues();

    foreach ($values as $valueKey => &$rowItem) {
        if($rowItem instanceof Bitrix\Main\Type\DateTime){
            $rowItem = $rowItem->toString();
        }

    }
    $aRow =&$adminList->AddRow(
        $values['ID'],
        $values);

    $aRow->AddInputField("NAME", array("size"=>20));
    $aRow->AddInputField("SORT", array("size"=>20));
    if($statuses[$values['STATUS_ID']]){
        $aRow->AddSelectField("STATUS_ID", $statuses);
    }
    else{
        $aRow->AddViewField("STATUS_ID", 'Статус не установлен');
    }

    $arActions = [];
    $arActions[] = [
        "ICON" => "edit",
        "TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
        "DEFAULT" => true,
        "ACTION" => $adminList->ActionRedirect(
            "test_news_edit.php?ID=" . $values['ID']
        ),
    ];
    $arActions[] = [
        "ICON" => "delete",
        "TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_DELETE"),
        "ACTION" =>
            "if (confirm('" . GetMessageJS("NEWS_DELETE_CONFIRM") . "')) "
            . $adminList->ActionDoGroup(
                $row['ID'],
                "delete",
            )
        ,
    ];

    if (!empty($arActions))
    {
        $aRow->AddActions($arActions);
    }
}

// Контекстное меню
$adminList->AddAdminContextMenu([
    [
        'TEXT' => Loc::getMessage('MAIN_ADMIN_MENU_ADD'),
        'LINK' => 'test_news_edit.php?lang='.LANGUAGE_ID,
        'ICON' => 'btn_new'
    ]
]);

// Групповые действия
$adminList->AddGroupActionTable([
    'edit' => Loc::getMessage('MAIN_ADMIN_MENU_EDIT'),
    'delete' => Loc::getMessage('MAIN_ADMIN_MENU_DELETE'),
    'for_all' => Loc::getMessage('NEWS_FOR_ALL'),
]);

$adminList->CheckListMode();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
$adminList->DisplayFilter($filterFields);
$adminList->DisplayList([
    'NAV_OBJECT' => $nav,
    'NAV_PARAM' => 'nav',
]);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');