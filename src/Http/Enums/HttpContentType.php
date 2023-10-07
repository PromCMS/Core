<?php

namespace PromCMS\Core\Http\Enums;

enum HttpContentType
{
    case JSON;
    case HTML;

    public function asString(): string
    {
        return match($this) 
        {
          ResponseType::JSON => 'json',   
          ResponseType::HTML => 'html',   
        };
    }

    public function asHeaderValue(): string
    {
        return match($this) 
        {
          ResponseType::JSON => 'application/json',   
          ResponseType::HTML => 'text/html',   
        };
    }
}