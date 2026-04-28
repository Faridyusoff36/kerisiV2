<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Sponsor > Profile (PAGEID 845 / MENUID 1025).
 *
 * Source: FIMS BL `V2_SFSP_SPONSOR_API`. Read-only datatable + smart
 * filter on the `sponsor` master in DB_SECOND_DATABASE.
 *
 * Smart-filter keys preserve the legacy contract:
 *   - sponsor      — LIKE %...% on CONCAT_WS(' - ', spn_sponsor_code, spn_sponsor_name)
 *   - country      — LIKE %...% case-insensitive on
 *                    spn_extended_field->>'$.spn_country_desc'
 *   - email        — LIKE %...% on IFNULL(spn_email, '')
 *   - spn_status   — LIKE %...% on spn_status_cd
 *
 * Global search (`q`) mirrors the legacy
 *   LOWER(CONCAT_WS('__', code, name, contact_person, contact_person2,
 *   contact_person3, status_desc)) LIKE LOWER('%?%')
 * surface, kept lower-case for case-insensitive matching.
 *
 * The legacy COMPONENT_JS exposes Edit / View / Delete / Assign-Student
 * actions deep-linking to legacy menuID=1068 (Sponsor form) and 1478
 * (Sponsor → Assign Student). Neither is migrated yet — the frontend
 * renders all four Action buttons as disabled with explanatory tooltips
 * until those editors are ported.
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) we
 * surface PDF / CSV / Excel exports for the rendered page set.
 */
class SponsorProfileController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'sponsor',
        'email',
        'spn_status',
        'status_of_invoice',
    ];

    public function options(): JsonResponse
    {
        // The legacy form offers ACTIVE / INACTIVE only (UNION literal in
        // Form_Item_lookup_query). Surface that two-row choice — using
        // the actual `spn_status_cd` codes so the filter parameter
        // matches what the column stores.
        return $this->sendOk([
            'status' => [
                ['id' => '1', 'label' => 'ACTIVE'],
                ['id' => '0', 'label' => 'INACTIVE'],
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'sponsor');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'sponsor';
        }

        $sponsor = trim((string) $request->input('sponsor', ''));
        $country = trim((string) $request->input('country', ''));
        $email = trim((string) $request->input('email', ''));
        $spnStatus = trim((string) $request->input('spn_status', ''));

        $base = $this->baseQuery();

        if ($q !== '') {
            $needle = $this->likeEscape(strtolower($q));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__', "
                ."IFNULL(s.spn_sponsor_code, ''), "
                ."IFNULL(s.spn_sponsor_name, ''), "
                ."IFNULL(s.spn_contact_person, ''), "
                ."IFNULL(s.spn_contact_person2, ''), "
                ."IFNULL(s.spn_contact_person3, ''), "
                ."IFNULL(s.spn_extended_field->>'\$.spn_status_desc', '')"
                .')) LIKE ?',
                [$needle]
            );
        }
        if ($email !== '') {
            $base->whereRaw("IFNULL(s.spn_email, '') LIKE ?", [$this->likeEscape($email)]);
        }
        if ($sponsor !== '') {
            $base->whereRaw(
                "CONCAT_WS(' - ', IFNULL(s.spn_sponsor_code, ''), IFNULL(s.spn_sponsor_name, '')) LIKE ?",
                [$this->likeEscape($sponsor)]
            );
        }
        if ($country !== '') {
            $base->whereRaw(
                "LOWER(IFNULL(s.spn_extended_field->>'\$.spn_country_desc', '')) LIKE ?",
                [$this->likeEscape(strtolower($country))]
            );
        }
        if ($spnStatus !== '') {
            $base->whereRaw('s.spn_status_cd LIKE ?', [$this->likeEscape($spnStatus)]);
        }

        $total = (clone $base)->count();

        $orderColumn = match ($sortBy) {
            'sponsor' => DB::raw("CONCAT_WS(' - ', IFNULL(s.spn_sponsor_code, ''), IFNULL(s.spn_sponsor_name, ''))"),
            'email' => 's.spn_email',
            'spn_status' => DB::raw("s.spn_extended_field->>'\$.spn_status_desc'"),
            'status_of_invoice' => DB::raw("s.spn_extended_field->>'\$.spn_status_invoice_desc'"),
            default => DB::raw("CONCAT_WS(' - ', IFNULL(s.spn_sponsor_code, ''), IFNULL(s.spn_sponsor_name, ''))"),
        };

        $rows = (clone $base)
            ->select([
                's.spn_sponsor_id',
                DB::raw("CONCAT_WS(' - ', IFNULL(s.spn_sponsor_code, ''), IFNULL(s.spn_sponsor_name, '')) AS sponsor"),
                's.spn_contact_person',
                's.spn_contact_no',
                's.spn_contact_person2',
                's.spn_contact_no2',
                's.spn_contact_person3',
                's.spn_contact_no3',
                's.spn_email AS email',
                DB::raw("s.spn_extended_field->>'\$.spn_status_desc' AS spon_status"),
                DB::raw("s.spn_extended_field->>'\$.spn_status_invoice_desc' AS status_of_invoice"),
                DB::raw('(SELECT COUNT(*) FROM stud_sponsor x WHERE x.spn_sponsor_code = s.spn_sponsor_name) AS has_child'),
            ])
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('s.spn_sponsor_id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'spnSponsorId' => $r->spn_sponsor_id,
            'sponsor' => $r->sponsor,
            'spnContactPerson' => $r->spn_contact_person,
            'spnContactNo' => $r->spn_contact_no,
            'spnContactPerson2' => $r->spn_contact_person2,
            'spnContactNo2' => $r->spn_contact_no2,
            'spnContactPerson3' => $r->spn_contact_person3,
            'spnContactNo3' => $r->spn_contact_no3,
            'email' => $r->email,
            'sponStatus' => $r->spon_status,
            'statusOfInvoice' => $r->status_of_invoice,
            'hasChild' => (int) $r->has_child > 0,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /**
     * Replicates the legacy `FROM ".DB2.".sponsor s`.
     */
    private function baseQuery(): Builder
    {
        return DB::connection('mysql_secondary')->table('sponsor as s');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
