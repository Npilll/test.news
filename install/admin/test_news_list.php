<?php
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/test.news/admin/news_list.php")){
    require($_SERVER["DOCUMENT_ROOT"]."/local/modules/test.news/admin/news_list.php");
}
else{
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/test.news/admin/news_list.php");
}
?>