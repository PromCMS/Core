<?php
namespace PromCMS\Core\Internal\Http\Middleware;

enum EntityMiddlewareMode: string
{
  case MODEL = "model";
  case SINGLETON = "singleton";
}
