<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
</div>

<div class="row">
    <div class="col-lg-12">
        
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-body d-flex align-items-center justify-content-between">
                <h5 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-tag"></i> Setting Hak Akses untuk Role:
                </h5>
                <form method="GET" action="<?= base_url('permission') ?>" class="form-inline">
                    <select name="role_id" class="form-control bg-light border-0 small" style="font-size: 1.1rem; font-weight:bold;" onchange="this.form.submit()">
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r->role_id ?>" <?= $r->role_id == $selected_role ? 'selected' : '' ?>>
                                <?= $r->role_nm ?> (<?= $r->role_id ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Matriks Menu & Navigasi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="10%">ID Menu</th>
                                <th>Nama Menu / Modul</th>
                                <th width="15%" class="text-center">Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($modules as $m): ?>
                            <?php 
                                // Logic Hierarki Visual (Sama seperti modul Navigasi)
                                $level = substr_count($m->nav_id, '.'); 
                                $indent = 10 + ($level * 30);
                                $is_parent = ($level == 0);
                                $bg_row = $is_parent ? 'bg-light font-weight-bold' : '';
                            ?>
                            <tr class="<?= $bg_row ?>">
                                <td><code><?= $m->nav_id ?></code></td>
                                <td style="padding-left: <?= $indent ?>px;">
                                    <?php if($level > 0): ?>
                                        <i class="fas fa-level-up-alt fa-rotate-90 text-gray-400 mr-2"></i>
                                    <?php endif; ?>
                                    <?= $m->nav_nm ?>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input access-toggle" 
                                            id="switch_<?= $m->nav_id ?>" 
                                            data-role="<?= $selected_role ?>"
                                            data-nav="<?= $m->nav_id ?>"
                                            <?= isset($permissions[$m->nav_id]) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="switch_<?= $m->nav_id ?>" style="cursor:pointer;"></label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
    <div id="toast-success" class="toast hide" role="alert" data-delay="3000">
        <div class="toast-header bg-success text-white">
            <strong class="mr-auto"><i class="fas fa-check"></i> Sukses</strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body">Permission berhasil disimpan.</div>
    </div>
    
    <div id="toast-error" class="toast hide" role="alert" data-delay="3000">
        <div class="toast-header bg-danger text-white">
            <strong class="mr-auto"><i class="fas fa-times"></i> Error</strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body" id="toast-error-msg">Gagal menyimpan.</div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.access-toggle').change(function() {
            const checkbox = $(this);
            const roleId   = checkbox.data('role');
            const navId    = checkbox.data('nav');
            const state    = checkbox.is(':checked') ? 1 : 0; // 1=On, 0=Off

            // 1. SIAPKAN DATA
            let postData = {
                role_id: roleId,
                nav_id: navId,
                state: state
            };

            // 2. INJECT CSRF TOKEN (WAJIB ADA!)
            // Mengambil token keamanan dari CodeIgniter agar tidak kena Error 403
            const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
            const csrfHash = '<?= $this->security->get_csrf_hash() ?>';
            
            // Masukkan token ke dalam data yang dikirim
            postData[csrfName] = csrfHash;

            // 3. Disable tombol sementara (Cegah klik brutal)
            checkbox.prop('disabled', true);
            $('body').css('cursor', 'wait'); // Ubah kursor jadi loading

            // 4. KIRIM AJAX
            $.ajax({
                url: "<?= base_url('permission/change') ?>",
                type: "POST",
                dataType: "JSON",
                data: postData, // Gunakan data yang sudah ada tokennya
                success: function(response) {
                    checkbox.prop('disabled', false);
                    $('body').css('cursor', 'default');

                    if(response.status) {
                        // SUKSES
                        $('#toast-success').toast('show');
                        console.log('Saved: ' + navId + ' = ' + state);
                    } else {
                        // GAGAL (Misal: Proteksi Superadmin)
                        checkbox.prop('checked', !state); // Kembalikan posisi tombol
                        $('#toast-error-msg').text(response.msg);
                        $('#toast-error').toast('show');
                    }
                },
                error: function(xhr, status, error) {
                    checkbox.prop('disabled', false);
                    checkbox.prop('checked', !state); // Kembalikan posisi tombol
                    $('body').css('cursor', 'default');

                    // Deteksi Jenis Error
                    let msg = 'Terjadi kesalahan sistem.';
                    if(xhr.status == 403) msg = 'Error 403: Token CSRF Invalid / Expired.';
                    if(xhr.status == 404) msg = 'Error 404: URL permission/change tidak ditemukan.';

                    $('#toast-error-msg').text(msg);
                    $('#toast-error').toast('show');
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        });
    });
</script>