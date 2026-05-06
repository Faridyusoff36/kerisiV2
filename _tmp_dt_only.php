<?php
$f = 'C:/Users/farid/OneDrive/Desktop/KerisiAI/Jason Level 4/CREDIT CONTROL_LEVEL 4.json';
$pid = $argv[1] ?? '';
$rows = json_decode(file_get_contents($f), true);
foreach ($rows as $r) {
    if ((string) ($r['PAGEID'] ?? '') !== $pid) {
        continue;
    }
    if (($r['COMPONENTTYPE'] ?? '') !== 'datatable') {
        continue;
    }
    echo $r['COMPONENTTITLE'] . "\n";
    echo $r['API_BL_NAME'] . "\n";
    echo ($r['Datatable column details'] ?? '') . "\n---\n";
}
