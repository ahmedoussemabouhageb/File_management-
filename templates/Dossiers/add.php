<?= $this->Flash->render() ?>

<h2>
    <?php if ($parentFolder): ?>
        Upload File or Create Folder in: <?= h($parentFolder->name) ?>
    <?php else: ?>
        Upload File or Create Folder
    <?php endif; ?>
</h2>

<?= $this->Form->create(null, ['type' => 'file', 'id' => 'upload-form']) ?>

<?= $this->Form->control('type', [
    'type' => 'select',
    'label' => 'Type',
    'options' => ['dossier' => 'Folder', 'fichier' => 'File'],
    'id' => 'type-select',
    'empty' => 'Select Type'
]) ?>

<div id="folder-fields" style="display: none;">
    <?= $this->Form->control('dossier_name', [
        'label' => 'Folder Name',
        'placeholder' => 'Enter folder name...',
        'id' => 'folder-name-input'
    ]) ?>
    <?php if ($parentFolder): ?>
        <p style="color: #ccc; font-size: 14px; margin-top: 10px;">
            📁 Folder will be created inside: <strong><?= h($parentFolder->name) ?></strong>
        </p>
    <?php else: ?>
        <p style="color: #ccc; font-size: 14px; margin-top: 10px;">
            📁 Folder will be created in the root directory
        </p>
    <?php endif; ?>
</div>

<div id="file-fields" style="display: none;">
    <?= $this->Form->control('fichier', [
        'type' => 'file',
        'label' => 'Choose a file',
        'id' => 'file-input'
    ]) ?>
    
    <?php if ($parentFolder): ?>
        <p style="color: #ccc; font-size: 14px; margin-top: 10px;">
            📤 File will be uploaded to: <strong><?= h($parentFolder->name) ?></strong>
        </p>
    <?php else: ?>
        <p style="color: #ccc; font-size: 14px; margin-top: 10px;">
            📤 File will be uploaded to the root directory
        </p>
    <?php endif; ?>
</div>

<?php if ($parentFolder): ?>
    <?= $this->Form->hidden('parent_id', ['value' => $parentFolder->id]) ?>
<?php endif; ?>

<?= $this->Form->button('Submit', ['id' => 'submit-btn']) ?>
<?= $this->Form->end() ?>

<p style="text-align: center; margin-top: 20px;">
    <?php if ($parentFolder): ?>
        <?= $this->Html->link('← Back to ' . h($parentFolder->name), ['action' => 'view', $parentFolder->id], ['class' => 'back-button']) ?>
    <?php else: ?>
        <?= $this->Html->link('← Back to Main', ['action' => 'index'], ['class' => 'back-button']) ?>
    <?php endif; ?>
</p>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('JavaScript loaded successfully');
    
    var select = document.getElementById('type-select');
    var folderFields = document.getElementById('folder-fields');
    var fileFields = document.getElementById('file-fields');
    var submitBtn = document.getElementById('submit-btn');
    var folderNameInput = document.getElementById('folder-name-input');
    var fileInput = document.getElementById('file-input');
    var form = document.getElementById('upload-form');

    if (!select || !folderFields || !fileFields || !submitBtn) {
        console.error('Required elements not found');
        return;
    }

    console.log('All elements found successfully');

    folderFields.style.display = 'none';
    fileFields.style.display = 'none';

    select.addEventListener('change', function () {
        console.log('Type changed to:', this.value);
        
        var value = this.value;
        
        folderFields.style.display = 'none';
        fileFields.style.display = 'none';
        
        if (value === 'dossier') {
            folderFields.style.display = 'block';
            console.log('Showing folder fields');
        } else if (value === 'fichier') {
            fileFields.style.display = 'block';
            console.log('Showing file fields');
        }
    });

    form.addEventListener('submit', function(e) {
        console.log('Form submitted');
        
        var selectedType = select.value;
        var isValid = false;
        
        if (selectedType === 'dossier') {
            var folderName = folderNameInput ? folderNameInput.value.trim() : '';
            if (!folderName) {
                e.preventDefault();
                alert('Please enter a folder name');
                return false;
            }
            isValid = true;
        } else if (selectedType === 'fichier') {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a file to upload');
                return false;
            }
            isValid = true;
        } else {
            e.preventDefault();
            alert('Please select a type (Folder or File)');
            return false;
        }
        
        if (isValid) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            console.log('Form is valid, submitting...');
        }
    });

    select.addEventListener('change', function() {
        console.log('Select value:', this.value);
        console.log('Folder fields display:', folderFields.style.display);
        console.log('File fields display:', fileFields.style.display);
    });
});
</script>

<style>
body, html {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #E0D3CC, #E0C5BC);
    color: white;
}

h2 {
    text-align: center;
    margin-top: 40px;
    font-size: 28px;
}

form {
    width: 450px;
    margin: 40px auto;
    background: rgba(0, 0, 0, 0.6);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
    color: white;
}

label {
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    color: white;
    font-size: 15px;
}

input[type="text"],
select,
input[type="file"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
    border: none;
    background-color: rgba(255, 255, 255, 0.9);
    color: black;
    font-size: 16px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 14px;
    background-color: #1d1d48;
    border: none;
    border-radius: 10px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s, background-color 0.3s;
}

button:hover {
    background-color: #111338;
    opacity: 1;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.back-button {
    background-color: #333;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.back-button:hover {
    background-color: #555;
}

/* Debug styles */
#folder-fields, #file-fields {
    border: 1px solid rgba(255,255,255,0.2);
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
}

#folder-fields {
    background-color: rgba(0,100,0,0.1);
}

#file-fields {
    background-color: rgba(0,0,100,0.1);
}
</style>