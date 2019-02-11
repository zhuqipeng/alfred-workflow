<?php

require 'simplehtmldom.php';

$version = $argv[1];
$dirName = $argv[2];

$urlList = [
    '5.7' => 'https://learnku.com/docs/laravel/5.7',
    '5.6' => 'https://learnku.com/docs/laravel/5.6',
    '5.5' => 'https://learnku.com/docs/laravel/5.5',
    '5.4' => 'https://learnku.com/docs/laravel/5.4',
    '5.3' => 'https://learnku.com/docs/laravel/5.3',
    '5.2' => 'https://learnku.com/docs/laravel/5.2',
    '5.1' => 'https://learnku.com/docs/laravel/5.1',
];

$url = $urlList[$version];

$ret = file_get_contents($url);

$html = new SimpleHtmlDom();
$html->load($ret);

$chapters = $html->find('ol[class=sorted_table]', 0)->find('li[data-filetype=chapter]');

$data = [];

foreach ($chapters as $chapter) {
    $chapterName = trim(substr($chapter->innertext, 60, 20));

    $files = $chapter->find('ol>li[class=item]');

    foreach ($files as $file) {
        $a = $file->find('a', 0);

        // 用来过滤翻译进度的提示
        $span = $a->find('span', 0);
        $span ? $span->innertext = '' : '';

        $id = $file->getAttribute('data-itemid');
        $fileName = trim($a->plaintext);
        $link = $a->href;
        $data[] = [
            'arg' => $link,
            'title' => sprintf('%s->%s', $chapterName, $fileName),
            'subtitle' => $link,
            'quicklookurl' => $link,
        ];
    }
}

if ($dirName) {
    usort($data, function ($a, $b) use ($dirName) {
        $ai = stripos($a['title'], $dirName);
        $bi = stripos($b['title'], $dirName);

        return ($ai > $bi or $ai !== false) ? -1 : 1;
    });
}

echo json_encode(['items' => $data]);
