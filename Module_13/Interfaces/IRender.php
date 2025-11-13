<?php

namespace Interfaces;

use Entities\TelegraphText;

interface IRender
{
    public function render(TelegraphText $telegraphText): string;
}
