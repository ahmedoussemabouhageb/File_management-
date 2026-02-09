<h1>Welcome, <?= h($user->username) ?>!</h1>

<h2>Upload New Dossier</h2>

<?= $this->Form->create(null, [
    'url' => ['controller' => 'Dossier', 'action' => 'add'],
    'type' => 'file'
]) ?>
    <?= $this->Form->control('dossier', ['type' => 'file', 'label' => 'Choose File']) ?>
    <?= $this->Form->button('Upload Dossier') ?>
<?= $this->Form->end() ?>

<hr>

<h2>Existing Dossiers</h2>

<?php if (!$dossiers->isEmpty()): ?>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Size (Bytes)</th>
                <th>Upload Date</th>
                <th>Download</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dossiers as $dossier): ?>
                <tr>
                    <td><?= h($dossier->id) ?></td>
                    <td><?= h($dossier->name) ?></td>
                    <td><?= h($dossier->size) ?></td>
                    <td><?= h($dossier->upload_date) ?></td>
                    <td>
                        <?php if (!empty($dossier->path)): ?>
                            <a href="<?= h($dossier->path) ?>" target="_blank">Download</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= h($dossier->username) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No dossiers uploaded yet.</p>
<?php endif; ?>
