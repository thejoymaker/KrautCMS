<?php
// Kraut/Markdown/KrautParsedown.php

declare(strict_types=1);

namespace Kraut\Markdown;

use Parsedown;

class KrautParsedown extends Parsedown
{

    public function __construct()
    {
        // Register the 'Gallery' block type for lines starting with '['
        $this->BlockTypes['{'][] = 'Gallery';
    }
        

    protected function blockGallery($Line)
    {
        if (preg_match('/^\{gallery\}/', trim($Line['text']))) {
            $Block = [
                'element' => [
                    'handler' => 'elements',
                    'name' => 'div',
                    'attributes' => [
                        'class' => 'gallery',
                    ],
                    'text' => [],
                ],
            ];

            return $Block;
        }

        // return false;
    }
    
    protected function blockGalleryContinue($Line, array &$Block)
    {
        // Trim whitespace from the line text
        $text = trim($Line['text']);

        // Check for the closing tag
        if (preg_match('/^\{\/gallery\}$/', $text)) {
            // Return null to signal the end of the block
            return null;
        }

        // Process image URL lines
        if ($text !== '') {
            $safeUrl = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            $Block['element']['text'][] = [
                'name' => 'a',
                'attributes' => [
                    'href' => $safeUrl,
                    'data-lightbox' => 'gallery',
                ],
                'handler' => 'element',
                'text' => [
                    'name' => 'img',
                    'attributes' => [
                        'src' => $safeUrl,
                        'alt' => '',
                    ],
                ],
            ];
        }
        // Return the block to continue processing
        return $Block;
    }

    protected function blockGalleryComplete($Block)
    {
        return $Block;
    }
}