<?php
$basePath = dirname(__DIR__);

require_once __DIR__ . '/vendor/autoload.php';

$titleMap = [
    'No' => '編號',
    '工廠名稱' => '工廠名稱或違章建築',
    '工廠名稱或違章建築
(請寫名稱或貼照片)' => '工廠名稱或違章建築',
    '市縣政府查處情形' => '縣市政府查處情形',
    '縣市政府執行情形' => '縣市政府查處情形',
    '地址(去識別化)' => '地址',
    '是否裝設AMI監控' => '裝設AMI監控情形',
    '市縣' => '縣市',
    '縣市政府會勘情形' => '縣市政府查處情形',
];
$skel = [
    '公告日期' => '',
    '公告名稱' => '',
    '編號' => '',
    '縣市' => '',
    '工廠名稱或違章建築' => '',
    '地址' => '',
    '地號' => '',
    '使用分區' => '',
    '使用地' => '',
    '縣市政府查處情形' => '',
    '裝設AMI監控情形' => '',
    '違規建物樣態' => '',
];
$dataPath = $basePath . '/data/city';
if (!file_exists($dataPath)) {
    mkdir($dataPath, 0777, true);
}
$oFh = [];
foreach (glob($basePath . '/raw/*/*.xlsx') as $xlsxFile) {
    $p = pathinfo($xlsxFile);
    $parts = explode('/', $p['dirname']);
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxFile);
    $worksheets = $spreadsheet->getAllSheets();
    foreach ($worksheets as $worksheet) {
        $data = $worksheet->toArray();
        if (!isset($data[2])) {
            continue;
        }
        $meta = '';
        for ($i = 0; $i < 3; $i++) {
            $meta .= implode("\t", $data[$i]) . PHP_EOL;
        }
        if (false === strpos($meta, 'No') && false === strpos($meta, '編號')) {
            continue;
        }
        $header = false;
        $reportTitle = '';
        foreach ($data as $line) {
            if ($line[0] === 'No' || $line[0] === '編號') {
                $header = $line;
                foreach ($header as $k => $item) {
                    if (isset($titleMap[$item])) {
                        $header[$k] = $titleMap[$item];
                    }
                }
                continue;
            } elseif (false === $header) {
                $reportTitle = trim(implode('', $line));
                continue;
            }
            if (false !== $header) {
                $result = $skel;
                $result['公告日期'] = $parts[6];
                $result['公告名稱'] = $reportTitle;
                $row = array_combine($header, $line);
                foreach ($row as $k => $v) {
                    if (isset($result[$k])) {
                        $result[$k] = trim($v);
                    }
                }
                if (!empty($result['編號']) && !empty($result['縣市'])) {
                    if (!isset($oFh[$result['縣市']])) {
                        $oFh[$result['縣市']] = fopen($dataPath . '/' . $result['縣市'] . '.csv', 'w');
                        fputcsv($oFh[$result['縣市']], array_keys($result));
                    }
                    fputcsv($oFh[$result['縣市']], $result);
                }
            }
        }
    }
}
