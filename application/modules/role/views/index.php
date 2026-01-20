<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <?php if($can_create): ?>
        <button class="btn btn-primary btn-sm shadow-sm" onclick="showModal('add')">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Role
        </button>
    <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-left-success" role="alert">
        <?= $this->session->flashdata('success') ?>
    </div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger border-left-danger" role="alert">
        <?= $this->session->flashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th width="15%">ID Role</th>
                        <th>Nama Role</th>
                        <th>Kelompok</th>
                        <th>Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $r): ?>
                    <tr>
                        <td><code><?= $r->role_id ?></code></td>
                        <td><strong><?= $r->role_nm ?></strong></td>
                        <td>
                            <?php if($r->role_tp == '01'): ?>
                                <span class="badge badge-info">Internal (Pegawai)</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Eksternal (Mitra/Magang)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($r->active_st == 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($can_update): ?>
                                <button class="btn btn-warning btn-sm btn-circle" onclick='editRole(<?= json_encode($r) ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if($can_delete && $r->role_id != '01.01'): ?>
                                <a href="<?= base_url('role/delete/'.$r->role_id) ?>" 
                                   class="btn btn-danger btn-sm btn-circle" 
                                   onclick="return confirm('Yakin ingin menghapus role <?= $r->role_nm ?>? Data akan hilang dari list.')" 
                                   title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="form-group" id="id_container" style="display:none;">
                        <label>ID Role</label>
                        <input type="text" name="id" id="role_id" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="role_nm" id="role_nm" class="form-control" required placeholder="Contoh: Staff Gudang">
                    </div>

                    <div class="form-group">
                        <label>Kelompok / Tipe Role <span class="text-danger">*</span></label>
                        <select name="role_tp" id="role_tp" class="form-control" required>
                            <option value="01">01 - Internal (Pegawai Tetap/Kontrak)</option>
                            <option value="02">02 - Eksternal (Magang/Mitra)</option>
                            <option value="03">03 - Lainnya</option>
                        </select>
                        <small class="text-muted text-auto-id">ID akan digenerate otomatis: <b>01.XX</b></small>
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
    function showModal(mode) {
        if(mode == 'add') {
            $('#modalTitle').text('Tambah Role Baru');
            $('#is_update').val('0');
            
            // Reset Form
            $('#role_nm').val('');
            $('#role_tp').val('01').attr('disabled', false); // Bisa pilih tipe saat baru
            $('#active_st').val('1');
            
            $('#id_container').hide(); // Sembunyikan ID field saat tambah baru (karena auto)
            $('.text-auto-id').show();
        }
        $('#roleModal').modal('show');
    }

    function editRole(data) {
        $('#modalTitle').text('Edit Role');
        $('#is_update').val('1');
        
        // Isi Data
        $('#role_id').val(data.role_id);
        $('#role_nm').val(data.role_nm);
        $('#role_tp').val(data.role_tp);
        $('#active_st').val(data.active_st);

        // Tampilkan ID (Readonly)
        $('#id_container').show();
        $('.text-auto-id').hide();
        
        $('#roleModal').modal('show');
    }

    // Update info teks helper saat dropdown berubah
    $('#role_tp').change(function(){
        let val = $(this).val();
        $('.text-auto-id').html('ID akan digenerate otomatis: <b>' + val + '.XX</b>');
    });
</script>