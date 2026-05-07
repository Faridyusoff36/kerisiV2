<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AP — Direct Voucher form page (menuId 3461 / pageId 2877).
 *
 * Provides autosuggest endpoints for every dropdown field on the
 * Voucher Details form. No cascade via petty_cash_main — AP direct
 * vouchers are not restricted to configured petty-cash combinations.
 */
class ApDirectVoucherController extends Controller
{
    use ApiResponse;

    private function conn()
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $term): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
    }

    // ─────────────────────────────────────────────────────────────────────
    // Payee (staff table)
    // ─────────────────────────────────────────────────────────────────────

    public function suggestPayee(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $limit = max(1, min(50, (int) $request->input('limit', 20)));

        $rows = $this->conn()
            ->table('staff')
            ->select(['stf_staff_id', 'stf_staff_name'])
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(stf_staff_id,''), IFNULL(stf_staff_name,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('stf_staff_id')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->stf_staff_id ?? ''),
                'desc' => (string) ($r->stf_staff_name ?? ''),
                'text' => trim(sprintf('%s - %s', $r->stf_staff_id ?? '', $r->stf_staff_name ?? ''), ' -'),
            ])->values()->all()
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Fund Type
    // ─────────────────────────────────────────────────────────────────────

    public function suggestFundType(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $limit = max(1, min(100, (int) $request->input('limit', 50)));

        $rows = $this->conn()
            ->table('fund_type')
            ->select(['fty_fund_type', 'fty_fund_desc'])
            ->where(fn ($b) => $b->whereNull('fty_status')->orWhere('fty_status', 1))
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(fty_fund_type,''), IFNULL(fty_fund_desc,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('fty_fund_type')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->fty_fund_type ?? ''),
                'desc' => (string) ($r->fty_fund_desc ?? ''),
                'text' => trim(sprintf('%s - %s', $r->fty_fund_type ?? '', $r->fty_fund_desc ?? ''), ' -'),
            ])->values()->all()
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Activity Code (cascades from Fund Type when supplied)
    // ─────────────────────────────────────────────────────────────────────

    public function suggestActivityCode(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $limit = max(1, min(100, (int) $request->input('limit', 50)));

        // No cascade — AP direct vouchers are not restricted to
        // petty_cash_main combinations, so return the full active list.
        $rows = $this->conn()
            ->table('activity_type')
            ->select(['at_activity_code', 'at_activity_description_bm'])
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(at_activity_code,''), IFNULL(at_activity_description_bm,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('at_activity_code')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->at_activity_code ?? ''),
                'desc' => (string) ($r->at_activity_description_bm ?? ''),
                'text' => trim(sprintf('%s - %s', $r->at_activity_code ?? '', $r->at_activity_description_bm ?? ''), ' -'),
            ])->values()->all()
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // PTJ / OU
    // ─────────────────────────────────────────────────────────────────────

    public function suggestPtj(Request $request): JsonResponse
    {
        $q        = trim((string) $request->input('q', ''));
        $limit    = max(1, min(100, (int) $request->input('limit', 50)));

        $rows = $this->conn()
            ->table('organization_unit')
            ->select(['oun_code', 'oun_desc'])
            ->where(fn ($b) => $b->whereNull('oun_status')->orWhere('oun_status', 1))
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(oun_code,''), IFNULL(oun_desc,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('oun_code')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->oun_code ?? ''),
                'desc' => (string) ($r->oun_desc ?? ''),
                'text' => trim(sprintf('%s - %s', $r->oun_code ?? '', $r->oun_desc ?? ''), ' -'),
            ])->values()->all()
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Cost Centre
    // ─────────────────────────────────────────────────────────────────────

    public function suggestCostCenter(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $limit = max(1, min(100, (int) $request->input('limit', 50)));

        $rows = $this->conn()
            ->table('costcentre')
            ->select(['ccr_costcentre', 'ccr_costcentre_desc'])
            ->where(fn ($b) => $b->whereNull('ccr_status')->orWhere('ccr_status', 1))
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(ccr_costcentre,''), IFNULL(ccr_costcentre_desc,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('ccr_costcentre')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->ccr_costcentre ?? ''),
                'desc' => (string) ($r->ccr_costcentre_desc ?? ''),
                'text' => trim(sprintf('%s - %s', $r->ccr_costcentre ?? '', $r->ccr_costcentre_desc ?? ''), ' -'),
            ])->values()->all()
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Account Code (used for both Debit and Credit account code fields)
    // Filters to BELANJA activity accounts at the deepest level, matching
    // the PettyCash pattern. Pass ?fund_type= to narrow by fund.
    // ─────────────────────────────────────────────────────────────────────

    public function suggestAccountCode(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $fund  = trim((string) $request->input('fund_type', ''));
        $limit = max(1, min(100, (int) $request->input('limit', 50)));

        $conn     = $this->conn();
        $maxLevel = $conn->table('account_main')->max('acm_acct_level');

        $rows = $conn
            ->table('account_main as am')
            ->select(['am.acm_acct_code', 'am.acm_acct_desc'])
            ->where('am.acm_acct_activity', 'BELANJA')
            ->when($maxLevel !== null, fn ($q2) => $q2->where('am.acm_acct_level', $maxLevel))
            ->when($fund !== '', function ($qry) use ($fund) {
                $qry->whereExists(function ($sub) use ($fund) {
                    $sub->select(DB::raw('1'))
                        ->from('account_main_fund as amf')
                        ->whereColumn('amf.acm_acct_code', 'am.acm_acct_code')
                        ->where('amf.fty_fund_type', $fund);
                });
            })
            ->when($q !== '', function ($qry) use ($q) {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $qry->whereRaw(
                    "LOWER(CONCAT_WS(' - ', IFNULL(am.acm_acct_code,''), IFNULL(am.acm_acct_desc,''))) LIKE ?",
                    [$like]
                );
            })
            ->orderBy('am.acm_acct_code')
            ->limit($limit)
            ->get();

        return $this->sendOk(
            $rows->map(fn ($r) => [
                'id'   => (string) ($r->acm_acct_code ?? ''),
                'desc' => (string) ($r->acm_acct_desc ?? ''),
                'text' => trim(sprintf('%s - %s', $r->acm_acct_code ?? '', $r->acm_acct_desc ?? ''), ' -'),
            ])->values()->all()
        );
    }
}
