<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Pastikan ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Untuk transaksi database jika diperlukan nanti
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Melindungi semua method di controller ini.
        // Kita akan gunakan permission 'role-permission-manage' yang sudah kita buat di seeder.
        // Penerapan bisa di constructor atau di masing-masing method.
        // Untuk sekarang, kita terapkan di masing-masing method.
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengelola peran.');
        }

        $roles = Role::withCount('permissions', 'users')->orderBy('name', 'asc')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'role-create'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menambah peran.');
        }

        $permissions = Permission::orderBy('name', 'asc')->get(); // Ambil semua permission
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'role-create'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan peran.');
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'], // Nama peran harus unik
            'permissions' => ['required', 'array'], // Permissions wajib dan harus array
            'permissions.*' => ['string', 'exists:permissions,name'], // Setiap permission harus ada di tabel permissions
        ]);

        // Gunakan DB Transaction untuk memastikan pembuatan role dan assignment permission berhasil bersamaan
        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validatedData['name']]);
            $role->givePermissionTo($validatedData['permissions']); // Berikan permission yang dipilih ke role baru

            DB::commit();

            return redirect()->route('admin.roles.index')->with('success', 'Peran "'.$role->name.'" berhasil ditambahkan dengan hak aksesnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Anda bisa tambahkan logging error di sini
            // Log::error('Error creating role: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Gagal menambahkan peran. Silakan coba lagi. Pesan Error: '.$e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role) // Route Model Binding
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'role-edit'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengedit peran.');
        }

        // Jangan izinkan edit peran Admin jika namanya 'Admin' (untuk proteksi dasar)
        // Anda bisa kembangkan ini lebih lanjut jika perlu
        if ($role->name === 'Admin') {
            // return redirect()->route('admin.roles.index')->with('error', 'Peran "Admin" tidak dapat diedit melalui antarmuka ini.');
        }

        $permissions = Permission::orderBy('name', 'asc')->get(); // Ambil semua permission
        $rolePermissions = $role->permissions->pluck('name')->toArray(); // Ambil nama permission yang sudah dimiliki peran ini

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'role-edit'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memperbarui peran.');
        }

        // Validasi, nama peran harus unik kecuali untuk peran ini sendiri
        // Jika nama peran default tidak boleh diubah, tambahkan logika untuk itu
        $isDefaultRole = in_array($role->name, ['Admin', 'StafGudang', 'Viewer']);

        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                $isDefaultRole ? Rule::unique('roles')->ignore($role->id) : 'unique:roles,name,'.$role->id // Jika default, hanya cek unique jika nama berubah. Jika tidak, nama bisa diedit.
                                                                                                        // Sebenarnya jika readonly di form, validasi nama tidak perlu sekompleks ini.
                                                                                                        // Cukup 'required|string|max:255' jika nama tidak diubah.
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        DB::beginTransaction();
        try {
            // Update nama peran hanya jika tidak termasuk peran default yang namanya kita kunci
            if (!$isDefaultRole) {
                $role->name = $validatedData['name'];
                $role->save();
            } else if ($role->name !== $validatedData['name']) {
                // Jika mencoba mengubah nama peran default, kirim error atau abaikan perubahan nama
                return redirect()->back()
                                ->with('error', 'Nama peran default (Admin, StafGudang, Viewer) tidak dapat diubah.')
                                ->withInput();
            }

            // Sinkronkan permission
            // syncPermissions akan menghapus permission lama dan menerapkan permission baru yang dipilih
            $role->syncPermissions($validatedData['permissions']);

            DB::commit();

            return redirect()->route('admin.roles.index')->with('success', 'Peran "'.$role->name.'" berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->with('error', 'Gagal memperbarui peran. Silakan coba lagi. Pesan Error: '.$e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'role-delete'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menghapus peran.');
        }

        // Daftar peran default yang tidak boleh dihapus
        $defaultRoles = ['Admin', 'StafGudang', 'Viewer']; // Sesuaikan dengan peran default Anda

        if (in_array($role->name, $defaultRoles)) {
            return redirect()->route('admin.roles.index')->with('error', 'Peran default "'.$role->name.'" tidak dapat dihapus.');
        }

        // Opsional: Cek apakah peran masih digunakan oleh pengguna
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Peran "'.$role->name.'" tidak dapat dihapus karena masih digunakan oleh ' . $role->users()->count() . ' pengguna. Harap pindahkan pengguna ke peran lain terlebih dahulu.');
        }

        // Proses penghapusan peran
        // Spatie/laravel-permission akan otomatis menghapus relasi di tabel role_has_permissions.
        // Relasi di model_has_roles juga akan terhapus jika menggunakan onDelete('cascade')
        // atau ditangani oleh Spatie saat role di-detach dari user.
        // Untuk memastikan, kita bisa detach semua user dari role ini dulu (meskipun delete() biasanya sudah handle).
        // $role->users()->sync([]); // Ini akan melepaskan semua user dari peran ini

        $roleName = $role->name; // Simpan nama untuk pesan sukses
        $role->delete(); // Ini akan menghapus peran dan relasinya di role_has_permissions

        return redirect()->route('admin.roles.index')->with('success', 'Peran "'.$roleName.'" berhasil dihapus!');
    }
}
