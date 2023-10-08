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
          HttpContentType::JSON => 'json',   
          HttpContentType::HTML => 'html',   
        };
    }

    public function asHeaderValue(): string
    {
        return match($this) 
        {
          HttpContentType::JSON => 'application/json',   
          HttpContentType::HTML => 'text/html',   
        };
    }
}