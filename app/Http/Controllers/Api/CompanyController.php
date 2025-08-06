<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with(['users' => function ($q) {
            $q->where('is_active', true)->where('membership_status', 'active');
        }])->where('is_active', true);

        // Filtros
        if ($request->has('industry')) {
            $query->where('industry', $request->industry);
        }

        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        if ($request->has('membership_type')) {
            $query->where('membership_type', $request->membership_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%")
                  ->orWhereJsonContains('services', $search);
            });
        }

        $companies = $query->paginate(20);

        return response()->json($companies);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'description' => 'nullable|string|max:1000',
            'company_description' => 'nullable|string|max:2000',
            'industry' => 'required|string|max:255',
            'size' => ['required', Rule::in(['startup', 'small', 'medium', 'large', 'enterprise'])],
            'employees_count' => 'nullable|integer|min:1',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'website' => 'nullable|url',
            'email' => 'required|email|unique:companies',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'toll_free_phone' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:20',
            'tax_address' => 'nullable|string|max:500',
            'address' => 'nullable|array',
            'social_links' => 'nullable|array',
            'business_hours' => 'nullable|array',
            'services' => 'nullable|array',
            'membership_type' => ['nullable', Rule::in(['basic', 'premium', 'enterprise'])],
        ]);

        $company = Company::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'company_description' => $request->company_description,
            'industry' => $request->industry,
            'size' => $request->size,
            'employees_count' => $request->employees_count,
            'founded_year' => $request->founded_year,
            'website' => $request->website,
            'email' => $request->email,
            'phone' => $request->phone,
            'fax' => $request->fax,
            'toll_free_phone' => $request->toll_free_phone,
            'tax_id' => $request->tax_id,
            'tax_address' => $request->tax_address,
            'address' => $request->address,
            'social_links' => $request->social_links,
            'business_hours' => $request->business_hours,
            'services' => $request->services,
            'membership_type' => $request->membership_type ?? 'basic',
            'membership_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Empresa creada exitosamente',
            'company' => $company,
        ], 201);
    }

    public function show(Company $company)
    {
        $company->load([
            'users' => function ($query) {
                $query->where('is_active', true)
                      ->where('membership_status', 'active')
                      ->select('id', 'name', 'position', 'specialty', 'avatar', 'company_id');
            },
            'events' => function ($query) {
                $query->published()->upcoming()->take(5);
            }
        ]);

        return response()->json([
            'company' => $company,
            'contact_info' => $company->getContactInfo(),
            'business_info' => $company->getBusinessInfo(),
            'is_active_member' => $company->isActiveMember(),
            'membership_expiring' => $company->isMembershipExpiring(),
        ]);
    }

    public function update(Request $request, Company $company)
    {
        // Solo miembros de la empresa o administradores pueden actualizar
        $user = $request->user();
        if ($user->company_id !== $company->id && !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar esta empresa'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:companies,name,' . $company->id,
            'description' => 'nullable|string|max:1000',
            'company_description' => 'nullable|string|max:2000',
            'industry' => 'sometimes|string|max:255',
            'size' => ['sometimes', Rule::in(['startup', 'small', 'medium', 'large', 'enterprise'])],
            'employees_count' => 'nullable|integer|min:1',
            'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'website' => 'nullable|url',
            'email' => 'sometimes|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'toll_free_phone' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:20',
            'tax_address' => 'nullable|string|max:500',
            'address' => 'nullable|array',
            'social_links' => 'nullable|array',
            'business_hours' => 'nullable|array',
            'services' => 'nullable|array',
        ]);

        $updateData = $request->only([
            'name', 'description', 'company_description', 'industry', 'size',
            'employees_count', 'founded_year', 'website', 'email', 'phone',
            'fax', 'toll_free_phone', 'tax_id', 'tax_address', 'address',
            'social_links', 'business_hours', 'services'
        ]);

        if (isset($updateData['name'])) {
            $updateData['slug'] = Str::slug($updateData['name']);
        }

        $company->update($updateData);

        return response()->json([
            'message' => 'Empresa actualizada exitosamente',
            'company' => $company->fresh(),
        ]);
    }

    public function destroy(Company $company)
    {
        // Solo administradores pueden eliminar empresas
        if (!request()->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar empresas'
            ], 403);
        }

        $company->update(['is_active' => false]);

        return response()->json([
            'message' => 'Empresa desactivada exitosamente',
        ]);
    }

    public function uploadLogo(Request $request, Company $company)
    {
        $user = $request->user();
        if ($user->company_id !== $company->id && !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar el logo de esta empresa'
            ], 403);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($company->logo && file_exists(public_path('storage/' . $company->logo))) {
                unlink(public_path('storage/' . $company->logo));
            }

            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $company->update(['logo' => $logoPath]);

            return response()->json([
                'message' => 'Logo actualizado exitosamente',
                'logo_url' => asset('storage/' . $logoPath),
                'company' => $company->fresh(),
            ]);
        }

        return response()->json([
            'message' => 'No se pudo subir el logo'
        ], 400);
    }

    public function updateMembership(Request $request, Company $company)
    {
        // Solo administradores pueden actualizar membresías
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar membresías'
            ], 403);
        }

        $request->validate([
            'membership_type' => ['sometimes', Rule::in(['basic', 'premium', 'enterprise'])],
            'membership_status' => ['sometimes', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'membership_start_date' => 'nullable|date',
            'membership_end_date' => 'nullable|date|after:membership_start_date',
        ]);

        $company->update($request->only([
            'membership_type', 'membership_status', 
            'membership_start_date', 'membership_end_date'
        ]));

        return response()->json([
            'message' => 'Membresía actualizada exitosamente',
            'company' => $company->fresh(),
        ]);
    }

    public function getMembers(Company $company)
    {
        $members = $company->users()
            ->where('is_active', true)
            ->with(['postLikes', 'eventAttendances'])
            ->paginate(20);

        return response()->json($members);
    }

    public function getStats(Company $company)
    {
        $stats = [
            'total_members' => $company->users()->where('is_active', true)->count(),
            'active_members' => $company->users()->where('membership_status', 'active')->count(),
            'total_events' => $company->events()->count(),
            'upcoming_events' => $company->events()->upcoming()->count(),
            'membership_status' => $company->membership_status,
            'is_active_member' => $company->isActiveMember(),
            'membership_expiring' => $company->isMembershipExpiring(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }
}