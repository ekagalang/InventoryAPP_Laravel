@extends('layouts.app')

@section('title', 'Daftar Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Daftar Barang</h1>
    @can('barang-create')
        <a href="{{ route('barang.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Barang
        </a>
    @endcan
</div>

{{-- Advanced Search Component --}}
<x-advanced-search 
    :action="route('barang.index')" 
    :fields="[
        [
            'name' => 'search',
            'type' => 'search',
            'label' => 'Search',
            'placeholder' => 'Cari nama barang, kode, atau deskripsi...',
            'width' => 4
        ],
        [
            'name' => 'kategori_id',
            'type' => 'select',
            'label' => 'Kategori',
            'options' => $kategoris->pluck('nama_kategori', 'id')->toArray(),
            'width' => 3
        ],
        [
            'name' => 'unit_id',
            'type' => 'select', 
            'label' => 'Unit',
            'options' => $units->pluck('nama_unit', 'id')->toArray(),
            'width' => 3
        ],
        [
            'name' => 'lokasi_id',
            'type' => 'select',
            'label' => 'Lokasi',
            'options' => $lokasis->pluck('nama_lokasi', 'id')->toArray(),
            'width' => 2
        ],
        [
            'name' => 'tipe_item',
            'type' => 'select',
            'label' => 'Tipe Item',
            'options' => [
                'habis_pakai' => 'Habis Pakai',
                'aset' => 'Aset (Pinjaman)'
            ],
            'width' => 3
        ],
        [
            'name' => 'stok_min',
            'type' => 'number',
            'label' => 'Stok Min',
            'placeholder' => 'Min stok',
            'width' => 2,
            'min' => 0
        ],
        [
            'name' => 'stok_max', 
            'type' => 'number',
            'label' => 'Stok Max',
            'placeholder' => 'Max stok',
            'width' => 2,
            'min' => 0
        ],
        [
            'name' => 'low_stock_only',
            'type' => 'select',
            'label' => 'Stok Rendah',
            'options' => [1 => 'Hanya Stok Rendah'],
            'width' => 2
        ]
    ]"
    :currentFilters="$currentFilters"
/>

{{-- Bulk Operations Toolbar --}}
<div class="card mb-3" id="bulkOperationsCard" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span id="selectedCount">0</span> item(s) selected
            </div>
            <div class="btn-group" role="group">
                @can('barang-delete')
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                @endcan
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkUpdateCategory()">
                    <i class="bi bi-pencil"></i> Update Category
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="bulkUpdateLocation()">
                    <i class="bi bi-geo-alt"></i> Update Location
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="bulkExport()">
                    <i class="bi bi-download"></i> Export Selected
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Results Table --}}
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                Total: {{ $barangs->total() }} barang
                @if($currentFilters)
                    (filtered)
                @endif
            </h6>
            <div>
                <small class="text-muted">
                    Showing {{ $barangs->firstItem() ?? 0 }} to {{ $barangs->lastItem() ?? 0 }} of {{ $barangs->total() }}
                </small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                    </th>
                    <th class="text-center">No</th>
                    <th class="text-center">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_barang', 'direction' => request('sort') == 'nama_barang' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                            Nama Barang
                            @if(request('sort') == 'nama_barang')
                                <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">Tipe Item</th>
                    <th class="text-center">Kategori</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Lokasi</th>
                    <th class="text-center">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'kode_barang', 'direction' => request('sort') == 'kode_barang' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                            Kode
                            @if(request('sort') == 'kode_barang')
                                <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'stok', 'direction' => request('sort') == 'stok' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                            Stok
                            @if(request('sort') == 'stok')
                                <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">Stok Min.</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $key => $barang)
                    <tr>
                        <td class="text-center align-middle">
                            <input type="checkbox" class="form-check-input item-checkbox" value="{{ $barang->id }}" data-name="{{ $barang->nama_barang }}">
                        </td>
                        <td class="text-center align-middle">{{ $barangs->firstItem() + $key }}</td>
                        <td class="text-center align-middle">{{ $barang->nama_barang }}</td> {{-- Biarkan nama barang rata kiri --}}
                        <td>
                            @if($barang->tipe_item == 'aset')
                                <span class="badge bg-info">Aset</span>
                            @else
                                <span class="badge bg-secondary">Habis Pakai</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->unit->singkatan_unit ?? ($barang->unit->nama_unit ?? '-') }}</td>
                        <td class="text-center align-middle">{{ $barang->lokasi->nama_lokasi ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->kode_barang ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->stok }}</td>
                        <td class="text-center align-middle">{{ number_format($barang->stok_minimum, 0, ',', '.') }}</td>
                        <td class="text-center align-middle">
                            @if ($barang->status == 'aktif')
                                <span class="badge bg-success text-capitalize">{{ $barang->status }}</span>
                            @elseif ($barang->status == 'rusak')
                                <span class="badge bg-warning text-capitalize">{{ $barang->status }}</span>
                            @elseif ($barang->status == 'hilang')
                                <span class="badge bg-danger text-capitalize">{{ $barang->status }}</span>
                            @else
                                <span class="badge bg-secondary text-capitalize">{{ $barang->status }}</span>
                            @endif
                        </td>
                        <td>
                            @can('barang-show')
                                <a href="{{ route('barang.show', $barang->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="bi bi-eye"></i> Detail</a>
                            @endcan
                            @can('barang-edit')
                                <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan
                            @can('barang-delete')
                                <form action="{{ route('barang.destroy', $barang->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center align-middle">Tidak ada data barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($barangs->hasPages())
        <div class="card-footer">
            {{ $barangs->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkOperationsCard = document.getElementById('bulkOperationsCard');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkOperationsVisibility();
    });

    // Individual checkbox functionality
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkOperationsVisibility();
        });
    });

    function updateSelectAllCheckbox() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        selectAllCheckbox.checked = checkedItems.length === itemCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedItems.length > 0 && checkedItems.length < itemCheckboxes.length;
    }

    function updateBulkOperationsVisibility() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        if (checkedItems.length > 0) {
            bulkOperationsCard.style.display = 'block';
            selectedCountSpan.textContent = checkedItems.length;
        } else {
            bulkOperationsCard.style.display = 'none';
        }
    }

    // Bulk Operations Functions
    window.bulkDelete = function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            alert('Please select items to delete');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selectedIds.length} selected item(s)?`)) {
            return;
        }

        // Send delete request
        fetch('{{ route("barang.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting items: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting items');
        });
    };

    window.bulkUpdateCategory = function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            alert('Please select items to update');
            return;
        }

        const categorySelect = `
            <select id="bulkCategorySelect" class="form-select">
                <option value="">Choose Category...</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        `;

        showBulkModal('Update Category', categorySelect, function() {
            const categoryId = document.getElementById('bulkCategorySelect').value;
            if (!categoryId) {
                alert('Please select a category');
                return;
            }

            performBulkUpdate(selectedIds, { kategori_id: categoryId });
        });
    };

    window.bulkUpdateLocation = function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            alert('Please select items to update');
            return;
        }

        const locationSelect = `
            <select id="bulkLocationSelect" class="form-select">
                <option value="">Choose Location...</option>
                @foreach($lokasis as $lokasi)
                    <option value="{{ $lokasi->id }}">{{ $lokasi->nama_lokasi }}</option>
                @endforeach
            </select>
        `;

        showBulkModal('Update Location', locationSelect, function() {
            const locationId = document.getElementById('bulkLocationSelect').value;
            if (!locationId) {
                alert('Please select a location');
                return;
            }

            performBulkUpdate(selectedIds, { lokasi_id: locationId });
        });
    };

    window.bulkExport = function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            alert('Please select items to export');
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("barang.bulk-export") }}';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);

        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
    }

    function showBulkModal(title, content, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="bulkConfirmBtn">Update</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        document.getElementById('bulkConfirmBtn').addEventListener('click', function() {
            onConfirm();
            bootstrapModal.hide();
        });

        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
    }

    function performBulkUpdate(ids, updateData) {
        fetch('{{ route("barang.bulk-update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: ids, data: updateData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating items: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating items');
        });
    }
});
</script>
@endpush