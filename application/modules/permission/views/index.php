<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter Role</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('permission') ?>">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label font-weight-bold">Pilih Jabatan (Role):</label>
                        <div class="col-sm-6">
                            <select name="role_id" class="form-control" onchange="this.form.submit()">
                                <?php foreach($roles as $r): ?>
                                    <option value="<?= $r->role_id ?>" <?= $r->role_id == $selected_role ? 'selected' : '' ?>>
                                        <?= $r->role_id ?> - <?= $r->role_nm ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th width="10%">ID Menu</th>
                        <th>Nama Menu Navigasi</th>
                        <th>Status</th>
                        <th class="text-center" width="15%">Akses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modules as $m): ?>
                    <tr>
                        <td><code><?= $m->nav_id ?></code></td>
                        <td>
                            <?php 
                                $level = substr_count($m->nav_id, '.'); 
                                echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                            ?>
                            <i class="<?= $m->icon ?> mr-1"></i> <?= $m->nav_nm ?>
                        </td>
                        <td>
                            <?php if($m->active_st == 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input access-toggle" 
                                    id="perm_<?= $m->nav_id ?>" 
                                    data-role="<?= $selected_role ?>"
                                    data-module="<?= $m->nav_id ?>"
                                    <?= isset($permissions[$m->nav_id]) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="perm_<?= $m->nav_id ?>"></label>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Script AJAX untuk auto-save
    $('.access-toggle').change(function() {
        const checkbox = $(this);
        const role_id = checkbox.data('role');
        const module_id = checkbox.data('module');
        const value = checkbox.is(':checked') ? 1 : 0;

        $.ajax({
            url: "<?= base_url('permission/change') ?>",
            type: "POST",
            data: {
                role_id: role_id,
                module_id: module_id,
                value: value
            },
            success: function(response) {
                console.log('Permission updated');
            },
            error: function(xhr) {
                alert('Gagal menyimpan perubahan. Periksa koneksi.');
                checkbox.prop('checked', !value); // Kembalikan posisi jika gagal
            }
        });
    });
</script>