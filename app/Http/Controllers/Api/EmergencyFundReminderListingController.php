<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Credit Control / Advance / Emergency Fund / Report / Reminder —
 * PAGEID 1686 / MENUID 2037.
 *
 * Legacy: `NAD_API_CC_EF_REPORT_REMINDER` with `$_GET['listingReport']=1`
 * on `ccontroller_reminder` (`DB_SECOND_DATABASE`).
 */
class EmergencyFundReminderListingController extends Controller
{
    use ApiResponse;

    private const SORT_MAP = [
        'crm_debtor_id' => 'crm_debtor_id',
        'crm_debtor_name' => 'crm_debtor_name',
        'crm_invoice_no' => 'crm_invoice_no',
        'crm_amount_inv' => 'crm_amount_inv',
        'crm_reminder_bil' => 'crm_reminder_bil',
        'crm_reminder_date_disp' => 'crm_reminder_date',
        'full_address' => 'full_address',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $qRaw = trim((string) $request->input('q', ''));

        $sortKey = (string) $request->input('sort_by', 'crm_debtor_id');
        $sortCol = self::SORT_MAP[$sortKey] ?? 'crm_debtor_id';
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $needle = mb_strtoupper($qRaw, 'UTF-8');
        $pattern = $needle === '' ? null : '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';

        $base = DB::connection('mysql_secondary')
            ->table('ccontroller_reminder AS cr');

        $base->select([
            'cr.crm_debtor_id',
            'cr.crm_debtor_name',
            'cr.crm_invoice_no',
            'cr.crm_amount_inv',
            'cr.crm_reminder_bil',
            DB::raw("DATE_FORMAT(cr.crm_reminder_date, '%d/%m/%Y') AS crm_reminder_date_disp"),
            DB::raw("TRIM(CONCAT_WS(' ',
                IFNULL(cr.crm_address1,''),
                IFNULL(cr.crm_address2,''),
                IFNULL(cr.crm_address3,''),
                IFNULL(cr.crm_pcode,''),
                IFNULL(cr.crm_city,''),
                IFNULL(cr.crm_state,''),
                IFNULL(cr.crm_country,''))) AS full_address"),
        ]);

        if ($pattern !== null) {
            $base->whereRaw(
                "UPPER(CONCAT_WS('__',
                    IFNULL(cr.crm_debtor_id,''),
                    IFNULL(cr.crm_debtor_name,''),
                    IFNULL(cr.crm_invoice_no,''),
                    IFNULL(cr.crm_amount_inv,''),
                    IFNULL(cr.crm_reminder_bil,''),
                    IFNULL(DATE_FORMAT(cr.crm_reminder_date, '%d/%m/%Y'),''),
                    IFNULL(TRIM(CONCAT_WS(' ',
                        IFNULL(cr.crm_address1,''), IFNULL(cr.crm_address2,''), IFNULL(cr.crm_address3,''),
                        IFNULL(cr.crm_pcode,''), IFNULL(cr.crm_city,''), IFNULL(cr.crm_state,''), IFNULL(cr.crm_country,'')
                    )),'')
                )) LIKE ?",
                [$pattern]
            );
        }

        /** @phpstan-ignore-next-line */
        $total = (clone $base)->count();

        $listQuery = (clone $base);

        if ($sortCol === 'full_address') {
            /** @phpstan-ignore-next-line */
            $listQuery->orderBy(
                DB::raw("TRIM(CONCAT_WS(' ',
                    IFNULL(cr.crm_address1,''), IFNULL(cr.crm_address2,''), IFNULL(cr.crm_address3,''),
                    IFNULL(cr.crm_pcode,''), IFNULL(cr.crm_city,''), IFNULL(cr.crm_state,''), IFNULL(cr.crm_country,'')
                ))"),
                $sortDir
            );
        } else {
            /** @phpstan-ignore-next-line */
            $listQuery->orderBy('cr.'.$sortCol, $sortDir);
        }

        /** @phpstan-ignore-next-line */
        $rows = $listQuery
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($rows as $index => $row) {
            /** @phpstan-ignore-next-line */
            $r = json_decode(json_encode($row), true);
            $r['list_index'] = ($page - 1) * $limit + $index + 1;
            $data[] = $r;
        }

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / $limit ?: 1),
        ]);
    }
}
