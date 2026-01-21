<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <div>
        <button class="btn btn-secondary btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#trashModal">
            <i class="fas fa-trash-restore fa-sm text-white-50"></i> Recycle Bin 
            <span class="badge badge-light"><?= isset($deleted_modules) ? count($deleted_modules) : 0 ?></span>
        </button>

        <?php if(isset($can_create) ? $can_create : true): ?>
            <button class="btn btn-primary btn-sm shadow-sm" onclick="showModal('add')">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Menu
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger border-left-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Menu & Navigasi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="10%">Kode ID</th>
                        <th>Nama Navigasi (Hierarki)</th>
                        <th>URL / Controller</th>
                        <th class="text-center">Icon</th>
                        <th class="text-center">Status</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($modules)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Belum ada data menu.</td></tr>
                    <?php else: ?>
                        <?php foreach($modules as $m): ?>
                        
                        <?php 
                            // LOGIKA VISUAL HIERARKI
                            // Hitung level berdasarkan jumlah titik. Contoh: "01.01" ada 1 titik -> Level 1 (Anak)
                            $level = substr_count($m->nav_id, '.'); 
                            
                            // Style untuk Root Menu (Menu Utama) -> Bold & Background tipis
                            $row_class = ($level == 0) ? 'bg-light font-weight-bold' : '';
                            
                            // Padding kiri agar menjorok ke dalam (30px per level)
                            $indent_style = "padding-left: " . (10 + ($level * 30)) . "px;";
                            
                            // Icon panah siku untuk sub-menu
                            $arrow_icon = ($level > 0) ? '<i class="fas fa-level-up-alt fa-rotate-90 text-gray-400 mr-2"></i>' : '';

                            // Proteksi Menu Sistem (Tidak boleh dihapus)
                            $protected_ids = ['00', '01', '01.01', '01.01.01', '01.01.02', '01.01.03'];
                            $is_protected = in_array($m->nav_id, $protected_ids);
                        ?>

                        <tr class="<?= $row_class ?>">
                            <td><code><?= $m->nav_id ?></code></td>
                            
                            <td style="<?= $indent_style ?>">
                                <?= $arrow_icon ?>
                                <?= $m->nav_nm ?>
                                <?php if($level == 0): ?>
                                    <span class="badge badge-secondary ml-1" style="font-size:0.6rem">ROOT</span>
                                <?php endif; ?>
                            </td>

                            <td><small><?= $m->nav_url ?></small></td>
                            
                            <td class="text-center">
                                <?php if($m->icon): ?>
                                    <i class="<?= $m->icon ?> text-primary"></i>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center">
                                <?php if($m->active_st == 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-circle" onclick='editModule(<?= json_encode($m) ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if(!$is_protected): ?>
                                    <a href="<?= base_url('module/delete/'.$m->nav_id) ?>" 
                                       class="btn btn-danger btn-sm btn-circle" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus menu <?= $m->nav_nm ?>? Data akan masuk ke Recycle Bin.')" 
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm btn-circle" disabled title="Menu Sistem Dilindungi">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="moduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('module/save') ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Form Navigasi</h5>
                    <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID Navigasi (Kode) <span class="text-danger">*</span></label>
                                <input type="text" name="nav_id" id="nav_id" class="form-control" required placeholder="Contoh: 01.02">
                                <small class="text-muted">Gunakan titik untuk sub-menu.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Induk Menu (Parent)</label>
                                <select name="nav_parent" id="nav_parent" class="form-control">
                                    <option value="">-- ROOT (Menu Utama) --</option>
                                    <?php foreach($parents as $p): ?>
                                        <option value="<?= $p->nav_id ?>"><?= $p->nav_id ?> - <?= $p->nav_nm ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="nav_nm" id="nav_nm" class="form-control" required placeholder="Contoh: User Management">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>URL Controller <span class="text-danger">*</span></label>
                                <input type="text" name="nav_url" id="nav_url" class="form-control" required placeholder="Contoh: user">
                                <small class="text-muted">Isi <b>#</b> jika menu punya sub-menu.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Icon Class</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                    </div>
                                    <input type="text" name="icon" id="icon" class="form-control" placeholder="fas fa-user">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="active_st" id="active_st" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="trashModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-trash-restore"></i> Recycle Bin (Navigasi)</h5>
                <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    <small><i class="fas fa-info-circle"></i> Data di sini bisa dikembalikan (Restore) atau dihapus selamanya agar ID bisa dipakai ulang.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm" width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama Menu</th>
                                <th>Dihapus Oleh</th>
                                <th>Waktu Hapus</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($deleted_modules)): ?>
                                <tr><td colspan="5" class="text-center">Tong sampah kosong.</td></tr>
                            <?php else: ?>
                                <?php foreach($deleted_modules as $dm): ?>
                                <tr>
                                    <td><?= $dm->nav_id ?></td>
                                    <td><?= $dm->nav_nm ?></td>
                                    <td><?= $dm->deleted_by ?></td>
                                    <td><?= $dm->deleted_at ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('module/restore/'.$dm->nav_id) ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Restore menu ini?')"
                                           title="Pulihkan">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="<?= base_url('module/hard_delete/'.$dm->nav_id) ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('PERINGATAN: Hapus Permanen? Data tidak bisa kembali!')"
                                           title="Hapus Permanen">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() { 
        // Inisialisasi DataTables
        // PENTING: "ordering": false agar urutan Parent->Child tidak diacak oleh plugin
        $('#dataTable').DataTable({
            "ordering": false,
            "pageLength": 25
        }); 
    });

    // Fungsi Reset & Buka Modal Tambah
    function showModal(mode) {
        if(mode == 'add') {
            $('#modalTitle').text('Tambah Navigasi Baru');
            $('#is_update').val('0');
            
            // Reset Form
            $('#nav_id').val('').attr('readonly', false).removeClass('bg-light'); 
            $('#nav_parent').val('');
            $('#nav_nm').val('');
            $('#nav_url').val('');
            $('#icon').val('');
            $('#active_st').val('1');
        }
        $('#moduleModal').modal('show');
    }

    // Fungsi Isi Form & Buka Modal Edit
    function editModule(data) {
        $('#modalTitle').text('Edit Navigasi');
        $('#is_update').val('1'); 
        
        // Isi Form
        $('#nav_id').val(data.nav_id).attr('readonly', true).addClass('bg-light'); // ID Kunci
        $('#nav_parent').val(data.nav_parent);
        $('#nav_nm').val(data.nav_nm);
        $('#nav_url').val(data.nav_url);
        $('#icon').val(data.icon);
        $('#active_st').val(data.active_st);
        
        $('#moduleModal').modal('show');
    }
</script>