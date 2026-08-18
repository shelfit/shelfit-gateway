<?php

namespace App\Entity;

class FeedPostType
{
    public const TYPE_REVIEW = 'review';
    public const TYPE_FINISHED = 'finished';
    public const TYPE_TEXT = 'text';

    public const ALL_TYPES = [
        self::TYPE_REVIEW,
        self::TYPE_FINISHED,
        self::TYPE_TEXT,
    ];
}