<?php

namespace App\Entity\Book;

class BookVisibility
{
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    public const ALL_VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_PRIVATE
    ];
}
