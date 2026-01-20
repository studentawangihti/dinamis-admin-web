<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <div>
        <button class="btn btn-secondary btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#trashModal">
            <i class="fas fa-trash-restore fa-sm text-white-50"></i> Recycle Bin (<?= isset($deleted_users) ? count($deleted_users) : 0 ?>)
        </button>

        <?php if($can_create): ?>
            <button class="btn btn-primary btn-sm shadow-sm" onclick="showModal('add')">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah User
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger border-left-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna Aplikasi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th width="15%">ID User</th>
                        <th>Username</th>
                        <th>Role / Jabatan</th>
                        <th class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><code><?= $u->user_id ?></code></td>
                            <td><strong><?= $u->auth_nm ?></strong></td>
                            <td><span class="badge badge-info"><?= $u->role_name ?></span></td>
                            <td class="text-center">
                                <?= $u->active_st == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non-Aktif</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php if($can_update): ?>
                                    <button class="btn btn-warning btn-sm btn-circle" onclick='editUser(<?= json_encode($u) ?>)' title="Edit"><i class="fas fa-edit"></i></button>
                                <?php endif; ?>
                                
                                <?php if($can_delete && $u->user_id != 'P0001'): ?>
                                    <a href="<?= base_url('user/delete/'.$u->user_id) ?>" class="btn btn-danger btn-sm btn-circle" onclick="return confirm('Hapus user ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
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

<div class="modal fade" id="userModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('user/save') ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Form User</h5>
                    <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="form-group" id="id_container" style="display:none;">
                        <label>ID User</label>
                        <input type="text" name="id" id="user_id" class="form-control" readonly style="background-color: #eaecf4;">
                    </div>

                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" required placeholder="ex: jhon_doe">
                    </div>

                    <div class="form-group">
                        <label>Role / Jabatan <span class="text-danger">*</span></label>
                        <select name="role_id" id="role_id" class="form-control" required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach($roles as $r): ?>
                                <option value="<?= $r->role_id ?>"><?= $r->role_nm ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="*******">
                        <small class="text-danger" id="pass_note" style="display:none;">
                            <i class="fas fa-exclamation-circle"></i> Kosongkan jika tidak ingin mengubah password.
                        </small>
                        <small class="text-primary" id="pass_new_note" style="display:none;">
                            <i class="fas fa-info-circle"></i> Password wajib diisi untuk user baru.
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
                <h5 class="modal-title"><i class="fas fa-trash-restore"></i> Recycle Bin (User)</h5>
                <button class="close text-white" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>ID Sampah</th>
                                <th>Username</th>
                                <th>Dihapus Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($deleted_users)): ?>
                                <tr><td colspan="4" class="text-center">Kosong.</td></tr>
                            <?php else: ?>
                                <?php foreach($deleted_users as $du): ?>
                                <tr>
                                    <td><code><?= $du->user_id ?></code></td>
                                    <td><?= $du->auth_nm ?></td>
                                    <td><?= $du->deleted_by ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('user/restore/'.$du->user_id) ?>" class="btn btn-success btn-sm" onclick="return confirm('Restore user ini?')">
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
    $(document).ready(function() { $('#dataTable').DataTable(); });

    function showModal(mode) {
        if(mode == 'add') {
            $('#modalTitle').text('Tambah User Baru');
            $('#is_update').val('0');
            
            $('#username').val('');
            $('#role_id').val('');
            $('#active_st').val('1');
            $('#password').val('');
            
            // Logic Tampilan Form
            $('#id_container').hide(); // ID disembunyikan (Auto ID)
            $('#pass_note').hide();
            $('#pass_new_note').show();
            $('#password').attr('required', true); // Wajib password
        }
        $('#userModal').modal('show');
    }

    function editUser(data) {
        $('#modalTitle').text('Edit User');
        $('#is_update').val('1');
        
        $('#user_id').val(data.user_id);
        $('#username').val(data.auth_nm);
        $('#role_id').val(data.role_id);
        $('#active_st').val(data.active_st);
        $('#password').val('');

        // Logic Tampilan Form
        $('#id_container').show(); // ID muncul (readonly)
        $('#pass_note').show();
        $('#pass_new_note').hide();
        $('#password').attr('required', false); // Tidak wajib password
        
        $('#userModal').modal('show');
    }
</script>