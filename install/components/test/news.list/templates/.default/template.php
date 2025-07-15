<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $arResult['AREA_ID']);
?>
<div class="news-container" id="<?=$arResult['AREA_ID'];?>">
<?php foreach ($arResult['ITEMS'] as $index => $item):?>
    <div class="news-container__item news-item">
        <div class="news-item__title"><?=$item['NAME'];?></div>
        <div class="news-item__description"><?=$item['DESCRIPTION'];?></div>
        <div class="news-item__date"><?=FormatDate('d F Y  H:i:s', $item['CREATED_AT']);?></div>
    </div>
<?php endforeach;?>
<script>
    var <?=$obName?> = new JSAjaxComponent(<?=CUtil::PhpToJSObject($jsParams, false, true)?>)
</script>
</div>