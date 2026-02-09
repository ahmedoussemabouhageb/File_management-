<?= $this->Flash->render() ?>
<?php
$username = $username ?? 'Guest';
$isAdmin = $isAdmin ?? false;
$search = $search ?? '';
$dossiers = $dossiers ?? [];
$rootFiles = $rootFiles ?? [];
$fichiers = $fichiers ?? [];
$results = $results ?? ['folders' => [], 'files' => []];
$permissions = $permissions ?? ['can_read' => 0, 'can_delete' => 0, 'can_download' => 0];
$canReadAll = $canReadAll ?? false;
$groupMembers = $groupMembers ?? [];
$currentDossierId = null;
?>

<div class="header">
    <h1 style="font-size: 2.5em; margin: 15px 0;">Hi, <?= h($username) ?>! 
        <?= $isAdmin ? '<span class="admin-badge">Admin</span>' : '' ?>
        <?php if (!$isAdmin && ($permissions['can_read'] || $permissions['can_delete'] || $permissions['can_download'])): ?>
            <span class="permission-badge">
                <?php if ($permissions['can_read']): ?>📖<?php endif; ?>
                <?php if ($permissions['can_delete']): ?>🗑️<?php endif; ?>
                <?php if ($permissions['can_download']): ?>⬇️<?php endif; ?>
                <?php if (!empty($groupMembers)): ?>👥<?php endif; ?>
            </span>
        <?php endif; ?>
        <?php if (!$isAdmin && !empty($groupMembers)): ?>
            <span class="group-badge">Group: <?= count($groupMembers) ?> members</span>
        <?php endif; ?>
    </h1>

    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="<?= $canReadAll ? 'Search all folders and files...' : (!empty($groupMembers) ? 'Search your group folders and files...' : 'Search your folders and files...') ?>" value="<?= h($search) ?>" class="search-input" />
        <button type="submit" class="search-btn"></button>
    </form>
    
    <div class="header-buttons">
        <div class="header-btn-container">
            <?= $this->Html->link('', ['action' => 'add'], ['class' => 'upload-btn', 'title' => 'Upload New Dossier or File']) ?>
            <span class="btn-label">Upload</span>
        </div>
        <div class="header-btn-container">
            <?= $this->Html->link('', ['controller' => 'Dossiers', 'action' => 'index'], ['class' => 'home-btn', 'title' => 'Back to Main Page']) ?>
            <span class="btn-label">Home</span>
        </div>
        <div class="header-btn-container">
            <?= $this->Html->link('', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'logout-btn', 'title' => 'Logout']) ?>
            <span class="btn-label">Logout</span>
        </div>
        
        <?php if ($isAdmin): ?>
            <div class="header-btn-container">
                <?= $this->Html->link('', ['controller' => 'Admin', 'action' => 'index'], ['class' => 'admin-btn', 'title' => 'Admin Panel']) ?>
                <span class="btn-label">Admin</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="helpText" class="help-text" style="display: none; margin: 20px 40px;">
    <p style="color: white;">
        Welcome to your document manager!<br>
        - Search folders and files using the bar above.<br>
        - Click a folder to browse inside it.<br>
        <?php if ($permissions['can_download'] || $isAdmin): ?>
        - Click a file to download it.<br>
        - Use the download button to download entire folders as ZIP files.<br>
        <?php else: ?>
        - File downloads are restricted for your account.<br>
        <?php endif; ?>
        - Use the info button to view folder/file details.<br>
        - Use the Home button to return to the main page.<br>
        - Use the Logout button to sign out safely.<br>
        
        <?php if ($isAdmin): ?>
        <strong>Admin Features:</strong><br>
        - You can view and manage all users' files and folders.<br>
        - Files are organized by username for easy management.<br>
        - Use the Admin Panel to manage user permissions.<br>
        <?php elseif ($canReadAll): ?>
        <strong>Extended Access:</strong><br>
        - You have read access to all users' files and folders.<br>
        <?php if ($permissions['can_delete']): ?>
        - You can delete files from any user.<br>
        <?php endif; ?>
        <?php if ($permissions['can_download']): ?>
        - You can download files and folders from any user.<br>
        <?php endif; ?>
        <?php elseif (!empty($groupMembers)): ?>
        <strong>Group Access:</strong><br>
        - You can access files from group members: <?= implode(', ', $groupMembers) ?><br>
        - Group files are clearly marked with owner names.<br>
        <?php if ($permissions['can_delete']): ?>
        - You can delete files from group members.<br>
        <?php endif; ?>
        <?php if ($permissions['can_download']): ?>
        - You can download files and folders from group members.<br>
        <?php endif; ?>
        <?php else: ?>
        - You can only see and manage your own files.<br>
        <?php endif; ?>
        Have fun organizing your files!
    </p>
</div>

<!-- Search Results -->
<div class="content-container">
    <?php if (!empty($search) && !empty($results)) : ?>
        <h2 style="color: white;">Search Results:</h2>
        <div style="color: white;">
            <?php if (count($results['folders'])) : ?>
                <h3>Folders</h3>
                <ul class="search-results">
                    <?php foreach ($results['folders'] as $folder): ?>
                        <li>
                            <?= $this->Html->link(
                                h($folder->name) . ' (Path: ' . ($folder->path ?: 'Root') . ')' . 
                                (($canReadAll || !empty($groupMembers)) ? ' - Owner: ' . h($folder->username) : ''),
                                ['controller' => 'Dossiers', 'action' => 'view', $folder->id],
                                ['class' => 'search-link']
                            ) ?>
                            <?php if (!$isAdmin && $folder->username !== $username && !empty($groupMembers) && in_array($folder->username, $groupMembers)): ?>
                                <small style="color: #87CEEB; margin-left: 10px;">[Group File]</small>
                            <?php endif; ?>
                            
                            <div class="search-actions" style="display: inline-block; margin-left: 10px;">
                                <button class="action-btn info" onclick="showFolderInfo('<?= h($folder->name) ?>', '<?= h($folder->username) ?>', '<?= h($folder->path ?: 'Root') ?>', '<?= $folder->id ?>')">ℹ️</button>
                                
                                <?php if ($permissions['can_download'] || $isAdmin): ?>
                                    <?= $this->Html->link('📦', ['controller' => 'Dossiers', 'action' => 'download', $folder->id], ['class' => 'action-btn download', 'target' => '_blank']) ?>
                                <?php endif; ?>
                                
                                <?php if ($permissions['can_delete'] || $isAdmin): ?>
                                    <?= $this->Form->postLink('🗑️', ['controller' => 'Dossiers', 'action' => 'delete', $folder->id, 'dossier'], ['confirm' => 'Are you sure you want to delete this folder?', 'style' => 'color:red; text-decoration:none;', 'class' => 'action-btn delete']) ?>
                                <?php endif; ?>
                            </div>
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
                                    (($canReadAll || !empty($groupMembers)) ? ' - Owner: ' . h($file->username) : ''),
                                    $file->path,
                                    ['target' => '_blank', 'class' => 'search-link']
                                ) ?>
                            <?php else: ?>
                                <span class="search-link disabled">
                                    <?= h($file->name) ?> (Path: <?= ($file->path ?: 'Root') ?>)
                                    <?= ($canReadAll || !empty($groupMembers)) ? ' - Owner: ' . h($file->username) : '' ?>
                                    <small style="color: #ff6b6b; margin-left: 10px;">[Download Restricted]</small>
                                </span>
                            <?php endif; ?>
                            <?php if (!$isAdmin && $file->username !== $username && !empty($groupMembers) && in_array($file->username, $groupMembers)): ?>
                                <small style="color: #87CEEB; margin-left: 10px;">[Group File]</small>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($dossiers) || !empty($rootFiles) || !empty($fichiers)): ?>
        <h2 style="color: white; text-align: center; margin: 30px 0;">
            <?php if ($canReadAll): ?>
                <?= $isAdmin ? 'All Content (Admin View)' : 'All Content (Extended Access)' ?>
            <?php elseif (!empty($groupMembers)): ?>
                Your Content + Group Files
                <small style="display: block; font-size: 14px; color: #87CEEB; margin-top: 5px;">
                    Group members: <?= implode(', ', $groupMembers) ?>
                </small>
            <?php else: ?>
                Your Content
            <?php endif; ?>
        </h2>
        
        <?php if ($canReadAll): ?>
            <?php 
                $groupedContent = [];
                foreach ($dossiers as $dossier) {
                    $groupedContent[$dossier->username]['dossiers'][] = $dossier;
                }
                foreach ($rootFiles as $file) {
                    $groupedContent[$file->username]['files'][] = $file;
                }
                foreach ($fichiers as $fichier) {
                    $groupedContent[$fichier->username]['fichiers'][] = $fichier;
                }
            ?>
            
            <?php foreach ($groupedContent as $user => $userContent): ?>
                <div class="user-section">
                    <h3 class="user-header">
                        Content from: <?= h($user) ?> 
                        (<?= (count($userContent['dossiers'] ?? []) + count($userContent['files'] ?? []) + count($userContent['fichiers'] ?? [])) ?> items)
                    </h3>
                    <div class="dossier-container">
                        <?php if (!empty($userContent['dossiers'])): ?>
                            <?php foreach ($userContent['dossiers'] as $dossier): ?>
                                <?php
                                    $isFolder = ($dossier->size === null || $dossier->size === '');
                                    if ($isFolder) {
                                        $link = $this->Url->build(['controller' => 'Dossiers', 'action' => 'view', $dossier->id]);
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
                                    <?php if ($isFolder): ?>
                                        <div class="dossier-actions">
                                <button class="action-btn info" onclick="showFolderInfo('<?= h($dossier->name) ?>', '<?= h($user) ?>', '<?= h($dossier->path ?: 'Root') ?>', '<?= $dossier->id ?>')">ℹ️</button>
                                
                                <?php if ($permissions['can_download'] || $isAdmin): ?>
                                    <?= $this->Html->link('📦', ['controller' => 'Dossiers', 'action' => 'download', $dossier->id], ['class' => 'action-btn download', 'target' => '_blank']) ?>
                                <?php endif; ?>
                                
                                <?php if ($permissions['can_delete'] || $isAdmin): ?>
                                    <?= $this->Form->postLink('🗑️', 
                                        ['controller' => 'Dossiers', 'action' => 'delete', $dossier->id],
                                        ['confirm' => 'Are you sure you want to delete this folder and all its contents?', 'class' => 'action-btn delete'])
                                    ?>
                                <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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
                                                <a href="<?= h($file->path) ?>" target="_blank" class="action-btn download" title="Download">📦</a>
                                            <?php else: ?>
                                                <span class="action-btn download disabled" title="Download restricted">📦</span>
                                            <?php endif; ?>
                                            
                                            <button class="action-btn info" onclick="alert('Uploaded by: <?= h($file->username) ?>')" title="File Info">ℹ️</button>
                                            
                                            <?php if ($isAdmin || ($permissions['can_delete'] && ($file->username === $username || $canReadAll))): ?>
                                                <?= $this->Form->postLink('🗑️', 
                                                    ['controller' => 'Fichier', 'action' => 'delete', $file->id],
                                                    ['confirm' => 'Are you sure you want to delete this file?', 'class' => 'action-btn delete'])
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($userContent['fichiers'])): ?>
                            <?php foreach ($userContent['fichiers'] as $fichier): ?>
                                <div class="dossier-item file-item">
                                    <div class="file-actions">
                                        <?php if ($permissions['can_download'] || $isAdmin): ?>
                                            <a href="<?= h($fichier->path) ?>" target="_blank" title="Open <?= h($fichier->name) ?>" class="file-link">
                                                <img src="/app/webroot/img/f.png" alt="file icon" />
                                                <span><?= h($fichier->name) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="file-link disabled" title="Download restricted">
                                                <img src="/app/webroot/img/f.png" alt="file icon" style="opacity: 0.5;" />
                                                <span style="color: #ccc;"><?= h($fichier->name) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <small class="owner-tag"><?= h($user) ?></small>
                                        <small class="file-tag">Fichier</small>

                                        <div class="action-buttons">
                                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                                <a href="<?= h($fichier->path) ?>" target="_blank" class="action-btn download" title="Download">📦</a>
                                            <?php else: ?>
                                                <span class="action-btn download disabled" title="Download restricted">📦</span>
                                            <?php endif; ?>
                                            
                                            <button class="action-btn info" onclick="alert('Uploaded by: <?= h($fichier->username) ?>\nSize: <?= h($fichier->size ?? 'Unknown') ?>\nType: <?= h($fichier->type ?? 'Unknown') ?>')" title="File Info">ℹ️ Info</button>
                                            
                                            <?php if ($isAdmin || ($permissions['can_delete'] && ($fichier->username === $username || $canReadAll))): ?>
                                                <?= $this->Form->postLink('🗑️ Delete', 
                                                    ['controller' => 'Fichier', 'action' => 'delete', $fichier->id],
                                                    ['confirm' => 'Are you sure you want to delete this fichier?', 'class' => 'action-btn delete', 'title' => 'Delete'])
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
        <?php elseif (!empty($groupMembers)): ?>
            <?php 
                $groupedContent = [];
                foreach ($dossiers as $dossier) {
                    $groupedContent[$dossier->username]['dossiers'][] = $dossier;
                }
                foreach ($rootFiles as $file) {
                    $groupedContent[$file->username]['files'][] = $file;
                }
                foreach ($fichiers as $fichier) {
                    $groupedContent[$fichier->username]['fichiers'][] = $fichier;
                }
            ?>
            
            <?php foreach ($groupedContent as $user => $userContent): ?>
                <div class="user-section <?= $user === $username ? 'own-content' : 'group-content' ?>">
                    <h3 class="user-header">
                        <?php if ($user === $username): ?>
                            Your Content (<?= (count($userContent['dossiers'] ?? []) + count($userContent['files'] ?? []) + count($userContent['fichiers'] ?? [])) ?> items)
                        <?php else: ?>
                            Group Content from: <?= h($user) ?> 
                            (<?= (count($userContent['dossiers'] ?? []) + count($userContent['files'] ?? []) + count($userContent['fichiers'] ?? [])) ?> items)
                            <small class="group-indicator">👥 Group Member</small>
                        <?php endif; ?>
                    </h3>
                    <div class="dossier-container">
                        <?php if (!empty($userContent['dossiers'])): ?>
                            <?php foreach ($userContent['dossiers'] as $dossier): ?>
                                <?php
                                    $isFolder = ($dossier->size === null || $dossier->size === '');
                                    if ($isFolder) {
                                        $link = $this->Url->build(['controller' => 'Dossiers', 'action' => 'view', $dossier->id]);
                                        $icon = "/app/webroot/img/d.png";
                                    } else {
                                        $link = $this->Url->build($dossier->path);
                                        $icon = "/app/webroot/img/f.png";
                                    }
                                ?>
                                <div class="dossier-item <?= $user !== $username ? 'group-item' : '' ?>">
                                    <a href="<?= $link ?>" title="Open <?= h($dossier->name) ?>">
                                        <img src="<?= $icon ?>" alt="icon" />
                                        <span><?= h($dossier->name) ?></span>
                                        <?php if ($user !== $username): ?>
                                            <small class="owner-tag">by <?= h($user) ?></small>
                                            <small class="group-tag">👥 Group</small>
                                        <?php endif; ?>
                                    </a>
                                    <?php if ($isFolder): ?>
                                        <div class="dossier-actions">
                                            <button class="action-btn info" onclick="showFolderInfo('<?= h($dossier->name) ?>', '<?= h($user) ?>', '<?= h($dossier->path ?: 'Root') ?>', '<?= $dossier->id ?>')" title="Folder Info">ℹ️</button>
                                            
                                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                                <?= $this->Html->link('📦', ['controller' => 'Dossiers', 'action' => 'download', $dossier->id], ['class' => 'action-btn download', 'title' => 'Download Folder as ZIP', 'target' => '_blank']) ?>
                                            <?php endif; ?>
                                            
                                            <?php if ($isAdmin || ($permissions['can_delete'] && ($user === $username || in_array($user, $groupMembers)))): ?>
                                                <?= $this->Form->postLink('🗑️', 
                                                    ['controller' => 'Dossiers', 'action' => 'delete', $dossier->id],
                                                    ['confirm' => 'Are you sure you want to delete this folder and all its contents?', 'class' => 'action-btn delete', 'title' => 'Delete Folder'])
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($userContent['files'])): ?>
                            <?php foreach ($userContent['files'] as $file): ?>
                                <div class="dossier-item file-item <?= $user !== $username ? 'group-item' : '' ?>">
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
                                        <?php if ($user !== $username): ?>
                                            <small class="owner-tag">by <?= h($user) ?></small>
                                            <small class="group-tag">👥 Group</small>
                                        <?php else: ?>
                                            <small class="file-tag">Root File</small>
                                        <?php endif; ?>

                                        <div class="action-buttons">
                                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                            <a href="<?= h($file->path) ?>" target="_blank" class="action-btn download">📦</a>
                                        <?php else: ?>
                                            <span class="action-btn download disabled">📦</span>
                                        <?php endif; ?>
                                        
                                        <button class="action-btn info" onclick="alert('Uploaded by: <?= h($file->username) ?>')">ℹ️</button>
                                            
                                            <?php if ($isAdmin || ($permissions['can_delete'] && ($file->username === $username || $canReadAll || in_array($file->username, $groupMembers)))): ?>
                                                <?= $this->Form->postLink('🗑️', 
                                                    ['controller' => 'Fichier', 'action' => 'delete', $file->id],
                                                    ['confirm' => 'Are you sure you want to delete this file?', 'class' => 'action-btn delete'])
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($userContent['fichiers'])): ?>
                            <?php foreach ($userContent['fichiers'] as $fichier): ?>
                                <div class="dossier-item file-item <?= $user !== $username ? 'group-item' : '' ?>">
                                    <div class="file-actions">
                                        <?php if ($permissions['can_download'] || $isAdmin): ?>
                                            <a href="<?= h($fichier->path) ?>" target="_blank" title="Open <?= h($fichier->name) ?>" class="file-link">
                                                <img src="/app/webroot/img/f.png" alt="file icon" />
                                                <span><?= h($fichier->name) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="file-link disabled" title="Download restricted">
                                                <img src="/app/webroot/img/f.png" alt="file icon" style="opacity: 0.5;" />
                                                <span style="color: #ccc;"><?= h($fichier->name) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($user !== $username): ?>
                                            <small class="owner-tag">by <?= h($user) ?></small>
                                            <small class="group-tag">👥 Group</small>
                                        <?php else: ?>
                                            <small class="file-tag">Fichier</small>
                                        <?php endif; ?>

                                        <div class="action-buttons">
                                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                                <a href="<?= h($fichier->path) ?>" target="_blank" class="action-btn download" title="Download">📦 </a>
                                            <?php else: ?>
                                                <span class="action-btn download disabled" title="Download restricted">📦 </span>
                                            <?php endif; ?>
                                            
                                            <button class="action-btn info" onclick="alert('Uploaded by: <?= h($fichier->username) ?>\nSize: <?= h($fichier->size ?? 'Unknown') ?>\nType: <?= h($fichier->type ?? 'Unknown') ?>')" title="File Info">ℹ️ Info</button>
                                            
                                            <?php if ($isAdmin || ($permissions['can_delete'] && ($fichier->username === $username || $canReadAll || in_array($fichier->username, $groupMembers)))): ?>
                                                <?= $this->Form->postLink('🗑️', 
                                                    ['controller' => 'Fichier', 'action' => 'delete', $fichier->id],
                                                    ['confirm' => 'Are you sure you want to delete this fichier?', 'class' => 'action-btn delete', 'title' => 'Delete'])
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
            <div class="dossier-container">
                <?php foreach ($dossiers as $dossier): ?>
                    <?php
                        $isFolder = ($dossier->size === null || $dossier->size === '');
                        if ($isFolder) {
                            $link = $this->Url->build(['controller' => 'Dossiers', 'action' => 'view', $dossier->id]);
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
                        </a>
                        <?php if ($isFolder): ?>
                            <div class="dossier-actions">
                                <button class="action-btn info" onclick="showFolderInfo('<?= h($dossier->name) ?>', '<?= h($username) ?>', '<?= h($dossier->path ?: 'Root') ?>', '<?= $dossier->id ?>')">ℹ️</button>
                                
                                <?php if ($permissions['can_download'] || $isAdmin): ?>
                                    <?= $this->Html->link('📦', ['controller' => 'Dossiers', 'action' => 'download', $dossier->id], ['class' => 'action-btn download', 'target' => '_blank']) ?>
                                <?php endif; ?>
                                
                                <?php if ($isAdmin || ($permissions['can_delete'] && $dossier->username === $username)): ?>
                                    <?= $this->Form->postLink('🗑️', 
                                        ['controller' => 'Dossiers', 'action' => 'delete', $dossier->id],
                                        ['confirm' => 'Are you sure you want to delete this folder and all its contents?', 'class' => 'action-btn delete'])
                                    ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($rootFiles as $file): ?>
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
                            <small class="file-tag">Root File</small>
                            
                            <div class="action-buttons">
                                <?php if ($permissions['can_download'] || $isAdmin): ?>
                                    <a href="<?= h($file->path) ?>" target="_blank" class="action-btn download">📦</a>
                                <?php else: ?>
                                    <span class="action-btn download disabled">📦</span>
                                <?php endif; ?>
                                
                                <button class="action-btn info" onclick="alert('Uploaded by: <?= h($file->username) ?>')">ℹ️</button>
                                
                                <?php if ($isAdmin || ($permissions['can_delete'] && $file->username === $username)): ?>
                                    <?= $this->Form->postLink('🗑️', 
                                        ['controller' => 'Fichier', 'action' => 'delete', $file->id],
                                        ['confirm' => 'Are you sure you want to delete this file?', 'class' => 'action-btn delete'])
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($fichiers as $fichier): ?>
                    <div class="dossier-item file-item">
                        <div class="file-actions">
                            <?php if ($permissions['can_download'] || $isAdmin): ?>
                                <a href="<?= h($fichier->path) ?>" target="_blank" title="Open <?= h($fichier->name) ?>" class="file-link">
                                    <img src="/app/webroot/img/f.png" alt="file icon" />
                                    <span><?= h($fichier->name) ?></span>
                                </a>
                            <?php else: ?>
                                <div class="file-link disabled" title="Download restricted">
                                    <img src="/app/webroot/img/f.png" alt="file icon" style="opacity: 0.5;" />
                                    <span style="color: #ccc;"><?= h($fichier->name) ?></span>
                                </div>
                            <?php endif; ?>
                            <small class="file-tag">Fichier</small>
                            
                            <div class="action-buttons">
                                <?php if ($permissions['can_download'] || $isAdmin): ?>
                                    <a href="<?= h($fichier->path) ?>" target="_blank" class="action-btn download">📦</a>
                                <?php else: ?>
                                    <span class="action-btn download disabled">📦</span>
                                <?php endif; ?>
                                
                                <button class="action-btn info" onclick="alert('Uploaded by: <?= h($fichier->username) ?>\nSize: <?= h($fichier->size ?? 'Unknown') ?>\nType: <?= h($fichier->type ?? 'Unknown') ?>')">ℹ️</button>
                                
                                <?php if ($isAdmin || ($permissions['can_delete'] && $fichier->username === $username)): ?>
                                    <?= $this->Form->postLink('🗑️', 
                                        ['controller' => 'Fichier', 'action' => 'delete', $fichier->id],
                                        ['confirm' => 'Are you sure you want to delete this fichier?', 'class' => 'action-btn delete'])
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: white; text-align: center;">
            <?php if ($canReadAll): ?>
                No content found in the system.
            <?php elseif (!empty($groupMembers)): ?>
                No content found. You and your group members haven't uploaded any files yet!
            <?php else: ?>
                No content found. Start by creating your first folder or uploading a file!
            <?php endif; ?>
        </p>
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

function showDetailedFolderInfo(folderId) {
    fetch('/dossiers/info/' + folderId)
        .then(response => response.json())
        .then(data => {
            const info = `Folder Information:
            
Name: ${data.name}
Owner: ${data.owner}
Path: ${data.path}
Created: ${data.created}
Files Count: ${data.file_count}
Total Size: ${data.total_size}
Last Modified: ${data.modified}`;
            
            if (confirm(info + '\n\nClick OK to view folder contents or Cancel to close.')) {
                window.location.href = '/dossiers/view/' + folderId;
            }
        })
        .catch(error => {
            alert('Error loading folder information: ' + error.message);
        });
}
</script>

<style>
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

.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    padding: 2px 4px;
    border-radius: 3px;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.action-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

.action-btn.info {
    color: #4CAF50;
}

.action-btn.download {
    color: #2196F3;
}

.action-btn.delete {
    color: #f44336;
}

.action-btn.disabled {
    color: #666;
    cursor: not-allowed;
    opacity: 0.5;
}

.action-btn.disabled:hover {
    background-color: transparent;
    transform: none;
}

.search-actions {
    display: inline-flex;
    gap: 5px;
    align-items: center;
}

@media (max-width: 600px) {
    .dossier-actions {
        flex-direction: column;
        gap: 2px;
    }
    
    .action-btn {
        font-size: 14px;
        padding: 4px 6px;
    }
}
</style>
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
    position: fixed; 
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
    background-image: url('/app/webroot/img/s.png');
    background-size: 20px 20px;
    background-repeat: no-repeat;
    background-position: center;
    width: 30px;
    height: 30px;
    border: 2px solid #ccc;
    border-radius: 6px;
    background-color: rgba(255, 255, 255, 0.1);
    transition: background-color 0.3s ease;
    cursor: pointer;
}

.search-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
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

.upload-btn, .home-btn, .logout-btn, .help-btn, .admin-btn {
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

.help-btn {
    background-image: url('/app/webroot/img/h.png');
}

.admin-btn {
    background-image: url('/app/webroot/img/admin.png');
    animation: subtle-pulse 2s infinite;
}

.upload-btn:hover, .home-btn:hover, .logout-btn:hover, .help-btn:hover, .admin-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
}

.upload-btn:active, .home-btn:active, .logout-btn:active, .help-btn:active, .admin-btn:active {
    transform: scale(0.95);
    box-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
}

@keyframes subtle-pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(255, 107, 107, 0);
    }
}

.help-text, .admin-panel {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.content-container {
    padding: 0 40px;
}

.user-section {
    margin-bottom: 40px;
}

.user-header {
    color: white;
    background: rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
    backdrop-filter: blur(10px);
}

.search-results {
    list-style: none;
    padding: 0;
}

.search-results li {
    margin-bottom: 10px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
    backdrop-filter: blur(5px);
}

.search-link {
    color: #87CEEB;
    text-decoration: none;
    transition: color 0.3s ease;
}

.search-link:hover {
    color: white;
    text-decoration: underline;
}

.search-link.disabled {
    color: #999;
    cursor: not-allowed;
}

.button {
    background-color: #1d1d48;
    color: white;
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
    display: inline-block;
}

.button:hover {
    background-color: #111338;
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
    transition: all 0.3s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dossier-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(0,0,0,0.8);
}

.dossier-item img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 12px;
    opacity: 0.75;
    filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));
    transition: opacity 0.3s ease;
}

.dossier-item:hover img {
    opacity: 1;
}

.dossier-item span {
    display: block;
    color: white;
    font-size: 16px;
    font-weight: 600;
    word-break: break-word;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.owner-tag {
    display: block;
    color: #87CEEB;
    font-size: 12px;
    margin-top: 5px;
    font-weight: normal;
}

.file-item {
    border: 2px solid rgba(135, 206, 235, 0.3);
}

.file-tag {
    display: block;
    color: #87CEEB;
    font-size: 11px;
    margin-top: 3px;
    font-weight: normal;
    font-style: italic;
}

.dossier-item a {
    text-decoration: none;
}

.file-actions {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.file-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
}

.file-link.disabled {
    cursor: not-allowed;
}

.action-buttons {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.action-btn {
    background: rgba(0, 0, 0, 0.6);
    border: 1px solid #ccc;
    color: white;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
    width: 40px;
    height: 40px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.action-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    color: #999;
}

.action-btn.disabled:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: none;
}

.action-btn.delete {
    color: #ff4c4c;
}

.action-btn.delete:hover {
    background: rgba(255, 76, 76, 0.2);
}

.action-btn.download {
    color: #87CEEB;
}

.action-btn.download:hover {
    background: rgba(135, 206, 235, 0.2);
}

.action-btn.info {
    color: #ffff99;
}

.action-btn.info:hover {
    background: rgba(255, 255, 153, 0.2);
}

@media (max-width: 768px) {
    .header {
        flex-direction: column;
        gap: 15px;
        padding: 15px 20px;
    }
    
    .search-input {
        width: 250px;
    }
    
    .content-container {
        padding: 0 20px;
    }
    
    .dossier-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .search-input {
        width: 200px;
    }
    
    .dossier-container {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
    }
    
    .dossier-item {
        padding: 15px;
    }
    
    .dossier-item img {
        width: 60px;
        height: 60px;
    }
    
    .header-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
<script>
// Complete Admin Panel JavaScript - Fixed Version
window.currentDossierId = null;
let currentUsers = [];
let isLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Panel JavaScript initializing...');
    initializeEventListeners();
    initializeSearch();
    initializeFileActions();
    initializeResponsiveFeatures();
    console.log('Admin Panel JavaScript fully loaded');
});

function initializeEventListeners() {
    const helpButton = document.getElementById('helpButton');
    const helpText = document.getElementById('helpText');
    
    if (helpButton && helpText) {
        helpButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePanel(helpText, 'Help Panel');
        });
    }

    const adminButton = document.getElementById('adminButton');
    const adminPanel = document.getElementById('adminPanel');
    
    if (adminButton && adminPanel) {
        adminButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePanel(adminPanel, 'Admin Panel');
        });
    }

    const loadUsersBtn = document.getElementById('loadUsersBtn');
    if (loadUsersBtn) {
        loadUsersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loadUsers();
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#helpButton') && !e.target.closest('#helpText')) {
            const helpText = document.getElementById('helpText');
            if (helpText && helpText.style.display !== 'none') {
                closePanel(helpText);
            }
        }
        
        if (!e.target.closest('#adminButton') && !e.target.closest('#adminPanel')) {
            const adminPanel = document.getElementById('adminPanel');
            if (adminPanel && adminPanel.style.display !== 'none') {
                closePanel(adminPanel);
            }
        }
    });

    addRippleEffect();
    enhanceSearchForm();
}

function togglePanel(panel, panelName) {
    if (!panel) {
        console.error(`Panel not found: ${panelName}`);
        return;
    }
    
    const isVisible = panel.style.display !== 'none';
    
    if (isVisible) {
        closePanel(panel);
    } else {
        openPanel(panel);
    }
    
    console.log(`${panelName} ${isVisible ? 'closed' : 'opened'}`);
}

function openPanel(panel) {
    panel.style.display = 'block';
    panel.style.opacity = '0';
    panel.style.transform = 'translateY(-10px)';
    
    
    panel.offsetHeight;
    
    panel.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    panel.style.opacity = '1';
    panel.style.transform = 'translateY(0)';
}

function closePanel(panel) {
    panel.style.opacity = '0';
    panel.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        panel.style.display = 'none';
    }, 300);
}

async function loadUsers() {
    if (isLoading) {
        console.log('Already loading users...');
        showNotification('Already loading users, please wait...', 'warning');
        return;
    }

    const loadUsersBtn = document.getElementById('loadUsersBtn');
    const usersTableBody = document.getElementById('usersTableBody');
    const loadingIndicator = document.getElementById('loadingIndicator');

    if (!loadUsersBtn || !usersTableBody) {
        showNotification('Required elements not found on page', 'error');
        return;
    }

    
    isLoading = true;
    loadUsersBtn.disabled = true;
    loadUsersBtn.innerHTML = '<span style="margin-right: 5px;">⏳</span>Loading...';
    loadUsersBtn.style.opacity = '0.6';
    
    if (loadingIndicator) {
        loadingIndicator.style.display = 'block';
    }

    
    usersTableBody.innerHTML = `
        <tr>
            <td colspan="4" style="text-align: center; padding: 30px;">
                <div class="loading-spinner" style="margin: 0 auto 15px;"></div>
                <div style="color: #ccc;">Loading users from database...</div>
            </td>
        </tr>
    `;

    try {
        console.log('Fetching users from server...');
        
        const response = await fetch('/dossiers/get-users', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        console.log('Response received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok
        });

        if (!response.ok) {
            let errorMessage;
            try {
                const errorData = await response.json();
                errorMessage = errorData.error || errorData.message || `HTTP ${response.status}`;
            } catch {
                errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const responseText = await response.text();
            console.error('Non-JSON response received:', responseText);
            throw new Error('Server returned invalid response format');
        }

        const users = await response.json();
        console.log('Users data received:', users);

        if (users.error) {
            throw new Error(users.error);
        }

        if (!Array.isArray(users)) {
            console.error('Invalid users data:', users);
            throw new Error('Invalid response format - expected user array');
        }

      
        currentUsers = users;
        displayUsers(users);
        showNotification(`Successfully loaded ${users.length} users`, 'success');

    } catch (error) {
        console.error('Error loading users:', error);
        showNotification(`Failed to load users: ${error.message}`, 'error');

        usersTableBody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; color: #ff6b6b; padding: 30px;">
                    <div style="font-size: 24px; margin-bottom: 10px;">❌</div>
                    <div><strong>Error Loading Users</strong></div>
                    <div style="margin-top: 10px; color: #ccc; font-size: 14px;">${error.message}</div>
                    <div style="margin-top: 15px;">
                        <button onclick="loadUsers()" class="admin-action-btn primary" style="padding: 8px 16px; font-size: 12px;">
                            Try Again
                        </button>
                    </div>
                </td>
            </tr>
        `;
    } finally {
        isLoading = false;
        loadUsersBtn.disabled = false;
        loadUsersBtn.innerHTML = '<span style="margin-right: 5px;">👥</span>Refresh Users';
        loadUsersBtn.style.opacity = '1';
        
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }
}

async function updatePermission(userId, permission, value) {
    console.log(`Updating permission: userId=${userId}, permission=${permission}, value=${value}`);
    
    const user = currentUsers.find(u => u.id == userId);
    if (!user) {
        showNotification('User not found in current list', 'error');
        return;
    }

    const checkbox = document.querySelector(`input[data-user-id="${userId}"][data-permission="${permission}"]`);
    if (checkbox) {
        checkbox.disabled = true;
        checkbox.closest('.permission-toggle').classList.add('loading');
    }

    try {
        console.log('Sending permission update request...');
        
        const requestData = {
            user_id: parseInt(userId),
            permission: permission,
            value: Boolean(value)
        };
        
        console.log('Request data:', requestData);
        
        const response = await fetch('/dossiers/update-user-permission', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(requestData)
        });

        console.log('Permission update response:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok
        });

        if (!response.ok) {
            let errorMessage;
            try {
                const errorData = await response.json();
                errorMessage = errorData.error || errorData.message || `HTTP ${response.status}`;
            } catch {
                errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }

        const result = await response.json();
        console.log('Permission update result:', result);

        if (result.error) {
            throw new Error(result.error);
        }

        if (result.success) {
            user[permission] = value ? 1 : 0;
            
            const permissionName = permission.replace('_', ' ').toLowerCase();
            showNotification(`${permissionName} permission updated for ${user.username}`, 'success');
        } else {
            throw new Error('Update failed - no success confirmation received');
        }

    } catch (error) {
        console.error('Error updating permission:', error);
        showNotification(`Failed to update permission: ${error.message}`, 'error');

        if (checkbox) {
            checkbox.checked = !value;
        }
    } finally {
        if (checkbox) {
            checkbox.disabled = false;
            checkbox.closest('.permission-toggle').classList.remove('loading');
        }
    }
}

function displayUsers(users) {
    const usersTableBody = document.getElementById('usersTableBody');
    if (!usersTableBody) {
        console.error('Users table body not found');
        return;
    }
    
    usersTableBody.innerHTML = '';
    
    if (!users || users.length === 0) {
        usersTableBody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; color: #ccc; padding: 30px;">
                    <div style="font-size: 24px; margin-bottom: 10px;">👤</div>
                    <div>No users found in the system</div>
                    <div style="margin-top: 10px; font-size: 12px; color: #999;">
                        This might indicate a database connection issue
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    console.log(`Displaying ${users.length} users in table`);
    
    users.forEach((user, index) => {
        try {
            const row = createUserRow(user, index);
            usersTableBody.appendChild(row);
        } catch (error) {
            console.error(`Error creating row for user ${user.id}:`, error);
        }
    });
    
    showNotification(`Displaying ${users.length} users in permissions table`, 'info');
}

function createUserRow(user, index) {
    const row = document.createElement('tr');
    row.className = 'user-row';
    row.style.transition = 'background-color 0.3s ease';
    row.setAttribute('data-user-id', user.id);

    const readAccess = Boolean(user.read_access == 1 || user.read_access === true || user.read_access === '1');
    const deleteAccess = Boolean(user.delete_access == 1 || user.delete_access === true || user.delete_access === '1');
    const downloadAccess = Boolean(user.download_access == 1 || user.download_access === true || user.download_access === '1');

    const isAdmin = user.role === 'admin';
    const userColor = isAdmin ? '#ff6b6b' : 'white';
    const roleIcon = isAdmin ? '👑' : '👤';

    row.innerHTML = `
        <td style="font-weight: bold; color: ${userColor};">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">${roleIcon}</span>
                <div>
                    <div>${escapeHtml(user.username || 'Unknown User')}</div>
                    <small style="display: block; color: #ccc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        ${user.role || 'user'}
                    </small>
                    ${user.email ? `<small style="display: block; color: #aaa; font-size: 10px; margin-top: 2px;">${escapeHtml(user.email)}</small>` : ''}
                </div>
            </div>
        </td>
        <td style="text-align: center;">
            <label class="permission-toggle" title="Toggle read access">
                <input type="checkbox" ${readAccess ? 'checked' : ''} 
                       data-user-id="${user.id}" 
                       data-permission="read_access"
                       onchange="updatePermission(${user.id}, 'read_access', this.checked)">
                <span class="toggle-slider"></span>
            </label>
        </td>
        <td style="text-align: center;">
            <label class="permission-toggle" title="Toggle delete access">
                <input type="checkbox" ${deleteAccess ? 'checked' : ''} 
                       data-user-id="${user.id}" 
                       data-permission="delete_access"
                       onchange="updatePermission(${user.id}, 'delete_access', this.checked)">
                <span class="toggle-slider"></span>
            </label>
        </td>
        <td style="text-align: center;">
            <label class="permission-toggle" title="Toggle download access">
                <input type="checkbox" ${downloadAccess ? 'checked' : ''} 
                       data-user-id="${user.id}" 
                       data-permission="download_access"
                       onchange="updatePermission(${user.id}, 'download_access', this.checked)">
                <span class="toggle-slider"></span>
            </label>
        </td>
    `;

    row.addEventListener('mouseenter', function () {
        this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        this.style.transform = 'translateX(5px)';
    });

    row.addEventListener('mouseleave', function () {
        this.style.backgroundColor = 'transparent';
        this.style.transform = 'translateX(0)';
    });

    return row;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        max-width: 350px;
        word-wrap: break-word;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        font-size: 14px;
        line-height: 1.4;
        border-left: 4px solid rgba(255,255,255,0.3);
    `;
    
    switch (type) {
        case 'success':
            notification.style.background = 'linear-gradient(45deg, #28a745, #20c997)';
            notification.innerHTML = '<span style="margin-right: 8px;">✅</span>' + message;
            break;
        case 'warning':
            notification.style.background = 'linear-gradient(45deg, #ffc107, #fd7e14)';
            notification.innerHTML = '<span style="margin-right: 8px;">⚠️</span>' + message;
            break;
        case 'error':
            notification.style.background = 'linear-gradient(45deg, #dc3545, #e83e8c)';
            notification.innerHTML = '<span style="margin-right: 8px;">❌</span>' + message;
            break;
        default:
            notification.style.background = 'linear-gradient(45deg, #007bff, #6f42c1)';
            notification.innerHTML = '<span style="margin-right: 8px;">ℹ️</span>' + message;
    }
    
    document.body.appendChild(notification);
    
    requestAnimationFrame(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    });
    
    const duration = Math.max(4000, message.length * 100);
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, duration);
}

function addRippleEffect() {
    const buttons = document.querySelectorAll('.upload-btn, .home-btn, .logout-btn, .help-btn, .admin-btn, .search-btn, .admin-action-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.disabled) return;
            
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.6);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
                z-index: 1;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.remove();
                }
            }, 600);
        });
    });
}

function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    
    if (!searchInput || !searchForm) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                console.log('Search query:', query);
            }, 300);
        }
    });
    
    searchForm.addEventListener('submit', function(e) {
        const query = searchInput.value.trim();
        if (!query) {
            e.preventDefault();
            showNotification('Please enter a search term', 'warning');
            searchInput.focus();
            return false;
        }
        
        showNotification(`Searching for: "${query}"`, 'info');
    });
    
    document.addEventListener('keydown', function(e) {

        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.blur();
        }
    });
}

function enhanceSearchForm() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;
    
    const originalPlaceholder = searchInput.placeholder;
    let placeholderIndex = 0;
    const placeholders = [
        originalPlaceholder,
        'Try searching for file names...',
        'Search by folder name...',
        'Find your documents quickly...',
        'Use keywords to narrow results...'
    ];
    
    const rotatePlaceholder = setInterval(() => {
        if (document.activeElement !== searchInput && !searchInput.value) {
            placeholderIndex = (placeholderIndex + 1) % placeholders.length;
            searchInput.placeholder = placeholders[placeholderIndex];
        }
    }, 3000);
    
    searchInput.addEventListener('focus', function() {
        this.placeholder = originalPlaceholder;
    });
    
    window.addEventListener('beforeunload', () => {
        clearInterval(rotatePlaceholder);
    });
}

function initializeFileActions() {
    const downloadLinks = document.querySelectorAll('.action-btn.download, a[target="_blank"]');
    
    downloadLinks.forEach(link => {
        link.addEventListener('click', function() {
            const fileName = this.closest('.dossier-item')?.querySelector('span')?.textContent || 'Unknown file';
            console.log(`Download initiated: ${fileName}`);
            showNotification(`Downloading ${fileName}...`, 'info');
        });
    });
    
    const deleteLinks = document.querySelectorAll('.action-btn.delete');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const fileName = this.closest('.dossier-item')?.querySelector('span')?.textContent || 'this file';
            
            const confirmDelete = confirm(
                `Are you sure you want to delete "${fileName}"?\n\n` +
                'This action cannot be undone and will permanently remove the file from the system.'
            );
            
            if (!confirmDelete) {
                e.preventDefault();
                showNotification('Delete operation cancelled', 'info');
            } else {
                showNotification(`Deleting ${fileName}...`, 'warning');
            }
        });
    });
}

function initializeResponsiveFeatures() {
    function handleResize() {
        const isMobile = window.innerWidth < 768;
        const isTablet = window.innerWidth < 1024;
        
        const adminStats = document.querySelector('.admin-stats');
        if (adminStats) {
            if (isMobile) {
                adminStats.style.gridTemplateColumns = '1fr';
            } else if (isTablet) {
                adminStats.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else {
                adminStats.style.gridTemplateColumns = 'repeat(auto-fit, minmax(150px, 1fr))';
            }
        }
        
        const permissionsTable = document.querySelector('.permissions-table');
        if (permissionsTable && isMobile) {
            permissionsTable.style.fontSize = '12px';
        }
    }
    
    handleResize();
    window.addEventListener('resize', handleResize);
    
    console.log('Responsive features initialized');
}

window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    showNotification('An unexpected error occurred. Please refresh the page.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showNotification('A network error occurred. Please check your connection.', 'error');
});

if (!document.querySelector('#admin-panel-animations-css')) {
    const style = document.createElement('style');
    style.id = 'admin-panel-animations-css';
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .user-row {
            animation: fadeInUp 0.3s ease-out;
        }
        
        .loading-spinner {
            width: 30px;
            height: 30px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-left: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}

console.log('Admin Panel JavaScript initialization complete');
</script>

