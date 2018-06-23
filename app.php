<?php

declare(strict_types=1);

require 'vendor/autoload.php';


use Amp\Artax\DefaultClient;
use function DvachPicScraper\Helpers\download;
use function DvachPicScraper\Helpers\takeFileInfoList;


if (!isset($argv[1])) {
    echo "Укажите ссылку на трэд!\n";
    exit(1);
}

define('REQUEST_URL', \str_replace('html', 'json', $argv[1]));

\Amp\Loop::run(function () {

    $client = new DefaultClient();
    /** @var \Amp\Artax\Response $response */
    $response = yield $client->request(REQUEST_URL); //https://2ch.hk/b/res/178168599.json
    if (200 !== $response->getStatus()) {
        echo "Кажется такого треда нет...\n";
        \Amp\Loop::stop();
        return;
    }


    $posts = \DvachPicScraper\Helpers\extractPosts(yield $response->getBody());
    $fileInfoList = \array_filter(takeFileInfoList($posts), function (\DvachPicScraper\FileInfo $fileInfo) {
        return $fileInfo->mimeType === 'jpg';
    });
    $fileInfoList = \array_chunk($fileInfoList, 5);

    foreach ($fileInfoList as $fI) {
        [$_, $data] = download($client, $fI);
        /** @var array $data */
        foreach ($data as $fileName => $file) {
            \Amp\asyncCall(function () use ($fileName, $file) {
                $fileName = 'downloads/' . $fileName;
                if (false === file_put_contents($fileName, $file)) {
                    echo "Ne zagrusilsa {$fileName}\n";
                }
            });
        }
    }

});

