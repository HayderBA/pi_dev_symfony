<?php

namespace App\Service;

class BadWordFilter
{
    /**
     * @var string[]
     */
    private array $badWords = [];

    public function __construct(string $badWordsFile)
    {
        if (is_file($badWordsFile)) {
            $words = json_decode((string) file_get_contents($badWordsFile), true);
            $this->badWords = is_array($words) ? array_values(array_filter($words, 'is_string')) : [];
        }
    }

    public function filter(string $text): string
    {
        $filtered = $text;

        foreach ($this->badWords as $word) {
            $replacement = str_repeat('*', mb_strlen($word));
            $filtered = (string) preg_replace('/\b' . preg_quote($word, '/') . '\b/ui', $replacement, $filtered);
        }

        return $filtered;
    }

    public function containsBadWord(string $text): bool
    {
        foreach ($this->badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/ui', $text) === 1) {
                return true;
            }
        }

        return false;
    }
}
