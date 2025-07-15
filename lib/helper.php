<?php
namespace Test\News;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class Helper
{
    public static function generateRandomString(int $length = 10): string
    {
        $russianLetters = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
        $lettersLength = mb_strlen($russianLetters, 'UTF-8');
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $lettersLength - 1);
            $result .= mb_substr($russianLetters, $randomIndex, 1, 'UTF-8');
        }
        return $result;
    }

    public static function generateRandomText(int $minWords = 5, int $maxWords = 20, int $wordsPerLine = 5): string {
        $wordCount = random_int($minWords, $maxWords);
        $words = [];

        for ($i = 0; $i < $wordCount; $i++) {
            $words[] = self::generateRandomString(random_int(2, 10));
        }

        $lines = array_chunk($words, $wordsPerLine);
        $text = '';
        foreach ($lines as $line) {
            $text .= implode(' ', $line) . "\n";
        }

        return $text;
    }
}
?>