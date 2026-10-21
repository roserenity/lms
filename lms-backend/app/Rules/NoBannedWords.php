<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BannedWord;

class NoBannedWords implements Rule
{
    protected $bannedWords;
    protected $offendingWord;

    public function __construct()
    {
        $this->bannedWords = BannedWord::pluck('word')->toArray();
    }

    public function passes($attribute, $value)
    {
        foreach ($this->bannedWords as $bannedWord) {
            // Escape any regex characters in the banned word
            $escapedWord = preg_quote($bannedWord, '/');

            // Match whole words using word boundaries \b
            if (preg_match("/\b{$escapedWord}\b/i", $value)) {
                $this->offendingWord = $bannedWord;
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The message contains inappropriate or banned words.';
    }
}
