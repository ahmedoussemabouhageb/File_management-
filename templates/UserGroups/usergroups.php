<?= $this->Flash->render() ?>
<?php
// Initialize variables with default values to prevent undefined variable warnings
$username = $username ?? 'Guest';
$isAdmin = $isAdmin ?? false;
$search = $search ?? '';
$dossiers = $dossiers ?? [];
$rootFiles = $rootFiles ?? [];
$results = $results ?? ['folders' => [], 'files' => []];
$permissions = $permissions ?? ['can_read' => 0, 'can_delete' => 0, 'can_download' => 0];
$canReadAll = $canReadAll ?? false;
$group = $group ?? null;
$groupUsers = $groupUsers ?? [];
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

    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search in this group..." value="<?= h($search) ?>" class="search-input">
        <button type="submit" class="search-btn"></button>
    </form>

    <div class="nav-buttons">
        <?= $this->Html->link('', ['controller' => 'Dossiers', 'action' => 'index'], ['class' => 'home-btn', 'title' => 'Back to Main Page']) ?>
        <?= $this->Html->link('', ['controller' => 'UserGroups', 'action' => 'index'], ['class' => 'groupes-btn', 'title' => 'Manage Groups']) ?>
        <?= $this->Html->link('', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'logout-btn', 'title' => 'Logout']) ?>
    </div>
</div>

<div class="welcome-message">
    <p style="color: white; text-align: center; margin: 20px; font-size: 16px;">
        Welcome to Group: <strong><?= h($group->name) ?></strong>!<br>
        <?php if (!empty($groupUsers)): ?>
            Members: 
            <?php foreach ($groupUsers as $user): ?>
                <?= h($user['username']) ?> (R:<?= $user['permissions']['can_read'] ?> D:<?= $user['permissions']['can_download'] ?> X:<?= $user['permissions']['can_delete'] ?>)
            <?php endforeach; ?>
        <?php else: ?>
            No members in this group.
        <?php endif; ?>
        <br>
        - Search folders and files within this group using the bar above.<br>
        - Click a folder to browse inside it.<br>
        <?php if ($permissions['can_download'] || $isAdmin): ?>
        - Click a file to download it.<br>
        <?php else: ?>
        - File downloads are restricted for your account in this group.<br>
        <?php endif; ?>
        - Use the Home button to return to the main page.<br>
        - Use the Logout button to sign out safely.<br>
    </p>
</div>

<!-- Search Results -->
<div class="content-container">
    <?php if (!empty($search) && !empty($results)) : ?>
        <h2 style="color: white;">Search Results in Group:</h2>
        <div style="color: white;">
            <?php if (count($results['folders'])) : ?>
                <h3>Folders</h3>
                <ul class="search-results">
                    <?php foreach ($results['folders'] as $folder): ?>
                        <li>
                            <?= $this->Html->link(
                                h($folder->name) . ' (Path: ' . ($folder->path ?: 'Root') . ')' . 
                                ' - Owner: ' . h($folder->username),
                                ['controller' => 'UserGroups', 'action' => 'view', $groupId, $folder->id],
                                ['class' => 'search-link']
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($results['files'])) : ?>
                <h3>Files</h3>
                <ul class="search-results">
                    <?php foreach ($results['files'] as $file): ?>
                        <li>
                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                <?= $this->Html->link(
                                    h($file->name) . ' (Path: ' . ($file->path ?: 'Root') . ')' . 
                                    ' - Owner: ' . h($file->username),
                                    $file->path,
                                    ['target' => '_blank', 'class' => 'search-link']
                                ) ?>
                            <?php else: ?>
                                <span class="search-link disabled">
                                    <?= h($file->name) ?> (Path: <?= ($file->path ?: 'Root') ?>)
                                    - Owner: <?= h($file->username) ?>
                                    <small style="color: #ff6b6b; margin-left: 10px;">[Download Restricted]</small>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Display -->
    <?php if (!empty($dossiers) || !empty($rootFiles)): ?>
        <h2 style="color: white; text-align: center; margin: 30px 0;">
            Group Content
        </h2>
        
        <?php 
            $groupedContent = [];
            foreach ($dossiers as $dossier) {
                $groupedContent[$dossier->username]['dossiers'][] = $dossier;
            }
            foreach ($rootFiles as $file) {
                $groupedContent[$file->username]['files'][] = $file;
            }
        ?>
        
        <?php foreach ($groupedContent as $user => $userContent): ?>
            <div class="user-section">
                <h3 class="user-header">
                    Content from: <?= h($user) ?> 
                    (<?= (count($userContent['dossiers'] ?? []) + count($userContent['files'] ?? [])) ?> items)
                </h3>
                <div class="dossier-container">
                    <?php if (!empty($userContent['dossiers'])): ?>
                        <?php foreach ($userContent['dossiers'] as $dossier): ?>
                            <?php
                                $isFolder = empty($dossier->path);
                                if ($isFolder) {
                                    $link = $this->Url->build(['controller' => 'UserGroups', 'action' => 'view', $groupId, $dossier->id]);
                                    $icon = "/app/webroot/img/d.png";
                                } else {
                                    $link = $this->Url->build($dossier->path);
                                    $icon = "/app/webroot/img/f.png";
                                }
                            ?>
                            <div class="dossier-item">
                                <a href="<?= $link ?>" title="Open <?= h($dossier->name) ?>">
                                    <img src="<?= $icon ?>" alt="icon" />
                                    <span><?= h($dossier->name) ?></span>
                                    <small class="owner-tag"><?= h($user) ?></small>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($userContent['files'])): ?>
                        <?php foreach ($userContent['files'] as $file): ?>
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
                                    <small class="owner-tag"><?= h($user) ?></small>
                                    <small class="file-tag">Root File</small>

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
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: white; text-align: center;">
            No content found in this group.
        </p>
    <?php endif; ?>
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

.search-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-input {
    width: 300px;
    padding: 10px 15px;
    border: 2px solid #ccc;
    border-radius: 6px;
    background-color: rgba(255, 255, 255, 0.9);
    color: #333;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    border-color: #007bff;
    background-color: white;
}

.search-btn {
    background-image: url('/app/webroot/img/search-icon.png'); /* Replace with your search icon */
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    background-color: transparent;
    border: none;
    width: 24px;
    height: 24px;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.search-btn:hover {
    opacity: 1;
}

.header h1 {
    color: white;
    font-size: 24px;
    margin: 0;
    display: flex;
    align-items: center;
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

.welcome-message {
    background-color: rgba(0, 0, 0, 0.6);
    border-radius: 8px;
    padding: 20px;
    margin: 20px auto;
    max-width: 800px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}

.welcome-message p {
    color: white;
    line-height: 1.6;
}

.welcome-message strong {
    color: #4CAF50;
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

.user-section {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    margin-bottom: 20px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.user-header {
    color: #4CAF50;
    font-size: 20px;
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    padding-bottom: 10px;
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

.search-results {
    list-style: none;
    padding: 0;
    color: white;
}

.search-results li {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
}

.search-results li a {
    color: #87CEEB;
    text-decoration: none;
}

.search-results li a:hover {
    text-decoration: underline;
}

.search-results li span.disabled {
    color: #ccc;
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

