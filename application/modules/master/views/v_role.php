<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <div>
        <button class="btn btn-secondary btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#trashModal">
            <i class="fas fa-trash-restore fa-sm text-white-50"></i> Recycle Bin (<?= isset($deleted_roles) ? count($deleted_roles) : 0 ?>)
        </button>

        <?php if($can_create): ?>
            <button class="btn btn-primary btn-sm shadow-sm" onclick="showModal('add')">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Role
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-left-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger border-left-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Jabatan & Wewenang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th width="10%">ID</th>
                        <th>Nama Role</th>
                        <th>Kelompok</th>
                        <th class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($roles)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data role.</td></tr>
                    <?php else: ?>
                        <?php foreach($roles as $r): ?>
                        <tr>
                            <td><code><?= $r->role_id ?></code></td>
                            <td class="font-weight-bold text-dark"><?= $r->role_nm ?></td>
                            <td>
                                <?php 
                                    $badges = [
                                        '01' => '<span class="badge badge-info"><i class="fas fa-building"></i> Internal</span>',
                                        '02' => '<span class="badge badge-warning"><i class="fas fa-handshake"></i> Eksternal</span>',
                                        '03' => '<span class="badge badge-secondary">Lainnya</span>'
                                    ];
                                    echo isset($badges[$r->role_tp]) ? $badges[$r->role_tp] : $r->role_tp;
                                ?>
                            </td>
                            <td class="text-center">
                                <?php if($r->active_st == 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($can_update): ?>
                                    <button class="btn btn-warning btn-sm btn-circle" onclick='editRole(<?= json_encode($r) ?>)' title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if($can_delete && $r->role_id != '01.01'): ?>
                                    <a href="<?= base_url('role/delete/'.$r->role_id) ?>" 
                                       class="btn btn-danger btn-sm btn-circle" 
                                       onclick="return confirm('Yakin ingin menghapus role <?= $r->role_nm ?>?')" 
                                       title="Hapus ke Recycle Bin">
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

<div class="modal fade" id="roleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('role/save') ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Form Role</h5>
                    <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="form-group" id="id_container" style="display:none;">
                        <label>ID Role</label>
                        <input type="text" name="id" id="role_id" class="form-control" readonly style="background-color: #eaecf4;">
                    </div>

                    <div class="form-group">
                        <label>Nama Role / Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="role_nm" id="role_nm" class="form-control" required placeholder="Contoh: Staff Gudang">
                    </div>

                    <div class="form-group">
                        <label>Kelompok Role <span class="text-danger">*</span></label>
                        <select name="role_tp" id="role_tp" class="form-control" required>
                            <option value="01">01 - Internal (Pegawai Tetap/Kontrak)</option>
                            <option value="02">02 - Eksternal (Magang/Mitra)</option>
                            <option value="03">03 - Lainnya</option>
                        </select>
                        <small class="text-primary mt-2 d-block text-auto-id">
                            <i class="fas fa-info-circle"></i> ID akan digenerate otomatis: <b>01.XX</b>
                        </small>
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
                <h5 class="modal-title"><i class="fas fa-trash-restore"></i> Recycle Bin</h5>
                <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Role</th>
                                <th>Dihapus Oleh</th>
                                <th>Waktu Hapus</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($deleted_roles)): ?>
                                <tr><td colspan="5" class="text-center">Tong sampah kosong.</td></tr>
                            <?php else: ?>
                                <?php foreach($deleted_roles as $dr): ?>
                                <tr>
                                    <td><code><?= $dr->role_id ?></code></td>
                                    <td><?= $dr->role_nm ?></td>
                                    <td><?= $dr->deleted_by ?></td>
                                    <td><small><?= $dr->deleted_at ?></small></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('role/restore/'.$dr->role_id) ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Restore data ini?')">
                                            <i class="fas fa-undo"></i> Restore
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    function showModal(mode) {
        if(mode == 'add') {
            $('#modalTitle').text('Tambah Role Baru');
            $('#is_update').val('0');
            
            // Reset Form
            $('#role_nm').val('');
            $('#role_tp').val('01').attr('disabled', false); // Enable dropdown
            $('#active_st').val('1');
            
            // Tampilan Insert (Sembunyikan ID input, tampilkan info auto-id)
            $('#id_container').hide();
            $('.text-auto-id').show();
            updateHelperText(); // Panggil fungsi update text
        }
        $('#roleModal').modal('show');
    }

    function editRole(data) {
        $('#modalTitle').text('Edit Role');
        $('#is_update').val('1');
        
        // Isi Form
        $('#role_id').val(data.role_id);
        $('#role_nm').val(data.role_nm);
        $('#role_tp').val(data.role_tp); 
        $('#active_st').val(data.active_st);

        // Tampilan Edit (Tampilkan ID input, sembunyikan info auto-id)
        $('#id_container').show();
        $('.text-auto-id').hide();
        
        $('#roleModal').modal('show');
    }

    // Event Listener saat dropdown 'Kelompok Role' berubah
    $('#role_tp').change(function(){
        updateHelperText();
    });

    function updateHelperText() {
        let val = $('#role_tp').val();
        $('.text-auto-id').html('<i class="fas fa-info-circle"></i> ID akan digenerate otomatis: <b>' + val + '.XX</b>');
    }
</script>