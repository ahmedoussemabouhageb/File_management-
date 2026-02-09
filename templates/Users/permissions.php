<h1>Manage User Permissions</h1>

<?= $this->Form->create(null, ['url' => ['action' => 'savePermissions']]) ?>
<table>
    <tr>
        <th>User</th>
        <th>Can Download</th>
        <th>Can Delete</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= h($user->username) ?></td>
            <td><?= $this->Form->checkbox("permissions[{$user->id}][download]", ['checked' => $user->can_download]) ?></td>
            <td><?= $this->Form->checkbox("permissions[{$user->id}][delete]", ['checked' => $user->can_delete]) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?= $this->Form->button('Save Permissions') ?>
<?= $this->Form->end() ?>
