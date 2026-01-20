<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <?php if($can_create): ?>
        <button class="btn btn-primary shadow-sm" onclick="showModal('add')">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Menu
        </button>
    <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-left-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger border-left-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
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
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($modules)): ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach($modules as $m): ?>
                        
                        <?php 
                            // LOGIKA VISUAL HIERARKI
                            // Hitung jumlah titik untuk menentukan level (0=Root, 1=Sub, 2=Sub-Sub)
                            $level = substr_count($m->nav_id, '.'); 
                            
                            // Style untuk Root Menu (Menu Utama)
                            $row_class = ($level == 0) ? 'bg-light font-weight-bold' : '';
                            
                            // Padding kiri agar menjorok ke dalam (25px per level)
                            $indent_style = "padding-left: " . (10 + ($level * 30)) . "px;";
                            
                            // Icon panah kecil untuk sub-menu
                            $arrow_icon = ($level > 0) ? '<i class="fas fa-level-up-alt fa-rotate-90 text-gray-400 mr-2"></i>' : '';
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
                                <?php if($can_update): ?>
                                    <button class="btn btn-warning btn-sm btn-circle" onclick='editModule(<?= json_encode($m) ?>)' title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if($can_delete): ?>
                                    <a href="<?= base_url('module/delete/'.$m->nav_id) ?>" 
                                       class="btn btn-danger btn-sm btn-circle" 
                                       onclick="return confirm('Hapus menu <?= $m->nav_nm ?>?')" 
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
                                <input type="text" name="nav_id" id="nav_id" class="form-control" required placeholder="Ex: 01.02">
                                <small class="text-muted">Gunakan titik untuk sub-menu (Cth: 01.01)</small>
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
                                <label>Icon Class (FontAwesome)</label>
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

<script>
    $(document).ready(function() { 
        // Menonaktifkan sorting default DataTables pada kolom nama
        // agar urutan hierarki (Induk -> Anak) tidak berantakan saat di-klik
        $('#dataTable').DataTable({
            "ordering": false 
        }); 
    });

    function showModal(mode) {
        if(mode == 'add') {
            $('#modalTitle').text('Tambah Navigasi');
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

    function editModule(data) {
        $('#modalTitle').text('Edit Navigasi');
        $('#is_update').val('1'); 
        
        // Isi Form
        $('#nav_id').val(data.nav_id).attr('readonly', true).addClass('bg-light'); 
        $('#nav_parent').val(data.nav_parent);
        $('#nav_nm').val(data.nav_nm);
        $('#nav_url').val(data.nav_url);
        $('#icon').val(data.icon);
        $('#active_st').val(data.active_st);
        
        $('#moduleModal').modal('show');
    }
</script>