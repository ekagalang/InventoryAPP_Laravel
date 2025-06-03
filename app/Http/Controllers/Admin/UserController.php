<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Import base Controller
use App\Models\User; // Import model User
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function __construct()
    {
        // Melindungi semua method di controller ini agar hanya bisa diakses
        // oleh user dengan permission tertentu.
        // Anda bisa sesuaikan nama permissionnya jika berbeda dengan yang di seeder.
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasPermissionTo('user-list') && 
                !Auth::user()->hasPermissionTo('user-create') && 
                !Auth::user()->hasPermissionTo('user-edit') && 
                !Auth::user()->hasPermissionTo('user-delete') &&
                !Auth::user()->hasPermissionTo('role-permission-manage') // Mungkin admin punya ini
            ) {
                // Alternatif: Cukup cek satu permission umum jika ada, misal 'user-manage'
                // if (!Auth::user()->hasPermissionTo('user-manage')) {
                //     abort(403, 'AKSES DITOLAK.');
                // }
            }
            // Cara lebih spesifik per method:
            // $this->middleware('permission:user-list')->only('index');
            // $this->middleware('permission:user-create')->only(['create', 'store']);
            // $this->middleware('permission:user-edit')->only(['edit', 'update']);
            // $this->middleware('permission:user-delete')->only('destroy');
            // Karena Spatie middleware kita bermasalah, kita akan cek manual di tiap method
            return $next($request);
        });
    }

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('user-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat daftar pengguna.');
        }

        // Ambil semua user, mungkin dengan rolenya juga (eager load)
        $users = User::with('roles')->orderBy('name', 'asc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->hasPermissionTo('user-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menambah pengguna.');
        }

        $roles = Role::orderBy('name', 'asc')->get(); // Ambil semua peran
        return view('admin.users.create', compact('roles'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('user-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan pengguna.');
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class], // Pastikan email unik di tabel users
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // Menggunakan aturan password default Laravel
            'roles' => ['required', 'array'], // Pastikan roles adalah array
            'roles.*' => ['string', 'exists:roles,name'], // Setiap item di array roles harus ada di tabel roles kolom name
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Hash passwordnya
            'email_verified_at' => now(), // Atau biarkan null jika ingin proses verifikasi email
        ]);

        // Berikan peran yang dipilih ke pengguna baru
        $user->assignRole($validatedData['roles']);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan!');
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
    public function edit(User $user) // Route Model Binding akan otomatis mengambil user berdasarkan ID
    {
        if (!Auth::user()->hasPermissionTo('user-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengedit pengguna.');
        }

        $roles = Role::orderBy('name', 'asc')->get(); // Ambil semua peran
        $userRoles = $user->roles->pluck('name')->toArray(); // Ambil nama peran yang sudah dimiliki user ini

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) // Route Model Binding
    {
        if (!Auth::user()->hasPermissionTo('user-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memperbarui pengguna.');
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users')->ignore($user->id), // Email unik, abaikan user saat ini
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional, jika diisi harus ada konfirmasi
            'roles' => ['required', 'array'], // Roles wajib dan harus array
            'roles.*' => ['string', 'exists:roles,name'], // Setiap role harus ada di tabel roles
        ]);

        // Update data dasar pengguna
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // Update password hanya jika field password baru diisi
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save(); // Simpan perubahan pada user (nama, email, password jika diubah)

        // Sinkronkan peran pengguna
        // syncRoles akan menghapus semua peran lama user dan menerapkan peran baru yang ada di array $validatedData['roles']
        $user->syncRoles($validatedData['roles']);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna "' . $user->name . '" berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) // Route Model Binding
    {
        if (!Auth::user()->hasPermissionTo('user-delete')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menghapus pengguna.');
        }

        // Mencegah admin menghapus akunnya sendiri
        if (Auth::user()->id == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        // (Opsional) Logika tambahan: Mencegah penghapusan user jika ia satu-satunya admin,
        // atau jika user tersebut memiliki data penting yang tidak bisa ditinggalkan (orphaned).
        // Misalnya, jika ada peran 'Super Admin' yang tidak boleh dihapus.
        // if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
        //     return redirect()->route('admin.users.index')->with('error', 'Tidak dapat menghapus satu-satunya Super Admin!');
        // }

        // Proses penghapusan pengguna
        // Trait HasRoles dari Spatie seharusnya otomatis menangani pelepasan peran/permission
        // ketika user dihapus, terutama jika foreign key di tabel pivot (model_has_roles, model_has_permissions)
        // memiliki onDelete('cascade') untuk model_id. Jika tidak, Anda mungkin perlu detach manual:
        // $user->roles()->detach();
        // $user->permissions()->detach();
        // Namun, biasanya $user->delete() sudah cukup.

        $userName = $user->name; // Simpan nama untuk pesan sukses
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna "' . $userName . '" berhasil dihapus!');
    }
}
