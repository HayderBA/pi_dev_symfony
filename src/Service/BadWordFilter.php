<?php

namespace App\Service;

class BadWordFilter
{
    private array $badWords = [];

    public function __construct(string $badWordsFile)
    {
        if (file_exists($badWordsFile)) {
            $content = file_get_contents($badWordsFile);
            $words = json_decode($content, true);
            $this->badWords = is_array($words) ? $words : [];
        }
    }

    public function filter(string $text): string
    {
        $filtered = $text;
        foreach ($this->badWords as $word) {
            $replacement = str_repeat('*', mb_strlen($word));
            $filtered = preg_replace('/\b' . preg_quote($word, '/') . '\b/ui', $replacement, $filtered);
        }
        return $filtered;
    }

    public function containsBadWord(string $text): bool
    {
        foreach ($this->badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/ui', $text)) {
                return true;
            }
        }
        return false;
    }
}