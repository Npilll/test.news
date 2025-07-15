<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<div style="padding: 20px;">
    <h2><?=Loc::getMessage('NEWS_UNINSTALL_TITLE')?></h2>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
        <input type="hidden" name="id" value="test.news">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">

        <p>
            <label>
                <input type="checkbox" name="savedata" value="Y" checked>
                <?=Loc::getMessage('NEWS_UNINSTALL_SAVE_DATA')?>
            </label>
        </p>

        <input type="submit" name="inst" value="<?=Loc::getMessage('NEWS_UNINSTALL_CONTINUE')?>">
    </form>
</div>