<?php
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Test\News\NewsTable;
use Test\News\StatusTable;

Loc::loadMessages(__FILE__);

class test_news extends CModule
{
    public $MODULE_ID = 'test.news';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('NEWS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('NEWS_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('NEWS_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('NEWS_PARTNER_URI');
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!Loader::includeModule('main')) {
            $APPLICATION->ThrowException(Loc::getMessage('NEWS_MAIN_MODULE_REQUIRED'));
            return false;
        }

        if (PHP_VERSION_ID < 70400) {
            $APPLICATION->ThrowException(Loc::getMessage('NEWS_PHP_VERSION_ERROR'));
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallAgents();
        $this->InstallFiles();

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('NEWS_INSTALL_TITLE'),
            __DIR__ . '/steps/step.php'
        );

        return true;
    }

    public function InstallAgents(){
        CAgent::AddAgent(
            "\Test\News\AgentControl::AgentAddNew();",
            $this->MODULE_ID,
            "N",
            3600,
            "",                       // дата первой проверки - текущее
            "Y",                      // агент активен
            "",                       // дата первого запуска - текущее
            30);
    }

    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        $connection = \Bitrix\Main\Application::getConnection();

        if (!$connection->isTableExists(NewsTable::getTableName())) {
            NewsTable::getEntity()->createDbTable();
        }

        if (!$connection->isTableExists(StatusTable::getTableName())) {
            StatusTable::getEntity()->createDbTable();

            StatusTable::addMulti([
                ['TITLE' => Loc::getMessage('NEWS_STATUS_DEFAULT1')],
                ['TITLE' => Loc::getMessage('NEWS_STATUS_DEFAULT2')],
                ['TITLE' => Loc::getMessage('NEWS_STATUS_DEFAULT3')],
            ]);
        }

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'Test\News\AdminMenu',
            'onBuildGlobalMenu'
        );
        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . '/components',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/',
            true,
            true
        );

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        if ($request['step'] < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('NEWS_UNINSTALL_TITLE'),
                __DIR__ . '/steps/unstep1.php'
            );
        } elseif ($request['step'] == 2) {
            $this->UnInstallDB(['savedata' => $request['savedata']]);
            $this->UnInstallEvents();
            $this->UnInstallAgents();
            $this->UnInstallFiles();

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('NEWS_UNINSTALL_TITLE'),
                __DIR__ . '/steps/unstep2.php'
            );
        }

        return true;
    }

    public function UnInstallDB($arParams = [])
    {
        Loader::includeModule($this->MODULE_ID);

        if ($arParams['savedata'] != 'Y') {
            $connection = \Bitrix\Main\Application::getConnection();
            if ($connection->isTableExists(NewsTable::getTableName())) {
                $connection->dropTable(NewsTable::getTableName());
            }

            if ($connection->isTableExists(StatusTable::getTableName())) {
                $connection->dropTable(StatusTable::getTableName());
            }
        }

        return true;
    }

    public function UnInstallAgents(){
        CAgent::RemoveModuleAgents($this->MODULE_ID);
    }


    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            'Test\News\AdminMenu',
            'onBuildGlobalMenu'
        );
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );

        DeleteDirFiles(
            __DIR__ . "/install/components",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components",
        );

        return true;
    }
}
?>