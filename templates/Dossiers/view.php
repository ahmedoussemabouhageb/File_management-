<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Folder Contents</title>
        <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #E0D3CC, #E0C5BC);
            background-attachment: fixed;
            min-height: 100vh;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header {
    position: fixed; /* أو relative حسب احتياجك */
    top: 0;
    left: 0;
    right: 0;

    background: linear-gradient(135deg, #000000, #C4ADAD);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;

    padding: 10px 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);

    width: 100vw;
    margin: 0;
    z-index: 999;
}


        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-btn-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .btn-label {
            color: white;
            font-size: 12px;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        .nav-icon-btn, .upload-btn, .home-btn, .logout-btn, .help-btn, .admin-btn, .back-btn {
            background-size: 24px 24px;
            background-repeat: no-repeat;
            background-position: center;
            width: 40px;
            height: 40px;
            border: 2px solid #ccc;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            display: block;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .upload-btn {
            background-image: url('/app/webroot/img/up.png');
        }

        .home-btn {
            background-image: url('/app/webroot/img/home.png');
        }

        .logout-btn {
            background-image: url('/app/webroot/img/l.png');
        }

        .admin-btn {
            background-image: url('/app/webroot/img/admin.png');
        }

        .back-btn {
            background-image: url('/app/webroot/img/arrow.png');
        }

        .nav-icon-btn:hover, .upload-btn:hover, .home-btn:hover, .logout-btn:hover, .admin-btn:hover, .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .nav-icon-btn:active, .upload-btn:active, .home-btn:active, .logout-btn:active, .admin-btn:active, .back-btn:active {
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
        }

        .content-container {
            padding: 100px 40px 40px 40px; /* Increased padding-top to account for fixed header */
        }

        .breadcrumb {
            color: white;
            margin-bottom: 20px;
            font-size: 25px;
        }

        .breadcrumb a {
            color: #87CEEB;
            text-decoration: none;

        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .folder-header {
            color: white;
            text-align: center;
            margin: 20px 0;
            font-size: 30px;
        }

        .current-path {
            color: #ffffff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.25);
            padding: 15px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .section-header {
            color: white;
            font-size: 18px;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .dossier-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 30px;
            padding: 0 0 40px 0;
            box-sizing: border-box;
        }

        .dossier-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: background 0.3s ease, transform 0.3s ease;
            box-shadow: 0 0 10px rgba(0,0,0,0.6);
        }

        .dossier-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .dossier-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 12px;
            opacity: 0.75;
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));
        }

        .dossier-item span {
            display: block;
            color: white;
            font-size: 16px;
            font-weight: 600;
            word-break: break-word;
        }

        .owner-tag {
            display: block;
            color: #87CEEB;
            font-size: 20px;
            margin-top: 5px;
            font-weight: normal;
        }

        .file-item {
            border: 2px solid rgba(135, 206, 235, 0.3);
        }

        .file-actions {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dossier-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .dossier-actions .action-btn {
            font-size: 16px;
            padding: 0;
            width: 40px;
            height: 40px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .action-btn {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #666;
            color: white;
            font-size: 16px;
            padding: 0;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .action-btn:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: scale(1.05);
        }

        .action-btn.delete {
            color: #ff4c4c;
        }

        .action-btn.download {
            color: #87CEEB;
        }

        .empty-folder {
            text-align: center;
            color: #ccc;
            font-style: italic;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin: 20px 0;
        }

        .debug-info {
            background: rgba(255, 255, 255, 0.1);
            color: #ccc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 12px;
            display: none;
        }
    </style>
</head>
<body>
    <?= $this->Flash->render() ?>

    <?php
    $username = $username ?? 'Guest';
    $isAdmin = $isAdmin ?? false;
    $dossier = $dossier ?? null;
    $files = $files ?? [];
    $subfolders = $subfolders ?? [];
    $breadcrumbs = $breadcrumbs ?? [];
    $currentPath = $currentPath ?? '';
    $permissions = $permissions ?? [];

    function canDeleteFile($file, $username, $isAdmin, $permissions) {
        if ($isAdmin) {
            return true;
        }

        if ($file->username === $username) {
            return true;
        }

        if (($permissions['can_delete'] ?? 0) == 1) {
            return true;
        }

        return false;
    }
    ?>

    <div class="header">
        <h1> <?= $isAdmin ? '<span class="admin-badge">Admin</span>' : '' ?></h1>

        <div class="header-buttons">
            <?php if (!empty($dossier)): ?>
                <div class="header-btn-container">
                    <?php if (!empty($breadcrumbs) && count($breadcrumbs) > 1): ?>
                        <?php $parentBreadcrumb = $breadcrumbs[count($breadcrumbs) - 2]; ?>
                        <?= $this->Html->link('', ['action' => 'view', $parentBreadcrumb['id']], ['class' => 'back-btn']) ?>
                    <?php else: ?>
                        <?= $this->Html->link('', ['action' => 'index'], ['class' => 'back-btn']) ?>
                    <?php endif; ?>
                    <span class="btn-label">Back</span>
                </div>
            <?php endif; ?>

            <div class="header-btn-container">
                <?= $this->Html->link('', ['action' => 'add', $dossier->id ?? null], ['class' => 'upload-btn']) ?>
                <span class="btn-label">Upload</span>
            </div>
            <div class="header-btn-container">
                <?= $this->Html->link('', ['action' => 'index'], ['class' => 'home-btn']) ?>
                <span class="btn-label">Home</span>
            </div>
            <div class="header-btn-container">
                <?= $this->Html->link('', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'logout-btn']) ?>
                <span class="btn-label">Logout</span>
            </div>

            <?php if ($isAdmin): ?>
                <div class="header-btn-container">
                    <?= $this->Html->link('', ['controller' => 'Admin', 'action' => 'index'], ['class' => 'admin-btn']) ?>
                    <span class="btn-label">Admin</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="content-container">
        <!-- Debug Information (hidden by default, uncomment to show) -->
        <!--
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Current User: <?= h($username) ?><br>
            Is Admin: <?= $isAdmin ? 'Yes' : 'No' ?><br>
            Permissions: Read=<?= ($permissions['can_read'] ?? 0) ?>, Delete=<?= ($permissions['can_delete'] ?? 0) ?>, Download=<?= ($permissions['can_download'] ?? 0) ?>
        </div>
        -->

        <!-- Breadcrumb Navigation -->
        <?php if (!empty($breadcrumbs)): ?>
            <div class="breadcrumb">
                <?= $this->Html->link('Home', ['action' => 'index']) ?>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        → <strong><?= h($crumb['name']) ?></strong>
                    <?php else: ?>
                        → <?= $this->Html->link(h($crumb['name']), ['action' => 'view', $crumb['id']]) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($dossier)): ?>
            <h2 class="folder-header">
                📁 <?= h($dossier->name) ?>
                <?php if ($isAdmin && $dossier->username !== $username): ?>
                    <small class="owner-tag">(Owner: <?= h($dossier->username) ?>)</small>
                <?php endif; ?>
            </h2>

            <?php if (!empty($currentPath)): ?>
                <div class="current-path">
                    Path: <?= h($currentPath) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($subfolders) && count($subfolders) > 0): ?>
            <h3 class="section-header">📁 Folders (<?= count($subfolders) ?>)</h3>
            <div class="dossier-container">
                <?php foreach ($subfolders as $subfolder): ?>
                    <div class="dossier-item">
                        <a href="<?= $this->Url->build(['action' => 'view', $subfolder->id]) ?>" title="Open <?= h($subfolder->name) ?>">
                            <img src="/app/webroot/img/d.png" alt="folder icon" />
                            <span><?= h($subfolder->name) ?></span>
                            <?php if ($isAdmin && $subfolder->username !== $username): ?>
                                <small class="owner-tag"><?= h($subfolder->username) ?></small>
                            <?php endif; ?>
                        </a>

                        <div class="dossier-actions">
                            <button class="action-btn info" onclick="showFolderInfo('<?= h($subfolder->name) ?>', '<?= h($subfolder->username ?? $username) ?>', '<?= h($currentPath . '/' . $subfolder->name) ?>', '<?= $subfolder->id ?>')">ℹ️</button>

                            <?php if ($isAdmin || ($permissions['can_download'] ?? 0) == 1): ?>
                                <?= $this->Html->link('📦', ['action' => 'download', $subfolder->id, 'dossier'], ['class' => 'action-btn download', 'target' => '_blank']) ?>
                            <?php endif; ?>

                            <?php if (canDeleteFile($subfolder, $username, $isAdmin, $permissions)): ?>
                                <?= $this->Form->postLink('🗑️',
                                    ['action' => 'delete', $subfolder->id, 'dossier'],
                                    [
                                        'confirm' => 'Are you sure you want to delete folder "' . $subfolder->name . '" and all its contents?',
                                        'class' => 'action-btn delete'
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($files) && count($files) > 0): ?>
            <h3 class="section-header">📄 Files (<?= count($files) ?>)</h3>
            <div class="dossier-container">
                <?php foreach ($files as $file): ?>
                    <div class="dossier-item file-item">
                        <div class="file-actions">
                            <a href="<?= h($file->path) ?>" target="_blank" title="Open <?= h($file->name) ?>" class="file-link">
                                <img src="/app/webroot/img/f.png" alt="file icon" />
                                <span><?= h($file->name) ?></span>
                            </a>
                            <?php if ($isAdmin && $file->username !== $username): ?>
                                <small class="owner-tag"><?= h($file->username) ?></small>
                            <?php endif; ?>

                            <div class="action-buttons">
                                <?php if ($isAdmin || ($permissions['can_download'] ?? 0) == 1): ?>
                                    <?= $this->Html->link('📦',
                                        ['action' => 'download', $file->id, 'fichier'],
                                        ['class' => 'action-btn download']
                                    ) ?>
                                <?php endif; ?>

                                <a href="<?= h($file->path) ?>" target="_blank" class="action-btn">ℹ️</a>

                                <?php if (canDeleteFile($file, $username, $isAdmin, $permissions)): ?>
                                    <?= $this->Form->postLink('🗑️',
                                        ['action' => 'delete', $file->id, 'fichier'],
                                        [
                                            'confirm' => 'Are you sure you want to delete "' . $file->name . '"?',
                                            'class' => 'action-btn delete'
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ((empty($subfolders) || count($subfolders) === 0) && (empty($files) || count($files) === 0)): ?>
            <div class="empty-folder">
                <h3>📂 This folder is empty</h3>
                <p>Start by uploading files or creating subfolders!</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showFolderInfo(folderName, owner, path, folderId) {

            const info = `Folder Information:

Name: ${folderName}
Owner: ${owner}
Path: ${path}
Folder ID: ${folderId}

Click OK to view folder contents or Cancel to close.`;

            if (confirm(info)) {
                window.location.href = '/dossiers/view/' + folderId;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            const dossierItems = document.querySelectorAll('.dossier-item');
            dossierItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (!e.target.closest('.action-btn')) {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    }
                });
            });
        });
    </script>
</body>
</html>
