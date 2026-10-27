<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLocationController extends Controller
{
    /**
     * Menampilkan daftar lokasi kantor.
     */
    public function index(): View
    {
        $locations = Location::query()
            ->withCount([
                'users as assigned_users_count' => function ($query) {
                    $query->where('role', 'magang');
                },
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalLocations = Location::count();

        $activeLocations = Location::where('is_active', true)->count();

        $assignedStudents = User::where('role', 'magang')
            ->whereNotNull('location_id')
            ->count();

        return view('admin.locations', compact(
            'locations',
            'totalLocations',
            'activeLocations',
            'assignedStudents'
        ));
    }

    /**
     * Menambahkan lokasi kantor.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'address' => $request->filled('address')
                ? trim((string) $request->input('address'))
                : null,
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                'unique:locations,name',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'radius_meters' => [
                'required',
                'integer',
                'min:10',
                'max:5000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        Location::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.locations')
            ->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    /**
     * Memperbarui lokasi kantor.
     */
    public function update(
        Request $request,
        Location $location
    ): RedirectResponse {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'address' => $request->filled('address')
                ? trim((string) $request->input('address'))
                : null,
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('locations', 'name')
                    ->ignore($location->id),
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'radius_meters' => [
                'required',
                'integer',
                'min:10',
                'max:5000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $location->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meters' => $validated['radius_meters'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.locations')
            ->with('success', 'Data lokasi kantor berhasil diperbarui.');
    }

    /**
     * Menghapus lokasi yang belum digunakan.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $hasAssignedUsers = $location->users()->exists();

        $hasAttendanceHistory = Attendance::query()
            ->where(function ($query) use ($location) {
                $query->where('location_id', $location->id)
                    ->orWhere(
                        'clock_out_location_id',
                        $location->id
                    );
            })
            ->exists();

        if ($hasAssignedUsers || $hasAttendanceHistory) {
            return redirect()
                ->route('admin.locations')
                ->with(
                    'error',
                    'Lokasi masih digunakan. Nonaktifkan lokasi jika sudah tidak diperlukan.'
                );
        }

        $location->delete();

        return redirect()
            ->route('admin.locations')
            ->with('success', 'Lokasi kantor berhasil dihapus.');
    }
}
