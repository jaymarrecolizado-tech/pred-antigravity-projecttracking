<?php

namespace App\Http\Controllers\Registry;

use App\Domain\Access\GrantResolver;
use App\Domain\Geo\PsgcArea;
use App\Domain\Registry\Institution;
use App\Domain\Registry\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'project.view')) {
            abort(403, 'Unauthorized.');
        }

        $query = Institution::query()
            ->with([
                'area.parent',
                'primaryLocation',
                'projectSites.project',
            ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        if ($kind = $request->input('kind')) {
            $query->where('kind', $kind);
        }

        $institutions = $query->orderBy('name')->paginate(25)->withQueryString();

        $stats = [
            'total_institutions' => Institution::count(),
            'lgu_count' => Institution::where('kind', 'lgu')->count(),
            'school_count' => Institution::where('kind', 'deped_school')->count(),
            'suc_count' => Institution::where('kind', 'suc')->count(),
            'hospital_count' => Institution::where('kind', 'hospital_clinic')->count(),
        ];

        $provinces = PsgcArea::where('level', 'prov')->orderBy('name')->get(['code', 'name']);
        $locations = Location::orderBy('name')->limit(100)->get(['id', 'name']);

        return Inertia::render('Institutions/Index', [
            'institutions' => $institutions,
            'stats' => $stats,
            'provinces' => $provinces,
            'locations' => $locations,
            'filters' => $request->only(['search', 'kind']),
            'canManage' => $this->grantResolver->allows($user, 'site.enroll'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, 'site.enroll')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'kind' => ['required', 'in:lgu,deped_school,suc,hospital_clinic,nga_regional_office,other'],
            'name' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:64'],
            'psgc_code' => ['required', 'string', 'min:9', 'max:10'],
            'primary_location_id' => ['nullable', 'exists:locations,id'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
        ]);

        Institution::create($validated);

        return back()->with('success', "Institution '{$validated['name']}' registered successfully.");
    }
}
