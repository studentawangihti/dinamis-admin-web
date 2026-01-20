<button class="btn btn-primary mb-3" onclick="showModal('add')">Tambah User</button>

<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?= $u->user_id ?></td>
            <td><?= $u->auth_nm ?></td>
            <td><?= $u->role_name ?></td>
            <td><?= $u->active_st == 1 ? 'Aktif' : 'Non' ?></td>
            <td>
                <button class="btn btn-warning btn-sm" onclick='editUser(<?= json_encode($u) ?>)'>Edit</button>
                <a href="<?= base_url('user/delete/'.$u->user_id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="userModal">
    <div class="modal-dialog">
        <form action="<?= base_url('user/save') ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Form User</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="form-group">
                        <label>User ID (Manual)</label>
                        <input type="text" name="id" id="id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role_id" id="role_id" class="form-control">
                            <?php foreach($roles as $r): ?>
                                <option value="<?= $r->role_id ?>"><?= $r->role_nm ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showModal(mode) {
    $('#is_update').val('0');
    $('#id').val('').attr('readonly', false);
    $('#username').val('');
    $('#userModal').modal('show');
}
function editUser(data) {
    $('#is_update').val('1');
    $('#id').val(data.user_id).attr('readonly', true);
    $('#username').val(data.auth_nm);
    $('#role_id').val(data.role_id);
    $('#is_active').val(data.active_st);
    $('#userModal').modal('show');
}
</script>