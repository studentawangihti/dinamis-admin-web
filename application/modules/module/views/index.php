<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
    <?php if($can_create): ?>
        <button class="btn btn-primary shadow-sm" onclick="showModal('add')">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Menu
        </button>
    <?php endif; ?>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
<?php elseif($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>ID (Kode)</th>
                        <th>Parent</th>
                        <th>Nama Navigasi</th>
                        <th>URL / Controller</th>
                        <th>Icon</th>
                        <th class="text-center">Aktif</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($modules as $m): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= $m->nav_id ?></strong></td>
                        <td><?= $m->parent_code ? $m->parent_code.' - '.$m->parent_name : '-' ?></td>
                        <td><?= $m->nav_nm ?></td>
                        <td><code><?= $m->nav_url ?></code></td>
                        <td><i class="<?= $m->icon ?> text-primary"></i> <?= $m->icon ?></td>
                        <td class="text-center">
                            <?php if($m->active_st == 1): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if($can_update): ?>
                                    <button class="btn btn-sm btn-info" onclick='editModule(<?= json_encode($m) ?>)' title="Edit"><i class="fas fa-edit"></i></button>
                                <?php endif; ?>
                                <?php if($can_delete): ?>
                                    <a href="<?= base_url('module/delete/'.$m->nav_id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus menu ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="moduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('module/save') ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Form Navigasi</h5>
                    <button class="close text-white" type="button" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="is_update" id="is_update" value="0">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nav ID (Kode Unik) <span class="text-danger">*</span></label>
                                <input type="text" name="nav_id" id="nav_id" class="form-control" placeholder="Contoh: 01.01" required>
                                <small class="text-muted">Gunakan format angka bertingkat (01, 01.01, 01.01.01)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Parent (Induk)</label>
                                <select name="nav_parent" id="nav_parent" class="form-control">
                                    <option value="">-- Root (Menu Utama) --</option>
                                    <?php foreach($parents as $p): ?>
                                        <option value="<?= $p->nav_id ?>"><?= $p->nav_id ?> - <?= $p->nav_nm ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Navigasi <span class="text-danger">*</span></label>
                        <input type="text" name="nav_nm" id="nav_nm" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>URL / Controller</label>
                        <input type="text" name="nav_url" id="nav_url" class="form-control" placeholder="#" required>
                        <small class="text-muted">Isi '#' jika ini adalah parent menu. Isi nama controller (misal: 'user') jika halaman.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Icon Class (FontAwesome)</label>
                                <input type="text" name="icon" id="icon" class="form-control" placeholder="fas fa-circle">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active_st" id="active_st" class="form-control">
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
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
            $('#modalTitle').text('Tambah Navigasi');
            $('#is_update').val('0');
            $('#nav_id').val('').attr('readonly', false); 
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
        $('#nav_id').val(data.nav_id).attr('readonly', true); // ID tidak boleh ganti saat edit
        $('#nav_parent').val(data.nav_parent);
        $('#nav_nm').val(data.nav_nm);
        $('#nav_url').val(data.nav_url);
        $('#icon').val(data.icon);
        $('#active_st').val(data.active_st);
        $('#moduleModal').modal('show');
    }

    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>