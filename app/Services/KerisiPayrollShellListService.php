<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shell list service for Payroll pages (MENUID 1122 parent).
 * Source: PAGE_MENUID1122_LEVEL3.json.
 * Each handler mirrors the legacy PHP BL SQL using the Laravel query-builder
 * on the `mysql_secondary` connection. No raw SQL strings; query builder only.
 *
 * Return contract: ['rows' => array, 'total' => int, 'connector' => string]
 */
class KerisiPayrollShellListService
{
    /** @return array{rows: list<array<string,mixed>>, total: int, connector: string} */
    public function fetch(int $menuId, Request $request): array
    {
        $page  = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q     = trim((string) $request->input('q', ''));

        return match ($menuId) {
            // ── Lookup ──────────────────────────────────────────────────────
            3310 => $this->lookupDetails($request, $page, $limit, $q, 'STAFFJOBTYPE'),
            1462 => $this->lookupDetails($request, $page, $limit, $q, 'STAFFSTATUS'),
            1463 => $this->lookupDetails($request, $page, $limit, $q, 'STAFFJOBSTATUS'),
            1464 => $this->lookupDetails($request, $page, $limit, $q, 'TITLE'),
            1467 => $this->lookupDetails($request, $page, $limit, $q, 'RELIGION'),
            1468 => $this->lookupDetails($request, $page, $limit, $q, 'RACE'),
            1469 => $this->lookupDetails($request, $page, $limit, $q, 'STATE'),
            3339 => $this->lookupDetails($request, $page, $limit, $q, 'BNM_CODE_CIMB'),
            // ── Lookup — Staff Prefix ────────────────────────────────────────
            3328 => $this->staffPrefix($request, $page, $limit, $q),
            // ── Lookup — Service Scheme ──────────────────────────────────────
            1474 => $this->serviceScheme($request, $page, $limit, $q),
            // ── Lookup — Salary Grade ────────────────────────────────────────
            3329 => $this->salaryGrade($request, $page, $limit, $q),
            // ── Setup ────────────────────────────────────────────────────────
            1440 => $this->monthlySetupSalary($request, $page, $limit, $q),
            1441 => $this->employerAccountInfo($request, $page, $limit, $q),
            1442 => $this->incomeType($request, $page, $limit, $q),
            1443 => $this->taxChildRelief($request, $page, $limit, $q),
            1995 => $this->otherDeduction($request, $page, $limit, $q),
            2674 => $this->taxRateCalculator($request, $page, $limit, $q),
            2948 => $this->activityMapping($request, $page, $limit, $q),
            2940 => $this->incomeCodeByInvoiceType($request, $page, $limit, $q),
            3020 => $this->changeEpfContribution($request, $page, $limit, $q),
            3222 => $this->deductionCodeByAccountCode($request, $page, $limit, $q),
            // ── Staff Profile ────────────────────────────────────────────────
            1325 => $this->listOfStaff($request, $page, $limit, $q),
            // ── Salary Processing ────────────────────────────────────────────
            2027 => $this->salaryGenerationVerification($request, $page, $limit, $q),
            // ── Salary Crediting ─────────────────────────────────────────────
            1927 => $this->journalList($request, $page, $limit, $q),
            1425 => $this->voucherList($request, $page, $limit, $q),
            // ── Allowance & Deduction ────────────────────────────────────────
            1850 => $this->listAllowanceDeduction($request, $page, $limit, $q),
            1845 => $this->allowanceDeductionBulks($request, $page, $limit, $q),
            1891 => $this->individualAllowanceDeduction($request, $page, $limit, $q),
            3032 => $this->deleteByBulk($request, $page, $limit, $q),
            // ── Employee Benefit ─────────────────────────────────────────────
            2102 => $this->gcrListing($request, $page, $limit, $q),
            // ── Income Tax ───────────────────────────────────────────────────
            2308 => $this->ecAccountCode($request, $page, $limit, $q),
            2313 => $this->ecRemunerationStaff($request, $page, $limit, $q),
            2759 => $this->processByBill($request, $page, $limit, $q),
            // ── Report ───────────────────────────────────────────────────────
            1476 => $this->penyataGajiInduk($request, $page, $limit, $q),
            1480 => $this->penyataSaraanPotongan($request, $page, $limit, $q),
            1888 => $this->creditingToBank($request, $page, $limit, $q),
            1893 => $this->incomeAdjustmentReport($request, $page, $limit, $q),
            1836 => $this->listingOfStaffReport($request, $page, $limit, $q),
            1889 => $this->varianceAllowanceDeduction($request, $page, $limit, $q),
            1978 => $this->dataChangeChecklist($request, $page, $limit, $q),
            1894 => $this->listOfPaymentReceiver($request, $page, $limit, $q),
            2549 => $this->journalLog($request, $page, $limit, $q),
            2675 => $this->varianceByIncomeCode($request, $page, $limit, $q),
            2681 => $this->varianceByType($request, $page, $limit, $q),
            2698 => $this->allowanceAndDeductionReport($request, $page, $limit, $q),
            2835 => $this->deductionListPerMonth($request, $page, $limit, $q),
            2913 => $this->incomeTypeList($request, $page, $limit, $q),
            3337 => $this->perjawatan($request, $page, $limit, $q),
            3335 => $this->senaraiElaun($request, $page, $limit, $q),
            3336 => $this->laporanKodElaun($request, $page, $limit, $q),
            // ── Integration ──────────────────────────────────────────────────
            2540 => $this->otherDeductionAdmin($request, $page, $limit, $q),
            2773 => $this->emergencyFund($request, $page, $limit, $q),
            3309 => $this->loanDeferredPayment($request, $page, $limit, $q),
            2749 => $this->monthlyLoan($request, $page, $limit, $q),
            2962 => $this->monthlyInvoice($request, $page, $limit, $q),
            3347 => $this->loanStatusComplete($request, $page, $limit, $q),
            // ── Kew 8 ────────────────────────────────────────────────────────
            3440 => $this->kew8ListOfStaff($request, $page, $limit, $q),
            3449 => $this->listOfKew8Forms($request, $page, $limit, $q),
            default => ['rows' => [], 'total' => 0, 'connector' => 'payroll_shell_preview'],
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function conn(): \Illuminate\Database\Connection
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $q): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    }

    private function paginate(\Illuminate\Database\Query\Builder $base, int $page, int $limit): array
    {
        $total = (clone $base)->count();
        $rows  = $base->skip(($page - 1) * $limit)->take($limit)->get()->toArray();
        return [
            'rows'  => array_map(fn ($r) => (array) $r, $rows),
            'total' => $total,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERIC: lookup_details by lma_code_name
    // ─────────────────────────────────────────────────────────────────────────
    private function lookupDetails(Request $request, int $page, int $limit, string $q, string $codeName): array
    {
        $base = $this->conn()->table('lookup_details')
            ->where('lma_code_name', $codeName)
            ->select([
                'lde_id',
                'lde_value',
                'lde_description',
                'lde_description2',
                'lde_sorting',
                'lde_status',
            ])
            ->selectRaw("IF(lde_status='1','ACTIVE','INACTIVE') as lde_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(lde_value,''), IFNULL(lde_description,''), IF(lde_status='1','ACTIVE','INACTIVE'))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('lde_sorting')->orderBy('lde_value');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => "lookup_details:{$codeName}"]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3328 — Lookup > Staff Prefix
    // Table: staff_type_main
    // ─────────────────────────────────────────────────────────────────────────
    private function staffPrefix(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_type_main')
            ->select(['stm_id', 'stm_code', 'stm_desc', 'stm_default_dep', 'stm_prefix', 'stm_no', 'stm_length']);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(stm_code,''), IFNULL(stm_desc,''), IFNULL(stm_default_dep,''), IFNULL(stm_prefix,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('stm_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'staff_prefix']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1474 — Lookup > Service Scheme
    // Table: service_scheme
    // ─────────────────────────────────────────────────────────────────────────
    private function serviceScheme(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('service_scheme')
            ->select([
                'ssc_service_code', 'ssc_service_desc', 'ssc_service_group',
                'ssc_class_code', 'sr_salary_grade', 'ssc_service_eng', 'ssc_service_type',
            ])
            ->selectRaw(
                "(SELECT DISTINCT UPPER(lde_description) FROM lookup_details WHERE lma_code_name = 'SERVICE_TYPE' AND lde_value = ssc_service_type) as service_type_desc"
            );

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ssc_service_code,''), IFNULL(ssc_service_desc,''), IFNULL(ssc_service_group,''), IFNULL(ssc_class_code,''), IFNULL(sr_salary_grade,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        foreach ([
            'sscServiceCode' => 'ssc_service_code',
            'sscServiceDesc' => 'ssc_service_desc',
            'sscServiceGroup' => 'ssc_service_group',
            'sscClassCode' => 'ssc_class_code',
            'srSalaryGrade' => 'sr_salary_grade',
        ] as $param => $col) {
            $val = trim((string) $request->input($param, ''));
            if ($val !== '') {
                $base->where($col, $val);
            }
        }

        $base->orderBy('ssc_service_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'service_scheme']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3329 — Lookup > Salary Grade
    // Table: salary_grade_setup
    // ─────────────────────────────────────────────────────────────────────────
    private function salaryGrade(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('salary_grade_setup')
            ->select([
                'sgs_salary_grade', 'sgs_grade', 'sgs_salary_min', 'sgs_salary_max',
                'sgs_kgt1', 'sgs_kgt2', 'sgs_salary_adjustment',
                'sgs_increment_amt', 'sgs_increment_percent', 'sgs_status',
            ])
            ->selectRaw("IF(sgs_status=1,'ACTIVE','INACTIVE') as sgs_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sgs_salary_grade,''), IFNULL(sgs_grade,''), IF(sgs_status=1,'active','inactive'))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        foreach ([
            'sgsSalaryGrade' => 'sgs_salary_grade',
            'sgsGrade' => 'sgs_grade',
            'sgsSalaryMin' => 'sgs_salary_min',
            'sgsSalaryMax' => 'sgs_salary_max',
        ] as $param => $col) {
            $val = trim((string) $request->input($param, ''));
            if ($val !== '') {
                $base->where($col, $val);
            }
        }

        $base->orderBy('sgs_salary_grade');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'salary_grade']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1440 — Setup > Monthly Setup Salary
    // Table: payroll_process_sch
    // ─────────────────────────────────────────────────────────────────────────
    private function monthlySetupSalary(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_process_sch')
            ->select(['pps_code', 'pps_pay_month'])
            ->selectRaw("DATE_FORMAT(pps_open_date,'%d/%m/%Y') as pps_open_date")
            ->selectRaw("DATE_FORMAT(pps_close_date,'%d/%m/%Y') as pps_close_date")
            ->selectRaw("DATE_FORMAT(pps_start_pdate,'%d/%m/%Y') as pps_start_pdate")
            ->selectRaw("DATE_FORMAT(pps_end_pdate,'%d/%m/%Y') as pps_end_pdate")
            ->selectRaw("DATE_FORMAT(pps_pay_date,'%d/%m/%Y') as pps_pay_date")
            ->selectRaw("DATE_FORMAT(pps_payslip_release,'%d/%m/%Y') as pps_payslip_release");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pps_pay_month,''), IFNULL(pps_code,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfPayMonth = trim((string) $request->input('ppsPpayMonth', ''));
        if ($sfPayMonth !== '') {
            $base->where('pps_pay_month', 'like', '%' . $sfPayMonth . '%');
        }

        $base->orderByDesc('pps_pay_month');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'monthly_setup_salary']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1441 — Setup > Employer Account Info
    // Table: employer_details
    // ─────────────────────────────────────────────────────────────────────────
    private function employerAccountInfo(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('employer_details')
            ->select(['emd_id', 'ity_income_code', 'emd_type_desc', 'emd_ref_no', 'emd_cawangan', 'emd_status'])
            ->selectRaw("IF(emd_status='1','ACTIVE','INACTIVE') as emd_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(emd_id,''), IFNULL(ity_income_code,''), IFNULL(emd_type_desc,''), IFNULL(emd_ref_no,''), IFNULL(emd_cawangan,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('emd_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'employer_account_info']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1442 — Setup > Income Type (Level 1)
    // Table: income_type (ity_level=1)
    // ─────────────────────────────────────────────────────────────────────────
    private function incomeType(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('income_type')
            ->where('ity_level', 1)
            ->select(['ity_income_code', 'ity_income_desc', 'ity_level', 'ity_status'])
            ->selectRaw("IF(ity_status='1','ACTIVE','INACTIVE') as ity_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ity_income_code,''), IFNULL(ity_income_desc,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'income_type_l1']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1443 — Setup > Tax Child Relief
    // Table: childrelief
    // ─────────────────────────────────────────────────────────────────────────
    private function taxChildRelief(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('childrelief')
            ->select(['crf_childrelief_code', 'crf_childrelief_desc', 'crf_relief_multiplier', 'crf_amount', 'crf_status'])
            ->selectRaw("IF(crf_status='1','ACTIVE','INACTIVE') as crf_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(crf_childrelief_desc,''), IFNULL(crf_amount,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfDesc = trim((string) $request->input('crfChildreliefDesc', ''));
        if ($sfDesc !== '') {
            $base->where('crf_childrelief_desc', 'like', '%' . $sfDesc . '%');
        }
        $sfAmt = trim((string) $request->input('crfAmount', ''));
        if ($sfAmt !== '') {
            $base->where('crf_amount', $sfAmt);
        }

        $base->orderBy('crf_childrelief_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'tax_child_relief']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1995 — Setup > Other Deduction
    // Table: other_deduction_setup
    // ─────────────────────────────────────────────────────────────────────────
    private function otherDeduction(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('other_deduction_setup')
            ->select(['ods_id', 'ity_income_code', 'ods_refno_req', 'ods_instructions', 'ods_refno_length', 'ods_status'])
            ->selectRaw("IFNULL(ods_extended_field->>'$.ods_status_desc', IF(ods_status='1','ACTIVE','INACTIVE')) as ods_status_desc");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ity_income_code,''), IFNULL(ods_refno_req,''), IFNULL(ods_instructions,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'other_deduction']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2674 — Setup > Tax Rate Calculator
    // Table: tax_rate_calc
    // ─────────────────────────────────────────────────────────────────────────
    private function taxRateCalculator(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tax_rate_calc')
            ->select([
                'trc_id', 'trc_year', 'trc_resident',
                'amt_remuneration__year_P_from', 'amt_remuneration__year_P_to',
                'percentage_tax_R', 'trc_status',
            ])
            ->selectRaw("IF(trc_status=1,'ACTIVE','INACTIVE') as trc_status_label")
            ->selectRaw("IF(trc_resident='Y','YES','NO') as trc_resident_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(trc_year,''), IF(trc_status=1,'active','inactive'))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfYear = trim((string) $request->input('trcYear', ''));
        if ($sfYear !== '') {
            $base->where('trc_year', $sfYear);
        }

        $base->orderByDesc('trc_year')->orderBy('amt_remuneration__year_P_from');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'tax_rate_calculator']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2948 — Setup > Activity Mapping
    // Table: org_unit_activity_mapping (join activity_type)
    // ─────────────────────────────────────────────────────────────────────────
    private function activityMapping(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('org_unit_activity_mapping as oam')
            ->leftJoin('org_unit as ou', 'ou.oun_code', '=', 'oam.oun_code')
            ->select([
                'oam.oun_code', 'ou.oun_desc',
                'at_activity_code_budget', 'at_activity_code_actual',
                'at_activity_code_kontrak', 'at_activity_code_sambilan', 'at_activity_code_lain_lain',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(oam.oun_code,''), IFNULL(ou.oun_desc,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfOun = trim((string) $request->input('ounCodeSmartfilter', ''));
        if ($sfOun !== '') {
            $base->where('oam.oun_code', $sfOun);
        }

        $base->orderBy('oam.oun_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'activity_mapping']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2940 — Setup > Income Code by Invoice Type
    // Table: inc_code_invoice (join income_type, lookup_details)
    // ─────────────────────────────────────────────────────────────────────────
    private function incomeCodeByInvoiceType(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('inc_code_invoice as ici')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'ici.ici_income_code')
            ->leftJoin('lookup_details as ld', function ($join) {
                $join->on('ld.lde_value', '=', 'ici.ici_inv_type')
                    ->where('ld.lma_code_name', '=', 'INVOICE_TYPE');
            })
            ->select([
                'ici.ici_id', 'ici.ici_income_code', 'it.ity_income_desc',
                'ld.lde_description as inv_type_desc', 'ici.ici_status',
            ])
            ->selectRaw("IF(ici.ici_status=1,'ACTIVE','INACTIVE') as ici_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ici.ici_income_code,''), IFNULL(it.ity_income_desc,''), IFNULL(ld.lde_description,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('ici.ici_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'income_code_invoice_type']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3020 — Setup > Change EPF Contribution
    // Table: staff + staff_salary (EPF percentages)
    // ─────────────────────────────────────────────────────────────────────────
    private function changeEpfContribution(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff as s')
            ->leftJoin('staff_salary as ss', 'ss.stf_staff_id', '=', 's.stf_staff_id')
            ->select([
                's.stf_staff_id', 's.stf_staff_name', 's.stf_staff_status',
                'ss.sal_epf_empyer_pct', 'ss.sal_epf_empyee_pct',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(s.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter params
        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $base->where('s.stf_staff_status', $status);
        }

        $base->orderBy('s.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'change_epf_contribution']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3222 — Setup > Deduction Code by Account Code
    // Table: income_type joined with account_code_mapping (ity_income_code LIKE 'D%')
    // ─────────────────────────────────────────────────────────────────────────
    private function deductionCodeByAccountCode(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('income_type as it')
            ->leftJoin('income_acct_mapping as iam', 'iam.ity_income_code', '=', 'it.ity_income_code')
            ->leftJoin('account_master as am', 'am.acm_acct_code', '=', 'iam.acm_acct_code')
            ->where('it.ity_income_code', 'like', 'D%')
            ->whereRaw('it.ity_level = (SELECT MAX(ity_level) FROM income_type)')
            ->select([
                'it.ity_income_code', 'it.ity_income_desc',
                'iam.acm_acct_code', 'am.acm_acct_desc', 'it.ity_status',
            ])
            ->selectRaw("IF(it.ity_status='1','ACTIVE','INACTIVE') as ity_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(it.ity_income_code,''), IFNULL(it.ity_income_desc,''), IFNULL(iam.acm_acct_code,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('it.ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'deduction_code_account_code']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1325 — Staff Profile > List of Staff
    // Table: staff + staff_service + service_scheme
    // ─────────────────────────────────────────────────────────────────────────
    private function listOfStaff(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff as s')
            ->leftJoin('staff_service as ss', 'ss.stf_staff_id', '=', 's.stf_staff_id')
            ->leftJoin('service_scheme as sc', 'sc.ssc_service_code', '=', 'ss.sts_jobcode')
            ->select([
                's.stf_staff_id', 's.stf_staff_name', 's.stf_ic_no',
                's.stf_staff_status',
                'ss.sts_salary_grade', 'ss.sts_job_status',
                'ss.sts_oun_code',
                'sc.ssc_service_code', 'sc.ssc_service_desc',
            ])
            ->selectRaw("DATE_FORMAT(ss.sts_join_date,'%d/%m/%Y') as sts_join_date")
            ->selectRaw("DATE_FORMAT(ss.sts_confirm_date,'%d/%m/%Y') as sts_confirm_date");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(s.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(s.stf_ic_no,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        foreach ([
            'mdlStfStaffId' => ['s.stf_staff_id', 'exact'],
            'mdlStfStaffName' => ['s.stf_staff_name', 'like'],
            'mdlStfIcNo' => ['s.stf_ic_no', 'like'],
            'mdlStsOunCode' => ['ss.sts_oun_code', 'exact'],
            'mdlStatusStaff' => ['ss.sts_salary_status', 'exact'],
            'mdlJobStatus' => ['ss.sts_job_status', 'exact'],
            'mdlStatus' => ['s.stf_staff_status', 'exact'],
            'mdlStsSalaryGrade' => ['ss.sts_salary_grade', 'exact'],
        ] as $param => [$col, $mode]) {
            $val = trim((string) $request->input($param, ''));
            if ($val !== '') {
                if ($mode === 'like') {
                    $base->where($col, 'like', '%' . $val . '%');
                } else {
                    $base->where($col, $val);
                }
            }
        }

        $base->orderBy('s.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'list_of_staff']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2027 — Salary Processing > Salary Generation Verification
    // Table: payroll_process_header
    // ─────────────────────────────────────────────────────────────────────────
    private function salaryGenerationVerification(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_process_header as pph')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pph.stf_staff_id')
            ->select([
                'pph.stf_staff_id', 's.stf_staff_name', 'pph.pps_pay_month',
                'pph.pph_total_allowance', 'pph.pph_total_deduction', 'pph.pph_net_salary',
                'pph.pph_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pph.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(pph.pps_pay_month,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pph.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'salary_generation_verification']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1927 — Salary Crediting > Journal
    // Table: manual_journal_master (mjm_system_id='MNL_PAYROLL')
    // ─────────────────────────────────────────────────────────────────────────
    private function journalList(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('manual_journal_master')
            ->where('mjm_system_id', 'MNL_PAYROLL')
            ->select([
                'mjm_journal_id', 'mjm_journal_no', 'mjm_journal_desc',
                'mjm_typeofjournal', 'mjm_total_amt', 'mjm_status',
                'mjm_enterdate', 'mjm_approvedate',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(mjm_journal_id,''), IFNULL(mjm_journal_no,''), IFNULL(mjm_journal_desc,''), IFNULL(mjm_status,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfStatus = trim((string) $request->input('sf_0', ''));
        if ($sfStatus !== '') {
            $base->where('mjm_status', 'like', '%' . $sfStatus . '%');
        }

        $base->orderByDesc('mjm_journal_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'journal_list']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1425 — Salary Crediting > Vouchers
    // Table: voucher_master (vom_system_id='PAYROLL')
    // ─────────────────────────────────────────────────────────────────────────
    private function voucherList(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('voucher_master')
            ->where('vom_system_id', 'PAYROLL')
            ->select([
                'vom_voucher_id', 'vom_voucher_no', 'vom_voucher_desc',
                'vom_total_amt', 'vom_status', 'vom_enterdate',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vom_voucher_id,''), IFNULL(vom_voucher_no,''), IFNULL(vom_status,''))) LIKE ?",
                [$like]
            );
        }

        $sfStatus = trim((string) $request->input('sf_0', ''));
        if ($sfStatus !== '') {
            $base->where('vom_status', 'like', '%' . $sfStatus . '%');
        }

        $base->orderByDesc('vom_voucher_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'voucher_list']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1850 — Allowance & Deduction > List of Allowance And Deduction
    // Table: staff_allowance_deduction
    // ─────────────────────────────────────────────────────────────────────────
    private function listAllowanceDeduction(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_allowance_deduction as sad')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sad.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'sad.ity_income_code')
            ->select([
                'sad.sad_id', 'sad.stf_staff_id', 's.stf_staff_name',
                'sad.ity_income_code', 'it.ity_income_desc',
                'sad.spa_start_date', 'sad.spa_end_date', 'sad.sad_amount',
                'sad.sad_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sad.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sad.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfStaffId = trim((string) $request->input('stfStaffId', ''));
        if ($sfStaffId !== '') {
            $base->where('sad.stf_staff_id', $sfStaffId);
        }
        $sfIncCode = trim((string) $request->input('ityIncomeCode', ''));
        if ($sfIncCode !== '') {
            $base->where('sad.ity_income_code', $sfIncCode);
        }

        $base->orderByDesc('sad.sad_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'list_allowance_deduction']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1845 — Allowance & Deduction > Bulks
    // Table: staff_allowance_deduction (by bulk filter)
    // ─────────────────────────────────────────────────────────────────────────
    private function allowanceDeductionBulks(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_allowance_deduction as sad')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sad.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'sad.ity_income_code')
            ->select([
                'sad.sad_id', 'sad.stf_staff_id', 's.stf_staff_name',
                'sad.ity_income_code', 'it.ity_income_desc',
                'sad.spa_start_date', 'sad.spa_end_date', 'sad.sad_amount',
                'sad.sad_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sad.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sad.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('sad.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'allowance_deduction_bulks']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1891 — Allowance & Deduction > Individual
    // Table: staff_allowance_deduction (filtered by top filter staff_id)
    // ─────────────────────────────────────────────────────────────────────────
    private function individualAllowanceDeduction(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_allowance_deduction as sad')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'sad.ity_income_code')
            ->select([
                'sad.sad_id', 'sad.stf_staff_id', 'sad.ity_income_code', 'it.ity_income_desc',
                'sad.spa_start_date', 'sad.spa_end_date', 'sad.sad_amount', 'sad.sad_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sad.stf_staff_id,''), IFNULL(sad.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $staffId = trim((string) $request->input('stfStaffId', ''));
        if ($staffId !== '') {
            $base->where('sad.stf_staff_id', $staffId);
        }

        $base->orderByDesc('sad.sad_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'individual_allowance_deduction']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3032 — Allowance & Deduction > Delete By Bulk
    // Table: staff_allowance_deduction (with bulk filter)
    // ─────────────────────────────────────────────────────────────────────────
    private function deleteByBulk(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_allowance_deduction as sad')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sad.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'sad.ity_income_code')
            ->select([
                'sad.sad_id', 'sad.stf_staff_id', 's.stf_staff_name',
                'sad.ity_income_code', 'it.ity_income_desc', 'sad.spa_start_date', 'sad.spa_end_date', 'sad.sad_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sad.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sad.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('sad.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'delete_by_bulk']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2102 — Employee Benefit (GCR) > GCR Listing
    // Table: gcr_master
    // ─────────────────────────────────────────────────────────────────────────
    private function gcrListing(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('gcr_master as gm')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'gm.stf_staff_id')
            ->select([
                'gm.gcr_id', 'gm.stf_staff_id', 's.stf_staff_name',
                'gm.gcr_year', 'gm.gcr_amount', 'gm.gcr_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(gm.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(gm.gcr_year,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderByDesc('gm.gcr_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'gcr_listing']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2308 — Income Tax > EC Account Code
    // Table: ec_account_code
    // ─────────────────────────────────────────────────────────────────────────
    private function ecAccountCode(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ec_account_code')
            ->select(['eac_id', 'eac_account_code', 'eac_description', 'eac_status'])
            ->selectRaw("IF(eac_status='1','ACTIVE','INACTIVE') as eac_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(eac_account_code,''), IFNULL(eac_description,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('eac_account_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'ec_account_code']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2313 — Income Tax > EC Remuneration Staff
    // Table: ec_remuneration (income)
    // ─────────────────────────────────────────────────────────────────────────
    private function ecRemunerationStaff(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ec_remuneration as er')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'er.stf_staff_id')
            ->select([
                'er.ecr_id', 'er.stf_staff_id', 's.stf_staff_name',
                'er.ecr_year', 'er.ecr_income_code', 'er.ecr_amount', 'er.ecr_type',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(er.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(er.ecr_year,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter: staff_id
        $staffId = trim((string) $request->input('stfStaffId', ''));
        if ($staffId !== '') {
            $base->where('er.stf_staff_id', $staffId);
        }

        $base->orderByDesc('er.ecr_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'ec_remuneration']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2759 — Income Tax > Process By Bill
    // Table: ec_process_bill
    // ─────────────────────────────────────────────────────────────────────────
    private function processByBill(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ec_process_bill')
            ->select(['epb_id', 'epb_bill_no', 'epb_year', 'epb_status', 'epb_process_date']);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(epb_bill_no,''), IFNULL(epb_year,''), IFNULL(epb_status,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $year = trim((string) $request->input('epbYear', ''));
        if ($year !== '') {
            $base->where('epb_year', $year);
        }

        $base->orderByDesc('epb_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'process_by_bill']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1476 — Report > Penyata Gaji Induk
    // Table: payroll_process_header + staff
    // ─────────────────────────────────────────────────────────────────────────
    private function penyataGajiInduk(Request $request, int $page, int $limit, string $q): array
    {
        $payMonth = trim((string) $request->input('payMonth', ''));

        $base = $this->conn()->table('payroll_process_header as pph')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pph.stf_staff_id')
            ->select([
                'pph.stf_staff_id', 's.stf_staff_name',
                'pph.pps_pay_month', 'pph.pph_total_allowance',
                'pph.pph_total_deduction', 'pph.pph_net_salary',
            ]);

        if ($payMonth !== '') {
            $base->where('pph.pps_pay_month', $payMonth);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pph.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pph.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'penyata_gaji_induk']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1480 — Report > Penyata Saraan dan Potongan
    // Table: payroll_allowance_detail + payroll_deduction_detail
    // ─────────────────────────────────────────────────────────────────────────
    private function penyataSaraanPotongan(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_allowance_detail as pad')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pad.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'pad.ity_income_code')
            ->select([
                'pad.pad_id', 'pad.stf_staff_id', 's.stf_staff_name',
                'pad.ity_income_code', 'it.ity_income_desc',
                'pad.pps_pay_month', 'pad.pad_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pad.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(pad.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter: staff_id
        $staffId = trim((string) $request->input('stfStaffId', ''));
        if ($staffId !== '') {
            $base->where('pad.stf_staff_id', $staffId);
        }
        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('pad.pps_pay_month', $payMonth);
        }

        $base->orderBy('pad.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'penyata_saraan_potongan']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1888 — Report > Crediting To Bank
    // Table: salary_bank_transfer
    // ─────────────────────────────────────────────────────────────────────────
    private function creditingToBank(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('salary_bank_transfer as sbt')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sbt.stf_staff_id')
            ->select([
                'sbt.sbt_id', 'sbt.stf_staff_id', 's.stf_staff_name',
                'sbt.pps_pay_month', 'sbt.sbt_bank_code', 'sbt.sbt_account_no',
                'sbt.sbt_amount', 'sbt.sbt_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(sbt.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sbt.sbt_bank_code,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('sbt.pps_pay_month', $payMonth);
        }

        // Smart filter
        $sfBank = trim((string) $request->input('sf_0', ''));
        if ($sfBank !== '') {
            $base->where('sbt.sbt_bank_code', $sfBank);
        }

        $base->orderBy('sbt.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'crediting_to_bank']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1893 — Report > Income Adjustment Report
    // Table: payroll_income_adjustment
    // ─────────────────────────────────────────────────────────────────────────
    private function incomeAdjustmentReport(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_income_adjustment as pia')
            ->leftJoin('account_master as am', 'am.acm_acct_code', '=', 'pia.acm_acct_code')
            ->select([
                'pia.pia_id', 'pia.pps_pay_month', 'pia.acm_acct_code',
                'am.acm_acct_desc', 'pia.pia_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pia.pps_pay_month,''), IFNULL(pia.acm_acct_code,''), IFNULL(am.acm_acct_desc,''))) LIKE ?",
                [$like]
            );
        }

        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('pia.pps_pay_month', $payMonth);
        }

        $base->orderByDesc('pia.pps_pay_month');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'income_adjustment_report']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1836 — Report > Listing of Staff
    // Table: staff + staff_service
    // ─────────────────────────────────────────────────────────────────────────
    private function listingOfStaffReport(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff as s')
            ->leftJoin('staff_service as ss', 'ss.stf_staff_id', '=', 's.stf_staff_id')
            ->leftJoin('service_scheme as sc', 'sc.ssc_service_code', '=', 'ss.sts_jobcode')
            ->select([
                's.stf_staff_id', 's.stf_staff_name', 's.stf_ic_no',
                's.stf_staff_status', 'ss.sts_oun_code', 'sc.ssc_service_desc',
                'ss.sts_salary_grade', 'ss.sts_job_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(s.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(s.stf_ic_no,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfJobStatus = trim((string) $request->input('sf_0', ''));
        if ($sfJobStatus !== '') {
            $base->where('ss.sts_job_status', $sfJobStatus);
        }
        $sfStatus = trim((string) $request->input('sf_1', ''));
        if ($sfStatus !== '') {
            $base->where('s.stf_staff_status', $sfStatus);
        }

        $base->orderBy('s.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'listing_of_staff']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1889 — Report > Variance Allowance And Deduction
    // Table: payroll_variance
    // ─────────────────────────────────────────────────────────────────────────
    private function varianceAllowanceDeduction(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_variance as pv')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pv.stf_staff_id')
            ->select([
                'pv.pvr_id', 'pv.stf_staff_id', 's.stf_staff_name',
                'pv.pvr_month_from', 'pv.pvr_month_to', 'pv.pvr_amount_from', 'pv.pvr_amount_to', 'pv.pvr_variance',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pv.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pv.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'variance_allowance_deduction']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1978 — Report > Data Change Checklist
    // Table: payroll_data_change
    // ─────────────────────────────────────────────────────────────────────────
    private function dataChangeChecklist(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_data_change as pdc')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pdc.stf_staff_id')
            ->select([
                'pdc.pdc_id', 'pdc.stf_staff_id', 's.stf_staff_name',
                'pdc.pdc_field_name', 'pdc.pdc_old_value', 'pdc.pdc_new_value',
                'pdc.pdc_change_date',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pdc.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(pdc.pdc_field_name,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderByDesc('pdc.pdc_change_date');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'data_change_checklist']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1894 — Report > List Of Payment Receiver
    // Table: payroll_payment_receiver
    // ─────────────────────────────────────────────────────────────────────────
    private function listOfPaymentReceiver(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_payment_receiver as ppr')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'ppr.stf_staff_id')
            ->select([
                'ppr.ppr_id', 'ppr.stf_staff_id', 's.stf_staff_name',
                'ppr.pps_pay_month', 'ppr.ppr_bank_code', 'ppr.ppr_account_no',
                'ppr.ppr_amount', 'ppr.ppr_type',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ppr.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('ppr.pps_pay_month', $payMonth);
        }

        $base->orderBy('ppr.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'list_payment_receiver']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2549 — Report > Journal Log
    // Table: payroll_journal_log
    // ─────────────────────────────────────────────────────────────────────────
    private function journalLog(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_journal_log as pjl')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pjl.stf_staff_id')
            ->select([
                'pjl.pjl_id', 'pjl.pps_pay_month', 'pjl.stf_staff_id', 's.stf_staff_name',
                'pjl.pjl_journal_no', 'pjl.pjl_amount', 'pjl.pjl_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pjl.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(pjl.pps_pay_month,''))) LIKE ?",
                [$like]
            );
        }

        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('pjl.pps_pay_month', $payMonth);
        }

        $base->orderByDesc('pjl.pjl_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'journal_log']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2675 — Report > Variance By Income Code
    // Table: payroll_variance_by_income_code
    // ─────────────────────────────────────────────────────────────────────────
    private function varianceByIncomeCode(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_variance_by_income as pvi')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'pvi.ity_income_code')
            ->select([
                'pvi.pvi_id', 'pvi.ity_income_code', 'it.ity_income_desc',
                'pvi.pvi_month_from', 'pvi.pvi_month_to',
                'pvi.pvi_amount_from', 'pvi.pvi_amount_to', 'pvi.pvi_variance',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pvi.ity_income_code,''), IFNULL(it.ity_income_desc,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pvi.ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'variance_by_income_code']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2681 — Report > Variance By Type
    // Table: payroll_variance_by_type
    // ─────────────────────────────────────────────────────────────────────────
    private function varianceByType(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_variance_by_type as pvt')
            ->select([
                'pvt.pvt_id', 'pvt.pvt_type', 'pvt.pvt_month_from', 'pvt.pvt_month_to',
                'pvt.pvt_amount_from', 'pvt.pvt_amount_to', 'pvt.pvt_variance',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pvt.pvt_type,''), IFNULL(pvt.pvt_month_from,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pvt.pvt_type');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'variance_by_type']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2698 — Report > Allowance and Deduction Report
    // Table: payroll_allowance_detail + payroll_deduction_detail
    // ─────────────────────────────────────────────────────────────────────────
    private function allowanceAndDeductionReport(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_allowance_detail as pad')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pad.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'pad.ity_income_code')
            ->select([
                'pad.pad_id', 'pad.stf_staff_id', 's.stf_staff_name',
                'pad.ity_income_code', 'it.ity_income_desc',
                'pad.pps_pay_month', 'pad.pad_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pad.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('pad.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'allowance_deduction_report']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2835 — Report > Deduction List Per Month
    // Table: payroll_deduction_detail
    // ─────────────────────────────────────────────────────────────────────────
    private function deductionListPerMonth(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_deduction_detail as pdd')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'pdd.stf_staff_id')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'pdd.ity_income_code')
            ->select([
                'pdd.pdd_id', 'pdd.stf_staff_id', 's.stf_staff_name',
                'pdd.ity_income_code', 'it.ity_income_desc',
                'pdd.pps_pay_month', 'pdd.pdd_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pdd.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(pdd.ity_income_code,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfJobStatus = trim((string) $request->input('sf_0', ''));
        if ($sfJobStatus !== '') {
            $base->where('pdd.sts_job_status', $sfJobStatus);
        }

        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('pdd.pps_pay_month', $payMonth);
        }

        $base->orderByDesc('pdd.pps_pay_month')->orderBy('pdd.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'deduction_list_per_month']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2913 — Report > Income Type List
    // Table: income_type (all levels)
    // ─────────────────────────────────────────────────────────────────────────
    private function incomeTypeList(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('income_type')
            ->select(['ity_income_code', 'ity_income_desc', 'ity_level', 'ity_status'])
            ->selectRaw("IF(ity_status='1','ACTIVE','INACTIVE') as ity_status_label");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ity_income_code,''), IFNULL(ity_income_desc,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderBy('ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'income_type_list']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3337 — Report > Perjawatan
    // Table: staff + staff_service
    // ─────────────────────────────────────────────────────────────────────────
    private function perjawatan(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff as s')
            ->leftJoin('staff_service as ss', 'ss.stf_staff_id', '=', 's.stf_staff_id')
            ->select([
                's.stf_staff_id', 's.stf_staff_name', 's.stf_ic_no',
                'ss.sts_oun_code', 'ss.sts_jobcode', 'ss.sts_salary_grade',
                'ss.sts_job_status', 's.stf_staff_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(s.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(ss.sts_oun_code,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $jobType = trim((string) $request->input('jobType', ''));
        if ($jobType !== '') {
            $base->where('ss.sts_job_type', $jobType);
        }

        $base->orderBy('s.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'perjawatan']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3335 — Report > Senarai Tuntutan Elaun Lebih Masa
    // Table: payroll_overtime (overtime allowance claim list)
    // ─────────────────────────────────────────────────────────────────────────
    private function senaraiElaun(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_overtime as po')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'po.stf_staff_id')
            ->select([
                'po.po_id', 'po.stf_staff_id', 's.stf_staff_name',
                'po.po_year', 'po.po_month', 'po.po_hours', 'po.po_amount', 'po.po_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(po.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $jobType = trim((string) $request->input('jobType', ''));
        if ($jobType !== '') {
            $base->where('po.po_job_type', $jobType);
        }

        $base->orderByDesc('po.po_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'senarai_elaun_lebih_masa']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3336 — Report > Laporan Kod Elaun / Potongan
    // Table: payroll_allowance_detail + income_type
    // ─────────────────────────────────────────────────────────────────────────
    private function laporanKodElaun(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_allowance_detail as pad')
            ->leftJoin('income_type as it', 'it.ity_income_code', '=', 'pad.ity_income_code')
            ->select([
                'pad.pad_id', 'pad.ity_income_code', 'it.ity_income_desc',
                'pad.pps_pay_month', 'pad.pad_amount',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pad.ity_income_code,''), IFNULL(it.ity_income_desc,''), IFNULL(pad.pps_pay_month,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderByDesc('pad.pps_pay_month')->orderBy('pad.ity_income_code');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'laporan_kod_elaun']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2540 — Integration > Other Deduction (Admin)
    // Table: other_deduction_master
    // ─────────────────────────────────────────────────────────────────────────
    private function otherDeductionAdmin(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('other_deduction_master as odm')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'odm.stf_staff_id')
            ->select([
                'odm.odm_id', 'odm.stf_staff_id', 's.stf_staff_name',
                'odm.odm_request_date', 'odm.odm_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(odm.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(odm.odm_status,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfStatus = trim((string) $request->input('sf_0', ''));
        if ($sfStatus !== '') {
            $base->where('odm.odm_status', $sfStatus);
        }

        $base->orderByDesc('odm.odm_request_date');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'other_deduction_admin']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2773 — Integration > Emergency Fund
    // Table: emergency_fund + ccontroller_master + ccontroller_reminder
    // ─────────────────────────────────────────────────────────────────────────
    private function emergencyFund(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('emergency_fund as emf')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'emf.stf_staff_id')
            ->select([
                'emf.emf_id', 'emf.stf_staff_id', 's.stf_staff_name',
                'emf.emf_emergency_fund_no', 'emf.emf_amount', 'emf.emf_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(emf.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(emf.emf_emergency_fund_no,''))) LIKE ?",
                [$like]
            );
        }

        $base->orderByDesc('emf.emf_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'emergency_fund']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3309 — Integration > Loan Deferred Payment
    // Table: loan_deferred_payment
    // ─────────────────────────────────────────────────────────────────────────
    private function loanDeferredPayment(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('loan_deferred_payment as ldp')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'ldp.stf_staff_id')
            ->select([
                'ldp.ldp_id', 'ldp.stf_staff_id', 's.stf_staff_name',
                'ldp.ldp_loan_type', 'ldp.ldp_amount', 'ldp.ldp_deferred_month', 'ldp.ldp_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ldp.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(ldp.ldp_loan_type,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $loanType = trim((string) $request->input('loanType', ''));
        if ($loanType !== '') {
            $base->where('ldp.ldp_loan_type', $loanType);
        }

        $base->orderByDesc('ldp.ldp_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'loan_deferred_payment']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2749 — Integration > Monthly Loan
    // Table: loan_monthly_deduction
    // ─────────────────────────────────────────────────────────────────────────
    private function monthlyLoan(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('loan_monthly_deduction as lmd')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'lmd.stf_staff_id')
            ->select([
                'lmd.lmd_id', 'lmd.stf_staff_id', 's.stf_staff_name',
                'lmd.lmd_loan_type', 'lmd.lmd_pay_month', 'lmd.lmd_amount', 'lmd.lmd_balance',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(lmd.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(lmd.lmd_loan_type,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $loanType = trim((string) $request->input('loanType', ''));
        if ($loanType !== '') {
            $base->where('lmd.lmd_loan_type', $loanType);
        }
        $payMonth = trim((string) $request->input('payMonth', ''));
        if ($payMonth !== '') {
            $base->where('lmd.lmd_pay_month', $payMonth);
        }

        $base->orderByDesc('lmd.lmd_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'monthly_loan']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2962 — Integration > Monthly Invoice
    // Table: monthly_invoice
    // ─────────────────────────────────────────────────────────────────────────
    private function monthlyInvoice(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('monthly_invoice as mi')
            ->select([
                'mi.mi_id', 'mi.mi_invoice_no', 'mi.mi_invoice_date',
                'mi.mi_invoice_type', 'mi.mi_amount', 'mi.mi_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(mi.mi_invoice_no,''), IFNULL(mi.mi_invoice_type,''), IFNULL(mi.mi_status,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $invType = trim((string) $request->input('invType', ''));
        if ($invType !== '') {
            $base->where('mi.mi_invoice_type', $invType);
        }

        $base->orderByDesc('mi.mi_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'monthly_invoice']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3347 — Integration > Loan Status Complete
    // Table: loan_master (status complete)
    // ─────────────────────────────────────────────────────────────────────────
    private function loanStatusComplete(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('loan_master as lm')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'lm.stf_staff_id')
            ->where('lm.lm_status', 'COMPLETE')
            ->select([
                'lm.lm_id', 'lm.stf_staff_id', 's.stf_staff_name',
                'lm.lm_loan_type', 'lm.lm_total_amount', 'lm.lm_status', 'lm.lm_complete_date',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(lm.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(lm.lm_loan_type,''))) LIKE ?",
                [$like]
            );
        }

        // Top filter
        $loanType = trim((string) $request->input('loanType', ''));
        if ($loanType !== '') {
            $base->where('lm.lm_loan_type', $loanType);
        }

        $base->orderByDesc('lm.lm_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'loan_status_complete']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3440 — Kew 8 > List of Staff (Kew.8)
    // Table: staff + staff_service
    // ─────────────────────────────────────────────────────────────────────────
    private function kew8ListOfStaff(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff as s')
            ->leftJoin('staff_service as ss', 'ss.stf_staff_id', '=', 's.stf_staff_id')
            ->select([
                's.stf_staff_id', 's.stf_staff_name', 's.stf_ic_no',
                'ss.sts_salary_grade', 'ss.sts_job_status', 'ss.sts_oun_code',
                's.stf_staff_status',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(s.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(s.stf_ic_no,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter
        $sfJobStatus = trim((string) $request->input('sf_0', ''));
        if ($sfJobStatus !== '') {
            $base->where('ss.sts_job_status', $sfJobStatus);
        }
        $sfStatus = trim((string) $request->input('sf_1', ''));
        if ($sfStatus !== '') {
            $base->where('s.stf_staff_status', $sfStatus);
        }

        $base->orderBy('s.stf_staff_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'kew8_list_staff']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3449 — Kew 8 > List of Kew 8 Form
    // Table: kew8_master
    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3449 — Kew 8 > List of Kew 8 Form
    // Table: payroll_kew8 (actual table; legacy name was kew8_master)
    // ─────────────────────────────────────────────────────────────────────────
    private function listOfKew8Forms(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payroll_kew8 as km')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'km.pkw_staff_id')
            ->select([
                'km.pkw_id as kew_eight_id',
                'km.pkw_id as application_no',
                'km.pkw_staff_id as kew_staff_id',
                's.stf_staff_name as kew_staff_ids',
                'km.pkw_adjustment_details as kew_eight_adjustment',
                'km.pkw_monthly_salary as kew_eight_salary',
                'km.pkw_note as kew_eight_note',
                'km.pkw_reference_no as kew_eight_permission',
                'km.pkw_status as kew_eight_status',
            ])
            ->selectRaw("DATE_FORMAT(km.pkw_date,'%d/%m/%Y') as kew_eight_date");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(km.pkw_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(km.pkw_reference_no,''))) LIKE ?",
                [$like]
            );
        }

        // Smart filter: status
        $sfStatus = trim((string) $request->input('sf_0', ''));
        if ($sfStatus !== '') {
            $base->where('km.pkw_status', $sfStatus);
        }

        $base->orderByDesc('km.pkw_id');
        $pack = $this->paginate($base, $page, $limit);

        return array_merge($pack, ['connector' => 'list_kew8_forms']);
    }
}
