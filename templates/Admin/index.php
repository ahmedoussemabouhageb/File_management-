<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'Administration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="header">
    <h1>Panneau d'Administration</h1>
    <div class="header-buttons">
        <a href="<?= $this->Url->build(['controller' => 'Dossiers', 'action' => 'index']) ?>" class="home-btn" title="Retour à la page principale">
            <i class="fas fa-home"></i>
        </a>
        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="logout-btn" title="Déconnexion">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<div class="admin-container">
    <div class="dashboard">
        <h2><i class="fas fa-chart-pie"></i> Tableau de Bord</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Dossiers</h3>
                    <span class="stat-number"><?= $totalFolders ?></span>
                </div>
            </div>

            <div class="stat-card clickable" onclick="openUsersModal()">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Utilisateurs Actifs</h3>
                    <span class="stat-number"><?= $activeUsers ?></span>
                    <div class="click-hint">Cliquez pour gérer</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-content">
                    <h3>Groupes</h3>
                    <span class="stat-number"><?= $totalGroups ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Fichiers</h3>
                    <span class="stat-number"><?= $totalFiles ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de gestion des utilisateurs -->
    <div id="usersModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-users-cog"></i> Gestion des Comptes Utilisateurs</h2>
                <span class="close" onclick="closeUsersModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="users-management-list">
                    <?php foreach ($users as $user): ?>
                    <div class="user-management-card">
                        <div class="user-management-info">
                            <div class="user-management-avatar">
                                <?= strtoupper(substr($user->username, 0, 1)) ?>
                            </div>
                            <div class="user-management-details">
                                <div class="user-management-name"><?= h($user->username) ?></div>
                                <div class="user-management-email"><?= h($user->email ?? $user->username . '@example.com') ?></div>
                                <div class="user-management-role <?= $user->username === 'farah' ? 'admin' : 'user' ?>">
                                    <?= $user->username === 'farah' ? 'Administrateur' : 'Utilisateur' ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($user->username !== 'farah'): ?>
                        <div class="user-management-actions">
                            <button class="action-btn edit-btn" onclick="openEditUserModal(<?= $user->id ?>)" title="Modifier l'utilisateur">
                                <i class="fas fa-edit"></i>
                                <span>Modifier</span>
                            </button>
                            <button class="action-btn delete-btn" onclick="confirmDeleteUser(<?= $user->id ?>, '<?= h($user->username) ?>')" title="Supprimer le compte">
                                <i class="fas fa-trash-alt"></i>
                                <span>Supprimer</span>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="user-management-actions">
                            <span class="admin-badge"><i class="fas fa-shield-alt"></i> Compte Protégé</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour modifier un utilisateur (NOUVEAU) -->
    <div id="editUserModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Modifier l'Utilisateur</h2>
                <span class="close" onclick="closeEditUserModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm" onsubmit="submitEditUser(event)">
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <div class="form-sections">
                        <!-- Section Identifiants -->
                        <div class="form-section">
                            <h3><i class="fas fa-key"></i> Identifiants de Connexion</h3>
                            <div class="form-group">
                                <label for="edit_username">
                                    <i class="fas fa-user"></i> Nom d'utilisateur
                                </label>
                                <input type="text" name="username" id="edit_username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_password">
                                    <i class="fas fa-lock"></i> Nouveau mot de passe
                                </label>
                                <input type="password" name="password" id="edit_password" class="form-control" placeholder="Laisser vide pour ne pas modifier">
                                <small class="form-hint">Laissez ce champ vide si vous ne souhaitez pas changer le mot de passe</small>
                            </div>
                        </div>

                        <!-- Section Informations Personnelles -->
                        <div class="form-section">
                            <h3><i class="fas fa-id-card"></i> Informations Personnelles</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_nom">
                                        <i class="fas fa-user"></i> Nom
                                    </label>
                                    <input type="text" name="nom" id="edit_nom" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="edit_prenom">
                                        <i class="fas fa-user"></i> Prénom
                                    </label>
                                    <input type="text" name="prenom" id="edit_prenom" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit_email">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_num">
                                    <i class="fas fa-phone"></i> Numéro de téléphone
                                </label>
                                <input type="tel" name="num" id="edit_num" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_adresse">
                                    <i class="fas fa-map-marker-alt"></i> Adresse
                                </label>
                                <textarea name="adresse" id="edit_adresse" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <button type="button" class="btn-secondary" onclick="closeEditUserModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header danger">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirmer la Suppression</h2>
                <span class="close" onclick="closeDeleteUserModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="warning-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Êtes-vous sûr de vouloir supprimer le compte de <strong id="delete_username"></strong> ?</p>
                    <p class="warning-detail">Cette action est irréversible et supprimera également :</p>
                    <ul class="warning-list">
                        <li><i class="fas fa-folder"></i> Tous les dossiers de l'utilisateur</li>
                        <li><i class="fas fa-file"></i> Tous les fichiers de l'utilisateur</li>
                        <li><i class="fas fa-users"></i> L'utilisateur de tous les groupes</li>
                    </ul>
                </div>
                <?= $this->Form->create(null, ['id' => 'deleteUserForm', 'url' => ['action' => 'deleteUser']]) ?>
                    <?= $this->Form->hidden('user_id', ['id' => 'delete_user_id']) ?>
                    <div class="modal-actions">
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash-alt"></i> Supprimer Définitivement
                        </button>
                        <button type="button" class="btn-secondary" onclick="closeDeleteUserModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>

    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('users')">
            <i class="fas fa-user-cog"></i> Gestion des Utilisateurs
        </button>
        <button class="tab-btn" onclick="showTab('groups')">
            <i class="fas fa-users-cog"></i> Gestion des Groupes
        </button>
    </div>

    <div id="users-tab" class="tab-content active">
        <div class="user-management">
            <h2><i class="fas fa-user-cog"></i> Gestion des Utilisateurs</h2>

            <div class="permissions-section">
                <h3><i class="fas fa-shield-alt"></i> Contrôle d'Accès</h3>
                <div class="permission-info">
                    <div class="info-item">
                        <i class="fas fa-eye text-success"></i>
                        <span><strong>Lecture :</strong> L'utilisateur peut voir les fichiers et dossiers</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-trash text-warning"></i>
                        <span><strong>Suppression :</strong> L'utilisateur peut supprimer son propre contenu</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-download text-info"></i>
                        <span><strong>Téléchargement :</strong> L'utilisateur peut télécharger les fichiers</span>
                    </div>
                </div>

                <?= $this->Form->create(null, ['id' => 'permissionsForm', 'url' => ['action' => 'updateAllPermissions']]) ?>
                    <div class="users-table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Utilisateur</th>
                                    <th><i class="fas fa-eye text-success"></i> Lecture</th>
                                    <th><i class="fas fa-trash text-warning"></i> Suppression</th>
                                    <th><i class="fas fa-download text-info"></i> Téléchargement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                <?= strtoupper(substr($user->username, 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: black;"><?= h($user->username) ?></div>
                                                <div style="color: #6c757d; font-size: 0.9rem;"><?= h($user->email ?? $user->username . '@example.com') ?></div>
                                                <div style="color: <?= $user->username === 'farah' ? '#e74c3c' : '#28a745' ?>; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                                                    <?= $user->username === 'farah' ? 'Administrateur' : 'Utilisateur' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="switch">
                                            <?= $this->Form->hidden("permissions.{$user->id}.can_read", ['value' => '0']) ?>
                                            <?= $this->Form->checkbox("permissions.{$user->id}.can_read", [
                                                'checked' => (bool)$user->can_read,
                                                'value' => '1',
                                                'hiddenField' => false,
                                                'data-user-id' => $user->id,
                                                'data-permission' => 'can_read'
                                            ]) ?>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="switch">
                                            <?= $this->Form->hidden("permissions.{$user->id}.can_delete", ['value' => '0']) ?>
                                            <?= $this->Form->checkbox("permissions.{$user->id}.can_delete", [
                                                'checked' => (bool)$user->can_delete,
                                                'value' => '1',
                                                'hiddenField' => false,
                                                'data-user-id' => $user->id,
                                                'data-permission' => 'can_delete'
                                            ]) ?>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="switch">
                                            <?= $this->Form->hidden("permissions.{$user->id}.can_download", ['value' => '0']) ?>
                                            <?= $this->Form->checkbox("permissions.{$user->id}.can_download", [
                                                'checked' => (bool)$user->can_download,
                                                'value' => '1',
                                                'hiddenField' => false,
                                                'data-user-id' => $user->id,
                                                'data-permission' => 'can_download'
                                            ]) ?>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="save-permissions-section">
                        <button type="submit" class="btn-primary save-permissions-btn">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <button type="button" class="btn-secondary reset-permissions-btn" onclick="location.reload()">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>

    <div id="groups-tab" class="tab-content">
        <div class="group-management">
            <h2><i class="fas fa-users-cog"></i> Gestion des Groupes</h2>

            <div class="create-group-section">
                <h3><i class="fas fa-plus-circle"></i> Créer un Nouveau Groupe</h3>
                <?= $this->Form->create(null, ['id' => 'createGroupForm', 'url' => ['action' => 'createGroup']]) ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="group_name">
                                <i class="fas fa-tag"></i> Nom du Groupe
                            </label>
                            <?= $this->Form->control('group_name', [
                                'type' => 'text',
                                'placeholder' => 'Entrez le nom du groupe',
                                'required' => true,
                                'label' => false,
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-users"></i> Sélectionner les Utilisateurs
                        </label>
                        <div class="users-selection">
                            <?php foreach ($users as $user): ?>
                            <div class="user-item">
                                <label class="user-checkbox">
                                    <?= $this->Form->checkbox("selected_users[]", [
                                        'value' => $user->id,
                                        'hiddenField' => false,
                                        'data-user-name' => $user->username
                                    ]) ?>
                                    <div class="user-card">
                                        <div class="user-avatar"><?= strtoupper(substr($user->username, 0, 1)) ?></div>
                                        <div class="user-info">
                                            <div class="user-name"><?= h($user->username) ?></div>
                                            <div class="user-role <?= $user->username === 'farah' ? 'admin' : 'user' ?>">
                                                <?= $user->username === 'farah' ? 'Administrateur' : 'Utilisateur' ?>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="group-permissions-section" id="groupPermissionsSection" style="display: none;">
                        <h4><i class="fas fa-shield-alt"></i> Permissions pour les Utilisateurs Sélectionnés</h4>
                        <div id="selectedUsersPermissions"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i> Créer le Groupe
                        </button>
                        <button type="button" class="btn-secondary" onclick="resetGroupForm()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                <?= $this->Form->end() ?>
            </div>

            <div class="existing-groups-section">
                <h3><i class="fas fa-list"></i> Groupes Existants</h3>
                <div class="groups-grid">
                    <?php if (empty($groups)): ?>
                        <div class="no-groups-message">
                            <i class="fas fa-info-circle"></i>
                            <p>Aucun groupe n'a été créé pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groups as $group): ?>
                        <div class="group-card">
                            <div class="group-header">
                                <h4><i class="fas fa-users"></i> <?= h($group->name) ?></h4>
                                <div class="group-actions">
                                    <?= $this->Form->create(null, [
                                        'url' => ['action' => 'deleteGroup'],
                                        'style' => 'display: inline-block; margin: 0;',
                                        'onsubmit' => 'return confirm("Êtes-vous sûr de vouloir supprimer ce groupe ?");'
                                    ]) ?>
                                        <?= $this->Form->hidden('group_id', ['value' => $group->id]) ?>
                                        <button type="submit" class="btn-delete" title="Supprimer le groupe">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?= $this->Form->end() ?>
                                </div>
                            </div>
                            <div class="group-members">
                                <?php
                                $hasMembers = false;
                                for ($i = 1; $i <= 100; $i++) {
                                    $userColumn = "user_{$i}";
                                    $permissionColumn = "user_{$i}_permissions";
                                    if (!empty($group->$userColumn)) {
                                        $hasMembers = true;
                                        $permissions = json_decode($group->$permissionColumn, true) ?? [];
                                        $permissionBadges = [];
                                        if (!empty($permissions['can_read'])) $permissionBadges[] = '<span class="permission-badge read">Lecture</span>';
                                        if (!empty($permissions['can_delete'])) $permissionBadges[] = '<span class="permission-badge delete">Suppression</span>';
                                        if (!empty($permissions['can_download'])) $permissionBadges[] = '<span class="permission-badge download">Téléchargement</span>';
                                        ?>
                                        <div class="member">
                                            <div class="member-avatar"><?= strtoupper(substr($group->$userColumn, 0, 1)) ?></div>
                                            <div class="member-info">
                                                <div class="member-name"><?= h($group->$userColumn) ?></div>
                                                <div class="member-permissions">
                                                    <?= implode('', $permissionBadges) ?>
                                                    <?php if (empty($permissionBadges)): ?>
                                                        <span class="no-permissions">Aucune permission</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                if (!$hasMembers): ?>
                                    <div class="no-members">
                                        <i class="fas fa-user-slash"></i>
                                        <span>Aucun membre dans ce groupe</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Fonction pour charger et ouvrir le modal d'édition
async function openEditUserModal(userId) {
    try {
        // Récupérer le token CSRF depuis un meta tag ou input hidden
        const csrfToken = document.querySelector('input[name="_csrfToken"]')?.value ||
                         document.querySelector('meta[name="csrfToken"]')?.content || '';

        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('_csrfToken', csrfToken);

        const response = await fetch('<?= $this->Url->build(['action' => 'getUserData']) ?>', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('edit_user_id').value = result.user.id;
            document.getElementById('edit_username').value = result.user.username;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_nom').value = result.user.nom || '';
            document.getElementById('edit_prenom').value = result.user.prenom || '';
            document.getElementById('edit_email').value = result.user.email || '';
            document.getElementById('edit_num').value = result.user.num || '';
            document.getElementById('edit_adresse').value = result.user.adresse || '';

            document.getElementById('editUserModal').classList.add('show');
        } else {
            alert('Erreur lors du chargement: ' + (result.message || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Erreur complète:', error);
        alert('Erreur lors du chargement des données utilisateur. Vérifiez la console pour plus de détails.');
    }
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
}

// Fonction pour soumettre le formulaire d'édition
async function submitEditUser(event) {
    event.preventDefault();

    // Récupérer le token CSRF
    const csrfToken = document.querySelector('input[name="_csrfToken"]')?.value ||
                     document.querySelector('meta[name="csrfToken"]')?.content || '';

    const formData = new FormData(event.target);
    formData.append('_csrfToken', csrfToken);

    try {
        const response = await fetch('<?= $this->Url->build(['action' => 'editUser']) ?>', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Utilisateur modifié avec succès!');
            closeEditUserModal();
            location.reload();
        } else {
            alert('Erreur lors de la modification: ' + (result.message || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Erreur complète:', error);
        alert('Erreur lors de la modification de l\'utilisateur. Vérifiez la console pour plus de détails.');
    }
}

// Fonctions pour les autres modals
function openUsersModal() {
    document.getElementById('usersModal').classList.add('show');
}

function closeUsersModal() {
    document.getElementById('usersModal').classList.remove('show');
}

function confirmDeleteUser(userId, username) {
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_username').textContent = username;
    document.getElementById('deleteUserModal').classList.add('show');
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').classList.remove('show');
}

// Fonction pour changer d'onglet
function showTab(tabName) {
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));

    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => btn.classList.remove('active'));

    document.getElementById(tabName + '-tab').classList.add('active');
    event.currentTarget.classList.add('active');
}

// Fonction pour réinitialiser le formulaire de groupe
function resetGroupForm() {
    document.getElementById('createGroupForm').reset();
    document.getElementById('groupPermissionsSection').style.display = 'none';
    document.getElementById('selectedUsersPermissions').innerHTML = '';
}

// Fermer le modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    });
}

// Gestion de la sélection des utilisateurs pour les groupes
document.addEventListener('DOMContentLoaded', function() {
    const userCheckboxes = document.querySelectorAll('.user-checkbox input[type="checkbox"]');

    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateGroupPermissionsSection();
        });
    });
});

function updateGroupPermissionsSection() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox input[type="checkbox"]:checked');
    const permissionsSection = document.getElementById('groupPermissionsSection');
    const permissionsContainer = document.getElementById('selectedUsersPermissions');

    if (selectedCheckboxes.length > 0) {
        permissionsSection.style.display = 'block';
        permissionsContainer.innerHTML = '';

        selectedCheckboxes.forEach(checkbox => {
            const userName = checkbox.getAttribute('data-user-name');
            const userId = checkbox.value;

            const userPermissionDiv = document.createElement('div');
            userPermissionDiv.className = 'user-group-permissions';
            userPermissionDiv.innerHTML = `
                <h5><i class="fas fa-user"></i> ${userName}</h5>
                <div class="permission-toggles">
                    <label class="switch">
                        <input type="checkbox" name="group_permissions[${userId}][can_read]" value="1">
                        <span class="slider"></span>
                        <span>Lecture</span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="group_permissions[${userId}][can_delete]" value="1">
                        <span class="slider"></span>
                        <span>Suppression</span>
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="group_permissions[${userId}][can_download]" value="1">
                        <span class="slider"></span>
                        <span>Téléchargement</span>
                    </label>
                </div>
            `;

            permissionsContainer.appendChild(userPermissionDiv);
        });
    } else {
        permissionsSection.style.display = 'none';
        permissionsContainer.innerHTML = '';
    }
}
</script>

<style>
    body, html {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #E0D3CC, #E0C5BC);
        background-attachment: fixed;
        min-height: 100vh;
        color: #000;
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

    .header h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
        color: white;
    }

    .header-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-buttons a {
        background-size: 20px 20px;
        background-repeat: no-repeat;
        background-position: center;
        width: 30px;
        height: 30px;
        border: 2px solid #ccc;
        border-radius: 6px;
        background-color: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        display: block;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .home-btn:hover, .logout-btn:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .admin-container {
        padding: 30px;
        max-width: 1200px;
        margin: 80px auto 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dashboard h2,
    .user-management h2,
    .group-management h2 {
        color: black;
        font-size: 1.6rem;
        margin-bottom: 25px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dashboard h2 i,
    .user-management h2 i,
    .group-management h2 i {
        color: #000000ff;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        color: #000;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    .stat-card.clickable {
        cursor: pointer;
        position: relative;
    }

    .stat-card.clickable:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(135, 206, 235, 0.5);
    }

    .stat-card.clickable .click-hint {
        font-size: 0.75rem;
        color: rgba(0, 0, 0, 0.6);
        margin-top: 5px;
        font-style: italic;
    }

    .stat-card.clickable * {
        cursor: pointer;
    }

    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        color: #000000ff;
    }

    .stat-content h3 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        opacity: 0.9;
        color: black;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: black;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
    }

    .modal.show {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content {
        background: linear-gradient(135deg, rgba(224, 211, 204, 0.95), rgba(224, 197, 188, 0.95));
        margin: 3% auto;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        width: 90%;
        max-width: 900px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        animation: slideIn 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .modal-content.modal-small {
        max-width: 500px;
    }

    .modal-content.modal-large {
        max-width: 800px;
    }

    .modal-header {
        background: linear-gradient(135deg, #000000, #C4ADAD);
        color: white;
        padding: 20px 30px;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header.danger {
        background: linear-gradient(135deg, #c0392b, #e74c3c);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
        padding: 0;
        color: white;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close:hover {
        color: #f1f1f1;
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 30px;
    }

    /* NOUVEAUX STYLES pour édition complète */
    .form-sections {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .form-section {
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-section h3 {
        color: #000;
        font-size: 1.2rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(135, 206, 235, 0.3);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section h3 i {
        color: #87CEEB;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .form-hint {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
        color: rgba(0, 0, 0, 0.6);
        font-style: italic;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .users-management-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        max-height: 500px;
        overflow-y: auto;
    }

    .user-management-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .user-management-card:hover {
        background: rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transform: translateX(5px);
    }

    .user-management-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-management-avatar {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.3rem;
    }

    .user-management-details {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .user-management-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: black;
    }

    .user-management-email {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .user-management-role {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 10px;
        display: inline-block;
        width: fit-content;
    }

    .user-management-role.admin {
        background-color: #e74c3c;
        color: white;
    }

    .user-management-role.user {
        background-color: #28a745;
        color: white;
    }

    .user-management-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .action-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .edit-btn {
        background: rgba(52, 152, 219, 0.8);
        color: white;
    }

    .edit-btn:hover {
        background: rgba(52, 152, 219, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4);
    }

    .delete-btn {
        background: rgba(231, 76, 60, 0.8);
        color: white;
    }

    .delete-btn:hover {
        background: rgba(231, 76, 60, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.4);
    }

    .admin-badge {
        background: rgba(255, 215, 0, 0.3);
        color: #000;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .warning-message {
        text-align: center;
        padding: 20px;
    }

    .warning-message i {
        font-size: 3rem;
        color: #e74c3c;
        margin-bottom: 15px;
    }

    .warning-message p {
        font-size: 1.1rem;
        color: #000;
        margin-bottom: 10px;
    }

    .warning-message strong {
        color: #e74c3c;
        font-weight: 700;
    }

    .warning-detail {
        font-weight: 600;
        margin-top: 15px;
    }

    .warning-list {
        list-style: none;
        padding: 0;
        margin-top: 15px;
        text-align: left;
    }

    .warning-list li {
        padding: 8px 15px;
        margin: 5px 0;
        background: rgba(231, 76, 60, 0.1);
        border-left: 3px solid #e74c3c;
        border-radius: 5px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #000;
    }

    .warning-list li i {
        color: #e74c3c;
        font-size: 1.1rem;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 25px;
    }

    .btn-danger {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        background: rgba(231, 76, 60, 0.9);
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background: rgba(231, 76, 60, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .tab-navigation {
        display: flex;
        margin-bottom: 30px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }

    .tab-btn {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
        padding: 12px 25px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        color: black;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        transition: background-color 0.3s ease, color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(5px);
    }

    .tab-btn:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .tab-btn.active {
        background-color: rgba(135, 206, 235, 0.3);
        color: #000;
        border-bottom: 2px solid #87CEEB;
    }

    .tab-content {
        display: none;
        padding-top: 20px;
    }

    .tab-content.active {
        display: block;
    }

    .permissions-section h3,
    .create-group-section h3,
    .existing-groups-section h3 {
        color: black;
        font-size: 1.4rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .permission-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
        padding: 15px;
        background: rgba(135, 206, 235, 0.1);
        border-left: 5px solid #010303ff;
        border-radius: 8px;
        backdrop-filter: blur(5px);
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        color: black;
    }

    .info-item i {
        font-size: 1.2rem;
    }

    .text-success {
        color: #28a745;
    }

    .text-warning {
        color: #ffc107;
    }

    .text-info {
        color: #17a2b8;
    }

    .users-table-container {
        overflow-x: auto;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .users-table th,
    .users-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .users-table th {
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.9rem;
        color: black;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .users-table tbody tr {
        color: black;
    }

    .users-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 45px;
        height: 25px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.3);
        transition: .4s;
        border-radius: 25px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 17px;
        width: 17px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #610404e7;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #000000ff;
    }

    input:checked + .slider:before {
        transform: translateX(20px);
    }

    .save-permissions-section {
        text-align: right;
        margin-top: 20px;
    }

    .btn-primary,
    .btn-secondary {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        backdrop-filter: blur(5px);
    }

    .btn-primary {
        background: rgba(136, 0, 0, 0.8);
        color: white;
    }

    .btn-primary:hover {
        background: rgba(40, 167, 69, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-secondary {
        background: rgba(108, 117, 125, 0.8);
        color: white;
        margin-left: 10px;
    }

    .btn-secondary:hover {
        background: rgba(108, 117, 125, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: black;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.1);
        color: black;
        backdrop-filter: blur(5px);
    }

    .form-control:focus {
        outline: none;
        border-color: #000000ff;
        box-shadow: 0 0 0 2px rgba(135, 206, 235, 0.3);
    }

    .form-control::placeholder {
        color: rgba(0, 0, 0, 0.6);
    }

    .users-selection {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        padding: 15px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        backdrop-filter: blur(5px);
    }

    .user-item {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .user-item:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    .user-checkbox {
        display: flex;
        align-items: center;
        padding: 10px;
        cursor: pointer;
        color: black;
    }

    .user-checkbox input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.2);
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .user-info .user-name {
        font-weight: 600;
        color: black;
    }

    .user-info .user-role {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 2px 6px;
        border-radius: 10px;
        display: inline-block;
        margin-top: 2px;
    }

    .user-info .user-role.admin {
        background-color: #e74c3c;
        color: white;
    }

    .user-info .user-role.user {
        background-color: #28a745;
        color: white;
    }

    .group-permissions-section {
        margin-top: 30px;
        padding: 20px;
        background: rgba(135, 206, 235, 0.1);
        border: 1px solid rgba(135, 206, 235, 0.3);
        border-radius: 8px;
        backdrop-filter: blur(5px);
    }

    .group-permissions-section h4 {
        color: black;
        font-size: 1.2rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .user-group-permissions {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
    }

    .user-group-permissions h5 {
        margin: 0 0 15px 0;
        color: black;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .permission-toggles {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
        align-items: center;
    }

    .permission-toggles .switch {
        display: flex;
        align-items: center;
        gap: 20px;
        margin: 0;
        flex-shrink: 0;
    }

    .permission-toggles .switch span:last-child {
        color: black;
        font-weight: 500;
        white-space: nowrap;
        min-width: 130px;
        flex-shrink: 0;
    }

    .form-actions {
        margin-top: 30px;
        text-align: right;
    }

    .groups-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }

    .group-card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .group-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    .group-header {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .group-header h4 {
        margin: 0;
        font-size: 1.1rem;
        color: black;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .group-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .group-actions form {
        margin: 0;
        padding: 0;
    }

    .group-actions button,
    .group-actions input[type="submit"] {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 8px;
        border-radius: 4px;
        color: black;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 35px;
        height: 35px;
    }

    .group-actions .btn-delete {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .group-actions .btn-delete:hover {
        background-color: rgba(231, 76, 60, 0.3);
        color: #c0392b;
        transform: scale(1.1);
    }

    .group-members {
        padding: 20px;
    }

    .member {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .member-avatar {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .member-info .member-name {
        font-weight: 600;
        color: black;
    }

    .member-permissions {
        margin-top: 5px;
    }

    .permission-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 5px;
        color: white;
    }

    .permission-badge.read {
        background-color: #28a745;
    }

    .permission-badge.delete {
        background-color: #dc3545;
    }

    .permission-badge.download {
        background-color: #17a2b8;
    }

    .no-permissions {
        color: rgba(0, 0, 0, 0.6);
        font-style: italic;
        font-size: 0.85rem;
    }

    .no-members,
    .no-groups-message {
        text-align: center;
        padding: 40px 20px;
        color: rgba(0, 0, 0, 0.6);
        font-style: italic;
    }

    .no-members i,
    .no-groups-message i {
        font-size: 2rem;
        margin-bottom: 10px;
        display: block;
        color: #000000ff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            gap: 10px;
            padding: 15px;
        }

        .admin-container {
            padding: 15px;
            margin: 10px auto;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .modal-large,
        .modal-content {
            width: 95%;
            max-width: none;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-section {
            padding: 15px;
        }

        .user-management-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .user-management-actions {
            width: 100%;
            justify-content: flex-start;
            margin-top: 10px;
        }

        .action-btn span {
            display: none;
        }
    }
</style>

</body>
</html>
