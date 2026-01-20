<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dashboard') ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin Panel</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="<?= base_url('dashboard') ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <?php
    /**
     * FUNGSI REKURSIF SB ADMIN 2
     * Mendukung Multi-Level Menu dengan ID Navigasi (01.01.01)
     */
    function render_sbadmin_menu($items, $parent_id = 'accordionSidebar') {
        $ci =& get_instance();
        
        foreach ($items as $item) {
            // Skip Dashboard manual karena sudah ada di atas
            if (strtolower($item['nav_url']) == 'app/dashboard' || strtolower($item['nav_url']) == 'dashboard') continue;

            // Generate ID unik untuk collapse (ganti titik dengan underscore)
            $collapse_id = 'collapse_' . str_replace('.', '_', $item['nav_id']);
            $url = ($item['nav_url'] == '#' || $item['nav_url'] == '') ? '#' : base_url($item['nav_url']);
            $icon = $item['icon'] ? $item['icon'] : 'fas fa-fw fa-circle';

            // Cek apakah punya Sub-Menu
            if (!empty($item['children'])) {
                ?>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#<?= $collapse_id ?>"
                        aria-expanded="true" aria-controls="<?= $collapse_id ?>">
                        <i class="<?= $icon ?>"></i>
                        <span><?= $item['nav_nm'] ?></span>
                    </a>
                    <div id="<?= $collapse_id ?>" class="collapse" aria-labelledby="headingTwo" data-parent="#<?= $parent_id ?>">
                        <div class="bg-white py-2 collapse-inner rounded">
                            
                            <?php 
                            // LOOPING ANAK (LEVEL SELANJUTNYA)
                            foreach($item['children'] as $child): 
                                $child_url = ($child['nav_url'] == '#' || $child['nav_url'] == '') ? '#' : base_url($child['nav_url']);
                                
                                // Jika anak ini punya anak lagi (Level 3)
                                if(!empty($child['children'])): 
                                    $grand_collapse_id = 'collapse_' . str_replace('.', '_', $child['nav_id']);
                                ?>
                                    <a class="collapse-item collapsed" href="#" data-toggle="collapse" data-target="#<?= $grand_collapse_id ?>">
                                        <?= $child['nav_nm'] ?> <i class="fas fa-angle-down float-right mt-1"></i>
                                    </a>
                                    <div id="<?= $grand_collapse_id ?>" class="collapse">
                                        <div class="collapse-inner pl-3 border-left ml-2">
                                            <?php foreach($child['children'] as $grandchild): ?>
                                                <a class="collapse-item" href="<?= base_url($grandchild['nav_url']) ?>">
                                                    <?= $grandchild['nav_nm'] ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <a class="collapse-item" href="<?= $child_url ?>">
                                        <?= $child['nav_nm'] ?>
                                    </a>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
                <?php
            } else {
                // Item Tanpa Anak (Single Link)
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $url ?>">
                        <i class="<?= $icon ?>"></i>
                        <span><?= $item['nav_nm'] ?></span>
                    </a>
                </li>
                <?php
            }
        }
    }

    // EKSEKUSI RENDER
    if (!empty($sidebar_menu)) {
        render_sbadmin_menu($sidebar_menu);
    }
    ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>