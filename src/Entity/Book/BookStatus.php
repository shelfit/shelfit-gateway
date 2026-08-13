<?php

namespace App\Entity\Book;

class BookStatus
{
    public const STATUS_READING = 'reading';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_TBR = 'tbr';
    public const STATUS_DNF = 'dnf';

    public const ALL_STATUSES = [
        self::STATUS_READING,
        self::STATUS_FINISHED,
        self::STATUS_TBR,
        self::STATUS_DNF,
    ];
}