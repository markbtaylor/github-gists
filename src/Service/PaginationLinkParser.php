<?php

namespace App\Service;

class PaginationLinkParser
{
    /**
     * Parse one or more pagination links, separated by commas to an array.
     *
     * @param string $linkHeader
     * @return array
     */
    public static function toArray(string $linkHeader): array
    {
        $values = [];
        foreach (explode(',', $linkHeader) as $link) {
            $values = array_merge($values, self::extractLinkData($link));
        }

        return $values;
    }

    /**
     * Parse a string in a format of <https://api.acme.com/endpoint?page=1>; rel="next"
     *
     * @param string $link
     * @return array
     */
    public static function extractLinkData(string $link): array
    {
        preg_match('/<(.*?(?:(?:\?|\&)page=(\d+).*)?)>.*rel="(.*)"/', $link, $matches, PREG_UNMATCHED_AS_NULL);

        return [
            $matches[3] => [
                'link' => $matches[1],
                'page' => $matches[2],
            ],
        ];
    }
}