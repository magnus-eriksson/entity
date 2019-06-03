<?php namespace Maer\Entity\Traits;

use InvalidArgumentException;

trait TextTrait
{
    /**
     * Get an excerpt of a text string
     *
     * @param  string  $propertyName
     * @param  integer $maxLength
     * @param  string  $suffix
     *
     * @return string
     */
    public function excerpt(string $propertyName, int $maxLength = 300, string $suffix = '...') : string
    {
        $text = $this->{$propertyName};

        if (!is_null($text) && !is_string($text)) {
            throw new InvalidArgumentException(
                "You can only create excerpts from strings"
            );
        }

        if (is_null($text) || strlen($text) <= $maxLength) {
            return $text;
        }

        // Check if the body has a user defined "<!--more-->"-tag
        $more = stripos($text, '<!--more-->');

        if ($more !== false) {
            // We found a tag, use that position for the excertip
            // instead of the default
            return strip_tags(substr($text, 0, $more));
        }

        // Remove any HTML so we get pure text
        $text = strip_tags($text);

        $text      = substr($text, 0, $maxLength - strlen($suffix));
        $lastSpace = strrpos($text, ' ');
        $text      = substr($text, 0, $lastSpace);

        return $text . $suffix;
    }
}
