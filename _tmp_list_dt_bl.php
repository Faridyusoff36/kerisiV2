<?php
$f = 'C:/Users/farid/OneDrive/Desktop/KerisiAI/Jason Level 4/CREDIT CONTROL_LEVEL 4.json';
$rows = json_decode(file_get_contents($f), true);
$menuOk = array_flip([2289, 2290, 2604, 2669, 3370, 3371, 3375, 3380, 3381, 3409, 3443, 3445, 3446, 3447, 3448]);
foreach ($rows as $r) {
    $mid = (int) ($r['MENUID'] ?? 0);
    if (!isset($menuOk[$mid])) {
        continue;
    }
    if (($r['COMPONENTTYPE'] ?? '') !== 'datatable') {
        continue;
    }
    $pid = $r['PAGEID'];
    $bl = $r['API_BL_NAME'] ?? '';
    echo $pid . "\t" . $mid . "\t" . $bl . "\n";
}
