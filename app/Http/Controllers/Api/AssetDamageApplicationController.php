<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Damage asset application workspace (Kerisi menu 3481 / PAGEID 2891).
 *
 * Header: {@see asset_damage_master}; line grid: {@see asset_damage_details} + {@see asset_inventory_main};
 * process flow: {@see wf_process} / {@see wf_application_status} for workflow code ASSET_DAMAGE_REPORT.
 */
class AssetDamageApplicationController extends Controller
{
    use ApiResponse;

    private function cx()
    {
        return DB::connection('mysql_secondary');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function show(int $drmId): JsonResponse
    {
        $conn = $this->cx();
        $master = $conn->table('asset_damage_master')->where('drm_id', $drmId)->first();
        if (! $master) {
            return $this->sendError(404, 'NOT_FOUND', 'Damage application not found');
        }

        $hdrStatus = (string) ($master->drm_status ?? '');
        $header = [
            'drmId' => (int) $master->drm_id,
            'drmReportNo' => (string) ($master->drm_report_no ?? ''),
            'drmDescription' => (string) ($master->drm_description ?? ''),
            'drmStatus' => $hdrStatus !== '' ? strtoupper(trim($hdrStatus)) : 'DRAFT',
        ];

        $rows = $conn->table('asset_damage_details as drd')
            ->leftJoin('asset_inventory_main as aim', 'drd.aim_asset_code', '=', 'aim.aim_asset_code')
            ->leftJoin('room_main as rmm', 'aim.aim_cur_room', '=', 'rmm.rmm_code')
            ->leftJoin('building_main as bdm', 'rmm.bdm_code', '=', 'bdm.bdm_code')
            ->leftJoin('organization_unit as ou', 'drd.oun_code', '=', 'ou.oun_code')
            ->leftJoin('fund_type as ft', 'drd.fty_fund_type', '=', 'ft.fty_fund_type')
            ->leftJoin('account_main as am', 'drd.acm_acct_code', '=', 'am.acm_acct_code')
            ->leftJoin('lookup_details as lcat', function ($j) {
                $j->on('aim.aim_category', '=', 'lcat.lde_value')
                    ->where('lcat.lma_code_name', '=', 'ITEM_CATEGORY');
            })
            ->leftJoin('item_subcategory as isc', function ($j) {
                $j->on('aim.aim_category', '=', 'isc.isc_category_code')
                    ->on('aim.aim_gasset_subcategory', '=', 'isc.isc_subcategory_code');
            })
            ->where('drd.drm_id', $drmId)
            ->orderBy('drd.drd_report_details_id')
            ->get([
                'drd.drd_report_details_id',
                'drd.aim_asset_code',
                'aim.aim_asset_desc',
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(aim.aim_category,''), NULLIF(lcat.lde_description,''))), '') AS category_display"),
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(aim.aim_gasset_subcategory,''), NULLIF(isc.isc_subcategory_desc,''))), '') AS subcategory_display"),
                DB::raw("TRIM(CONCAT_WS(' / ', NULLIF(aim.aim_brand_name,''), NULLIF(aim.aim_model,''))) AS brand_model"),
                DB::raw("TRIM(CONCAT_WS(' / ', NULLIF(aim.aim_serial_no,''), NULLIF(aim.aim_chasis_no,''))) AS serial_chasis"),
                'aim.aim_plate_no',
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(drd.oun_code,''), NULLIF(ou.oun_desc,''))), '') AS ptj_display"),
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(drd.fty_fund_type,''), NULLIF(ft.fty_fund_desc,''))), '') AS fund_display"),
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(drd.acm_acct_code,''), NULLIF(am.acm_acct_desc,''))), '') AS account_display"),
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(bdm.bdm_code,''), NULLIF(bdm.bdm_name,''))), '') AS building_display"),
                DB::raw("NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(rmm.rmm_code,''), NULLIF(rmm.rmm_name,''))), '') AS room_display"),
                'drd.drd_last_user',
                'drd.drd_damage_details',
                'drd.drd_damage_date',
                'drd.drd_recommendation',
                'drd.drd_est_maintenance_cost',
                'drd.aim_install_cost',
            ]);

        $linesOut = $rows->values()->map(function ($row, int $i) {
            $damageDate = '';
            if (! empty($row->drd_damage_date)) {
                try {
                    $c = Carbon::parse($row->drd_damage_date);
                    if ($c->year > 1900) {
                        $damageDate = $c->format('d/m/Y');
                    }
                } catch (\Throwable) {
                    $damageDate = '';
                }
            }

            return [
                'index' => $i + 1,
                'drdReportDetailsId' => (int) $row->drd_report_details_id,
                'assetNo' => (string) ($row->aim_asset_code ?? ''),
                'assetDesc' => (string) ($row->aim_asset_desc ?? ''),
                'category' => (string) ($row->category_display ?? ''),
                'subcategory' => (string) ($row->subcategory_display ?? ''),
                'brandModel' => (string) ($row->brand_model ?? ''),
                'serialChasis' => (string) ($row->serial_chasis ?? ''),
                'plateNo' => (string) ($row->aim_plate_no ?? ''),
                'ptj' => (string) ($row->ptj_display ?? ''),
                'fundType' => (string) ($row->fund_display ?? ''),
                'accountCode' => (string) ($row->account_display ?? ''),
                'building' => (string) ($row->building_display ?? ''),
                'room' => (string) ($row->room_display ?? ''),
                'lastUser' => (string) ($row->drd_last_user ?? ''),
                'damageDetails' => (string) ($row->drd_damage_details ?? ''),
                'damageDate' => $damageDate,
                'recommendation' => (string) ($row->drd_recommendation ?? ''),
                'estimatedCost' => $row->drd_est_maintenance_cost !== null && $row->drd_est_maintenance_cost !== ''
                    ? (string) $row->drd_est_maintenance_cost
                    : '',
                'installCost' => $row->aim_install_cost !== null && $row->aim_install_cost !== ''
                    ? (string) $row->aim_install_cost
                    : '',
            ];
        });

        $appIds = array_values(array_unique(array_filter(
            [(string) ($master->drm_report_no ?? ''), (string) $master->drm_id],
            fn ($v) => $v !== ''
        )));

        $processFlow = [];
        if ($appIds !== []) {
            $flowRows = $conn
                ->table('wf_process as wfp')
                ->leftJoin('wf_application_status as was', function ($j) use ($appIds) {
                    $j->on('wfp.wfp_process_id', '=', 'was.was_process_id')
                        ->whereIn('was.was_application_id', $appIds)
                        ->where('was.was_workflow_code', '=', 'ASSET_DAMAGE_REPORT');
                })
                ->leftJoin('staff as stf', 'was.createdby', '=', 'stf.stf_ad_username')
                ->leftJoin('staff_service as ss', function ($j) {
                    $j->on('ss.stf_staff_id', '=', 'stf.stf_staff_id')
                        ->where('ss.sts_job_flag', '=', 1);
                })
                ->where('wfp.wfp_workflow_code', 'ASSET_DAMAGE_REPORT')
                ->orderBy('wfp.wfp_sequence')
                ->orderBy('was.createddate')
                ->get([
                    'wfp.wfp_process_name',
                    'was.was_extended_field',
                    'was.was_notes',
                    'was.createddate as was_createddate',
                    'was.was_createdby_ptj',
                    'ss.sts_oun_code',
                    'ss.sts_extended_field',
                    'stf.stf_email_addr',
                    'stf.stf_telno_work',
                ]);

            foreach ($flowRows as $r) {
                $wasExt = $this->decodeJson($r->was_extended_field);
                $stsExt = $this->decodeJson($r->sts_extended_field);

                $ounCode = (string) ($r->sts_oun_code ?? '');
                $ounDesc = (string) ($stsExt['sts_oun_desc'] ?? '');
                $ounFromStaff = trim($ounCode.($ounDesc !== '' ? '-'.$ounDesc : ''), '-');

                $ptjField = (string) ($r->was_createdby_ptj ?? '');
                $ptjDesc = (string) ($wasExt['was_createdby_ptj_desc'] ?? '');
                $ounFromWas = trim($ptjField.($ptjDesc !== '' ? '-'.$ptjDesc : ''), '-');
                $ounLabel = $ounFromWas !== '' ? $ounFromWas : $ounFromStaff;

                $createdBy = (string) ($wasExt['createdby_name'] ?? '');
                $statusDesc = strtoupper((string) ($wasExt['was_status_desc'] ?? ''));

                $createdAt = $r->was_createddate
                    ? Carbon::parse($r->was_createddate)
                    : null;

                $processFlow[] = [
                    'processName' => (string) ($r->wfp_process_name ?? ''),
                    'createdByName' => $createdBy,
                    'ounDesc' => $ounLabel,
                    'emailAddr' => (string) ($r->stf_email_addr ?? ''),
                    'telNoWork' => (string) ($r->stf_telno_work ?? ''),
                    'statusDesc' => $statusDesc,
                    'remark' => (string) ($r->was_notes ?? ''),
                    'createdDate' => $createdAt?->format('d/m/Y') ?? '',
                    'createdTime' => $createdAt?->format('h:i A') ?? '',
                ];
            }
        }

        return $this->sendOk([
            'header' => $header,
            'lines' => $linesOut,
            'processFlow' => $processFlow,
        ]);
    }
}
