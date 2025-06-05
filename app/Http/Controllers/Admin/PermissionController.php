<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission; // Import model Permission
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Semua method di controller ini akan dilindungi oleh permission 'role-permission-manage'
        // karena pengelolaan permission adalah bagian dari pengelolaan peran & hak akses.
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengelola hak akses.');
        }

        $permissions = Permission::orderBy('name', 'asc')->paginate(20); // Ambil semua permission, paginasi
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menambah hak akses.');
        }

        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan hak akses.');
        }

        $validatedData = $request->validate([
            // Nama permission harus unik untuk guard_name yang sama (default 'web')
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'], 
        ]);

        // guard_name default adalah 'web' jika tidak dispesifikkan.
        // Jika Anda ingin guard_name bisa dipilih, tambahkan input di form dan validasi di sini.
        Permission::create(['name' => $validatedData['name'] /*, 'guard_name' => 'web' */]);

        // Reset cache permission Spatie agar permission baru langsung dikenali
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission "'.$validatedData['name'].'" berhasil ditambahkan.');
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
    public function edit(Permission $permission) // Route Model Binding
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'permission-edit'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengedit hak akses.');
        }

        // Sebaiknya nama permission tidak diubah jika sudah banyak digunakan.
        // Di sini kita hanya menampilkan formnya.
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'permission-edit'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memperbarui hak akses.');
        }

        $validatedData = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('permissions')->ignore($permission->id)->where(function ($query) use ($permission) {
                    return $query->where('guard_name', $permission->guard_name); // Unik berdasarkan nama DAN guard_name
                }),
            ],
            // 'guard_name' => ['sometimes', 'string', 'max:255'], // Jika guard_name boleh diubah
        ]);

        // Hati-hati saat mengubah nama permission jika sudah digunakan
        $permission->name = $validatedData['name'];
        // if ($request->filled('guard_name')) { // Jika guard name bisa diubah
        //     $permission->guard_name = $request->guard_name;
        // }
        $permission->save();

        // Reset cache permission Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        if (!Auth::user()->hasPermissionTo('role-permission-manage')) { // Atau permission 'permission-delete'
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menghapus hak akses.');
        }

        // PENTING: Menghapus permission akan otomatis mencabutnya dari semua peran yang memilikinya.
        // Pertimbangkan untuk memberikan peringatan yang sangat jelas atau bahkan mencegah penghapusan
        // permission yang masih aktif digunakan oleh peran-peran penting.

        // Contoh: Cek apakah permission ini masih digunakan oleh role mana pun
        if ($permission->roles()->count() > 0) {
            $rolesUsingPermission = $permission->roles()->pluck('name')->implode(', ');
            return redirect()->route('admin.permissions.index')
                            ->with('error', 'Permission "'.$permission->name.'" tidak dapat dihapus karena masih digunakan oleh peran: ' . $rolesUsingPermission . '. Harap lepaskan permission ini dari peran tersebut terlebih dahulu.');
        }

        // (Opsional) Tambahan pengecekan untuk permission krusial yang tidak boleh dihapus, misal 'role-permission-manage'
        $criticalPermissions = ['role-permission-manage', 'user-list', 'user-create', 'user-edit', 'user-delete']; // Sesuaikan daftarnya
        if (in_array($permission->name, $criticalPermissions)) {
            return redirect()->route('admin.permissions.index')->with('error', 'Permission default/krusial "'.$permission->name.'" tidak dapat dihapus.');
        }

        $permissionName = $permission->name;
        $permission->delete();

        // Reset cache permission Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission "'.$permissionName.'" berhasil dihapus.');
    }
}
