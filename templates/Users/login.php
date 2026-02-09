<?= $this->Flash->render() ?>

<div class="login-wrapper">
  <div class="login-left">
    <div class="login-container">
      <h2>Login</h2>

      <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'login']]) ?>
          <?= $this->Form->control('username', ['label' => 'Username']) ?>
          <?= $this->Form->control('password', [
              'label' => 'Password',
              'type' => 'password',
              'id' => 'password-field'
          ]) ?>
          <span class="show-password" onclick="togglePassword()">Show/Hide Password</span>
          <?= $this->Form->button('Login') ?>
          <button type="button" id="openSignupModal" class="signup-button">Sign Up</button>
      <?= $this->Form->end() ?>
    </div>
  </div>

  <div class="login-right">
    <img src="/app/webroot/img//a.png" alt="Login Image" />
  </div>
</div>


<div id="signupModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeSignupModal">&times;</span>
    <h2>Register</h2>

    <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'register']]) ?>
      <?= $this->Form->control('email', ['label' => 'Email']) ?>
      <?= $this->Form->control('nom', ['label' => 'Nom']) ?>
      <?= $this->Form->control('prenom', ['label' => 'Prénom']) ?>
      <?= $this->Form->control('num', ['label' => 'Numéro de téléphone']) ?>
      <?= $this->Form->control('adresse', ['label' => 'Adresse']) ?>
      <?= $this->Form->control('username', ['label' => 'Username']) ?>
      <?= $this->Form->control('password', ['label' => 'Password', 'type' => 'password']) ?>
      <?= $this->Form->button('Register', ['class' => 'register-button']) ?>
    <?= $this->Form->end() ?>
  </div>
</div>


<script>
function togglePassword() {
    var x = document.getElementById("password-field");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

var modal = document.getElementById("signupModal");
var openBtn = document.getElementById("openSignupModal");
var closeBtn = document.getElementById("closeSignupModal");

openBtn.onclick = function() {
    modal.style.display = "block";
}

closeBtn.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<style>
body, html {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #E0D3CC, #E0C5BC);
    overflow: hidden;
}

.login-wrapper {
    display: flex;
    height: 100vh;
    width: 100vw;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    box-sizing: border-box;
}

.login-left {
    flex: 0 0 600px;
    display: flex;
    justify-content: flex-start;
    z-index: 2;
}

.login-container {
    width: 100%;
    background: rgba(0, 0, 0, 0.5);
    padding: 50px;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(0,0,0,0.6);
    color: #fff;
    backdrop-filter: blur(4px);
}

h2 {
    margin-bottom: 25px;
    color: #fff;
    font-size: 28px;
}

input[type="text"],
input[type="password"],
input[type="email"] {
    width: 100%;
    padding: 14px;
    margin: 10px 0;
    box-sizing: border-box;
    border: 1px solid #444;
    border-radius: 4px;
    font-size: 16px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

input::placeholder {
    color: #ccc;
}

button,
.register-button {
    background-color: #1d1d48;
    color: white;
    padding: 14px;
    width: 100%;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    margin-top: 12px;
    opacity: 0.6;
}

button:hover,
.register-button:hover {
    background-color: #218838;
}

.signup-button {
    background-color: #33337f;
    margin-top: 18px;
    opacity: 0.6;
    font-size: 15px;
    border-radius: 10px;
}

.signup-button:hover {
    background-color: #0056b3;
}

.show-password {
    margin-top: 12px;
    font-size: 14px;
    cursor: pointer;
    color: #00bfff;
    user-select: none;
    display: inline-block;
}

.login-right {
    flex: 1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    height: 100%;
}

.login-right img {
    width: 300%;
    height: auto;
    max-height: none;
    object-fit: contain;
    opacity: 0.3;
    transform: translateX(10px);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    font-color
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #000;
    color: white;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    width: 400px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}

</style>
