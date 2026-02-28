<?php

namespace App\Enums;

enum ApiCode: string
{
    case VALIDATION_ERROR    = 'validation_error';
    case NOT_FOUND           = 'not_found';
    case UNAUTHENTICATED     = 'unauthenticated';
    case INVALID_CREDENTIALS = 'invalid_credentials';
    case IMAGE_NOT_FOUND     = 'image_not_found';
}
