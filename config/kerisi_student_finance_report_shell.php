<?php

declare(strict_types=1);

/**
 * Kerisi Student Finance > Report shell menus (registry `menuPath` like
 * `Student Finance>Report>…`). {@see KerisiSfStudentFinanceReportShellService}
 * wires list payloads for these menu IDs so `/api/student-finance/kerisi-level3/{id}`
 * does not return an empty grid when secondary DB has data.
 *
 * @see client/src/config/kerisi-sf-level3-registry.generated.ts (menuPath filter)
 */
return [
    'menu_ids' => [
        1262, 1284, 1285, 1286, 1538, 1539, 1550,
        1790, 1794, 1795, 1797, 1798, 1799, 1800, 1801, 1802, 1804, 1806, 1807,
        1810, 1811, 1812, 1814, 1816,
        1916, 1918, 1923, 1924,
        1949, 1954, 1956, 1965, 1984, 1988, 2008, 2010, 2026, 2029, 2064,
        2437, 2622,
    ],
];
