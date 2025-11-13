<?php

namespace Core\Templates;

use Entities\View;
use Entities\TelegraphText;

class Swig extends View
{
    public function render(TelegraphText $telegraphText): string
    {
        $filename = sprintf('templates/%s.swig', $this->templateName);
        $template = file_get_contents($filename);
        
        foreach ($this->variables as $variable) {
            $template = str_replace('{{ ' . $variable . ' }}', $telegraphText->$variable, $template);
        }
        
        return $template;
    }
}
