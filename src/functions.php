<?php

declare(strict_types=1);

namespace DvachPicScraper\Helpers {


    use function Amp\call;
    use Amp\MultiReasonException;
    use function Amp\Promise\any;
    use function Amp\Promise\wait;
    use DvachPicScraper\FileInfo;
    use RecursiveArrayIterator;
    use RecursiveIteratorIterator;

    function flat(array $arr): array
    {
        return iterator_to_array(new RecursiveIteratorIterator (new RecursiveArrayIterator($arr)), false);
    }

    function extractPaths(array $files): array
    {
        return \array_map(function ($file) {
            return \array_map(function ($item) {
                return $item->path;
            }, $file);
        }, $files);
    }

    function extractFiles(array $posts): array
    {
        return
            \array_filter(
                \array_map(function ($post) {
                    return $post->files;
                }, $posts), function ($item) {
                return !empty($item);
            });
    }

    function extractFileInfo(array $files)
    {
        return \array_map(function ($file) {
            return \array_map(function ($item) {
                return new FileInfo($item);
            }, $file);
        }, $files);
    }

    /**
     * @todo сделать только чтобы изображения без вебм
     * @param string $body
     * @return array
     */
    function takeImagePaths(string $body): array
    {


        return flat(extractPaths(extractFiles($posts)));
    }

    function extractPosts(string $body)
    {
        $tread = \json_decode($body);
        return ((array)$tread->threads[0])['posts'];
    }

    /**
     * From '/di/thumb/351632/15290485595960s.jpg' take 'jpg'
     * @param string $fileName
     * @return string
     */
    function takeMimeType(string $fileName): string
    {
        $re = '/(\.)(.*$)/m';
        preg_match($re, $fileName, $match);
        return (string)@$match[2];
    }

    function download(\Amp\Artax\Client $client, array $fileInfoList)
    {
        $promises = [];
        $responses = [];

        /** @var FileInfo $fileInfo */
        foreach ($fileInfoList as $fileInfo) {
            $promises[$fileInfo->name] = call(function () use ($client, $fileInfo) {
                try {
                    /** @var \Amp\Artax\Response $response */
                    $response = yield $client->request('https://2ch.hk' . $fileInfo->path);
                    echo $response->getStatus() . ' - ' . $fileInfo->fullName . PHP_EOL;
                    $body = yield $response->getBody();
                } catch (MultiReasonException $e) {
                    echo $e->getMessage(), "\n";
                }
                return $body;
            });
        }
        try {
            $responses = wait(any($promises));
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        return $responses;
    }

    function takeFileInfoList(array $posts): array
    {
        return \array_merge(...\DvachPicScraper\Helpers\extractFileInfo(extractFiles($posts)));
    }
}