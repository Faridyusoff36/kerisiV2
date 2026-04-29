<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Purchasing / Purchase Requisition / New Purchase Requisition (MENUID 1771).
 *
 * Backed by `requisition_master` on `mysql_secondary` (legacy FIMS).
 */
class PurchasingPurchaseRequisitionService
{
    private const CONN = 'mysql_secondary';

    private function conn(): Connection
    {
        return DB::connection(self::CONN);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsStaff(): array
    {
        $rows = $this->conn()
            ->table('staff')
            ->select(['stf_staff_id', 'stf_staff_name'])
            ->whereNotNull('stf_staff_id')
            ->where('stf_staff_id', '!=', '')
            ->orderBy('stf_staff_name')
            ->limit(2000)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $id = trim((string) ($r->stf_staff_id ?? ''));
            if ($id === '') {
                continue;
            }
            $name = trim((string) ($r->stf_staff_name ?? ''));
            $label = $name !== '' ? $id.' — '.$name : $id;

            $out[] = ['value' => $id, 'label' => $label];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsOrganizationUnits(): array
    {
        $rows = $this->conn()
            ->table('organization_unit')
            ->select(['oun_code', 'oun_desc'])
            ->orderBy('oun_code')
            ->limit(2000)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $code = trim((string) ($r->oun_code ?? ''));
            if ($code === '') {
                continue;
            }
            $d = trim((string) ($r->oun_desc ?? ''));
            $out[] = ['value' => $code, 'label' => $d !== '' ? $code.' — '.$d : $code];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsCostCentres(?string $ounCode): array
    {
        $q = $this->conn()
            ->table('costcentre')
            ->select(['ccr_costcentre', 'ccr_costcentre_desc', 'oun_code'])
            ->whereNotNull('ccr_costcentre')
            ->where('ccr_costcentre', '!=', '')
            ->orderBy('oun_code')->orderBy('ccr_costcentre');

        $oc = trim((string) ($ounCode ?? ''));
        if ($oc !== '') {
            $q->where(function ($w) use ($oc) {
                $w->where('oun_code', $oc)->orWhereNull('oun_code');
            });
        }

        $out = [];
        foreach ($q->limit(5000)->get() as $r) {
            $cc = trim((string) ($r->ccr_costcentre ?? ''));
            if ($cc === '') {
                continue;
            }
            $d = trim((string) ($r->ccr_costcentre_desc ?? ''));
            $ou = trim((string) ($r->oun_code ?? ''));
            $label = ($ou !== '' ? $ou.' / ' : '').$cc;
            if ($d !== '') {
                $label .= ' — '.$d;
            }

            $out[] = ['value' => $cc, 'label' => $label];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsFundTypes(): array
    {
        $rows = $this->conn()
            ->table('fund_type')
            ->select(['fty_fund_type', 'fty_fund_desc'])
            ->whereNotNull('fty_fund_type')
            ->orderBy('fty_fund_type')
            ->limit(500)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $code = trim((string) ($r->fty_fund_type ?? ''));
            if ($code === '') {
                continue;
            }
            $d = trim((string) ($r->fty_fund_desc ?? ''));
            $out[] = ['value' => $code, 'label' => $d !== '' ? $code.' — '.$d : $code];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsActivityCodes(): array
    {
        $rows = $this->conn()
            ->table('activity_type')
            ->select(['at_activity_code', 'at_activity_description_bm', 'at_activity_description_en'])
            ->whereNotNull('at_activity_code')
            ->orderBy('at_activity_code')
            ->limit(5000)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $code = trim((string) ($r->at_activity_code ?? ''));
            if ($code === '') {
                continue;
            }
            $d = trim((string) ($r->at_activity_description_bm ?? ''))
                ?: trim((string) ($r->at_activity_description_en ?? ''));
            $out[] = ['value' => $code, 'label' => $d !== '' ? $code.' — '.$d : $code];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsVendors(): array
    {
        $rows = $this->conn()
            ->table('vend_customer_supplier')
            ->select(['vcs_vendor_code', 'vcs_vendor_name'])
            ->whereNotNull('vcs_vendor_code')
            ->orderBy('vcs_vendor_code')
            ->limit(8000)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $c = trim((string) ($r->vcs_vendor_code ?? ''));
            if ($c === '') {
                continue;
            }
            $n = trim((string) ($r->vcs_vendor_name ?? ''));
            $out[] = ['value' => $c, 'label' => $n !== '' ? $c.' — '.$n : $c];
        }

        return $out;
    }

    /**
     * Currency codes enabled in `currency_master`.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsCurrencyCodes(): array
    {
        $rows = $this->conn()
            ->table('currency_master')
            ->select(['cym_currency_code', 'cym_currency_desc'])
            ->whereNotNull('cym_currency_code')
            ->where('cym_currency_code', '!=', '')
            ->orderBy('cym_currency_code')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $code = trim((string) ($r->cym_currency_code ?? ''));
            if ($code === '') {
                continue;
            }
            $d = trim((string) ($r->cym_currency_desc ?? ''));
            $out[] = ['value' => $code, 'label' => $d !== '' ? $code.' — '.$d : $code];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsRateTypes(): array
    {
        $rows = $this->conn()
            ->table('currency_exchange_type')
            ->select(['cet_exchange_type_code', 'cet_exchange_type_desc'])
            ->whereRaw("IFNULL(cet_status,'') IN ('1','Y','A','')")
            ->orderBy('cet_exchange_type_code')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $c = trim((string) ($r->cet_exchange_type_code ?? ''));
            if ($c === '') {
                continue;
            }
            $d = trim((string) ($r->cet_exchange_type_desc ?? ''));
            $out[] = ['value' => $c, 'label' => $d !== '' ? $c.' — '.$d : $c];
        }

        return $out;
    }

    /**
     * Requisition Type — store `lde_value` (fits `rqm_jenis_tender` varchar(15)).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsRequisitionTypes(): array
    {
        return $this->lookupValueOptions('TENDERTYPE');
    }

    /**
     * Purchase Method — `rqm_tender_type` varchar(30); store `lde_value` when present.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsPurchaseMethods(): array
    {
        return $this->lookupValueOptions('PURCHASEMETHOD');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function lookupValueOptions(string $lmaCodeName): array
    {
        $rows = $this->conn()
            ->table('lookup_details')
            ->select(['lde_value', 'lde_description'])
            ->where('lma_code_name', $lmaCodeName)
            ->whereRaw("IFNULL(lde_status,'') NOT IN ('0','N','INACTIVE')")
            ->orderBy('lde_sorting')
            ->orderBy('lde_value')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $val = trim((string) ($r->lde_value ?? ''));
            $desc = trim((string) ($r->lde_description ?? ''));
            if ($val === '' && $desc === '') {
                continue;
            }
            $value = $val !== '' ? $val : $desc;
            $label = $val !== '' && $desc !== '' ? $val.' — '.$desc : ($desc !== '' ? $desc : $value);
            $out[] = ['value' => $value, 'label' => $label];
        }

        return $out;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dropdownOptionsAgreementNumbers(): array
    {
        $rows = $this->conn()
            ->table('aggrement_po')
            ->select(['agg_no', 'agg_id'])
            ->whereNotNull('agg_no')
            ->where('agg_no', '!=', '')
            ->orderByDesc('agg_id')
            ->limit(500)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $no = trim((string) ($r->agg_no ?? ''));
            if ($no === '') {
                continue;
            }
            $out[] = ['value' => $no, 'label' => $no];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptionsPayload(): array
    {
        return [
            'requestBy' => $this->dropdownOptionsStaff(),
            'contactPerson' => $this->dropdownOptionsStaff(),
            'ptj' => $this->dropdownOptionsOrganizationUnits(),
            'costCentres' => $this->dropdownOptionsCostCentres(null),
            'fund' => $this->dropdownOptionsFundTypes(),
            'activity' => $this->dropdownOptionsActivityCodes(),
            'vendor' => $this->dropdownOptionsVendors(),
            'agreementYesNo' => [
                ['value' => 'Y', 'label' => 'Yes'],
                ['value' => 'N', 'label' => 'No'],
            ],
            'agreementNo' => $this->dropdownOptionsAgreementNumbers(),
            'foreignCurrencyCode' => $this->dropdownOptionsCurrencyCodes(),
            'rateType' => $this->dropdownOptionsRateTypes(),
            'requisitionType' => $this->dropdownOptionsRequisitionTypes(),
            'purchaseMethod' => $this->dropdownOptionsPurchaseMethods(),
        ];
    }

    /**
     * Filter cost centres when PTJ (`oun_code`) changes.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function costCentresForPtj(string $ounCode): array
    {
        return $this->dropdownOptionsCostCentres($ounCode);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findMasterForApi(int $rqmRequisitionId): ?array
    {
        $row = $this->conn()->table('requisition_master')
            ->where('rqm_requisition_id', $rqmRequisitionId)
            ->first();

        if (! $row) {
            return null;
        }

        return $this->mapRowToCamel((array) $row);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapRowToCamel(array $row): array
    {
        $reqDate = $this->toDateString($row['rqm_request_date'] ?? null);
        $rateDate = $this->toDateString($row['rqm_rate_date'] ?? null);
        $docRecv = $this->toDateString($row['rqm_doc_receive_date'] ?? null);

        return [
            'rqmRequisitionId' => (int) ($row['rqm_requisition_id'] ?? 0),
            'rqmRequisitionNo' => $row['rqm_requisition_no'] ?? null,
            'orgCode' => $row['org_code'] ?? null,
            'ounCode' => $row['oun_code'] ?? null,
            'ftyFundType' => $row['fty_fund_type'] ?? null,
            'ccrCostcentre' => $row['ccr_costcentre'] ?? null,
            'atActivityCode' => $row['at_activity_code'] ?? null,
            'rqmRequisitionTitle' => $row['rqm_requisition_title'] ?? null,
            'rqmTenderScope' => $row['rqm_tender_scope'] ?? null,
            'rqmTenderType' => $row['rqm_tender_type'] ?? null,
            'rqmJenisTender' => $row['rqm_jenis_tender'] ?? null,
            'rqmConversionRate' => $row['rqm_conversion_rate'] ?? null,
            'rqmCurrencyUnit' => $row['rqm_currency_unit'] ?? null,
            'rqmCurrencyCode' => $row['rqm_currency_code'] ?? null,
            'rqmRateType' => $row['rqm_rate_type'] ?? null,
            'rqmRateDate' => $rateDate,
            'rqmEntAmt' => $row['rqm_ent_amt'] ?? null,
            'rqmAmount' => $row['rqm_amount'] ?? null,
            'rqmStatus' => $row['rqm_status'] ?? null,
            'rqmRequestBy' => $row['rqm_request_by'] ?? null,
            'rqmRequestDate' => $reqDate,
            'rqmQuotationReceive' => $row['rqm_quotation_receive'] ?? null,
            'rqmRefNo' => $row['rqm_ref_no'] ?? null,
            'rqmDocReceiveDate' => $docRecv,
            'rqmContactPerson' => $row['rqm_contact_person'] ?? null,
            /** Vendor code on `requisition_master.rqm_payee_code` (legacy naming). */
            'rqmPayeeCode' => $row['rqm_payee_code'] ?? null,
            'rqmIsagreementExist' => $row['rqm_isagreement_exist'] ?? null,
            'rqmAggNo' => $row['rqm_agg_no'] ?? null,
            'pprRequisitionId' => $row['ppr_requisition_id'] ?? null,
        ];
    }

    private function toDateString(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Persist new header (minimal legacy-compatible insert).
     *
     * @param  array<string, mixed>  $v  validated snake_case
     */
    public function create(array $v): int
    {
        $conn = $this->conn();

        return (int) $conn->transaction(function () use ($conn, $v) {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $user = 'SPA';

            $nextId = (int) $conn->table('requisition_master')->max('rqm_requisition_id');
            $nextId = $nextId > 0 ? $nextId + 1 : 1;

            $insert = [
                'rqm_requisition_id' => $nextId,
                'org_code' => $v['org_code'] ?? null,
                'oun_code' => $v['oun_code'],
                'fty_fund_type' => $v['fty_fund_type'],
                'ccr_costcentre' => $v['ccr_costcentre'],
                'at_activity_code' => $v['at_activity_code'],
                'rqm_requisition_title' => $v['rqm_requisition_title'],
                'rqm_tender_scope' => $v['rqm_tender_scope'],
                'rqm_tender_type' => $v['rqm_tender_type'] ?? null,
                'rqm_jenis_tender' => $v['rqm_jenis_tender'] ?? null,
                'rqm_conversion_rate' => $v['rqm_conversion_rate'] ?? null,
                'rqm_currency_unit' => $v['rqm_currency_unit'] ?? null,
                'rqm_currency_code' => $v['rqm_currency_code'] ?? null,
                'rqm_rate_type' => $v['rqm_rate_type'] ?? null,
                'rqm_rate_date' => $this->parseDateOrNull($v['rqm_rate_date'] ?? null),
                'rqm_ent_amt' => $v['rqm_ent_amt'] ?? null,
                'rqm_amount' => $v['rqm_amount'] ?? $v['rqm_ent_amt'] ?? null,
                'rqm_status' => $v['rqm_status'] ?? 'DRAFT',
                'rqm_request_by' => $v['rqm_request_by'],
                'rqm_request_date' => $this->parseDateOrNull($v['rqm_request_date'] ?? null) ?? $now,
                'rqm_quotation_receive' => $v['rqm_quotation_receive'] ?? null,
                'rqm_ref_no' => $v['rqm_ref_no'] ?? null,
                'rqm_doc_receive_date' => $this->parseDateOrNull($v['rqm_doc_receive_date'] ?? null),
                'rqm_contact_person' => $v['rqm_contact_person'],
                'rqm_isagreement_exist' => $v['rqm_isagreement_exist'] ?? 'N',
                'rqm_agg_no' => ($v['rqm_isagreement_exist'] ?? 'N') === 'Y' ? ($v['rqm_agg_no'] ?? null) : null,
                'rqm_payee_code' => $v['rqm_payee_code'] ?? null,
                'createddate' => $now,
                'createdby' => $user,
                'updateddate' => $now,
                'updatedby' => $user,
            ];

            $conn->table('requisition_master')->insert($insert);

            $yy = Carbon::now()->format('y');
            $no = sprintf('PRA%06d/%s', $nextId, $yy);
            $conn->table('requisition_master')
                ->where('rqm_requisition_id', $nextId)
                ->update(['rqm_requisition_no' => $no, 'updateddate' => $now]);

            return $nextId;
        });
    }

    /**
     * @param  array<string, mixed>  $v
     */
    public function update(int $id, array $v): void
    {
        $conn = $this->conn();
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $update = [
            'org_code' => $v['org_code'] ?? null,
            'oun_code' => $v['oun_code'],
            'fty_fund_type' => $v['fty_fund_type'],
            'ccr_costcentre' => $v['ccr_costcentre'],
            'at_activity_code' => $v['at_activity_code'],
            'rqm_requisition_title' => $v['rqm_requisition_title'],
            'rqm_tender_scope' => $v['rqm_tender_scope'],
            'rqm_tender_type' => $v['rqm_tender_type'] ?? null,
            'rqm_jenis_tender' => $v['rqm_jenis_tender'] ?? null,
            'rqm_conversion_rate' => $v['rqm_conversion_rate'] ?? null,
            'rqm_currency_unit' => $v['rqm_currency_unit'] ?? null,
            'rqm_currency_code' => $v['rqm_currency_code'] ?? null,
            'rqm_rate_type' => $v['rqm_rate_type'] ?? null,
            'rqm_rate_date' => $this->parseDateOrNull($v['rqm_rate_date'] ?? null),
            'rqm_ent_amt' => $v['rqm_ent_amt'] ?? null,
            'rqm_amount' => $v['rqm_amount'] ?? $v['rqm_ent_amt'] ?? null,
            'rqm_request_by' => $v['rqm_request_by'],
            'rqm_request_date' => $this->parseDateOrNull($v['rqm_request_date'] ?? null) ?? $now,
            'rqm_quotation_receive' => $v['rqm_quotation_receive'] ?? null,
            'rqm_ref_no' => $v['rqm_ref_no'] ?? null,
            'rqm_doc_receive_date' => $this->parseDateOrNull($v['rqm_doc_receive_date'] ?? null),
            'rqm_contact_person' => $v['rqm_contact_person'],
            'rqm_isagreement_exist' => $v['rqm_isagreement_exist'] ?? 'N',
            'rqm_agg_no' => ($v['rqm_isagreement_exist'] ?? 'N') === 'Y' ? ($v['rqm_agg_no'] ?? null) : null,
            'rqm_payee_code' => $v['rqm_payee_code'] ?? null,
            'updateddate' => $now,
            'updatedby' => 'SPA',
        ];

        $conn->table('requisition_master')->where('rqm_requisition_id', $id)->update($update);
    }

    private function parseDateOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
