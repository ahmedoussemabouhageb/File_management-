<?= $this->Flash->render() ?>

<h2>Register</h2>

<?= $this->Form->create() ?>

<?= $this->Form->control('username') ?>
<?= $this->Form->control('password', ['type' => 'password']) ?>
<?= $this->Form->control('email') ?>
<?= $this->Form->control('nom') ?>
<?= $this->Form->control('prenom') ?>
<?= $this->Form->control('num') ?>
<?= $this->Form->control('adresse') ?>

<?= $this->Form->button('Register') ?>
<?= $this->Form->end() ?>
