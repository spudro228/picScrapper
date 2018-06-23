<?php

declare(strict_types=1);
require 'vendor/autoload.php';

use function DvachPicScraper\Helpers\takeMimeType;

assert('jpg' === takeMimeType('/di/thumb/351632/15290485595960s.jpg'));
assert('mp3' === takeMimeType('15290485595960s.mp3'));
assert('webm' === takeMimeType('15290485595960.webm'));
assert('' === takeMimeType('15290485595960'));
