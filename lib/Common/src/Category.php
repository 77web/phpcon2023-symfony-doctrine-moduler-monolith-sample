<?php

declare(strict_types=1);

namespace MyApp\Common;

enum Category: string
{
    case PHP = 'php';
    case COMPOSER = 'composer';
    case BEGINNER = 'beginner';
    case SYMFONY = 'symfony';
    case LARAVEL = 'laravel';
}