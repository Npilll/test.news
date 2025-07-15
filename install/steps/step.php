<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<div style="padding: 20px;">
    <h2><?=Loc::getMessage('NEWS_INSTALL_TITLE')?></h2>
    <p><?=Loc::getMessage('NEWS_INSTALL_MESSAGE')?></p>
    <form action="<?=$APPLICATION->GetCurPage()?>">
        <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
        <input type="submit" value="<?=Loc::getMessage('NEWS_INSTALL_FINISH')?>">
    </form>
</div>