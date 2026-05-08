<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Credit Control / Subsidiary Ledger / All (legacy SNA_API_CC_SUBSLEDGER_ALL).
 *
 * Line-level listing over {@see rep_aging_debtor} with subsidiary accounts only,
 * running balance matching classic ordering (transaction date, document no, DT before CR).
 */
class CreditControlSubsidiaryLedgerAllController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'pde_trans_date',
        'pde_document_no',
        'pde_payto_id',
        'acm_acct_code',
        'debit',
        'credit',
        'balance',
    ];

    public function options(): JsonResponse
    {
        $db = DB::connection('mysql_secondary');

        $customerTypes = $db->table('lookup_details')
            ->where('lma_code_name', 'CUSTOMER_TYPE')
            ->orderBy('lde_value')
            ->get(['lde_value', 'lde_description'])
            ->map(fn ($r) => [
                'code' => (string) $r->lde_value,
                'label' => trim((string) ($r->lde_value ?? '').' - '.(string) ($r->lde_description ?? '')),
            ])
            ->values()
            ->all();

        $fundTypes = $db->table('fund_type')
            ->whereNotNull('fty_fund_type')
            ->orderBy('fty_fund_type')
            ->get(['fty_fund_type AS code', 'fty_fund_desc AS description'])
            ->map(fn ($r) => [
                'code' => (string) $r->code,
                'label' => trim((string) ($r->code ?? '').' - '.(string) ($r->description ?? '')),
            ])
            ->values()
            ->all();

        $costCentres = $db->table('costcentre')
            ->where(function ($q) {
                $q->whereNull('ccr_status')->orWhere('ccr_status', '1');
            })
            ->orderBy('ccr_costcentre')
            ->get(['ccr_costcentre AS code', 'ccr_costcentre_desc AS description'])
            ->map(fn ($r) => [
                'code' => (string) $r->code,
                'label' => trim((string) ($r->code ?? '').' - '.(string) ($r->description ?? '')),
            ])
            ->values()
            ->all();

        $accountCodes = $db->table('account_main')
            ->where('acm_flag_subsidiary', 'Y')
            ->whereNotNull('acm_acct_code')
            ->orderBy('acm_acct_code')
            ->get(['acm_acct_code AS code', 'acm_acct_desc AS description'])
            ->map(fn ($r) => [
                'code' => (string) $r->code,
                'label' => trim((string) ($r->code ?? '').' - '.(string) ($r->description ?? '')),
            ])
            ->values()
            ->all();

        return $this->sendOk([
            'customerTypes' => $customerTypes,
            'fundTypes' => $fundTypes,
            'costCentres' => $costCentres,
            'accountCodes' => $accountCodes,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $customerType = trim((string) $request->input('customer_type', ''));
        if ($customerType === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'customer_type is required');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $sortBy = (string) $request->input('sort_by', 'pde_trans_date');
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'pde_trans_date';
        }
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $bindings = [];
        $whereParts = [
            "rad.pde_status = 'APPROVE'",
            "am.acm_flag_subsidiary = 'Y'",
            'rad.pde_payto_type = ?',
        ];
        $bindings[] = $customerType;

        foreach (
            [
                ['col' => 'rad.pde_payto_id', 'param' => 'customer_id'],
                ['col' => 'rad.acm_acct_code', 'param' => 'acm_acct_code'],
                ['col' => 'rad.ccr_costcentre', 'param' => 'ccr_costcentre'],
                ['col' => 'rad.fty_fund_type', 'param' => 'fty_fund_type'],
            ] as $map
        ) {
            $v = trim((string) $request->input($map['param'], ''));
            if ($v !== '') {
                $whereParts[] = $map['col'].' = ?';
                $bindings[] = $v;
            }
        }

        $stmtDate = $this->toMysqlDate($request->input('statement_date'));
        if ($stmtDate !== null) {
            $whereParts[] = 'DATE(rad.pde_trans_date) <= ?';
            $bindings[] = $stmtDate;
        }

        $ds = $this->toMysqlDate($request->input('date_start'));
        $de = $this->toMysqlDate($request->input('date_end'));
        if ($ds !== null && $de !== null) {
            $whereParts[] = 'DATE(rad.pde_trans_date) BETWEEN ? AND ?';
            $bindings[] = $ds;
            $bindings[] = $de;
        } elseif ($ds !== null) {
            $whereParts[] = 'DATE(rad.pde_trans_date) >= ?';
            $bindings[] = $ds;
        } elseif ($de !== null) {
            $whereParts[] = 'DATE(rad.pde_trans_date) <= ?';
            $bindings[] = $de;
        }

        $whereSql = implode(' AND ', $whereParts);

        $baseSelect = <<<SQL
            SELECT
                rad.pde_posting_detl_id,
                rad.pde_payto_type,
                COALESCE(ld.lde_description2, ld.lde_description, rad.pde_payto_type) AS customer_type_label,
                rad.pde_payto_id,
                rad.pde_payto_name,
                rad.pde_document_no,
                COALESCE(rad.docDescription, rad.pde_doc_description) AS pde_doc_description,
                rad.fty_fund_type,
                rad.at_activity_code,
                rad.oun_code,
                rad.ccr_costcentre,
                CASE
                    WHEN rad.cpa_project_no IS NULL THEN NULL
                    WHEN RIGHT(rad.cpa_project_no, 5) = '00000' THEN NULL
                    WHEN LENGTH(rad.cpa_project_no) > 16 THEN SUBSTRING(rad.cpa_project_no, 17, 10)
                    ELSE NULL
                END AS cpa_so_code,
                rad.acm_acct_code,
                am.acm_acct_desc,
                rad.pde_reference,
                rad.pde_reference1,
                rad.pde_reference2,
                rad.pde_trans_date,
                rad.pde_trans_type,
                IF(rad.pde_trans_type = 'DT', rad.pde_trans_amt, 0.00) AS debit,
                IF(rad.pde_trans_type = 'CR', rad.pde_trans_amt, 0.00) AS credit,
                rad.pde_trans_amt
            FROM rep_aging_debtor AS rad
            INNER JOIN account_main AS am ON rad.acm_acct_code = am.acm_acct_code
            LEFT JOIN lookup_details AS ld
                ON ld.lma_code_name = 'CUSTOMER_TYPE' AND ld.lde_value = rad.pde_payto_type
            WHERE {$whereSql}
        SQL;

        $searchClause = '';
        if ($q !== '') {
            $needle = mb_strtolower($q, 'UTF-8');
            $like = $this->likeWrap($needle);
            $searchClause = " AND LOWER(CONCAT_WS('__',
                IFNULL(customer_type_label,''),
                IFNULL(pde_payto_id,''),
                IFNULL(pde_payto_name,''),
                IFNULL(pde_document_no,''),
                IFNULL(pde_doc_description,''),
                IFNULL(fty_fund_type,''),
                IFNULL(at_activity_code,''),
                IFNULL(oun_code,''),
                IFNULL(ccr_costcentre,''),
                IFNULL(cpa_so_code,''),
                IFNULL(acm_acct_code,''),
                IFNULL(acm_acct_desc,''),
                IFNULL(pde_reference,''),
                IFNULL(pde_reference1,''),
                IFNULL(pde_reference2,'')
            )) LIKE ?";
            $bindings[] = $like;
        }

        $windowed = <<<SQL
            SELECT
                t.*,
                SUM(
                    CASE
                        WHEN t.pde_trans_type = 'DT' THEN t.pde_trans_amt
                        WHEN t.pde_trans_type = 'CR' THEN -t.pde_trans_amt
                        ELSE 0
                    END
                ) OVER (
                    ORDER BY
                        t.pde_trans_date,
                        t.pde_document_no,
                        FIELD(t.pde_trans_type, 'DT', 'CR'),
                        t.pde_posting_detl_id
                ) AS balance
            FROM ({$baseSelect}) AS t
            WHERE 1 = 1{$searchClause}
        SQL;

        $db = DB::connection('mysql_secondary');

        $countRow = $db->selectOne('SELECT COUNT(*) AS c FROM ('.$windowed.') AS cnt', $bindings);
        $total = (int) ($countRow->c ?? 0);

        $orderColumn = match ($sortBy) {
            'pde_document_no' => 'pde_document_no',
            'pde_payto_id' => 'pde_payto_id',
            'acm_acct_code' => 'acm_acct_code',
            'debit' => 'debit',
            'credit' => 'credit',
            'balance' => 'balance',
            default => 'pde_trans_date',
        };

        $offset = ($page - 1) * $limit;
        $dataSql = 'SELECT * FROM ('.$windowed.') AS w ORDER BY '
            .$orderColumn.' '.$sortDir
            .', w.pde_document_no ASC, FIELD(w.pde_trans_type, \'DT\', \'CR\') ASC, w.pde_posting_detl_id ASC'
            .' LIMIT '.(int) $limit.' OFFSET '.(int) $offset;

        $rows = $db->select($dataSql, $bindings);

        $data = [];
        foreach ($rows as $i => $r) {
            $data[] = [
                'index' => $offset + $i + 1,
                'pdePostingDetlId' => (int) $r->pde_posting_detl_id,
                'customerTypeLabel' => $r->customer_type_label !== null ? (string) $r->customer_type_label : null,
                'pdePaytoId' => $r->pde_payto_id !== null ? (string) $r->pde_payto_id : null,
                'pdePaytoName' => $r->pde_payto_name !== null ? (string) $r->pde_payto_name : null,
                'pdeDocumentNo' => $r->pde_document_no !== null ? trim((string) $r->pde_document_no) : null,
                'docDescription' => $r->pde_doc_description !== null ? trim((string) $r->pde_doc_description) : null,
                'fundType' => $r->fty_fund_type !== null ? (string) $r->fty_fund_type : null,
                'activityCode' => $r->at_activity_code !== null ? (string) $r->at_activity_code : null,
                'ounCode' => $r->oun_code !== null ? (string) $r->oun_code : null,
                'costCentre' => $r->ccr_costcentre !== null ? (string) $r->ccr_costcentre : null,
                'soCode' => $r->cpa_so_code !== null && (string) $r->cpa_so_code !== '' ? (string) $r->cpa_so_code : null,
                'acctCode' => $r->acm_acct_code !== null ? (string) $r->acm_acct_code : null,
                'acctDesc' => $r->acm_acct_desc !== null ? (string) $r->acm_acct_desc : null,
                'reference1' => $r->pde_reference !== null ? (string) $r->pde_reference : null,
                'reference2' => $r->pde_reference1 !== null ? (string) $r->pde_reference1 : null,
                'reference3' => $r->pde_reference2 !== null ? (string) $r->pde_reference2 : null,
                'transDate' => $this->fmtDmY($r->pde_trans_date ?? null),
                'debit' => $r->debit !== null ? (float) $r->debit : 0.0,
                'credit' => $r->credit !== null ? (float) $r->credit : 0.0,
                'balance' => $r->balance !== null ? (float) $r->balance : 0.0,
            ];
        }

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function toMysqlDate(mixed $raw): ?string
    {
        $s = trim((string) ($raw ?? ''));
        if ($s === '') {
            return null;
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $s);

                return $dt->format('Y-m-d');
            } catch (\Throwable) {
            }
        }
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function fmtDmY(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        try {
            return Carbon::parse($v)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $v;
        }
    }

    private function likeWrap(string $needleLower): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needleLower);

        return '%'.$escaped.'%';
    }
}
