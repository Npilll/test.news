<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use \Test\News\NewsTable;
use \Test\News\StatusTable;

class TestNewsListBlock extends CBitrixComponent
{
    /**
     * Bitrix vars
     *
     * @var array $this- >$arParams
     * @var array $this- >$arResult
     * @var array $componentPath =$this->getPath()
     * @var array $componentName =$this->getName()
     * @var array $componentTemplate =$this->getTemplate()
     * @var CBitrixComponent $this
     */

    private $uniqueId;
    private $defaultPublicStatusId = 3;


    private function initModule(): void
    {
        global $USER, $APPLICATION;

        $this->arResult = $this->arParams;
        $this->uniqueId = md5($this->randString());
        $this->uniqueId = $this->GetEditAreaId($this->uniqueId);
        $this->arResult['AREA_ID'] = $this->uniqueId;

        $this->arResult['ITEMS'] = $this->getNewsData();
    }


    public function executeComponent(): void
    {
        try {
            if(!Loader::includeModule('test.news')){
                throw new \Exception(Loc::getMessage('TEST_NEWS_MODULE_ERROR'));
            }
            $this->initModule();
            $this->includeComponentTemplate();
        }
        catch (\Exception $e){
            ShowError($e->getMessage());
        }
    }
    private function getNewsData(){
        $newsData = NewsTable::getList(
            [
                'count_total' => true,
                'order' => ['CREATED_AT' => 'DESC', 'SORT' => 'DESC'],
                'filter' => [
                    'STATUS_ID' => $this->defaultPublicStatusId,
                ],
            ]
        )->fetchAll();
        return $newsData;
    }
}