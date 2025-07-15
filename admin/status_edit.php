<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Test\News\StatusTable;

/** @var $APPLICATION */
/** @var $save */
/** @var $apply */

Loc::loadMessages(__FILE__);

Loader::includeModule('test.news');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

$POST_RIGHT = $APPLICATION->GetGroupRight('test.news');
if ($POST_RIGHT < 'W') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$entity = StatusTable::getEntity();
$newsEntityFields = $entity->getFields();
$errorMessage = null;

// сформируем список закладок
$aTabs = array(
    array("DIV" => "edit1", "TAB" => Loc::getMessage("STATUS_INFO"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("STATUS_INFO")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = (int)$_REQUEST['ID'];
$isNew = ($ID == 0);

$APPLICATION->SetTitle($isNew ?
    Loc::getMessage('STATUS_ADD') :
    Loc::getMessage('STATUS_EDIT'));


if ($_SERVER['REQUEST_METHOD'] == 'POST'
    && ($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
    && $POST_RIGHT=="W"
    && check_bitrix_sessid()) {
    try {
        $fields = [
            'TITLE' => trim($_POST['TITLE']),
        ];

        if ($isNew) {
            $result = StatusTable::add($fields);
            $ID = $result->getId();
        } else {
            $result = StatusTable::update($ID, $fields);
        }

        if(!$result->isSuccess()){
            $errors = $result->getErrorMessages();
            throw new \Exception(implode(PHP_EOL,$errors));
        }
        else{
            if ($apply != "")
                LocalRedirect("test_status_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
            else
                LocalRedirect("test_status_list.php?lang=".LANG);
        }
    } catch (Exception $e) {
        $APPLICATION->ThrowException($e->getMessage());
    }

    if($e = $APPLICATION->GetException())
        $errorMessage = new CAdminMessage(Loc::GetMessage("STATUS_SAVE_ERROR"), $e);
}

if (!$isNew) {
    $newsData = StatusTable::getById($ID)->fetch();
    if (!$newsData) {
        $APPLICATION->ThrowException(Loc::getMessage('STATUS_NOT_FOUND'));
        LocalRedirect('test_status_list.php?lang='.LANGUAGE_ID);
    }
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

// eсли есть сообщения об успешном сохранении, выведем их
if ($_REQUEST["mess"] == "ok") {
    CAdminMessage::ShowMessage(array("MESSAGE" => "Сохранено успешно", "TYPE" => "OK"));
}
if($errorMessage)
    echo $errorMessage->Show();
elseif($result->LAST_ERROR!="")
    CAdminMessage::ShowMessage($result->LAST_ERROR);

?>
    <form method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>"
          ENCTYPE="multipart/form-data" name="post_form">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <?php if($ID>0):?>
            <input type="hidden" name="ID" value="<?=$ID?>">
        <?php endif;?>
        <?php
        // отобразим заголовки закладок
        $tabControl->Begin();
        ?>
        <?php
        $tabControl->BeginNextTab();
        ?>
        <?php foreach ($newsEntityFields as $field):?>
            <?php
                if($field->getName()=='ID'
                    || $field->getName()=='CREATED_AT'
                    || $field->getName()=='UPDATED_AT'
                    || $field->getName()=='STATUS_ID'
                )continue;
            ?>
            <tr>
                <td width="40%"><?=$field->getTitle()?>:</td>
                <td width="60%">
                    <?php switch ($field->getDataType()):
                        case 'text':?>
                            <?php
                            $fieldValue = $_POST[$field->getName()] ?: ($newsData[$field->getName()] ?: $field->getDefaultValue());
                            ?>
                            <textarea cols="60" rows="15" name="<?=$field->getName();?>"
                                      style="width:100%;"
                                      name="<?=$field->getName()?>"
                                      size="50" <?php $field->isRequired() ? 'required' : '';?>><?=htmlspecialcharsbx($fieldValue)?></textarea>
                            <?php break;?>
                        <?php case 'datetime':?>
                            <?php
                            $fieldValue = $_POST[$field->getName()] ?: ($newsData[$field->getName()] ?: $field->getDefaultValue());
                            ?>
                            <?=CAdminCalendar::CalendarDate($field->getName(), $fieldValue)?>
                            <?php break;?>
                        <?php default:?>
                            <?php
                            $fieldValue = $_POST[$field->getName()] ?: ($newsData[$field->getName()] ?: $field->getDefaultValue());
                            ?>
                            <input type="text" name="<?=$field->getName()?>" value="<?=htmlspecialcharsbx($fieldValue)?>" size="50" <?php $field->isRequired() ? 'required' : '';?>>
                            <?php break;?>
                    <?php endswitch;?>
                </td>
            </tr>
        <?php endforeach;?>
        <?php
        // завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT < "W"),
                "back_url"=>"test_status_list.php?lang=".LANG,
            )
        );
        ?>
        <?php
        // завершаем интерфейс закладки
        $tabControl->End();
        ?>
    </form>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');