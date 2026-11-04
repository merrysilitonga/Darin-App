<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    /**
     * Menampilkan daftar hari libur.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tahun' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $tahun = (int) (
            $validated['tahun'] ?? now('Asia/Jakarta')->year
        );

        $search = trim($validated['q'] ?? '');

        $query = Holiday::query()
            ->whereYear('date', $tahun);

        // Filter berdasarkan nama atau keterangan hari libur.
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Statistik berdasarkan tahun yang dipilih.
        $totalHolidays = (clone $query)->count();

        $activeHolidays = (clone $query)
            ->where('is_active', true)
            ->count();

        $inactiveHolidays = (clone $query)
            ->where('is_active', false)
            ->count();

        // Daftar hari libur.
        $holidays = (clone $query)
            ->orderBy('date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.holidays.index', compact(
            'holidays',
            'tahun',
            'search',
            'totalHolidays',
            'activeHolidays',
            'inactiveHolidays'
        ));
    }

    /**
     * Menyimpan hari libur baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
                'unique:holidays,date',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'type' => [
                'required',
                Rule::in([
                    'Nasional',
                    'Khusus ESDM',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ], [
            'date.required' => 'Tanggal hari libur wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'date.unique' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'name.max' => 'Nama hari libur maksimal 150 karakter.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
            'description.max' => 'Keterangan maksimal 2.000 karakter.',
            'is_active.required' => 'Status hari libur wajib ditentukan.',
        ]);

        Holiday::create($validated);

        return redirect()
            ->route('admin.holidays', [
                'tahun' => substr($validated['date'], 0, 4),
            ])
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    /**
     * Memperbarui data hari libur.
     */
    public function update(Request $request, int $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays', 'date')->ignore($holiday->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'type' => [
                'required',
                Rule::in([
                    'Nasional',
                    'Khusus ESDM',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ], [
            'date.required' => 'Tanggal hari libur wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'date.unique' => 'Tanggal tersebut sudah digunakan oleh hari libur lain.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
            'description.max' => 'Keterangan maksimal 2.000 karakter.',
        ]);

        $holiday->update($validated);

        return redirect()
            ->route('admin.holidays', [
                'tahun' => substr($validated['date'], 0, 4),
            ])
            ->with('success', 'Data hari libur berhasil diperbarui.');
    }

    /**
     * Menghapus data hari libur.
     */
    public function destroy(int $id)
    {
        $holiday = Holiday::findOrFail($id);

        $tahun = $holiday->date->format('Y');

        $holiday->delete();

        return redirect()
            ->route('admin.holidays', [
                'tahun' => $tahun,
            ])
            ->with('success', 'Hari libur berhasil dihapus.');
    }
}
