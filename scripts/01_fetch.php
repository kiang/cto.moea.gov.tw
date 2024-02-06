<?php
$basePath = dirname(__DIR__);
$page = file_get_contents('https://www.cto.moea.gov.tw/FactoryMCLA/web/information/list.php?cid=1');
$pos = strpos($page, '<table id="dtList"');
if (false !== $pos) {
    $posEnd = strpos($page, '</tbody>', $pos);
    $lines = explode('</tr>', substr($page, $pos, $posEnd - $pos));
    foreach ($lines as $line) {
        $cols = explode('</td>', $line);
        if (!isset($cols[3])) {
            continue;
        }
        $cols[0] = trim(strip_tags($cols[0]));
        $rawPath = $basePath . '/raw/' . $cols[0];
        if (!file_exists($rawPath)) {
            mkdir($rawPath, 0777, true);
        }
        $fPos = strpos($cols[3], 'https://www.cto.moea.gov.tw');
        while (false !== $fPos) {
            $fPosEnd = strpos($cols[3], '\'', $fPos);
            $fUrl = substr($cols[3], $fPos, $fPosEnd - $fPos);
            $parts = explode('/', $fUrl);
            $rawFile = $rawPath . '/' . $parts[6];
            $parts[6] = urlencode($parts[6]);
            if (!file_exists($rawFile)) {
                file_put_contents($rawFile, file_get_contents(implode('/', $parts)));
            }
            $fPos = strpos($cols[3], 'https://www.cto.moea.gov.tw', $fPosEnd);
        }
    }
}
