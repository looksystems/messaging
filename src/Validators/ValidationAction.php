<?php

namespace Look\Messaging\Validators;

enum ValidationAction
{
    case Allow;
    case Drop;
    case Exception;
}
