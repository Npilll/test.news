<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\AdminPageNavigation;
use \Test\News\StatusTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('test.news');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

// Проверка прав
if ($APPLICATION->GetGroupRight('test.news') < 'R') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$APPLICATION->SetTitle(Loc::getMessage('STATUS_LIST_TITLE'));

$entity = StatusTable::getEntity();
$fields = $entity->getFields();

// Инициализация сортировки
$sTableID = 'test_status_list';
$oSort = new CAdminSorting($sTableID, "ID", "DESC");

$adminList = new CAdminList($sTableID, $oSort);

$arHeaders = [];
foreach ($fields as $field) {
    $arHeaders[] = [
        'id' => $field->getName(),
        'content' => $field->getTitle(),
        'sort' => mb_strtolower($field->getName()),
        'default' => true,
    ];
}

$adminList->AddHeaders($arHeaders);

$connection = \Bitrix\Main\Application::getConnection();

// сохранение отредактированных элементов
if ($adminList->EditAction() && $APPLICATION->GetGroupRight('test.news') >= 'W') {

    try {
        $connection->startTransaction();
    // пройдем по списку переданных элементов
        foreach($adminList->GetEditFields() as $ID => $arFields)
        {
            $ID = IntVal($ID);
            $result = StatusTable::update($ID, $arFields);
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
        $rsData = StatusTable::getList([
            'select' => ['ID'],
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
                    $result = StatusTable::delete($ID);
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

// Навигация
$nav = new AdminPageNavigation('nav');

// Получение данных
$params = [
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
    'order' => [$oSort->getField() => $oSort->getOrder()],
];

// Получение данных
$result = StatusTable::getList($params);

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
    $arActions = [];
    $arActions[] = [
        "ICON" => "edit",
        "TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
        "DEFAULT" => true,
        "ACTION" => $adminList->ActionRedirect(
            "test_status_edit.php?ID=" . $values['ID']
        ),
    ];
    $arActions[] = [
        "ICON" => "delete",
        "TEXT" => Loc::getMessage("MAIN_ADMIN_MENU_DELETE"),
        "ACTION" =>
            "if (confirm('" . GetMessageJS("ANKETA_DELETE_CONFIRM") . "')) "
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
        'LINK' => 'test_status_edit.php?lang='.LANGUAGE_ID,
        'ICON' => 'btn_new'
    ]
]);

// Групповые действия
$adminList->AddGroupActionTable([
    "edit" => Loc::getMessage("MAIN_ADMIN_MENU_EDIT"),
    'delete' => Loc::getMessage('MAIN_ADMIN_MENU_DELETE')
]);

$adminList->CheckListMode();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$adminList->DisplayList([
    'NAV_OBJECT' => $nav,
    'NAV_PARAM' => 'nav',
]);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');