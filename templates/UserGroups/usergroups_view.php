<?= $this->Flash->render() ?>
<?php
// Initialize variables with default values to prevent undefined variable warnings
$username = $username ?? 'Guest';
$isAdmin = $isAdmin ?? false;
$dossier = $dossier ?? null;
$files = $files ?? [];
$subfolders = $subfolders ?? [];
$breadcrumbs = $breadcrumbs ?? [];
$currentPath = $currentPath ?? '';
$permissions = $permissions ?? ['can_read' => 0, 'can_delete' => 0, 'can_download' => 0];
$canReadAll = $canReadAll ?? false;
$group = $group ?? null;
$groupId = $groupId ?? null;
?>

<div class="header">
    <h1>Hi, <?= h($username) ?>! 
        <?= $isAdmin ? '<span class="admin-badge">Admin</span>' : '' ?>
        <?php if (!$isAdmin && ($permissions['can_read'] || $permissions['can_delete'] || $permissions['can_download'])): ?>
            <span class="permission-badge">
                <?php if ($permissions['can_read']): ?>📖<?php endif; ?>
                <?php if ($permissions['can_delete']): ?>🗑️<?php endif; ?>
                <?php if ($permissions['can_download']): ?>⬇️<?php endif; ?>
            </span>
        <?php endif; ?>
    </h1>

    <div class="nav-buttons">
        <?= $this->Html->link('', ['controller' => 'Dossiers', 'action' => 'index'], ['class' => 'home-btn', 'title' => 'Back to Main Page']) ?>
        <?= $this->Html->link('', ['controller' => 'UserGroups', 'action' => 'index'], ['class' => 'groupes-btn', 'title' => 'Manage Groups']) ?>
        <?= $this->Html->link('', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'logout-btn', 'title' => 'Logout']) ?>
    </div>
</div>

<div class="content-container">
    <h2 style="color: white; text-align: center; margin: 30px 0;">
        Group: <?= h($group->name) ?> - Folder: <?= h($dossier->name) ?>
    </h2>

    <div class="breadcrumbs" style="color: white; margin-bottom: 20px;">
        <?= $this->Html->link('Group Home', ['controller' => 'UserGroups', 'action' => 'index', $groupId]) ?>
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            &gt; <?= $this->Html->link(h($breadcrumb['name']), ['controller' => 'UserGroups', 'action' => 'view', $groupId, $breadcrumb['id']]) ?>
        <?php endforeach; ?>
    </div>

    <div class="dossier-container">
        <?php if (!empty($subfolders)): ?>
            <?php foreach ($subfolders as $subfolder): ?>
                <div class="dossier-item">
                    <a href="<?= $this->Url->build(['controller' => 'UserGroups', 'action' => 'view', $groupId, $subfolder->id]) ?>" title="Open <?= h($subfolder->name) ?>">
                        <img src="/app/webroot/img/d.png" alt="folder icon" />
                        <span><?= h($subfolder->name) ?></span>
                        <small class="owner-tag"><?= h($subfolder->username) ?></small>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($files)): ?>
            <?php foreach ($files as $file): ?>
                <div class="dossier-item file-item">
                    <div class="file-actions">
                        <?php if ($permissions['can_download'] || $isAdmin): ?>
                            <a href="<?= h($file->path) ?>" target="_blank" title="Open <?= h($file->name) ?>" class="file-link">
                                <img src="/app/webroot/img/f.png" alt="file icon" />
                                <span><?= h($file->name) ?></span>
                            </a>
                        <?php else: ?>
                            <div class="file-link disabled" title="Download restricted">
                                <img src="/app/webroot/img/f.png" alt="file icon" style="opacity: 0.5;" />
                                <span style="color: #ccc;"><?= h($file->name) ?></span>
                            </div>
                        <?php endif; ?>
                        <small class="owner-tag"><?= h($file->username) ?></small>
                        <small class="file-tag">File</small>

                        <div class="action-buttons">
                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                <a href="<?= h($file->path) ?>" target="_blank" class="action-btn download" title="Download">⬇</a>
                            <?php else: ?>
                                <span class="action-btn download disabled" title="Download restricted">⬇</span>
                            <?php endif; ?>
                            
                            <button class="action-btn info" onclick="alert('Uploaded by: <?= h($file->username) ?>')" title="File Info">ℹ</button>
                            
                            <?php if ($isAdmin || ($permissions['can_delete'] && ($file->username === $username || $canReadAll))): ?>
                                <?= $this->Form->postLink('❌', 
                                    ['controller' => 'Fichier', 'action' => 'delete', $file->id],
                                    ['confirm' => 'Are you sure you want to delete this file?', 'class' => 'action-btn delete', 'title' => 'Delete'])
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($subfolders) && empty($files)): ?>
            <p style="color: white; text-align: center; width: 100%;">No content in this folder.</p>
        <?php endif; ?>
    </div>
</div>

<style>
body, html {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #000000, #001F4D);
    background-attachment: fixed;
    min-height: 100vh;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.header {
    background: linear-gradient(135deg, #000000, #666666);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin: 0;
    padding: 10px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    width: 100%;
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

.permission-badge {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.nav-buttons {
    display: flex;
    gap: 10px;
}

.nav-buttons .home-btn,
.nav-buttons .groupes-btn,
.nav-buttons .logout-btn {
    display: block;
    width: 36px;
    height: 36px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    border: none;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.nav-buttons .home-btn:hover,
.nav-buttons .groupes-btn:hover,
.nav-buttons .logout-btn:hover {
    opacity: 1;
}

.nav-buttons .home-btn {
    background-image: url('/app/webroot/img/home-icon.png'); /* Replace with your home icon */
}

.nav-buttons .groupes-btn {
    background-image: url('/app/webroot/img/group-icon.png'); /* Replace with your groups icon */
}

.nav-buttons .logout-btn {
    background-image: url('/app/webroot/img/logout-icon.png'); /* Replace with your logout icon */
}

.content-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.content-container h2 {
    color: white;
    text-align: center;
    margin: 30px 0;
    font-size: 28px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.breadcrumbs {
    background-color: rgba(0, 0, 0, 0.6);
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.breadcrumbs a {
    color: #87CEEB;
    text-decoration: none;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

.dossier-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: flex-start;
}

.dossier-item {
    background-color: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 15px;
    width: 180px; /* Fixed width for consistency */
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
}

.dossier-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
}

.dossier-item a {
    text-decoration: none;
    color: white;
    font-weight: bold;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.dossier-item img {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
}

.dossier-item span {
    word-break: break-word;
    font-size: 14px;
}

.file-item .file-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.file-item .file-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: white;
    font-weight: bold;
    width: 100%;
}

.file-item .file-link.disabled {
    cursor: not-allowed;
}

.file-item .file-link img {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
}

.file-item .file-link span {
    word-break: break-word;
    font-size: 14px;
}

.action-buttons {
    margin-top: 10px;
    display: flex;
    gap: 5px;
    justify-content: center;
    width: 100%;
}

.action-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s ease;
    text-decoration: none; /* For links */
    display: inline-block; /* For links */
    line-height: 1; /* For consistent height */
}

.action-btn:hover {
    background-color: #0056b3;
}

.action-btn.download {
    background-color: #28a745;
}

.action-btn.download:hover {
    background-color: #218838;
}

.action-btn.info {
    background-color: #ffc107;
    color: #333;
}

.action-btn.info:hover {
    background-color: #e0a800;
}

.action-btn.delete {
    background-color: #dc3545;
}

.action-btn.delete:hover {
    background-color: #c82333;
}

.action-btn.disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.owner-tag {
    background-color: #6c757d;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    margin-top: 5px;
}

.file-tag {
    background-color: #17a2b8;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    margin-top: 5px;
}
</style>



<script>
// Toggle help text visibility
function toggleHelp() {
    const helpText = document.getElementById('helpText');
    if (helpText.style.display === 'none') {
        helpText.style.display = 'block';
    } else {
        helpText.style.display = 'none';
    }
}

// Show file information
function showFileInfo(name, owner, uploadDate, size) {
    alert(`Informations du fichier:\n\nNom: ${name}\nPropriétaire: ${owner}\nDate de téléchargement: ${uploadDate}\nTaille: ${size}`);
}

// Add help button functionality if it exists
document.addEventListener('DOMContentLoaded', function() {
    const helpBtn = document.querySelector('.help-btn');
    if (helpBtn) {
        helpBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleHelp();
        });
    }
});
</script>

