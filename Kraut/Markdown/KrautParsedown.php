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

        return false;
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

// declare(strict_types=1);

// namespace Kraut\Markdown;

// use Parsedown;

// class KrautParsedown extends Parsedown
// {
//     public function __construct()
//     {
//         // Register the 'Gallery' block type for lines starting with '['
//         $this->BlockTypes['['][] = 'Gallery';
//     }

//     /**
//      * Handle the opening [gallery] tag.
//      *
//      * @param array $Line Parsedown Line Array
//      * @return array|bool Parsedown Block Array or FALSE
//      */
//     protected function blockGallery($Line)
//     {
//         if (preg_match('/^\[gallery\]$/', trim($Line['text']))) {
//             $Block = [
//                 'type' => 'Gallery',
//                 'element' => [
//                     'name' => 'div',
//                     'attributes' => [
//                         'class' => 'gallery',
//                     ],
//                     'handler' => 'elements',
//                     'text' => [],
//                 ],
//             ];

//             return $Block;
//         }

//         return false;
//     }

//     /**
//      * Continue parsing lines within the [gallery] block.
//      *
//      * @param array $Line Parsedown Line Array
//      * @param array $Block Parsedown Block Array (passed by reference)
//      * @return array|bool Modified Block Array or FALSE
//      */
//     protected function blockGalleryContinue($Line, array &$Block)
//     {
//         $text = trim($Line['text']);

//         // Check for the closing [/gallery] tag
//         if (preg_match('/^\[\/gallery\]$/', $text)) {
//             // Return null to signal the end of the block
//             return null;
//         }

//         // Process image URL lines
//         if ($text !== '') {
//             // Optionally, validate the URL format here
//             if (!filter_var($text, FILTER_VALIDATE_URL) && !preg_match('/^\/[a-zA-Z0-9\/\.\-_]+$/', $text)) {
//                 // Invalid URL format; skip or handle as needed
//                 return $Block;
//             }

//             $safeUrl = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

//             $Block['element']['text'][] = [
//                 'name' => 'a',
//                 'attributes' => [
//                     'href' => $safeUrl,
//                     'data-lightbox' => 'gallery',
//                 ],
//                 'handler' => 'element',
//                 'text' => [
//                     'name' => 'img',
//                     'attributes' => [
//                         'src' => $safeUrl,
//                         'alt' => '',
//                     ],
//                 ],
//             ];
//         }

//         // Continue processing the block
//         return $Block;
//     }

//     /**
//      * Finalize the [gallery] block.
//      *
//      * @param array $Block Parsedown Block Array
//      * @return array Parsedown Block Array
//      */
//     protected function blockGalleryComplete(array $Block)
//     {
//         return $Block;
//     }
// }
// ?>